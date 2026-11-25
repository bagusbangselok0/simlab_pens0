@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">
                    Daftar Jabatan
                </h5>
                <button type="button" class="btn btn-primary" id="showAddJabatanModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Jabatan
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="jabatanTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Jabatan</th>
                                <th>Slug</th>
                                <th>Jumlah User</th>
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

    <!-- Modal Tambah / Edit Jabatan -->
    <div class="modal fade" id="jabatanModal" tabindex="-1" aria-labelledby="jabatanModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jabatanModalTitle">Tambah Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="jabatanForm">
                    <div class="modal-body">
                        <input type="hidden" id="jabatan_id" name="id">

                        <div class="mb-3">
                            <label for="nama_jabatan" class="form-label">Nama Jabatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan"
                                placeholder="Contoh: Kepala Laboratorium" required>
                            <small class="text-danger" id="namaJabatanError"></small>
                        </div>

                        <div class="mb-2">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug"
                                placeholder="Otomatis dari Nama Jabatan bila dikosongkan">
                            <small class="text-danger d-block" id="slugError"></small>
                            <small class="text-muted">
                                Catatan: Slug digunakan internal sistem untuk identifikasi hak akses jabatan tertentu
                                (seperti <code>kalab</code>, <code>plp</code>, <code>satpam</code>).
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveJabatan">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const baseUrl = @json(url('jabatans'));
            const storeUrl = @json(route('jabatan.store'));
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            let slugManuallyEdited = false;

            function toSlug(text) {
                return String(text || '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            function clearErrors() {
                $('#namaJabatanError').text('');
                $('#slugError').text('');
                $('#nama_jabatan').removeClass('is-invalid');
                $('#slug').removeClass('is-invalid');
            }

            function resetForm() {
                $('#jabatanForm')[0].reset();
                $('#jabatan_id').val('');
                slugManuallyEdited = false;
                clearErrors();
            }

            function showToast(message, isError = false) {
                Toastify({
                    text: message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: isError ? "#ff6b6b" : "#4fbe87",
                }).showToast();
            }

            const table = $('#jabatanTable').DataTable({
                processing: true,
                serverSide: true,
                pagingType: 'simple',
                ajax: @json(route('jabatan.index')),
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_jabatan',
                        name: 'nama_jabatan',
                        class: 'text-nowrap'
                    },
                    {
                        data: 'slug',
                        name: 'slug',
                        class: 'text-nowrap'
                    },
                    {
                        data: 'users_count',
                        name: 'users_count',
                        searchable: false,
                        class: 'text-nowrap'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at',
                        searchable: false,
                        class: 'text-nowrap'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-nowrap'
                    }
                ]
            });

            $('#showAddJabatanModal').on('click', function() {
                resetForm();
                $('#jabatanModalTitle').text('Tambah Jabatan');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('jabatanModal')).show();
            });

            $('#nama_jabatan').on('input', function() {
                if (!slugManuallyEdited) {
                    $('#slug').val(toSlug($(this).val()));
                }
            });

            $('#slug').on('input', function() {
                slugManuallyEdited = $(this).val().trim() !== '';
            });

            $('#jabatanForm').on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const id = $('#jabatan_id').val();
                const url = id ? (baseUrl + '/' + id) : storeUrl;
                const method = id ? 'PUT' : 'POST';
                const $btn = $('#btnSaveJabatan').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: $(this).serialize(),
                    success: function(response) {
                        bootstrap.Modal.getOrCreateInstance(document.getElementById(
                            'jabatanModal')).hide();
                        table.ajax.reload(null, false);
                        showToast(response.message || 'Data jabatan berhasil disimpan.');
                    },
                    error: function(xhr) {
                        const json = xhr.responseJSON || {};
                        const errors = json.errors || {};

                        if (errors.nama_jabatan) {
                            $('#nama_jabatan').addClass('is-invalid');
                            $('#namaJabatanError').text(Array.isArray(errors.nama_jabatan) ?
                                errors.nama_jabatan[0] : errors.nama_jabatan);
                        }
                        if (errors.slug) {
                            $('#slug').addClass('is-invalid');
                            $('#slugError').text(Array.isArray(errors.slug) ? errors.slug[0] :
                                errors.slug);
                        }

                        if (!errors.nama_jabatan && !errors.slug) {
                            showToast(json.message ||
                                'Terjadi kesalahan saat menyimpan data jabatan.', true);
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.btn-edit-jabatan', function() {
                const id = $(this).data('id');
                resetForm();

                $.get(baseUrl + '/' + id + '/edit')
                    .done(function(data) {
                        $('#jabatanModalTitle').text('Edit Jabatan');
                        $('#jabatan_id').val(data.id);
                        $('#nama_jabatan').val(data.nama_jabatan);
                        $('#slug').val(data.slug);
                        slugManuallyEdited = true;
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('jabatanModal'))
                            .show();
                    })
                    .fail(function(xhr) {
                        showToast(xhr.responseJSON?.message || 'Gagal memuat data jabatan.', true);
                    });
            });

            $(document).on('click', '.btn-delete-jabatan', function() {
                const id = $(this).data('id');
                const nama = $(this).data('nama') || 'jabatan ini';

                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus Jabatan?',
                    text: 'Apakah Anda yakin ingin menghapus jabatan "' + nama + '"?',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: baseUrl + '/' + id,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            success: function(response) {
                                table.ajax.reload(null, false);
                                showToast(response.message ||
                                    'Data jabatan berhasil dihapus.');
                            },
                            error: function(xhr) {
                                showToast(xhr.responseJSON?.message ||
                                    'Gagal menghapus data jabatan.', true);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
