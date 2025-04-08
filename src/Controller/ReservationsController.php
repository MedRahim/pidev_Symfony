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
            // Validation des champs de paiement
            $cardNumber = $request->request->get('cardNumber');
            $expiryDate = $request->request->get('expiryDate');
            $cvv = $request->request->get('cvv');
            
            // Contrôle de saisie
            $errors = [];
            
            if (empty($cardNumber)) {
                $errors[] = 'Le numéro de carte est requis';
            } elseif (!preg_match('/^\d{16}$/', $cardNumber)) {
                $errors[] = 'Le numéro de carte doit contenir 16 chiffres';
            }
            
            if (empty($expiryDate)) {
                $errors[] = 'La date d\'expiration est requise';
            } elseif (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $expiryDate)) {
                $errors[] = 'Format de date invalide (MM/AA)';
            }
            
            if (empty($cvv)) {
                $errors[] = 'Le code CVV est requis';
            } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
                $errors[] = 'Le code CVV doit contenir 3 ou 4 chiffres';
            }
            
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->redirectToRoute('app_reservations_payment');
            }
            
            // Si validation OK, créer la réservation
            $reservation = new Reservations();
            $reservation->setTrip($trip);
            $reservation->setReservationTime(new \DateTime());
            $reservation->setUser($this->getUser());
            $reservation->setTransportId($data['transport_id']);
            $reservation->setSeatNumber($data['seat_number']);
            $reservation->setSeatType($data['seat_type']);
            $reservation->setStatus('confirmed');
            $reservation->setPaymentStatus('confirmed');
            
            try {
                $em->persist($reservation);
                $em->flush();
                $session->remove('reservation_data');
                
                // Redirection vers la confirmation
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
        $reservation->setStatus('pending');
        $reservation->setPaymentStatus('pending');
        
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

    #[Route('/edit/{id}', name: 'app_reservations_edit', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
public function edit(Request $request, Reservations $reservation, EntityManagerInterface $em, TripsRepository $tripsRepository): Response
{
    // Vérification que la réservation appartient à l'utilisateur
    if ($reservation->getUser() !== $this->getUser()) {
        $this->addFlash('error', 'Accès non autorisé');
        return $this->redirectToRoute('app_reservations_list');
    }

    // Empêcher la modification si la réservation est annulée
    if ($reservation->getStatus() === 'cancelled') {
        $this->addFlash('warning', 'Les réservations annulées ne peuvent pas être modifiées');
        return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
    }

    // Sauvegarder les valeurs originales pour comparaison
    $originalData = [
        'seatNumber' => $reservation->getSeatNumber(),
        'seatType' => $reservation->getSeatType(),
        'price' => $this->calculateReservationPrice($reservation, $reservation->getTrip())
    ];

    $form = $this->createForm(ReservationsType::class, $reservation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Calculer le nouveau prix
        $newPrice = $this->calculateReservationPrice($reservation, $reservation->getTrip());
        
        // Vérifier si des modifications ont été apportées
        if ($reservation->getSeatNumber() == $originalData['seatNumber'] && 
            $reservation->getSeatType() == $originalData['seatType']) {
            $this->addFlash('info', 'Aucune modification apportée à la réservation');
            return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
        }

        // Stocker les données en session pour le paiement
        $request->getSession()->set('reservation_data', [
            'trip_id' => $reservation->getTrip()->getId(),
            'seat_number' => $reservation->getSeatNumber(),
            'seat_type' => $reservation->getSeatType(),
            'price' => $newPrice,
            'transport_id' => $reservation->getTransportId(),
            'is_edit' => true,
            'reservation_id' => $reservation->getId(),
            'original_price' => $originalData['price'],
            'price_difference' => $newPrice - $originalData['price']
        ]);

        // Rediriger vers la page de confirmation de modification
        return $this->redirectToRoute('app_reservations_edit_confirmation', ['id' => $reservation->getId()]);
    }

    return $this->render('FrontOffice/reservations/edit.html.twig', [
        'form' => $form->createView(),
        'reservation' => $reservation,
        'originalPrice' => $this->calculateReservationPrice(
            (new Reservations())
                ->setSeatNumber($originalData['seatNumber'])
                ->setSeatType($originalData['seatType']),
            $reservation->getTrip()
        ),
        'newPrice' => $this->calculateReservationPrice($reservation, $reservation->getTrip())
    ]);
}

#[Route('/edit/confirmation/{id}', name: 'app_reservations_edit_confirmation', methods: ['GET'])]
public function editConfirmation(Request $request, int $id, EntityManagerInterface $em): Response
{
    $session = $request->getSession();
    $data = $session->get('reservation_data');
    
    if (!$data || !isset($data['is_edit']) || $data['reservation_id'] != $id) {
        $this->addFlash('error', 'Session invalide pour la modification');
        return $this->redirectToRoute('app_reservations_list');
    }
    
    $reservation = $em->getRepository(Reservations::class)->find($id);
    if (!$reservation || $reservation->getUser() !== $this->getUser()) {
        $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
        return $this->redirectToRoute('app_reservations_list');
    }
    
    return $this->render('FrontOffice/reservations/edit_confirmation.html.twig', [
        'reservation' => $reservation,
        'originalPrice' => $data['original_price'],
        'newPrice' => $data['price'],
        'priceDifference' => $data['price_difference']
    ]);
}

#[Route('/edit/process/{id}', name: 'app_reservations_edit_process', methods: ['POST'])]
public function processEdit(Request $request, int $id, EntityManagerInterface $em): Response
{
    $session = $request->getSession();
    $data = $session->get('reservation_data');
    
    if (!$data || !isset($data['is_edit']) || $data['reservation_id'] != $id) {
        $this->addFlash('error', 'Session invalide pour la modification');
        return $this->redirectToRoute('app_reservations_list');
    }
    
    $reservation = $em->getRepository(Reservations::class)->find($id);
    if (!$reservation || $reservation->getUser() !== $this->getUser()) {
        $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
        return $this->redirectToRoute('app_reservations_list');
    }
    
    // Si différence de prix à payer, rediriger vers le paiement
    if ($data['price_difference'] > 0) {
        return $this->redirectToRoute('app_reservations_payment');
    }
    
    // Si remboursement ou pas de changement de prix
    try {
        $reservation->setSeatNumber($data['seat_number']);
        $reservation->setSeatType($data['seat_type']);
        
        // Si la réservation était déjà confirmée, on la laisse confirmée
        if ($reservation->getStatus() === 'confirmed') {
            $reservation->setStatus('confirmed');
        } else {
            $reservation->setStatus('pending');
        }
        
        $em->flush();
        $session->remove('reservation_data');
        
        $this->addFlash('success', 'Réservation modifiée avec succès');
        return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
    } catch (\Exception $e) {
        $this->addFlash('error', 'Erreur lors de la modification : '.$e->getMessage());
        return $this->redirectToRoute('app_reservations_edit', ['id' => $reservation->getId()]);
    }
}

    #[Route('/pay/{id}', name: 'app_reservations_pay_pending', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function payPending(Request $request, Reservations $reservation, EntityManagerInterface $em): Response
    {
        if ($reservation->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Accès non autorisé');
            return $this->redirectToRoute('app_reservations_list');
        }

        if ($reservation->getPaymentStatus() !== 'pending') {
            $this->addFlash('warning', 'Cette réservation a déjà été traitée');
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

            $reservation->setPaymentStatus('confirmed');
            $reservation->setStatus('confirmed');

            try {
                $em->flush();
                $this->addFlash('success', 'Paiement effectué avec succès');
                return $this->redirectToRoute('app_reservations_payment_confirmation', ['id' => $reservation->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors du traitement du paiement : '.$e->getMessage());
            }
        }

        return $this->render('FrontOffice/reservations/pay_pending.html.twig', [
            'reservation' => $reservation,
            'price' => $this->calculateReservationPrice($reservation, $reservation->getTrip())
        ]);
    }

    #[Route('/cancel/{id}', name: 'app_reservations_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(int $id, EntityManagerInterface $em): Response
    {
        $reservation = $em->getRepository(Reservations::class)->find($id);
        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
            return $this->redirectToRoute('app_reservations_list');
        }
        
        if ($reservation->getStatus() === 'cancelled') {
            $this->addFlash('info', 'La réservation est déjà annulée');
            return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
        }
        
        $reservation->setStatus('cancelled');
        $reservation->setPaymentStatus('cancelled');
        
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
