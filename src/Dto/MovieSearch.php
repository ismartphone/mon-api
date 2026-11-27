<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class MovieSearch extends Pagination
{
    #[Assert\Length(max: 255)]
    public ?string $title = null;

    #[Assert\Range(min: 1850, max: 2100)]
    public ?int $year = null;

    #[Assert\Positive]
    public ?int $category = null;

    #[Assert\Choice(choices: ['title', 'releaseYear', 'createdAt'])]
    public string $sort = 'releaseYear';

    #[Assert\Choice(choices: ['ASC', 'DESC', 'asc', 'desc'])]
    public string $order = 'DESC';
}
