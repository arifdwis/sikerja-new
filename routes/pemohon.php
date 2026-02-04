<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\MonevController;

// Profile
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
Route::put('/profile/biodata', [ProfileController::class, 'updateBiodata'])->name('profile.biodata.update');
Route::put('/profile/corporate', [ProfileController::class, 'updateCorporate'])->name('profile.corporate.update');

// Permohonan (Pemohon)
Route::get('/riwayat', [PermohonanController::class, 'riwayat'])->name('riwayat.index');
Route::get('permohonan/selesai', [PermohonanController::class, 'index'])->name('permohonan.selesai');
Route::get('api/kotas/{provinsi}', [PermohonanController::class, 'getKotas'])->name('api.kotas');
Route::resource('permohonan', PermohonanController::class);
Route::post('permohonan/{permohonan}/upload', [PermohonanController::class, 'uploadFile'])->name('permohonan.upload');
Route::delete('permohonan/{permohonan}/file/{file}', [PermohonanController::class, 'deleteFile'])->name('permohonan.file.destroy');
Route::put('permohonan/{permohonan}/submit', [PermohonanController::class, 'submit'])->name('permohonan.submit');
Route::get('permohonan/file/{uuid}/diskusi', [PermohonanController::class, 'getFileDiskusi'])->name('permohonan.file.diskusi.index');
Route::post('permohonan/file/{uuid}/diskusi', [PermohonanController::class, 'storeFileDiskusi'])->name('permohonan.file.diskusi.store');
Route::put('permohonan/file/{uuid}/review', [PermohonanController::class, 'reviewFile'])->name('permohonan.file.review');
Route::post('permohonan/file/{uuid}/revision', [PermohonanController::class, 'uploadFileRevision'])->name('permohonan.file.revision');

// Monev (Pemohon & Admin)
Route::get('monev', [MonevController::class, 'index'])->name('monev.index');
Route::get('monev/create', [MonevController::class, 'create'])->name('monev.create');
Route::post('monev', [MonevController::class, 'store'])->name('monev.store');
Route::get('monev/{uuid}', [MonevController::class, 'show'])->name('monev.show');
Route::post('monev/{uuid}/review', [MonevController::class, 'review'])->name('monev.review');
