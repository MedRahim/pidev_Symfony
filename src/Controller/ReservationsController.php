<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Entity\Reservations;
use App\Form\FrontReservationType;
use App\Repository\TripsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Users;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/reservations')]
class ReservationsController extends AbstractController
{
    private const FIXED_USER_ID = 7;

    private function getFixedUser(EntityManagerInterface $em): Users
    {
        //Get the fixed user without throwing an exception
        $user = $em->getRepository(Users::class)->find(self::FIXED_USER_ID);
        if (!$user) {
            throw new \Exception('Fixed user not found.  Check that user ID 7 exists.');
        }
        return $user;
    }

    #[Route('/list', name: 'app_reservations_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): Response
    {
        $fixedUser = $this->getFixedUser($em);
        $reservations = $em->getRepository(Reservations::class)
            ->findBy(['user' => $fixedUser], ['reservationTime' => 'DESC']);

        return $this->render('FrontOffice/reservations/list.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new/{tripId}', name: 'app_reservations_new', methods: ['GET', 'POST'])]
    public function new(
        int $tripId,
        Request $request,
        EntityManagerInterface $em,
        TripsRepository $tripsRepository
    ): Response {
        $trip = $tripsRepository->find($tripId);
        if (!$trip) {
            $this->addFlash('error', 'Le trajet demandé n’existe pas.');
            return $this->redirectToRoute('app_reservations_list');
        }

        $fixedUser = $this->getFixedUser($em);
        $reservation = (new Reservations())
            ->setTrip($trip)
            ->setReservationTime(new \DateTime())
            ->setUser($fixedUser)
            ->setTransportId($trip->getTransportId());

        $form = $this->createForm(FrontReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $price = $this->calculateReservationPrice($reservation, $trip);
            $request->getSession()->set('reservation_data', [
                'trip_id'      => $trip->getId(),
                'seat_number'  => $reservation->getSeatNumber(),
                'seat_type'    => $reservation->getSeatType(),
                'price'        => $price,
                'transport_id' => $trip->getTransportId(),
            ]);

            return $this->redirectToRoute('app_reservations_choose');
        }

        return $this->render('FrontOffice/reservations/add.html.twig', [
            'form' => $form->createView(),
            'trip' => $trip,
        ]);
    }

    #[Route('/choose', name: 'app_reservations_choose', methods: ['GET'])]
    public function choose(Request $request, TripsRepository $tripsRepository): Response
    {
        $data = $request->getSession()->get('reservation_data');
        if (!$data) {
            $this->addFlash('error', 'Aucune réservation en attente.');
            return $this->redirectToRoute('app_reservations_list');
        }

        $trip = $tripsRepository->find($data['trip_id']);
        if (!$trip) {
            $this->addFlash('error', 'Trajet inexistant.');
            return $this->redirectToRoute('app_reservations_list');
        }

        return $this->render('FrontOffice/reservations/choose.html.twig', [
            'trip'       => $trip,
            'seatNumber' => $data['seat_number'],
            'seatType'   => $data['seat_type'],
            'price'      => $data['price'],
        ]);
    }

    #[Route('/payment', name: 'app_reservations_payment', methods: ['GET', 'POST'])]
    public function payment(
        Request $request,
        EntityManagerInterface $em,
        TripsRepository $tripsRepository,
        LoggerInterface $logger
    ): Response {
        $session = $request->getSession();
        $data    = $session->get('reservation_data');
        if (!$data) {
            $this->addFlash('error', 'Aucune réservation en attente.');
            return $this->redirectToRoute('app_reservations_list');
        }

        $trip = $tripsRepository->find($data['trip_id']);
        if (!$trip) {
            $this->addFlash('error', 'Trajet inexistant.');
            return $this->redirectToRoute('app_reservations_list');
        }

        if ($request->isMethod('POST')) {
            $errors = $this->validatePayment(
                $request->request->get('cardNumber'),
                $request->request->get('expiryDate'),
                $request->request->get('cvv')
            );
            if ($errors) {
                foreach ($errors as $e) {
                    $this->addFlash('error', $e);
                }
                return $this->redirectToRoute('app_reservations_payment');
            }

            $fixedUser = $this->getFixedUser($em);
            try {
                $reservation = (new Reservations())
                    ->setTrip($trip)
                    ->setUser($fixedUser)
                    ->setTransportId($data['transport_id'])
                    ->setSeatNumber($data['seat_number'])
                    ->setSeatType($data['seat_type'])
                    ->setReservationTime(new \DateTime())
                    ->setStatus(Reservations::STATUS_CONFIRMED)
                    ->setPaymentStatus(Reservations::PAYMENT_PAID);

                $em->persist($reservation);
                $em->flush();
                $session->remove('reservation_data');

                $logger->info('Paiement réussi', [
                    'reservation_id' => $reservation->getId(),
                    'user_id'        => self::FIXED_USER_ID,
                    'amount'         => $data['price'],
                ]);
                $this->addFlash('success', sprintf('Réservation #%d confirmée.', $reservation->getId()));
                return $this->redirectToRoute('app_reservations_payment_confirmation', ['id' => $reservation->getId()]);
            } catch (\Exception $e) {
                $logger->error('Erreur paiement', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors du paiement.');
                return $this->redirectToRoute('app_reservations_payment');
            }
        }

        return $this->render('FrontOffice/reservations/pay.html.twig', ['price' => $data['price']]);
    }

    #[Route('/payment/confirmation/{id}', name: 'app_reservations_payment_confirmation', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function paymentConfirmation(int $id, EntityManagerInterface $em): Response
    {
        $reservation = $em->getRepository(Reservations::class)->find($id);
        if (!$reservation || $reservation->getUser()->getId() !== self::FIXED_USER_ID) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_reservations_list');
        }
        $paid = $this->calculateReservationPrice($reservation, $reservation->getTrip());
        return $this->render('FrontOffice/reservations/payment_confirmation.html.twig', [
            'reservation' => $reservation,
            'paidAmount'  => $paid,
        ]);
    }

