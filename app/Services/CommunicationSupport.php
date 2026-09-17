<?php

declare(strict_types=1);

// Связывает события календаря с задачами группы «Сопровождение связи».
// Обычные встречи здесь не трогаем: автоматизация включается только тогда,
// когда пользователь явно выбрал эту услугу в событии.

namespace App\Services;

use App\Support\DemoData;

final class CommunicationSupport
{
    public function __construct(
        private DemoData $state,
        private EmployeeDirectory $directory,
        private Notifications $notifications,
    ) {
    }

    public function events(): array
    {
        $rows = $this->state->get('events', []);
        usort($rows, static fn(array $a, array $b): int => strcmp((string) $a['start'], (string) $b['start']));
        return $rows;
    }

    public function tasks(): array
    {
        $rows = $this->state->get('tasks', []);
        usort($rows, static fn(array $a, array $b): int => strcmp((string) $a['deadline'], (string) $b['deadline']));
        return $rows;
    }

    public function jobs(): array
    {
        $rows = $this->state->get('support_jobs', []);
        usort($rows, static fn(array $a, array $b): int => ((int) $b['id']) <=> ((int) $a['id']));
        return $rows;
    }

    public function logs(): array
    {
        $rows = $this->state->get('support_logs', []);
        usort($rows, static fn(array $a, array $b): int => strcmp((string) $b['created_at'], (string) $a['created_at']));
        return $rows;
    }

    public function unreadTaskChanges(): array
    {
        $updates = $this->state->get('task_updates', []);
        return is_array($updates) ? $updates : [];
    }

    public function unreadTaskCount(): int
    {
        return count($this->unreadTaskChanges());
    }

    public function markTaskChangesRead(): void
    {
        $this->state->set('task_updates', []);
    }

    public function createEvent(array $payload): array
    {
        $events = $this->state->get('events', []);
        $needsCommunicationSupport = !empty($payload['communication_support']);

        $event = [
            'id' => $this->nextId($events, 100),
            'title_ru' => trim((string) ($payload['title_ru'] ?? $payload['title'] ?? 'Новая встреча')),
            'title_en' => trim((string) ($payload['title_en'] ?? $payload['title'] ?? 'New meeting')),
            'start' => (string) $payload['start'],
            'end' => (string) $payload['end'],
            'location' => trim((string) ($payload['location'] ?? 'Онлайн')),
            'organizer_id' => (int) ($payload['organizer_id'] ?? config('demo_user_id', 1)),
            'attendees' => array_values(array_unique(array_map('intval', $payload['attendees'] ?? []))),
            'description_ru' => trim((string) ($payload['description_ru'] ?? $payload['description'] ?? '')),
            'description_en' => trim((string) ($payload['description_en'] ?? $payload['description'] ?? '')),
            'comments' => [],
            'communication_support' => $needsCommunicationSupport,
            'simulate_failure' => $needsCommunicationSupport && !empty($payload['simulate_failure']),
        ];

        $events[] = $event;
        $this->state->set('events', $events);

        // Без отметки «Сопровождение связи» событие остаётся обычной встречей.
        if ($needsCommunicationSupport) {
            $this->queue((int) $event['id'], 'create', (bool) $event['simulate_failure']);
        }

        return $event;
    }

    public function shiftEvent(int $eventId, int $days = 1): ?array
    {
        $events = $this->state->get('events', []);
        $updated = null;

        foreach ($events as &$event) {
            if ((int) $event['id'] !== $eventId) {
                continue;
            }

            $event['start'] = (new \DateTimeImmutable((string) $event['start']))
                ->modify('+' . $days . ' day')
                ->format(DATE_ATOM);
            $event['end'] = (new \DateTimeImmutable((string) $event['end']))
                ->modify('+' . $days . ' day')
                ->format(DATE_ATOM);
            $updated = $event;
            break;
        }
        unset($event);

        if (!$updated) {
            return null;
        }

        $this->state->set('events', $events);

        // Пользователь сразу видит изменённую встречу. Связанная задача обновляется
        // отдельно и только для мероприятий, где сопровождение действительно заказано.
        if ($this->needsSupport($updated)) {
            $this->queue($eventId, 'update');
        }

        return $updated;
    }

    public function addComment(int $eventId, int $authorId, string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $events = $this->state->get('events', []);
        $created = null;
        $eventNeedsSupport = false;

        foreach ($events as &$event) {
            if ((int) $event['id'] !== $eventId) {
                continue;
            }

            $comments = $event['comments'] ?? [];
            $created = [
                'id' => $this->nextId($comments, 0),
                'author_id' => $authorId,
                'created_at' => (new \DateTimeImmutable('now'))->format(DATE_ATOM),
                'text' => $text,
            ];
            $comments[] = $created;
            $event['comments'] = $comments;
            $eventNeedsSupport = $this->needsSupport($event);
            break;
        }
        unset($event);

        if (!$created) {
            return null;
        }

        $this->state->set('events', $events);

        // Комментарии обычной встречи остаются только в календаре. Для заявки
        // на сопровождение тот же комментарий позже попадёт в связанную задачу.
        if ($eventNeedsSupport) {
            $this->queue($eventId, 'comment');
        }

        return $created;
    }

