<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('FrontOffice/about.html.twig');
    }

    #[Route('/about/team', name: 'about_team')]
    public function team(): Response
    {
        // Render a section about the team
        return $this->render('FrontOffice/about_team.html.twig');
    }

    #[Route('/about/history', name: 'about_history')]
    public function history(): Response
    {
        // Render a section about the company's history
        return $this->render('FrontOffice/about_history.html.twig');
    }

    #[Route('/about/mission', name: 'about_mission')]
    public function mission(): Response
    {
        // Render a section about the company's mission
        return $this->render('FrontOffice/about_mission.html.twig');
    }
}
