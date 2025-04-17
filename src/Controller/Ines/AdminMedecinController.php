<?php

namespace App\Controller\Ines;

use App\Entity\Ines\Medecin;
use App\Form\Ines\MedecinType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/medecins', name: 'backoffice_medecin_')]
class AdminMedecinController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $medecins = $entityManager->getRepository(Medecin::class)->findAll();

        return $this->render('BackOffice/medecin.html.twig', [
            'medecins' => $medecins,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $medecin = new Medecin();
        $form = $this->createForm(MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medecin);
            $entityManager->flush();

            $this->addFlash('success', 'Médecin ajouté avec succès !');
            return $this->redirectToRoute('backoffice_medecin_list'); 
        }

        return $this->render('BackOffice/medecin_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{idMedecin}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Médecin modifié avec succès.');
            return $this->redirectToRoute('backoffice_medecin_list');
        }

        return $this->render('BackOffice/medecin_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{idMedecin}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $medecin->getIdMedecin(), $request->request->get('_token'))) {
            $entityManager->remove($medecin);
            $entityManager->flush();
            $this->addFlash('success', 'Médecin supprimé avec succès.');
        }

        return $this->redirectToRoute('backoffice_medecin_list');
    }
}
