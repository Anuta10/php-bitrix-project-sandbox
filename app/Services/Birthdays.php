<?php

declare(strict_types=1);

// Бизнес-логика дней рождения, избранного и правил напоминаний.

namespace App\Services;

use App\Support\DemoData;

final class Birthdays
{
    public function __construct(private DemoData $state, private EmployeeDirectory $directory)
    {
    }

    public function upcoming(int $days = 60, string $locale = 'ru'): array
    {
        $today = new \DateTimeImmutable('today');
        $rows = [];
        foreach ($this->directory->all() as $employee) {
            $next = $this->nextBirthday((string) $employee['birth_date'], $today);
            $delta = (int) $today->diff($next)->format('%a');
            if ($delta > $days) {
                continue;
            }
            $rows[] = $this->decorateOccurrence($employee, $next, $delta, $locale);
        }
        usort($rows, static fn(array $a, array $b): int => $a['_days'] <=> $b['_days'] ?: strcmp($a['_name'], $b['_name']));
        return $rows;
    }

    /**
     * Возвращает дни рождения внутри произвольного календарного периода.
     * В отличие от upcoming(), здесь сохраняются и прошедшие даты — они нужны
     * для приглушённого состояния карточек.
     */
    public function occurrencesBetween(string $from, string $to, string $locale = 'ru'): array
    {
        $fromDate = new \DateTimeImmutable($from);
        $toDate = new \DateTimeImmutable($to);
        if ($toDate < $fromDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $today = new \DateTimeImmutable('today');
        $rows = [];
        $fromYear = (int) $fromDate->format('Y');
        $toYear = (int) $toDate->format('Y');

        foreach ($this->directory->all() as $employee) {
            $monthDay = substr((string) $employee['birth_date'], 5);
            for ($year = $fromYear; $year <= $toYear; $year++) {
                $occurrence = new \DateTimeImmutable($year . '-' . $monthDay);
                if ($occurrence < $fromDate || $occurrence > $toDate) {
                    continue;
                }
                $delta = (int) $today->diff($occurrence)->format('%r%a');
                $rows[] = $this->decorateOccurrence($employee, $occurrence, $delta, $locale);
            }
        }

        usort($rows, static function (array $a, array $b): int {
            $date = strcmp((string) $a['_next_birthday'], (string) $b['_next_birthday']);
            return $date !== 0 ? $date : strcmp((string) $a['_name'], (string) $b['_name']);
        });
        return $rows;
    }

    private function decorateOccurrence(array $employee, \DateTimeImmutable $date, int $delta, string $locale): array
    {
        $row = $employee;
        $row['_days'] = $delta;
        $row['_next_birthday'] = $date->format('Y-m-d');
        $row['_age'] = (int) $date->format('Y') - (int) substr((string) $employee['birth_date'], 0, 4);
        $row['_anniversary'] = $row['_age'] % 5 === 0;
        $row['_name'] = $this->directory->localized($employee, 'name', $locale);
        $row['_position'] = $this->directory->localized($employee, 'position', $locale);
        $row['_department'] = $this->directory->departmentName((int) $employee['department_id'], $locale);
        $row['_favorite'] = $this->isEmployeeFavorite((int) $employee['id']);
        $row['_reminder'] = $this->effectiveReminder((int) $employee['id']);
        $row['_congratulated'] = $this->wasCongratulated((int) $employee['id']);
        return $row;
    }

    public function nextBirthday(string $birthDate, ?\DateTimeImmutable $from = null): \DateTimeImmutable
    {
        $from ??= new \DateTimeImmutable('today');
        $monthDay = substr($birthDate, 5);
        $candidate = new \DateTimeImmutable($from->format('Y') . '-' . $monthDay);
        if ($candidate < $from) {
            $candidate = new \DateTimeImmutable(((int) $from->format('Y') + 1) . '-' . $monthDay);
        }
        return $candidate;
    }

    public function wasCongratulated(int $employeeId): bool
    {
        return in_array($employeeId, array_map('intval', $this->state->get('congratulated_employees', [])), true);
    }

    public function markCongratulated(int $employeeId): void
    {
        $items = array_map('intval', $this->state->get('congratulated_employees', []));
        $items[] = $employeeId;
        $this->state->set('congratulated_employees', array_values(array_unique($items)));
    }

    public function isEmployeeFavorite(int $employeeId): bool
    {
        return in_array($employeeId, $this->state->get('employee_favorites', []), true);
    }

    public function toggleEmployeeFavorite(int $employeeId): bool
    {
        $items = array_values(array_map('intval', $this->state->get('employee_favorites', [])));
        if (in_array($employeeId, $items, true)) {
            $items = array_values(array_diff($items, [$employeeId]));
            $enabled = false;
        } else {
            $items[] = $employeeId;
            $items = array_values(array_unique($items));
            $enabled = true;
        }
        $this->state->set('employee_favorites', $items);
        return $enabled;
    }

    public function isDepartmentFavorite(int $departmentId): bool
    {
        return in_array($departmentId, $this->state->get('department_favorites', []), true);
    }

    public function toggleDepartmentFavorite(int $departmentId): bool
    {
        $items = array_values(array_map('intval', $this->state->get('department_favorites', [])));
        if (in_array($departmentId, $items, true)) {
            $items = array_values(array_diff($items, [$departmentId]));
            $enabled = false;
        } else {
            $items[] = $departmentId;
            $items = array_values(array_unique($items));
            $enabled = true;
        }
        $this->state->set('department_favorites', $items);
        return $enabled;
    }

    public function setEmployeeReminder(int $employeeId, ?int $days): void
    {
        $rules = $this->state->get('employee_reminders', []);
        if ($days === null) {
            unset($rules[$employeeId]);
        } else {
            $rules[$employeeId] = $days;
        }
        $this->state->set('employee_reminders', $rules);
    }

    public function setDepartmentReminder(int $departmentId, ?int $days): void
    {
        $rules = $this->state->get('department_reminders', []);
        if ($days === null) {
            unset($rules[$departmentId]);
        } else {
            $rules[$departmentId] = $days;
        }
        $this->state->set('department_reminders', $rules);
    }

    public function effectiveReminder(int $employeeId): ?int
    {
        $personal = $this->state->get('employee_reminders', []);
        if (array_key_exists($employeeId, $personal)) {
            return (int) $personal[$employeeId];
        }
        $employee = $this->directory->find($employeeId);
        if (!$employee) {
            return null;
        }
        $departmentId = (int) $employee['department_id'];
        if (!$this->isDepartmentFavorite($departmentId)) {
            return null;
        }
        $exclusions = $this->state->get('department_exclusions', []);
        if (in_array($employeeId, array_map('intval', $exclusions[$departmentId] ?? []), true)) {
            return null;
        }
        $departmentRules = $this->state->get('department_reminders', []);
        return array_key_exists($departmentId, $departmentRules) ? (int) $departmentRules[$departmentId] : null;
    }

    public function favoriteDepartments(string $locale): array
    {
        $ids = array_map('intval', $this->state->get('department_favorites', []));
        $result = [];
        foreach ($ids as $id) {
            $department = $this->directory->findDepartment($id);
            if (!$department) {
                continue;
            }
            $result[] = [
                'id' => $id,
                'name' => $this->directory->localized($department, 'name', $locale),
                'count' => count($this->directory->byDepartment($id, false)),
                'reminder' => $this->state->get('department_reminders', [])[$id] ?? null,
            ];
        }
        return $result;
    }

    public function filtered(string $mode, string $locale, ?string $from = null, ?string $to = null): array
    {
        if ($from && $to) {
            $rows = $this->occurrencesBetween($from, $to, $locale);
        } else {
            $rows = $this->upcoming(365, $locale);
        }

        return array_values(array_filter($rows, function (array $row) use ($mode): bool {
            return match ($mode) {
                'favorites' => $row['_favorite'] || $this->isDepartmentFavorite((int) $row['department_id']),
                'reminders' => $row['_reminder'] !== null,
                'anniversaries' => (bool) $row['_anniversary'],
                default => true,
            };
        }));
    }
}
