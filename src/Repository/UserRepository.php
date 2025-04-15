<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($registry, User::class);
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;

    }

    public function findAllUsers(): array
    {
        return $this->findAll();
    }

    public function findById(int $id): ?User
    {
        return $this->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['Email' => $email]);
    }

    public function findByName(string $name): array
    {
        return $this->findBy(['Name' => $name]);
    }
    public function save(User $user, bool $flush = true): void
    {
        if ($user->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            $user->eraseCredentials();
        }
        $this->entityManager->persist($user);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(User $user, bool $flush = true): void
    {
        $this->entityManager->remove($user);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function updatePassword(User $user, string $newPassword, bool $flush = true): void
    {

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $newPassword
        );

        $user->setPassword($hashedPassword);
        $this->entityManager->persist($user);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function updateIsActive(User $user, bool $isActive, bool $flush = true): void
    {
        $user->setIsActive($isActive);
        $this->entityManager->persist($user);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function updateUser(User $user, bool $flush = true): void
    {
        $this->entityManager->persist($user);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}