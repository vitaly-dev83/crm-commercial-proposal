<?php
// generate_beautiful.php - Улучшенный дизайн коммерческих предложений
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\TablePosition;
use PhpOffice\PhpWord\Style\Cell;
use PhpOffice\PhpWord\Style\Table;

// Данные для примера (можно заменить на форму)
$data = [
    'company' => 'ООО "ТехноСервис"',
    'client' => 'Петров Сергей Владимирович',
    'client_company' => 'ООО "Ромашка"',
    'items' => [
        ['name' => 'Ноутбук HP ProBook 450 G9', 'qty' => 5, 'price' => 65000],
        ['name' => 'Монитор Dell 27" 4K', 'qty' => 5, 'price' => 35000],
        ['name' => 'Клавиатура Logitech MX Keys', 'qty' => 10, 'price' => 8500],
        ['name' => 'Мышь Logitech MX Master 3S', 'qty' => 10, 'price' => 7200],
    ]
];

$phpWord = new PhpWord();

// Настройка стилей документа
$phpWord->setDefaultFontName('DejaVu Sans');
$phpWord->setDefaultFontSize(11);

// Стиль для заголовков
$titleStyle = ['bold' => true, 'size' => 24, 'color' => '2c3e50', 'allCaps' => true];
$subtitleStyle = ['bold' => true, 'size' => 14, 'color' => '34495e'];

// Стиль для текста
$textStyle = ['size' => 11, 'color' => '2c3e50'];

// Добавляем секцию с отступами
$section = $phpWord->addSection([
    'orientation' => 'portrait',
    'marginTop' => 1000,
    'marginBottom' => 1000,
    'marginLeft' => 1500,
    'marginRight' => 1500
]);

// Добавляем водяной знак (текст на фоне)
$header = $section->addHeader();
$header->addWatermark('КОММЕРЧЕСКОЕ ПРЕДЛОЖЕНИЕ', ['color' => 'e0e0e0', 'size' => 48]);

// ===== ВЕРХНИЙ КОЛОНТИТУЛ =====
$tableHeader = $section->addTable(['width' => 100*50, 'unit' => 'pct']);
$tableHeader->addRow();

// Логотип/название компании
$cell = $tableHeader->addCell(6000);
$cell->addText('ООО "АСТИ"', ['bold' => true, 'size' => 18, 'color' => '3498db']);
$cell->addText('Ваш надежный партнер', ['size' => 10, 'color' => '7f8c8d']);
$cell->addTextBreak(1);
$cell->addText('ИНН: 1234567890 | ОГРН: 1234567890123', ['size' => 9, 'color' => '95a5a6']);
$cell->addText('Тел: +7 (495) 123-45-67 | Email: info@asti.ru', ['size' => 9, 'color' => '95a5a6']);

$section->addTextBreak(1);

// ===== ЗАГОЛОВОК =====
$section->addText('КОММЕРЧЕСКОЕ ПРЕДЛОЖЕНИЕ', $titleStyle, ['alignment' => Jc::CENTER]);
$section->addTextBreak(1);

// Декоративная линия
$section->addText('__________________________________________________', ['size' => 8, 'color' => 'bdc3c7'], ['alignment' => Jc::CENTER]);
$section->addTextBreak(1);

// ===== ИНФОРМАЦИЯ О КЛИЕНТЕ =====
$infoTable = $section->addTable(['width' => 100*50, 'unit' => 'pct', 'borderSize' => 0]);
$infoTable->addRow();

$infoTable->addCell(5000, ['borderSize' => 0]);
$infoTable->addCell(5000, ['borderSize' => 0]);

$section->addTextBreak(0.5);

// Выделенная информация
$phpWord->addFontStyle('rTitle', ['bold' => true, 'size' => 10, 'color' => '7f8c8d']);
$phpWord->addFontStyle('rValue', ['bold' => true, 'size' => 11, 'color' => '2c3e50']);

$section->addText('Кому:', 'rTitle');
$section->addText($data['client'], 'rValue');
$section->addText($data['client_company'], 'rValue');
$section->addTextBreak(0.5);

$section->addText('От кого:', 'rTitle');
$section->addText($data['company'], 'rValue');
$section->addTextBreak(0.5);

$section->addText('Дата:', 'rTitle');
$section->addText(date('d.m.Y'), 'rValue');
$section->addTextBreak(0.5);

$section->addText('№ КП:', 'rTitle');
$section->addText(date('Ymd') . '-' . rand(100, 999), 'rValue');

$section->addTextBreak(1);

// ===== ВВОДНЫЙ ТЕКСТ =====
$section->addText('Уважаемый клиент!', ['bold' => true, 'size' => 12, 'color' => '2c3e50']);
$section->addText('Благодарим Вас за обращение в нашу компанию. Направляем Вам коммерческое предложение на поставку следующего оборудования:', ['size' => 11, 'color' => '34495e']);
$section->addTextBreak(1);

