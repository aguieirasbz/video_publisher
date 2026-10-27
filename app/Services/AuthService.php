<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final class AuthService
{
    public function __construct(private readonly User $users = new User())
    {
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->users->findActiveByEmail(mb_strtolower(trim($email)));
        if ($user === null || !(bool) $user['ativo'] || !password_verify($password, $user['senha_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        unset($user['senha_hash']);
        $_SESSION['user'] = $user;
        $this->users->touchLastLogin((int) $user['id']);

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
    }
}
