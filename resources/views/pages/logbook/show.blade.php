@extends('layouts.app')

@section('styles')
<style>
    .signature-pad-canvas {
        border: 2px dashed #6c757d;
        border-radius: 8px;
        background-color: #ffffff;
        touch-action: none;
        width: 100%;
        height: 220px;
        cursor: crosshair;
    }
    .ttd-preview-box {
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 4px 8px;
        display: inline-block;
    }
</style>
@endsection

@section('content')
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

    {{-- Header Buku Logbook --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <a href="{{ route('logbook.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Logbook
                    </a>
                    <h4 class="fw-bold mb-1">{{ $logbook->nama_mk }}</h4>
                    <div class="text-muted small d-flex flex-wrap gap-3">
                        <span><i class="bi bi-people me-1"></i>Kelas: <strong>{{ $logbook->kelas }}</strong></span>
                        <span><i class="bi bi-building me-1"></i>Lab: <strong>{{ $logbook->lab->kode_lab ?? '-' }} ({{ $logbook->lab->nama_lab ?? '-' }})</strong></span>
                        <span><i class="bi bi-clock me-1"></i>Jadwal: <strong>{{ $logbook->hari ?: '-' }} / {{ $logbook->jam_default ?: '-' }}</strong></span>
                        <span><i class="bi bi-person-badge me-1"></i>Dosen: <strong>{{ $logbook->display_dosen_name }}</strong></span>
                    </div>
                    <div class="mt-2 d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border">{{ $logbook->semester }} {{ $logbook->tahun_akademik }}</span>
                        @if ($autoApproveActive)
                            <span class="badge bg-info text-dark"><i class="bi bi-lightning-charge-fill me-1"></i>Auto-Approve Dosen Aktif</span>
                        @else
                            <span class="badge bg-secondary">Approval Manual Dosen</span>
                        @endif
                        @if (!$logbook->dosen_id)
                            <span class="badge bg-warning text-dark">Dosen belum tertaut akun SIMLAB</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editBookModal">
                        <i class="bi bi-gear me-1"></i> Edit Info Buku
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#templateModal">
                        <i class="bi bi-list-check me-1"></i> Preset Aktivitas ({{ $templates->count() }})
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#generateModal">
                        <i class="bi bi-calendar-plus me-1"></i> Generate Mingguan
                    </button>
                    @if ($logbook->pertemuans->isNotEmpty())
                        <a href="{{ route('logbook.export-pdf', $logbook->id) }}" target="_blank" class="btn btn-danger btn-sm">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Cetak 1 Buku (PDF)
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Perwakilan Mahasiswa 1 Semester (Komting) --}}
    <div class="card mb-3">
        <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <small class="text-muted d-block fw-semibold">Perwakilan Mahasiswa Default (1 Semester — Komting)</small>
                    @if ($logbook->default_mahasiswa_nama)
                        <div class="fw-bold">
                            {{ $logbook->default_mahasiswa_nama }}
                            @if ($logbook->default_mahasiswa_nrp)
                                <span class="text-muted fw-normal">({{ $logbook->default_mahasiswa_nrp }})</span>
                            @endif
                        </div>
                        <small class="text-muted">Otomatis dipakai pada cetakan PDF jika Bagian A (Peminjaman Alat) diisi dan tidak di-override.</small>
                    @else
                        <span class="text-muted">Belum ditetapkan (opsional, bisa diatur per pertemuan atau 1 semester).</span>
                    @endif
                </div>
                @if ($logbook->default_mahasiswa_ttd_url)
                    <div class="ttd-preview-box">
                        <img src="{{ $logbook->default_mahasiswa_ttd_url }}" alt="TTD Mahasiswa" style="height: 42px;">
                    </div>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary btn-open-signpad"
                        data-target-type="logbook"
                        data-target-id="{{ $logbook->id }}"
                        data-user-id="{{ $logbook->default_mahasiswa_user_id }}"
                        data-nama="{{ $logbook->default_mahasiswa_nama }}"
                        data-nrp="{{ $logbook->default_mahasiswa_nrp }}"
                        data-title="TTD Perwakilan Mahasiswa (1 Semester)">
                    <i class="bi bi-vector-pen me-1"></i> TTD Langsung di Layar
                </button>
                <button type="button" class="btn btn-sm btn-outline-success btn-open-qr-sign"
                        data-target-type="logbook"
                        data-target-id="{{ $logbook->id }}">
                    <i class="bi bi-qr-code me-1"></i> QR / Link TTD Mahasiswa
                </button>
            </div>
        </div>
    </div>

    {{-- Daftar Pertemuan --}}
    <form method="POST" action="{{ route('logbook.bulk-submit', $logbook->id) }}" id="bulkSubmitForm">
        @csrf
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">Daftar Pertemuan ({{ $logbook->pertemuans->count() }} Halaman)</h5>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success btn-sm" id="btnBulkSubmit" disabled>
                        <i class="bi bi-send-check me-1"></i> Ajukan TTD Terpilih (<span id="selectedCount">0</span>)
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAddPertemuan">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Pertemuan
                    </button>
                </div>
            </div>

            <div class="card-body">
                @if ($logbook->pertemuans->isEmpty())
                    <div class="text-center py-4">
                        <p class="text-muted mb-3">Belum ada entri pertemuan pada buku logbook ini.</p>
                        <button type="button" class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#generateModal">
                            <i class="bi bi-calendar-plus me-1"></i> Generate 1 Semester Otomatis
                        </button>
                        <button type="button" class="btn btn-primary" onclick="$('#btnAddPertemuan').click()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Pertemuan Minggu ke-1
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 36px;">
                                        <input type="checkbox" class="form-check-input" id="checkAllPertemuan">
                                    </th>
                                    <th class="text-center" style="width: 75px;">Minggu</th>
                                    <th>Judul Praktikum & Ringkasan</th>
                                    <th>Tanggal & Jam</th>
                                    <th>TTD Mahasiswa (Bag. A)</th>
                                    <th>Status Dosen</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logbook->pertemuans as $p)
                                    @php
                                        $canSubmit = in_array($p->status, [\App\Models\LogbookPertemuan::STATUS_DRAFT, \App\Models\LogbookPertemuan::STATUS_REJECTED], true);
                                        $canEdit = $canSubmit;
                                        $hasPeralatan = $p->hasPeralatan();
                                        $effMhs = $p->effective_mahasiswa;
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            @if ($canSubmit)
                                                <input type="checkbox" name="pertemuan_ids[]" value="{{ $p->id }}" class="form-check-input pertemuan-checkbox">
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6">{{ $p->minggu_ke }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $p->judul_praktikum }}</div>
                                            <div class="small text-muted d-flex flex-wrap gap-2 mt-1">
                                                <span><i class="bi bi-tools"></i> {{ count($p->peralatan ?? []) }} alat</span>
                                                <span><i class="bi bi-list-task"></i> {{ count($p->aktivitas ?? []) }} aktivitas</span>
                                                <span><i class="bi bi-wrench"></i> {{ count($p->permasalahan ?? []) }} masalah</span>
                                            </div>
                                            @if ($p->rejection_note)
                                                <div class="alert alert-danger py-1 px-2 mt-1 mb-0 small">
                                                    <strong>Catatan Dosen:</strong> {{ $p->rejection_note }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <div>{{ $p->tanggal_pelaksanaan->translatedFormat('d M Y') }}</div>
                                            <small class="text-muted">{{ $p->jam_pelaksanaan }}</small>
                                        </td>
                                        <td>
                                            @if ($hasPeralatan)
                                                @if (!empty($effMhs['display']))
                                                    <div class="small fw-semibold">{{ $effMhs['display'] }}</div>
                                                @endif
                                                @if (!empty($effMhs['ttd_url']))
                                                    <span class="badge bg-success" style="font-size: 0.7rem;">TTD Tersedia ({{ $effMhs['source'] }})</span>
                                                @else
                                                    <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Belum TTD</span>
                                                @endif
                                            @else
                                                <small class="text-muted">Tidak pinjam alat</small>
                                            @endif
                                            @if ($canEdit)
                                                <div class="mt-1 d-flex gap-1">
                                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 btn-open-signpad" style="font-size: 0.72rem;"
                                                            data-target-type="pertemuan"
                                                            data-target-id="{{ $p->id }}"
                                                            data-user-id="{{ $p->mahasiswa_user_id }}"
                                                            data-nama="{{ $p->mahasiswa_nama }}"
                                                            data-nrp="{{ $p->mahasiswa_nrp }}"
                                                            data-title="TTD Mahasiswa Minggu ke-{{ $p->minggu_ke }}">
                                                        Pad
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-outline-success py-0 px-2 btn-open-qr-sign" style="font-size: 0.72rem;"
                                                            data-target-type="pertemuan"
                                                            data-target-id="{{ $p->id }}">
                                                        QR
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $p->status_badge_class }}">{{ $p->status_label }}</span>
                                            @if ($p->approved_at)
                                                <small class="text-muted d-block mt-1">{{ $p->approved_at->format('d/m/Y H:i') }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            @if ($canEdit)
                                                <button type="button" class="btn btn-sm btn-warning btn-edit-pertemuan"
                                                        data-pertemuan="{{ json_encode($p) }}" title="Edit Isi Pertemuan">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            @endif
                                            @if ($canSubmit)
                                                <button type="button" class="btn btn-sm btn-success btn-single-submit"
                                                        data-url="{{ route('logbook.pertemuan.submit', $p->id) }}" title="Ajukan TTD Dosen">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-info btn-single-post"
                                                    data-url="{{ route('logbook.pertemuan.duplicate', $p->id) }}" title="Duplikat ke Minggu Berikutnya">
                                                <i class="bi bi-copy"></i>
                                            </button>
                                            <a href="{{ route('logbook.export-pdf', [$logbook->id, 'pertemuan_id' => $p->id]) }}" target="_blank"
                                               class="btn btn-sm btn-outline-danger" title="Cetak 1 Halaman PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            @if ($canEdit)
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-single-delete"
                                                        data-url="{{ route('logbook.pertemuan.destroy', $p->id) }}"
                                                        data-confirm="Hapus pertemuan Minggu ke-{{ $p->minggu_ke }}?" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </form>
</section>

{{-- Helper Hidden Forms untuk aksi per baris di dalam tabel --}}
<form method="POST" id="singleActionForm" class="d-none">
    @csrf
    <input type="hidden" name="_method" id="singleActionMethod" value="POST">
</form>

{{-- Modal Tambah / Edit Pertemuan (Isi FM. LAB-02) --}}
<div class="modal fade" id="pertemuanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" id="pertemuanForm" action="{{ route('logbook.pertemuan.store', $logbook->id) }}" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="pertemuanMethod" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="pertemuanModalTitle">Tambah Pertemuan Logbook</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Minggu Ke- <span class="text-danger">*</span></label>
                        <input type="number" name="minggu_ke" id="p_minggu_ke" class="form-control" min="1" max="30" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Judul Praktikum <span class="text-danger">*</span></label>
                        <input type="text" name="judul_praktikum" id="p_judul_praktikum" class="form-control" placeholder="Contoh: Pengenalan Database" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pelaksanaan" id="p_tanggal_pelaksanaan" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Jam <span class="text-danger">*</span></label>
                        <input type="text" name="jam_pelaksanaan" id="p_jam_pelaksanaan" class="form-control" placeholder="08:40 WIB" required>
                    </div>
                </div>

                {{-- Bagian A: Peminjaman Peralatan --}}
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">A. Peminjaman Peralatan Praktikum (Opsional)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddAlatRow">
                            <i class="bi bi-plus"></i> Tambah Alat
                        </button>
                    </div>
                    <datalist id="alatSuggestions">
                        @foreach ($inventarisAlat as $alatName)
                            <option value="{{ $alatName }}">
                        @endforeach
                    </datalist>
                    <div class="table-responsive mb-2">
                        <table class="table table-sm table-bordered mb-0" id="alatRowsTable">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>Nama Alat</th>
                                    <th style="width: 170px;">Kategori</th>
                                    <th style="width: 100px;">Jumlah</th>
                                    <th style="width: 120px;">Kondisi</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="row g-2 align-items-end pt-2 border-top">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Override Peminjam Khusus Minggu Ini (Opsional)</label>
                            <select name="mahasiswa_user_id" id="p_mahasiswa_user_id" class="form-select form-select-sm">
                                <option value="">-- Ikuti Default Semester / Ketik Manual --</option>
                                @foreach ($mahasiswas as $mhs)
                                    <option value="{{ $mhs->id }}" data-nama="{{ $mhs->nama_asli }}" data-nrp="{{ $mhs->nrp }}">
                                        {{ $mhs->nrp }} — {{ $mhs->nama_asli }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Nama Peminjam (Override)</label>
                            <input type="text" name="mahasiswa_nama" id="p_mahasiswa_nama" class="form-control form-control-sm" placeholder="Kosongkan jika ikut default">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">NRP</label>
                            <input type="text" name="mahasiswa_nrp" id="p_mahasiswa_nrp" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3" id="clearOverrideWrap" style="display: none;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="clear_mahasiswa_override" id="p_clear_override" value="1">
                                <label class="form-check-label small" for="p_clear_override">Reset ke default semester</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian B: Aktivitas --}}
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h6 class="fw-bold mb-0">B. Aktivitas dalam Praktikum / Praktek / Workshop</h6>
                        <div class="d-flex flex-wrap gap-1 align-items-center">
                            @if ($templates->isNotEmpty())
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bi bi-magic me-1"></i> Isi dari Preset
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach ($templates as $tpl)
                                            <li>
                                                <button type="button" class="dropdown-item btn-apply-template"
                                                        data-items="{{ json_encode($tpl->aktivitas) }}">
                                                    {{ $tpl->nama_template }} ({{ count($tpl->aktivitas) }} poin)
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddAktivitasRow">
                                <i class="bi bi-plus"></i> Tambah Baris
                            </button>
                        </div>
                    </div>
                    <div id="aktivitasRowsContainer" class="d-flex flex-column gap-2"></div>
                </div>

                {{-- Bagian C: Permasalahan --}}
                <div class="border rounded p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">C. Permasalahan (Peralatan, Mahasiswa, System, dll)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddMasalahRow">
                            <i class="bi bi-plus"></i> Tambah Masalah
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="masalahRowsTable">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>Masalah</th>
                                    <th>Solusi</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pertemuan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Signature Pad Mode Fokus --}}
