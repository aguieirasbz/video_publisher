<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class History
{
    public function videosForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT v.id, v.titulo_gerado, v.status, v.erro, v.gerado_em, v.criado_em, a.titulo AS assunto
             FROM videos v INNER JOIN assuntos a ON a.id = v.assunto_id
             WHERE a.usuario_id = :usuario_id ORDER BY v.criado_em DESC LIMIT 100'
        );
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    public function publicationsForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT p.id, p.titulo_publicacao, p.status, p.ultimo_erro, p.agendado_para, p.publicada_em,
                    v.titulo_gerado, c.nome AS canal, pl.nome AS plataforma
             FROM publicacoes p INNER JOIN canais c ON c.id = p.canal_id
             INNER JOIN plataformas pl ON pl.id = c.plataforma_id
             INNER JOIN videos v ON v.id = p.video_id
             WHERE c.usuario_id = :usuario_id ORDER BY p.agendado_para DESC LIMIT 100'
        );
        $statement->execute(['usuario_id' => $userId]);
        return $statement->fetchAll();
    }

    public function executionsForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT e.id, e.tipo, e.status, e.iniciado_em, e.encerrado_em, e.mensagem_erro
             FROM execucoes e
             WHERE (e.tipo = :video AND EXISTS (SELECT 1 FROM videos v INNER JOIN assuntos a ON a.id = v.assunto_id WHERE v.id = e.referencia_id AND a.usuario_id = :usuario_id_video))
                OR (e.tipo = :publicacao AND EXISTS (SELECT 1 FROM publicacoes p INNER JOIN canais c ON c.id = p.canal_id WHERE p.id = e.referencia_id AND c.usuario_id = :usuario_id_publicacao))
             ORDER BY e.iniciado_em DESC LIMIT 100'
        );
        $statement->execute(['video' => 'video', 'publicacao' => 'publicacao', 'usuario_id_video' => $userId, 'usuario_id_publicacao' => $userId]);
        return $statement->fetchAll();
    }
}
