<p align="center">
  <h2>Simlab (Sistem Informasi Manajemen Laboratorium)</h2>
</p>

# Simlab (Sistem Informasi Manajemen Laboratorium)

## 📖 Deskripsi Aplikasi (Application Description)

**Simlab (Sistem Informasi Manajemen Laboratorium)** adalah aplikasi berbasis web yang dikembangkan menggunakan framework **Laravel 12** untuk mendigitalkan dan mengotomatisasi seluruh proses administrasi dan operasional laboratorium kampus. Sistem ini dirancang untuk menggantikan birokrasi dan pencatatan manual menjadi alur kerja yang terpusat, transparan, terintegrasi, dan aman bagi 5 peran pengguna: **Administrator**, **Dosen / Kepala Laboratorium (Kalab)**, **Pranata Laboratorium Pendidikan (PLP)**, **Petugas Keamanan (Satpam)**, dan **Mahasiswa**.

Melalui Simlab:

- **Mahasiswa** dapat dengan mudah mengajukan izin peminjaman ruangan dan peralatan lab secara daring, memantau riwayat pengajuan, mengajukan presensi kehadiran di lab, serta mengunduh surat izin peminjaman resmi.
- **PLP & Kalab** dapat meninjau, menyetujui, atau menolak permohonan peminjaman bertingkat, mengelola inventaris peralatan lab, memantau kehadiran mahasiswa secara _real-time_, dan mencetak laporan Daftar Inventaris Ruangan (DIR).
- **Satpam** memiliki modul khusus untuk mengonfirmasi kehadiran (_check-in_ & _check-out_) mahasiswa yang sedang meminjam lab dan mencetak bukti fisik presensi.
- **Admin** memiliki kendali penuh terhadap manajemen master data (pengguna, lab, pejabat pengelola lab, setting auto-approval), verifikasi pendaftaran akun mahasiswa baru, verifikasi keabsahan tanda tangan digital pejabat, pembuatan link reset password sekali pakai, rekap ekspor data peminjaman, serta pemantauan sistem secara menyeluruh.

Dokumen surat peminjaman lab dicetak dalam format **Flattened PDF** (dokumen dan tanda tangan digital dikunci menjadi citra utuh beresolusi tinggi via `Imagick` & `Ghostscript`), sehingga isi surat dan tanda tangan tidak dapat dimanipulasi, diekstrak, maupun disalahgunakan.

---

## 🚀 Fitur Utama (Key Features)

### 1. 👥 Multi-Role Access Control (5 Role Pengguna)

- **Admin:** Manajemen pengguna, master laboratorium, pejabat pengelola lab (_Lab Manager_), verifikasi akun mahasiswa baru, verifikasi tanda tangan digital pejabat, pengaturan sistem (_Auto-Approval Kalab_ per lab), rekap dan ekspor data peminjaman (Excel & PDF), serta manajemen inventaris master.
- **Dosen / Kepala Laboratorium (Kalab):** Persetujuan peminjaman tahap akhir (_Kalab Approval_), monitoring presensi lab binaan, pengelolaan Daftar Inventaris Ruangan (DIR), dan ekspor DIR ke PDF.
- **PLP (Pranata Laboratorium Pendidikan):** Persetujuan peminjaman tahap awal (_PLP Approval_), monitoring presensi lab, pengelolaan master barang inventaris (CRUD, import massal Excel, penempatan barang ke ruangan), dan pengelolaan DIR.
- **Satpam:** Dashboard konfirmasi presensi masuk dan keluar mahasiswa peminjam lab, penugasan satpam piket, dan pencetakan bukti fisik presensi.
- **Mahasiswa:** Pengajuan izin peminjaman ruang dan alat lab, pengajuan presensi masuk & keluar lab ke petugas satpam, unduh surat peminjaman PDF resmi ber-TTD, dan penerimaan notifikasi _real-time_.

### 2. 📝 Alur Peminjaman Lab & Auto-Approval Bertingkat

- **Alur Persetujuan Bertingkat:** Normalnya melalui verifikasi PLP (`pending_plp`), lalu dilanjutkan ke Kalab (`pending_kalab`), hingga berstatus `disetujui`.
- **Per-Lab Kalab Auto-Approval:** Admin dapat mengaktifkan fitur auto-approval Kalab per laboratorium. Jika aktif, setelah PLP menyetujui permohonan, sistem langsung mengesahkan status peminjaman menjadi `disetujui` secara otomatis (syarat: Kalab telah terdaftar dan tanda tangan digitalnya sudah disetujui Admin).
- **Auto-Cancel & Expiration:** Scheduler otomatis membatalkan (_auto-cancel_) pengajuan peminjaman yang tidak diproses selama 24 jam atau jika waktu peminjaman yang diminta telah lewat. Peminjaman aktif yang telah melewati batas waktu selesai otomatis dialihkan menjadi `kadaluarsa`.

