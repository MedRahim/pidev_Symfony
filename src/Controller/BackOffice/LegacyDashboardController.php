<?php

namespace App\Controller\BackOffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacyDashboardController extends AbstractController
{
    #[Route('/admin/old', name: 'app_backoffice_dashboard')]
    public function index(): Response
    {
        return $this->render('backoffice/dashboard/index.html.twig');
    }
}