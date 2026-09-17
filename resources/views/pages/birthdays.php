<?php
$groups = [];
foreach ($rows as $row) {
    $groups[$row['_next_birthday']][] = $row;
}

$periodQuery = static function (string $value) use ($mode): string {
    return '/birthdays?' . http_build_query(['mode' => $mode, 'period' => $value]);
};

$employeeWord = static function (int $count, string $locale): string {
    if ($locale !== 'ru') {
        return $count === 1 ? 'employee' : 'employees';
    }

    $mod100 = $count % 100;
    $mod10 = $count % 10;
    if ($mod100 >= 11 && $mod100 <= 14) return 'сотрудников';
    if ($mod10 === 1) return 'сотрудник';
    if ($mod10 >= 2 && $mod10 <= 4) return 'сотрудника';
    return 'сотрудников';
};

$relativeLabel = static function (int $days, string $locale): string {
    if ($locale !== 'ru') {
        if ($days === 0) return 'Today';
        if ($days === 1) return 'Tomorrow';
        if ($days === -1) return 'Yesterday';
        return $days < 0 ? abs($days) . ' days ago' : 'In ' . $days . ' days';
    }

    if ($days === 0) return 'Сегодня';
    if ($days === 1) return 'Завтра';
    if ($days === -1) return 'Вчера';

    $n = abs($days);
    $mod100 = $n % 100;
    $mod10 = $n % 10;
    $word = ($mod100 >= 11 && $mod100 <= 14)
        ? 'дней'
        : ($mod10 === 1 ? 'день' : (($mod10 >= 2 && $mod10 <= 4) ? 'дня' : 'дней'));

    return $days < 0 ? $n . ' ' . $word . ' назад' : 'Через ' . $n . ' ' . $word;
};

$rangeLabel = $period === 'custom'
    ? format_date_long($from, $locale) . ' — ' . format_date_long($to, $locale)
    : ($locale === 'ru' ? 'Выбрать' : 'Choose');
?>

