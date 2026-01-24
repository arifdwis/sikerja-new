<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Send WhatsApp message directly (Sync)
     * 
     * @param string $target Target number or Group ID
     * @param string $message Message content
     * @return bool
     */
    public function sendMessage($target, $message)
    {
        if (!filter_var(env('WA_GATEWAY_RUN', false), FILTER_VALIDATE_BOOLEAN)) {
            Log::info("WA Gateway disabled. Message to $target skipped.");
            return false;
        }

        $url = env('WA_GATEWAY_URL');
        $user = env('WA_GATEWAY_USER');
        $pass = env('WA_GATEWAY_PASS');

        if (!$url || !$user || !$pass) {
            Log::error("WA Gateway configuration missing.");
            return false;
        }

        try {
            // Normalize number if it looks like a phone number and not a Group ID (@g.us)
            if (strpos($target, '@g.us') === false) {
                $target = self::normalizePhone($target);
            }

            if (!$target) {
                Log::warning("Invalid WA API Target: null or empty");
                return false;
            }

            $response = Http::post($url, [
                'user' => $user,
                'pass' => $pass,
                'id' => $target, // The API expects 'id'
                'pesan' => $message
            ]);

            if ($response->successful()) {
                Log::info("WA Message sent to $target");
                return true;
            } else {
                Log::error("WA Gateway Error ($target): " . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalize phone number to standard format (628xxx)
     * for Sending to Individual
     */
    public static function normalizePhone($phone)
    {
        if (empty($phone))
            return null;

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone))
            return null;

        if (substr($phone, 0, 2) === '62') {
            return $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            return '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 1) === '8') {
            return '62' . $phone;
        }

        return $phone;
    }
}
