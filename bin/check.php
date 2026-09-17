<?php

declare(strict_types=1);

$_SESSION = [];
require dirname(__DIR__) . '/bootstrap.php';

use App\Services\EmployeeDirectory;
use App\Support\DemoData;

$errors = [];
$warnings = [];

if (PHP_VERSION_ID < 80300) {
    $errors[] = 'Нужен PHP 8.3 или новее. Сейчас: ' . PHP_VERSION;
}

$requiredFiles = [
    'public/index.php',
    'public/assets/app.css',
    'public/assets/app.js',
    'data/demo.php',
    'README.md',
    'docs/project-structure.md',
    'docs/cases.md',
    'docs/demo-scenario.md',
    'docs/local-setup.md',
    'docs/deployment.md',
    'testing.md',
];
foreach ($requiredFiles as $file) {
    if (!is_file(base_path($file))) {
        $errors[] = 'Не найден обязательный файл: ' . $file;
    }
}

$state = new DemoData();
$directory = new EmployeeDirectory($state);
if (count($directory->all()) < 39) {
    $errors[] = 'Синтетический список сотрудников неожиданно мал.';
}
if (!$directory->findDepartment(10)) {
    $errors[] = 'Не найдено демо-подразделение группы сопровождения связи.';
}

// Перед публикацией проверяем, что в код случайно не вернулись названия реальных заказчиков.
$forbidden = ['gazprom', 'газпром', 'vekus', 'vsp.', 'dinteg', 'transgaz', 'трансгаз'];
foreach (['app','data','resources','public','config'] as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($dir), FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $item) {
        if (!$item->isFile()) {
            continue;
        }
        if (!in_array(strtolower($item->getExtension()), ['php','js','css','md','txt','html'], true)) {
            continue;
        }
        $content = strtolower((string) file_get_contents($item->getPathname()));
        foreach ($forbidden as $needle) {
            if (str_contains($content, $needle)) {
                $errors[] = 'Возможная ссылка на заказчика «' . $needle . '» в ' . str_replace(base_path() . '/', '', $item->getPathname());
            }
        }
    }
}

// В финальном интерфейсе не должно оставаться служебных суффиксов от промежуточных макетов.
foreach (['public/assets/app.css', 'resources/views'] as $path) {
    $fullPath = base_path($path);
    $files = is_dir($fullPath)
        ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullPath, FilesystemIterator::SKIP_DOTS))
        : [new SplFileInfo($fullPath)];

    foreach ($files as $item) {
        if (!$item->isFile()) {
            continue;
        }
        $content = (string) file_get_contents($item->getPathname());
        if (preg_match('/(?:^|[-_])v\d+(?:[-_]|\b)/i', $content)) {
            $errors[] = 'Найден служебный суффикс версии в ' . str_replace(base_path() . '/', '', $item->getPathname());
        }
    }
}

if (config('compensation.show_on_public_about_page', false)) {
    $errors[] = 'Для публичной версии нужно скрыть зарплатную вилку на странице About.';
}

// Тексты с фиксированным названием месяца быстро делают демо визуально устаревшим.
$demoText = text_lower((string) file_get_contents(base_path('data/demo.php')));
foreach (['январск', 'февральск', 'мартовск', 'апрельск', 'июньск', 'июльск', 'августовск', 'сентябрьск', 'октябрьск', 'ноябрьск', 'декабрьск'] as $monthWord) {
    if (str_contains($demoText, $monthWord)) {
        $errors[] = 'В демо-текстах найдено фиксированное название месяца: ' . $monthWord;
    }
}

printf("PHP: %s\n", PHP_VERSION);
printf("Сотрудников: %d\n", count($directory->all()));
printf("Подразделений: %d\n", count($directory->departments()));
foreach ($warnings as $warning) {
    echo "[WARN] {$warning}\n";
}
foreach ($errors as $error) {
    echo "[ERROR] {$error}\n";
}

echo $errors ? "CHECK FAILED\n" : "CHECK OK\n";
exit($errors ? 1 : 0);
