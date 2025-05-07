<?php
// src/Service/RewardConfig.php
namespace App\Service;

class RewardConfig
{
    public array $seuils = [
        5  => 'small_discount',
        10 => 'medium_discount',
        15 => 'big_discount',
    ];

    public function getThresholdType(int $count): ?string
    {
        return $this->seuils[$count] ?? null;
    }

    public function randomTypeFor(string $threshold): string
    {
        return match ($threshold) {
            'small_discount'  => '5%_OFF',
            'medium_discount' => '10%_OFF',
            'big_discount'    => '20%_OFF',
            default           => 'SURPRISE',
        };
    }
    public function getRewardName(string $type): string
    {
        return match($type) {
            'small_discount' => 'Réduction Express',
            'medium_discount' => 'Bon Voyage',
            'big_discount' => 'Aventure Premium',
            default => 'Cadeau Mystère'
        };
    }
    
    public function getRewardDescription(string $type): string
    {
        return match($type) {
            'small_discount' => '5% de réduction sur votre prochain trajet !',
            'medium_discount' => '10% de réduction pour votre fidélité !',
            'big_discount' => '20% de réduction - Bravo pour votre engagement !',
            default => 'Une surprise exclusive pour vos efforts'
        };
    }


}
