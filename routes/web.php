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
    // Infografis publik (Req 15)
    Route::get('/infografis', 'infografis')->name('landing.infografis');
    // Generic page route
    Route::get('/page/{slug}', 'page')->name('landing.page');
});

// Chatbot API (Public - Throttled)
Route::middleware('throttle:15,1')->group(function () {
    Route::post('/api/chatbot', [\App\Http\Controllers\ChatbotController::class, 'chat'])->name('api.chatbot');
    Route::get('/api/chatbot/{requestId}', [\App\Http\Controllers\ChatbotController::class, 'status'])->name('api.chatbot.status');
});

// API Routes (Public - Throttled)
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
})->middleware('throttle:30,1')->name('api.kotas.all');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'ensure.email', 'pemohon.profile'])->name('dashboard');

Route::middleware(['auth', 'ensure.email', 'pemohon.profile'])->group(function () {

    // PEMOHON Routings
    require __DIR__ . '/pemohon.php';

    // TKKSD Routings
    require __DIR__ . '/tkksd.php';

    // ADMIN Routings
    require __DIR__ . '/admin.php';

});


require __DIR__ . '/auth.php';

// Template dokumen tetap (Req 3) — tersedia untuk semua user terautentikasi
Route::middleware('auth')->group(function () {
    Route::get('/templates/{type}', [\App\Http\Controllers\TemplateController::class, 'download'])
        ->name('template.download');
    Route::get('/templates', [\App\Http\Controllers\TemplateController::class, 'list'])
        ->name('template.list');
});
