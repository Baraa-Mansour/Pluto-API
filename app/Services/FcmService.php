<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmService
{
    private Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }


    public function sendNotification(string $token, string $title, string $body, array $data = []): void
    {
        Log::info('FcmService sendNotification called.', [
            'token' => $token,
            'data' => $data,
        ]);

        $message = CloudMessage::new()
            ->toToken($token)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        try
        {
            $this->messaging->send($message);
            Log::info('FCM Notification sent successfully.');
        }
        catch(Exception $e)
        {
            Log::error('FCM Notification Error: ' . $e->getMessage());
        }
    }
}
