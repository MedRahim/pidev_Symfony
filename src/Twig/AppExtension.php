<?php
namespace App\Twig;

use App\Service\CurrentUserService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use App\Service\RewardConfig;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private CurrentUserService $currentUserService;

    public function __construct(CurrentUserService $currentUserService)
    {
        $this->currentUserService = $currentUserService;
    }

    public function getGlobals(): array
    {
        return [
        'current_user_service' => $this->currentUserService,
    ];
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