<?php
/**
 * PDF Report Generator Script
 * Handles PDF generation for all tables and search results
 */

require_once '../config.php';
require_once '../includes/PDFReportGenerator.php';

$pdo = getDBConnection();
$table = $_GET['table'] ?? '';
$search = $_GET['search'] ?? '';

// Handle search results export
if ($search === '1' && isset($_GET['search_term']) && isset($_GET['search_type'])) {
    generateSearchPDF($pdo, $_GET['search_term'], $_GET['search_type']);
    exit;
}

// Handle table export
switch ($table) {
    case 'users':
        generateUsersPDF($pdo);
        break;
    case 'categories':
        generateCategoriesPDF($pdo);
        break;
    case 'products':
        generateProductsPDF($pdo);
        break;
    case 'orders':
        generateOrdersPDF($pdo);
        break;
    case 'order_items':
        generateOrderItemsPDF($pdo);
        break;
    default:
        die('Неверный параметр таблицы');
}

function generateUsersPDF($pdo) {
    $stmt = $pdo->query("SELECT * FROM пользователи ORDER BY id_пользователя DESC");
    $users = $stmt->fetchAll();
    
    $pdf = new PDFReportGenerator('Отчёт: Пользователи');
    $pdf->addTitle('Отчёт по пользователям');
    $pdf->addInfo('Дата формирования', date('d.m.Y H:i'));
    $pdf->addInfo('Всего записей', count($users));
    $pdf->addLine();
    
    $headers = ['ID', 'Имя', 'Фамилия', 'Email', 'Телефон', 'Дата создания'];
    $data = [];
    
    foreach ($users as $user) {
        $data[] = [
            $user['id_пользователя'],
            $user['имя'],
            $user['фамилия'],
            $user['email'],
            $user['телефон'] ?? '-',
            $user['дата_создания']
        ];
    }
    
    $colWidths = [15, 30, 30, 50, 30, 25];
    $pdf->addTable($headers, $data, $colWidths);
    
    $pdf->output('users_report.pdf', 'I');
}

function generateCategoriesPDF($pdo) {
    $stmt = $pdo->query("SELECT * FROM категории_товаров ORDER BY id_категории DESC");
    $categories = $stmt->fetchAll();
    
    $pdf = new PDFReportGenerator('Отчёт: Категории');
    $pdf->addTitle('Отчёт по категориям товаров');
    $pdf->addInfo('Дата формирования', date('d.m.Y H:i'));
    $pdf->addInfo('Всего записей', count($categories));
    $pdf->addLine();
    
    $headers = ['ID', 'Название', 'Описание'];
    $data = [];
    
    foreach ($categories as $category) {
        $data[] = [
            $category['id_категории'],
            $category['название'],
            mb_substr($category['описание'] ?? '-', 0, 60)
        ];
    }
    
    $colWidths = [15, 50, 115];
    $pdf->addTable($headers, $data, $colWidths);
    
    $pdf->output('categories_report.pdf', 'I');
}

