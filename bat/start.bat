@echo off
echo Запуск сервера PHP...
echo.
echo Убедитесь, что:
echo 1. PHP установлен и добавлен в PATH
echo 2. MySQL запущен
echo 3. База данных создана (выполните database.sql)
echo.
echo Сервер будет доступен по адресу: http://localhost:8000
echo.
echo Для остановки нажмите Ctrl+C
echo.
php -S localhost:8000
pause
