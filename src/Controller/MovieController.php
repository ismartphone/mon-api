<?php

namespace App\Controller;

use App\Dto\MovieInput;
use App\Dto\MovieSearch;
use App\Entity\Movie;
use App\Model\Paginator;
use App\Repository\MovieRepository;
use App\Service\MovieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/movies', name: 'api_movies_', format: 'json')]
final class MovieController extends AbstractController
{
    private const array READ_GROUPS = ['movie:read', 'movie:detail'];

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        MovieRepository $movieRepository,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        MovieSearch $search = new MovieSearch(),
    ): JsonResponse {
        $paginator = Paginator::paginate($movieRepository->createSearchQueryBuilder($search), $search, fetchJoinCollection: true);

        return $this->json($paginator, context: ['groups' => [Paginator::GROUP, 'movie:read']]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Movie $movie): JsonResponse
    {
        return $this->json($movie, context: ['groups' => self::READ_GROUPS]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(#[MapRequestPayload] MovieInput $input, MovieService $movieService): JsonResponse
    {
        $movie = $movieService->create($input);

        return $this->json(
            $movie,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('api_movies_show', ['id' => $movie->getId()])],
            ['groups' => self::READ_GROUPS],
        );
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Movie $movie, #[MapRequestPayload] MovieInput $input, MovieService $movieService): JsonResponse
    {
        return $this->json($movieService->update($movie, $input), context: ['groups' => self::READ_GROUPS]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Movie $movie, MovieService $movieService): Response
    {
        $movieService->delete($movie);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
