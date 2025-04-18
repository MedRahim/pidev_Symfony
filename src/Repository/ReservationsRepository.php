<?php

namespace App\Repository;

use App\Entity\Reservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class ReservationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservations::class);
    }

    public function getPaginatedReservations(int $page, int $limit, ?string $status = null): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')->addSelect('u')
            ->leftJoin('r.trip', 't')->addSelect('t')
            ->orderBy('r.reservationTime', 'DESC');

        if ($status) {
            $queryBuilder->andWhere('r.status = :status')
                ->setParameter('status', $status);
        }

        $query = $queryBuilder->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }


    public function save(Reservations $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reservations $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}