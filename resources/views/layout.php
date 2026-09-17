<?php
$standalonePortfolio = $standalonePortfolio ?? false;
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$langLink = static fn(string $lang): string => $currentPath . '?lang=' . $lang;
$taskUnreadCount = unread_task_count();
?>
<!doctype html>
<html lang="<?= e($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="csrf-token" content="<?= e($csrf) ?>">
    <title><?= e($pageTitle ?? config('name')) ?> · <?= e(config('name')) ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="<?= $standalonePortfolio ? 'portfolio-body' : 'portal-body' ?>">

<?php if ($standalonePortfolio): ?>
    <header class="portfolio-topbar">
        <a class="brand brand-dark" href="/">
            <span class="brand-mark">AK</span>
            <span>
                <strong>Anna Kolesova</strong>
                <small>PHP / 1C-Bitrix · <?= $locale === 'ru' ? 'проектная работа' : 'project work' ?></small>
            </span>
        </a>

        <div class="topbar-actions">
            <div class="language-switch" aria-label="Language">
                <a class="<?= $locale === 'ru' ? 'is-active' : '' ?>" href="<?= e($langLink('ru')) ?>">RU</a>
                <a class="<?= $locale === 'en' ? 'is-active' : '' ?>" href="<?= e($langLink('en')) ?>">EN</a>
            </div>
            <a class="button button-primary button-sm" href="mailto:<?= e(config('contact.email')) ?>">
                <?= e(t('common.discuss_project')) ?>
            </a>
        </div>
    </header>

    <main><?= $content ?></main>
<?php else: ?>
    <div class="portal-shell">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-head">
                <a class="brand" href="/demo">
                    <span class="brand-mark">N</span>
                    <span>
                        <strong>Nexa</strong>
                        <small><?= $locale === 'ru' ? 'корпоративный портал' : 'corporate portal' ?></small>
                    </span>
                </a>
                <button class="icon-button mobile-only" type="button" data-sidebar-close aria-label="Close">×</button>
            </div>

            <div class="demo-chip">
                <span></span><?= e(t('common.demo_badge')) ?>
            </div>

            <nav class="nav-list">
                <?php
                $mainNav = [
                    ['dashboard', '/demo', '⌂'],
                    ['employees', '/employees', '◎'],
                    ['birthdays', '/birthdays', '◇'],
                    ['calendar', '/calendar', '□'],
                    ['tasks', '/tasks', '✓'],
                ];
                ?>

                <?php foreach ($mainNav as [$key, $href, $icon]): ?>
                    <a class="nav-item <?= ($active ?? '') === $key ? 'is-active' : '' ?>" href="<?= $href ?>">
                        <span class="nav-icon"><?= e($icon) ?></span>
                        <span><?= e(t('nav.' . $key)) ?></span>
                        <?php if ($key === 'tasks' && $taskUnreadCount > 0): ?>
                            <span class="task-menu-badge" title="<?= $locale === 'ru' ? 'Есть непросмотренные изменения' : 'Unseen task updates' ?>">
                                <?= e($taskUnreadCount > 99 ? '99+' : (string) $taskUnreadCount) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>

                <span class="nav-section-label"><?= $locale === 'ru' ? 'Демо и устройство' : 'Demo & internals' ?></span>

                <?php
                $techNav = [
                    ['notifications', '/notifications', '◌'],
                    ['cases', '/case-studies', '≡'],
                    ['about', '/about', 'i'],
                ];
                ?>

                <?php foreach ($techNav as [$key, $href, $icon]): ?>
                    <a class="nav-item nav-item-secondary <?= ($active ?? '') === $key ? 'is-active' : '' ?>" href="<?= $href ?>">
                        <span class="nav-icon"><?= e($icon) ?></span>
                        <span><?= e(t('nav.' . $key)) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-foot">
                <a class="nav-item" href="/">
                    <span class="nav-icon">←</span>
                    <span><?= e(t('nav.portfolio')) ?></span>
                </a>
                <p><?= $locale === 'ru' ? 'Все данные в этой версии вымышлены.' : 'All data in this demo is fictional.' ?></p>
            </div>
        </aside>

        <section class="portal-main">
            <header class="portal-topbar">
                <div class="topbar-left">
                    <button class="icon-button mobile-only" type="button" data-sidebar-open aria-label="Menu">☰</button>
                    <div>
                        <strong><?= e($pageTitle ?? '') ?></strong>
                        <span><?= e(config('company')) ?></span>
                    </div>
                </div>

                <!-- <form class="top-search" action="/employees" method="get">
                    <span>⌕</span>
                    <input
                        type="search"
                        name="q"
                        placeholder="<?= $locale === 'ru' ? 'Сотрудник, отдел, телефон…' : 'Person, team, phone…' ?>"
                        aria-label="Search"
                    >
                </form> -->

                <div class="topbar-actions">
                    <div class="language-switch">
                        <a class="<?= $locale === 'ru' ? 'is-active' : '' ?>" href="<?= e($langLink('ru')) ?>">RU</a>
                        <a class="<?= $locale === 'en' ? 'is-active' : '' ?>" href="<?= e($langLink('en')) ?>">EN</a>
                    </div>

                    <button class="button button-ghost button-sm" type="button" data-reset-demo>
                        <?= e(t('common.reset')) ?>
                    </button>

                    <a class="user-pill" href="/about">
                        <span class="avatar avatar-sm">AK</span>
                        <span>
                            <strong>Anna</strong>
                            <small><?= $locale === 'ru' ? 'разработчик' : 'developer' ?></small>
                        </span>
                    </a>
                </div>
            </header>

            <main class="content-area">
                <div class="notice-banner notice-banner-compact">
                    <span class="notice-icon">i</span>
                    <span><?= e(t('common.fictional_notice')) ?></span>
                </div>
                <?= $content ?>
            </main>
        </section>

        <!-- <aside class="portal-rail" aria-label="Quick links">
            <a href="/notifications" title="<?= e(t('nav.notifications')) ?>">◌</a>
            <a href="/calendar" title="<?= e(t('nav.calendar')) ?>">◷</a>
            <a href="/case-studies" title="<?= e(t('nav.cases')) ?>">?</a>
        </aside> -->
        <aside class="portal-rail" aria-label="Quick links">
            <a href="#">◌</a>
            <a href="#">◷</a>
            <a href="#">?</a>
        </aside>
    </div>
<?php endif; ?>

<div class="toast-stack" id="toast-stack" aria-live="polite"></div>
<script>
window.PORTAL = {
    csrf: <?= json_encode($csrf) ?>,
    locale: <?= json_encode($locale) ?>
};
</script>
<script src="/assets/app.js" defer></script>
</body>
</html>
