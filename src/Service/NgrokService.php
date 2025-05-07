<?php
// src/Service/NgrokService.php
namespace App\Service;

class NgrokService
{
    public function getPublicUrl(): ?string
    {
        try {
            $ngrokData = @file_get_contents('http://localhost:4040/api/tunnels');
            if ($ngrokData) {
                $tunnels = json_decode($ngrokData, true);
                foreach ($tunnels['tunnels'] as $tunnel) {
                    if ($tunnel['proto'] === 'https') {
                        return $tunnel['public_url'];
                    }
                }
            }
        } catch (\Exception $e) {
            return null;
        }
        
        return null;
    }
}