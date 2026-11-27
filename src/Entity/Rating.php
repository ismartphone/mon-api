<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_rating_user_movie', columns: ['user_id', 'movie_id'])]
#[ORM\Index(name: 'idx_rating_rated_at', columns: ['rated_at'])]
class Rating
{
    public const int MIN_SCORE = 1;
    public const int MAX_SCORE = 10;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['rating:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['rating:read'])]
    private int $score;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['rating:read'])]
    private ?string $comment = null;

    #[ORM\Column]
    #[Groups(['rating:read'])]
    private \DateTimeImmutable $ratedAt;

    #[ORM\ManyToOne(inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['rating:read'])]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['rating:read'])]
    private Movie $movie;

    public function __construct(User $user, Movie $movie, int $score, ?string $comment = null)
    {
        $this->user = $user;
        $this->movie = $movie;
        $this->update($score, $comment);
    }

    public function update(int $score, ?string $comment): void
    {
        $this->score = $score;
        $this->comment = $comment;
        $this->ratedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getRatedAt(): \DateTimeImmutable
    {
        return $this->ratedAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getMovie(): Movie
    {
        return $this->movie;
    }
}
