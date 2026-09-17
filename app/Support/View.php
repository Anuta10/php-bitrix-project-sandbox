<?php

declare(strict_types=1);

// Шаблоны рендерятся обычным PHP и оборачиваются в общий layout.

namespace App\Support;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'layout'): void
    {
        $viewFile = base_path('resources/views/' . $view . '.php');
        $layoutFile = base_path('resources/views/' . $layout . '.php');

        if (!is_file($viewFile) || !is_file($layoutFile)) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = (string) ob_get_clean();
        require $layoutFile;
    }
}
