@extends('layouts.app')

@section('title', 'Daftar Inventaris Ruangan (DIR)')

@section('content')
<style>
    .dir-toolbar { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: flex-end; }
    .dir-toolbar .btn { margin: 0 !important; }
    .dir-header { gap: .75rem; flex-wrap: wrap; }
    .dir-table { min-width: 950px; }
    @media (max-width: 575.98px) {
        .dir-toolbar { justify-content: stretch; }
        .dir-toolbar .btn { flex: 1 1 100%; }
        .dir-filter .input-group { flex-wrap: wrap; }
        .dir-filter .input-group > * { width: 100%; border-radius: .375rem !important; }
        .dir-filter .input-group > * + * { margin-top: .5rem; }
        .dir-card-body { padding: 1rem .75rem !important; }
        .dir-header { align-items: flex-start !important; }
        .dir-header > div { width: 100%; }
        .dir-header > div:last-child { display: flex; flex-wrap: wrap; gap: .35rem; }
        .dir-header .badge { white-space: normal; text-align: left; }
        .dir-modal-dialog { margin: .5rem; }
        .dir-modal-dialog .modal-footer { flex-direction: column-reverse; align-items: stretch; gap: .5rem; }
        .dir-modal-dialog .modal-footer .btn { width: 100%; }
    }
