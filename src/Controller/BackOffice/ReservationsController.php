<?php
// src/Controller/BackOffice/ReservationsController.php

namespace App\Controller\BackOffice;
use App\Entity\Users;                      // ← Import corrigé : votre entité s'appelle Users

use App\Entity\Reservations;
use App\Form\ReservationsType;
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
    public function index(Request $request, ReservationsRepository $repo): Response
    {
        $status = $request->query->get('status');
        $page   = $request->query->getInt('page', 1);
        $limit  = 10;

        $paginator          = $repo->getPaginatedReservations($page, $limit, $status);
        $totalReservations  = $paginator->count();
        $totalPages         = (int) ceil($totalReservations / $limit);

        return $this->render('backoffice/reservations/index.html.twig', [
            'reservations'  => $paginator,
            'current_page'  => $page,
            'total_pages'   => $totalPages,
        ]);
    }


    
        #[Route('/new', name: 'app_admin_reservations_new', methods: ['GET', 'POST'])]
        public function new(Request $request, EntityManagerInterface $em): Response
        {
            $reservation = new Reservations();
            $form        = $this->createForm(ReservationsType::class, $reservation);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                // 1. Date de réservation
                $reservation->setReservationTime(new \DateTime());
    
                // 2. Transport via le trajet sélectionné
                $trip = $reservation->getTrip();
                $reservation->setTransportId($trip->getTransportId());
    
                // 3. Forcer l'utilisateur à celui dont l'ID = 9
                $userRef = $em->getReference(Users::class, 9);
                $reservation->setUser($userRef);
    
                // 4. Persister et flush
                $em->persist($reservation);
                $em->flush();
    
                $this->addFlash('success', 'La réservation a été créée avec succès (user=9).');
                return $this->redirectToRoute('app_admin_reservations_index');
            }
    
            return $this->render('backoffice/reservations/new.html.twig', [
                'reservation' => $reservation,
                'form'        => $form->createView(),
            ]);
        }
    
    
    #[Route('/{id}', name: 'app_admin_reservations_show', methods: ['GET'])]
    public function show(int $id, ReservationsRepository $repo): Response
    {
        $reservation = $repo->find($id);
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation introuvable.');
        }

        return $this->render('backoffice/reservations/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_reservations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservations $reservation, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ReservationsType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'La réservation #' . $reservation->getId() . ' a été mise à jour.');
            return $this->redirectToRoute('app_admin_reservations_index');
        }

        return $this->render('backoffice/reservations/edit.html.twig', [
            'reservation' => $reservation,
            'form'        => $form->createView(),
        ]);
    }

    #[Route('/{id}/confirm', name: 'app_admin_reservations_confirm', methods: ['POST'])]
    public function confirm(Request $request, Reservations $reservation, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('confirm'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('Confirmed');
            $em->flush();
            $this->addFlash('success', 'Réservation confirmée.');
        }

        return $this->redirectToRoute('app_admin_reservations_show', ['id' => $reservation->getId()]);
    }
    #[Route('/{id}/cancel', name: 'app_admin_reservations_cancel', methods: ['POST'])]
    public function cancel(Request $request, Reservations $reservation, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide');
        }
    
        $currentStatus = $reservation->getStatus();
    
        if ($currentStatus === 'Cancelled') {
            $this->addFlash('info', 'Cette réservation est déjà annulée.');
        } elseif ($currentStatus === 'Confirmed') {
            $this->addFlash('warning', 'Impossible d’annuler une réservation déjà confirmée.');
        } else {
            $reservation->setStatus('Cancelled');
            $em->flush();
            $this->addFlash('success', 'La réservation a bien été annulée.');
        }
    
        return $this->redirectToRoute('app_admin_reservations_show', ['id' => $reservation->getId()]);
    }
    
}
