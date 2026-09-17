<?php
$previewDate = new DateTimeImmutable('today');
if ($locale === 'ru') {
    $weekdays = [1=>'Понедельник',2=>'Вторник',3=>'Среда',4=>'Четверг',5=>'Пятница',6=>'Суббота',7=>'Воскресенье'];
    $months = [1=>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'];
    $previewDateLabel = $weekdays[(int) $previewDate->format('N')] . ', ' . $previewDate->format('j') . ' ' . $months[(int) $previewDate->format('n')];
} else {
    $previewDateLabel = $previewDate->format('l, F j');
}

$projectMail = 'mailto:' . config('contact.email') . '?subject=' . rawurlencode(
    $locale === 'ru' ? 'Проект PHP / 1С-Битрикс' : 'PHP / 1C-Bitrix project'
);

$portfolioServices = [
    [
        'number' => '01',
        'title' => t('portfolio.service1'),
        'description' => t('portfolio.service1d'),
        'tags' => ['PHP', '1C-Bitrix', 'Legacy'],
    ],
    [
        'number' => '02',
        'title' => t('portfolio.service2'),
        'description' => t('portfolio.service2d'),
        'tags' => ['D7', 'ORM', 'Components'],
    ],
    [
        'number' => '03',
        'title' => t('portfolio.service3'),
        'description' => t('portfolio.service3d'),
        'tags' => ['API', 'REST', 'PHP'],
    ],
    [
        'number' => '04',
        'title' => t('portfolio.service4'),
        'description' => t('portfolio.service4d'),
        'tags' => ['AJAX', 'JavaScript', 'Search'],
    ],
    [
        'number' => '05',
        'title' => t('portfolio.service5'),
        'description' => t('portfolio.service5d'),
        'tags' => ['Bitrix24', 'Internal tools', 'UI'],
    ],
    [
        'number' => '06',
        'title' => t('portfolio.service6'),
        'description' => t('portfolio.service6d'),
        'tags' => ['Debugging', 'Refactoring', 'Production'],
    ],
];

$portfolioCases = [
    [
        'href' => '/employees',
        'number' => '01',
        'title' => t('portfolio.case1'),
        'description' => t('portfolio.case1d'),
        'tags' => ['PHP', 'AJAX', 'Search'],
    ],
    [
        'href' => '/birthdays',
        'number' => '02',
        'title' => t('portfolio.case2'),
        'description' => t('portfolio.case2d'),
        'tags' => ['Rules', 'Notifications', 'UX'],
    ],
    [
        'href' => '/calendar',
        'number' => '03',
        'title' => t('portfolio.case3'),
        'description' => t('portfolio.case3d'),
        'tags' => ['Events', 'Queue', 'Retries'],
    ],
    [
        'href' => '/demo',
        'number' => '04',
        'title' => t('portfolio.case4'),
        'description' => t('portfolio.case4d'),
        'tags' => ['Components', 'Responsive', 'JS'],
    ],
];
?>

<section class="portfolio-hero">
    <div class="portfolio-hero-inner">
        <div class="portfolio-copy">
            <span class="eyebrow"><?= e(t('portfolio.eyebrow')) ?></span>
            <h1><?= e(t('portfolio.title')) ?></h1>
            <p class="hero-lead"><?= e(t('portfolio.lead')) ?></p>

            <div class="hero-actions">
                <a class="button button-primary" href="<?= e($projectMail) ?>">
                    <?= e(t('common.discuss_project')) ?> <span>→</span>
                </a>
                <a class="button button-secondary" href="/demo<?= $locale === 'en' ? '?lang=en' : '' ?>">
                    <?= e(t('common.open_demo')) ?>
                </a>
            </div>

            <p class="proof-line"><?= e(t('portfolio.proof')) ?></p>
        </div>

        <div class="portal-preview" aria-label="Portal preview">
            <div class="portal-preview-top">
                <span class="preview-logo">N</span>
                <strong>Nexa</strong>
                <span class="preview-search">⌕ <?= $locale === 'ru' ? 'Поиск' : 'Search' ?></span>
                <span class="preview-avatar">AK</span>
            </div>

            <div class="portal-preview-body">
                <div class="preview-sidebar">
                    <span class="is-active">⌂ <b><?= $locale === 'ru' ? 'Главная' : 'Home' ?></b></span>
                    <span>◎ <b><?= $locale === 'ru' ? 'Сотрудники' : 'People' ?></b></span>
                    <span>◇ <b><?= $locale === 'ru' ? 'Дни рождения' : 'Birthdays' ?></b></span>
                    <span>□ <b><?= $locale === 'ru' ? 'Календарь' : 'Calendar' ?></b></span>
                    <span>✓ <b><?= $locale === 'ru' ? 'Задачи' : 'Tasks' ?></b></span>
                </div>

                <div class="preview-workspace">
                    <div class="preview-welcome">
                        <small><?= e($previewDateLabel) ?></small>
                        <strong><?= $locale === 'ru' ? 'Добрый день, Анна' : 'Good afternoon, Anna' ?></strong>
                    </div>

                    <div class="preview-grid">
                        <article class="preview-card preview-news">
                            <span><?= $locale === 'ru' ? 'Новости компании' : 'Company news' ?></span>
                            <strong>
                                <?= $locale === 'ru'
                                    ? 'На главной стало проще найти нужное'
                                    : 'The homepage is easier to use' ?>
                            </strong>
                            <p>
                                <?= $locale === 'ru'
                                    ? 'Новости, встречи и объявления теперь рядом.'
                                    : 'News, meetings and announcements are now together.' ?>
                            </p>
                        </article>

                        <article class="preview-card">
                            <span><?= $locale === 'ru' ? 'Сегодня' : 'Today' ?></span>
                            <strong>14:30</strong>
                            <p><?= $locale === 'ru' ? 'Совещание проектной группы' : 'Project team meeting' ?></p>
                        </article>

                        <article class="preview-card">
                            <span><?= $locale === 'ru' ? 'Скоро день рождения' : 'Upcoming birthday' ?></span>
                            <strong><?= $locale === 'ru' ? 'Иван Петров' : 'Ivan Petrov' ?></strong>
                            <p>Backend Developer · +1 <?= $locale === 'ru' ? 'день' : 'day' ?></p>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="portfolio-section">
    <div class="section-heading centered">
        <span class="eyebrow"><?= e(t('portfolio.services_eyebrow')) ?></span>
        <h2><?= e(t('portfolio.services_title')) ?></h2>
        <p><?= e(t('portfolio.services_lead')) ?></p>
    </div>

    <div class="service-card-grid">
        <?php foreach ($portfolioServices as $service): ?>
            <article class="service-card">
                <span class="case-number"><?= e($service['number']) ?></span>
                <h3><?= e($service['title']) ?></h3>
                <p><?= e($service['description']) ?></p>
                <div class="tag-row">
                    <?php foreach ($service['tags'] as $tag): ?>
                        <span><?= e($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="project-format-strip">
        <div class="project-format-item">
            <strong><?= e(t('portfolio.format1')) ?></strong>
            <span><?= e(t('portfolio.format1d')) ?></span>
        </div>
        <div class="project-format-item">
            <strong><?= e(t('portfolio.format2')) ?></strong>
            <span><?= e(t('portfolio.format2d')) ?></span>
        </div>
        <div class="project-format-item">
            <strong><?= e(t('portfolio.format3')) ?></strong>
            <span><?= e(t('portfolio.format3d')) ?></span>
        </div>
    </div>
</section>

<section class="portfolio-section">
    <div class="section-heading centered">
        <span class="eyebrow"><?= e(t('portfolio.cases_eyebrow')) ?></span>
        <h2><?= e(t('portfolio.cases_title')) ?></h2>
        <p><?= e(t('portfolio.privacy')) ?></p>
    </div>

    <div class="case-card-grid">
        <?php foreach ($portfolioCases as $case): ?>
            <a class="portfolio-case-card" href="<?= e($case['href']) ?>">
                <span class="case-number"><?= e($case['number']) ?></span>
                <h3><?= e($case['title']) ?></h3>
                <p><?= e($case['description']) ?></p>

                <div class="tag-row">
                    <?php foreach ($case['tags'] as $tag): ?>
                        <span><?= e($tag) ?></span>
                    <?php endforeach; ?>
                </div>

                <strong><?= $locale === 'ru' ? 'Посмотреть' : 'Open' ?> →</strong>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="portfolio-section portfolio-cta-section">
    <div class="portfolio-cta">
        <div>
            <span class="eyebrow"><?= e(t('portfolio.cta_eyebrow')) ?></span>
            <h2><?= e(t('portfolio.cta_title')) ?></h2>
            <p><?= e(t('portfolio.cta_body')) ?></p>
        </div>

        <div class="portfolio-cta-actions">
            <a class="button button-primary" href="<?= e($projectMail) ?>">
                <?= e(t('common.discuss_project')) ?>
            </a>
            <a class="button button-secondary" href="<?= e(config('contact.github')) ?>" target="_blank" rel="noreferrer">
                <?= e(t('common.view_github')) ?>
            </a>
        </div>
    </div>
</section>

<footer class="portfolio-footer">
    <div>
        <strong>Anna Kolesova</strong>
        <span><?= e(config('contact.' . ($locale === 'ru' ? 'role_ru' : 'role_en'))) ?></span>
    </div>

    <div>
        <a href="mailto:<?= e(config('contact.email')) ?>"><?= e(config('contact.email')) ?></a>
        <a href="<?= e(config('contact.github')) ?>" target="_blank" rel="noreferrer">GitHub</a>
    </div>
</footer>
