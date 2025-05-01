<?php
// src/Service/QrCodeService.php

namespace App\Service;  // Cette ligne est CRUCIALE

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Entity\Reservations;

class QrCodeService
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

   // src/Service/QrCodeService.php

public function generateQrCode(Reservations $reservation): string
{
    // URL de base Ngrok (à configurer proprement - voir solution alternative ci-dessous)
    $baseUrl = 'https://2466-197-3-116-29.ngrok-free.app';
    
    $ticketPath = $this->router->generate('app_reservation_ticket', [
        'id' => $reservation->getId()
    ]);
    
    $ticketUrl = $baseUrl . $ticketPath;

    $renderer = new ImageRenderer(
        new RendererStyle(400),
        new SvgImageBackEnd()
    );
    
    $writer = new Writer($renderer);
    return $writer->writeString($ticketUrl);
}
}