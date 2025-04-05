<?php

namespace App\Repository\Ines;

use App\Entity\Rendezvous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RendezvousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rendezvous::class);
    }

    // Add custom methods as needed
}