    public function deleteEvent(int $eventId): bool
    {
        $events = $this->state->get('events', []);
        $event = $this->findEvent($eventId);
        if (!$event) {
            return false;
        }

        $events = array_values(array_filter(
            $events,
            static fn(array $row): bool => (int) $row['id'] !== $eventId
        ));
        $this->state->set('events', $events);

        if ($this->needsSupport($event) || $this->linkedTaskId($eventId) !== null) {
            $this->queue($eventId, 'delete');
        }

        return true;
    }

    public function processNext(?int $eventId = null): ?array
    {
        $jobs = $this->state->get('support_jobs', []);
        $targetIndex = null;

        foreach ($jobs as $index => $job) {
            if (($job['status'] ?? '') !== 'queued') {
                continue;
            }
            if ($eventId !== null && (int) ($job['event_id'] ?? 0) !== $eventId) {
                continue;
            }
            $targetIndex = $index;
            break;
        }

        if ($targetIndex === null) {
            return null;
        }

        $started = microtime(true);
        $job = $jobs[$targetIndex];
        $job['attempt'] = (int) $job['attempt'] + 1;
        $job['status'] = 'processing';
        $job['updated_at'] = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        $jobs[$targetIndex] = $job;
        $this->state->set('support_jobs', $jobs);

        if (!empty($job['fail_once']) && (int) $job['attempt'] === 1) {
            usleep(30000);
            $jobs = $this->state->get('support_jobs', []);
            $jobs[$targetIndex]['status'] = 'failed';
            $jobs[$targetIndex]['last_error'] = 'Временная ошибка демо-обработчика';
            $jobs[$targetIndex]['updated_at'] = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
            $this->state->set('support_jobs', $jobs);

            $duration = max(1, (int) round((microtime(true) - $started) * 1000));
            $this->writeLog(
                $job,
                null,
                'failed',
                $duration,
                'Временная ошибка. Обработку можно безопасно повторить.',
                'Temporary error. The job can be safely retried.'
            );
            return $jobs[$targetIndex];
        }

        $event = $this->findEvent((int) $job['event_id']);
        $taskId = null;

        if ($job['action'] === 'delete') {
            $taskId = $this->deleteLinkedTask((int) $job['event_id']);
            $messageRu = 'Связанная задача сопровождения связи удалена вместе с событием.';
            $messageEn = 'The linked communications-support task was removed with the event.';
        } elseif ($event && $this->needsSupport($event)) {
            $taskId = $this->saveTaskFromEvent($event);
            $this->rememberTaskChange($taskId, (string) $job['action']);
            $messageRu = match ($job['action']) {
                'create' => 'По событию календаря создана задача для группы сопровождения связи.',
                'comment' => 'Комментарий из календаря перенесён в связанную задачу.',
                default => 'Связанная задача обновлена по изменениям календаря.',
            };
            $messageEn = match ($job['action']) {
                'create' => 'A communications-support task was created from the calendar event.',
                'comment' => 'The calendar comment was copied to the linked task.',
                default => 'The linked task was updated from the calendar changes.',
            };
        } else {
            $messageRu = 'Сопровождение связи для этого события не требуется. Задача не создавалась.';
            $messageEn = 'Communications support is not required for this event. No task was created.';
        }

        usleep(20000);
        $jobs = $this->state->get('support_jobs', []);
        $jobs[$targetIndex]['status'] = 'success';
        $jobs[$targetIndex]['last_error'] = null;
        $jobs[$targetIndex]['updated_at'] = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        $this->state->set('support_jobs', $jobs);

        $duration = max(1, (int) round((microtime(true) - $started) * 1000));
        $this->writeLog($job, $taskId, 'success', $duration, $messageRu, $messageEn);
        $this->notifications->add('communication_support', 'portal', 1, $messageRu, $messageEn);
        return $jobs[$targetIndex];
    }

    public function processAll(int $max = 20): array
    {
        $processed = [];
        for ($i = 0; $i < $max; $i++) {
            $job = $this->processNext();
            if ($job === null) {
                break;
            }
            $processed[] = $job;
        }
        return $processed;
    }

    public function retry(int $jobId): bool
    {
        $jobs = $this->state->get('support_jobs', []);

        foreach ($jobs as &$job) {
            if ((int) $job['id'] !== $jobId || ($job['status'] ?? '') !== 'failed') {
                continue;
            }

            $job['status'] = 'queued';
            $job['last_error'] = null;
            $job['updated_at'] = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
            $this->state->set('support_jobs', $jobs);
            unset($job);
            return true;
        }
        unset($job);

        return false;
    }

    public function linkedTaskId(int $eventId): ?int
    {
        $links = $this->state->get('event_task_links', []);
        return isset($links[$eventId]) ? (int) $links[$eventId] : null;
    }

    public function findEvent(int $eventId): ?array
    {
        foreach ($this->state->get('events', []) as $event) {
            if ((int) $event['id'] === $eventId) {
                return $event;
            }
        }
        return null;
    }

