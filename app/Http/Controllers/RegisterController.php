<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Menampilkan form registrasi.
     */
    public function showRegistrationForm()
    {
        $prodis = Prodi::all();
        return view('pages.auth.register', compact('prodis'));
    }

    /**
     * Menangani proses pendaftaran user baru.
     */
    public function register(Request $request)
    {
        $messages = [
            'nama_asli.required' => 'Nama lengkap harus diisi.',
            'nrp.required'       => 'NRP harus diisi.',
            'nrp.unique'         => 'NRP sudah terdaftar.',
            'nrp.max'            => 'NRP maksimal 10 karakter.',
            'nrp.min'            => 'NRP minimal 10 karakter.',
            'email.required'     => 'Email harus diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar.',
            'prodi_id.required'  => 'Program studi harus dipilih.',
            'password.required'  => 'Password harus diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];

        $validator = Validator::make($request->all(), [
            'nama_asli' => 'required|string|max:255',
            'nrp'       => 'required|string|max:10|min:10|unique:users,nrp',
            'email'     => 'required|string|email|max:255|unique:users,email',
            'prodi_id'  => 'required|exists:prodi,id',
            'password'  => 'required|string|min:6|confirmed',
        ], $messages);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $user = User::create([
                'nama_asli'   => $request->nama_asli,
                'nrp'         => $request->nrp,
                'email'       => $request->email,
                'prodi_id'    => $request->prodi_id,
                'password'    => Hash::make($request->password),
                'role_id'     => 5, // Default Role: Mahasiswa
                'is_verified' => false, // Perlu validasi admin sesuai request USER
                'is_active'   => true,  // Akun aktif secara sistem, tapi verifikasi data menyusul
            ]);

            session()->flash('registered_success', 'Registrasi berhasil! Silakan hubungi Admin untuk verifikasi data Anda agar dapat login.');

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil! Silakan hubungi Admin untuk verifikasi data Anda agar dapat login.',
                    'redirect' => route('login', ['registered' => '1'])
                ]);
            }

            return redirect()->route('login', ['registered' => '1'])->with('registered_success', 'Registrasi berhasil! Silakan hubungi Admin untuk verifikasi data Anda agar dapat login.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
