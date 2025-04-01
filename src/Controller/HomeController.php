<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        // Remove leading slash in template path
        return $this->render('FrontOffice/index.html.twig');
    }

    #[Route('/about', name: 'about')] // Unique name
    public function about(): Response
    {
        return $this->render('FrontOffice/about.html.twig');
    }
}