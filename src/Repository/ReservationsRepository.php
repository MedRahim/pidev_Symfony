<?php

namespace App\Repository;

use App\Entity\Reservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use \DateTimeInterface;

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

    public function getTotalRevenue(): float
    {
        return (float) $this->createQueryBuilder('r')
            ->select('SUM(r.seatNumber * t.price)')
            ->join('r.trip', 't')
            ->where('r.paymentStatus = :paid')
            ->setParameter('paid', 'Paid')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getStatusDistribution(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.status, COUNT(r.id) as count')
            ->groupBy('r.status')
            ->getQuery()
            ->getResult();
    }

    public function getTotalRevenueForPeriod(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): float {
        $result = $this->createQueryBuilder('r')
            ->select('SUM(r.seatNumber * t.price) as total') // Utilisation cohérente avec getTotalRevenue()
            ->join('r.trip', 't')
            ->where('r.reservationTime BETWEEN :start AND :end')
            ->andWhere('r.paymentStatus = :paid')
            ->setParameters([
                'start' => $startDate,
                'end' => $endDate,
                'paid' => 'Paid'
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }
    public function getRecentActivities(int $maxResults = 10): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.reservationTime as date, IDENTITY(r.trip) as trip_id, r.seatNumber')
            ->orderBy('r.reservationTime', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getArrayResult();
    }
    public function getActiveReservationsStats(): array
    {
        return $this->createQueryBuilder('r')
            ->select([
                'COUNT(r.id) as total',
                'SUM(CASE WHEN r.status = :pending THEN 1 ELSE 0 END) as pending',
                'SUM(r.seatNumber * t.price) as revenue'
            ])
            ->join('r.trip', 't')
            ->where('r.paymentStatus = :paid')
            ->setParameters([
                'pending' => Reservations::STATUS_PENDING,
                'paid' => Reservations::PAYMENT_PAID
            ])
            ->getQuery()
            ->getSingleResult();
    }
}