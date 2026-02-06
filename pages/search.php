<?php
require_once '../config.php';

$pdo = getDBConnection();
$results = [];
$searchTerm = '';
$searchType = 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchTerm = trim($_POST['search_term']);
    $searchType = $_POST['search_type'] ?? 'all';
    
    if (!empty($searchTerm)) {
        $searchPattern = '%' . $searchTerm . '%';
        
        if ($searchType === 'all' || $searchType === 'users') {
            $stmt = $pdo->prepare("
                SELECT 'пользователь' as тип, id_пользователя as id, 
                       CONCAT(имя, ' ', фамилия) as название, 
                       email as дополнительно, 
                       телефон as дополнительно2
                FROM пользователи 
                WHERE имя LIKE ? OR фамилия LIKE ? OR email LIKE ? OR телефон LIKE ?
            ");
            $stmt->execute([$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
            $users = $stmt->fetchAll();
            foreach ($users as $user) {
                $results[] = $user;
            }
        }
        
        if ($searchType === 'all' || $searchType === 'categories') {
            $stmt = $pdo->prepare("
                SELECT 'категория' as тип, id_категории as id, 
                       название, 
                       описание as дополнительно, 
                       NULL as дополнительно2
                FROM категории_товаров 
                WHERE название LIKE ? OR описание LIKE ?
            ");
            $stmt->execute([$searchPattern, $searchPattern]);
            $categories = $stmt->fetchAll();
            foreach ($categories as $category) {
                $results[] = $category;
            }
        }
        
        if ($searchType === 'all' || $searchType === 'products') {
            $stmt = $pdo->prepare("
                SELECT 'товар' as тип, id_товара as id, 
                       название, 
                       CONCAT('Цена: ', цена, ' ₽') as дополнительно, 
                       CONCAT('На складе: ', количество_на_складе) as дополнительно2
                FROM товары 
                WHERE название LIKE ? OR описание LIKE ?
            ");
            $stmt->execute([$searchPattern, $searchPattern]);
            $products = $stmt->fetchAll();
            foreach ($products as $product) {
                $results[] = $product;
            }
        }
        
        if ($searchType === 'all' || $searchType === 'orders') {
            $stmt = $pdo->prepare("
                SELECT 'заказ' as тип, з.id_заказа as id, 
                       CONCAT('Заказ #', з.id_заказа) as название, 
                       CONCAT('Пользователь: ', п.имя, ' ', п.фамилия) as дополнительно, 
                       CONCAT('Статус: ', з.статус, ', Сумма: ', з.общая_сумма, ' ₽') as дополнительно2
                FROM заказы з
                LEFT JOIN пользователи п ON з.id_пользователя = п.id_пользователя
                WHERE з.статус LIKE ? OR CAST(з.id_заказа AS CHAR) LIKE ?
            ");
            $stmt->execute([$searchPattern, $searchPattern]);
            $orders = $stmt->fetchAll();
            foreach ($orders as $order) {
                $results[] = $order;
            }
        }
        
        if ($searchType === 'all' || $searchType === 'order_items') {
            $stmt = $pdo->prepare("
                SELECT 'товар_в_заказе' as тип, твз.id_товара_в_заказе as id, 
                       CONCAT('Товар в заказе #', твз.id_заказа) as название, 
                       т.название as дополнительно, 
                       CONCAT('Количество: ', твз.количество, ', Цена: ', твз.цена_на_момент_покупки, ' ₽') as дополнительно2
                FROM товары_в_заказе твз
                LEFT JOIN товары т ON твз.id_товара = т.id_товара
                WHERE т.название LIKE ? OR CAST(твз.id_заказа AS CHAR) LIKE ?
            ");
            $stmt->execute([$searchPattern, $searchPattern]);
            $orderItems = $stmt->fetchAll();
            foreach ($orderItems as $item) {
                $results[] = $item;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск - Ритуальные услуги</title>
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
                <a href="order_items.php">Товары в заказе</a>
                <a href="search.php" class="active">Поиск</a>
            </nav>
        </header>

        <main>
            <div class="search-section">
                <h2>Поиск по базе данных</h2>
                <form method="POST" action="" class="search-form">
                    <input type="text" name="search_term" placeholder="Введите поисковый запрос..." value="<?php echo htmlspecialchars($searchTerm); ?>" required>
                    <select name="search_type">
                        <option value="all" <?php echo $searchType === 'all' ? 'selected' : ''; ?>>Все таблицы</option>
                        <option value="users" <?php echo $searchType === 'users' ? 'selected' : ''; ?>>Пользователи</option>
                        <option value="categories" <?php echo $searchType === 'categories' ? 'selected' : ''; ?>>Категории</option>
                        <option value="products" <?php echo $searchType === 'products' ? 'selected' : ''; ?>>Товары</option>
                        <option value="orders" <?php echo $searchType === 'orders' ? 'selected' : ''; ?>>Заказы</option>
                        <option value="order_items" <?php echo $searchType === 'order_items' ? 'selected' : ''; ?>>Товары в заказе</option>
                    </select>
                    <button type="submit" name="search" class="btn-primary">Поиск</button>
                </form>
            </div>

            <?php if (!empty($results)): ?>
                <div class="search-results">
                    <h2>Результаты поиска (найдено: <?php echo count($results); ?>)</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Тип</th>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Дополнительная информация</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'пользователь' => '👤 Пользователь',
                                            'категория' => '📦 Категория',
                                            'товар' => '🛍️ Товар',
                                            'заказ' => '📋 Заказ',
                                            'товар_в_заказе' => '🛒 Товар в заказе'
                                        ];
                                        echo $typeLabels[$result['тип']] ?? $result['тип'];
                                        ?>
                                    </td>
                                    <td><?php echo $result['id']; ?></td>
                                    <td><?php echo htmlspecialchars($result['название']); ?></td>
                                    <td>
                                        <?php if ($result['дополнительно']): ?>
                                            <div><?php echo htmlspecialchars($result['дополнительно']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($result['дополнительно2']): ?>
                                            <div style="color: #666; font-size: 0.9em;"><?php echo htmlspecialchars($result['дополнительно2']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <?php
                                        $editPages = [
                                            'пользователь' => 'users.php',
                                            'категория' => 'categories.php',
                                            'товар' => 'products.php',
                                            'заказ' => 'orders.php',
                                            'товар_в_заказе' => 'order_items.php'
                                        ];
                                        $page = $editPages[$result['тип']] ?? '';
                                        if ($page):
                                        ?>
                                            <a href="<?php echo $page; ?>?edit=<?php echo $result['id']; ?>" class="btn-edit">Редактировать</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="no-results">
                    <p>По запросу "<?php echo htmlspecialchars($searchTerm); ?>" ничего не найдено.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
