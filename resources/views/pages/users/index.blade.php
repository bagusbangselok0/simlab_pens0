@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">
                    Pengguna
                </h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle"></i> Tambah Pengguna
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 d-flex justify-content-end align-items-center gap-2">
                        <label for="filter_users_with_role" class="form-label mb-0 fw-mediumbold">Role</label>
                        <select class="form-select w-auto" id="filter_users_with_role">
                            <option value="">Semua Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->nama_role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="table2">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>NIP</th>
                                <th>NRP</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Last Login</th>
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

    <!-- Modal Generate Reset Link -->
    <div class="modal fade" id="resetLinkModal" tabindex="-1" aria-labelledby="resetLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetLinkModalLabel">Generate Reset Password Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Tautan reset password untuk <br> <strong id="resetTargetEmail"></strong>:</p>
                    <div class="input-group mb-3">
                        <input type="text" id="generatedResetUrl" class="form-control" readonly>
                        <button class="btn btn-primary" type="button" id="copyResetUrl">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>
                    <div id="copySuccess" class="text-success d-none mb-3 small">Tautan berhasil disalin!</div>

                    <a href="" id="sendToWaBtn" target="_blank" class="btn btn-success w-100">
                        <i class="bi bi-whatsapp"></i> Kirim Langsung ke WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('pages.users.modals.add')
    @include('pages.users.modals.edit')
@endsection

