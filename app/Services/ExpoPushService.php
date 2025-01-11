<?php

namespace App\Services;

use GuzzleHttp\Client;

class ExpoPushService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://exp.host/--/api/v2/']);
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        if (!str_starts_with($token, 'ExponentPushToken')) {
            throw new \Exception("Invalid Expo Push Token: $token");
        }

        $response = $this->client->post('push/send', [
            'json' => [
                'to' => $token,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
