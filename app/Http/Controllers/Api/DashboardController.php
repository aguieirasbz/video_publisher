<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\JsonResponse;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Models\DashboardStats;

final class DashboardController
{
    public function index(): void
    {
        ApiAuthMiddleware::handle();
        JsonResponse::send([
            'data' => (new DashboardStats())->forUser((int) $_SESSION['user']['id']),
        ]);
    }
}
