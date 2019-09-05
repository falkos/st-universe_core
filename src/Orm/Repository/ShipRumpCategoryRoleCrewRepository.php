<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;

final class ShipRumpCategoryRoleCrewRepository extends EntityRepository implements ShipRumpCategoryRoleCrewRepositoryInterface
{
    public function getByShipRumpCategoryAndRole(
        int $shipRumpCategoryId,
        int $shipRumpRoleId
    ): ?ShipRumpCategoryRoleCrewInterface {
        return $this->findOneBy([
            'rump_category_id' => $shipRumpCategoryId,
            'rump_role_id' => $shipRumpRoleId,
        ]);
    }
}