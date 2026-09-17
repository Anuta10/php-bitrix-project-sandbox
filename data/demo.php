<?php

declare(strict_types=1);

$today = new DateTimeImmutable('today');
$now = new DateTimeImmutable('now');

$departments = [
    ['id' => 1, 'parent_id' => null, 'name_ru' => 'Nexa', 'name_en' => 'Nexa', 'sort' => 10],
    ['id' => 2, 'parent_id' => 1, 'name_ru' => 'Технологии', 'name_en' => 'Technology', 'sort' => 20],
    ['id' => 3, 'parent_id' => 2, 'name_ru' => 'Разработка', 'name_en' => 'Development', 'sort' => 30],
    ['id' => 4, 'parent_id' => 2, 'name_ru' => 'Инфраструктура', 'name_en' => 'Infrastructure', 'sort' => 40],
    ['id' => 5, 'parent_id' => 1, 'name_ru' => 'Продукт и дизайн', 'name_en' => 'Product & Design', 'sort' => 50],
    ['id' => 6, 'parent_id' => 1, 'name_ru' => 'HR и развитие', 'name_en' => 'HR & People', 'sort' => 60],
    ['id' => 7, 'parent_id' => 1, 'name_ru' => 'Финансы', 'name_en' => 'Finance', 'sort' => 70],
    ['id' => 8, 'parent_id' => 1, 'name_ru' => 'Продажи', 'name_en' => 'Sales', 'sort' => 80],
    ['id' => 9, 'parent_id' => 1, 'name_ru' => 'Операции', 'name_en' => 'Operations', 'sort' => 90],
    ['id' => 10, 'parent_id' => 2, 'name_ru' => 'Сопровождение связи', 'name_en' => 'Communications Support', 'sort' => 45],
];

