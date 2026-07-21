<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\Pemohon;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WhatsappService
{
    /**
     * Send email notification to all admins (administrator/superadmin).
     */
    public function sendToAdmin(string $target, string $message, ?string $subject = null): bool
    {
        return $this->sendToGroup($message, 'admin_group_id', $subject);
    }

    /**
     * Blast email notification to all admin, superadmin, and tkksd users in place of WA group notifications.
     */
    public function sendToGroup(string $message, ?string $groupKey = 'group_id', ?string $subject = null): bool
    {
        $recipients = User::adminNotificationRecipients()->get()->unique('id');

        if ($recipients->isEmpty()) {
            Log::warning("Mail Group Notification: No recipients found.");
            return false;
        }

        $subjectTitle = $subject ?? self::extractSubject($message);
        $sentCount = 0;

        foreach ($recipients as $user) {
            $email = $user->target_notification_email;
            if ($email) {
                try {
                    Mail::to($email)->send(new NotificationMail($subjectTitle, $message));
                    Log::info("Mail Group Notification blasted to {$email} (User: {$user->name})");
                    $sentCount++;
                } catch (\Throwable $e) {
                    Log::error("Mail Group Notification Error to {$email}: " . $e->getMessage());
                }
            } else {
                Log::warning("Mail Group Notification: User #{$user->id} ({$user->name}) has no valid notification email.");
            }
        }

        return $sentCount > 0;
    }

    /**
     * Send email notification to single target (User, Email address, or Phone recipient).
     *
     * @param mixed $target User instance, Email string, or Phone number string
     * @param string $message
     * @param string|null $subject
     * @return bool
     */
    public function sendMessage($target, string $message, ?string $subject = null): bool
    {
        if (empty($target)) {
            return false;
        }

        // Check if target is a group identifier
        if (is_string($target) && (strpos($target, '@g.us') !== false || strpos($target, 'group') !== false)) {
            return $this->sendToGroup($message, null, $subject);
        }

        $email = null;

        if ($target instanceof User) {
            $email = $target->target_notification_email;
        } elseif (is_string($target) && filter_var($target, FILTER_VALIDATE_EMAIL)) {
            $email = $target;
        } elseif (is_string($target)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $target);

            // 1. Search user by email or phone
            $user = User::where('email', $target)
                ->orWhere('notification_email', $target)
                ->orWhere('phone', $target)
                ->orWhere('phone', '0' . substr($cleanPhone, 2))
                ->orWhere('phone', $cleanPhone)
                ->first();

            if (!$user) {
                // 2. Search pemohon by phone
                $pemohon = Pemohon::where('phone', $target)
                    ->orWhere('phone', '0' . substr($cleanPhone, 2))
                    ->orWhere('phone', $cleanPhone)
                    ->first();

                if ($pemohon) {
                    $user = User::find($pemohon->id_operator);
                }
            }

            if ($user) {
                $email = $user->target_notification_email;
            } elseif (filter_var($target, FILTER_VALIDATE_EMAIL)) {
                $email = $target;
            }
        }

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Mail Notification: Unable to resolve a valid email address for target '{$target}'.");
            return false;
        }

        $subjectTitle = $subject ?? self::extractSubject($message);

        try {
            Mail::to($email)->send(new NotificationMail($subjectTitle, $message));
            Log::info("Mail Notification sent successfully to {$email}");
            return true;
        } catch (\Throwable $e) {
            Log::error("Mail Notification Error to {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract a clean subject line from the first line of the message body.
     */
    public static function extractSubject(string $message): string
    {
        $lines = explode("\n", trim($message));
        $firstLine = trim($lines[0] ?? '');

        // Remove markdown formatting symbols
        $clean = trim(str_replace(['*', '_', '[NOTIFIKASI INTERNAL]', '[DEV — Group Notif Dialihkan]'], '', $firstLine));

        if (!empty($clean) && strlen($clean) < 120) {
            return $clean;
        }

        return 'Notifikasi SIKERJA Samarinda';
    }

    /**
     * Legacy helper to normalize phone numbers
     */
    public static function normalizePhone($phone)
    {
        if (empty($phone)) return null;
        $phone = trim($phone);
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($phone)) return null;

        if (substr($phone, 0, 2) === '62') {
            if (substr($phone, 0, 3) === '620') {
                return '62' . substr($phone, 3);
            }
            return $phone;
        }

        if (substr($phone, 0, 1) === '0') {
            return '62' . substr($phone, 1);
        }

        if (substr($phone, 0, 1) === '8') {
            return '62' . $phone;
        }

        return $phone;
    }
}
