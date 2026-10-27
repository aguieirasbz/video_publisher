<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Services\AuthService;

final class AuthController
{
    public function showLogin(): void
    {
        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }

        View::render('auth/login', ['csrf' => Csrf::token()]);
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Token CSRF inválido.');
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        if ($email === false || $password === '') {
            View::render('auth/login', ['csrf' => Csrf::token(), 'error' => 'Informe e-mail e senha válidos.']);
            return;
        }

        if (!(new AuthService())->attempt($email, $password)) {
            View::render('auth/login', ['csrf' => Csrf::token(), 'error' => 'Credenciais inválidas ou usuário inativo.']);
            return;
        }

        header('Location: /dashboard');
        exit;
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Token CSRF inválido.');
        }

        (new AuthService())->logout();
        header('Location: /login');
        exit;
    }
}
