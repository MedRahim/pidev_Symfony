<?php
// src/Controller/BackOffice/ReservationsController.php

namespace App\Controller\BackOffice;
use App\Form\ReservationsType;
use App\Entity\Reservations;
use App\Repository\ReservationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/reservations')]
class ReservationsController extends AbstractController
{
    #[Route('/', name: 'app_admin_reservations_index', methods: ['GET'])]
    public function index(Request $request, ReservationsRepository $reservationsRepository): Response
    {
        $status = $request->query->get('status');
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        
        $paginator = $reservationsRepository->getPaginatedReservations($page, $limit, $status);
        $totalReservations = $paginator->count();
        $totalPages = ceil($totalReservations / $limit);

        return $this->render('backoffice/reservations/index.html.twig', [
            'reservations' => $paginator,
            'current_page' => $page,
            'total_pages' => $totalPages,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_reservations_show', methods: ['GET'])]
    public function show(Reservations $reservation): Response
    {
        return $this->render('backoffice/reservations/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }


    #[Route('/{id}/confirm', name: 'app_admin_reservations_confirm', methods: ['POST'])]
    public function confirm(Request $request, Reservations $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('confirm'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('Confirmed');
            $entityManager->flush();

            $this->addFlash('success', 'Réservation confirmée avec succès');
        }

        return $this->redirectToRoute('app_admin_reservations_show', ['id' => $reservation->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_admin_reservations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservations $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationsType::class, $reservation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            $this->addFlash('success', 'La réservation #' . $reservation->getId() . ' a été mise à jour avec succès');
            return $this->redirectToRoute('app_admin_reservations_index');
        }
    
        return $this->render('backoffice/reservations/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

}
