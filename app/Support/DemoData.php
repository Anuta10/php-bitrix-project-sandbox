<?php

declare(strict_types=1);

// У каждого посетителя своя копия демо-данных. Если портал открыли в новый день,
// базовый набор собирается заново, чтобы встречи, дни рождения и уведомления
// снова выглядели актуально относительно сегодняшней даты.

namespace App\Support;

final class DemoData
{
    private array $seed;

    public function __construct()
    {
        $this->seed = require base_path('data/demo.php');
        $this->ensureStarted();
    }

    private function ensureStarted(): void
    {
        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $state = $_SESSION['demo_state'] ?? null;

        if (!is_array($state) || ($state['demo_date'] ?? null) !== $today) {
            $_SESSION['demo_state'] = $this->freshState($today);
        }
    }

    private function freshState(?string $today = null): array
    {
        $today ??= (new \DateTimeImmutable('today'))->format('Y-m-d');

        return [
            'demo_date' => $today,
            'created_at' => time(),
            'employee_favorites' => [2, 7, 14],
            'department_favorites' => [3],
            'department_exclusions' => [],
            'employee_reminders' => [7 => 1, 14 => 3],
            'department_reminders' => [3 => 7],
            'channels' => ['portal' => true, 'email' => true],
            'congratulated_employees' => [],
            'events' => $this->seed['events'],
            'tasks' => $this->seed['tasks'],
            'task_updates' => [],
            'event_task_links' => $this->seed['event_task_links'],
            'support_jobs' => $this->seed['support_jobs'],
            'support_logs' => $this->seed['support_logs'],
            'notifications' => $this->seed['notifications'],
        ];
    }

    public function seed(string $key): array
    {
        return $this->seed[$key] ?? [];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION['demo_state'][$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION['demo_state'][$key] = $value;
    }

    public function reset(): void
    {
        $_SESSION['demo_state'] = $this->freshState();
    }
}
