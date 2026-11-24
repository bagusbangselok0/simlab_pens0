@extends('layouts.app')

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

    {{-- Header Aksi Utama --}}
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1 fw-bold">Buku Logbook Praktikum (FM. LAB-02)</h5>
                <p class="text-muted small mb-0">
                    Kelola catatan pelaksanaan praktikum per mata kuliah, ambil TTD perwakilan mahasiswa, dan ajukan pengesahan ke Dosen Pengampu.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#syncJadwalModal"
                        @if (!$activePeriode || $availableJadwals->isEmpty()) disabled @endif>
                    <i class="bi bi-calendar2-check me-1"></i>
                    Buat dari Jadwal Aktif
                    @if ($availableJadwals->isNotEmpty())
                        <span class="badge bg-white text-dark ms-1">{{ $availableJadwals->count() }}</span>
                    @endif
                </button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createBookModal"
                        @if ($labs->isEmpty()) disabled @endif>
                    <i class="bi bi-plus-lg me-1"></i> Buat Manual
                </button>
            </div>
        </div>
    </div>

    {{-- Kartu Praktikum Hari Ini --}}
    @if ($todayHari && $todayLogbooks->isNotEmpty())
        <div class="card border-primary mb-3">
            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-lightning-charge-fill me-1"></i> Praktikum Hari Ini ({{ $todayHari }}, {{ \Carbon\Carbon::parse($todayDate)->translatedFormat('d F Y') }})</span>
                <span class="badge bg-white text-dark">{{ $todayLogbooks->count() }} Kelas</span>
            </div>
            <div class="card-body py-3">
                <div class="row g-2">
                    @foreach ($todayLogbooks as $tBook)
                        <div class="col-md-6 col-xl-4">
                            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <span class="badge bg-light text-dark border">{{ $tBook->lab->kode_lab ?? '-' }}</span>
                                        <small class="text-muted fw-semibold">{{ $tBook->jam_default ?: '-' }}</small>
                                    </div>
                                    <h6 class="fw-bold mt-2 mb-1">{{ $tBook->nama_mk }}</h6>
                                    <div class="small text-muted mb-2">
                                        Kelas: <strong>{{ $tBook->kelas }}</strong> &bull; Dosen: {{ $tBook->display_dosen_name }}
                                    </div>
                                </div>
                                <div class="mt-2">
                                    @if ($tBook->today_entry)
                                        <a href="{{ route('logbook.show', $tBook->id) }}" class="btn btn-sm btn-outline-success w-100">
                                            <i class="bi bi-check-circle me-1"></i> Sudah Diisi (Minggu ke-{{ $tBook->today_entry->minggu_ke }})
                                        </a>
                                    @else
                                        <a href="{{ route('logbook.show', ['logbook' => $tBook->id, 'quick_fill' => 1]) }}" class="btn btn-sm btn-primary w-100">
                                            <i class="bi bi-pencil-square me-1"></i> Isi Logbook Minggu ke-{{ $tBook->next_minggu_ke }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Daftar Buku Logbook --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Daftar Buku Logbook Saya</h5>
        </div>
        <div class="card-body">
            @if ($labs->isEmpty())
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle me-1"></i> Akun Anda belum ditugaskan ke laboratorium mana pun di menu Penanggung Jawab Lab.
                </div>
            @elseif ($logbooks->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-journal-bookmark fs-1 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold">Belum Ada Buku Logbook</h5>
                    <p class="text-muted mb-3">
                        @if ($activePeriode && $availableJadwals->isNotEmpty())
                            Tersedia <strong>{{ $availableJadwals->count() }} jadwal</strong> pada periode <strong>{{ $activePeriode->nama_periode }}</strong> yang siap dijadikan buku logbook dengan 1 klik.
                        @else
                            Klik tombol <strong>Buat Manual</strong> atau sinkronkan dari <strong>Jadwal Lab</strong> aktif.
                        @endif
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mata Kuliah & Kelas</th>
                                <th>Laboratorium</th>
                                <th>Jadwal</th>
                                <th>Dosen Pengampu</th>
                                <th>Semester / TA</th>
                                <th class="text-center">Progress Pertemuan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logbooks as $book)
                                <tr>
                                    <td>
                                        <a href="{{ route('logbook.show', $book->id) }}" class="fw-bold text-decoration-none">
                                            {{ $book->nama_mk }}
                                        </a>
                                        <div class="small text-muted">Kelas: {{ $book->kelas }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $book->lab->kode_lab ?? '-' }}</span>
                                        <div class="small text-muted">{{ $book->lab->nama_lab ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $book->hari ?: '-' }}</div>
                                        <small class="text-muted">{{ $book->jam_default ?: '-' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $book->display_dosen_name }}</div>
                                        @if (!$book->dosen_id)
                                            <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Belum Tertaut Akun</span>
                                        @endif
                                    </td>
                                    <td>{{ $book->semester }}<br><small class="text-muted">{{ $book->tahun_akademik }}</small></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            <span class="badge bg-secondary" title="Total Pertemuan">{{ $book->pertemuans_count }} Pertemuan</span>
                                            @if ($book->approved_count > 0)
                                                <span class="badge bg-success" title="Disetujui">{{ $book->approved_count }} TTD</span>
                                            @endif
                                            @if ($book->pending_count > 0)
                                                <span class="badge bg-warning text-dark" title="Menunggu Dosen">{{ $book->pending_count }} Pending</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('logbook.show', $book->id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-folder2-open me-1"></i> Buka
                                        </a>
                                        @if ($book->pertemuans_count > 0)
                                            <a href="{{ route('logbook.export-pdf', $book->id) }}" target="_blank" class="btn btn-sm btn-danger" title="Cetak PDF 1 Buku">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('logbook.destroy', $book->id) }}" class="d-inline form-delete-confirm"
                                              data-confirm="Hapus buku logbook {{ $book->nama_mk }} beserta seluruh pertemuannya?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Buku">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Modal 1-Klik Sync dari Jadwal Lab Aktif --}}
