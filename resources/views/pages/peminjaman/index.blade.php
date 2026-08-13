@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header text-end">
                <div class="d-flex justify-content-end align-items-end mb-4 mt-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" id="showAddPeminjamaLabModal"
                        data-bs-target="#addPeminjamanModal">+ Ajukan Pinjaman</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="peminjamanTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Lab</th>
                                <th>Keperluan</th>
                                <th>Waktu Mulai</th>
                                <th>Waktu Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Detail Peminjaman -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
        aria-hidden="true">
        <div class="modal-lg modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Peminjaman Lab</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Nama Lab</th>
                            <td id="detailNamaLab"></td>
                        </tr>
                        <tr>
                            <th>PLP</th>
                            <td id="detailPLP"></td>
                        </tr>
                        <tr>
                            <th>Waktu Disetujui PLP</th>
                            <td id="detailWaktuDisetujuiPLP"></td>
                        </tr>
                        <tr>
                            <th>Kalab</th>
                            <td id="detailKalab"></td>
                        </tr>
                        <tr>
                            <th>Waktu Disetujui Kalab</th>
                            <td id="detailWaktuDisetujuiKalab"></td>
                        </tr>
                        <tr>
                            <th>Catatan Tolak</th>
                            <td id="detailCatatanTolak"></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td id="detailStatus"></td>
                        </tr>
                        <tr>
                            <th>Waktu Pengajuan</th>
                            <td id="detailCreatedAt"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <a href="#" id="btnCetakDetail" target="_blank" class="btn btn-primary" style="display: none;"><i
                            class="bi bi-printer"></i> Cetak PDF</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPeminjamanModal" tabindex="-1" role="dialog" aria-labelledby="addPeminjamanModalLabel"
        aria-hidden="true">
        <div class="modal-lg modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Ajukan Peminjaman Lab</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addPeminjamanForm" method="POST" action="javascript:void(0);">
                        <div class="form-group mb-3">
                            <label for="lab_id" class="form-label">Pilih Laboratorium</label>
                            <select name="lab_id" class="form-control" id="lab_id">
                                <option value="">-- Pilih Lab --</option>
                                @foreach ($labs as $lab)
                                    <option value="{{ $lab->id }}">{{ $lab->nama_lab }} ({{ $lab->kode_lab }})</option>
                                @endforeach
                                <span class="text-danger" id="labError"></span>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="tujuan" class="form-label">Tujuan Peminjaman</label>
                            <textarea name="tujuan" class="form-control" rows="3" placeholder="Contoh: Pengerjaan Tugas Akhir"
                                id="tujuan"></textarea>
                            <span class="text-danger" id="tujuanError"></span>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
                                <input type="datetime-local" name="waktu_mulai" class="form-control" id="waktu_mulai">
                                <span class="text-danger" id="waktuMulaiError"></span>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="waktu_selesai" class="form-label">Waktu Selesai</label>
                                <input type="datetime-local" name="waktu_selesai" class="form-control"
                                    id="waktu_selesai">
                                <span class="text-danger" id="waktuSelesaiError"></span>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i>
                            <strong>Informasi:</strong>
                            <ul>
                                <li>Setelah Anda submit pengajuan, Harap menghubungi PLP dan Kalab
                                    yang bersangkutan.</li>
                                <li>Harap pilih waktu mulai setelah waktu sekarang.</li>
                                <li>Waktu peminjaman dimulai dari jam 08.00 - 21.00 WIB</li>
                                <li>Peminjaman akan dibatalkan sistem ketika 1x24 jam belum disetujui.</li>
                                <li>Harap meminjam 1 hari sebelum hari peminjaman.</li>
                            </ul>
                            <strong>Lama waktu peminjaman:</strong>
                            <ul>
                                <li>Maksimal 7 hari untuk pengerjaan PA (Proyek Akhir).</li>
                                <li>Maksimal 3 hari untuk pengerjaan tugas kuliah.</li>
                                <li>Apabila peminjaman mendesak, hanya bisa meminjam pada hari tersebut.</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-success">Kirim Pengajuan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#peminjamanTable').DataTable({
                processing: true,
                serverSide: true,
                pagingType: 'simple',
                ajax: "{{ route('peminjaman.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_lab',
                        name: 'nama_lab',
                        className: 'text-nowrap'
                    },
                    {
                        data: 'keperluan',
                        name: 'keperluan',
                        width: '30%'
                    },
                    {
                        data: 'waktu_mulai',
                        name: 'waktu_mulai',
                    },
                    {
                        data: 'waktu_selesai',
                        name: 'waktu_selesai',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        className: 'text-center text-nowrap'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('body').on('click', '.detailData', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: "/peminjaman/detail/" + id,
                    method: "GET",
                    success: function(response) {
                        let detail = response.data;
                        $('#detailNamaLab').text(detail.nama_lab);
                        $('#detailPLP').text(detail.plp ?? '-');
                        $('#detailWaktuDisetujuiPLP').text(detail.tgl_ttd_plp ?? '-');
                        $('#detailKalab').text(detail.kalab ?? '-');
                        $('#detailWaktuDisetujuiKalab').text(detail.tgl_ttd_kalab ?? '-');
                        $('#detailCatatanTolak').text(detail.catatan_tolak + ' ' + detail
                            .penolak);
                        $('#detailStatus').html(detail.status_badge);
                        if (detail.status_badge ==
                            '<span class="badge bg-success">Disetujui</span>' && detail
                            .ttd_mahasiswa_file != null && detail.ttd_kalab_file != null &
                            detail.ttd_plp_file != null || detail.status_badge ==
                            '<span class="badge bg-secondary">Kadaluarsa</span>' && detail
                            .ttd_mahasiswa_file != null && detail.ttd_kalab_file != null &
                            detail.ttd_plp_file != null) {
                            $('#btnCetakDetail').attr('href', '/peminjaman/cetak/' + id).show();
                        } else {
                            $('#btnCetakDetail').hide();
                        }
                        $('#detailCreatedAt').text(detail.created_at);
                    },
                    error: function() {
                        Toastify({
                            text: 'Gagal mengambil detail peminjaman.',
                            backgroundColor: "#dc3545",
                            position: "right",
                        }).showToast();
                    }
                });
            });

            $('#detailModal').on('hidden.bs.modal', function() {
                $('#btnCetakDetail').hide();
                $('#detailNamaLab').text('');
                $('#detailPLP').text('');
                $('#detailWaktuDisetujuiPLP').text('');
                $('#detailKalab').text('');
                $('#detailWaktuDisetujuiKalab').text('');
                $('#detailCatatanTolak').text('');
                $('#detailStatus').html('');
            });

            $('#addPeminjamanForm').on('submit', function(e) {
                e.preventDefault();

                // Reset pesan error setiap kali kirim
                $('.text-danger').text('');
                let $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('Mengirim...');

                $.ajax({
                    url: "{{ route('peminjaman.store') }}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#addPeminjamanModal').modal('hide');
                        $('#peminjamanTable').DataTable().ajax.reload();

                        Toastify({
                            text: response.message,
                            backgroundColor: "#4fbe87",
                            position: "right",
                        }).showToast();

                        $('#addPeminjamanForm')[0].reset();
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            // Clear previous errors
                            $('.text-danger').text('');
                            // Mapping error ke ID masing-masing span
                            if (errors.lab_id) $('#labError').text(errors.lab_id[0]);
                            if (errors.tujuan) $('#tujuanError').text(errors.tujuan[0]);
                            if (errors.waktu_mulai) $('#waktuMulaiError').text(errors
                                .waktu_mulai[0]);
                            if (errors.waktu_selesai) $('#waktuSelesaiError').text(errors
                                .waktu_selesai[0]);
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            Toastify({
                                text: xhr.responseJSON.message,
                                backgroundColor: "#dc3545",
                                position: "right",
                                duration: 5000,
                            }).showToast();
                        } else {
                            Toastify({
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                                backgroundColor: "#dc3545",
                                position: "right",
                            }).showToast();
                        }
                    },
                    complete: function() {
                        // Re-enable button if needed
                        $btn.prop('disabled', false).text('Kirim Pengajuan');
                    }
                });
            });

            // Handle Batalkan Pengajuan
            $('body').on('click', '.cancelData', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Anda akan membatalkan pengajuan peminjaman ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Tutup'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/peminjaman/" + id + "/cancel",
                            method: "PATCH",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Toastify({
                                        text: response.message,
                                        backgroundColor: "#4fbe87",
                                        position: "right",
                                    }).showToast();
                                    $('#peminjamanTable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire('Gagal', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                let msg = 'Terjadi kesalahan sistem.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
