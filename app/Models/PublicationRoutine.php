<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class PublicationRoutine
{
    public function allForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT r.*, c.nome AS canal_nome, p.nome AS plataforma_nome, g.nome AS configuracao_nome
             FROM rotinas_publicacao r
             INNER JOIN canais c ON c.id = r.canal_id
             INNER JOIN plataformas p ON p.id = c.plataforma_id
             LEFT JOIN configuracoes_geracao g ON g.id = r.configuracao_id
             WHERE c.usuario_id = :usuario_id ORDER BY r.ativo DESC, r.nome'
        );
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT r.* FROM rotinas_publicacao r INNER JOIN canais c ON c.id = r.canal_id
             WHERE r.id = :id AND c.usuario_id = :usuario_id LIMIT 1'
        );
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
        return $statement->fetch() ?: null;
    }

    public function create(array $data): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO rotinas_publicacao (canal_id, configuracao_id, nome, videos_por_dia, horario_local, fuso_horario, ativo)
             VALUES (:canal_id, :configuracao_id, :nome, :videos_por_dia, :horario_local, :fuso_horario, :ativo)'
        );
        $statement->execute($data);
    }

    public function update(int $id, int $userId, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE rotinas_publicacao r INNER JOIN canais c ON c.id = r.canal_id
             SET r.canal_id = :canal_id, r.configuracao_id = :configuracao_id, r.nome = :nome,
                 r.videos_por_dia = :videos_por_dia, r.horario_local = :horario_local,
                 r.fuso_horario = :fuso_horario, r.ativo = :ativo
             WHERE r.id = :id AND c.usuario_id = :usuario_id'
        );
        $statement->execute(['id' => $id, 'usuario_id' => $userId] + $data);
    }

    public function delete(int $id, int $userId): void
    {
        $statement = Database::connection()->prepare(
            'DELETE r FROM rotinas_publicacao r INNER JOIN canais c ON c.id = r.canal_id
             WHERE r.id = :id AND c.usuario_id = :usuario_id'
        );
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
    }
}
