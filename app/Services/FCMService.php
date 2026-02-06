<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $factory = (new Factory)->withServiceAccount(storage_path('app/firebase/firebase-credentials.json'));
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('❌ FCM Service initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi ke satu device (FCM token)
     */
    public function sendToToken(string $fcmToken, string $title, string $body, array $data = [])
    {
        try {
            if (empty($fcmToken)) {
                Log::warning('⚠️ FCM Token kosong, notifikasi tidak dikirim');
                return false;
            }

            // Build notification
            $notification = Notification::create($title, $body);

            // Build message
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withData($data);

            // Send
            $this->messaging->send($message);

            Log::info('✅ FCM Notification sent successfully', [
                'fcm_token' => substr($fcmToken, 0, 20) . '...',
                'title' => $title,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('❌ Failed to send FCM notification: ' . $e->getMessage(), [
                'fcm_token' => substr($fcmToken, 0, 20) . '...',
                'title' => $title,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Kirim notifikasi ke multiple devices
     */
    public function sendToMultipleTokens(array $fcmTokens, string $title, string $body, array $data = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            // Send multicast
            $this->messaging->sendMulticast($message, $fcmTokens);

            Log::info('✅ FCM Multicast notification sent', [
                'token_count' => count($fcmTokens),
                'title' => $title,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('❌ Failed to send multicast FCM: ' . $e->getMessage());
            return false;
        }
    }
}
