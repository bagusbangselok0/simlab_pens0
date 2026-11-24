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
    .nav-pills .nav-link.room-tab {
        border: 1px solid rgba(0, 0, 0, 0.08);
    }
    html[data-bs-theme="dark"] .nav-pills .nav-link.room-tab {
        border-color: rgba(255, 255, 255, 0.12);
    }
</style>
@endsection

@section('content')
@php
    $canManage = Auth::check() && in_array(Auth::user()->role->slug, ['admin', 'plp'], true);
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
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-file-earmark-excel me-1"></i>Sumber: {{ $selectedPeriode->source_filename }}
                            </small>
                        @endif
                    </form>
                </div>

                <div class="col-lg-7 d-flex flex-wrap gap-2 justify-content-lg-end">
                    @if ($selectedPeriode && !empty($roomsData))
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Cetak PDF
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
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

                        <a href="{{ route('jadwal-lab.export-excel', ['periode_id' => $selectedPeriode->id]) }}"
                           class="btn btn-success">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                        </a>
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
                                  class="d-inline form-confirm-delete" data-confirm-text="Hapus periode {{ $selectedPeriode->nama_periode }} beserta seluruh jadwal di dalamnya?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Hapus Periode">
                                    <i class="bi bi-trash"></i>
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
                <ul class="nav nav-pills gap-2 flex-wrap" id="roomTabs" role="tablist">
                    @foreach ($roomsData as $roomName => $roomInfo)
                        @php $tabId = 'room-' . \Illuminate\Support\Str::slug($roomName); @endphp
                        <li class="nav-item" role="presentation">
                            <button class="nav-link room-tab {{ $activeRoom === $roomName ? 'active' : '' }}"
                                    id="{{ $tabId }}-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#{{ $tabId }}"
                                    data-room-name="{{ $roomName }}"
                                    type="button" role="tab">
                                {{ $roomName }}
                                <span class="badge {{ $activeRoom === $roomName ? 'bg-white text-dark' : 'bg-secondary' }} ms-1">
                                    {{ $roomInfo['total'] }}
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="roomTabsContent">
                    @foreach ($roomsData as $roomName => $roomInfo)
                        @php $tabId = 'room-' . \Illuminate\Support\Str::slug($roomName); @endphp
                        <div class="tab-pane fade {{ $activeRoom === $roomName ? 'show active' : '' }}"
                             id="{{ $tabId }}" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0 fw-bold">Jadwal Penggunaan {{ $roomName }}</h5>
                                    <small class="text-muted">Semester {{ $selectedPeriode->semester }} Tahun Ajaran {{ $selectedPeriode->tahun_ajaran }}</small>
                                </div>
                            </div>

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
                                                                            'Teori & Praktek' => 'bg-info text-dark',
                                                                            default => 'bg-secondary',
                                                                        };
                                                                    @endphp
                                                                    <span class="badge {{ $jenisClass }}" style="font-size: 0.72rem;">{{ $item->jenis }}</span>
                                                                @endif
                                                                @if ($item->kelas)
                                                                    <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">{{ $item->kelas }}</span>
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
                    @endforeach
                </div>

                {{-- Legenda Jam Ke --}}
                <div class="mt-4 pt-3 border-top">
                    <div class="row g-3 small text-muted">
                        <div class="col-md-4">
                            <div class="fw-bold text-body mb-1">Hari Senin s/d Kamis — Jam ke</div>
                            <div class="row row-cols-2 g-1">
                                @foreach ([1, 2, 3, 4, 5] as $jam)
                                    <div class="col">{{ $jam }} : {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['start'] }} - {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['end'] }}</div>
                                @endforeach
                                <div class="col-12 fw-semibold text-warning-emphasis">ISTIRAHAT (12.10 - 13.00)</div>
                                @foreach ([6, 7, 8, 9, 10] as $jam)
                                    <div class="col">{{ $jam }} : {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['start'] }} - {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['end'] }}</div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-bold text-body mb-1">Hari Jum'at — Jam ke</div>
                            @foreach ([1, 2, 3, 4] as $jam)
                                <div>{{ $jam }} : {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['start'] }} - {{ \App\Models\JadwalLab::JAM_KE_MAP[$jam]['end'] }}</div>
                            @endforeach
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
        <div class="modal-dialog">
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
                            Format yang didukung: file Jadwal Perkuliahan multi-sheet per prodi (contoh: <code>Jadwal Perkuliahan Ganjil 2026_2027.xlsx</code>).
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
            <div class="modal-dialog modal-lg">
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

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                                <select name="hari" id="entry_hari" class="form-select" required>
                                    @foreach (\App\Models\JadwalLab::HARI_ORDER as $h)
                                        <option value="{{ $h }}">{{ $h }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jam Ke Mulai</label>
                                <select name="jam_ke_mulai" id="entry_jam_ke_mulai" class="form-select">
                                    <option value="">-- Manual --</option>
                                    @foreach (\App\Models\JadwalLab::JAM_KE_MAP as $jam => $range)
                                        <option value="{{ $jam }}" data-start="{{ $range['start'] }}">Jam {{ $jam }} ({{ $range['start'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jam Ke Selesai</label>
                                <select name="jam_ke_selesai" id="entry_jam_ke_selesai" class="form-select">
                                    <option value="">-- Manual --</option>
                                    @foreach (\App\Models\JadwalLab::JAM_KE_MAP as $jam => $range)
                                        <option value="{{ $jam }}" data-end="{{ $range['end'] }}">Jam {{ $jam }} ({{ $range['end'] }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="text" name="waktu_mulai" id="entry_waktu_mulai" class="form-control" placeholder="08.00" required>
                            </div>
                            <div class="col-md-3">
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

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Semester</label>
                                <input type="text" name="semester" id="entry_semester" class="form-control" placeholder="1 / 3 / 5">
                            </div>
                            <div class="col-md-3">
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

        // Update link PDF ruangan aktif saat tab ruangan diklik
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            currentRoom = $(e.target).data('room-name');
            $('#printRoomLabel').text(currentRoom);
            if (periodeId) {
                $('#btnPrintCurrentRoom').attr(
                    'href',
                    exportPdfBaseUrl + '?periode_id=' + encodeURIComponent(periodeId) + '&ruangan=' + encodeURIComponent(currentRoom)
                );
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
