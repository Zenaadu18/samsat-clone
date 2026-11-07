<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Tentukan apakah aplikasi sedang dalam mode maintenance...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Daftarkan Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Normalisasi huruf besar/kecil subfolder URL pada server Windows/XAMPP
// untuk mencegah kegagalan Symfony pathinfo matching saat diakses dengan huruf kecil (misal /clone2/ vs /Clone2/)
if (isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
    $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if ($scriptDir !== '') {
        $_SERVER['REQUEST_URI'] = preg_replace(
            '#^/' . preg_quote($scriptDir, '#') . '(?=/|$)#i',
            '/' . $scriptDir,
            $_SERVER['REQUEST_URI']
        );
    }
}

// Bootstrap Laravel dan tangani request...
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());

