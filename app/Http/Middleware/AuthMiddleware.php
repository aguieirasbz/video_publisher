<?php

declare(strict_types=1);

namespace App\Http\Middleware;

final class AuthMiddleware
{
    public static function handle(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (($_SESSION['user']['perfil'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Acesso restrito a administradores.');
        }
    }
}
