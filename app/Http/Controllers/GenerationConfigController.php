<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Http\Middleware\AuthMiddleware;
use App\Models\GenerationConfig;

final class GenerationConfigController
{
    private GenerationConfig $configs;

    public function __construct()
    {
        $this->configs = new GenerationConfig();
    }

    public function index(): void
    {
        AuthMiddleware::handle();
        View::render('configs/index', ['configs' => $this->configs->allForUser($this->userId()), 'csrf' => Csrf::token()]);
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        View::render('configs/form', ['config' => null, 'csrf' => Csrf::token()]);
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        $config = $this->configs->findForUser((int) ($_GET['id'] ?? 0), $this->userId());
        if ($config === null) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }
        View::render('configs/form', ['config' => $config, 'csrf' => Csrf::token()]);
    }

    public function save(): void
    {
        AuthMiddleware::handle();
        $this->validateCsrf();
        $data = $this->validatedData();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->configs->update($id, $this->userId(), $data);
        } else {
            $this->configs->create($this->userId(), $data);
        }
        header('Location: /configuracoes');
        exit;
    }

    public function delete(): void
    {
        AuthMiddleware::handle();
        $this->validateCsrf();
        $this->configs->delete((int) ($_POST['id'] ?? 0), $this->userId());
        header('Location: /configuracoes');
        exit;
    }

    private function validatedData(): array
    {
        $name = trim((string) ($_POST['nome'] ?? ''));
        $language = trim((string) ($_POST['idioma'] ?? 'pt-BR'));
        $duration = filter_input(INPUT_POST, 'duracao_alvo_segundos', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 3600]]);
        $fps = filter_input(INPUT_POST, 'fps', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 120]]);
        if ($name === '' || mb_strlen($name) > 120 || $language === '' || mb_strlen($language) > 15 || $duration === false || $fps === false) {
            http_response_code(422);
            exit('Dados inválidos para a configuração.');
        }

        $extras = $this->nullablePost('configuracoes_extras');
        if ($extras !== null && json_decode($extras, true) === null && json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(422);
            exit('Preferências de geração devem estar em JSON válido.');
        }

        return [
            'nome' => $name, 'duracao' => $duration, 'largura' => 1080, 'altura' => 1920, 'fps' => $fps,
            'idioma' => $language, 'roteiro' => $this->limitedNullablePost('provedor_roteiro', 70),
            'imagem' => $this->limitedNullablePost('provedor_imagem', 70), 'voz' => $this->limitedNullablePost('provedor_voz', 70), 'voz_id' => $this->limitedNullablePost('voz_id', 120),
            'legendas' => trim((string) ($_POST['estilo_legendas'] ?? 'central')), 'movimento' => trim((string) ($_POST['estilo_movimento'] ?? 'ken_burns')),
            'extras' => $extras, 'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];
    }

    private function nullablePost(string $key): ?string
    {
        $value = trim((string) ($_POST[$key] ?? ''));
        return $value === '' ? null : $value;
    }

    private function limitedNullablePost(string $key, int $maxLength): ?string
    {
        $value = $this->nullablePost($key);
        if ($value !== null && mb_strlen($value) > $maxLength) {
            http_response_code(422);
            exit('Valor excede o limite permitido.');
        }
        return $value;
    }

    private function validateCsrf(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Token CSRF inválido.');
        }
    }

    private function userId(): int
    {
        return (int) $_SESSION['user']['id'];
    }
}
