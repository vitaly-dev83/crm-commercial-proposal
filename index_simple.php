<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>АСТИ Мебель - Генератор КП</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        input, button { padding: 10px; margin: 5px; }
        .product-item { border: 1px solid #ccc; padding: 10px; margin: 10px 0; }
        button { background: #e74c3c; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <h1>?? АСТИ Мебель</h1>
    <form method="POST" action="generate_pdf.php" target="_blank" id="pdfForm">
        <label>Ваша компания:</label>
        <input type="text" name="company_name" value="АСТИ Мебель" required><br>
        
        <label>ФИО клиента:</label>
        <input type="text" name="client_name" value="Иванов Иван Иванович" required><br>
        
        <label>Компания клиента:</label>
        <input type="text" name="client_company" value="ООО Эльдорадо"><br>
        
        <div id="productsContainer">
            <div class="product-item">
                <input type="text" name="products[]" placeholder="Товар" value="Кухонный гарнитур">
                <input type="number" name="quantities[]" placeholder="Кол-во" value="1">
                <input type="text" name="prices[]" placeholder="Цена" value="150000">
            </div>
        </div>
        
        <button type="button" onclick="addProduct()">+ Добавить товар</button><br><br>
        
        <label>Скидка (%):</label>
        <input type="number" name="discount" value="10"><br><br>
        
        <button type="submit">?? Создать PDF</button>
    </form>
</div>

<script>
function addProduct() {
    const container = document.getElementById('productsContainer');
    const div = document.createElement('div');
    div.className = 'product-item';
    div.innerHTML = '<input type="text" name="products[]" placeholder="Товар" value="Новый товар">' +
                    '<input type="number" name="quantities[]" placeholder="Кол-во" value="1">' +
                    '<input type="text" name="prices[]" placeholder="Цена" value="50000">' +
                    '<button type="button" onclick="this.parentElement.remove()">Удалить</button>';
    container.appendChild(div);
}
</script>
</body>
</html>
