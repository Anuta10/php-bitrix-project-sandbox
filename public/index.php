<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Controllers\BirthdaysController;
use App\Controllers\DemoController;
use App\Controllers\EmployeesController;
use App\Controllers\HomeController;
use App\Controllers\MeetingsController;
use App\Services\Birthdays;
use App\Services\EmployeeDirectory;
use App\Services\CommunicationSupport;
use App\Services\Notifications;
use App\Support\DemoData;
use App\Support\Request;
use App\Support\Response;
use App\Support\Router;

$request = Request::capture();

// Render проверяет этот адрес после деплоя. Здесь не нужны демо-данные и сессия портала.
if ($request->method === 'GET' && $request->path === '/health') {
    Response::json(['status' => 'ok']);
}

// Язык хранится в сессии, чтобы после переключения не таскать ?lang= по всем ссылкам.
if (isset($request->query['lang']) && in_array($request->query['lang'], config('supported_locales', ['ru', 'en']), true)) {
    $_SESSION['locale'] = $request->query['lang'];
}

$state = new DemoData();
$directory = new EmployeeDirectory($state);
$birthdays = new Birthdays($state, $directory);
$notifications = new Notifications($state, $directory, $birthdays);
$communicationSupport = new CommunicationSupport($state, $directory, $notifications);

$home = new HomeController($state, $directory, $birthdays, $communicationSupport);
$employees = new EmployeesController($directory);
$birthdayPages = new BirthdaysController($state, $directory, $birthdays, $notifications);
$meetingPages = new MeetingsController($state, $directory, $communicationSupport);
$demo = new DemoController($state, $directory, $notifications);

// Все POST-запросы из интерфейса проходят одну и ту же проверку токена.
if ($request->method === 'POST' && !csrf_ok($request)) {
    Response::json(['ok' => false, 'error' => 'csrf_failed'], 419);
}

$router = new Router();

// Обычные страницы.
$router->get('/', static fn() => $home->portfolio());
$router->get('/demo', static fn() => $home->dashboard());
$router->get('/employees', static fn(Request $r) => $employees->index($r));
$router->get('/employees/{id}', static fn(Request $r, array $p) => $employees->profile((int) $p['id']));
$router->get('/birthdays', static fn(Request $r) => $birthdayPages->index($r));
$router->get('/calendar', static fn() => $meetingPages->calendar());
$router->get('/tasks', static fn() => $meetingPages->tasks());
$router->get('/automation', static fn() => $meetingPages->automation());
$router->get('/notifications', static fn() => $demo->notifications());
$router->get('/case-studies', static fn() => $home->cases());
$router->get('/about', static fn() => $home->about());

// Телефонный справочник.
$router->get('/api/employees/search', static fn(Request $r) => $employees->search($r));

// Дни рождения и напоминания.
$router->post('/api/favorites/employee', static fn(Request $r) => $birthdayPages->toggleEmployeeFavorite($r));
$router->post('/api/favorites/department', static fn(Request $r) => $birthdayPages->toggleDepartmentFavorite($r));
$router->post('/api/reminders/employee', static fn(Request $r) => $birthdayPages->setEmployeeReminder($r));
$router->post('/api/reminders/department', static fn(Request $r) => $birthdayPages->setDepartmentReminder($r));
$router->post('/api/notifications/channels', static fn(Request $r) => $birthdayPages->setChannels($r));
$router->post('/api/notifications/generate', static fn() => $birthdayPages->generateReminders());
$router->post('/api/birthdays/congratulate', static fn(Request $r) => $birthdayPages->congratulate($r));

// Календарь и задача группы сопровождения связи.
$router->post('/api/calendar/events', static fn(Request $r) => $meetingPages->createEvent($r));
$router->post('/api/calendar/events/{id}/shift', static fn(Request $r, array $p) => $meetingPages->shiftEvent((int) $p['id']));
$router->post('/api/calendar/events/{id}/comments', static fn(Request $r, array $p) => $meetingPages->addComment($r, (int) $p['id']));
$router->post('/api/calendar/events/{id}/delete', static fn(Request $r, array $p) => $meetingPages->deleteEvent((int) $p['id']));
$router->post('/api/automation/process-next', static fn() => $meetingPages->processNext());
$router->post('/api/automation/process-all', static fn() => $meetingPages->processAll());
$router->post('/api/automation/jobs/{id}/retry', static fn(Request $r, array $p) => $meetingPages->retry((int) $p['id']));

$router->post('/api/demo/reset', static fn() => $demo->reset());
$router->dispatch($request);
