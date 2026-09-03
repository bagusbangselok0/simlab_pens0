<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $title = 'Profil Saya';
        $user = User::where('id', Auth::id())->with('jabatan', 'role')->firstOrFail(); // Pastikan user ada, jika tidak akan 404

        return view('pages.profile.index', compact('title', 'user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Password saat ini harus diisi',
            'new_password.required' => 'Password baru harus diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user = User::where('id', Auth::id())->firstOrFail();

        // Pastikan current password tidak sama dengan new password
        if ($request->current_password === $request->new_password) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password baru tidak boleh sama dengan password saat ini'
                ], 422);
            }
            return back()->withErrors(['new_password' => 'Password baru tidak boleh sama dengan password saat ini']);
        }

        // Cek apakah current password benar
        if (!password_verify($request->current_password, $user->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password saat ini salah'
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Password saat ini salah']);
        }

        // // Cek apakah new password dengan confirm password cocok
        // if ($request->new_password !== $request->new_password_confirmation) {
        //     if ($request->ajax()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Konfirmasi password tidak cocok'
        //         ], 422);
        //     }
        //     return back()->withErrors(['new_password_confirmation' => 'Konfirmasi password tidak cocok']);
        // }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah'
            ], 200);
        }

        return back()->with('success', 'Password berhasil diubah');
    }

    public function updateNoHp(Request $request)
    {
        $request->validate([
            'no_hp' => 'nullable|string|max:15',
        ]);

        $user = User::findOrFail(Auth::id());
        $user->no_hp = $request->no_hp;
        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'No Handphone berhasil diperbarui',
            ]);
        }

        return back()->with('success', 'No Handphone berhasil diperbarui');
    }

    public function uploadFileTtd(Request $request, $id)
    {
        $request->validate([
            'signature_path' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if (Auth::id() != $id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $user = User::findOrFail($id);

        if ($request->hasFile('signature_path')) {
            // Hapus file lama jika ada
            if ($user->signature_path && file_exists(public_path('storage/signatures/' . $user->signature_path))) {
                unlink(public_path('storage/signatures/' . $user->signature_path));
            }

            $file = $request->file('signature_path');
            $filename = time() . '_' . $user->nama_asli . '.' . $file->getClientOriginalExtension();

            // Buat direktori jika belum ada
            $signaturePath = public_path('storage/signatures');
            if (!file_exists($signaturePath)) {
                mkdir($signaturePath, 0755, true);
            }

            // Simpan ke public/storage/signatures/
            $file->move($signaturePath, $filename);

            // Simpan nama file ke database & set status pending verifikasi
            $user->signature_path = $filename;
            $user->signature_status = 'pending';
            $user->signature_rejection_note = null;
            $user->signature_verified_at = null;
            $user->signature_verified_by = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Tanda tangan berhasil diunggah dan sedang menunggu persetujuan Admin.',
                'signature_url' => asset('storage/signatures/' . $filename),
                'signature_status' => 'pending',
                'signature_status_label' => 'Menunggu Persetujuan Admin'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal mengunggah tanda tangan'], 400);
    }

    public function updatePhoto(Request $request, $id)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:1024', // max 1MB
        ]);

        $user = User::findOrFail($id);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $user->nama_asli . '.' . $file->getClientOriginalExtension();

            // Kompresi gambar menggunakan GD
            $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
            $width = imagesx($image);
            $height = imagesy($image);

            // Resize jika lebih besar dari 800x800 untuk menghemat ukuran
            $maxSize = 800;
            if ($width > $maxSize || $height > $maxSize) {
                $ratio = min($maxSize / $width, $maxSize / $height);
                $newWidth = $width * $ratio;
                $newHeight = $height * $ratio;

                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

                // Pertahankan transparansi untuk PNG
                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension == 'png') {
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                    imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
                }

                imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                $image = $resizedImage;
            }

            // Simpan ke public/storage/photo_profile/ dengan kompresi
            $storagePath = public_path('storage/photo_profile');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $path = $storagePath . '/' . $filename;
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension == 'png') {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagepng($image, $path, 7); // PNG quality 0-9, 7 ≈ 75% quality
            } else {
                imagejpeg($image, $path, 75); // JPEG quality 75%
            }

            imagedestroy($image);

            // Hapus foto lama jika ada
            if ($user->photo && file_exists(public_path('storage/photo_profile/' . $user->photo))) {
                unlink(public_path('storage/photo_profile/' . $user->photo));
            }

            // Simpan nama file ke database
            $user->photo = $filename;
            $user->save();

            return response()->json(['success' => true, 'message' => 'Foto profil berhasil diperbarui']);
        }

        return response()->json(['success' => false, 'message' => 'Gagal mengunggah foto profil'], 400);
    }
}
