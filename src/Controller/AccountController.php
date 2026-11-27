<?php

namespace App\Controller;

use App\Dto\Pagination;
use App\Dto\RegisterInput;
use App\Entity\User;
use App\Model\Paginator;
use App\Repository\RatingRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api', name: 'api_account_', format: 'json')]
final class AccountController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(#[MapRequestPayload] RegisterInput $input, UserService $userService): JsonResponse
    {
        $user = $userService->create($input->email, $input->username, $input->password);

        return $this->json($user, Response::HTTP_CREATED, context: ['groups' => ['user:read']]);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json($user, context: ['groups' => ['user:read']]);
    }

    #[Route('/me/ratings', name: 'ratings', methods: ['GET'])]
    public function ratings(
        #[CurrentUser] User $user,
        RatingRepository $ratingRepository,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        Pagination $pagination = new Pagination(),
    ): JsonResponse {
        $paginator = Paginator::paginate($ratingRepository->createLatestByUserQueryBuilder($user), $pagination);

        return $this->json($paginator, context: ['groups' => [Paginator::GROUP, 'rating:read']]);
    }

    #[Route('/me/following', name: 'following', methods: ['GET'])]
    public function following(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json($user->getFollowing(), context: ['groups' => ['user:public']]);
    }

    #[Route('/me/followers', name: 'followers', methods: ['GET'])]
    public function followers(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json($user->getFollowers(), context: ['groups' => ['user:public']]);
    }
}
