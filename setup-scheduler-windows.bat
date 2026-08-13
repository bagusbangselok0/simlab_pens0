@echo off
REM Script untuk membuat Windows Scheduled Task untuk Laravel Scheduler
REM Jalankan script ini sebagai Administrator

setlocal enabledelayedexpansion

>nul 2>&1 net session
if %ERRORLEVEL% neq 0 (
    echo ERROR: Jalankan script ini sebagai Administrator.
    pause
    exit /b 1
)

set "PHP_PATH=C:\xampp\php\php.exe"
set "PROJECT_PATH=c:\xampp\htdocs\simlab"
set "TASK_NAME=Laravel Scheduler - SimLab"
set TASK_ACTION="%PHP_PATH%" "%PROJECT_PATH%\artisan" schedule:run

REM Cek apakah PHP ada
if not exist "%PHP_PATH%" (
    echo ERROR: PHP tidak ditemukan di %PHP_PATH%
    echo Silakan ubah PHP_PATH di script ini sesuai instalasi XAMPP Anda
    pause
    exit /b 1
)

REM Cek apakah project ada
if not exist "%PROJECT_PATH%" (
    echo ERROR: Project tidak ditemukan di %PROJECT_PATH%
    echo Silakan ubah PROJECT_PATH di script ini
    pause
    exit /b 1
)

echo.
echo ======================================
echo Setup Windows Task Scheduler
echo =====================================
echo.
echo Task Name: %TASK_NAME%
echo PHP Path: %PHP_PATH%
echo Project: %PROJECT_PATH%
echo Action: %TASK_ACTION%
echo.

REM Hapus task jika sudah ada
schtasks /query /tn "%TASK_NAME%" >nul 2>&1
if %ERRORLEVEL% == 0 (
    echo Menghapus task lama...
    schtasks /delete /tn "%TASK_NAME%" /f
    if %ERRORLEVEL% == 0 (
        echo Task lama berhasil dihapus.
    ) else (
        echo WARNING: Gagal menghapus task lama. Mungkin task tidak ada atau permission bermasalah.
    )
) else (
    echo Task lama tidak ditemukan.
)

echo Membuat task baru...
schtasks /create /tn "%TASK_NAME%" /tr "%TASK_ACTION%" /sc minute /mo 1 /f /ru SYSTEM

if %ERRORLEVEL% == 0 (
    echo.
    echo ✓ Berhasil membuat scheduled task!
    echo.
    echo Task sedang berjalan setiap 1 menit untuk mengeksekusi:
    echo   %TASK_ACTION%
    echo.
    echo Untuk melihat task:
    echo   - Buka Task Scheduler (taskschd.msc)
    echo   - Cari "%TASK_NAME%"
    echo.
    echo Untuk test manual:
    echo   - Buka Command Prompt di %PROJECT_PATH%
    echo   - Ketik: php artisan peminjaman:expire
    echo.
) else (
    echo.
    echo ERROR: Gagal membuat scheduled task. Kode error: %ERRORLEVEL%
    echo Pastikan Anda menjalankan script ini sebagai Administrator.
    echo.
    echo Detail status task:
    schtasks /query /tn "%TASK_NAME%" 2>&1
    echo.
)

pause
