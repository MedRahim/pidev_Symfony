<?php
// src/Service/HuggingFaceService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HuggingFaceService
{
    private $client;
    private $apiToken;
    private $apiUrl;

    public function __construct(HttpClientInterface $client, string $apiToken, string $apiUrl)
    {
        $this->client = $client;
        $this->apiToken = $apiToken;
        $this->apiUrl = $apiUrl;
    }

    public function generateContent(string $prompt): string
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_length' => 500,
                        'temperature' => 0.7,
                    ]
                ],
                'timeout' => 30
            ]);

            $content = $response->toArray();
            return $content[0]['generated_text'];
        } catch (\Exception $e) {
            error_log('Hugging Face API Error: ' . $e->getMessage());
            return ''; // Handle errors appropriately        }
    }
}
}