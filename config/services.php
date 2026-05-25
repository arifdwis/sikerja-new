<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'enabled' => env('WA_GATEWAY_RUN', false),
        'url' => env('WA_GATEWAY_URL'),
        'username' => env('WA_GATEWAY_USER'),
        'password' => env('WA_GATEWAY_PASS'),
        'group_id' => env('WA_GROUP_ID', '120363189423910876@g.us'),
        'admin_group_id' => env('WA_ADMIN_GROUP_ID', '120363189423910876@g.us'),

        // ============ DEVELOPMENT TOGGLE ============
        // Saat sistem masih dalam tahap pengembangan, notifikasi grup dinonaktifkan
        // dan dialihkan ke nomor pribadi developer/tester (dev_redirect_phone).
        // Aktifkan kembali (set true) saat akan rilis ke production.
        'notify_group_enabled' => env('WA_NOTIFY_GROUP_ENABLED', false),
        'dev_redirect_phone' => env('WA_DEV_REDIRECT_PHONE', '085121942579'),

        // Disable notif ke admin (administrator/superadmin) saat development.
        // Notif ke pemohon, TKKSD, TKKSD Lokus tetap jalan normal.
        // Set true saat siap rilis ke production agar admin terima notifikasi.
        'notify_admin_enabled' => env('WA_NOTIFY_ADMIN_ENABLED', false),
    ],

    /**
     * Whitelist penerima notifikasi admin (sistem + WA).
     *
     * Bila diisi, HANYA user dengan email yang cocok salah satu di whitelist ini
     * yang menerima notifikasi internal admin & WA admin — meskipun ada banyak
     * akun ber-role administrator/superadmin di sistem.
     *
     * Bila kosong, fallback ke semua user ber-role administrator/superadmin.
     */
    'admin_notification_recipients' => array_filter(array_map(
        'trim',
        explode(',', env('ADMIN_NOTIFICATION_RECIPIENTS', ''))
    )),

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2'),
    ],

];
