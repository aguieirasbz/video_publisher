<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ApiToken
{
    public function allForUser(int $userId): array
    {
        $statement = Database::connection()->prepare('SELECT id, nome, escopos, expira_em, ultimo_uso_em, revogado_em, criado_em FROM api_tokens WHERE usuario_id = :usuario_id ORDER BY criado_em DESC');
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    public function create(int $userId, string $name, string $scopes, ?string $expiresAt): string
    {
        $plainToken = 'ats_' . bin2hex(random_bytes(32));
        $statement = Database::connection()->prepare('INSERT INTO api_tokens (usuario_id, nome, token_hash, escopos, expira_em) VALUES (:usuario_id, :nome, :token_hash, :escopos, :expira_em)');
        $statement->execute(['usuario_id' => $userId, 'nome' => $name, 'token_hash' => hash('sha256', $plainToken), 'escopos' => $scopes, 'expira_em' => $expiresAt]);
        return $plainToken;
    }

    public function revoke(int $id, int $userId): void
    {
        $statement = Database::connection()->prepare('UPDATE api_tokens SET revogado_em = UTC_TIMESTAMP() WHERE id = :id AND usuario_id = :usuario_id AND revogado_em IS NULL');
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
    }

    public function authenticate(string $plainToken): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT t.id AS token_id, t.usuario_id, t.escopos, u.nome, u.email, u.perfil
             FROM api_tokens t INNER JOIN usuarios u ON u.id = t.usuario_id
             WHERE t.token_hash = :token_hash AND t.revogado_em IS NULL AND u.ativo = 1
               AND (t.expira_em IS NULL OR t.expira_em > UTC_TIMESTAMP()) LIMIT 1'
        );
        $statement->execute(['token_hash' => hash('sha256', $plainToken)]);
        $token = $statement->fetch();
        if (!$token) { return null; }
        $update = Database::connection()->prepare('UPDATE api_tokens SET ultimo_uso_em = UTC_TIMESTAMP() WHERE id = :id');
        $update->execute(['id' => $token['token_id']]);
        return $token;
    }
}
