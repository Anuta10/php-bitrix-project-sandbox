<?php

declare(strict_types=1);

/**
 * Небольшие общие функции проекта.
 * Здесь намеренно нет отдельного класса под каждую операцию: для такой песочницы
 * простые функции читаются быстрее и не прячут смысл за лишними обёртками.
 */

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function current_locale(): string
{
    $locale = (string) ($_SESSION['locale'] ?? config('default_locale', 'ru'));
    $supported = config('supported_locales', ['ru', 'en']);
    return in_array($locale, $supported, true) ? $locale : 'ru';
}

function t(string $key, array $replace = []): string
{
    static $messages = [];
    $locale = current_locale();

    if (!isset($messages[$locale])) {
        $messages[$locale] = require base_path('resources/lang/' . $locale . '.php');
    }

    $value = $messages[$locale];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $key;
        }
        $value = $value[$part];
    }

    $text = is_string($value) ? $value : $key;
    foreach ($replace as $name => $replacement) {
        $text = str_replace(':' . $name, (string) $replacement, $text);
    }
    return $text;
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(24));
    }
    return (string) $_SESSION['_csrf'];
}

function csrf_ok(\App\Support\Request $request): bool
{
    $provided = (string) ($request->post['_token'] ?? $request->server['HTTP_X_CSRF_TOKEN'] ?? '');
    return $provided !== '' && hash_equals(csrf_token(), $provided);
}


function unread_task_count(): int
{
    $updates = $_SESSION['demo_state']['task_updates'] ?? [];
    return is_array($updates) ? count($updates) : 0;
}

function text_lower(string $value): string
{
    $value = strtolower($value);
    $upper = preg_split('//u', 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ', -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $lower = preg_split('//u', 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя', -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return strtr($value, array_combine($upper, $lower) ?: []);
}

function text_contains(string $text, string $query): bool
{
    return $query === '' || str_contains(text_lower($text), text_lower($query));
}

function text_starts_with(string $text, string $query): bool
{
    return str_starts_with(text_lower($text), text_lower($query));
}

function only_digits(string $value): string
{
    return preg_replace('/\D+/', '', $value) ?? '';
}

function keyboard_swap(string $value): string
{
    $en = "qwertyuiop[]asdfghjkl;'zxcvbnm,.`QWERTYUIOP{}ASDFGHJKL:\"ZXCVBNM<>~";
    $ru = "йцукенгшщзхъфывапролджэячсмитьбюёЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮЁ";
    $enChars = preg_split('//u', $en, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $ruChars = preg_split('//u', $ru, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $map = array_combine($enChars, $ruChars) ?: [];

    // Если пользователь начал писать русское слово в английской раскладке —
    // меняем раскладку. Для русского ввода работает и обратное направление.
    $selected = preg_match('/[А-Яа-яЁё]/u', $value) === 1 ? array_flip($map) : $map;
    $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return implode('', array_map(static fn(string $char): string => $selected[$char] ?? $char, $chars));
}

function format_date(string $value, ?string $locale = null, bool $withTime = false): string
{
    try {
        $date = new DateTimeImmutable($value);
    } catch (Throwable) {
        return $value;
    }

    $locale ??= current_locale();
    if ($locale === 'ru') {
        $months = [1=>'янв',2=>'фев',3=>'мар',4=>'апр',5=>'май',6=>'июн',7=>'июл',8=>'авг',9=>'сен',10=>'окт',11=>'ноя',12=>'дек'];
        $result = $date->format('j') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
        return $withTime ? $result . ', ' . $date->format('H:i') : $result;
    }
    return $withTime ? $date->format('M j, Y · H:i') : $date->format('M j, Y');
}

function format_date_long(string $value, ?string $locale = null): string
{
    try {
        $date = new DateTimeImmutable($value);
    } catch (Throwable) {
        return $value;
    }

    $locale ??= current_locale();
    if ($locale === 'ru') {
        $months = [1=>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'];
        return $date->format('j') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }
    return $date->format('F j, Y');
}

function format_time(string $value): string
{
    try {
        return (new DateTimeImmutable($value))->format('H:i');
    } catch (Throwable) {
        return $value;
    }
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $letters = [];
    foreach (array_slice($parts, 0, 2) as $part) {
        $chars = preg_split('//u', $part, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($chars) {
            $letters[] = $chars[0];
        }
    }
    return implode('', $letters) ?: '?';
}

function format_relative_time(string $value, ?string $locale = null): string
{
    try {
        $date = new DateTimeImmutable($value);
    } catch (Throwable) {
        return $value;
    }

    $locale ??= current_locale();
    $today = new DateTimeImmutable('today');
    $day = $date->setTime(0, 0);
    $diff = (int) $day->diff($today)->format('%r%a');

    if ($day == $today) {
        return ($locale === 'ru' ? 'сегодня, ' : 'today, ') . $date->format('H:i');
    }
    if ($diff === 1) {
        return ($locale === 'ru' ? 'вчера, ' : 'yesterday, ') . $date->format('H:i');
    }

    return format_date($value, $locale, true);
}
