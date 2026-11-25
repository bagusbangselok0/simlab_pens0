<p align="center">
  <h2>Simlab (Sistem Informasi Manajemen Laboratorium)</h2>
</p>

# Simlab (Sistem Informasi Manajemen Laboratorium)

## 📖 Deskripsi Aplikasi (Application Description)

**Simlab (Sistem Informasi Manajemen Laboratorium)** adalah aplikasi berbasis web yang dikembangkan menggunakan framework **Laravel 12** untuk mendigitalkan dan mengotomatisasi seluruh proses administrasi dan operasional laboratorium kampus. Sistem ini dirancang untuk menggantikan birokrasi dan pencatatan manual menjadi alur kerja yang terpusat, transparan, terintegrasi, dan aman bagi 5 peran pengguna: **Administrator**, **Dosen (Kalab & Dosen Pengampu)**, **Pranata Laboratorium Pendidikan (PLP)**, **Petugas Keamanan (Satpam)**, dan **Mahasiswa**.

Melalui Simlab:

- **Mahasiswa** dapat mengajukan izin peminjaman ruangan laboratorium maupun peminjaman peralatan/instrumen inventaris secara daring, memantau riwayat pengajuan secara bertahap via *stepper timeline*, mengajukan presensi kehadiran di lab, mengunduh surat bukti izin peminjaman resmi berformat PDF, serta membubuhkan tanda tangan perwakilan mahasiswa pada logbook praktikum via Signature Pad atau QR link.
- **PLP** dapat meninjau & menyetujui permohonan peminjaman ruangan dan peralatan, mengelola inventaris master barang dan Daftar Inventaris Ruangan (DIR), mengelola **Jadwal Pemakaian Lab** per periode (import Excel / input manual beserta deteksi bentrok), mengisi **Logbook Pelaksanaan Praktikum (`FM. LAB-02 Rev.00`)** beserta TTD perwakilan mahasiswa, memantau kehadiran mahasiswa secara _real-time_, serta mengesahkan serah terima dan pengembalian alat.
- **Dosen** memiliki hak akses terpisah antara **Kepala Laboratorium (Kalab)** (`Approval Lab`, `Monitoring Presensi`, `DIR`, serta persetujuan akhir peminjaman ruang) dan **Dosen Pengampu Praktikum** (Dashboard khusus Dosen Pengampu, **Jadwal Lab**, serta **Approval Logbook Praktikum** secara manual maupun Auto-Approve berbasis izin eksplisit).
- **Satpam** memiliki modul khusus untuk mengonfirmasi kehadiran (_check-in_ & _check-out_) mahasiswa yang sedang beraktivitas di laboratorium dan mencetak bukti fisik presensi.
- **Admin** memiliki kendali penuh terhadap manajemen master data (users, **jabatan**, laboratorium/ruangan dengan flag `bisa_dipinjam_ruangan`, penanggung jawab lab, dan pengaturan sistem terpusat), verifikasi pendaftaran akun mahasiswa baru, verifikasi tanda tangan digital, pembuatan link reset password sekali pakai, manajemen jadwal & logbook, serta rekap ekspor data peminjaman.

Dokumen surat peminjaman lab dicetak dalam format **Flattened PDF** (dokumen dan tanda tangan digital dikunci menjadi citra utuh beresolusi tinggi via `Imagick` & `Ghostscript`), sehingga isi surat dan tanda tangan tidak dapat dimanipulasi, diekstrak, maupun disalahgunakan.

---

## 🚀 Fitur Utama (Key Features)

### 1. 👥 Multi-Role Access Control (5 Role + Pemisahan Kalab & Dosen Pengampu)

