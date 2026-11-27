<?php

namespace App\Controller;

use App\Dto\CategoryInput;
use App\Dto\CategorySearch;
use App\Entity\Category;
use App\Model\Paginator;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/categories', name: 'api_categories_', format: 'json')]
final class CategoryController extends AbstractController
{
    private const array READ_GROUPS = ['category:read'];

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        CategoryRepository $categoryRepository,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        CategorySearch $search = new CategorySearch(),
    ): JsonResponse {
        $paginator = Paginator::paginate($categoryRepository->createSearchQueryBuilder($search), $search);

        return $this->json($paginator, context: ['groups' => [Paginator::GROUP, ...self::READ_GROUPS]]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        return $this->json($category, context: ['groups' => self::READ_GROUPS]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(#[MapRequestPayload] CategoryInput $input, CategoryService $categoryService): JsonResponse
    {
        $category = $categoryService->create($input);

        return $this->json(
            $category,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('api_categories_show', ['id' => $category->getId()])],
            ['groups' => self::READ_GROUPS],
        );
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Category $category, #[MapRequestPayload] CategoryInput $input, CategoryService $categoryService): JsonResponse
    {
        return $this->json($categoryService->update($category, $input), context: ['groups' => self::READ_GROUPS]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Category $category, CategoryService $categoryService): Response
    {
        $categoryService->delete($category);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
