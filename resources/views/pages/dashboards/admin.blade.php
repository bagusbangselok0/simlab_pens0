@extends('layouts.app')

@section('styles')
    <style>
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

        .quick-action-card {
            transition: all 0.25s ease;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 12px;
            text-decoration: none;
            color: inherit;
        }

        .quick-action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            color: inherit;
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

        /* Nav Pills Custom Tabs */
        .nav-pills-custom .nav-link {
            color: var(--bs-secondary-color, #64748b);
            background: transparent;
            border: 1px solid var(--bs-border-color, rgba(0, 0, 0, 0.12));
            transition: all 0.2s ease;
        }

        .nav-pills-custom .nav-link:hover {
            color: #435ebe;
            background-color: rgba(67, 94, 190, 0.08);
        }

        .nav-pills-custom .nav-link.active {
            color: #ffffff !important;
            background-color: #435ebe !important;
            border-color: #435ebe !important;
            box-shadow: 0 2px 8px rgba(67, 94, 190, 0.35);
        }

        [data-bs-theme="dark"] .nav-pills-custom .nav-link {
            border-color: rgba(255, 255, 255, 0.12);
            color: #a5b4fc;
        }

        [data-bs-theme="dark"] .nav-pills-custom .nav-link.active {
            background-color: #435ebe !important;
            color: #ffffff !important;
        }

        /* Custom Soft Color Palettes for Icons */
        .icon-bg-purple { background-color: #f0edff; color: #6f42c1; }
        .icon-bg-blue   { background-color: #e7f1ff; color: #0d6efd; }
        .icon-bg-teal   { background-color: #e0fbf6; color: #0d9488; }
        .icon-bg-red    { background-color: #ffeef0; color: #dc3545; }
        .icon-bg-amber  { background-color: #fff8e6; color: #d97706; }
        .icon-bg-green  { background-color: #e8f8ee; color: #198754; }
        .icon-bg-gray   { background-color: #f1f3f5; color: #6c757d; }
        .icon-bg-indigo { background-color: #eef2ff; color: #4f46e5; }

        /* Dark Mode Fine-Tuning */
        [data-bs-theme="dark"] .quick-action-card,
        [data-bs-theme="dark"] .card {
            background-color: #1e1e2d;
            border-color: rgba(255, 255, 255, 0.08);
        }

        [data-bs-theme="dark"] thead.table-light,
        [data-bs-theme="dark"] thead.table-light th {
            background-color: #151521 !important;
            color: #e2e8f0 !important;
            border-color: #35354f !important;
        }

        [data-bs-theme="dark"] .table {
            color: #e2e8f0;
            border-color: #35354f;
        }

        /* Responsive Adjustments for Mobile Screens (< 576px) */
        @media (max-width: 575.98px) {
            .page-heading h3 {
                font-size: 1.35rem;
            }
            .icon-box-shape {
                width: 38px;
                height: 38px;
                font-size: 1.05rem;
                border-radius: 10px;
            }
            .card-body {
                padding: 0.85rem 0.75rem !important;
            }
            .quick-action-card {
                padding: 0.6rem 0.65rem !important;
            }
            .quick-action-card h6 {
                font-size: 0.82rem;
            }
            .quick-action-card small {
                font-size: 0.68rem;
                display: block;
            }
            .stat-title {
                font-size: 0.75rem !important;
                line-height: 1.2;
            }
            .stat-val {
                font-size: 1.2rem !important;
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
    {{-- ===== HEADER DASHBOARD ===== --}}
    <div class="page-heading mb-3">
        <h3>Dashboard Admin</h3>
        <p class="text-subtitle text-muted mb-0">Selamat datang kembali, <strong>{{ $user->full_name }}</strong>! Pusat kendali, pengawasan operasional, dan ringkasan sistem SIMLAB.</p>
    </div>

    {{-- ===== QUICK ACTIONS ===== --}}
    <section class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header pb-2 pt-3 px-3 px-sm-4 bg-transparent border-0">
                    <h5 class="card-title d-flex align-items-center mb-0 fs-6 fs-sm-5 fw-bold">
                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Aksi Cepat & Navigasi Utama
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-2 g-sm-3">
                        {{-- 1. Kelola User --}}
                        <div class="col">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-primary w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card h-100">
                                <div class="icon-box-shape icon-bg-purple me-2 me-sm-3">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Kelola User</h6>
                                    <small class="text-muted text-truncate d-block">Pengguna & Role</small>
                                </div>
                            </a>
                        </div>

                        {{-- 2. Kelola Lab --}}
                        <div class="col">
                            <a href="{{ route('lab.index') }}" class="btn btn-outline-info w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card h-100">
                                <div class="icon-box-shape icon-bg-teal me-2 me-sm-3">
                                    <i class="bi bi-building-gear"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Kelola Lab</h6>
                                    <small class="text-muted text-truncate d-block">Fasilitas & Kalab</small>
                                </div>
                            </a>
                        </div>

                        {{-- 3. Approval Ruang Lab --}}
                        <div class="col">
                            <a href="{{ route('approval.index') }}" class="btn btn-outline-warning w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card h-100">
                                <div class="icon-box-shape icon-bg-amber me-2 me-sm-3">
                                    <i class="bi bi-door-open-fill"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Approval Lab</h6>
                                    <small class="text-muted text-truncate d-block">
                                        @if($pending_admin > 0)
                                            <span class="badge bg-warning text-dark px-1.5 py-0.5 rounded-pill">{{ $pending_admin }} Pending</span>
                                        @else
                                            Peminjaman Lab
                                        @endif
                                    </small>
                                </div>
                            </a>
                        </div>

                        {{-- 4. Approval Alat Lab --}}
                        <div class="col">
                            <a href="{{ route('peminjaman-alat.approval') }}" class="btn btn-outline-primary w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card h-100">
                                <div class="icon-box-shape icon-bg-indigo me-2 me-sm-3">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Approval Alat</h6>
                                    <small class="text-muted text-truncate d-block">
                                        @if($pending_alat_admin > 0)
                                            <span class="badge bg-danger text-white px-1.5 py-0.5 rounded-pill">{{ $pending_alat_admin }} Pending</span>
                                        @else
                                            Peminjaman Alat
                                        @endif
                                    </small>
                                </div>
                            </a>
                        </div>

                        {{-- 5. Master Inventaris --}}
                        <div class="col">
                            <a href="{{ route('inventaris.index') }}" class="btn btn-outline-secondary w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card h-100">
                                <div class="icon-box-shape icon-bg-blue me-2 me-sm-3">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Inventaris</h6>
                                    <small class="text-muted text-truncate d-block">Data Barang & DIR</small>
                                </div>
                            </a>
                        </div>

                        {{-- 6. Monitoring Presensi --}}
                        <div class="col">
                            <a href="{{ route('presensi.monitoring') }}" class="btn btn-outline-success w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card h-100">
                                <div class="icon-box-shape icon-bg-green me-2 me-sm-3">
                                    <i class="bi bi-display-fill"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Monitoring</h6>
                                    <small class="text-muted text-truncate d-block">Presensi Real-Time</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 1: STATISTIK PENGGUNA ===== --}}
    <h6 class="mb-2 text-muted fw-bold d-flex align-items-center">
        <i class="bi bi-person-gear me-2 text-primary"></i>Pengguna Sistem SIMLAB
    </h6>
    <section class="row">
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-purple me-2 me-sm-3">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Mahasiswa</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $jml_mahasiswa }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-blue me-2 me-sm-3">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Dosen</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $jml_dosen }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-teal me-2 me-sm-3">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">PLP</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $jml_plp }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-red me-2 me-sm-3">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Satpam</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $jml_satpam }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 2: STATISTIK PEMINJAMAN SISTEM (RUANG LAB & ALAT) ===== --}}
    <h6 class="mt-1 mb-2 text-muted fw-bold d-flex align-items-center">
        <i class="bi bi-journal-text me-2 text-primary"></i>Statistik Peminjaman Sistem (Ruang Lab & Alat)
    </h6>
    <section class="row">
        {{-- Total Pengajuan Ruang --}}
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-indigo me-2">
                            <i class="bi bi-door-open-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Peminjaman Lab</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $total_peminjaman }}</h4>
                            <small class="text-muted" style="font-size: 0.7rem;">Total Pengajuan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Pengajuan Alat --}}
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-blue me-2">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Peminjaman Alat</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $total_alat_admin }}</h4>
                            <small class="text-muted" style="font-size: 0.7rem;">Total Pengajuan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Ruang Lab --}}
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100 shadow-sm {{ $pending_admin > 0 ? 'highlight-pending-card' : 'border-0' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-amber me-2">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Pending Lab</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $pending_admin }}</h4>
                            <small class="text-muted" style="font-size: 0.7rem;">Menunggu Approval</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Alat Lab --}}
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100 shadow-sm {{ $pending_alat_admin > 0 ? 'highlight-pending-card' : 'border-0' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-red me-2">
                            <i class="bi bi-wrench-adjustable-circle"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Pending Alat</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $pending_alat_admin }}</h4>
                            <small class="text-muted" style="font-size: 0.7rem;">Menunggu Approval</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sedang Digunakan (Lab & Alat) --}}
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-green me-2">
                            <i class="bi bi-calendar2-check-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Sedang Digunakan</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $peminjaman_hari_ini + $dipinjam_alat_admin }}</h4>
                            <small class="text-muted" style="font-size: 0.7rem;">{{ $peminjaman_hari_ini }} Lab &bull; {{ $dipinjam_alat_admin }} Alat</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Selesai --}}
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-teal me-2">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Total Selesai</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $approved_admin + $selesai_alat_admin }}</h4>
                            <small class="text-muted" style="font-size: 0.7rem;">{{ $approved_admin }} Lab &bull; {{ $selesai_alat_admin }} Alat</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 3: RINGKASAN MONITORING PRESENSI ===== --}}
    <h6 class="mt-1 mb-2 text-muted fw-bold d-flex align-items-center">
        <i class="bi bi-activity me-2 text-primary"></i>Ringkasan Presensi Real-Time
    </h6>
    <section class="row">
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-blue me-2 me-sm-3">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Presensi Hari Ini</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $presensi_hari_ini }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-amber me-2 me-sm-3">
                            <i class="bi bi-person-clock"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Menunggu Satpam</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $presensi_menunggu }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-green me-2 me-sm-3">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Di Dalam Lab</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $mahasiswa_didalam }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-purple me-2 me-sm-3">
                            <i class="bi bi-building-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Total Lab Terdaftar</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $labs->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 4: GRAFIK ===== --}}
    <section class="row mt-1">
        <div class="col-12 col-lg-8 mb-3">
            {{-- Grafik Bar: Peminjaman per Lab --}}
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header pt-3 pb-0 bg-transparent border-0">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0 fw-bold">Peminjaman per Laboratorium</h5>
                </div>
                <div class="card-body">
                    <div id="chart-peminjaman-per-lab"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 mb-3">
            {{-- Grafik Donut: Status Semua Peminjaman --}}
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header pt-3 pb-0 bg-transparent border-0">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0 fw-bold">Status Seluruh Peminjaman</h5>
                    <small class="text-muted">Komposisi status peminjaman ruang & alat di sistem.</small>
                </div>
                <div class="card-body">
                    <div id="chart-status-peminjaman"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 5: TABEL & DAFTAR LAB ===== --}}
    <section class="row">
        {{-- Pengajuan Terbaru dengan Dual Tabs (Ruang Lab vs Alat Lab) --}}
        <div class="col-12 col-lg-8 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center pt-3 pb-2 gap-2 bg-transparent border-0">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0 fw-bold">Pengajuan Terbaru di Sistem</h5>
                    <ul class="nav nav-pills nav-pills-custom" id="recentAdminTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-3 rounded-pill fw-semibold small"
                                id="admin-tab-lab-btn" data-bs-toggle="pill" data-bs-target="#admin-tab-lab" type="button" role="tab"
                                aria-controls="admin-tab-lab" aria-selected="true">
                                <i class="bi bi-door-open me-1"></i> Ruang Lab
                            </button>
                        </li>
                        <li class="nav-item ms-1" role="presentation">
                            <button class="nav-link py-1 px-3 rounded-pill fw-semibold small"
                                id="admin-tab-alat-btn" data-bs-toggle="pill" data-bs-target="#admin-tab-alat" type="button" role="tab"
                                aria-controls="admin-tab-alat" aria-selected="false">
                                <i class="bi bi-tools me-1"></i> Alat Lab
                                @if($pending_alat_admin > 0)
                                    <span class="badge bg-danger ms-1 rounded-pill" style="font-size: 0.65rem;">{{ $pending_alat_admin }}</span>
                                @endif
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="recentAdminTabContent">
                        {{-- TAB: RUANG LAB --}}
                        <div class="tab-pane fade show active" id="admin-tab-lab" role="tabpanel" aria-labelledby="admin-tab-lab-btn">
                            <div class="p-2 px-3 border-bottom d-flex justify-content-between align-items-center bg-body-tertiary">
                                <small class="text-muted fw-semibold">5 Pengajuan Ruang Lab Terakhir</small>
                                <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-0" style="font-size: 0.8rem;">
                                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Mahasiswa</th>
                                            <th>Laboratorium</th>
                                            <th>Waktu Mulai</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pengajuan_terbaru as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="fw-semibold text-truncate d-inline-block" style="max-width: 140px;">{{ $item->mahasiswa->full_name ?? '-' }}</span>
                                                </td>
                                                <td><small>{{ $item->lab->nama_lab ?? '-' }}</small></td>
                                                <td><small>{{ $item->waktu_mulai ? $item->waktu_mulai->format('d/m/Y H:i') : '-' }}</small></td>
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
                                                    @else
                                                        <span class="badge bg-light text-dark">{{ $item->status }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">Belum ada pengajuan peminjaman ruang lab.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB: ALAT LAB --}}
                        <div class="tab-pane fade" id="admin-tab-alat" role="tabpanel" aria-labelledby="admin-tab-alat-btn">
                            <div class="p-2 px-3 border-bottom d-flex justify-content-between align-items-center bg-body-tertiary">
                                <small class="text-muted fw-semibold">5 Pengajuan Alat Lab Terakhir</small>
                                <a href="{{ route('peminjaman-alat.approval') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-0" style="font-size: 0.8rem;">
                                    Kelola Semua <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Mahasiswa</th>
                                            <th>Laboratorium</th>
                                            <th>Rincian Alat</th>
                                            <th>Jadwal Pinjam</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pengajuan_alat_terbaru_admin as $alatItem)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="fw-semibold text-truncate d-inline-block" style="max-width: 130px;">
                                                        {{ $alatItem->mahasiswa->nama_asli ?? ($alatItem->mahasiswa->full_name ?? '-') }}
                                                    </span><br>
                                                    <small class="text-muted">{{ $alatItem->kode_peminjaman }}</small>
                                                </td>
                                                <td><small>{{ $alatItem->lab->nama_lab ?? '-' }}</small></td>
                                                <td>
                                                    <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                                        @foreach ($alatItem->details->take(2) as $d)
                                                            <li>• {{ $d->nama_alat }} (<strong>{{ $d->jumlah }} {{ $d->satuan }}</strong>)</li>
                                                        @endforeach
                                                        @if ($alatItem->details->count() > 2)
                                                            <li class="text-muted fst-italic">+{{ $alatItem->details->count() - 2 }} alat lainnya</li>
                                                        @endif
                                                    </ul>
                                                </td>
                                                <td>
                                                    <small>{{ $alatItem->waktu_pinjam ? $alatItem->waktu_pinjam->format('d/m/Y') : '-' }}</small><br>
                                                    <small class="text-muted">Kembali: {{ $alatItem->rencana_kembali ? $alatItem->rencana_kembali->format('d/m/Y') : '-' }}</small>
                                                </td>
                                                <td>
                                                    {!! $alatItem->status_badge !!}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-3">Belum ada pengajuan peminjaman alat lab.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Laboratorium --}}
        <div class="col-12 col-lg-4 mb-3">
            <div class="card mb-0 h-100 shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center pt-3 pb-2 bg-transparent border-0">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0 fw-bold">Daftar Laboratorium</h5>
                    <a href="{{ route('lab.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Kelola</a>
                </div>
                <div class="card-content pb-2">
                    @forelse ($lab_managers as $lm)
                        <div class="recent-message d-flex px-3 px-sm-4 py-2.5 border-bottom">
                            <div class="icon-box-shape icon-bg-blue me-3">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="name">
                                <h6 class="mb-0 font-bold" style="font-size: 0.9rem;">{{ $lm->lab->nama_lab ?? '-' }}</h6>
                                <small class="text-muted d-block" style="font-size: 0.78rem;">
                                    <i class="bi bi-geo-alt"></i> {{ $lm->lab->lokasi ?? '-' }}
                                </small>
                                <small class="text-muted d-block" style="font-size: 0.78rem;">Kalab: {{ $lm->kalab->full_name ?? '-' }}</small>
                                <small class="text-muted" style="font-size: 0.78rem;">PLP: {{ $lm->plp->full_name ?? '-' }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted px-4 py-3 mb-0">Belum ada data laboratorium.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    // Grafik Bar: Jumlah Peminjaman per Laboratorium
    const labNames  = @json($peminjaman_per_lab->pluck('nama_lab'));
    const labCounts = @json($peminjaman_per_lab->pluck('peminjaman_labs_count'));

    const chartBar = new ApexCharts(document.querySelector('#chart-peminjaman-per-lab'), {
        series: [{
            name: 'Jumlah Peminjaman Ruang',
            data: labCounts,
        }],
        chart: {
            type: 'bar',
            height: 280,
            toolbar: { show: false },
        },
        colors: ['#435ebe'],
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '50%',
            },
        },
        xaxis: {
            categories: labNames,
            labels: { style: { fontSize: '11px' } },
        },
        yaxis: {
            title: { text: 'Jumlah' },
            min: 0,
            tickAmount: 4,
        },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: val => val + ' pengajuan' } },
    });
    chartBar.render();

    // Grafik Donut: Status Seluruh Peminjaman di Sistem (Ruang Lab + Alat)
    const totalApproved = {{ $approved_admin + $selesai_alat_admin }};
    const totalPending  = {{ $pending_admin + $pending_alat_admin }};
    const totalRejected = {{ $rejected_admin + $rejected_alat_admin }};
    const totalExpired  = {{ $kadaluarsa_admin }};

    const chartDonut = new ApexCharts(document.querySelector('#chart-status-peminjaman'), {
        series: [totalApproved, totalPending, totalRejected, totalExpired],
        labels: ['Disetujui / Selesai', 'Pending', 'Ditolak', 'Kadaluarsa'],
        colors: ['#198754', '#ffc107', '#dc3545', '#6c757d'],
        chart: {
            type: 'donut',
            height: 280,
        },
        legend: {
            position: 'bottom',
        },
        plotOptions: {
            pie: {
                donut: { size: '55%' },
            },
        },
        dataLabels: {
            formatter: (val, opts) => opts.w.config.series[opts.seriesIndex],
        },
    });
    chartDonut.render();
</script>
@endsection
