<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsService
{
    public function send(string $phone, string $message): void
    {
        $client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $client->messages->create($phone, [
            'from' => config('services.twilio.from'),
            'body' => $message,
        ]);
    }
}
