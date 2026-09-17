<?php

declare(strict_types=1);

namespace App\Controllers;

// Страницы календаря, задач и технического разбора автоматизации сопровождения связи.

use App\Services\CommunicationSupport;
use App\Services\EmployeeDirectory;
use App\Support\DemoData;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;

final class MeetingsController
{
    public function __construct(
        private DemoData $state,
        private EmployeeDirectory $directory,
        private CommunicationSupport $communicationSupport,
    ) {
    }

    public function calendar(): void
    {
        View::render('pages/calendar', $this->page([
            'pageTitle' => t('calendar.title'),
            'active' => 'calendar',
            'events' => $this->communicationSupport->events(),
            'tasks' => $this->communicationSupport->tasks(),
            'employees' => $this->directory->all(),
            'links' => $this->state->get('event_task_links', []),
            'communicationService' => config('communication_service', []),
        ]));
    }

    public function createEvent(Request $request): never
    {
        $title = trim((string) $request->input('title', ''));
        $start = trim((string) $request->input('start', ''));
        $end = trim((string) $request->input('end', ''));

        if ($title === '' || !$this->validDateTime($start) || !$this->validDateTime($end)) {
            Response::json(['ok' => false, 'error' => 'invalid_event'], 422);
        }
        if (new \DateTimeImmutable($end) <= new \DateTimeImmutable($start)) {
            Response::json(['ok' => false, 'error' => 'end_before_start'], 422);
        }

        $attendees = $request->post['attendees'] ?? [];
        if (!is_array($attendees)) {
            $attendees = [$attendees];
        }

        $needsCommunicationSupport = $request->bool('communication_support');
        $description = $needsCommunicationSupport ? (string) $request->input('description', '') : '';

        $event = $this->communicationSupport->createEvent([
            'title' => $title,
            'title_ru' => $title,
            'title_en' => $title,
            'start' => (new \DateTimeImmutable($start))->format(DATE_ATOM),
            'end' => (new \DateTimeImmutable($end))->format(DATE_ATOM),
            'location' => (string) $request->input('location', 'Онлайн'),
            'organizer_id' => (int) $request->input('organizer_id', config('demo_user_id', 1)),
            'attendees' => $attendees,
            'description' => $description,
            'description_ru' => $description,
            'description_en' => $description,
            'communication_support' => $needsCommunicationSupport,
            'simulate_failure' => $request->bool('simulate_failure'),
        ]);

        // В рабочем портале эту операцию забрал бы агент или worker. В песочнице
        // запускаем один шаг сразу, чтобы рекрутер увидел результат без отдельного cron.
        $backgroundJob = !empty($event['communication_support'])
            ? $this->communicationSupport->processNext((int) $event['id'])
            : null;

        Response::json([
            'ok' => true,
            'event' => $event,
            'communication_support' => !empty($event['communication_support']),
            'background_status' => $backgroundJob['status'] ?? null,
            'task_unread_count' => $this->communicationSupport->unreadTaskCount(),
        ]);
    }

    public function shiftEvent(int $id): never
    {
        $event = $this->communicationSupport->shiftEvent($id, 1);
        $backgroundJob = $event ? $this->communicationSupport->processNext($id) : null;

        Response::json([
            'ok' => $event !== null,
            'event' => $event,
            'background_status' => $backgroundJob['status'] ?? null,
            'task_unread_count' => $this->communicationSupport->unreadTaskCount(),
        ], $event ? 200 : 404);
    }

    public function addComment(Request $request, int $id): never
    {
        $text = trim((string) $request->input('text', ''));
        if ($text === '') {
            Response::json(['ok' => false, 'error' => 'empty_comment'], 422);
        }

        $comment = $this->communicationSupport->addComment(
            $id,
            (int) $request->input('author_id', config('demo_user_id', 1)),
            $text
        );
        $backgroundJob = $comment ? $this->communicationSupport->processNext($id) : null;

        Response::json([
            'ok' => $comment !== null,
            'comment' => $comment,
            'background_status' => $backgroundJob['status'] ?? null,
            'task_unread_count' => $this->communicationSupport->unreadTaskCount(),
        ], $comment ? 200 : 404);
    }

    public function deleteEvent(int $id): never
    {
        $ok = $this->communicationSupport->deleteEvent($id);
        $backgroundJob = $ok ? $this->communicationSupport->processNext($id) : null;

        Response::json([
            'ok' => $ok,
            'background_status' => $backgroundJob['status'] ?? null,
            'task_unread_count' => $this->communicationSupport->unreadTaskCount(),
        ], $ok ? 200 : 404);
    }

    public function tasks(): void
    {
        $taskChanges = $this->communicationSupport->unreadTaskChanges();

        // Сам переход в раздел «Задачи» считается просмотром. На карточках
        // изменения ещё подсвечиваются один раз, а счётчик в меню уже сбрасывается.
        $this->communicationSupport->markTaskChangesRead();

        View::render('pages/tasks', $this->page([
            'pageTitle' => t('tasks.title'),
            'active' => 'tasks',
            'tasks' => $this->communicationSupport->tasks(),
            'taskChanges' => $taskChanges,
        ]));
    }

    public function automation(): void
    {
        View::render('pages/automation', $this->page([
            'pageTitle' => t('automation.title'),
            'active' => 'automation',
            'jobs' => $this->communicationSupport->jobs(),
            'logs' => $this->communicationSupport->logs(),
        ]));
    }

    public function processNext(): never
    {
        Response::json(['ok' => true, 'job' => $this->communicationSupport->processNext()]);
    }

    public function processAll(): never
    {
        $jobs = $this->communicationSupport->processAll();
        Response::json(['ok' => true, 'processed_count' => count($jobs), 'jobs' => $jobs]);
    }

    public function retry(int $id): never
    {
        $ok = $this->communicationSupport->retry($id);
        Response::json(['ok' => $ok], $ok ? 200 : 422);
    }

    private function validDateTime(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        try {
            new \DateTimeImmutable($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
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
