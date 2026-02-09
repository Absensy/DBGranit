<?php
// Скрипт для заполнения базы данных mock данными
require_once '../config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заполнение базы данных</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Заполнение базы данных</h1>
        <nav>
            <a href="../index.php">Главная</a>
            <a href="../pages/users.php">Пользователи</a>
            <a href="../pages/categories.php">Категории</a>
            <a href="../pages/products.php">Товары</a>
            <a href="../pages/orders.php">Заказы</a>
            <a href="../pages/order_items.php">Товары в заказе</a>
            <a href="../pages/search.php">Поиск</a>
        </nav>
    </header>

    <main>
        <div class="form-section">
            <h2>Заполнение базы данных тестовыми данными</h2>
            <p>Нажмите кнопку ниже, чтобы заполнить базу данных mock данными (пользователи, категории, товары, заказы).</p>
            
            <?php if (!isset($_GET['execute'])): ?>
                <form method="GET" action="">
                    <input type="hidden" name="execute" value="1">
                    <button type="submit" class="btn-primary" style="margin-top: 20px;">
                        Заполнить базу данных
                    </button>
                </form>
            <?php else: ?>
                <div style="margin-top: 20px;">
                    <a href="fill_database.php" class="btn-primary" style="display: inline-block; margin-bottom: 20px;">
                        ← Вернуться назад
                    </a>
                </div>
                <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 20px;">
<?php
try {
    $pdo = getDBConnection();
    
    echo "Начинаем заполнение базы данных...\n\n";
    
    // Читаем SQL файл
    $sql = file_get_contents(__DIR__ . '/../database/mock_data.sql');
    
    if ($sql === false) {
        die("Ошибка: Не удалось прочитать файл mock_data.sql\n");
    }
    
    // Удаляем USE команду и комментарии
    $sql = preg_replace('/USE\s+`[^`]+`;\s*/i', '', $sql);
    $sql = preg_replace('/--.*$/m', '', $sql); // Удаляем комментарии
    
    // Разбиваем на запросы по точке с запятой
    // Используем более надежный метод - разбиваем по ; и собираем многострочные запросы
    $parts = preg_split('/;\s*(?=\n|$)/', $sql);
    $queries = [];
    
    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part)) {
            continue;
        }
        
        // Пропускаем SET FOREIGN_KEY_CHECKS и TRUNCATE
        if (preg_match('/^(SET FOREIGN_KEY_CHECKS|TRUNCATE)/i', $part)) {
            continue;
        }
        
        // Оставляем только INSERT запросы
        if (preg_match('/^INSERT/i', $part)) {
            $queries[] = $part;
        }
    }
    
    echo "Найдено запросов: " . count($queries) . "\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($queries as $index => $query) {
        $query = trim($query);
        if (empty($query)) {
            continue;
        }
        
        try {
            $result = $pdo->exec($query);
            if ($result !== false) {
                $successCount++;
                if ($index < 3) {
                    echo "✓ Выполнен запрос #" . ($index + 1) . " (затронуто строк: $result)\n";
                }
            }
        } catch (PDOException $e) {
            // Игнорируем ошибки дублирования (если данные уже есть)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                // Тихо игнорируем дубликаты
                $successCount++; // Считаем как успех, т.к. данные уже есть
            } else {
                echo "✗ Ошибка в запросе #" . ($index + 1) . ": " . htmlspecialchars(substr($e->getMessage(), 0, 150)) . "\n";
                $errorCount++;
            }
        }
    }
    
    echo "\n✓ Заполнение завершено!\n";
    echo "Успешно выполнено запросов: $successCount\n";
    if ($errorCount > 0) {
        echo "Ошибок: $errorCount\n";
    }
    
    // Показываем статистику
    echo "\n--- Статистика ---\n";
    
    $tables = [
        'пользователи' => 'SELECT COUNT(*) as count FROM `пользователи`',
        'категории_товаров' => 'SELECT COUNT(*) as count FROM `категории_товаров`',
        'товары' => 'SELECT COUNT(*) as count FROM `товары`',
        'заказы' => 'SELECT COUNT(*) as count FROM `заказы`',
        'товары_в_заказе' => 'SELECT COUNT(*) as count FROM `товары_в_заказе`'
    ];
    
    foreach ($tables as $tableName => $query) {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "$tableName: {$result['count']} записей\n";
    }
    
} catch (PDOException $e) {
    echo "Ошибка: " . htmlspecialchars($e->getMessage()) . "\n";
    exit(1);
}
?>
                </pre>
                <div style="margin-top: 20px;">
                    <a href="../index.php" class="btn-primary">← Вернуться на главную</a>
                    <a href="fill_database.php" class="btn-primary" style="margin-left: 10px;">Заполнить снова</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