### 3. ⏱️ Presensi & Monitoring Kehadiran Laboratorium

- Mahasiswa mengajukan presensi masuk (_check-in_) dan keluar (_check-out_) dengan memilih petugas satpam yang sedang bertugas.
- Satpam menerima notifikasi dan memvalidasi presensi melalui menu khusus konfirmasi presensi.
- Satpam, PLP, Dosen, dan Admin dapat memonitor kehadiran di lab secara _real-time_ dan mencetak bukti presensi.

### 4. 📦 Manajemen Inventaris Barang & DIR (Daftar Inventaris Ruangan)

- **Master Inventaris Barang:** Pencatatan aset laboratorium secara terperinci (kode barang, nama, merk, kondisi, spesifikasi, tanggal pengadaan).
- **Import Massal Excel (.xlsx):** Kemudahan impor data inventaris massal dengan template Excel yang dapat langsung diunduh dari aplikasi (didukung `Shuchkin\SimpleXLSX`).
- **Penempatan & Mutasi Barang:** Fitur penempatan barang dari master inventaris ke ruangan laboratorium tertentu.
- **Daftar Inventaris Ruangan (DIR):** Laporan inventaris per laboratorium lengkap dengan fitur cetak / ekspor ke PDF resmi berformat standar.

### 5. 🔒 Flattened & Secure PDF Export

- Surat peminjaman yang disahkan dikonversi menjadi gambar utuh (_flattened PDF_) beresolusi 200 DPI menggunakan `Imagick` & `Ghostscript`.
- Seluruh teks dan berkas tanda tangan digital terkunci rapat, tidak dapat diseleksi (_copy-paste_), dipisah, atau diekstrak, demi mencegah pemalsuan dokumen surat resmi.

### 6. ✍️ Manajemen & Verifikasi Tanda Tangan Digital

- Pengguna pejabat (Dosen, Kalab, PLP) dapat mengunggah berkas tanda tangan digital melalui halaman profil secara instan tanpa memuat ulang halaman (AJAX).
- Tanda tangan yang diunggah berstatus `pending` dan wajib diverifikasi/disetujui oleh Administrator sebelum dapat dibubuhkan pada dokumen resmi.

### 7. 🔔 Notifikasi Real-Time (Web Push & WhatsApp)

- **Web Push API:** Notifikasi native browser/OS langsung ke perangkat pengguna yang bekerja di latar belakang tanpa dependensi Firebase/FCM.
- **Integrasi WhatsApp:** Dukungan pengiriman notifikasi otomatis via Fonnte API (dapat diaktifkan/dinonaktifkan melalui konfigurasi `.env`).

### 8. 📊 Rekapitulasi & Pelaporan Data

- Admin dapat menyaring dan mengekspor seluruh rekap peminjaman lab ke format **Excel (.xlsx)** (didukung oleh `SimpleXLSXGen`) dan **PDF**.
- Ekspor laporan Daftar Inventaris Ruangan (DIR) per lab ke PDF.

### 9. 🔐 Keamanan & Pemulihan Akun

- **Verifikasi Registrasi Mahasiswa:** Mahasiswa yang mendaftar mandiri harus diverifikasi terlebih dahulu oleh Administrator sebelum dapat menggunakan akunnya.
- **Admin-Assisted Password Reset:** Menghindari celah pengiriman email otomatis tanpa proteksi, pemulihan akun dilakukan melalui pembuatan token/link reset satu kali pakai yang digenerate oleh Admin dari dashboard untuk dibagikan ke pengguna (misal via WhatsApp).

---

## 🛠️ Technology Stack