- **Admin:** Manajemen pengguna, **master Jabatan (`jabatans`)**, master laboratorium & ruang teknis (`bisa_dipinjam_ruangan`), pejabat pengelola lab (_Lab Manager_), verifikasi akun mahasiswa baru, verifikasi tanda tangan digital, **Pengaturan Sistem terpusat** (Auto-Approval Kalab per lab, batas peminjaman alat, Gateway WhatsApp & Web Push + test kirim, dan kustomisasi tema/loader/sidebar), rekap dan ekspor data peminjaman (Excel & PDF), serta pengawasan seluruh modul termasuk Jadwal Lab dan Logbook Praktikum.
- **Dosen — Kepala Laboratorium (Kalab):** Teridentifikasi melalui `User::isKalab()` (jabatan `kalab` atau ditugaskan sebagai `kalab_id` di `lab_managers`). Berwenang atas persetujuan peminjaman ruang lab tahap akhir (_Kalab Approval_), monitoring presensi lab binaan, pengelolaan Daftar Inventaris Ruangan (DIR) & ekspor PDF, serta Approval Logbook bila mengampu praktikum.
- **Dosen — Dosen Pengampu Praktikum (Non-Kalab):** Memiliki tampilan **Dashboard Dosen Pengampu** tersendiri, akses melihat & mencetak **Jadwal Pemakaian Lab**, serta menu **Approval Logbook** untuk meninjau, menandatangani (satuan / bulk), menolak dengan catatan revisi, mencabut TTD (*Revoke*), dan mengatur Auto-Approve atas izin dosen. Rute khusus Kalab (`Approval Lab`, `Inventaris Ruangan`, `Monitoring Presensi`) terlindungi dari akses Dosen Non-Kalab.
- **PLP (Pranata Laboratorium Pendidikan):** Persetujuan peminjaman ruang lab tahap awal (_PLP Approval_), verifikasi dan persetujuan peminjaman alat laboratorium, konfirmasi pengembalian & pemeriksaan kondisi alat, pengelolaan inventaris barang (CRUD, import massal Excel, penempatan ke ruangan) & DIR, pengelolaan **Jadwal Pemakaian Lab**, serta pengisian dan pengajuan **Logbook Pelaksanaan Praktikum (`FM. LAB-02 Rev.00`)**.
- **Satpam:** Dashboard konfirmasi presensi masuk dan keluar mahasiswa peminjam lab, penugasan satpam piket, pencetakan bukti fisik presensi, serta akses melihat Jadwal Lab.
- **Mahasiswa:** Pengajuan izin peminjaman ruangan lab dan peminjaman alat laboratorium (per NUP DIR / Manual), pemantauan status bertahap (_interactive timeline stepper_), pengajuan presensi masuk & keluar lab, unduh bukti peminjaman PDF resmi ber-TTD, akses melihat Jadwal Lab, serta pembubuhan TTD perwakilan mahasiswa logbook melalui tautan QR publik.

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

### 6. 📅 Manajemen Jadwal Pemakaian Laboratorium

- **Akses Lintas Role:** Seluruh pengguna terautentikasi dapat melihat jadwal pemakaian lab aktif dan mencetaknya ke PDF/Excel; pembuatan, import, serta pengelolaan periode dikendalikan oleh **Admin & PLP**.
- **Import Jadwal Perkuliahan (Preview & Commit):** Upload file jadwal Excel (`.xlsx`) per periode (`PeriodeJadwal`) melalui `JadwalPerkuliahanParser` dengan halaman *preview* sebelum *commit*, pencocokan otomatis kode ruangan ke `labs` dan nama dosen ke akun `users`, serta deteksi bentrok jadwal antar entri secara otomatis.
- **Pengelolaan Periode & Entri Manual:** Mendukung aktivasi periode jadwal aktif, tambah/edit/hapus slot pemakaian per ruangan, serta penandaan konflik jadwal di antarmuka.
- **Ekspor PDF & Excel (`.xlsx`) Rapi:** Ekspor per ruangan atau semua ruangan ke format PDF dan Excel dengan tabel baris berselang-seling (*striped rows*), kolom catatan yang otomatis disembunyikan bila kosong seluruhnya dalam satu ruangan, serta blok keterangan jam perkuliahan Senin–Kamis & Jum'at.

