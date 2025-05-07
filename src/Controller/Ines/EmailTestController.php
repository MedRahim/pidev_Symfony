<?php

namespace App\Controller\Ines;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailTestController extends AbstractController
{
    #[Route('/test-email', name: 'test_email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('ines.rahrah@esprit.tn') // Assure-toi que cette adresse est autorisée dans Gmail
            ->to('ines.rahrah@esprit.tn') // Remplace avec une vraie adresse email
            ->subject('🕒 Rappel de votre rendez-vous')
            ->text("Bonjour,\nCeci est un rappel automatique pour votre rendez-vous.")
            ->html('<p>Bonjour,</p><p>Ceci est un <strong>rappel</strong> de votre rendez-vous.</p>');

        $mailer->send($email);

        return new Response('✅ Email de test envoyé avec succès.');
    }
}
