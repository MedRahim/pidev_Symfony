<?php

namespace App\Controller\Ines;

use App\Entity\Ines\Medecin;
use App\Form\Ines\MedecinType;
use App\Form\Ines\MedecinBackType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    $form = $this->createForm(MedecinBackType::class, $medecin);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Assurez-vous que l'image est bien définie avant de persister
        $medecin->setImageFile($medecin->getImageFile());

        // Persister le médecin et l'image
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
        $form = $this->createForm(MedecinBackType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $medecin->setImageFile($medecin->getImageFile());

            $entityManager->flush();
            $this->addFlash('success', 'Médecin modifié avec succès.');
            return $this->redirectToRoute('backoffice_medecin_list');
        }

        return $this->render('BackOffice/medecin_edit.html.twig', [
            'form' => $form->createView(),
            'medecin' => $medecin,
        ]);
    }

    #[Route('/delete/{idMedecin}', name: 'delete', methods: ['POST'])]
    public function delete(Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/medecins/' . $medecin->getImageName();
        if ($medecin->getImageName() && file_exists($imagePath)) {
            unlink($imagePath);
        }

        $entityManager->remove($medecin);
        $entityManager->flush();

        $this->addFlash('success', 'Médecin supprimé avec succès.');

        return $this->redirectToRoute('backoffice_medecin_list');
    }
}
