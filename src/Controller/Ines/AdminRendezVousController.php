<?php

namespace App\Controller\Ines;

use App\Entity\Ines\Rendezvous;
use App\Entity\Ines\Medecin;
use App\Form\Ines\RendezVousType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/rendezvous', name: 'backoffice_rendezvous_')]
class AdminRendezVousController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $rendezvous = $entityManager->getRepository(Rendezvous::class)->findAll();

        return $this->render('BackOffice/rendezvous.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rendezvous $rendezvous, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezvous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Rendez-Vous modifié avec succès !');
            return $this->redirectToRoute('backoffice_rendezvous_list');
        }

        return $this->render('BackOffice/rendezvous_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Rendezvous $rendezvous, EntityManagerInterface $entityManager): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $rendezvous->getId(), $request->request->get('_token'))) {
            $entityManager->remove($rendezvous);
            $entityManager->flush();
            $this->addFlash('success', 'Rendez-Vous supprimé avec succès !');
        }

        return $this->redirectToRoute('backoffice_rendezvous_list');
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rendezvous = new Rendezvous();
       
        // Choisir un médecin par défaut ou laisser l'utilisateur en choisir un
        $medecin = $entityManager->getRepository(Medecin::class)->findOneBy([]);
   
        if (!$medecin) {
            throw $this->createNotFoundException('Aucun médecin disponible');
        }
   
        // Utiliser getIdMedecin() pour récupérer l'ID du médecin
        $rendezvous->setIdMedecin($medecin->getIdMedecin());
   
        $form = $this->createForm(RendezVousType::class, $rendezvous);
        $form->handleRequest($request);
   
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder le rendez-vous dans la base de données
            $entityManager->persist($rendezvous);
            $entityManager->flush();
            $this->addFlash('success', 'Rendez-Vous ajouté avec succès !');
   
            return $this->redirectToRoute('backoffice_rendezvous_list');
        }
   
        return $this->render('BackOffice/rendezvous_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
   

   
}


