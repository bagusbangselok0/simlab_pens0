@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    Daftar Laboratorium & Ruangan
                </h5>
            </div>
            <div class="card-body">
                <button class="btn btn-primary btn-round ml-auto" data-bs-toggle="modal" id="showAddLabModal"
                    data-bs-target="#addLabModal">
                    <i class="fa fa-plus"></i>
                    Tambah Data
                </button>
                <div class="table-responsive">
                    <table class="table table-striped" id="labTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Lab</th>
                                <th>Kode Lab</th>
                                <th>Lokasi</th>
                                <th>Bisa Dipinjam Ruangan</th>
                                <th>Updated At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Tambah Data -->
    <div class="modal fade" id="addLabModal" tabindex="-1" role="dialog" aria-labelledby="addLabModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addLabForm">
                        <input type="hidden" id="lab_id">
                        <div class="form-group">
                            <label for="nama_lab">Nama Lab / Ruangan</label>
                            <input type="text" class="form-control" id="nama_lab" name="nama_lab">
                            <span class="text-danger" id="namaLabError"></span>
                        </div>
                        <div class="form-group">
                            <label for="kode_lab">Kode Lab / Ruangan</label>
                            <input type="text" class="form-control" id="kode_lab" name="kode_lab" required>
                            <span class="text-danger" id="kodeLabError"></span>
                        </div>
                        <div class="form-group mb-3">
                            <label for="lokasi">Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                            <span class="text-danger" id="lokasiError"></span>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="bisa_dipinjam_ruangan" name="bisa_dipinjam_ruangan" value="1" checked>
                            <label class="form-check-label fw-bold" for="bisa_dipinjam_ruangan">Bisa Dipinjam sebagai Ruangan Mahasiswa</label>
                            <small class="text-muted d-block">Centang jika ini Laboratorium praktikum. Hilangkan centang jika ini Ruang Teknisi / Gudang Alat agar tidak muncul di form peminjaman ruangan.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#labTable').DataTable({
                processing: true,
                serverSide: true,
                pagingType: 'simple',
                ajax: '{{ route('lab.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_lab',
                        name: 'nama_lab',
                        class: 'text-nowrap'
                    },
                    {
                        data: 'kode_lab',
                        name: 'kode_lab',
                        class: 'text-nowrap'
                    },
                    {
                        data: 'lokasi',
                        name: 'lokasi',
                        class: 'text-nowrap'
                    },
                    {
                        data: 'bisa_dipinjam_ruangan',
                        name: 'bisa_dipinjam_ruangan',
                        render: function(data) {
                            return data !== false && data != 0
                                ? '<span class="badge bg-success">Ya (Lab)</span>'
                                : '<span class="badge bg-secondary">Tidak (Ruang Teknisi/Gudang)</span>';
                        }
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at',
                        class: 'text-nowrap'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#showAddLabModal').click(function() {
                resetForm();
                $('#modalTitle').text('Tambah Data');
                $('#bisa_dipinjam_ruangan').prop('checked', true);
                $('#addLabModal').modal('show');
            });

            function resetForm() {
                $('#addLabForm')[0].reset();
                $('#lab_id').val('');
                $('#bisa_dipinjam_ruangan').prop('checked', true);
                $('#namaLabError').text('');
                $('#kodeLabError').text('');
                $('#lokasiError').text('');
            }

            $('#addLabForm').on('submit', function(e) {
                e.preventDefault();

                let id = $('#lab_id').val();
                let url = id ? "/labs/" + id : "{{ route('lab.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#addLabModal').modal('hide');
                        $('#labTable').DataTable().ajax.reload();

                        Toastify({
                            text: response.message,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#4fbe87",
                        }).showToast();
                    },
                    error: function(response) {
                        if (response.status === 400) {
                            Toastify({
                                text: response.responseJSON.message,
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#ff6b6b",
                            }).showToast();
                        }
                        $('#namaLabError').text(response.responseJSON.errors?.nama_lab ?? '');
                        $('#kodeLabError').text(response.responseJSON.errors?.kode_lab ?? '');
                        $('#lokasiError').text(response.responseJSON.errors?.lokasi ?? '');
                    }
                });
            });

            $(document).on('click', '#editData', function() {
                let id = $(this).data('id');
                resetForm();

                $.ajax({
                    url: "{{ url('labs') }}/" + id + "/edit",
                    type: "GET",
                    success: function(data) {
                        $('#modalTitle').text('Edit Data');

                        $('#lab_id').val(data.id);
                        $('#nama_lab').val(data.nama_lab);
                        $('#kode_lab').val(data.kode_lab);
                        $('#lokasi').val(data.lokasi);
                        $('#bisa_dipinjam_ruangan').prop('checked', data.bisa_dipinjam_ruangan !== false && data.bisa_dipinjam_ruangan != 0);

                        $('#addLabModal').modal('show');
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });

            $('#labTable').on('click', '#deleteData', function() {
                var data = $('#labTable').DataTable().row($(this).parents('tr')).data();
                Swal.fire({
                    icon: 'warning',
                    title: "Apakah Anda yakin ingin menghapus data ini?",
                    showDenyButton: true,
                    confirmButtonText: "Hapus",
                    denyButtonText: `Batal`
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/labs/" + data.id,
                            type: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                $('#labTable').DataTable().ajax.reload();
                                Toastify({
                                    text: response.message,
                                    duration: 3000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#4fbe87",
                                }).showToast();
                            },
                            error: function(response) {
                                Toastify({
                                    text: response.responseJSON.message,
                                    duration: 3000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#ff6b6b",
                                }).showToast();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
