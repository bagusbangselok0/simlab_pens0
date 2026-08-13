@extends('layouts.app')

@section('styles')
@endsection

@section('content')
    {{-- Halaman Profil dengan tab berbeda (profil, ubah password, upload ttd (khusus jabatan plp dan kalab)) --}}
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Profil Saya</h4>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                            type="button" role="tab" aria-controls="profile" aria-selected="true">Profil</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password"
                            type="button" role="tab" aria-controls="password" aria-selected="false">Ubah
                            Password</button>
                    </li>
                    @if (($user->jabatan && in_array($user->jabatan->slug, ['plp', 'kalab'])) || $user->role->slug === 'mahasiswa')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ttd-tab" data-bs-toggle="tab" data-bs-target="#ttd"
                                type="button" role="tab" aria-controls="ttd" aria-selected="false">Upload TTD</button>
                        </li>
                    @endif
                </ul>
                <div class="tab-content mt-3" id="myTabContent">
                    <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        @include('pages.profile.partials.profile')
                    </div>
                    <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                        @include('pages.profile.partials.password')
                    </div>
                    @if (($user->jabatan && in_array($user->jabatan->slug, ['plp', 'kalab'])) || $user->role->slug === 'mahasiswa')
                        <div class="tab-pane fade" id="ttd" role="tabpanel" aria-labelledby="ttd-tab">
                            @include('pages.profile.partials.ttd')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Modal Edit Foto Profil --}}
    @include('pages.profile.partials.editPhotoModal')
@endsection

@section('scripts')
    <script src="{{ asset('mazer/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            let id = $(this).data('id');

            $('#photoModalBtn').on('click', function() {
                $('#editPhotoModal').modal('show');
            });

            // Handle form submit untuk update photo
            $('#editPhotoModal form').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = {{ Auth::id() }}; // Ambil ID user yang login

                $.ajax({
                    url: '{{ route('profile.update_photo', ':id') }}'.replace(':id', id),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Sukses', response.message, 'success');
                            $('#editPhotoModal').modal('hide');
                            location.reload(); // Reload untuk update foto di UI
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            if (errors.photo) {
                                Swal.fire('Error', errors.photo[0], 'error');
                            } else {
                                Swal.fire('Error', 'Data tidak valid', 'error');
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            Swal.fire('Error', xhr.responseJSON.message, 'error');
                        } else {
                            Swal.fire('Error', 'Terjadi kesalahan sistem internal atau ukuran file terlalu besar', 'error');
                            console.error(xhr.responseText);
                        }
                    }
                });
            });

            // Preview foto saat dipilih
            $('#photo').on('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photoPreview').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handle form update no_hp
            $('#formUpdateNoHp').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let $btn = $(this).find('button[type="submit"]');

                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...'
                );

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $btn.prop('disabled', false).html('Update');
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html('Update');
                        let errorMessage = 'Terjadi kesalahan saat memperbarui No Handphone';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.no_hp) {
                            errorMessage = xhr.responseJSON.errors.no_hp[0];
                        }
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            });

            // Preview foto saat dipilih
            $('#signature_path').on('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#signaturePreview').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handle form upload ttd
            $('#uploadTtdForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let $btn = $(this).find('button[type="submit"]');

                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...'
                );

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Sukses', response.message, 'success');
                            if (response.signature_url) {
                                $('#signaturePreview').attr('src', response.signature_url);
                            }
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            Swal.fire('Error', (errors.signature_path ? errors.signature_path[0] : 'File tidak valid'), 'error');
                        } else {
                            Swal.fire('Error', 'Terjadi kesalahan saat mengunggah tanda tangan',
                                'error');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('Upload');
                    }
                });
            });

            // Handle form ubah password
            $('form[action="{{ route('profile.update_password') }}"]').on('submit', function(e) {
                e.preventDefault();
                let $form = $(this);

                // Reset error messages
                $('.form-control').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();
                $form.find('.alert').remove();
                $form.next('.alert').remove();

                $.ajax({
                    url: '{{ route('profile.update_password') }}',
                    method: 'POST',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'current_password': $('#current_password').val(),
                        'new_password': $('#new_password').val(),
                        'new_password_confirmation': $('#new_password_confirmation').val()
                    },
                    success: function(response) {
                        Swal.fire('Sukses', 'Password berhasil diubah', 'success');
                        $form[0].reset();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function(key, messages) {
                                let $field = $('#' + key);
                                $field.addClass('is-invalid');
                                $field.after('<div class="invalid-feedback d-block">' +
                                    messages[0] + '</div>');
                            });
                            return;
                        }

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            Swal.fire('Error', xhr.responseJSON.message, 'error');
                        } else {
                            Swal.fire('Error', 'Terjadi kesalahan saat mengubah password',
                                'error');
                        }
                    }
                });
            });
        });
    </script>
@endsection
