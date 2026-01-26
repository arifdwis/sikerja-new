<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\PenjadwalanController;
use App\Http\Controllers\PembahasanController;
use App\Http\Controllers\PersetujuanController;
use App\Http\Controllers\ValidasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Master\KategoriController;
// use App\Http\Controllers\Master\CorporateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public Landing Pages
Route::controller(\App\Http\Controllers\LandingController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/tentang', 'about')->name('landing.about');
    Route::get('/alur', 'workflow')->name('landing.workflow');
    Route::get('/jenis-kerjasama', 'products')->name('landing.products');
    Route::get('/faq', 'faq')->name('landing.faq');
    // Generic page route
    Route::get('/page/{slug}', 'page')->name('landing.page');
});

// API Routes (Public)
Route::get('/api/kotas-all', function () {
    $provinsis = \App\Models\Provinsi::with('kotas')->orderBy('name')->get();
    return response()->json($provinsis->map(function ($p) {
        return [
            'provinsi' => $p->name,
            'kotas' => $p->kotas->map(function ($k) {
                return [
                    'id' => $k->id,
                    'name' => $k->type . ' ' . $k->name
                ];
            })
        ];
    }));
})->name('api.kotas.all');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'pemohon.profile'])->name('dashboard');

Route::middleware(['auth', 'pemohon.profile'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile/biodata', [ProfileController::class, 'updateBiodata'])->name('profile.biodata.update');
    Route::put('/profile/corporate', [ProfileController::class, 'updateCorporate'])->name('profile.corporate.update');



    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('kategori', KategoriController::class);
        // Route::resource('corporate', CorporateController::class);
        Route::resource('pemohon', \App\Http\Controllers\Master\PemohonController::class);
        Route::resource('slider', \App\Http\Controllers\Master\SliderController::class);
        Route::resource('faq', \App\Http\Controllers\Master\FaqController::class);
        Route::resource('laman', \App\Http\Controllers\Master\LamanController::class);
    });

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

    // Penjadwalan
    Route::resource('penjadwalan', PenjadwalanController::class)->only(['store', 'update', 'destroy']);
    Route::put('penjadwalan/{penjadwalan}/review', [PenjadwalanController::class, 'review'])->name('penjadwalan.review');

    // Persetujuan (Admin)
    Route::resource('persetujuan', PersetujuanController::class)->only(['index', 'show', 'update']);

    // Validasi (Admin)
    Route::resource('validasi', ValidasiController::class)->only(['index', 'show', 'update']);

    // Pembahasan
    Route::resource('pembahasan', PembahasanController::class);

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
});


require __DIR__ . '/auth.php';
