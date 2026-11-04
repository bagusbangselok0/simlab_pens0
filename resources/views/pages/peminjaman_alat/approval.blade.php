@extends('layouts.app')

@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Persetujuan Peminjaman Alat Laboratorium</h5>

                        {{-- Filter Status --}}
                        <div class="d-flex align-items-center gap-2">
                            <label for="filterStatus" class="form-label mb-0 fw-bold">Status:</label>
                            <select class="form-select form-select-sm w-auto" id="filterStatus">
                                <option value="all">Semua Status</option>
                                <option value="pending_plp" selected>Menunggu PLP</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                                <option value="selesai">Selesai / Dikembalikan</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover w-100" id="approvalAlatTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Mahasiswa</th>
                                        <th>Lab / Ruang</th>
                                        <th>Daftar Alat</th>
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
            </div>
        </section>
    </div>

    <!-- Modal Detail Peminjaman Alat untuk PLP -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white" id="detailModalLabel">
                        <i class="bi bi-info-circle"></i> Detail Pengajuan Peminjaman Alat
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
                            <th>Nama Mahasiswa</th>
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
                            <th>Rencana Pengembalian</th>
                            <td id="dRencanaKembali"></td>
                        </tr>
                        <tr>
                            <th>Status Saat Ini</th>
                            <td id="dStatus"></td>
                        </tr>
                        <tr id="rowCatatanTolak" style="display:none;">
                            <th class="text-danger">Alasan Penolakan</th>
                            <td id="dCatatanTolak" class="text-danger fw-bold"></td>
                        </tr>
                        <tr id="rowWaktuKembali" style="display:none;">
                            <th>Waktu Kembali Aktual</th>
                            <td id="dWaktuKembali"></td>
                        </tr>
                    </table>

                    <h6 class="fw-bold"><i class="bi bi-tools"></i> Rincian Alat yang Dipinjam:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Alat</th>
                                    <th>Kode / NUP</th>
                                    <th>Ruang Asal</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Keterangan / Spek</th>
                                    <th>Sumber Data</th>
                                </tr>
                            </thead>
                            <tbody id="detailItemsBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="modalActionButtons"></div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tolak Peminjaman -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-x-circle"></i> Tolak Peminjaman Alat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rejectPeminjamanId">
                    <div class="form-group">
                        <label for="catatan_penolakan" class="form-label fw-bold">Alasan / Catatan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="catatan_penolakan" rows="3" placeholder="Contoh: Alat sedang dalam masa kalibrasi / perbaikan..." required></textarea>
                        <span class="text-danger" id="rejectError"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnSubmitReject">Tolak Pengajuan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Pengembalian Alat -->
    <div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-arrow-return-left"></i> Konfirmasi Pengembalian Alat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="returnPeminjamanId">
                    <div class="form-group">
                        <label for="catatan_pengembalian" class="form-label fw-bold">Catatan Kondisi Alat saat Kembali</label>
                        <textarea class="form-control" id="catatan_pengembalian" rows="3" placeholder="Contoh: Alat telah dikembalikan lengkap dalam keadaan baik."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnSubmitReturn">Konfirmasi Selesai</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#approvalAlatTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('peminjaman-alat.approval') }}",
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode_peminjaman', name: 'kode_peminjaman' },
                    { data: 'mahasiswa', name: 'mahasiswa' },
                    { data: 'lab', name: 'lab' },
                    { data: 'daftar_alat', name: 'daftar_alat', orderable: false },
                    { data: 'waktu_pinjam', name: 'waktu_pinjam' },
                    { data: 'rencana_kembali', name: 'rencana_kembali' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            $('#filterStatus').on('change', function() {
                table.ajax.reload();
            });

            // Tampilkan Detail
            $(document).on('click', '.detailBtn', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ url('peminjaman-alat') }}/" + id,
                    type: "GET",
                    success: function(res) {
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

                        if (data.status === 'ditolak' && data.catatan_penolakan !== '-') {
                            $('#rowCatatanTolak').show();
                            $('#dCatatanTolak').text(data.catatan_penolakan);
                        } else {
                            $('#rowCatatanTolak').hide();
                        }

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

                        // Actions di modal
                        let actionBtns = '';
                        if (data.status === 'pending_plp') {
                            actionBtns += `<button class="btn btn-success approveBtn me-1" data-id="${data.id}"><i class="bi bi-check-circle"></i> Setujui</button>`;
                            actionBtns += `<button class="btn btn-danger rejectBtn me-1" data-id="${data.id}"><i class="bi bi-x-circle"></i> Tolak</button>`;
                        }
                        if (['disetujui', 'sedang_dipinjam', 'selesai'].includes(data.status)) {
                            actionBtns += `<a href="{{ url('peminjaman-alat/cetak') }}/${data.id}" target="_blank" class="btn btn-secondary me-1"><i class="bi bi-printer"></i> Cetak PDF</a>`;
                        }
                        $('#modalActionButtons').html(actionBtns);

                        $('#detailModal').modal('show');
                    }
                });
            });

            // Setujui Peminjaman oleh PLP
            $(document).on('click', '.approveBtn', function() {
                const id = $(this).data('id');
                $('#detailModal').modal('hide');

                Swal.fire({
                    title: 'Setujui Peminjaman Alat?',
                    text: 'Pastikan Anda telah memeriksa ketersediaan alat fisik yang diminta.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Setujui!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('peminjaman-alat') }}/" + id + "/approve",
                            type: "PATCH",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(res) {
                                Swal.fire('Disetujui!', res.message, 'success');
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });

            // Buka Modal Tolak
            $(document).on('click', '.rejectBtn', function() {
                const id = $(this).data('id');
                $('#detailModal').modal('hide');
                $('#rejectPeminjamanId').val(id);
                $('#catatan_penolakan').val('');
                $('#rejectError').text('');
                $('#rejectModal').modal('show');
            });

            // Submit Penolakan
            $('#btnSubmitReject').on('click', function() {
                const id = $('#rejectPeminjamanId').val();
                const catatan = $('#catatan_penolakan').val();

                if (!catatan.trim()) {
                    $('#rejectError').text('Harap isi alasan penolakan.');
                    return;
                }

                $.ajax({
                    url: "{{ url('peminjaman-alat') }}/" + id + "/reject",
                    type: "PATCH",
                    data: {
                        _token: "{{ csrf_token() }}",
                        catatan_penolakan: catatan
                    },
                    success: function(res) {
                        $('#rejectModal').modal('hide');
                        Swal.fire('Ditolak', res.message, 'success');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                    }
                });
            });

            // Buka Modal Pengembalian
            $(document).on('click', '.returnBtn', function() {
                const id = $(this).data('id');
                $('#returnPeminjamanId').val(id);
                $('#catatan_pengembalian').val('Alat telah dikembalikan lengkap dan dalam kondisi baik.');
                $('#returnModal').modal('show');
            });

            // Submit Pengembalian
            $('#btnSubmitReturn').on('click', function() {
                const id = $('#returnPeminjamanId').val();
                const catatan = $('#catatan_pengembalian').val();

                $.ajax({
                    url: "{{ url('peminjaman-alat') }}/" + id + "/kembalikan",
                    type: "PATCH",
                    data: {
                        _token: "{{ csrf_token() }}",
                        catatan: catatan
                    },
                    success: function(res) {
                        $('#returnModal').modal('hide');
                        Swal.fire('Selesai', res.message, 'success');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                    }
                });
            });
        });
    </script>
@endsection

