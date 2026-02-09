<?php
require_once '../config.php';

$pdo = getDBConnection();
$message = '';

// Обработка добавления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO пользователи (имя, фамилия, email, пароль, телефон) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['имя'],
            $_POST['фамилия'],
            $_POST['email'],
            password_hash($_POST['пароль'], PASSWORD_DEFAULT),
            $_POST['телефон'] ?? null
        ]);
        $message = '<div class="message success">Пользователь успешно добавлен!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM пользователи WHERE id_пользователя = ?");
        $stmt->execute([$_GET['delete']]);
        $message = '<div class="message success">Пользователь успешно удален!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Обработка изменения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    try {
        $stmt = $pdo->prepare("UPDATE пользователи SET имя = ?, фамилия = ?, email = ?, телефон = ? WHERE id_пользователя = ?");
        $stmt->execute([
            $_POST['имя'],
            $_POST['фамилия'],
            $_POST['email'],
            $_POST['телефон'] ?? null,
            $_POST['id_пользователя']
        ]);
        $message = '<div class="message success">Пользователь успешно обновлен!</div>';
    } catch (PDOException $e) {
        $message = '<div class="message error">Ошибка: ' . $e->getMessage() . '</div>';
    }
}

// Получение данных для редактирования
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM пользователи WHERE id_пользователя = ?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch();
}

// Получение всех пользователей
$stmt = $pdo->query("SELECT * FROM пользователи ORDER BY id_пользователя DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи - Ритуальные услуги</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Ритуальные услуги</h1>
            <nav>
                <a href="../index.php">Главная</a>
                <a href="users.php" class="active">Пользователи</a>
                <a href="categories.php">Категории</a>
                <a href="products.php">Товары</a>
                <a href="orders.php">Заказы</a>
                <a href="order_items.php">Товары в заказе</a>
                <a href="search.php">Поиск</a>
            </nav>
        </header>

        <main>
            <?php echo $message; ?>

            <div class="form-section">
                <h2><?php echo $editUser ? 'Редактировать пользователя' : 'Добавить пользователя'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $editUser ? 'edit' : 'add'; ?>">
                    <?php if ($editUser): ?>
                        <input type="hidden" name="id_пользователя" value="<?php echo $editUser['id_пользователя']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Имя *</label>
                        <input type="text" name="имя" value="<?php echo $editUser['имя'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Фамилия *</label>
                        <input type="text" name="фамилия" value="<?php echo $editUser['фамилия'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?php echo $editUser['email'] ?? ''; ?>" required>
                    </div>
                    
                    <?php if (!$editUser): ?>
                    <div class="form-group">
                        <label>Пароль *</label>
                        <input type="password" name="пароль" required>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="text" name="телефон" value="<?php echo $editUser['телефон'] ?? ''; ?>">
                    </div>
                    
                    <button type="submit" class="btn-primary"><?php echo $editUser ? 'Обновить' : 'Добавить'; ?></button>
                    <?php if ($editUser): ?>
                        <a href="users.php" class="btn-edit" style="margin-left: 10px;">Отмена</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="margin: 0;">Список пользователей</h2>
                    <a href="../reports/generate_pdf.php?table=users" class="btn-primary" style="text-decoration: none; padding: 8px 15px; background: #28a745; color: white; border-radius: 4px;">📄 Экспорт в PDF</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Фамилия</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Нет данных</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id_пользователя']; ?></td>
                                    <td><?php echo htmlspecialchars($user['имя']); ?></td>
                                    <td><?php echo htmlspecialchars($user['фамилия']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['телефон'] ?? '-'); ?></td>
                                    <td><?php echo $user['дата_создания']; ?></td>
                                    <td class="actions">
                                        <a href="?edit=<?php echo $user['id_пользователя']; ?>" class="btn-edit">Изменить</a>
                                        <a href="?delete=<?php echo $user['id_пользователя']; ?>" class="btn-danger" onclick="return confirm('Вы уверены?')">Удалить</a>
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
