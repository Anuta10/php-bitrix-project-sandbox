<?php

declare(strict_types=1);

return [
    'name' => 'Anna Kolesova · PHP / 1C-Bitrix',
    'company' => 'Nexa',
    'default_locale' => 'ru',
    'supported_locales' => ['ru', 'en'],
    'timezone' => 'Europe/Moscow',
    'demo_user_id' => 1,
    'communication_service' => [
        'department_id' => 10,
        'assignee_id' => 37,
        'participant_ids' => [38, 39],
    ],
    'contact' => [
        'name' => 'Anna Kolesova',
        'role_ru' => 'PHP / 1С-Битрикс разработчик · проектная разработка',
        'role_en' => 'PHP / 1C-Bitrix Developer · project development',
        'email' => 'kolesova-niusha91@yandex.ru',
        'github' => 'https://github.com/Anuta10',
    ],
    'compensation' => [
        'full_time_ru' => '200 000–230 000 ₽ net / месяц',
        'full_time_en' => 'RUB 200,000–230,000 net / month',
        'hourly_ru' => '2 000–2 500 ₽ / час',
        'hourly_en' => 'RUB 2,000–2,500 / hour',
        'show_on_public_about_page' => false,
    ],
];