---

### 7. 📓 Logbook Pelaksanaan Praktikum PLP (`FM. LAB-02 Rev.00`) & Approval Dosen

- **Sinkronisasi 1-Klik dari Jadwal Aktif:** PLP dapat membuat banyak buku logbook sekaligus dari entri `jadwal_labs` pada periode aktif (dengan filter Praktikum / Teori & Praktek) atau membuat buku secara manual untuk laboratorium kelolaannya (`LabManager`).
- **Pintasan Praktikum Hari Ini & Generator Mingguan:** Halaman utama logbook menampilkan kartu **Praktikum Hari Ini** sesuai hari berjalan beserta pintasan *Quick Fill*, generator pertemuan mingguan otomatis yang menyelaraskan tanggal ke hari jadwal kuliah (+7 hari per minggu), fitur duplikat pertemuan, serta manajemen **Preset Template Aktivitas** (`plp_aktivitas_templates`).
- **Struktur Resmi FM. LAB-02:**
  - **Bagian A — Peminjaman Alat & Bahan:** Pilihan kategori (`Alat 1`, `Alat 2`, `Alat 3`, `Bahan Umum`, `Bahan Khusus`), jumlah, kondisi (`Baik`/`Rusak`), serta autocomplete saran nama alat dari Daftar Inventaris Ruangan (DIR).
  - **Bagian B — Aktivitas Praktikum** & **Bagian C — Permasalahan & Solusi**.
- **Hybrid TTD Perwakilan Mahasiswa:** Mendukung TTD default perwakilan mahasiswa untuk 1 semester atau override per pertemuan lewat:
  1. **Signature Pad Langsung** di modal (atau memakai file TTD profil mahasiswa yang telah disetujui Admin).
  2. **QR Code / Temporary Signed URL** (berlaku 30 menit, akses publik tanpa login, kanvas responsif sentuh yang tahan terhadap perubahan tinggi viewport keyboard HP).
- **Alur Approval Dosen (Manual & Auto-Approve Atas Izin Dosen):**
  - Pengajuan satuan atau massal (*bulk submit*) oleh PLP.
  - Peninjauan oleh Dosen Pengampu di menu **Approval Logbook**: preview detail pertemuan, preview PDF, Setuju & TTD satuan, **Bulk Approve**, Tolak dengan catatan revisi wajib, serta **Batalkan TTD (Revoke)** untuk menarik kembali pengesahan.
  - **Auto-Approve Logbook (`dosen_logbook_auto_approves`):** Dapat diaktifkan oleh Dosen (memerlukan TTD digital terverifikasi Admin + centang persetujuan *consent* eksplisit) dengan cakupan global (semua PLP) atau per PLP dan opsional batas tanggal berlaku.
- **Kunci Integritas Data & Cetak PDF Resmi:** Pertemuan berstatus `pending_dosen` maupun `approved` terkunci dari perubahan/hapus; pertemuan `approved` mengunci pembaruan TTD mahasiswa, penghapusan buku, dan penggantian `dosen_id`. Cetak PDF A4 Portrait mengikuti tata letak resmi **FM. LAB-02 Rev.00** (per pertemuan atau seluruh buku).

---

### 8. 📊 Dashboard Cerdas & Terpadu (Dual-Tab & Pemisahan Kalab/Dosen)

Dashboard untuk seluruh peran pengguna telah diselaraskan dengan integrasi peminjaman ruang lab, peminjaman alat, dan logbook praktikum:

- **Dashboard Mahasiswa:**
  - *Hero CTA Dropdown:* Pilihan cepat langsung membuat pengajuan *Pinjam Ruang Lab* atau *Pinjam Alat Lab*.
  - *Interactive Stepper:* Tab alur peminjaman ruang (6 langkah) dan alur peminjaman alat (5 langkah: TTD $\rightarrow$ Pengajuan $\rightarrow$ Approval PLP $\rightarrow$ Ambil Alat $\rightarrow$ Selesai).
  - *Spotlight Card:* Menampilkan sorotan status peminjaman ruang dan peminjaman alat aktif terkini.
  - *Statistik:* Akumulasi peminjaman dengan rincian `X Lab • Y Alat`.
