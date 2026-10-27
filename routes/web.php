<?php

declare(strict_types=1);

use App\Core\Router;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

$router = new Router();
$router->get('/', [DashboardController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);

return $router;
