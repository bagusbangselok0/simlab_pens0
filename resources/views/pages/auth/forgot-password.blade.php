<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SIMLAB PENS Sumenep</title>
    <link rel="shortcut icon" href="{{ asset('images/logo/SIMLAB_logo1.png') }}" type="image/x-icon">
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
                        <a href="{{ route('login') }}"><img src="{{ asset('images/logo/SIMLAB_logo.png') }}" alt="Logo"
                                style="width: 200px; height: 100px; object-fit: contain;"></a>
                    </div>
                    <h1 class="auth-title">Forgot Password</h1>
                    <p class="auth-subtitle mb-5">Input your email and we will send you reset password link.</p>

                    <form id="forgotPasswordForm" action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="email" class="form-control form-control-xl @error('email') is-invalid @enderror" 
                                placeholder="Email" name="email" id="email" value="{{ old('email') }}" required>
                            <div class="form-control-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="invalid-feedback email-error">
                                @error('email') {{ $message }} @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg mt-5" id="submitBtn">
                            Send Reset Link
                        </button>
                    </form>

                    <div id="statusMessage" class="alert alert-success mt-3 d-none"></div>
                    <div id="generalError" class="alert alert-danger mt-3 d-none"></div>

                    <div class="text-center mt-5 text-lg fs-4">
                        <p class='text-gray-600'>Remember your account? <a href="{{ route('login') }}" class="font-bold">Log in</a>.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">
                    <img src="{{ asset('images/illustrations/login0.png') }}" alt="Auth Image"
                        class="img-fluid fill-cover" style="height: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('mazer/assets/static/js/initTheme.js') }}"></script>
    <script src="{{ asset('bootstrap/js/jquery-3.7.1.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#forgotPasswordForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous messages
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').empty();
                $('#statusMessage').addClass('d-none').empty();
                $('#generalError').addClass('d-none').empty();

                // Show loading
                $('#submitBtn').prop('disabled', true).text('Sending...');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#submitBtn').prop('disabled', false).text('Send Reset Link');
                        if (response.success) {
                            $('#statusMessage').text(response.message).removeClass('d-none');
                            $('#forgotPasswordForm')[0].reset();
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false).text('Send Reset Link');
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                $('#' + field).addClass('is-invalid');
                                $('.' + field + '-error').text(messages[0]);
                            });
                        } else {
                            let message = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan. Silakan coba lagi.';
                            $('#generalError').text(message).removeClass('d-none');
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
