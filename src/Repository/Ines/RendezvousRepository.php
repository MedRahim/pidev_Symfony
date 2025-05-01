<?php

namespace App\Repository\Ines;

use App\Entity\Ines\Rendezvous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RendezvousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rendezvous::class);
    }

    public function getRendezvousQueryBuilder()
    {
        return $this->createQueryBuilder('r');
    }

    /**
     * Vérifie si un rendez-vous avec la même date et heure existe déjà
     */
    public function findOneByDateAndTime(\DateTimeInterface $dateRendezVous, \DateTimeInterface $timeRendezVous): ?Rendezvous
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.dateRendezVous = :date')
            ->andWhere('r.timeRendezVous = :time')
            ->setParameter('date', $dateRendezVous)
            ->setParameter('time', $timeRendezVous)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