    private function queue(int $eventId, string $action, bool $failOnce = false): array
    {
        $jobs = $this->state->get('support_jobs', []);
        $job = [
            'id' => $this->nextId($jobs, 900),
            'event_id' => $eventId,
            'action' => $action,
            'status' => 'queued',
            'attempt' => 0,
            'fail_once' => $failOnce,
            'created_at' => (new \DateTimeImmutable('now'))->format(DATE_ATOM),
            'updated_at' => (new \DateTimeImmutable('now'))->format(DATE_ATOM),
            'last_error' => null,
        ];
        $jobs[] = $job;
        $this->state->set('support_jobs', $jobs);
        return $job;
    }

    private function needsSupport(array $event): bool
    {
        return !empty($event['communication_support']);
    }

    private function saveTaskFromEvent(array $event): int
    {
        $tasks = $this->state->get('tasks', []);
        $links = $this->state->get('event_task_links', []);
        $eventId = (int) $event['id'];
        $taskId = isset($links[$eventId]) ? (int) $links[$eventId] : null;
        $service = config('communication_service', []);

        $taskData = [
            'title_ru' => 'Сопровождение связи: ' . $event['title_ru'],
            'title_en' => 'Communications support: ' . $event['title_en'],
            'assignee_id' => (int) ($service['assignee_id'] ?? 37),
            'participant_ids' => array_map('intval', $service['participant_ids'] ?? [38, 39]),
            'deadline' => $event['end'],
            'status' => 'open',
            'source' => 'calendar_communication_support',
            'event_id' => $eventId,
            'meeting' => [
                'title_ru' => $event['title_ru'],
                'title_en' => $event['title_en'],
                'organizer_id' => (int) ($event['organizer_id'] ?? config('demo_user_id', 1)),
                'start' => $event['start'],
                'end' => $event['end'],
                'location' => $event['location'],
                'attendee_ids' => array_map('intval', $event['attendees'] ?? []),
                'description_ru' => (string) ($event['description_ru'] ?? ''),
                'description_en' => (string) ($event['description_en'] ?? ''),
                'comments' => array_values($event['comments'] ?? []),
            ],
        ];

        // Для одного события всегда одна задача. Перенос встречи, изменения участников
        // и новые комментарии обновляют существующую запись, а не создают дубль.
        if ($taskId) {
            foreach ($tasks as &$task) {
                if ((int) $task['id'] === $taskId) {
                    $task = array_merge($task, $taskData);
                    break;
                }
            }
            unset($task);
        } else {
            $taskId = $this->nextId($tasks, 400);
            $taskData['id'] = $taskId;
            $tasks[] = $taskData;
            $links[$eventId] = $taskId;
        }

        $this->state->set('tasks', $tasks);
        $this->state->set('event_task_links', $links);
        return $taskId;
    }

    private function deleteLinkedTask(int $eventId): ?int
    {
        $links = $this->state->get('event_task_links', []);
        if (!isset($links[$eventId])) {
            return null;
        }

        $taskId = (int) $links[$eventId];
        $tasks = array_values(array_filter(
            $this->state->get('tasks', []),
            static fn(array $task): bool => (int) $task['id'] !== $taskId
        ));

        unset($links[$eventId]);
        $this->state->set('tasks', $tasks);
        $this->state->set('event_task_links', $links);

        $updates = $this->unreadTaskChanges();
        unset($updates[$taskId]);
        $this->state->set('task_updates', $updates);
        return $taskId;
    }

    private function rememberTaskChange(int $taskId, string $action): void
    {
        $updates = $this->unreadTaskChanges();
        $updates[$taskId] = [
            'task_id' => $taskId,
            'kind' => match ($action) {
                'create' => 'created',
                'comment' => 'comment',
                default => 'updated',
            },
            'updated_at' => (new \DateTimeImmutable('now'))->format(DATE_ATOM),
        ];

        // Считаем задачи, а не количество технических событий: если одна и та же
        // задача обновилась несколько раз, в меню всё равно будет один маркер.
        $this->state->set('task_updates', $updates);
    }

    private function writeLog(
        array $job,
        ?int $taskId,
        string $status,
        int $durationMs,
        string $messageRu,
        string $messageEn
    ): void {
        $logs = $this->state->get('support_logs', []);
        $logs[] = [
            'id' => $this->nextId($logs, 0),
            'created_at' => (new \DateTimeImmutable('now'))->format(DATE_ATOM),
            'job_id' => (int) $job['id'],
            'event_id' => (int) $job['event_id'],
            'task_id' => $taskId,
            'action' => (string) $job['action'],
            'status' => $status,
            'attempt' => (int) $job['attempt'],
            'duration_ms' => $durationMs,
            'message_ru' => $messageRu,
            'message_en' => $messageEn,
        ];
        $this->state->set('support_logs', $logs);
    }

    private function nextId(array $rows, int $floor): int
    {
        $max = $floor;
        foreach ($rows as $row) {
            $max = max($max, (int) ($row['id'] ?? 0));
        }
        return $max + 1;
    }
}
