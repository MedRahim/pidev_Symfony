<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
{
    #[Route('/factures', name: 'facture_index')]
    public function index(): Response
    {
        // Logic to list factures
        return $this->render('facture/index.html.twig');
    }

    #[Route('/facture/{id}', name: 'facture_show')]
    public function show(int $id): Response
    {
        // Logic to show a single facture
        return $this->render('facture/show.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/facture/{id}/recu', name: 'facture_recu')]
    public function generateRecu(int $id): Response
    {
        // Logic to generate a receipt for a facture
        return new Response('Receipt generated for facture: ' . $id);
    }
}
