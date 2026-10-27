<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\JsonResponse;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Models\Channel;
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

    private function userId(): int
    {
        return (int) $_SESSION['user']['id'];
    }
}
