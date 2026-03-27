@echo off
echo ========================================
echo  Multi-Branch Inventory System Setup
echo ========================================
echo.

echo [1/4] Creating CodeIgniter 4 project...
composer create-project codeigniter4/appstarter . --no-interaction
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Composer failed. Please ensure Composer is installed.
    exit /b 1
)

echo.
echo [2/4] Installing additional Composer packages (JWT)...
composer require firebase/php-jwt
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: JWT package install failed. Will use manual install.
)

echo.
echo [3/4] Setting up frontend (Vue 3 + Vite)...
cd frontend
call npm install
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: npm install failed.
    exit /b 1
)
cd ..

echo.
echo [4/4] Setup complete!
echo.
echo Next steps:
echo  1. Copy env to .env and configure DB credentials
echo  2. Run: php spark migrate
echo  3. Run: php spark db:seed RoleSeeder
echo  4. Run: php spark db:seed UserSeeder
echo  5. Start backend:  php spark serve
echo  6. Start frontend: cd frontend ^&^& npm run dev
echo.
echo Test credentials:
echo  Admin:          admin@system.com   / Admin@12345
echo  Branch Manager: manager@branch1.com / Manager@12345
echo  Sales User:     sales@branch1.com  / Sales@12345
echo.
pause
