<?php

namespace App\Repository;

use App\Dto\CategorySearch;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function createSearchQueryBuilder(CategorySearch $search): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('category')
            ->orderBy('category.name', 'ASC');

        if (null !== $search->name && '' !== $search->name) {
            $queryBuilder
                ->andWhere('LOWER(category.name) LIKE :name')
                ->setParameter('name', '%'.mb_strtolower($search->name).'%');
        }

        return $queryBuilder;
    }

    /**
     * @return array<string, int> category ids indexed by name
     */
    public function findIdsIndexedByName(): array
    {
        $rows = $this->createQueryBuilder('category')
            ->select('category.id', 'category.name')
            ->getQuery()
            ->getArrayResult();

        return array_column($rows, 'id', 'name');
    }
}
