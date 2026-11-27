<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\JadwalLabController;
use App\Http\Controllers\LogbookApprovalController;
use App\Http\Controllers\LogbookPraktikumController;
use App\Http\Controllers\SignatureVerificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\InventarisBarangController;
use App\Http\Controllers\InventarisRuanganController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LabManagerController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PeminjamanAlatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PrivateSignatureController;
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
    Route::post('/login', [LoginController::class, 'authenticate'])
        ->middleware('throttle:login')
        ->name('login.post');

    // Registrasi
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->middleware('throttle:register')
        ->name('register.post');

    // Reset Password (Public)
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->middleware('throttle:password-reset')
        ->name('password.update');
});

// Public Signed Routes untuk TTD Perwakilan Mahasiswa (Tanpa Login)
Route::middleware(['signed', 'throttle:public-logbook-sign'])
    ->whereIn('type', ['logbook', 'pertemuan'])
    ->group(function () {
        Route::get('/logbook/ttd-mahasiswa/{type}/{id}', [LogbookPraktikumController::class, 'showPublicSign'])
            ->name('logbook.sign-mahasiswa.show');
        Route::post('/logbook/ttd-mahasiswa/{type}/{id}', [LogbookPraktikumController::class, 'submitPublicSign'])
            ->name('logbook.sign-mahasiswa.submit');
    });

