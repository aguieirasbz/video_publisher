<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\JsonResponse;
use App\Http\Middleware\BearerTokenMiddleware;
use App\Models\Execution;

final class ExecutionController
{
    public function pendingSubjects(): void
    {
        $token = BearerTokenMiddleware::user();
        BearerTokenMiddleware::requireScope($token, 'assuntos:read');
        JsonResponse::send(['data' => (new Execution())->pendingSubjects((int) $token['usuario_id'])]);
    }

    public function start(): void
    {
        $token = BearerTokenMiddleware::user();
        BearerTokenMiddleware::requireScope($token, 'execucoes:write');
        $input = $this->input();
        $subjectId = filter_var($input['assunto_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $idempotency = trim((string) ($input['idempotencia_chave'] ?? ''));
        if ($subjectId === false || $idempotency === '' || mb_strlen($idempotency) > 190) { JsonResponse::send(['error' => 'assunto_id e idempotencia_chave são obrigatórios.'], 422); return; }
        try { $execution = (new Execution())->start((int) $token['usuario_id'], $subjectId, $idempotency); } catch (\InvalidArgumentException|\RuntimeException $exception) { JsonResponse::send(['error' => $exception->getMessage()], 409); return; }
        JsonResponse::send(['data' => ['execucao_id' => $execution['id'], 'assunto_id' => $execution['assunto_id'], 'titulo' => $execution['titulo'], 'bloqueio_token' => $execution['bloqueio_token'], 'status' => 'iniciada']], 201);
    }

    public function log(): void
    {
        $token = BearerTokenMiddleware::user(); BearerTokenMiddleware::requireScope($token, 'execucoes:write'); $input = $this->input();
        $id = filter_var($input['execucao_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); $message = trim((string) ($input['mensagem'] ?? ''));
        if ($id === false || $message === '') { JsonResponse::send(['error' => 'execucao_id e mensagem são obrigatórios.'], 422); return; }
        $ok = (new Execution())->addLog((int) $token['usuario_id'], $id, trim((string) ($input['nivel'] ?? 'info')), $this->nullable($input['etapa'] ?? null), $message);
        JsonResponse::send($ok ? ['data' => ['registrado' => true]] : ['error' => 'Execução não encontrada.'], $ok ? 201 : 404);
    }

    public function finish(): void
    {
        $token = BearerTokenMiddleware::user(); BearerTokenMiddleware::requireScope($token, 'execucoes:write'); $input = $this->input();
        $id = filter_var($input['execucao_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); $status = trim((string) ($input['status'] ?? ''));
        if ($id === false || !in_array($status, ['concluida', 'falhou'], true)) { JsonResponse::send(['error' => 'execucao_id e status válido são obrigatórios.'], 422); return; }
        $ok = (new Execution())->finish((int) $token['usuario_id'], $id, $status, $this->nullable($input['mensagem_erro'] ?? null));
        JsonResponse::send($ok ? ['data' => ['encerrada' => true]] : ['error' => 'Execução não encontrada.'], $ok ? 200 : 404);
    }

    private function input(): array { $data = json_decode(file_get_contents('php://input'), true); return is_array($data) ? $data : []; }
    private function nullable(mixed $value): ?string { $value = trim((string) $value); return $value === '' ? null : $value; }
}
