<?php
// download_missing.php - Скачивание недостающих файлов
$files = [
    'Text.php' => 'https://raw.githubusercontent.com/PHPOffice/PHPWord/0.18.3/src/PhpWord/Element/Text.php',
    'Font.php' => 'https://raw.githubusercontent.com/PHPOffice/PHPWord/0.18.3/src/PhpWord/Style/Font.php',
    'Table.php' => 'https://raw.githubusercontent.com/PHPOffice/PHPWord/0.18.3/src/PhpWord/Element/Table.php',
    'Converter.php' => 'https://raw.githubusercontent.com/PHPOffice/PHPWord/0.18.3/src/PhpWord/Shared/Converter.php',
];

echo "<h1>Скачивание файлов PHPWord</h1>";

foreach ($files as $name => $url) {
    $localDir = __DIR__ . '/vendor/phpoffice/phpword/src/PhpWord/' . dirname(str_replace('https://raw.githubusercontent.com/PHPOffice/PHPWord/0.18.3/src/PhpWord/', '', $url));
    $localPath = __DIR__ . '/vendor/phpoffice/phpword/src/PhpWord/' . str_replace('https://raw.githubusercontent.com/PHPOffice/PHPWord/0.18.3/src/PhpWord/', '', $url);
    
    if (!is_dir($localDir)) {
        mkdir($localDir, 0777, true);
    }
    
    $content = @file_get_contents($url);
    if ($content !== false) {
        file_put_contents($localPath, $content);
        echo "<p style='color:green'>? $name скачан</p>";
    } else {
        echo "<p style='color:orange'>? $name не скачан</p>";
    }
}

echo "<p><a href='test.php'>Проверить работу</a></p>";
?>
