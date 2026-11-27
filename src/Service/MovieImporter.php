<?php

namespace App\Service;

use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Movie;
use App\Repository\ActorRepository;
use App\Repository\CategoryRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Imports movies from the "sample_mflix/movies.json" dataset (one JSON document per line).
 */
final class MovieImporter
{
    private const int BATCH_SIZE = 500;

    /** @var array<string, int> */
    private array $categoryIds = [];

    /** @var array<string, int> */
    private array $actorIds = [];

    /** @var array<string, true> "title|year" keys of the movies already in database */
    private array $existingMovies = [];

    /** @var array<string, Category> categories created in the current batch */
    private array $newCategories = [];

    /** @var array<string, Actor> actors created in the current batch */
    private array $newActors = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
        private readonly ActorRepository $actorRepository,
        private readonly MovieRepository $movieRepository,
    ) {
    }

    /**
     * @param callable(int $importedCount): void $onProgress
     *
     * @return int number of imported movies
     */
    public function import(string $filePath, ?int $limit, callable $onProgress): int
    {
        $file = fopen($filePath, 'r');
        if (false === $file) {
            throw new \RuntimeException(\sprintf('Unable to open "%s".', $filePath));
        }

        $this->categoryIds = $this->categoryRepository->findIdsIndexedByName();
        $this->actorIds = $this->actorRepository->findIdsIndexedByName();
        $this->existingMovies = array_fill_keys($this->movieRepository->findTitleYearKeys(), true);
        $importedCount = 0;

        while (!feof($file) && (null === $limit || $importedCount < $limit)) {
            $line = fgets($file);
            if (false === $line || '' === trim($line)) {
                continue;
            }

            $movieData = json_decode($line, true);
            if (!\is_array($movieData) || !$this->importMovie($movieData)) {
                continue;
            }

            ++$importedCount;
            if (0 === $importedCount % self::BATCH_SIZE) {
                $this->flushBatch();
                $onProgress($importedCount);
            }
        }

        fclose($file);
        $this->flushBatch();
        $onProgress($importedCount);

        return $importedCount;
    }

    /**
     * @param array<string, mixed> $movieData
     */
    private function importMovie(array $movieData): bool
    {
        $title = trim((string) ($movieData['title'] ?? ''));
        $releaseYear = $this->extractYear($movieData['year'] ?? null);

        if ('' === $title || null === $releaseYear) {
            return false;
        }

        $title = mb_substr($title, 0, 255);
        $key = MovieRepository::titleYearKey($title, $releaseYear);
        if (isset($this->existingMovies[$key])) {
            return false;
        }
        $this->existingMovies[$key] = true;

        $movie = new Movie($title, $releaseYear);
        $movie
            ->setDescription($movieData['plot'] ?? $movieData['fullplot'] ?? null)
            ->setDirector($this->extractDirector($movieData['directors'] ?? []))
            ->setRuntime($this->extractInt($movieData['runtime'] ?? null))
            ->setPosterUrl($this->extractPosterUrl($movieData['poster'] ?? null));

        foreach ($this->extractNames($movieData['genres'] ?? [], 100) as $name) {
            $movie->addCategory($this->getCategory($name));
        }

        foreach ($this->extractNames($movieData['cast'] ?? [], 255) as $name) {
            $movie->addActor($this->getActor($name));
        }

        $this->entityManager->persist($movie);

        return true;
    }

    private function getCategory(string $name): Category
    {
        if (isset($this->newCategories[$name])) {
            return $this->newCategories[$name];
        }

        if (isset($this->categoryIds[$name])) {
            return $this->entityManager->getReference(Category::class, $this->categoryIds[$name]);
        }

        $category = new Category($name);
        $this->entityManager->persist($category);

        return $this->newCategories[$name] = $category;
    }

    private function getActor(string $name): Actor
    {
        if (isset($this->newActors[$name])) {
            return $this->newActors[$name];
        }

        if (isset($this->actorIds[$name])) {
            return $this->entityManager->getReference(Actor::class, $this->actorIds[$name]);
        }

        $actor = new Actor($name);
        $this->entityManager->persist($actor);

        return $this->newActors[$name] = $actor;
    }

    /**
     * Saves the current batch and frees memory, keeping the ids of the new categories and actors.
     */
    private function flushBatch(): void
    {
        $this->entityManager->flush();

        foreach ($this->newCategories as $name => $category) {
            $this->categoryIds[$name] = (int) $category->getId();
        }

        foreach ($this->newActors as $name => $actor) {
            $this->actorIds[$name] = (int) $actor->getId();
        }

        $this->newCategories = [];
        $this->newActors = [];
        $this->entityManager->clear();
    }

    /**
     * MongoDB exports numbers as {"$numberInt": "42"}; some years also contain extra characters ("2014è").
     */
    private function extractInt(mixed $value): ?int
    {
        if (\is_array($value)) {
            $value = $value['$numberInt'] ?? $value['$numberLong'] ?? $value['$numberDouble'] ?? null;
        }

        if (null === $value || !preg_match('/\d+/', (string) $value, $matches)) {
            return null;
        }

        return (int) $matches[0];
    }

    private function extractYear(mixed $value): ?int
    {
        $year = $this->extractInt($value);

        return null !== $year && $year >= 1850 && $year <= 2100 ? $year : null;
    }

    private function extractDirector(mixed $directors): ?string
    {
        $names = $this->extractNames($directors, 255);

        return [] === $names ? null : mb_substr(implode(', ', $names), 0, 255);
    }

    private function extractPosterUrl(mixed $url): ?string
    {
        return \is_string($url) && mb_strlen($url) <= 500 && filter_var($url, \FILTER_VALIDATE_URL) ? $url : null;
    }

    /**
     * @return list<string> unique, trimmed names
     */
    private function extractNames(mixed $names, int $maxLength): array
    {
        if (!\is_array($names)) {
            return [];
        }

        $cleanNames = [];
        foreach ($names as $name) {
            $name = mb_substr(trim((string) $name), 0, $maxLength);
            if ('' !== $name) {
                $cleanNames[$name] = $name;
            }
        }

        return array_values($cleanNames);
    }
}
