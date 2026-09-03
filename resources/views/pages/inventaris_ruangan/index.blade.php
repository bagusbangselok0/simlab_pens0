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
            <form action="{{ route('inventaris-ruangan.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Pilih Laboratorium / Ruangan</label>
                    <select name="lab_id" class="form-select" onchange="this.form.submit()">
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
                    <select name="kondisi" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kondisi</option>
                        <option value="baik" {{ request('kondisi') == 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="rusak_ringan" {{ request('kondisi') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ request('kondisi') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Pencarian</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, kode, spesifikasi..." value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row g-2 pt-2 border-top">
                        <div class="col-6 col-md-2"><input type="text" name="filter_kode_barang" class="form-control form-control-sm" placeholder="Filter kode" value="{{ request('filter_kode_barang') }}"></div>
                        <div class="col-6 col-md-1"><input type="text" name="filter_nup" class="form-control form-control-sm" placeholder="Filter NUP" value="{{ request('filter_nup') }}"></div>
                        <div class="col-12 col-md-3"><input type="text" name="filter_nama_barang" class="form-control form-control-sm" placeholder="Filter nama barang" value="{{ request('filter_nama_barang') }}"></div>
                        <div class="col-12 col-md-3"><input type="text" name="filter_spesifikasi_merk_tipe" class="form-control form-control-sm" placeholder="Filter spesifikasi / merk tipe" value="{{ request('filter_spesifikasi_merk_tipe') }}"></div>
                        <div class="col-6 col-md-1"><input type="text" name="filter_tahun_perolehan" class="form-control form-control-sm" placeholder="Tahun" value="{{ request('filter_tahun_perolehan') }}"></div>
                        <div class="col-6 col-md-1"><input type="text" name="filter_jumlah" class="form-control form-control-sm" placeholder="Jumlah" value="{{ request('filter_jumlah') }}"></div>
                        <div class="col-6 col-md-2"><select name="filter_dapat_dipinjam" class="form-select form-select-sm"><option value="">Filter pinjam</option><option value="ya" {{ request('filter_dapat_dipinjam') === 'ya' ? 'selected' : '' }}>Dapat dipinjam</option><option value="tidak" {{ request('filter_dapat_dipinjam') === 'tidak' ? 'selected' : '' }}>Tidak dipinjam</option></select></div>
                        <div class="col-6 col-md-2"><select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()"><option value="10" {{ request('per_page', 25) == 10 ? 'selected' : '' }}>10 baris</option><option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 baris</option><option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50 baris</option><option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100 baris</option></select></div>
                        <div class="col-12 d-flex flex-wrap gap-2"><button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i> Terapkan Filter Kolom</button>@if(request()->except('page'))<a href="{{ route('inventaris-ruangan.index', ['lab_id' => $selectedLab?->id]) }}" class="btn btn-sm btn-outline-secondary">Reset Semua Filter</a>@endif</div>
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
                    <table class="table table-hover table-striped dir-table">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">NO</th>
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
                            @forelse($inventaris as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $inventaris->firstItem() + $index }}</td>
                                    <td>
                                        @if($item->kode_barang)
                                            <span class="font-monospace fw-bold">{{ $item->kode_barang }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                        @if($item->nup)
                                            <br><small class="text-muted">NUP: {{ $item->nup }}</small>
                                        @endif
                                    </td>
                                    <td class="fw-bold">{{ $item->nama_barang }}</td>
                                    <td>{{ $item->spesifikasi_merk_tipe ?? '-' }}</td>
                                    <td class="text-center">{{ $item->tahun_perolehan ?? '-' }}</td>
                                    <td class="text-center fw-bold">{{ $item->jumlah }} {{ $item->satuan }}</td>
                                    <td class="text-center">
                                        <span class="{{ $item->kondisi_badge_class }}">
                                            {{ $item->kondisi_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($item->is_bisa_dipinjam)
                                            <span class="badge bg-light-success text-success"><i class="bi bi-check-circle"></i> Ya</span>
                                        @else
                                            <span class="badge bg-light-secondary text-secondary"><i class="bi bi-dash-circle"></i> Tidak</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id }}" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ $item->id }}')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $item->id }}" action="{{ route('inventaris-ruangan.destroy', $item->id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-labelledby="modalEditLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg dir-modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('inventaris-ruangan.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-warning text-dark">
                                                    <h5 class="modal-title" id="modalEditLabel{{ $item->id }}"><i class="bi bi-pencil-square me-2"></i> Edit Inventaris</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Kode Barang (Opsional)</label>
                                                            <input type="text" name="kode_barang" class="form-control" value="{{ old('kode_barang', $item->kode_barang) }}" placeholder="Contoh: 3050204004">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">NUP (Opsional)</label>
                                                            <input type="text" name="nup" class="form-control" value="{{ old('nup', $item->nup) }}" placeholder="Contoh: 1 s.d 24">
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                                                            <input type="text" name="nama_barang" class="form-control" value="{{ old('nama_barang', $item->nama_barang) }}" required>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label class="form-label fw-bold">Spesifikasi / Merk Tipe</label>
                                                            <input type="text" name="spesifikasi_merk_tipe" class="form-control" value="{{ old('spesifikasi_merk_tipe', $item->spesifikasi_merk_tipe) }}" placeholder="Contoh: Panasonic / HP / Malvin 800M">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Tahun Perolehan</label>
                                                            <input type="number" name="tahun_perolehan" class="form-control" value="{{ old('tahun_perolehan', $item->tahun_perolehan) }}" placeholder="Contoh: 2020" min="1900" max="2100">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                                                            <input type="number" name="jumlah" class="form-control" value="{{ old('jumlah', $item->jumlah) }}" min="1" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                                                            <input type="text" name="satuan" class="form-control" value="{{ old('satuan', $item->satuan) }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Kondisi <span class="text-danger">*</span></label>
                                                            <select name="kondisi" class="form-select" required>
                                                                <option value="baik" {{ $item->kondisi == 'baik' ? 'selected' : '' }}>Baik</option>
                                                                <option value="rusak_ringan" {{ $item->kondisi == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                                                <option value="rusak_berat" {{ $item->kondisi == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-check form-switch mt-2">
                                                                <input class="form-check-input" type="checkbox" name="is_bisa_dipinjam" id="is_bisa_dipinjam{{ $item->id }}" value="1" {{ $item->is_bisa_dipinjam ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold" for="is_bisa_dipinjam{{ $item->id }}">Dapat Dipinjam Mahasiswa</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-bold">Keterangan / Catatan</label>
                                                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan kondisi alat">{{ old('keterangan', $item->keterangan) }}</textarea>
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
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Belum ada data inventaris untuk ruangan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                    <small class="text-muted">Menampilkan {{ $inventaris->firstItem() ?? 0 }}-{{ $inventaris->lastItem() ?? 0 }} dari {{ $inventaris->total() }} data</small>
                    {{ $inventaris->links() }}
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

    function confirmDelete(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data inventaris ini?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush
@endsection
