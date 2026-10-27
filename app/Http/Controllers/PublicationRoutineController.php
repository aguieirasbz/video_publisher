<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Http\Middleware\AuthMiddleware;
use App\Models\Channel;
use App\Models\GenerationConfig;
use App\Models\PublicationRoutine;

final class PublicationRoutineController
{
    private PublicationRoutine $routines;

    public function __construct()
    {
        $this->routines = new PublicationRoutine();
    }

    public function index(): void
    {
        AuthMiddleware::handle();
        View::render('routines/index', ['routines' => $this->routines->allForUser($this->userId()), 'csrf' => Csrf::token()]);
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        View::render('routines/form', $this->formData(null));
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        $routine = $this->routines->findForUser((int) ($_GET['id'] ?? 0), $this->userId());
        if ($routine === null) { http_response_code(404); View::render('errors/404'); return; }
        View::render('routines/form', $this->formData($routine));
    }

    public function save(): void
    {
        AuthMiddleware::handle();
        $this->validateCsrf();
        $userId = $this->userId();
        $channelId = $this->positiveInt('canal_id');
        $configId = $this->nullablePositiveInt('configuracao_id');
        $name = trim((string) ($_POST['nome'] ?? ''));
        $videosPerDay = filter_input(INPUT_POST, 'videos_por_dia', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]);
        $time = trim((string) ($_POST['horario_local'] ?? ''));
        $timezone = trim((string) ($_POST['fuso_horario'] ?? 'America/Sao_Paulo'));
        if ($channelId === null || !$this->channelBelongsToUser($channelId, $userId) || ($configId !== null && !$this->configBelongsToUser($configId, $userId)) || $name === '' || mb_strlen($name) > 120 || $videosPerDay === false || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) || !in_array($timezone, timezone_identifiers_list(), true)) {
            http_response_code(422); exit('Dados inválidos para a rotina.');
        }
        $data = ['canal_id' => $channelId, 'configuracao_id' => $configId, 'nome' => $name, 'videos_por_dia' => $videosPerDay, 'horario_local' => $time . ':00', 'fuso_horario' => $timezone, 'ativo' => isset($_POST['ativo']) ? 1 : 0];
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) { $this->routines->update($id, $userId, $data); } else { $this->routines->create($data); }
        header('Location: /rotinas'); exit;
    }

    public function delete(): void
    {
        AuthMiddleware::handle(); $this->validateCsrf();
        $this->routines->delete((int) ($_POST['id'] ?? 0), $this->userId());
        header('Location: /rotinas'); exit;
    }

    private function formData(?array $routine): array
    {
        $userId = $this->userId();
        return ['routine' => $routine, 'channels' => (new Channel())->allForUser($userId), 'configs' => (new GenerationConfig())->allForUser($userId), 'csrf' => Csrf::token()];
    }

    private function channelBelongsToUser(int $id, int $userId): bool { return (new Channel())->findForUser($id, $userId) !== null; }
    private function configBelongsToUser(int $id, int $userId): bool { return (new GenerationConfig())->findForUser($id, $userId) !== null; }
    private function positiveInt(string $key): ?int { $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT); return $value === false || $value === null || $value < 1 ? null : $value; }
    private function nullablePositiveInt(string $key): ?int { return $this->positiveInt($key); }
    private function userId(): int { return (int) $_SESSION['user']['id']; }
    private function validateCsrf(): void { if (!Csrf::validate($_POST['_csrf'] ?? null)) { http_response_code(419); exit('Token CSRF inválido.'); } }
}
