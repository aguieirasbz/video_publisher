<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Http\Middleware\AuthMiddleware;
use App\Models\Channel;

final class ChannelController
{
    private Channel $channels;

    public function __construct()
    {
        $this->channels = new Channel();
    }

    public function index(): void
    {
        AuthMiddleware::handle();
        View::render('channels/index', ['channels' => $this->channels->allForUser($this->userId()), 'csrf' => Csrf::token()]);
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        View::render('channels/form', ['channel' => null, 'platforms' => $this->channels->platforms(), 'csrf' => Csrf::token()]);
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        $channel = $this->channels->findForUser((int) ($_GET['id'] ?? 0), $this->userId());
        if ($channel === null) { http_response_code(404); View::render('errors/404'); return; }
        View::render('channels/form', ['channel' => $channel, 'platforms' => $this->channels->platforms(), 'csrf' => Csrf::token()]);
    }

    public function save(): void
    {
        AuthMiddleware::handle();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { http_response_code(419); exit('Token CSRF inválido.'); }
        $platformId = filter_input(INPUT_POST, 'plataforma_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $name = trim((string) ($_POST['nome'] ?? ''));
        $timezone = trim((string) ($_POST['fuso_horario'] ?? 'America/Sao_Paulo'));
        if ($platformId === false || !$this->channels->platformExists($platformId) || $name === '' || mb_strlen($name) > 150 || !in_array($timezone, timezone_identifiers_list(), true)) {
            http_response_code(422); exit('Dados inválidos para o canal.');
        }
        $data = ['plataforma_id' => $platformId, 'nome' => $name, 'identificador_externo' => $this->nullablePost('identificador_externo'), 'fuso_horario' => $timezone, 'ativo' => isset($_POST['ativo']) ? 1 : 0];
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) { $this->channels->update($id, $this->userId(), $data); } else { $this->channels->create($this->userId(), $data); }
        header('Location: /canais'); exit;
    }

    private function userId(): int { return (int) $_SESSION['user']['id']; }
    private function nullablePost(string $key): ?string { $value = trim((string) ($_POST[$key] ?? '')); return $value === '' ? null : $value; }
}
