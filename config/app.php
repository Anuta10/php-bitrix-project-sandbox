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
        // Project pricing is estimated per task after scope review.
        'show_on_public_about_page' => false,
    ],
];
