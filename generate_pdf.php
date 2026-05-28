<?php
// generate_pdf.php - PDF генератор для АСТИ Мебель
require_once __DIR__ . '/vendor/autoload.php';

$companyName = $_POST['company_name'] ?? 'АСТИ Мебель';
$clientName = $_POST['client_name'] ?? 'Иванов Иван Иванович';
$clientCompany = $_POST['client_company'] ?? 'ООО Эльдорадо';
$discount = floatval($_POST['discount'] ?? 0);

$products = $_POST['products'] ?? [];
$quantities = $_POST['quantities'] ?? [];
$prices = $_POST['prices'] ?? [];

if (empty($products) || empty($products[0])) {
    $products = ['Кухонный гарнитур'];
    $quantities = [1];
    $prices = [150000];
}

if (!is_dir('proposals')) {
    mkdir('proposals', 0777, true);
}

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('АСТИ Мебель');
$pdf->SetAuthor('АСТИ Мебель');
$pdf->SetTitle('Коммерческое предложение');
$pdf->SetSubject('КП от АСТИ Мебель');

$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 9);

// Логотип
$logoHtml = '';
if (file_exists(__DIR__ . '/logo.png')) {
    $logoData = base64_encode(file_get_contents(__DIR__ . '/logo.png'));
    $logoHtml = '<div style="text-align: center; margin-bottom: 5px;">
                    <img src="data:image/png;base64,' . $logoData . '" width="50" height="50">
                 </div>';
}

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>
<body>
' . $logoHtml . '

<h2 style="text-align: center; margin: 0 0 3px 0; font-size: 16pt;">' . $companyName . '</h2>
<h4 style="text-align: center; color: #e74c3c; margin: 0 0 3px 0; font-size: 10pt;">ПРОИЗВОДСТВО МЕБЕЛИ НА ЗАКАЗ</h4>
<p style="text-align: center; font-size: 7pt; margin: 0 0 2px 0;">ИНН: 1234567890 | ОГРН: 1234567890123 | тел: +7 (495) 123-45-67 | email: info@asti-furniture.ru</p>
<p style="text-align: center; font-size: 10pt; margin: 3px 0;">* * *</p>
<h3 style="text-align: center; margin: 3px 0 5px 0; font-size: 14pt;">КОММЕРЧЕСКОЕ ПРЕДЛОЖЕНИЕ</h3>

<table width="100%" style="font-size: 9pt; margin: 5px 0;">
    <tr>
        <td width="15%"><strong>Кому:</strong></td>
        <td width="35%">' . htmlspecialchars($clientName) . '</td>
        <td width="15%"><strong>От кого:</strong></td>
        <td width="35%">' . htmlspecialchars($companyName) . '</td>
    </tr>
    <tr>
        <td width="15%"> </td>
        <td width="35%">' . htmlspecialchars($clientCompany) . '</td>
        <td width="15%"> </td>
        <td width="35%"> </td>
    </tr>
    <tr>
        <td width="15%"><strong>Дата:</strong></td>
        <td width="35%">' . date('d.m.Y') . '</td>
        <td width="15%"><strong>№ КП:</strong></td>
        <td width="35%">АМ-' . date('Ymd') . '-' . rand(100, 999) . '</td>
    </tr>
</table>

<p style="font-size: 9pt; margin: 5px 0;"><strong>Уважаемый ' . htmlspecialchars($clientName) . '!</strong></p>
<p style="font-size: 9pt; margin: 3px 0;">Благодарим Вас за обращение в компанию "АСТИ Мебель". Направляем коммерческое предложение:</p>

<table border="1" cellpadding="4" style="border-collapse: collapse; width: 100%; font-size: 8pt;">
<tr style="background-color: #3498db; color: white;">
    <th width="5%">№</th>
    <th width="55%">Наименование</th>
    <th width="10%">Кол-во</th>
    <th width="15%">Цена</th>
    <th width="15%">Сумма</th>
</tr>';

$total = 0;
foreach ($products as $idx => $product) {
    if (!empty($product)) {
        $qty = floatval($quantities[$idx] ?? 1);
        $price = floatval($prices[$idx] ?? 0);
        $sum = $qty * $price;
        $total += $sum;
        $html .= '
<tr>
    <td align="center">' . ($idx + 1) . '</td>
    <td>' . htmlspecialchars($product) . '</td>
    <td align="center">' . $qty . '</td>
    <td align="right">' . number_format($price, 2) . ' ₽</td>
    <td align="right">' . number_format($sum, 2) . ' ₽</td>
</tr>';
    }
}

$finalTotal = $total;
if ($discount > 0) {
    $discountAmount = $total * ($discount / 100);
    $finalTotal = $total - $discountAmount;
    $html .= '
<tr style="background-color: #f8f9fa;">
    <td colspan="4" align="left"><strong>Подытог:</strong></td>
    <td align="right"><strong>' . number_format($total, 2) . ' ₽</strong></td>
</tr>
<tr style="background-color: #f8f9fa;">
    <td colspan="4" align="left"><strong style="color: #e74c3c;">Скидка ' . $discount . '%:</strong></td>
    <td align="right" style="color: #e74c3c;"><strong>- ' . number_format($discountAmount, 2) . ' ₽</strong></td>
</tr>';
}

$html .= '
<tr style="background-color: #e8f8f5;">
    <td colspan="4" align="left"><strong>ИТОГО К ОПЛАТЕ:</strong></td>
    <td align="right"><strong style="color: #e74c3c; font-size: 11pt;">' . number_format($finalTotal, 2) . ' ₽</strong></td>
</td>
</table>

<p style="font-size: 8pt; margin: 8px 0 3px 0;"><strong>⭐ НАШИ ПРЕИМУЩЕСТВА</strong></p>
<p style="font-size: 7pt; margin: 2px 0;">🏆 Лидер рынка с 2010 года | ⚡ Индивидуальный подход | 🔧 Своё производство</p>

<p style="font-size: 8pt; margin: 8px 0 3px 0;"><strong>📋 УСЛОВИЯ СОТРУДНИЧЕСТВА</strong></p>
<p style="font-size: 7pt; margin: 2px 0;">✅ Срок изготовления: 7-14 дней | ✅ Доставка: бесплатно по Москве | ✅ Гарантия: 12-36 месяцев</p>
<p style="font-size: 7pt; margin: 2px 0;">💰 Оплата: 50% предоплата | 📅 Сборка: включена в стоимость | 🛠 Материалы: ЛДСП/МДФ/массив</p>

<!-- Отступ 3 см перед подписями (используем margin-top) -->
<div style="margin-top: 85px;"></div>

<table width="100%">
    <tr>
        <td width="50%"><strong>Генеральный директор:</strong></td>
        <td width="50%"><strong>Клиент:</strong></td>
    </tr>
    <tr>
        <td>____________________________ /Иванов И.И./</td>
        <td>_________________________ /______________/</td>
    </tr>
</table>

</body>
</html>';

$pdf->writeHTML($html, true, false, true, false, '');
$filename = 'proposals/КП_АСТИ_Мебель_' . date('Y-m-d_H-i-s') . '.pdf';
$pdf->Output(__DIR__ . '/' . $filename, 'F');

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Content-Length: ' . filesize($filename));
readfile($filename);
exit;
?>
