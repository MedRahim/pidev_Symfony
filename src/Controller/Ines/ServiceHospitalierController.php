<?php

namespace App\Controller\Ines;

use App\Repository\Ines\ServiceHospitalierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class ServiceHospitalierController extends AbstractController
{
    
    #[Route('/hopital', name: 'hopital')]
public function index(ServiceHospitalierRepository $repository, EntityManagerInterface $em): Response
{
    $services = $repository->findAll();

    foreach ($services as $service) {
        $qb = $em->createQueryBuilder();

        $count = $qb->select('count(r.idRendezVous)')
            ->from(\App\Entity\Ines\Rendezvous::class, 'r')
            ->join('r.medecin', 'm')
            ->join('m.service', 's')
            ->where('s.idService = :idService')
            ->setParameter('idService', $service->getIdService())
            ->getQuery()
            ->getSingleScalarResult();

        if ($count < 2){
            $em->remove($service);
        }
    }

    $em->flush();

    return $this->render('FrontOffice/hopital.html.twig', [
        'services' => $repository->findAll(),
    ]);
}


#[Route('/medecins/{id}', name: 'medecins_par_service')]
public function afficherMedecins(ServiceHospitalierRepository $serviceRepo, int $id): Response
{
    $service = $serviceRepo->find($id);

    if (!$service) {
        throw $this->createNotFoundException("Service non trouvé.");
    }

    return $this->render('FrontOffice/medecins.html.twig', [
        'service' => $service,
        'medecins' => $service->getMedecins(),
    ]);
}



#[Route('/reserver-lit/{id}', name: 'reserver_lit')]
public function reserverLit(
    int $id,
    ServiceHospitalierRepository $serviceRepo,
    EntityManagerInterface $em
): RedirectResponse {
    $service = $serviceRepo->find($id);

    if (!$service) {
        $this->addFlash('danger', "Service non trouvé.");
        return $this->redirectToRoute('hopital');
    }

    if ($service->getNombreLitsDisponibles() > 0) {
        $service->setNombreLitsDisponibles($service->getNombreLitsDisponibles() - 1);
        $em->flush();

        $this->addFlash('success', "✅ Votre lit a été réservé avec succès dans le service {$service->getNomService()} !");
    } else {
        $this->addFlash('danger', "❌ Aucun lit disponible dans le service {$service->getNomService()}.");
    }

    return $this->redirectToRoute('hopital');
}



#[Route('/recherche-service', name: 'recherche_service')]
public function rechercherService(Request $request, ServiceHospitalierRepository $repository): Response
{
    $nom = $request->query->get('nom', '');

    $services = $repository->createQueryBuilder('s')
        ->where('LOWER(s.nomService) LIKE :nom')
        ->setParameter('nom', '%' . strtolower($nom) . '%')
        ->getQuery()
        ->getResult();

        return $this->render('FrontOffice/_services.html.twig', [
            'services' => $services,
        ]);
}















}
