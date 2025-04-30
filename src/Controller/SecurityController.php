<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // Google routes with explicit prefix
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connect(ClientRegistry $clientRegistry, Request $request): RedirectResponse
    {
        $request->getSession()->invalidate(); // Clear existing session
        return $clientRegistry->getClient('google')
            ->redirect(
                ['email','profile','openid'],
                ['prompt' => 'select_account'] // Force account selection
            );
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function check(): void
    {
        throw new \Exception('Should not be reached directly.');
    }

    // Regular login/logout without /connect prefix
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This method can be blank - intercepted by firewall.');
    }
}
