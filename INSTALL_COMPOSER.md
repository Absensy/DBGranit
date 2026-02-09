# Установка Composer

## Вариант 1: Глобальная установка (рекомендуется)

### Windows:

1. **Создайте папку для Composer:**
   - Создайте папку `C:\ProgramData\Composer\` (или любую другую удобную)

2. **Поместите composer.phar:**
   - Скопируйте скачанный `composer.phar` в эту папку

3. **Создайте файл composer.bat:**
   - В той же папке создайте файл `composer.bat` со следующим содержимым:
   ```batch
   @echo off
   php "%~dp0composer.phar" %*
   ```

4. **Добавьте в PATH:**
   - Нажмите Win+R, введите `sysdm.cpl` и нажмите Enter
   - Перейдите на вкладку "Дополнительно" → "Переменные среды"
   - В "Системные переменные" найдите `Path` и нажмите "Изменить"
   - Нажмите "Создать" и добавьте путь: `C:\ProgramData\Composer\`
   - Нажмите "ОК" во всех окнах

5. **Проверьте установку:**
   - Откройте новую командную строку (важно - новую!)
   - Выполните: `composer --version`
   - Должна появиться версия Composer

### Linux/Mac:

1. Переместите composer.phar в системную папку:
   ```bash
   sudo mv composer.phar /usr/local/bin/composer
   sudo chmod +x /usr/local/bin/composer
   ```

2. Проверьте: `composer --version`

## Вариант 2: Локальная установка (только для проекта)

1. Поместите `composer.phar` в корень проекта `DBGranit/`

2. Используйте через PHP:
   ```bash
   php composer.phar install
   ```

   Или создайте алиас:
   ```bash
   # Windows (в PowerShell)
   Set-Alias composer php composer.phar
   
   # Linux/Mac
   alias composer='php composer.phar'
   ```

## После установки Composer

Выполните в корне проекта:
```bash
composer install
```

Или если используете локальный composer.phar:
```bash
php composer.phar install
```

Это установит TCPDF и другие зависимости.
