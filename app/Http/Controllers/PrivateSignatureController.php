<?php

namespace App\Http\Controllers;

use App\Models\LogbookPertemuan;
use App\Models\LogbookPraktikum;
use App\Models\User;
use App\Services\SignatureStorageService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrivateSignatureController extends Controller
{
    public function showUserSignature(User $user, SignatureStorageService $storage): BinaryFileResponse
    {
        $authUser = Auth::user();
        if (!$authUser) {
            abort(403);
        }

        $isAdmin = $authUser->role?->slug === 'admin';
        if ((int) $authUser->id !== (int) $user->id && !$isAdmin) {
            abort(403, 'Anda tidak berhak mengakses berkas tanda tangan ini.');
        }

        $path = $storage->resolveUserSignaturePath($user->signature_path);
        if (!$path) {
            abort(404);
        }

        return $this->streamPrivateImage($path, $storage);
    }

    public function showLogbookSignature(string $type, int $id, SignatureStorageService $storage): BinaryFileResponse
    {
        $authUser = Auth::user();
        if (!$authUser) {
            abort(403);
        }

        if ($type === 'logbook') {
            $logbook = LogbookPraktikum::findOrFail($id);
            $filename = $logbook->default_mahasiswa_ttd_path;
        } elseif ($type === 'pertemuan') {
            $pertemuan = LogbookPertemuan::with('logbook')->findOrFail($id);
            $logbook = $pertemuan->logbook;
            $filename = $pertemuan->mahasiswa_ttd_path;
        } else {
            abort(404);
        }

        $isAdmin = $authUser->role?->slug === 'admin';
        $isOwnerPlp = (int) $logbook->plp_id === (int) $authUser->id;
        $isAssignedDosen = $authUser->role?->slug === 'dosen' && (int) $logbook->dosen_id === (int) $authUser->id;

        if (!$isAdmin && !$isOwnerPlp && !$isAssignedDosen) {
            abort(403, 'Anda tidak berhak mengakses berkas tanda tangan mahasiswa ini.');
        }

        $path = $storage->resolveLogbookSignaturePath($filename);
        if (!$path) {
            abort(404);
        }

        return $this->streamPrivateImage($path, $storage);
    }

    private function streamPrivateImage(string $absolutePath, SignatureStorageService $storage): BinaryFileResponse
    {
        return response()->file($absolutePath, [
            'Content-Type' => $storage->detectMimeType($absolutePath),
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
