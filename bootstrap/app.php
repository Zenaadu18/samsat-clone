<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ApiKeyAuthenticate;
use App\Http\Middleware\AdminSessionTimeout;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Endpoint legacy compatibility & API PWA dikecualikan dari CSRF
        // karena dipanggil dari klien React SPA via fetch/JSON.
        $middleware->validateCsrfTokens(except: [
            '/api_tax.php',
            '/calculate_tax.php',
            'api/*',
            '/api/*',
        ]);

        // Daftarkan alias middleware
        $middleware->alias([
            'api.key'       => ApiKeyAuthenticate::class,
            'admin.timeout' => AdminSessionTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Kembalikan error 404 dan 422 sebagai JSON untuk request API
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validasi gagal.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Endpoint tidak ditemukan.',
                ], 404);
            }
        });
    })->create();
