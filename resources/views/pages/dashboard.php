<?php
$heroNews = array_slice($news, 0, 3);
$listNews = array_slice($news, 0, 4);
$promo = $announcements[0] ?? null;
$sideAnnouncement = $announcements[1] ?? $announcements[0] ?? null;
?>

<div class="portal-home">
    <div class="portal-home-main">
        <div class="portal-home-top-grid">
            <section class="portal-section home-news-section" aria-label="<?= e(t('dashboard.news')) ?>">
                <div class="home-hero-slider" data-home-slider>
                    <?php foreach ($heroNews as $index => $item): ?>
                        <article
                            class="home-hero-slide <?= $index === 0 ? 'is-active' : '' ?>"
                            data-home-slide
                        >
                            <div class="home-hero-pattern" aria-hidden="true">
                                <i></i>
                                <i></i>
                                <i></i>
                            </div>

                            <div class="home-hero-copy">
                                <time><?= e(format_date($item['date'], $locale)) ?></time>
                                <h1><?= e($item['title_' . $locale]) ?></h1>
                                <p><?= e($item['body_' . $locale]) ?></p>
                                <a class="portal-link-button" href="#news-list">
                                    <?= $locale === 'ru' ? 'Читать новости' : 'Read news' ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <div class="home-slider-dots" aria-label="Slides">
                        <?php foreach ($heroNews as $index => $item): ?>
                            <button
                                type="button"
                                class="<?= $index === 0 ? 'is-active' : '' ?>"
                                data-home-dot="<?= $index ?>"
                                aria-label="<?= $index + 1 ?>"
                            ></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="home-slider-arrows">
                        <button type="button" data-home-prev aria-label="Previous">‹</button>
                        <button type="button" data-home-next aria-label="Next">›</button>
                    </div>
                </div>

                <div class="portal-section-heading" id="news-list">
                    <h2><?= e(t('dashboard.news')) ?></h2>
                    <span><?= $locale === 'ru' ? 'последние записи' : 'latest posts' ?></span>
                </div>

                <div class="home-news-list">
                    <?php foreach ($listNews as $item): ?>
                        <article class="home-news-item">
                            <time><?= e(format_date($item['date'], $locale)) ?></time>
                            <div>
                                <h3><?= e($item['title_' . $locale]) ?></h3>
                                <p><?= e($item['body_' . $locale]) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="portal-section home-ads-section" aria-label="<?= e(t('dashboard.announcements')) ?>">
                <?php if ($promo): ?>
                    <article class="home-promo-banner">
                        <span class="home-promo-kicker">
                            <?= $locale === 'ru' ? 'Важно на этой неделе' : 'This week' ?>
                        </span>
                        <h2><?= e($promo['title_' . $locale]) ?></h2>
                        <p><?= e($promo['body_' . $locale]) ?></p>
                        <span class="home-promo-mark" aria-hidden="true">N</span>
                    </article>
                <?php endif; ?>

                <div class="portal-section-heading">
                    <h2><?= e(t('dashboard.announcements')) ?></h2>
                </div>

                <div class="home-announcement-panel">
                    <?php foreach ($announcements as $item): ?>
                        <article>
                            <span class="home-announcement-bullet"></span>
                            <div>
                                <h3><?= e($item['title_' . $locale]) ?></h3>
                                <p><?= e($item['body_' . $locale]) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <a class="portal-link-button portal-link-button-block" href="#">
                        <?= $locale === 'ru' ? 'Все сообщения' : 'All messages' ?>
                    </a>
                </div>
            </section>
        </div>

        <section class="home-livefeed" aria-label="<?= $locale === 'ru' ? 'Живая лента' : 'Activity feed' ?>">
            <div class="portal-section-heading portal-section-heading-between">
                <div>
                    <h2><?= $locale === 'ru' ? 'Живая лента' : 'Activity feed' ?></h2>
                    <small class="home-standard-note">
                        <?= $locale === 'ru'
                            ? 'Стандартный компонент 1С-Битрикс, встроенный в кастомную главную страницу.'
                            : 'Standard 1C-Bitrix component integrated into the custom portal homepage.' ?>
                    </small>
                </div>
                <span><?= $locale === 'ru' ? 'внутренние сообщения команды' : 'team updates' ?></span>
            </div>

            <div class="livefeed-panel">
                <?php foreach ($feed as $post): ?>
                    <article class="livefeed-post">
                        <span class="avatar"><?= e(initials($post['author_' . $locale])) ?></span>

                        <div class="livefeed-copy">
                            <div class="livefeed-meta">
                                <strong><?= e($post['author_' . $locale]) ?></strong>
                                <time><?= e(format_relative_time($post['created_at'], $locale)) ?></time>
                            </div>
                            <p><?= e($post['text_' . $locale]) ?></p>
                            <div class="livefeed-actions">
                                <span>♡ <?= 2 + (int) $post['id'] ?></span>
                                <span>○ <?= (int) $post['id'] ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <aside class="portal-home-sidebar">
        <?php if ($sideAnnouncement): ?>
            <section class="home-side-banner">
                <span><?= $locale === 'ru' ? 'Анонс' : 'Announcement' ?></span>
                <h2><?= e($sideAnnouncement['title_' . $locale]) ?></h2>
                <p><?= e($sideAnnouncement['body_' . $locale]) ?></p>
            </section>
        <?php endif; ?>

        <section class="home-side-widget home-calendar-widget">
            <div class="home-side-heading">
                <h2><?= $locale === 'ru' ? 'Календарь' : 'Calendar' ?></h2>
                <a href="/calendar">→</a>
            </div>

            <div class="home-mini-events">
                <?php foreach (array_slice($events, 0, 4) as $event): ?>
                    <?php $eventDate = new DateTimeImmutable($event['start']); ?>
                    <a href="/calendar" class="home-mini-event">
                        <span class="home-mini-date">
                            <strong><?= e($eventDate->format('d')) ?></strong>
                            <small><?= e($eventDate->format('m')) ?></small>
                        </span>
                        <span>
                            <strong><?= e($event['title_' . $locale]) ?></strong>
                            <small><?= e(format_time($event['start'])) ?> · <?= e($event['location']) ?></small>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="home-side-widget">
            <div class="home-side-heading">
                <h2><?= $locale === 'ru' ? 'Новые сотрудники' : 'New colleagues' ?></h2>
                <!-- <span class="home-heading-sticker">+</span> -->
            </div>

            <div class="home-people-list">
                <?php foreach ($newEmployees as $employee): ?>
                    <?php $name = $directory->localized($employee, 'name', $locale); ?>
                    <a href="/employees/<?= (int) $employee['id'] ?>">
                        <span class="avatar"><?= e(initials($name)) ?></span>
                        <span>
                            <strong><?= e($name) ?></strong>
                            <small><?= e($directory->localized($employee, 'position', $locale)) ?></small>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

            <a class="portal-link-button portal-link-button-block" href="/employees">
                <?= $locale === 'ru' ? 'Открыть справочник' : 'Open directory' ?>
            </a>
        </section>

        <section class="home-side-widget home-birthday-widget">
            <div class="home-side-heading">
                <h2><?= $locale === 'ru' ? 'Поздравления' : 'Congratulations' ?></h2>
                <!-- <span class="home-heading-sticker home-heading-sticker-orange">★</span> -->
            </div>

            <div class="home-birthday-list">
                <?php foreach ($birthdays as $person): ?>
                    <article>
                        <span class="avatar"><?= e(initials($person['_name'])) ?></span>
                        <div>
                            <a href="/employees/<?= (int) $person['id'] ?>"><?= e($person['_name']) ?></a>
                            <small><?= e($person['_position']) ?></small>
                            <strong><?= e(format_date($person['_next_birthday'], $locale)) ?></strong>
                            <button
                                class="home-person-action <?= !empty($person['_congratulated']) ? 'is-sent' : '' ?>"
                                type="button"
                                data-congratulate-open
                                data-employee-id="<?= (int) $person['id'] ?>"
                                data-employee-name="<?= e($person['_name']) ?>"
                                <?= !empty($person['_congratulated']) ? 'disabled' : '' ?>
                            >
                                <?= !empty($person['_congratulated'])
                                    ? ($locale === 'ru' ? '✓ Поздравлено' : '✓ Sent')
                                    : ($locale === 'ru' ? 'Поздравить' : 'Congratulate') ?>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <a class="home-widget-link" href="/birthdays">
                <?= $locale === 'ru' ? 'Все дни рождения →' : 'All birthdays →' ?>
            </a>
        </section>

        <!-- <section class="home-side-widget home-weather-widget">
            <div>
                <span>☁</span>
                <strong>18°</strong>
            </div>
            <p><?= $locale === 'ru' ? 'Санкт-Петербург · облачно' : 'Saint Petersburg · cloudy' ?></p>
        </section> -->

    </aside>
</div>

<?php require base_path('resources/views/partials/birthday-congratulation-modal.php'); ?>
