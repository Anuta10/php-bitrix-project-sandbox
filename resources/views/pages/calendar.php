<?php
$minStart = (new DateTimeImmutable('+1 hour'))->format('Y-m-d\TH:i');
$defaultEnd = (new DateTimeImmutable('+2 hours'))->format('Y-m-d\TH:i');

$employeeById = [];
foreach ($employees as $employee) {
    $employeeById[(int) $employee['id']] = $employee;
}

$taskById = [];
foreach ($tasks as $task) {
    $taskById[(int) $task['id']] = $task;
}

$communicationAssigneeId = (int) ($communicationService['assignee_id'] ?? 0);
$communicationParticipantIds = array_map('intval', $communicationService['participant_ids'] ?? []);
$communicationAssignee = $communicationAssigneeId ? $directory->find($communicationAssigneeId) : null;
$communicationTeam = [];
foreach ($communicationParticipantIds as $id) {
    if ($person = $directory->find($id)) {
        $communicationTeam[] = $person;
    }
}

$monthStart = new DateTimeImmutable('first day of this month');
$monthEnd = $monthStart->modify('last day of this month');
$monthNames = [
    '01' => 'Январь',
    '02' => 'Февраль',
    '03' => 'Март',
    '04' => 'Апрель',
    '05' => 'Май',
    '06' => 'Июнь',
    '07' => 'Июль',
    '08' => 'Август',
    '09' => 'Сентябрь',
    '10' => 'Октябрь',
    '11' => 'Ноябрь',
    '12' => 'Декабрь',
];
$monthName = $locale === 'ru' ? $monthNames[$monthStart->format('m')] : $monthStart->format('F');
$weekdays = $locale === 'ru'
    ? ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
    : ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
$firstDow = (int) $monthStart->format('N');
$daysInMonth = (int) $monthEnd->format('d');
$eventDays = [];
foreach ($events as $event) {
    $eventDays[(new DateTimeImmutable($event['start']))->format('Y-m-d')] = true;
}
?>

