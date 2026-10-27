<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

final class Execution
{
    public function pendingSubjects(int $userId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, titulo, instrucoes, configuracao_id, ordem, nao_antes_de FROM assuntos
             WHERE usuario_id = :usuario_id AND status = 'pendente'
               AND (nao_antes_de IS NULL OR nao_antes_de <= UTC_TIMESTAMP())
             ORDER BY ordem, id LIMIT 100"
        );
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    public function start(int $userId, int $subjectId, string $idempotencyKey): int
    {
        $subject = Database::connection()->prepare("SELECT id FROM assuntos WHERE id = :id AND usuario_id = :usuario_id AND status = 'pendente' LIMIT 1");
        $subject->execute(['id' => $subjectId, 'usuario_id' => $userId]);
        if (!$subject->fetchColumn()) { throw new \InvalidArgumentException('Assunto pendente não encontrado.'); }

        $statement = Database::connection()->prepare('INSERT INTO execucoes (tipo, referencia_id, idempotencia_chave, status) VALUES (\'geracao_assunto\', :referencia_id, :idempotencia, \'iniciada\')');
        try {
            $statement->execute(['referencia_id' => $subjectId, 'idempotencia' => $idempotencyKey]);
        } catch (PDOException $exception) {
            if ((int) $exception->errorInfo[1] === 1062) { throw new \RuntimeException('A chave de idempotência já foi utilizada.'); }
            throw $exception;
        }
        return (int) Database::connection()->lastInsertId();
    }

    public function finish(int $userId, int $executionId, string $status, ?string $message): bool
    {
        if (!$this->owned($userId, $executionId)) { return false; }
        $statement = Database::connection()->prepare('UPDATE execucoes SET status = :status, encerrado_em = UTC_TIMESTAMP(), mensagem_erro = :mensagem WHERE id = :id');
        $statement->execute(['status' => $status, 'mensagem' => $message, 'id' => $executionId]);
        return true;
    }

    public function addLog(int $userId, int $executionId, string $level, ?string $stage, string $message): bool
    {
        if (!$this->owned($userId, $executionId)) { return false; }
        $statement = Database::connection()->prepare('INSERT INTO logs_execucao (execucao_id, nivel, etapa, mensagem) VALUES (:execucao_id, :nivel, :etapa, :mensagem)');
        $statement->execute(['execucao_id' => $executionId, 'nivel' => $level, 'etapa' => $stage, 'mensagem' => $message]);
        return true;
    }

    private function owned(int $userId, int $executionId): bool
    {
        $statement = Database::connection()->prepare("SELECT 1 FROM execucoes e INNER JOIN assuntos a ON a.id = e.referencia_id WHERE e.id = :id AND e.tipo = 'geracao_assunto' AND a.usuario_id = :usuario_id LIMIT 1");
        $statement->execute(['id' => $executionId, 'usuario_id' => $userId]);
        return (bool) $statement->fetchColumn();
    }
}
