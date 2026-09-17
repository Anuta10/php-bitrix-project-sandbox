<?php

declare(strict_types=1);

// Несколько коротких ответов, которых достаточно для этой демо-версии.

namespace App\Support;

final class Response
{
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    public static function redirect(string $url, int $status = 302): never
    {
        header('Location: ' . $url, true, $status);
        exit;
    }

    public static function notFound(): never
    {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }
}
