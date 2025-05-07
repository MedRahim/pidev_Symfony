<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }
    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o.id)');
    
        foreach ($criteria as $field => $value) {
            $qb->andWhere("o.$field = :$field")
               ->setParameter($field, $value);
        }
    
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    public function countConfirmedSince(\DateTimeInterface $since): int
{
    return $this->createQueryBuilder('o')
        ->select('COUNT(o.id)')
        ->where('o.status = :status')
        ->andWhere('o.confirmedAt > :since') // Changed from o.date to o.createdAt
        ->setParameter('status', 'confirmed')
        ->setParameter('since', $since)
        ->getQuery()
        ->getSingleScalarResult();
}
    // Add custom query methods here if needed
    
}
