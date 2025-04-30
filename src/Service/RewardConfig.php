<?php

namespace App\Service;

class RewardConfig
{
    public int $seuilTrips = 3; // Seuil réduit pour les tests
    
    public function randomType(): string
    {
        $types = [
            'reduction' => 60, // 60% de chance
            'upgrade' => 30,   // 30% de chance
            'gift' => 10       // 10% de chance
        ];
        
        $total = array_sum($types);
        $rand = mt_rand(1, $total);
        
        foreach ($types as $type => $weight) {
            if ($rand <= $weight) {
                return $type;
            }
            $rand -= $weight;
        }
        
        return 'reduction';
    }
}