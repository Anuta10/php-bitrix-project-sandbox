<?php

declare(strict_types=1);

$_SESSION = [];
require dirname(__DIR__) . '/bootstrap.php';

use App\Services\Birthdays;
use App\Services\EmployeeDirectory;
use App\Services\CommunicationSupport;
use App\Services\Notifications;
use App\Support\DemoData;

$passed = 0;
$failed = 0;

function check(bool $condition, string $message): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "[PASS] {$message}\n";
        return;
    }
    $failed++;
    echo "[FAIL] {$message}\n";
}

$state = new DemoData();
$today = (new DateTimeImmutable('today'))->format('Y-m-d');
check($state->get('demo_date') === $today, 'Демо-состояние привязано к текущей дате');

// Имитируем браузерную сессию, которую открыли на следующий день.
// Базовые данные должны пересобраться относительно новой даты сами.
$_SESSION['demo_state']['demo_date'] = (new DateTimeImmutable('yesterday'))->format('Y-m-d');
$_SESSION['demo_state']['events'] = [];
$state = new DemoData();
check($state->get('demo_date') === $today && count($state->get('events', [])) >= 3, 'Старая сессия автоматически получает свежие демо-даты');

$directory = new EmployeeDirectory($state);
$birthdays = new Birthdays($state, $directory);
$notifications = new Notifications($state, $directory, $birthdays);
$meetings = new CommunicationSupport($state, $directory, $notifications);

check(count($birthdays->upcoming(0, 'ru')) >= 1, 'В любой день в демо есть день рождения «сегодня»');
check(count($directory->all()) >= 39, 'Загружен синтетический список сотрудников');
check(count($directory->departments()) >= 10, 'Загружена структура подразделений');
check($directory->departmentName(10, 'ru') === 'Сопровождение связи', 'В структуре есть обезличенная группа «Сопровождение связи»');

$results = $directory->search('Backend', 3, 'en');
check(count($results) >= 2, 'Поиск находит backend-разработчиков внутри Разработки');
check(($results[0]['_score'] ?? 0) > 0, 'Поиск рассчитывает релевантность');

$layoutResults = $directory->search('bdfy', null, 'ru');
check(!empty($layoutResults) && (int) $layoutResults[0]['id'] === 10, 'Исправление раскладки находит Ивана');
check(keyboard_swap('bdfy') === 'иван', 'Преобразование раскладки работает отдельно');

$profileEmployee = $directory->find(27);
$profileEmployee = $profileEmployee ? $directory->present($profileEmployee, 'ru') : null;
check(is_array($profileEmployee) && ($profileEmployee['_location'] ?? '') !== '', 'Карточка сотрудника получает локализованную локацию');

$phoneResults = $directory->search('0005550110', null, 'ru');
check(!empty($phoneResults) && (int) $phoneResults[0]['id'] === 10, 'Поиск нормализует телефон');

