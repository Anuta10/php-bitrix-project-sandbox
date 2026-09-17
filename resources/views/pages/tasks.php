<div class="page-head">
    <div>
        <h1><?= e(t('tasks.title')) ?></h1>
        <p><?= e(t('tasks.subtitle')) ?></p>

        <div class="calendar-custom-note">
            <strong><?= $locale === 'ru' ? 'Что здесь доработано' : 'What is customized here' ?></strong>
            <span>
                <?= $locale === 'ru'
                    ? 'Задачи — стандартный функционал 1С-Битрикс. В этом кейсе показана доработка для «Сопровождения связи»: задача создаётся только из отмеченного события календаря и получает время, место, участников, описание и комментарии.'
                    : 'Tasks are a standard 1C-Bitrix feature. This case shows the Communications Support workflow: a task is created only from a selected calendar event and receives its time, place, attendees, description and comments.' ?>
            </span>
        </div>
    </div>

    <div class="page-head-actions">
        <a class="button button-secondary" href="/calendar"><?= e(t('calendar.title')) ?> →</a>
        <a class="button button-ghost" href="/automation">
            <?= $locale === 'ru' ? 'Как работает автоматизация' : 'How the automation works' ?> →
        </a>
    </div>
</div>

<div class="task-board">
    <?php foreach ($tasks as $task):
        $assignee = $directory->find((int) $task['assignee_id']);
        $participants = [];
        foreach ($task['participant_ids'] ?? [] as $participantId) {
            if ($person = $directory->find((int) $participantId)) {
                $participants[] = $person;
            }
        }

        $isCommunicationTask = ($task['source'] ?? '') === 'calendar_communication_support';
        $meeting = $task['meeting'] ?? null;
        $taskChange = $taskChanges[(int) $task['id']] ?? null;
        $changeLabel = match ($taskChange['kind'] ?? '') {
            'created' => $locale === 'ru' ? 'Новая' : 'New',
            'comment' => $locale === 'ru' ? 'Новый комментарий' : 'New comment',
            'updated' => $locale === 'ru' ? 'Обновлена' : 'Updated',
            default => '',
        };
    ?>
        <article class="task-card <?= $isCommunicationTask ? 'communication-task-card' : '' ?> <?= $taskChange ? 'has-unseen-change' : '' ?>">
            <div class="task-card-top">
                <div class="task-card-statuses">
                    <span class="status-pill status-open"><?= e(t('tasks.open')) ?></span>
                    <?php if ($changeLabel !== ''): ?>
                        <span class="task-change-pill"><?= e($changeLabel) ?></span>
                    <?php endif; ?>
                </div>
                <strong>#<?= (int) $task['id'] ?></strong>
            </div>

            <h3><?= e($task['title_' . $locale]) ?></h3>

            <?php if ($isCommunicationTask): ?>
                <div class="communication-task-label">
                    <?= $locale === 'ru' ? 'Сопровождение связи' : 'Communications Support' ?>
                </div>
            <?php endif; ?>

            <div class="task-meta">
                <div>
                    <span><?= e(t('tasks.assignee')) ?></span>
                    <strong><?= e($assignee ? $directory->localized($assignee, 'name', $locale) : '—') ?></strong>
                </div>
                <div>
                    <span><?= e(t('tasks.deadline')) ?></span>
                    <strong><?= e(format_date($task['deadline'], $locale, true)) ?></strong>
                </div>
            </div>

            <?php if ($participants): ?>
                <div class="communication-task-team">
                    <span><?= $locale === 'ru' ? 'Соисполнители' : 'Participants' ?></span>
                    <p><?= e(implode(', ', array_map(
                        fn(array $person): string => $directory->localized($person, 'name', $locale),
                        $participants
                    ))) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($meeting):
                $organizer = $directory->find((int) ($meeting['organizer_id'] ?? 0));
                $meetingPeople = [];
                foreach ($meeting['attendee_ids'] ?? [] as $personId) {
                    if ($person = $directory->find((int) $personId)) {
                        $meetingPeople[] = $directory->localized($person, 'name', $locale);
                    }
                }
                $description = (string) ($meeting['description_' . $locale] ?? $meeting['description_ru'] ?? '');
            ?>
                <div class="communication-task-details">
                    <h4><?= $locale === 'ru' ? 'Информация из календаря' : 'Calendar details' ?></h4>

                    <dl>
                        <div>
                            <dt><?= $locale === 'ru' ? 'Событие' : 'Event' ?></dt>
                            <dd><?= e($meeting['title_' . $locale] ?? '') ?></dd>
                        </div>
                        <div>
                            <dt><?= $locale === 'ru' ? 'Организатор' : 'Organizer' ?></dt>
                            <dd><?= e($organizer ? $directory->localized($organizer, 'name', $locale) : '—') ?></dd>
                        </div>
                        <div>
                            <dt><?= $locale === 'ru' ? 'Дата и время' : 'Date and time' ?></dt>
                            <dd><?= e(format_date((string) $meeting['start'], $locale, true)) ?> — <?= e(format_time((string) $meeting['end'])) ?></dd>
                        </div>
                        <div>
                            <dt><?= $locale === 'ru' ? 'Место' : 'Location' ?></dt>
                            <dd><?= e($meeting['location'] ?? '—') ?></dd>
                        </div>
                    </dl>

                    <?php if ($meetingPeople): ?>
                        <div class="communication-task-block">
                            <strong><?= $locale === 'ru' ? 'Участники встречи' : 'Meeting attendees' ?></strong>
                            <p><?= e(implode(', ', $meetingPeople)) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($description !== ''): ?>
                        <div class="communication-task-block">
                            <strong><?= $locale === 'ru' ? 'Что нужно подготовить' : 'Support request' ?></strong>
                            <p><?= e($description) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($meeting['comments'])): ?>
                        <div class="communication-task-block">
                            <strong><?= $locale === 'ru' ? 'Комментарии из события' : 'Comments from the event' ?></strong>
                            <div class="communication-task-comments">
                                <?php foreach ($meeting['comments'] as $comment):
                                    $author = $directory->find((int) ($comment['author_id'] ?? 0));
                                ?>
                                    <p>
                                        <b><?= e($author ? $directory->localized($author, 'name', $locale) : '—') ?>:</b>
                                        <?= e($comment['text']) ?>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <a href="/calendar" class="communication-task-calendar-link">
                        <?= $locale === 'ru' ? '← Вернуться к событию календаря' : '← Back to calendar event' ?>
                    </a>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
