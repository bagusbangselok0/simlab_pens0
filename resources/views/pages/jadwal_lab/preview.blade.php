@extends('layouts.app')

@section('styles')
<style>
    .preview-table th, .preview-table td {
        font-size: 0.82rem;
        vertical-align: middle;
    }
    .preview-table input.form-control-sm,
    .preview-table select.form-select-sm {
        font-size: 0.8rem;
        min-width: 90px;
    }
    @media (max-width: 767.98px) {
        .preview-room-tabs {
            flex-wrap: nowrap !important;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            padding-bottom: 0.35rem;
        }
        .preview-room-tabs .nav-item {
            flex: 0 0 auto;
        }
        .preview-room-tabs .filter-room-btn {
            white-space: nowrap;
            font-size: 0.85rem;
        }
    }
    @media (max-width: 575.98px) {
        .preview-mobile-full {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<section class="section">
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading fw-bold mb-1">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Gagal Menyimpan Jadwal
            </h6>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Ringkasan Hasil Parsing --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card mb-0 h-100">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Total Sesi Terbaca</small>
                    <h4 class="fw-bold mb-0">{{ $parsed['stats']['total_entries'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card mb-0 h-100">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Ruangan / Lab</small>
                    <h4 class="fw-bold mb-0">{{ $parsed['stats']['total_rooms'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card mb-0 h-100 {{ $parsed['stats']['total_conflicts'] > 0 ? 'border border-danger' : '' }}">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Potensi Bentrok</small>
                    <h4 class="fw-bold mb-0 {{ $parsed['stats']['total_conflicts'] > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $parsed['stats']['total_conflicts'] }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card mb-0 h-100">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Belum Ter-mapping (Lab / Dosen)</small>
                    <h4 class="fw-bold mb-0">
                        {{ $parsed['stats']['unmapped_labs'] }} / {{ $parsed['stats']['unmapped_dosens'] }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($parsed['conflicts']))
        <div class="alert alert-danger">
            <h6 class="alert-heading fw-bold mb-1">
                <i class="bi bi-exclamation-octagon-fill me-1"></i> Ditemukan Potensi Bentrok Jadwal
            </h6>
            <ul class="mb-0 small ps-3">
                @foreach ($parsed['conflicts'] as $conflict)
                    <li>{{ $conflict }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('jadwal-lab.commit-import') }}" id="commitImportForm" novalidate>
        @csrf
        <input type="hidden" name="source_filename" value="{{ $sourceFilename }}">

        {{-- Metadata Periode --}}
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">Informasi Periode Jadwal</h5>
                <a href="{{ route('jadwal-lab.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" name="nama_periode" class="form-control" value="{{ old('nama_periode', $parsed['meta']['nama_periode']) }}" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                        @php $oldSemester = old('semester', $parsed['meta']['semester']); @endphp
                        <select name="semester" class="form-select" required>
                            <option value="Ganjil" @selected($oldSemester === 'Ganjil')>Ganjil</option>
                            <option value="Genap" @selected($oldSemester === 'Genap')>Genap</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <input type="text" name="tahun_ajaran" class="form-control" value="{{ old('tahun_ajaran', $parsed['meta']['tahun_ajaran']) }}" required>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="set_active" id="setActivePeriode" value="1" checked>
                            <label class="form-check-label fw-semibold" for="setActivePeriode">Aktifkan Periode Ini</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview & Edit Tabel Konversi --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div style="min-width: 0;">
                        <h5 class="card-title mb-0 text-break">Hasil Konversi per Ruangan ({{ $sourceFilename }})</h5>
                        <small class="text-muted">Periksa atau sesuaikan mapping Lab, Dosen, maupun Catatan sebelum menyimpan.</small>
                    </div>
                    <button type="submit" class="btn btn-primary preview-mobile-full" id="btnSaveImport">
                        <i class="bi bi-check-lg me-1"></i> Simpan Jadwal (<span id="remainingCount">{{ count($parsed['entries']) }}</span> Sesi)
                    </button>
                </div>

                <ul class="nav nav-pills gap-2 mt-3 flex-wrap preview-room-tabs" id="previewRoomFilter">
                    <li class="nav-item">
                        <button type="button" class="nav-link active py-1 px-3 filter-room-btn" data-room="__ALL__">
                            Semua Ruangan ({{ count($parsed['entries']) }})
                        </button>
                    </li>
                    @foreach ($parsed['rooms'] as $roomName)
                        @php $roomCount = count(array_filter($parsed['entries'], fn($e) => $e['nama_ruangan'] === $roomName)); @endphp
                        <li class="nav-item">
                            <button type="button" class="nav-link py-1 px-3 filter-room-btn" data-room="{{ $roomName }}">
                                {{ $roomName }} ({{ $roomCount }})
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body">
                <div class="d-md-none text-muted small mb-2">
                    <i class="bi bi-arrow-left-right me-1"></i>Geser tabel ke samping untuk melihat dan mengubah seluruh kolom.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered preview-table align-middle" id="previewEntriesTable">
                        <thead class="table-light text-center">
                            <tr>
                                <th>#</th>
                                <th>Ruangan & Master Lab</th>
                                <th>Hari</th>
                                <th>Jam Mulai - Selesai</th>
                                <th>Mata Kuliah & Jenis</th>
                                <th>Smt / Prodi</th>
                                <th>Dosen Pengajar</th>
                                <th>Catatan</th>
                                <th>Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($parsed['entries'] as $i => $entry)
                                <tr data-room="{{ $entry['nama_ruangan'] }}" class="{{ !empty($entry['is_conflict']) ? 'table-danger' : '' }}">
                                    <td class="text-center row-num">{{ $i + 1 }}</td>
                                    <td style="min-width: 190px;">
                                        <input type="text" name="entries[{{ $i }}][nama_ruangan]" class="form-control form-control-sm mb-1 fw-semibold"
                                               value="{{ $entry['nama_ruangan'] }}" required>
                                        <select name="entries[{{ $i }}][lab_id]" class="form-select form-select-sm">
                                            <option value="">-- Non-Lab / Belum Match --</option>
                                            @foreach ($labs as $lab)
                                                <option value="{{ $lab->id }}" @selected((int)($entry['lab_id'] ?? 0) === $lab->id)>
                                                    {{ $lab->kode_lab }} ({{ $lab->nama_lab }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="min-width: 105px;">
                                        <select name="entries[{{ $i }}][hari]" class="form-select form-select-sm" required>
                                            @foreach (\App\Models\JadwalLab::HARI_ORDER as $h)
                                                <option value="{{ $h }}" @selected($entry['hari'] === $h)>{{ $h }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="min-width: 135px;">
                                        <input type="hidden" name="entries[{{ $i }}][jam_ke_mulai]" value="{{ $entry['jam_ke_mulai'] }}">
                                        <input type="hidden" name="entries[{{ $i }}][jam_ke_selesai]" value="{{ $entry['jam_ke_selesai'] }}">
                                        <div class="d-flex gap-1 align-items-center">
                                            <input type="text" name="entries[{{ $i }}][waktu_mulai]" class="form-control form-control-sm text-center"
                                                   value="{{ $entry['waktu_mulai'] }}" style="min-width: 58px;" required>
                                            <span>-</span>
                                            <input type="text" name="entries[{{ $i }}][waktu_berakhir]" class="form-control form-control-sm text-center"
                                                   value="{{ $entry['waktu_berakhir'] }}" style="min-width: 58px;" required>
                                        </div>
                                        <small class="text-muted d-block text-center mt-1">
                                            Jam ke {{ $entry['jam_ke_mulai'] }}@if($entry['jam_ke_selesai'] != $entry['jam_ke_mulai'])–{{ $entry['jam_ke_selesai'] }}@endif
                                        </small>
                                    </td>
                                    <td style="min-width: 220px;">
                                        <input type="text" name="entries[{{ $i }}][mata_kuliah]" class="form-control form-control-sm mb-1 fw-semibold"
                                               value="{{ $entry['mata_kuliah'] }}" required>
                                        <div class="d-flex gap-1">
                                            <select name="entries[{{ $i }}][jenis]" class="form-select form-select-sm">
                                                <option value="">-- Jenis --</option>
                                                <option value="Teori" @selected(($entry['jenis'] ?? '') === 'Teori')>Teori</option>
                                                <option value="Praktek" @selected(($entry['jenis'] ?? '') === 'Praktek')>Praktek</option>
                                                <option value="Teori & Praktek" @selected(($entry['jenis'] ?? '') === 'Teori & Praktek')>Teori & Praktek</option>
                                            </select>
                                            <input type="text" name="entries[{{ $i }}][kelas]" class="form-control form-control-sm"
                                                   value="{{ $entry['kelas'] ?? '' }}" placeholder="Kelas" style="min-width: 75px;">
                                        </div>
                                    </td>
                                    <td style="min-width: 115px;">
                                        <input type="text" name="entries[{{ $i }}][semester]" class="form-control form-control-sm mb-1 text-center"
                                               value="{{ $entry['semester'] }}" placeholder="Smt" style="min-width: 60px;">
                                        <input type="text" name="entries[{{ $i }}][program_studi]" class="form-control form-control-sm text-center"
                                               value="{{ $entry['program_studi'] }}" placeholder="Prodi" style="min-width: 60px;">
                                    </td>
                                    <td style="min-width: 200px;">
                                        <input type="text" name="entries[{{ $i }}][nama_dosen]" class="form-control form-control-sm mb-1"
                                               value="{{ $entry['nama_dosen'] }}">
                                        <select name="entries[{{ $i }}][dosen_id]" class="form-select form-select-sm">
                                            <option value="">-- Belum Match Akun --</option>
                                            @foreach ($dosens as $dosen)
                                                <option value="{{ $dosen->id }}" @selected((int)($entry['dosen_id'] ?? 0) === $dosen->id)>
                                                    {{ $dosen->nama_asli }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="min-width: 130px;">
                                        <input type="text" name="entries[{{ $i }}][catatan]" class="form-control form-control-sm"
                                               value="{{ $entry['catatan'] ?? '' }}" placeholder="Catatan...">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus Baris">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.filter-room-btn').on('click', function() {
            $('.filter-room-btn').removeClass('active');
            $(this).addClass('active');
            const room = $(this).data('room');
            if (room === '__ALL__') {
                $('#previewEntriesTable tbody tr').show();
            } else {
                $('#previewEntriesTable tbody tr').each(function() {
                    $(this).toggle($(this).data('room') === room);
                });
            }
        });

        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
            const remaining = $('#previewEntriesTable tbody tr').length;
            $('#remainingCount').text(remaining);
            if (remaining === 0) {
                $('#btnSaveImport').prop('disabled', true);
            }
        });

        $(document).on('input change', '#commitImportForm [required]', function() {
            if ($(this).val() && String($(this).val()).trim() !== '') {
                $(this).removeClass('is-invalid');
            }
        });

        // Validasi semua baris (termasuk di tab tersembunyi) & serialisasi JSON agar aman terhadap max_input_vars PHP
        $('#commitImportForm').on('submit', function(e) {
            let firstInvalid = null;
            $(this).find('[required]').each(function() {
                const val = $(this).val();
                if (!val || String(val).trim() === '') {
                    $(this).addClass('is-invalid');
                    if (!firstInvalid) {
                        firstInvalid = $(this);
                    }
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (firstInvalid) {
                e.preventDefault();
                $('.filter-room-btn[data-room="__ALL__"]').trigger('click');
                firstInvalid.trigger('focus');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Mohon lengkapi kolom wajib yang ditandai merah sebelum menyimpan.'
                    });
                } else {
                    alert('Mohon lengkapi kolom wajib yang ditandai merah sebelum menyimpan.');
                }
                return false;
            }

            const payload = [];
            $('#previewEntriesTable tbody tr').each(function() {
                const row = {};
                $(this).find('input[name], select[name]').each(function() {
                    const name = $(this).attr('name');
                    const match = name && name.match(/entries\[\d+\]\[([^\]]+)\]/);
                    if (match) {
                        row[match[1]] = $(this).val();
                    }
                    $(this).prop('disabled', true);
                });
                payload.push(row);
            });

            $('<input>', {
                type: 'hidden',
                name: 'entries_payload',
                value: JSON.stringify(payload)
            }).appendTo(this);
        });
    });
</script>
@endsection
