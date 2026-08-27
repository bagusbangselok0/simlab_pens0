<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\InventarisBarangController;
use App\Http\Controllers\InventarisRuanganController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LabManagerController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// 1. Root Redirect
Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/login');
});

// 2. Guest Routes (Hanya untuk yang belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');

    // Registrasi
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

    // Reset Password (Public)
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// 3. Authenticated Routes (Harus login)
Route::middleware('auth')->group(function () {

    // Logout & Dashboard Utama
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update_password');
        Route::post('/upload-ttd/{id}', [ProfileController::class, 'uploadFileTtd'])->name('profile.upload_ttd');
        Route::post('/update-photo/{id}', [ProfileController::class, 'updatePhoto'])->name('profile.update_photo');
        Route::post('/update-nohp', [ProfileController::class, 'updateNoHp'])->name('profile.update_nohp');
        // Tambahkan route lain untuk edit profile jika diperlukan
    });

    // ---------------------------------------------------------
    // NOTIFIKASI (Semua role yang terautentikasi)
    // ---------------------------------------------------------
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread', [NotificationController::class, 'fetchUnread'])->name('notifications.unread');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
        // Push notification subscription
        Route::post('/push/subscribe', [NotificationController::class, 'subscribePush'])->name('notifications.push.subscribe');
        Route::post('/push/unsubscribe', [NotificationController::class, 'unsubscribePush'])->name('notifications.push.unsubscribe');
    });

    // ---------------------------------------------------------
    // ROLE: MAHASISWA
    // ---------------------------------------------------------
    Route::middleware('role:mahasiswa,admin')->group(function () {
        Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::get('/peminjaman/detail/{id}', [PeminjamanController::class, 'detailModal'])->name('peminjaman.detail');
        Route::post('/peminjaman/store', [PeminjamanController::class, 'store'])->name('peminjaman.store');
        Route::patch('/peminjaman/{id}/cancel', [PeminjamanController::class, 'cancel'])->name('peminjaman.cancel');
        Route::get('/peminjaman/cetak/{id}', [PeminjamanController::class, 'cetak'])->name('peminjaman.cetak');

        // Fitur Presensi
        Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
        Route::post('/presensi/store', [PresensiController::class, 'store'])->name('presensi.store');
        Route::get('/presensi/riwayat', [PresensiController::class, 'riwayat'])->name('presensi.riwayat');
    });

    // ---------------------------------------------------------
    // ROLE: SATPAM
    // ---------------------------------------------------------
    Route::middleware('role:satpam,admin')->group(function () {
        // Konfirmasi Presensi
        Route::get('/konfirmasi-presensi', [PresensiController::class, 'indexSatpam'])->name('satpam.presensi');
        Route::patch('/konfirmasi-presensi/{id}', [PresensiController::class, 'confirmPresence'])->name('satpam.confirm');
        Route::patch('/konfirmasi-presensi/{id}/assign', [PresensiController::class, 'assignSatpam'])->name('satpam.assign');
        Route::get('/konfirmasi-presensi/cetak/{id}', [PresensiController::class, 'cetak'])->name('satpam.cetak');
    });

    // ---------------------------------------------------------
    // ROLE: PLP & DOSEN (Approver)
    // ---------------------------------------------------------
    Route::middleware('role:plp,dosen,admin')->group(function () {
        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::patch('/approval/{id}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::patch('/approval/{id}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
        Route::patch('/approval/{id}/rejection-note', [ApprovalController::class, 'rejectWithNote'])->name('approval.reject-with-note');
    });

    // ---------------------------------------------------------
    // ROLE: PLP, DOSEN, SATPAM (Monitoring Presensi)
    // ---------------------------------------------------------
    Route::middleware('role:plp,dosen,satpam,admin')->group(function () {
        // Monitoring Presensi
        Route::get('/monitoring-presensi', [PresensiController::class, 'indexMonitoring'])->name('presensi.monitoring');
        Route::get('/monitoring-presensi/cetak/{id}', [PresensiController::class, 'cetak'])->name('presensi.monitoring.cetak');
    });

    // ---------------------------------------------------------
    // ROLE: ADMIN (Manajemen Master Data)
    // ---------------------------------------------------------
    Route::middleware('role:admin')->group(function () {
        // Manajemen Users
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::patch('/{id}/verify', [UserController::class, 'verifyUser'])->name('users.verify');
            Route::post('/{id}/generate-reset-link', [UserController::class, 'generateResetLink'])->name('users.reset_link');
        });

        Route::get('/admin/peminjaman', [PeminjamanController::class, 'indexAdmin'])->name('peminjaman.admin');

        // Manajemen Labs
        Route::prefix('labs')->group(function () {
            Route::get('/', [LabController::class, 'index'])->name('lab.index');
            Route::post('/', [LabController::class, 'store'])->name('lab.store');
            Route::get('/{id}/edit', [LabController::class, 'edit'])->name('lab.edit');
            Route::put('/{id}', [LabController::class, 'update'])->name('lab.update');
            Route::delete('/{id}', [LabController::class, 'destroy'])->name('lab.destroy');
        });

        Route::prefix('lab-managers')->group(function () {
            Route::get('/', [LabManagerController::class, 'index'])->name('lab_manager.index');
            Route::post('/', [LabManagerController::class, 'store'])->name('lab_manager.store');
            Route::get('/detail/{id}', [LabManagerController::class, 'detailAndEditModal'])->name('lab_manager.edit');
            Route::put('/{id}', [LabManagerController::class, 'update'])->name('lab_manager.update');
            Route::delete('/{id}', [LabManagerController::class, 'destroy'])->name('lab_manager.destroy');
            // Tambahkan route lain untuk CRUD Lab Manager jika diperlukan
        });
    });

    // ---------------------------------------------------------
    
    // ---------------------------------------------------------
    // ROLE: PLP & ADMIN (Master Inventaris)
    // ---------------------------------------------------------
    Route::middleware('role:plp,admin')->group(function () {
        Route::get('/inventaris', [InventarisBarangController::class, 'index'])->name('inventaris.index');
        Route::post('/inventaris', [InventarisBarangController::class, 'store'])->name('inventaris.store');
        Route::post('/inventaris/import', [InventarisBarangController::class, 'import'])->name('inventaris.import');
        Route::get('/inventaris/template', [InventarisBarangController::class, 'downloadTemplate'])->name('inventaris.template');
        Route::put('/inventaris/{id}', [InventarisBarangController::class, 'update'])->name('inventaris.update');
        Route::delete('/inventaris/{id}', [InventarisBarangController::class, 'destroy'])->name('inventaris.destroy');
        Route::post('/inventaris/{id}/assign-ruangan', [InventarisBarangController::class, 'assignToRuangan'])->name('inventaris.assign');
    });

    // ROLE: PLP, DOSEN (KALAB), ADMIN (Inventaris Ruangan / DIR)
    // ---------------------------------------------------------
    Route::middleware('role:plp,dosen,admin')->group(function () {
        Route::get('/inventaris-ruangan', [InventarisRuanganController::class, 'index'])->name('inventaris-ruangan.index');
        Route::post('/inventaris-ruangan', [InventarisRuanganController::class, 'store'])->name('inventaris-ruangan.store');
        Route::put('/inventaris-ruangan/{id}', [InventarisRuanganController::class, 'update'])->name('inventaris-ruangan.update');
        Route::delete('/inventaris-ruangan/{id}', [InventarisRuanganController::class, 'destroy'])->name('inventaris-ruangan.destroy');
        Route::get('/inventaris-ruangan/export-pdf/{lab_id}', [InventarisRuanganController::class, 'exportPdf'])->name('inventaris-ruangan.export-pdf');
    });

});
