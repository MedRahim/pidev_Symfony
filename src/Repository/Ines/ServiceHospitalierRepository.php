<?php

namespace App\Repository\Ines;

use App\Entity\Ines\ServiceHospitalier; // Assure-toi que le nom de ton entité est correct
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Servicehospitalier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Servicehospitalier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Servicehospitalier[]    findAll()
 * @method Servicehospitalier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceHospitalierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceHospitalier::class);
    }

    // Méthode personnalisée pour obtenir tous les services hospitaliers
    public function findAllServices(): array
    {
        return $this->findAll();  // Utilisation de la méthode par défaut findAll()
    }

    // Exemple d'une méthode personnalisée pour chercher un service par son nom
    public function findByNomService(string $nomService): ?ServiceHospitalier
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.nomService = :nomService')
            ->setParameter('nomService', $nomService)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