<div class="birthday-page">
    <div class="birthday-page-head">
        <div>
            <span class="module-kicker"><?= $locale === 'ru' ? 'Корпоративный сервис' : 'Corporate service' ?></span>
            <h1><?= e(t('birthdays.title')) ?></h1>
            <p>
                <?= $locale === 'ru'
                    ? 'Здесь собраны дни рождения коллег, личные напоминания и подписки на подразделения.'
                    : 'Colleagues’ birthdays, personal reminders and department subscriptions in one place.' ?>
            </p>
        </div>

        <button class="birthday-reminders-button" type="button" data-generate-reminders>
            <?= $locale === 'ru' ? 'Проверить напоминания' : 'Check reminders' ?>
        </button>
    </div>

    <section class="birthday-period-panel">
        <div class="birthday-period-presets">
            <a class="<?= $period === 'today' ? 'is-active' : '' ?>" href="<?= e($periodQuery('today')) ?>">
                <?= $locale === 'ru' ? 'Сегодня' : 'Today' ?>
            </a>
            <a class="<?= $period === 'week' ? 'is-active' : '' ?>" href="<?= e($periodQuery('week')) ?>">
                <?= $locale === 'ru' ? 'Неделя' : 'Week' ?>
            </a>
            <a class="<?= $period === 'month' ? 'is-active' : '' ?>" href="<?= e($periodQuery('month')) ?>">
                <?= $locale === 'ru' ? 'Месяц' : 'Month' ?>
            </a>
        </div>

        <form class="birthday-range-form" method="get" action="/birthdays" data-birthday-range-form>
            <input type="hidden" name="mode" value="<?= e($mode) ?>">
            <input type="hidden" name="from" value="<?= e($from) ?>" data-birthday-range-from>
            <input type="hidden" name="to" value="<?= e($to) ?>" data-birthday-range-to>

            <button class="birthday-range-trigger" type="button" data-birthday-range-open>
                <span class="birthday-range-icon">▦</span>
                <span><?= $locale === 'ru' ? 'Диапазон дат:' : 'Date range:' ?></span>
                <strong data-birthday-range-label><?= e($rangeLabel) ?></strong>
            </button>

            <div
                class="birthday-range-popover"
                data-birthday-range-picker
                data-from="<?= e($period === 'custom' ? $from : '') ?>"
                data-to="<?= e($period === 'custom' ? $to : '') ?>"
                hidden
            >
                <div class="birthday-range-calendar-head">
                    <button type="button" data-range-prev aria-label="Previous month">‹</button>
                    <strong data-range-month></strong>
                    <button type="button" data-range-next aria-label="Next month">›</button>
                </div>

                <div class="birthday-range-weekdays">
                    <?php foreach (($locale === 'ru' ? ['пн','вт','ср','чт','пт','сб','вс'] : ['mo','tu','we','th','fr','sa','su']) as $weekday): ?>
                        <span><?= e($weekday) ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="birthday-range-days" data-range-days></div>

                <div class="birthday-range-calendar-footer">
                    <button type="button" class="birthday-range-reset" data-range-reset>
                        <?= $locale === 'ru' ? 'Сбросить' : 'Reset' ?>
                    </button>
                    <button type="button" class="birthday-range-apply" data-range-apply>
                        <?= $locale === 'ru' ? 'Применить' : 'Apply' ?>
                    </button>
                </div>
            </div>
        </form>
    </section>

    <section class="birthday-toolbar">
        <nav class="birthday-tabs" aria-label="Filters">
            <?php foreach (['all', 'favorites', 'reminders', 'anniversaries'] as $tab):
                $label = $tab === 'all' ? t('common.all') : t('birthdays.' . $tab);
                $href = '/birthdays?' . http_build_query(['mode' => $tab, 'from' => $from, 'to' => $to]);
            ?>
                <a class="<?= $mode === $tab ? 'is-active' : '' ?>" href="<?= e($href) ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="birthday-channels">
            <span><?= $locale === 'ru' ? 'Уведомлять через' : 'Notify via' ?></span>

            <label>
                <input type="checkbox" data-channel="portal" <?= !empty($channels['portal']) ? 'checked' : '' ?>>
                <i></i><?= e(t('birthdays.portal')) ?>
            </label>

            <label>
                <input type="checkbox" data-channel="email" <?= !empty($channels['email']) ? 'checked' : '' ?>>
                <i></i><?= e(t('birthdays.email')) ?>
            </label>

            <button class="birthday-email-preview-button" type="button" data-email-preview-open>
                ✉ <?= $locale === 'ru' ? 'Пример письма' : 'Email preview' ?>
            </button>
        </div>
    </section>

    <div class="birthday-layout">
        <main class="birthday-results">
            <?php if (!$rows): ?>
                <div class="birthday-empty">
                    <?= $locale === 'ru'
                        ? 'В выбранном периоде дней рождения нет. Попробуйте расширить даты.'
                        : 'No birthdays in this period. Try a wider date range.' ?>
                </div>
            <?php endif; ?>

            <?php foreach ($groups as $date => $people):
                $days = (int) $people[0]['_days'];
                $count = count($people);
                $groupMeta = $days === 0
                    ? ($locale === 'ru' ? 'СЕГОДНЯ' : 'TODAY')
                    : ($days === 1
                        ? ($locale === 'ru' ? 'ЗАВТРА' : 'TOMORROW')
                        : $count . ' ' . $employeeWord($count, $locale));
            ?>
                <section class="birthday-date-group <?= $days < 0 ? 'is-past' : '' ?>">
                    <div class="birthday-date-group-head">
                        <h2><?= e(format_date($date, $locale)) ?></h2>
                        <span><?= e($groupMeta) ?></span>
                    </div>

                    <div class="birthday-card-grid">
                        <?php foreach ($people as $person):
                            $personDays = (int) $person['_days'];
                            $today = $personDays === 0;
                            $tomorrow = $personDays === 1;
                            $past = $personDays < 0;
                            $stateClass = $past ? 'is-past' : ($today ? 'is-today' : ($tomorrow ? 'is-tomorrow' : 'is-future'));
                            $relative = $relativeLabel($personDays, $locale);
                        ?>
                            <article class="birthday-person-card <?= $stateClass ?> <?= !empty($person['_anniversary']) ? 'is-anniversary' : '' ?>">
                                <div class="birthday-card-line">
                                    <div class="birthday-card-date-state">
                                        <span class="birthday-status <?= $stateClass ?>"><?= e($relative) ?></span>
                                        <span class="birthday-card-date"><?= e(format_date_long($person['_next_birthday'], $locale)) ?></span>
                                    </div>

                                    <?php if (!empty($person['_anniversary'])): ?>
                                        <span class="birthday-anniversary"><?= $locale === 'ru' ? 'Юбилей' : 'Milestone' ?></span>
                                    <?php endif; ?>

                                    <button
                                        class="birthday-favorite <?= !empty($person['_favorite']) ? 'is-active' : '' ?>"
                                        type="button"
                                        data-toggle-employee-favorite
                                        data-employee-id="<?= (int) $person['id'] ?>"
                                        title="favorite"
                                    >
                                        <?= !empty($person['_favorite']) ? '★' : '☆' ?>
                                    </button>
                                </div>

                                <div class="birthday-person-main">
                                    <a class="birthday-avatar" href="/employees/<?= (int) $person['id'] ?>">
                                        <?= e(initials($person['_name'])) ?>
                                    </a>
                                    <div>
                                        <a class="birthday-person-name" href="/employees/<?= (int) $person['id'] ?>">
                                            <?= e($person['_name']) ?>
                                        </a>
                                        <span><?= e($person['_position']) ?></span>
                                    </div>
                                </div>

                                <div class="birthday-department-line"><?= e($person['_department']) ?></div>

                                <div class="birthday-person-actions <?= $today ? '' : 'is-single' ?>">
                                    <label class="birthday-reminder-control">
                                        <span>🔔</span>
                                        <select data-employee-reminder data-employee-id="<?= (int) $person['id'] ?>">
                                            <option value="" <?= $person['_reminder'] === null ? 'selected' : '' ?>><?= e(t('birthdays.no_reminder')) ?></option>
                                            <option value="0" <?= $person['_reminder'] === 0 ? 'selected' : '' ?>><?= e(t('birthdays.on_day')) ?></option>
                                            <option value="1" <?= $person['_reminder'] === 1 ? 'selected' : '' ?>><?= e(t('birthdays.before_1')) ?></option>
                                            <option value="3" <?= $person['_reminder'] === 3 ? 'selected' : '' ?>><?= e(t('birthdays.before_3')) ?></option>
                                            <option value="7" <?= $person['_reminder'] === 7 ? 'selected' : '' ?>><?= e(t('birthdays.before_7')) ?></option>
                                        </select>
                                    </label>

                                    <?php if ($today): ?>
                                        <button
                                            class="birthday-congratulate <?= !empty($person['_congratulated']) ? 'is-sent' : '' ?>"
                                            type="button"
                                            data-congratulate-open
                                            data-employee-id="<?= (int) $person['id'] ?>"
                                            data-employee-name="<?= e($person['_name']) ?>"
                                            <?= !empty($person['_congratulated']) ? 'disabled' : '' ?>
                                        >
                                            <?= !empty($person['_congratulated'])
                                                ? ($locale === 'ru' ? '✓ Поздравлено' : '✓ Sent')
                                                : '🎁 ' . e(t('birthdays.congratulate')) ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </main>

        <aside class="birthday-settings">
            <section class="birthday-settings-card">
                <h2><?= e(t('birthdays.favorite_departments')) ?></h2>
                <p><?= e(t('birthdays.priority_note')) ?></p>

                <?php if (!$favoriteDepartments): ?>
                    <div class="birthday-settings-empty">
                        <?= $locale === 'ru' ? 'Пока нет подписок на подразделения.' : 'No department subscriptions yet.' ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($favoriteDepartments as $department): ?>
                    <article class="birthday-department-row">
                        <div>
                            <strong><?= e($department['name']) ?></strong>
                            <small><?= (int) $department['count'] ?> <?= $locale === 'ru' ? 'сотр.' : 'people' ?></small>
                        </div>

                        <button
                            class="birthday-department-star is-active"
                            type="button"
                            data-toggle-department-favorite
                            data-department-id="<?= (int) $department['id'] ?>"
                            title="favorite"
                        >★</button>

                        <select data-department-reminder data-department-id="<?= (int) $department['id'] ?>">
                            <option value="" <?= $department['reminder'] === null ? 'selected' : '' ?>><?= e(t('birthdays.no_reminder')) ?></option>
                            <option value="0" <?= $department['reminder'] === 0 ? 'selected' : '' ?>><?= e(t('birthdays.on_day')) ?></option>
                            <option value="1" <?= $department['reminder'] === 1 ? 'selected' : '' ?>><?= e(t('birthdays.before_1')) ?></option>
                            <option value="3" <?= $department['reminder'] === 3 ? 'selected' : '' ?>><?= e(t('birthdays.before_3')) ?></option>
                            <option value="7" <?= $department['reminder'] === 7 ? 'selected' : '' ?>><?= e(t('birthdays.before_7')) ?></option>
                        </select>
                    </article>
                <?php endforeach; ?>

                <div class="birthday-add-departments">
                    <span><?= $locale === 'ru' ? 'Добавить подразделение' : 'Add department' ?></span>
                    <?php foreach ($departments as $department):
                        if ((int) $department['id'] === 1) continue;
                        $isFavorite = false;
                        foreach ($favoriteDepartments as $favorite) {
                            if ((int) $favorite['id'] === (int) $department['id']) {
                                $isFavorite = true;
                                break;
                            }
                        }
                        if ($isFavorite) continue;
                    ?>
                        <button
                            type="button"
                            data-toggle-department-favorite
                            data-department-id="<?= (int) $department['id'] ?>"
                        >
                            + <?= e($directory->localized($department, 'name', $locale)) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>
        </aside>
    </div>
