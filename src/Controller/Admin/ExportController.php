<?php
// src/Controller/Admin/ExportController.php
namespace App\Controller\Admin;

use App\Entity\Reservations;
use App\Entity\Trips;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ExportController extends AbstractController
{
    #[Route('/admin/reservations/export.csv', name: 'export_reservations_csv')]
    public function exportReservations(EntityManagerInterface $em): StreamedResponse
    {
        $response = new StreamedResponse(function() use ($em) {
            $query = $em->createQueryBuilder()
                ->select('r', 'u', 't')
                ->from(Reservations::class, 'r')
                ->leftJoin('r.user', 'u')
                ->leftJoin('r.trip', 't')
                ->getQuery();

            $handle = fopen('php://output', 'w+');
            fputcsv($handle, [
                'ID', 'Client', 'Email', 'Trajet', 
                'Date Réservation', 'Sièges', 'Type Siège', 
                'Statut', 'Paiement', 'Montant (TND)'
            ], ';');

            foreach ($query->toIterable() as $reservation) {
                $trip = $reservation->getTrip();
                $user = $reservation->getUser();

                fputcsv($handle, [
                    $reservation->getId(),
                    $user ? $user->getUsername() : 'N/A',
                    $user ? $user->getEmail() : 'N/A',
                    $trip ? "{$trip->getDeparture()} → {$trip->getDestination()}" : 'N/A',
                    $reservation->getReservationTime()->format('d/m/Y H:i'),
                    $reservation->getSeatNumber(),
                    $reservation->getSeatType(),
                    $reservation->getStatus(),
                    $reservation->getPaymentStatus(),
                    number_format(
                        $trip ? ($trip->getPrice() * $reservation->getSeatNumber()) : 0, 
                        2, 
                        ',', 
                        ' '
                    )
                ], ';');

                $em->detach($reservation);
            }

            fclose($handle);
        });

        $filename = sprintf('reservations_%s.csv', date('Ymd_His'));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    #[Route('/admin/trips/export.csv', name: 'export_trips_csv')]
    public function exportTrips(EntityManagerInterface $em): StreamedResponse
    {
        $response = new StreamedResponse(function() use ($em) {
            $query = $em->createQueryBuilder()
                ->select('t', 'tt')
                ->from(Trips::class, 't')
                ->leftJoin('t.transport', 'tt')
                ->getQuery();

            $handle = fopen('php://output', 'w+');
            fputcsv($handle, [
                'ID', 'Départ', 'Destination', 'Date Départ',
                'Date Arrivée', 'Prix', 'Capacité', 
                'Places Restantes', 'Transport', 'Distance (km)'
            ], ';');

            foreach ($query->toIterable() as $trip) {
                fputcsv($handle, [
                    $trip->getId(),
                    $trip->getDeparture(),
                    $trip->getDestination(),
                    $trip->getDepartureTime()->format('d/m/Y H:i'),
                    $trip->getArrivalTime()->format('d/m/Y H:i'),
                    number_format($trip->getPrice(), 2, ',', ' '),
                    $trip->getCapacity(),
                    $trip->getAvailableSeats(),
                    $trip->getTransport()?->getName() ?? 'N/A',
                    number_format($trip->getDistance(), 1, ',', ' ')
                ], ';');

                $em->detach($trip);
            }

            fclose($handle);
        });

        $filename = sprintf('trajets_%s.csv', date('Ymd_His'));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}