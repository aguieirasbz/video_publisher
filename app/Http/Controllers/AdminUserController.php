<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Http\Middleware\AuthMiddleware;
use App\Models\User;

final class AdminUserController
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function index(): void
    {
        AuthMiddleware::handle();
        View::render('users/index', ['users' => $this->users->allAdmins(), 'csrf' => Csrf::token()]);
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        View::render('users/form', ['user' => null, 'csrf' => Csrf::token()]);
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        $user = $this->users->findAdmin((int) ($_GET['id'] ?? 0));
        if ($user === null) { http_response_code(404); View::render('errors/404'); return; }
        View::render('users/form', ['user' => $user, 'csrf' => Csrf::token()]);
    }

    public function save(): void
    {
        AuthMiddleware::handle();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { http_response_code(419); exit('Token CSRF inválido.'); }
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['nome'] ?? ''));
        $email = filter_var(strtolower(trim((string) ($_POST['email'] ?? ''))), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        if ($name === '' || mb_strlen($name) > 150 || $email === false || $this->users->emailInUse($email, $id ?: null) || ($id === 0 && strlen($password) < 12) || ($id > 0 && $password !== '' && strlen($password) < 12)) {
            http_response_code(422); exit('Dados inválidos. A senha deve ter pelo menos 12 caracteres.');
        }
        if ($id > 0) {
            $active = isset($_POST['ativo']);
            if ($id === $this->userId() && !$active) { http_response_code(422); exit('O administrador atualmente conectado não pode ser desativado.'); }
            $this->users->updateAdmin($id, $name, $email, $active, $password === '' ? null : $password);
        } else {
            $this->users->createAdmin($name, $email, $password);
        }
        header('Location: /usuarios'); exit;
    }

    private function userId(): int { return (int) $_SESSION['user']['id']; }
}
