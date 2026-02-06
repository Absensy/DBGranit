@echo off
chcp 65001 >nul
echo Заполнение базы данных mock данными...
echo.
C:\xampp\php\php.exe fill_database.php
echo.
pause
