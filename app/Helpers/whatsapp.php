<?php

use App\Jobs\SendWhatsAppJob;

/**
 * Normalize phone number to standard format (628xxx)
 */
if (!function_exists('normalize_phone')) {
    function normalize_phone($number)
    {
        if (empty($number)) {
            return null;
        }

        $number = preg_replace('/[^0-9]/', '', $number);

        if (strlen($number) < 9 || strlen($number) > 15) {
            return null;
        }

        if (strpos($number, '08') === 0) {
            $number = '62' . substr($number, 1);
        } elseif (strpos($number, '8') === 0) {
            $number = '62' . $number;
        } elseif (strpos($number, '+62') === 0) {
            $number = substr($number, 1);
        }

        return $number;
    }
}

/**
 * Send WhatsApp message to a number (async via Job)
 */
if (!function_exists('send_whatsapp')) {
    function send_whatsapp($number, $message)
    {
        if (!config('services.whatsapp.enabled', false)) {
            return false;
        }

        $normalizedNumber = normalize_phone($number);
        if (!$normalizedNumber) {
            \Log::warning('WhatsApp: Invalid phone number', ['number' => $number]);
            return false;
        }

        $finalNumber = $normalizedNumber . '@s.whatsapp.net';
        SendWhatsAppJob::dispatch($finalNumber, $message)->delay(3);
        return true;
    }
}

/**
 * Send WhatsApp message to group (async via Job)
 */
if (!function_exists('send_group_whatsapp')) {
    function send_group_whatsapp($message, $groupId = null)
    {
        if (!config('services.whatsapp.enabled', false)) {
            return false;
        }

        $number = $groupId ?? config('services.whatsapp.group_id');
        SendWhatsAppJob::dispatch($number, $message)->delay(3);
        return true;
    }
}

/**
 * Send WhatsApp message to admin group (async via Job)
 */
if (!function_exists('send_admin_whatsapp')) {
    function send_admin_whatsapp($message)
    {
        if (!config('services.whatsapp.enabled', false)) {
            return false;
        }

        $groupId = config('services.whatsapp.admin_group_id');
        SendWhatsAppJob::dispatch($groupId, $message)->delay(3);
        return true;
    }
}

/**
 * Send WhatsApp message synchronously (for immediate sending)
 */
if (!function_exists('send_whatsapp_sync')) {
    function send_whatsapp_sync($number, $message)
    {
        if (!config('services.whatsapp.enabled', false)) {
            return ['success' => false, 'message' => 'WA Gateway is disabled'];
        }

        $url = config('services.whatsapp.url');
        $username = config('services.whatsapp.username');
        $password = config('services.whatsapp.password');

        if (strpos($number, '@') === false) {
            $normalized = normalize_phone($number);
            if ($normalized) {
                $number = $normalized . '@s.whatsapp.net';
            }
        }

        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 15,
                'http_errors' => false
            ]);

            $res = $client->post($url, [
                'auth' => [$username, $password],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'number' => $number,
                    'message' => $message,
                ],
            ]);

            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody(), true);

            return [
                'success' => $statusCode === 200,
                'status' => $statusCode,
                'data' => $response
            ];

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return [
                'success' => false,
                'message' => 'Request Error: ' . $e->getMessage(),
                'code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'General Error: ' . $e->getMessage(),
            ];
        }
    }
}
