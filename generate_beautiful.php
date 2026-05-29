<?php
// generate_beautiful.php - Улучшенный дизайн коммерческих предложений
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

// Данные для примера
$data = [
    'company' => 'ООО "АСТИ"',
    'client' => 'Петров Сергей Владимирович',
    'client_company' => 'ООО "Ромашка"',
    'items' => [
        ['name' => 'Кухонный гарнитур', 'qty' => 1, 'price' => 150000],
        ['name' => 'Шкаф-купе', 'qty' => 1, 'price' => 85000],
    ]
];

$phpWord = new PhpWord();

// Настройка документа
$section = $phpWord->addSection([
    'orientation' => 'portrait',
    'marginTop' => 1000,
    'marginBottom' => 1000,
    'marginLeft' => 1500,
    'marginRight' => 1500
]);

// Заголовок
$section->addTitle('Коммерческое предложение', 1);
$section->addTextBreak(1);

// Информация о клиенте
$section->addText("Клиент: {$data['client']}", ['bold' => true]);
$section->addText("Компания: {$data['client_company']}");
$section->addText("Дата: " . date('d.m.Y'));
$section->addText("№ КП: " . date('Ymd') . '-' . rand(100, 999));
$section->addTextBreak(1);

// Таблица с товарами
$table = $section->addTable();
$table->addRow();
$table->addCell(2000)->addText('№', ['bold' => true]);
$table->addCell(6000)->addText('Наименование', ['bold' => true]);
$table->addCell(2000)->addText('Кол-во', ['bold' => true]);
$table->addCell(2000)->addText('Цена', ['bold' => true]);
$table->addCell(2000)->addText('Сумма', ['bold' => true]);

$total = 0;
foreach ($data['items'] as $index => $item) {
    $sum = $item['qty'] * $item['price'];
    $total += $sum;
    
    $table->addRow();
    $table->addCell(2000)->addText($index + 1);
    $table->addCell(6000)->addText($item['name']);
    $table->addCell(2000)->addText($item['qty']);
    $table->addCell(2000)->addText(number_format($item['price'], 2) . ' ₽');
    $table->addCell(2000)->addText(number_format($sum, 2) . ' ₽');
}

$table->addRow();
$table->addCell(12000, ['gridSpan' => 4])->addText('ИТОГО:', ['bold' => true]);
$table->addCell(2000)->addText(number_format($total, 2) . ' ₽', ['bold' => true]);

$section->addTextBreak(1);
$section->addText('Генеральный директор', ['bold' => true]);
$section->addTextBreak(2);
$section->addText('______________ /Иванов И.И./');

// Сохраняем
if (!is_dir('proposals')) {
    mkdir('proposals', 0777, true);
}

$filename = 'proposals/КП_' . date('Y-m-d_H-i-s') . '.docx';
$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save($filename);

echo "✅ КП успешно создано с улучшенным дизайном!\n";
echo "📁 Файл сохранен: {$filename}\n";
echo "📥 Полный путь: " . realpath($filename) . "\n\n";
echo "Откройте файл в Microsoft Word для просмотра!\n";
?>