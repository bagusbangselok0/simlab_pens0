<p align="center">
  <h2>Simlab (Sistem Informasi Manajemen Laboratorium)</h2>
</p>

# Simlab (Sistem Informasi Manajemen Laboratorium)

## 📖 Deskripsi Aplikasi (Application Description)

**Simlab (Sistem Informasi Manajemen Laboratorium)** adalah aplikasi berbasis web yang dikembangkan menggunakan framework **Laravel 12** untuk mendigitalkan dan mengotomatisasi seluruh proses administrasi dan operasional laboratorium kampus. Sistem ini dirancang untuk menggantikan birokrasi dan pencatatan manual menjadi alur kerja yang terpusat, transparan, terintegrasi, dan aman bagi 5 peran pengguna: **Administrator**, **Dosen / Kepala Laboratorium (Kalab)**, **Pranata Laboratorium Pendidikan (PLP)**, **Petugas Keamanan (Satpam)**, dan **Mahasiswa**.

Melalui Simlab:

- **Mahasiswa** dapat mengajukan izin peminjaman ruangan laboratorium maupun peminjaman peralatan/instrumen inventaris secara daring, memantau riwayat pengajuan secara bertahap via *stepper timeline*, mengajukan presensi kehadiran di lab, serta mengunduh surat bukti izin peminjaman resmi berformat PDF.
- **PLP & Kalab** dapat meninjau, menyetujui, atau menolak permohonan peminjaman ruangan dan peralatan, mengelola inventaris master barang dan Daftar Inventaris Ruangan (DIR), memantau kehadiran mahasiswa secara _real-time_, serta mengesahkan proses serah terima dan pengembalian alat.
- **Satpam** memiliki modul khusus untuk mengonfirmasi kehadiran (_check-in_ & _check-out_) mahasiswa yang sedang beraktivitas di laboratorium dan mencetak bukti fisik presensi.
- **Admin** memiliki kendali penuh terhadap manajemen master data (pengguna, lab, pejabat pengelola lab, setting auto-approval), verifikasi pendaftaran akun mahasiswa baru, verifikasi keabsahan tanda tangan digital pejabat, pembuatan link reset password sekali pakai, rekap ekspor data peminjaman, serta pengawasan sistem secara menyeluruh.

Dokumen surat peminjaman lab dicetak dalam format **Flattened PDF** (dokumen dan tanda tangan digital dikunci menjadi citra utuh beresolusi tinggi via `Imagick` & `Ghostscript`), sehingga isi surat dan tanda tangan tidak dapat dimanipulasi, diekstrak, maupun disalahgunakan.

---

## 🚀 Fitur Utama (Key Features)

### 1. 👥 Multi-Role Access Control (5 Role Pengguna)

- **Admin:** Manajemen pengguna, master laboratorium, pejabat pengelola lab (_Lab Manager_), verifikasi akun mahasiswa baru, verifikasi tanda tangan digital, pengaturan sistem (_Auto-Approval Kalab_ per lab), rekap dan ekspor data peminjaman (Excel & PDF), pengawasan seluruh pengajuan peminjaman ruang & alat, serta manajemen inventaris master.
- **Dosen / Kepala Laboratorium (Kalab):** Persetujuan peminjaman ruang lab tahap akhir (_Kalab Approval_), monitoring presensi lab binaan, pengelolaan Daftar Inventaris Ruangan (DIR), dan ekspor DIR ke PDF.
- **PLP (Pranata Laboratorium Pendidikan):** Persetujuan peminjaman ruang lab tahap awal (_PLP Approval_), **verifikasi dan persetujuan peminjaman alat laboratorium**, konfirmasi pengembalian & pemeriksaan kondisi alat, pengelolaan inventaris barang (CRUD, import massal Excel, penempatan barang ke ruangan), dan pengelolaan DIR.
- **Satpam:** Dashboard konfirmasi presensi masuk dan keluar mahasiswa peminjam lab, penugasan satpam piket, dan pencetakan bukti fisik presensi.
- **Mahasiswa:** Pengajuan izin peminjaman ruangan lab dan **peminjaman alat laboratorium (per NUP DIR / Manual)**, pemantauan status bertahap (_interactive timeline stepper_), pengajuan presensi masuk & keluar lab ke petugas satpam, serta unduh lembar bukti peminjaman PDF resmi ber-TTD.

