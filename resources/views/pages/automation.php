<?php
$statusLabels = [
    'success' => $locale === 'ru' ? 'Готово' : 'Done',
    'failed' => $locale === 'ru' ? 'Ошибка' : 'Error',
    'queued' => $locale === 'ru' ? 'В очереди' : 'Queued',
    'processing' => $locale === 'ru' ? 'В работе' : 'Processing',
];

$actionLabels = [
    'create' => $locale === 'ru' ? 'Создать задачу сопровождения' : 'Create support task',
    'update' => $locale === 'ru' ? 'Обновить задачу' : 'Update task',
    'comment' => $locale === 'ru' ? 'Перенести комментарий' : 'Copy comment',
    'delete' => $locale === 'ru' ? 'Удалить связанную задачу' : 'Remove linked task',
];
?>

<div class="page-head">
    <div>
        <a class="page-back-link" href="/calendar">← <?= $locale === 'ru' ? 'К календарю' : 'Back to calendar' ?></a>
        <h1><?= e(t('automation.title')) ?></h1>
        <p><?= e(t('automation.subtitle')) ?></p>
    </div>

    <div class="page-head-actions">
        <button class="button button-secondary" type="button" data-process-next>
            <?= e(t('automation.run_next')) ?>
        </button>
        <button class="button button-primary" type="button" data-process-all>
            <?= e(t('automation.run_all')) ?>
        </button>
    </div>
</div>

<div class="calendar-custom-note">
    <strong><?= $locale === 'ru' ? 'Это технический экран' : 'This is a technical view' ?></strong>
    <span>
        <?= $locale === 'ru'
            ? 'Обычные события сюда не попадают. Очередь появляется только после явного выбора «Сопровождения связи». Для сотрудника вся эта обработка остаётся под капотом.'
            : 'Regular events never enter this queue. Background work starts only after Communications Support is explicitly selected, and remains invisible to the employee.' ?>
    </span>
</div>

<div class="help-card">
    <span>i</span>
    <p><?= e(t('automation.failure_help')) ?></p>
</div>

<div class="automation-layout">
    <section class="panel">
        <div class="panel-head">
            <h2><?= e(t('automation.jobs')) ?></h2>
            <span><?= count($jobs) ?></span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th><?= e(t('automation.event')) ?></th>
                    <th><?= e(t('automation.action')) ?></th>
                    <th><?= e(t('automation.attempt')) ?></th>
                    <th><?= e(t('common.status')) ?></th>
                    <th><?= e(t('common.actions')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$jobs): ?>
                    <tr>
                        <td colspan="6" class="empty-cell">
                            <?= $locale === 'ru'
                                ? 'Сейчас очередь пуста. Создайте встречу с выбранным «Сопровождением связи».'
                                : 'The queue is empty. Create a meeting with Communications Support selected.' ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($jobs as $job):
                    $statusLabel = $statusLabels[$job['status']] ?? $job['status'];
                    $actionLabel = $actionLabels[$job['action']] ?? $job['action'];
                    $statusClass = $job['status'] === 'success' ? 'ok' : ($job['status'] === 'failed' ? 'error' : 'neutral');
                ?>
                    <tr>
                        <td>#<?= (int) $job['id'] ?></td>
                        <td>#<?= (int) $job['event_id'] ?></td>
                        <td><?= e($actionLabel) ?></td>
                        <td><?= (int) $job['attempt'] ?></td>
                        <td>
                            <span class="status-pill status-<?= $statusClass ?>"><?= e($statusLabel) ?></span>
                            <?php if (!empty($job['last_error'])): ?>
                                <small class="error-text">
                                    <?= $locale === 'ru' ? 'Временная ошибка. Можно повторить.' : e($job['last_error']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($job['status'] === 'failed'): ?>
                                <button class="button button-ghost button-sm" type="button" data-retry-job data-job-id="<?= (int) $job['id'] ?>">
                                    <?= e(t('common.retry')) ?>
                                </button>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2><?= e(t('automation.logs')) ?></h2>
            <span><?= count($logs) ?></span>
        </div>

        <div class="timeline-log">
            <?php foreach (array_slice($logs, 0, 30) as $log):
                $logAction = $actionLabels[$log['action']] ?? $log['action'];
            ?>
                <article class="log-entry <?= $log['status'] === 'failed' ? 'is-error' : 'is-success' ?>">
                    <span class="log-dot"></span>
                    <div class="log-main">
                        <div>
                            <time><?= e(format_time($log['created_at'])) ?></time>
                            <strong><?= e($logAction) ?></strong>
                            <span><?= $locale === 'ru' ? 'встреча' : 'meeting' ?> #<?= (int) $log['event_id'] ?></span>
                            <?php if ($log['task_id']): ?>
                                <span><?= $locale === 'ru' ? 'задача' : 'task' ?> #<?= (int) $log['task_id'] ?></span>
                            <?php endif; ?>
                        </div>
                        <p><?= e($log['message_' . $locale]) ?></p>
                    </div>
                    <div class="log-side">
                        <span><?= (int) $log['duration_ms'] ?> ms</span>
                        <small><?= $locale === 'ru' ? 'попытка' : 'attempt' ?> <?= (int) $log['attempt'] ?></small>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</div>
