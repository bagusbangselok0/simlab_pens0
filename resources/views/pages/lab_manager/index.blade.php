@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4>Daftar Laboratorium</h4>
                <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" id="showAddLabManagerModal"
                        data-bs-target="#addLabManagerModal"><i class="fa fa-plus"></i> Tambah Penanggung Jawab</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="labManagerTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Lab</th>
                                <th>Lokasi</th>
                                <th>PLP</th>
                                <th>Kalab</th>
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

    @include('pages.lab_manager.modals.add')
    @include('pages.lab_manager.modals.detailAndEdit')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#labManagerTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('lab_manager.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_lab',
                        name: 'nama_lab'
                    },
                    {
                        data: 'lokasi',
                        name: 'lokasi'
                    },
                    {
                        data: 'plp',
                        name: 'plp',
                        defaultContent: '-'
                    },
                    {
                        data: 'kalab',
                        name: 'kalab',
                        defaultContent: '-'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#addLabManagerForm').submit(function(e) {
                e.preventDefault();
                $('#labError').text('');
                $('#plpError').text('');
                $('#kalabError').text('');
                var formData = $(this).serialize();

                $.ajax({
                    url: '{{ route('lab_manager.store') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    success: function(response) {
                        $('#addLabManagerModal').modal('hide');
                        $('#labManagerTable').DataTable().ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('lab_id').val(0);
                        $('plp_id').val(0);
                        $('kalab_id').val(0);
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        if (errors) {
                            if (errors.lab_id) {
                                $('#labError').text(errors.lab_id[0]);
                            }
                            if (errors.plp_id) {
                                $('#plpError').text(errors.plp_id[0]);
                            }
                            if (errors.kalab_id) {
                                $('#kalabError').text(errors.kalab_id[0]);
                            }
                        } else if (xhr.responseJSON.message) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan yang tidak diketahui.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    }
                });
            });

            // Handle show detail modal
            $(document).on('click', '.show-detail-modal', function() {
                var labManagerId = $(this).data('id');
                $.ajax({
                    url: '{{ route('lab_manager.edit', ':id') }}'.replace(':id', labManagerId),
                    method: 'GET',
                    success: function(response) {
                        $('#lab_manager_id').val(response.id);
                        $('#edit_lab_id').val(response.lab_id);
                        $('#edit_plp_id').val(response.plp_id);
                        $('#edit_kalab_id').val(response.kalab_id);
                        // Reset to detail mode
                        $('#edit_lab_id, #edit_plp_id, #edit_kalab_id').prop('disabled', true);
                        $('#updateBtn').addClass('d-none');
                        $('#editBtn').show();
                        $('#cancelEditBtn').hide();
                        $('#detailLabError, #plpError, #kalabError').text('');
                        $('#detailAndEditLabManagerModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal mengambil data penanggung jawab laboratorium.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            });

            // Handle edit button click
            $('#editBtn').click(function() {
                $('#edit_lab_id, #edit_plp_id, #edit_kalab_id').prop('disabled', false);
                $('#updateBtn').removeClass('d-none');
                $('#editBtn').hide();
                $('#cancelEditBtn').show();
            });

            // Handle cancel edit button click
            $('#cancelEditBtn').click(function() {
                $('#edit_lab_id, #edit_plp_id, #edit_kalab_id').prop('disabled', true);
                $('#updateBtn').addClass('d-none');
                $('#editBtn').show();
                $('#cancelEditBtn').hide();
                // Reset form errors
                $('#detailLabError, #plpError, #kalabError').text('');
            });

            // Reset modal to detail mode when closed
            $('#detailAndEditLabManagerModal').on('hidden.bs.modal', function() {
                $('#edit_lab_id, #edit_plp_id, #edit_kalab_id').prop('disabled', true);
                $('#updateBtn').addClass('d-none');
                $('#editBtn').show();
                $('#cancelEditBtn').hide();
                $('#detailLabError, #plpError, #kalabError').text('');
            });

            // Ensure modal starts in detail mode
            $('#detailAndEditLabManagerModal').on('show.bs.modal', function() {
                $('#edit_lab_id, #edit_plp_id, #edit_kalab_id').prop('disabled', true);
                $('#updateBtn').addClass('d-none');
                $('#editBtn').show();
                $('#cancelEditBtn').hide();
            });

            $('#detailAndEditLabManagerForm').submit(function(e) {
                e.preventDefault();
                $('#detailLabError').text('');
                $('#plpError').text('');
                $('#kalabError').text('');
                var labManagerId = $('#lab_manager_id').val();
                var formData = $(this).serialize();

                $.ajax({
                    url: '{{ route('lab_manager.update', ':id') }}'.replace(':id', labManagerId),
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    success: function(response) {
                        $('#detailAndEditLabManagerModal').modal('hide');
                        $('#labManagerTable').DataTable().ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        if (errors) {
                            if (errors.lab_id) {
                                $('#detailLabError').text(errors.lab_id[0]);
                            }
                            if (errors.plp_id) {
                                $('#plpError').text(errors.plp_id[0]);
                            }
                            if (errors.kalab_id) {
                                $('#kalabError').text(errors.kalab_id[0]);
                            }
                        } else if (xhr.responseJSON.message) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan yang tidak diketahui.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    }
                });
            });

            // Handle delete button click
            $(document).on('click', '.delete-lab-manager', function() {
                var labManagerId = $(this).data('id');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data penanggung jawab laboratorium akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('lab_manager.destroy', ':id') }}'.replace(':id',
                                labManagerId),
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                $('#labManagerTable').DataTable().ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Gagal menghapus data penanggung jawab laboratorium.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