---

### 2. 🛠️ Sistem Peminjaman Alat Laboratorium (Equipment Loan Management)

Fitur terpadu yang memfasilitasi mahasiswa dalam meminjam alat ukur, instrumen praktikum, dan perlengkapan inventaris lab dengan alur kerja terstruktur:

- **Pemilihan Alat Berbasis DIR & NUP Spesifik:**
  - Mengambil data alat langsung dari Daftar Inventaris Ruangan (DIR) pada laboratorium yang dipilih.
  - Setiap nomor inventaris (NUP) ditampilkan sebagai unit individual terpisah.
  - Jumlah alat terkunci otomatis sebesar **1 unit per NUP**, dan satuan otomatis mengikuti data inventaris aset.
- **Opsi Input Manual Fleksibel:**
  - Menyediakan opsi *Input Manual* untuk peralatan/komponen habis pakai atau instrumen pendukung yang belum tercatat pada buku induk inventaris DIR.
- **Pencegahan Konflik & Double-Booking NUP:**
  - Sistem secara otomatis memfilter dan mendeteksi ketersediaan fisik alat; NUP yang sedang dipinjam atau menunggu persetujuan tidak dapat dipilih oleh mahasiswa lain.
- **Formulir Repeater Multi-Item Ramah Mobile:**
  - Mahasiswa dapat meminjam beberapa alat sekaligus dalam satu formulir pengajuan.
  - Tata letak otomatis beradaptasi menjadi *Mobile Card Items* (`.alat-item-row`) pada layar sentuh dengan dukungan tema **Dark & Light Mode**.
- **Alur Persetujuan & Siklus Hidup Peminjaman Alat:**
  ```text
  [Mahasiswa Ajukan] -> pending_plp -> disetujui (Siap Diambil di Lab) -> sedang_dipinjam -> selesai (Verifikasi Kembali)
                                    \-> ditolak (dengan Catatan Penolakan Wajib)
  ```
- **Pemeriksaan Kondisi & Pengembalian:**
  - PLP mencatat konfirmasi serah terima kembali dan catatan kondisi fisik alat saat dikembalikan oleh mahasiswa.
- **Cetak Surat Bukti Peminjaman Alat (PDF):**
  - Dilengkapi fitur ekspor PDF bukti peminjaman alat resmi berisikan kode peminjaman unik, rincian daftar alat, NUP, ruang asal, spesifikasi, dan stempel tanda tangan digital mahasiswa & PLP.

---

### 3. 🏢 Alur Peminjaman Ruang Lab & Auto-Approval Bertingkat

- **Alur Persetujuan Bertingkat:** Normalnya melalui verifikasi PLP (`pending_plp`), lalu dilanjutkan ke Kalab (`pending_kalab`), hingga berstatus `disetujui`.
- **Per-Lab Kalab Auto-Approval:** Admin dapat mengaktifkan fitur auto-approval Kalab per laboratorium. Jika aktif, setelah PLP menyetujui permohonan, sistem langsung mengesahkan status peminjaman menjadi `disetujui` secara otomatis (syarat: Kalab telah terdaftar dan tanda tangan digitalnya sudah disetujui Admin).
- **Auto-Cancel & Expiration:** Scheduler otomatis membatalkan (_auto-cancel_) pengajuan peminjaman yang tidak diproses selama 24 jam atau jika waktu peminjaman yang diminta telah lewat. Peminjaman aktif yang telah melewati batas waktu selesai otomatis dialihkan menjadi `kadaluarsa`.

---

### 4. ⏱️ Presensi & Monitoring Kehadiran Laboratorium

- Mahasiswa mengajukan presensi masuk (_check-in_) dan keluar (_check-out_) dengan memilih petugas satpam yang sedang bertugas.
- Satpam menerima notifikasi dan memvalidasi presensi melalui menu khusus konfirmasi presensi.
- Satpam, PLP, Dosen, dan Admin dapat memonitor kehadiran di lab secara _real-time_ dan mencetak bukti presensi.

---

### 5. 📦 Manajemen Inventaris Barang & DIR (Daftar Inventaris Ruangan)

