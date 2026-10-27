<?php

declare(strict_types=1);

use App\Core\View;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();
$app = require dirname(__DIR__) . '/config/app.php';
date_default_timezone_set($app['timezone']);
session_name($app['session_name']);
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();

if ($app['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

try {
    $router = require dirname(__DIR__) . '/routes/web.php';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', rtrim($path, '/') ?: '/');
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);
    if ($app['debug']) {
        echo htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
    } else {
        View::render('errors/500');
    }
}
