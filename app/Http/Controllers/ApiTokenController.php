<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Http\Middleware\AuthMiddleware;
use App\Models\ApiToken;

final class ApiTokenController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        View::render('tokens/index', ['tokens' => (new ApiToken())->allForUser($this->userId()), 'csrf' => Csrf::token()]);
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        View::render('tokens/form', ['csrf' => Csrf::token()]);
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { http_response_code(419); exit('Token CSRF inválido.'); }
        $name = trim((string) ($_POST['nome'] ?? ''));
        $scopes = trim((string) ($_POST['escopos'] ?? ''));
        $expires = trim((string) ($_POST['expira_em'] ?? ''));
        if ($name === '' || mb_strlen($name) > 120 || mb_strlen($scopes) > 500 || ($expires !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expires))) {
            http_response_code(422); exit('Dados inválidos para o token.');
        }
        $plainToken = (new ApiToken())->create($this->userId(), $name, $scopes, $expires === '' ? null : $expires . ' 23:59:59');
        View::render('tokens/created', ['token' => $plainToken]);
    }

    public function revoke(): void
    {
        AuthMiddleware::handle();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { http_response_code(419); exit('Token CSRF inválido.'); }
        (new ApiToken())->revoke((int) ($_POST['id'] ?? 0), $this->userId());
        header('Location: /tokens'); exit;
    }

    private function userId(): int { return (int) $_SESSION['user']['id']; }
}
