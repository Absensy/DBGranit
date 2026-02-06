<?php
// Скрипт для создания базы данных
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Подключаемся к MySQL без указания базы данных
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Читаем SQL файл
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Выполняем SQL скрипт
    $pdo->exec($sql);
    
    echo "База данных 'ритуальные_услуги' успешно создана!\n";
    echo "Все таблицы созданы.\n";
    
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
?>
