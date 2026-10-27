<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class GenerationConfig
{
    public function allForUser(int $userId): array
    {
        $statement = Database::connection()->prepare('SELECT * FROM configuracoes_geracao WHERE usuario_id = :usuario_id ORDER BY ativo DESC, nome');
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM configuracoes_geracao WHERE id = :id AND usuario_id = :usuario_id LIMIT 1');
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
        return $statement->fetch() ?: null;
    }

    public function create(int $userId, array $data): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO configuracoes_geracao (usuario_id, nome, duracao_alvo_segundos, largura, altura, fps, idioma, provedor_roteiro, provedor_imagem, provedor_voz, voz_id, estilo_legendas, estilo_movimento, configuracoes_extras, ativo)
             VALUES (:usuario_id, :nome, :duracao, :largura, :altura, :fps, :idioma, :roteiro, :imagem, :voz, :voz_id, :legendas, :movimento, :extras, :ativo)'
        );
        $statement->execute(['usuario_id' => $userId] + $data);
    }

    public function update(int $id, int $userId, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE configuracoes_geracao SET nome = :nome, duracao_alvo_segundos = :duracao, largura = :largura, altura = :altura, fps = :fps, idioma = :idioma, provedor_roteiro = :roteiro, provedor_imagem = :imagem, provedor_voz = :voz, voz_id = :voz_id, estilo_legendas = :legendas, estilo_movimento = :movimento, configuracoes_extras = :extras, ativo = :ativo WHERE id = :id AND usuario_id = :usuario_id'
        );
        $statement->execute(['id' => $id, 'usuario_id' => $userId] + $data);
    }

    public function delete(int $id, int $userId): void
    {
        $statement = Database::connection()->prepare('DELETE FROM configuracoes_geracao WHERE id = :id AND usuario_id = :usuario_id');
        $statement->execute(['id' => $id, 'usuario_id' => $userId]);
    }
}
