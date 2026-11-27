<?php

namespace App\Controller;

use App\Dto\Pagination;
use App\Dto\RatingInput;
use App\Entity\Movie;
use App\Entity\User;
use App\Model\Paginator;
use App\Repository\RatingRepository;
use App\Service\RatingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/movies/{id}', requirements: ['id' => '\d+'], name: 'api_movie_ratings_', format: 'json')]
final class RatingController extends AbstractController
{
    #[Route('/ratings', name: 'list', methods: ['GET'])]
    public function list(
        Movie $movie,
        RatingRepository $ratingRepository,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        Pagination $pagination = new Pagination(),
    ): JsonResponse {
        $paginator = Paginator::paginate($ratingRepository->createLatestByMovieQueryBuilder($movie), $pagination);

        return $this->json($paginator, context: ['groups' => [Paginator::GROUP, 'rating:read']]);
    }

    #[Route('/rating', name: 'rate', methods: ['PUT'])]
    public function rate(
        Movie $movie,
        #[CurrentUser] User $user,
        #[MapRequestPayload] RatingInput $input,
        RatingService $ratingService,
    ): JsonResponse {
        [$rating, $created] = $ratingService->rate($user, $movie, $input);

        return $this->json(
            $rating,
            $created ? Response::HTTP_CREATED : Response::HTTP_OK,
            context: ['groups' => ['rating:read']],
        );
    }

    #[Route('/rating', name: 'delete', methods: ['DELETE'])]
    public function delete(Movie $movie, #[CurrentUser] User $user, RatingService $ratingService): Response
    {
        $ratingService->delete($user, $movie);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
