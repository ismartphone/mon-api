<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class MovieInput
{
    /**
     * @param list<int> $categoryIds
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $title = '',

        #[Assert\Length(max: 5000)]
        public ?string $description = null,

        #[Assert\NotNull]
        #[Assert\Range(min: 1850, max: 2100)]
        public ?int $releaseYear = null,

        #[Assert\Length(max: 255)]
        public ?string $director = null,

        #[Assert\Positive]
        public ?int $runtime = null,

        #[Assert\Url(requireTld: true)]
        #[Assert\Length(max: 500)]
        public ?string $posterUrl = null,

        #[Assert\All([new Assert\Type('integer'), new Assert\Positive()])]
        public array $categoryIds = [],
    ) {
    }
}
