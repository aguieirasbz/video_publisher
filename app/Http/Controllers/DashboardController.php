<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\View;
use App\Http\Middleware\AuthMiddleware;
use App\Models\DashboardStats;

final class DashboardController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        View::render('dashboard/index', [
            'user' => $_SESSION['user'],
            'stats' => (new DashboardStats())->forUser((int) $_SESSION['user']['id']),
        ]);
    }
}
