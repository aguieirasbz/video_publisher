<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Subject
{
    public function allForUser(int $userId): array
    {
        $statement = Database::connection()->prepare('SELECT a.*, c.nome AS configuracao_nome FROM assuntos a LEFT JOIN configuracoes_geracao c ON c.id = a.configuracao_id WHERE a.usuario_id = :usuario_id ORDER BY a.ordem, a.id');
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM assuntos WHERE id = :id AND usuario_id = :usuario_id LIMIT 1');
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
        return $statement->fetch() ?: null;
    }

    public function create(int $userId, array $data): void
    {
        $statement = Database::connection()->prepare('INSERT INTO assuntos (usuario_id, configuracao_id, titulo, instrucoes, ordem, status, nao_antes_de) VALUES (:usuario_id, :configuracao_id, :titulo, :instrucoes, :ordem, :status, :nao_antes_de)');
        $statement->execute(['usuario_id' => $userId] + $data);
    }

    public function update(int $id, int $userId, array $data): void
    {
        $statement = Database::connection()->prepare('UPDATE assuntos SET configuracao_id = :configuracao_id, titulo = :titulo, instrucoes = :instrucoes, ordem = :ordem, status = :status, nao_antes_de = :nao_antes_de WHERE id = :id AND usuario_id = :usuario_id');
        $statement->execute(['id' => $id, 'usuario_id' => $userId] + $data);
    }

    public function delete(int $id, int $userId): void
    {
        $statement = Database::connection()->prepare('DELETE FROM assuntos WHERE id = :id AND usuario_id = :usuario_id');
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
    }
}
