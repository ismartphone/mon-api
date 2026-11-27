<?php

namespace App\Service;

use App\Dto\RatingInput;
use App\Entity\Movie;
use App\Entity\Rating;
use App\Entity\User;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class RatingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RatingRepository $ratingRepository,
    ) {
    }

    /**
     * Creates the user's rating for the movie, or updates it if it already exists.
     *
     * @return array{Rating, bool} the rating, and whether it was created
     */
    public function rate(User $user, Movie $movie, RatingInput $input): array
    {
        $rating = $this->ratingRepository->findOneBy(['user' => $user, 'movie' => $movie]);
        $created = null === $rating;

        if ($created) {
            $rating = new Rating($user, $movie, (int) $input->score, $input->comment);
            $this->entityManager->persist($rating);
        } else {
            $rating->update((int) $input->score, $input->comment);
        }

        $this->entityManager->flush();

        return [$rating, $created];
    }

    public function delete(User $user, Movie $movie): void
    {
        $rating = $this->ratingRepository->findOneBy(['user' => $user, 'movie' => $movie]);
        if (null === $rating) {
            throw new NotFoundHttpException('You have not rated this movie.');
        }

        $this->entityManager->remove($rating);
        $this->entityManager->flush();
    }
}
