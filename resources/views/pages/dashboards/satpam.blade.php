@extends('layouts.app')

@section('styles')
    <style>
        .dashboard-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 24px;
            background: linear-gradient(135deg, #0f766e 0%, #2563eb 48%, #7c3aed 100%);
            color: #ffffff;
            box-shadow: 0 18px 40px rgba(37, 99, 235, 0.22);
        }

        .dashboard-hero::before,
        .dashboard-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 70%);
        }

        .dashboard-hero::before {
            width: 280px;
            height: 280px;
            top: -90px;
            right: -40px;
        }

        .dashboard-hero::after {
            width: 220px;
            height: 220px;
            bottom: -80px;
            left: -30px;
        }

        .dashboard-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.22);
        }

        .dashboard-chip-soft {
            background: rgba(255, 255, 255, 0.14);
        }

        .dashboard-header-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            justify-content: flex-start;
        }

        .dashboard-section-card .card-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .dashboard-section-card .card-header h4 {
            margin-bottom: 0;
            flex: 1 1 220px;
        }

        .dashboard-stat-card .card-body {
            padding: 1.15rem 1.15rem 1.15rem;
        }

        .dashboard-stat-card .d-flex {
            align-items: flex-start;
            gap: 0.85rem;
        }

        .dashboard-list-item {
            display: flex;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 0.75rem;
            border-radius: 14px;
            padding: 0.9rem 1rem;
            margin: 0.6rem 0 0;
            background: rgba(248, 250, 252, 0.9);
        }

        .dashboard-list-item:first-child {
            margin-top: 0;
        }

        .dashboard-list-item:last-child {
            margin-bottom: 0;
        }

        .dashboard-list-item .name {
            min-width: 0;
            flex: 1 1 220px;
        }

        .table-responsive .table {
            min-width: 700px;
        }

        .highlight-pending-card {
            border: 2px solid #ff9f43 !important;
            background-color: #fffaf0 !important;
            position: relative;
            animation: pulse-border-warning 2s infinite;
        }

        .highlight-pending-card::after {
            content: "Perlu Tindakan";
            position: absolute;
            top: -10px;
            right: 15px;
            background-color: #ff9f43;
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(255, 159, 67, 0.4);
            text-transform: uppercase;
        }

        @keyframes pulse-border-warning {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 159, 67, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 159, 67, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 159, 67, 0);
            }
        }

        [data-bs-theme="dark"] .highlight-pending-card {
            background-color: #2b1d0c !important;
            border-color: #ff9f43 !important;
        }

        [data-bs-theme="dark"] .highlight-pending-card .text-muted {
            color: #ffd8a8 !important;
        }

        [data-bs-theme="dark"] .highlight-pending-card .font-extrabold {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .dashboard-section-card {
            background-color: #111827;
            color: #f8fafc;
            box-shadow: 0 8px 24px rgba(2, 6, 23, 0.35);
        }

        [data-bs-theme="dark"] .dashboard-section-card .card-header {
            border-color: rgba(255, 255, 255, 0.08);
            color: #f8fafc;
        }

        [data-bs-theme="dark"] .dashboard-list-item {
            background: rgba(15, 23, 42, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        [data-bs-theme="dark"] .dashboard-list-item h6,
        [data-bs-theme="dark"] .dashboard-list-item .name {
            color: #f8fafc;
        }

        [data-bs-theme="dark"] .dashboard-list-item .text-muted {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .dashboard-list-item .rounded,
        [data-bs-theme="dark"] .dashboard-list-item .rounded-circle {
            background: rgba(59, 130, 246, 0.16) !important;
        }

        .dashboard-section-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .dashboard-section-card .card-header {
            border-bottom: 1px solid rgba(15, 23, 42, 0.05);
            background: transparent;
        }

        .dashboard-stat-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.09);
        }

        .dashboard-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .dashboard-stat-number {
            font-size: clamp(1.2rem, 1.7vw, 1.55rem);
            line-height: 1.1;
        }

        .stat-subtext {
            font-size: 0.78rem;
            margin-top: 0.35rem;
        }
    </style>
@endsection

@section('content')
    {{-- ===== HEADER SELAMAT DATANG ===== --}}
    <div class="card border-0 shadow-sm dashboard-hero mb-4">
        <div class="card-body p-4 p-md-5 position-relative">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3">
                <div class="flex-grow-1">
                    <span class="dashboard-chip mb-3">
                        <i class="bi bi-shield-check me-2"></i>Area Verifikasi Satpam
                    </span>
                    <h3 class="fw-bold mb-2">Halo, {{ $user->full_name }} 👋</h3>
                    <p class="mb-0 text-white-50">Pantau presensi laboratorium, verifikasi aktivitas mahasiswa, dan kelola
                        status keamanan laboratorium secara real-time.</p>
                </div>
                <div class="dashboard-header-meta w-100 w-lg-auto">
                    <span id="dashboard-date" class="dashboard-chip dashboard-chip-soft">
                        <i
                            class="bi bi-calendar3 me-2"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                    </span>
                    <span id="dashboard-time" class="dashboard-chip dashboard-chip-soft">
                        <i class="bi bi-clock-history me-2"></i>{{ \Carbon\Carbon::now()->translatedFormat('H:i') }} WIB
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== BARIS 1: KARTU STATISTIK OPERASIONAL PRESENSI ===== --}}
    <section class="row g-3 mb-3">
        {{-- Presensi Masuk Hari Ini --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon" style="background: #e7f1ff; color: #2563eb;">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Presensi Masuk Hari Ini</h6>
                            <h4 class="font-extrabold mb-0 dashboard-stat-number">{{ $presensi_hari_ini }}</h4>
                            <div class="stat-subtext text-muted">
                                Dari <span class="fw-semibold text-body">{{ $total_jadwal_hari_ini }}</span> jadwal hari ini
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Menunggu Konfirmasi --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100 {{ $presensi_menunggu > 0 ? 'highlight-pending-card' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon" style="background: #fff8e6; color: #d97706;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Menunggu Konfirmasi</h6>
                            <h4 class="font-extrabold mb-0 dashboard-stat-number">{{ $presensi_menunggu }}</h4>
                            <div class="stat-subtext">
                                @if ($presensi_menunggu > 0)
                                    <span class="text-warning fw-bold"><i class="bi bi-exclamation-circle me-1"></i>Perlu
                                        tindakan segera</span>
                                @else
                                    <span class="text-success"><i class="bi bi-check2 me-1"></i>Semua terverifikasi</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mahasiswa di Dalam Lab --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon" style="background: #e8f8ee; color: #198754;">
                            <i class="bi bi-door-open"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Mahasiswa di Dalam Lab</h6>
                            <h4 class="font-extrabold mb-0 dashboard-stat-number">{{ $mahasiswa_didalam }}</h4>
                            <div class="stat-subtext text-muted">
                                Aktif di ruangan saat ini
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lab Sedang Digunakan --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon" style="background: #f0edff; color: #6f42c1;">
                            <i class="bi bi-building-check"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Lab Sedang Digunakan</h6>
                            <h4 class="font-extrabold mb-0 dashboard-stat-number">{{ $lab_digunakan_count }}</h4>
                            <div class="stat-subtext text-muted">
                                Dari <span class="fw-semibold text-body">{{ $labs->count() }}</span> total lab terdaftar
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 2: STATISTIK KONFIRMASI SAYA ===== --}}
    <section class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon" style="background: #eef2ff; color: #4f46e5;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Total Konfirmasi Saya</h6>
                            <h4 class="font-extrabold mb-0 dashboard-stat-number">{{ $total_konfirmasi_saya }}</h4>
                            <div class="stat-subtext text-muted">Akumulasi seluruh verifikasi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon" style="background: #e7f1ff; color: #2563eb;">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Konfirmasi Masuk</h6>
                            <h4 class="font-extrabold mb-0 dashboard-stat-number">{{ $konfirmasi_masuk_saya }}</h4>
                            <div class="stat-subtext text-muted">Izin masuk diverifikasi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon" style="background: #ffeef0; color: #dc3545;">
                            <i class="bi bi-box-arrow-left"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Konfirmasi Keluar</h6>
                            <h4 class="font-extrabold mb-0 dashboard-stat-number">{{ $konfirmasi_keluar_saya }}</h4>
                            <div class="stat-subtext text-muted">Izin keluar diverifikasi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon" style="background: #e8f8ee; color: #198754;">
                            <i class="bi bi-calendar2-day"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Konfirmasi Hari Ini</h6>
                            <h4 class="font-extrabold mb-0 dashboard-stat-number">{{ $konfirmasi_saya_hari_ini }}</h4>
                            <div class="stat-subtext text-muted">Aktivitas shift hari ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 3: PRESENSI MENUNGGU + MAHASISWA DI DALAM ===== --}}
    <section class="row g-3 mb-3">
        {{-- Presensi Menunggu Konfirmasi --}}
        <div class="col-12 col-xl-7">
            <div class="card dashboard-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h4 class="mb-0 fs-6 fs-sm-5 fw-bold">
                        Presensi Menunggu Konfirmasi
                        @if ($presensi_menunggu > 0)
                            <span class="badge bg-warning text-dark ms-2">{{ $presensi_menunggu }} Menunggu</span>
                        @endif
                    </h4>
                    <a href="{{ route('satpam.presensi') }}" class="btn btn-sm btn-primary rounded-pill px-3">Kelola
                        Semua</a>
                </div>
                <div class="card-body p-0">
                    @if ($presensi_list_menunggu->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mahasiswa</th>
                                        <th>Laboratorium</th>
                                        <th>Jenis</th>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($presensi_list_menunggu as $presensi)
                                        <tr>
                                            <td>
                                                <span
                                                    class="fw-semibold">{{ $presensi->mahasiswa->full_name ?? '-' }}</span><br>
                                                <small class="text-muted">{{ $presensi->mahasiswa->email ?? '' }}</small>
                                            </td>
                                            <td><small
                                                    class="fw-semibold">{{ $presensi->peminjamanLab->lab->nama_lab . ' (' . $presensi->peminjamanLab->lab->kode_lab . ')' ?? '-' }}</small>
                                            </td>
                                            <td>
                                                @if ($presensi->status_presensi === 'menunggu_konfirmasi_masuk')
                                                    <span class="badge bg-primary">Masuk</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Keluar</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $presensi->created_at->format('H:i') }} WIB</small></td>
                                            <td>
                                                @if (
                                                    (auth()->user()->id === $presensi->satpamMasuk?->id &&
                                                        $presensi->status_presensi === 'menunggu_konfirmasi_masuk') ||
                                                        (auth()->user()->id === $presensi->satpamKeluar?->id &&
                                                            $presensi->status_presensi === 'menunggu_konfirmasi_keluar'))
                                                    <a href="{{ route('satpam.presensi') }}"
                                                        class="btn btn-xs btn-success rounded-pill px-2">
                                                        <i class="bi bi-check-circle"></i> Konfirmasi
                                                    </a>
                                                @else
                                                    <span class="text-muted small">Bukan giliran Anda</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem;"></i>
                            <h6 class="mt-3 text-muted">Tidak ada presensi menunggu konfirmasi saat ini</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mahasiswa Sedang di Dalam Lab --}}
        <div class="col-12 col-xl-5">
            <div class="card dashboard-section-card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fs-6 fs-sm-5 fw-bold">Mahasiswa di Dalam Lab</h4>
                    <span class="badge bg-success rounded-pill px-2 py-1">{{ $mahasiswa_didalam }} Aktif</span>
                </div>
                <div class="card-content pb-3 px-3">
                    @forelse($mahasiswa_didalam_list as $presensi)
                        <div class="dashboard-list-item d-flex align-items-start">
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                style="width:40px; height:40px; background:#e8f8ee; flex-shrink:0;">
                                <i class="bi bi-person-fill text-success fs-5"></i>
                            </div>
                            <div class="name ms-3">
                                <h6 class="mb-0 font-bold">{{ $presensi->mahasiswa->full_name ?? '-' }}</h6>
                                <small class="text-muted d-block">
                                    <i class="bi bi-building me-1"></i>
                                    {{ $presensi->peminjamanLab->lab->nama_lab . '(' . $presensi->peminjamanLab->lab->kode_lab . ')' ?? '-' }}
                                </small>
                                <small class="text-success fw-semibold">
                                    <i class="bi bi-clock me-1"></i> Masuk:
                                    {{ $presensi->jam_masuk ? $presensi->jam_masuk->format('H:i') : '-' }} WIB
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 px-4">
                            <i class="bi bi-door-closed text-muted" style="font-size: 2.2rem;"></i>
                            <p class="text-muted mt-2 mb-0">Tidak ada mahasiswa yang sedang berada di lab.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 4: GRAFIK ANALISA OPERASIONAL SATPAM ===== --}}
    <section class="row g-3 mb-3">
        {{-- Grafik Tren Presensi 7 Hari Terakhir --}}
        <div class="col-12 col-xl-8">
            <div class="card dashboard-section-card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fs-6 fs-sm-5 fw-bold">Tren Lalu Lintas Presensi (7 Hari Terakhir)</h4>
                        <small class="text-muted">Perbandingan frekuensi mahasiswa masuk vs keluar lab per hari</small>
                    </div>
                    <span class="badge bg-light-primary text-primary rounded-pill px-3 py-2">
                        <i class="bi bi-activity me-1"></i>Aktivitas Mingguan
                    </span>
                </div>
                <div class="card-body">
                    <div id="chart-tren-satpam"></div>
                </div>
            </div>
        </div>

        {{-- Grafik Distribusi Status Presensi Hari Ini --}}
        <div class="col-12 col-xl-4">
            <div class="card dashboard-section-card h-100">
                <div class="card-header py-3">
                    <h4 class="mb-0 fs-6 fs-sm-5 fw-bold">Status Kehadiran Hari Ini</h4>
                    <small class="text-muted">Proporsi aktivitas presensi hari ini</small>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div id="chart-status-satpam"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 5: GRAFIK LAB & DAFTAR LAB ===== --}}
    <section class="row g-3 mb-3">
        {{-- Grafik Presensi per Lab --}}
        <div class="col-12 col-xl-8">
            <div class="card dashboard-section-card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fs-6 fs-sm-5 fw-bold">Akumulasi Presensi per Laboratorium</h4>
                        <small class="text-muted">Laboratorium dengan frekuensi kunjungan tertinggi</small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chart-presensi-per-lab"></div>
                </div>
            </div>
        </div>

        {{-- Daftar Laboratorium --}}
        <div class="col-12 col-xl-4">
            <div class="card dashboard-section-card h-100">
                <div class="card-header py-3">
                    <h4 class="mb-0 fs-6 fs-sm-5 fw-bold">Daftar Laboratorium</h4>
                    <small class="text-muted">Lokasi & Penanggung Jawab Lab</small>
                </div>
                <div class="card-content pb-4 px-3" style="max-height: 360px; overflow-y: auto;">
                    @forelse ($lab_managers as $lm)
                        <div class="dashboard-list-item d-flex align-items-start">
                            <div class="d-flex align-items-center justify-content-center rounded"
                                style="width:40px; height:40px; background: #e8f0fe; flex-shrink:0;">
                                <i class="bi bi-building text-primary"></i>
                            </div>
                            <div class="name ms-3">
                                <h6 class="mb-0 font-bold">
                                    {{ $lm->lab->nama_lab . '(' . $lm->lab->kode_lab . ')' ?? '-' }}
                                </h6>
                                <small class="text-muted d-block">
                                    <i class="bi bi-geo-alt"></i> {{ $lm->lab->lokasi ?? '-' }}
                                </small>
                                <small class="text-muted">Kalab: {{ $lm->kalab->full_name ?? '-' }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted px-4 py-3 mb-0">Belum ada data laboratorium.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 6: RIWAYAT KONFIRMASI TERAKHIR ===== --}}
    <section class="row g-3">
        <div class="col-12">
            <div class="card dashboard-section-card">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h4 class="mb-0 fs-6 fs-sm-5 fw-bold">Riwayat Konfirmasi Presensi Terbaru</h4>
                        <small class="text-muted">Log 5 verifikasi presensi terakhir di sistem</small>
                    </div>
                    <a href="{{ route('presensi.monitoring') }}"
                        class="btn btn-sm btn-outline-primary rounded-pill px-3">Monitoring Lengkap</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Mahasiswa</th>
                                    <th>Laboratorium</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayat_konfirmasi as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $item->mahasiswa->full_name ?? '-' }}</span>
                                        </td>
                                        <td>{{ $item->peminjamanLab->lab->nama_lab . '(' . $item->peminjamanLab->lab->kode_lab . ')' ?? '-' }}
                                        </td>
                                        <td>
                                            @if ($item->jam_masuk)
                                                <span
                                                    class="text-success fw-semibold">{{ $item->jam_masuk->format('d/m H:i') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->jam_keluar)
                                                <span
                                                    class="text-danger fw-semibold">{{ $item->jam_keluar->format('d/m H:i') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->status_presensi === 'didalam')
                                                <span class="badge bg-success">Di Dalam</span>
                                            @elseif($item->status_presensi === 'selesai')
                                                <span class="badge bg-secondary">Selesai</span>
                                            @elseif($item->status_presensi === 'menunggu_konfirmasi_masuk')
                                                <span class="badge bg-primary">Menunggu Masuk</span>
                                            @elseif($item->status_presensi === 'menunggu_konfirmasi_keluar')
                                                <span class="badge bg-warning text-dark">Menunggu Keluar</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $item->status_presensi }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat
                                            konfirmasi presensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateEl = document.getElementById('dashboard-date');
            const timeEl = document.getElementById('dashboard-time');

            if (dateEl && timeEl) {
                const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });
                const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });

                const updateClock = () => {
                    const now = new Date();
                    dateEl.innerHTML = '<i class="bi bi-calendar3 me-2"></i>' + dateFormatter.format(now);
                    timeEl.innerHTML = '<i class="bi bi-clock-history me-2"></i>' + timeFormatter.format(now) +
                        ' WIB';
                };

                updateClock();
                setInterval(updateClock, 1000);
            }
        });

        // ====================================================
        // 1. GRAFIK TREN LALU LINTAS PRESENSI (7 HARI TERAKHIR)
        // ====================================================
        const trenOptions = {
            series: [{
                    name: 'Presensi Masuk',
                    data: @json($tren_satpam_masuk)
                },
                {
                    name: 'Presensi Keluar',
                    data: @json($tren_satpam_keluar)
                }
            ],
            chart: {
                type: 'area',
                height: 290,
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit'
            },
            colors: ['#2563eb', '#10b981'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2.5
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: @json($tren_satpam_labels),
                labels: {
                    style: {
                        fontSize: '12px',
                        colors: '#6b7280'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                title: {
                    text: 'Jumlah Mahasiswa',
                    style: {
                        fontSize: '12px',
                        fontWeight: 500,
                        color: '#6b7280'
                    }
                },
                min: 0,
                forceNiceScale: true
            },
            markers: {
                size: 4,
                strokeColors: '#ffffff',
                strokeWidth: 2,
                hover: {
                    size: 6
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + ' mahasiswa';
                    }
                }
            },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 4
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        };
        const chartTren = new ApexCharts(document.querySelector('#chart-tren-satpam'), trenOptions);
        chartTren.render();

        // ====================================================
        // 2. GRAFIK STATUS KEHADIRAN HARI INI (DONUT CHART)
        // ====================================================
        const statusData = [
            {{ $status_satpam_chart['didalam'] }},
            {{ $status_satpam_chart['selesai'] }},
            {{ $status_satpam_chart['menunggu'] }},
            {{ $status_satpam_chart['belum_hadir'] }}
        ];
        const statusTotal = statusData.reduce((a, b) => a + b, 0);

        const statusOptions = {
            series: statusData,
            labels: ['Di Dalam Lab', 'Selesai / Keluar', 'Menunggu Verifikasi', 'Belum Hadir'],
            chart: {
                type: 'donut',
                height: 290,
                fontFamily: 'inherit'
            },
            colors: ['#10b981', '#6b7280', '#f59e0b', '#ef4444'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 2,
                colors: ['#ffffff']
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '13px',
                                color: '#6b7280'
                            },
                            value: {
                                show: true,
                                fontSize: '20px',
                                fontWeight: 700,
                                formatter: function(val) {
                                    return val;
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total Jadwal',
                                fontSize: '12px',
                                fontWeight: 600,
                                color: '#6b7280',
                                formatter: function() {
                                    return statusTotal;
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '12px'
            },
            noData: {
                text: 'Tidak ada data presensi hari ini',
                align: 'center',
                verticalAlign: 'middle',
                style: {
                    color: '#6b7280',
                    fontSize: '14px'
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + ' mahasiswa';
                    }
                }
            }
        };
        const chartStatus = new ApexCharts(document.querySelector('#chart-status-satpam'), statusOptions);
        chartStatus.render();

        // ====================================================
        // 3. GRAFIK PRESENSI PER LABORATORIUM (BAR CHART)
        // ====================================================
        const labNames = @json($presensi_per_lab->pluck('nama_lab'));
        const labCodes = @json($presensi_per_lab->pluck('kode_lab'));
        const labCounts = @json($presensi_per_lab->pluck('presensi_count'));

        const chartBar = new ApexCharts(document.querySelector('#chart-presensi-per-lab'), {
            series: [{
                name: 'Jumlah Presensi',
                data: labCounts,
            }],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit'
            },
            colors: ['#435ebe'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '45%',
                    distributed: true
                },
            },
            xaxis: {
                categories: labNames.map((name, index) => `${name} (${labCodes[index]})`),
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                },
            },
            yaxis: {
                title: {
                    text: 'Jumlah Kunjungan'
                },
                min: 0,
                forceNiceScale: true
            },
            dataLabels: {
                enabled: false
            },
            tooltip: {
                y: {
                    formatter: val => val + ' presensi'
                }
            },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 4
            },
            legend: {
                show: false
            }
        });
        chartBar.render();
    </script>
@endsection
