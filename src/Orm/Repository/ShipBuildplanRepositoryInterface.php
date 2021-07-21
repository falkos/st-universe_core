<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipBuildplanInterface;

/**
 * @method null|ShipBuildplanInterface find(integer $id)
 */
interface ShipBuildplanRepositoryInterface extends ObjectRepository
{
    /**
     * @return ShipBuildplanInterface[]
     */
    public function getByUserAndBuildingFunction(int $userId, int $buildingFunction): array;

    public function getCountByRumpAndUser(int $rumpId, int $userId): int;

    public function getByUserShipRumpAndSignature(int $userId, int $shipRumpId, string $signature): ?ShipBuildplanInterface;

    public function getWorkbeeBuildplan(int $factionId): ?ShipBuildplanInterface;

    public function prototype(): ShipBuildplanInterface;

    public function save(ShipBuildplanInterface $shipBuildplan): void;

    public function delete(ShipBuildplanInterface $shipBuildplan): void;

    /**
     * @return ShipBuildplanInterface[]
     */
    public function getByUser(int $userId): array;
}
