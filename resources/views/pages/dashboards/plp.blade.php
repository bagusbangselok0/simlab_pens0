@extends('layouts.app')

@section('styles')
    <style>
        .dashboard-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 24px;
            background: linear-gradient(135deg, #1d4ed8 0%, #4f46e5 45%, #7c3aed 100%);
            color: #ffffff;
            box-shadow: 0 18px 40px rgba(79, 70, 229, 0.22);
        }

        .dashboard-hero::before,
        .dashboard-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
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
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(8px);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.22);
        }

        .dashboard-chip-soft {
            background: rgba(255,255,255,0.14);
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
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 14px;
            background: var(--bs-card-bg, #ffffff);
        }

        .quick-action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0,0,0,0.08);
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

        .icon-bg-purple { background-color: #f0edff; color: #6f42c1; }
        .icon-bg-blue   { background-color: #e7f1ff; color: #0d6efd; }
        .icon-bg-teal   { background-color: #e0fbf6; color: #0d9488; }
        .icon-bg-red    { background-color: #ffeef0; color: #dc3545; }
        .icon-bg-amber  { background-color: #fff8e6; color: #d97706; }
        .icon-bg-green  { background-color: #e8f8ee; color: #198754; }
        .icon-bg-gray   { background-color: #f1f3f5; color: #6c757d; }
        .icon-bg-indigo { background-color: #eef2ff; color: #4f46e5; }

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
                        <i class="bi bi-shield-check me-2"></i>Area Persetujuan PLP
                    </span>
                    <h3 class="fw-bold mb-2">Halo, {{ $user->full_name }} 👋</h3>
                    <p class="mb-0 text-white-50">Pantau peminjaman laboratorium, tindaklanjuti pengajuan, dan pastikan proses berjalan lancar.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span id="dashboard-date" class="dashboard-chip dashboard-chip-soft">
                        <i class="bi bi-calendar3 me-2"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                    </span>
                    <span id="dashboard-time" class="dashboard-chip dashboard-chip-soft">
                        <i class="bi bi-clock-history me-2"></i>{{ \Carbon\Carbon::now()->translatedFormat('H:i') }} WIB
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($show_signature_alert)
        <div class="card border-warning shadow-sm mb-4">
            <div class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <h6 class="mb-2 text-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i>TTD Digital Belum Diunggah</h6>
                    <p class="mb-0 text-muted">Anda belum mengunggah tanda tangan digital. Silakan unggah terlebih dahulu agar proses persetujuan peminjaman dapat berjalan dengan lancar.</p>
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
                        <div class="col-6 col-md-4">
                            <a href="{{ route('approval.index') }}" class="btn btn-outline-warning w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
                                <div class="icon-box-shape icon-bg-amber me-2 me-sm-3">
                                    <i class="bi bi-check2-square"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Approval Peminjaman</h6>
                                    <small class="text-muted text-truncate d-none d-sm-block">Persetujuan PLP</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-4">
                            <a href="{{ route('presensi.monitoring') }}" class="btn btn-outline-success w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
                                <div class="icon-box-shape icon-bg-green me-2 me-sm-3">
                                    <i class="bi bi-display-fill"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-truncate">Monitoring Presensi</h6>
                                    <small class="text-muted text-truncate d-none d-sm-block">Pantau Presensi Lab</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-md-4">
                            <a href="{{ route('profile.index') }}" class="btn btn-outline-primary w-100 text-start py-2.5 px-2 px-sm-3 d-flex align-items-center quick-action-card">
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

    {{-- ===== KARTU STATISTIK SISTEM ===== --}}
    <h6 class="mb-3 text-muted fw-bold d-flex align-items-center">
        <i class="bi bi-speedometer2 me-2 text-primary"></i>Ringkasan Statistik Peminjaman
    </h6>
    <section class="row">
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-teal">
                            <i class="bi bi-building-gear"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Total Lab</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $labs->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-purple">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Mahasiswa</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $jml_mahasiswa }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-indigo">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Total Peminjaman</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $total_peminjaman_by_plp }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-blue">
                            <i class="bi bi-calendar2-event-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Aktif Hari Ini</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $peminjaman_hari_ini }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4 col-md-4 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card {{ $pending_plp > 0 ? 'highlight-pending-card' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-amber">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Menunggu Approval</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $pending_plp }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4 col-md-4 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-green">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Peminjaman Selesai</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $selesai_plp }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 col-md-4 mb-3">
            <div class="card mb-0 h-100 dashboard-stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dashboard-stat-icon icon-bg-red">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <div>
                            <h6 class="text-muted font-semibold stat-title mb-1">Peminjaman Ditolak</h6>
                            <h4 class="font-extrabold stat-val mb-0 dashboard-stat-number">{{ $tolak_plp }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS GRAFIK ===== --}}
    <section class="row mb-3">
        <div class="col-12">
            <div class="card mb-0 dashboard-section-card">
                <div class="card-header pt-3 pb-0">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0">Peminjaman per Laboratorium</h5>
                </div>
                <div class="card-body">
                    <div id="chart-peminjaman-dosen"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARIS 2: DAFTAR LAB + PENGAJUAN TERBARU ===== --}}
    <section class="row">
        {{-- Daftar Laboratorium --}}
        <div class="col-12 col-lg-5 mb-3">
            <div class="card mb-0 h-100 dashboard-section-card">
                <div class="card-header pt-3 pb-2">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0">Informasi Laboratorium</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Lab</th>
                                    <th>Lokasi</th>
                                    <th>Kalab</th>
                                    <th>PLP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lab_managers as $lm)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $lm->lab->nama_lab ?? '-' }}</span><br>
                                            <small class="text-muted">{{ $lm->lab->kode_lab ?? '' }}</small>
                                        </td>
                                        <td><small class="text-muted">{{ $lm->lab->lokasi ?? '-' }}</small></td>
                                        <td><small>{{ $lm->kalab->full_name ?? '-' }}</small></td>
                                        <td><small>{{ $lm->plp->full_name ?? '-' }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Belum ada data laboratorium.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pengajuan Terbaru --}}
        <div class="col-12 col-lg-7 mb-3">
            <div class="card mb-0 h-100 dashboard-section-card">
                <div class="card-header d-flex justify-content-between align-items-center pt-3 pb-2">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0">Peminjaman Terbaru di Lab Anda</h5>
                    <a href="{{ route('approval.index') }}" class="btn btn-sm btn-primary rounded-pill">Kelola</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th>Laboratorium</th>
                                    <th>Waktu Mulai</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pengajuan_terbaru_plp as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-truncate d-inline-block" style="max-width: 140px;">{{ $item->mahasiswa->full_name ?? '-' }}</span>
                                        </td>
                                        <td><small>{{ $item->lab->nama_lab ?? '-' }}</small></td>
                                        <td>
                                            <small>{{ $item->waktu_mulai ? $item->waktu_mulai->format('d/m/Y') : '-' }}</small><br>
                                            <small class="text-muted">{{ $item->waktu_mulai ? $item->waktu_mulai->format('H:i') : '' }}</small>
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
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada pengajuan peminjaman.</td>
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
        document.addEventListener('DOMContentLoaded', function () {
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
                    timeEl.innerHTML = '<i class="bi bi-clock-history me-2"></i>' + timeFormatter.format(now) + ' WIB';
                };

                updateClock();
                setInterval(updateClock, 1000);
            }
        });

        // Grafik Bar: Jumlah Peminjaman per Laboratorium
        const labNames = @json($peminjaman_per_lab->pluck('nama_lab'));
        const labCounts = @json($peminjaman_per_lab->pluck('peminjaman_labs_count'));

        const chartBar = new ApexCharts(document.querySelector('#chart-peminjaman-dosen'), {
            series: [{
                name: 'Jumlah Peminjaman',
                data: labCounts,
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: {
                    show: false
                },
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
                labels: {
                    style: {
                        fontSize: '11px'
                    }
                },
            },
            yaxis: {
                min: 0,
                tickAmount: 4,
            },
            dataLabels: {
                enabled: false
            },
            tooltip: {
                y: {
                    formatter: val => val + ' pengajuan'
                }
            },
            grid: {
                borderColor: '#f1f1f1'
            },
        });
        chartBar.render();
    </script>
@endsection
