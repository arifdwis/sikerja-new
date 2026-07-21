<?php

use App\Jobs\SendWhatsAppJob;
use App\Services\WhatsappService;

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
 * Send email notification to a user/number
 */
if (!function_exists('send_whatsapp')) {
    function send_whatsapp($target, $message)
    {
        return app(WhatsappService::class)->sendMessage($target, $message);
    }
}

/**
 * Send email notification to group (admins & TKKSD)
 */
if (!function_exists('send_group_whatsapp')) {
    function send_group_whatsapp($message, $groupId = null)
    {
        return app(WhatsappService::class)->sendToGroup($message, $groupId);
    }
}

/**
 * Send email notification to admin group
 */
if (!function_exists('send_admin_whatsapp')) {
    function send_admin_whatsapp($message)
    {
        return app(WhatsappService::class)->sendToAdmin('admin', $message);
    }
}

/**
 * Send email notification synchronously
 */
if (!function_exists('send_whatsapp_sync')) {
    function send_whatsapp_sync($target, $message)
    {
        $success = app(WhatsappService::class)->sendMessage($target, $message);
        return [
            'success' => $success,
            'status'  => $success ? 200 : 500,
            'data'    => ['message' => $success ? 'Mail sent' : 'Mail failed']
        ];
    }
}
