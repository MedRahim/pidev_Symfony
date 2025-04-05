<?php

namespace App\Controller\Ines;

use App\Entity\Ines\Rendezvous;
use App\Form\Ines\RendezVousType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/rendezvous', name: 'rendezvous_')]

class RendezVousController extends AbstractController
{
    #[Route('/new/{idMedecin}', name: 'new', methods: ['GET', 'POST'])]

    public function new(int $idMedecin, Request $request, EntityManagerInterface $entityManager): Response
    {
        $rendezvous = new Rendezvous();
        $rendezvous->setIdMedecin($idMedecin); // Associer le médecin

        $form = $this->createForm(RendezVousType::class, $rendezvous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rendezvous);
            $entityManager->flush();

            return $this->redirectToRoute('rendezvous_rendezvous_success');
        }

        return $this->render('FrontOffice/rendezvous_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/success', name: 'rendezvous_success')]
    public function success(): Response
    {
        return $this->render('FrontOffice/rendezvous_success.html.twig');
    }
}
