<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Http\Middleware\AuthMiddleware;
use App\Models\GenerationConfig;
use App\Models\Subject;

final class SubjectController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        $model = new Subject();
        View::render('subjects/index', ['subjects' => $model->allForUser($this->userId()), 'csrf' => Csrf::token()]);
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        View::render('subjects/form', ['subject' => null, 'configs' => $this->configs(), 'csrf' => Csrf::token()]);
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        $subject = (new Subject())->findForUser((int) ($_GET['id'] ?? 0), $this->userId());
        if ($subject === null) { http_response_code(404); View::render('errors/404'); return; }
        View::render('subjects/form', ['subject' => $subject, 'configs' => $this->configs(), 'csrf' => Csrf::token()]);
    }

    public function save(): void
    {
        AuthMiddleware::handle();
        $this->validateCsrf();
        $title = trim((string) ($_POST['titulo'] ?? ''));
        $order = filter_input(INPUT_POST, 'ordem', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $status = (string) ($_POST['status'] ?? 'pendente');
        if ($title === '' || mb_strlen($title) > 255 || $order === false || !in_array($status, ['pendente', 'processando', 'concluido', 'cancelado'], true)) {
            http_response_code(422); exit('Dados inválidos para o assunto.');
        }
        $configId = $this->nullableInt('configuracao_id');
        if ($configId !== null && (new GenerationConfig())->findForUser($configId, $this->userId()) === null) {
            http_response_code(422); exit('Configuração de geração inválida.');
        }
        $data = ['configuracao_id' => $configId, 'titulo' => $title, 'instrucoes' => $this->nullableText('instrucoes'), 'ordem' => $order, 'status' => $status, 'nao_antes_de' => $this->nullableText('nao_antes_de')];
        $model = new Subject(); $id = (int) ($_POST['id'] ?? 0);
        $id > 0 ? $model->update($id, $this->userId(), $data) : $model->create($this->userId(), $data);
        header('Location: /assuntos'); exit;
    }

    public function bulk(): void
    {
        AuthMiddleware::handle(); $this->validateCsrf();
        $titles = preg_split('/\R/u', (string) ($_POST['titulos'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $model = new Subject(); $order = 0;
        foreach ($titles as $title) {
            $title = trim($title); if ($title === '' || mb_strlen($title) > 255) { continue; }
            $model->create($this->userId(), ['configuracao_id' => null, 'titulo' => $title, 'instrucoes' => null, 'ordem' => $order++, 'status' => 'pendente', 'nao_antes_de' => null]);
        }
        header('Location: /assuntos'); exit;
    }

    public function delete(): void
    {
        AuthMiddleware::handle(); $this->validateCsrf();
        (new Subject())->delete((int) ($_POST['id'] ?? 0), $this->userId()); header('Location: /assuntos'); exit;
    }

    private function configs(): array
    {
        return (new GenerationConfig())->allForUser($this->userId());
    }
    private function userId(): int { return (int) $_SESSION['user']['id']; }
    private function nullableInt(string $key): ?int { $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT); return $value === false || $value === null || $value < 1 ? null : $value; }
    private function nullableText(string $key): ?string { $value = trim((string) ($_POST[$key] ?? '')); return $value === '' ? null : $value; }
    private function validateCsrf(): void { if (!Csrf::validate($_POST['_csrf'] ?? null)) { http_response_code(419); exit('Token CSRF inválido.'); } }
}
