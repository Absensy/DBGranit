<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройка базы данных</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Настройка базы данных</h1>
        
        <?php
        $step = $_GET['step'] ?? 'form';
        $host = $_POST['host'] ?? 'localhost';
        $user = $_POST['user'] ?? 'root';
        $pass = $_POST['pass'] ?? '';
        $dbname = 'ритуальные_услуги';
        
        if ($step === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            echo '<div class="info">Попытка подключения к MySQL...</div>';
            
            try {
                // Подключаемся к MySQL
                $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo '<div class="success">✓ Подключение к MySQL успешно!</div>';
                
                // Создаём базу данных
                echo '<div class="info">Создание базы данных "' . htmlspecialchars($dbname) . '"...</div>';
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo '<div class="success">✓ База данных создана!</div>';
                
                // Подключаемся к созданной базе
                $pdo->exec("USE `$dbname`");
                
                // Читаем SQL файл
                $sqlFile = __DIR__ . '/database/database.sql';
                if (!file_exists($sqlFile)) {
                    throw new Exception("Файл $sqlFile не найден!");
                }
                
                echo '<div class="info">Импорт структуры таблиц...</div>';
                $sql = file_get_contents($sqlFile);
                
                // Удаляем комментарии и SET команды, оставляем только CREATE TABLE
                $sql = preg_replace('/^--.*$/m', '', $sql);
                $sql = preg_replace('/^SET.*$/m', '', $sql);
                
                // Разбиваем на запросы
                $queries = array_filter(array_map('trim', explode(';', $sql)));
                
                $tablesCreated = 0;
                foreach ($queries as $query) {
                    if (!empty($query) && 
                        (stripos($query, 'CREATE TABLE') !== false || 
                         stripos($query, 'CREATE SCHEMA') !== false ||
                         stripos($query, 'USE ') !== false)) {
                        try {
                            $pdo->exec($query);
                            if (stripos($query, 'CREATE TABLE') !== false) {
                                $tablesCreated++;
                            }
                        } catch (PDOException $e) {
                            // Игнорируем ошибки "уже существует"
                            if (stripos($e->getMessage(), 'already exists') === false &&
                                stripos($e->getMessage(), 'Duplicate') === false) {
                                echo '<div class="error">⚠ ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                        }
                    }
                }
                
                echo '<div class="success">✓ Создано таблиц: ' . $tablesCreated . '</div>';
                
                // Обновляем config.php если пароль был указан
                if ($pass !== '') {
                    $configFile = __DIR__ . '/config.php';
                    $configContent = file_get_contents($configFile);
                    $configContent = preg_replace(
                        "/define\('DB_PASS',\s*'[^']*'\);/",
                        "define('DB_PASS', '$pass');",
                        $configContent
                    );
                    file_put_contents($configFile, $configContent);
                    echo '<div class="success">✓ Файл config.php обновлён с паролем</div>';
                }
                
                echo '<div class="success" style="margin-top: 20px; font-size: 18px; font-weight: bold;">';
                echo '🎉 База данных успешно создана!<br>';
                echo '<a href="index.php" style="color: #155724; text-decoration: underline;">Перейти на главную страницу →</a>';
                echo '</div>';
                
            } catch (PDOException $e) {
                echo '<div class="error">';
                echo '<strong>Ошибка подключения:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '<br><br>Проверьте:<ul>';
                echo '<li>Правильность пароля пользователя root</li>';
                echo '<li>Запущен ли MySQL сервер</li>';
                echo '<li>Настройки хоста (обычно localhost)</li>';
                echo '</ul>';
                echo '</div>';
                echo '<a href="?step=form" style="color: #4CAF50;">← Попробовать снова</a>';
            } catch (Exception $e) {
                echo '<div class="error">Ошибка: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<a href="?step=form" style="color: #4CAF50;">← Попробовать снова</a>';
            }
        } else {
            // Показываем форму
            ?>
            <div class="info">
                <strong>Инструкция:</strong><br>
                Введите данные для подключения к MySQL. Обычно используется пользователь <code>root</code> без пароля.
                Если у вас установлен пароль для root, введите его.
            </div>
            
            <form method="POST" action="?step=create">
                <div class="form-group">
                    <label>Хост MySQL:</label>
                    <input type="text" name="host" value="<?php echo htmlspecialchars($host); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Пользователь:</label>
                    <input type="text" name="user" value="<?php echo htmlspecialchars($user); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Пароль (оставьте пустым, если пароля нет):</label>
                    <input type="password" name="pass" value="">
                </div>
                
                <button type="submit">Создать базу данных</button>
            </form>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 14px;">
                <strong>Что будет сделано:</strong>
                <ul>
                    <li>Создана база данных "ритуальные_услуги"</li>
                    <li>Созданы все необходимые таблицы</li>
                    <li>Обновлён файл config.php (если указан пароль)</li>
                </ul>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>
