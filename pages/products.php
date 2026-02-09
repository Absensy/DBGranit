<?php
require_once '../config.php';

$pdo = getDBConnection();
$message = '';

// Получение категорий для выпадающего списка
$stmt = $pdo->query("SELECT * FROM категории_товаров ORDER BY название");
$categories = $stmt->fetchAll();

// Обработка добавления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO товары (название, описание, цена, количество_на_складе, id_категории, оплата_онлайн) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['название'],
            $_POST['описание'] ?? null,
            $_POST['цена'],
            $_POST['количество_на_складе'] ?? 0,
            $_POST['id_категории'] ?: null,
            isset($_POST['оплата_онлайн']) ? 1 : 0
        ]);
        $message = '<div class="message success">Товар успешно добавлен!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM товары WHERE id_товара = ?");
        $stmt->execute([$_GET['delete']]);
        $message = '<div class="message success">Товар успешно удален!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка изменения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    try {
        $stmt = $pdo->prepare("UPDATE товары SET название = ?, описание = ?, цена = ?, количество_на_складе = ?, id_категории = ?, оплата_онлайн = ? WHERE id_товара = ?");
        $stmt->execute([
            $_POST['название'],
            $_POST['описание'] ?? null,
            $_POST['цена'],
            $_POST['количество_на_складе'] ?? 0,
            $_POST['id_категории'] ?: null,
            isset($_POST['оплата_онлайн']) ? 1 : 0,
            $_POST['id_товара']
        ]);
        $message = '<div class="message success">Товар успешно обновлен!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Получение данных для редактирования
$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM товары WHERE id_товара = ?");
    $stmt->execute([$_GET['edit']]);
    $editProduct = $stmt->fetch();
}

// Получение всех товаров с категориями
$stmt = $pdo->query("
    SELECT т.*, к.название as категория_название 
    FROM товары т 
    LEFT JOIN категории_товаров к ON т.id_категории = к.id_категории 
    ORDER BY т.id_товара DESC
");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товары - Ритуальные услуги</title>
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
                <a href="products.php" class="active">Товары</a>
                <a href="orders.php">Заказы</a>
                <a href="order_items.php">Товары в заказе</a>
                <a href="search.php">Поиск</a>
            </nav>
        </header>

        <main>
            <?php echo $message; ?>

            <div class="form-section">
                <h2><?php echo $editProduct ? 'Редактировать товар' : 'Добавить товар'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
                    <?php if ($editProduct): ?>
                        <input type="hidden" name="id_товара" value="<?php echo $editProduct['id_товара']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Название *</label>
                        <input type="text" name="название" value="<?php echo $editProduct['название'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea name="описание"><?php echo $editProduct['описание'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Цена *</label>
                        <input type="number" name="цена" step="0.01" min="0" value="<?php echo $editProduct['цена'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Количество на складе</label>
                        <input type="number" name="количество_на_складе" min="0" value="<?php echo $editProduct['количество_на_складе'] ?? 0; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Категория</label>
                        <select name="id_категории">
                            <option value="">-- Без категории --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id_категории']; ?>" 
                                    <?php echo ($editProduct && $editProduct['id_категории'] == $category['id_категории']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['название']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="оплата_онлайн" value="1" 
                                <?php echo ($editProduct && $editProduct['оплата_онлайн']) ? 'checked' : 'checked'; ?>>
                            Оплата онлайн
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-primary"><?php echo $editProduct ? 'Обновить' : 'Добавить'; ?></button>
                    <?php if ($editProduct): ?>
                        <a href="products.php" class="btn-edit" style="margin-left: 10px;">Отмена</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="margin: 0;">Список товаров</h2>
                    <a href="../reports/generate_pdf.php?table=products" class="btn-primary" style="text-decoration: none; padding: 8px 15px; background: #28a745; color: white; border-radius: 4px;">📄 Экспорт в PDF</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Цена</th>
                            <th>Количество</th>
                            <th>Категория</th>
                            <th>Онлайн оплата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Нет данных</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id_товара']; ?></td>
                                    <td><?php echo htmlspecialchars($product['название']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($product['описание'] ?? '-', 0, 50)) . (strlen($product['описание'] ?? '') > 50 ? '...' : ''); ?></td>
                                    <td><?php echo number_format($product['цена'], 2); ?> ₽</td>
                                    <td><?php echo $product['количество_на_складе']; ?></td>
                                    <td><?php echo htmlspecialchars($product['категория_название'] ?? '-'); ?></td>
                                    <td><?php echo $product['оплата_онлайн'] ? 'Да' : 'Нет'; ?></td>
                                    <td class="actions">
                                        <a href="?edit=<?php echo $product['id_товара']; ?>" class="btn-edit">Изменить</a>
                                        <a href="?delete=<?php echo $product['id_товара']; ?>" class="btn-danger" onclick="return confirm('Вы уверены?')">Удалить</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
