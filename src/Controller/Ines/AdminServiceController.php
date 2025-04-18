<?php

namespace App\Controller\Ines;

use App\Entity\Ines\ServiceHospitalier;
use App\Form\Ines\ServiceHospitalierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/services-hospitaliers', name: 'backoffice_service_')]
class AdminServiceController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $services = $entityManager->getRepository(ServiceHospitalier::class)->findAll();

        return $this->render('BackOffice/service.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $service = new ServiceHospitalier();
    $form = $this->createForm(ServiceHospitalierType::class, $service);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($service);
        $entityManager->flush();

        $this->addFlash('success', 'Service ajouté avec succès !');
        return $this->redirectToRoute('backoffice_service_list');
    }

    return $this->render('BackOffice/service_new.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/edit/{idService}', name: 'edit', methods: ['GET', 'POST'])]
public function edit(Request $request, ServiceHospitalier $service, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(ServiceHospitalierType::class, $service);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'Service modifié avec succès.');
        return $this->redirectToRoute('backoffice_service_list');
    }

    return $this->render('BackOffice/service_edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/delete/{idService}', name: 'delete', methods: ['POST'])]
public function delete(Request $request, ServiceHospitalier $service, EntityManagerInterface $entityManager): RedirectResponse
{
    if ($this->isCsrfTokenValid('delete' . $service->getIdService(), $request->request->get('_token'))) {
        $entityManager->remove($service);
        $entityManager->flush();
        $this->addFlash('success', 'Service supprimé avec succès.');
    }

    return $this->redirectToRoute('backoffice_service_list');
}

}
