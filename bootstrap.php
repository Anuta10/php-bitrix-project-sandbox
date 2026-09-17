<?php

declare(strict_types=1);

$config = require __DIR__ . '/config/app.php';
date_default_timezone_set($config['timezone']);

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https';

    session_name('portal_sandbox');
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => $isHttps,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

function config(?string $key = null, mixed $default = null): mixed
{
    static $cfg;
    $cfg ??= require __DIR__ . '/config/app.php';

    if ($key === null) {
        return $cfg;
    }

    $value = $cfg;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function base_path(string $path = ''): string
{
    return __DIR__ . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

require_once __DIR__ . '/app/Support/helpers.php';
