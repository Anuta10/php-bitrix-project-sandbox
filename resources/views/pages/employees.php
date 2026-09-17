<?php
$letters = $locale === 'ru'
    ? preg_split('//u', 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЭЮЯ', -1, PREG_SPLIT_NO_EMPTY)
    : range('A', 'Z');

$alphabetUrl = static function (string $value) use ($selectedDepartment): string {
    $params = ['letter' => $value];
    if ($selectedDepartment) {
        $params['department'] = (int) $selectedDepartment;
    }
    return '/employees?' . http_build_query($params);
};
?>

<div class="directory-page">
    <div class="directory-page-head">
        <div>
            <span class="module-kicker"><?= $locale === 'ru' ? 'Корпоративный справочник' : 'Corporate directory' ?></span>
            <h1><?= e(t('employees.title')) ?></h1>
        </div>
    </div>

    <section class="directory-nav">
        <nav class="directory-tabs" data-phonebook-tabs>
            <a class="is-active" href="#results" data-phonebook-tab="employees">
                <?= $locale === 'ru' ? 'Работники' : 'Employees' ?>
            </a>
            <a href="#structure" data-phonebook-tab="departments">
                <?= $locale === 'ru' ? 'Подразделения' : 'Departments' ?>
            </a>
        </nav>

        <form class="directory-search" method="get" action="/employees" data-employee-search-form>
            <input type="hidden" name="letter" value="<?= e($letter ?? '') ?>" data-employee-letter-input>
            <input
                type="search"
                name="q"
                value="<?= e($query) ?>"
                placeholder="<?= e(t('employees.placeholder')) ?>"
                autocomplete="off"
                data-employee-search-input
            >

            <select name="department" data-employee-department>
                <option value="0"><?= $locale === 'ru' ? 'Все подразделения' : 'All departments' ?></option>
                <?php foreach ($departments as $department): ?>
                    <?php if ((int) $department['id'] === 1) continue; ?>
                    <option
                        value="<?= (int) $department['id'] ?>"
                        <?= (int) ($selectedDepartment ?? 0) === (int) $department['id'] ? 'selected' : '' ?>
                    >
                        <?= e($directory->localized($department, 'name', $locale)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit"><?= e(t('common.search')) ?></button>
        </form>

        <div class="directory-alphabet" aria-label="Alphabet" data-employee-alphabet>
            <?php foreach ($letters as $item): ?>
                <a
                    href="<?= e(($letter ?? '') === $item ? $alphabetUrl('') : $alphabetUrl($item)) ?>"
                    data-letter="<?= e($item) ?>"
                    class="<?= ($letter ?? '') === $item ? 'is-active' : '' ?>"
                >
                    <?= e($item) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <p class="phonebook-layout-hint">
            <?= $locale === 'ru'
                ? 'Алфавит фильтрует сотрудников по первой букве фамилии. Строка поиска ищет шире: по ФИО, должности, подразделению и телефону.'
                : 'The alphabet filters by surname initial. Text search is broader: name, role, department and phone.' ?>
        </p>
    </section>

    <div class="directory-layout">
        <aside class="directory-structure" id="structure">
            <div class="phonebook-section-title">
                <h2><?= e(t('employees.org_structure')) ?></h2>
            </div>

            <a
                class="phonebook-root-link <?= !$selectedDepartment ? 'is-active' : '' ?>"
                href="/employees<?= ($letter ?? '') !== '' ? '?' . e(http_build_query(['letter' => $letter])) : '' ?>"
            >
                <?= $locale === 'ru' ? 'Вся компания' : 'Whole company' ?>
                <span><?= count($directory->all()) ?></span>
            </a>

            <div class="directory-tree">
                <?php require base_path('resources/views/partials/department-tree.php'); ?>
            </div>
        </aside>

        <section class="directory-results" id="results">
            <div class="phonebook-results-head">
                <div>
                    <h2><?= $locale === 'ru' ? 'Сотрудники' : 'Employees' ?></h2>
                    <span>
                        <?= $locale === 'ru' ? 'Найдено' : 'Found' ?>:
                        <strong data-results-count><?= (int) $totalResults ?></strong>
                        <?php if (($letter ?? '') !== ''): ?>
                            · <?= $locale === 'ru' ? 'фамилия на' : 'surname starts with' ?> «<?= e($letter) ?>»
                        <?php endif; ?>
                    </span>
                </div>
                <small><?= $locale === 'ru' ? 'Подгрузка без перезагрузки' : 'Incremental loading without reload' ?></small>
            </div>

            <div class="directory-table">
                <div class="phonebook-table-head">
                    <span><?= $locale === 'ru' ? 'Сотрудник' : 'Employee' ?></span>
                    <span><?= $locale === 'ru' ? 'Телефон' : 'Phone' ?></span>
                    <span><?= $locale === 'ru' ? 'Должность' : 'Position' ?></span>
                    <span><?= $locale === 'ru' ? 'Подразделение' : 'Department' ?></span>
                </div>

                <div class="phonebook-table-body" data-employee-results>
                    <?php foreach ($results as $employee): ?>
                        <?php require base_path('resources/views/partials/employee-card.php'); ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div
                class="phonebook-load-more-wrap"
                <?= count($results) >= $totalResults ? 'hidden' : '' ?>
                data-load-more-wrap
            >
                <span>
                    <span data-visible-count><?= count($results) ?></span>
                    <?= $locale === 'ru' ? 'из' : 'of' ?>
                    <span data-total-count><?= (int) $totalResults ?></span>
                </span>
                <button
                    type="button"
                    data-employee-load-more
                    data-offset="<?= count($results) ?>"
                    data-limit="<?= (int) $pageSize ?>"
                >
                    <?= $locale === 'ru' ? 'Показать ещё' : 'Load more' ?>
                </button>
            </div>
        </section>
    </div>
</div>
