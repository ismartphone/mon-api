<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class Pagination
{
    #[Assert\Positive]
    public int $page = 1;

    #[Assert\Range(min: 1, max: 50)]
    public int $limit = 10;
}