$people = [
    ['Анна Морозова','Anna Morozova','Генеральный директор','Chief Executive Officer',1,null,'1989-10-10','North Campus'],
    ['Даниил Рид','Daniel Reed','Технический директор','Chief Technology Officer',2,1,'1987-08-18','North Campus'],
    ['Елена Воронова','Elena Voronova','Руководитель разработки','Head of Development',3,2,'1991-08-16','North Campus'],
    ['Максим Орлов','Maxim Orlov','Руководитель инфраструктуры','Head of Infrastructure',4,2,'1988-09-02','River Office'],
    ['София Белова','Sofia Belova','Руководитель продукта','Head of Product',5,1,'1992-11-04','North Campus'],
    ['Мария Лебедева','Maria Lebedeva','HR-директор','People Director',6,1,'1990-08-27','North Campus'],
    ['Алексей Смирнов','Alexey Smirnov','Финансовый директор','Finance Director',7,1,'1986-12-12','River Office'],
    ['Наталья Волкова','Natalia Volkova','Директор по продажам','Sales Director',8,1,'1989-09-15','North Campus'],
    ['Илья Соколов','Ilya Sokolov','Операционный директор','Operations Director',9,1,'1985-10-22','River Office'],
    ['Иван Петров','Ivan Petrov','Backend-разработчик','Backend Developer',3,3,'1994-08-14','Remote'],
    ['Ольга Крылова','Olga Krylova','Frontend-разработчик','Frontend Developer',3,3,'1996-08-20','North Campus'],
    ['Артём Фёдоров','Artem Fedorov','PHP-разработчик','PHP Developer',3,3,'1993-09-08','Remote'],
    ['Ксения Павлова','Ksenia Pavlova','QA-инженер','QA Engineer',3,3,'1995-09-21','North Campus'],
    ['Никита Новиков','Nikita Novikov','Full-Stack разработчик','Full-Stack Developer',3,3,'1992-08-25','Remote'],
    ['Полина Алексеева','Polina Alexeeva','Бизнес-аналитик','Business Analyst',3,3,'1994-10-05','North Campus'],
    ['Сергей Макаров','Sergey Makarov','DevOps-инженер','DevOps Engineer',4,4,'1991-08-31','River Office'],
    ['Виктория Захарова','Victoria Zakharova','Системный администратор','Systems Administrator',4,4,'1997-11-11','River Office'],
    ['Роман Титов','Roman Titov','Инженер поддержки','Support Engineer',4,4,'1998-09-28','Remote'],
    ['Дарья Мельникова','Daria Melnikova','Product Manager','Product Manager',5,5,'1993-08-22','North Campus'],
    ['Михаил Козлов','Mikhail Kozlov','UX/UI дизайнер','UX/UI Designer',5,5,'1995-12-03','Remote'],
    ['Алёна Громова','Alena Gromova','Контент-дизайнер','Content Designer',5,5,'1997-10-18','North Campus'],
    ['Екатерина Романова','Ekaterina Romanova','HR Business Partner','HR Business Partner',6,6,'1992-08-29','North Campus'],
    ['Павел Николаев','Pavel Nikolaev','Рекрутер','Recruiter',6,6,'1994-09-12','Remote'],
    ['Людмила Сергеева','Lyudmila Sergeeva','Специалист по обучению','Learning Specialist',6,6,'1996-11-26','North Campus'],
    ['Андрей Беляев','Andrey Belyaev','Финансовый аналитик','Financial Analyst',7,7,'1993-08-17','River Office'],
    ['Юлия Королёва','Yulia Koroleva','Бухгалтер','Accountant',7,7,'1990-10-30','River Office'],
    ['Владислав Егоров','Vladislav Egorov','Менеджер по продажам','Sales Manager',8,8,'1991-08-24','North Campus'],
    ['Вероника Кузнецова','Veronika Kuznetsova','Менеджер по работе с клиентами','Account Manager',8,8,'1995-09-05','Remote'],
    ['Денис Жуков','Denis Zhukov','Sales Operations Specialist','Sales Operations Specialist',8,8,'1992-12-19','North Campus'],
    ['Антон Гусев','Anton Gusev','Координатор проектов','Project Coordinator',9,9,'1994-08-30','River Office'],
    ['Ирина Баранова','Irina Baranova','Офис-менеджер','Office Manager',9,9,'1996-09-18','North Campus'],
    ['Степан Власов','Stepan Vlasov','Специалист по закупкам','Procurement Specialist',9,9,'1993-11-07','River Office'],
    ['Татьяна Миронова','Tatiana Mironova','Backend-разработчик','Backend Developer',3,3,'1998-12-01','Remote'],
    ['Григорий Куликов','Grigory Kulikov','Frontend-разработчик','Frontend Developer',3,3,'1997-10-13','North Campus'],
    ['Алина Комарова','Alina Komarova','QA Automation Engineer','QA Automation Engineer',3,3,'1999-09-25','Remote'],
    ['Борис Давыдов','Boris Davydov','Site Reliability Engineer','Site Reliability Engineer',4,4,'1994-11-16','River Office'],
    [
        'Алексей Воронцов',
        'Alexey Vorontsov',
        'Руководитель группы сопровождения связи',
        'Head of Communications Support',
        10,
        2,
        '1989-09-09',
        'North Campus',
    ],
    ['Игорь Павлов','Igor Pavlov','Инженер связи','Communications Engineer',10,37,'1993-10-14','North Campus'],
    ['Елена Громова','Elena Gromova','Инженер мультимедиа','Multimedia Engineer',10,37,'1995-12-21','North Campus'],
];

// В портфолио важнее показать сценарий в любой день года, чем хранить условные даты навсегда.
// Год рождения остаётся прежним, а день и месяц сдвигаются относительно текущей даты.
// Поэтому в списке всегда есть недавние, сегодняшние и ближайшие дни рождения.
$birthdayOffsets = [
    -5, -3, -2, -1, 0, 1, 2, 4, 5, 6, 7, 9, 11, 13, 14, 16, 18, 20, 22, 24,
    26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 10, 12, 15, 17, 19, 21, 27,
];