</style>
<div class="page-heading">
    <div class="page-title mb-3">
        <div class="row align-items-center">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Daftar Inventaris Ruangan (DIR)</h3>
                <p class="text-subtitle text-muted">Kelola inventaris dan kondisi peralatan di setiap laboratorium</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first text-md-end mb-3 mb-md-0 dir-toolbar">
                @if($selectedLab)
                    <a href="{{ route('inventaris-ruangan.export-pdf', $selectedLab->id) }}" target="_blank" class="btn btn-danger me-2">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak DIR (PDF)
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah" {{ $masterInventaris->isEmpty() ? 'disabled' : '' }}>
                        <i class="bi bi-plus-circle me-1"></i> Tambah Inventaris
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show fade">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible show fade">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Ruangan / Lab -->
    <div class="card mb-4">
        <div class="card-body dir-filter">
            <form id="filterForm" class="row g-3 align-items-end" onsubmit="return false;">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Pilih Laboratorium / Ruangan</label>
                    <select name="lab_id" id="filter_lab_id" class="form-select">
                        @forelse($labs as $lab)
                            <option value="{{ $lab->id }}" {{ ($selectedLab && $selectedLab->id == $lab->id) ? 'selected' : '' }}>
                                {{ $lab->nama_lab }} ({{ $lab->kode_lab }})
                            </option>
                        @empty
                            <option value="">-- Tidak ada laboratorium yang dapat dikelola --</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Filter Kondisi</label>
                    <select name="kondisi" id="filter_kondisi" class="form-select">
                        <option value="">Semua Kondisi</option>
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Pencarian</label>
                    <div class="input-group">
                        <input type="text" name="search" id="filter_search" class="form-control" placeholder="Cari nama, kode, spesifikasi...">
                        <button class="btn btn-outline-primary" type="button" id="btnSearch"><i class="bi bi-search"></i> Cari</button>
                        <button class="btn btn-outline-secondary" type="button" id="btnResetFilter">Reset</button>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row g-2 pt-2 border-top">
                        <div class="col-6 col-md-2"><input type="text" name="filter_kode_barang" id="filter_kode_barang" class="form-control form-control-sm" placeholder="Filter kode"></div>
                        <div class="col-6 col-md-1"><input type="text" name="filter_nup" id="filter_nup" class="form-control form-control-sm" placeholder="Filter NUP"></div>
                        <div class="col-12 col-md-3"><input type="text" name="filter_nama_barang" id="filter_nama_barang" class="form-control form-control-sm" placeholder="Filter nama barang"></div>
                        <div class="col-12 col-md-3"><input type="text" name="filter_spesifikasi_merk_tipe" id="filter_spesifikasi_merk_tipe" class="form-control form-control-sm" placeholder="Filter spesifikasi / merk tipe"></div>
                        <div class="col-6 col-md-1"><input type="text" name="filter_tahun_perolehan" id="filter_tahun_perolehan" class="form-control form-control-sm" placeholder="Tahun"></div>
                        <div class="col-6 col-md-1"><input type="text" name="filter_jumlah" id="filter_jumlah" class="form-control form-control-sm" placeholder="Jumlah"></div>
                        <div class="col-6 col-md-2">
                            <select name="filter_dapat_dipinjam" id="filter_dapat_dipinjam" class="form-select form-select-sm">
                                <option value="">Filter pinjam</option>
                                <option value="ya">Dapat dipinjam</option>
                                <option value="tidak">Tidak dipinjam</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="per_page" id="filter_per_page" class="form-select form-select-sm">
                                <option value="10">10 baris</option>
                                <option value="25" selected>25 baris</option>
                                <option value="50">50 baris</option>
                                <option value="100">100 baris</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="button" id="btnApplyFilter" class="btn btn-sm btn-primary">
                                <i class="bi bi-funnel me-1"></i> Terapkan Filter Kolom
                            </button>
                            <button type="button" id="btnResetAll" class="btn btn-sm btn-outline-secondary">Reset Semua Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedLab)
        <!-- Ringkasan Statistik -->
        <div class="row mb-4">
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card mb-0 shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body px-3 py-3">
                        <div class="row">
                            <div class="col-md-4 col-4">
                                <div class="stats-icon purple mb-2"><i class="bi bi-box-seam"></i></div>
                            </div>
                            <div class="col-md-8 col-8">
                                <h6 class="text-muted font-semibold">Total Item</h6>
                                <h4 class="font-extrabold mb-0">{{ $stats['total_item'] }} Jenis</h4>
                                <small class="text-muted">({{ $stats['total_unit'] }} Unit)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card mb-0 shadow-sm border-0 border-start border-success border-4">
                    <div class="card-body px-3 py-3">
                        <div class="row">
                            <div class="col-md-4 col-4">
                                <div class="stats-icon green mb-2"><i class="bi bi-check2-circle"></i></div>
                            </div>
                            <div class="col-md-8 col-8">
                                <h6 class="text-muted font-semibold">Kondisi Baik</h6>
                                <h4 class="font-extrabold text-success mb-0">{{ $stats['baik'] }} Unit</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6 mt-3 mt-md-0">
                <div class="card mb-0 shadow-sm border-0 border-start border-warning border-4">
                    <div class="card-body px-3 py-3">
                        <div class="row">
                            <div class="col-md-4 col-4">
                                <div class="stats-icon yellow mb-2"><i class="bi bi-exclamation-triangle"></i></div>
                            </div>
                            <div class="col-md-8 col-8">
                                <h6 class="text-muted font-semibold">Rusak Ringan</h6>
                                <h4 class="font-extrabold text-warning mb-0">{{ $stats['rusak_ringan'] }} Unit</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6 mt-3 mt-md-0">
                <div class="card mb-0 shadow-sm border-0 border-start border-danger border-4">
                    <div class="card-body px-3 py-3">
                        <div class="row">
                            <div class="col-md-4 col-4">
                                <div class="stats-icon red mb-2"><i class="bi bi-x-octagon"></i></div>
                            </div>
                            <div class="col-md-8 col-8">
                                <h6 class="text-muted font-semibold">Rusak Berat</h6>
                                <h4 class="font-extrabold text-danger mb-0">{{ $stats['rusak_berat'] }} Unit</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Inventaris Ruangan -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-light dir-header">
                <div class="fw-bold">
                    <i class="bi bi-table me-2"></i> DIR: <span class="text-primary">{{ $selectedLab->nama_lab }}</span> (Kode: {{ $selectedLab->kode_lab }})
                </div>
                <div>
                    @if($selectedLab->labManager && $selectedLab->labManager->plp)
                        <span class="badge bg-secondary">PLP: {{ $selectedLab->labManager->plp->nama_asli }}</span>
                    @endif
                    @if($selectedLab->labManager && $selectedLab->labManager->kalab)
                        <span class="badge bg-info text-dark">Ka.Lab: {{ $selectedLab->labManager->kalab->nama_asli }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body mt-3 dir-card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped dir-table w-100" id="dirTable">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;" class="text-center">NO</th>
                                <th>KODE BARANG</th>
                                <th>NAMA BARANG</th>
                                <th>SPESIFIKASI / MERK TIPE</th>
                                <th class="text-center">TAHUN PEROLEHAN</th>
                                <th class="text-center">JUMLAH</th>
                                <th class="text-center">KONDISI</th>
                                <th class="text-center">PINJAM</th>
                                <th class="text-center" style="width: 120px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables Server-Side Rendering -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shared Modal Edit Inventaris -->
        <div class="modal fade" id="modalEditDir" tabindex="-1" aria-labelledby="modalEditDirLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg dir-modal-dialog">
                <div class="modal-content">
                    <form id="formEditDir" action="" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title" id="modalEditDirLabel"><i class="bi bi-pencil-square me-2"></i> Edit Inventaris Ruangan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Kode Barang</label>
                                    <input type="text" name="kode_barang" id="edit_kode_barang" class="form-control" placeholder="Contoh: 3030101033">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">NUP</label>
                                    <input type="text" name="nup" id="edit_nup" class="form-control" placeholder="Contoh: 1, 2, 3">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_barang" id="edit_nama_barang" class="form-control" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Spesifikasi / Merk Tipe</label>
                                    <textarea name="spesifikasi_merk_tipe" id="edit_spesifikasi_merk_tipe" class="form-control" rows="2" placeholder="Merk, model, spesifikasi alat"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tahun Perolehan</label>
                                    <input type="number" name="tahun_perolehan" id="edit_tahun_perolehan" class="form-control" placeholder="Contoh: 2022" min="1900" max="2100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah" id="edit_jumlah" class="form-control" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                                    <input type="text" name="satuan" id="edit_satuan" class="form-control" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Kondisi <span class="text-danger">*</span></label>
                                    <select name="kondisi" id="edit_kondisi" class="form-select" required>
                                        <option value="baik">Baik</option>
                                        <option value="rusak_ringan">Rusak Ringan</option>
                                        <option value="rusak_berat">Rusak Berat</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_bisa_dipinjam" id="edit_is_bisa_dipinjam" value="1">
                                        <label class="form-check-label fw-bold" for="edit_is_bisa_dipinjam">Dapat Dipinjam Mahasiswa</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Keterangan / Catatan</label>
                                    <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="2" placeholder="Catatan tambahan kondisi alat"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Inventaris -->
        <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg dir-modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('inventaris-ruangan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="lab_id" value="{{ $selectedLab->id }}">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title text-white" id="modalTambahLabel"><i class="bi bi-plus-circle me-2"></i> Tambah Inventaris ke {{ $selectedLab->nama_lab }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Pilih Kode dan Nama Barang <span class="text-danger">*</span></label>
                                    <select id="masterBarangGroup" class="form-select" required>
                                        <option value="">-- Pilih kode dan nama barang --</option>
                                        @foreach($masterInventaris->groupBy(fn ($master) => implode('|', [$master->kode_barang, $master->nama_barang, $master->merk, $master->tipe])) as $groupKey => $masters)
                                            <option value="{{ md5($groupKey) }}">{{ $masters->first()->kode_barang ?: 'Tanpa kode' }} - {{ $masters->first()->nama_barang }}{{ $masters->first()->merk_tipe !== '-' ? ' (' . $masters->first()->merk_tipe . ')' : '' }}</option>
                                        @endforeach
                                        @if($masterInventaris->isEmpty())
                                            <option value="" disabled>Semua NUP sudah ditempatkan</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Pilih NUP Tersedia <span class="text-danger">*</span></label>
                                    <select name="inventaris_barang_ids[]" id="masterBarangNups" class="form-select" multiple size="6" required disabled>
                                        @foreach($masterInventaris as $master)
                                            @php($groupKey = implode('|', [$master->kode_barang, $master->nama_barang, $master->merk, $master->tipe]))
                                            <option value="{{ $master->id }}" data-group="{{ md5($groupKey) }}">NUP {{ $master->nup ?: '-' }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Tekan Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu NUP. Setiap NUP dihitung sebagai 1 unit.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Jumlah NUP Terpilih</label>
                                    <input type="number" name="jumlah" id="jumlahNupTerpilih" class="form-control" value="0" min="1" readonly required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                                    <input type="text" name="satuan" class="form-control" value="Unit" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Kondisi <span class="text-danger">*</span></label>
                                    <select name="kondisi" class="form-select" required>
                                        <option value="baik" selected>Baik</option>
                                        <option value="rusak_ringan">Rusak Ringan</option>
                                        <option value="rusak_berat">Rusak Berat</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_bisa_dipinjam" id="is_bisa_dipinjam_add" value="1">
                                        <label class="form-check-label fw-bold" for="is_bisa_dipinjam_add">Dapat Dipinjam Mahasiswa</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Keterangan / Catatan</label>
                                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan jika ada"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" {{ $masterInventaris->isEmpty() ? 'disabled' : '' }}>Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Hidden Form for Delete -->
        <form id="deleteFormDir" action="" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-door-closed fs-1 text-muted d-block mb-3"></i>
                <h5>Tidak ada laboratorium yang dipilih atau dikelola</h5>
                <p class="text-muted">Silakan hubungi Administrator untuk mengatur penanggung jawab laboratorium.</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    var tableDir;

    document.addEventListener('DOMContentLoaded', function () {
        const groupSelect = document.getElementById('masterBarangGroup');
        const nupSelect = document.getElementById('masterBarangNups');

        if (groupSelect && nupSelect) {
            groupSelect.addEventListener('change', function () {
                const selectedGroup = this.value;
                nupSelect.disabled = !selectedGroup;
                nupSelect.value = '';
                Array.from(nupSelect.options).forEach(function (option) {
                    const visible = selectedGroup && option.dataset.group === selectedGroup;
                    option.hidden = !visible;
                    if (!visible) option.selected = false;
                });
                updateNupCount();
            });

            nupSelect.addEventListener('change', updateNupCount);
        }

        function updateNupCount() {
            const countInput = document.getElementById('jumlahNupTerpilih');
            if (countInput && nupSelect) countInput.value = nupSelect.selectedOptions.length;
        }
    });

    $(document).ready(function() {
        @if($selectedLab)
        tableDir = $('#dirTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            searching: false, // Custom filter inputs used above
            ajax: {
                url: "{{ route('inventaris-ruangan.index') }}",
                data: function(d) {
                    d.lab_id = $('#filter_lab_id').val();
                    d.kondisi = $('#filter_kondisi').val();
                    d.search_global = $('#filter_search').val();
                    d.filter_kode_barang = $('#filter_kode_barang').val();
                    d.filter_nup = $('#filter_nup').val();
                    d.filter_nama_barang = $('#filter_nama_barang').val();
                    d.filter_spesifikasi_merk_tipe = $('#filter_spesifikasi_merk_tipe').val();
                    d.filter_tahun_perolehan = $('#filter_tahun_perolehan').val();
                    d.filter_jumlah = $('#filter_jumlah').val();
                    d.filter_dapat_dipinjam = $('#filter_dapat_dipinjam').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kode_barang_display', name: 'kode_barang' },
                { data: 'nama_barang_display', name: 'nama_barang' },
                { data: 'spesifikasi_display', name: 'spesifikasi_merk_tipe' },
                { data: 'tahun_perolehan_display', name: 'tahun_perolehan', className: 'text-center' },
                { data: 'jumlah_display', name: 'jumlah', className: 'text-center' },
                { data: 'kondisi_badge', name: 'kondisi', className: 'text-center' },
                { data: 'pinjam_badge', name: 'is_bisa_dipinjam', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap' }
            ],
            order: [[2, 'asc']], // Order by nama_barang
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...',
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ditemukan data yang sesuai",
                emptyTable: "Belum ada data inventaris untuk ruangan ini.",
                paginate: {
                    first: "Awal",
                    previous: "Sebelumnya",
                    next: "Selanjutnya",
                    last: "Akhir"
                }
            }
        });
        @endif

        // Lab selector navigates to lab
        $('#filter_lab_id').on('change', function() {
            var labId = $(this).val();
            if (labId) {
                window.location.href = "{{ route('inventaris-ruangan.index') }}?lab_id=" + labId;
            }
        });

        // Kondisi dropdown filter triggers reload
        $('#filter_kondisi').on('change', function() {
            if (tableDir) tableDir.ajax.reload();
        });

        // Search button click
        $('#btnSearch').on('click', function() {
            if (tableDir) tableDir.ajax.reload();
        });

        // Enter key in filter form inputs
        $('#filterForm input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                if (tableDir) tableDir.ajax.reload();
            }
        });

        // Apply column filters
        $('#btnApplyFilter').on('click', function() {
            if (tableDir) tableDir.ajax.reload();
        });

        // Per page dropdown sync
        $('#filter_per_page').on('change', function() {
            if (tableDir) tableDir.page.len(parseInt($(this).val())).draw();
        });

        // Reset filter
        function resetFilters() {
            $('#filter_kondisi').val('');
            $('#filter_search').val('');
            $('#filter_kode_barang').val('');
            $('#filter_nup').val('');
            $('#filter_nama_barang').val('');
            $('#filter_spesifikasi_merk_tipe').val('');
            $('#filter_tahun_perolehan').val('');
            $('#filter_jumlah').val('');
            $('#filter_dapat_dipinjam').val('');
            $('#filter_per_page').val('25');
            if (tableDir) {
                tableDir.page.len(25);
                tableDir.ajax.reload();
            }
        }

        $('#btnResetFilter, #btnResetAll').on('click', function() {
            resetFilters();
        });

        // --- Event Delegation for Dynamic Table Action Buttons ---

        // 1. Modal Edit Inventaris Ruangan
        $(document).on('click', '.btnEdit', function() {
            var id = $(this).data('id');
            $('#formEditDir').attr('action', '/inventaris-ruangan/' + id);

            // Clear inputs
            $('#edit_kode_barang').val('');
            $('#edit_nup').val('');
            $('#edit_nama_barang').val('');
            $('#edit_spesifikasi_merk_tipe').val('');
            $('#edit_tahun_perolehan').val('');
            $('#edit_jumlah').val('');
            $('#edit_satuan').val('');
            $('#edit_kondisi').val('baik');
            $('#edit_is_bisa_dipinjam').prop('checked', false);
            $('#edit_keterangan').val('');
            $('#modalEditDir').modal('show');

            $.ajax({
                url: '/inventaris-ruangan/' + id + '/detail',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var item = response.data;
                    $('#edit_kode_barang').val(item.kode_barang || '');
                    $('#edit_nup').val(item.nup || '');
                    $('#edit_nama_barang').val(item.nama_barang || '');
                    $('#edit_spesifikasi_merk_tipe').val(item.spesifikasi_merk_tipe || '');
                    $('#edit_tahun_perolehan').val(item.tahun_perolehan || '');
                    $('#edit_jumlah').val(item.jumlah || 1);
                    $('#edit_satuan').val(item.satuan || 'Unit');
                    $('#edit_kondisi').val(item.kondisi || 'baik');
                    $('#edit_is_bisa_dipinjam').prop('checked', !!item.is_bisa_dipinjam);
                    $('#edit_keterangan').val(item.keterangan || '');
                },
                error: function() {
                    alert('Gagal mengambil data inventaris ruangan untuk diedit.');
                }
            });
        });

        // 2. Delete Confirmation
        $(document).on('click', '.btnDelete', function() {
            var id = $(this).data('id');
            if (confirm('Apakah Anda yakin ingin menghapus data inventaris ini dari ruangan?')) {
                var form = $('#deleteFormDir');
                form.attr('action', '/inventaris-ruangan/' + id);
                form.submit();
            }
        });
    });
</script>
@endpush
@endsection
