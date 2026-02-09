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
- 📄 **PDF Export** - generate PDF reports for all tables and search results

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
│   └── start.bat              # PHP server startup
├── database/
│   ├── database.sql           # Database creation SQL script
│   └── mock_data.sql          # Test data
├── includes/
│   ├── PDFReportGenerator.php # PDF generation class
│   └── tcpdf/                 # TCPDF library
├── pages/
│   ├── users.php              # User management
│   ├── categories.php         # Category management
│   ├── products.php           # Product management
│   ├── orders.php             # Order management
│   ├── order_items.php        # Order items
│   └── search.php             # Data search
├── reports/
│   └── generate_pdf.php       # PDF report generation
├── scripts/
│   ├── fill_database.php      # Database seeding with test data
│   └── test_connection.php    # Connection test
├── config.php                 # Database configuration
├── config.example.php         # Configuration example
├── setup_database.php         # Web interface for database setup
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

**Option A: Via Web Interface (recommended)**

1. Start PHP server (see Step 4)
2. Open in browser: `http://localhost:8000/setup_database.php`
3. Enter MySQL connection details and click "Create Database"
4. Database and all tables will be created automatically

**Option B: Via Command Line**

1. Start MySQL server
2. Create database:
   ```bash
   mysql -u root -p < database/database.sql
   ```
   Or import via phpMyAdmin/MySQL Workbench

3. (Optional) Fill database with test data via web interface:
   - Open: `http://localhost:8000/scripts/fill_database.php`
   - Click "Fill Database"

### Step 3: Install TCPDF (for PDF export feature)

For PDF export functionality, install TCPDF library:

**Via Composer (recommended):**
```bash
composer install
```

**Or manually:**
See detailed instructions in [INSTALL_PDF.md](INSTALL_PDF.md)

### Step 4: Configuration

Copy `config.example.php` to `config.php` and update connection parameters if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // or 'dbgranit' if you created a separate user
define('DB_PASS', '');      // enter password if it's set
define('DB_NAME', 'ритуальные_услуги');
```

**Note:** If you use the web interface `setup_database.php`, it will automatically update `config.php` when creating the database.

### Step 5: Run Application

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

### Step 6: Open in Browser

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
- Export search results to PDF

### PDF Export

- Generate PDF reports for all tables (users, categories, products, orders, order items)
- Export search results to PDF
- Beautiful formatting with headers, tables, and metadata
- Requires TCPDF library installation (see [INSTALL_PDF.md](INSTALL_PDF.md))

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

- Use web interface: `http://localhost:8000/setup_database.php`
- Or run SQL script `database/database.sql` manually
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
