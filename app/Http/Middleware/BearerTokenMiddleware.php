<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\JsonResponse;
use App\Models\ApiToken;

final class BearerTokenMiddleware
{
    public static function user(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            JsonResponse::send(['error' => 'Bearer token necessário.'], 401);
            exit;
        }
        $token = (new ApiToken())->authenticate(trim($matches[1]));
        if ($token === null || $token['perfil'] !== 'admin') {
            JsonResponse::send(['error' => 'Token inválido, expirado ou revogado.'], 401);
            exit;
        }
        return $token;
    }

    public static function requireScope(array $token, string $requiredScope): void
    {
        $scopes = array_filter(array_map('trim', explode(',', (string) $token['escopos'])));
        if (!in_array($requiredScope, $scopes, true)) {
            JsonResponse::send(['error' => 'O token não possui o escopo necessário.', 'required_scope' => $requiredScope], 403);
            exit;
        }
    }
}
