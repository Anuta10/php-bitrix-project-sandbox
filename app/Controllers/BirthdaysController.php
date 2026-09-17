<?php

declare(strict_types=1);

namespace App\Controllers;

// Страница дней рождения и действия, связанные с избранным и напоминаниями.

use App\Services\Birthdays;
use App\Services\EmployeeDirectory;
use App\Services\Notifications;
use App\Support\DemoData;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;

final class BirthdaysController
{
    public function __construct(
        private DemoData $state,
        private EmployeeDirectory $directory,
        private Birthdays $birthdays,
        private Notifications $notifications,
    ) {
    }

    public function index(Request $request): void
    {
        $mode = (string) $request->input('mode', 'all');
        if (!in_array($mode, ['all', 'favorites', 'reminders', 'anniversaries'], true)) {
            $mode = 'all';
        }

        [$period, $from, $to] = $this->period($request);
        $rows = $this->birthdays->filtered($mode, current_locale(), $from, $to);
        $emailPerson = null;
        foreach ($rows as $candidate) {
            if ((int) ($candidate['_days'] ?? -1) >= 0) {
                $emailPerson = $candidate;
                break;
            }
        }
        $emailPerson ??= ($this->birthdays->upcoming(30, current_locale())[0] ?? null);

        View::render('pages/birthdays', $this->page([
            'pageTitle' => t('birthdays.title'),
            'active' => 'birthdays',
            'mode' => $mode,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
            'emailPerson' => $emailPerson,
            'favoriteDepartments' => $this->birthdays->favoriteDepartments(current_locale()),
            'departments' => $this->directory->departments(),
            'channels' => $this->state->get('channels', ['portal' => true, 'email' => true]),
        ]));
    }

    public function toggleEmployeeFavorite(Request $request): never
    {
        $id = (int) $request->input('employee_id', 0);
        if (!$this->directory->find($id)) {
            Response::json(['ok' => false, 'error' => 'employee_not_found'], 404);
        }
        Response::json(['ok' => true, 'enabled' => $this->birthdays->toggleEmployeeFavorite($id)]);
    }

    public function toggleDepartmentFavorite(Request $request): never
    {
        $id = (int) $request->input('department_id', 0);
        if (!$this->directory->findDepartment($id)) {
            Response::json(['ok' => false, 'error' => 'department_not_found'], 404);
        }
        Response::json(['ok' => true, 'enabled' => $this->birthdays->toggleDepartmentFavorite($id)]);
    }

    public function setEmployeeReminder(Request $request): never
    {
        $id = (int) $request->input('employee_id', 0);
        $days = $this->reminderDays((string) $request->input('days', ''));
        $this->birthdays->setEmployeeReminder($id, $days);
        Response::json(['ok' => true, 'effective' => $this->birthdays->effectiveReminder($id)]);
    }

    public function setDepartmentReminder(Request $request): never
    {
        $id = (int) $request->input('department_id', 0);
        $days = $this->reminderDays((string) $request->input('days', ''));
        $this->birthdays->setDepartmentReminder($id, $days);
        Response::json(['ok' => true]);
    }

    public function setChannels(Request $request): never
    {
        $this->notifications->setChannels($request->bool('portal'), $request->bool('email'));
        Response::json(['ok' => true, 'channels' => $this->state->get('channels')]);
    }

    public function congratulate(Request $request): never
    {
        $message = trim((string) $request->input('message', ''));
        if (strlen($message) > 2000) {
            $message = substr($message, 0, 2000);
        }
        $rows = $this->notifications->congratulate(
            (int) $request->input('employee_id', 0),
            $message,
            (string) $request->input('card', ''),
            (string) $request->input('sticker', '')
        );
        Response::json(['ok' => true, 'created' => $rows]);
    }

    public function generateReminders(): never
    {
        $rows = $this->notifications->generateBirthdayReminders();
        Response::json(['ok' => true, 'created_count' => count($rows), 'created' => $rows]);
    }

    private function period(Request $request): array
    {
        $today = new \DateTimeImmutable('today');
        $period = (string) $request->input('period', 'month');
        $from = (string) $request->input('from', '');
        $to = (string) $request->input('to', '');
        $validDate = static fn(string $value): bool => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);

        if (!$validDate($from) || !$validDate($to)) {
            [$fromDate, $toDate] = match ($period) {
                'today' => [$today, $today],
                'week' => [$today->modify('monday this week'), $today->modify('sunday this week')],
                default => [$today->modify('first day of this month'), $today->modify('last day of this month')],
            };
            $from = $fromDate->format('Y-m-d');
            $to = $toDate->format('Y-m-d');
        } else {
            $period = 'custom';
        }

        if (new \DateTimeImmutable($to) < new \DateTimeImmutable($from)) {
            [$from, $to] = [$to, $from];
        }
        return [$period, $from, $to];
    }

    private function reminderDays(string $raw): ?int
    {
        $days = $raw === '' ? null : (int) $raw;
        if ($days !== null && !in_array($days, [0, 1, 3, 7], true)) {
            Response::json(['ok' => false, 'error' => 'invalid_reminder'], 422);
        }
        return $days;
    }

    private function page(array $data): array
    {
        return $data + [
            'locale' => current_locale(),
            'csrf' => csrf_token(),
            'directory' => $this->directory,
            'config' => config(),
        ];
    }
}
