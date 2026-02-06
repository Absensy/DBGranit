<?php
require_once '../config.php';

$pdo = getDBConnection();
$message = '';

// Получение пользователей для выпадающего списка
$stmt = $pdo->query("SELECT * FROM пользователи ORDER BY фамилия, имя");
$users = $stmt->fetchAll();

// Обработка добавления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO заказы (id_пользователя, статус, общая_сумма, способ_оплаты) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['id_пользователя'] ?: null,
            $_POST['статус'],
            $_POST['общая_сумма'] ?: null,
            $_POST['способ_оплаты']
        ]);
        $message = '<div class="message success">Заказ успешно добавлен!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM заказы WHERE id_заказа = ?");
        $stmt->execute([$_GET['delete']]);
        $message = '<div class="message success">Заказ успешно удален!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка изменения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    try {
        $stmt = $pdo->prepare("UPDATE заказы SET id_пользователя = ?, статус = ?, общая_сумма = ?, способ_оплаты = ? WHERE id_заказа = ?");
        $stmt->execute([
            $_POST['id_пользователя'] ?: null,
            $_POST['статус'],
            $_POST['общая_сумма'] ?: null,
            $_POST['способ_оплаты'],
            $_POST['id_заказа']
        ]);
        $message = '<div class="message success">Заказ успешно обновлен!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Получение данных для редактирования
$editOrder = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM заказы WHERE id_заказа = ?");
    $stmt->execute([$_GET['edit']]);
    $editOrder = $stmt->fetch();
}

// Получение всех заказов с пользователями
$stmt = $pdo->query("
    SELECT з.*, п.имя, п.фамилия, п.email 
    FROM заказы з 
    LEFT JOIN пользователи п ON з.id_пользователя = п.id_пользователя 
    ORDER BY з.id_заказа DESC
");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказы - Ритуальные услуги</title>
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
                <a href="orders.php" class="active">Заказы</a>
                <a href="order_items.php">Товары в заказе</a>
                <a href="search.php">Поиск</a>
            </nav>
        </header>

        <main>
            <?php echo $message; ?>

            <div class="form-section">
                <h2><?php echo $editOrder ? 'Редактировать заказ' : 'Добавить заказ'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $editOrder ? 'edit' : 'add'; ?>">
                    <?php if ($editOrder): ?>
                        <input type="hidden" name="id_заказа" value="<?php echo $editOrder['id_заказа']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Пользователь</label>
                        <select name="id_пользователя">
                            <option value="">-- Без пользователя --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id_пользователя']; ?>" 
                                    <?php echo ($editOrder && $editOrder['id_пользователя'] == $user['id_пользователя']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['фамилия'] . ' ' . $user['имя'] . ' (' . $user['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Статус *</label>
                        <select name="статус" required>
                            <option value="в_обработке" <?php echo ($editOrder && $editOrder['статус'] == 'в_обработке') ? 'selected' : ''; ?>>В обработке</option>
                            <option value="оплачен" <?php echo ($editOrder && $editOrder['статус'] == 'оплачен') ? 'selected' : ''; ?>>Оплачен</option>
                            <option value="отправлен" <?php echo ($editOrder && $editOrder['статус'] == 'отправлен') ? 'selected' : ''; ?>>Отправлен</option>
                            <option value="выполнен" <?php echo ($editOrder && $editOrder['статус'] == 'выполнен') ? 'selected' : ''; ?>>Выполнен</option>
                            <option value="офлайн" <?php echo ($editOrder && $editOrder['статус'] == 'офлайн') ? 'selected' : ''; ?>>Офлайн</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Общая сумма</label>
                        <input type="number" name="общая_сумма" step="0.01" min="0" value="<?php echo $editOrder['общая_сумма'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Способ оплаты *</label>
                        <select name="способ_оплаты" required>
                            <option value="онлайн" <?php echo ($editOrder && $editOrder['способ_оплаты'] == 'онлайн') ? 'selected' : ''; ?>>Онлайн</option>
                            <option value="офлайн" <?php echo ($editOrder && $editOrder['способ_оплаты'] == 'офлайн') ? 'selected' : ''; ?>>Офлайн</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary"><?php echo $editOrder ? 'Обновить' : 'Добавить'; ?></button>
                    <?php if ($editOrder): ?>
                        <a href="orders.php" class="btn-edit" style="margin-left: 10px;">Отмена</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-section">
                <h2>Список заказов</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Дата заказа</th>
                            <th>Статус</th>
                            <th>Сумма</th>
                            <th>Способ оплаты</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Нет данных</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['id_заказа']; ?></td>
                                    <td><?php echo $order['имя'] ? htmlspecialchars($order['фамилия'] . ' ' . $order['имя']) : '-'; ?></td>
                                    <td><?php echo $order['дата_заказа']; ?></td>
                                    <td><?php echo htmlspecialchars($order['статус']); ?></td>
                                    <td><?php echo $order['общая_сумма'] ? number_format($order['общая_сумма'], 2) . ' ₽' : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($order['способ_оплаты']); ?></td>
                                    <td class="actions">
                                        <a href="?edit=<?php echo $order['id_заказа']; ?>" class="btn-edit">Изменить</a>
                                        <a href="?delete=<?php echo $order['id_заказа']; ?>" class="btn-danger" onclick="return confirm('Вы уверены?')">Удалить</a>
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
