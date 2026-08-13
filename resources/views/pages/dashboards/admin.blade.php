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
        }

        .quick-action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
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

        /* Custom Soft Color Palettes for Icons */
        .icon-bg-purple { background-color: #f0edff; color: #6f42c1; }
        .icon-bg-blue   { background-color: #e7f1ff; color: #0d6efd; }
        .icon-bg-teal   { background-color: #e0fbf6; color: #0d9488; }
        .icon-bg-red    { background-color: #ffeef0; color: #dc3545; }
        .icon-bg-amber  { background-color: #fff8e6; color: #d97706; }
        .icon-bg-green  { background-color: #e8f8ee; color: #198754; }
        .icon-bg-gray   { background-color: #f1f3f5; color: #6c757d; }
        .icon-bg-indigo { background-color: #eef2ff; color: #4f46e5; }

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
        <p class="text-subtitle text-muted mb-0">Selamat datang kembali, <strong>{{ $user->full_name }}</strong>! Berikut ringkasan sistem SIMLAB.</p>
    </div>

    {{-- ===== QUICK ACTIONS ===== --}}
    <section class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-2 pt-3 px-3 px-sm-4">
                    <h5 class="card-title d-flex align-items-center mb-0 fs-6 fs-sm-5">
                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Aksi Cepat (Quick Shortcuts)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 g-sm-3">
                        <div class="col-6 col-md-3">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-primary w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
                                <div class="icon-box-shape icon-bg-purple me-2 me-sm-3">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Kelola User</h6>
                                    <small class="text-muted text-truncate d-none d-xs-block d-sm-block">Pengguna & Role</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('lab.index') }}" class="btn btn-outline-info w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
                                <div class="icon-box-shape icon-bg-teal me-2 me-sm-3">
                                    <i class="bi bi-building-gear"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Kelola Lab</h6>
                                    <small class="text-muted text-truncate d-none d-xs-block d-sm-block">Fasilitas Lab</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('approval.index') }}" class="btn btn-outline-warning w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
                                <div class="icon-box-shape icon-bg-amber me-2 me-sm-3">
                                    <i class="bi bi-check2-square"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Approval</h6>
                                    <small class="text-muted text-truncate d-none d-xs-block d-sm-block">Peminjaman Lab</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('presensi.monitoring') }}" class="btn btn-outline-success w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
                                <div class="icon-box-shape icon-bg-green me-2 me-sm-3">
                                    <i class="bi bi-display-fill"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Monitoring</h6>
                                    <small class="text-muted text-truncate d-none d-xs-block d-sm-block">Presensi Mahasiswa</small>
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
        <i class="bi bi-person-gear me-2 text-primary"></i>Pengguna Sistem
    </h6>
    <section class="row">
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100">
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
            <div class="card mb-0 h-100">
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
            <div class="card mb-0 h-100">
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
            <div class="card mb-0 h-100">
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

    {{-- ===== BARIS 2: STATISTIK PEMINJAMAN ===== --}}
    <h6 class="mt-1 mb-2 text-muted fw-bold d-flex align-items-center">
        <i class="bi bi-journal-text me-2 text-primary"></i>Statistik Peminjaman Lab
    </h6>
    <section class="row">
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-indigo me-2">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Total Pengajuan</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $total_peminjaman }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100 {{ $pending_admin > 0 ? 'highlight-pending-card' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-amber me-2">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Menunggu</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $pending_admin }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-green me-2">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Disetujui</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $approved_admin }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-red me-2">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Ditolak</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $rejected_admin }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-gray me-2">
                            <i class="bi bi-calendar-x-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Kadaluarsa</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $kadaluarsa_admin }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2 col-md-4 mb-3">
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-blue me-2">
                            <i class="bi bi-calendar2-event-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Aktif Hari Ini</h6>
                            <h4 class="font-extrabold stat-val mb-0">{{ $peminjaman_hari_ini }}</h4>
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
            <div class="card mb-0 h-100">
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
            <div class="card mb-0 h-100">
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
            <div class="card mb-0 h-100">
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
            <div class="card mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-box-shape icon-bg-purple me-2 me-sm-3">
                            <i class="bi bi-building-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Total Lab Aktif</h6>
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
            <div class="card h-100">
                <div class="card-header pt-3 pb-0">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0">Peminjaman per Laboratorium</h5>
                </div>
                <div class="card-body">
                    <div id="chart-peminjaman-per-lab"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 mb-3">
            {{-- Grafik Donut: Status Peminjaman --}}
            <div class="card h-100">
                <div class="card-header pt-3 pb-0">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0">Status Semua Peminjaman</h5>
                </div>
                <div class="card-body">
                    <div id="chart-status-peminjaman"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 5: TABEL & SIDEBAR ===== --}}
    <section class="row">
        <div class="col-12 col-lg-8 mb-3">
            {{-- Tabel 5 Pengajuan Terbaru --}}
            <div class="card mb-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center pt-3 pb-2">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0">Pengajuan Terbaru</h5>
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead>
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
                                        <td colspan="5" class="text-center text-muted py-3">Belum ada pengajuan peminjaman.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            {{-- Daftar Laboratorium --}}
            <div class="card mb-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center pt-3 pb-2">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0">Daftar Laboratorium</h5>
                    <a href="{{ route('lab.index') }}" class="btn btn-sm btn-outline-primary">Kelola</a>
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
            name: 'Jumlah Peminjaman',
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

    // Grafik Donut: Status Peminjaman
    const chartDonut = new ApexCharts(document.querySelector('#chart-status-peminjaman'), {
        series: [{{ $approved_admin }}, {{ $pending_admin }}, {{ $rejected_admin }}, {{ $kadaluarsa_admin }}],
        labels: ['Disetujui', 'Pending', 'Ditolak', 'Kadaluarsa'],
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