- **Dashboard PLP:**
  - *Quick Shortcuts:* Akses cepat ke halaman persetujuan ruang lab, persetujuan alat lab, dan logbook praktikum dilengkapi *counter badge* pengajuan pending.
  - *Antrean Approval & Fisik:* Menampilkan pengajuan alat yang menunggu persetujuan (*pulse warning highlight*) dan pemantauan jumlah alat yang sedang berada di luar / dipinjam.
  - *Tabel Dual-Tab:* Tab riwayat 5 pengajuan ruang lab dan 5 pengajuan alat lab terbaru.
- **Dashboard Dosen (Kalab vs Dosen Pengampu Non-Kalab):**
  - **Kalab (`$user->isKalab()`):** Menampilkan statistik laboratorium binaan (total lab, peminjam, belum presensi hari ini, sedang di lab), grafik tren & status peminjaman ApexCharts, serta pengajuan terbaru.
  - **Dosen Pengampu (Non-Kalab):** Menampilkan area khusus Dosen Pengampu berisi status TTD digital, ringkasan **Logbook Menunggu TTD**, **Logbook Disetujui**, **MK Praktikum Diampu**, status **Auto-Approve Logbook**, daftar antrean logbook terbaru, serta pintasan ke **Approval Logbook** & **Jadwal Lab**.
- **Dashboard Admin:**
  - Ringkasan statistik sistem terpadu (Total Pengajuan Ruang & Alat, Antrean Pending, Sedang Digunakan, Total Selesai).
  - Navigasi cepat modul utama SIMLAB dan tabel pengajuan sistem dual-tab.

---

### 9. 🔒 Flattened & Secure PDF Export

- Dokumen resmi surat peminjaman dikonversi menjadi gambar utuh (_flattened PDF_) beresolusi 200 DPI menggunakan `Imagick` & `Ghostscript`.
- Seluruh teks dan berkas tanda tangan digital terkunci rapat, tidak dapat diseleksi (_copy-paste_), dipisah, atau diekstrak, demi mencegah pemalsuan dokumen resmi.

---

### 10. ✍️ Manajemen & Verifikasi Tanda Tangan Digital

- Pengguna (Mahasiswa, PLP, Dosen/Kalab) mengunggah berkas tanda tangan digital melalui halaman profil secara instan tanpa memuat ulang halaman (AJAX).
- Tanda tangan digital wajib diverifikasi dan disetujui oleh Administrator sebelum pengajuan peminjaman atau pengesahan/Auto-Approve Logbook dapat diproses.

---

### 11. 🔔 Notifikasi Real-Time (Web Push & WhatsApp)

- **Web Push API:** Notifikasi native browser/OS langsung ke perangkat pengguna yang bekerja di latar belakang tanpa dependensi Firebase/FCM.
- **Integrasi WhatsApp:** Dukungan pengiriman notifikasi otomatis via Fonnte API (dapat dikonfigurasi via UI **Pengaturan Sistem** maupun `.env`).

---

### 12. 📊 Rekapitulasi & Pelaporan Data

- Admin dapat menyaring dan mengekspor seluruh rekap peminjaman lab ke format **Excel (.xlsx)** (`SimpleXLSXGen`) dan **PDF**.
- Ekspor laporan **Daftar Inventaris Ruangan (DIR)** per lab ke PDF resmi.
- Ekspor **Jadwal Pemakaian Lab** per ruangan/keseluruhan ke format **PDF** dan **Excel (.xlsx)**.
- Ekspor **Logbook Praktikum PLP (`FM. LAB-02 Rev.00`)** per pertemuan atau satu buku semester lengkap ke format **PDF**.

---

### 13. 🔐 Keamanan & Pemulihan Akun