<div class="modal fade" id="signaturePadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('logbook.mahasiswa-signature') }}" id="signaturePadForm" class="modal-content">
            @csrf
            <input type="hidden" name="target_type" id="sig_target_type">
            <input type="hidden" name="target_id" id="sig_target_id">
            <input type="hidden" name="signature_data" id="sig_data_input">
            <input type="hidden" name="use_profile_signature" id="sig_use_profile" value="0">

            <div class="modal-header">
                <h5 class="modal-title" id="sigModalTitle">TTD Perwakilan Mahasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Pilih Akun Mahasiswa SIMLAB (Opsional)</label>
                    <select name="mahasiswa_user_id" id="sig_mahasiswa_user_id" class="form-select form-select-sm">
                        <option value="">-- Ketik Manual Nama & NRP --</option>
                        @foreach ($mahasiswas as $mhs)
                            <option value="{{ $mhs->id }}"
                                    data-nama="{{ $mhs->nama_asli }}"
                                    data-nrp="{{ $mhs->nrp }}"
                                    data-has-ttd="{{ $mhs->hasApprovedSignature() ? '1' : '0' }}">
                                {{ $mhs->nrp }} — {{ $mhs->nama_asli }} {{ $mhs->hasApprovedSignature() ? '(TTD Terverifikasi)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label small fw-semibold mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="mahasiswa_nama" id="sig_mahasiswa_nama" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-semibold mb-1">NRP</label>
                        <input type="text" name="mahasiswa_nrp" id="sig_mahasiswa_nrp" class="form-control form-control-sm">
                    </div>
                </div>

                <div id="useProfileSigAlert" class="alert alert-info py-2 px-3 small d-none">
                    Mahasiswa ini sudah memiliki TTD profil terverifikasi Admin.
                    <button type="button" class="btn btn-sm btn-info text-dark fw-semibold ms-2" id="btnUseProfileSig">
                        Pakai TTD Profil SIMLAB
                    </button>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold mb-0">Tanda Tangan di Kotak Berikut:</label>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0" id="btnClearCanvas">Ulangi</button>
                </div>
                <canvas id="mahasiswaSignatureCanvas" class="signature-pad-canvas"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan TTD</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal QR / Link TTD Mahasiswa --}}
