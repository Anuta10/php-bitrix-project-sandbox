<?php

declare(strict_types=1);

// Небольшой роутер без фреймворка: поддерживает GET, POST и параметры в фигурных скобках.

namespace App\Support;

final class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $paramNames = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static function (array $m) use (&$paramNames): string {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $pattern);
        $regex = '#^' . rtrim((string) $regex, '/') . '/?$#';
        if ($pattern === '/') {
            $regex = '#^/$#';
        }
        $this->routes[] = compact('method', 'regex', 'paramNames', 'handler');
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($request->method !== $route['method']) {
                continue;
            }
            if (!preg_match($route['regex'], $request->path, $matches)) {
                continue;
            }
            array_shift($matches);
            $params = [];
            foreach ($route['paramNames'] as $i => $name) {
                $params[$name] = urldecode($matches[$i] ?? '');
            }
            ($route['handler'])($request, $params);
            return;
        }
        Response::notFound();
    }
}
