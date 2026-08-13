<p align="center">
  <!-- Replace with actual logo path if different -->
  <h2>Simlab</h2>
</p>

# Simlab (Sistem Informasi Manajemen Laboratorium)

## 📖 Deskripsi Aplikasi (Application Description)

**Simlab (Sistem Informasi Manajemen Laboratorium)** adalah aplikasi berbasis web yang dikembangkan menggunakan framework **Laravel 12** untuk mendigitalkan dan mengotomatisasi seluruh proses administrasi di laboratorium kampus. Aplikasi ini dirancang untuk menggantikan pencatatan manual menjadi sistem yang terpusat, transparan, dan efisien bagi Mahasiswa, Dosen, dan Administrator.

Melalui aplikasi ini, **mahasiswa** dapat dengan mudah mengajukan permohonan peminjaman ruangan dan alat laboratorium dari mana saja. **Dosen dan Admin** memiliki akses ke dashboard khusus untuk memantau presensi (kehadiran) di laboratorium, meninjau dan menyetujui permohonan peminjaman, serta mengelola inventaris. 

Aplikasi ini juga dilengkapi dengan fitur modern untuk mempermudah birokrasi, seperti unggah **tanda tangan digital (Digital Signature)** secara instan tanpa memuat ulang halaman (via AJAX), **pembatalan otomatis (Auto-Cancel)** untuk permohonan yang tidak ditindaklanjuti selama 24 jam, manajemen profil pengguna, dan kemampuan untuk mencetak bukti peminjaman atau laporan dalam format **PDF**. Secara keseluruhan, Simlab bertujuan untuk menciptakan lingkungan laboratorium yang tertib, terdata dengan baik, dan mudah dikelola.

## 🚀 Key Features

- **Role-Based Access Control:** Dedicated, distinct dashboards for Admin, Lecturer (Dosen), and Students (Mahasiswa).
- **Peminjaman Lab (Lab Loans):** Streamlined requesting and management of laboratory rooms and equipment. Includes an automated system that cancels pending requests after 24 hours of inactivity if not approved.
- **Browser Push Notifications:** Real-time native OS/device notifications via standard Web Push API (built-in browser API), working in the background even when the app tab is closed (FCM/Firebase-free).
- **WhatsApp Notification Integration:** Built-in channel using Fonnte API (optional, can be turned on/off).
- **PDF Reporting:** Export detailed lab loan records directly to PDF format using `dompdf`.
- **Presensi & Monitoring:** Real-time tracking and monitoring of laboratory attendance.
- **User & Profile Management:** Advanced profile management including seamless, AJAX-powered signature file uploads (without page reloads).
- **Admin-Assisted Password Reset:** A secure manual password recovery workflow where admins generate one-time reset links from the dashboard (designed to be shared via WhatsApp).
- **Modern UI/UX:** Features a professional, glassmorphism-styled custom loading screen utilizing pure CSS/JS and custom SVGs for maximum performance without heavy animation libraries.
- **Containerized Environment:** Fully configured with Docker (`Dockerfile` and `docker-compose.yml`) for consistent local development and deployment.

## 🛠️ Technology Stack

- **Backend:** Laravel 12 (PHP 8.2+), `minishlink/web-push`
- **Frontend:** Service Worker (for Web Push), Blade Templates, Vanilla CSS/JS, AJAX, Select2
- **PDF Generation:** `barryvdh/laravel-dompdf`
- **Data Display:** `yajra/laravel-datatables-oracle`
- **Development Environment:** Docker & Docker Compose / Local Server (XAMPP/Laragon)

## 🐳 Installation (Docker)

Simlab is pre-configured to run easily with Docker. Follow these steps to spin up the project locally:

1. **Clone the repository:**
   ```bash
   git clone <your-repository-url>
   cd simlab
   ```

2. **Environment Configuration:**
   Copy the example `.env` file and adjust your database and environment settings.
   ```bash
   cp .env.example .env
   ```

3. **Spin up the containers:**
   Build and start the application, web server, and database containers.
   ```bash
   docker-compose up -d --build
   ```

4. **Install Dependencies and Initialize:**
   Execute into the application container to install PHP/Node dependencies, generate the app key, and run migrations.
   ```bash
   docker-compose exec app bash
   
   # Inside the container:
   composer install
   npm install && npm run build
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Access the application:**
   Open your web browser and navigate to `http://localhost:8080`.

---

## 💻 Installation (Non-Docker / XAMPP)

Jika Anda tidak menggunakan Docker dan ingin menjalankan aplikasi menggunakan lokal server seperti **XAMPP**:

1. **Persiapan Database & PHP Extension:**
   - Buka file `php.ini` di panel XAMPP Anda.
   - Pastikan extension **curl** dan **gmp** sudah diaktifkan (hapus tanda titik koma `;` jika masih dikomen):
     ```ini
     extension=curl
     extension=gmp
     ```
   - Restart apache & MySQL di control panel XAMPP.
   - Buat database baru bernama `simlab` di phpMyAdmin Anda (`http://localhost/phpmyadmin`).

