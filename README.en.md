# DBGranit - Funeral Services Management System

> 🇷🇺 [Русская версия](README.md) | 🇬🇧 English version

Web application for managing funeral services with full CRUD functionality, search, and order management.

## 📋 Description

Funeral Services Management System is a PHP web application designed to manage users, product categories, products, orders, and their items. The application provides a convenient interface for working with MySQL database.

## ✨ Key Features

- 👥 **User Management** - add, edit, delete users
- 📦 **Category Management** - work with product and service categories
- 🛍️ **Product Management** - full CRUD for products with category linking
- 📋 **Order Management** - create and manage orders with statuses
- 🛒 **Order Items** - manage items in orders
- 🔍 **Search** - universal search across all system data
- 📊 **Database Seeding** - scripts for automatic test data filling

## 🛠️ Technologies

- **PHP 7.4+** - server-side
- **MySQL 5.7+** - database
- **PDO** - database operations
- **HTML5, CSS3, JavaScript** - client-side
- **XAMPP/OpenServer** - local development environment

## 📁 Project Structure

```
DBGranit/
├── assets/
│   └── css/
│       └── style.css          # Application styles
├── bat/
│   ├── create_db.bat          # Database creation script
│   ├── fill_db.bat            # Database seeding script
│   └── start.bat              # PHP server startup
├── database/
│   ├── database.sql           # Database creation SQL script
│   └── mock_data.sql          # Test data
├── pages/
│   ├── users.php              # User management
│   ├── categories.php         # Category management
│   ├── products.php           # Product management
│   ├── orders.php             # Order management
│   ├── order_items.php        # Order items
│   └── search.php             # Data search
├── scripts/
│   ├── create_database.php    # Database creation
│   ├── fill_database.php      # Database seeding
│   └── test_connection.php    # Connection test
├── config.php                 # Database configuration
├── config.example.php         # Configuration example
├── index.php                  # Main page
└── README.md                  # Documentation
```

## 🗄️ Database Structure

The `ритуальные_услуги` database contains the following tables:

- **пользователи** (users) - system user information
- **категории_товаров** (product_categories) - product and service categories
- **товары** (products) - products and services with prices and quantities
- **заказы** (orders) - user orders with statuses
- **товары_в_заказе** (order_items) - link between products and orders

## 🚀 Installation and Setup

### Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or built-in PHP server
- XAMPP/OpenServer (optional, for easier setup)

### Step 1: Clone Repository

```bash
git clone https://github.com/Absensy/DBGranit.git
cd DBGranit
```

### Step 2: Database Setup

1. Start MySQL server
2. Create database by running the script:
   ```bash
   mysql -u root -p < database/database.sql
   ```
   Or import via phpMyAdmin/MySQL Workbench

3. (Optional) Fill database with test data:
   ```bash
   mysql -u root -p ритуальные_услуги < database/mock_data.sql
   ```

### Step 3: Configuration

Copy `config.example.php` to `config.php` and update connection parameters if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ритуальные_услуги');
```

### Step 4: Run Application

#### Option A: Built-in PHP Server

```bash
php -S localhost:8000
```

Or use the ready script:
```bash
# Windows
bat\start.bat

# Linux/Mac
php -S localhost:8000
```

#### Option B: XAMPP

1. Copy project folder to `C:\xampp\htdocs\`
2. Start Apache via XAMPP control panel
3. Open: `http://localhost/DBGranit`

#### Option C: OpenServer

1. Copy project folder to `domains`
2. Select domain in OpenServer
3. Open selected domain in browser

### Step 5: Open in Browser

Navigate to: `http://localhost:8000` (or your web server address)

## 📖 Usage

### Main Page

The main page (`index.php`) provides navigation to all system sections and quick access cards.

### User Management

- Add new users with email validation
- Edit user data
- Delete users
- View list of all users

### Category Management

- Create product categories
- Edit names and descriptions
- Delete categories (with check for linked products)

### Product Management

- Add products with category linking
- Manage prices and stock quantities
- Configure online payment availability
- Edit and delete products

### Order Management

- Create orders linked to users
- Manage statuses: в_обработке (processing), оплачен (paid), отправлен (sent), выполнен (completed), офлайн (offline)
- Choose payment method: online/offline
- Automatic total amount calculation

### Order Items

- Add products to orders
- Automatic price filling at purchase time
- Edit quantities and delete items

### Search

- Universal search across all tables
- Filter by data type
- Navigate to edit found records

## 🔒 Security

- PDO prepared statements for SQL injection protection
- User password hashing (password_hash)
- Server-side data validation
- XSS protection through output escaping

## 🐛 Troubleshooting

### Database Connection Error

- Make sure MySQL server is running
- Check settings in `config.php`
- Ensure database is created

### PHP Not Found

- Add PHP to PATH variable
- Or use XAMPP/OpenServer

### Database Not Found

- Run SQL script `database/database.sql`
- Check database name in `config.php`

### Encoding Issues

- Ensure database uses `utf8mb4`
- Check charset settings in `config.php`

## 📝 License

This project is created for educational purposes.

## 👤 Author

Project developed for funeral services management.

## 🤝 Contributing

Any suggestions and improvements are welcome! Create issues and pull requests.

---

**Note:** For production use, it is recommended to add authentication system, improve error handling, and add logging.
