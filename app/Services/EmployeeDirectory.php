<?php

declare(strict_types=1);

// Справочник сотрудников: структура отделов, карточки, поиск, алфавит и ранжирование.

namespace App\Services;

use App\Support\DemoData;

final class EmployeeDirectory
{
    public function __construct(private DemoData $state)
    {
    }

    public function all(): array
    {
        return $this->state->seed('employees');
    }

    public function departments(): array
    {
        return $this->state->seed('departments');
    }

    public function find(int $id): ?array
    {
        foreach ($this->all() as $employee) {
            if ((int) $employee['id'] === $id) {
                return $employee;
            }
        }
        return null;
    }

    public function findDepartment(int $id): ?array
    {
        foreach ($this->departments() as $department) {
            if ((int) $department['id'] === $id) {
                return $department;
            }
        }
        return null;
    }

    public function employeeName(?int $id, string $locale): string
    {
        if (!$id) {
            return '—';
        }
        $employee = $this->find($id);
        return $employee ? $this->localized($employee, 'name', $locale) : '—';
    }

    public function departmentName(int $id, string $locale): string
    {
        $department = $this->findDepartment($id);
        return $department ? $this->localized($department, 'name', $locale) : '—';
    }

    public function localized(array $row, string $field, string $locale): string
    {
        return (string) ($row[$field . '_' . $locale] ?? $row[$field . '_en'] ?? $row[$field . '_ru'] ?? '');
    }

    public function byDepartment(?int $departmentId, bool $includeChildren = false): array
    {
        if (!$departmentId) {
            return $this->all();
        }
        $ids = [$departmentId];
        if ($includeChildren) {
            $ids = array_merge($ids, $this->descendantIds($departmentId));
        }
        return array_values(array_filter(
            $this->all(),
            static fn(array $employee): bool => in_array((int) $employee['department_id'], $ids, true)
        ));
    }

    public function descendantIds(int $parentId): array
    {
        $result = [];
        $queue = [$parentId];
        while ($queue) {
            $current = array_shift($queue);
            foreach ($this->departments() as $department) {
                if ((int) ($department['parent_id'] ?? 0) === $current) {
                    $id = (int) $department['id'];
                    $result[] = $id;
                    $queue[] = $id;
                }
            }
        }
        return $result;
    }

    public function children(int $parentId): array
    {
        $rows = array_values(array_filter(
            $this->departments(),
            static fn(array $department): bool => (int) ($department['parent_id'] ?? 0) === $parentId
        ));
        usort($rows, static fn(array $a, array $b): int => ((int) $a['sort']) <=> ((int) $b['sort']));
        return $rows;
    }

    public function departmentTree(string $locale): array
    {
        $build = function (?int $parentId) use (&$build, $locale): array {
            $nodes = [];
            foreach ($this->departments() as $department) {
                $actualParent = $department['parent_id'] === null ? null : (int) $department['parent_id'];
                if ($actualParent !== $parentId) {
                    continue;
                }
                $id = (int) $department['id'];
                $nodes[] = [
                    'id' => $id,
                    'name' => $this->localized($department, 'name', $locale),
                    'count' => count($this->byDepartment($id, true)),
                    'children' => $build($id),
                ];
            }
            return $nodes;
        };
        return $build(null);
    }

    /**
     * Буква и строка поиска работают независимо.
     * Буква фильтрует только по первой букве фамилии, а обычный поиск
     * смотрит ФИО, телефон, должность, подразделение, почту и локацию.
     */
    public function search(string $query, ?int $departmentId, string $locale, int $limit = 50, ?string $initial = null): array
    {
        $query = trim($query);
        $initial = trim((string) $initial);
        $keyboard = $query !== '' ? keyboard_swap($query) : '';
        $terms = array_values(array_unique(array_filter([$query, $keyboard], static fn(string $v): bool => $v !== '')));
        $candidates = $this->byDepartment($departmentId, true);

        if ($initial !== '') {
            $candidates = array_values(array_filter(
                $candidates,
                fn(array $employee): bool => $this->surnameStartsWith($employee, $initial, $locale)
            ));
        }

        if ($terms === []) {
            usort($candidates, function (array $a, array $b) use ($locale): int {
                $surname = strcmp($this->surname($a, $locale), $this->surname($b, $locale));
                if ($surname !== 0) {
                    return $surname;
                }
                return strcmp($this->localized($a, 'name', $locale), $this->localized($b, 'name', $locale));
            });
            return array_slice(array_map(fn(array $employee): array => $this->decorateSearchResult($employee, 0, 'all', $locale), $candidates), 0, $limit);
        }

        $results = [];
        foreach ($candidates as $employee) {
            $bestScore = -1;
            $bestMatch = '';
            foreach ($terms as $term) {
                [$score, $match] = $this->score($employee, $term, $locale);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $match;
                }
            }
            if ($bestScore >= 0) {
                $results[] = $this->decorateSearchResult($employee, $bestScore + (int) $employee['rank'] / 100, $bestMatch, $locale);
            }
        }