2. **Clone & Setup Environment:**
   ```bash
   git clone <your-repository-url>
   cd simlab
   cp .env.example .env
   ```
   Buka file `.env` baru Anda, dan sesuaikan data database jika diperlukan (secara default XAMPP tidak memiliki password):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=simlab
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Install Dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```

4. **Generate Key & Migrasi:**
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Jalankan local server:**
   ```bash
   php artisan serve
   ```
   Buka browser Anda dan akses di `http://127.0.0.1:8000`.

---

## 🔔 Web Push Notification Setup

Untuk mengaktifkan fitur notifikasi push langsung ke device/browser pengguna, Anda perlu men-generate **VAPID Keys**:

1. **Jalankan perintah generator:**
   ```bash
   php artisan vapid:generate
   ```
   Perintah ini akan membuat sepasang public dan private key baru dan menyimpannya langsung ke file `.env` Anda.

2. **Bersihkan cache konfigurasi:**
   ```bash
   php artisan config:clear
   ```

3. **Cara Menggunakan di Browser:**
   - Setelah masuk ke aplikasi, klik tombol **"Push"** yang ada di sebelah ikon bel/notifikasi di navbar kanan atas.
   - Browser akan memunculkan popup izin notifikasi. Klik **Izinkan / Allow**.
   - Notifikasi push Anda sekarang aktif! Ketika ada perubahan status peminjaman, OS Anda akan memunculkan notifikasi secara native.

---

## ⏰ Task Scheduler Setup

Aplikasi ini menggunakan **Laravel Task Scheduler** untuk menjalankan tugas otomatis berikut (didefinisikan di `app/Console/Kernel.php`):

| Perintah | Jadwal | Fungsi |
|---|---|---|
| `peminjaman:expire` | Setiap jam | Otomatis membatalkan peminjaman yang berstatus *pending* lebih dari 24 jam |
| `notifications:prune` | Setiap hari pukul 02:00 | Menghapus notifikasi yang sudah lebih dari 10 hari |

Agar kedua tugas ini berjalan, Anda **harus** mengaktifkan scheduler sesuai environment Anda:

### Docker

Tambahkan cron job di dalam container aplikasi. Jika menggunakan `docker-compose exec`:

```bash
# Masuk ke container
docker-compose exec app bash

# Tambahkan cron entry
echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" | crontab -

# Pastikan service cron berjalan
service cron start
```

> **Tip:** Untuk solusi yang lebih permanen, tambahkan konfigurasi cron ke dalam `Dockerfile` atau buat container supervisor terpisah.

### Non-Docker / Linux

**Production (Cron Job):**

Tambahkan satu entry cron pada server Anda:

```bash
crontab -e
```

Lalu tambahkan baris berikut (sesuaikan path project):

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Development:**

Untuk development lokal, gunakan built-in worker yang akan berjalan di foreground:

```bash
# Jalankan sekali (untuk testing)
php artisan schedule:run

# Jalankan terus-menerus selama development (Laravel 8+)
php artisan schedule:work
```

### Non-Docker / Windows (XAMPP / Laragon)

**Production (Task Scheduler):**

Gunakan **Windows Task Scheduler** untuk menjalankan scheduler secara otomatis:

1. Buka **Task Scheduler** (`taskschd.msc`).
2. Klik **Create Basic Task**, beri nama misalnya `Simlab Laravel Scheduler`.
3. Pilih trigger **Daily**, lalu di bagian **Advanced settings** centang **Repeat task every 1 minute** selama **Indefinitely**.
4. Pilih action **Start a Program**, lalu isi:
   - **Program/script:** `php`
   - **Add arguments:** `artisan schedule:run`
   - **Start in:** `C:\path-to-your-project` (sesuaikan path project Anda)
5. Klik **Finish**.

Atau jalankan via Command Prompt / PowerShell:

```powershell
schtasks /create /tn "Simlab Scheduler" /tr "php artisan schedule:run" /sc minute /mo 1 /f
```

> **Catatan:** Pastikan `php` sudah terdaftar di environment variable `PATH` Windows Anda.

**Development:**

Untuk development lokal, cukup buka terminal di folder project dan jalankan:

```bash
# Jalankan sekali (untuk testing)
php artisan schedule:run

# Jalankan terus-menerus selama development (Laravel 8+)
php artisan schedule:work
```

> **Catatan:** `schedule:work` akan berjalan di foreground dan mengecek jadwal setiap menit, cocok untuk development tanpa perlu setup cron/Task Scheduler.

---

## 🔧 Core Workflows Overview

- **Signature Management:** Digital signatures are handled via an AJAX controller (`ProfileController`). Uploading a new signature safely removes the old file from local storage and updates the database reference seamlessly.
- **Password Reset Protocol:** To enhance security, automated email resets are disabled. Users must request a reset from an Administrator, who will generate a secure, one-time link from the Admin Dashboard.
- **Automated Request Expiration:** Lab loan requests that remain in a "pending" state for over 24 hours are automatically flagged and canceled by the system to free up requested resources.

## 📄 License

This project is built on top of the Laravel framework, which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