// ===== ТАБЛИЦА С ТОВАРАМИ =====
// Стили для таблицы
$tableStyle = [
    'borderColor' => 'bdc3c7',
    'borderSize' => 6,
    'cellMargin' => 80,
    'alignment' => Jc::CENTER,
];
$phpWord->addTableStyle('fancyTable', $tableStyle);

// Стили для ячеек
$cellHeaderStyle = [
    'bgColor' => '3498db',
    'valign' => 'center',
    'borderColor' => '2980b9',
    'borderSize' => 6
];

$cellBodyStyle = [
    'valign' => 'center',
    'borderColor' => 'ecf0f1',
    'borderSize' => 6
];

$table = $section->addTable('fancyTable');

// Заголовки таблицы
$table->addRow(400);
$table->addCell(1000, $cellHeaderStyle)->addText('№', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);
$table->addCell(5000, $cellHeaderStyle)->addText('Наименование товара/услуги', ['bold' => true, 'color' => 'ffffff', 'size' => 12]);
$table->addCell(1500, $cellHeaderStyle)->addText('Кол-во', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);
$table->addCell(2000, $cellHeaderStyle)->addText('Цена (руб.)', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);
$table->addCell(2500, $cellHeaderStyle)->addText('Сумма (руб.)', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);

// Заполнение данных
$total = 0;
foreach ($data['items'] as $index => $item) {
    $sum = $item['qty'] * $item['price'];
    $total += $sum;
    
    $table->addRow();
    $table->addCell(1000, $cellBodyStyle)->addText($index + 1, null, ['alignment' => Jc::CENTER]);
    $table->addCell(5000, $cellBodyStyle)->addText($item['name']);
    $table->addCell(1500, $cellBodyStyle)->addText($item['qty'], null, ['alignment' => Jc::CENTER]);
    $table->addCell(2000, $cellBodyStyle)->addText(number_format($item['price'], 0, '', ' '), null, ['alignment' => Jc::CENTER]);
    $table->addCell(2500, $cellBodyStyle)->addText(number_format($sum, 0, '', ' '), null, ['alignment' => Jc::CENTER]);
}

// Итоговая строка
$table->addRow();
$cellTotal = $table->addCell(9500, ['gridSpan' => 4]);
$cellTotal->addText('ИТОГО:', ['bold' => true, 'size' => 13]);
$table->addCell(2500, ['bgColor' => 'ecf0f1'])->addText(number_format($total, 0, '', ' ') . ' ₽', ['bold' => true, 'size' => 13, 'color' => 'e74c3c'], ['alignment' => Jc::CENTER]);

$section->addTextBreak(1);

// ===== ДОПОЛНИТЕЛЬНЫЕ УСЛОВИЯ =====
$section->addText('Условия поставки:', ['bold' => true, 'size' => 13, 'color' => '2c3e50']);
$section->addTextBreak(0.5);

$conditions = [
    '✓ Срок поставки: 3-5 рабочих дней с момента оплаты',
    '✓ Доставка: бесплатно при заказе от 500 000 ₽',
    '✓ Гарантия: 24 месяца на все оборудование',
    '✓ Оплата: безналичный расчет, отсрочка платежа до 30 дней для постоянных клиентов',
    '✓ Скидка: 5% при заказе от 300 000 ₽, 10% от 500 000 ₽'
];

foreach ($conditions as $condition) {
    $section->addText($condition, ['size' => 11, 'color' => '34495e']);
}

$section->addTextBreak(1);

// ===== ПОДПИСИ =====
$signTable = $section->addTable(['width' => 100*50, 'unit' => 'pct', 'borderSize' => 0]);
$signTable->addRow();

// Отправитель
$cell = $signTable->addCell(5000, ['borderSize' => 0]);
$cell->addText('Генеральный директор', ['bold' => true, 'size' => 11]);
$cell->addTextBreak(2);
$cell->addText('______________ /Иванов И.И./', ['size' => 10]);

// Получатель
$cell = $signTable->addCell(5000, ['borderSize' => 0]);
$cell->addText('Клиент', ['bold' => true, 'size' => 11]);
$cell->addTextBreak(2);
$cell->addText('______________ /_________________/', ['size' => 10]);

$section->addTextBreak(1);

// ===== НИЖНИЙ КОЛОНТИТУЛ =====
$footer = $section->addFooter();
$footer->addText('С уважением, команда ООО "АСТИ" • www.asti.ru • +7 (495) 123-45-67', 
    ['size' => 8, 'color' => '95a5a6'], ['alignment' => Jc::CENTER]);

// Сохраняем документ
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