$employees = [];
foreach ($people as $index => $row) {
    $id = $index + 1;
    [$nameRu,$nameEn,$positionRu,$positionEn,$departmentId,$managerId,$birthDate,$location] = $row;
    $birthYear = substr($birthDate, 0, 4);
    $birthdayDate = $today->modify(($birthdayOffsets[$index] >= 0 ? '+' : '') . $birthdayOffsets[$index] . ' days');
    $birthDate = $birthYear . '-' . $birthdayDate->format('m-d');
    $locationRu = match ($location) {
        'North Campus' => 'Главный офис',
        'River Office' => 'Офис на набережной',
        'Remote' => 'Удалённо',
        default => $location,
    };
    $phone = sprintf('+7 000 555-%02d-%02d', intdiv($id, 10), $id % 100);
    $email = 'employee' . str_pad((string) $id, 2, '0', STR_PAD_LEFT) . '@nexa.example.com';
    $vacationStart = null;
    $vacationEnd = null;
    if (in_array($id, [11, 18, 27], true)) {
        $vacationStart = $today->modify('-2 days')->format('Y-m-d');
        $vacationEnd = $today->modify('+6 days')->format('Y-m-d');
    }
    $employees[] = [
        'id' => $id,
        'name_ru' => $nameRu,
        'name_en' => $nameEn,
        'position_ru' => $positionRu,
        'position_en' => $positionEn,
        'department_id' => $departmentId,
        'manager_id' => $managerId,
        'birth_date' => $birthDate,
        'phone' => $phone,
        'email' => $email,
        'location' => $location,
        'location_ru' => $locationRu,
        'location_en' => $location,
        'vacation_start' => $vacationStart,
        'vacation_end' => $vacationEnd,
        'rank' => $id <= 9 ? 100 - $id : 20,
    ];
}

$events = [
    [
        'id' => 101,
        'title_ru' => 'Совещание проектной группы',
        'title_en' => 'Project team meeting',
        'start' => $today->modify('+1 day')->setTime(11, 0)->format(DATE_ATOM),
        'end' => $today->modify('+1 day')->setTime(12, 0)->format(DATE_ATOM),
        'location' => 'Переговорная «Орион»',
        'organizer_id' => 3,
        'attendees' => [3, 10, 11, 13, 14],
        'description_ru' => 'Нужно подключить экран и обеспечить видеосвязь с удалённым участником.',
        'description_en' => 'Please prepare the display and a video link for a remote participant.',
        'comments' => [
            [
                'id' => 1,
                'author_id' => 3,
                'created_at' => $now->modify('-50 minutes')->format(DATE_ATOM),
                'text' => 'Проверьте, пожалуйста, подключение ноутбука через HDMI до начала встречи.',
            ],
        ],
        'communication_support' => true,
        'simulate_failure' => false,
    ],
    [
        'id' => 102,
        'title_ru' => 'Демо нового корпоративного интерфейса',
        'title_en' => 'Corporate UI demo',
        'start' => $today->modify('+3 days')->setTime(15, 0)->format(DATE_ATOM),
        'end' => $today->modify('+3 days')->setTime(16, 0)->format(DATE_ATOM),
        'location' => 'Переговорная «Вега»',
        'organizer_id' => 5,
        'attendees' => [5, 19, 20, 21],
        'description_ru' => 'Обсудить финальные правки интерфейса и собрать обратную связь команды.',
        'description_en' => 'Review the final interface changes and collect team feedback.',
        'comments' => [],
        'communication_support' => false,
        'simulate_failure' => false,
    ],
    [
        'id' => 103,
        'title_ru' => 'Координация проектных команд',
        'title_en' => 'Project teams coordination',
        'start' => $today->modify('+6 days')->setTime(10, 30)->format(DATE_ATOM),
        'end' => $today->modify('+6 days')->setTime(11, 15)->format(DATE_ATOM),
        'location' => 'Онлайн + переговорная',
        'organizer_id' => 2,
        'attendees' => [2, 3, 4, 5, 6],
        'description_ru' => 'Еженедельная координация задач между командами продукта и разработки.',
        'description_en' => 'Weekly coordination between the product and development teams.',
        'comments' => [],
        'communication_support' => false,
        'simulate_failure' => false,
    ],
];

