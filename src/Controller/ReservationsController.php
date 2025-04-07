<?php

namespace App\Controller;

use App\Entity\Reservations;
use App\Form\ReservationsType;
use App\Repository\TripsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/reservations')]
class ReservationsController extends AbstractController
{
    #[Route('/new/{tripId}', name: 'app_reservations_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(int $tripId, Request $request, EntityManagerInterface $em, TripsRepository $tripsRepository): Response
    {
        $trip = $tripsRepository->find($tripId);
        if (!$trip) {
            $this->addFlash('error', 'Le trajet demandé n\'existe pas');
            return $this->redirectToRoute('home');
        }
        
        if (!$trip->getTransport()) {
            $this->addFlash('error', 'Ce trajet n\'a pas de transport associé');
            return $this->redirectToRoute('home');
        }
        
        $reservation = new Reservations();
        $reservation->setTrip($trip);
        $reservation->setReservationTime(new \DateTime());
        $reservation->setUser($this->getUser());
        $reservation->setTransportId($trip->getTransportId());
        
        $form = $this->createForm(ReservationsType::class, $reservation);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $price = $this->calculateReservationPrice($reservation, $trip);
            
            $request->getSession()->set('reservation_data', [
                'trip_id' => $trip->getId(),
                'seat_number' => $reservation->getSeatNumber(),
                'seat_type' => $reservation->getSeatType(),
                'price' => $price,
                'transport_id' => $trip->getTransportId()
            ]);
            
            return $this->redirectToRoute('app_reservations_choose');
        }
        
