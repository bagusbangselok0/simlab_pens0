@extends('layouts.app')

@section('title', 'Master Data Inventaris')

@section('content')
<style>
    .inventory-toolbar { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: flex-end; }
    .inventory-toolbar .btn { margin: 0 !important; }
    .inventory-table { min-width: 980px; }
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
            <form action="{{ route('inventaris.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Status Penempatan</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="unassigned" {{ request('status') == 'unassigned' ? 'selected' : '' }}>Belum Masuk DIR (Unassigned)</option>
                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Sudah Masuk DIR</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Pencarian Master Barang</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama barang, kode barang, NUP, merk, tipe..." value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                        @if(request()->except('page'))
                            <a href="{{ route('inventaris.index') }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <div class="row g-2 pt-2 border-top">
                        <div class="col-6 col-md-2"><input type="text" name="filter_kode_barang" class="form-control form-control-sm" placeholder="Filter kode" value="{{ request('filter_kode_barang') }}"></div>
                        <div class="col-6 col-md-1"><input type="text" name="filter_nup" class="form-control form-control-sm" placeholder="Filter NUP" value="{{ request('filter_nup') }}"></div>
                        <div class="col-12 col-md-3"><input type="text" name="filter_nama_barang" class="form-control form-control-sm" placeholder="Filter nama barang" value="{{ request('filter_nama_barang') }}"></div>
                        <div class="col-6 col-md-2"><input type="text" name="filter_merk" class="form-control form-control-sm" placeholder="Filter merk" value="{{ request('filter_merk') }}"></div>
                        <div class="col-6 col-md-2"><input type="text" name="filter_tipe" class="form-control form-control-sm" placeholder="Filter tipe" value="{{ request('filter_tipe') }}"></div>
                        <div class="col-6 col-md-1"><input type="date" name="filter_tgl_buku_pertama" class="form-control form-control-sm" title="Filter tanggal buku" value="{{ request('filter_tgl_buku_pertama') }}"></div>
                        <div class="col-6 col-md-1"><input type="date" name="filter_tgl_perolehan" class="form-control form-control-sm" title="Filter tanggal perolehan" value="{{ request('filter_tgl_perolehan') }}"></div>
                        <div class="col-12 col-md-2"><select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()"><option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10 baris</option><option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 baris</option><option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50 baris</option><option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100 baris</option></select></div>
                        <div class="col-12 d-flex flex-wrap gap-2"><button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i> Terapkan Filter Kolom</button>@if(request()->except('page'))<a href="{{ route('inventaris.index') }}" class="btn btn-sm btn-outline-secondary">Reset Semua Filter</a>@endif</div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Master Inventaris -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle inventory-table">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>KODE BARANG</th>
                            <th>NUP</th>
                            <th>NAMA BARANG</th>
                            <th>MERK / TIPE</th>
                            <th class="text-center">TGL BUKU</th>
                            <th class="text-center">TGL PEROLEHAN</th>
                            <th class="text-center">STATUS DIR</th>
                            <th class="text-center" style="width: 150px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td class="text-center">{{ $items->firstItem() + $index }}</td>
                                <td>
                                    @if($item->kode_barang)
                                        <span class="font-monospace fw-bold">{{ $item->kode_barang }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item->nup ?? '-' }}</td>
                                <td class="fw-bold">{{ $item->nama_barang }}</td>
                                <td>{{ $item->merk_tipe }}</td>
                                <td class="text-center">{{ $item->tgl_buku_pertama ? $item->tgl_buku_pertama->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">{{ $item->tgl_perolehan ? $item->tgl_perolehan->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">
                                    @if($item->assigned_dir)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i> {{ $item->assigned_dir->lab->nama_lab ?? 'DIR' }}
                                        </span>
                                        <br><small class="text-muted">({{ $item->assigned_dir->kondisi_label }})</small>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock-history me-1"></i> Belum Masuk DIR
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!$item->assigned_dir)
                                        <button type="button" class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#modalAssign{{ $item->id }}" title="Tempatkan ke Ruangan (DIR)">
                                            <i class="bi bi-door-open-fill"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#modalEditMaster{{ $item->id }}" title="Edit Master">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteMaster('{{ $item->id }}')" title="Hapus Master">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <form id="delete-master-{{ $item->id }}" action="{{ route('inventaris.destroy', $item->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Assign ke Ruangan -->
                            @if(!$item->inventarisRuangan)
                                <div class="modal fade" id="modalAssign{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog inventory-modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('inventaris.assign', $item->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title text-white"><i class="bi bi-door-open me-2"></i> Tempatkan ke Ruangan (DIR)</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="alert alert-light-secondary mb-3">
                                                        <strong>Barang:</strong> {{ $item->nama_barang }}<br>
                                                        <small class="text-muted">Kode: {{ $item->kode_barang ?? '-' }} | Merk/Tipe: {{ $item->merk_tipe }}</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Pilih Laboratorium / Ruangan <span class="text-danger">*</span></label>
                                                        <select name="lab_id" class="form-select" required>
                                                            <option value="">-- Pilih Laboratorium --</option>
                                                            @foreach($labs as $lab)
                                                                <option value="{{ $lab->id }}">{{ $lab->nama_lab }} ({{ $lab->kode_lab }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <input type="hidden" name="jumlah" value="1">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                                                        <input type="text" name="satuan" class="form-control" value="Unit" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Kondisi Awal <span class="text-danger">*</span></label>
                                                        <select name="kondisi" class="form-select" required>
                                                            <option value="baik" selected>Baik</option>
                                                            <option value="rusak_ringan">Rusak Ringan</option>
                                                            <option value="rusak_berat">Rusak Berat</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" name="is_bisa_dipinjam" id="is_pinjam_assign_{{ $item->id }}" value="1">
                                                        <label class="form-check-label fw-bold" for="is_pinjam_assign_{{ $item->id }}">Dapat Dipinjam Mahasiswa</label>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Catatan Penempatan (Opsional)</label>
                                                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Posisi meja/rak, dsb">{{ $item->keterangan }}</textarea>
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
                            @endif

                            <!-- Modal Edit Master -->
                            <div class="modal fade" id="modalEditMaster{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg inventory-modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('inventaris.update', $item->id) }}" method="POST">
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
                                                        <input type="text" name="kode_barang" class="form-control" value="{{ old('kode_barang', $item->kode_barang) }}" placeholder="Contoh: 3030101033">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">NUP</label>
                                                        <input type="text" name="nup" class="form-control" value="{{ old('nup', $item->nup) }}" placeholder="Contoh: 1">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                                                        <input type="text" name="nama_barang" class="form-control" value="{{ old('nama_barang', $item->nama_barang) }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Merk</label>
                                                        <input type="text" name="merk" class="form-control" value="{{ old('merk', $item->merk) }}" placeholder="Contoh: HP, Dell, Panasonic">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Tipe</label>
                                                        <input type="text" name="tipe" class="form-control" value="{{ old('tipe', $item->tipe) }}" placeholder="Contoh: Pavilion, Core i5">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Tanggal Buku Pertama</label>
                                                        <input type="date" name="tgl_buku_pertama" class="form-control" value="{{ old('tgl_buku_pertama', $item->tgl_buku_pertama ? $item->tgl_buku_pertama->format('Y-m-d') : '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Tanggal Perolehan</label>
                                                        <input type="date" name="tgl_perolehan" class="form-control" value="{{ old('tgl_perolehan', $item->tgl_perolehan ? $item->tgl_perolehan->format('Y-m-d') : '') }}">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label fw-bold">Spesifikasi / Uraian Teknis</label>
                                                        <textarea name="spesifikasi" class="form-control" rows="2">{{ old('spesifikasi', $item->spesifikasi) }}</textarea>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label fw-bold">Keterangan Tambahan</label>
                                                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $item->keterangan) }}</textarea>
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
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Belum ada data master inventaris.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <small class="text-muted">Menampilkan {{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data</small>
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah Master -->
    <div class="modal fade" id="modalTambahMaster" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg inventory-modal-dialog">
            <div class="modal-content">
                <form action="{{ route('inventaris.store') }}" method="POST">
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

@push('scripts')
<script>
    function confirmDeleteMaster(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data master inventaris ini?')) {
            document.getElementById('delete-master-' + id).submit();
        }
    }
</script>
@endpush
@endsection
