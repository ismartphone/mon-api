<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ORM\Index(name: 'idx_movie_title', columns: ['title'])]
#[ORM\Index(name: 'idx_movie_release_year', columns: ['release_year'])]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['movie:read', 'rating:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['movie:read', 'rating:read'])]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['movie:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['movie:read', 'rating:read'])]
    private int $releaseYear;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['movie:read'])]
    private ?string $director = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['movie:read'])]
    private ?int $runtime = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['movie:read', 'rating:read'])]
    private ?string $posterUrl = null;

    #[ORM\Column]
    #[Groups(['movie:read'])]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'movies')]
    #[ORM\JoinTable(name: 'movie_category')]
    #[ORM\JoinColumn(name: 'movie_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id')]
    #[Groups(['movie:read'])]
    private Collection $categories;

    /**
     * @var Collection<int, Actor>
     */
    #[ORM\ManyToMany(targetEntity: Actor::class, inversedBy: 'movies')]
    #[ORM\JoinTable(name: 'movie_actor')]
    #[ORM\JoinColumn(name: 'movie_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'actor_id', referencedColumnName: 'id')]
    #[Groups(['movie:detail'])]
    private Collection $actors;

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'movie', orphanRemoval: true)]
    private Collection $ratings;

    public function __construct(string $title, int $releaseYear)
    {
        $this->title = $title;
        $this->releaseYear = $releaseYear;
        $this->createdAt = new \DateTimeImmutable();
        $this->categories = new ArrayCollection();
        $this->actors = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getReleaseYear(): int
    {
        return $this->releaseYear;
    }

    public function setReleaseYear(int $releaseYear): static
    {
        $this->releaseYear = $releaseYear;

        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(?string $director): static
    {
        $this->director = $director;

        return $this;
    }

    public function getRuntime(): ?int
    {
        return $this->runtime;
    }

    public function setRuntime(?int $runtime): static
    {
        $this->runtime = $runtime;

        return $this;
    }

    public function getPosterUrl(): ?string
    {
        return $this->posterUrl;
    }

    public function setPosterUrl(?string $posterUrl): static
    {
        $this->posterUrl = $posterUrl;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
        }

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    #[Groups(['movie:detail'])]
    public function getRatingsCount(): int
    {
        return $this->ratings->count();
    }

    #[Groups(['movie:detail'])]
    public function getAverageRating(): ?float
    {
        if ($this->ratings->isEmpty()) {
            return null;
        }

        $total = array_sum($this->ratings->map(static fn (Rating $rating): int => $rating->getScore())->toArray());

        return round($total / $this->ratings->count(), 1);
    }
}