<div class="modal fade" id="qrLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-qr-code me-2"></i>QR / Link TTD Mahasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="small text-muted">Mahasiswa dapat memindai QR ini (berlaku 30 menit, tanpa perlu login) untuk membubuhkan tanda tangan dari HP-nya sendiri.</p>
                <div id="qrLoading" class="py-4">Menyiapkan link...</div>
                <div id="qrError" class="alert alert-danger small d-none mb-0"></div>
                <div id="qrContent" class="d-none">
                    <img id="qrImage" src="" alt="QR TTD Mahasiswa" class="img-fluid border rounded p-2 bg-white mb-3" style="max-width: 220px;">
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" id="qrUrlInput" class="form-control" readonly>
                        <button type="button" class="btn btn-outline-primary" id="btnCopyQrUrl">Salin</button>
                    </div>
                    <a id="btnOpenQrUrl" href="#" target="_blank" class="btn btn-sm btn-success">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman TTD Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Generate Mingguan --}}
<div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('logbook.generate-pertemuans', $logbook->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Generate Pertemuan Mingguan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Awal Perkuliahan <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                    @if ($logbook->hari)
                        <small class="text-muted">Otomatis diselaraskan ke hari <strong>{{ $logbook->hari }}</strong> dan berulang setiap +7 hari.</small>
                    @endif
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Dari Minggu Ke-</label>
                        <input type="number" name="minggu_mulai" class="form-control" value="1" min="1" max="20" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Sampai Minggu Ke-</label>
                        <input type="number" name="minggu_selesai" class="form-control" value="14" min="1" max="20" required>
                    </div>
                </div>
                <div>
                    <label class="form-label fw-semibold">Awalan Judul (Opsional)</label>
                    <input type="text" name="judul_prefix" class="form-control" value="Praktikum {{ $logbook->nama_mk }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Generate Draft</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Preset Aktivitas --}}
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preset Aktivitas PLP (Bagian B)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('logbook.templates.store') }}" class="border rounded p-3 mb-3">
                    @csrf
                    <h6 class="fw-bold mb-2">Tambah Preset Baru</h6>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Nama Preset <span class="text-danger">*</span></label>
                        <input type="text" name="nama_template" class="form-control form-control-sm" placeholder="Contoh: Praktikum Pemrograman / Lab Komputer" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Daftar Aktivitas (1 baris = 1 poin) <span class="text-danger">*</span></label>
                        <textarea name="aktivitas_text" class="form-control form-control-sm" rows="4"
                                  placeholder="Menyiapkan ruangan laboratorium dan menyalakan perangkat komputer&#10;Mendampingi jalannya praktikum sesuai jadwal&#10;Mengecek kondisi perangkat dan merapikan lab setelah selesai" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Preset</button>
                </form>

                <h6 class="fw-bold">Preset Tersimpan</h6>
                @forelse ($templates as $tpl)
                    <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-semibold">{{ $tpl->nama_template }}</div>
                            <ol class="small text-muted mb-0 ps-3">
                                @foreach ($tpl->aktivitas as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ol>
                        </div>
                        <form method="POST" action="{{ route('logbook.templates.destroy', $tpl->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Belum ada preset aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Info Buku Logbook --}}
<div class="modal fade" id="editBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('logbook.update', $logbook->id) }}" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Informasi Buku Logbook</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Laboratorium</label>
                        <select name="lab_id" class="form-select" required>
                            @foreach ($labs as $lab)
                                <option value="{{ $lab->id }}" @selected($logbook->lab_id === $lab->id)>{{ $lab->kode_lab }} — {{ $lab->nama_lab }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama MK Praktikum</label>
                        <input type="text" name="nama_mk" class="form-control" value="{{ $logbook->nama_mk }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kelas</label>
                        <input type="text" name="kelas" class="form-control" value="{{ $logbook->kelas }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hari</label>
                        <select name="hari" class="form-select">
                            <option value="">-- Bebas --</option>
                            @foreach (\App\Models\JadwalLab::HARI_ORDER as $h)
                                <option value="{{ $h }}" @selected($logbook->hari === $h)>{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jam Default</label>
                        <input type="text" name="jam_default" class="form-control" value="{{ $logbook->jam_default }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Semester</label>
                        <input type="text" name="semester" class="form-control" value="{{ $logbook->semester }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tahun Akademik</label>
                        <input type="text" name="tahun_akademik" class="form-control" value="{{ $logbook->tahun_akademik }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Keterangan PLP</label>
                        <input type="text" name="keterangan_plp" class="form-control" value="{{ $logbook->keterangan_plp }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Dosen Pengampu (Akun SIMLAB)</label>
                        <select name="dosen_id" class="form-select">
                            <option value="">-- Belum Ditautkan --</option>
                            @foreach ($dosens as $dosen)
                                <option value="{{ $dosen->id }}" @selected($logbook->dosen_id === $dosen->id)>{{ $dosen->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Dosen Fallback</label>
                        <input type="text" name="nama_dosen_fallback" class="form-control" value="{{ $logbook->nama_dosen_fallback }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const storePertemuanUrl = @json(route('logbook.pertemuan.store', $logbook->id));
        const basePertemuanUrl = @json(url('/logbook/pertemuan'));
        const signLinkUrl = @json(route('logbook.sign-link'));
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        const defaultJam = @json($logbook->jam_default ?: '08.00 WIB');
        const nextMinggu = @json($nextMingguKe);
        const kategoriOptions = @json(\App\Models\LogbookPertemuan::KATEGORI_ALAT);

        let alatIndex = 0;
        let masalahIndex = 0;

        function escapeHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function addAlatRow(item = {}) {
            const idx = alatIndex++;
            const opts = kategoriOptions.map(k =>
                `<option value="${escapeHtml(k)}" ${item.kategori === k ? 'selected' : ''}>${escapeHtml(k)}</option>`
            ).join('');
            const kondisi = item.kondisi || 'Baik';
            $('#alatRowsTable tbody').append(`
                <tr>
                    <td><input type="text" name="peralatan[${idx}][nama_alat]" class="form-control form-control-sm" list="alatSuggestions" value="${escapeHtml(item.nama_alat || '')}" placeholder="Nama alat/bahan"></td>
                    <td><select name="peralatan[${idx}][kategori]" class="form-select form-select-sm">${opts}</select></td>
                    <td><input type="text" name="peralatan[${idx}][jumlah]" class="form-control form-control-sm" value="${escapeHtml(item.jumlah || '1')}"></td>
                    <td>
                        <select name="peralatan[${idx}][kondisi]" class="form-select form-select-sm">
                            <option value="Baik" ${kondisi === 'Baik' ? 'selected' : ''}>Baik</option>
                            <option value="Rusak" ${kondisi === 'Rusak' ? 'selected' : ''}>Rusak</option>
                        </select>
                    </td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-del-row">&times;</button></td>
                </tr>
            `);
        }

        function addAktivitasRow(text = '') {
            $('#aktivitasRowsContainer').append(`
                <div class="input-group input-group-sm">
                    <input type="text" name="aktivitas[]" class="form-control" value="${escapeHtml(text)}" placeholder="Uraian aktivitas praktikum...">
                    <button type="button" class="btn btn-outline-danger btn-del-aktivitas">&times;</button>
                </div>
            `);
        }

        function addMasalahRow(item = {}) {
            const idx = masalahIndex++;
            $('#masalahRowsTable tbody').append(`
                <tr>
                    <td><input type="text" name="permasalahan[${idx}][masalah]" class="form-control form-control-sm" value="${escapeHtml(item.masalah || '')}" placeholder="Kendala yang terjadi..."></td>
                    <td><input type="text" name="permasalahan[${idx}][solusi]" class="form-control form-control-sm" value="${escapeHtml(item.solusi || '')}" placeholder="Solusi penanganan..."></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-del-row">&times;</button></td>
                </tr>
            `);
        }

        $('#btnAddAlatRow').on('click', () => addAlatRow());
        $('#btnAddAktivitasRow').on('click', () => addAktivitasRow());
        $('#btnAddMasalahRow').on('click', () => addMasalahRow());

        $(document).on('click', '.btn-del-row', function() {
            $(this).closest('tr').remove();
        });
        $(document).on('click', '.btn-del-aktivitas', function() {
            $(this).closest('.input-group').remove();
        });

        $(document).on('click', '.btn-apply-template', function() {
            const items = $(this).data('items') || [];
            $('#aktivitasRowsContainer').empty();
            items.forEach(line => addAktivitasRow(line));
        });

        $('#p_mahasiswa_user_id').on('change', function() {
            const sel = $(this).find(':selected');
            if ($(this).val()) {
                $('#p_mahasiswa_nama').val(sel.data('nama') || '');
                $('#p_mahasiswa_nrp').val(sel.data('nrp') || '');
            }
        });

        function openCreatePertemuanModal() {
            $('#pertemuanForm').attr('action', storePertemuanUrl);
            $('#pertemuanMethod').val('POST');
            $('#pertemuanModalTitle').text('Tambah Pertemuan Logbook');
            $('#pertemuanForm')[0].reset();
            $('#p_minggu_ke').val(nextMinggu);
            $('#p_tanggal_pelaksanaan').val(@json(now('Asia/Jakarta')->toDateString()));
            $('#p_jam_pelaksanaan').val(defaultJam);
            $('#clearOverrideWrap').hide();

            $('#alatRowsTable tbody').empty();
            $('#aktivitasRowsContainer').empty();
            addAktivitasRow('');
            $('#masalahRowsTable tbody').empty();

            bootstrap.Modal.getOrCreateInstance(document.getElementById('pertemuanModal')).show();
        }

        $('#btnAddPertemuan').on('click', openCreatePertemuanModal);

        @if (request()->boolean('quick_fill'))
            openCreatePertemuanModal();
        @endif

        $(document).on('click', '.btn-edit-pertemuan', function() {
            const p = $(this).data('pertemuan');
            if (!p) return;

            $('#pertemuanForm').attr('action', basePertemuanUrl + '/' + p.id);
            $('#pertemuanMethod').val('PUT');
            $('#pertemuanModalTitle').text('Edit Pertemuan Minggu ke-' + p.minggu_ke);

            $('#p_minggu_ke').val(p.minggu_ke);
            $('#p_judul_praktikum').val(p.judul_praktikum || '');
            $('#p_tanggal_pelaksanaan').val((p.tanggal_pelaksanaan || '').substring(0, 10));
            $('#p_jam_pelaksanaan').val(p.jam_pelaksanaan || defaultJam);

            $('#p_mahasiswa_user_id').val(p.mahasiswa_user_id || '');
            $('#p_mahasiswa_nama').val(p.mahasiswa_nama || '');
            $('#p_mahasiswa_nrp').val(p.mahasiswa_nrp || '');
            $('#p_clear_override').prop('checked', false);
            $('#clearOverrideWrap').toggle(!!(p.mahasiswa_nama || p.mahasiswa_ttd_path || p.mahasiswa_user_id));

            $('#alatRowsTable tbody').empty();
            (p.peralatan || []).forEach(row => addAlatRow(row));

            $('#aktivitasRowsContainer').empty();
            const acts = p.aktivitas || [];
            if (acts.length > 0) {
                acts.forEach(line => addAktivitasRow(line));
            } else {
                addAktivitasRow('');
            }

            $('#masalahRowsTable tbody').empty();
            (p.permasalahan || []).forEach(row => addMasalahRow(row));

            bootstrap.Modal.getOrCreateInstance(document.getElementById('pertemuanModal')).show();
        });

        // Bulk Submit checkboxes
        function syncBulkCount() {
            const count = $('.pertemuan-checkbox:checked').length;
            $('#selectedCount').text(count);
            $('#btnBulkSubmit').prop('disabled', count === 0);
        }
        $('#checkAllPertemuan').on('change', function() {
            $('.pertemuan-checkbox').prop('checked', $(this).is(':checked'));
            syncBulkCount();
        });
        $(document).on('change', '.pertemuan-checkbox', syncBulkCount);

        // Single actions outside bulk form
        $(document).on('click', '.btn-single-submit, .btn-single-post', function() {
            $('#singleActionForm').attr('action', $(this).data('url'));
            $('#singleActionMethod').val('POST');
            $('#singleActionForm').submit();
        });

        $(document).on('click', '.btn-single-delete', function() {
            const url = $(this).data('url');
            const msg = $(this).data('confirm') || 'Hapus pertemuan ini?';
            const execDelete = () => {
                $('#singleActionForm').attr('action', url);
                $('#singleActionMethod').val('DELETE');
                $('#singleActionForm').submit();
            };
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((res) => { if (res.isConfirmed) execDelete(); });
            } else if (confirm(msg)) {
                execDelete();
            }
        });

        // Signature Pad Canvas
        const canvas = document.getElementById('mahasiswaSignatureCanvas');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let hasDrawn = false;

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();
            canvas.width = (rect.width || 440) * ratio;
            canvas.height = (rect.height || 220) * ratio;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.4;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#111827';
            hasDrawn = false;
        }

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const point = e.touches ? e.touches[0] : e;
            return { x: point.clientX - rect.left, y: point.clientY - rect.top };
        }

        canvas.addEventListener('pointerdown', (e) => {
            drawing = true;
            hasDrawn = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        });
        canvas.addEventListener('pointermove', (e) => {
            if (!drawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        });
        window.addEventListener('pointerup', () => { drawing = false; });

        $('#btnClearCanvas').on('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
            $('#sig_use_profile').val('0');
        });

        document.getElementById('signaturePadModal').addEventListener('shown.bs.modal', resizeCanvas);

        $(document).on('click', '.btn-open-signpad', function() {
            $('#sig_target_type').val($(this).data('target-type'));
            $('#sig_target_id').val($(this).data('target-id'));
            $('#sigModalTitle').text($(this).data('title') || 'TTD Perwakilan Mahasiswa');
            $('#sig_mahasiswa_user_id').val($(this).data('user-id') || '').trigger('change');
            $('#sig_mahasiswa_nama').val($(this).data('nama') || '');
            $('#sig_mahasiswa_nrp').val($(this).data('nrp') || '');
            $('#sig_data_input').val('');
            $('#sig_use_profile').val('0');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('signaturePadModal')).show();
        });

        $('#sig_mahasiswa_user_id').on('change', function() {
            const opt = $(this).find(':selected');
            if ($(this).val()) {
                $('#sig_mahasiswa_nama').val(opt.data('nama') || '');
                $('#sig_mahasiswa_nrp').val(opt.data('nrp') || '');
                $('#useProfileSigAlert').toggleClass('d-none', opt.data('has-ttd') !== 1 && opt.data('has-ttd') !== '1');
            } else {
                $('#useProfileSigAlert').addClass('d-none');
            }
        });

        $('#btnUseProfileSig').on('click', function() {
            $('#sig_use_profile').val('1');
            $('#sig_data_input').val('');
            $('#signaturePadForm').submit();
        });

        $('#signaturePadForm').on('submit', function(e) {
            if ($('#sig_use_profile').val() !== '1') {
                if (!hasDrawn) {
                    e.preventDefault();
                    alert('Silakan goreskan tanda tangan di kotak kanvas terlebih dahulu.');
                    return false;
                }
                $('#sig_data_input').val(canvas.toDataURL('image/png'));
            }
        });

        // QR / Link Signed URL
        $(document).on('click', '.btn-open-qr-sign', function() {
            const type = $(this).data('target-type');
            const id = $(this).data('target-id');
            $('#qrLoading').removeClass('d-none');
            $('#qrError').addClass('d-none').text('');
            $('#qrContent').addClass('d-none');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('qrLinkModal')).show();

            $.post(signLinkUrl, {
                _token: csrfToken,
                target_type: type,
                target_id: id
            }).done(function(res) {
                if (res?.success && res?.url) {
                    $('#qrUrlInput').val(res.url);
                    $('#btnOpenQrUrl').attr('href', res.url);
                    $('#qrImage').attr('src', 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' + encodeURIComponent(res.url));
                    $('#qrLoading').addClass('d-none');
                    $('#qrContent').removeClass('d-none');
                } else {
                    $('#qrLoading').addClass('d-none');
                    $('#qrError').text(res?.message || 'Gagal membuat tautan QR. Silakan coba lagi.').removeClass('d-none');
                }
            }).fail(function(xhr) {
                $('#qrLoading').addClass('d-none');
                const msg = xhr.responseJSON?.message || 'Gagal membuat tautan QR. Silakan muat ulang halaman dan coba lagi.';
                $('#qrError').text(msg).removeClass('d-none');
            });
        });

        $('#btnCopyQrUrl').on('click', function() {
            const val = $('#qrUrlInput').val();
            navigator.clipboard?.writeText(val);
            $(this).text('Tersalin!');
            setTimeout(() => $(this).text('Salin'), 1500);
        });
    });
</script>
@endsection
