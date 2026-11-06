@extends('layouts.app')

@section('content')
<style>
    /* =========================================================
       Responsive Mobile Styling & Dark/Light Theme Integration
       ========================================================= */
    @media (max-width: 767.98px) {
        #addPeminjamanAlatModal .modal-dialog {
            margin: 0.5rem auto;
            max-width: calc(100% - 1rem);
        }

        #addPeminjamanAlatModal .modal-body {
            padding: 1rem 0.75rem;
        }

        /* Nonaktifkan overflow table-responsive pada mobile agar layout card mengalir vertikal */
        #addPeminjamanAlatModal .table-responsive {
            overflow: visible !important;
            margin-bottom: 0.5rem !important;
        }

        #tableItems,
        #tableItems tbody {
            display: block;
            width: 100% !important;
        }

        #tableItems thead {
            display: none !important;
        }

        /* Base Card Styling (Mobile) */
        #tableItems tr.alat-item-row {
            display: flex;
            flex-wrap: wrap;
            border-radius: 0.75rem;
            padding: 0.85rem;
            margin-bottom: 0.85rem;
            position: relative;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        #tableItems tr.alat-item-row > td {
            display: block;
            padding: 0.25rem 0;
            border: none;
        }

        .alat-col-header {
            width: 100% !important;
            margin-bottom: 0.5rem;
        }

        .alat-col-sumber {
            width: 100% !important;
            margin-bottom: 0.5rem;
        }

        .alat-col-nama {
            width: 100% !important;
            margin-bottom: 0.5rem;
        }

        .alat-col-jumlah {
            width: calc(50% - 0.35rem) !important;
            margin-right: 0.7rem;
            margin-bottom: 0.5rem;
        }

        .alat-col-satuan {
            width: calc(50% - 0.35rem) !important;
            margin-bottom: 0.5rem;
        }

        .alat-col-keterangan {
            width: 100% !important;
            margin-bottom: 0.25rem;
        }

        /* Ukuran input dan select nyaman disentuh di layar sentuh HP */
        #addPeminjamanAlatModal .form-control,
        #addPeminjamanAlatModal .form-select {
            font-size: 14.5px;
            min-height: 40px;
        }
    }

    /* ---------------------------------------------------------
       LIGHT MODE (Default & Eksplisit html[data-bs-theme="light"])
       --------------------------------------------------------- */
    #tableItems tr.alat-item-row,
    html:not([data-bs-theme="dark"]) #tableItems tr.alat-item-row,
    html[data-bs-theme="light"] #tableItems tr.alat-item-row {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
    }

    .alat-card-header,
    html:not([data-bs-theme="dark"]) .alat-card-header,
    html[data-bs-theme="light"] .alat-card-header {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }

    .alat-card-label,
    html:not([data-bs-theme="dark"]) .alat-card-label,
    html[data-bs-theme="light"] .alat-card-label {
        color: #475569 !important;
    }

    .alat-input-readonly,
    html:not([data-bs-theme="dark"]) .alat-input-readonly,
    html[data-bs-theme="light"] .alat-input-readonly {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        border: 1px solid #cbd5e1 !important;
    }

    #btnTambahBarisBawah,
    html:not([data-bs-theme="dark"]) #btnTambahBarisBawah,
    html[data-bs-theme="light"] #btnTambahBarisBawah {
        border: 1px dashed #435ebe !important;
        color: #435ebe !important;
        background-color: #f8fafc !important;
    }
    #btnTambahBarisBawah:hover,
    html:not([data-bs-theme="dark"]) #btnTambahBarisBawah:hover,
    html[data-bs-theme="light"] #btnTambahBarisBawah:hover {
        background-color: #435ebe !important;
        color: #ffffff !important;
    }

    /* ---------------------------------------------------------
       DARK MODE (HANYA saat html[data-bs-theme="dark"])
       --------------------------------------------------------- */
    html[data-bs-theme="dark"] #tableItems tr.alat-item-row {
        background-color: #1e1e2d !important;
        border: 1px solid #35354f !important;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3) !important;
    }

    html[data-bs-theme="dark"] .alat-card-header {
        background-color: #151521 !important;
        border: 1px solid #35354f !important;
    }

    html[data-bs-theme="dark"] .alat-card-label {
        color: #9899ac !important;
    }

    html[data-bs-theme="dark"] .alat-input-readonly {
        background-color: #151521 !important;
        color: #9899ac !important;
        border: 1px solid #35354f !important;
    }

    /* Header tabel desktop di dark mode */
    html[data-bs-theme="dark"] #tableItems thead.table-light th {
        background-color: #1b1b29 !important;
        color: #c2c2d9 !important;
        border-color: #35354f !important;
    }

    /* Alert ketentuan di Dark Mode */
    html[data-bs-theme="dark"] #addPeminjamanAlatModal .alert-light-primary {
        background-color: rgba(67, 94, 190, 0.15) !important;
        color: #b7c3ef !important;
        border: 1px solid rgba(67, 94, 190, 0.3) !important;
    }

    /* Tombol tambah baris bawah di Dark Mode */
    html[data-bs-theme="dark"] #btnTambahBarisBawah {
        border: 1px dashed #435ebe !important;
        color: #b7c3ef !important;
        background-color: rgba(67, 94, 190, 0.1) !important;
    }
    html[data-bs-theme="dark"] #btnTambahBarisBawah:hover {
        background-color: #435ebe !important;
        color: #ffffff !important;
    }
