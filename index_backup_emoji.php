<?php
// index.php - CRM система для мебельной компании ASTI (DOCX + PDF на одной странице)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

$generationMessage = '';
$generationError = '';
$downloadLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_docx'])) {
    try {
        $companyName = htmlspecialchars($_POST['company_name'] ?? 'АСТИ Мебель');
        $clientName = htmlspecialchars($_POST['client_name'] ?? 'Иванов Иван Иванович');
        $clientCompany = htmlspecialchars($_POST['client_company'] ?? 'ООО "Эльдорадо"');
        $discount = floatval($_POST['discount'] ?? 0);
        
        $products = $_POST['products'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $prices = $_POST['prices'] ?? [];
        
        if (empty($products) || empty($products[0])) {
            $products = ['Кухонный гарнитур'];
            $quantities = [1];
            $prices = [0];
        }
        
        $phpWord = new PhpWord();
        
        $section = $phpWord->addSection([
            'orientation' => 'portrait',
            'marginTop' => 800,
            'marginBottom' => 800,
            'marginLeft' => 1200,
            'marginRight' => 1200
        ]);
        
        // Логотип
        if (file_exists('logo.png')) {
            try {
                $section->addImage('logo.png', ['width' => 80, 'height' => 80, 'alignment' => Jc::CENTER]);
                $section->addTextBreak(0.3);
            } catch (Exception $e) {}
        }
        
        $section->addText('ПРОИЗВОДСТВО МЕБЕЛИ НА ЗАКАЗ', 
            ['bold' => true, 'size' => 12, 'color' => 'e74c3c'], 
            ['alignment' => Jc::CENTER]);
        $section->addText('ИНН: 1234567890 | ОГРН: 1234567890123 | тел: +7 (495) 123-45-67 | email: info@asti-furniture.ru', 
            ['size' => 9, 'color' => '34495e'], 
            ['alignment' => Jc::CENTER]);
        $section->addTextBreak(0.5);
        $section->addText('? ? ?', ['size' => 20, 'color' => 'bdc3c7'], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(0.5);
        $section->addText('КОММЕРЧЕСКОЕ ПРЕДЛОЖЕНИЕ', 
            ['bold' => true, 'size' => 18, 'color' => '2c3e50'], 
            ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);
        
        // Информация о клиенте
        $infoTable = $section->addTable([
            'width' => 100*50,
            'unit' => 'pct', 
            'borderSize' => 0,
            'alignment' => Jc::CENTER,
            'cellMargin' => 4
        ]);
        
        $infoTable->addRow();
        $infoTable->addCell(1100, ['borderSize' => 0])->addText('Кому:', ['bold' => true, 'size' => 10, 'color' => '2c3e50']);
        $infoTable->addCell(5200, ['borderSize' => 0])->addText($clientName, ['size' => 10, 'color' => '2c3e50']);
        $infoTable->addCell(1300, ['borderSize' => 0, 'noWrap' => true])->addText('От кого:', ['bold' => true, 'size' => 10, 'color' => '2c3e50']);
        $infoTable->addCell(3200, ['borderSize' => 0])->addText($companyName, ['size' => 10, 'color' => '2c3e50']);
        
        $infoTable->addRow();
        $infoTable->addCell(1100, ['borderSize' => 0])->addText('');
        $infoTable->addCell(5200, ['borderSize' => 0])->addText($clientCompany, ['size' => 10, 'color' => '34495e']);
        $infoTable->addCell(1300, ['borderSize' => 0])->addText('');
        $infoTable->addCell(3200, ['borderSize' => 0])->addText('');
        
        $infoTable->addRow();
        $infoTable->addCell(1100, ['borderSize' => 0])->addText('Дата:', ['bold' => true, 'size' => 10, 'color' => '2c3e50']);
        $infoTable->addCell(2000, ['borderSize' => 0])->addText(date('d.m.Y'), ['size' => 10, 'color' => '2c3e50']);
        $infoTable->addCell(1300, ['borderSize' => 0, 'noWrap' => true])->addText('№ КП:', ['bold' => true, 'size' => 10, 'color' => '2c3e50']);
        $infoTable->addCell(6200, ['borderSize' => 0])->addText('АМ-' . date('Ymd') . '-' . rand(100, 999), ['size' => 10, 'color' => '2c3e50']);
        
        $section->addTextBreak(1.2);
        
        // Обращение
        $section->addText('Уважаемый ' . $clientName . '!', ['bold' => true, 'size' => 11, 'color' => '2c3e50']);
        $section->addText('Благодарим Вас за обращение в компанию "АСТИ Мебель". Направляем коммерческое предложение:', 
            ['size' => 11, 'color' => '2c3e50']);
        $section->addTextBreak(0.8);
        
        // Таблица товаров
        $tableStyle = [
            'borderColor' => '3498db',
            'borderSize' => 4,
            'cellMargin' => 60,
            'width' => 100*50,
            'unit' => 'pct'
        ];
        $phpWord->addTableStyle('productsTable', $tableStyle);
        $table = $section->addTable('productsTable');
        
        $table->addRow();
        $table->addCell(400, ['bgColor' => '3498db'])->addText('№', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);
        $table->addCell(5500, ['bgColor' => '3498db'])->addText('Наименование изделия', ['bold' => true, 'color' => 'ffffff', 'size' => 12]);
        $table->addCell(1200, ['bgColor' => '3498db'])->addText('Кол-во', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);
        $table->addCell(1800, ['bgColor' => '3498db'])->addText('Цена, ?', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);
        $table->addCell(2300, ['bgColor' => '3498db'])->addText('Сумма, ?', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);
        
        $total = 0;
        foreach ($products as $idx => $product) {
            if (!empty($product)) {
                $qty = floatval($quantities[$idx] ?? 1);
                $price = floatval($prices[$idx] ?? 0);
                $sum = $qty * $price;
                $total += $sum;
                
                $table->addRow();
                $table->addCell(400)->addText($idx + 1, null, ['alignment' => Jc::CENTER]);
                $table->addCell(5500)->addText($product);
                $table->addCell(1200)->addText($qty, null, ['alignment' => Jc::CENTER]);
                $table->addCell(1800)->addText(number_format($price, 2), null, ['alignment' => Jc::CENTER]);
                $table->addCell(2300)->addText(number_format($sum, 2), null, ['alignment' => Jc::CENTER]);
            }
        }
        
        $finalTotal = $total;
        if ($discount > 0) {
            $discountAmount = $total * ($discount / 100);
            $finalTotal = $total - $discountAmount;
            
            $table->addRow();
            $table->addCell(9500, ['gridSpan' => 4])->addText('Подытог:', ['bold' => true, 'size' => 10]);
            $table->addCell(2300)->addText(number_format($total, 2), null, ['alignment' => Jc::CENTER]);
            
            $table->addRow();
            $table->addCell(9500, ['gridSpan' => 4])->addText('Скидка ' . $discount . '%:', ['bold' => true, 'size' => 10, 'color' => 'e74c3c']);
            $table->addCell(2300)->addText('- ' . number_format($discountAmount, 2), ['color' => 'e74c3c'], ['alignment' => Jc::CENTER]);
        }
        
        $table->addRow();
        $table->addCell(9500, ['gridSpan' => 4])->addText('ИТОГО К ОПЛАТЕ:', ['bold' => true, 'size' => 10]);
        $table->addCell(2300, ['bgColor' => 'e8f8f5'])->addText(number_format($finalTotal, 2) . ' ?', 
            ['bold' => true, 'size' => 12, 'color' => 'e74c3c'], ['alignment' => Jc::CENTER]);
        
        $section->addTextBreak(0.8);
        
        // Преимущества
        $section->addText('? НАШИ ПРЕИМУЩЕСТВА', ['bold' => true, 'size' => 12, 'color' => 'e74c3c']);
        $section->addTextBreak(0.3);
        $advText = '?? Лидер рынка с 2010 года        ? Индивидуальный подход         ?? Своё производство';
        $section->addText($advText, ['size' => 10, 'color' => '2c3e50']);
        $section->addTextBreak(0.9);
        
        // Условия
        $section->addText('?? УСЛОВИЯ СОТРУДНИЧЕСТВА', ['bold' => true, 'size' => 12, 'color' => 'e74c3c']);
        $section->addTextBreak(0.5);
        $condText1 = '? Срок изготовления: 7-14 дней     ? Доставка: бесплатно по Москве     ? Гарантия: 12-36 месяцев';
        $section->addText($condText1, ['size' => 10, 'color' => '2c3e50']);
        $condText2 = '?? Оплата: 50% предоплата    ?? Сборка: включена в стоимость   ?? Материалы: ЛДСП/МДФ/массив';
        $section->addText($condText2, ['size' => 10, 'color' => '2c3e50']);
        $section->addTextBreak(1);
        
        // Подписи
        $signTable = $section->addTable([
            'width' => 85*50,
            'unit' => 'pct', 
            'borderSize' => 0,
            'alignment' => Jc::CENTER,
            'cellMargin' => 10
        ]);
        
        $signTable->addRow();
        $signTable->addCell(5000, ['borderSize' => 0])->addText('Генеральный директор:', ['bold' => true, 'size' => 10, 'color' => '2c3e50']);
        $signTable->addCell(5000, ['borderSize' => 0])->addText('Клиент:', ['bold' => true, 'size' => 10, 'color' => '2c3e50']);
        
        $signTable->addRow(1134);
        $signTable->addCell(5000, ['borderSize' => 0, 'valign' => 'bottom'])->addText('____________________________ /Иванов И.И./', ['size' => 8]);
        $signTable->addCell(5000, ['borderSize' => 0, 'valign' => 'bottom'])->addText('_________________________ /______________/', ['size' => 8]);
        
        $section->addTextBreak(0.5);
        
        // Сохраняем
        if (!is_dir('proposals')) {
            mkdir('proposals', 0777, true);
        }
        
        $filename = 'proposals/КП_АСТИ_Мебель_' . date('Y-m-d_H-i-s') . '.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filename);
        
        $generationMessage = "? DOCX успешно создано!";
        $downloadLink = $filename;
        
    } catch (Exception $e) {
        $generationError = "? Ошибка DOCX: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>АСТИ Мебель - Генератор КП</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#667eea,#764ba2);min-height:100vh;padding:30px}
        .container{max-width:1000px;margin:0 auto}
        .header{background:#fff;border-radius:20px;padding:40px;margin-bottom:30px;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.1)}
        .header h1{font-size:2.5em;background:linear-gradient(135deg,#e74c3c,#f39c12);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .header p{color:#666;margin-top:8px}
        .content{background:#fff;border-radius:20px;padding:40px;box-shadow:0 10px 30px rgba(0,0,0,0.1)}
        .form-group{margin-bottom:20px}
        label{display:block;margin-bottom:8px;font-weight:600;color:#2c3e50}
        input{width:100%;padding:12px 16px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;transition:0.2s}
        input:focus{outline:none;border-color:#e74c3c;box-shadow:0 0 0 3px rgba(231,76,60,0.1)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .product-item{background:#f8f9fa;border-radius:12px;padding:15px;margin-bottom:10px;border:1px solid #e0e0e0}
        .product-grid{display:grid;grid-template-columns:1fr 100px 120px;gap:10px}
        .remove-product{background:#e74c3c;color:#fff;border:none;padding:5px 15px;border-radius:6px;cursor:pointer;margin-top:10px}
        .add-product{background:#27ae60;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer}
        .button-group{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px}
        .btn-docx{background:linear-gradient(135deg,#3498db,#2980b9);color:#fff;border:none;padding:15px;border-radius:12px;font-size:16px;font-weight:bold;cursor:pointer;transition:0.2s}
        .btn-pdf{background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff;border:none;padding:15px;border-radius:12px;font-size:16px;font-weight:bold;cursor:pointer;transition:0.2s}
        .btn-docx:hover,.btn-pdf:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(0,0,0,0.2)}
        .success-message{background:#d4edda;color:#155724;padding:15px;border-radius:10px;margin-bottom:20px;border-left:4px solid #27ae60}
        .error-message{background:#f8d7da;color:#721c24;padding:15px;border-radius:10px;margin-bottom:20px;border-left:4px solid #dc3545}
        .download-btn{background:#27ae60;color:#fff;padding:8px 20px;border-radius:6px;text-decoration:none;display:inline-block;margin-top:10px}
        @media (max-width:768px){.product-grid,.form-row,.button-group{grid-template-columns:1fr}.content{padding:25px}}
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>?? АСТИ Мебель</h1>
        <p>Производство мебели на заказ | Генератор коммерческих предложений</p>
    </div>
    <div class="content">
        <?php if ($generationMessage): ?>
        <div class="success-message">
            ?? <strong><?php echo $generationMessage; ?></strong>
            <?php if ($downloadLink): ?>
            <br><a href="<?php echo $downloadLink; ?>" class="download-btn" download>?? Скачать DOCX</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($generationError): ?>
        <div class="error-message">? <?php echo $generationError; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="mainForm">
            <div class="form-row">
                <div class="form-group"><label>?? Ваша компания</label><input type="text" name="company_name" required value="АСТИ Мебель"></div>
                <div class="form-group"><label>?? ФИО клиента</label><input type="text" name="client_name" required placeholder="Иванов Иван Иванович"></div>
            </div>
            <div class="form-group"><label>?? Название организации</label><input type="text" name="client_company" placeholder="ООО Эльдорадо"></div>
            <div class="form-group">
                <label>?? Изделия мебели</label>
                <div id="productsContainer">
                    <div class="product-item"><div class="product-grid"><input type="text" name="products[]" placeholder="Наименование изделия"><input type="number" name="quantities[]" placeholder="Кол-во" value="1"><input type="text" name="prices[]" placeholder="Цена"></div></div>
                </div>
                <button type="button" class="add-product" onclick="addProduct()">+ Добавить изделие</button>
            </div>
            <div class="form-group"><label>?? Скидка (%)</label><input type="number" name="discount" step="any" min="0" max="100" value="0"></div>
            
           <div class="button-group">
    <button type="submit" name="generate_docx" class="btn-docx">?? Создать DOCX</button>
    <button type="button" class="btn-pdf" onclick="submitToPDF()">?? Создать PDF</button>
</div>
        </form>
    </div>
</div>

<script>
function addProduct() {
    const container = document.getElementById('productsContainer');
    const div = document.createElement('div');
    div.className = 'product-item';
    div.innerHTML = '<div class="product-grid"><input type="text" name="products[]" placeholder="Наименование изделия"><input type="number" name="quantities[]" placeholder="Кол-во" value="1"><input type="text" name="prices[]" placeholder="Цена"></div><button type="button" class="remove-product" onclick="this.parentElement.remove()">?? Удалить</button>';
    container.appendChild(div);
}

function submitToPDF() {
    // Собираем данные формы
    var form = document.getElementById('mainForm');
    var formData = new FormData(form);
    
    // Создаем временную форму для отправки PDF
    var pdfForm = document.createElement('form');
    pdfForm.method = 'POST';
    pdfForm.action = 'generate_pdf.php';
    pdfForm.target = '_blank';
    
    // Копируем все поля
    for (var pair of formData.entries()) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = pair[0];
        input.value = pair[1];
        pdfForm.appendChild(input);
    }
    
    document.body.appendChild(pdfForm);
    pdfForm.submit();
    document.body.removeChild(pdfForm);
}
</script>
</body>
</html>
