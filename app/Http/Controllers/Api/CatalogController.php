<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\JsonResponse;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Models\Channel;
use App\Models\GenerationConfig;
use App\Models\History;
use App\Models\PublicationRoutine;
use App\Models\Subject;

final class CatalogController
{
    public function subjects(): void
    {
        ApiAuthMiddleware::handle();
        JsonResponse::send(['data' => (new Subject())->allForUser($this->userId())]);
    }

    public function channels(): void
    {
        ApiAuthMiddleware::handle();
        JsonResponse::send(['data' => (new Channel())->allForUser($this->userId())]);
    }

    public function configurations(): void
    {
        ApiAuthMiddleware::handle();
        JsonResponse::send(['data' => (new GenerationConfig())->allForUser($this->userId())]);
    }

    public function routines(): void
    {
        ApiAuthMiddleware::handle();
        JsonResponse::send(['data' => (new PublicationRoutine())->allForUser($this->userId())]);
    }

    public function history(): void
    {
        ApiAuthMiddleware::handle();
        $history = new History();
        JsonResponse::send(['data' => [
            'videos' => $history->videosForUser($this->userId()),
            'publicacoes' => $history->publicationsForUser($this->userId()),
            'execucoes' => $history->executionsForUser($this->userId()),
        ]]);
    }

    private function userId(): int
    {
        return (int) $_SESSION['user']['id'];
    }
}