</style>
    <section class="section">
        <div class="card">
            <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <h5 class="card-title mb-0">Daftar Peminjaman Alat Laboratorium</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPeminjamanAlatModal" id="btnTambahPeminjaman">
                    <i class="bi bi-plus-circle me-1"></i> Ajukan Peminjaman Alat
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="peminjamanAlatTable" class="table table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Lab / Ruang</th>
                                <th>Daftar Alat</th>
                                <th>Keperluan</th>
                                <th>Waktu Pinjam</th>
                                <th>Rencana Kembali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Ajukan Peminjaman Alat -->
    <div class="modal fade" id="addPeminjamanAlatModal" tabindex="-1" role="dialog" aria-labelledby="addPeminjamanAlatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="addPeminjamanAlatModalLabel">
                        <i class="bi bi-tools"></i> Form Pengajuan Peminjaman Alat Lab
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPeminjamanAlatForm" method="POST" action="javascript:void(0);">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lab_id" class="form-label fw-bold">Pilih Ruangan / Lab Asal Alat <span class="text-danger">*</span></label>
                                <select name="lab_id" id="lab_id" class="form-select" required>
                                    <option value="">-- Pilih Ruangan / Lab Terlebih Dahulu --</option>
                                    @foreach ($labs as $lab)
                                        <option value="{{ $lab->id }}">{{ $lab->nama_lab }} ({{ $lab->lokasi }})</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-text" id="labIdError"></span>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle"></i> Memilih ruangan akan otomatis memuat daftar alat inventaris yang tersedia di ruangan tersebut.
                                </small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="keperluan" class="form-label fw-bold">Tujuan / Keperluan Peminjaman <span class="text-danger">*</span></label>
                                <textarea name="keperluan" id="keperluan" rows="2" class="form-control" placeholder="Contoh: Praktikum Mandiri / Tugas Akhir Pembuatan Robot" required></textarea>
                                <span class="text-danger error-text" id="keperluanError"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="waktu_pinjam" class="form-label fw-bold">Waktu Mulai Pinjam <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="waktu_pinjam" id="waktu_pinjam" class="form-control" required>
                                <span class="text-danger error-text" id="waktuPinjamError"></span>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="rencana_kembali" class="form-label fw-bold">Rencana Waktu Pengembalian <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="rencana_kembali" id="rencana_kembali" class="form-control" required>
                                <span class="text-danger error-text" id="rencanaKembaliError"></span>
                            </div>
                        </div>

                        <hr>

                        <!-- Repeater Daftar Alat -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0"><i class="bi bi-card-checklist"></i> Daftar Alat yang Ingin Dipinjam</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahBarisAlat">
                                <i class="bi bi-plus-lg"></i> Tambah Baris Alat
                            </button>
                        </div>

                        <div class="table-responsive mb-2">
                            <table class="table table-bordered table-sm align-middle mb-0" id="tableItems">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th style="width: 17%;">Sumber Alat</th>
                                        <th style="width: 40%;">Nama Alat / Pilihan Inventaris</th>
                                        <th style="width: 10%;">Jumlah</th>
                                        <th style="width: 12%;">Satuan</th>
                                        <th style="width: 16%;">Keterangan / Spesifikasi</th>
                                        <th style="width: 5%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyItems">
                                    <!-- Baris item akan dimasukkan di sini oleh JS -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Tombol Tambah Baris Khusus Mobile (Mudah dijangkau di bawah item) -->
                        <div class="d-md-none text-center mb-3">
                            <button type="button" class="btn btn-outline-primary w-100 py-2 fw-semibold" id="btnTambahBarisBawah">
                                <i class="bi bi-plus-circle me-1"></i> + Tambah Baris Alat
                            </button>
                        </div>

                        <div class="alert alert-light-primary color-primary mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill fs-4 me-2"></i>
                                <div>
                                    <strong>Ketentuan Peminjaman Alat:</strong>
                                    <ul class="mb-0 ps-3">
                                        <li>Alat yang dipilih dari <strong>Inventaris</strong> memiliki jumlah <strong>1 unit per NUP</strong> (terkunci otomatis), dan satuannya mengikuti data inventaris.</li>
                                        <li>Gunakan <strong>Input Manual</strong> jika alat belum tercatat di inventaris. Pada mode ini, Anda dapat menginputkan jumlah dan satuan secara bebas.</li>
                                        <li>Peminjaman alat memerlukan persetujuan langsung dari <strong>PLP</strong> penanggung jawab ruangan tersebut.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column-reverse flex-sm-row justify-content-between gap-2 pt-2 border-top">
                            <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i> Batal / Tutup
                            </button>
                            <button type="submit" class="btn btn-success w-100 w-sm-auto" id="btnSubmitPeminjaman">
                                <i class="bi bi-send me-1"></i> Kirim Pengajuan ke PLP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Peminjaman Alat -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white" id="detailModalLabel">
                        <i class="bi bi-info-circle"></i> Detail Peminjaman Alat
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered mb-3">
                        <tr>
                            <th style="width: 30%;">Kode Peminjaman</th>
                            <td id="dKode" class="fw-bold"></td>
                        </tr>
                        <tr>
                            <th>Nama Peminjam</th>
                            <td id="dNamaMahasiswa"></td>
                        </tr>
                        <tr>
                            <th>NRP / Prodi</th>
                            <td id="dNrpProdi"></td>
                        </tr>
                        <tr>
                            <th>Laboratorium / Ruang</th>
                            <td id="dLab"></td>
                        </tr>
                        <tr>
                            <th>Keperluan</th>
                            <td id="dKeperluan"></td>
                        </tr>
                        <tr>
                            <th>Waktu Pinjam</th>
                            <td id="dWaktuPinjam"></td>
                        </tr>
                        <tr>
                            <th>Rencana Kembali</th>
                            <td id="dRencanaKembali"></td>
                        </tr>
                        <tr>
                            <th>Status Peminjaman</th>
                            <td id="dStatus"></td>
                        </tr>
                        <tr>
                            <th>PLP Penyetuju</th>
                            <td id="dPlp"></td>
                        </tr>
                        <tr id="rowWaktuDisetujui" style="display:none;">
                            <th>Waktu Disetujui PLP</th>
                            <td id="dWaktuDisetujui"></td>
                        </tr>
                        <tr id="rowCatatanTolak" style="display:none;">
                            <th class="text-danger">Catatan Penolakan</th>
                            <td id="dCatatanTolak" class="text-danger fw-bold"></td>
                        </tr>
                        <tr id="rowAlasanBatal" style="display:none;">
                            <th class="text-secondary">Alasan Pembatalan</th>
                            <td id="dAlasanBatal" class="text-secondary"></td>
                        </tr>
                        <tr id="rowWaktuKembali" style="display:none;">
                            <th>Waktu Pengembalian</th>
                            <td id="dWaktuKembali"></td>
                        </tr>
                    </table>

                    <h6 class="fw-bold"><i class="bi bi-list-check"></i> Daftar Alat yang Dipinjam:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Alat</th>
                                    <th>Kode / NUP</th>
                                    <th>Ruang / Lab</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Keterangan</th>
                                    <th>Status Sumber</th>
                                </tr>
                            </thead>
                            <tbody id="detailItemsBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="btnCetakModal" target="_blank" class="btn btn-primary" style="display: none;">
                        <i class="bi bi-printer"></i> Cetak PDF
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Cache alat inventaris untuk ruangan yang sedang dipilih
        let currentLabItems = [];
        let rowIndex = 0;

        // Fungsi membuat elemen option untuk dropdown inventaris (per setiap NUP)
        function buildInventarisOptionsHtml() {
            const labId = $('#lab_id').val();
            if (!labId) {
                return '<option value="">-- Pilih Ruangan / Lab Terlebih Dahulu --</option>';
            }

            if (currentLabItems.length === 0) {
                return '<option value="">-- Tidak ada alat inventaris yang bisa dipinjam di ruangan ini (Gunakan Input Manual) --</option>';
            }

            let html = '<option value="">-- Pilih Alat & NUP dari Inventaris --</option>';
            currentLabItems.forEach(item => {
                const nupStr = (item.nup && item.nup !== '-') ? ` (NUP: ${item.nup})` : '';
                const spekStr = item.spesifikasi_merk_tipe ? ` [${item.spesifikasi_merk_tipe}]` : '';
                const statusStr = item.is_dipinjam ? ' - [Sedang Dipinjam]' : '';
                const disabledAttr = item.is_dipinjam ? 'disabled' : '';

                html += `<option value="${item.unique_id}" data-id="${item.inventaris_ruangan_id}" data-nup="${item.nup}" data-satuan="${item.satuan || 'Unit'}" data-spek="${item.spesifikasi_merk_tipe || ''}" ${disabledAttr}>
                    ${item.nama_barang}${nupStr}${spekStr}${statusStr}
                </option>`;
            });
            return html;
        }

        // Fungsi memperbarui seluruh dropdown inventaris yang ada di tabel
        function updateAllInventarisSelects() {
            const optionsHtml = buildInventarisOptionsHtml();
            $('.select-inventaris').each(function() {
                const index = $(this).data('index');
                $(this).html(optionsHtml);
                $(`#nup_${index}`).val('');
                $(`#satuan_${index}`).val('Unit');
                $(`#jumlah_${index}`).val(1);
            });
        }

        function updateRowNumbers() {
            $('#tbodyItems tr.alat-item-row').each(function(idx) {
                $(this).find('.alat-number-badge').html('<i class="bi bi-tools me-1"></i>Alat #' + (idx + 1));
            });
        }

        // Fungsi menambah baris alat ke form pengajuan
        function addAlatRow() {
            const index = rowIndex++;
            const optionsHtml = buildInventarisOptionsHtml();

            const rowHtml = `
                <tr id="itemRow_${index}" class="alat-item-row">
                    <!-- Mobile Card Header (Hanya tampil di layar Mobile < 768px) -->
                    <td class="alat-col-header d-md-none p-0 border-0 mb-2">
                        <div class="d-flex justify-content-between align-items-center alat-card-header p-2 rounded">
                            <span class="badge bg-primary alat-number-badge"><i class="bi bi-tools me-1"></i>Alat</span>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row py-0 px-2" data-index="${index}">
                                <i class="bi bi-trash me-1"></i>Hapus
                            </button>
                        </div>
                    </td>

                    <!-- Sumber Alat -->
                    <td class="alat-col-sumber">
                        <label class="d-md-none form-label small fw-bold alat-card-label mb-1">Sumber Alat:</label>
                        <select name="items[${index}][is_manual]" class="form-select form-select-sm select-sumber" data-index="${index}">
                            <option value="0">Dari Inventaris</option>
                            <option value="1">Input Manual</option>
                        </select>
                    </td>

                    <!-- Nama Alat / Pilihan Inventaris -->
                    <td class="alat-col-nama">
                        <label class="d-md-none form-label small fw-bold alat-card-label mb-1">Nama Alat / Inventaris:</label>
                        <div class="box-inventaris" id="boxInventaris_${index}">
                            <select name="items[${index}][inventaris_ruangan_id]" class="form-select form-select-sm select-inventaris" data-index="${index}">
                                ${optionsHtml}
                            </select>
                            <input type="hidden" name="items[${index}][nup]" id="nup_${index}" class="input-nup" value="">
                        </div>
                        <div class="box-manual d-none" id="boxManual_${index}">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-warning text-dark fw-bold">Manual</span>
                                <input type="text" name="items[${index}][nama_alat]" class="form-control" placeholder="Ketik nama alat lengkap...">
                            </div>
                        </div>
                    </td>

                    <!-- Jumlah -->
                    <td class="alat-col-jumlah">
                        <label class="d-md-none form-label small fw-bold alat-card-label mb-1">Jumlah:</label>
                        <input type="number" name="items[${index}][jumlah]" class="form-control form-control-sm text-center input-jumlah bg-light alat-input-readonly" id="jumlah_${index}" value="1" min="1" readonly required title="Jumlah terkunci 1 per NUP untuk alat inventaris">
                    </td>

                    <!-- Satuan -->
                    <td class="alat-col-satuan">
                        <label class="d-md-none form-label small fw-bold alat-card-label mb-1">Satuan:</label>
                        <input type="text" name="items[${index}][satuan]" class="form-control form-control-sm input-satuan bg-light alat-input-readonly" id="satuan_${index}" value="Unit" placeholder="Unit/Pcs" readonly title="Satuan otomatis mengikuti master inventaris">
                    </td>

                    <!-- Keterangan / Spesifikasi -->
                    <td class="alat-col-keterangan">
                        <label class="d-md-none form-label small fw-bold alat-card-label mb-1">Keterangan / Spesifikasi:</label>
                        <input type="text" name="items[${index}][keterangan]" class="form-control form-control-sm input-keterangan" id="keterangan_${index}" placeholder="Catatan/spek tambahan (opsional)...">
                    </td>

                    <!-- Tombol Hapus Desktop (Hanya tampil di Desktop >= 768px) -->
                    <td class="alat-col-aksi text-center d-none d-md-table-cell">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" data-index="${index}" title="Hapus Baris">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#tbodyItems').append(rowHtml);
            updateRowNumbers();
        }

        $(document).ready(function() {
            // Sinkronisasi class body dengan tema aktif
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || localStorage.getItem('theme') || 'light';
            if (currentTheme === 'light') {
                $('body').removeClass('dark theme-dark').addClass('light');
            } else {
                $('body').removeClass('light theme-light').addClass('dark');
            }

            // Inisialisasi DataTable
            const table = $('#peminjamanAlatTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('peminjaman-alat.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode_peminjaman', name: 'kode_peminjaman' },
                    { data: 'lab', name: 'lab' },
                    { data: 'daftar_alat', name: 'daftar_alat', orderable: false },
                    { data: 'keperluan', name: 'keperluan' },
                    { data: 'waktu_pinjam', name: 'waktu_pinjam' },
                    { data: 'rencana_kembali', name: 'rencana_kembali' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-nowrap' },
                ]
            });

            // Event saat ruangan / lab dipilih: AJAX refresh alat inventaris
            $('#lab_id').on('change', function() {
                const labId = $(this).val();

                if (!labId) {
                    currentLabItems = [];
                    updateAllInventarisSelects();
                    return;
                }

                // Indikator loading sementara di select inventaris
                $('.select-inventaris').html('<option value="">Sedang memuat alat inventaris...</option>');

                $.ajax({
                    url: "{{ url('peminjaman-alat/get-alat') }}/" + labId,
                    type: "GET",
                    success: function(items) {
                        currentLabItems = items;
                        updateAllInventarisSelects();
                    },
                    error: function() {
                        $('.select-inventaris').html('<option value="">Gagal memuat alat inventaris</option>');
                    }
                });
            });

            // Tambahkan baris pertama saat modal dibuka jika kosong
            $('#btnTambahPeminjaman').on('click', function() {
                if ($('#tbodyItems tr').length === 0) {
                    addAlatRow();
                }
            });

            $('#btnTambahBarisAlat, #btnTambahBarisBawah').on('click', function() {
                addAlatRow();
            });

            // Hapus baris alat
            $(document).on('click', '.btn-remove-row', function() {
                if ($('#tbodyItems tr.alat-item-row').length <= 1) {
                    Swal.fire('Info', 'Minimal harus ada satu alat dalam pengajuan.', 'info');
                    return;
                }
                $(this).closest('tr.alat-item-row').remove();
                updateRowNumbers();
            });

            // Toggle antara Pilihan Inventaris dan Input Manual
            $(document).on('change', '.select-sumber', function() {
                const index = $(this).data('index');
                const isManual = $(this).val() === '1';

                const inputJumlah = $(`#jumlah_${index}`);
                const inputSatuan = $(`#satuan_${index}`);

                if (isManual) {
                    $(`#boxInventaris_${index}`).addClass('d-none');
                    $(`#boxManual_${index}`).removeClass('d-none');

                    // Reset pilihan inventaris & NUP
                    $(`#boxInventaris_${index} select`).val('');
                    $(`#nup_${index}`).val('');

                    // Input Manual: Jumlah dan Satuan bisa diedit bebas
                    inputJumlah.prop('readonly', false).removeClass('bg-light alat-input-readonly').val(1).removeAttr('title');
                    inputSatuan.prop('readonly', false).removeClass('bg-light alat-input-readonly').val('Unit').removeAttr('title');
                } else {
                    $(`#boxInventaris_${index}`).removeClass('d-none');
                    $(`#boxManual_${index}`).addClass('d-none');

                    // Dari Inventaris: Jumlah terkunci 1 dan Satuan diambil dari inventaris
                    inputJumlah.prop('readonly', true).addClass('bg-light alat-input-readonly').val(1).attr('title', 'Jumlah terkunci 1 per NUP untuk alat inventaris');
                    inputSatuan.prop('readonly', true).addClass('bg-light alat-input-readonly').attr('title', 'Satuan otomatis mengikuti master inventaris');

                    const selected = $(`#boxInventaris_${index} select`).find(':selected');
                    const satuan = selected.data('satuan');
                    const nup = selected.data('nup');
                    if (satuan) {
                        inputSatuan.val(satuan);
                    }
                    $(`#nup_${index}`).val(nup && nup !== '-' ? nup : '');
                }
            });

            // Saat memilih alat inventaris, isi satuan secara otomatis & pertahankan jumlah 1
            $(document).on('change', '.select-inventaris', function() {
                const index = $(this).data('index');
                const selected = $(this).find(':selected');
                const val = $(this).val();

                // Cek duplikasi NUP pada baris lain
                if (val) {
                    let duplicate = false;
                    $('.select-inventaris').not(this).each(function() {
                        if ($(this).val() === val) {
                            duplicate = true;
                            return false;
                        }
                    });
                    if (duplicate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Alat & NUP Sudah Dipilih',
                            text: 'Unit alat dengan NUP ini sudah dipilih pada baris lain. Setiap baris harus memilih NUP yang berbeda.',
                        });
                        $(this).val('');
                        $(`#nup_${index}`).val('');
                        $(`#satuan_${index}`).val('Unit');
                        return;
                    }
                }

                const nup = selected.data('nup');
                const satuan = selected.data('satuan');
                const spek = selected.data('spek');

                // Simpan NUP individual yang dipilih ke hidden input
                $(`#nup_${index}`).val(nup && nup !== '-' ? nup : '');

                if (satuan) {
                    $(`#satuan_${index}`).val(satuan);
                } else {
                    $(`#satuan_${index}`).val('Unit');
                }

                // Pastikan jumlah tetap 1 dan readonly
                $(`#jumlah_${index}`).val(1).prop('readonly', true).addClass('bg-light');

                if (spek) {
                    $(`#keterangan_${index}`).attr('placeholder', spek);
                }
            });

            // Submit Form Pengajuan
            $('#addPeminjamanAlatForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-text').text('');

                const formData = $(this).serialize();
                const submitBtn = $('#btnSubmitPeminjaman');
                submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Menyimpan...');

                $.ajax({
                    url: "{{ route('peminjaman-alat.store') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-send"></i> Kirim Pengajuan ke PLP');
                        $('#addPeminjamanAlatModal').modal('hide');
                        $('#addPeminjamanAlatForm')[0].reset();
                        $('#tbodyItems').empty();
                        currentLabItems = [];
                        rowIndex = 0;
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diajukan!',
                            text: res.message || 'Peminjaman alat berhasil dikirim.',
                        });
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-send"></i> Kirim Pengajuan ke PLP');
                        const res = xhr.responseJSON;

                        if (xhr.status === 422) {
                            if (res.errors) {
                                if (res.errors.lab_id) $('#labIdError').text(res.errors.lab_id[0]);
                                if (res.errors.keperluan) $('#keperluanError').text(res.errors.keperluan[0]);
                                if (res.errors.waktu_pinjam) $('#waktuPinjamError').text(res.errors.waktu_pinjam[0]);
                                if (res.errors.rencana_kembali) $('#rencanaKembaliError').text(res.errors.rencana_kembali[0]);
                            }
                            Swal.fire({
                                icon: 'warning',
                                title: 'Periksa Formulir',
                                text: res.message || 'Harap periksa kembali isian formulir Anda.',
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res?.message || 'Terjadi kesalahan sistem.',
                            });
                        }
                    }
                });
            });

            // Tampilkan Detail Modal
            $(document).on('click', '.detailBtn', function() {
                const id = $(this).data('id');
                const btn = $(this);
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ url('peminjaman-alat') }}/" + id,
                    type: "GET",
                    success: function(res) {
                        btn.prop('disabled', false);
                        if (!res.success) return;
                        const data = res.data;

                        $('#dKode').text(data.kode_peminjaman);
                        $('#dNamaMahasiswa').text(data.mahasiswa_nama);
                        $('#dNrpProdi').text(`${data.mahasiswa_nrp} / ${data.mahasiswa_prodi}`);
                        $('#dLab').text(data.lab_nama);
                        $('#dKeperluan').text(data.keperluan);
                        $('#dWaktuPinjam').text(data.waktu_pinjam);
                        $('#dRencanaKembali').text(data.rencana_kembali);
                        $('#dStatus').html(data.status_badge);
                        $('#dPlp').text(data.plp_nama);

                        // Waktu Disetujui
                        if (data.tgl_ttd_plp && data.tgl_ttd_plp !== '-') {
                            $('#rowWaktuDisetujui').show();
                            $('#dWaktuDisetujui').text(data.tgl_ttd_plp);
                        } else {
                            $('#rowWaktuDisetujui').hide();
                        }

                        // Catatan tolak
                        if (data.status === 'ditolak' && data.catatan_penolakan !== '-') {
                            $('#rowCatatanTolak').show();
                            $('#dCatatanTolak').text(data.catatan_penolakan);
                        } else {
                            $('#rowCatatanTolak').hide();
                        }

                        // Alasan Batal
                        if (data.status === 'dibatalkan' && data.alasan_batal !== '-') {
                            $('#rowAlasanBatal').show();
                            $('#dAlasanBatal').text(data.alasan_batal);
                        } else {
                            $('#rowAlasanBatal').hide();
                        }

                        // Waktu Kembali
                        if (data.waktu_kembali && data.waktu_kembali !== '-') {
                            $('#rowWaktuKembali').show();
                            $('#dWaktuKembali').text(`${data.waktu_kembali} (${data.kondisi_kembali_catatan})`);
                        } else {
                            $('#rowWaktuKembali').hide();
                        }

                        // Render items
                        let itemsHtml = '';
                        data.items.forEach((item, idx) => {
                            const badgeSumber = item.is_manual
                                ? '<span class="badge bg-warning text-dark"><i class="bi bi-pencil-square"></i> Input Manual</span>'
                                : '<span class="badge bg-success"><i class="bi bi-box-seam"></i> Inventaris DIR</span>';

                            itemsHtml += `
                                <tr>
                                    <td class="text-center">${idx + 1}</td>
                                    <td class="fw-bold">${item.nama_alat}</td>
                                    <td>${item.kode_barang} / ${item.nup}</td>
                                    <td>${item.lokasi_lab}</td>
                                    <td class="text-center">${item.jumlah}</td>
                                    <td>${item.satuan}</td>
                                    <td><small>${item.keterangan !== '-' ? item.keterangan : (item.spesifikasi !== '-' ? item.spesifikasi : '-')}</small></td>
                                    <td class="text-center">${badgeSumber}</td>
                                </tr>
                            `;
                        });
                        $('#detailItemsBody').html(itemsHtml);

                        // Tombol cetak
                        if (['disetujui', 'sedang_dipinjam', 'selesai'].includes(data.status)) {
                            $('#btnCetakModal').attr('href', "{{ url('peminjaman-alat/cetak') }}/" + data.id).show();
                        } else {
                            $('#btnCetakModal').hide();
                        }

                        $('#detailModal').modal('show');
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memuat Detail',
                            text: xhr.responseJSON?.message || 'Tidak dapat memuat detail peminjaman (Error: ' + xhr.status + ').'
                        });
                    }
                });
            });

            // Pembatalan Peminjaman oleh Mahasiswa
            $(document).on('click', '.cancelBtn', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Batalkan Peminjaman?',
                    text: 'Pengajuan peminjaman alat ini akan dibatalkan.',
                    icon: 'warning',
                    input: 'text',
                    inputPlaceholder: 'Tuliskan alasan pembatalan (opsional)...',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Tutup'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('peminjaman-alat') }}/" + id + "/cancel",
                            type: "PATCH",
                            data: {
                                _token: "{{ csrf_token() }}",
                                alasan: result.value || 'Dibatalkan oleh mahasiswa.'
                            },
                            success: function(res) {
                                Swal.fire('Dibatalkan!', res.message, 'success');
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
