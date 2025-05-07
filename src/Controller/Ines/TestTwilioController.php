<?php

namespace App\Controller\Ines;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twilio\Rest\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TestTwilioController extends AbstractController
{
    #[Route('/test-sms', name: 'test_sms')]
    public function testSms(ParameterBagInterface $params): Response
    {
        $sid = $params->get('TWILIO_SID');
        $token = $params->get('TWILIO_TOKEN');
        $from = $params->get('TWILIO_FROM');

        $client = new Client($sid, $token);

        try {
            $client->messages->create(
                '+21650136592',
                [
                    'from' => $from,
                    'body' => 'Test SMS depuis Symfony Controller'
                ]
            );
            return new Response('✅ SMS envoyé avec succès');
        } catch (\Exception $e) {
            return new Response('❌ Erreur Twilio : ' . $e->getMessage());
        }
    }
}
