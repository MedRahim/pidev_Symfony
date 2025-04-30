<?php

namespace App\Service;

use App\Entity\MysteryReward;
use App\Entity\Reservations;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProgressService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private RewardConfig $rewardConfig,
    ) {}

    public function recordTrip(Users $user, string $mode, int $distanceKm): void
    {
        $co2Saved = $this->calculateCo2Saved($mode, $distanceKm);
        
        $this->requestStack->getSession()->set('last_trip_stats', [
            'distance' => $distanceKm,
            'co2_saved' => $co2Saved,
            'mode' => $mode
        ]);

        $reservationsCount = $this->em->getRepository(Reservations::class)
            ->count(['user' => $user, 'status' => 'confirmed']);
            
        if ($reservationsCount > 0 && $reservationsCount % $this->rewardConfig->seuilTrips === 0) {
            $this->createReward($user);
        }
    }

    private function calculateCo2Saved(string $mode, int $distance): float
    {
        $mode = ucfirst(strtolower($mode));
        
        $co2Factors = [
            'Bus' => 0.089,
            'Train' => 0.041,
            'Metro' => 0.035,
            'Tram' => 0.022,
            'default' => 0.12
        ];

        $factor = $co2Factors[$mode] ?? $co2Factors['default'];
        return round(($co2Factors['default'] - $factor) * $distance, 2);
    }

    private function createReward(Users $user): void
    {
        $reward = new MysteryReward();
        $reward->setUser($user);
        $reward->setGrantedAt(new \DateTime());
        $reward->setType($this->rewardConfig->randomType());
        
        $this->em->persist($reward);
        
        $this->requestStack->getSession()->getFlashBag()->add(
            'reward',
            '🎁 Félicitations ! Vous avez gagné un bon de réduction.'
        );
    }
}