// 3. Authenticated Routes (Harus login)
Route::middleware('auth')->group(function () {

    // Logout & Dashboard Utama
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/signatures/user/{user}', [PrivateSignatureController::class, 'showUserSignature'])
        ->name('signatures.user.show');
    Route::get('/signatures/logbook/{type}/{id}', [PrivateSignatureController::class, 'showLogbookSignature'])
        ->whereIn('type', ['logbook', 'pertemuan'])
        ->name('signatures.logbook.show');

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/update-password', [ProfileController::class, 'updatePassword'])
            ->middleware('throttle:password-reset')
            ->name('profile.update_password');
        Route::post('/upload-ttd/{id}', [ProfileController::class, 'uploadFileTtd'])
            ->middleware('throttle:signature-upload')
            ->name('profile.upload_ttd');
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
    // JADWAL PEMAKAIAN LAB (Semua role dapat melihat & cetak)
    // ---------------------------------------------------------
    Route::prefix('jadwal-lab')->name('jadwal-lab.')->group(function () {
        Route::get('/', [JadwalLabController::class, 'index'])->name('index');
        Route::get('/export-pdf', [JadwalLabController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export-excel', [JadwalLabController::class, 'exportExcel'])->name('export-excel');

        Route::middleware('role:admin,plp')->group(function () {
            Route::get('/import/preview', [JadwalLabController::class, 'showPreview'])->name('preview-show');
            Route::post('/import/preview', [JadwalLabController::class, 'previewImport'])
                ->middleware('throttle:excel-import')
                ->name('preview-import');
            Route::post('/import/commit', [JadwalLabController::class, 'commitImport'])->name('commit-import');
            Route::post('/', [JadwalLabController::class, 'store'])->name('store');
            Route::put('/{jadwalLab}', [JadwalLabController::class, 'update'])->name('update');
            Route::delete('/{jadwalLab}', [JadwalLabController::class, 'destroy'])->name('destroy');
            Route::patch('/periode/{periode}/activate', [JadwalLabController::class, 'activatePeriode'])->name('periode.activate');
            Route::delete('/periode/{periode}', [JadwalLabController::class, 'destroyPeriode'])->name('periode.destroy');
        });
    });

    // ---------------------------------------------------------
    // LOGBOOK PRAKTIKUM PLP (FM. LAB-02) & APPROVAL DOSEN
    // ---------------------------------------------------------
    Route::middleware('role:plp,admin')->prefix('logbook')->name('logbook.')->group(function () {
        Route::get('/', [LogbookPraktikumController::class, 'index'])->name('index');
        Route::post('/from-jadwal', [LogbookPraktikumController::class, 'storeFromJadwal'])->name('store-from-jadwal');
        Route::post('/', [LogbookPraktikumController::class, 'store'])->name('store');
        Route::post('/mahasiswa-signature', [LogbookPraktikumController::class, 'saveMahasiswaSignature'])
            ->middleware('throttle:signature-upload')
            ->name('mahasiswa-signature');
        Route::post('/sign-link', [LogbookPraktikumController::class, 'generateSignLink'])->name('sign-link');
        Route::post('/templates', [LogbookPraktikumController::class, 'storeTemplate'])->name('templates.store');
        Route::delete('/templates/{template}', [LogbookPraktikumController::class, 'destroyTemplate'])->name('templates.destroy');

        Route::get('/{logbook}', [LogbookPraktikumController::class, 'show'])->name('show');
        Route::put('/{logbook}', [LogbookPraktikumController::class, 'update'])->name('update');
        Route::delete('/{logbook}', [LogbookPraktikumController::class, 'destroy'])->name('destroy');
        Route::post('/{logbook}/generate-pertemuan', [LogbookPraktikumController::class, 'generatePertemuans'])->name('generate-pertemuans');
        Route::post('/{logbook}/pertemuan', [LogbookPraktikumController::class, 'storePertemuan'])->name('pertemuan.store');
        Route::post('/{logbook}/bulk-submit', [LogbookPraktikumController::class, 'bulkSubmit'])->name('bulk-submit');

        Route::put('/pertemuan/{pertemuan}', [LogbookPraktikumController::class, 'updatePertemuan'])->name('pertemuan.update');
        Route::delete('/pertemuan/{pertemuan}', [LogbookPraktikumController::class, 'destroyPertemuan'])->name('pertemuan.destroy');
        Route::post('/pertemuan/{pertemuan}/duplicate', [LogbookPraktikumController::class, 'duplicatePertemuan'])->name('pertemuan.duplicate');
        Route::post('/pertemuan/{pertemuan}/submit', [LogbookPraktikumController::class, 'submitPertemuan'])->name('pertemuan.submit');
    });

    Route::middleware('role:plp,dosen,admin')->get('/logbook/{logbook}/export-pdf', [LogbookPraktikumController::class, 'exportPdf'])
        ->name('logbook.export-pdf');

    Route::middleware('role:dosen,admin')->prefix('approval-logbook')->name('logbook.approval.')->group(function () {
        Route::get('/', [LogbookApprovalController::class, 'index'])->name('index');
        Route::post('/{pertemuan}/approve', [LogbookApprovalController::class, 'approve'])->name('approve');
        Route::post('/bulk-approve', [LogbookApprovalController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/{pertemuan}/reject', [LogbookApprovalController::class, 'reject'])->name('reject');
        Route::post('/{pertemuan}/revoke', [LogbookApprovalController::class, 'revoke'])->name('revoke');
        Route::post('/auto-approve', [LogbookApprovalController::class, 'saveAutoApprove'])->name('auto-approve.save');
        Route::delete('/auto-approve/{setting}', [LogbookApprovalController::class, 'deleteAutoApprove'])->name('auto-approve.destroy');
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

        // Fitur Peminjaman Alat
        Route::get('/peminjaman-alat', [PeminjamanAlatController::class, 'index'])->name('peminjaman-alat.index');
        Route::get('/peminjaman-alat/get-alat/{lab_id}', [PeminjamanAlatController::class, 'getAlatByLab'])->name('peminjaman-alat.get-alat');
        Route::post('/peminjaman-alat/store', [PeminjamanAlatController::class, 'store'])->name('peminjaman-alat.store');
        Route::patch('/peminjaman-alat/{id}/cancel', [PeminjamanAlatController::class, 'cancel'])->name('peminjaman-alat.cancel');

        // Fitur Presensi
        Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
        Route::post('/presensi/store', [PresensiController::class, 'store'])->name('presensi.store');
        Route::get('/presensi/riwayat', [PresensiController::class, 'riwayat'])->name('presensi.riwayat');
    });

    // ---------------------------------------------------------
    // ROLE: MAHASISWA, PLP & ADMIN (Detail & Cetak Peminjaman Alat)
    // ---------------------------------------------------------
    Route::middleware('role:mahasiswa,plp,admin')->group(function () {
        Route::get('/peminjaman-alat/{id}', [PeminjamanAlatController::class, 'show'])->name('peminjaman-alat.show');
        Route::get('/peminjaman-alat/cetak/{id}', [PeminjamanAlatController::class, 'cetak'])->name('peminjaman-alat.cetak');
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
    // ROLE: PLP & ADMIN (Persetujuan Peminjaman Alat)
    // ---------------------------------------------------------
    Route::middleware('role:plp,admin')->group(function () {
        Route::get('/approval-alat', [PeminjamanAlatController::class, 'approvalIndex'])->name('peminjaman-alat.approval');
        Route::patch('/peminjaman-alat/{id}/approve', [PeminjamanAlatController::class, 'approve'])->name('peminjaman-alat.approve');
        Route::patch('/peminjaman-alat/{id}/reject', [PeminjamanAlatController::class, 'reject'])->name('peminjaman-alat.reject');
        Route::patch('/peminjaman-alat/{id}/kembalikan', [PeminjamanAlatController::class, 'kembalikan'])->name('peminjaman-alat.kembalikan');
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

        // Verifikasi Tanda Tangan Digital
        Route::prefix('admin/verifikasi-ttd')->group(function () {
            Route::get('/', [SignatureVerificationController::class, 'index'])->name('admin.signatures.index');
            Route::post('/{id}/approve', [SignatureVerificationController::class, 'approve'])->name('admin.signatures.approve');
            Route::post('/{id}/reject', [SignatureVerificationController::class, 'reject'])->name('admin.signatures.reject');
        });

        // Manajemen Users
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::patch('/{id}/verify', [UserController::class, 'verifyUser'])->name('users.verify');
            Route::post('/{id}/generate-reset-link', [UserController::class, 'generateResetLink'])
                ->middleware('throttle:password-reset')
                ->name('users.reset_link');
        });

        // Manajemen Jabatan
        Route::prefix('jabatans')->name('jabatan.')->group(function () {
            Route::get('/', [JabatanController::class, 'index'])->name('index');
            Route::post('/', [JabatanController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [JabatanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [JabatanController::class, 'update'])->name('update');
            Route::delete('/{id}', [JabatanController::class, 'destroy'])->name('destroy');
        });

        Route::get('/admin/peminjaman', [PeminjamanController::class, 'indexAdmin'])->name('peminjaman.admin');
        Route::get('/admin/peminjaman/export-excel', [PeminjamanController::class, 'exportAdminExcel'])->name('peminjaman.admin.export-excel');
        Route::get('/admin/peminjaman/export-pdf', [PeminjamanController::class, 'exportAdminPdf'])->name('peminjaman.admin.export-pdf');

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

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::patch('/settings/labs/{lab}/auto-approve-kalab', [SettingsController::class, 'updateAutoApproveKalab'])
            ->name('settings.auto-approve-kalab');
        Route::post('/settings/peminjaman-alat', [SettingsController::class, 'updateAlatSettings'])
            ->name('settings.peminjaman-alat');
        Route::post('/settings/notification-gateway', [SettingsController::class, 'updateNotificationSettings'])
            ->name('settings.notification-gateway');
        Route::post('/settings/notification-gateway/generate-vapid', [SettingsController::class, 'generateVapidKeys'])
            ->name('settings.notification.generate-vapid');
        Route::post('/settings/notification-gateway/test-wa', [SettingsController::class, 'testWhatsApp'])
            ->name('settings.notification.test-wa');
        Route::post('/settings/notification-gateway/test-push', [SettingsController::class, 'testWebPush'])
            ->name('settings.notification.test-push');
        Route::post('/settings/theme-loader', [SettingsController::class, 'updateThemeSettings'])
            ->name('settings.theme-loader');
    });

    // ---------------------------------------------------------

    // ---------------------------------------------------------
    // ROLE: PLP & ADMIN (Master Inventaris)
    // ---------------------------------------------------------
    Route::middleware('role:plp,admin')->group(function () {
        Route::get('/inventaris', [InventarisBarangController::class, 'index'])->name('inventaris.index');
        Route::post('/inventaris', [InventarisBarangController::class, 'store'])->name('inventaris.store');
        Route::post('/inventaris/import', [InventarisBarangController::class, 'import'])
            ->middleware('throttle:excel-import')
            ->name('inventaris.import');
        Route::get('/inventaris/template', [InventarisBarangController::class, 'downloadTemplate'])->name('inventaris.template');
        Route::get('/inventaris/{id}/detail', [InventarisBarangController::class, 'show'])->name('inventaris.show');
        Route::put('/inventaris/{id}', [InventarisBarangController::class, 'update'])->name('inventaris.update');
        Route::delete('/inventaris/{id}', [InventarisBarangController::class, 'destroy'])->name('inventaris.destroy');
        Route::post('/inventaris/{id}/assign-ruangan', [InventarisBarangController::class, 'assignToRuangan'])->name('inventaris.assign');
    });

    // ROLE: PLP, DOSEN (KALAB), ADMIN (Inventaris Ruangan / DIR)
    // ---------------------------------------------------------
    Route::middleware('role:plp,dosen,admin')->group(function () {
        Route::get('/inventaris-ruangan', [InventarisRuanganController::class, 'index'])->name('inventaris-ruangan.index');
        Route::get('/inventaris-ruangan/{id}/detail', [InventarisRuanganController::class, 'show'])->name('inventaris-ruangan.show');
        Route::post('/inventaris-ruangan', [InventarisRuanganController::class, 'store'])->name('inventaris-ruangan.store');
        Route::put('/inventaris-ruangan/{id}', [InventarisRuanganController::class, 'update'])->name('inventaris-ruangan.update');
        Route::delete('/inventaris-ruangan/{id}', [InventarisRuanganController::class, 'destroy'])->name('inventaris-ruangan.destroy');
        Route::get('/inventaris-ruangan/export-pdf/{lab_id}', [InventarisRuanganController::class, 'exportPdf'])->name('inventaris-ruangan.export-pdf');
    });
});
