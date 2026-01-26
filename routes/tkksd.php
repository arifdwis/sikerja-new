<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenjadwalanController;
use App\Http\Controllers\PembahasanController;

// Penjadwalan
Route::resource('penjadwalan', PenjadwalanController::class)->only(['store', 'update', 'destroy']);
Route::put('penjadwalan/{penjadwalan}/review', [PenjadwalanController::class, 'review'])->name('penjadwalan.review');

// Pembahasan
Route::resource('pembahasan', PembahasanController::class);
