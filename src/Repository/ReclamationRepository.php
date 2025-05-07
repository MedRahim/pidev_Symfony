<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

//    /**
//     * @return Reclamation[] Returns an array of Reclamation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Reclamation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getMonthlyStats(?int $year = null, ?int $month = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r) as count, SUBSTRING(r.datee, 1, 7) as month')
            ->groupBy('month')
            ->orderBy('month', 'ASC');

        if ($year !== null) {
            $qb->andWhere('SUBSTRING(r.datee, 1, 4) = :year')
               ->setParameter('year', $year);
        }

        if ($month !== null) {
            $qb->andWhere('SUBSTRING(r.datee, 6, 2) = :month')
               ->setParameter('month', str_pad($month, 2, '0', STR_PAD_LEFT));
        }

        $results = $qb->getQuery()->getResult();

        $labels = [];
        $data = [];

        foreach ($results as $result) {
            $labels[] = $result['month'];
            $data[] = $result['count'];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    public function getTypeStats(?int $year = null, ?int $month = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r) as count, r.type')
            ->groupBy('r.type');

        if ($year !== null) {
            $qb->andWhere('SUBSTRING(r.datee, 1, 4) = :year')
               ->setParameter('year', $year);
        }

        if ($month !== null) {
            $qb->andWhere('SUBSTRING(r.datee, 6, 2) = :month')
               ->setParameter('month', str_pad($month, 2, '0', STR_PAD_LEFT));
        }

        $results = $qb->getQuery()->getResult();

        $labels = [];
        $data = [];
        $colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69',
            '#858796', '#5a5c69', '#e74a3b', '#f6c23e', '#36b9cc', '#1cc88a'
        ];

        foreach ($results as $index => $result) {
            $labels[] = $result['type'];
            $data[] = $result['count'];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels))
        ];
    }

    public function findPaginated(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        
        return $this->createQueryBuilder('r')
            ->orderBy('r.datee', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
