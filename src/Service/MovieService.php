<?php

namespace App\Service;

use App\Dto\MovieInput;
use App\Entity\Category;
use App\Entity\Movie;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class MovieService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function create(MovieInput $input): Movie
    {
        $movie = new Movie($input->title, (int) $input->releaseYear);
        $this->hydrate($movie, $input);

        $this->entityManager->persist($movie);
        $this->entityManager->flush();

        return $movie;
    }

    public function update(Movie $movie, MovieInput $input): Movie
    {
        $movie
            ->setTitle($input->title)
            ->setReleaseYear((int) $input->releaseYear);
        $this->hydrate($movie, $input);

        $this->entityManager->flush();

        return $movie;
    }

    public function delete(Movie $movie): void
    {
        $this->entityManager->remove($movie);
        $this->entityManager->flush();
    }

    private function hydrate(Movie $movie, MovieInput $input): void
    {
        $movie
            ->setDescription($input->description)
            ->setDirector($input->director)
            ->setRuntime($input->runtime)
            ->setPosterUrl($input->posterUrl);

        foreach ($movie->getCategories()->toArray() as $category) {
            $movie->removeCategory($category);
        }

        foreach ($this->findCategories($input->categoryIds) as $category) {
            $movie->addCategory($category);
        }
    }

    /**
     * @param list<int> $categoryIds
     *
     * @return list<Category>
     */
    private function findCategories(array $categoryIds): array
    {
        $categoryIds = array_values(array_unique($categoryIds));
        if ([] === $categoryIds) {
            return [];
        }

        $categories = $this->categoryRepository->findBy(['id' => $categoryIds]);
        $foundIds = array_map(static fn (Category $category): ?int => $category->getId(), $categories);
        $unknownIds = array_diff($categoryIds, $foundIds);

        if ([] !== $unknownIds) {
            throw new UnprocessableEntityHttpException(\sprintf('Unknown category id(s): %s.', implode(', ', $unknownIds)));
        }

        return $categories;
    }
}