$tasks = [
    [
        'id' => 401,
        'title_ru' => 'Сопровождение связи: Совещание проектной группы',
        'title_en' => 'Communications support: Project team meeting',
        'assignee_id' => 37,
        'participant_ids' => [38, 39],
        'deadline' => $today->modify('+1 day')->setTime(12, 0)->format(DATE_ATOM),
        'status' => 'open',
        'source' => 'calendar_communication_support',
        'event_id' => 101,
        'meeting' => [
            'title_ru' => 'Совещание проектной группы',
            'title_en' => 'Project team meeting',
            'organizer_id' => 3,
            'start' => $today->modify('+1 day')->setTime(11, 0)->format(DATE_ATOM),
            'end' => $today->modify('+1 day')->setTime(12, 0)->format(DATE_ATOM),
            'location' => 'Переговорная «Орион»',
            'attendee_ids' => [3, 10, 11, 13, 14],
            'description_ru' => 'Нужно подключить экран и обеспечить видеосвязь с удалённым участником.',
            'description_en' => 'Please prepare the display and a video link for a remote participant.',
            'comments' => [
                [
                    'id' => 1,
                    'author_id' => 3,
                    'created_at' => $now->modify('-50 minutes')->format(DATE_ATOM),
                    'text' => 'Проверьте, пожалуйста, подключение ноутбука через HDMI до начала встречи.',
                ],
            ],
        ],
    ],
    [
        'id' => 402,
        'title_ru' => 'Проверить адаптив главной страницы',
        'title_en' => 'Review dashboard responsiveness',
        'assignee_id' => 11,
        'participant_ids' => [20],
        'deadline' => $today->modify('+2 days')->setTime(18, 0)->format(DATE_ATOM),
        'status' => 'open',
        'source' => 'manual',
        'event_id' => null,
    ],
    [
        'id' => 403,
        'title_ru' => 'Подготовить отчёт по поиску',
        'title_en' => 'Prepare search quality report',
        'assignee_id' => 15,
        'participant_ids' => [3],
        'deadline' => $today->modify('+4 days')->setTime(17, 0)->format(DATE_ATOM),
        'status' => 'open',
        'source' => 'manual',
        'event_id' => null,
    ],
];

$eventTaskLinks = [101 => 401];
$supportJobs = [];
$supportLogs = [
    [
        'id' => 1,
        'created_at' => $now->modify('-35 minutes')->format(DATE_ATOM),
        'job_id' => 900,
        'event_id' => 101,
        'task_id' => 401,
        'action' => 'create',
        'status' => 'success',
        'attempt' => 1,
        'duration_ms' => 286,
        'message_ru' => 'Задача группы сопровождения связи создана по событию календаря.',
        'message_en' => 'A communications support task was created from the calendar event.',
    ],
];

$birthdayToday = null;
foreach ($employees as $employee) {
    if (substr((string) $employee['birth_date'], 5) === $today->format('m-d')) {
        $birthdayToday = $employee;
        break;
    }
}

$birthdayNameRu = $birthdayToday['name_ru'] ?? 'коллеги';
$birthdayNameEn = $birthdayToday['name_en'] ?? 'a colleague';

$notifications = [
    [
        'id' => 1,
        'created_at' => $now->modify('-70 minutes')->format(DATE_ATOM),
        'type' => 'birthday_reminder',
        'channel' => 'portal',
        'recipient_id' => 1,
        'message_ru' => 'Напоминание: сегодня день рождения у ' . $birthdayNameRu . '.',
        'message_en' => 'Reminder: ' . $birthdayNameEn . ' has a birthday today.',
        'status' => 'success',
    ],
    [
        'id' => 2,
        'created_at' => $now->modify('-68 minutes')->format(DATE_ATOM),
        'type' => 'birthday_reminder',
        'channel' => 'email',
        'recipient_id' => 1,
        'message_ru' => 'Напоминание: в ближайшие дни у нескольких коллег день рождения.',
        'message_en' => 'Reminder: several colleagues have birthdays coming up soon.',
        'status' => 'success',
    ],
];

$news = [
    [
        'id' => 1,
        'title_ru' => 'Сегодня вечером обновим портал',
        'title_en' => 'Portal update this evening',
        'body_ru' => 'Работы начнутся в 19:00 и займут около часа. В это время отдельные разделы могут открываться с задержкой.',
        'body_en' => 'Maintenance starts at 19:00 and should take about an hour. Some sections may load more slowly during the update.',
        'date' => $today->format('Y-m-d'),
    ],
    [
        'id' => 2,
        'title_ru' => 'Открыта запись на внутренние мини-лекции',
        'title_en' => 'Sign-up is open for internal mini talks',
        'body_ru' => 'Если хотите поделиться инструментом, разбором ошибки или удачным решением из проекта — выберите свободную среду в календаре команды.',
        'body_en' => 'If you want to share a useful tool, a tricky bug or a solution that worked well, choose an open Wednesday in the team calendar.',
        'date' => $today->modify('-1 day')->format('Y-m-d'),
    ],
    [
        'id' => 3,
        'title_ru' => 'Обновили памятку по оформлению задач',
        'title_en' => 'Task-writing guide updated',
        'body_ru' => 'Добавили короткие примеры для задач на разработку, поддержку и контент. Памятка лежит в базе знаний.',
        'body_en' => 'We added short examples for development, support and content tasks. The guide is available in the knowledge base.',
        'date' => $today->modify('-3 days')->format('Y-m-d'),
    ],
    [
        'id' => 4,
        'title_ru' => 'В переговорной «Орион» заменили оборудование',
        'title_en' => 'New equipment in the Orion meeting room',
        'body_ru' => 'Камера и микрофон уже настроены. Если заметите проблемы на звонке, напишите в поддержку прямо из портала.',
        'body_en' => 'The camera and microphone are already configured. If anything behaves oddly during a call, message support from the portal.',
        'date' => $today->modify('-5 days')->format('Y-m-d'),
    ],
];

