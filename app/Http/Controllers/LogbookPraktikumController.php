<?php

namespace App\Http\Controllers;

use App\Models\DosenLogbookAutoApprove;
use App\Models\InventarisRuangan;
use App\Models\JadwalLab;
use App\Models\Lab;
use App\Models\LabManager;
use App\Models\LogbookPertemuan;
use App\Models\LogbookPraktikum;
use App\Models\PeriodeJadwal;
use App\Models\PlpAktivitasTemplate;
use App\Models\User;
use App\Notifications\LogbookNotification;
use App\Services\SignatureStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LogbookPraktikumController extends Controller
{
    private const DAY_ISO_TO_HARI = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => "Jum'at",
    ];

    private const HARI_TO_ISO = [
        'Senin' => 1,
        'Selasa' => 2,
        'Rabu' => 3,
        'Kamis' => 4,
        "Jum'at" => 5,
    ];

    public function index(Request $request)
    {
        $title = 'Logbook Praktikum PLP';
        $user = Auth::user();
        $isAdmin = $user->role?->slug === 'admin';

        $managedLabIds = $isAdmin
            ? Lab::pluck('id')
            : LabManager::where('plp_id', $user->id)->pluck('lab_id');

        $labs = Lab::whereIn('id', $managedLabIds)->orderBy('kode_lab')->get();
        $dosens = User::whereHas('role', fn ($q) => $q->where('slug', 'dosen'))
            ->orderBy('nama_asli')
            ->get();

        $logbooksQuery = LogbookPraktikum::with(['lab', 'dosen', 'plp'])
            ->withCount([
                'pertemuans',
                'pertemuans as approved_count' => fn ($q) => $q->where('status', LogbookPertemuan::STATUS_APPROVED),
                'pertemuans as pending_count' => fn ($q) => $q->where('status', LogbookPertemuan::STATUS_PENDING_DOSEN),
                'pertemuans as draft_count' => fn ($q) => $q->whereIn('status', [LogbookPertemuan::STATUS_DRAFT, LogbookPertemuan::STATUS_REJECTED]),
            ])
            ->orderByDesc('tahun_akademik')
            ->orderBy('nama_mk');

        if (!$isAdmin) {
            $logbooksQuery->where('plp_id', $user->id);
        }

        $logbooks = $logbooksQuery->get();

        // Praktikum Hari Ini berdasarkan hari jadwal
        $now = Carbon::now('Asia/Jakarta');
        $todayHari = self::DAY_ISO_TO_HARI[$now->dayOfWeekIso] ?? null;
        $todayDate = $now->toDateString();

        $todayLogbooks = collect();
        if ($todayHari) {
            $todayLogbooks = $logbooks->filter(fn (LogbookPraktikum $book) => $book->hari === $todayHari)
                ->map(function (LogbookPraktikum $book) use ($todayDate) {
                    $maxWeek = (int) ($book->pertemuans()->max('minggu_ke') ?? 0);
                    $todayEntry = $book->pertemuans()->whereDate('tanggal_pelaksanaan', $todayDate)->first();
                    $book->next_minggu_ke = $maxWeek + 1;
                    $book->today_entry = $todayEntry;
                    return $book;
                })
                ->values();
        }

        // Jadwal Lab periode aktif yang belum dibuatkan logbook oleh user ini
        $activePeriode = PeriodeJadwal::active()->first();
        $availableJadwals = collect();
        if ($activePeriode && $managedLabIds->isNotEmpty()) {
            $existingJadwalIds = $logbooks->pluck('jadwal_lab_id')->filter()->all();
            $availableJadwals = JadwalLab::with(['lab', 'dosen'])
                ->where('periode_jadwal_id', $activePeriode->id)
                ->whereIn('lab_id', $managedLabIds)
                ->when(!empty($existingJadwalIds), fn ($q) => $q->whereNotIn('id', $existingJadwalIds))
                ->get()
                ->sortBy(function (JadwalLab $j) {
                    $isPraktek = in_array($j->jenis, ['Praktek', 'Teori & Praktek'], true) ? 0 : 1;
                    return $isPraktek . '_' . JadwalLab::hariOrderIndex($j->hari) . '_' . $j->waktu_mulai;
                })
                ->values();
        }

        return view('pages.logbook.index', compact(
            'title',
            'labs',
            'dosens',
            'logbooks',
            'todayHari',
            'todayDate',
            'todayLogbooks',
            'activePeriode',
            'availableJadwals'
        ));
    }

    public function storeFromJadwal(Request $request)
    {
        $validated = $request->validate([
            'jadwal_lab_ids' => ['required', 'array', 'min:1'],
            'jadwal_lab_ids.*' => ['integer', 'exists:jadwal_labs,id'],
            'keterangan_plp' => ['nullable', 'string', 'max:100'],
        ], [
            'jadwal_lab_ids.required' => 'Pilih minimal 1 jadwal praktikum untuk dibuatkan logbook.',
        ]);

        $user = Auth::user();
        $isAdmin = $user->role?->slug === 'admin';
        $managedLabIds = $isAdmin
            ? Lab::pluck('id')->all()
            : LabManager::where('plp_id', $user->id)->pluck('lab_id')->all();

        $jadwals = JadwalLab::with('periode')
            ->whereIn('id', $validated['jadwal_lab_ids'])
            ->get();

        $createdCount = 0;
        DB::transaction(function () use ($jadwals, $user, $isAdmin, $managedLabIds, $validated, &$createdCount) {
            foreach ($jadwals as $jadwal) {
                if (!$jadwal->lab_id || (!$isAdmin && !in_array($jadwal->lab_id, $managedLabIds, true))) {
                    continue;
                }

                $alreadyExists = LogbookPraktikum::where('plp_id', $user->id)
                    ->where('jadwal_lab_id', $jadwal->id)
                    ->exists();
                if ($alreadyExists) {
                    continue;
                }

                $kelas = trim((string) $jadwal->kelas);
                if ($kelas === '') {
                    $kelas = trim(($jadwal->semester ? $jadwal->semester . ' ' : '') . ($jadwal->program_studi ?? ''));
                }

                LogbookPraktikum::create([
                    'jadwal_lab_id' => $jadwal->id,
                    'lab_id' => $jadwal->lab_id,
                    'plp_id' => $user->id,
                    'dosen_id' => $jadwal->dosen_id,
                    'nama_dosen_fallback' => $jadwal->nama_dosen,
                    'nama_mk' => $jadwal->mata_kuliah,
                    'kelas' => $kelas ?: '-',
                    'hari' => $jadwal->hari,
                    'semester' => $jadwal->periode?->semester ?? 'Ganjil',
                    'tahun_akademik' => $jadwal->periode?->tahun_ajaran ?? (date('Y') . '/' . (date('Y') + 1)),
                    'jam_default' => $jadwal->waktu_mulai . ' WIB',
                    'keterangan_plp' => !empty($validated['keterangan_plp']) ? trim($validated['keterangan_plp']) : null,
                ]);
                $createdCount++;
            }
        });

        if ($createdCount === 0) {
            return redirect()->route('logbook.index')
                ->with('error', 'Tidak ada buku logbook baru yang dibuat (mungkin sudah ada atau bukan lab kelolaan Anda).');
        }

        return redirect()->route('logbook.index')
            ->with('success', "Berhasil membuat {$createdCount} buku logbook dari Jadwal Lab aktif.");
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->role?->slug === 'admin';
        $managedLabIds = $isAdmin
            ? Lab::pluck('id')->all()
            : LabManager::where('plp_id', $user->id)->pluck('lab_id')->all();

        $validated = $request->validate([
            'lab_id' => ['required', 'exists:labs,id'],
            'dosen_id' => ['nullable', 'exists:users,id'],
            'nama_dosen_fallback' => ['nullable', 'string', 'max:150'],
            'nama_mk' => ['required', 'string', 'max:200'],
            'kelas' => ['required', 'string', 'max:100'],
            'hari' => ['nullable', Rule::in(JadwalLab::HARI_ORDER)],
            'semester' => ['required', 'string', 'max:30'],
            'tahun_akademik' => ['required', 'string', 'max:20'],
            'jam_default' => ['nullable', 'string', 'max:30'],
            'keterangan_plp' => ['nullable', 'string', 'max:100'],
        ]);

        if (!$isAdmin && !in_array((int) $validated['lab_id'], $managedLabIds, true)) {
            abort(403, 'Anda bukan penanggung jawab laboratorium ini.');
        }

        $validated['plp_id'] = $user->id;
        if (empty($validated['dosen_id']) && empty(trim((string) ($validated['nama_dosen_fallback'] ?? '')))) {
            $validated['nama_dosen_fallback'] = '-';
        }

        $book = LogbookPraktikum::create($validated);

        return redirect()->route('logbook.show', $book->id)
            ->with('success', 'Buku logbook "' . $book->nama_mk . '" berhasil dibuat.');
    }

    public function show(Request $request, LogbookPraktikum $logbook)
    {
        $this->authorizeLogbookAccess($logbook);

        $logbook->load(['lab', 'plp', 'dosen', 'defaultMahasiswa', 'pertemuans.mahasiswa']);

        $title = 'Detail Logbook — ' . $logbook->nama_mk;
        $user = Auth::user();
        $isAdmin = $user->role?->slug === 'admin';

        $managedLabIds = $isAdmin
            ? Lab::pluck('id')
            : LabManager::where('plp_id', $user->id)->pluck('lab_id');
        $labs = Lab::whereIn('id', $managedLabIds)->orderBy('kode_lab')->get();
        $dosens = User::whereHas('role', fn ($q) => $q->where('slug', 'dosen'))
            ->orderBy('nama_asli')
            ->get();
        $mahasiswas = User::whereHas('role', fn ($q) => $q->where('slug', 'mahasiswa'))
            ->orderBy('nama_asli')
            ->get(['id', 'nama_asli', 'nrp', 'signature_path', 'signature_status']);

        $templates = PlpAktivitasTemplate::where('plp_id', $logbook->plp_id)
            ->orderBy('nama_template')
            ->get();

        $inventarisAlat = InventarisRuangan::where('lab_id', $logbook->lab_id)
            ->orderBy('nama_barang')
            ->pluck('nama_barang')
            ->unique()
            ->values();

        $autoApproveActive = $logbook->dosen_id && $logbook->dosen?->hasApprovedSignature()
            ? DosenLogbookAutoApprove::isActiveFor($logbook->dosen_id, $logbook->plp_id)
            : false;

        $nextMingguKe = ((int) ($logbook->pertemuans->max('minggu_ke') ?? 0)) + 1;

        return view('pages.logbook.show', compact(
            'title',
            'logbook',
            'labs',
            'dosens',
            'mahasiswas',
            'templates',
            'inventarisAlat',
            'autoApproveActive',
            'nextMingguKe'
        ));
    }

    public function update(Request $request, LogbookPraktikum $logbook)
    {
        $this->authorizeLogbookAccess($logbook);

        $user = Auth::user();
        $isAdmin = $user->role?->slug === 'admin';
        $managedLabIds = $isAdmin
            ? Lab::pluck('id')->all()
            : LabManager::where('plp_id', $user->id)->pluck('lab_id')->all();

        $validated = $request->validate([
            'lab_id' => ['required', 'exists:labs,id'],
            'dosen_id' => ['nullable', 'exists:users,id'],
            'nama_dosen_fallback' => ['nullable', 'string', 'max:150'],
            'nama_mk' => ['required', 'string', 'max:200'],
            'kelas' => ['required', 'string', 'max:100'],
            'hari' => ['nullable', Rule::in(JadwalLab::HARI_ORDER)],
            'semester' => ['required', 'string', 'max:30'],
            'tahun_akademik' => ['required', 'string', 'max:20'],
            'jam_default' => ['nullable', 'string', 'max:30'],
            'keterangan_plp' => ['nullable', 'string', 'max:100'],
        ]);

        if (!$isAdmin && !in_array((int) $validated['lab_id'], $managedLabIds, true)) {
            abort(403, 'Anda bukan penanggung jawab laboratorium ini.');
        }

        $newDosenId = !empty($validated['dosen_id']) ? (int) $validated['dosen_id'] : null;
        if ($newDosenId !== ($logbook->dosen_id ? (int) $logbook->dosen_id : null)
            && $logbook->pertemuans()->where('status', LogbookPertemuan::STATUS_APPROVED)->exists()) {
            return redirect()->route('logbook.show', $logbook->id)
                ->with('error', 'Dosen Pengampu tidak dapat diubah karena buku ini sudah memiliki pertemuan yang disetujui/ditandatangani.');
        }

        $logbook->update($validated);

        return redirect()->route('logbook.show', $logbook->id)
            ->with('success', 'Informasi buku logbook berhasil diperbarui.');
    }

    public function destroy(LogbookPraktikum $logbook)
    {
        $this->authorizeLogbookAccess($logbook);

        if ($logbook->pertemuans()->where('status', LogbookPertemuan::STATUS_APPROVED)->exists()) {
            return redirect()->route('logbook.index')
                ->with('error', 'Buku logbook tidak dapat dihapus karena sudah memiliki pertemuan yang disetujui dosen.');
        }

        $nama = $logbook->nama_mk;
        $logbook->delete();

        return redirect()->route('logbook.index')
            ->with('success', 'Buku logbook "' . $nama . '" berhasil dihapus.');
    }

    public function generatePertemuans(Request $request, LogbookPraktikum $logbook)
    {
        $this->authorizeLogbookAccess($logbook);

        $validated = $request->validate([
            'tanggal_mulai' => ['required', 'date'],
            'minggu_mulai' => ['required', 'integer', 'min:1', 'max:20'],
            'minggu_selesai' => ['required', 'integer', 'gte:minggu_mulai', 'max:20'],
            'judul_prefix' => ['nullable', 'string', 'max:150'],
        ]);

        $startDate = Carbon::parse($validated['tanggal_mulai']);
        if ($logbook->hari && isset(self::HARI_TO_ISO[$logbook->hari])) {
            $targetIso = self::HARI_TO_ISO[$logbook->hari];
            $diffDays = ($targetIso - $startDate->dayOfWeekIso + 7) % 7;
            $startDate->addDays($diffDays);
        }

        $existingWeeks = $logbook->pertemuans()->pluck('minggu_ke')->all();
        $prefix = trim((string) ($validated['judul_prefix'] ?? '')) ?: ('Praktikum ' . $logbook->nama_mk);
        $jamDefault = $logbook->jam_default ?: '08.00 WIB';

        $created = 0;
        $currentDate = $startDate->copy();
        for ($week = (int) $validated['minggu_mulai']; $week <= (int) $validated['minggu_selesai']; $week++) {
            if (!in_array($week, $existingWeeks, true)) {
                LogbookPertemuan::create([
                    'logbook_praktikum_id' => $logbook->id,
                    'minggu_ke' => $week,
                    'judul_praktikum' => $prefix . ' (Minggu ke-' . $week . ')',
                    'tanggal_pelaksanaan' => $currentDate->toDateString(),
                    'jam_pelaksanaan' => $jamDefault,
                    'peralatan' => [],
                    'aktivitas' => [],
                    'permasalahan' => [],
                    'status' => LogbookPertemuan::STATUS_DRAFT,
                ]);
                $created++;
            }
            $currentDate->addDays(7);
        }

        return redirect()->route('logbook.show', $logbook->id)
            ->with('success', "Berhasil men-generate {$created} draft pertemuan.");
    }

    public function storePertemuan(Request $request, LogbookPraktikum $logbook)
    {
        $this->authorizeLogbookAccess($logbook);

        $data = $this->validatePertemuan($request);
        $data['logbook_praktikum_id'] = $logbook->id;
        $data['status'] = LogbookPertemuan::STATUS_DRAFT;

        $pertemuan = LogbookPertemuan::create($data);

        return redirect()->route('logbook.show', $logbook->id)
            ->with('success', 'Pertemuan Minggu ke-' . $pertemuan->minggu_ke . ' berhasil ditambahkan.');
    }

    public function updatePertemuan(Request $request, LogbookPertemuan $pertemuan)
    {
        Gate::authorize('manage', $pertemuan);
        $logbook = $pertemuan->logbook;

        if (!in_array($pertemuan->status, [LogbookPertemuan::STATUS_DRAFT, LogbookPertemuan::STATUS_REJECTED], true)) {
            return redirect()->route('logbook.show', $logbook->id)
                ->with('error', 'Hanya pertemuan berstatus Draft atau Perlu Revisi yang dapat diubah.');
        }

        $data = $this->validatePertemuan($request);

        if ($request->boolean('clear_mahasiswa_override')) {
            $data['mahasiswa_user_id'] = null;
            $data['mahasiswa_nama'] = null;
            $data['mahasiswa_nrp'] = null;
            $data['mahasiswa_ttd_path'] = null;
        }

        // Jika sebelumnya rejected, kembalikan ke draft setelah direvisi
        if ($pertemuan->status === LogbookPertemuan::STATUS_REJECTED) {
            $data['status'] = LogbookPertemuan::STATUS_DRAFT;
        }

        $pertemuan->update($data);

        return redirect()->route('logbook.show', $logbook->id)
            ->with('success', 'Pertemuan Minggu ke-' . $pertemuan->minggu_ke . ' berhasil diperbarui.');
    }

    public function destroyPertemuan(LogbookPertemuan $pertemuan)
    {
        Gate::authorize('manage', $pertemuan);
        $logbook = $pertemuan->logbook;

        if (!in_array($pertemuan->status, [LogbookPertemuan::STATUS_DRAFT, LogbookPertemuan::STATUS_REJECTED], true)) {
            return redirect()->route('logbook.show', $logbook->id)
                ->with('error', 'Hanya pertemuan berstatus Draft atau Perlu Revisi yang dapat dihapus.');
        }

        $minggu = $pertemuan->minggu_ke;
        $pertemuan->delete();

        return redirect()->route('logbook.show', $logbook->id)
            ->with('success', 'Pertemuan Minggu ke-' . $minggu . ' berhasil dihapus.');
    }

    public function duplicatePertemuan(LogbookPertemuan $pertemuan)
    {
        Gate::authorize('manage', $pertemuan);
        $logbook = $pertemuan->logbook;

        $nextWeek = ((int) ($logbook->pertemuans()->max('minggu_ke') ?? 0)) + 1;
        $copy = LogbookPertemuan::create([
            'logbook_praktikum_id' => $logbook->id,
            'minggu_ke' => $nextWeek,
            'judul_praktikum' => $pertemuan->judul_praktikum,
            'tanggal_pelaksanaan' => $pertemuan->tanggal_pelaksanaan->copy()->addDays(7)->toDateString(),
            'jam_pelaksanaan' => $pertemuan->jam_pelaksanaan,
            'peralatan' => $pertemuan->peralatan ?? [],
            'aktivitas' => $pertemuan->aktivitas ?? [],
            'permasalahan' => $pertemuan->permasalahan ?? [],
            'status' => LogbookPertemuan::STATUS_DRAFT,
        ]);

        return redirect()->route('logbook.show', $logbook->id)
            ->with('success', 'Pertemuan berhasil diduplikat ke Minggu ke-' . $copy->minggu_ke . '.');
    }

    public function submitPertemuan(LogbookPertemuan $pertemuan)
    {
        $logbook = $pertemuan->logbook()->with(['dosen', 'plp', 'lab'])->firstOrFail();
        $this->authorizeLogbookAccess($logbook);

        if (!in_array($pertemuan->status, [LogbookPertemuan::STATUS_DRAFT, LogbookPertemuan::STATUS_REJECTED], true)) {
            return redirect()->route('logbook.show', $logbook->id)
                ->with('error', 'Hanya pertemuan berstatus Draft atau Perlu Revisi yang dapat diajukan.');
        }

        if (!$logbook->dosen_id || !$logbook->dosen) {
            return redirect()->route('logbook.show', $logbook->id)
                ->with('error', 'Buku logbook ini belum ditautkan ke akun Dosen Pengampu SIMLAB.');
        }

        $mode = $this->processSubmit($pertemuan, $logbook, Auth::user());

        $msg = $mode === 'auto'
            ? 'Pertemuan Minggu ke-' . $pertemuan->minggu_ke . ' langsung disetujui otomatis (Auto-Approve aktif atas izin dosen).'
            : 'Pertemuan Minggu ke-' . $pertemuan->minggu_ke . ' berhasil diajukan ke Dosen Pengampu.';

        return redirect()->route('logbook.show', $logbook->id)->with('success', $msg);
    }

    public function bulkSubmit(Request $request, LogbookPraktikum $logbook)
    {
        $this->authorizeLogbookAccess($logbook);
        $logbook->load(['dosen', 'plp', 'lab']);

        $validated = $request->validate([
            'pertemuan_ids' => ['required', 'array', 'min:1'],
            'pertemuan_ids.*' => ['integer', 'exists:logbook_pertemuans,id'],
        ], [
            'pertemuan_ids.required' => 'Pilih minimal 1 pertemuan untuk diajukan.',
        ]);

        if (!$logbook->dosen_id || !$logbook->dosen) {
            return redirect()->route('logbook.show', $logbook->id)
                ->with('error', 'Buku logbook ini belum ditautkan ke akun Dosen Pengampu SIMLAB.');
        }

        $pertemuans = LogbookPertemuan::where('logbook_praktikum_id', $logbook->id)
            ->whereIn('id', $validated['pertemuan_ids'])
            ->whereIn('status', [LogbookPertemuan::STATUS_DRAFT, LogbookPertemuan::STATUS_REJECTED])
            ->get();

        $autoCount = 0;
        $manualCount = 0;
        $user = Auth::user();

        foreach ($pertemuans as $pertemuan) {
            $mode = $this->processSubmit($pertemuan, $logbook, $user);
            if ($mode === 'auto') {
                $autoCount++;
            } else {
                $manualCount++;
            }
        }

        if ($autoCount + $manualCount === 0) {
            return redirect()->route('logbook.show', $logbook->id)
                ->with('error', 'Tidak ada pertemuan berstatus Draft/Revisi pada pilihan Anda.');
        }

        return redirect()->route('logbook.show', $logbook->id)
            ->with('success', "Berhasil memproses pengajuan ({$manualCount} menunggu TTD dosen, {$autoCount} auto-approved).");
    }

    public function saveMahasiswaSignature(Request $request)
    {
        $validated = $request->validate([
            'target_type' => ['required', Rule::in(['logbook', 'pertemuan'])],
            'target_id' => ['required', 'integer'],
            'mahasiswa_user_id' => ['nullable', 'exists:users,id'],
            'mahasiswa_nama' => ['required', 'string', 'max:150'],
            'mahasiswa_nrp' => ['nullable', 'string', 'max:50'],
            'signature_data' => ['nullable', 'string'],
            'use_profile_signature' => ['nullable', 'boolean'],
        ]);

        if ($validated['target_type'] === 'logbook') {
            $target = LogbookPraktikum::findOrFail($validated['target_id']);
            $this->authorizeLogbookAccess($target);
            $redirectBookId = $target->id;
        } else {
            $target = LogbookPertemuan::with('logbook')->findOrFail($validated['target_id']);
            $this->authorizeLogbookAccess($target->logbook);
            $redirectBookId = $target->logbook_praktikum_id;

            if ($this->isPertemuanSignatureLocked($target)) {
                $msg = 'TTD mahasiswa tidak dapat diubah karena pertemuan sedang menunggu approval atau sudah disetujui dosen.';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->route('logbook.show', $redirectBookId)->with('error', $msg);
            }
        }

        $filename = null;
        if ($request->boolean('use_profile_signature') && !empty($validated['mahasiswa_user_id'])) {
            $mhsUser = User::find($validated['mahasiswa_user_id']);
            if ($mhsUser && $mhsUser->hasApprovedSignature()) {
                $filename = $this->copyProfileSignatureToLogbook($mhsUser->signature_path);
            }
        }

        if (!$filename && !empty($validated['signature_data'])) {
            $filename = $this->storeBase64Png($validated['signature_data']);
        }

        if ($validated['target_type'] === 'logbook') {
            $updateData = [
                'default_mahasiswa_user_id' => $validated['mahasiswa_user_id'] ?? null,
                'default_mahasiswa_nama' => trim($validated['mahasiswa_nama']),
                'default_mahasiswa_nrp' => !empty($validated['mahasiswa_nrp']) ? trim($validated['mahasiswa_nrp']) : null,
            ];
            if ($filename) {
                $updateData['default_mahasiswa_ttd_path'] = $filename;
            }
            $target->update($updateData);
        } else {
            $updateData = [
                'mahasiswa_user_id' => $validated['mahasiswa_user_id'] ?? null,
                'mahasiswa_nama' => trim($validated['mahasiswa_nama']),
                'mahasiswa_nrp' => !empty($validated['mahasiswa_nrp']) ? trim($validated['mahasiswa_nrp']) : null,
            ];
            if ($filename) {
                $updateData['mahasiswa_ttd_path'] = $filename;
            }
            $target->update($updateData);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tanda tangan perwakilan mahasiswa berhasil disimpan.',
            ]);
        }

        return redirect()->route('logbook.show', $redirectBookId)
            ->with('success', 'Identitas & TTD Perwakilan Mahasiswa berhasil disimpan.');
    }

    public function generateSignLink(Request $request)
    {
        $validated = $request->validate([
            'target_type' => ['required', Rule::in(['logbook', 'pertemuan'])],
            'target_id' => ['required', 'integer'],
        ]);

        if ($validated['target_type'] === 'logbook') {
            $book = LogbookPraktikum::findOrFail($validated['target_id']);
            $this->authorizeLogbookAccess($book);
        } else {
            $pertemuan = LogbookPertemuan::with('logbook')->findOrFail($validated['target_id']);
            $this->authorizeLogbookAccess($pertemuan->logbook);

            if ($this->isPertemuanSignatureLocked($pertemuan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pertemuan sedang menunggu approval atau sudah disetujui dosen sehingga TTD mahasiswa tidak dapat diubah.',
                ], 422);
            }
        }

        $url = URL::temporarySignedRoute(
            'logbook.sign-mahasiswa.show',
            now()->addMinutes(30),
            ['type' => $validated['target_type'], 'id' => $validated['target_id']]
        );

        return response()->json([
            'success' => true,
            'url' => $url,
        ]);
    }

    public function showPublicSign(Request $request, string $type, int $id)
    {
        if (!in_array($type, ['logbook', 'pertemuan'], true)) {
            abort(404);
        }

        $target = $type === 'logbook'
            ? LogbookPraktikum::with(['lab', 'plp'])->findOrFail($id)
            : LogbookPertemuan::with(['logbook.lab', 'logbook.plp'])->findOrFail($id);

        $logbook = $type === 'logbook' ? $target : $target->logbook;
        $pertemuan = $type === 'pertemuan' ? $target : null;

        if ($pertemuan && $this->isPertemuanSignatureLocked($pertemuan)) {
            abort(403, 'Pertemuan sedang menunggu approval atau sudah disetujui dosen sehingga TTD mahasiswa tidak dapat diubah.');
        }

        $defaultName = $pertemuan
            ? ($pertemuan->mahasiswa_nama ?: $logbook->default_mahasiswa_nama)
            : $logbook->default_mahasiswa_nama;
        $defaultNrp = $pertemuan
            ? ($pertemuan->mahasiswa_nrp ?: $logbook->default_mahasiswa_nrp)
            : $logbook->default_mahasiswa_nrp;

        $submitUrl = URL::temporarySignedRoute(
            'logbook.sign-mahasiswa.submit',
            now()->addMinutes(30),
            ['type' => $type, 'id' => $id]
        );

        return view('pages.logbook.sign_public', compact(
            'type',
            'logbook',
            'pertemuan',
            'defaultName',
            'defaultNrp',
            'submitUrl'
        ));
    }

    public function submitPublicSign(Request $request, string $type, int $id)
    {
        if (!in_array($type, ['logbook', 'pertemuan'], true)) {
            abort(404);
        }

        $validated = $request->validate([
            'mahasiswa_nama' => ['required', 'string', 'max:150'],
            'mahasiswa_nrp' => ['required', 'string', 'max:50'],
            'signature_data' => ['required', 'string', 'max:700000'],
        ], [
            'mahasiswa_nama.required' => 'Nama lengkap wajib diisi.',
            'mahasiswa_nrp.required' => 'NRP wajib diisi.',
            'signature_data.required' => 'Silakan bubuhkan tanda tangan pada kotak yang tersedia.',
            'signature_data.max' => 'Ukuran tanda tangan melebihi batas maksimal.',
        ]);

        if ($type === 'logbook') {
            $logbook = LogbookPraktikum::with(['lab', 'plp'])->findOrFail($id);
            $pertemuan = null;
        } else {
            $pertemuan = LogbookPertemuan::with(['logbook.lab', 'logbook.plp'])->findOrFail($id);
            if ($this->isPertemuanSignatureLocked($pertemuan)) {
                abort(403, 'Pertemuan sedang menunggu approval atau sudah disetujui dosen sehingga TTD mahasiswa tidak dapat diubah.');
            }
            $logbook = $pertemuan->logbook;
        }

        $filename = $this->storeBase64Png($validated['signature_data']);
        if (!$filename) {
            $msg = 'Format tanda tangan tidak valid atau melebihi batas ukuran (maks 512KB PNG/JPG). Silakan ulangi tanda tangan.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withInput()->withErrors(['signature_data' => $msg])->with('error', $msg);
        }

        if ($type === 'logbook') {
            $logbook->update([
                'default_mahasiswa_nama' => trim($validated['mahasiswa_nama']),
                'default_mahasiswa_nrp' => trim($validated['mahasiswa_nrp']),
                'default_mahasiswa_ttd_path' => $filename,
            ]);
        } else {
            $pertemuan->update([
                'mahasiswa_nama' => trim($validated['mahasiswa_nama']),
                'mahasiswa_nrp' => trim($validated['mahasiswa_nrp']),
                'mahasiswa_ttd_path' => $filename,
            ]);
        }

        $signedSuccess = true;
        $signedNama = trim($validated['mahasiswa_nama']);
        $signedNrp = trim($validated['mahasiswa_nrp']);

        return view('pages.logbook.sign_public', compact(
            'type',
            'logbook',
            'pertemuan',
            'signedSuccess',
            'signedNama',
            'signedNrp'
        ));
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'nama_template' => ['required', 'string', 'max:120'],
            'aktivitas_text' => ['required', 'string'],
        ]);

        $items = collect(preg_split('/\r?\n/', $validated['aktivitas_text']))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        if (empty($items)) {
            return back()->with('error', 'Isi minimal 1 baris aktivitas untuk template.');
        }

        PlpAktivitasTemplate::create([
            'plp_id' => Auth::id(),
            'nama_template' => trim($validated['nama_template']),
            'aktivitas' => $items,
        ]);

        return back()->with('success', 'Preset aktivitas "' . $validated['nama_template'] . '" berhasil disimpan.');
    }

    public function destroyTemplate(PlpAktivitasTemplate $template)
    {
        $user = Auth::user();
        if ($user->role?->slug !== 'admin' && $template->plp_id !== $user->id) {
            abort(403);
        }

        $template->delete();

        return back()->with('success', 'Preset aktivitas berhasil dihapus.');
    }

    public function exportPdf(Request $request, LogbookPraktikum $logbook)
    {
        Gate::authorize('downloadPdf', $logbook);

        $logbook->load(['lab', 'plp', 'dosen', 'defaultMahasiswa']);

        $pertemuansQuery = $logbook->pertemuans()->with('mahasiswa');
        if ($request->filled('pertemuan_id')) {
            $pertemuansQuery->where('id', (int) $request->get('pertemuan_id'));
        }

        $pertemuans = $pertemuansQuery->get();
        if ($pertemuans->isEmpty()) {
            return back()->with('error', 'Belum ada pertemuan logbook untuk dicetak.');
        }

        $logoPensBase64 = $this->fileToBase64(public_path('images/logo/logo_PENS.png'));

        $plpTtdBase64 = null;
        if ($logbook->plp && $logbook->plp->hasApprovedSignature()) {
            $plpTtdBase64 = $this->userSignatureToBase64($logbook->plp->signature_path);
        }

        $dosenTtdBase64 = null;
        if ($logbook->dosen && $logbook->dosen->hasApprovedSignature()) {
            $dosenTtdBase64 = $this->userSignatureToBase64($logbook->dosen->signature_path);
        }

        $pages = $pertemuans->map(function (LogbookPertemuan $pertemuan) use ($dosenTtdBase64) {
            $hasPeralatan = $pertemuan->hasPeralatan();
            $effMhs = $pertemuan->effective_mahasiswa;
            $mhsTtdBase64 = null;

            if ($hasPeralatan && !empty($effMhs['ttd_path'])) {
                $mhsTtdBase64 = SignatureStorageService::logbookSignatureDataUri($effMhs['ttd_path']);
            }

            return [
                'pertemuan' => $pertemuan,
                'has_peralatan' => $hasPeralatan,
                'peralatan' => array_values(array_filter(
                    $pertemuan->peralatan ?? [],
                    fn ($row) => trim((string) ($row['nama_alat'] ?? '')) !== ''
                )),
                'aktivitas' => array_values(array_filter(
                    $pertemuan->aktivitas ?? [],
                    fn ($line) => trim((string) $line) !== ''
                )),
                'permasalahan' => array_values(array_filter(
                    $pertemuan->permasalahan ?? [],
                    fn ($row) => trim((string) ($row['masalah'] ?? '')) !== '' || trim((string) ($row['solusi'] ?? '')) !== ''
                )),
                'mahasiswa_display' => $hasPeralatan ? $effMhs['display'] : '',
                'mahasiswa_ttd_base64' => $mhsTtdBase64,
                'dosen_ttd_base64' => $pertemuan->status === LogbookPertemuan::STATUS_APPROVED ? $dosenTtdBase64 : null,
            ];
        });

        $cleanMk = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $logbook->nama_mk);
        $fileName = 'FM_LAB_02_' . $cleanMk . ($request->filled('pertemuan_id') ? '_Minggu_' . $pertemuans->first()->minggu_ke : '_Full') . '.pdf';

        $pdf = Pdf::loadView('pages.logbook.pdf', compact(
            'logbook',
            'pages',
            'logoPensBase64',
            'plpTtdBase64'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream($fileName);
    }

    private function isPertemuanSignatureLocked(LogbookPertemuan $pertemuan): bool
    {
        return in_array($pertemuan->status, [
            LogbookPertemuan::STATUS_PENDING_DOSEN,
            LogbookPertemuan::STATUS_APPROVED,
        ], true);
    }

    private function authorizeLogbookAccess(LogbookPraktikum $logbook, bool $allowDosen = false): void
    {
        Gate::authorize($allowDosen ? 'view' : 'manage', $logbook);
    }

    private function validatePertemuan(Request $request): array
    {
        $validated = $request->validate([
            'minggu_ke' => ['required', 'integer', 'min:1', 'max:30'],
            'judul_praktikum' => ['required', 'string', 'max:255'],
            'tanggal_pelaksanaan' => ['required', 'date'],
            'jam_pelaksanaan' => ['required', 'string', 'max:30'],
            'peralatan' => ['nullable', 'array'],
            'peralatan.*.nama_alat' => ['nullable', 'string', 'max:200'],
            'peralatan.*.kategori' => ['nullable', 'string', 'max:50'],
            'peralatan.*.jumlah' => ['nullable', 'string', 'max:30'],
            'peralatan.*.kondisi' => ['nullable', 'string', 'max:30'],
            'aktivitas' => ['nullable', 'array'],
            'aktivitas.*' => ['nullable', 'string', 'max:1000'],
            'permasalahan' => ['nullable', 'array'],
            'permasalahan.*.masalah' => ['nullable', 'string', 'max:1000'],
            'permasalahan.*.solusi' => ['nullable', 'string', 'max:1000'],
            'mahasiswa_user_id' => ['nullable', 'exists:users,id'],
            'mahasiswa_nama' => ['nullable', 'string', 'max:150'],
            'mahasiswa_nrp' => ['nullable', 'string', 'max:50'],
        ]);

        $peralatan = collect($validated['peralatan'] ?? [])
            ->filter(fn ($row) => trim((string) ($row['nama_alat'] ?? '')) !== '')
            ->map(fn ($row) => [
                'nama_alat' => trim((string) $row['nama_alat']),
                'kategori' => trim((string) ($row['kategori'] ?? 'Alat 1')),
                'jumlah' => trim((string) ($row['jumlah'] ?? '1')),
                'kondisi' => trim((string) ($row['kondisi'] ?? 'Baik')),
            ])
            ->values()
            ->all();

        $aktivitas = collect($validated['aktivitas'] ?? [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        $permasalahan = collect($validated['permasalahan'] ?? [])
            ->filter(fn ($row) => trim((string) ($row['masalah'] ?? '')) !== '' || trim((string) ($row['solusi'] ?? '')) !== '')
            ->map(fn ($row) => [
                'masalah' => trim((string) ($row['masalah'] ?? '')),
                'solusi' => trim((string) ($row['solusi'] ?? '')),
            ])
            ->values()
            ->all();

        $mhsNama = trim((string) ($validated['mahasiswa_nama'] ?? ''));
        $mhsNrp = trim((string) ($validated['mahasiswa_nrp'] ?? ''));
        if (!empty($validated['mahasiswa_user_id']) && $mhsNama === '') {
            $mhsUser = User::find($validated['mahasiswa_user_id']);
            $mhsNama = $mhsUser?->nama_asli ?? '';
            $mhsNrp = $mhsNrp !== '' ? $mhsNrp : ($mhsUser?->nrp ?? '');
        }

        return [
            'minggu_ke' => (int) $validated['minggu_ke'],
            'judul_praktikum' => trim($validated['judul_praktikum']),
            'tanggal_pelaksanaan' => $validated['tanggal_pelaksanaan'],
            'jam_pelaksanaan' => trim($validated['jam_pelaksanaan']),
            'peralatan' => $peralatan,
            'aktivitas' => $aktivitas,
            'permasalahan' => $permasalahan,
            'mahasiswa_user_id' => $validated['mahasiswa_user_id'] ?? null,
            'mahasiswa_nama' => $mhsNama !== '' ? $mhsNama : null,
            'mahasiswa_nrp' => $mhsNrp !== '' ? $mhsNrp : null,
        ];
    }

    private function processSubmit(LogbookPertemuan $pertemuan, LogbookPraktikum $logbook, User $actor): string
    {
        $dosen = $logbook->dosen;
        $now = now('Asia/Jakarta');

        $canAutoApprove = $dosen
            && $dosen->hasApprovedSignature()
            && DosenLogbookAutoApprove::isActiveFor($dosen->id, $logbook->plp_id);

        if ($canAutoApprove) {
            $pertemuan->update([
                'status' => LogbookPertemuan::STATUS_APPROVED,
                'approval_mode' => 'auto',
                'submitted_at' => $now,
                'approved_at' => $now,
                'rejection_note' => null,
                'revoked_at' => null,
                'revoked_reason' => null,
            ]);

            $dosen->notify(new LogbookNotification($pertemuan, 'auto_approved', $actor));
            return 'auto';
        }

        $pertemuan->update([
            'status' => LogbookPertemuan::STATUS_PENDING_DOSEN,
            'approval_mode' => null,
            'submitted_at' => $now,
            'rejection_note' => null,
        ]);

        if ($dosen) {
            $dosen->notify(new LogbookNotification($pertemuan, 'submitted', $actor));
        }

        return 'manual';
    }

    private function storeBase64Png(string $base64Data): ?string
    {
        return app(SignatureStorageService::class)->storeLogbookBase64Signature($base64Data);
    }

    private function copyProfileSignatureToLogbook(string $profileSignaturePath): ?string
    {
        return app(SignatureStorageService::class)->copyUserSignatureToLogbook($profileSignaturePath);
    }

    private function findSignatureAbsolutePath(?string $filename): ?string
    {
        return app(SignatureStorageService::class)->resolveUserSignaturePath($filename);
    }

    private function userSignatureToBase64(?string $filename): ?string
    {
        return SignatureStorageService::userSignatureDataUri($filename);
    }

    private function fileToBase64(?string $path): ?string
    {
        return app(SignatureStorageService::class)->fileToDataUri($path);
    }
}
