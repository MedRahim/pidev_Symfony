<?php
namespace App\Twig;

use App\Service\CurrentUserService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

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
}