$announcements = [
    [
        'id' => 1,
        'title_ru' => 'Парковка у главного входа',
        'title_en' => 'Parking by the main entrance',
        'body_ru' => 'Завтра с 8:00 до 13:00 часть мест будет закрыта из-за доставки оборудования.',
        'body_en' => 'Tomorrow, some parking spaces will be closed from 8:00 to 13:00 for an equipment delivery.',
    ],
    [
        'id' => 2,
        'title_ru' => 'Корпоративный спорт',
        'title_en' => 'Company sports',
        'body_ru' => 'На следующей неделе собираем команду на вечернюю тренировку. Запись открыта до конца недели.',
        'body_en' => 'We are putting together a group for an evening workout next week. Sign-up is open until the end of the week.',
    ],
    [
        'id' => 3,
        'title_ru' => 'Новый раздел в базе знаний',
        'title_en' => 'New knowledge base section',
        'body_ru' => 'Собрали инструкции по внутренним сервисам и ответы на вопросы, которые чаще всего приходят в поддержку.',
        'body_en' => 'We collected internal service guides and answers to the questions support receives most often.',
    ],
];

$feed = [
    [
        'id' => 1,
        'author_ru' => 'Мария Лебедева',
        'author_en' => 'Maria Lebedeva',
        'created_at' => $today->setTime(10, 20)->format(DATE_ATOM),
        'text_ru' => 'Коллеги, напомню: до конца недели можно записаться в следующую группу по английскому. Ссылка — в разделе обучения.',
        'text_en' => 'A quick reminder: registration for the next English group is open until the end of the week. The link is in the Learning section.',
    ],
    [
        'id' => 2,
        'author_ru' => 'Сергей Макаров',
        'author_en' => 'Sergey Makarov',
        'created_at' => $today->modify('-1 day')->setTime(17, 45)->format(DATE_ATOM),
        'text_ru' => 'Завтра утром проверяем резервное копирование. На работу портала это не повлияет, но в журнале могут появиться тестовые уведомления.',
        'text_en' => 'Tomorrow morning we are checking backups. The portal will stay available, but you may notice a few test entries in the log.',
    ],
    [
        'id' => 3,
        'author_ru' => 'Елена Воронова',
        'author_en' => 'Elena Voronova',
        'created_at' => $today->modify('-1 day')->setTime(13, 10)->format(DATE_ATOM),
        'text_ru' => 'После демо оставила заметки по новому шаблону карточки сотрудника. Если есть идеи по поиску или фильтрам — добавляйте комментарии.',
        'text_en' => 'I left notes after the demo of the new employee profile. If you have ideas for search or filters, add them in the comments.',
    ],
];

