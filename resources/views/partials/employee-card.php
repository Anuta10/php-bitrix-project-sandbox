<?php
$terms = $highlightTerms ?? [];
$highlight = static function (string $value) use ($terms): string {
    $safe = e($value);
    foreach ($terms as $term) {
        $term = trim((string) $term);
        if ($term === '') {
            continue;
        }
        $safeTerm = e($term);
        $safe = preg_replace(
            '/(' . preg_quote($safeTerm, '/') . ')/iu',
            '<mark class="phonebook-highlight">$1</mark>',
            $safe
        ) ?? $safe;
    }
    return $safe;
};
?>

<article class="phonebook-employee-row" data-employee-card>
    <a class="phonebook-employee-name" href="/employees/<?= (int) $employee['id'] ?>">
        <span class="avatar"><?= e(initials($employee['_name'])) ?></span>
        <span>
            <strong><?= $highlight($employee['_name']) ?></strong>
            <?php if (!empty($employee['_vacation'])): ?>
                <small><?= e(t('employees.vacation')) ?></small>
            <?php elseif (!empty($employee['_match']) && $employee['_match'] !== 'all'): ?>
                <small><?= e(t('employees.matched')) ?>: <?= e($employee['_match']) ?></small>
            <?php endif; ?>
        </span>
    </a>

    <span class="phonebook-cell phonebook-phone"><?= $highlight((string) $employee['phone']) ?></span>
    <span class="phonebook-cell"><?= $highlight($employee['_position']) ?></span>
    <span class="phonebook-cell"><?= $highlight($employee['_department']) ?></span>
</article>
