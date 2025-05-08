<?php

namespace App\Controller;

use App\Entity\Reservations;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $user = $this->em->getRepository(User::class)->find(7);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $reservations = $this->em->getRepository(Reservations::class)->findBy([
            'user' => $user,
            'status' => 'confirmed'
        ]);

        $stats = $this->calculateStats($reservations);
        $badges = $this->determineBadges($stats);
        $tripsData = $this->getTripsData($reservations);

        return $this->render('FrontOffice/reservations/dashboard.html.twig', [
            'user' => $user,
            'stats' => $stats,
            'badges' => $badges,
            'garden' => $this->generateGarden($stats['tripsCount']),
            'tripsData' => $tripsData
        ]);
    }

    private function getTripsData(array $reservations): array
    {
        $tripsData = [];

        foreach ($reservations as $reservation) {
            $trip = $reservation->getTrip();
            if ($trip) {
                $tripsData[] = [
                    'departure' => $trip->getDeparture(),
                    'destination' => $trip->getDestination(),
                    'distance' => $trip->getDistance(),
                    'co2Saved' => $this->calculateCo2Saved(
                        $trip->getTransportName(),
                        $trip->getDistance()
                    ),
                    'departureCoords' => $this->geocodeAddress($trip->getDeparture()),
                    'destinationCoords' => $this->geocodeAddress($trip->getDestination())
                ];
            }
        }

        return $tripsData;
    }

    private function geocodeAddress(string $address): array
    {
        // Simulation de géocodage - À remplacer par un vrai service
        return [
            'lat' => 48.8566 + (rand(-100, 100) * 0.01),
            'lng' => 2.3522 + (rand(-100, 100) * 0.01)
        ];
    }

    private function calculateStats(array $reservations): array
    {
        $stats = [
            'tripsCount' => 0,
            'totalKm' => 0,
            'co2Saved' => 0,
            'modesUsed' => [],
            'totalSeats' => 0
        ];

        foreach ($reservations as $reservation) {
            $trip = $reservation->getTrip();
            if ($trip) {
                $stats['tripsCount']++;
                $stats['totalKm'] += $trip->getDistance();

                $transportName = $trip->getTransportName();
                $stats['co2Saved'] += $this->calculateCo2Saved(
                    $transportName,
                    $trip->getDistance()
                );

                if (!in_array($transportName, $stats['modesUsed'])) {
                    $stats['modesUsed'][] = $transportName;
                }

                $stats['totalSeats'] += count(explode(',', $reservation->getSeatNumber()));
            }
        }

        // Arrondir les valeurs
        $stats['totalKm'] = round($stats['totalKm'], 2);
        $stats['co2Saved'] = round($stats['co2Saved'], 2);

        return $stats;
    }

    private function calculateCo2Saved(string $mode, float $distance): float
    {
        // Normalisation
        $modeNorm = strtolower(trim($mode));
        $modeNorm = preg_replace('/[^a-z]/', '', $modeNorm);

        // Clés toutes en minuscules
        $co2Factors = [
            'bus'     => 0.089,
            'autobus' => 0.089,
            'train'   => 0.041,
            'rail'    => 0.041,
            'metro'   => 0.035,
            'subway'  => 0.035,
            'tram'    => 0.022,
            'tramway' => 0.022,
            'default' => 0.12
        ];

        $factor = $co2Factors[$modeNorm] ?? $co2Factors['default'];
        $co2SavedRaw = ($co2Factors['default'] - $factor) * $distance;

        return round(max(0, $co2SavedRaw), 2);
    }

    private function determineBadges(array $stats): array
    {
        $badges = [];

        // Badges basés sur le nombre de trajets
        if ($stats['tripsCount'] >= 10) {
            $badges[] = 'voyageur_experimente';
        } elseif ($stats['tripsCount'] >= 5) {
            $badges[] = 'voyageur_actif';
        }

        // Badges basés sur les modes de transport
        $modesCount = count($stats['modesUsed']);
        if ($modesCount >= 5) {
            $badges[] = 'explorateur_urbain';
        } elseif ($modesCount >= 3) {
            $badges[] = 'voyageur_diversifie';
        }

        // Badges basés sur la distance
        if ($stats['totalKm'] >= 100) {
            $badges[] = 'centurion';
        } elseif ($stats['totalKm'] >= 50) {
            $badges[] = 'semi_centurion';
        }

        return array_unique($badges);
    }

    private function generateGarden(int $tripsCount): array
    {
        $plants = [];

        for ($i = 1; $i <= $tripsCount; $i++) {
            // 1. Déterminer le type de base (arbre ou fleur)
            $plantType = ($i % 3 == 0)
                ? 'flower' . (($i % 5) + 1)  // Fleurs: flower1 à flower5
                : 'tree' . (($i % 5) + 1);   // Arbres: tree1 à tree5

            // 2. Remplacer occasionnellement par un buisson
            if ($i % 5 == 0) {
                $plantType = 'bush' . (($i % 2) + 1); // Buissons: bush1 ou bush2
            }

            $plants[] = $plantType;
        }

        return $plants;
    }
}