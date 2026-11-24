<?php

namespace App\Http\Controllers;

use App\Models\DosenLogbookAutoApprove;
use App\Models\LogbookPertemuan;
use App\Models\User;
use App\Notifications\LogbookNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookApprovalController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Approval Logbook Praktikum';
        $user = Auth::user();
        $isAdmin = $user->role?->slug === 'admin';

        $baseQuery = LogbookPertemuan::with(['logbook.lab', 'logbook.plp', 'logbook.dosen', 'mahasiswa'])
            ->whereHas('logbook', function ($q) use ($user, $isAdmin) {
                if (!$isAdmin) {
                    $q->where('dosen_id', $user->id);
                }
            });

        $pendingPertemuans = (clone $baseQuery)
            ->where('status', LogbookPertemuan::STATUS_PENDING_DOSEN)
            ->orderByDesc('submitted_at')
            ->orderBy('minggu_ke')
            ->get();

        $historyPertemuans = (clone $baseQuery)
            ->whereIn('status', [LogbookPertemuan::STATUS_APPROVED, LogbookPertemuan::STATUS_REJECTED])
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        $autoApproveRules = DosenLogbookAutoApprove::with('plp')
            ->where('dosen_id', $user->id)
            ->orderByRaw('plp_id IS NULL DESC')
            ->get();

        $globalAutoRule = $autoApproveRules->firstWhere('plp_id', null);
        $plpAutoRules = $autoApproveRules->whereNotNull('plp_id')->values();

        $plps = User::whereHas('role', fn ($q) => $q->where('slug', 'plp'))
            ->orderBy('nama_asli')
            ->get();

        $activeTab = $request->get('tab', 'pending');

        return view('pages.logbook.approval', compact(
            'title',
            'user',
            'pendingPertemuans',
            'historyPertemuans',
            'globalAutoRule',
            'plpAutoRules',
            'plps',
            'activeTab'
        ));
    }

    public function approve(LogbookPertemuan $pertemuan)
    {
        $pertemuan->loadMissing(['logbook.dosen', 'logbook.plp', 'logbook.lab']);
        $this->authorizeDosenAccess($pertemuan);

        $user = Auth::user();
        $signer = $user->role?->slug === 'admin' ? $pertemuan->logbook->dosen : $user;
        if (!$signer || !$signer->hasApprovedSignature()) {
            return back()->with('error', 'Anda belum memiliki TTD digital yang disetujui Admin. Silakan unggah & tunggu verifikasi TTD di halaman Profil.');
        }

        if ($pertemuan->status !== LogbookPertemuan::STATUS_PENDING_DOSEN) {
            return back()->with('error', 'Hanya pengajuan berstatus Menunggu TTD Dosen yang dapat disetujui.');
        }

        $pertemuan->update([
            'status' => LogbookPertemuan::STATUS_APPROVED,
            'approval_mode' => 'manual',
            'approved_at' => now('Asia/Jakarta'),
            'rejection_note' => null,
            'revoked_at' => null,
            'revoked_reason' => null,
        ]);

        if ($pertemuan->logbook->plp) {
            $pertemuan->logbook->plp->notify(new LogbookNotification($pertemuan, 'approved', $user));
        }

        return redirect()->route('logbook.approval.index')
            ->with('success', 'Logbook "' . $pertemuan->logbook->nama_mk . '" Minggu ke-' . $pertemuan->minggu_ke . ' berhasil disetujui.');
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'pertemuan_ids' => ['required', 'array', 'min:1'],
            'pertemuan_ids.*' => ['integer', 'exists:logbook_pertemuans,id'],
        ], [
            'pertemuan_ids.required' => 'Pilih minimal 1 entri logbook untuk disetujui.',
        ]);

        $user = Auth::user();
        $isAdmin = $user->role?->slug === 'admin';

        if (!$isAdmin && !$user->hasApprovedSignature()) {
            return back()->with('error', 'Anda belum memiliki TTD digital terverifikasi Admin. Silakan unggah TTD di halaman Profil terlebih dahulu.');
        }

        $pertemuans = LogbookPertemuan::with(['logbook.dosen', 'logbook.plp', 'logbook.lab'])
            ->whereIn('id', $validated['pertemuan_ids'])
            ->where('status', LogbookPertemuan::STATUS_PENDING_DOSEN)
            ->get();

        $approvedCount = 0;
        $now = now('Asia/Jakarta');

        foreach ($pertemuans as $pertemuan) {
            if (!$isAdmin && $pertemuan->logbook->dosen_id !== $user->id) {
                continue;
            }
            if ($isAdmin && (!$pertemuan->logbook->dosen || !$pertemuan->logbook->dosen->hasApprovedSignature())) {
                continue;
            }

            $pertemuan->update([
                'status' => LogbookPertemuan::STATUS_APPROVED,
                'approval_mode' => 'manual',
                'approved_at' => $now,
                'rejection_note' => null,
                'revoked_at' => null,
                'revoked_reason' => null,
            ]);

            if ($pertemuan->logbook->plp) {
                $pertemuan->logbook->plp->notify(new LogbookNotification($pertemuan, 'approved', $user));
            }
            $approvedCount++;
        }

        if ($approvedCount === 0) {
            return back()->with('error', 'Tidak ada logbook valid yang berhasil disetujui.');
        }

        return redirect()->route('logbook.approval.index')
            ->with('success', "Berhasil menyetujui & menandatangani {$approvedCount} entri logbook.");
    }

    public function reject(Request $request, LogbookPertemuan $pertemuan)
    {
        $pertemuan->loadMissing(['logbook.plp', 'logbook.dosen']);
        $this->authorizeDosenAccess($pertemuan);

        $validated = $request->validate([
            'rejection_note' => ['required', 'string', 'max:500'],
        ], [
            'rejection_note.required' => 'Catatan revisi wajib diisi saat menolak logbook.',
        ]);

        if ($pertemuan->status !== LogbookPertemuan::STATUS_PENDING_DOSEN) {
            return back()->with('error', 'Status logbook tidak valid untuk ditolak.');
        }

        $pertemuan->update([
            'status' => LogbookPertemuan::STATUS_REJECTED,
            'approved_at' => null,
            'rejection_note' => trim($validated['rejection_note']),
        ]);

        if ($pertemuan->logbook->plp) {
            $pertemuan->logbook->plp->notify(
                new LogbookNotification($pertemuan, 'rejected', Auth::user(), $pertemuan->rejection_note)
            );
        }

        return redirect()->route('logbook.approval.index')
            ->with('success', 'Logbook dikembalikan ke PLP dengan catatan revisi.');
    }

    public function revoke(Request $request, LogbookPertemuan $pertemuan)
    {
        $pertemuan->loadMissing(['logbook.plp', 'logbook.dosen']);
        $this->authorizeDosenAccess($pertemuan);

        $validated = $request->validate([
            'revoked_reason' => ['required', 'string', 'max:500'],
        ], [
            'revoked_reason.required' => 'Alasan pembatalan TTD wajib diisi.',
        ]);

        if ($pertemuan->status !== LogbookPertemuan::STATUS_APPROVED) {
            return back()->with('error', 'Hanya logbook berstatus Disetujui yang dapat dibatalkan TTD-nya.');
        }

        $reason = trim($validated['revoked_reason']);
        $pertemuan->update([
            'status' => LogbookPertemuan::STATUS_REJECTED,
            'approved_at' => null,
            'revoked_at' => now('Asia/Jakarta'),
            'revoked_reason' => $reason,
            'rejection_note' => 'TTD Dibatalkan: ' . $reason,
        ]);

        if ($pertemuan->logbook->plp) {
            $pertemuan->logbook->plp->notify(
                new LogbookNotification($pertemuan, 'revoked', Auth::user(), $reason)
            );
        }

        return redirect()->route('logbook.approval.index', ['tab' => 'riwayat'])
            ->with('success', 'TTD pada logbook berhasil dibatalkan (Revoke) dan dikembalikan ke PLP.');
    }

    public function saveAutoApprove(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasApprovedSignature()) {
            return redirect()->route('logbook.approval.index', ['tab' => 'auto'])
                ->with('error', 'Fitur Auto-Approve hanya dapat diaktifkan setelah TTD digital Anda diverifikasi Admin.');
        }

        $validated = $request->validate([
            'plp_id' => ['nullable', 'exists:users,id'],
            'is_enabled' => ['nullable', 'boolean'],
            'valid_until' => ['nullable', 'date'],
            'consent' => ['nullable'],
        ]);

        $isEnabled = $request->boolean('is_enabled');
        if ($isEnabled && !$request->boolean('consent')) {
            return redirect()->route('logbook.approval.index', ['tab' => 'auto'])
                ->with('error', 'Anda wajib mencentang pernyataan persetujuan izin Auto-Approve sebelum mengaktifkannya.');
        }

        $plpId = !empty($validated['plp_id']) ? (int) $validated['plp_id'] : null;

        // Karena SQLite/MySQL memperlakukan NULL berbeda pada unique index, cari eksplisit terlebih dahulu
        $rule = DosenLogbookAutoApprove::where('dosen_id', $user->id)
            ->when($plpId === null, fn ($q) => $q->whereNull('plp_id'), fn ($q) => $q->where('plp_id', $plpId))
            ->first();

        $payload = [
            'dosen_id' => $user->id,
            'plp_id' => $plpId,
            'is_enabled' => $isEnabled,
            'valid_until' => $validated['valid_until'] ?? null,
            'consented_at' => $isEnabled ? now('Asia/Jakarta') : ($rule?->consented_at),
        ];

        if ($rule) {
            $rule->update($payload);
        } else {
            DosenLogbookAutoApprove::create($payload);
        }

        $msg = $isEnabled
            ? 'Pengaturan Auto-Approve atas izin Anda berhasil diaktifkan.'
            : 'Pengaturan Auto-Approve berhasil dinonaktifkan.';

        return redirect()->route('logbook.approval.index', ['tab' => 'auto'])
            ->with('success', $msg);
    }

    public function deleteAutoApprove(DosenLogbookAutoApprove $setting)
    {
        $user = Auth::user();
        if ($user->role?->slug !== 'admin' && $setting->dosen_id !== $user->id) {
            abort(403);
        }

        $setting->delete();

        return redirect()->route('logbook.approval.index', ['tab' => 'auto'])
            ->with('success', 'Aturan Auto-Approve berhasil dihapus.');
    }

    private function authorizeDosenAccess(LogbookPertemuan $pertemuan): void
    {
        $user = Auth::user();
        if ($user->role?->slug === 'admin') {
            return;
        }

        if ($user->role?->slug === 'dosen' && $pertemuan->logbook->dosen_id === $user->id) {
            return;
        }

        abort(403, 'Anda bukan Dosen Pengampu untuk logbook ini.');
    }
}
