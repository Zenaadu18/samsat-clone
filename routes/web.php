<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Api\UserAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes — BAPENDA NTT Samsat Clone & Mobile PWA
|--------------------------------------------------------------------------
*/

// -------------------------------------------------------------------------
// PWA Auth & Banner API Fallback Routes
// Menjamin endpoint auth tetap aktif jika routes/api.php di hosting belum termuat
// Mendukung akses langsung maupun subfolder (misal /clone2/public/)
// -------------------------------------------------------------------------
foreach (['', 'clone2/public/', 'Clone2/public/', 'public/'] as $subPrefix) {
    Route::prefix($subPrefix . 'api/pwa/auth')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
        ->middleware('throttle:120,1')
        ->group(function () {
            Route::match(['post', 'get'], '/login', [UserAuthController::class, 'login']);
            Route::get('/me', [UserAuthController::class, 'me']);
            Route::post('/logout', [UserAuthController::class, 'logout']);
        });

    Route::prefix($subPrefix . 'api/pwa')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
        ->middleware('throttle:60,1')
        ->group(function () {
            Route::get('/banner', [UserAuthController::class, 'banner']);
            Route::get('/user/activities', [UserAuthController::class, 'activities']);
        });
}

// -------------------------------------------------------------------------
// Panel Admin (Full Feature Controller)
// -------------------------------------------------------------------------
Route::prefix('admin')->middleware(['throttle:120,1', 'admin.timeout'])->group(function () {
    Route::match(['get', 'post'], '/', [AdminController::class, 'index'])->name('admin');
    Route::post('/save-config', [AdminController::class, 'saveConfig'])->name('admin.save_config');
    Route::post('/save-pkb', [AdminController::class, 'savePkb'])->name('admin.save_pkb');
    Route::post('/save-stnk', [AdminController::class, 'saveStnk'])->name('admin.save_stnk');
    Route::post('/save-swdkllj', [AdminController::class, 'saveSwdkllj'])->name('admin.save_swdkllj');
    Route::post('/save-klasifikasi', [AdminController::class, 'saveKlasifikasi'])->name('admin.save_klasifikasi');
    Route::post('/delete-klasifikasi', [AdminController::class, 'deleteKlasifikasi'])->name('admin.delete_klasifikasi');
    Route::post('/search-njkb', [AdminController::class, 'searchNjkb'])->name('admin.search_njkb');
    Route::post('/save-njkb', [AdminController::class, 'saveNjkbItem'])->name('admin.save_njkb');
    Route::post('/delete-njkb', [AdminController::class, 'deleteNjkbItem'])->name('admin.delete_njkb');

    // Manajemen Pengguna
    Route::post('/save-user', [AdminController::class, 'saveUser'])->name('admin.save_user');
    Route::post('/toggle-block-user', [AdminController::class, 'toggleBlockUser'])->name('admin.toggle_block_user');
    Route::post('/delete-user', [AdminController::class, 'deleteUser'])->name('admin.delete_user');
    Route::post('/reset-device-user', [AdminController::class, 'resetUserDevice'])->name('admin.reset_device_user');

    // Kontrol Banner Informasi (Multi-Banner: Gambar/Teks/Kombinasi)
    Route::post('/save-banner-item', [AdminController::class, 'saveBannerItem'])->name('admin.save_banner_item');
    Route::post('/toggle-banner-status', [AdminController::class, 'toggleBannerStatus'])->name('admin.toggle_banner_status');
    Route::post('/delete-banner-item', [AdminController::class, 'deleteBannerItem'])->name('admin.delete_banner_item');

    // Audit Activity Logs
    Route::get('/activity-logs', [AdminController::class, 'getActivityLogs'])->name('admin.activity_logs');

    Route::post('/change-password', [AdminController::class, 'changePassword'])->name('admin.change_password');
    Route::post('/clear-cache', [AdminController::class, 'clearSystemCache'])->name('admin.clear_cache');
    Route::get('/logout', [AdminController::class, 'logout'])->name('admin.logout');
});

// -------------------------------------------------------------------------
// Legacy Compatibility Endpoints
// -------------------------------------------------------------------------
Route::middleware(['throttle:30,1'])->group(function () {
    Route::match(['get', 'post'], '/api_tax.php', [ApiController::class, 'apiTax']);
    Route::match(['get', 'post'], '/calculate_tax.php', [ApiController::class, 'calculateTax']);
    Route::match(['get', 'post'], '/cek_njkb.php', [PageController::class, 'cekNjkb']);
    Route::match(['get', 'post'], '/pajak_massal.php', [PageController::class, 'pajakMassal']);
});

// -------------------------------------------------------------------------
// PWA Frontend (React Single Page App)
// -------------------------------------------------------------------------
Route::get('/{any?}', function () {
    return view('pwa');
})->where('any', '^(?!admin|api|.*api|build|icons|assets|storage).*$')->name('pwa.app');
