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
            right: 12px;
            background-color: #ff9f43;
            color: #ffffff;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 2px 7px;
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

        .icon-box-shape {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .quick-action-card {
            transition: all 0.25s ease;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 14px;
            background: var(--bs-card-bg, #ffffff);
        }

        .quick-action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        }

        .quick-action-card > .text-truncate {
            min-width: 0;
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
            font-size: clamp(1.15rem, 1.7vw, 1.5rem);
            line-height: 1.1;
        }

        .dashboard-section-card .card-header > div,
        .dashboard-section-card .card-header > h5 {
            min-width: 0;
        }

        .dashboard-section-card .table {
            min-width: 620px;
        }

        #chart-tren-peminjaman-kalab,
        #chart-status-peminjaman-kalab {
            min-width: 0;
        }

        .icon-bg-purple {
            background-color: #f0edff;
            color: #6f42c1;
        }

        .icon-bg-blue {
            background-color: #e7f1ff;
            color: #0d6efd;
        }

        .icon-bg-teal {
            background-color: #e0fbf6;
            color: #0d9488;
        }

        .icon-bg-red {
            background-color: #ffeef0;
            color: #dc3545;
        }

        .icon-bg-amber {
            background-color: #fff8e6;
            color: #d97706;
        }

        .icon-bg-green {
            background-color: #e8f8ee;
            color: #198754;
        }

        .icon-bg-gray {
            background-color: #f1f3f5;
            color: #6c757d;
        }

        .icon-bg-indigo {
            background-color: #eef2ff;
            color: #4f46e5;
        }

        @media (max-width: 1199.98px) {
            .dashboard-hero .card-body {
                padding: 1.5rem !important;
            }

            .dashboard-stat-card .card-body {
                padding: 1rem !important;
            }

            .dashboard-stat-card .d-flex {
                gap: 0.75rem !important;
            }

            .dashboard-stat-card .dashboard-stat-icon {
                width: 42px;
                height: 42px;
                font-size: 1.1rem;
                border-radius: 12px;
            }

            .dashboard-section-card .card-header {
                gap: 0.75rem;
            }
        }

        @media (max-width: 767.98px) {
            .dashboard-hero {
                border-radius: 18px;
            }

            .dashboard-hero h3 {
                font-size: 1.35rem;
            }

            .dashboard-hero p {
                font-size: 0.9rem;
                line-height: 1.5;
            }

            .dashboard-hero .d-flex.flex-wrap {
                width: 100%;
            }

            .dashboard-chip {
                font-size: 0.75rem;
                padding: 0.4rem 0.65rem;
            }

            .dashboard-section-card .card-header {
                align-items: flex-start !important;
                flex-wrap: wrap;
            }

            .dashboard-section-card .card-header .badge,
            .dashboard-section-card .card-header > a {
                margin-left: auto;
            }

            .dashboard-section-card .card-header h5 {
                font-size: 0.95rem;
            }

            .dashboard-section-card .card-header small {
                display: block;
                line-height: 1.4;
            }

            .dashboard-stat-number {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 575.98px) {
            .icon-box-shape {
                width: 38px;
                height: 38px;
                font-size: 1.05rem;
                border-radius: 10px;
            }

            .card-body {
                padding: 0.85rem 0.75rem !important;
            }

            .stat-title {
                font-size: 0.75rem !important;
                line-height: 1.2;
            }

            .stat-val {
                font-size: 1.2rem !important;
            }

            .dashboard-hero .card-body {
                padding: 1.25rem !important;
            }

            .dashboard-hero .dashboard-chip:first-child {
                white-space: normal;
            }

            .dashboard-hero .d-flex.flex-wrap .dashboard-chip {
                flex: 1 1 auto;
                justify-content: center;
            }

            .dashboard-section-card .card-body {
                padding: 0.85rem 0.75rem !important;
            }

            .dashboard-section-card .card-header {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            .dashboard-section-card .card-header .badge,
            .dashboard-section-card .card-header > a {
                margin-left: 0;
                width: 100%;
                text-align: center;
            }

            .quick-action-card {
                min-height: 64px;
            }

            .quick-action-card h6 {
                font-size: 0.85rem;
            }

            .quick-action-card small {
                font-size: 0.72rem;
            }

            .dashboard-section-card .table {
                min-width: 680px;
            }

            .highlight-pending-card::after {
                font-size: 0.55rem;
                top: -8px;
                right: 8px;
                padding: 1px 5px;
            }
        }
    </style>
@endsection

@section('content')
    {{-- ===== HEADER SELAMAT DATANG ===== --}}
    <div class="card border-0 shadow-sm dashboard-hero mb-4">
        <div class="card-body p-4 p-md-5 position-relative">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <span class="dashboard-chip mb-3">
                        <i class="bi bi-person-badge me-2"></i>Area Kepala Laboratorium (Kalab)
                    </span>
                    <h3 class="fw-bold mb-2">Halo, {{ $user->full_name }} 👋</h3>
                    <p class="mb-0 text-white-50">
                        @if ($my_lab_managers->isNotEmpty())
                            Memantau aktivitas & persetujuan peminjaman untuk:
                            <strong class="text-white">
                                {{ $my_lab_managers->pluck('lab.nama_lab')->filter()->join(', ') }}
                                ({{ $my_lab_managers->pluck('lab.kode_lab')->filter()->join(', ') }})
                            </strong>
                        @else
                            Pantau pengajuan laboratorium, kelola persetujuan, dan pastikan aktivitas lab tetap terkontrol.
                        @endif
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
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

    @if ($show_signature_alert)
        <div class="card border-warning shadow-sm mb-4">
            <div
                class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <h6 class="mb-2 text-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i>TTD Digital Belum
                        Diunggah</h6>
                    <p class="mb-0 text-muted">Anda belum mengunggah tanda tangan digital. Silakan unggah terlebih dahulu
                        agar proses persetujuan peminjaman dapat berjalan dengan lancar.</p>
                </div>
                <a href="{{ route('profile.index') }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pen-fill me-1"></i>Upload TTD Digital
                </a>
            </div>
        </div>
    @endif

    {{-- ===== QUICK ACTIONS ===== --}}
    <section class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-section-card">
                <div class="card-header pb-2 pt-3 px-3 px-sm-4">
                    <h5 class="card-title d-flex align-items-center mb-0 fs-6 fs-sm-5">
                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Aksi Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 g-sm-3">
                        <div class="col-12 col-sm-6 col-md-4">
                            <a href="{{ route('approval.index') }}"
                                class="btn btn-outline-warning w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
                                <div class="icon-box-shape icon-bg-amber me-2 me-sm-3">
                                    <i class="bi bi-check2-square"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Approval Peminjaman</h6>
                                    <small class="text-muted text-truncate d-none d-sm-block">Persetujuan Kalab</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <a href="{{ route('presensi.monitoring') }}"
                                class="btn btn-outline-success w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
                                <div class="icon-box-shape icon-bg-green me-2 me-sm-3">
                                    <i class="bi bi-display-fill"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Monitoring Presensi</h6>
                                    <small class="text-muted text-truncate d-none d-sm-block">Pantau Presensi Lab</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <a href="{{ route('profile.index') }}"
                                class="btn btn-outline-primary w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
                                <div class="icon-box-shape icon-bg-purple me-2 me-sm-3">
                                    <i class="bi bi-person-gear"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Profil & TTD</h6>
                                    <small class="text-muted text-truncate d-none d-sm-block">Tanda Tangan Digital</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== KARTU STATISTIK LAB KALAB ===== --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="mb-0 text-muted fw-bold d-flex align-items-center">
            <i class="bi bi-speedometer2 me-2 text-primary"></i>Statistik Laboratorium Anda
        </h6>
        <small class="text-muted fst-italic">Data otomatis difilter sesuai lab tanggung jawab Anda</small>
    </div>

    {{-- Baris 1: Ringkasan Utama --}}
    <section class="row">
        <div class="col-12 col-sm-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-teal">
                            <i class="bi bi-building-check"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Lab Dikelola</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $total_lab_kalab }}</h4>
                            <small class="text-muted" style="font-size: 0.72rem;">Unit Laboratorium</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-purple">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Peminjam Lab</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $total_mahasiswa_kalab }}
                            </h4>
                            <small class="text-muted" style="font-size: 0.72rem;">Mahasiswa unik</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-indigo">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Total Peminjaman</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $total_peminjaman_by_kalab }}
                            </h4>
                            <small class="text-muted" style="font-size: 0.72rem;">Semua riwayat pengajuan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card"
                title="Mahasiswa dengan peminjaman aktif hari ini di lab Anda namun belum melakukan presensi masuk">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-blue">
                            <i class="bi bi-person-exclamation"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Belum Presensi Hari Ini</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number text-primary">
                                {{ $belum_presensi_hari_ini_kalab }}</h4>
                            <small class="text-muted" style="font-size: 0.72rem;">Jadwal aktif belum hadir</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Baris 2: Status Persetujuan & Aktivitas Lab --}}
    <section class="row mb-3">
        <div class="col-12 col-sm-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card {{ $pending_kalab > 0 ? 'highlight-pending-card' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-amber">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Menunggu Approval</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $pending_kalab }}</h4>
                            <small class="text-muted" style="font-size: 0.72rem;">Perlu ditinjau Kalab</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-teal">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Sedang di Lab</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number text-success">
                                {{ $sedang_di_lab_kalab }}</h4>
                            <small class="text-muted" style="font-size: 0.72rem;">Sedang beraktivitas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-green">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Peminjaman Selesai</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $selesai_kalab }}</h4>
                            <small class="text-muted" style="font-size: 0.72rem;">Selesai digunakan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-red">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Peminjaman Ditolak</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $tolak_kalab }}</h4>
                            <small class="text-muted" style="font-size: 0.72rem;">Permohonan tidak lolos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS GRAFIK & VISUALISASI LAB ===== --}}
    <section class="row mb-4">
        {{-- Grafik 1: Tren Peminjaman Lab (6 Bulan Terakhir) --}}
        <div class="col-12 col-lg-8 mb-3 mb-lg-0">
            <div class="card mb-0 h-100 dashboard-section-card">
                <div class="card-header d-flex align-items-center justify-content-between pt-3 pb-2">
                    <div>
                        <h5 class="card-title fs-6 fs-sm-5 mb-0 d-flex align-items-center">
                            <i class="bi bi-graph-up-arrow text-primary me-2"></i>Tren Peminjaman Lab Anda
                        </h5>
                        <small class="text-muted">Aktivitas peminjaman selama 6 bulan terakhir</small>
                    </div>
                    <span class="badge bg-light-primary text-primary fw-semibold px-2 py-1">
                        Total: {{ $total_peminjaman_by_kalab }} Pengajuan
                    </span>
                </div>
                <div class="card-body pt-2">
                    <div id="chart-tren-peminjaman-kalab"></div>
                </div>
            </div>
        </div>

        {{-- Grafik 2: Komposisi Status Peminjaman (Donut Chart) --}}
        <div class="col-12 col-lg-4">
            <div class="card mb-0 h-100 dashboard-section-card">
                <div class="card-header pt-3 pb-2">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0 d-flex align-items-center">
                        <i class="bi bi-pie-chart-fill text-info me-2"></i>Status Peminjaman Lab
                    </h5>
                    <small class="text-muted">Distribusi status permohonan</small>
                </div>
                <div class="card-body d-flex flex-column justify-content-center pt-2">
                    @if ($total_peminjaman_by_kalab > 0)
                        <div id="chart-status-peminjaman-kalab"></div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <p class="mb-0 small">Belum ada data peminjaman di lab Anda</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 2: DETAIL LAB ANDA + PENGAJUAN TERBARU ===== --}}
    <section class="row">
        {{-- Informasi Detail Laboratorium yang Dikelola --}}
        <div class="col-12 col-lg-5 mb-3">
            <div class="card mb-0 h-100 dashboard-section-card">
                <div class="card-header pt-3 pb-2">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0 d-flex align-items-center">
                        <i class="bi bi-building text-teal me-2"></i>Laboratorium yang Anda Kelola
                    </h5>
                    <small class="text-muted">Informasi teknis & pendamping lab</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Lab</th>
                                    <th>Lokasi</th>
                                    <th>Teknisi / PLP</th>
                                    <th>Status Saat Ini</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($my_lab_managers as $lm)
                                    @php
                                        // Hitung mahasiswa yang sedang ada di dalam lab ini
                                        $mhs_di_lab_ini = \App\Models\PresensiLab::whereHas('peminjamanLab', function (
                                            $q,
                                        ) use ($lm) {
                                            $q->where('lab_id', $lm->lab_id);
                                        })
                                            ->where('status_presensi', 'didalam')
                                            ->count();
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $lm->lab->nama_lab ?? '-' }}</span><br>
                                            <span class="badge bg-light-secondary text-secondary"
                                                style="font-size: 0.7rem;">{{ $lm->lab->kode_lab ?? '-' }}</span>
                                        </td>
                                        <td><small class="text-muted">{{ $lm->lab->lokasi ?? '-' }}</small></td>
                                        <td>
                                            <small class="fw-medium text-dark">{{ $lm->plp->full_name ?? '-' }}</small>
                                            @if (!empty($lm->plp->nip))
                                                <br><small class="text-muted" style="font-size: 0.72rem;">NIP.
                                                    {{ $lm->plp->nip }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($mhs_di_lab_ini > 0)
                                                <span class="badge bg-info text-white">
                                                    <i class="bi bi-person-fill me-1"></i>Digunakan
                                                    ({{ $mhs_di_lab_ini }})
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check2 me-1"></i>Tersedia / Kosong
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bi bi-exclamation-circle fs-3 d-block text-warning mb-2"></i>
                                            Akun Anda belum terdaftar sebagai Kepala Lab pada data laboratorium.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pengajuan Terbaru di Lab Kalab --}}
        <div class="col-12 col-lg-7 mb-3">
            <div class="card mb-0 h-100 dashboard-section-card">
                <div class="card-header d-flex justify-content-between align-items-center pt-3 pb-2">
                    <div>
                        <h5 class="card-title fs-6 fs-sm-5 mb-0 d-flex align-items-center">
                            <i class="bi bi-clock-history text-primary me-2"></i>Pengajuan Terbaru di Lab Anda
                        </h5>
                        <small class="text-muted">Daftar permohonan terkini untuk lab tanggung jawab Anda</small>
                    </div>
                    <a href="{{ route('approval.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">Kelola
                        Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th>Laboratorium</th>
                                    <th>Waktu Penggunaan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pengajuan_terbaru_kalab as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-truncate d-inline-block"
                                                style="max-width: 140px;">
                                                {{ $item->mahasiswa->full_name ?? ($item->mahasiswa->nama_asli ?? '-') }}
                                            </span><br>
                                            <small class="text-muted">{{ $item->mahasiswa->nrp ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small class="fw-medium">{{ $item->lab->nama_lab ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $item->waktu_mulai ? $item->waktu_mulai->format('d/m/Y') : '-' }}</small><br>
                                            <small
                                                class="text-muted">{{ $item->waktu_mulai ? $item->waktu_mulai->format('H:i') : '' }}
                                                - {{ $item->waktu_selesai ? $item->waktu_selesai->format('H:i') : '' }}
                                                WIB</small>
                                        </td>
                                        <td>
                                            @if ($item->status == 'pending_plp')
                                                <span class="badge bg-warning text-dark">Menunggu PLP</span>
                                            @elseif ($item->status == 'pending_kalab')
                                                <span class="badge bg-info">Menunggu Kalab</span>
                                            @elseif ($item->status == 'disetujui')
                                                <span class="badge bg-success">Disetujui</span>
                                            @elseif ($item->status == 'ditolak')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @elseif ($item->status == 'kadaluarsa')
                                                <span class="badge bg-secondary">Kadaluarsa</span>
                                            @elseif ($item->status == 'dibatalkan')
                                                <span class="badge bg-danger">Dibatalkan Sistem</span>
                                            @elseif ($item->status == 'selesai')
                                                <span class="badge bg-success">Selesai</span>
                                            @elseif ($item->status == 'dibatalkan_mahasiswa')
                                                <span class="badge bg-danger">Dibatalkan Mahasiswa</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-3 d-block text-secondary opacity-50 mb-2"></i>
                                            Belum ada pengajuan peminjaman untuk lab Anda.
                                        </td>
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

        // ==========================================
        // 1. Grafik Tren Peminjaman Lab (Area Chart)
        // ==========================================
        const trenLabels = @json($tren_kalab_labels ?? []);
        const trenData = @json($tren_kalab_data ?? []);

        const optionsTren = {
            series: [{
                name: 'Pengajuan Peminjaman',
                data: trenData
            }],
            chart: {
                type: 'area',
                height: 270,
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit'
            },
            colors: ['#435ebe'],
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100]
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: trenLabels,
                labels: {
                    style: {
                        colors: '#6c757d',
                        fontSize: '11px'
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
                min: 0,
                forceNiceScale: true,
                labels: {
                    formatter: val => Math.round(val),
                    style: {
                        colors: '#6c757d',
                        fontSize: '11px'
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: val => val + ' Pengajuan'
                }
            },
            grid: {
                borderColor: '#eef2f6',
                strokeDashArray: 4
            }
        };

        const chartTren = new ApexCharts(document.querySelector('#chart-tren-peminjaman-kalab'), optionsTren);
        chartTren.render();

        // ==========================================
        // 2. Grafik Komposisi Status (Donut Chart)
        // ==========================================
        @if ($total_peminjaman_by_kalab > 0)
            const statusLabels = @json($status_kalab_chart['labels'] ?? []);
            const statusSeries = @json($status_kalab_chart['series'] ?? []);

            const optionsStatus = {
                series: statusSeries,
                labels: statusLabels,
                chart: {
                    type: 'donut',
                    height: 250,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit'
                },
                colors: ['#198754', '#ff9f43', '#dc3545', '#6c757d'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '12px',
                                    fontWeight: 600
                                },
                                value: {
                                    show: true,
                                    fontSize: '18px',
                                    fontWeight: 700,
                                    formatter: val => val
                                },
                                total: {
                                    show: true,
                                    showAlways: false,
                                    label: 'Total',
                                    fontSize: '12px',
                                    fontWeight: 600,
                                    formatter: w => {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    markers: {
                        radius: 12
                    }
                },
                tooltip: {
                    y: {
                        formatter: val => val + ' Pengajuan'
                    }
                }
            };

            const chartStatus = new ApexCharts(document.querySelector('#chart-status-peminjaman-kalab'), optionsStatus);
            chartStatus.render();
        @endif
    </script>
@endsection
