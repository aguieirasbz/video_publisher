<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

final class Execution
{
    public function pendingSubjects(int $userId): array
    {
        $cleanup = Database::connection()->prepare("UPDATE assuntos SET status = 'pendente', bloqueio_token = NULL, bloqueado_ate = NULL WHERE usuario_id = :usuario_id AND status = 'processando' AND bloqueado_ate IS NOT NULL AND bloqueado_ate < UTC_TIMESTAMP()");
        $cleanup->execute(['usuario_id' => $userId]);
        $statement = Database::connection()->prepare(
            "SELECT id, titulo, instrucoes, configuracao_id, ordem, nao_antes_de FROM assuntos
             WHERE usuario_id = :usuario_id AND status = 'pendente'
               AND (nao_antes_de IS NULL OR nao_antes_de <= UTC_TIMESTAMP())
             ORDER BY ordem, id LIMIT 100"
        );
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    public function start(int $userId, int $subjectId, string $idempotencyKey): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $subject = $pdo->prepare("SELECT id, titulo FROM assuntos WHERE id = :id AND usuario_id = :usuario_id AND status = 'pendente' AND (nao_antes_de IS NULL OR nao_antes_de <= UTC_TIMESTAMP()) LIMIT 1 FOR UPDATE");
            $subject->execute(['id' => $subjectId, 'usuario_id' => $userId]);
            $subjectData = $subject->fetch();
            if (!$subjectData) { throw new \InvalidArgumentException('Assunto pendente não encontrado ou já reservado.'); }

            $statement = $pdo->prepare('INSERT INTO execucoes (tipo, referencia_id, idempotencia_chave, status) VALUES (\'geracao_assunto\', :referencia_id, :idempotencia, \'iniciada\')');
            $statement->execute(['referencia_id' => $subjectId, 'idempotencia' => $idempotencyKey]);
            $executionId = (int) $pdo->lastInsertId();
            $lockToken = $this->uuid();
            $lock = $pdo->prepare("UPDATE assuntos SET status = 'processando', bloqueio_token = :bloqueio_token, bloqueado_ate = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 MINUTE) WHERE id = :id");
            $lock->execute(['bloqueio_token' => $lockToken, 'id' => $subjectId]);
            $pdo->commit();
            return ['id' => $executionId, 'assunto_id' => $subjectId, 'titulo' => $subjectData['titulo'], 'bloqueio_token' => $lockToken];
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            if ((int) $exception->errorInfo[1] === 1062) { throw new \RuntimeException('A chave de idempotência já foi utilizada.'); }
            throw $exception;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $exception;
        }
    }

    public function finish(int $userId, int $executionId, string $status, ?string $message): bool
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $owned = $pdo->prepare("SELECT referencia_id FROM execucoes e INNER JOIN assuntos a ON a.id = e.referencia_id WHERE e.id = :id AND e.tipo = 'geracao_assunto' AND a.usuario_id = :usuario_id LIMIT 1 FOR UPDATE");
            $owned->execute(['id' => $executionId, 'usuario_id' => $userId]);
            $referenceId = $owned->fetchColumn();
            if (!$referenceId) { $pdo->rollBack(); return false; }
            $statement = $pdo->prepare('UPDATE execucoes SET status = :status, encerrado_em = UTC_TIMESTAMP(), mensagem_erro = :mensagem WHERE id = :id');
            $statement->execute(['status' => $status, 'mensagem' => $message, 'id' => $executionId]);
            $subjectStatus = $status === 'concluida' ? 'concluido' : 'pendente';
            $subject = $pdo->prepare('UPDATE assuntos SET status = :status, bloqueio_token = NULL, bloqueado_ate = NULL, ultimo_erro = :erro WHERE id = :id');
            $subject->execute(['status' => $subjectStatus, 'erro' => $status === 'falhou' ? $message : null, 'id' => $referenceId]);
            $pdo->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $exception;
        }
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

    private function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
