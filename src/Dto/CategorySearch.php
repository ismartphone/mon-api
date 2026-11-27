<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CategorySearch extends Pagination
{
    #[Assert\Length(max: 100)]
    public ?string $name = null;
}
