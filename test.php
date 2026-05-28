<?php
// test.php - Простой тест
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'autoload.php';

echo "<h1>Тест CRM ASTI</h1>";

// Проверка PHPWord
if (class_exists('PhpOffice\PhpWord\PhpWord')) {
    echo "<p style='color:green'>✅ PHPWord загружен!</p>";
    
    try {
        // Создаем простой документ
        $phpWord = new PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Тест CRM ASTI');
        $section->addText('Дата: ' . date('d.m.Y H:i:s'));
        
        echo "<p style='color:green'>✅ Документ создан!</p>";
        
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Ошибка: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>❌ PHPWord не загружен</p>";
}

// Проверка файлов
echo "<h2>Проверка файлов:</h2>";
$files = [
    'vendor/phpoffice/phpword/src/PhpWord/PhpWord.php',
    'vendor/phpoffice/phpword/src/PhpWord/IOFactory.php',
    'vendor/phpoffice/phpword/src/PhpWord/TemplateProcessor.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo file_exists($path) ? "✅ $file<br>" : "❌ $file<br>";
}
?>
