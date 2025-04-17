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


   






    
}