<div class="calendar-page">
    <div class="calendar-page-head">
        <div>
            <span class="module-kicker"><?= $locale === 'ru' ? 'Рабочий календарь' : 'Work calendar' ?></span>
            <h1><?= e(t('calendar.title')) ?></h1>
            <p>
                <?= $locale === 'ru'
                    ? 'Обычная встреча остаётся только в календаре. Задача для службы появляется, только если сотрудник отдельно выбирает «Сопровождение связи».'
                    : 'A regular meeting stays in the calendar. A service task appears only when the employee explicitly selects Communications Support.' ?>
            </p>

            <div class="calendar-custom-note">
                <strong><?= $locale === 'ru' ? 'Что здесь доработано' : 'What is customized here' ?></strong>
                <span>
                    <?= $locale === 'ru'
                        ? 'Календарь и Задачи — стандартные инструменты 1С-Битрикс. Доработка срабатывает только при выбранном «Сопровождении связи»: создаёт одну связанную задачу и обновляет её при изменениях встречи.'
                        : 'Calendar and Tasks are standard 1C-Bitrix tools. The custom layer runs only when Communications Support is selected: it creates one linked task and keeps it updated when the meeting changes.' ?>
                </span>
            </div>

            <div class="communication-flow">
                <span><?= $locale === 'ru' ? 'Календарь' : 'Calendar' ?></span>
                <b>→</b>
                <span><?= $locale === 'ru' ? 'Сопровождение связи' : 'Communications Support' ?></span>
                <b>→</b>
                <span><?= $locale === 'ru' ? 'Связанная задача' : 'Linked task' ?></span>
            </div>
        </div>

        <div class="calendar-head-actions">
            <a href="/automation" class="calendar-tech-link">
                <?= $locale === 'ru' ? 'Как работает автоматизация' : 'How the automation works' ?> →
            </a>
            <button type="button" class="calendar-new-button" data-calendar-form-focus>
                + <?= e(t('calendar.create')) ?>
            </button>
        </div>
    </div>

    <div class="calendar-workspace">
        <aside class="calendar-mini-panel">
            <div class="calendar-month-title">
                <button type="button" aria-label="Previous">‹</button>
                <strong><?= e($monthName) ?> <?= $monthStart->format('Y') ?></strong>
                <button type="button" aria-label="Next">›</button>
            </div>

            <div class="calendar-weekdays">
                <?php foreach ($weekdays as $day): ?>
                    <span><?= e($day) ?></span>
                <?php endforeach; ?>
            </div>

            <div class="calendar-days">
                <?php for ($i = 1; $i < $firstDow; $i++): ?>
                    <span class="is-empty"></span>
                <?php endfor; ?>

                <?php for ($day = 1; $day <= $daysInMonth; $day++):
                    $date = $monthStart->setDate((int) $monthStart->format('Y'), (int) $monthStart->format('m'), $day);
                    $iso = $date->format('Y-m-d');
                    $today = $iso === (new DateTimeImmutable('today'))->format('Y-m-d');
                ?>
                    <span class="<?= $today ? 'is-today' : '' ?> <?= isset($eventDays[$iso]) ? 'has-event' : '' ?>">
                        <?= $day ?>
                    </span>
                <?php endfor; ?>
            </div>

            <div class="calendar-mini-legend">
                <span><i></i><?= $locale === 'ru' ? 'Есть встреча' : 'Has meeting' ?></span>
            </div>
        </aside>

        <section class="calendar-events-panel">
            <div class="calendar-section-head">
                <h2><?= $locale === 'ru' ? 'Ближайшие встречи' : 'Upcoming meetings' ?></h2>
                <span><?= count($events) ?></span>
            </div>

            <div class="calendar-event-list">
                <?php foreach ($events as $event):
                    $eventId = (int) $event['id'];
                    $taskId = (int) ($links[$eventId] ?? 0);
                    $linked = $taskId > 0;
                    $needsSupport = !empty($event['communication_support']);
                    $attendeeNames = [];
                    foreach ($event['attendees'] as $id) {
                        if (isset($employeeById[(int) $id])) {
                            $attendeeNames[] = $directory->localized($employeeById[(int) $id], 'name', $locale);
                        }
                    }
                    $organizer = $directory->find((int) ($event['organizer_id'] ?? 0));
                    $comments = $event['comments'] ?? [];
                ?>
                    <article class="calendar-event">
                        <div class="calendar-event-time">
                            <strong><?= e(format_time($event['start'])) ?></strong>
                            <span><?= e(format_date($event['start'], $locale)) ?></span>
                        </div>

                        <div class="calendar-event-content">
                            <h3><?= e($event['title_' . $locale]) ?></h3>
                            <p>
                                <?= e($event['location']) ?> ·
                                <?= count($event['attendees']) ?> <?= $locale === 'ru' ? 'участников' : 'attendees' ?>
                            </p>

                            <?php if ($organizer): ?>
                                <small class="communication-event-organizer">
                                    <?= $locale === 'ru' ? 'Организатор:' : 'Organizer:' ?>
                                    <?= e($directory->localized($organizer, 'name', $locale)) ?>
                                </small>
                            <?php endif; ?>

                            <?php if (!empty($event['description_' . $locale])): ?>
                                <p class="communication-event-description"><?= e($event['description_' . $locale]) ?></p>
                            <?php endif; ?>

                            <div class="calendar-attendees">
                                <?php foreach (array_slice($attendeeNames, 0, 4) as $name): ?>
                                    <span class="avatar avatar-xs" title="<?= e($name) ?>"><?= e(initials($name)) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($attendeeNames) > 4): ?>
                                    <small>+<?= count($attendeeNames) - 4 ?></small>
                                <?php endif; ?>
                            </div>

                            <?php if ($needsSupport): ?>
                                <div class="linked-task <?= $linked ? 'is-linked' : 'is-pending' ?>">
                                    <span><?= $linked ? '✓' : '↻' ?></span>
                                    <div>
                                        <strong>
                                            <?= $linked
                                                ? ($locale === 'ru' ? 'Сопровождение связи · задача #' : 'Communications Support · task #') . $taskId
                                                : ($locale === 'ru' ? 'Сопровождение связи выбрано · ждёт обработки' : 'Communications Support selected · waiting for processing') ?>
                                        </strong>
                                        <?php if ($linked && isset($taskById[$taskId])): ?>
                                            <small><?= e($taskById[$taskId]['title_' . $locale]) ?></small>
                                        <?php else: ?>
                                            <small><?= $locale === 'ru' ? 'После фоновой обработки появится одна связанная задача.' : 'One linked task appears after background processing.' ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="linked-task is-off">
                                    <span>○</span>
                                    <div>
                                        <strong><?= $locale === 'ru' ? 'Сопровождение связи не требуется' : 'Communications Support is not required' ?></strong>
                                        <small><?= $locale === 'ru' ? 'Это обычное событие календаря, задача не создаётся.' : 'This is a regular calendar event, so no task is created.' ?></small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <details class="event-comments">
                                <summary>
                                    <?= $locale === 'ru' ? 'Комментарии к встрече' : 'Meeting comments' ?>
                                    <?php if ($comments): ?><span><?= count($comments) ?></span><?php endif; ?>
                                </summary>

                                <?php if ($comments): ?>
                                    <div class="event-comments-list">
                                        <?php foreach ($comments as $comment):
                                            $author = $directory->find((int) ($comment['author_id'] ?? 0));
                                        ?>
                                            <div>
                                                <strong><?= e($author ? $directory->localized($author, 'name', $locale) : '—') ?></strong>
                                                <small><?= e(format_date((string) $comment['created_at'], $locale, true)) ?></small>
                                                <p><?= e($comment['text']) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <form class="event-comment-form" data-event-comment-form data-event-id="<?= $eventId ?>" data-support="<?= $needsSupport ? '1' : '0' ?>">
                                    <input type="hidden" name="author_id" value="<?= (int) config('demo_user_id', 1) ?>">
                                    <input name="text" maxlength="500" placeholder="<?= $locale === 'ru' ? 'Например: добавьте подключение удалённого участника' : 'For example: add a remote participant connection' ?>" required>
                                    <button type="submit"><?= $locale === 'ru' ? 'Добавить' : 'Add' ?></button>
                                </form>
                            </details>
                        </div>

                        <div class="calendar-event-actions">
                            <button type="button" data-shift-event data-event-id="<?= $eventId ?>">
                                <?= e(t('calendar.shift')) ?>
                            </button>
                            <button type="button" class="is-danger" data-delete-event data-event-id="<?= $eventId ?>">
                                <?= e(t('calendar.delete')) ?>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <section class="calendar-editor communication-editor" id="new-meeting">
        <div class="calendar-editor-title">
            <div>
                <span><?= $locale === 'ru' ? 'Событие календаря' : 'Calendar event' ?></span>
                <h2><?= e(t('calendar.create')) ?></h2>
            </div>
            <small>
                <?= $locale === 'ru'
                    ? 'После сохранения обычная встреча останется в календаре. Фоновая обработка запускается только при выбранном сопровождении связи.'
                    : 'After saving, a regular meeting stays in the calendar. Background processing starts only when Communications Support is selected.' ?>
            </small>
        </div>

        <form data-calendar-form>
            <div class="calendar-editor-main">
                <label class="calendar-field-wide">
                    <span><?= e(t('calendar.name')) ?></span>
                    <input name="title" required value="<?= $locale === 'ru' ? 'Совещание проектной группы' : 'Project team meeting' ?>">
                </label>

                <div class="calendar-field-grid">
                    <label>
                        <span><?= e(t('calendar.start')) ?></span>
                        <input type="datetime-local" name="start" required value="<?= e($minStart) ?>">
                    </label>
                    <label>
                        <span><?= e(t('calendar.end')) ?></span>
                        <input type="datetime-local" name="end" required value="<?= e($defaultEnd) ?>">
                    </label>
                </div>

                <label>
                    <span><?= e(t('calendar.location')) ?></span>
                    <input name="location" value="<?= $locale === 'ru' ? 'Переговорная «Орион»' : 'Orion meeting room' ?>">
                </label>

                <label>
                    <span><?= $locale === 'ru' ? 'Организатор' : 'Organizer' ?></span>
                    <select name="organizer_id">
                        <?php foreach (array_slice($employees, 0, 18) as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>" <?= (int) $employee['id'] === 3 ? 'selected' : '' ?>>
                                <?= e($directory->localized($employee, 'name', $locale)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <span><?= e(t('calendar.attendees')) ?></span>
                    <select name="attendees[]" multiple size="6">
                        <?php foreach (array_slice($employees, 0, 24) as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>" <?= in_array((int) $employee['id'], [3, 10, 11, 13], true) ? 'selected' : '' ?>>
                                <?= e($directory->localized($employee, 'name', $locale)) ?> —
                                <?= e($directory->localized($employee, 'position', $locale)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="communication-support-toggle">
                    <input type="checkbox" name="communication_support" value="1" data-communication-support>
                    <span>
                        <strong><?= $locale === 'ru' ? 'Сопровождение связи' : 'Communications Support' ?></strong>
                        <small><?= $locale === 'ru' ? 'Выберите только если для встречи нужна видеосвязь, оборудование или помощь инженера.' : 'Select only when the meeting needs video conferencing, room equipment or an engineer.' ?></small>
                    </span>
                </label>

                <label class="calendar-field-wide" data-communication-details hidden>
                    <span><?= $locale === 'ru' ? 'Что нужно подготовить службе связи' : 'What Communications Support should prepare' ?></span>
                    <textarea name="description" rows="4"><?= $locale === 'ru' ? 'Нужно подключить экран и обеспечить видеосвязь с удалённым участником.' : 'Please prepare the display and a video link for a remote participant.' ?></textarea>
                </label>

                <details class="calendar-tech-option" data-communication-details hidden>
                    <summary><?= $locale === 'ru' ? 'Технический сценарий для демонстрации' : 'Technical demo scenario' ?></summary>
                    <label>
                        <input type="checkbox" name="simulate_failure" value="1">
                        <span><?= e(t('calendar.simulate')) ?></span>
                    </label>
                </details>
            </div>

            <aside class="calendar-editor-side">
                <div class="communication-team-card" data-communication-details hidden>
                    <small><?= $locale === 'ru' ? 'Получатель' : 'Recipient' ?></small>
                    <strong><?= $locale === 'ru' ? 'Сопровождение связи' : 'Communications Support' ?></strong>

                    <?php if ($communicationAssignee): ?>
                        <p>
                            <?= $locale === 'ru' ? 'Ответственный:' : 'Assignee:' ?>
                            <?= e($directory->localized($communicationAssignee, 'name', $locale)) ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($communicationTeam): ?>
                        <p>
                            <?= $locale === 'ru' ? 'Соисполнители:' : 'Participants:' ?>
                            <?= e(implode(', ', array_map(fn(array $person): string => $directory->localized($person, 'name', $locale), $communicationTeam))) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="calendar-editor-help">
                    <strong><?= $locale === 'ru' ? 'Как работает сценарий' : 'How the scenario works' ?></strong>
                    <p><?= $locale === 'ru' ? 'Без дополнительной услуги встреча просто сохраняется в календаре.' : 'Without the extra service, the meeting is simply saved in the calendar.' ?></p>
                    <ol data-communication-details hidden>
                        <li><?= $locale === 'ru' ? 'Отмеченное событие попадает в фоновую обработку.' : 'The selected event goes to background processing.' ?></li>
                        <li><?= $locale === 'ru' ? 'Группа «Сопровождение связи» получает одну связанную задачу.' : 'The support team receives one linked task.' ?></li>
                        <li><?= $locale === 'ru' ? 'Переносы и комментарии обновляют эту же задачу.' : 'Date changes and comments update that same task.' ?></li>
                    </ol>
                    <a href="/automation"><?= $locale === 'ru' ? 'Посмотреть, что происходит под капотом' : 'See what happens under the hood' ?> →</a>
                </div>

                <button class="calendar-save-button" type="submit">
                    <?= $locale === 'ru' ? 'Сохранить событие' : 'Save event' ?>
                </button>
            </aside>
        </form>
    </section>
</div>
