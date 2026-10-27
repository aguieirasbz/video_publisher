<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public function findActiveByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, nome, email, senha_hash, perfil, ativo FROM usuarios WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function touchLastLogin(int $id): void
    {
        $statement = Database::connection()->prepare('UPDATE usuarios SET ultimo_login_em = UTC_TIMESTAMP() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
