<?php
// autoload.php - Простой автозагрузчик
spl_autoload_register(function ($class) {
    // Для PHPWord
    if (strpos($class, 'PhpOffice\PhpWord\\') === 0) {
        $file = __DIR__ . '/vendor/phpoffice/phpword/src/PhpWord/' . 
                str_replace('\\', '/', substr($class, 18)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Загрузим основные файлы напрямую
$essential = [
    __DIR__ . '/vendor/phpoffice/phpword/src/PhpWord/PhpWord.php',
    __DIR__ . '/vendor/phpoffice/phpword/src/PhpWord/IOFactory.php',
    __DIR__ . '/vendor/phpoffice/phpword/src/PhpWord/TemplateProcessor.php'
];

foreach ($essential as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}
?>
