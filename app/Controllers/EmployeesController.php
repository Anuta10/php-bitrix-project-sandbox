<?php

declare(strict_types=1);

namespace App\Controllers;

// Телефонный справочник: список, карточка сотрудника и AJAX-подгрузка результатов.

use App\Services\EmployeeDirectory;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;

final class EmployeesController
{
    public function __construct(private EmployeeDirectory $directory)
    {
    }

    public function index(Request $request): void
    {
        $query = trim((string) $request->input('q', ''));
        $department = (int) $request->input('department', 0);
        $letter = $this->firstLetter((string) $request->input('letter', ''));
        $departmentId = $department > 0 ? $department : null;

        $allResults = $this->directory->search($query, $departmentId, current_locale(), 500, $letter ?: null);
        $pageSize = 10;
        $terms = array_values(array_unique(array_filter([$query, $query !== '' ? keyboard_swap($query) : ''])));

        View::render('pages/employees', $this->page([
            'pageTitle' => t('employees.title'),
            'active' => 'employees',
            'query' => $query,
            'letter' => $letter,
            'selectedDepartment' => $departmentId,
            'results' => array_slice($allResults, 0, $pageSize),
            'totalResults' => count($allResults),
            'pageSize' => $pageSize,
            'highlightTerms' => $terms,
            'tree' => $this->directory->departmentTree(current_locale()),
            'departments' => $this->directory->departments(),
        ]));
    }

    public function profile(int $id): void
    {
        $employee = $this->directory->find($id);
        if (!$employee) {
            Response::notFound();
        }
        $employee = $this->directory->present($employee, current_locale());

        View::render('pages/employee-profile', $this->page([
            'pageTitle' => $employee['_name'],
            'active' => 'employees',
            'employee' => $employee,
        ]));
    }

    public function search(Request $request): never
    {
        $query = trim((string) $request->input('q', ''));
        $department = (int) $request->input('department', 0);
        $letter = $this->firstLetter((string) $request->input('letter', ''));
        $offset = max(0, (int) $request->input('offset', 0));
        $limit = max(1, min(25, (int) $request->input('limit', 10)));

        $allRows = $this->directory->search(
            $query,
            $department > 0 ? $department : null,
            current_locale(),
            500,
            $letter ?: null
        );
        $rows = array_slice($allRows, $offset, $limit);

        Response::json([
            'ok' => true,
            'results' => $rows,
            'total' => count($allRows),
            'offset' => $offset,
            'next_offset' => $offset + count($rows),
            'has_more' => $offset + count($rows) < count($allRows),
            'search_terms' => array_values(array_unique(array_filter([$query, $query !== '' ? keyboard_swap($query) : '']))),
            'letter' => $letter,
        ]);
    }

    private function firstLetter(string $value): string
    {
        $chars = preg_split('//u', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return (string) ($chars[0] ?? '');
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