    #[Route('/pay/{id}', name: 'app_reservations_pay_pending', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function payPending(Request $request, Reservations $reservation, EntityManagerInterface $em): Response
    {
        // remplacer comparaison à getUser() par FIXED_USER_ID
        if ($reservation->getUser()->getId() !== self::FIXED_USER_ID) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_reservations_list');
        }

        if ($request->isMethod('POST')) {
            $reservation->setPaymentStatus(Reservations::PAYMENT_PAID);
            $reservation->setStatus(Reservations::STATUS_CONFIRMED);
            $em->flush();
            $this->addFlash('success', 'Paiement effectué.');
            return $this->redirectToRoute('app_reservations_payment_confirmation', ['id' => $reservation->getId()]);
        }
        return $this->render('FrontOffice/reservations/pay_pending.html.twig', [
            'reservation' => $reservation,
            'price'       => $this->calculateReservationPrice($reservation, $reservation->getTrip()),
        ]);
    }

    #[Route('/reserve', name: 'app_reservations_reserve', methods: ['POST'])]
   // #[IsGranted('ROLE_USER')] // REMOVE THIS LINE
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

        $fixedUser = $this->getFixedUser($em);

        $reservation = new Reservations();
        $reservation
            ->setTrip($trip)
            ->setReservationTime(new \DateTime())
            ->setUser($fixedUser)
            ->setTransportId($data['transport_id'])
            ->setSeatNumber($data['seat_number'])
            ->setSeatType($data['seat_type'])
            ->setStatus('pending')
            ->setPaymentStatus('pending')
        ;

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

        if (!$reservation || $reservation->getUser()->getId() !== self::FIXED_USER_ID) {
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

        if (!$reservation || $reservation->getUser()->getId() !== self::FIXED_USER_ID) {
            $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé');
            return $this->redirectToRoute('app_reservations_list');
        }

        return $this->render('FrontOffice/reservations/details.html.twig', [
            'reservation' => $reservation,
        ]);
    }
    #[Route('/edit/{id}', name: 'app_reservations_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Reservations $reservation, EntityManagerInterface $em): Response
    {
        if ($reservation->getUser()->getId() !== self::FIXED_USER_ID) {
            $this->addFlash('error', 'Accès non autorisé.');
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

        $form = $this->createForm(FrontReservationType::class, $reservation);
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
    #[Route('/edit/{id}/process', name: 'app_reservations_edit_process', methods: ['POST'])]
    public function Processedit(Request $request, Reservations $reservation, EntityManagerInterface $em): Response
    {
        // 1. Persist + flush
        $em->persist($reservation);
        $em->flush();

        // 2. Récupération des prix
        $originalPrice  = floatval($request->request->get('originalPrice'));
        $newPrice       = $this->calculateReservationPrice($reservation, $reservation->getTrip());
        $priceDifference = $newPrice - $originalPrice;

        // 3. Logique paiement/remboursement
        if ($priceDifference > 0) {
            $this->addFlash('warning', "Vous devez encore payer $priceDifference TND.");
            return $this->redirectToRoute('app_reservations_pay_pending', ['id' => $reservation->getId()]);
        } elseif ($priceDifference < 0) {
                $this->addFlash('success', "Vous allez être remboursé de ".abs($priceDifference)." TND.");
        } else{
             $this->addFlash('success', "Votre réservation a été modifiée .");
        }
        return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
    }

    #[Route('/cancel/{id}', name: 'app_reservations_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, Reservations $reservation, EntityManagerInterface $em): Response
    {
        if ($reservation->getUser()->getId() !== self::FIXED_USER_ID) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_reservations_list');
        }

        if ($this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('cancelled');
            $em->flush();
            $this->addFlash('success', 'Réservation annulée.');
        }

        return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
    }

    private function calculateReservationPrice(Reservations $reservation, $trip): float
    {
        $basePrice = $trip->getPrice();
        $seatType = $reservation->getSeatType();
        $seatNumber = $reservation->getSeatNumber();
        $price = $basePrice;

        if ($seatType == 'VIP') {
            $price *= 1.5;
        }
         return $price;
    }

    private function validatePayment(string $cardNumber, string $expiryDate, string $cvv): array
    {
        $errors = [];

        if (empty($cardNumber) || !preg_match('/^[0-9]{16}$/', $cardNumber)) {
            $errors[] = 'Numéro de carte invalide.';
        }

        if (empty($expiryDate) || !preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $expiryDate)) {
            $errors[] = 'Date d\'expiration invalide.';
        }

        if (empty($cvv) || !preg_match('/^[0-9]{3,4}$/', $cvv)) {
            $errors[] = 'Code CVV invalide.';
        }

        return $errors;
    }
}
