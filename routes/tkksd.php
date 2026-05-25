<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenjadwalanController;
use App\Http\Controllers\PembahasanController;

// Penjadwalan
Route::resource('penjadwalan', PenjadwalanController::class)->only(['store', 'update', 'destroy']);
Route::put('penjadwalan/{penjadwalan}/review', [PenjadwalanController::class, 'review'])->name('penjadwalan.review');

// Pembahasan
Route::get('pembahasan/riwayat', [PembahasanController::class, 'riwayat'])->name('pembahasan.riwayat');
Route::get('pembahasan/arsip', [PembahasanController::class, 'arsip'])->name('pembahasan.arsip');
Route::resource('pembahasan', PembahasanController::class);

// Monev review oleh TKKSD Lokus (Req 11.5, 11.6)
Route::put('monev/{uuid}/tkksd-review', [\App\Http\Controllers\MonevController::class, 'tkksdReview'])
    ->name('monev.tkksd-review');

// Daftar kerjasama TKKSD Lokus (read-only) — Aktif & Selesai
Route::get('tkksd-lokus/kerjasama/{tipe}', [\App\Http\Controllers\TkksdLokusController::class, 'kerjasama'])
    ->whereIn('tipe', ['aktif', 'selesai'])
    ->name('tkksd-lokus.kerjasama');
