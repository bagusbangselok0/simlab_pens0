# Setup Otomatis Expire Peminjaman Lab

## 📋 Deskripsi
Fitur ini akan secara **otomatis mengubah status peminjaman** berdasarkan kondisi waktu **tanpa perlu user login atau membuka aplikasi**.

Sistem akan:
- ✅ Mengubah peminjaman `pending_plp`/`pending_kalab` yang belum disetujui dalam **24 jam** → `dibatalkan`
- ✅ Mengubah peminjaman `disetujui` yang sudah sampai ke **waktu selesai** → `kadaluarsa`
- ✅ Berjalan secara background/otomatis setiap jam

## 🔧 Cara Kerja Teknis

### 1. **Command** (`app/Console/Commands/ExpirePeminjamanLab.php`)
```php
// Command ini melakukan 2 logika:
// A. Pending > 24 jam:
//    - Status pending_plp atau pending_kalab yang dibuat > 24 jam lalu
//    - Ubah status ke: dibatalkan

// B. Disetujui lewat waktu selesai:
//    - Status disetujui yang waktu_selesai sudah lewat
//    - Ubah status ke: kadaluarsa

// Dijalankan dengan: php artisan peminjaman:expire
```

### 2. **Scheduler** (`app/Console/Kernel.php`)
```php
// Scheduler mengatur kapan command dijalankan:
// - Setiap jam otomatis
// - Mencegah duplikasi dengan withoutOverlapping()
```

### 3. **Execution** 
System menggunakan Laravel's task scheduler yang perlu dipicu oleh:
- Windows Task Scheduler (untuk Windows/XAMPP)
- Crontab (untuk Linux/Mac/Production)

---

## ⚙️ Setup Lengkap untuk XAMPP (Windows)

### **Langkah 1: Pastikan XAMPP Siap**

1. Buka XAMPP Control Panel
2. Mulai layanan:
   - ✅ Apache (bukan wajib untuk scheduler, tapi bagus untuk development)
   - ✅ **MySQL** (PENTING! Dibutuhkan untuk database)

### **Langkah 2: Jalankan Script Setup Otomatis**

Cara termudah adalah menggunakan script yang sudah disiapkan:

1. Buka **Command Prompt sebagai Administrator**
   - Tekan `Win + R`
   - Ketik `cmd` 
   - Tekan `Ctrl + Shift + Enter`

2. Navigasi ke project:
   ```cmd
   cd c:\xampp\htdocs\simlab
   ```

3. Jalankan script setup:
   ```cmd
   setup-scheduler-windows.bat
   ```

4. Jika berhasil, akan muncul pesan: ✓ **Berhasil membuat scheduled task!**

### **Langkah 3: Verifikasi Task sudah Terbuat**

1. Buka **Task Scheduler** Windows:
   - Tekan `Win + R`
   - Ketik `taskschd.msc`
   - Tekan Enter

2. Cari misi **"Laravel Scheduler - SimLab"** di folder Task Scheduler Library

3. Klik kanan → Properties untuk melihat detail:
   - Trigger: "At logon" atau "Daily"
   - Action: PHP path + artisan schedule:run command
   - Run with highest privileges: ✓ (untuk menghindari permission issues)

---

## 🧪 Testing & Verifikasi

### **Test Paket Komando Manual**

Buka Command Prompt di folder project:

```cmd
cd c:\xampp\htdocs\simlab
```

#### Test 1: Lihat apakah command tersedia
```cmd
php artisan list | findstr peminjaman
```

**Output yang diharapkan:**
```
 peminjaman:expire    Mengubah status peminjaman yang belum disetujui dalam 24 jam menjadi kadaluarsa
```

#### Test 2: Jalankan command sekali
```cmd
php artisan peminjaman:expire
```

**Output yang diharapkan (jika ada peminjaman expired):**
```
Jumlah peminjaman yang berubah menjadi kadaluarsa: 3
```

**Atau jika tidak ada:**
```
Jumlah peminjaman yang berubah menjadi kadaluarsa: 0
```

#### Test 3: Test scheduler (jalankan task scheduler sekali)
```cmd
php artisan schedule:work
```

Ini akan menampilkan scheduler bekerja secara realtime. Tekan `Ctrl + C` untuk berhenti.

---

## 📊 Database Schema Check

Pastikan tabel `peminjaman_lab` memiliki kolom-kolom berikut:

```sql
-- Gunakan command ini untuk melihat struktur tabel
php artisan tinker
>>> DB::select('DESCRIBE peminjaman_lab');
```

Kolom yang diperlukan:
- ✅ `id` - Primary key
- ✅ `status` - String (pending_plp, pending_kalab, disetujui, ditolak, kadaluarsa)
- ✅ `created_at` - Timestamp (untuk mengecek 24 jam)
- ✅ `updated_at` - Timestamp (auto-update oleh Laravel)

---

## 📊 Status Peminjaman dan Expiration

| Status | Deskripsi | Kondisi Expiration |
|--------|-----------|-------------------|
| `pending_plp` | Menunggu persetujuan PLP | ⏰ > 24 jam → `dibatalkan` |
| `pending_kalab` | Menunggu persetujuan Kalab | ⏰ > 24 jam → `dibatalkan` |
| `disetujui` | Sudah disetujui (aktif) | ⏰ waktu_selesai lewat → `kadaluarsa` |
| `dibatalkan` | Dibatalkan (pending > 24 jam) | ❌ Final state |
| `ditolak` | Ditolak oleh PLP/Kalab | ❌ Final state |
| `kadaluarsa` | Kadaluarsa (disetujui lewat waktu) | ❌ Final state |

