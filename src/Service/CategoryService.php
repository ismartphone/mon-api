<?php

namespace App\Service;

use App\Dto\CategoryInput;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EntityValidator $entityValidator,
    ) {
    }

    public function create(CategoryInput $input): Category
    {
        $category = new Category(trim($input->name));
        $this->entityValidator->validate($category);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    public function update(Category $category, CategoryInput $input): Category
    {
        $category->setName(trim($input->name));
        $this->entityValidator->validate($category);

        $this->entityManager->flush();

        return $category;
    }

    public function delete(Category $category): void
    {
        foreach ($category->getMovies() as $movie) {
            $movie->removeCategory($category);
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
