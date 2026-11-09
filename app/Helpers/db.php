<?php
// app/Helpers/db.php
global $conn;

$isAppBound = function_exists('app') && (app() instanceof \Illuminate\Contracts\Foundation\Application) && app()->bound('config');

$host = ($isAppBound ? config('database.connections.mysql.host') : null)
    ?: (env('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? '127.0.0.1')));
$port = (int)(($isAppBound ? config('database.connections.mysql.port') : null)
    ?: (env('DB_PORT') ?: ($_ENV['DB_PORT'] ?? ($_SERVER['DB_PORT'] ?? 3306))));
$user = ($isAppBound ? config('database.connections.mysql.username') : null)
    ?: (env('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? ($_SERVER['DB_USERNAME'] ?? 'root')));
$pass = ($isAppBound ? config('database.connections.mysql.password') : null)
    ?? (env('DB_PASSWORD') ?? ($_ENV['DB_PASSWORD'] ?? ($_SERVER['DB_PASSWORD'] ?? '')));
$db   = ($isAppBound ? config('database.connections.mysql.database') : null)
    ?: (env('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? ($_SERVER['DB_DATABASE'] ?? 'samsat_db')));

mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = @new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        $conn = null;
    }
} catch (Throwable $e) {
    $conn = null;
}
