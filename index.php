<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ритуальные услуги - Главная</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Ритуальные услуги</h1>
            <nav>
                <a href="index.php" class="active">Главная</a>
                <a href="pages/users.php">Пользователи</a>
                <a href="pages/categories.php">Категории</a>
                <a href="pages/products.php">Товары</a>
                <a href="pages/orders.php">Заказы</a>
                <a href="pages/order_items.php">Товары в заказе</a>
                <a href="pages/search.php">Поиск</a>
            </nav>
        </header>

        <main>
            <div class="welcome-section">
                <h2>Добро пожаловать в систему управления ритуальными услугами</h2>
                <p>Выберите раздел в меню для работы с данными:</p>
                
                <div class="cards">
                    <div class="card">
                        <h3>👥 Пользователи</h3>
                        <p>Управление пользователями системы</p>
                        <a href="pages/users.php" class="btn">Перейти</a>
                    </div>
                    
                    <div class="card">
                        <h3>📦 Категории</h3>
                        <p>Управление категориями товаров</p>
                        <a href="pages/categories.php" class="btn">Перейти</a>
                    </div>
                    
                    <div class="card">
                        <h3>🛍️ Товары</h3>
                        <p>Управление товарами и услугами</p>
                        <a href="pages/products.php" class="btn">Перейти</a>
                    </div>
                    
                    <div class="card">
                        <h3>📋 Заказы</h3>
                        <p>Управление заказами</p>
                        <a href="pages/orders.php" class="btn">Перейти</a>
                    </div>
                    
                    <div class="card">
                        <h3>🛒 Товары в заказе</h3>
                        <p>Управление товарами в заказах</p>
                        <a href="pages/order_items.php" class="btn">Перейти</a>
                    </div>
                    
                    <div class="card">
                        <h3>🔍 Поиск</h3>
                        <p>Поиск по всем данным</p>
                        <a href="pages/search.php" class="btn">Перейти</a>
                    </div>
                    
                    <div class="card">
                        <h3>📊 Заполнение БД</h3>
                        <p>Заполнить базу данных тестовыми данными</p>
                        <a href="scripts/fill_database.php" class="btn">Перейти</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
