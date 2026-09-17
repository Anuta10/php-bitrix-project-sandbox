<?php
$typeLabels = [
    'birthday_reminder' => $locale === 'ru' ? 'Напоминание о дне рождения' : 'Birthday reminder',
    'congratulation' => $locale === 'ru' ? 'Поздравление' : 'Congratulations',
    'communication_support' => $locale === 'ru' ? 'Сопровождение связи' : 'Communications support',
];

$channelLabels = [
    'portal' => $locale === 'ru' ? 'Портал' : 'Portal',
    'email' => $locale === 'ru' ? 'Почта' : 'Email',
];
?>

<div class="page-head">
    <div>
        <h1><?= e(t('notifications.title')) ?></h1>
        <p><?= e(t('notifications.subtitle')) ?></p>
    </div>

    <button class="button button-secondary" type="button" data-generate-reminders>
        <?= e(t('birthdays.generate')) ?>
    </button>
</div>

<section class="panel">
    <div class="notification-list">
        <?php if (!$notifications): ?>
            <p class="empty-state-large"><?= e(t('common.empty')) ?></p>
        <?php endif; ?>

        <?php foreach ($notifications as $notification): ?>
            <?php
            $recipient = $directory->find((int) $notification['recipient_id']);
            $recipientName = $recipient
                ? $directory->localized($recipient, 'name', $locale)
                : '#' . (int) $notification['recipient_id'];

            $typeLabel = $typeLabels[$notification['type']]
                ?? str_replace('_', ' ', ucwords($notification['type'], '_'));
            $channelLabel = $channelLabels[$notification['channel']] ?? $notification['channel'];
            ?>

            <article class="notification-row">
                <span class="notification-icon">
                    <?= $notification['channel'] === 'email' ? '@' : '◌' ?>
                </span>

                <div class="notification-copy">
                    <div>
                        <strong><?= e($typeLabel) ?></strong>
                        <span class="status-pill status-ok"><?= e($channelLabel) ?></span>
                    </div>

                    <p><?= e($notification['message_' . $locale]) ?></p>
                    <small>
                        <?= e(format_date($notification['created_at'], $locale, true)) ?> ·
                        <?= e($recipientName) ?>
                    </small>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
