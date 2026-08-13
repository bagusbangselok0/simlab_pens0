@extends('layouts.app')

@section('styles')
    <style>
        /* Utility Colors */
        .text-indigo {
            color: #6366f1 !important;
        }

        .bg-indigo {
            background-color: #6366f1 !important;
        }

        .bg-indigo-subtle {
            background-color: rgba(99, 102, 241, 0.12) !important;
        }

        .text-purple {
            color: #8b5cf6 !important;
        }

        .text-amber {
            color: #d97706 !important;
        }

        .text-warning-light {
            color: #fde047 !important;
        }

        /* Hero Banner Styling */
        .hero-banner {
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #8b5cf6 100%);
            border-radius: 20px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.25);
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: 5%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .hero-glass-pill {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 500;
            padding: 0.4rem 0.85rem;
            border-radius: 50rem;
            display: inline-flex;
            align-items: center;
        }

        /* Shine Effect CTA Button */
        .btn-shine {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-shine::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 50px;
            height: 200%;
            background: rgba(255, 255, 255, 0.4);
            transform: rotate(30deg);
            transition: all 0.6s ease;
        }

        .btn-shine:hover::after {
            left: 130%;
        }

        .btn-shine:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        /* Quick Action Card */
        .quick-action-card {
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            background: var(--bs-card-bg, #ffffff);
            text-decoration: none;
            color: var(--bs-body-color);
            display: block;
            height: 100%;
        }

        .quick-action-card .quick-action-title {
            color: var(--bs-body-color);
        }

        .quick-action-card .quick-action-desc {
            color: var(--bs-secondary-color);
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
            border-color: #6366f1;
            color: inherit;
        }

        .quick-action-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            transition: transform 0.3s ease;
        }

        .quick-action-card:hover .quick-action-icon {
            transform: scale(1.1);
        }

        /* Stepper Card & Wrapper */
        .stepper-card {
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 18px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            background: var(--bs-card-bg, #ffffff);
            margin-bottom: 25px;
        }

        .stepper-container {
            overflow-x: auto;
            padding: 20px 10px 10px 10px;
        }

        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            min-width: 750px;
            position: relative;
        }

        .stepper-item {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        /* Connecting lines */
        .stepper-item::before,
        .stepper-item::after {
            position: absolute;
            content: "";
            height: 3px;
            width: 100%;
            top: 22px;
            z-index: 1;
            background: repeating-linear-gradient(to right, #e2e8f0, #e2e8f0 5px, transparent 5px, transparent 10px);
            transition: all 0.4s ease;
        }

        .stepper-item::before {
            left: -50%;
        }

        .stepper-item::after {
            left: 50%;
        }

        .stepper-item:first-child::before,
        .stepper-item:last-child::after {
            content: none;
        }

        /* Step Circle */
        .step-circle {
            position: relative;
            z-index: 3;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background-color: var(--bs-body-bg, #f8fafc);
            color: #94a3b8;
            border: 3px solid #e2e8f0;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 10px;
            font-size: 1.15rem;
            line-height: 1;
            text-align: center;
            transition: all 0.3s ease;
        }

        /* Centering the icon inside each step circle */
        .step-circle i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            margin: 0;
            padding: 0;
        }

        /* Completed State */
        .stepper-item.completed .step-circle {
            background-color: #ecfdf5;
            color: #10b981;
            border-color: #10b981;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.25);
        }

        .stepper-item.completed::after,
        .stepper-item.completed+.stepper-item::before {
            background: #10b981;
        }

        /* Active State */
        .stepper-item.active .step-circle {
            background-color: #eff6ff;
            color: #3b82f6;
            border-color: #3b82f6;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.35);
            animation: pulse-blue 2s infinite;
        }

        .stepper-item.active::before {
            background: #10b981;
        }

        .stepper-item.active::after,
        .stepper-item.active+.stepper-item::before {
            background: repeating-linear-gradient(to right, #60a5fa, #60a5fa 5px, transparent 5px, transparent 10px);
        }

        /* Rejected / Cancelled / Expired State */
        .stepper-item.rejected .step-circle,
        .stepper-item.cancelled .step-circle,
        .stepper-item.expired .step-circle {
            background-color: #fef2f2;
            color: #ef4444;
            border-color: #ef4444;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
        }

        .stepper-item.rejected::after,
        .stepper-item.rejected+.stepper-item::before {
            background: #ef4444;
        }

        /* Text labels */
        .step-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 2px;
            transition: all 0.3s ease;
        }

        .step-desc {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .stepper-item.completed .step-label {
            color: #059669;
        }

        .stepper-item.active .step-label {
            color: #2563eb;
        }

        .stepper-item.rejected .step-label,
        .stepper-item.cancelled .step-label,
        .stepper-item.expired .step-label {
            color: #dc2626;
        }

        @keyframes pulse-blue {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }

        /* Stat Card */
        .stat-card {
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 16px;
            background: var(--bs-card-bg, #ffffff);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .icon-box-shape {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .icon-bg-indigo {
            background-color: rgba(99, 102, 241, 0.12);
            color: #6366f1;
        }

        .icon-bg-purple {
            background-color: rgba(139, 92, 246, 0.12);
            color: #8b5cf6;
        }

        .icon-bg-amber {
            background-color: rgba(245, 158, 11, 0.12);
            color: #d97706;
        }

        .icon-bg-green {
            background-color: rgba(16, 185, 129, 0.12);
            color: #10b981;
        }

        .icon-bg-red {
            background-color: rgba(239, 68, 68, 0.12);
            color: #ef4444;
        }

        .icon-bg-teal {
            background-color: rgba(20, 184, 166, 0.12);
            color: #14b8a6;
        }

        /* Spotlight & Lab Cards */
        .spotlight-card {
            border-radius: 18px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            background: var(--bs-card-bg, #ffffff);
            overflow: hidden;
        }

        .lab-card {
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 16px;
            background: var(--bs-card-bg, #ffffff);
            transition: all 0.3s ease;
        }

        .lab-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            border-color: rgba(99, 102, 241, 0.3);
        }

        /* Dark Mode Adjustments */
        [data-bs-theme="dark"] .quick-action-card,
        [data-bs-theme="dark"] .stepper-card,
        [data-bs-theme="dark"] .stat-card,
        [data-bs-theme="dark"] .spotlight-card,
        [data-bs-theme="dark"] .lab-card,
        [data-bs-theme="dark"] .chart-card {
            border-color: rgba(255, 255, 255, 0.08);
        }

        [data-bs-theme="dark"] .quick-action-card,
        [data-bs-theme="dark"] .stepper-card,
        [data-bs-theme="dark"] .stat-card,
        [data-bs-theme="dark"] .spotlight-card,
        [data-bs-theme="dark"] .lab-card,
        [data-bs-theme="dark"] .chart-card {
            background-color: #1e1e2d;
        }

        /* Stat card label tetap terbaca di dark mode */
        [data-bs-theme="dark"] .stat-card small {
            color: var(--bs-secondary-color);
        }

        [data-bs-theme="dark"] .step-circle {
            background-color: #1e1e2d;
            border-color: #323248;
            color: #6c757d;
        }

        [data-bs-theme="dark"] .stepper-item::before,
        [data-bs-theme="dark"] .stepper-item::after {
            background: repeating-linear-gradient(to right, #323248, #323248 5px, transparent 5px, transparent 10px);
        }

        /* ── Dark Mode: Inner detail boxes (bg-body-tertiary fallback) ── */
        .detail-box {
            background-color: var(--bs-secondary-bg, #f1f5f9);
            border-color: var(--bs-border-color, rgba(0, 0, 0, 0.06));
        }

        [data-bs-theme="dark"] .detail-box {
            background-color: #1b1b2b;
            border-color: #35354f;
        }

        /* ── Dark Mode: Approval Chart Card ── */
        .chart-card {
            background-color: var(--bs-card-bg, #ffffff);
            border-color: rgba(0, 0, 0, 0.06);
        }

        [data-bs-theme="dark"] .chart-card {
            background-color: #1e1e2d;
            border-color: rgba(255, 255, 255, 0.08);
        }

        /* ── Dark Mode: Contextual Action Box ── */
        .action-box {
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
        }

        .action-box .action-title {
            color: var(--bs-body-color);
        }

        .action-box .action-desc {
            color: var(--bs-secondary-color);
        }

        /* ── Dark Mode: Spotlight detail values ── */
        .spotlight-value {
            color: var(--bs-body-color);
        }

        .spotlight-label {
            color: var(--bs-secondary-color);
        }

        /* ── Dark Mode: Lab card ── */
        .lab-card .lab-name {
            color: var(--bs-body-color);
        }

        .lab-card .lab-person {
            color: var(--bs-body-color);
        }

        .lab-card .lab-loc {
            color: var(--bs-secondary-color);
        }

        /* Dark: lab badge tetap kontras */
        [data-bs-theme="dark"] .bg-indigo-subtle {
            background-color: rgba(99, 102, 241, 0.25) !important;
        }

        [data-bs-theme="dark"] .badge.bg-indigo-subtle {
            color: #a5b4fc !important;
        }

        /* ── Dark Mode: Stat card numbers ── */
        .stat-card .stat-number {
            color: var(--bs-body-color);
        }

        /* ── Dark Mode: Hero glass pill text ── */
        [data-bs-theme="dark"] .hero-glass-pill {
            background: rgba(255, 255, 255, 0.12);
        }

        /* ── Dark Mode: Stepper labels ── */
        [data-bs-theme="dark"] .step-label {
            color: #a5b4fc;
        }

        [data-bs-theme="dark"] .step-desc {
            color: #7b7f9e;
        }

        [data-bs-theme="dark"] .stepper-item.completed .step-label {
            color: #34d399;
        }

        [data-bs-theme="dark"] .stepper-item.active .step-label {
            color: #60a5fa;
        }

        [data-bs-theme="dark"] .stepper-item.rejected .step-label,
        [data-bs-theme="dark"] .stepper-item.cancelled .step-label,
        [data-bs-theme="dark"] .stepper-item.expired .step-label {
            color: #f87171;
        }

        /* ── Dark Mode: Chart total label ── */
        [data-bs-theme="dark"] .apexcharts-donut-label,
        [data-bs-theme="dark"] .apexcharts-datalabel,
        [data-bs-theme="dark"] .apexcharts-datalabel-label,
        [data-bs-theme="dark"] .apexcharts-datalabel-value {
            fill: #e0e0e0 !important;
            color: #e0e0e0 !important;
        }

        /* Dark: ApexCharts legend text */
        [data-bs-theme="dark"] .apexcharts-legend-text {
            color: #e0e0e0 !important;
        }

        [data-bs-theme="dark"] .apexcharts-tooltip {
            background: #1e1e2d !important;
            color: #fff !important;
            border: 1px solid #323248 !important;
        }

        [data-bs-theme="dark"] .apexcharts-tooltip-title {
            background: #2a2a3d !important;
            color: #fff !important;
        }

        /* Dark: Badge text stays readable */
        [data-bs-theme="dark"] .badge.bg-primary.bg-opacity-10 {
            color: #a5b4fc !important;
        }

        @media (max-width: 575.98px) {
            .hero-banner {
                padding: 1.5rem 1.25rem !important;
                border-radius: 16px;
            }

            .hero-banner h3 {
                font-size: 1.35rem;
            }

            .icon-box-shape {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
                border-radius: 10px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $hasSignature = !empty(Auth::user()->signature_path);
        $hasLoan = $jml_pengajuan_mhs > 0;

        $loanStatus = $pinjaman_lab_terakhir ? $pinjaman_lab_terakhir->status : null;
        $isApproved = in_array($loanStatus, ['disetujui', 'selesai']);
        $isPending = in_array($loanStatus, ['pending_plp', 'pending_kalab']);
        $isExpired = $loanStatus === 'kadaluarsa';
        $isRejected = $loanStatus === 'ditolak';
        $isCancelled = in_array($loanStatus, ['dibatalkan', 'dibatalkan_mahasiswa']);

        $presensi = $pinjaman_lab_terakhir && $isApproved ? $pinjaman_lab_terakhir->presensiTerakhir : null;
        $presensiStatus = $presensi ? $presensi->status_presensi : null;

        // Step 1: Upload TTD
        $step1_status = $hasSignature ? 'completed' : 'active';

        // Step 2: Mengajukan Peminjaman
        if (!$hasSignature) {
            $step2_status = 'waiting';
        } elseif (!$hasLoan) {
            $step2_status = 'active';
        } else {
            $step2_status = 'completed';
        }

        // Step 3: Setuju
        if (!$hasLoan) {
            $step3_status = 'waiting';
        } elseif ($isPending) {
            $step3_status = 'active';
        } elseif ($isApproved) {
            $step3_status = 'completed';
        } elseif ($isRejected) {
            $step3_status = 'rejected';
        } elseif ($isCancelled) {
            $step3_status = 'cancelled';
        } elseif ($isExpired) {
            $step3_status = 'expired';
        } else {
            $step3_status = 'waiting';
        }

        // Step 4: Presensi
        if (!$isApproved) {
            $step4_status = 'waiting';
        } elseif ($presensiStatus == 'belum_hadir' || !$presensi) {
            $step4_status = 'active';
        } else {
            $step4_status = 'completed';
        }

        // Step 5: ACC Satpam
        if (!$presensi || $presensiStatus == 'belum_hadir') {
            $step5_status = 'waiting';
        } elseif (in_array($presensiStatus, ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])) {
            $step5_status = 'active';
        } else {
            $step5_status = 'completed';
        }

        // Step 6: Meminjam Lab
        if (!$presensi || in_array($presensiStatus, ['belum_hadir', 'menunggu_konfirmasi_masuk'])) {
            $step6_status = 'waiting';
        } elseif ($presensiStatus == 'didalam') {
            $step6_status = 'active';
        } elseif ($presensiStatus == 'selesai') {
            $step6_status = 'completed';
        } else {
            $step6_status = 'waiting';
        }
    @endphp

    <!-- 1. Banner Welcoming Dynamic (Hero Header) -->
    <div class="hero-banner p-4 p-md-5 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if ($hasSignature)
                        <span class="hero-glass-pill text-white">
                            <i class="bi bi-patch-check-fill text-success me-2 fs-6"></i> TTD Digital Terverifikasi
                        </span>
                    @else
                        <span class="hero-glass-pill text-warning-light">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-6"></i> TTD Digital Belum
                            Diunggah
                        </span>
                    @endif
                    <span id="dashboard-date" class="hero-glass-pill">
                        <i class="bi bi-calendar3 me-2"></i> {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d M Y') }}
                    </span>
                    <span id="dashboard-time" class="hero-glass-pill">
                        <i class="bi bi-clock-history me-2"></i> {{ \Carbon\Carbon::now()->translatedFormat('H:i') }} WIB
                    </span>
                </div>
                <h3 class="fw-bold mb-2 text-white">
                    Selamat Datang Kembali, {{ Auth::user()->nama_asli ?? Auth::user()->full_name }}! 👋
                </h3>
                <p class="mb-0 text-white-50 fs-6">
                    Pusat Layanan & Dashboard Peminjaman Laboratorium Terpadu SIMLAB.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('peminjaman.index') }}"
                    class="btn btn-light btn-lg fw-bold rounded-pill px-4 btn-shine text-indigo">
                    <i class="bi bi-plus-circle-fill me-2"></i> Buat Pengajuan Baru
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Quick Action Grid (Kartu Pintas Interaktif) -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
        <div class="col">
            <a href="{{ route('peminjaman.index') }}" class="quick-action-card p-3 p-xl-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="quick-action-icon icon-bg-indigo">
                        <i class="bi bi-file-earmark-plus-fill"></i>
                    </div>
                    <i class="bi bi-arrow-right fs-5 quick-action-desc"></i>
                </div>
                <h6 class="fw-bold mb-1 quick-action-title">Ajukan Peminjaman</h6>
                <p class="small mb-0 quick-action-desc">Formulir peminjaman lab</p>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('presensi.index') }}" class="quick-action-card p-3 p-xl-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="quick-action-icon icon-bg-teal">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <i class="bi bi-arrow-right fs-5 quick-action-desc"></i>
                </div>
                <h6 class="fw-bold mb-1 quick-action-title">Presensi Lab</h6>
                <p class="small mb-0 quick-action-desc">Scan barcode & masuk lab</p>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('peminjaman.index') }}" class="quick-action-card p-3 p-xl-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="quick-action-icon icon-bg-purple">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <i class="bi bi-arrow-right fs-5 quick-action-desc"></i>
                </div>
                <h6 class="fw-bold mb-1 quick-action-title">Riwayat Peminjaman</h6>
                <p class="small mb-0 quick-action-desc">Cek status & histori</p>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('profile.index') }}" class="quick-action-card p-3 p-xl-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="quick-action-icon icon-bg-amber">
                        <i class="bi bi-pen-fill"></i>
                    </div>
                    <i class="bi bi-arrow-right fs-5 quick-action-desc"></i>
                </div>
                <h6 class="fw-bold mb-1 quick-action-title">Tanda Tangan Digital</h6>
                <p class="small mb-0 quick-action-desc">
                    {{ $hasSignature ? 'Sudah Terverifikasi' : 'Belum Diunggah' }}
                </p>
            </a>
        </div>
    </div>

    <!-- 3. Interactive Stepper Timeline (Status Peminjaman Terkini) -->
    <div class="card stepper-card">
        <div
            class="card-header pb-0 pt-3 px-3 px-sm-4 bg-transparent border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-1 fs-6 fs-sm-5 d-flex align-items-center fw-bold">
                    <i class="bi bi-diagram-3-fill text-indigo me-2"></i>Status Alur Peminjaman Terkini
                </h5>
                <p class="text-muted small mb-0">Lacak tahapan proses peminjaman laboratorium Anda secara real-time.</p>
            </div>
        </div>
        <div class="card-body px-3 px-sm-4 pb-4">
            <div class="stepper-container">
                <div class="stepper-wrapper">
                    <!-- Step 1: Upload TTD -->
                    <div class="stepper-item {{ $step1_status }}">
                        <div class="step-circle">
                            @if ($step1_status == 'completed')
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-pen-fill"></i>
                            @endif
                        </div>
                        <div class="step-label">Upload TTD</div>
                        <div class="step-desc">
                            @if ($hasSignature)
                                Selesai
                            @else
                                Belum Diunggah
                            @endif
                        </div>
                    </div>

                    <!-- Step 2: Mengajukan Peminjaman -->
                    <div class="stepper-item {{ $step2_status }}">
                        <div class="step-circle">
                            @if ($step2_status == 'completed')
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-file-earmark-plus-fill"></i>
                            @endif
                        </div>
                        <div class="step-label">Pengajuan</div>
                        <div class="step-desc">
                            @if ($hasLoan)
                                Diajukan
                            @elseif($step2_status == 'active')
                                Siap Diajukan
                            @else
                                Menunggu
                            @endif
                        </div>
                    </div>

                    <!-- Step 3: Persetujuan -->
                    <div class="stepper-item {{ $step3_status }}">
                        <div class="step-circle">
                            @if ($step3_status == 'completed')
                                <i class="bi bi-check-lg"></i>
                            @elseif(in_array($step3_status, ['rejected', 'cancelled', 'expired']))
                                <i class="bi bi-x-lg"></i>
                            @else
                                <i class="bi bi-shield-check"></i>
                            @endif
                        </div>
                        <div class="step-label">Persetujuan</div>
                        <div class="step-desc">
                            @if ($step3_status == 'active')
                                Diproses PLP/Kalab
                            @elseif($step3_status == 'completed')
                                Disetujui
                            @elseif($step3_status == 'rejected')
                                Ditolak
                            @elseif($step3_status == 'cancelled')
                                Dibatalkan
                            @elseif($step3_status == 'expired')
                                Kadaluarsa
                            @else
                                Menunggu
                            @endif
                        </div>
                    </div>

                    <!-- Step 4: Presensi -->
                    <div class="stepper-item {{ $step4_status }}">
                        <div class="step-circle">
                            @if ($step4_status == 'completed')
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-qr-code-scan"></i>
                            @endif
                        </div>
                        <div class="step-label">Presensi</div>
                        <div class="step-desc">
                            @if ($step4_status == 'active')
                                Silakan Presensi
                            @elseif($step4_status == 'completed')
                                Sudah Hadir
                            @else
                                Menunggu
                            @endif
                        </div>
                    </div>

                    <!-- Step 5: ACC Satpam -->
                    <div class="stepper-item {{ $step5_status }}">
                        <div class="step-circle">
                            @if ($step5_status == 'completed')
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-person-badge-fill"></i>
                            @endif
                        </div>
                        <div class="step-label">ACC Satpam</div>
                        <div class="step-desc">
                            @if ($step5_status == 'active')
                                Verifikasi Satpam
                            @elseif($step5_status == 'completed')
                                Dikonfirmasi
                            @else
                                Menunggu
                            @endif
                        </div>
                    </div>

                    <!-- Step 6: Meminjam Lab -->
                    <div class="stepper-item {{ $step6_status }}">
                        <div class="step-circle">
                            @if ($step6_status == 'completed')
                                <i class="bi bi-check-all"></i>
                            @else
                                <i class="bi bi-door-open-fill"></i>
                            @endif
                        </div>
                        <div class="step-label">Selesai</div>
                        <div class="step-desc">
                            @if ($step6_status == 'active')
                                Sedang Menggunakan
                            @elseif($step6_status == 'completed')
                                Selesai
                            @else
                                Menunggu
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contextual Action Box -->
            <div
                class="mt-4 p-3 rounded-3 border d-flex flex-wrap align-items-center justify-content-between gap-3 action-box">
                @if (!$hasSignature)
                    <div class="d-flex align-items-center text-warning">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                        <div>
                            <div class="fw-bold action-title">Langkah 1: Unggah Tanda Tangan Digital</div>
                            <small class="action-desc">Anda wajib mengunggah TTD digital pada profil sebelum dapat membuat
                                pengajuan peminjaman.</small>
                        </div>
                    </div>
                    <a href="{{ route('profile.index') }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-upload me-1"></i> Upload TTD Sekarang
                    </a>
                @elseif (!$hasLoan)
                    <div class="d-flex align-items-center text-info">
                        <i class="bi bi-info-circle-fill fs-4 me-2 text-primary"></i>
                        <div>
                            <div class="fw-bold action-title">Langkah 2: Buat Pengajuan Peminjaman Lab</div>
                            <small class="action-desc">Anda siap mengajukan peminjaman laboratorium untuk praktikum atau
                                tugas akhir.</small>
                        </div>
                    </div>
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Buat Pengajuan
                    </a>
                @elseif ($isPending)
                    <div class="d-flex align-items-center text-primary">
                        <i class="bi bi-hourglass-split fs-4 me-2 text-warning"></i>
                        <div>
                            <div class="fw-bold action-title">Langkah 3: Menunggu Approval</div>
                            <small class="action-desc">Pengajuan Anda sedang ditinjau oleh PLP / Kepala
                                Laboratorium.</small>
                        </div>
                    </div>
                    <a href="{{ route('peminjaman.index') }}"
                        class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-search me-1"></i> Cek Status Detail
                    </a>
                @elseif ($isApproved && ($presensiStatus == 'belum_hadir' || !$presensi))
                    <div class="d-flex align-items-center text-success">
                        <i class="bi bi-qr-code-scan fs-4 me-2 text-success"></i>
                        <div>
                            <div class="fw-bold action-title">Langkah 4: Lakukan Presensi Masuk</div>
                            <small class="action-desc">Pengajuan Anda disetujui! Lakukan scan barcode atau presensi saat
                                tiba di lokasi lab.</small>
                        </div>
                    </div>
                    <a href="{{ route('presensi.index') }}" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-qr-code me-1"></i> Presensi Sekarang
                    </a>
                @elseif ($isApproved && in_array($presensiStatus, ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar']))
                    <div class="d-flex align-items-center text-warning">
                        <i class="bi bi-person-badge fs-4 me-2 text-warning"></i>
                        <div>
                            <div class="fw-bold action-title">Langkah 5: Konfirmasi Satpam</div>
                            <small class="action-desc">Presensi berhasil dikirim. Menunggu verifikasi oleh petugas Satpam
                                di laboratorium.</small>
                        </div>
                    </div>
                    <a href="{{ route('presensi.index') }}"
                        class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-eye me-1"></i> Lihat Status Presensi
                    </a>
                @elseif ($isApproved && $presensiStatus == 'didalam')
                    <div class="d-flex align-items-center text-primary">
                        <i class="bi bi-door-open-fill fs-4 me-2 text-primary"></i>
                        <div>
                            <div class="fw-bold action-title">Langkah 6: Sedang Menggunakan Laboratorium</div>
                            <small class="action-desc">Selamat berpraktikum! Lakukan presensi keluar jika kegiatan Anda
                                telah selesai.</small>
                        </div>
                    </div>
                    <a href="{{ route('presensi.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-box-arrow-right me-1"></i> Presensi Keluar
                    </a>
                @elseif ($isApproved && $presensiStatus == 'selesai')
                    <div class="d-flex align-items-center text-success">
                        <i class="bi bi-check-circle-fill fs-4 me-2 text-success"></i>
                        <div>
                            <div class="fw-bold action-title">Peminjaman Laboratorium Selesai</div>
                            <small class="action-desc">Terima kasih telah menggunakan fasilitas laboratorium dengan
                                tertib.</small>
                        </div>
                    </div>
                    <a href="{{ route('peminjaman.index') }}"
                        class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Pinjam Lagi
                    </a>
                @elseif ($isRejected)
                    <div class="d-flex align-items-center text-danger">
                        <i class="bi bi-x-circle-fill fs-4 me-2 text-danger"></i>
                        <div>
                            <div class="fw-bold action-title">Pengajuan Peminjaman Ditolak</div>
                            <small class="action-desc">Catatan:
                                {{ $pinjaman_lab_terakhir->catatan_tolak ?? 'Tidak ada catatan.' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-arrow-repeat me-1"></i> Ajukan Ulang
                    </a>
                @else
                    <div class="d-flex align-items-top text-muted">
                        <i class="bi bi-info-circle fs-4 me-3"></i>
                        <div>
                            <div class="fw-bold action-title">Status Peminjaman Terakhir:
                                {{ ucfirst($loanStatus ?? 'Belum ada') }}</div>
                            <small class="action-desc">Akses menu peminjaman untuk informasi lengkap.</small>
                        </div>
                    </div>
                    <a href="{{ route('peminjaman.index') }}"
                        class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-arrow-right me-1"></i> Detail Peminjaman
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- 4. Baris Statistik & Spotlight -->
    <div class="row g-3 mb-4">
        <!-- Main Column (Stat & Spotlight) -->
        <div class="col-12 col-lg-8">
            <!-- Summary Stat Cards Grid -->
            <div class="row row-cols-2 row-cols-md-4 g-2 g-sm-3 mb-3">
                <div class="col">
                    <div class="stat-card p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-shape icon-bg-purple me-2">
                                <i class="bi bi-file-earmark-text-fill"></i>
                            </div>
                            <div>
                                <small class="text-muted fw-semibold d-block" style="font-size: 0.75rem;">Total
                                    Pengajuan</small>
                                <h4 class="fw-bold mb-0 stat-number" style="font-size: 1.25rem;">{{ $jml_pengajuan_mhs }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-shape icon-bg-amber me-2">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div>
                                <small class="text-muted fw-semibold d-block" style="font-size: 0.75rem;">Pending</small>
                                <h4 class="fw-bold mb-0 stat-number" style="font-size: 1.25rem;">{{ $jml_pending_mhs }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-shape icon-bg-green me-2">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div>
                                <small class="text-muted fw-semibold d-block"
                                    style="font-size: 0.75rem;">Disetujui</small>
                                <h4 class="fw-bold mb-0 stat-number" style="font-size: 1.25rem;">{{ $jml_approved_mhs }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-shape icon-bg-red me-2">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                            <div>
                                <small class="text-muted fw-semibold d-block" style="font-size: 0.75rem;">Ditolak</small>
                                <h4 class="fw-bold mb-0 stat-number" style="font-size: 1.25rem;">{{ $jml_rejected_mhs }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Loan Spotlight Card -->
            <div class="card spotlight-card h-90 mb-0">
                <div
                    class="card-header bg-transparent pt-3 pb-2 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0 fw-bold d-flex align-items-center">
                        <i class="bi bi-stars text-amber me-2"></i>Sorotan Peminjaman Aktif / Terakhir
                    </h5>
                    @if ($pinjaman_lab_terakhir)
                        <a href="{{ route('peminjaman.index') }}"
                            class="btn btn-sm btn-link text-decoration-none fw-semibold p-0">
                            Lihat Semua <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
                <div class="card-body pt-2">
                    @if ($pinjaman_lab_terakhir)
                        <div class="p-3 rounded-3 detail-box border">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold mb-1">
                                        {{ $pinjaman_lab_terakhir->lab->kode_lab ?? '-' }}
                                    </span>
                                    <h5 class="fw-bold mb-0 lab-name">
                                        {{ $pinjaman_lab_terakhir->lab->nama_lab ?? '-' }}
                                    </h5>
                                </div>
                                <div>
                                    @if ($pinjaman_lab_terakhir->status == 'pending_plp')
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i
                                                class="bi bi-clock me-1"></i> Menunggu PLP</span>
                                    @elseif ($pinjaman_lab_terakhir->status == 'pending_kalab')
                                        <span class="badge bg-info px-3 py-2 rounded-pill"><i
                                                class="bi bi-clock me-1"></i> Menunggu Kalab</span>
                                    @elseif ($pinjaman_lab_terakhir->status == 'disetujui')
                                        <span class="badge bg-success px-3 py-2 rounded-pill"><i
                                                class="bi bi-check-circle me-1"></i> Disetujui</span>
                                    @elseif ($pinjaman_lab_terakhir->status == 'ditolak')
                                        <span class="badge bg-danger px-3 py-2 rounded-pill"><i
                                                class="bi bi-x-circle me-1"></i> Ditolak</span>
                                    @elseif ($pinjaman_lab_terakhir->status == 'kadaluarsa')
                                        <span class="badge bg-secondary px-3 py-2 rounded-pill"><i
                                                class="bi bi-slash-circle me-1"></i> Kadaluarsa</span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2 rounded-pill"><i
                                                class="bi bi-x-circle me-1"></i> Dibatalkan</span>
                                    @endif
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-bullseye text-indigo me-3 fs-5"></i>
                                        <div>
                                            <small class="text-muted d-block spotlight-label"
                                                style="font-size: 0.78rem;">Tujuan Peminjaman</small>
                                            <span
                                                class="fw-semibold spotlight-value">{{ $pinjaman_lab_terakhir->tujuan }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt-fill text-danger me-3 fs-5"></i>
                                        <div>
                                            <small class="text-muted d-block spotlight-label"
                                                style="font-size: 0.78rem;">Lokasi Laboratorium</small>
                                            <span
                                                class="fw-semibold spotlight-value">{{ $pinjaman_lab_terakhir->lab->lokasi ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar-event text-success me-3 fs-5"></i>
                                        <div>
                                            <small class="text-muted d-block spotlight-label"
                                                style="font-size: 0.78rem;">Waktu Mulai</small>
                                            <span
                                                class="fw-semibold spotlight-value">{{ $pinjaman_lab_terakhir->waktu_mulai->format('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar-check text-purple me-3 fs-5"></i>
                                        <div>
                                            <small class="text-muted d-block spotlight-label"
                                                style="font-size: 0.78rem;">Waktu Selesai</small>
                                            <span
                                                class="fw-semibold spotlight-value">{{ $pinjaman_lab_terakhir->waktu_selesai->format('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($pinjaman_lab_terakhir->status == 'ditolak' && $pinjaman_lab_terakhir->catatan_tolak)
                                <div
                                    class="mt-3 p-2 rounded bg-danger bg-opacity-10 text-danger border border-danger-subtle small">
                                    <strong>Catatan Penolakan:</strong> {{ $pinjaman_lab_terakhir->catatan_tolak }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4 my-2">
                            <div class="icon-box-shape icon-bg-indigo mx-auto mb-3"
                                style="width: 60px; height: 60px; font-size: 1.8rem;">
                                <i class="bi bi-journal-plus"></i>
                            </div>
                            <h6 class="fw-bold mb-1 stat-number">Belum Ada Peminjaman Lab</h6>
                            <p class="text-muted small mb-3">Anda belum memiliki riwayat pengajuan peminjaman laboratorium.
                            </p>
                            <a href="{{ route('peminjaman.index') }}"
                                class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                                <i class="bi bi-plus-lg me-1"></i> Buat Pengajuan Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column (Approval Chart) -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 mb-0 border-0 shadow-sm rounded-4 chart-card">
                <div class="card-header bg-transparent pt-3 pb-0 border-0">
                    <h5 class="card-title fs-6 fs-sm-5 mb-0 fw-bold d-flex align-items-center">
                        <i class="bi bi-pie-chart-fill text-indigo me-2"></i>Status Persetujuan
                    </h5>
                    <small class="text-muted">Rasio persetujuan peminjaman Anda.</small>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div id="chart-approval-status" class="w-100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Laboratorium Explorer Grid -->
    <div class="mb-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-0 fs-6 fs-sm-5 d-flex align-items-center">
                    <i class="bi bi-building-gear text-indigo me-2"></i>Eksplorasi Laboratorium Kampus
                </h5>
                <small class="text-muted">Daftar laboratorium yang dapat dipinjam beserta penanggung jawab.</small>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
            @forelse ($lab_managers as $lab_manager)
                <div class="col">
                    <div class="lab-card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-indigo-subtle text-indigo fw-bold px-2 py-1 rounded">
                                    {{ $lab_manager->lab->kode_lab }}
                                </span>
                                <small class="lab-loc d-flex align-items-center" style="font-size: 0.78rem;">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $lab_manager->lab->lokasi }}
                                </small>
                            </div>
                            <h6 class="fw-bold lab-name mb-3" style="font-size: 0.95rem;">
                                {{ $lab_manager->lab->nama_lab }}
                            </h6>
                            <div class="p-2 rounded detail-box mb-3" style="font-size: 0.8rem;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted"><i class="bi bi-person-badge me-1"></i> Kalab:</span>
                                    <span class="fw-semibold lab-person text-truncate ms-2"
                                        style="max-width: 160px;">{{ $lab_manager->kalab->full_name ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="bi bi-person-workspace me-1"></i> PLP:</span>
                                    <span class="fw-semibold lab-person text-truncate ms-2"
                                        style="max-width: 160px;">{{ $lab_manager->plp->full_name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('peminjaman.index') }}"
                                class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-semibold">
                                <i class="bi bi-calendar-plus me-1"></i> Ajukan Peminjaman
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-4 text-muted detail-box rounded-3">
                        <i class="bi bi-building-x fs-2 d-block mb-2"></i>
                        <span>Belum ada data laboratorium terdaftar.</span>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateEl = document.getElementById('dashboard-date');
            const timeEl = document.getElementById('dashboard-time');

            if (dateEl && timeEl) {
                const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    day: '2-digit',
                    month: 'short',
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

        let optionsApprovalStatus = {
            series: [{{ $jml_approved_mhs }}, {{ $jml_rejected_mhs }}, {{ $jml_pending_mhs }}],
            labels: ["Disetujui", "Ditolak", "Pending"],
            colors: ["#10b981", "#ef4444", "#f59e0b"],
            chart: {
                type: "donut",
                width: "100%",
                height: 280,
                fontFamily: 'inherit'
            },
            legend: {
                position: "bottom",
                horizontalAlign: "center"
            },
            stroke: {
                show: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: "65%",
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function(w) {
                                    return {{ $jml_pengajuan_mhs }};
                                }
                            }
                        }
                    },
                },
            },
            dataLabels: {
                enabled: false
            }
        }

        let chartApprovalStatus = new ApexCharts(
            document.querySelector("#chart-approval-status"),
            optionsApprovalStatus
        )
        chartApprovalStatus.render()
    </script>
@endsection
