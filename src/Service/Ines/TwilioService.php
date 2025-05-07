<?php

namespace App\Service\Ines;

use Twilio\Rest\Client;

class TwilioService
{
    private $twilio;
    private $from;

    public function __construct(string $sid, string $token, string $from)
    {
        if (empty($sid) || empty($token) || empty($from)) {
            throw new \RuntimeException('Les informations Twilio sont manquantes.');
        }

        $this->twilio = new Client($sid, $token);
        $this->from = $from;
    }

    public function sendSms(string $to, string $message): bool
    {
        try {
            $messageSent = $this->twilio->messages->create(
                $to,
                ['from' => $this->from, 'body' => $message]
            );
            return !empty($messageSent->sid);
        } catch (\Exception $e) {
            echo '❌ Erreur Twilio : ' . $e->getMessage();
            return false;
        }
    }
}
