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
            // Normalize & Suffix handling (Matching Old App Logic)
            if (strpos($target, '@g.us') !== false) {
                // It's a group, keep as is
            } else {
                // Must be an individual, normalize and append suffix
                $normalized = self::normalizePhone($target);
                if (!$normalized) {
                    Log::warning("Invalid WA Target: $target");
                    return false;
                }
                $target = $normalized . '@s.whatsapp.net';
            }

            // Using Laravel Http Facade with Basic Auth (Matching Old App Guzzle Auth)
            $response = Http::withBasicAuth($user, $pass)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, [
                    'number' => $target,
                    'message' => $message,
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

        $phone = trim($phone);
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone))
            return null;

        // Handle generic 62 prefix first
        if (substr($phone, 0, 2) === '62') {
            // Check for double prefix like 6208...
            if (substr($phone, 0, 3) === '620') {
                return '62' . substr($phone, 3);
            }
            return $phone;
        }

        // Handle standard local 08 or 0 prefix
        if (substr($phone, 0, 1) === '0') {
            return '62' . substr($phone, 1);
        }

        // Handle number starting without 0 (e.g. 812...)
        if (substr($phone, 0, 1) === '8') {
            return '62' . $phone;
        }

        return $phone;
    }
}
