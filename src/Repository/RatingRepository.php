<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\Rating;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function createLatestByUserQueryBuilder(User $user): QueryBuilder
    {
        return $this->createLatestQueryBuilder()
            ->andWhere('rating.user = :user')
            ->setParameter('user', $user);
    }

    public function createLatestByMovieQueryBuilder(Movie $movie): QueryBuilder
    {
        return $this->createLatestQueryBuilder()
            ->andWhere('rating.movie = :movie')
            ->setParameter('movie', $movie);
    }

    /**
     * Latest ratings written by the users that $follower follows.
     */
    public function createFeedQueryBuilder(User $follower): QueryBuilder
    {
        return $this->createLatestQueryBuilder()
            ->innerJoin('author.followers', 'follower')
            ->andWhere('follower = :follower')
            ->setParameter('follower', $follower);
    }

    private function createLatestQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('rating')
            ->innerJoin('rating.user', 'author')
            ->addSelect('author')
            ->innerJoin('rating.movie', 'movie')
            ->addSelect('movie')
            ->orderBy('rating.ratedAt', 'DESC')
            ->addOrderBy('rating.id', 'DESC');
    }
}