| Komponen                      | Teknologi                                                         |
| ----------------------------- | ----------------------------------------------------------------- |
| **Framework Backend**         | [Laravel 12](https://laravel.com/) (PHP 8.2+)                     |
| **Database**                  | MySQL 8.0                                                         |
| **Frontend**                  | Blade Templates, Vanilla CSS, Tailwind CSS / Vite, AJAX, Select2  |
| **Data Tables**               | Yajra Laravel DataTables (`yajra/laravel-datatables-oracle`)      |
| **PDF Generation & Security** | `barryvdh/laravel-dompdf`, `ext-imagick`, `ghostscript`           |
| **Excel Handling**            | `shuchkin/simplexlsx` (Import), `shuchkin/simplexlsxgen` (Export) |
| **Web Push Notification**     | `minishlink/web-push` (VAPID API)                                 |
| **WhatsApp Gateway**          | Fonnte API (`App\Channels\WhatsAppChannel`)                       |
| **Environment**               | Docker & Docker Compose / Local Server (XAMPP / Laragon / Native) |

---

## 🐳 Panduan Instalasi Menggunakan Docker

Simlab telah dilengkapi konfigurasi Docker lengkap (`Dockerfile` dan `docker-compose.yml`) yang menyertakan PHP 8.2, Nginx, MySQL 8, ImageMagick, Ghostscript, serta container background scheduler otomatis.

1. **Clone repositori:**

    ```bash
    git clone <url-repositori-anda>
    cd simlab
    ```

2. **Konfigurasi Environment:**

    ```bash
    cp .env.example .env
    ```

    _Sesuaikan pengaturan koneksi database di `.env` untuk Docker:_

    ```env
    DB_CONNECTION=mysql
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=<nama_database_anda>
    DB_USERNAME=<user_anda>
    DB_PASSWORD=<password_anda>
    ```

3. **Jalankan Container Docker:**

    ```bash
    docker-compose up -d --build
    ```

    _Port & Layanan yang berjalan:_
    - **Web App (Nginx):** `http://localhost:8080`
    - **Database (MySQL):** Port `3307` di host komputer (internal container: `3306`)
    - **App (PHP-FPM):** Port internal `9000`
    - **Scheduler Container:** Berjalan otomatis di latar belakang menjalankan `php artisan schedule:work`

4. **Inisialisasi Aplikasi di dalam Container:**

    ```bash
    docker-compose exec app bash

    # Di dalam container app:
    composer install
    npm install && npm run build
    php artisan key:generate
    php artisan migrate --seed
    php artisan storage:link
    exit
    ```

5. **Generate VAPID Key untuk Push Notification:**

    ```bash
    docker-compose exec app php artisan vapid:generate
    docker-compose exec app php artisan config:clear
    ```

6. **Buka Aplikasi:**
   Akses `http://localhost:8080` melalui peramban web Anda.

---

## 💻 Panduan Instalasi Non-Docker (XAMPP / Laragon / Local)

Jika Anda menjalankan aplikasi langsung di komputer lokal menggunakan **XAMPP**, **Laragon**, atau **Native PHP**:

### 1. Persiapan PHP Extension & Tool Sistem

- Buka file `php.ini` Anda dan pastikan ekstensi berikut telah diaktifkan (hapus tanda titik koma `;` di depannya):
    ```ini
    extension=curl
    extension=gmp
    extension=gd
    extension=imagick
    extension=zip
    extension=mbstring
    extension=pdo_mysql
    ```
- **Wajib untuk Fitur Flatten PDF (Keamanan Surat):**
    - Pasang **Ghostscript** dan **ImageMagick** di sistem operasi Anda:
        - **Ubuntu / Debian / WSL:**
            ```bash
            sudo apt-get update
            sudo apt-get install -y php-imagick ghostscript libmagickwand-dev
            ```
        - **Windows:** Pasang installer Ghostscript for Windows dan pastikan binary `gswin64c` atau `gs` terdaftar di System `PATH`. Pasang extension `php_imagick.dll` yang sesuai dengan versi PHP Anda.
    - Restart web server (Apache/Nginx).
    - Buat database baru bernama `simlab` di phpMyAdmin / MySQL (`http://localhost/phpmyadmin`).

### 2. Setup Project & Environment

```bash
git clone <url-repositori-anda>
cd simlab
cp .env.example .env
```

Sesuaikan konfigurasi koneksi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simlab
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Install Dependencies & Build Asset

```bash
composer install
npm install && npm run build
```

### 4. Setup Database & Key Aplikasi

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan vapid:generate
php artisan config:clear
```

### 5. Jalankan Server Aplikasi

```bash
php artisan serve
```

Akses aplikasi melalui browser di `http://127.0.0.1:8000`.

---

## 🔔 Konfigurasi Web Push & WhatsApp Notification

### A. Web Push Notification (VAPID)

1. Generate pasangan VAPID keys:
    ```bash
    php artisan vapid:generate
    php artisan config:clear
    ```
2. Pengguna dapat mengaktifkan push notification dengan menekan tombol **"Push"** di samping ikon bel notifikasi di navbar kanan atas.
3. Notifikasi akan muncul secara otomatis saat status peminjaman diverifikasi atau saat ada permintaan presensi.

### B. WhatsApp Notification (Fonnte API - Opsional)

Jika ingin mengaktifkan notifikasi via WhatsApp, sesuaikan variabel berikut di file `.env`:

```env
WA_ENABLED=true
FONNTE_TOKEN=your_fonnte_token_here
```

Daftar dan dapatkan token API di [Fonnte](https://fonnte.com/). Jika diset `false`, sistem tetap berjalan normal dan hanya menggunakan notifikasi in-app database serta web push.

---

## ⏰ Konfigurasi Task Scheduler (Otomatisasi Pembatalan & Kadaluarsa)

Aplikasi memiliki dua tugas scheduler utama di `routes/console.php`:

| Perintah              | Frekuensi           | Fungsi                                                                                                                                                 |
| --------------------- | ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `peminjaman:expire`   | Setiap menit        | Membatalkan pengajuan pending yang belum diproses >24 jam atau telah lewat waktu; mengubah peminjaman disetujui yang lewat waktu menjadi `kadaluarsa`. |
| `notifications:prune` | Setiap hari (02:00) | Membersihkan notifikasi lama yang telah berusia lebih dari 10 hari.                                                                                    |

### Pada Lingkungan Docker

Container `simlab_scheduler` di dalam `docker-compose.yml` telah dikonfigurasi untuk menjalankan `php artisan schedule:work` secara otomatis di latar belakang. **Tidak diperlukan konfigurasi cron tambahan.**

### Pada Linux / VPS (Production)

Tambahkan entri crontab sistem:

```bash
crontab -e
```

Tambahkan baris berikut (sesuaikan path project Anda):

```cron
* * * * * cd /path/ke/simlab && php artisan schedule:run >> /dev/null 2>&1
```

### Pada Windows (Development & Production)

- **Development:** Cukup jalankan perintah berikut di terminal terpisah selama proses pengembangan:
    ```powershell
    php artisan schedule:work
    ```
- **Production / Otomatisasi Windows:**
  Gunakan skrip `setup-scheduler-windows.bat` yang telah disediakan, atau daftarkan task di Windows Task Scheduler:
    ```powershell
    schtasks /create /tn "Simlab Scheduler" /tr "php artisan schedule:run" /sc minute /mo 1 /f
    ```
- **Pengujian Manual:**
  Untuk menguji eksekusi pembatalan langsung tanpa menunggu scheduler:
    ```bash
    php artisan peminjaman:expire
    ```

---

## ⚙️ Pengaturan Auto-Approval Kalab

Fitur auto-approval mempersingkat proses birokrasi permohonan peminjaman lab:

```text
[Alur Standar]:  Pengajuan -> pending_plp -> pending_kalab -> disetujui
[Auto-Approve]:  Pengajuan -> pending_plp -> disetujui
```

**Ketentuan & Aturan:**

1. Pengaturan dikonfigurasi **per laboratorium** oleh Admin melalui menu **Data Master > Pengaturan Sistem**.
2. PLP tetap **wajib** memeriksa dan menyetujui pengajuan terlebih dahulu sebelum auto-approval Kalab berjalan.
3. Auto-approval hanya aktif jika laboratorium memiliki Kalab yang terdaftar dan tanda tangan digital Kalab telah berstatus **Disetujui** oleh Administrator.
4. Dosen yang bukan Kalab dan PLP tidak memiliki pengaturan auto-approval.
5. Konfigurasi disimpan dalam tabel `settings` dengan key `approval.auto_approve_kalab` dan scope `lab:{id}`.

Untuk mereset konfigurasi default via seeder tanpa menimpa pengaturan yang sudah diubah:

```bash
php artisan db:seed --class=SettingsSeeder
```

---

## 🔧 Ringkasan Alur Kerja & Keamanan (Core Workflows)

- **Keamanan Surat & Flat PDF:** Dokumen bukti peminjaman lab dikonversi menjadi citra utuh yang terproteksi (_flattened_). Hal ini mencegah duplikasi, modifikasi isi surat, dan ekstraksi gambar tanda tangan digital pejabat.
- **Verifikasi Tanda Tangan Digital:** Pengunggahan berkas tanda tangan baru secara otomatis menggantikan berkas lama di media penyimpanan lokal secara aman dan mengubah status verifikasi menjadi `pending` hingga diverifikasi oleh Admin.
- **Protokol Reset Password Aman:** Menghindari celah reset email massal tanpa proteksi, reset kata sandi dikontrol oleh Admin yang menghasilkan tautan unik sekali pakai dengan batas kadaluarsa 60 menit.
- **Presensi Tervalidasi Petugas:** Mahasiswa tidak dapat memalsukan jam masuk/keluar karena waktu presensi diverifikasi dan dicatat langsung oleh petugas Satpam yang bertugas saat itu.
- **Automated Request Expiration:** Pengajuan peminjaman lab yang tertahan pada status `pending_plp` atau `pending_kalab` lebih dari 24 jam atau melewati waktu selesai otomatis dibatalkan, dan mahasiswa pemohon menerima pemberitahuan.

---

## 📄 Lisensi

Aplikasi Simlab dilisensikan di bawah lisensi terbuka [MIT License](https://opensource.org/licenses/MIT).
