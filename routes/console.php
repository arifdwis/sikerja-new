<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reminder kerjasama yang akan berakhir 3 bulan lagi (Req 14)
\Illuminate\Support\Facades\Schedule::command('kerjasama:check-expiry')
    ->dailyAt('07:00')
    ->name('check-kerjasama-expiry')
    ->withoutOverlapping();
