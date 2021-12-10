<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Module\Ship\Lib\Battle\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ProjectileWeaponPhaseInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipAttackCycle implements ShipAttackCycleInterface
{
    public const MAIN_SEMAPHORE_KEY = 1;

    private ShipRepositoryInterface $shipRepository;

    private EnergyWeaponPhaseInterface $energyWeaponPhase;

    private ProjectileWeaponPhaseInterface $projectileWeaponPhase;

    private FightLibInterface $fightLib;

    /**
     * @return ShipInterface[]
     */
    private array $attacker = [];

    /**
     * @return ShipInterface[]
     */
    private array $defender = [];

    private bool $firstStrike = true;

    private array $messages = [];

    private array $usedShips = ['attacker' => [], 'defender' => []];

    private bool $oneWay = false;

    private $semaphores = [];

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        EnergyWeaponPhaseInterface $energyWeaponPhase,
        ProjectileWeaponPhaseInterface $projectileWeaponPhase,
        FightLibInterface $fightLib
    ) {
        $this->shipRepository = $shipRepository;
        $this->energyWeaponPhase = $energyWeaponPhase;
        $this->projectileWeaponPhase = $projectileWeaponPhase;
        $this->fightLib = $fightLib;
    }

    public function init(
        array $attackingShips,
        array $defendingShips,
        bool $oneWay = false
    ): void {
        $this->attacker = $attackingShips;
        $this->defender = $defendingShips;
        $this->oneWay = $oneWay;

        //concurrency
        $this->acquireSemaphores();

        $this->firstStrike = true;
        $this->messages = [];
        $this->usedShips = ['attacker' => [], 'defender' => []];
    }

    private function acquireSemaphores(): void
    {
        $mainSema = sem_get(self::MAIN_SEMAPHORE_KEY, 1, 0666, 0);
        sem_acquire($mainSema);

        foreach ($this->attacker as $ship) {
            $this->semaphores[] = sem_get($ship->getId(), 1, 0666, 0);
        }
        foreach ($this->defender as $ship) {
            $this->semaphores[] = sem_get($ship->getId(), 1, 0666, 0);
        }

        foreach ($this->semaphores as $sema) {
            sem_acquire($sema);
        }

        sem_release($mainSema);
    }

    public function releaseSemaphores(): void
    {
        foreach ($this->semaphores as $sema) {
            sem_release($sema);
        }
    }

    /**
     * @param string $usedShipKey
     * @param ShipInterface[] $attacker
     * @param ShipInterface[] $defender
     */
    private function getFixture(string $usedShipKey, array $attacker, array $defender): array
    {
        if ($attacker === []) die();

        $attackingShip = $attacker[array_rand($attacker)];
        $this->usedShips[$usedShipKey][$attackingShip->getId()] = $attackingShip;

        return [
            $attackingShip,
            $defender
        ];
    }

    public function cycle(bool $isAlertRed = false): void
    {
        foreach ($this->attacker as $attacker) {
            $this->addMessageMerge($this->fightLib->ready($attacker));
        }
        if (!$this->oneWay) {
            foreach ($this->defender as $defender) {
                $this->addMessageMerge($this->fightLib->ready($defender));
            }
        }

        while (true) {
            $usedAttackerCount = count($this->usedShips['attacker']);
            $usedDefenderCount = count($this->usedShips['defender']);

            // Check if there're any useable ships at all
            if ($usedAttackerCount >= count($this->attacker) && $usedDefenderCount >= count($this->defender)) {
                break;
            }

            $attackerPool = $this->fightLib->filterInactiveShips($this->attacker);
            $defenderPool = $this->fightLib->filterInactiveShips($this->defender);

            if ($attackerPool === [] || $defenderPool === []) {
                break;
            }

            if ($this->firstStrike) {
                $this->firstStrike = false;

                [$attackingShip, $targetShipPool] = $this->getFixture('attacker', $attackerPool, $defenderPool);
            } else {
                $readyAttacker = array_filter(
                    $attackerPool,
                    function (ShipInterface $ship): bool {
                        return !array_key_exists($ship->getId(), $this->usedShips['attacker']) && $this->canFire($ship);
                    }
                );
                $readyDefender = array_filter(
                    $defenderPool,
                    function (ShipInterface $ship): bool {
                        return !$this->oneWay && !array_key_exists($ship->getId(), $this->usedShips['defender']) && $this->canFire($ship);
                    }
                );
                if ($readyAttacker === [] && $readyDefender === []) {
                    break;
                }
                if ($readyAttacker === []) {
                    [$attackingShip, $targetShipPool] = $this->getFixture('defender', $readyDefender, $attackerPool);
                } else {
                    $random = rand(1, 2);
                    if ($readyDefender === [] || $random === 1 || $this->oneWay) {
                        [$attackingShip, $targetShipPool] = $this->getFixture('attacker', $readyAttacker, $defenderPool);
                    } else {
                        [$attackingShip, $targetShipPool] = $this->getFixture('defender', $readyDefender, $attackerPool);
                    }
                }
            }

            $this->addMessageMerge($this->energyWeaponPhase->fire($attackingShip, $targetShipPool, $isAlertRed));

            $this->addMessageMerge($this->projectileWeaponPhase->fire($attackingShip, $this->fightLib->filterInactiveShips($targetShipPool), $isAlertRed));
        }

        foreach ($this->attacker as $ship) {
            $this->shipRepository->save($ship);
        }

        foreach ($this->defender as $ship) {
            $this->shipRepository->save($ship);
        }
    }

    private function addMessageMerge($msg): void
    {
        $this->messages = array_merge($this->getMessages(), $msg);
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    private function canFire(ShipInterface $ship): bool
    {
        if ($ship->getDisabled()) {
            return false;
        }
        if ($ship->getEps() === 0) {
            return false;
        }
        if (!$ship->getNbs()) {
            return false;
        }
        if (!$ship->canAttack()) {
            return false;
        }
        return true;
    }
}
