<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Send WhatsApp message to admin (administrator/superadmin).
     * 
     * Saat development (notify_admin_enabled=false), admin TIDAK terima notif
     * sehingga inbox admin tidak penuh dengan data uji.
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function sendToAdmin(string $phone, string $message): bool
    {
        $adminEnabled = filter_var(
            config('services.whatsapp.notify_admin_enabled', false),
            FILTER_VALIDATE_BOOLEAN
        );

        if (!$adminEnabled) {
            Log::info("WA admin notif disabled (development mode). Skip $phone.");
            return false;
        }

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send WhatsApp message to admin/notify group.
     * 
     * Saat sistem masih dalam tahap pengembangan (notify_group_enabled=false),
     * pesan dialihkan ke nomor pribadi developer (dev_redirect_phone) dengan
     * prefix "[DEV]" agar mudah dibedakan.
     *
     * Penggunaan: ganti $wa->sendMessage(config('services.whatsapp.group_id'), $msg)
     * dengan       $wa->sendToGroup($msg)
     *
     * @param string $message
     * @param string|null $groupKey 'group_id' atau 'admin_group_id' (default: group_id)
     * @return bool
     */
    public function sendToGroup(string $message, ?string $groupKey = 'group_id'): bool
    {
        $groupEnabled = filter_var(
            config('services.whatsapp.notify_group_enabled', false),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($groupEnabled) {
            $groupId = config("services.whatsapp.{$groupKey}");
            if (!$groupId) {
                Log::warning("WA group_id ({$groupKey}) tidak diset.");
                return false;
            }
            return $this->sendMessage($groupId, $message);
        }

        // DEV mode: redirect ke nomor pribadi
        $devPhone = config('services.whatsapp.dev_redirect_phone');
        if (!$devPhone) {
            Log::warning('WA dev_redirect_phone tidak diset, group notif dilewati.');
            return false;
        }

        $devMessage = "[DEV — Group Notif Dialihkan]\n" . $message;
        Log::info("WA group notif redirected to dev phone {$devPhone}");
        return $this->sendMessage($devPhone, $devMessage);
    }

    /**
     * Send WhatsApp message directly (Sync)
     * 
     * @param string $target Target number or Group ID
     * @param string $message Message content
     * @return bool
     */
    public function sendMessage($target, $message)
    {
        if (!filter_var(config('services.whatsapp.enabled', false), FILTER_VALIDATE_BOOLEAN)) {
            Log::info("WA Gateway disabled. Message to $target skipped.");
            return false;
        }

        $url = config('services.whatsapp.url');
        $user = config('services.whatsapp.username');
        $pass = config('services.whatsapp.password');

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
            $response = Http::withoutVerifying()
                ->withBasicAuth($user, $pass)
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
