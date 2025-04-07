<?php

namespace App\Repository;

use App\Entity\Trips;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TripsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trips::class);
    }

    public function findByCriteria(array $criteria, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.capacity > 0');

        if (!empty($criteria['departure'])) {
            $qb->andWhere('t.departure LIKE :departure')
               ->setParameter('departure', '%'.$criteria['departure'].'%');
        }

        if (!empty($criteria['destination'])) {
            $qb->andWhere('t.destination LIKE :destination')
               ->setParameter('destination', '%'.$criteria['destination'].'%');
        }

        if (!empty($criteria['minPrice'])) {
            $qb->andWhere('t.price >= :minPrice')
               ->setParameter('minPrice', $criteria['minPrice']);
        }

        if (!empty($criteria['maxPrice'])) {
            $qb->andWhere('t.price <= :maxPrice')
               ->setParameter('maxPrice', $criteria['maxPrice']);
        }

        if (!empty($criteria['departureDate'])) {
            $qb->andWhere('DATE(t.departureTime) = :departureDate')
               ->setParameter('departureDate', $criteria['departureDate']);
        }

        if (!empty($criteria['transport'])) {
            $qb->andWhere('t.transportName = :transport')
               ->setParameter('transport', $criteria['transport']);
        }

        switch ($criteria['sort'] ?? 'departureTime') {
            case 'price':
                $qb->orderBy('t.price', 'ASC');
                break;
            case 'distance':
                $qb->orderBy('t.distance', 'DESC');
                break;
            default:
                $qb->orderBy('t.departureTime', 'ASC');
        }

        return $qb->setMaxResults($limit)
                 ->setFirstResult($offset)
                 ->getQuery()
                 ->getResult();
    }

    public function countByCriteria(array $criteria): int
    {
        $qb = $this->createQueryBuilder('t')
                   ->select('COUNT(t.id)')
                   ->where('t.capacity > 0');

        // Les mêmes conditions que findByCriteria...

        return $qb->getQuery()->getSingleScalarResult();
    }
}