- **Verifikasi Registrasi Mahasiswa:** Mahasiswa yang mendaftar mandiri harus diverifikasi terlebih dahulu oleh Administrator sebelum dapat menggunakan akunnya.
- **Admin-Assisted Password Reset:** Pemulihan akun dilakukan melalui pembuatan token/link reset satu kali pakai yang digenerate oleh Admin dari menu Users untuk dibagikan ke pengguna (misal via WhatsApp).

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
| **Testing**                   | PHPUnit 11 (`phpunit/phpunit`)                                    |
| **Environment**               | Docker & Docker Compose / Local Server (XAMPP / Laragon / Native) |

---

## 🗄️ Struktur Entitas Basis Data Utama (Database Models)

- `users`: Data pengguna (Mahasiswa, Dosen, PLP, Satpam, Admin) beserta `jabatan_id`, path tanda tangan digital, & status verifikasinya.
- `roles` & `jabatans`: Pengaturan peran akses (`admin`, `dosen`, `plp`, `satpam`, `mahasiswa`) serta master jabatan struktural dengan `slug` unik (`kadep`, `kaprodi`, `kalab`, `plp`, `satpam`, dll.) yang dapat dikelola via CRUD Admin.
- `labs`: Data laboratorium & ruangan beserta lokasi serta flag `bisa_dipinjam_ruangan`.
- `lab_managers`: Relasi penugasan Kepala Lab (Kalab, opsional/nullable) dan PLP penanggung jawab pada tiap laboratorium.
- `peminjaman_lab`: Transaksi pengajuan peminjaman ruangan laboratorium.
- `presensi_lab`: Pencatatan kehadiran mahasiswa di laboratorium yang diverifikasi Satpam.
- `inventaris_barangs`: Master buku induk aset inventaris barang kampus.
- `inventaris_ruangans`: Alokasi penempatan barang inventaris ke ruangan laboratorium tertentu (DIR).
- `peminjaman_alats` & `peminjaman_alat_details`: Master & rincian item alat yang dipinjam (relasi ke DIR, kode barang, NUP, nama alat, jumlah, satuan, spesifikasi, dan catatan kondisi).
- `periode_jadwals` & `jadwal_labs`: Master periode semester/tahun ajaran dan entri jadwal pemakaian laboratorium per ruangan.
- `logbook_praktikums` & `logbook_pertemuans`: Buku logbook mata kuliah praktikum PLP (`FM. LAB-02`) serta catatan per pertemuan (Peralatan Bagian A, Aktivitas Bagian B, Permasalahan Bagian C, TTD mahasiswa, status approval manual/auto, & riwayat revoke).
- `dosen_logbook_auto_approves`: Aturan izin *Auto-Approve* logbook per dosen (cakupan global atau per PLP, `consented_at`, dan `valid_until`).
- `plp_aktivitas_templates`: Preset template daftar aktivitas praktikum milik masing-masing PLP.
- `settings`: Konfigurasi sistem dinamis (Auto-Approval Kalab per lab, batas peminjaman alat, Gateway WhatsApp & Web Push, dan preferensi tema/loader/sidebar).

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

## 🔔 Konfigurasi Web Push, WhatsApp Gateway & Pengaturan Sistem

Selain melalui file `.env` dan Artisan CLI, Administrator kini dapat mengelola sekaligus menguji gateway notifikasi maupun preferensi antarmuka langsung dari menu **Data Master > Pengaturan Sistem**:

### A. Web Push Notification (VAPID)

1. Generate pasangan VAPID keys melalui tombol **"Generate VAPID Keys"** di halaman **Pengaturan Sistem**, atau via CLI:
    ```bash
    php artisan vapid:generate
    php artisan config:clear
    ```
2. Pengguna dapat mengaktifkan push notification dengan menekan tombol **"Push"** di samping ikon bel notifikasi di navbar kanan atas.
3. Admin dapat melakukan uji pengiriman langsung lewat tombol **Test Web Push** di halaman Pengaturan Sistem.

