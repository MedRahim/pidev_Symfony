<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ContentFilterService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function containsBadWords(string $text): bool
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                'https://www.purgomalum.com/service/containsprofanity',
                [
                    'query' => ['text' => $text]
                ]
            );

            return filter_var($response->getContent(), FILTER_VALIDATE_BOOLEAN);
        } catch (\Exception $e) {
            // Log error or handle exception
            return false;
        }
    }
}