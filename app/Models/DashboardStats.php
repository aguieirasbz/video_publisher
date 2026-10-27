<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class DashboardStats
{
    public function forUser(int $userId): array
    {
        $stats = [];
        $stats['assuntos_total'] = $this->count('SELECT COUNT(*) FROM assuntos WHERE usuario_id = :usuario_id', $userId);
        $stats['assuntos_pendentes'] = $this->count("SELECT COUNT(*) FROM assuntos WHERE usuario_id = :usuario_id AND status = 'pendente'", $userId);
        $stats['videos_gerados'] = $this->count('SELECT COUNT(*) FROM videos v INNER JOIN assuntos a ON a.id = v.assunto_id WHERE a.usuario_id = :usuario_id AND v.gerado_em IS NOT NULL', $userId);
        $stats['publicacoes_agendadas'] = $this->count("SELECT COUNT(*) FROM publicacoes p INNER JOIN canais c ON c.id = p.canal_id WHERE c.usuario_id = :usuario_id AND p.status = 'agendada'", $userId);
        $stats['publicacoes_realizadas'] = $this->count('SELECT COUNT(*) FROM publicacoes p INNER JOIN canais c ON c.id = p.canal_id WHERE c.usuario_id = :usuario_id AND p.publicada_em IS NOT NULL', $userId);
        $stats['falhas'] = $this->count("SELECT COUNT(*) FROM videos v INNER JOIN assuntos a ON a.id = v.assunto_id WHERE a.usuario_id = :usuario_id AND (v.erro IS NOT NULL OR v.status IN ('falha', 'falhou'))", $userId)
            + $this->count("SELECT COUNT(*) FROM publicacoes p INNER JOIN canais c ON c.id = p.canal_id WHERE c.usuario_id = :usuario_id AND (p.ultimo_erro IS NOT NULL OR p.status IN ('falha', 'falhou'))", $userId);

        return $stats;
    }

    private function count(string $sql, int $userId): int
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute(['usuario_id' => $userId]);
        return (int) $statement->fetchColumn();
    }
}
