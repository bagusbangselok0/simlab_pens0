@extends('layouts.app')

@section('styles')
<style>
    .jadwal-table th {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        vertical-align: middle;
        white-space: nowrap;
    }
    .jadwal-table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .hari-cell {
        font-weight: 700;
        background-color: rgba(var(--bs-primary-rgb), 0.04);
        text-align: center;
        width: 100px;
    }
    .nav-pills .nav-link.room-tab:not(.active) {
        color: #334155;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .nav-pills .nav-link.room-tab:not(.active):hover {
        color: #0f172a;
        background-color: #eef2f7;
    }
    .nav-pills .nav-link.room-tab.active {
        border: 1px solid transparent;
    }
    .jadwal-time-text {
        color: var(--bs-primary, #435ebe);
    }
    .jadwal-chip-accent {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary, #435ebe);
        border: 1px solid rgba(var(--bs-primary-rgb), 0.24);
    }
    .jadwal-chip-info {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .jadwal-chip-kelas {
        background-color: #eef2ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
    }
    .jadwal-chip-meta {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .jadwal-badge-tp {
        background-color: #0284c7;
        color: #ffffff;
    }
    .jadwal-subtext {
        color: #475569;
    }
    .jadwal-istirahat-text {
        color: #b45309;
    }
    .jadwal-empty-box {
        background-color: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 0.75rem;
    }
    .mobile-day-group {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        overflow: hidden;
        background-color: #ffffff;
    }
    .mobile-day-group.is-today {
        border-color: var(--bs-primary, #435ebe);
        box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.12);
    }
    .mobile-day-header {
        background-color: #f8fafc;
        color: #1e293b;
        padding: 0.6rem 0.85rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .mobile-day-group.is-today .mobile-day-header {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }
    .mobile-session-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.6rem;
        padding: 0.75rem;
        background-color: #f8fafc;
        color: #0f172a;
    }
    .mobile-session-card.conflict {
        border-color: #ef4444;
        background-color: #fef2f2;
    }

    /* Penyesuaian kontras khusus Dark Mode (Mazer #1e1e2d) */
    html[data-bs-theme="dark"] .nav-pills .nav-link.room-tab:not(.active) {
        color: #cbd5e1 !important;
        background-color: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.12);
    }
    html[data-bs-theme="dark"] .nav-pills .nav-link.room-tab:not(.active):hover {
        color: #f8fafc !important;
        background-color: rgba(255, 255, 255, 0.09);
    }
    html[data-bs-theme="dark"] .jadwal-table thead th {
        background-color: #252538;
        color: #e2e8f0;
        border-color: rgba(255, 255, 255, 0.1);
    }
    html[data-bs-theme="dark"] .jadwal-table td {
        border-color: rgba(255, 255, 255, 0.08);
    }
    html[data-bs-theme="dark"] .hari-cell {
        background-color: rgba(255, 255, 255, 0.03);
        color: #f1f5f9;
    }
    html[data-bs-theme="dark"] .jadwal-time-text {
        color: #93c5fd !important;
    }
    html[data-bs-theme="dark"] .jadwal-chip-accent {
        background-color: rgba(99, 102, 241, 0.2);
        color: #bfdbfe !important;
        border-color: rgba(147, 197, 253, 0.32);
    }
    html[data-bs-theme="dark"] .jadwal-chip-info {
        background-color: rgba(14, 165, 233, 0.18);
        color: #7dd3fc;
        border-color: rgba(125, 211, 252, 0.28);
    }
    html[data-bs-theme="dark"] .jadwal-chip-kelas {
        background-color: rgba(129, 140, 248, 0.18);
        color: #e0e7ff;
        border-color: rgba(129, 140, 248, 0.34);
    }
    html[data-bs-theme="dark"] .jadwal-chip-meta {
        background-color: rgba(148, 163, 184, 0.14);
        color: #cbd5e1;
        border-color: rgba(148, 163, 184, 0.26);
    }
    html[data-bs-theme="dark"] .jadwal-badge-tp {
        background-color: #0369a1;
        color: #e0f2fe;
    }
    html[data-bs-theme="dark"] .jadwal-subtext {
        color: #94a3b8;
    }
    html[data-bs-theme="dark"] .jadwal-istirahat-text {
        color: #fbbf24;
    }
    html[data-bs-theme="dark"] .jadwal-empty-box {
        background-color: #181826;
        border-color: rgba(255, 255, 255, 0.15);
        color: #cbd5e1;
    }
    html[data-bs-theme="dark"] .mobile-day-group {
        background-color: #181826;
        border-color: rgba(255, 255, 255, 0.09);
    }
    html[data-bs-theme="dark"] .mobile-day-group.is-today {
        border-color: #60a5fa;
        box-shadow: 0 0 0 1px rgba(96, 165, 250, 0.28);
    }
    html[data-bs-theme="dark"] .mobile-day-header {
        background-color: #222236;
        color: #f1f5f9;
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }
    html[data-bs-theme="dark"] .mobile-day-group.is-today .mobile-day-header {
        background-color: rgba(59, 130, 246, 0.2);
    }
    html[data-bs-theme="dark"] .mobile-day-header .btn-quick-add {
        color: #93c5fd !important;
        border-color: rgba(147, 197, 253, 0.45) !important;
    }
    html[data-bs-theme="dark"] .mobile-session-card {
        background-color: #232336;
        border-color: rgba(255, 255, 255, 0.08);
        color: #f1f5f9;
    }
    html[data-bs-theme="dark"] .mobile-session-card .border-top {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    html[data-bs-theme="dark"] .mobile-session-card.conflict {
        border-color: rgba(248, 113, 113, 0.55);
        background-color: rgba(239, 68, 68, 0.14);
    }
    @media (max-width: 767.98px) {
        .jadwal-room-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            padding-bottom: 0.35rem;
        }
        .jadwal-room-tabs .nav-item {
            flex: 0 0 auto;
        }
        .jadwal-room-tabs .nav-link.room-tab {
            white-space: nowrap;
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
        }
    }
    @media (max-width: 575.98px) {
        .jadwal-action-bar {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem !important;
        }
        .jadwal-action-bar > .btn-group,
        .jadwal-action-bar > a.btn,
        .jadwal-action-bar > button.btn,
        .jadwal-action-bar > form {
            width: 100%;
        }
        .jadwal-action-bar > .btn-group > .btn,
        .jadwal-action-bar > form > .btn {
            width: 100%;
        }
        .jadwal-action-bar .btn {
            font-size: 0.84rem;
            padding: 0.45rem 0.55rem;
        }
        .jadwal-action-bar .dropdown-menu {
            min-width: 100%;
        }
        .jadwal-delete-periode-form {
            grid-column: 1 / -1;
        }
    }
</style>
@endsection

@section('content')
@php
    $canManage = Auth::check() && in_array(Auth::user()->role->slug, ['admin', 'plp'], true);
    $isoDayMap = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => "Jum'at",
    ];
    $todayHari = ($selectedPeriode && $selectedPeriode->is_active)
        ? ($isoDayMap[(int) now()->dayOfWeekIso] ?? null)
        : null;
@endphp

<section class="section">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header Kontrol & Filter Periode --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end justify-content-between">
                <div class="col-lg-5">
                    <form method="GET" action="{{ route('jadwal-lab.index') }}" id="periodeFilterForm">
                        <label for="periodeSelect" class="form-label fw-semibold mb-1">Periode Jadwal</label>
                        <div class="d-flex gap-2 align-items-center">
                            <select name="periode_id" id="periodeSelect" class="form-select" onchange="this.form.submit()">
                                @forelse ($periodes as $p)
                                    <option value="{{ $p->id }}" @selected($selectedPeriode && $selectedPeriode->id === $p->id)>
                                        {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @empty
                                    <option value="">Belum ada periode jadwal</option>
                                @endforelse
                            </select>
                            @if ($selectedPeriode)
                                @if ($selectedPeriode->is_active)
                                    <span class="badge bg-success text-nowrap px-3 py-2">Aktif</span>
                                @else
                                    <span class="badge bg-secondary text-nowrap px-3 py-2">Arsip</span>
                                @endif
                            @endif
                        </div>
                        @if ($selectedPeriode && $selectedPeriode->source_filename)
                            <small class="text-muted d-block mt-1 text-truncate" title="{{ $selectedPeriode->source_filename }}">
                                <i class="bi bi-file-earmark-excel me-1"></i>Sumber: {{ $selectedPeriode->source_filename }}
                            </small>
                        @endif
                    </form>
                </div>

                <div class="col-lg-7 d-flex flex-wrap gap-2 justify-content-lg-end jadwal-action-bar">
                    @if ($selectedPeriode && !empty($roomsData))
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Cetak PDF
                            </button>
                            <ul class="dropdown-menu dropdown-menu-sm-end">
                                @if ($isDosen && ($mySchedule['total'] ?? 0) > 0)
                                    <li>
                                        <a class="dropdown-item fw-semibold text-primary" target="_blank"
                                           href="{{ route('jadwal-lab.export-pdf', ['periode_id' => $selectedPeriode->id, 'scope' => 'mine']) }}">
                                            <i class="bi bi-person-workspace me-1"></i> Jadwal Mengajar Saya (1 Tabel)
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li>
                                    <a class="dropdown-item" id="btnPrintCurrentRoom" target="_blank"
                                       href="{{ route('jadwal-lab.export-pdf', ['periode_id' => $selectedPeriode->id, 'ruangan' => $activeRoom]) }}">
                                        <i class="bi bi-printer me-1"></i> Ruangan Aktif (<span id="printRoomLabel">{{ $activeRoom }}</span>)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                       href="{{ route('jadwal-lab.export-pdf', ['periode_id' => $selectedPeriode->id]) }}">
                                        <i class="bi bi-files me-1"></i> Semua Ruangan ({{ count($roomsData) }} Halaman)
                                    </a>
                                </li>
                            </ul>
                        </div>

                        @if ($isDosen && ($mySchedule['total'] ?? 0) > 0)
                            <div class="btn-group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                                </button>
                                <ul class="dropdown-menu dropdown-menu-sm-end">
                                    <li>
                                        <a class="dropdown-item fw-semibold text-primary"
                                           href="{{ route('jadwal-lab.export-excel', ['periode_id' => $selectedPeriode->id, 'scope' => 'mine']) }}">
                                            <i class="bi bi-person-workspace me-1"></i> Jadwal Mengajar Saya (1 Sheet)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                           href="{{ route('jadwal-lab.export-excel', ['periode_id' => $selectedPeriode->id]) }}">
                                            <i class="bi bi-files me-1"></i> Semua Ruangan ({{ count($roomsData) }} Sheet)
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @else
                            <a href="{{ route('jadwal-lab.export-excel', ['periode_id' => $selectedPeriode->id]) }}"
                               class="btn btn-success">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                            </a>
                        @endif
                    @endif

                    @if ($canManage)
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importJadwalModal">
                            <i class="bi bi-cloud-upload me-1"></i> Import & Convert Excel
                        </button>

                        @if ($selectedPeriode)
                            <button type="button" class="btn btn-outline-primary" id="btnAddEntry">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Jadwal
                            </button>

                            @if (!$selectedPeriode->is_active)
                                <form method="POST" action="{{ route('jadwal-lab.periode.activate', $selectedPeriode->id) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success">
                                        <i class="bi bi-check2-circle me-1"></i> Jadikan Aktif
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('jadwal-lab.periode.destroy', $selectedPeriode->id) }}"
                                  class="d-inline form-confirm-delete jadwal-delete-periode-form" data-confirm-text="Hapus periode {{ $selectedPeriode->nama_periode }} beserta seluruh jadwal di dalamnya?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Hapus Periode">
                                    <i class="bi bi-trash"></i><span class="d-sm-none ms-1">Hapus Periode</span>
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Peringatan Bentrok --}}
    @if (!empty($conflictMessages))
        <div class="alert alert-danger">
            <h6 class="alert-heading fw-bold mb-1">
                <i class="bi bi-exclamation-octagon-fill me-1"></i> Terdeteksi {{ count($conflictMessages) }} Bentrok Jadwal
            </h6>
            <ul class="mb-0 small ps-3">
                @foreach ($conflictMessages as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Konten Utama per Ruangan --}}
    @if ($selectedPeriode && !empty($roomsData))
        <div class="card">
            <div class="card-header pb-2">
                <div class="scroll-cue-wrapper">
                    <ul class="nav nav-pills gap-2 flex-md-wrap jadwal-room-tabs scroll-cue-list" id="roomTabs" role="tablist">
                        @if ($isDosen)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link room-tab {{ $activeTab === 'mine' ? 'active' : '' }}"
                                        id="my-schedule-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#my-schedule"
                                        data-scope="mine"
                                        type="button" role="tab">
                                    <i class="bi bi-person-workspace me-1"></i>Jadwal Saya
                                    <span class="badge {{ $activeTab === 'mine' ? 'bg-white text-dark' : 'bg-primary' }} ms-1">
                                        {{ $mySchedule['total'] }}
                                    </span>
                                </button>
                            </li>
                            <li class="nav-item d-none d-md-flex align-items-center" aria-hidden="true">
                                <span class="vr mx-1 my-1"></span>
                            </li>
                        @endif
                        @foreach ($roomsData as $roomName => $roomInfo)
                            @php
                                $tabId = 'room-' . \Illuminate\Support\Str::slug($roomName);
                                $isRoomActive = $activeTab === 'room' && $activeRoom === $roomName;
                            @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link room-tab {{ $isRoomActive ? 'active' : '' }}"
                                        id="{{ $tabId }}-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#{{ $tabId }}"
                                        data-room-name="{{ $roomName }}"
                                        type="button" role="tab">
                                    {{ $roomName }}
                                    <span class="badge {{ $isRoomActive ? 'bg-white text-dark' : 'bg-secondary' }} ms-1">
                                        {{ $roomInfo['total'] }}
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <button type="button" class="scroll-cue-chevron" aria-label="Geser tab ruangan ke kanan">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="tab-content" id="roomTabsContent">
                    @if ($isDosen)
                        <div class="tab-pane fade {{ $activeTab === 'mine' ? 'show active' : '' }}"
                             id="my-schedule" role="tabpanel">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <div>
                                    <h5 class="mb-0 fw-bold">
                                        <i class="bi bi-person-workspace jadwal-time-text me-1"></i> Jadwal Mengajar Saya
                                    </h5>
                                    <small class="text-muted">
                                        {{ Auth::user()->full_name }} — Semester {{ $selectedPeriode->semester }} Tahun Ajaran {{ $selectedPeriode->tahun_ajaran }}
                                    </small>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge jadwal-chip-accent px-3 py-2">
                                        {{ $mySchedule['total'] }} Sesi Mengajar
                                    </span>
                                    <span class="badge jadwal-chip-info px-3 py-2">
                                        {{ $mySchedule['rooms_count'] }} Ruangan
                                    </span>
                                </div>
                            </div>

                            @if ($mySchedule['total'] === 0)
                                <div class="jadwal-empty-box text-center py-4 px-3 mb-0">
                                    <i class="bi bi-calendar2-check fs-2 jadwal-subtext d-block mb-2"></i>
                                    <h6 class="fw-bold mb-1">Belum Ada Mata Kuliah yang Anda Ampu di Periode Ini</h6>
                                    <p class="jadwal-subtext small mb-0">
                                        Anda tetap dapat melihat jadwal seluruh mata kuliah per ruangan melalui tab ruangan di atas.
                                    </p>
                                </div>
                            @else
                                {{-- Tampilan Desktop (Tabel) --}}
                                <div class="d-none d-md-block">
                                    <div class="table-responsive">
                                        <table class="table table-bordered jadwal-table align-middle mb-0">
                                            <thead class="table-light">
                                                <tr class="text-center">
                                                    <th>Hari</th>
                                                    <th>Ruangan / Lab</th>
                                                    <th>Mata Kuliah</th>
                                                    <th>Waktu Mulai</th>
                                                    <th>Waktu Berakhir</th>
                                                    <th>Semester</th>
                                                    <th>Program Studi</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach (\App\Models\JadwalLab::HARI_ORDER as $hari)
                                                    @php $dayItems = $mySchedule['days'][$hari] ?? []; @endphp
                                                    @foreach ($dayItems as $idx => $item)
                                                        @php $isConflict = isset($conflictIds[$item->id]); @endphp
                                                        <tr class="{{ $isConflict ? 'table-danger' : '' }}">
                                                            @if ($idx === 0)
                                                                <td class="hari-cell" rowspan="{{ count($dayItems) }}">{{ $hari }}</td>
                                                            @endif
                                                            <td class="text-center text-nowrap">
                                                                <span class="badge jadwal-chip-accent fw-semibold">
                                                                    {{ $item->nama_ruangan }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="fw-semibold">
                                                                    @if ($isConflict)
                                                                        <i class="bi bi-exclamation-triangle-fill text-danger me-1" title="Jadwal bentrok"></i>
                                                                    @endif
                                                                    {{ $item->mata_kuliah }}
                                                                </div>
                                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                                    @if ($item->jenis)
                                                                        @php
                                                                            $jenisClass = match($item->jenis) {
                                                                                'Praktek' => 'bg-primary',
                                                                                'Teori & Praktek' => 'jadwal-badge-tp',
                                                                                default => 'bg-secondary',
                                                                            };
                                                                        @endphp
                                                                        <span class="badge {{ $jenisClass }}" style="font-size: 0.72rem;">{{ $item->jenis }}</span>
                                                                    @endif
                                                                    @if ($item->kelas)
                                                                        <span class="badge jadwal-chip-kelas" style="font-size: 0.72rem;">{{ $item->kelas }}</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td class="text-center fw-semibold text-nowrap">{{ $item->waktu_mulai }}</td>
                                                            <td class="text-center fw-semibold text-nowrap">{{ $item->waktu_berakhir }}</td>
                                                            <td class="text-center">{{ $item->semester ?: '-' }}</td>
                                                            <td class="text-center">{{ $item->program_studi ?: '-' }}</td>
                                                            <td>{{ $item->catatan ?: '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Tampilan Mobile (Kartu per Hari) --}}
                                <div class="d-md-none d-flex flex-column gap-3">
                                    @foreach (\App\Models\JadwalLab::HARI_ORDER as $hari)
                                        @php
                                            $dayItems = $mySchedule['days'][$hari] ?? [];
                                            $isToday = $todayHari === $hari;
                                        @endphp
                                        @if (!empty($dayItems))
                                            <div class="mobile-day-group {{ $isToday ? 'is-today' : '' }}">
                                                <div class="mobile-day-header d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="fw-bold">{{ $hari }}</span>
                                                        @if ($isToday)
                                                            <span class="badge bg-warning text-dark">Hari Ini</span>
                                                        @endif
                                                    </div>
                                                    <span class="badge bg-primary">{{ count($dayItems) }} sesi</span>
                                                </div>
                                                <div class="p-2 d-flex flex-column gap-2">
                                                    @foreach ($dayItems as $item)
                                                        @php $isConflict = isset($conflictIds[$item->id]); @endphp
                                                        <div class="mobile-session-card {{ $isConflict ? 'conflict' : '' }}">
                                                            <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                                                <span class="small fw-semibold jadwal-time-text text-nowrap">
                                                                    <i class="bi bi-clock me-1"></i>{{ $item->waktu_mulai }} – {{ $item->waktu_berakhir }}
                                                                </span>
                                                                <span class="badge jadwal-chip-accent fw-semibold">
                                                                    {{ $item->nama_ruangan }}
                                                                </span>
                                                            </div>
                                                            <div class="fw-semibold mb-1">
                                                                @if ($isConflict)
                                                                    <i class="bi bi-exclamation-triangle-fill text-danger me-1" title="Jadwal bentrok"></i>
                                                                @endif
                                                                {{ $item->mata_kuliah }}
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @if ($item->jenis)
                                                                    @php
                                                                        $jenisClass = match($item->jenis) {
                                                                            'Praktek' => 'bg-primary',
                                                                            'Teori & Praktek' => 'jadwal-badge-tp',
                                                                            default => 'bg-secondary',
                                                                        };
                                                                    @endphp
                                                                    <span class="badge {{ $jenisClass }}" style="font-size: 0.72rem;">{{ $item->jenis }}</span>
                                                                @endif
                                                                @if ($item->kelas)
                                                                    <span class="badge jadwal-chip-kelas" style="font-size: 0.72rem;">{{ $item->kelas }}</span>
                                                                @endif
                                                                @if ($item->semester)
                                                                    <span class="badge jadwal-chip-meta" style="font-size: 0.72rem;">Smt {{ $item->semester }}</span>
                                                                @endif
                                                                @if ($item->program_studi)
                                                                    <span class="badge jadwal-chip-meta" style="font-size: 0.72rem;">{{ $item->program_studi }}</span>
                                                                @endif
                                                            </div>
                                                            @if (trim((string) $item->catatan) !== '')
                                                                <div class="small jadwal-subtext mt-2 pt-1 border-top">
                                                                    <i class="bi bi-sticky me-1"></i>{{ $item->catatan }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @foreach ($roomsData as $roomName => $roomInfo)
                        @php
                            $tabId = 'room-' . \Illuminate\Support\Str::slug($roomName);
                            $isRoomActive = $activeTab === 'room' && $activeRoom === $roomName;
                        @endphp
                        <div class="tab-pane fade {{ $isRoomActive ? 'show active' : '' }}"
                             id="{{ $tabId }}" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0 fw-bold">Jadwal Penggunaan {{ $roomName }}</h5>
                                    <small class="text-muted">Semester {{ $selectedPeriode->semester }} Tahun Ajaran {{ $selectedPeriode->tahun_ajaran }}</small>
                                </div>
                            </div>

                            {{-- Tampilan Desktop (Tabel) --}}
                            <div class="d-none d-md-block">
                                <div class="table-responsive">
                                    <table class="table table-bordered jadwal-table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th>Hari</th>
                                                <th>Mata Kuliah</th>
                                                <th>Waktu Mulai</th>
                                                <th>Waktu Berakhir</th>
                                                <th>Dosen Pengajar</th>
                                                <th>Semester</th>
                                                <th>Program Studi</th>
                                                <th>Catatan</th>
                                                @if ($canManage)
                                                    <th style="width: 100px;">Aksi</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach (\App\Models\JadwalLab::HARI_ORDER as $hari)
                                                @php $dayItems = $roomInfo['days'][$hari] ?? []; @endphp
                                                @if (empty($dayItems))
                                                    <tr>
                                                        <td class="hari-cell">{{ $hari }}</td>
                                                        <td class="text-center text-muted">-</td>
                                                        <td class="text-center text-muted">-</td>
                                                        <td class="text-center text-muted">-</td>
                                                        <td class="text-center text-muted">-</td>
                                                        <td class="text-center text-muted">-</td>
                                                        <td class="text-center text-muted">-</td>
                                                        <td class="text-muted"></td>
                                                        @if ($canManage)
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-outline-primary btn-quick-add"
                                                                        data-room="{{ $roomName }}" data-hari="{{ $hari }}" title="Tambah jadwal hari {{ $hari }}">
                                                                    <i class="bi bi-plus"></i>
                                                                </button>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @else
                                                    @foreach ($dayItems as $idx => $item)
                                                        @php $isConflict = isset($conflictIds[$item->id]); @endphp
                                                        <tr class="{{ $isConflict ? 'table-danger' : '' }}">
                                                            @if ($idx === 0)
                                                                <td class="hari-cell" rowspan="{{ count($dayItems) }}">{{ $hari }}</td>
                                                            @endif
                                                            <td>
                                                                <div class="fw-semibold">
                                                                    @if ($isConflict)
                                                                        <i class="bi bi-exclamation-triangle-fill text-danger me-1" title="Jadwal bentrok"></i>
                                                                    @endif
                                                                    {{ $item->mata_kuliah }}
                                                                </div>
                                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                                    @if ($item->jenis)
                                                                        @php
                                                                            $jenisClass = match($item->jenis) {
                                                                                'Praktek' => 'bg-primary',
                                                                                'Teori & Praktek' => 'jadwal-badge-tp',
                                                                                default => 'bg-secondary',
                                                                            };
                                                                        @endphp
                                                                        <span class="badge {{ $jenisClass }}" style="font-size: 0.72rem;">{{ $item->jenis }}</span>
                                                                    @endif
                                                                    @if ($item->kelas)
                                                                        <span class="badge jadwal-chip-kelas" style="font-size: 0.72rem;">{{ $item->kelas }}</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td class="text-center fw-semibold text-nowrap">{{ $item->waktu_mulai }}</td>
                                                            <td class="text-center fw-semibold text-nowrap">{{ $item->waktu_berakhir }}</td>
                                                            <td>{{ $item->display_dosen }}</td>
                                                            <td class="text-center">{{ $item->semester ?: '-' }}</td>
                                                            <td class="text-center">{{ $item->program_studi ?: '-' }}</td>
                                                            <td>{{ $item->catatan ?: '-' }}</td>
                                                            @if ($canManage)
                                                                <td class="text-center text-nowrap">
                                                                    <button type="button" class="btn btn-sm btn-warning btn-edit-entry"
                                                                            data-entry="{{ json_encode($item) }}" title="Edit">
                                                                        <i class="bi bi-pencil-square"></i>
                                                                    </button>
                                                                    <form method="POST" action="{{ route('jadwal-lab.destroy', $item->id) }}"
                                                                          class="d-inline form-confirm-delete" data-confirm-text="Hapus jadwal {{ $item->mata_kuliah }}?">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tampilan Mobile (Kartu per Hari) --}}
                            <div class="d-md-none d-flex flex-column gap-3">
                                @foreach (\App\Models\JadwalLab::HARI_ORDER as $hari)
                                    @php
                                        $dayItems = $roomInfo['days'][$hari] ?? [];
                                        $isToday = $todayHari === $hari;
                                    @endphp
                                    <div class="mobile-day-group {{ $isToday ? 'is-today' : '' }}">
                                        <div class="mobile-day-header d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="fw-bold">{{ $hari }}</span>
                                                @if ($isToday)
                                                    <span class="badge bg-warning text-dark">Hari Ini</span>
                                                @endif
                                                <span class="badge {{ empty($dayItems) ? 'bg-secondary' : 'bg-primary' }}">
                                                    {{ count($dayItems) }} sesi
                                                </span>
                                            </div>
                                            @if ($canManage)
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-quick-add"
                                                        data-room="{{ $roomName }}" data-hari="{{ $hari }}" title="Tambah jadwal hari {{ $hari }}">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @if (empty($dayItems))
                                            <div class="px-3 py-2 jadwal-subtext small fst-italic">Tidak ada jadwal</div>
                                        @else
                                            <div class="p-2 d-flex flex-column gap-2">
                                                @foreach ($dayItems as $item)
                                                    @php $isConflict = isset($conflictIds[$item->id]); @endphp
                                                    <div class="mobile-session-card {{ $isConflict ? 'conflict' : '' }}">
                                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                                            <span class="small fw-semibold jadwal-time-text text-nowrap">
                                                                <i class="bi bi-clock me-1"></i>{{ $item->waktu_mulai }} – {{ $item->waktu_berakhir }}
                                                            </span>
                                                            @if ($item->kelas)
                                                                <span class="badge jadwal-chip-kelas" style="font-size: 0.72rem;">{{ $item->kelas }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="fw-semibold mb-1">
                                                            @if ($isConflict)
                                                                <i class="bi bi-exclamation-triangle-fill text-danger me-1" title="Jadwal bentrok"></i>
                                                            @endif
                                                            {{ $item->mata_kuliah }}
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                                            @if ($item->jenis)
                                                                @php
                                                                    $jenisClass = match($item->jenis) {
                                                                        'Praktek' => 'bg-primary',
                                                                        'Teori & Praktek' => 'jadwal-badge-tp',
                                                                        default => 'bg-secondary',
                                                                    };
                                                                @endphp
                                                                <span class="badge {{ $jenisClass }}" style="font-size: 0.72rem;">{{ $item->jenis }}</span>
                                                            @endif
                                                            @if ($item->semester)
                                                                <span class="badge jadwal-chip-meta" style="font-size: 0.72rem;">Smt {{ $item->semester }}</span>
                                                            @endif
                                                            @if ($item->program_studi)
                                                                <span class="badge jadwal-chip-meta" style="font-size: 0.72rem;">{{ $item->program_studi }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="small jadwal-subtext">
                                                            <i class="bi bi-person me-1"></i>{{ $item->display_dosen }}
                                                        </div>
                                                        @if (trim((string) $item->catatan) !== '')
                                                            <div class="small jadwal-subtext mt-1">
                                                                <i class="bi bi-sticky me-1"></i>{{ $item->catatan }}
                                                            </div>
                                                        @endif
                                                        @if ($canManage)
                                                            <div class="d-flex gap-2 mt-2 pt-2 border-top">
                                                                <button type="button" class="btn btn-sm btn-warning flex-fill btn-edit-entry"
                                                                        data-entry="{{ json_encode($item) }}">
                                                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                                                </button>
                                                                <form method="POST" action="{{ route('jadwal-lab.destroy', $item->id) }}"
                                                                      class="flex-fill form-confirm-delete" data-confirm-text="Hapus jadwal {{ $item->mata_kuliah }}?">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger w-100">
                                                                        <i class="bi bi-trash me-1"></i> Hapus
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Legenda Jam Ke --}}
                <div class="mt-4 pt-3 border-top">
                    <div class="row g-3 small text-muted">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="fw-bold text-body mb-1">Hari Senin s/d Kamis — Jam ke</div>
                            <div class="row row-cols-2 g-1">
                                @foreach ([1, 2, 3, 4, 5] as $jam)
                                    <div class="col text-nowrap">{{ $jam }} : {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['start'] }} - {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['end'] }}</div>
                                @endforeach
                                <div class="col-12 fw-semibold jadwal-istirahat-text">ISTIRAHAT (12.10 - 13.00)</div>
                                @foreach ([6, 7, 8, 9, 10] as $jam)
                                    <div class="col text-nowrap">{{ $jam }} : {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['start'] }} - {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['end'] }}</div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="fw-bold text-body mb-1">Hari Jum'at — Jam ke</div>
                            <div class="row row-cols-2 row-cols-md-1 g-1">
                                @foreach ([1, 2, 3, 4] as $jam)
                                    <div class="col text-nowrap">{{ $jam }} : {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['start'] }} - {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['end'] }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold">Belum Ada Jadwal Pemakaian Lab</h5>
                <p class="text-muted mb-3">
                    @if ($canManage)
                        Silakan unggah file Excel Jadwal Perkuliahan dari Akademik untuk mengonversi jadwal secara otomatis per laboratorium.
                    @else
                        Jadwal pemakaian laboratorium untuk periode berjalan belum dipublikasikan oleh Admin/PLP.
                    @endif
                </p>
                @if ($canManage)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importJadwalModal">
                        <i class="bi bi-cloud-upload me-1"></i> Import & Convert Excel Sekarang
                    </button>
                @endif
            </div>
        </div>
    @endif