        usort($results, static function (array $a, array $b): int {
            $score = ($b['_score'] <=> $a['_score']);
            return $score !== 0 ? $score : strcmp($a['_name'], $b['_name']);
        });

        return array_slice($results, 0, $limit);
    }

    private function surnameStartsWith(array $employee, string $initial, string $locale): bool
    {
        $surname = $this->surname($employee, $locale);
        $characters = preg_split('//u', $surname, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return isset($characters[0]) && text_lower($characters[0]) === text_lower($initial);
    }

    private function surname(array $employee, string $locale): string
    {
        $name = trim($this->localized($employee, 'name', $locale));
        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return (string) ($parts[count($parts) - 1] ?? $name);
    }

    private function score(array $employee, string $term, string $locale): array
    {
        $termLower = text_lower($term);
        $termDigits = only_digits($term);
        $name = $this->localized($employee, 'name', $locale);
        $otherName = $this->localized($employee, 'name', $locale === 'ru' ? 'en' : 'ru');
        $position = $this->localized($employee, 'position', $locale);
        $department = $this->departmentName((int) $employee['department_id'], $locale);

        if (text_lower($name) === $termLower || text_lower($otherName) === $termLower) {
            return [120, 'exact name'];
        }
        if (text_starts_with($name, $term) || text_starts_with($otherName, $term)) {
            return [100, 'name starts'];
        }
        if (text_contains($name, $term) || text_contains($otherName, $term)) {
            return [85, 'name'];
        }

        if ($termDigits !== '' && str_contains(only_digits((string) $employee['phone']), $termDigits)) {
            return [80, 'phone'];
        }
        if (text_contains($position, $term)) {
            return [65, 'position'];
        }
        if (text_contains($department, $term)) {
            return [55, 'department'];
        }
        if (text_contains((string) $employee['email'], $term)) {
            return [50, 'email'];
        }
        if (text_contains($this->localized($employee, 'location', $locale), $term)) {
            return [40, 'location'];
        }

        return [-1, ''];
    }

    public function present(array $employee, string $locale): array
    {
        $employee['_name'] = $this->localized($employee, 'name', $locale);
        $employee['_position'] = $this->localized($employee, 'position', $locale);
        $employee['_department'] = $this->departmentName((int) $employee['department_id'], $locale);
        $employee['_manager'] = $this->employeeName($employee['manager_id'] ? (int) $employee['manager_id'] : null, $locale);
        $employee['_location'] = $this->localized($employee, 'location', $locale);
        $employee['_vacation'] = $this->isOnVacation($employee);
        return $employee;
    }

    private function decorateSearchResult(array $employee, float $score, string $match, string $locale): array
    {
        $employee = $this->present($employee, $locale);
        $employee['_score'] = round($score, 2);
        $matchLabels = $locale === 'ru' ? [
            'exact name' => 'точному имени',
            'name starts' => 'началу имени',
            'name' => 'имени',
            'phone' => 'телефону',
            'position' => 'должности',
            'department' => 'подразделению',
            'email' => 'почте',
            'location' => 'локации',
            'all' => 'all',
        ] : [];
        $employee['_match'] = $matchLabels[$match] ?? $match;
        return $employee;
    }

    public function isOnVacation(array $employee, ?\DateTimeImmutable $date = null): bool
    {
        if (empty($employee['vacation_start']) || empty($employee['vacation_end'])) {
            return false;
        }
        $date ??= new \DateTimeImmutable('today');
        $start = new \DateTimeImmutable((string) $employee['vacation_start']);
        $end = new \DateTimeImmutable((string) $employee['vacation_end']);
        return $date >= $start && $date <= $end;
    }
}
