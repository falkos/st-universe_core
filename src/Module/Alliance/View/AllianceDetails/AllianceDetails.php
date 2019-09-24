<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\AllianceDetails;

use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class AllianceDetails implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ALLIANCE';

    private $allianceDetailsRequest;

    private $allianceRelationRepository;

    private $allianceActionManager;

    private $allianceJobRepository;

    private $allianceRepository;

    public function __construct(
        AllianceDetailsRequestInterface $allianceDetailsRequest,
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceActionManagerInterface $allianceActionManager,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceRepositoryInterface $allianceRepository
    ) {
        $this->allianceDetailsRequest = $allianceDetailsRequest;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceRepository = $allianceRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $this->allianceRepository->find($this->allianceDetailsRequest->getAllianceId());
        if ($alliance === null) {
            return;
        }

        $allianceId = $alliance->getId();
        $userId = $game->getUser()->getId();

        $result = $this->allianceRelationRepository->getActiveByAlliance($allianceId);
        $userIsFounder = $this->allianceJobRepository->getSingleResultByAllianceAndType(
            $allianceId,
            ALLIANCE_JOBS_FOUNDER
        )->getUserId() === $game->getUser()->getId();

        $relations = [];
        foreach ($result as $key => $obj) {
            $relations[$key] = [
                'relation' => $obj,
                'opponent' => $obj->getOpponentId() == $alliance->getId() ? $obj->getAlliance() : $obj->getOpponent()
            ];
        }

        $replacementVars = $this->getReplacementVars($alliance);

        $description = str_replace(
            array_keys($replacementVars),
            array_values($replacementVars),
            $alliance->getDescription()
        );

        $isInAlliance = $alliance->getId() == $game->getUser()->getAllianceId();

        $game->setPageTitle(_('Allianz anzeigen'));
        $game->setTemplateFile('html/alliancedetails.xhtml');

        $game->setTemplateVar('ALLIANCE', $alliance);
        $game->setTemplateVar('ALLIANCE_RELATIONS', $relations);
        $game->setTemplateVar('DESCRIPTION', $description);
        $game->setTemplateVar('IS_IN_ALLIANCE', $isInAlliance);
        $game->setTemplateVar('CAN_LEAVE_ALLIANCE', $isInAlliance && !$userIsFounder);
        $game->setTemplateVar(
            'CAN_EDIT',
            $this->allianceActionManager->mayEdit($allianceId, $userId)
        );
        $game->setTemplateVar(
            'CAN_MANAGE_FOREIGN_RELATIONS',
            $this->allianceActionManager->mayManageForeignRelations($allianceId, $userId)
        );
        $game->setTemplateVar(
            'CAN_SIGNUP',
            $game->getUser()->maySignup($allianceId)
        );

        if ($game->getUser()->getAllianceId() > 0) {
            $game->appendNavigationPart(
                'alliance.php',
                _('Allianz')
            );
        }
        $game->appendNavigationPart('alliance.php?SHOW_LIST=1', _('Allianzliste'));
        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_ALLIANCE=1&id=%d', $alliance->getId()),
            _('Allianz anzeigen')
        );
    }

    /**
     * @todo refactor - duplicate of Overview::getReplacementVars
     */
    private function getReplacementVars(AllianceInterface $alliance): array
    {
        $replacementVars = [];
        $replacementVars['$ALLIANCE_HOMEPAGE_LINK'] = '<a href="' . $alliance->getHomepage() . '" target="_blank">' . _('Zur Allianz Homepage') . '</a>';
        $replacementVars['$ALLIANCE_BANNER'] = ($alliance->getAvatar() ? '<img src="' . $alliance->getFullAvatarpath() . '" />' : false);
        $replacementVars['$ALLIANCE_PRESIDENT'] = $alliance->getFounder()->getUser()->getUser();
        $replacementVars['$ALLIANCE_VICEPRESIDENT'] = ($alliance->getSuccessor() ? $alliance->getSuccessor()->getUser()->getUser() : _('Unbesetzt'));
        $replacementVars['$ALLIANCE_FOREIGNMINISTER'] = ($alliance->getDiplomatic() ? $alliance->getDiplomatic()->getUser()->getUser() : _('Unbesetzt'));
        return $replacementVars;
    }
}
