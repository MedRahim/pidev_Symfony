<?php
// src/Controller/WeatherController.php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/meteo')] // <-- Ajoutez ce préfixe de route ici
class WeatherController extends AbstractController
{
    public function __construct(
        private WeatherService $weatherService
    ) {}

    #[Route('/{city}', name: 'app_weather_details', methods: ['GET'])]
    public function details(string $city): Response
    {
        $weather = $this->weatherService->getCurrentWeather($city);
        
        return $this->render('FrontOffice/listing/weather.html.twig', [
            'city' => $city,
            'weather' => $weather
        ]);
    }
} 