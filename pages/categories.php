<?php
require_once '../config.php';

$pdo = getDBConnection();
$message = '';

// Обработка добавления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO категории_товаров (название, описание) VALUES (?, ?)");
        $stmt->execute([
            $_POST['название'],
            $_POST['описание'] ?? null
        ]);
        $message = '<div class="message success">Категория успешно добавлена!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM категории_товаров WHERE id_категории = ?");
        $stmt->execute([$_GET['delete']]);
        $message = '<div class="message success">Категория успешно удалена!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка изменения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    try {
        $stmt = $pdo->prepare("UPDATE категории_товаров SET название = ?, описание = ? WHERE id_категории = ?");
        $stmt->execute([
            $_POST['название'],
            $_POST['описание'] ?? null,
            $_POST['id_категории']
        ]);
        $message = '<div class="message success">Категория успешно обновлена!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Получение данных для редактирования
$editCategory = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM категории_товаров WHERE id_категории = ?");
    $stmt->execute([$_GET['edit']]);
    $editCategory = $stmt->fetch();
}

// Получение всех категорий
$stmt = $pdo->query("SELECT * FROM категории_товаров ORDER BY id_категории DESC");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Категории - Ритуальные услуги</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Ритуальные услуги</h1>
            <nav>
                <a href="../index.php">Главная</a>
                <a href="users.php">Пользователи</a>
                <a href="categories.php" class="active">Категории</a>
                <a href="products.php">Товары</a>
                <a href="orders.php">Заказы</a>
                <a href="order_items.php">Товары в заказе</a>
                <a href="search.php">Поиск</a>
            </nav>
        </header>

        <main>
            <?php echo $message; ?>

            <div class="form-section">
                <h2><?php echo $editCategory ? 'Редактировать категорию' : 'Добавить категорию'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $editCategory ? 'edit' : 'add'; ?>">
                    <?php if ($editCategory): ?>
                        <input type="hidden" name="id_категории" value="<?php echo $editCategory['id_категории']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Название *</label>
                        <input type="text" name="название" value="<?php echo $editCategory['название'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea name="описание"><?php echo $editCategory['описание'] ?? ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary"><?php echo $editCategory ? 'Обновить' : 'Добавить'; ?></button>
                    <?php if ($editCategory): ?>
                        <a href="categories.php" class="btn-edit" style="margin-left: 10px;">Отмена</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="margin: 0;">Список категорий</h2>
                    <a href="../reports/generate_pdf.php?table=categories" class="btn-primary" style="text-decoration: none; padding: 8px 15px; background: #28a745; color: white; border-radius: 4px;">📄 Экспорт в PDF</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Нет данных</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id_категории']; ?></td>
                                    <td><?php echo htmlspecialchars($category['название']); ?></td>
                                    <td><?php echo htmlspecialchars($category['описание'] ?? '-'); ?></td>
                                    <td class="actions">
                                        <a href="?edit=<?php echo $category['id_категории']; ?>" class="btn-edit">Изменить</a>
                                        <a href="?delete=<?php echo $category['id_категории']; ?>" class="btn-danger" onclick="return confirm('Вы уверены?')">Удалить</a>
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
