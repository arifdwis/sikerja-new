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
// Laporan
Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/statistik', 'statistik')->name('statistik');
    Route::get('/akumulatif', 'akumulatif')->name('akumulatif');
    Route::get('/rekap-mitra', 'rekapMitra')->name('rekap-mitra');
    Route::get('/persentase-opd', 'persentaseOpd')->name('persentase-opd');
    Route::get('/persentase-bidang', 'persentaseBidang')->name('persentase-bidang');
    Route::get('/cetak-detail/{uuid}', 'cetakDetail')->name('cetak-detail');
    Route::get('/cetak-semua', 'cetakSemua')->name('cetak-semua');
});

// Monev (Admin fills evaluation for expired kerjasama)
Route::prefix('monev')->name('monev.')->controller(\App\Http\Controllers\MonevController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::get('/export', 'export')->name('export');
    Route::post('/', 'store')->name('store');
    Route::get('/{uuid}', 'show')->name('show');
    Route::post('/{uuid}/review', 'review')->name('review');
    Route::post('/{uuid}/notify-pemohon', 'notifyPemohon')->name('notify-pemohon');
});

// Settings (Admin)
Route::get('pengaturan', function () {
    return redirect()->route('settings.users.index');
})->name('pengaturan');

Route::prefix('settings')->name('settings.')->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('roles', App\Http\Controllers\RoleController::class);

    // AI Feedback Log (superadmin only)
    Route::get('ai-feedback', [App\Http\Controllers\ChatbotFeedbackLogController::class, 'index'])
        ->middleware('role:superadmin')
        ->name('ai-feedback.index');

    // Roles Permission
    Route::get('roles/{role}/permission', [App\Http\Controllers\RoleController::class, 'permission'])->name('roles.permission');
    Route::put('roles/{role}/permission', [App\Http\Controllers\RoleController::class, 'updatePermission'])->name('roles.permission.update');

    Route::resource('permissions', App\Http\Controllers\PermissionController::class);
    Route::resource('menu', App\Http\Controllers\MenuController::class);
    Route::post('menu/reorder', [App\Http\Controllers\MenuController::class, 'reorder'])->name('menu.reorder');
    Route::resource('log-activity', App\Http\Controllers\LogActivityController::class)->only(['index', 'show', 'destroy']);
});

// PKS workflow (Admin) - Req 9 & 10
Route::post('permohonan/{uuid}/pks/admin', [\App\Http\Controllers\PksController::class, 'adminUpload'])
    ->name('permohonan.pks.admin');
Route::put('permohonan/{uuid}/pks/approve', [\App\Http\Controllers\PksController::class, 'approve'])
    ->name('permohonan.pks.approve');

// Validasi dokumen tertandatangani (Admin) - Req 9
Route::put('permohonan/{uuid}/ttd/validate', [\App\Http\Controllers\PenandatangananController::class, 'validateTtd'])
    ->name('permohonan.ttd.validate');

// Master OPD (Admin only) - Req 12.4
Route::resource('master/opd', \App\Http\Controllers\Master\OpdController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->names('master.opd');

// Kerjasama Manual (Admin only) - Req 17
Route::resource('kerjasama-manual', \App\Http\Controllers\KerjasamaManualController::class)
    ->parameters(['kerjasama-manual' => 'uuid']);

// Monev manual (Admin only) - Req 16
Route::post('monev/manual', [\App\Http\Controllers\MonevController::class, 'storeManual'])
    ->name('monev.manual.store');


// Tahap Penandatanganan (status 3, 4, 5) — admin lihat list permohonan yang sedang dalam proses tanda tangan
Route::get('penandatanganan', [\App\Http\Controllers\PermohonanController::class, 'index'])
    ->name('penandatanganan.index');

// Tahap Pelaksanaan (status 6) — kerjasama yang sedang aktif berjalan
Route::get('pelaksanaan', [\App\Http\Controllers\PermohonanController::class, 'index'])
    ->name('pelaksanaan.index');