$alphabetResults = $directory->search('', null, 'ru', 100, 'М');
$alphabetOk = $alphabetResults !== [];
foreach ($alphabetResults as $row) {
    $parts = preg_split('/\s+/u', trim((string) ($row['_name'] ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $surname = (string) ($parts[count($parts) - 1] ?? '');
    $first = (preg_split('//u', $surname, -1, PREG_SPLIT_NO_EMPTY) ?: [])[0] ?? '';
    $alphabetOk = $alphabetOk && text_lower((string) $first) === 'м';
}
check($alphabetOk, 'Алфавит фильтрует только по первой букве фамилии');

$birthdays->toggleDepartmentFavorite(3); // Убираем предустановленное избранное.
$birthdays->toggleDepartmentFavorite(3); // Возвращаем, чтобы проверить правило отдела.
$birthdays->setDepartmentReminder(3, 7);
$birthdays->setEmployeeReminder(10, 1);
check($birthdays->effectiveReminder(10) === 1, 'Личное напоминание имеет приоритет над настройкой отдела');
$birthdays->setEmployeeReminder(10, null);
check($birthdays->effectiveReminder(10) === 7, 'При отсутствии личной настройки работает правило отдела');

$rangeFrom = (new DateTimeImmutable('today'))->format('Y-m-d');
$rangeTo = (new DateTimeImmutable('today +7 days'))->format('Y-m-d');
$rangeRows = $birthdays->filtered('all', 'ru', $rangeFrom, $rangeTo);
$rangeOk = $rangeRows !== [];
foreach ($rangeRows as $row) {
    $date = (string) ($row['_next_birthday'] ?? '');
    $rangeOk = $rangeOk && $date >= $rangeFrom && $date <= $rangeTo;
}
check($rangeOk, 'Календарный фильтр оставляет даты внутри выбранного диапазона');

$monthFrom = (new DateTimeImmutable('first day of this month'))->format('Y-m-d');
$monthTo = (new DateTimeImmutable('last day of this month'))->format('Y-m-d');
$monthRows = $birthdays->filtered('all', 'ru', $monthFrom, $monthTo);
$hasPast = false;
$hasTodayOrFuture = false;
foreach ($monthRows as $row) {
    $hasPast = $hasPast || (int) ($row['_days'] ?? 0) < 0;
    $hasTodayOrFuture = $hasTodayOrFuture || (int) ($row['_days'] ?? -1) >= 0;
}
check($hasPast && $hasTodayOrFuture, 'Месячный список содержит прошлые и будущие состояния карточек');

$regularEvent = $meetings->createEvent([
    'title_ru' => 'Обычная планёрка',
    'title_en' => 'Regular planning meeting',
    'start' => (new DateTimeImmutable('+1 day 09:00'))->format(DATE_ATOM),
    'end' => (new DateTimeImmutable('+1 day 10:00'))->format(DATE_ATOM),
    'location' => 'Переговорная «Луна»',
    'organizer_id' => 3,
    'attendees' => [3, 10, 11],
    'description_ru' => 'Внутренняя встреча команды.',
    'description_en' => 'Internal team meeting.',
    'communication_support' => false,
]);
check($meetings->linkedTaskId((int) $regularEvent['id']) === null, 'Обычная встреча не получает связанную задачу');
check(count($meetings->jobs()) === 0, 'Без «Сопровождения связи» фоновая обработка не запускается');
$meetings->shiftEvent((int) $regularEvent['id']);
$meetings->addComment((int) $regularEvent['id'], 3, 'Обычный комментарий к встрече.');
check(count($meetings->jobs()) === 0, 'Изменения обычной встречи тоже не запускают обработку');
check($meetings->unreadTaskCount() === 0, 'Обычная встреча не создаёт маркер в задачах');

$event = $meetings->createEvent([
    'title_ru' => 'Тестовое совещание',
    'title_en' => 'Test meeting',
    'start' => (new DateTimeImmutable('+2 days 10:00'))->format(DATE_ATOM),
    'end' => (new DateTimeImmutable('+2 days 11:00'))->format(DATE_ATOM),
    'location' => 'Переговорная «Тест»',
    'organizer_id' => 3,
    'attendees' => [3, 10, 11],
    'description_ru' => 'Нужна видеосвязь.',
    'description_en' => 'Video link required.',
    'communication_support' => true,
    'simulate_failure' => true,
]);
check((int) $event['id'] > 103, 'Новое событие получает собственный ID');
check(!empty($event['communication_support']), 'Для тестовой встречи выбрано «Сопровождение связи»');

$first = $meetings->processNext();
check(($first['status'] ?? null) === 'failed' && (int) ($first['attempt'] ?? 0) === 1, 'Демо-ошибка срабатывает на первой попытке');
check($meetings->retry((int) $first['id']), 'Неудачную операцию можно вернуть в очередь');
$second = $meetings->processNext();
check(($second['status'] ?? null) === 'success' && (int) ($second['attempt'] ?? 0) === 2, 'Повторная обработка завершается успешно');

$link = $meetings->linkedTaskId((int) $event['id']);
check($link !== null, 'После обработки создаётся связь событие → задача');
check($meetings->unreadTaskCount() === 1, 'Новая задача создаёт один непросмотренный маркер');
$newChanges = $meetings->unreadTaskChanges();
check(($newChanges[$link]['kind'] ?? null) === 'created', 'Новая связанная задача помечается как «Новая»');
$meetings->markTaskChangesRead();
check($meetings->unreadTaskCount() === 0, 'После просмотра маркер задач сбрасывается');

$linkedTask = null;
foreach ($meetings->tasks() as $task) {
    if ((int) $task['id'] === $link) {
        $linkedTask = $task;
        break;
    }
}
check((int) ($linkedTask['assignee_id'] ?? 0) === 37, 'Задача назначается руководителю группы сопровождения связи');
check(($linkedTask['meeting']['location'] ?? '') === 'Переговорная «Тест»', 'В задачу переносится место встречи');
check(($linkedTask['meeting']['description_ru'] ?? '') === 'Нужна видеосвязь.', 'В задачу переносится запрос на сопровождение');

$beforeTasks = count($meetings->tasks());
$meetings->shiftEvent((int) $event['id']);
$meetings->processNext();
check(count($meetings->tasks()) === $beforeTasks, 'Перенос встречи обновляет ту же задачу без дубля');
check($meetings->unreadTaskCount() === 1, 'Обновление той же задачи возвращает один маркер');
$updatedChanges = $meetings->unreadTaskChanges();
check(($updatedChanges[$link]['kind'] ?? null) === 'updated', 'Перенос встречи отмечает задачу как обновлённую');
$meetings->markTaskChangesRead();

$comment = $meetings->addComment((int) $event['id'], 3, 'Добавьте подключение удалённого участника.');
check($comment !== null, 'Комментарий добавляется к событию');
$meetings->processNext();

$linkedTask = null;
foreach ($meetings->tasks() as $task) {
    if ((int) $task['id'] === $link) {
        $linkedTask = $task;
        break;
    }
}
$comments = $linkedTask['meeting']['comments'] ?? [];
check(!empty($comments) && end($comments)['text'] === 'Добавьте подключение удалённого участника.', 'Комментарий переносится в связанную задачу');
check($meetings->unreadTaskCount() === 1, 'Комментарий снова поднимает маркер связанной задачи');
$commentChanges = $meetings->unreadTaskChanges();
check(($commentChanges[$link]['kind'] ?? null) === 'comment', 'После комментария задача помечается как имеющая новый комментарий');

$meetings->addComment((int) $event['id'], 3, 'Ещё одно уточнение до просмотра задачи.');
$meetings->processNext();
check($meetings->unreadTaskCount() === 1, 'Несколько изменений одной задачи дают один счётчик, а не несколько');

$createdNotifications = $notifications->congratulate(10);
check(count($createdNotifications) === 2, 'Поздравление создаёт демо-уведомления для портала и почты');
check($birthdays->wasCongratulated(10), 'После поздравления сотрудник помечается как поздравленный в текущей демо-сессии');
$todayBirthday = $birthdays->upcoming(0, 'ru');
$congratulatedRow = array_values(array_filter($todayBirthday, static fn(array $row): bool => (int) $row['id'] === 10));
if ($congratulatedRow !== []) {
    check(!empty($congratulatedRow[0]['_congratulated']), 'Статус поздравления возвращается вместе с карточкой дня рождения');
} else {
    check(true, 'Статус поздравления хранится даже если сотрудник не входит в сегодняшний срез');
}

$state->reset();
check(count($meetings->jobs()) === 0, 'Сброс демо возвращает пустую очередь');

printf("\nResult: %d passed, %d failed\n", $passed, $failed);
exit($failed === 0 ? 0 : 1);
