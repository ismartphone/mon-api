<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'This email is already used.')]
#[UniqueEntity(fields: ['username'], message: 'This username is already used.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:public', 'rating:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read'])]
    private string $email;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['user:read', 'user:public', 'rating:read'])]
    private string $username;

    /**
     * @var list<string>
     */
    #[ORM\Column]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column]
    private string $password = '';

    #[ORM\Column]
    #[Groups(['user:read', 'user:public'])]
    private \DateTimeImmutable $createdAt;

    /**
     * Users that this user follows.
     *
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'followers')]
    #[ORM\JoinTable(name: 'user_follow')]
    #[ORM\JoinColumn(name: 'follower_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'followed_id', referencedColumnName: 'id')]
    private Collection $following;

    /**
     * Users that follow this user.
     *
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'following')]
    private Collection $followers;

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $ratings;

    public function __construct(string $email, string $username)
    {
        $this->email = $email;
        $this->username = $username;
        $this->createdAt = new \DateTimeImmutable();
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFollowing(): Collection
    {
        return $this->following;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function follow(User $user): static
    {
        if (!$this->following->contains($user)) {
            $this->following->add($user);
        }

        return $this;
    }

    public function unfollow(User $user): static
    {
        $this->following->removeElement($user);

        return $this;
    }

    public function isFollowing(User $user): bool
    {
        return $this->following->contains($user);
    }

    #[Groups(['user:read', 'user:public'])]
    public function getFollowersCount(): int
    {
        return $this->followers->count();
    }

    #[Groups(['user:read', 'user:public'])]
    public function getFollowingCount(): int
    {
        return $this->following->count();
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }
}
