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

    #[Route('/backoffice/products', name: 'products_page')]
    public function products(): Response
    {
        // Replace with your logic for displaying products
        return $this->render('BackOffice/products.html.twig');
    }

    #[Route('/backoffice/orders', name: 'orders_page')]
    public function orders(): Response
    {
        // Replace with your logic for displaying orders
        return $this->render('BackOffice/orders.html.twig');
    }
}
