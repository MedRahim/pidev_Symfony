<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    // Add custom query methods here if needed

    public function findByNameLike(string $name): array
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.name) LIKE LOWER(:name)')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }

    public function findByNameAndPriceRange(?string $name, ?float $minPrice, ?float $maxPrice): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($name) {
            $qb->andWhere('LOWER(p.name) LIKE LOWER(:name)')
               ->setParameter('name', '%' . $name . '%');
        }
        if ($minPrice !== null) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }
        if ($maxPrice !== null) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }

        return $qb->getQuery()->getResult();
    }

    public function findLowStock(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stock < p.stockLimit')
            ->getQuery()
            ->getResult();
    }
}
