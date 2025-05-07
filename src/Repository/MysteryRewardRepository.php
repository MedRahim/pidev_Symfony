<?php
// src/Repository/MysteryRewardRepository.php

namespace App\Repository;

use App\Entity\MysteryReward;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MysteryRewardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MysteryReward::class);
    }

    // Ajoutez ici vos méthodes personnalisées si besoin
}
