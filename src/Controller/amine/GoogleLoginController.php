<?php

// src/Controller/GoogleLoginController.php

namespace App\Controller\amine;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class GoogleLoginController extends AbstractController
{
//    #[Route('/connect/google', name: 'connect_google')]
//    public function connect(): Response
//    {
//        return $this->redirectToRoute('home');
//    }
//
//    #[Route('/connect/google/check', name: 'connect_google_check')]
//    public function connectCheck(): Response
//    {
//        // The authenticator will handle this route
//        return new Response('', Response::HTTP_OK);
//    }
}