<?php

declare(strict_types=1);

namespace App\Controllers;

// Главные страницы портала: стартовая, рабочая главная, кейсы и раздел об авторе.

use App\Services\Birthdays;
use App\Services\EmployeeDirectory;
use App\Services\CommunicationSupport;
use App\Support\DemoData;
use App\Support\View;

final class HomeController
{
    public function __construct(
        private DemoData $state,
        private EmployeeDirectory $directory,
        private Birthdays $birthdays,
        private CommunicationSupport $communicationSupport,
    ) {
    }

    public function portfolio(): void
    {
        View::render('pages/portfolio', $this->page([
            'pageTitle' => t('nav.portfolio'),
            'active' => 'portfolio',
            'standalonePortfolio' => true,
            'cases' => $this->state->seed('cases'),
        ]));
    }

    public function dashboard(): void
    {
        $employees = $this->directory->all();
        $departments = $this->directory->departments();
        $birthdays = $this->birthdays->upcoming(30, current_locale());
        $tasks = array_values(array_filter(
            $this->communicationSupport->tasks(),
            static fn(array $task): bool => ($task['status'] ?? '') === 'open'
        ));
        View::render('pages/dashboard', $this->page([
            'pageTitle' => t('dashboard.title'),
            'active' => 'dashboard',
            'stats' => [
                'employees' => count($employees),
                'departments' => count($departments) - 1,
                'birthdays' => count($birthdays),
                'tasks' => count($tasks),
            ],
            'birthdays' => array_slice($birthdays, 0, 5),
            'events' => array_slice($this->communicationSupport->events(), 0, 4),
            'newEmployees' => array_slice(array_reverse($employees), 0, 5),
            'news' => $this->state->seed('news'),
            'announcements' => $this->state->seed('announcements'),
            'feed' => $this->state->seed('feed'),
        ]));
    }

    public function cases(): void
    {
        View::render('pages/cases', $this->page([
            'pageTitle' => t('cases.title'),
            'active' => 'cases',
            'cases' => $this->state->seed('cases'),
        ]));
    }

    public function about(): void
    {
        $certificates = [
            ['ru' => 'Администратор. Базовый', 'en' => 'Administrator. Basic', 'date' => '2023-08-30'],
            ['ru' => 'Интеграция дизайна и настройка платформы', 'en' => 'Design Integration and Platform Configuration', 'date' => '2023-11-06'],
            ['ru' => 'Технология Композитный сайт', 'en' => 'Composite Site Technology', 'date' => '2023-12-25'],
            ['ru' => 'Bitrix Framework: основные технологии и расширение типовых возможностей системы', 'en' => 'Bitrix Framework: Core Technologies and Extending Standard Features', 'date' => '2024-07-30'],
        ];

        View::render('pages/about', $this->page([
            'pageTitle' => t('about.title'),
            'active' => 'about',
            'certificates' => $certificates,
        ]));
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