---

## 🔍 Troubleshooting

### ❌ "MySQL connection refused"
**Solusi:** 
- Buka XAMPP Control Panel
- Klik tombol **Start** di sebelah MySQL
- Tunggu sampai berwarna hijau dan bertulisan "Running"

### ❌ "Command not found: peminjaman:expire"
**Solusi:**
- Pastikan file `app/Console/Commands/ExpirePeminjamanLab.php` sudah ada
- Jalankan: `php artisan cache:clear`
- Jalankan: `composer dump-autoload`

### ❌ Task Scheduler tidak ada/error saat membuat
**Solusi:**
- Pastikan Command Prompt dibuka **sebagai Administrator**
- Edit `setup-scheduler-windows.bat` dan sesuaikan:
  - `PHP_PATH` ke lokasi PHP XAMPP Anda
  - `PROJECT_PATH` ke lokasi project

### ❌ Task ada tapi tidak berjalan
**Solusi:**
- Pastikan MySQL sudah dimulai
- Check log: `storage/logs/laravel.log`
- Test manual: `cd c:\xampp\htdocs\simlab && php artisan peminjaman:expire`

### ❌ Ingin mengubah frekuensi (misal tiap 30 menit)
**Solusi:**
1. Edit `app/Console/Kernel.php`
2. Ubah `->hourly()` menjadi `->everyThirtyMinutes()`
3. Re-run `setup-scheduler-windows.bat` untuk update task

---

## 📋 Setup untuk Linux/Mac (Production)

Jika menggunakan server Linux/Mac, setup dengan crontab:

1. Buka SSH ke server Anda

2. Edit crontab:
```bash
crontab -e
```

3. Tambahkan baris ini:
```cron
* * * * * cd /path/ke/simlab && php artisan schedule:run >> /dev/null 2>&1
```

4. Simpan dan keluar

Ini akan menjalankan Laravel scheduler setiap menit, dan Laravel akan memutuskan kapan menjalankan command berdasarkan konfigurasi di `Kernel.php`.

---

## 📁 File yang Telah Dibuat/Dimodifikasi

### Dibuat:
- ✅ `app/Console/Commands/ExpirePeminjamanLab.php` - Command untuk expire peminjaman
- ✅ `app/Console/Kernel.php` - Scheduler configuration
- ✅ `setup-scheduler-windows.bat` - Script otomatis untuk setup Windows Task
- ✅ `SETUP_AUTO_EXPIRE.md` - Dokumentasi ini

### Tidak dimodifikasi (sudah mendukung):
- ✓ `bootstrap/app.php` - Sudah menggunakan Laravel 11 console routing
- ✓ `routes/console.php` - Sudah terdaftar di bootstrap/app.php

---

## 📞 Quick Reference

### Command Penting:
```bash
# Test command
php artisan peminjaman:expire

# Lihat daftar command
php artisan list

# Jalankan scheduler (realtime)
php artisan schedule:work

# Clear cache (jika ada issue)
php artisan cache:clear
composer dump-autoload
```

### File Penting:
```
app/Console/Commands/ExpirePeminjamanLab.php   - Logic expire
app/Console/Kernel.php                          - Scheduler schedule
setup-scheduler-windows.bat                     - Windows setup script
SETUP_AUTO_EXPIRE.md                            - Doc ini
```

---

## ✅ Checklist Verifikasi

Sebelum go-live, pastikan:

- [ ] MySQL sudah berjalan di XAMPP
- [ ] Command `php artisan peminjaman:expire` berjalan tanpa error
- [ ] Task "Laravel Scheduler - SimLab" ada di Windows Task Scheduler
- [ ] Task sudah di-trigger test (klik kanan → Run)
- [ ] Status peminjaman test sudah berubah ke `kadaluarsa` setelah > 24 jam
- [ ] Log di `storage/logs/laravel.log` menunjukkan command dijalankan
- [ ] Database connection working properly

---

## 📌 Notes Penting

1. **Jangan stop MySQL** - MySQL harus tetap berjalan untuk scheduler bisa akses database
2. **Windows perlu running** - Task Scheduler memerlukan PC tetap hidup (atau always-on server untuk production)
3. **Cek timezone** - Pastikan server timezone benar (di `config/app.php`)
4. **Monitoring** - Cek `storage/logs/laravel.log` secara berkala untuk memastikan tidak ada error

---

## 🎯 Hasil Akhir

Setelah setup berhasil:

```
📦 Scenario A: Peminjaman Pending > 24 Jam
  User buat peminjaman baru
    ↓
  ⏳ Status = pending_plp atau pending_kalab
    ↓
  ⏱️ Sistem menunggu 24 jam...
    ↓
  🤖 Scheduler otomatis berjalan setiap jam
    ↓
  ✅ Jika sudah lewat 24 jam → Status otomatis berubah ke dibatalkan
    ↓
  📢 Tanpa perlu login atau membuka aplikasi!

📦 Scenario B: Peminjaman Disetujui Lewat Waktu Selesai
  PLP dan Kalab sudah setuju peminjaman
    ↓
  ✅ Status = disetujui (peminjaman aktif)
    ↓
  ⏱️ Sistem menunggu waktu selesai...
    ↓
  🤖 Scheduler otomatis berjalan setiap jam
    ↓
  ✅ Jika sudah sampai waktu selesai → Status otomatis berubah ke kadaluarsa
    ↓
  📢 Tanpa perlu login atau membuka aplikasi!
```

---

**Dibuat oleh:** GitHub Copilot  
**Tanggal:** April 7, 2026  
**Versi:** Laravel 11