$cases = [
    [
        'slug' => 'employee-directory',
        'title_ru' => 'Корпоративный справочник сотрудников',
        'title_en' => 'Corporate Employee Directory',
        'problem_ru' => 'В большой компании поиск нужного человека часто занимает больше времени, чем должен: кто за что отвечает, в каком он отделе и как с ним связаться.',
        'problem_en' => 'In a larger company, finding the right person often takes longer than it should: who owns the topic, where they sit and how to reach them.',
        'solution_ru' => 'Справочник с деревом подразделений, карточками сотрудников и быстрым поиском по имени, должности, телефону, почте и отделу. Учтены отпуска, ошибочная раскладка и релевантность результатов.',
        'solution_en' => 'A directory with department navigation, employee profiles and fast search by name, role, phone, email or team. It also handles vacation status, keyboard layout mistakes and result relevance.',
        'production_ru' => '1С-Битрикс: инфоблоки сотрудников/подразделений, серверная выборка, AJAX-обработчики, кастомная сортировка и шаблоны.',
        'production_en' => '1C-Bitrix: employee/department information blocks, server-side retrieval, AJAX handlers, custom ranking and templates.',
        'skills' => ['PHP','Search ranking','AJAX','Hierarchical data','UX'],
    ],
    [
        'slug' => 'birthday-automation',
        'title_ru' => 'Дни рождения и напоминания',
        'title_en' => 'Birthday Automation & Reminders',
        'problem_ru' => 'Хочется не пропускать дни рождения коллег, но при этом не получать лишние уведомления обо всех подряд.',
        'problem_en' => 'People want to remember colleagues’ birthdays without getting noisy reminders for everyone in the company.',
        'solution_ru' => 'Можно добавить в избранное конкретных людей или целые отделы, выбрать срок напоминания и отправить поздравление. Личная настройка сотрудника имеет приоритет над общей настройкой отдела.',
        'solution_en' => 'Users can follow individual colleagues or whole departments, choose reminder timing and send congratulations. Personal settings override a department-level rule.',
        'production_ru' => '1С-Битрикс: CUserOptions, Agents, IM, почтовые события, AJAX и защита от повторной отправки.',
        'production_en' => '1C-Bitrix: CUserOptions, Agents, IM, mail events, AJAX and duplicate-send protection.',
        'skills' => ['PHP','Business rules','Notifications','AJAX','Idempotency'],
    ],
    [
        'slug' => 'calendar-communication-support',
        'title_ru' => 'Календарь → сопровождение связи',
        'title_en' => 'Calendar → Communications Support',
        'problem_ru' => 'Не каждой встрече нужна помощь службы связи. Но когда требуется видеоконференция или оборудование, организатору неудобно отдельно ставить задачу и потом вручную переносить каждое изменение.',
        'problem_en' => 'Not every meeting needs communications support. When video conferencing or room equipment is required, the organizer should not have to create a separate task and repeat every calendar change manually.',
        'solution_ru' => 'Если при создании события выбрано «Сопровождение связи», модуль передаёт его профильной группе. Создаётся одна связанная задача с местом, временем, участниками, описанием и комментариями. Изменения проходят в фоне и обновляют ту же задачу без дублей.',
        'solution_en' => 'If Communications Support is selected for a calendar event, the module hands it to the service team. One linked task stores the meeting place, time, attendees, description and comments. Background processing updates that same task without duplicates.',
        'production_ru' => '1С-Битрикс: события Calendar/Forum, D7/DataManager, фоновые агенты, блокировки, повторные попытки, синхронизация участников и уведомлений.',
        'production_en' => '1C-Bitrix: Calendar/Forum events, D7/DataManager, background agents, locks, retries, participant and notification sync.',
        'skills' => ['Event-driven design','Queues','Retries','Idempotency','Audit logs'],
    ],
    [
        'slug' => 'corporate-dashboard',
        'title_ru' => 'Кастомная корпоративная главная',
        'title_en' => 'Custom Corporate Dashboard',
        'problem_ru' => 'Стандартной главной бывает недостаточно: сотрудникам нужны именно те новости, встречи и внутренние сервисы, которыми они пользуются каждый день.',
        'problem_en' => 'A default portal homepage is not always enough: employees need quick access to the news, meetings and internal services they actually use every day.',
        'solution_ru' => 'Собрана отдельная главная с новостями, объявлениями, календарём, днями рождения и быстрыми переходами. Разметка и стили изолированы, чтобы кастомизация не мешала штатным компонентам.',
        'solution_en' => 'A separate homepage brings together news, announcements, calendar items, birthdays and quick links. Markup and styles are isolated so customization does not interfere with standard components.',
        'production_ru' => '1С-Битрикс/Bitrix24: глобальная тема, отдельная главная, шаблоны компонентов, штатная Живая лента и Calendar без копирования core-разметки.',
        'production_en' => '1C-Bitrix/Bitrix24: global theme, independent homepage, component templates, native Live Feed and Calendar without copying core markup.',
        'skills' => ['PHP','Component integration','CSS architecture','JavaScript','Responsive UI'],
    ],
];

return compact(
    'departments',
    'employees',
    'events',
    'tasks',
    'eventTaskLinks',
    'supportJobs',
    'supportLogs',
    'notifications',
    'news',
    'announcements',
    'feed',
    'cases'
) + [
    'event_task_links' => $eventTaskLinks,
    'support_jobs' => $supportJobs,
    'support_logs' => $supportLogs,
];
