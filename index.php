<?php
// index.php - CRM система для мебельной компании ASTI (с БД)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

// Подключение к базе данных
$host = 'db';           // для Docker
$dbname = 'crm_asti';
$user = 'root';
$pass = 'rootpassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

$generationMessage = '';
$generationError = '';
$downloadLink = '';

// Получаем список клиентов для выпадающего списка
$clients = [];
$stmt = $pdo->query("SELECT id, name, company FROM clients ORDER BY name");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем список товаров
$productsList = [];
$stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY name");
$productsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_docx'])) {
    try {
        // Если выбран существующий клиент
        if (!empty($_POST['client_id'])) {
            $stmt = $pdo->prepare("SELECT name, company FROM clients WHERE id = ?");
            $stmt->execute([$_POST['client_id']]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            $clientName = $client['name'];
            $clientCompany = $client['company'];
        } else {
            $clientName = htmlspecialchars($_POST['client_name'] ?? '');
            $clientCompany = htmlspecialchars($_POST['client_company'] ?? '');
        }
        
        $companyName = htmlspecialchars($_POST['company_name'] ?? 'АСТИ Мебель');
        $discount = floatval($_POST['discount'] ?? 0);
        
        $products = $_POST['products'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $prices = $_POST['prices'] ?? [];
        
        if (empty($products) || empty($products[0])) {
            throw new Exception('Добавьте хотя бы один товар');
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
        $section->addText('✦ ✦ ✦', ['size' => 20, 'color' => 'bdc3c7'], ['alignment' => Jc::CENTER]);
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
        $firstName = explode(' ', $clientName)[0];
        $section->addText('Уважаемый(ая) ' . $firstName . '!', ['bold' => true, 'size' => 11, 'color' => '2c3e50']);
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
        $table->addCell(1800, ['bgColor' => '3498db'])->addText('Цена, ₽', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);
        $table->addCell(2300, ['bgColor' => '3498db'])->addText('Сумма, ₽', ['bold' => true, 'color' => 'ffffff', 'size' => 12], ['alignment' => Jc::CENTER]);
        
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
        $table->addCell(2300, ['bgColor' => 'e8f8f5'])->addText(number_format($finalTotal, 2) . ' ₽', 
            ['bold' => true, 'size' => 12, 'color' => 'e74c3c'], ['alignment' => Jc::CENTER]);
        
        $section->addTextBreak(0.8);
        
        // Преимущества
        $section->addText('⭐ НАШИ ПРЕИМУЩЕСТВА', ['bold' => true, 'size' => 12, 'color' => 'e74c3c']);
        $section->addTextBreak(0.3);
        $advText = '🏆 Лидер рынка с 2010 года        ⚡ Индивидуальный подход         🔧 Своё производство';
        $section->addText($advText, ['size' => 10, 'color' => '2c3e50']);
        $section->addTextBreak(0.9);
        
        // Условия
        $section->addText('📋 УСЛОВИЯ СОТРУДНИЧЕСТВА', ['bold' => true, 'size' => 12, 'color' => 'e74c3c']);
        $section->addTextBreak(0.5);
        $condText1 = '✅ Срок изготовления: 7-14 дней     ✅ Доставка: бесплатно по Москве     ✅ Гарантия: 12-36 месяцев';
        $section->addText($condText1, ['size' => 10, 'color' => '2c3e50']);
        $condText2 = '💰 Оплата: 50% предоплата    📅 Сборка: включена в стоимость   🛠 Материалы: ЛДСП/МДФ/массив';
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
        
        // Сохраняем в базу данных
        $stmt = $pdo->prepare("INSERT INTO proposals (client_id, proposal_number, date, total, discount) VALUES (?, ?, ?, ?, ?)");
        $clientId = !empty($_POST['client_id']) ? $_POST['client_id'] : null;
        $proposalNumber = 'АМ-' . date('Ymd') . '-' . rand(100, 999);
        $stmt->execute([$clientId, $proposalNumber, date('Y-m-d'), $finalTotal, $discount]);
        $proposalId = $pdo->lastInsertId();
        
        // Сохраняем позиции
        $stmt = $pdo->prepare("INSERT INTO proposal_items (proposal_id, product_name, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($products as $idx => $product) {
            if (!empty($product)) {
                $qty = floatval($quantities[$idx] ?? 1);
                $price = floatval($prices[$idx] ?? 0);
                $sum = $qty * $price;
                $stmt->execute([$proposalId, $product, $qty, $price, $sum]);
            }
        }
        
        $filename = 'proposals/КП_АСТИ_Мебель_' . date('Y-m-d_H-i-s') . '.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filename);
        
        $generationMessage = "✅ DOCX успешно создано! Данные сохранены в БД.";
        $downloadLink = $filename;
        
    } catch (Exception $e) {
        $generationError = "❌ Ошибка DOCX: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_pdf'])) {
    ?>
    <form id="pdfForm" method="POST" action="generate_pdf.php" target="_blank">
        <input type="hidden" name="company_name" value="<?php echo htmlspecialchars($_POST['company_name'] ?? 'АСТИ Мебель'); ?>">
        <input type="hidden" name="client_name" value="<?php echo htmlspecialchars($_POST['client_name'] ?? ''); ?>">
        <input type="hidden" name="client_company" value="<?php echo htmlspecialchars($_POST['client_company'] ?? ''); ?>">
        <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($_POST['client_id'] ?? ''); ?>">
        <input type="hidden" name="discount" value="<?php echo htmlspecialchars($_POST['discount'] ?? 0); ?>">
        <?php
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $idx => $product) {
                if (!empty($product)) {
                    echo '<input type="hidden" name="products[]" value="' . htmlspecialchars($product) . '">';
                    echo '<input type="hidden" name="quantities[]" value="' . htmlspecialchars($_POST['quantities'][$idx] ?? 1) . '">';
                    echo '<input type="hidden" name="prices[]" value="' . htmlspecialchars($_POST['prices'][$idx] ?? 0) . '">';
                }
            }
        }
        ?>
    </form>
    <script>document.getElementById('pdfForm').submit();</script>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>АСТИ Мебель - Генератор КП</title>
    <link rel="icon" type="image/png" href="logo.png">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#667eea,#764ba2);min-height:100vh;padding:30px}
        .container{max-width:1000px;margin:0 auto}
        .header{background:#fff;border-radius:20px;padding:20px 30px;margin-bottom:30px;display:flex;align-items:center;gap:20px;box-shadow:0 10px 30px rgba(0,0,0,0.1)}
        .logo{flex-shrink:0}
        .logo img{width:70px;height:70px;border-radius:12px}
        .logo-placeholder{width:70px;height:70px;background:linear-gradient(135deg,#e74c3c,#f39c12);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:32px;color:#fff}
        .header-text{flex-grow:1}
        .header-text h1{color:#2c3e50;font-size:2em;margin-bottom:5px}
        .header-text p{color:#666}
        .content{background:#fff;border-radius:20px;padding:40px;box-shadow:0 10px 30px rgba(0,0,0,0.1)}
        .form-group{margin-bottom:20px}
        label{display:block;margin-bottom:8px;font-weight:600;color:#2c3e50}
        select, input{width:100%;padding:12px 16px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;transition:0.2s}
        select:focus, input:focus{outline:none;border-color:#e74c3c;box-shadow:0 0 0 3px rgba(231,76,60,0.1)}
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
        .client-select{background:#fff;cursor:pointer}
        .or-divider{text-align:center;margin:10px 0;color:#999;font-size:12px}
        @media (max-width:768px){.product-grid,.form-row{grid-template-columns:1fr}.header{flex-direction:column;text-align:center}}
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">
            <?php if (file_exists('logo.png')): ?>
                <img src="logo.png" alt="Логотип">
            <?php else: ?>
                <div class="logo-placeholder">🪑</div>
            <?php endif; ?>
        </div>
        <div class="header-text">
            <h1>АСТИ Мебель</h1>
            <p>Производство мебели на заказ | Генератор коммерческих предложений</p>
        </div>
    </div>
    <div class="content">
        <?php if ($generationMessage): ?>
        <div class="success-message">
            🎉 <strong><?php echo $generationMessage; ?></strong>
            <?php if ($downloadLink): ?>
            <br><a href="<?php echo $downloadLink; ?>" class="download-btn" download>📥 Скачать DOCX</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($generationError): ?>
        <div class="error-message">❌ <?php echo $generationError; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="mainForm">
            <div class="form-row">
                <div class="form-group">
                    <label>Ваша компания</label>
                    <input type="text" name="company_name" required value="АСТИ Мебель">
                </div>
                <div class="form-group">
                    <label>Выбрать клиента</label>
                    <select name="client_id" class="client-select" onchange="fillClientData(this)">
                        <option value="">-- Новый клиент --</option>
                        <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($client['name']); ?>"
                                data-company="<?php echo htmlspecialchars($client['company']); ?>">
                            <?php echo htmlspecialchars($client['name']); ?> (<?php echo htmlspecialchars($client['company']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="or-divider">— или укажите нового клиента —</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>ФИО клиента</label>
                    <input type="text" name="client_name" id="client_name" placeholder="Иванов Иван Иванович">
                </div>
                <div class="form-group">
                    <label>Название организации</label>
                    <input type="text" name="client_company" id="client_company" placeholder="ООО Эльдорадо">
                </div>
            </div>
            
            <div class="form-group">
                <label>Изделия мебели</label>
                <div id="productsContainer">
                    <div class="product-item">
                        <div class="product-grid">
                            <select name="products[]" class="product-select" onchange="updatePrice(this)">
                                <option value="">-- Выберите товар --</option>
                                <?php foreach ($productsList as $product): ?>
                                <option value="<?php echo htmlspecialchars($product['name']); ?>" 
                                        data-price="<?php echo $product['price']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?> - <?php echo number_format($product['price'], 0, '', ' '); ?> ₽
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="quantities[]" placeholder="Кол-во" value="1" step="any">
                            <input type="text" name="prices[]" placeholder="Цена" class="price-input">
                        </div>
                    </div>
                </div>
                <button type="button" class="add-product" onclick="addProduct()">+ Добавить изделие</button>
            </div>
            
            <div class="form-group">
                <label>Скидка (%)</label>
                <input type="number" name="discount" step="any" min="0" max="100" value="0">
            </div>
            
            <div class="button-group">
                <button type="submit" name="generate_docx" class="btn-docx" onclick="this.form.action=''; this.form.target='';">Создать DOCX</button>
                <button type="button" class="btn-pdf" onclick="submitToPDF()">Создать PDF</button>
            </div>
        </form>
    </div>
</div>

<script>
function fillClientData(select) {
    const option = select.options[select.selectedIndex];
    if (option.value) {
        document.getElementById('client_name').value = option.getAttribute('data-name') || '';
        document.getElementById('client_company').value = option.getAttribute('data-company') || '';
    } else {
        document.getElementById('client_name').value = '';
        document.getElementById('client_company').value = '';
    }
}

function updatePrice(select) {
    const option = select.options[select.selectedIndex];
    const price = option.getAttribute('data-price') || 0;
    const priceInput = select.closest('.product-grid').querySelector('.price-input');
    if (priceInput) {
        priceInput.value = price;
    }
}

function addProduct() {
    const container = document.getElementById('productsContainer');
    const div = document.createElement('div');
    div.className = 'product-item';
    div.innerHTML = `
        <div class="product-grid">
            <select name="products[]" class="product-select" onchange="updatePrice(this)">
                <option value="">-- Выберите товар --</option>
                <?php foreach ($productsList as $product): ?>
                <option value="<?php echo htmlspecialchars($product['name']); ?>" 
                        data-price="<?php echo $product['price']; ?>">
                    <?php echo htmlspecialchars($product['name']); ?> - <?php echo number_format($product['price'], 0, '', ' '); ?> ₽
                </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="quantities[]" placeholder="Кол-во" value="1" step="any">
            <input type="text" name="prices[]" placeholder="Цена" class="price-input">
        </div>
        <button type="button" class="remove-product" onclick="this.parentElement.remove()">Удалить</button>
    `;
    container.appendChild(div);
}

function submitToPDF() {
    var form = document.getElementById('mainForm');
    var formData = new FormData(form);
    var pdfForm = document.createElement('form');
    pdfForm.method = 'POST';
    pdfForm.action = 'generate_pdf.php';
    pdfForm.target = '_blank';
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