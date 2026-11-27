<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityValidator $entityValidator,
    ) {
    }

    /**
     * @param list<string> $roles
     */
    public function create(string $email, string $username, string $plainPassword, array $roles = []): User
    {
        $user = new User(mb_strtolower(trim($email)), trim($username));
        $user
            ->setRoles($roles)
            ->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->entityValidator->validate($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function follow(User $follower, User $followed): void
    {
        if ($follower === $followed) {
            throw new UnprocessableEntityHttpException('You cannot follow yourself.');
        }

        $follower->follow($followed);
        $this->entityManager->flush();
    }

    public function unfollow(User $follower, User $followed): void
    {
        $follower->unfollow($followed);
        $this->entityManager->flush();
    }
}
