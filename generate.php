<?php
// generate.php - Прямая генерация КП через командную строку
require_once 'autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

echo "=== CRM ASTI - Генератор коммерческих предложений ===\n\n";

// Данные для примера
$data = [
    'company' => 'ООО "ТехноСервис"',
    'client' => 'Петров Сергей',
    'items' => [
        ['name' => 'Ноутбук HP ProBook', 'qty' => 5, 'price' => 45000],
        ['name' => 'Монитор Dell 24"', 'qty' => 5, 'price' => 15000],
        ['name' => 'Клавиатура Logitech', 'qty' => 10, 'price' => 1500],
        ['name' => 'Мышь Logitech', 'qty' => 10, 'price' => 800],
        ['name' => 'Программное обеспечение', 'qty' => 5, 'price' => 5000],
    ]
];

echo "Создание КП для компании: {$data['company']}\n";
echo "Контактное лицо: {$data['client']}\n\n";

try {
    $phpWord = new PhpWord();
    
    // Настройка документа
    $section = $phpWord->addSection();
    
    // Заголовок
    $section->addTitle('КОММЕРЧЕСКОЕ ПРЕДЛОЖЕНИЕ', 1);
    $section->addTextBreak(1);
    
    // Информация о компании
    $section->addText("От: ООО \"АСТИ\"", ['bold' => true]);
    $section->addText("Клиент: {$data['company']}");
    $section->addText("Контакт: {$data['client']}");
    $section->addText("Дата: " . date('d.m.Y'));
    $section->addTextBreak(1);
    
    // Таблица с товарами
    $table = $section->addTable();
    $table->addRow();
    $table->addCell(2000)->addText('№', ['bold' => true]);
    $table->addCell(5000)->addText('Наименование', ['bold' => true]);
    $table->addCell(2000)->addText('Кол-во', ['bold' => true]);
    $table->addCell(2000)->addText('Цена', ['bold' => true]);
    $table->addCell(2000)->addText('Сумма', ['bold' => true]);
    
    $total = 0;
    foreach ($data['items'] as $index => $item) {
        $sum = $item['qty'] * $item['price'];
        $total += $sum;
        
        $table->addRow();
        $table->addCell(2000)->addText($index + 1);
        $table->addCell(5000)->addText($item['name']);
        $table->addCell(2000)->addText($item['qty']);
        $table->addCell(2000)->addText(number_format($item['price'], 2) . ' ?');
        $table->addCell(2000)->addText(number_format($sum, 2) . ' ?');
    }
    
    $table->addRow();
    $table->addCell(11000, ['gridSpan' => 4])->addText('ИТОГО:', ['bold' => true]);
    $table->addCell(2000)->addText(number_format($total, 2) . ' ?', ['bold' => true]);
    
    $section->addTextBreak(1);
    $section->addText('С уважением,', ['bold' => true]);
    $section->addText('Генеральный директор ООО "АСТИ"');
    $section->addText('______________ /Иванов И.И./');
    
    // Сохраняем
    if (!is_dir('proposals')) {
        mkdir('proposals', 0777, true);
    }
    
    $filename = 'proposals/КП_' . date('Y-m-d_H-i-s') . '.docx';
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($filename);
    
    echo "? КП успешно создано!\n";
    echo "?? Файл сохранен: {$filename}\n";
    echo "?? Путь: " . realpath($filename) . "\n";
    
} catch (Exception $e) {
    echo "? Ошибка: " . $e->getMessage() . "\n";
}
?>
