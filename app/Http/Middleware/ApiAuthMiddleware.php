<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\JsonResponse;

final class ApiAuthMiddleware
{
    public static function handle(): void
    {
        if (!isset($_SESSION['user']) || ($_SESSION['user']['perfil'] ?? '') !== 'admin') {
            JsonResponse::send(['error' => 'Autenticação administrativa necessária.'], 401);
            exit;
        }
    }
}
