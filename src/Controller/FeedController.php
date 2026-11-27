<?php

namespace App\Controller;

use App\Dto\Pagination;
use App\Entity\User;
use App\Model\Paginator;
use App\Repository\RatingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class FeedController extends AbstractController
{
    /**
     * Latest ratings written by the users I follow.
     */
    #[Route('/api/feed', name: 'api_feed', methods: ['GET'], format: 'json')]
    public function __invoke(
        #[CurrentUser] User $user,
        RatingRepository $ratingRepository,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        Pagination $pagination = new Pagination(),
    ): JsonResponse {
        $paginator = Paginator::paginate($ratingRepository->createFeedQueryBuilder($user), $pagination);

        return $this->json($paginator, context: ['groups' => [Paginator::GROUP, 'rating:read']]);
    }
}
