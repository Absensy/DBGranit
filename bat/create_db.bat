@echo off
chcp 65001 >nul
cd /d "%~dp0"
C:\xampp\mysql\bin\mysql.exe -u root < database.sql
if %errorlevel% equ 0 (
    echo База данных успешно создана!
) else (
    echo Ошибка при создании базы данных.
)
pause
