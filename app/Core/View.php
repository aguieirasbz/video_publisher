<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $view = dirname(__DIR__) . '/Views/' . $template . '.php';
        if (!is_file($view)) {
            throw new \RuntimeException('View não encontrada.');
        }

        require $view;
    }
}
