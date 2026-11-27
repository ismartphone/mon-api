<?php

namespace App\Dto;

use App\Entity\Rating;
use Symfony\Component\Validator\Constraints as Assert;

final class RatingInput
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Range(min: Rating::MIN_SCORE, max: Rating::MAX_SCORE)]
        public ?int $score = null,

        #[Assert\Length(max: 1000)]
        public ?string $comment = null,
    ) {
    }
}
