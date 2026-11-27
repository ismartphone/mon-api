<?php

namespace App\Repository;

use App\Entity\Actor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Actor>
 */
class ActorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actor::class);
    }

    /**
     * @return array<string, int> actor ids indexed by name
     */
    public function findIdsIndexedByName(): array
    {
        $rows = $this->createQueryBuilder('actor')
            ->select('actor.id', 'actor.name')
            ->getQuery()
            ->getArrayResult();

        return array_column($rows, 'id', 'name');
    }
}
