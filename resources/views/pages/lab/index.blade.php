@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    Daftar Laboratorium
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
                            <label for="nama_lab">Nama Lab</label>
                            <input type="text" class="form-control" id="nama_lab" name="nama_lab">
                            <span class="text-danger" id="namaLabError"></span>
                        </div>
                        <div class="form-group">
                            <label for="kode_lab">Kode Lab</label>
                            <input type="text" class="form-control" id="kode_lab" name="kode_lab" required>
                            <span class="text-danger" id="kodeLabError"></span>
                        </div>
                        <div class="form-group">
                            <label for="lokasi">Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                            <span class="text-danger" id="lokasiError"></span>
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
                ajax: "{{ route('lab.index') }}",
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
                        data: 'updated_at',
                        name: 'updated_at',
                        class: 'text-nowrap'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-nowrap'
                    },
                ]
            });

            $('#showAddLabModal').click(function() {
                $('#modalTitle').text('Tambah Data');
                $('#addLabModal').modal('show');
            });

            function resetForm() {
                $('#addLabForm')[0].reset()
                $('#lab_id').val('')
            }

            $('#addLabForm').on('submit', function(e) {
                e.preventDefault();

                let id = $('#lab_id').val()

                let url = id ? "/labs/" + id : "{{ route('lab.store') }}"
                let method = id ? "PUT" : "POST"

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

                        $('#addLabForm')[0].reset();
                    },
                    error: function(response) {
                        Toastify({
                            text: response.responseJSON.message,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#ff6b6b",
                        }).showToast();
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
                        console.log(data);

                        $('#modalTitle').text('Edit Data');

                        $('#lab_id').val(data.id);
                        $('#nama_lab').val(data.nama_lab);
                        $('#kode_lab').val(data.kode_lab);
                        $('#lokasi').val(data.lokasi);

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
                        // Swal.fire("Saved!", "", "success");
                    }
                });
            });
        });
    </script>
@endsection