</section>

@if ($canManage)
    {{-- Modal Import Excel --}}
    <div class="modal fade" id="importJadwalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('jadwal-lab.preview-import') }}" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-excel me-2 text-success"></i>Import & Convert Jadwal Perkuliahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="excelFileInput" class="form-label fw-semibold">Pilih File Excel (.xlsx)</label>
                        <input type="file" name="file" id="excelFileInput" class="form-control" accept=".xlsx" required>
                        <small class="text-muted d-block mt-1">
                            Format yang didukung: file Jadwal Perkuliahan multi-sheet per prodi (contoh: <code class="text-break">Jadwal Perkuliahan Ganjil 2026_2027.xlsx</code>).
                        </small>
                    </div>
                    <div class="alert alert-info mb-0 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Setelah diunggah, sistem akan menampilkan halaman <strong>Preview & Cek Bentrok</strong> terlebih dahulu sebelum data disimpan ke database.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-magic me-1"></i> Proses & Preview
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tambah / Edit Manual Jadwal --}}
    @if ($selectedPeriode)
        <div class="modal fade" id="entryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
                <form method="POST" id="entryForm" action="{{ route('jadwal-lab.store') }}" class="modal-content">
                    @csrf
                    <input type="hidden" name="_method" id="entryMethod" value="POST">
                    <input type="hidden" name="periode_jadwal_id" value="{{ $selectedPeriode->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="entryModalTitle">Tambah Jadwal Lab</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Ruangan / Lab <span class="text-danger">*</span></label>
                                <input type="text" name="nama_ruangan" id="entry_nama_ruangan" class="form-control"
                                       list="roomSuggestions" placeholder="Contoh: Lab L-201" required>
                                <datalist id="roomSuggestions">
                                    @foreach (array_keys($roomsData) as $rName)
                                        <option value="{{ $rName }}">
                                    @endforeach
                                    @foreach ($labs as $lab)
                                        <option value="Lab {{ $lab->kode_lab }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tautkan Master Lab (Opsional)</label>
                                <select name="lab_id" id="entry_lab_id" class="form-select">
                                    <option value="">-- Non-Lab / Belum Ditautkan --</option>
                                    @foreach ($labs as $lab)
                                        <option value="{{ $lab->id }}">{{ $lab->kode_lab }} — {{ $lab->nama_lab }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                                <select name="hari" id="entry_hari" class="form-select" required>
                                    @foreach (\App\Models\JadwalLab::HARI_ORDER as $h)
                                        <option value="{{ $h }}">{{ $h }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label fw-semibold">Jam Ke Mulai</label>
                                <select name="jam_ke_mulai" id="entry_jam_ke_mulai" class="form-select">
                                    <option value="">-- Manual --</option>
                                    @foreach (\App\Models\JadwalLab::JAM_KE_MAP as $jam => $range)
                                        <option value="{{ $jam }}" data-start="{{ $range['start'] }}">Jam {{ $jam }} ({{ $range['start'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label fw-semibold">Jam Ke Selesai</label>
                                <select name="jam_ke_selesai" id="entry_jam_ke_selesai" class="form-select">
                                    <option value="">-- Manual --</option>
                                    @foreach (\App\Models\JadwalLab::JAM_KE_MAP as $jam => $range)
                                        <option value="{{ $jam }}" data-end="{{ $range['end'] }}">Jam {{ $jam }} ({{ $range['end'] }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label fw-semibold">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="text" name="waktu_mulai" id="entry_waktu_mulai" class="form-control" placeholder="08.00" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label fw-semibold">Waktu Berakhir <span class="text-danger">*</span></label>
                                <input type="text" name="waktu_berakhir" id="entry_waktu_berakhir" class="form-control" placeholder="09.40" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis Perkuliahan</label>
                                <select name="jenis" id="entry_jenis" class="form-select">
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="Teori">Teori</option>
                                    <option value="Praktek">Praktek</option>
                                    <option value="Teori & Praktek">Teori & Praktek</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Mata Kuliah <span class="text-danger">*</span></label>
                                <input type="text" name="mata_kuliah" id="entry_mata_kuliah" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kelas</label>
                                <input type="text" name="kelas" id="entry_kelas" class="form-control" placeholder="Contoh: 3D3TI">
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label fw-semibold">Semester</label>
                                <input type="text" name="semester" id="entry_semester" class="form-control" placeholder="1 / 3 / 5">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label fw-semibold">Program Studi</label>
                                <input type="text" name="program_studi" id="entry_program_studi" class="form-control" placeholder="TI / MMB">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Akun Dosen (SIMLAB)</label>
                                <select name="dosen_id" id="entry_dosen_id" class="form-select">
                                    <option value="">-- Tidak Ditautkan / Input Teks --</option>
                                    @foreach ($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" data-name="{{ $dosen->nama_asli }}">{{ $dosen->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Dosen (Tampilan Cetak)</label>
                                <input type="text" name="nama_dosen" id="entry_nama_dosen" class="form-control" placeholder="Nama dosen pengajar">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Catatan</label>
                                <input type="text" name="catatan" id="entry_catatan" class="form-control" placeholder="Opsional">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endif
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const periodeId = @json($selectedPeriode?->id);
        const exportPdfBaseUrl = @json(route('jadwal-lab.export-pdf'));
        const baseEntryUrl = @json(url('/jadwal-lab'));
        let currentRoom = @json($activeRoom);

        // Update link PDF ruangan aktif & warna badge saat tab berpindah
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (window.innerWidth < 768 && e.target && typeof e.target.scrollIntoView === 'function') {
                e.target.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }

            if (e.relatedTarget) {
                const prevTab = $(e.relatedTarget);
                const prevBadge = prevTab.find('.badge');
                prevBadge.removeClass('bg-white text-dark')
                    .addClass(prevTab.attr('id') === 'my-schedule-tab' ? 'bg-primary' : 'bg-secondary');
            }

            const activeTabBtn = $(e.target);
            activeTabBtn.find('.badge').removeClass('bg-primary bg-secondary').addClass('bg-white text-dark');

            const roomName = activeTabBtn.data('room-name');
            if (roomName) {
                currentRoom = roomName;
                $('#printRoomLabel').text(currentRoom);
                if (periodeId) {
                    $('#btnPrintCurrentRoom').attr(
                        'href',
                        exportPdfBaseUrl + '?periode_id=' + encodeURIComponent(periodeId) + '&ruangan=' + encodeURIComponent(currentRoom)
                    );
                }
            }
        });

        // Auto-isi jam mulai/berakhir dari Jam Ke
        $('#entry_jam_ke_mulai').on('change', function() {
            const start = $(this).find(':selected').data('start');
            if (start) $('#entry_waktu_mulai').val(start);
        });
        $('#entry_jam_ke_selesai').on('change', function() {
            const end = $(this).find(':selected').data('end');
            if (end) $('#entry_waktu_berakhir').val(end);
        });
        $('#entry_dosen_id').on('change', function() {
            const name = $(this).find(':selected').data('name');
            if (name && !$('#entry_nama_dosen').val().trim()) {
                $('#entry_nama_dosen').val(name);
            }
        });

        function openCreateModal(room, hari) {
            const modalEl = document.getElementById('entryModal');
            if (!modalEl) return;
            $('#entryForm').attr('action', @json(route('jadwal-lab.store')));
            $('#entryMethod').val('POST');
            $('#entryModalTitle').text('Tambah Jadwal Lab');
            $('#entryForm')[0].reset();
            if (room) $('#entry_nama_ruangan').val(room);
            if (hari) $('#entry_hari').val(hari);
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        $('#btnAddEntry').on('click', function() {
            openCreateModal(currentRoom || '', 'Senin');
        });

        $(document).on('click', '.btn-quick-add', function() {
            openCreateModal($(this).data('room'), $(this).data('hari'));
        });

        $(document).on('click', '.btn-edit-entry', function() {
            const item = $(this).data('entry');
            const modalEl = document.getElementById('entryModal');
            if (!modalEl || !item) return;

            $('#entryForm').attr('action', baseEntryUrl + '/' + item.id);
            $('#entryMethod').val('PUT');
            $('#entryModalTitle').text('Edit Jadwal Lab');

            $('#entry_nama_ruangan').val(item.nama_ruangan || '');
            $('#entry_lab_id').val(item.lab_id || '');
            $('#entry_hari').val(item.hari || 'Senin');
            $('#entry_jam_ke_mulai').val(item.jam_ke_mulai || '');
            $('#entry_jam_ke_selesai').val(item.jam_ke_selesai || '');
            $('#entry_waktu_mulai').val(item.waktu_mulai || '');
            $('#entry_waktu_berakhir').val(item.waktu_berakhir || '');
            $('#entry_jenis').val(item.jenis || '');
            $('#entry_mata_kuliah').val(item.mata_kuliah || '');
            $('#entry_kelas').val(item.kelas || '');
            $('#entry_semester').val(item.semester || '');
            $('#entry_program_studi').val(item.program_studi || '');
            $('#entry_dosen_id').val(item.dosen_id || '');
            $('#entry_nama_dosen').val(item.nama_dosen || '');
            $('#entry_catatan').val(item.catatan || '');

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        $(document).on('submit', '.form-confirm-delete', function(e) {
            e.preventDefault();
            const form = this;
            const text = $(this).data('confirm-text') || 'Data yang dihapus tidak dapat dikembalikan.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((res) => {
                    if (res.isConfirmed) form.submit();
                });
            } else if (confirm(text)) {
                form.submit();
            }
        });
    });
</script>
@endsection
