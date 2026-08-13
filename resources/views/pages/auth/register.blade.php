<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SIMLAB PENS Sumenep</title>
    <link rel="shortcut icon" href="{{ asset('images/logo/SIMLAB_logo1.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/logo/SIMLAB_logo1.png') }}" type="image/png">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/app.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/auth.css') }}">
</head>

<body>
    <div id="auth">

        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo mb-1">
                        <a href="void(0)"><img src="{{ asset('images/logo/SIMLAB_logo.png') }}" alt="Logo"
                                style="width: 200px; height: 100px; object-fit: contain;"></a>
                    </div>
                    <h1 class="auth-title">Sign Up.</h1>
                    <p class="auth-subtitle mb-3">Input data Anda untuk mendaftar ke sistem kami.</p>

                    <div class="alert alert-info alert-dismissible fade show p-3 mb-4 shadow-sm border-0 border-start border-4 border-primary"
                        style="background-color: rgba(67, 94, 190, 0.07);" role="alert">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-info-circle-fill text-primary fs-3 lh-1 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1 fw-bold text-primary">Informasi Pendaftaran</h6>
                                <p class="mb-1 small text-body">
                                    Pendaftaran mandiri saat ini hanya tersedia untuk <strong
                                        class="text-primary">Mahasiswa</strong>.
                                </p>
                                <p class="mb-2 small text-body">
                                    Bagi <strong>Dosen</strong>, <strong>PLP</strong>, atau <strong>Staf /
                                        Satpam</strong>, silakan hubungi <strong>Admin</strong> untuk pembuatan akun
                                    Anda.
                                </p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    </div>

                    <form id="registerForm" action="{{ route('register.post') }}" method="POST">
                        @csrf
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-xl" placeholder="Nama Lengkap"
                                name="nama_asli" id="nama_asli" value="{{ old('nama_asli') }}">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="invalid-feedback nama_asli-error"></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-xl" placeholder="NRP" name="nrp"
                                id="nrp" value="{{ old('nrp') }}">
                            <div class="form-control-icon">
                                <i class="bi bi-hash"></i>
                            </div>
                            <div class="invalid-feedback nrp-error"></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="email" class="form-control form-control-xl" placeholder="Email"
                                name="email" id="email" value="{{ old('email') }}">
                            <div class="form-control-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="invalid-feedback email-error"></div>
                        </div>

                        <div class="form-group position-relative mb-4">
                            <select name="prodi_id" id="prodi_id" class="form-control form-control-xl">
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback prodi_id-error"></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-xl" placeholder="Password"
                                name="password" id="password">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div class="invalid-feedback password-error"></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-xl"
                                placeholder="Konfirmasi Password" name="password_confirmation"
                                id="password_confirmation">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg mt-5"
                            id="registerBtn">
                            Daftar
                        </button>
                    </form>

                    <div id="generalError" class="alert alert-danger mt-3 d-none">
                        <ul class="mb-0" id="errorList"></ul>
                    </div>

                    <div class="text-center mt-5 text-lg fs-4">
                        <p class='text-gray-600'>Sudah punya akun? <a href="{{ route('login') }}"
                                class="font-bold">Masuk</a>.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">
                    <img src="{{ asset('images/illustrations/login0.png') }}" alt="Register Image"
                        class="img-fluid fill-cover" style="height: 100%; object-fit: cover;">
                </div>
            </div>
        </div>

    </div>

    <script src="{{ asset('mazer/assets/static/js/initTheme.js') }}"></script>
    <script src="{{ asset('bootstrap/js/jquery-3.7.1.js') }}"></script>
    <script src="{{ asset('mazer/compiled/js/app.js') }}"></script>
    <script src="{{ asset('mazer/extensions/toastify-js/src/toastify.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();

                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').empty().hide();
                $('#generalError').addClass('d-none');
                $('#errorList').empty();

                $('#registerBtn').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Registering...'
                );

                $.ajax({
                    url: '{{ route('register.post') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        Toastify({
                            text: response.message,
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                        }).showToast();

                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 2000);
                    },
                    error: function(xhr) {
                        $('#registerBtn').prop('disabled', false).html('Sign Up');

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                let $field = $('#' + field);
                                let $errorDiv = $('.' + field + '-error');
                                $field.addClass('is-invalid');
                                $errorDiv.text(messages[0]).show();
                            });
                        } else {
                            $('#errorList').append('<li>' + (xhr.responseJSON.message ||
                                'Terjadi kesalahan sistem.') + '</li>');
                            $('#generalError').removeClass('d-none');
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
