<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Channel
{
    public function allForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT c.*, p.nome AS plataforma_nome, p.codigo AS plataforma_codigo
             FROM canais c INNER JOIN plataformas p ON p.id = c.plataforma_id
             WHERE c.usuario_id = :usuario_id ORDER BY c.ativo DESC, c.nome'
        );
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    public function platforms(): array
    {
        return Database::connection()->query('SELECT id, codigo, nome FROM plataformas WHERE ativo = 1 ORDER BY nome')->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM canais WHERE id = :id AND usuario_id = :usuario_id LIMIT 1');
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
        return $statement->fetch() ?: null;
    }

    public function platformExists(int $platformId): bool
    {
        $statement = Database::connection()->prepare('SELECT 1 FROM plataformas WHERE id = :id AND ativo = 1 LIMIT 1');
        $statement->execute(['id' => $platformId]);
        return (bool) $statement->fetchColumn();
    }

    public function create(int $userId, array $data): void
    {
        $statement = Database::connection()->prepare('INSERT INTO canais (usuario_id, plataforma_id, nome, identificador_externo, fuso_horario, ativo) VALUES (:usuario_id, :plataforma_id, :nome, :identificador_externo, :fuso_horario, :ativo)');
        $statement->execute(['usuario_id' => $userId] + $data);
    }

    public function update(int $id, int $userId, array $data): void
    {
        $statement = Database::connection()->prepare('UPDATE canais SET plataforma_id = :plataforma_id, nome = :nome, identificador_externo = :identificador_externo, fuso_horario = :fuso_horario, ativo = :ativo WHERE id = :id AND usuario_id = :usuario_id');
        $statement->execute(['id' => $id, 'usuario_id' => $userId] + $data);
    }
}