        return $this->render('FrontOffice/reservations/new.html.twig', [
            'form' => $form->createView(),
            'trip' => $trip,
        ]);
    }

    #[Route('/choose', name: 'app_reservations_choose', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function choose(Request $request, TripsRepository $tripsRepository): Response
    {
        $session = $request->getSession();
        $data = $session->get('reservation_data');
        
        if (!$data) {
            $this->addFlash('error', 'Aucune réservation en attente');
            return $this->redirectToRoute('home');
        }
        
        $trip = $tripsRepository->find($data['trip_id']);
        if (!$trip) {
            $this->addFlash('error', 'Le trajet demandé n\'existe pas');
            return $this->redirectToRoute('home');
        }
        
        return $this->render('FrontOffice/reservations/choose.html.twig', [
            'trip' => $trip,
            'seatNumber' => $data['seat_number'],
            'seatType' => $data['seat_type'],
            'price' => $data['price']
        ]);
    }

    #[Route('/payment', name: 'app_reservations_payment', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function payment(Request $request, EntityManagerInterface $em, TripsRepository $tripsRepository): Response
    {
        $session = $request->getSession();
        $data = $session->get('reservation_data');
        
        if (!$data) {
            $this->addFlash('error', 'Aucune réservation en attente');
            return $this->redirectToRoute('home');
        }
        
        $trip = $tripsRepository->find($data['trip_id']);
        if (!$trip) {
            $this->addFlash('error', 'Le trajet demandé n\'existe pas');
            return $this->redirectToRoute('home');
        }
        
        if ($request->isMethod('POST')) {
            $cardNumber = $request->request->get('cardNumber');
            $expiryDate = $request->request->get('expiryDate');
            $cvv = $request->request->get('cvv');
            
            if (empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
                $this->addFlash('error', 'Tous les champs de paiement sont requis');
                return $this->redirectToRoute('app_reservations_payment');
            }
            
            $reservation = new Reservations();
            $reservation->setTrip($trip);
            $reservation->setReservationTime(new \DateTime());
            $reservation->setUser($this->getUser());
            $reservation->setTransportId($data['transport_id']);
            $reservation->setSeatNumber($data['seat_number']);
            $reservation->setSeatType($data['seat_type']);
            $reservation->setStatus('Confirmed');
            $reservation->setPaymentStatus('Paid');
            
            try {
                $em->persist($reservation);
                $em->flush();
                $session->remove('reservation_data');
                return $this->redirectToRoute('app_reservations_payment_confirmation', ['id' => $reservation->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors du traitement du paiement : ' . $e->getMessage());
                return $this->redirectToRoute('app_reservations_payment');
            }
        }
        
        return $this->render('FrontOffice/reservations/pay.html.twig', [
            'price' => $data['price'],
            'seatNumber' => $data['seat_number'],
            'seatType' => $data['seat_type'],
        ]);
    }

    #[Route('/payment/confirmation/{id}', name: 'app_reservations_payment_confirmation', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function paymentConfirmation(int $id, EntityManagerInterface $em): Response
    {
        $reservation = $em->getRepository(Reservations::class)->find($id);
        
        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
            return $this->redirectToRoute('app_reservations_list');
        }
        
        return $this->render('FrontOffice/reservations/payment_confirmation.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/reserve', name: 'app_reservations_reserve', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reserveWithoutPay(Request $request, EntityManagerInterface $em, TripsRepository $tripsRepository): Response
    {
        $session = $request->getSession();
        $data = $session->get('reservation_data');
        
        if (!$data) {
            $this->addFlash('error', 'Aucune réservation en attente');
            return $this->redirectToRoute('home');
        }
        
        $trip = $tripsRepository->find($data['trip_id']);
        if (!$trip) {
            $this->addFlash('error', 'Le trajet demandé n\'existe pas');
            return $this->redirectToRoute('home');
        }
        
        $reservation = new Reservations();
        $reservation->setTrip($trip);
        $reservation->setReservationTime(new \DateTime());
        $reservation->setUser($this->getUser());
        $reservation->setTransportId($data['transport_id']);
        $reservation->setSeatNumber($data['seat_number']);
        $reservation->setSeatType($data['seat_type']);
        $reservation->setStatus('Pending_Payment');
        $reservation->setPaymentStatus('Pending');
        
        try {
            $em->persist($reservation);
            $em->flush();
            $session->remove('reservation_data');
            return $this->redirectToRoute('app_reservations_reserve_confirmation', ['id' => $reservation->getId()]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la réservation : ' . $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }

    #[Route('/reserve/confirmation/{id}', name: 'app_reservations_reserve_confirmation', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function reserveConfirmation(int $id, EntityManagerInterface $em): Response
    {
        $reservation = $em->getRepository(Reservations::class)->find($id);
        
        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
            return $this->redirectToRoute('app_reservations_list');
        }
        
        return $this->render('FrontOffice/reservations/reserve_without_pay.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/details/{id}', name: 'app_reservations_details', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function details(int $id, EntityManagerInterface $em): Response
    {
        $reservation = $em->getRepository(Reservations::class)->find($id);
        
        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
            return $this->redirectToRoute('app_reservations_list');
        }
        
        return $this->render('FrontOffice/reservations/details.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    // Méthode pour modifier une réservation (accessible à toutes via le bouton Modifier)
    #[Route('/edit/{id}', name: 'app_reservations_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $reservation = $em->getRepository(Reservations::class)->find($id);
        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
            return $this->redirectToRoute('app_reservations_list');
        }
        
        // Modification autorisée uniquement si le paiement est en attente
        if ($reservation->getPaymentStatus() !== 'Pending') {
            $this->addFlash('error', 'Modification impossible pour une réservation déjà payée');
            return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
        }
        
        $form = $this->createForm(ReservationsType::class, $reservation);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $price = $this->calculateReservationPrice($reservation, $reservation->getTrip());
            try {
                $em->flush();
                $this->addFlash('success', 'Réservation modifiée avec succès');
                return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }
        
        return $this->render('FrontOffice/reservations/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
            'price' => $price ?? null,
        ]);
    }
    
    // Méthode pour payer une réservation en attente
    #[Route('/pay/{id}', name: 'app_reservations_pay_pending', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function payPending(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $reservation = $em->getRepository(Reservations::class)->find($id);
        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
            return $this->redirectToRoute('app_reservations_list');
        }
        if ($reservation->getPaymentStatus() !== 'Pending') {
            $this->addFlash('error', 'La réservation n\'est pas en attente de paiement');
            return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
        }
        if ($request->isMethod('POST')) {
            $cardNumber = $request->request->get('cardNumber');
            $expiryDate = $request->request->get('expiryDate');
            $cvv = $request->request->get('cvv');
            if (empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
                $this->addFlash('error', 'Tous les champs de paiement sont requis');
                return $this->redirectToRoute('app_reservations_pay_pending', ['id' => $reservation->getId()]);
            }
            $reservation->setStatus('Confirmed');
            $reservation->setPaymentStatus('Paid');
            try {
                $em->flush();
                $this->addFlash('success', 'Paiement effectué avec succès');
                return $this->redirectToRoute('app_reservations_payment_confirmation', ['id' => $reservation->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors du paiement: ' . $e->getMessage());
            }
        }
        return $this->render('FrontOffice/reservations/pay_pending.html.twig', [
            'reservation' => $reservation,
        ]);
    }
    
    // Méthode pour annuler une réservation
    #[Route('/cancel/{id}', name: 'app_reservations_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(int $id, EntityManagerInterface $em): Response
    {
        $reservation = $em->getRepository(Reservations::class)->find($id);
        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
            return $this->redirectToRoute('app_reservations_list');
        }
        
        if ($reservation->getStatus() === 'Annulé') {
            $this->addFlash('info', 'La réservation est déjà annulée');
            return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
        }
        
        $reservation->setStatus('Annulé');
        $reservation->setPaymentStatus('Annulé'); // Vous pouvez adapter si besoin
        
        try {
            $em->flush();
            $this->addFlash('success', 'Réservation annulée avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
    }
    
    #[Route('/list', name: 'app_reservations_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(EntityManagerInterface $em): Response
    {
        $reservations = $em->getRepository(Reservations::class)->findBy(
            ['user' => $this->getUser()],
            ['reservationTime' => 'DESC']
        );
        
        return $this->render('FrontOffice/reservations/list.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    private function calculateReservationPrice(Reservations $reservation, $trip): float
    {
        $seatNumber = $reservation->getSeatNumber();
        $seatType = $reservation->getSeatType();
        $basePrice = (float) $trip->getPrice();
        
        return ($seatType === 'premuim')
            ? ($basePrice * $seatNumber) + (10 * $seatNumber)
            : $basePrice * $seatNumber;
    }
}
