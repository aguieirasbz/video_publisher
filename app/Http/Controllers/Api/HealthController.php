<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\Database;
use App\Core\JsonResponse;

final class HealthController
{
    public function index(): void
    {
        try {
            Database::connection()->query('SELECT 1');
            JsonResponse::send(['status' => 'ok', 'database' => 'ok']);
        } catch (\Throwable $exception) {
            error_log((string) $exception);
            JsonResponse::send(['status' => 'error', 'database' => 'unavailable'], 503);
        }
    }
}
