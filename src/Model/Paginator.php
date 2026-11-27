<?php

namespace App\Model;

use App\Dto\Pagination;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Symfony\Component\Serializer\Attribute\Groups;

final class Paginator
{
    public const string GROUP = 'paginate';

    /**
     * @var list<object>
     */
    #[Groups([self::GROUP])]
    public readonly array $items;

    #[Groups([self::GROUP])]
    public readonly int $total;

    #[Groups([self::GROUP])]
    public readonly int $page;

    #[Groups([self::GROUP])]
    public readonly int $limit;

    #[Groups([self::GROUP])]
    public readonly int $lastPage;

    /**
     * @param list<object> $items
     */
    private function __construct(array $items, int $total, Pagination $pagination)
    {
        $this->items = $items;
        $this->total = $total;
        $this->page = $pagination->page;
        $this->limit = $pagination->limit;
        $this->lastPage = max(1, (int) ceil($total / $pagination->limit));
    }

    public static function paginate(QueryBuilder $queryBuilder, Pagination $pagination, bool $fetchJoinCollection = false): self
    {
        $query = $queryBuilder
            ->setFirstResult(($pagination->page - 1) * $pagination->limit)
            ->setMaxResults($pagination->limit)
            ->getQuery();

        $doctrinePaginator = new DoctrinePaginator($query, $fetchJoinCollection);

        return new self(iterator_to_array($doctrinePaginator, false), \count($doctrinePaginator), $pagination);
    }
}
