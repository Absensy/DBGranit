<?php
require_once '../config.php';

$pdo = getDBConnection();
$message = '';

// Получение заказов для выпадающего списка
$stmt = $pdo->query("SELECT * FROM заказы ORDER BY id_заказа DESC");
$orders = $stmt->fetchAll();

// Получение товаров для выпадающего списка
$stmt = $pdo->query("SELECT * FROM товары ORDER BY название");
$products = $stmt->fetchAll();

// Обработка добавления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO товары_в_заказе (id_заказа, id_товара, количество, цена_на_момент_покупки) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['id_заказа'],
            $_POST['id_товара'],
            $_POST['количество'],
            $_POST['цена_на_момент_покупки']
        ]);
        $message = '<div class="message success">Товар в заказе успешно добавлен!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM товары_в_заказе WHERE id_товара_в_заказе = ?");
        $stmt->execute([$_GET['delete']]);
        $message = '<div class="message success">Товар в заказе успешно удален!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка изменения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    try {
        $stmt = $pdo->prepare("UPDATE товары_в_заказе SET id_заказа = ?, id_товара = ?, количество = ?, цена_на_момент_покупки = ? WHERE id_товара_в_заказе = ?");
        $stmt->execute([
            $_POST['id_заказа'],
            $_POST['id_товара'],
            $_POST['количество'],
            $_POST['цена_на_момент_покупки'],
            $_POST['id_товара_в_заказе']
        ]);
        $message = '<div class="message success">Товар в заказе успешно обновлен!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Получение данных для редактирования
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM товары_в_заказе WHERE id_товара_в_заказе = ?");
    $stmt->execute([$_GET['edit']]);
    $editItem = $stmt->fetch();
}

// Получение всех товаров в заказах с дополнительной информацией
$stmt = $pdo->query("
    SELECT 
        твз.*,
        з.id_заказа as заказ_id,
        т.название as товар_название,
        т.цена as текущая_цена
    FROM товары_в_заказе твз
    LEFT JOIN заказы з ON твз.id_заказа = з.id_заказа
    LEFT JOIN товары т ON твз.id_товара = т.id_товара
    ORDER BY твз.id_товара_в_заказе DESC
");
$orderItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товары в заказе - Ритуальные услуги</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Ритуальные услуги</h1>
            <nav>
                <a href="../index.php">Главная</a>
                <a href="users.php">Пользователи</a>
                <a href="categories.php">Категории</a>
                <a href="products.php">Товары</a>
                <a href="orders.php">Заказы</a>
                <a href="order_items.php" class="active">Товары в заказе</a>
                <a href="search.php">Поиск</a>
            </nav>
        </header>

        <main>
            <?php echo $message; ?>

            <div class="form-section">
                <h2><?php echo $editItem ? 'Редактировать товар в заказе' : 'Добавить товар в заказ'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $editItem ? 'edit' : 'add'; ?>">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="id_товара_в_заказе" value="<?php echo $editItem['id_товара_в_заказе']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Заказ *</label>
                        <select name="id_заказа" required>
                            <?php foreach ($orders as $order): ?>
                                <option value="<?php echo $order['id_заказа']; ?>" 
                                    <?php echo ($editItem && $editItem['id_заказа'] == $order['id_заказа']) ? 'selected' : ''; ?>>
                                    Заказ #<?php echo $order['id_заказа']; ?> (<?php echo $order['дата_заказа']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Товар *</label>
                        <select name="id_товара" required id="productSelect" onchange="updatePrice()">
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id_товара']; ?>" 
                                    data-price="<?php echo $product['цена']; ?>"
                                    <?php echo ($editItem && $editItem['id_товара'] == $product['id_товара']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($product['название']); ?> (<?php echo number_format($product['цена'], 2); ?> ₽)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Количество *</label>
                        <input type="number" name="количество" min="1" value="<?php echo $editItem['количество'] ?? 1; ?>" required onchange="calculateTotal()">
                    </div>
                    
                    <div class="form-group">
                        <label>Цена на момент покупки *</label>
                        <input type="number" name="цена_на_момент_покупки" step="0.01" min="0" id="priceInput" value="<?php echo $editItem['цена_на_момент_покупки'] ?? ''; ?>" required>
                    </div>
                    
                    <button type="submit" class="btn-primary"><?php echo $editItem ? 'Обновить' : 'Добавить'; ?></button>
                    <?php if ($editItem): ?>
                        <a href="order_items.php" class="btn-edit" style="margin-left: 10px;">Отмена</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-section">
                <h2>Список товаров в заказах</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Заказ</th>
                            <th>Товар</th>
                            <th>Количество</th>
                            <th>Цена покупки</th>
                            <th>Текущая цена</th>
                            <th>Итого</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orderItems)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Нет данных</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td><?php echo $item['id_товара_в_заказе']; ?></td>
                                    <td>Заказ #<?php echo $item['id_заказа']; ?></td>
                                    <td><?php echo htmlspecialchars($item['товар_название'] ?? '-'); ?></td>
                                    <td><?php echo $item['количество']; ?></td>
                                    <td><?php echo number_format($item['цена_на_момент_покупки'], 2); ?> ₽</td>
                                    <td><?php echo $item['текущая_цена'] ? number_format($item['текущая_цена'], 2) . ' ₽' : '-'; ?></td>
                                    <td><?php echo number_format($item['количество'] * $item['цена_на_момент_покупки'], 2); ?> ₽</td>
                                    <td class="actions">
                                        <a href="?edit=<?php echo $item['id_товара_в_заказе']; ?>" class="btn-edit">Изменить</a>
                                        <a href="?delete=<?php echo $item['id_товара_в_заказе']; ?>" class="btn-danger" onclick="return confirm('Вы уверены?')">Удалить</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function updatePrice() {
            const select = document.getElementById('productSelect');
            const priceInput = document.getElementById('priceInput');
            const selectedOption = select.options[select.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            if (price && !priceInput.value) {
                priceInput.value = price;
            }
        }

        function calculateTotal() {
            // Можно добавить расчет общей суммы, если нужно
        }

        // Инициализация при загрузке страницы
        if (document.getElementById('productSelect') && !document.getElementById('priceInput').value) {
            updatePrice();
        }
    </script>
</body>
</html>
