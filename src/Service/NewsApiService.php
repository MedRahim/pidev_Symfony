<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class NewsApiService
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = 'pub_83819bc298adfbb046b3cf5e573fd7d2324bc';
    }

    /**
     * Fetch news articles related to a query (e.g., category or keyword)
     */
    public function fetchNews(string $query = '', int $limit = 5): array
    {
        if (empty($query)) {
            return [];
        }

        try {
            $url = 'https://newsdata.io/api/1/news';
            $params = [
                'apikey' => $this->apiKey,
                'q' => $query,
                'language' => 'en',
                'country' => 'us',
                'size' => $limit
            ];

            $response = $this->client->request('GET', $url, ['query' => $params]);
            $data = $response->toArray(false);

            if (isset($data['results']) && is_array($data['results'])) {
                return array_map(function($article) {
                    return [
                        'title' => $article['title'] ?? '',
                        'link' => $article['link'] ?? '',
                        'pubDate' => $article['pubDate'] ?? '',
                        'image_url' => $article['image_url'] ?? null,
                        'source_url' => $article['source_url'] ?? ''
                    ];
                }, $data['results']);
            }
        } catch (\Exception $e) {
            // Log error if needed
            // error_log('NewsAPI error: ' . $e->getMessage());
        }

        return [];
    }
}
