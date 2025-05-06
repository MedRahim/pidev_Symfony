<?php

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPost>
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }
    // Update this method in your BlogPostRepository class
    public function findBySearchTermAndCategory(?string $searchTerm, ?string $category): array
    {
    $queryBuilder = $this->createQueryBuilder('b');
    
    // If we have a search term, search only in the title
    if ($searchTerm) {
        $queryBuilder
            ->andWhere('b.title LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');
    }
    
    // If we have a category filter, add it to the query
    if ($category) {
        $queryBuilder
            ->andWhere('b.category = :category')
            ->setParameter('category', $category);
    }
    
    // Order by most recent first
    $queryBuilder->orderBy('b.postDate', 'DESC');
    
    return $queryBuilder->getQuery()->getResult();
}

    // Add custom query methods if needed
}
