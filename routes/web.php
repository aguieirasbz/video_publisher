<?php

declare(strict_types=1);

use App\Core\Router;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GenerationConfigController;
use App\Http\Controllers\SubjectController;

$router = new Router();
$router->get('/', [DashboardController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/configuracoes', [GenerationConfigController::class, 'index']);
$router->get('/configuracoes/nova', [GenerationConfigController::class, 'create']);
$router->get('/configuracoes/editar', [GenerationConfigController::class, 'edit']);
$router->post('/configuracoes/salvar', [GenerationConfigController::class, 'save']);
$router->post('/configuracoes/excluir', [GenerationConfigController::class, 'delete']);
$router->get('/assuntos', [SubjectController::class, 'index']);
$router->get('/assuntos/novo', [SubjectController::class, 'create']);
$router->get('/assuntos/editar', [SubjectController::class, 'edit']);
$router->post('/assuntos/salvar', [SubjectController::class, 'save']);
$router->post('/assuntos/lote', [SubjectController::class, 'bulk']);
$router->post('/assuntos/excluir', [SubjectController::class, 'delete']);

return $router;
