<?php

namespace App\Repository;

use App\Entity\Trips;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class TripsRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 9;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trips::class);
    }

    public function search(
        ?string $query,
        ?string $departure,
        ?string $destination,
        ?float $maxPrice,
        ?string $transportType,
        ?string $departureDate,
        ?string $sort,
        int $page
    ): array {
        $qb = $this->createQueryBuilder('t')
            ->where('t.capacity > 0');

        // Filtre de recherche globale
        if ($query) {
            $qb->andWhere('t.departure LIKE :query OR t.destination LIKE :query')
               ->setParameter('query', '%'.$query.'%');
        }

        // Filtres individuels
        $this->applyFilters($qb, [
            'departure' => $departure,
            'destination' => $destination,
            'maxPrice' => $maxPrice,
            'transport_type' => $transportType,
            'departure_date' => $departureDate
        ]);

        // Tri
        $this->applySorting($qb, $sort);

        // Pagination
        return $qb->setFirstResult(($page - 1) * self::PER_PAGE)
            ->setMaxResults(self::PER_PAGE)
            ->getQuery()
            ->getResult();
    }

    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        if (!empty($filters['departure'])) {
            $qb->andWhere('t.departure LIKE :departure')
               ->setParameter('departure', '%'.$filters['departure'].'%');
        }

        if (!empty($filters['destination'])) {
            $qb->andWhere('t.destination LIKE :destination')
               ->setParameter('destination', '%'.$filters['destination'].'%');
        }

        if (!empty($filters['maxPrice'])) {
            $qb->andWhere('t.price <= :maxPrice')
               ->setParameter('maxPrice', $filters['maxPrice']);
        }

        if (!empty($filters['transport_type'])) {
            $qb->andWhere('t.transportName = :transportType')
               ->setParameter('transportType', $filters['transport_type']);
        }

        if (!empty($filters['departure_date'])) {
            $date = \DateTime::createFromFormat('d/m/Y', $filters['departure_date']);
            if ($date) {
                $start = $date->format('Y-m-d 00:00:00');
                $end = $date->format('Y-m-d 23:59:59');
                $qb->andWhere('t.departureTime BETWEEN :start AND :end')
                   ->setParameter('start', $start)
                   ->setParameter('end', $end);
            }
        }
    }

    private function applySorting(QueryBuilder $qb, ?string $sort): void
    {
        switch ($sort) {
            case 'price_asc':
                $qb->orderBy('t.price', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('t.price', 'DESC');
                break;
            case 'date_asc':
                $qb->orderBy('t.departureTime', 'ASC');
                break;
            case 'date_desc':
                $qb->orderBy('t.departureTime', 'DESC');
                break;
            default:
                $qb->orderBy('t.departureTime', 'ASC');
        }
    }

    public function countByFilters(array $filters): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.capacity > 0');

        $this->applyFilters($qb, $filters);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}