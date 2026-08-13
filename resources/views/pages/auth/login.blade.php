<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMLAB PENS Sumenep</title>
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
                    <h1 class="auth-title">Log in.</h1>
                    <p class="auth-subtitle mb-4">Masuk dengan data yang Anda daftarkan.</p>

                    @if (session('registered_success') || session('success') || request('registered') == '1')
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-3 p-3 mb-4 shadow-sm"
                            role="alert" style="border-left: 5px solid #198754;">
                            <i class="bi bi-check-circle-fill text-success fs-3 mt-1"></i>
                            <div>
                                <h6 class="alert-heading mb-1 text-success fw-bold">Pendaftaran Akun Berhasil!</h6>
                                <p class="mb-2 small">
                                    Akun Anda telah berhasil dibuat. Silakan hubungi <strong>Admin</strong> untuk
                                    melakukan <strong>verifikasi data akun</strong> agar Anda dapat login.
                                </p>
                                <a href="https://wa.me/6281934975754?text=Halo%20Admin%20Simlab,%20saya%20baru%20saja%20mendaftar%20akun.%20Mohon%20bantuannya%20untuk%20verifikasi%20akun%20saya."
                                    target="_blank" class="btn btn-sm btn-success text-white fw-bold">
                                    <i class="bi bi-whatsapp me-1"></i> Hubungi Admin via WhatsApp
                                </a>
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    <form id="loginForm" action="{{ route('login.post') }}" method="POST">
                        @csrf
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-xl" placeholder="email"
                                name="email" id="email" value="{{ old('email') }}">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="invalid-feedback email-error"></div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-xl" placeholder="password"
                                name="password" id="password">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div class="invalid-feedback password-error"></div>
                        </div>
                        <div class="form-check form-check-lg d-flex align-items-end">
                            <input class="form-check-input me-2" type="checkbox" name="remember" value="1"
                                id="flexCheckDefault" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-gray-600" for="flexCheckDefault">
                                Ingat saya
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg mt-5" id="loginBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            Log in
                        </button>
                    </form>

                    <div id="generalError" class="alert alert-danger mt-3 d-none">
                        <ul class="mb-0" id="errorList"></ul>
                    </div>
                    <div class="text-center mt-5 text-lg fs-4">
                        <p class="text-gray-600">Belum punya akun? <a href="{{ route('register') }}"
                                class="font-bold">Daftar (Mahasiswa)</a>.</p>
                        <p><a class="font-bold" href="javascript:void(0)" data-bs-toggle="modal"
                                data-bs-target="#forgotPasswordModal">Lupa password?</a>. | <a
                                href="https://wa.me/6281934975754" target="_blank" class="font-bold">Hubungi
                                admin</a>.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">
                    <!-- Gambar atau konten tambahan untuk sisi kanan halaman login bisa ditempatkan di sini -->
                    <img src="{{ asset('images/illustrations/login0.png') }}" alt="Login Image"
                        class="img-fluid fill-cover" style="height: 100%; object-fit: cover;">
                </div>
            </div>
        </div>

    </div>
    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Lupa Password?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <i class="bi bi-chat-left-dots-fill text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <p class="fs-5">Silakan hubungi Admin untuk melakukan reset password akun Anda.</p>
                    <p class="text-muted">Admin akan memberikan tautan khusus untuk mengatur ulang password Anda
                        melalui
                        WhatsApp.</p>

                    <a href="https://wa.me/6281934975754?text=Halo%20Admin%20Simlab,%20saya%20lupa%20password%20akun%20saya.%20Mohon%20bantuannya%20untuk%20reset%20password."
                        target="_blank" class="btn btn-success btn-lg mt-3">
                        <i class="bi bi-whatsapp me-2"></i> Hubungi Admin via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('mazer/assets/static/js/initTheme.js') }}"></script>
    <script src="{{ asset('bootstrap/js/jquery-3.7.1.js') }}"></script>
    <script src="{{ asset('mazer/compiled/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Handle form submission with AJAX
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').empty().hide();
                $('#generalError').addClass('d-none');
                $('#errorList').empty();

                // Show loading spinner
                $('#loginBtn').prop('disabled', true);
                $('#loginBtn .spinner-border').removeClass('d-none');
                $('#loginBtn').html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Logging in...'
                );

                $.ajax({
                    url: '{{ route('login.post') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        email: $('#email').val(),
                        password: $('#password').val(),
                        remember: $('#flexCheckDefault').is(':checked') ? 1 : 0
                    },
                    success: function(response) {
                        // Redirect on success
                        window.location.href = response.redirect || '/dashboard';
                    },
                    error: function(xhr) {
                        // Hide loading spinner
                        $('#loginBtn').prop('disabled', false);
                        $('#loginBtn').html('Log in');

                        let response = xhr.responseJSON;
                        if (response && response.errors) {
                            $.each(response.errors, function(field, messages) {
                                let $field = $('#' + field);
                                let $errorDiv = $('.' + field + '-error');

                                $field.addClass('is-invalid');
                                $errorDiv.text(messages[0]).show();
                            });
                        } else if (response && response.message) {
                            $('#errorList').append('<li>' + response.message + '</li>');
                            $('#generalError').removeClass('d-none');
                        } else {
                            $('#errorList').append(
                                '<li>Terjadi kesalahan saat login. Silakan coba lagi.</li>');
                            $('#generalError').removeClass('d-none');
                        }
                    }
                });
            });
        });

        $(window).on('load', function() {
            $('body').addClass('loaded');
        });
    </script>
</body>

</html>
