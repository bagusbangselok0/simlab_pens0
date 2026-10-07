@extends('layouts.app')

@section('title', 'Master Data Inventaris')

@section('content')
<style>
    .inventory-toolbar { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: flex-end; }
    .inventory-toolbar .btn { margin: 0 !important; }
    .inventory-table { min-width: 1180px; }
    @media (max-width: 575.98px) {
        .inventory-toolbar { justify-content: stretch; }
        .inventory-toolbar .btn { flex: 1 1 100%; }
        .inventory-filter .input-group { flex-wrap: wrap; }
        .inventory-filter .input-group > * { width: 100%; border-radius: .375rem !important; }
        .inventory-filter .input-group > * + * { margin-top: .5rem; }
        .inventory-card-body { padding: 1rem .75rem !important; }
        .inventory-modal-dialog { margin: .5rem; }
        .inventory-modal-dialog .modal-footer { flex-direction: column-reverse; align-items: stretch; gap: .5rem; }
        .inventory-modal-dialog .modal-footer .btn,
        .inventory-modal-dialog .modal-footer > div { width: 100%; }
        .inventory-modal-dialog .modal-footer > div { display: flex; flex-direction: column-reverse; gap: .5rem; }
    }
</style>
<div class="page-heading">
    <div class="page-title mb-3">
        <div class="row align-items-center">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Master Data Inventaris</h3>
                <p class="text-subtitle text-muted">Katalog aset & inventaris yang belum atau sudah ditempatkan ke ruangan (DIR)</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first text-md-end mb-3 mb-md-0 inventory-toolbar">
                <a href="{{ route('inventaris.template') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-download me-1"></i> Template Excel
                </a>
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Import Excel
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahMaster">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Master Barang
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show fade">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible show fade">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
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

    <!-- Ringkasan Statistik -->
    <div class="row mb-4">
        <div class="col-12 col-md-4">
            <div class="card mb-0 shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body px-3 py-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon purple me-3"><i class="bi bi-collection"></i></div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Total Master Aset</h6>
                            <h4 class="font-extrabold mb-0">{{ $stats['total_item'] }} Item</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mt-3 mt-md-0">
            <div class="card mb-0 shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body px-3 py-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon yellow me-3"><i class="bi bi-inbox"></i></div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Belum Masuk DIR</h6>
                            <h4 class="font-extrabold text-warning mb-0">{{ $stats['unassigned'] }} Item</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mt-3 mt-md-0">
            <div class="card mb-0 shadow-sm border-0 border-start border-success border-4">
                <div class="card-body px-3 py-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon green me-3"><i class="bi bi-door-open"></i></div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Sudah Masuk DIR</h6>
                            <h4 class="font-extrabold text-success mb-0">{{ $stats['assigned'] }} Item</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card mb-4">
        <div class="card-body inventory-filter">
            <form id="filterForm" class="row g-3 align-items-end" onsubmit="return false;">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Status Penempatan</label>
                    <select name="status" id="filter_status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="unassigned">Belum Masuk DIR (Unassigned)</option>
                        <option value="assigned">Sudah Masuk DIR</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Pencarian Master Barang</label>
                    <div class="input-group">
                        <input type="text" name="search" id="filter_search" class="form-control" placeholder="Cari nama barang, kode barang, NUP, merk, tipe...">
                        <button class="btn btn-outline-primary" type="button" id="btnSearch"><i class="bi bi-search"></i> Cari</button>
                        <button class="btn btn-outline-secondary" type="button" id="btnResetFilter">Reset</button>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row g-2 pt-2 border-top">
                        <div class="col-6 col-md-2"><input type="text" name="filter_kode_barang" id="filter_kode_barang" class="form-control form-control-sm" placeholder="Filter kode"></div>
                        <div class="col-6 col-md-1"><input type="text" name="filter_nup" id="filter_nup" class="form-control form-control-sm" placeholder="Filter NUP"></div>
                        <div class="col-12 col-md-3"><input type="text" name="filter_nama_barang" id="filter_nama_barang" class="form-control form-control-sm" placeholder="Filter nama barang"></div>
                        <div class="col-6 col-md-2"><input type="text" name="filter_merk" id="filter_merk" class="form-control form-control-sm" placeholder="Filter merk"></div>
                        <div class="col-6 col-md-2"><input type="text" name="filter_tipe" id="filter_tipe" class="form-control form-control-sm" placeholder="Filter tipe"></div>
                        <div class="col-6 col-md-1"><input type="date" name="filter_tgl_buku_pertama" id="filter_tgl_buku_pertama" class="form-control form-control-sm" title="Filter tanggal buku"></div>
                        <div class="col-6 col-md-1"><input type="date" name="filter_tgl_perolehan" id="filter_tgl_perolehan" class="form-control form-control-sm" title="Filter tanggal perolehan"></div>
                        <div class="col-12 col-md-2">
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

    <!-- Tabel Master Inventaris -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle inventory-table w-100" id="inventarisTable">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>KODE BARANG</th>
                            <th>NUP</th>
                            <th>NAMA BARANG</th>
                            <th>JENIS BARANG</th>
                            <th>SUMBER DANA</th>
                            <th>MERK / TIPE</th>
                            <th class="text-center">TGL BUKU</th>
                            <th class="text-center">TGL PEROLEHAN</th>
                            <th class="text-center">STATUS DIR</th>
                            <th class="text-center" style="width: 150px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables Server-Side Rendering -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Shared Modal: Foto Barang -->
    <div class="modal fade" id="modalFotoMaster" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg inventory-modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFotoTitle"><i class="bi bi-image me-2"></i>Foto Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" id="modalFotoBody">
                    <div class="spinner-border text-primary my-4" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shared Modal: Assign ke Ruangan (DIR) -->
    <div class="modal fade" id="modalAssignMaster" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog inventory-modal-dialog">
            <div class="modal-content">
                <form id="formAssignMaster" action="" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title text-white"><i class="bi bi-door-open me-2"></i> Tempatkan ke Ruangan (DIR)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="alert alert-light-secondary mb-3" id="assignItemInfo">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat info barang...
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Laboratorium / Ruangan <span class="text-danger">*</span></label>
                            <select name="lab_id" id="assign_lab_id" class="form-select" required>
                                <option value="">-- Pilih Laboratorium --</option>
                                @foreach($labs as $lab)
                                    <option value="{{ $lab->id }}">{{ $lab->nama_lab }} ({{ $lab->kode_lab }})</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="jumlah" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" id="assign_satuan" class="form-control" value="Unit" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kondisi Awal <span class="text-danger">*</span></label>
                            <select name="kondisi" id="assign_kondisi" class="form-select" required>
                                <option value="baik" selected>Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_bisa_dipinjam" id="assign_is_bisa_dipinjam" value="1">
                            <label class="form-check-label fw-bold" for="assign_is_bisa_dipinjam">Dapat Dipinjam Mahasiswa</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Penempatan (Opsional)</label>
                            <textarea name="keterangan" id="assign_keterangan" class="form-control" rows="2" placeholder="Posisi meja/rak, dsb"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Tempatkan ke Ruangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Shared Modal: Edit Master -->
    <div class="modal fade" id="modalEditMaster" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg inventory-modal-dialog">
            <div class="modal-content">
                <form id="formEditMaster" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Edit Master Inventaris</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Kode Barang (10 digit)</label>
                                <input type="text" name="kode_barang" id="edit_kode_barang" class="form-control" placeholder="Contoh: 3030101033">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NUP</label>
                                <input type="text" name="nup" id="edit_nup" class="form-control" placeholder="Contoh: 1">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" name="nama_barang" id="edit_nama_barang" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jenis Barang <span class="text-danger">*</span></label>
                                <select name="jenis_barang" id="edit_jenis_barang" class="form-select" required>
                                    <option value="barang_tidak_habis_pakai">Barang Tidak Habis Pakai</option>
                                    <option value="barang_habis_pakai">Barang Habis Pakai</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sumber Dana</label>
                                <select name="sumber_dana" id="edit_sumber_dana" class="form-select">
                                    <option value="">-- Pilih Sumber Dana --</option>
                                    <option value="apbn">APBN</option>
                                    <option value="apbd">APBD</option>
                                    <option value="prodi">Prodi</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Merk</label>
                                <input type="text" name="merk" id="edit_merk" class="form-control" placeholder="Contoh: HP, Dell, Panasonic">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipe</label>
                                <input type="text" name="tipe" id="edit_tipe" class="form-control" placeholder="Contoh: Pavilion, Core i5">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Foto Barang</label>
                                <input type="file" name="foto_barang" class="form-control" accept="image/*">
                                <small class="text-muted">Opsional, maksimal 5 MB. Upload baru akan menggantikan foto lama.</small>
                                <div id="edit_current_foto" class="mt-2 small text-muted"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Buku Pertama</label>
                                <input type="date" name="tgl_buku_pertama" id="edit_tgl_buku_pertama" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Perolehan</label>
                                <input type="date" name="tgl_perolehan" id="edit_tgl_perolehan" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Spesifikasi / Uraian Teknis</label>
                                <textarea name="spesifikasi" id="edit_spesifikasi" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Keterangan Tambahan</label>
                                <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="2"></textarea>
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

    <!-- Modal Tambah Master -->
    <div class="modal fade" id="modalTambahMaster" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg inventory-modal-dialog">
            <div class="modal-content">
                <form action="{{ route('inventaris.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white"><i class="bi bi-plus-circle me-2"></i> Tambah Master Inventaris Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Kode Barang (10 digit)</label>
                                <input type="text" name="kode_barang" class="form-control" placeholder="Contoh: 3030101033">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NUP</label>
                                <input type="text" name="nup" class="form-control" placeholder="Contoh: 1">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Personal Computer / Osiloskop" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jenis Barang <span class="text-danger">*</span></label>
                                <select name="jenis_barang" class="form-select" required>
                                    <option value="barang_tidak_habis_pakai" selected>Barang Tidak Habis Pakai</option>
                                    <option value="barang_habis_pakai">Barang Habis Pakai</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sumber Dana</label>
                                <select name="sumber_dana" class="form-select">
                                    <option value="" selected>-- Pilih Sumber Dana --</option>
                                    <option value="apbn">APBN</option>
                                    <option value="apbd">APBD</option>
                                    <option value="prodi">Prodi</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Merk</label>
                                <input type="text" name="merk" class="form-control" placeholder="Contoh: HP, Dell, Panasonic">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipe</label>
                                <input type="text" name="tipe" class="form-control" placeholder="Contoh: Pavilion, Core i5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Buku Pertama</label>
                                <input type="date" name="tgl_buku_pertama" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Perolehan</label>
                                <input type="date" name="tgl_perolehan" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Foto Barang</label>
                                <input type="file" name="foto_barang" class="form-control" accept="image/*">
                                <small class="text-muted">Opsional, maksimal 5 MB.</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Spesifikasi / Uraian Teknis</label>
                                <textarea name="spesifikasi" class="form-control" rows="2" placeholder="Spesifikasi teknis alat..."></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Keterangan Tambahan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan pengadaan/sumber dana..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Master Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div class="modal fade" id="modalImportExcel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog inventory-modal-dialog">
            <div class="modal-content">
                <form action="{{ route('inventaris.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title text-white"><i class="bi bi-file-earmark-excel me-2"></i> Import Master Inventaris (Excel/CSV)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="alert alert-light-info mb-3">
                            <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Format Header Excel:</h6>
                            <p class="mb-1 small">Pastikan file memiliki header kolom berikut:</p>
                            <code class="small d-block bg-white p-2 rounded border">Kode Barang | NUP | Nama Barang | Merk | Tipe | Tanggal Buku Pertama | Tanggal Perolehan</code>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih File Excel / CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Format file yang didukung: .xlsx, .xls, .csv (Maksimal 10MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <a href="{{ route('inventaris.template') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i> Unduh Format Contoh
                        </a>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i> Mulai Import</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Delete -->
    <form id="deleteFormMaster" action="" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

@push('scripts')
<script>
    var tableInventaris;

    $(document).ready(function() {
        tableInventaris = $('#inventarisTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            searching: false, // We use the custom filter and search bar above
            ajax: {
                url: "{{ route('inventaris.index') }}",
                data: function(d) {
                    d.status = $('#filter_status').val();
                    d.search_global = $('#filter_search').val();
                    d.filter_kode_barang = $('#filter_kode_barang').val();
                    d.filter_nup = $('#filter_nup').val();
                    d.filter_nama_barang = $('#filter_nama_barang').val();
                    d.filter_merk = $('#filter_merk').val();
                    d.filter_tipe = $('#filter_tipe').val();
                    d.filter_tgl_buku_pertama = $('#filter_tgl_buku_pertama').val();
                    d.filter_tgl_perolehan = $('#filter_tgl_perolehan').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kode_barang_display', name: 'kode_barang' },
                { data: 'nup_display', name: 'nup' },
                { data: 'nama_barang_display', name: 'nama_barang' },
                { data: 'jenis_barang_display', name: 'jenis_barang' },
                { data: 'sumber_dana_display', name: 'sumber_dana' },
                { data: 'merk_tipe_display', name: 'merk' },
                { data: 'tgl_buku_display', name: 'tgl_buku_pertama', className: 'text-center' },
                { data: 'tgl_perolehan_display', name: 'tgl_perolehan', className: 'text-center' },
                { data: 'status_dir', name: 'status_dir', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap' }
            ],
            order: [], // Default to controller's orderByDesc('created_at')
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...',
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ditemukan data yang sesuai",
                emptyTable: "Belum ada data master inventaris.",
                paginate: {
                    first: "Awal",
                    previous: "Sebelumnya",
                    next: "Selanjutnya",
                    last: "Akhir"
                }
            }
        });

        // Trigger reload when Status Penempatan changes
        $('#filter_status').on('change', function() {
            tableInventaris.ajax.reload();
        });

        // Search button click
        $('#btnSearch').on('click', function() {
            tableInventaris.ajax.reload();
        });

        // Enter key in filter form inputs
        $('#filterForm input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                tableInventaris.ajax.reload();
            }
        });

        // Apply column filters
        $('#btnApplyFilter').on('click', function() {
            tableInventaris.ajax.reload();
        });

        // Per page dropdown sync with DataTables page length
        $('#filter_per_page').on('change', function() {
            tableInventaris.page.len(parseInt($(this).val())).draw();
        });

        // Reset filter
        function resetFilters() {
            $('#filter_status').val('');
            $('#filter_search').val('');
            $('#filter_kode_barang').val('');
            $('#filter_nup').val('');
            $('#filter_nama_barang').val('');
            $('#filter_merk').val('');
            $('#filter_tipe').val('');
            $('#filter_tgl_buku_pertama').val('');
            $('#filter_tgl_perolehan').val('');
            $('#filter_per_page').val('25');
            tableInventaris.page.len(25);
            tableInventaris.ajax.reload();
        }

        $('#btnResetFilter, #btnResetAll').on('click', function() {
            resetFilters();
        });

        // --- Event Delegation for Dynamic Table Action Buttons ---

        // 1. Modal Foto Barang
        $(document).on('click', '.btnFoto', function() {
            var id = $(this).data('id');
            $('#modalFotoTitle').html('<i class="bi bi-image me-2"></i>Foto Barang');
            $('#modalFotoBody').html('<div class="spinner-border text-primary my-4" role="status"><span class="visually-hidden">Loading...</span></div>');
            $('#modalFotoMaster').modal('show');

            $.ajax({
                url: '/inventaris/' + id + '/detail',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var item = response.data;
                    $('#modalFotoTitle').html('<i class="bi bi-image me-2"></i>Foto Barang - ' + (item.nama_barang || ''));
                    if (item.foto_barang) {
                        $('#modalFotoBody').html('<img src="' + item.foto_barang + '" alt="Foto ' + item.nama_barang + '" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;">');
                    } else {
                        $('#modalFotoBody').html('<div class="text-muted py-5"><i class="bi bi-image fs-1 d-block mb-2"></i>Foto barang belum tersedia.</div>');
                    }
                },
                error: function() {
                    $('#modalFotoBody').html('<div class="alert alert-danger my-3">Gagal memuat foto barang.</div>');
                }
            });
        });

        // 2. Modal Assign ke Ruangan (DIR)
        $(document).on('click', '.btnAssign', function() {
            var id = $(this).data('id');
            $('#formAssignMaster').attr('action', '/inventaris/' + id + '/assign-ruangan');
            $('#assignItemInfo').html('<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data barang...');
            $('#assign_lab_id').val('');
            $('#assign_satuan').val('Unit');
            $('#assign_kondisi').val('baik');
            $('#assign_is_bisa_dipinjam').prop('checked', false);
            $('#assign_keterangan').val('');
            $('#modalAssignMaster').modal('show');

            $.ajax({
                url: '/inventaris/' + id + '/detail',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var item = response.data;
                    $('#assignItemInfo').html('<strong>Barang:</strong> ' + (item.nama_barang || '-') + '<br><small class="text-muted">Kode: ' + (item.kode_barang || '-') + ' | NUP: ' + (item.nup || '-') + ' | Merk/Tipe: ' + (item.merk_tipe || '-') + '</small>');
                    $('#assign_keterangan').val(item.keterangan || '');
                },
                error: function() {
                    $('#assignItemInfo').html('<span class="text-danger">Gagal memuat detail barang.</span>');
                }
            });
        });

        // 3. Modal Edit Master
        $(document).on('click', '.btnEdit', function() {
            var id = $(this).data('id');
            $('#formEditMaster').attr('action', '/inventaris/' + id);
            
            // Reset input values
            $('#edit_kode_barang').val('');
            $('#edit_nup').val('');
            $('#edit_nama_barang').val('');
            $('#edit_jenis_barang').val('barang_tidak_habis_pakai');
            $('#edit_sumber_dana').val('');
            $('#edit_merk').val('');
            $('#edit_tipe').val('');
            $('#edit_tgl_buku_pertama').val('');
            $('#edit_tgl_perolehan').val('');
            $('#edit_spesifikasi').val('');
            $('#edit_keterangan').val('');
            $('#edit_current_foto').html('');
            $('#modalEditMaster').modal('show');

            $.ajax({
                url: '/inventaris/' + id + '/detail',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var item = response.data;
                    $('#edit_kode_barang').val(item.kode_barang || '');
                    $('#edit_nup').val(item.nup || '');
                    $('#edit_nama_barang').val(item.nama_barang || '');
                    $('#edit_jenis_barang').val(item.jenis_barang || 'barang_tidak_habis_pakai');
                    $('#edit_sumber_dana').val(item.sumber_dana || '');
                    $('#edit_merk').val(item.merk || '');
                    $('#edit_tipe').val(item.tipe || '');
                    $('#edit_tgl_buku_pertama').val(item.tgl_buku_pertama || '');
                    $('#edit_tgl_perolehan').val(item.tgl_perolehan || '');
                    $('#edit_spesifikasi').val(item.spesifikasi || '');
                    $('#edit_keterangan').val(item.keterangan || '');
                    if (item.foto_barang) {
                        $('#edit_current_foto').html('Foto saat ini: <a href="' + item.foto_barang + '" target="_blank" class="text-primary fw-bold">Lihat Foto</a>');
                    }
                },
                error: function() {
                    alert('Gagal mengambil data inventaris untuk diedit.');
                }
            });
        });

        // 4. Delete Confirmation
        $(document).on('click', '.btnDelete', function() {
            var id = $(this).data('id');
            if (confirm('Apakah Anda yakin ingin menghapus data master inventaris ini?')) {
                var form = $('#deleteFormMaster');
                form.attr('action', '/inventaris/' + id);
                form.submit();
            }
        });
    });
</script>
@endpush
