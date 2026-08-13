<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('pages.auth.login');
    }
    // app/Http/Controllers/LoginController.php

    public function authenticate(Request $request)
    {
        $messages = [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password harus diisi',
        ];

        try {
            // Validasi input
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ], $messages);

            // Coba login
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $user = Auth::user();

                // Cek apakah akun aktif
                if (!$user->is_active) {
                    Auth::logout();

                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Akun Anda nonaktif.',
                        ], 422);
                    }

                    return back()->withErrors([
                        'email' => 'Akun Anda nonaktif.',
                    ])->onlyInput('email');
                }

                // Cek apakah akun diverifikasi
                if (!$user->is_verified) {
                    Auth::logout();

                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Akun Anda belum diverifikasi oleh Admin. Hubungi admin untuk verfikasi.',
                        ], 422);
                    }

                    return back()->withErrors([
                        'email' => 'Akun Anda belum diverifikasi oleh Admin. Hubungi admin untuk verfikasi.',
                    ])->onlyInput('email');
                }

                // Catat last login
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                    'last_login_platform' => 'web',
                ]);

                $request->session()->regenerate();

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Login berhasil!',
                        'redirect' => '/dashboard'
                    ]);
                }

                // Redirect ke dashboard
                return redirect()->intended('/dashboard');
            }

            // Jika login gagal
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'email' => ['Email atau password yang Anda masukkan salah.']
                    ]
                ], 422);
            }

            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])->onlyInput('email');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangani validation errors
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput($request->only('email'));
        }
    }

    private function redirectBasedOnRole(string $roleSlug): RedirectResponse
    {
        return match ($roleSlug) {
            'admin'     => redirect()->intended('/admin/dashboard'),
            'plp'       => redirect()->intended('/plp/dashboard'),
            'dosen'     => redirect()->intended('/dosen/dashboard'),
            'satpam'    => redirect()->intended('/satpam/dashboard'),
            'mahasiswa' => redirect()->intended('/peminjaman'),
            default     => redirect()->intended('/'),
        };
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    }
}
