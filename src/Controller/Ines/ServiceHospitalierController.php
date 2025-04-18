<?php

namespace App\Controller\Ines;

use App\Repository\Ines\ServiceHospitalierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceHospitalierController extends AbstractController
{
    #[Route('/hopital', name: 'hopital')]
public function index(ServiceHospitalierRepository $repository): Response
{
    $services = $repository->findAll();

    return $this->render('FrontOffice/hopital.html.twig', [
        'services' => $services,
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

}