<div class="modal fade" id="syncJadwalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('logbook.store-from-jadwal') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar2-check me-2 text-primary"></i>Buat Buku Logbook dari Jadwal Aktif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($activePeriode)
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <span class="badge bg-success me-1">Periode Aktif</span>
                            <strong>{{ $activePeriode->nama_periode }}</strong>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="filterPraktekOnly" checked>
                            <label class="form-check-label small fw-semibold" for="filterPraktekOnly">Hanya Praktek / Teori & Praktek</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Keterangan Jenjang PLP di PDF (Opsional)</label>
                        <input type="text" name="keterangan_plp" class="form-control form-control-sm" placeholder="Contoh: PLP Terampil">
                    </div>

                    <div class="table-responsive" style="max-height: 360px;">
                        <table class="table table-sm table-bordered align-middle mb-0" id="syncJadwalTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="checkAllJadwals">
                                    </th>
                                    <th>Mata Kuliah & Jenis</th>
                                    <th>Lab & Hari/Jam</th>
                                    <th>Kelas / Prodi</th>
                                    <th>Dosen Pengampu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($availableJadwals as $j)
                                    @php $isPraktek = in_array($j->jenis, ['Praktek', 'Teori & Praktek'], true); @endphp
                                    <tr class="jadwal-sync-row" data-is-praktek="{{ $isPraktek ? '1' : '0' }}">
                                        <td class="text-center">
                                            <input type="checkbox" name="jadwal_lab_ids[]" value="{{ $j->id }}" class="form-check-input jadwal-checkbox">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $j->mata_kuliah }}</div>
                                            @if ($j->jenis)
                                                <span class="badge {{ $isPraktek ? 'bg-primary' : 'bg-secondary' }}" style="font-size: 0.7rem;">{{ $j->jenis }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $j->lab->kode_lab ?? $j->nama_ruangan }}</div>
                                            <small class="text-muted">{{ $j->hari }}, {{ $j->waktu_mulai }}-{{ $j->waktu_berakhir }}</small>
                                        </td>
                                        <td>{{ $j->kelas ?: ($j->semester . ' ' . $j->program_studi) }}</td>
                                        <td>
                                            {{ $j->display_dosen }}
                                            @if (!$j->dosen_id)
                                                <span class="badge bg-warning text-dark d-block mt-1" style="font-size: 0.65rem;">Belum ada akun SIMLAB</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Belum ada periode Jadwal Lab yang aktif.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" @if ($availableJadwals->isEmpty()) disabled @endif>
                    <i class="bi bi-check-lg me-1"></i> Buat Logbook Terpilih
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Buat Manual --}}
<div class="modal fade" id="createBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('logbook.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Buat Buku Logbook Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Laboratorium <span class="text-danger">*</span></label>
                        <select name="lab_id" class="form-select" required>
                            @foreach ($labs as $lab)
                                <option value="{{ $lab->id }}">{{ $lab->kode_lab }} — {{ $lab->nama_lab }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama MK Praktikum <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mk" class="form-control" placeholder="Contoh: Praktek Basis Data" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="kelas" class="form-control" placeholder="Contoh: 1 SM D3 IT" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hari Rutin</label>
                        <select name="hari" class="form-select">
                            <option value="">-- Bebas / Insidental --</option>
                            @foreach (\App\Models\JadwalLab::HARI_ORDER as $h)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jam Default</label>
                        <input type="text" name="jam_default" class="form-control" placeholder="08:40 WIB">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select" required>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tahun Akademik <span class="text-danger">*</span></label>
                        <input type="text" name="tahun_akademik" class="form-control" value="{{ date('Y') . '/' . (date('Y') + 1) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Keterangan PLP (di PDF)</label>
                        <input type="text" name="keterangan_plp" class="form-control" placeholder="Contoh: PLP Terampil">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Dosen Pengampu (Akun SIMLAB)</label>
                        <select name="dosen_id" class="form-select">
                            <option value="">-- Pilih Akun Dosen --</option>
                            @foreach ($dosens as $dosen)
                                <option value="{{ $dosen->id }}">{{ $dosen->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Dosen Fallback (Jika belum punya akun)</label>
                        <input type="text" name="nama_dosen_fallback" class="form-control" placeholder="Opsional">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Buku Logbook</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        function applyPraktekFilter() {
            const onlyPraktek = $('#filterPraktekOnly').is(':checked');
            $('.jadwal-sync-row').each(function() {
                const isPraktek = $(this).data('is-praktek') === 1 || $(this).data('is-praktek') === '1';
                const show = !onlyPraktek || isPraktek;
                $(this).toggle(show);
                if (!show) {
                    $(this).find('.jadwal-checkbox').prop('checked', false);
                }
            });
        }

        $('#filterPraktekOnly').on('change', applyPraktekFilter);
        applyPraktekFilter();

        $('#checkAllJadwals').on('change', function() {
            const checked = $(this).is(':checked');
            $('.jadwal-sync-row:visible .jadwal-checkbox').prop('checked', checked);
        });

        $(document).on('submit', '.form-delete-confirm', function(e) {
            e.preventDefault();
            const form = this;
            const msg = $(this).data('confirm') || 'Hapus data ini?';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((res) => { if (res.isConfirmed) form.submit(); });
            } else if (confirm(msg)) {
                form.submit();
            }
        });
    });
</script>
@endsection
