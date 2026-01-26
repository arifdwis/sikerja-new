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

    // PEMOHON Routings
    require __DIR__ . '/pemohon.php';

    // TKKSD Routings
    require __DIR__ . '/tkksd.php';

    // ADMIN Routings
    require __DIR__ . '/admin.php';

});


require __DIR__ . '/auth.php';
