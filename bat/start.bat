@echo off
echo Запуск сервера PHP...
echo.
echo Убедитесь, что:
echo 1. MySQL запущен (через XAMPP Control Panel)
echo 2. База данных создана (выполните database.sql)
echo.
echo Сервер будет доступен по адресу: http://localhost:8000
echo.
echo Для остановки нажмите Ctrl+C
echo.
cd /d "%~dp0.."
C:\xampp\php\php.exe -S localhost:8000
pause
