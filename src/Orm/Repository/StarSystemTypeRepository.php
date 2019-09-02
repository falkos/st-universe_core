<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\StarSystemType;

final class StarSystemTypeRepository extends EntityRepository implements StarSystemTypeRepositoryInterface
{
    public function getWithoutDatabaseEntry(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT t FROM %s t WHERE t.database_id NOT IN (SELECT d.id FROM %s d WHERE d.category_id = :categoryId)',
                    StarSystemType::class,
                    DatabaseEntry::class
                )
            )
            ->setParameters([
                'categoryId' => DATABASE_CATEGORY_STAR_SYSTEM_TYPE,
            ])
            ->getResult();
    }
}