- **Master Inventaris Barang:** Pencatatan aset laboratorium secara terperinci (kode barang, NUP, nama barang, merk/tipe, kondisi, sumber dana, tanggal perolehan/buku).
- **Import Massal Excel (.xlsx):** Kemudahan impor data inventaris massal dengan template Excel yang dapat langsung diunduh dari aplikasi (didukung `Shuchkin\SimpleXLSX`).
- **Penempatan & Mutasi Barang:** Fitur penempatan barang dari master inventaris ke ruangan laboratorium tertentu secara parsial per NUP.
- **Daftar Inventaris Ruangan (DIR):** Laporan inventaris per laboratorium lengkap dengan fitur cetak / ekspor ke PDF resmi berformat standar.

---

### 6. 📊 Dashboard Cerdas & Terpadu (Dual-Tab System)

Dashboard untuk seluruh peran pengguna telah diselaraskan dengan integrasi peminjaman ruang lab dan alat:

- **Dashboard Mahasiswa:**
  - *Hero CTA Dropdown:* Pilihan cepat langsung membuat pengajuan *Pinjam Ruang Lab* atau *Pinjam Alat Lab*.
  - *Interactive Stepper:* Tab alur peminjaman ruang (6 langkah) dan alur peminjaman alat (5 langkah: TTD $\rightarrow$ Pengajuan $\rightarrow$ Approval PLP $\rightarrow$ Ambil Alat $\rightarrow$ Selesai).
  - *Spotlight Card:* Menampilkan sorotan status peminjaman ruang dan peminjaman alat aktif terkini.
  - *Statistik:* Akumulasi peminjaman dengan rincian `X Lab • Y Alat`.
- **Dashboard PLP:**
  - *Quick Shortcuts:* Akses cepat ke halaman persetujuan ruang lab dan persetujuan alat lab dilengkapi *counter badge* pengajuan pending.
  - *Antrean Approval & Fisik:* Menampilkan pengajuan alat yang menunggu persetujuan (*pulse warning highlight*) dan pemantauan jumlah alat yang sedang berada di luar / dipinjam.
  - *Tabel Dual-Tab:* Tab riwayat 5 pengajuan ruang lab dan 5 pengajuan alat lab terbaru.
- **Dashboard Admin:**
  - Ringkasan statistik sistem terpadu (Total Pengajuan Ruang & Alat, Antrean Pending, Sedang Digunakan, Total Selesai).
  - Navigasi cepat 6 modul utama SIMLAB dan tabel pengajuan sistem dual-tab.

---

### 7. 🔒 Flattened & Secure PDF Export

- Dokumen resmi surat peminjaman dikonversi menjadi gambar utuh (_flattened PDF_) beresolusi 200 DPI menggunakan `Imagick` & `Ghostscript`.
- Seluruh teks dan berkas tanda tangan digital terkunci rapat, tidak dapat diseleksi (_copy-paste_), dipisah, atau diekstrak, demi mencegah pemalsuan dokumen resmi.

---

### 8. ✍️ Manajemen & Verifikasi Tanda Tangan Digital

- Pengguna (Mahasiswa, PLP, Dosen/Kalab) mengunggah berkas tanda tangan digital melalui halaman profil secara instan tanpa memuat ulang halaman (AJAX).
- Tanda tangan digital wajib diverifikasi dan disetujui oleh Administrator sebelum pengajuan peminjaman dapat diproses.

---

### 9. 🔔 Notifikasi Real-Time (Web Push & WhatsApp)

- **Web Push API:** Notifikasi native browser/OS langsung ke perangkat pengguna yang bekerja di latar belakang tanpa dependensi Firebase/FCM.
- **Integrasi WhatsApp:** Dukungan pengiriman notifikasi otomatis via Fonnte API (dapat diaktifkan/dinonaktifkan melalui konfigurasi `.env`).

---

### 10. 📊 Rekapitulasi & Pelaporan Data

- Admin dapat menyaring dan mengekspor seluruh rekap peminjaman lab ke format **Excel (.xlsx)** (didukung oleh `SimpleXLSXGen`) dan **PDF**.
- Ekspor laporan Daftar Inventaris Ruangan (DIR) per lab ke PDF resmi.

---

### 11. 🔐 Keamanan & Pemulihan Akun

