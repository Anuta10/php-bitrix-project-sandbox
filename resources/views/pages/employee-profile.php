<div class="employee-profile-page">
    <a class="employee-profile-back" href="/employees">← <?= e(t('common.back')) ?></a>

    <div class="phonebook-profile-title">
        <span class="module-kicker"><?= e(t('employees.profile')) ?></span>
        <h1><?= e($employee['_name']) ?></h1>
        <p><?= e($employee['_position']) ?> · <?= e($employee['_department']) ?></p>
    </div>

    <section class="employee-profile-card">
        <div class="phonebook-profile-data">
            <?php if ($employee['_vacation']): ?>
                <div class="phonebook-vacation-note">
                    <?= $locale === 'ru' ? 'Сотрудник сейчас в отпуске' : 'Currently on vacation' ?> ·
                    <?= e(format_date($employee['vacation_start'], $locale)) ?> —
                    <?= e(format_date($employee['vacation_end'], $locale)) ?>
                </div>
            <?php endif; ?>

            <table>
                <tbody>
                <tr>
                    <th><?= $locale === 'ru' ? 'ФИО' : 'Name' ?></th>
                    <td><?= e($employee['_name']) ?></td>
                </tr>
                <tr>
                    <th><?= e(t('employees.department')) ?></th>
                    <td><?= e($employee['_department']) ?></td>
                </tr>
                <tr>
                    <th><?= $locale === 'ru' ? 'Должность' : 'Position' ?></th>
                    <td><?= e($employee['_position']) ?></td>
                </tr>
                <tr>
                    <th><?= e(t('employees.phone')) ?></th>
                    <td><?= e($employee['phone']) ?></td>
                </tr>
                <tr>
                    <th><?= e(t('employees.email')) ?></th>
                    <td><a href="mailto:<?= e($employee['email']) ?>"><?= e($employee['email']) ?></a></td>
                </tr>
                <tr>
                    <th><?= e(t('employees.location')) ?></th>
                    <td><?= e($employee['_location']) ?></td>
                </tr>
                <tr>
                    <th><?= e(t('employees.manager')) ?></th>
                    <td>
                        <?php if (!empty($employee['manager_id'])): ?>
                            <a href="/employees/<?= (int) $employee['manager_id'] ?>"><?= e($employee['_manager']) ?></a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <aside class="employee-profile-photo">
            <span class="avatar avatar-xl"><?= e(initials($employee['_name'])) ?></span>
            <strong><?= e($employee['_name']) ?></strong>
            <span><?= $employee['_vacation'] ? e(t('employees.vacation')) : ($locale === 'ru' ? 'На связи' : 'Available') ?></span>
            <button type="button" onclick="window.print()">⌑ <?= $locale === 'ru' ? 'Печать' : 'Print' ?></button>
        </aside>
    </section>

    <?php if (!empty($employee['manager_id'])):
        $manager = $directory->find((int) $employee['manager_id']);
    ?>
        <section class="employee-manager-card">
            <h2><?= $locale === 'ru' ? 'Руководитель' : 'Manager' ?></h2>
            <div class="phonebook-chief-table">
                <span><?= $locale === 'ru' ? 'ФИО' : 'Name' ?></span>
                <span><?= $locale === 'ru' ? 'Телефон' : 'Phone' ?></span>
                <span><?= $locale === 'ru' ? 'Статус' : 'Status' ?></span>
                <a href="/employees/<?= (int) $employee['manager_id'] ?>"><?= e($employee['_manager']) ?></a>
                <span><?= e($manager['phone'] ?? '—') ?></span>
                <span><?= $locale === 'ru' ? 'Работает' : 'Active' ?></span>
            </div>
        </section>
    <?php endif; ?>
</div>