</div>

<?php require base_path('resources/views/partials/birthday-congratulation-modal.php'); ?>

<div class="birthday-modal" data-email-preview-modal hidden>
    <div class="birthday-modal-backdrop" data-email-preview-close></div>

    <section class="birthday-modal-card birthday-email-modal" role="dialog" aria-modal="true" aria-labelledby="email-preview-title">
        <button class="birthday-modal-close" type="button" data-email-preview-close>×</button>
        <span class="module-kicker"><?= $locale === 'ru' ? 'Демонстрация канала Email' : 'Email channel demo' ?></span>
        <h2 id="email-preview-title"><?= $locale === 'ru' ? 'Как выглядит письмо' : 'Email preview' ?></h2>
        <p class="birthday-demo-caption">
            <?= $locale === 'ru'
                ? 'Это только предпросмотр: письмо не уходит во внешний почтовый сервис.'
                : 'Preview only: nothing is sent to an external mail service.' ?>
        </p>

        <div class="demo-email-window">
            <div class="demo-email-toolbar">
                <span></span><span></span><span></span><strong>Nexa Mail</strong>
            </div>

            <div class="demo-email-head">
                <div>
                    <small><?= $locale === 'ru' ? 'Кому' : 'To' ?></small>
                    <strong>demo.user@nexa.example.com</strong>
                </div>
                <div>
                    <small><?= $locale === 'ru' ? 'Тема' : 'Subject' ?></small>
                    <strong><?= $locale === 'ru' ? 'Скоро день рождения коллеги' : 'A colleague has a birthday coming up' ?></strong>
                </div>
            </div>

            <div class="demo-email-body">
                <div class="demo-email-logo">N</div>
                <p><?= $locale === 'ru' ? 'Добрый день!' : 'Hello!' ?></p>

                <?php if ($emailPerson): ?>
                    <p>
                        <?= $locale === 'ru' ? 'Напоминаем:' : 'Reminder:' ?>
                        <strong><?= e(format_date_long($emailPerson['_next_birthday'], $locale)) ?></strong>
                        <?= $locale === 'ru' ? 'день рождения у' : 'is the birthday of' ?>
                        <strong><?= e($emailPerson['_name']) ?></strong>, <?= e($emailPerson['_department']) ?>.
                    </p>
                <?php else: ?>
                    <p>
                        <?= $locale === 'ru'
                            ? 'В выбранном периоде нет ближайших дней рождения.'
                            : 'There are no upcoming birthdays in the selected period.' ?>
                    </p>
                <?php endif; ?>

                <a href="/birthdays"><?= $locale === 'ru' ? 'Открыть список дней рождения' : 'Open birthday list' ?></a>
                <small><?= $locale === 'ru' ? 'Автоматическое уведомление корпоративного портала.' : 'Automated corporate portal notification.' ?></small>
            </div>
        </div>
    </section>
</div>
