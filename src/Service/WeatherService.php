<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * Récupère la météo pour une ville donnée.
     *
     * @param string $city  Nom de la ville (ex : "Tunis")
     * @return array|null   Retourne les données météo ou null en cas d'erreur.
     */
    public function getCurrentWeather(string $city): ?array
    {
        try {
            $response = $this->client->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ],
            ]);
    
            $data = $response->toArray();
            $now = time();
            $sunrise = $data['sys']['sunrise'];
            $sunset = $data['sys']['sunset'];
    
            // Calcul de la position du soleil
            $sun_position = $this->calculateSunPosition($now, $sunrise, $sunset);
    
            return [
                'temp' => $data['main']['temp'],
                'feels_like' => $data['main']['feels_like'],
                'humidity' => $data['main']['humidity'],
                'pressure' => $data['main']['pressure'],
                'wind_speed' => $data['wind']['speed'] * 3.6,
                'wind_deg' => $data['wind']['deg'] ?? null,
                'visibility' => $data['visibility'],
                'uvi' => $data['current']['uvi'] ?? null,
                'description' => ucfirst($data['weather'][0]['description']),
                'icon' => $data['weather'][0]['icon'],
                'sunrise' => \DateTime::createFromFormat('U', $sunrise),
                'sunset' => \DateTime::createFromFormat('U', $sunset),
                'sun_position' => $sun_position // Ajout de la position calculée
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
    
    private function calculateSunPosition(int $currentTime, int $sunrise, int $sunset): array
    {
        if ($currentTime < $sunrise) {
            return ['x' => 0, 'y' => 50]; // Avant le lever
        }
        
        if ($currentTime > $sunset) {
            return ['x' => 100, 'y' => 50]; // Après le coucher
        }
    
        $daylightDuration = $sunset - $sunrise;
        $currentPosition = $currentTime - $sunrise;
        $percentage = $currentPosition / $daylightDuration;
    
        // Calcul des coordonnées sur une trajectoire semi-circulaire
        $angle = $percentage * pi();
        $x = (1 - cos($angle)) * 50;
        $y = sin($angle) * 50;
    
        return [
            'x' => round($x, 2),
            'y' => round(100 - $y, 2) // Inversion Y pour le placement CSS
        ];
    }
    
}