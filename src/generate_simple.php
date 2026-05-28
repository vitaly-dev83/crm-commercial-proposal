<?php
/**
 * Упрощенный генератор коммерческих предложений
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

// Тестовые данные сессии
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

// Получаем параметры
$deal_id = $_GET['deal_id'] ?? 1;

echo "<h2>Генерация коммерческого предложения</h2>";

try {
    // 1. Подключение к БД
    $db = Database::getConnection();
    echo "✅ Подключение к БД успешно<br>";
    
    // 2. Получаем данные сделки
    $deal = $db->query("
        SELECT d.*, c.company_name 
        FROM deals d 
        LEFT JOIN clients c ON d.client_id = c.client_id 
        WHERE d.deal_id = ?", 
        [$deal_id]
    )->fetch();
    
    if (!$deal) {
        die("❌ Сделка не найдена");
    }
    
    echo "✅ Сделка: {$deal['deal_name']} (#{$deal['deal_number']})<br>";
    
    // 3. Создаем простой документ
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();
    
    // Заголовок
    $section->addText('КОММЕРЧЕСКОЕ ПРЕДЛОЖЕНИЕ', ['bold' => true, 'size' => 16]);
    $section->addTextBreak();
    
    // Информация о сделке
    $section->addText("Номер сделки: {$deal['deal_number']}");
    $section->addText("Название: {$deal['deal_name']}");
    $section->addText("Клиент: {$deal['company_name']}");
    $section->addText("Бюджет: {$deal['budget']} руб.");
    $section->addText("Дата: " . date('d.m.Y'));
    $section->addTextBreak();
    
    // Текст предложения
    $section->addText('Уважаемый клиент!');
    $section->addText('Представляем вам коммерческое предложение по указанной сделке.');
    $section->addTextBreak();
    
    $section->addText('С уважением,');
    $section->addText('Компания ASTI Мебель');
    
    // 4. Сохраняем документ
    $output_dir = __DIR__ . '/../storage/';
    if (!is_dir($output_dir)) {
        mkdir($output_dir, 0777, true);
    }
    
    $output_file = $output_dir . 'proposal_' . $deal['deal_number'] . '.docx';
    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($output_file);
    
    echo "✅ Документ сгенерирован!<br>";
    
    // 5. Показываем ссылку
    $web_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $output_file);
    echo "<h3 style='color: green;'>🎉 Готово!</h3>";
    echo "<p><a href='$web_path' download style='
        padding: 15px 30px;
        background: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 18px;
    '>📥 Скачать коммерческое предложение</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Ошибка:</h3>";
    echo $e->getMessage();
}
?>