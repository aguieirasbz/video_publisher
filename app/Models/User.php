<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public function allAdmins(): array
    {
        return Database::connection()->query("SELECT id, nome, email, perfil, ativo, ultimo_login_em, criado_em FROM usuarios WHERE perfil = 'admin' ORDER BY nome")->fetchAll();
    }

    public function findAdmin(int $id): ?array
    {
        $statement = Database::connection()->prepare("SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = :id AND perfil = 'admin' LIMIT 1");
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function emailInUse(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM usuarios WHERE email = :email';
        $params = ['email' => $email];
        if ($exceptId !== null) { $sql .= ' AND id <> :id'; $params['id'] = $exceptId; }
        $sql .= ' LIMIT 1';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return (bool) $statement->fetchColumn();
    }

    public function createAdmin(string $name, string $email, string $password): void
    {
        $statement = Database::connection()->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (:nome, :email, :senha_hash, 'admin', 1)");
        $statement->execute(['nome' => $name, 'email' => $email, 'senha_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function updateAdmin(int $id, string $name, string $email, bool $active, ?string $password): void
    {
        $sql = 'UPDATE usuarios SET nome = :nome, email = :email, ativo = :ativo';
        $params = ['id' => $id, 'nome' => $name, 'email' => $email, 'ativo' => $active ? 1 : 0];
        if ($password !== null) { $sql .= ', senha_hash = :senha_hash'; $params['senha_hash'] = password_hash($password, PASSWORD_DEFAULT); }
        $sql .= " WHERE id = :id AND perfil = 'admin'";
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
    }

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
