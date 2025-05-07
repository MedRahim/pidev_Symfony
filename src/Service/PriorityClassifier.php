<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PriorityClassifier
{
    private $client;
    private $apiKey;
    private $apiUrl = 'https://api-inference.huggingface.co/models/facebook/bart-large-mnli';

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function determinePriority(string $description): string
    {
        // Log the incoming description
        $logFile = __DIR__ . '/priority_classifier.log';
        file_put_contents($logFile, "\n\n--- New Priority Classification Request ---\n", FILE_APPEND);
        file_put_contents($logFile, "Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        file_put_contents($logFile, "Description: " . $description . "\n", FILE_APPEND);
        file_put_contents($logFile, "API Key (first 10 chars): " . substr($this->apiKey, 0, 10) . "...\n", FILE_APPEND);

        // Skip the fast check for testing API
        // if ($this->isCritical($description)) {
        //     return 'Haute';
        // }

        // 2. AI Classification
        try {
            $payload = [
                'inputs' => $description,
                'parameters' => [
                    'candidate_labels' => ['high priority', 'low priority'],
                    'multi_label' => false
                ]
            ];

            // Log the request payload
            file_put_contents($logFile, "Request Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

            $response = $this->client->request(
                'POST',
                $this->apiUrl,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                    'timeout' => 15 // Increased timeout
                ]
            );

            // Log the raw response
            file_put_contents($logFile, "Response Status Code: " . $response->getStatusCode() . "\n", FILE_APPEND);
            file_put_contents($logFile, "Response Headers: " . json_encode($response->getHeaders(), JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
            
            try {
                $result = $response->toArray();
                file_put_contents($logFile, "API Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
            } catch (\Exception $e) {
                file_put_contents($logFile, "Raw Response Content: " . $response->getContent(false) . "\n", FILE_APPEND);
                throw $e;
            }
            
            // Log specific details about the classification
            if (isset($result['labels']) && isset($result['scores'])) {
                file_put_contents($logFile, "\nClassification Details:\n", FILE_APPEND);
                foreach ($result['labels'] as $index => $label) {
                    $score = $result['scores'][$index];
                    file_put_contents($logFile, "Label: $label, Score: $score\n", FILE_APPEND);
                }
            }

            // Determine final priority
            $priority = isset($result['labels'][0]) && $result['labels'][0] === 'high priority' ? 'Haute' : 'Faible';
            file_put_contents($logFile, "Final Priority Decision: $priority\n", FILE_APPEND);
            
            return $priority;

        } catch (\Exception $e) {
            // Log any errors with full details
            file_put_contents($logFile, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents($logFile, "Error Class: " . get_class($e) . "\n", FILE_APPEND);
            file_put_contents($logFile, "Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            return $this->fallbackPriority($description);
        }
    }

    private function isCritical(string $text): bool
    {
        $keywords = ['urgent', 'critical', 'danger', 'broken', 'emergency', 
                    'safety hazard', 'recall', 'contamination', 'lawsuit'];
        
        foreach ($keywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    private function fallbackPriority(string $text): string
    {
        return (str_word_count($text) > 30 ? 'Haute' : 'Faible');
    }
}