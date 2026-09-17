<?php
$contact = config('contact');
$skills = [
    'PHP', '1C-Bitrix', 'Bitrix24', 'D7 / ORM', 'JavaScript', 'AJAX',
    'HTML5', 'CSS / SASS', 'Git', 'REST API',
    $locale === 'ru' ? 'События / агенты' : 'Events / agents',
    $locale === 'ru' ? 'Поиск и фильтрация' : 'Search & filtering',
    $locale === 'ru' ? 'Очереди / повторные попытки' : 'Queues / retries',
];
$projectMail = 'mailto:' . $contact['email'] . '?subject=' . rawurlencode(
    $locale === 'ru' ? 'Проект PHP / 1С-Битрикс' : 'PHP / 1C-Bitrix project'
);
?>

<div class="about-hero panel">
    <div class="about-avatar">AK</div>

    <div class="about-copy">
        <span class="eyebrow">PHP · 1C-Bitrix · Backend / Full-Stack</span>
        <h1>Anna Kolesova</h1>
        <h2><?= e($contact[$locale === 'ru' ? 'role_ru' : 'role_en']) ?></h2>
        <p class="lead-copy"><?= e(t('about.lead')) ?></p>
        <p><?= e(t('about.body')) ?></p>

        <div class="about-links">
            <a class="button button-primary button-sm" href="<?= e($projectMail) ?>">
                <?= e(t('common.discuss_project')) ?>
            </a>
            <a class="button button-secondary button-sm" href="<?= e($contact['github']) ?>" target="_blank" rel="noreferrer">
                GitHub
            </a>
        </div>
    </div>
</div>

<div class="about-grid">
    <section class="panel">
        <div class="panel-head">
            <h2><?= e(t('about.skills')) ?></h2>
        </div>
        <div class="skill-cloud">
            <?php foreach ($skills as $skill): ?>
                <span><?= e($skill) ?></span>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2><?= e(t('about.certs')) ?></h2>
        </div>
        <div class="certificate-list">
            <?php foreach ($certificates as $certificate): ?>
                <article>
                    <span>✓</span>
                    <div>
                        <strong><?= e($certificate[$locale]) ?></strong>
                        <small><?= e(format_date($certificate['date'], $locale)) ?></small>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2><?= e(t('about.approach')) ?></h2>
        </div>
        <div class="interest-list">
            <span><?= e(t('about.approach1')) ?></span>
            <span><?= e(t('about.approach2')) ?></span>
            <span><?= e(t('about.approach3')) ?></span>
            <span><?= e(t('about.approach4')) ?></span>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2><?= e(t('about.availability')) ?></h2>
        </div>
        <dl class="details-list">
            <div>
                <dt><?= e(t('about.short_tasks')) ?></dt>
                <dd><?= e(t('about.short_tasks_value')) ?></dd>
            </div>
            <div>
                <dt><?= e(t('about.projects')) ?></dt>
                <dd><?= e(t('about.projects_value')) ?></dd>
            </div>
            <div>
                <dt><?= e(t('about.payment')) ?></dt>
                <dd><?= e(t('about.payment_value')) ?></dd>
            </div>
        </dl>
    </section>
</div>