function generateProductsPDF($pdo) {
    $stmt = $pdo->query("
        SELECT т.*, к.название as категория_название 
        FROM товары т 
        LEFT JOIN категории_товаров к ON т.id_категории = к.id_категории 
        ORDER BY т.id_товара DESC
    ");
    $products = $stmt->fetchAll();
    
    $pdf = new PDFReportGenerator('Отчёт: Товары');
    $pdf->addTitle('Отчёт по товарам');
    $pdf->addInfo('Дата формирования', date('d.m.Y H:i'));
    $pdf->addInfo('Всего записей', count($products));
    $pdf->addLine();
    
    $headers = ['ID', 'Название', 'Категория', 'Цена', 'На складе', 'Онлайн оплата'];
    $data = [];
    
    foreach ($products as $product) {
        $data[] = [
            $product['id_товара'],
            mb_substr($product['название'], 0, 25),
            $product['категория_название'] ?? '-',
            number_format($product['цена'], 2, '.', ' ') . ' ₽',
            $product['количество_на_складе'],
            $product['оплата_онлайн'] ? 'Да' : 'Нет'
        ];
    }
    
    $colWidths = [12, 50, 40, 25, 20, 25];
    $pdf->addTable($headers, $data, $colWidths);
    
    $pdf->output('products_report.pdf', 'I');
}

function generateOrdersPDF($pdo) {
    $stmt = $pdo->query("
        SELECT з.*, п.имя, п.фамилия, п.email
        FROM заказы з
        LEFT JOIN пользователи п ON з.id_пользователя = п.id_пользователя
        ORDER BY з.id_заказа DESC
    ");
    $orders = $stmt->fetchAll();
    
    $pdf = new PDFReportGenerator('Отчёт: Заказы');
    $pdf->addTitle('Отчёт по заказам');
    $pdf->addInfo('Дата формирования', date('d.m.Y H:i'));
    $pdf->addInfo('Всего записей', count($orders));
    $pdf->addLine();
    
    $headers = ['ID', 'Пользователь', 'Дата заказа', 'Статус', 'Сумма', 'Оплата'];
    $data = [];
    
    foreach ($orders as $order) {
        $userName = ($order['имя'] && $order['фамилия']) 
            ? $order['имя'] . ' ' . $order['фамилия'] 
            : ($order['email'] ?? '-');
        
        $data[] = [
            $order['id_заказа'],
            mb_substr($userName, 0, 20),
            $order['дата_заказа'],
            $order['статус'],
            $order['общая_сумма'] ? number_format($order['общая_сумма'], 2, '.', ' ') . ' ₽' : '-',
            $order['способ_оплаты']
        ];
    }
    
    $colWidths = [12, 50, 35, 30, 30, 20];
    $pdf->addTable($headers, $data, $colWidths);
    
    $pdf->output('orders_report.pdf', 'I');
}

function generateOrderItemsPDF($pdo) {
    $stmt = $pdo->query("
        SELECT твз.*, т.название as товар_название, з.id_заказа
        FROM товары_в_заказе твз
        LEFT JOIN товары т ON твз.id_товара = т.id_товара
        LEFT JOIN заказы з ON твз.id_заказа = з.id_заказа
        ORDER BY твз.id_товара_в_заказе DESC
    ");
    $items = $stmt->fetchAll();
    
    $pdf = new PDFReportGenerator('Отчёт: Товары в заказах');
    $pdf->addTitle('Отчёт по товарам в заказах');
    $pdf->addInfo('Дата формирования', date('d.m.Y H:i'));
    $pdf->addInfo('Всего записей', count($items));
    $pdf->addLine();
    
    $headers = ['ID', 'ID Заказа', 'Товар', 'Количество', 'Цена'];
    $data = [];
    
    foreach ($items as $item) {
        $data[] = [
            $item['id_товара_в_заказе'],
            $item['id_заказа'],
            mb_substr($item['товар_название'] ?? '-', 0, 30),
            $item['количество'],
            number_format($item['цена_на_момент_покупки'], 2, '.', ' ') . ' ₽'
        ];
    }
    
    $colWidths = [15, 20, 80, 25, 40];
    $pdf->addTable($headers, $data, $colWidths);
    
    $pdf->output('order_items_report.pdf', 'I');
}

function generateSearchPDF($pdo, $searchTerm, $searchType) {
    $results = [];
    $searchPattern = '%' . $searchTerm . '%';
    
    // Same search logic as in search.php
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
    
    $typeLabels = [
        'пользователь' => 'Пользователь',
        'категория' => 'Категория',
        'товар' => 'Товар',
        'заказ' => 'Заказ',
        'товар_в_заказе' => 'Товар в заказе'
    ];
    
    $pdf = new PDFReportGenerator('Отчёт: Результаты поиска');
    $pdf->addTitle('Результаты поиска');
    $pdf->addInfo('Поисковый запрос', $searchTerm);
    $pdf->addInfo('Тип поиска', $searchType === 'all' ? 'Все таблицы' : $typeLabels[$searchType] ?? $searchType);
    $pdf->addInfo('Дата формирования', date('d.m.Y H:i'));
    $pdf->addInfo('Найдено записей', count($results));
    $pdf->addLine();
    
    if (empty($results)) {
        $pdf->addText('По запросу "' . $searchTerm . '" ничего не найдено.', 'C');
    } else {
        $headers = ['Тип', 'ID', 'Название', 'Дополнительная информация'];
        $data = [];
        
        foreach ($results as $result) {
            $info = '';
            if ($result['дополнительно']) {
                $info .= $result['дополнительно'];
            }
            if ($result['дополнительно2']) {
                $info .= ($info ? ' | ' : '') . $result['дополнительно2'];
            }
            
            $data[] = [
                $typeLabels[$result['тип']] ?? $result['тип'],
                $result['id'],
                mb_substr($result['название'], 0, 30),
                mb_substr($info ?: '-', 0, 50)
            ];
        }
        
        $colWidths = [40, 15, 50, 75];
        $pdf->addTable($headers, $data, $colWidths);
    }
    
    $pdf->output('search_report.pdf', 'I');
}
