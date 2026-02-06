<?php
require_once '../config.php';

try {
    $pdo = getDBConnection();
    echo "✓ Подключение к базе данных успешно!\n";
    echo "✓ База данных: " . DB_NAME . "\n";
    
    // Проверяем таблицы
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "\n✓ Найдено таблиц: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
?>
