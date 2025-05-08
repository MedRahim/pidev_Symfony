<?php
// src/Service/ProgressService.php
namespace App\Service;

use App\Entity\MysteryReward;
use App\Entity\Reservations;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProgressService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack          $requestStack,
        private RewardConfig          $rewardConfig
    ) {}

    /**
     * Enregistre un trajet et crée une récompense si un palier est atteint.
     */
    public function recordTrip(User $user, string $transportName, float $distance, int $co2Saved): void
    {
        // 1) Stocke les stats du dernier trajet en session
        $this->requestStack->getSession()->set('last_trip_stats', [
            'mode'      => $transportName,
            'distance'  => $distance,
            'co2_saved' => $co2Saved,
        ]);

        // 2) Compte les trajets confirmés
        $count = $this->em
            ->getRepository(Reservations::class)
            ->count(['user' => $user, 'status' => 'confirmed']);

        // 3) Vérifie le palier exact atteint (5, 10 ou 15)
        $thresholdType = $this->rewardConfig->getThresholdType($count);
        if (null === $thresholdType) {
            return;
        }

        // 4) Si pas déjà récompensé, on crée la récompense
        $existing = $this->em
            ->getRepository(MysteryReward::class)
            ->findOneBy(['user' => $user, 'type' => $thresholdType]);

        if ($existing) {
            return;
        }

        $reward = new MysteryReward();
        $reward
            ->setUser($user)
            ->setGrantedAt(new \DateTime())
            ->setType($thresholdType);

        $this->em->persist($reward);
        $this->em->flush();

        // 5) Stocke en session les infos pour Twig
        $this->requestStack->getSession()->set('mystery_reward', [
            'type'    => $thresholdType,
            'code'    => $this->rewardConfig->randomTypeFor($thresholdType),
            'message' => sprintf('Bravo ! Vous venez d’atteindre %d trajets.', $count),
        ]);
    }
}
