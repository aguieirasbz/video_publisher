<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\View;
use App\Http\Middleware\AuthMiddleware;
use App\Models\History;

final class HistoryController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        $history = new History();
        View::render('history/index', [
            'videos' => $history->videosForUser((int) $_SESSION['user']['id']),
            'publications' => $history->publicationsForUser((int) $_SESSION['user']['id']),
            'executions' => $history->executionsForUser((int) $_SESSION['user']['id']),
        ]);
    }
}
