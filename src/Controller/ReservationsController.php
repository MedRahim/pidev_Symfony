<?php

namespace App\Controller;
// Ajoutez ce use statement avec les autres imports
// En haut du fichier avec les autres use statements
use App\Service\QrCodeService;  // <-- Ajoutez cette ligne
use App\Entity\Trips;
use App\Service\QrCodeGenerator;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use App\Service\NgrokService;


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

    // src/Controller/ReservationsController.php


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
    
        $reservation = new Reservations();
        $form = $this->createForm(FrontReservationType::class, $reservation);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $seatNumbers = $form->get('seatNumber')->getData();
            if (empty($seatNumbers)) {
                $this->addFlash('error', 'Veuillez sélectionner au moins un siège');
                return $this->redirectToRoute('app_reservations_new', ['tripId' => $tripId]);
            }
    
            $request->getSession()->set('reservation_data', [
                'trip_id' => $trip->getId(),
                'seat_number' => $seatNumbers,
                'seat_type' => $form->get('seatType')->getData(),
                'price' => $this->calculateReservationPrice($reservation, $trip),
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

        $selectedSeats = explode(',', $data['seat_number'] ?? '');
        $seatCount     = count($selectedSeats);

        if ($request->isMethod('POST')) {
            if ($seatCount > $trip->getCapacity()) {
                $this->addFlash('error', 'Pas assez de places disponibles.');
                return $this->redirectToRoute('app_reservations_new', ['tripId' => $trip->getId()]);
            }

            $em->getConnection()->beginTransaction();
            try {
                $reservation = (new Reservations())
                    ->setTrip($trip)
                    ->setUser($this->getFixedUser($em))
                    ->setTransportId($data['transport_id'])
                    ->setSeatNumber($data['seat_number'])
                    ->setSeatType($data['seat_type'])
                    ->setReservationTime(new \DateTime())
                    ->setStatus(Reservations::STATUS_CONFIRMED)
                    ->setPaymentStatus(Reservations::PAYMENT_PAID);

                // Mise à jour de la capacité
                $trip->setCapacity($trip->getCapacity() - $seatCount);

                // Persistance réservation + trip
                $em->persist($reservation);
                $em->persist($trip);

                // Calcul CO₂
                $co2PerKmPerSeat = 0.05;
                $co2Saved = (int) round($trip->getDistance() * $seatCount * $co2PerKmPerSeat);

                // Enregistrement progression (sans flush interne)
                $this->progressService->recordTrip(
                    $this->getFixedUser($em),
                    $trip->getTransportName(),
                    $trip->getDistance(),
                    $co2Saved
                );

                // Flush + commit
                $em->flush();
                $em->getConnection()->commit();

                // Nettoyage + log + redirection
                $session->remove('reservation_data');
                $logger->info('Paiement réussi', ['reservation_id' => $reservation->getId()]);

                return $this->redirectToRoute('app_reservations_payment_confirmation', [
                    'id' => $reservation->getId(),
                ]);
            } catch (\Throwable $e) {
                $em->getConnection()->rollBack();
                $logger->error('Erreur paiement détaillée', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                $this->addFlash('error', 'Erreur lors du traitement du paiement : ' . $e->getMessage());

                return $this->redirectToRoute('app_reservations_payment', [
                    'tripId' => $trip->getId(),
                ]);
            }
        }

        // Si GET ou méthode autre que POST → affichage du formulaire de paiement
        return $this->render('FrontOffice/reservations/pay.html.twig', [
            'price' => $this->calculateReservationPrice(
                (new Reservations())
                    ->setSeatNumber($data['seat_number'])
                    ->setSeatType($data['seat_type']),
                $trip
            ),
        ]);
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

    if ($reservation->getStatus() === 'cancelled') {
        $this->addFlash('warning', 'Les réservations annulées ne peuvent pas être modifiées');
        return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
    }

    // Sauvegarde des valeurs originales
    $originalData = [
        'seatNumber' => $reservation->getSeatNumber(),
        'seatType'   => $reservation->getSeatType(),
        'price'      => $this->calculateReservationPrice($reservation, $reservation->getTrip()),
    ];

    // *** Passage de l’option 'trip' ici aussi
    $form = $this->createForm(FrontReservationType::class, $reservation, [
        'trip' => $reservation->getTrip(),
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $newPrice = $this->calculateReservationPrice($reservation, $reservation->getTrip());

        if ($reservation->getSeatNumber() == $originalData['seatNumber'] &&
            $reservation->getSeatType() == $originalData['seatType']) {
            $this->addFlash('info', 'Aucune modification apportée à la réservation');
            return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
        }

        $request->getSession()->set('reservation_data', [
            'trip_id'         => $reservation->getTrip()->getId(),
            'seat_number'     => $reservation->getSeatNumber(),
            'seat_type'       => $reservation->getSeatType(),
            'price'           => $newPrice,
            'transport_id'    => $reservation->getTransportId(),
            'is_edit'         => true,
            'reservation_id'  => $reservation->getId(),
            'original_price'  => $originalData['price'],
            'price_difference'=> $newPrice - $originalData['price'],
        ]);

        return $this->redirectToRoute('app_reservations_edit_confirmation', ['id' => $reservation->getId()]);
    }

    return $this->render('FrontOffice/reservations/edit.html.twig', [
        'form'          => $form->createView(),
        'reservation'   => $reservation,
        'originalPrice' => $originalData['price'],
        'newPrice'      => $this->calculateReservationPrice($reservation, $reservation->getTrip()),
    ]);
}


    #[Route('/cancel/{id}', name: 'app_reservations_cancel', methods: ['POST'])]
    public function cancel(Request $request, Reservations $reservation, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->request->get('_token'))) {
            $em->getConnection()->beginTransaction();
            try {
                // Récupération des sièges annulés
                $seatCount = count(explode(',', $reservation->getSeatNumber()));
                $trip = $reservation->getTrip();
                
                // Mise à jour de la capacité
                $trip->setCapacity($trip->getCapacity() + $seatCount);
                
                // Annulation de la réservation
                $reservation->setStatus('cancelled');
                
                $em->persist($trip);
                $em->persist($reservation);
                $em->flush();
                $em->getConnection()->commit();
                
                $this->addFlash('success', 'Réservation annulée avec succès.');
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();
                $this->addFlash('error', 'Erreur lors de l\'annulation.');
            }
        }
        return $this->redirectToRoute('app_reservations_details', ['id' => $reservation->getId()]);
    }
    

    private function calculateReservationPrice(Reservations $reservation, Trips $trip): float
    {
        $basePrice = (float) $trip->getPrice();
        $types     = explode(',', $reservation->getSeatType());
        $total     = 0.0;
    
        foreach ($types as $type) {
            $mult = $type === 'Premium' ? 1.5 : 1.0; // Modifier 2.0 → 1.5
            $total += $basePrice * $mult;
        }
    
        return round($total, 2);
    }

    #[Route('/api/seat-configuration/{tripId}', name: 'api_seat_configuration', methods: ['GET'])]
    public function getSeatConfiguration(int $tripId, EntityManagerInterface $em): JsonResponse
    {
        $trip = $em->getRepository(Trips::class)->find($tripId);
        if (!$trip) {
            return $this->json(['error' => 'Trip not found'], 404);
        }

        $reservations = $em->getRepository(Reservations::class)
                           ->findBy(['trip' => $trip]);

        $reservedSeats = [];
        foreach ($reservations as $r) {
            $seats = array_filter(explode(',', $r->getSeatNumber()));
            $reservedSeats = array_merge($reservedSeats, $seats);
        }
        $reservedSeats = array_values(array_unique($reservedSeats));
        $reservedCount = count($reservedSeats);

        // Total original seats = capacité actuelle + déjà réservé
        $totalSeatsOriginal = $trip->getCapacity() + $reservedCount;

        return $this->json([
            'totalSeats'    => $totalSeatsOriginal,
            'reservedSeats' => $reservedSeats,
            'seatPrice'     => (float) $trip->getPrice(),
        ]);
    }
    #[Route('/api/update-capacity/{tripId}', name: 'update_capacity', methods: ['POST'])]
    public function updateCapacity(Request $request, int $tripId, EntityManagerInterface $em): JsonResponse
    {
        $trip = $em->getRepository(Trips::class)->find($tripId);
        $data = json_decode($request->getContent(), true);

        try {
            $trip->setCapacity($trip->getCapacity() - $data['seatsCount']);
            $em->flush();

            return $this->json([
                'success'     => true,
                'newCapacity' => $trip->getCapacity(),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/{id}/qrcode', name: 'app_reservations_qrcode', methods: ['GET'])]
    public function showQrCode(Reservations $reservation, QrCodeService $qrCodeService): Response
    {
        if ($reservation->getUser()->getId() !== self::FIXED_USER_ID) {
            throw $this->createAccessDeniedException();
        }
        
        return new Response(
            $qrCodeService->generateQrCode($reservation),
            Response::HTTP_OK,
            ['Content-Type' => 'image/svg+xml']
        );
    }
    #[Route('/reservation/{id}/ticket', name: 'app_reservation_ticket')]
    public function digitalTicket(Reservations $reservation, NgrokService $ngrokService): Response
    {
        $ngrokUrl = $ngrokService->getPublicUrl();
        
        return $this->render('FrontOffice/reservations/digital_ticket.html.twig', [
            'reservation' => $reservation,
            'ngrok_url' => $ngrokUrl
        ]);
    }
}