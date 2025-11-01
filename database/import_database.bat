@echo off
REM Fabricon.shop Database Import Script
REM This script imports the database schema into MySQL via XAMPP

echo ========================================
echo Fabricon.shop Database Import
echo ========================================
echo.

REM Set MySQL path (adjust if your XAMPP is installed elsewhere)
set MYSQL_PATH=C:\xampp\mysql\bin
set DB_NAME=fabricon_db
set SQL_FILE=%~dp0fabricon_schema.sql

echo Checking MySQL installation...
if not exist "%MYSQL_PATH%\mysql.exe" (
    echo ERROR: MySQL not found at %MYSQL_PATH%
    echo Please update MYSQL_PATH in this script to match your XAMPP installation
    pause
    exit /b 1
)

echo MySQL found!
echo.
echo Database Name: %DB_NAME%
echo SQL File: %SQL_FILE%
echo.

REM Check if SQL file exists
if not exist "%SQL_FILE%" (
    echo ERROR: SQL file not found at %SQL_FILE%
    pause
    exit /b 1
)

echo Importing database...
echo.
echo Please enter your MySQL root password when prompted.
echo (Default XAMPP installation has no password - just press Enter)
echo.

REM Import the database
"%MYSQL_PATH%\mysql.exe" -u root -p < "%SQL_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo SUCCESS! Database imported successfully
    echo ========================================
    echo.
    echo You can now access the database:
    echo - Database Name: %DB_NAME%
    echo - phpMyAdmin: http://localhost/phpmyadmin
    echo.
    echo Next steps:
    echo 1. Open phpMyAdmin in your browser
    echo 2. Select 'fabricon_db' from the left sidebar
    echo 3. Verify all tables are created
    echo.
) else (
    echo.
    echo ========================================
    echo ERROR: Database import failed
    echo ========================================
    echo.
    echo Please check:
    echo 1. XAMPP MySQL is running
    echo 2. Your MySQL password is correct
    echo 3. The SQL file is not corrupted
    echo.
)

pause