@section('scripts')
    <script>
        let customized_datatable = $("#table2").DataTable({
            responsive: true,
            pagingType: 'full_numbers',
            dom: "<'row'<'col-3'l><'col-9'f>>" +
                "<'row dt-row'<'col-sm-12'tr>>" +
                "<'row'<'col-4'i><'col-8'p>>",
            "language": {
                "info": "Page _PAGE_ of _PAGES_",
                "lengthMenu": "_MENU_ ",
                "search": "",
                "searchPlaceholder": "Search.."
            },
            processing: true,
            ordering: true,
            order: [],
            serverSide: true,
            autoWidth: true,
            scrollX: true,
            ajax: {
                url: "{{ route('users.index') }}",
                type: 'GET',
                data: function(d) {
                    d.role_id = $('#filter_users_with_role').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nama_asli',
                    name: 'nama_asli'
                },
                {
                    data: 'roles',
                    name: 'roles',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nip',
                    name: 'nip'
                },
                {
                    data: 'nrp',
                    name: 'nrp'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'last_login_at',
                    name: 'last_login_at',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'updated_at',
                    name: 'updated_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $('#filter_users_with_role').on('change', function() {
            $('#table2').DataTable().ajax.reload();
        });

        const setTableColor = () => {
            document.querySelectorAll('.dataTables_paginate .pagination').forEach(dt => {
                dt.classList.add('pagination-primary')
            })
        }
        setTableColor()
        customized_datatable.on('draw', setTableColor)

        // Handle Verification
        $('body').on('click', '.verifyUserBtn', function() {
            let id = $(this).data('id');
            let btn = $(this);

            Swal.fire({
                title: 'Apakah Anda yakin ingin memverifikasi user ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak',
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm"></span>');

                    $.ajax({
                        url: `/users/${id}/verify`,
                        type: 'PATCH',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Toastify({
                                text: response.message,
                                duration: 3000,
                                close: true,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                            }).showToast();
                            customized_datatable.ajax.reload();
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(
                                '<i class="bi bi-check-circle-fill"></i>');
                            alert('Gagal memverifikasi user: ' + (xhr.responseJSON.message ||
                                'Terjadi kesalahan sistem.'));
                        }
                    });
                }
            });
        });

        // Handle Generate Reset Link
        $('body').on('click', '.generateResetBtn', function() {
            let id = $(this).data('id');
            let email = $(this).data('email');
            let btn = $(this);

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: `/users/${id}/generate-reset-link`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-key-fill"></i>');

                    $('#resetTargetEmail').text(response.email);
                    $('#generatedResetUrl').val(response.reset_url);
                    $('#copySuccess').addClass('d-none');

                    // Setup WA Link (optional but helpful)
                    let waText = encodeURIComponent(
                        `Halo, berikut adalah tautan untuk mereset password akun SIMLAB Anda: ${response.reset_url}\n\nTautan ini hanya dapat digunakan satu kali.`
                    );
                    $('#sendToWaBtn').attr('href', `https://wa.me/?text=${waText}`);

                    $('#resetLinkModal').modal('show');
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-key-fill"></i>');
                    alert('Gagal membuat tautan reset password.');
                }
            });
        });

        // Copy to clipboard
        $('#copyResetUrl').on('click', function() {
            let copyText = document.getElementById("generatedResetUrl");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            $('#copySuccess').removeClass('d-none');
        });

        // Toggle Fields based on Role (Add Modal)
        $('#role_id').on('change', function() {
            let slug = $(this).find(':selected').data('slug');
            $('.field-group').hide();

            if (slug === 'mahasiswa') {
                $('.nrp-field, .prodi-field').show();
            } else if (slug === 'dosen' || slug === 'plp') {
                $('.nip-field, .gelar-field, .prodi-field, .jabatan-field').show();
            } else if (slug === 'satpam') {
                $('.nip-field, .jabatan-field').show();
            }
        });

        // Reset Add Modal on Show
        $('#addUserModal').on('show.bs.modal', function() {
            $('#addUserForm')[0].reset();
            $('.field-group').hide();
            $('.error-text').text('');
            $('#addUserForm input, #addUserForm select').removeClass('is-invalid');
        });

        // Handle Add User Submit
        $('#addUserForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#saveUserBtn');

            $('.error-text').text('');
            $('#addUserForm input, #addUserForm select').removeClass('is-invalid');
            btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

            $.ajax({
                url: "{{ route('users.store') }}",
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#addUserModal').modal('hide');
                    btn.prop('disabled', false).html(
                        '<i class="bx bx-check d-block d-sm-none"></i><span class="d-none d-sm-block">Simpan Pengguna</span>'
                    );
                    $('#addUserForm')[0].reset();
                    $('.field-group').hide();

                    Toastify({
                        text: response.message,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                    }).showToast();

                    customized_datatable.ajax.reload();
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(
                        '<i class="bx bx-check d-block d-sm-none"></i><span class="d-none d-sm-block">Simpan Pengguna</span>'
                    );
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(prefix, val) {
                            $('#addUserForm .' + prefix + '_error').text(val[0]);
                            $('#addUserForm #' + prefix).addClass('is-invalid');
                        });
                    } else {
                        alert('Terjadi kesalahan sistem: ' + (xhr.responseJSON?.message ||
                            'Gagal menyimpan user.'));
                    }
                }
            });
        });

        // Toggle Fields based on Role (Edit Modal)
        $('#edit_role_id').on('change', function() {
            let slug = $(this).find(':selected').data('slug');
            $('.edit-field-group').hide();

            if (slug === 'mahasiswa') {
                $('.edit-nrp-field, .edit-prodi-field').show();
            } else if (slug === 'dosen' || slug === 'plp') {
                $('.edit-nip-field, .edit-gelar-field, .edit-prodi-field, .edit-jabatan-field').show();
            } else if (slug === 'satpam') {
                $('.edit-nip-field, .edit-jabatan-field').show();
            }
        });

        // Handle Edit Button Click
        $('body').on('click', '#editData', function() {
            let id = $(this).data('id');
            $('.error-text').text('');
            $('input, select').removeClass('is-invalid');

            $.get(`/users/${id}/edit`, function(response) {
                if (response.success) {
                    let user = response.data;
                    $('#edit_id').val(user.id);
                    $('#edit_role_id').val(user.role_id).trigger('change');
                    $('#edit_nama_asli').val(user.nama_asli);
                    $('#edit_email').val(user.email);
                    $('#edit_nip').val(user.nip);
                    $('#edit_nrp').val(user.nrp);
                    $('#edit_gelar_depan').val(user.gelar_depan);
                    $('#edit_gelar_belakang').val(user.gelar_belakang);
                    $('#edit_prodi_id').val(user.prodi_id);
                    $('#edit_jabatan_id').val(user.jabatan_id);

                    $('#editUserModal').modal('show');
                }
            });
        });

        // Handle Update User Submit
        $('#editUserForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let id = $('#edit_id').val();
            let btn = $('#updateUserBtn');

            $('.error-text').text('');
            btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm"></span> Memperbarui...');

            $.ajax({
                url: `/users/${id}`,
                type: 'PUT',
                data: form.serialize(),
                success: function(response) {
                    $('#editUserModal').modal('hide');
                    btn.prop('disabled', false).html('Perbarui Pengguna');

                    Toastify({
                        text: response.message,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                    }).showToast();

                    customized_datatable.ajax.reload();
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('Perbarui Pengguna');
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(prefix, val) {
                            $('.edit_' + prefix + '_error').text(val[0]);
                            $('#edit_' + prefix).addClass('is-invalid');
                        });
                    } else {
                        alert('Terjadi kesalahan sistem.');
                    }
                }
            });
        });

        // Handle Delete User
        $('body').on('click', '#deleteData', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data pengguna akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/users/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Toastify({
                                text: response.message,
                                duration: 3000,
                                close: true,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                            }).showToast();
                            customized_datatable.ajax.reload();
                        },
                        error: function() {
                            alert('Gagal menghapus user.');
                        }
                    });
                }
            });
        });
    </script>
@endsection
