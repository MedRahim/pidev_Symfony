<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends AbstractController
{
    #[Route('/backoffice', name: 'backoffice_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('BackOffice/base.html.twig');
    }


   





#[Route('/backoffice/medecins', name: 'medecins_page')]
public function medecins(): Response
{
    return $this->render('BackOffice/medecins.html.twig');
}

#[Route('/backoffice/services-hospitaliers', name: 'services_hospitaliers_page')]
public function servicesHospitaliers(): Response
{
    return $this->render('BackOffice/services-hospitaliers.html.twig');
}


    
}
