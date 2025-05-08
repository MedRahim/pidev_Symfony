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

        // Normalize filters for partial matches
        if (!empty($filters['cin'])) {
            $qb->andWhere('u.CIN LIKE :cin')
                ->setParameter('cin', '%' . $filters['cin'] . '%');
        }

        if (!empty($filters['email'])) {
            $qb->andWhere('u.Email LIKE :email')
                ->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['name'])) {
            $qb->andWhere('u.Name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        // Age filter (range-based using birthday)
        if (!empty($filters['age'])) {
            $minDate = (new \DateTime())->modify('-' . (intval($filters['age']) + 1) . ' years');
            $maxDate = (new \DateTime())->modify('-' . intval($filters['age']) . ' years');

            $qb->andWhere('u.birthday BETWEEN :minDate AND :maxDate')
                ->setParameter('minDate', $minDate)
                ->setParameter('maxDate', $maxDate);
        }

        // Allowlist of fields safe to sort on
        $sortableFields = ['CIN', 'Email', 'Name', 'birthday', 'createdAt', 'lastLoginDate'];

        if ($sort) {
            if ($sort === 'age') {
                $qb->addSelect('TIMESTAMPDIFF(YEAR, u.birthday, CURRENT_DATE()) AS HIDDEN age');
                $qb->orderBy('age', $direction);
            } elseif (in_array($sort, $sortableFields, true)) {
                $qb->orderBy('u.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }


    public function getRegistrationTimeline(): array
    {
        return $this->createQueryBuilder('u')
            ->select("DATE_FORMAT(u.createdAt, '%Y-%m') as month, COUNT(u.id) as count")
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countUsersByRole(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.roles as roles, COUNT(u.id) as count')
            ->groupBy('u.roles')
            ->getQuery()
            ->getResult();
    }



    

    // Exemple de méthode personnalisée pour trouver un utilisateur par email
    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.Email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Ajoutez ici d'autres méthodes de requête personnalisées si besoin
}