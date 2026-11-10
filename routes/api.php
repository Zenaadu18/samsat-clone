<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TaxController;
use App\Http\Controllers\Api\PwaApiController;
use App\Http\Controllers\Api\UserAuthController;

/*
|--------------------------------------------------------------------------
| API Routes — BAPENDA NTT Samsat Clone & Mobile PWA
|--------------------------------------------------------------------------
|
| Semua route di sini diawali dengan /api secara otomatis oleh Laravel.
|
*/

// -------------------------------------------------------------------------
// PWA User Authentication & Info Banner
// -------------------------------------------------------------------------
Route::prefix('pwa/auth')->middleware('throttle:120,1')->group(function () {
    Route::post('/login', [UserAuthController::class, 'login'])->name('pwa.auth.login');
    Route::get('/me', [UserAuthController::class, 'me'])->name('pwa.auth.me');
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('pwa.auth.logout');
});

Route::prefix('pwa')->middleware('throttle:60,1')->group(function () {
    Route::get('/banner', [UserAuthController::class, 'banner'])->name('pwa.banner');
    Route::get('/user/activities', [UserAuthController::class, 'activities'])->name('pwa.user.activities');

    Route::post('/check', [PwaApiController::class, 'check'])->name('pwa.check');
    Route::post('/simulate', [PwaApiController::class, 'simulate'])->name('pwa.simulate');
    Route::post('/simulate-bbn2', [PwaApiController::class, 'simulateBbn2'])->name('pwa.simulate_bbn2');
    Route::get('/njkb/nopol', [PwaApiController::class, 'checkNjkbByNopol'])->name('pwa.njkb.nopol');
    Route::get('/njkb', [PwaApiController::class, 'searchNjkb'])->name('pwa.njkb');
    Route::get('/config', [PwaApiController::class, 'config'])->name('pwa.config');
    Route::post('/pro-ntt/bayar', [PwaApiController::class, 'proNttBayar'])->name('pwa.pro_ntt.bayar');
    Route::post('/pro-ntt/status', [PwaApiController::class, 'proNttStatus'])->name('pwa.pro_ntt.status');
    Route::get('/pro-ntt/history', [PwaApiController::class, 'proNttHistory'])->name('pwa.pro_ntt.history');
});

// -------------------------------------------------------------------------
// Health check publik
// -------------------------------------------------------------------------
Route::get('/status', [TaxController::class, 'status'])
    ->middleware('throttle:120,1')
    ->name('api.status');

// -------------------------------------------------------------------------
// Grup API v1 — memerlukan API Key & rate limit
// -------------------------------------------------------------------------
Route::prefix('v1')
    ->middleware(['api.key', 'throttle:60,1'])
    ->name('api.v1.')
    ->group(function () {
        Route::post('/hitung-pajak', [TaxController::class, 'hitungPajak'])->name('hitung-pajak');
        Route::get('/cek-njkb', [TaxController::class, 'cekNjkb'])->name('cek-njkb');
    });
