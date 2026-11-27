<?php

namespace App\Controller;

use App\Dto\Pagination;
use App\Entity\User;
use App\Model\Paginator;
use App\Repository\RatingRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/users/{id}', requirements: ['id' => '\d+'], name: 'api_users_', format: 'json')]
final class UserController extends AbstractController
{
    #[Route('', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, context: ['groups' => ['user:public']]);
    }

    #[Route('/ratings', name: 'ratings', methods: ['GET'])]
    public function ratings(
        User $user,
        RatingRepository $ratingRepository,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        Pagination $pagination = new Pagination(),
    ): JsonResponse {
        $paginator = Paginator::paginate($ratingRepository->createLatestByUserQueryBuilder($user), $pagination);

        return $this->json($paginator, context: ['groups' => [Paginator::GROUP, 'rating:read']]);
    }

    #[Route('/follow', name: 'follow', methods: ['POST'])]
    public function follow(User $user, #[CurrentUser] User $currentUser, UserService $userService): Response
    {
        $userService->follow($currentUser, $user);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/follow', name: 'unfollow', methods: ['DELETE'])]
    public function unfollow(User $user, #[CurrentUser] User $currentUser, UserService $userService): Response
    {
        $userService->unfollow($currentUser, $user);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
