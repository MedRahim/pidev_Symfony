<?php

namespace App\Controller\Ines;

use App\Entity\Ines\Rendezvous;
use App\Form\Ines\RendezVousType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\Ines\RendezvousRepository;
use App\Entity\Ines\Medecin; 

#[Route('/rendezvous', name: 'rendezvous_')]


class RendezVousController extends AbstractController
{
    #[Route('/new/{idMedecin}', name: 'new', methods: ['GET', 'POST'])]
public function new(int $idMedecin, Request $request, EntityManagerInterface $em): Response
{
    $medecin = $em->getRepository(Medecin::class)->find($idMedecin);
    if (!$medecin) {
        throw $this->createNotFoundException('Médecin non trouvé.');
    }

    $rendezvous = new Rendezvous();
    $rendezvous->setMedecin($medecin); // 👈 nouvelle relation

    $form = $this->createForm(RendezVousType::class, $rendezvous);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($rendezvous);
        $em->flush();

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