- **Verifikasi Registrasi Mahasiswa:** Mahasiswa yang mendaftar mandiri harus diverifikasi terlebih dahulu oleh Administrator sebelum dapat menggunakan akunnya.
- **Admin-Assisted Password Reset:** Pemulihan akun dilakukan melalui pembuatan token/link reset satu kali pakai yang digenerate oleh Admin dari dashboard untuk dibagikan ke pengguna (misal via WhatsApp).

---

## 🛠️ Technology Stack

| Komponen                      | Teknologi                                                         |
| ----------------------------- | ----------------------------------------------------------------- |
| **Framework Backend**         | [Laravel 12](https://laravel.com/) (PHP 8.2+)                     |
| **Database**                  | MySQL 8.0                                                         |
| **Frontend**                  | Blade Templates, Bootstrap 5 (Mazer Admin), Vanilla CSS, Vite, AJAX, Select2 |
| **Data Tables**               | Yajra Laravel DataTables (`yajra/laravel-datatables-oracle`)      |
| **Charts**                    | ApexCharts                                                        |
| **PDF Generation & Security** | `barryvdh/laravel-dompdf`, `ext-imagick`, `ghostscript`           |
| **Excel Handling**            | `shuchkin/simplexlsx` (Import), `shuchkin/simplexlsxgen` (Export) |
| **Web Push Notification**     | `minishlink/web-push` (VAPID API)                                 |
| **WhatsApp Gateway**          | Fonnte API (`App\Channels\WhatsAppChannel`)                       |
| **Environment**               | Docker & Docker Compose / Local Server (XAMPP / Laragon / Native) |

---

## 🗄️ Struktur Entitas Basis Data Utama (Database Models)

- `users`: Data pengguna (Mahasiswa, Dosen, PLP, Satpam, Admin) beserta path tanda tangan digital & status verifikasinya.
- `roles` & `jabatans`: Pengaturan peran dan jabatan struktural pengelola lab.
- `labs`: Data laboratorium kampus dan lokasinya.
- `lab_managers`: Relasi penugasan Kepala Lab (Kalab) dan PLP penanggung jawab pada tiap laboratorium.
- `peminjaman_lab`: Transaksi pengajuan peminjaman ruangan laboratorium.
- `presensi_lab`: Pencatatan kehadiran mahasiswa di laboratorium yang diverifikasi Satpam.
- `inventaris_barangs`: Master buku induk aset inventaris barang kampus.
- `inventaris_ruangans`: Alokasi penempatan barang inventaris ke ruangan laboratorium tertentu (DIR).
- `peminjaman_alats`: Master transaksi pengajuan peminjaman alat laboratorium.
- `peminjaman_alat_details`: Rincian item alat yang dipinjam (relasi ke DIR, kode barang, NUP, nama alat, jumlah, satuan, spesifikasi, dan catatan kondisi).
- `settings`: Konfigurasi sistem dinamis (Auto-Approval Kalab per lab).

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
- **Validasi Ketersediaan NUP Alat:** Sistem menjamin tidak ada tumpang tindih (_double-booking_) pada unit alat inventaris yang sama pada rentang waktu peminjaman.
- **Verifikasi Tanda Tangan Digital:** Pengunggahan berkas tanda tangan baru secara otomatis menggantikan berkas lama di media penyimpanan lokal secara aman dan mengubah status verifikasi menjadi `pending` hingga diverifikasi oleh Admin.
- **Protokol Reset Password Aman:** Menghindari celah reset email massal tanpa proteksi, reset kata sandi dikontrol oleh Admin yang menghasilkan tautan unik sekali pakai dengan batas kadaluarsa 60 menit.
- **Presensi Tervalidasi Petugas:** Mahasiswa tidak dapat memalsukan jam masuk/keluar karena waktu presensi diverifikasi dan dicatat langsung oleh petugas Satpam yang bertugas saat itu.
- **Automated Request Expiration:** Pengajuan peminjaman lab yang tertahan pada status `pending_plp` atau `pending_kalab` lebih dari 24 jam atau melewati waktu selesai otomatis dibatalkan, dan mahasiswa pemohon menerima pemberitahuan.

---

## 📄 Lisensi

Aplikasi Simlab dilisensikan di bawah lisensi terbuka [MIT License](https://opensource.org/licenses/MIT).
