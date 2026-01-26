<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersetujuanController;
use App\Http\Controllers\ValidasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Master\KategoriController;

// Master Data
Route::prefix('master')->name('master.')->group(function () {
    Route::resource('kategori', KategoriController::class);
    // Route::resource('corporate', CorporateController::class);
    Route::resource('pemohon', \App\Http\Controllers\Master\PemohonController::class);
    Route::resource('slider', \App\Http\Controllers\Master\SliderController::class);
    Route::resource('faq', \App\Http\Controllers\Master\FaqController::class);
    Route::resource('laman', \App\Http\Controllers\Master\LamanController::class);
});

// Persetujuan (Admin)
Route::resource('persetujuan', PersetujuanController::class)->only(['index', 'show', 'update']);

// Validasi (Admin)
Route::resource('validasi', ValidasiController::class)->only(['index', 'show', 'update']);

// Laporan
Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');

// Settings (Admin)
Route::get('pengaturan', function () {
    return redirect()->route('settings.users.index');
})->name('pengaturan');

Route::prefix('settings')->name('settings.')->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('roles', App\Http\Controllers\RoleController::class);

    // Roles Permission
    Route::get('roles/{role}/permission', [App\Http\Controllers\RoleController::class, 'permission'])->name('roles.permission');
    Route::put('roles/{role}/permission', [App\Http\Controllers\RoleController::class, 'updatePermission'])->name('roles.permission.update');

    Route::resource('permissions', App\Http\Controllers\PermissionController::class);
    Route::resource('menu', App\Http\Controllers\MenuController::class);
    Route::post('menu/reorder', [App\Http\Controllers\MenuController::class, 'reorder'])->name('menu.reorder');
    Route::resource('log-activity', App\Http\Controllers\LogActivityController::class)->only(['index', 'show', 'destroy']);
});
