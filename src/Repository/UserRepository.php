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

    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct($registry, User::class);
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }
    
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function setPasswordHasher(UserPasswordHasherInterface $passwordHasher): void
    {
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
        return $this->createQueryBuilder('u')
            ->andWhere('u.Email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
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

    public function findAllWithFilters(array $filters = [], string $sort = null, string $direction = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('u');

        // CIN filter (exact match or regex)
        if (!empty($filters['cin'])) {
            if (str_starts_with($filters['cin'], '%')) {
                // Handle regex-like pattern
                $qb->andWhere('u.CIN LIKE :cin')
                    ->setParameter('cin', str_replace('%', '', $filters['cin']).'%');
            } else {
                // Exact match
                $qb->andWhere('u.CIN = :cin')
                    ->setParameter('cin', $filters['cin']);
            }
        }

        // Email filter
        if (!empty($filters['email'])) {
            $qb->andWhere('u.Email LIKE :email')
                ->setParameter('email', '%'.$filters['email'].'%');
        }

        // Age filter (calculated from birthday)
        if (!empty($filters['age'])) {
            $minDate = new \DateTime();
            $minDate->modify('-'.(intval($filters['age'])+1).' years');
            $maxDate = new \DateTime();
            $maxDate->modify('-'.intval($filters['age']).' years');

            $qb->andWhere('u.birthday <= :minDate')
                ->andWhere('u.birthday > :maxDate')
                ->setParameter('minDate', $minDate)
                ->setParameter('maxDate', $maxDate);
        }

        // Sorting
        if ($sort) {
            if ($sort === 'age') {
                // Special handling for age sorting
                $qb->addSelect('TIMESTAMPDIFF(YEAR, u.birthday, CURRENT_DATE()) AS HIDDEN age');
                $qb->orderBy('age', $direction);
            } else {
                $qb->orderBy('u.'.$sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }

}