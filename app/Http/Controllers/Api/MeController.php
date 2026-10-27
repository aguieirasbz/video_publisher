<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\JsonResponse;
use App\Http\Middleware\BearerTokenMiddleware;

final class MeController
{
    public function index(): void
    {
        $user = BearerTokenMiddleware::user();
        JsonResponse::send(['data' => ['id' => (int) $user['usuario_id'], 'nome' => $user['nome'], 'email' => $user['email'], 'escopos' => $user['escopos']]]);
    }
}