### B. WhatsApp Notification (Fonnte API - Opsional)

Jika ingin mengaktifkan notifikasi via WhatsApp, sesuaikan melalui menu **Data Master > Pengaturan Sistem** (tersedia tombol **Test WhatsApp** & toggle per jenis notifikasi) atau melalui variabel berikut di file `.env`:

```env
WA_ENABLED=true
FONNTE_TOKEN=your_fonnte_token_here
```

Daftar dan dapatkan token API di [Fonnte](https://fonnte.com/). Jika diset `false`, sistem tetap berjalan normal dan hanya menggunakan notifikasi in-app database serta web push.

### C. Pengaturan Tambahan di Pengaturan Sistem

- **Peminjaman Alat:** Batas maksimal item per pengajuan, durasi maksimal hari peminjaman, maksimal pinjaman aktif, dan izin input manual alat.
- **Tema, Loader & Sidebar:** Mode tampilan default (`light`/`dark`), aktif/kecepatan halaman loader, warna aksen UI, serta state default sidebar (`expanded`/`collapsed`).

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
- **Integritas Logbook Praktikum (`FM. LAB-02`):** Pertemuan yang berstatus `pending_dosen` atau `approved` dikunci dari penyuntingan/hapus; perubahan TTD mahasiswa, perubahan `dosen_id`, maupun penghapusan buku dikunci setelah ada pertemuan berstatus `approved`.
- **Pemisahan Akses Kalab vs Dosen Pengampu:** Rute khusus Kalab (`Approval Lab`, `Inventaris Ruangan`, `Monitoring Presensi`) dilindungi dari akun dosen non-Kalab, sementara Dosen Pengampu tetap memiliki akses **Approval Logbook** dan **Jadwal Lab**.
- **Proteksi Penghapusan Master Jabatan:** Jabatan yang masih terikat pada akun pengguna tidak dapat dihapus sampai relasi user-nya dialihkan terlebih dahulu.
- **Verifikasi Tanda Tangan Digital:** Pengunggahan berkas tanda tangan baru secara otomatis menggantikan berkas lama di media penyimpanan lokal secara aman dan mengubah status verifikasi menjadi `pending` hingga diverifikasi oleh Admin.
- **Protokol Reset Password Aman:** Menghindari celah reset email massal tanpa proteksi, reset kata sandi dikontrol oleh Admin yang menghasilkan tautan unik sekali pakai dengan batas kadaluarsa 60 menit.
- **Presensi Tervalidasi Petugas:** Mahasiswa tidak dapat memalsukan jam masuk/keluar karena waktu presensi diverifikasi dan dicatat langsung oleh petugas Satpam yang bertugas saat itu.
- **Automated Request Expiration:** Pengajuan peminjaman lab yang tertahan pada status `pending_plp` atau `pending_kalab` lebih dari 24 jam atau melewati waktu selesai otomatis dibatalkan, dan mahasiswa pemohon menerima pemberitahuan.

---

## 🧪 Pengujian Otomatis (Automated Testing)

Simlab dilengkapi feature test berbasis PHPUnit (menggunakan SQLite `:memory:` sesuai konfigurasi `phpunit.xml`), mencakup antara lain `JabatanCrudTest`, `JadwalLabTest`, `LogbookPraktikumTest`, `InventarisBarangTest`, `InventarisRuanganTest`, `PresensiAutoGenerationTest`, `PresensiNotificationTest`, `SignatureVerificationTest`, dan `ExpirePeminjamanLabTest`.

Jalankan seluruh pengujian:

```bash
php artisan test
```

Atau jalankan test spesifik per modul:

```bash
php artisan test --filter=LogbookPraktikumTest
php artisan test --filter=JadwalLabTest
php artisan test --filter=JabatanCrudTest
```

---

## 📄 Lisensi

Aplikasi Simlab dilisensikan di bawah lisensi terbuka [MIT License](https://opensource.org/licenses/MIT).
