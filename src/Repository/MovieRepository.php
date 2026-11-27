<?php

namespace App\Repository;

use App\Dto\MovieSearch;
use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function createSearchQueryBuilder(MovieSearch $search): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('movie')
            ->leftJoin('movie.categories', 'category')
            ->addSelect('category')
            ->orderBy('movie.'.$search->sort, $search->order)
            ->addOrderBy('movie.id', 'ASC');

        if (null !== $search->title && '' !== $search->title) {
            $queryBuilder
                ->andWhere('LOWER(movie.title) LIKE :title')
                ->setParameter('title', '%'.mb_strtolower($search->title).'%');
        }

        if (null !== $search->year) {
            $queryBuilder
                ->andWhere('movie.releaseYear = :year')
                ->setParameter('year', $search->year);
        }

        if (null !== $search->category) {
            $queryBuilder
                ->andWhere(':categoryId MEMBER OF movie.categories')
                ->setParameter('categoryId', $search->category);
        }

        return $queryBuilder;
    }

    /**
     * @return list<string> one "title|year" key per movie, used to avoid importing duplicates
     */
    public function findTitleYearKeys(): array
    {
        $rows = $this->createQueryBuilder('movie')
            ->select('movie.title', 'movie.releaseYear')
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $row): string => self::titleYearKey($row['title'], $row['releaseYear']), $rows);
    }

    public static function titleYearKey(string $title, int $releaseYear): string
    {
        return mb_strtolower($title).'|'.$releaseYear;
    }
}
