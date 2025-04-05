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
        return $this->render('FrontOffice/index.html.twig');
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('FrontOffice/about.html.twig');
    }

    #[Route('/blog', name: 'blog')]
    public function blog(): Response
    {
        return $this->render('FrontOffice/blog.html.twig');
    }

    #[Route('/blog-details', name: 'blog_details')]
    public function blogDetails(): Response
    {
        return $this->render('FrontOffice/blog-details.html.twig');
    }

    
    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('FrontOffice/contact.html.twig');
    }

    #[Route('/elements', name: 'elements')]
    public function elements(): Response
    {
        return $this->render('FrontOffice/elements.html.twig');
    }

    #[Route('/listing', name: 'listing')]
    public function listing(): Response
    {
        return $this->render('FrontOffice/listing.html.twig');
    }

    #[Route('/listing-details', name: 'listing_details')]
    public function listingDetails(): Response
    {
        return $this->render('FrontOffice/listing_details.html.twig');
    }


    #[Route('/search', name: 'search')]
    public function search(): Response
    {
        // Implement your search logic here or render a search page.
        return $this->render('FrontOffice/search.html.twig');
    }

    #[Route('/register', name: 'register')]
    public function register(): Response
    {
        return $this->render('security/register.html.twig');
    }
    #[Route('/location/{city}', name: 'location')]
public function location(string $city): Response
{
    // Logique pour afficher les détails d'une ville
    return $this->render('FrontOffice/location.html.twig', [
        'city' => $city
    ]);
}

#[Route('/how-it-works/{step}', name: 'how_it_works')]
public function howItWorks(int $step): Response
{
    // Logique pour les étapes de fonctionnement
    return $this->render('FrontOffice/how_it_works.html.twig', [
        'step' => $step
    ]);
}

#[Route('/subscribe', name: 'subscribe')]
public function subscribe(): Response
{
    // Logique d'abonnement
    return $this->render('FrontOffice/subscribe.html.twig');
}

#[Route('/download-app/{platform}', name: 'download_app')]
public function downloadApp(string $platform): Response
{
    // Logique de téléchargement d'app
    return $this->render('FrontOffice/download_app.html.twig', [
        'platform' => $platform
    ]);
}

#[Route('/social/{platform}', name: 'social')]
public function social(string $platform): Response
{
    // Logique de redirection sociale
    return $this->render('FrontOffice/social.html.twig', [
        'platform' => $platform
    ]);
}

#[Route('/back-to-top', name: 'back_to_top')]
public function backToTop(): Response
{
    // Logique pour remonter en haut de page
    return $this->redirectToRoute('home');
}
}
