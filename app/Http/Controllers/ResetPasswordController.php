<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordController extends Controller
{
    /**
     * Tampilkan form reset password.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('pages.auth.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Proses pembaruan password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        // Proses reset password menggunakan broker default Laravel
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            SecurityAudit::log('password_reset_success', [
                'target_email' => $request->email,
            ]);

            return redirect()->route('login')->with('status', 'Password Anda telah berhasil diubah! Silakan login kembali.');
        }

        SecurityAudit::log('password_reset_failed', [
            'target_email' => $request->email,
            'status' => $status,
        ], 'warning');

        return back()->withErrors(['email' => [__($status)]]);
    }
}
