<?php
// src/Twig/AppExtension.php

namespace App\Twig;

use App\Service\RewardConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function __construct(private RewardConfig $rewardConfig) 
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('reward_name', [$this, 'getRewardName']),
        ];
    }

    public function getRewardName(string $type): string
    {
        return match($type) {
            'small_discount' => '5% de réduction',
            'medium_discount' => '10% de réduction',
            'big_discount' => '20% de réduction',
            default => 'Récompense spéciale'
        };
    }
}