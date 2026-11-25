<?php

namespace App\Http\Controllers;

use App\Models\InventarisRuangan;
use App\Models\Lab;
use App\Models\LabManager;
use App\Models\PeminjamanAlat;
use App\Models\PeminjamanAlatDetail;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PeminjamanAlatNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class PeminjamanAlatController extends Controller
{
    /**
     * Tampilan daftar peminjaman alat milik mahasiswa yang sedang login.
     */
    public function index(Request $request)
    {
        $title = 'Peminjaman Alat Laboratorium';
        $user = Auth::user();

        // Data ruangan / lab untuk pilihan lokasi
        $labs = Lab::orderBy('nama_lab')->get();

        if ($request->ajax()) {
            $query = PeminjamanAlat::with(['lab', 'plp', 'details.inventarisRuangan.lab'])
                ->where('mahasiswa_id', $user->id)
                ->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('kode_peminjaman', function ($row) {
                    return '<strong>' . e($row->kode_peminjaman) . '</strong>';
                })
                ->addColumn('lab', function ($row) {
                    return $row->lab ? e($row->lab->nama_lab) : '<span class="text-muted">-</span>';
                })
                ->addColumn('daftar_alat', function ($row) {
                    $details = $row->details;
                    $total = $details->count();
                    $shown = $details->take(2);
                    $remaining = $total - 2;

                    $html = '<ul class="list-unstyled mb-0">';
                    foreach ($shown as $d) {
                        $badge = $d->is_manual
                            ? ' <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Input Manual</span>'
                            : '';
                        $html .= '<li>• ' . e($d->nama_alat) . ' (' . $d->jumlah . ' ' . e($d->satuan) . ')' . $badge . '</li>';
                    }

                    if ($remaining > 0) {
                        $html .= '<li class="text-muted fst-italic" style="font-size:0.8rem;">• .... (+' . $remaining . ' alat lainnya)</li>';
                    }

                    $html .= '</ul>';
                    return $html;
                })
                ->addColumn('keperluan', function ($row) {
                    return e($row->keperluan);
                })
                ->addColumn('waktu_pinjam', function ($row) {
                    return $row->waktu_pinjam ? $row->waktu_pinjam->format('d-m-Y H:i') : '-';
                })
                ->addColumn('rencana_kembali', function ($row) {
                    return $row->rencana_kembali ? $row->rencana_kembali->format('d-m-Y H:i') : '-';
                })
                ->addColumn('status', function ($row) {
                    return $row->status_badge;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-info detailBtn" data-id="' . $row->id . '"><i class="bi bi-eye"></i> Detail</button>';

                    if ($row->status === 'pending_plp') {
                        $btn .= '<button type="button" class="btn btn-sm btn-danger cancelBtn ms-1" data-id="' . $row->id . '"><i class="bi bi-x-circle"></i> Batal</button>';
                    }

                    if (in_array($row->status, ['disetujui', 'sedang_dipinjam', 'selesai'])) {
                        $btn .= '<a href="' . route('peminjaman-alat.cetak', $row->id) . '" target="_blank" class="btn btn-sm btn-secondary ms-1"><i class="bi bi-printer"></i> Cetak</a>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['kode_peminjaman', 'lab', 'daftar_alat', 'status', 'action'])
                ->make(true);
        }

        // Ambil pengaturan kebijakan batas peminjaman alat
        $maxItems       = Setting::int(Setting::MAX_ITEMS_PER_REQUEST, 'global', 5);
        $maxLoanDays    = Setting::int(Setting::MAX_LOAN_DAYS, 'global', 7);
        $maxActiveLoans = Setting::int(Setting::MAX_ACTIVE_LOANS, 'global', 2);
        $allowManual    = Setting::boolean(Setting::ALLOW_MANUAL_INPUT, 'global', true);

        return view('pages.peminjaman_alat.index', compact(
            'title',
            'labs',
            'maxItems',
            'maxLoanDays',
            'maxActiveLoans',
            'allowManual'
        ));
    }

    /**
     * Parsing string NUP (koma atau rentang angka seperti 1-5) menjadi array NUP individual.
     */
    private function parseNupList(?string $nupStr): array
    {
        if (empty($nupStr)) {
            return [];
        }

        $result = [];
        $parts = preg_split('/\s*,\s*/', trim($nupStr), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^(\d+)\s*[-–]\s*(\d+)$/', $part, $matches)) {
                $start = (int) $matches[1];
                $end = (int) $matches[2];
                $padLen = max(strlen($matches[1]), strlen($matches[2]));
                if ($start <= $end && ($end - $start) < 200) {
                    for ($i = $start; $i <= $end; $i++) {
                        $result[] = str_pad($i, $padLen, '0', STR_PAD_LEFT);
                    }
                    continue;
                }
            }
            $result[] = $part;
        }

        return array_values(array_unique($result));
    }

    /**
     * Ambil daftar alat inventaris per NUP (1 NUP = 1 unit) berdasarkan ruangan (AJAX).
     */
    public function getAlatByLab($labId)
    {
        $rawItems = InventarisRuangan::where('lab_id', $labId)
            ->where('is_bisa_dipinjam', true)
            ->where('kondisi', '!=', 'rusak_berat')
            ->orderBy('nama_barang')
            ->get();

        // Cari NUP yang sedang dalam peminjaman aktif di lab ini
        $borrowedUnits = PeminjamanAlatDetail::whereHas('peminjamanAlat', function ($q) use ($labId) {
            $q->where('lab_id', $labId)
              ->whereIn('status', ['pending_plp', 'disetujui', 'sedang_dipinjam']);
        })
        ->whereNotNull('inventaris_ruangan_id')
        ->whereNotNull('nup')
        ->get(['inventaris_ruangan_id', 'nup'])
        ->map(fn($row) => $row->inventaris_ruangan_id . '__' . trim($row->nup))
        ->toArray();

        $expandedItems = [];

        foreach ($rawItems as $item) {
            $nups = $this->parseNupList($item->nup);

            if (count($nups) > 0) {
                foreach ($nups as $singleNup) {
                    $key = $item->id . '__' . $singleNup;
                    $isBorrowed = in_array($key, $borrowedUnits);

                    $expandedItems[] = [
                        'unique_id' => $key,
                        'inventaris_ruangan_id' => $item->id,
                        'nama_barang' => $item->nama_barang,
                        'kode_barang' => $item->kode_barang,
                        'nup' => $singleNup,
                        'satuan' => $item->satuan ?? 'Unit',
                        'spesifikasi_merk_tipe' => $item->spesifikasi_merk_tipe,
                        'kondisi' => $item->kondisi,
                        'is_dipinjam' => $isBorrowed,
                    ];
                }
            } else {
                $key = $item->id . '__none';
                $isBorrowed = in_array($key, $borrowedUnits);

                $expandedItems[] = [
                    'unique_id' => $key,
                    'inventaris_ruangan_id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kode_barang' => $item->kode_barang,
                    'nup' => '-',
                    'satuan' => $item->satuan ?? 'Unit',
                    'spesifikasi_merk_tipe' => $item->spesifikasi_merk_tipe,
                    'kondisi' => $item->kondisi,
                    'is_dipinjam' => $isBorrowed,
                ];
            }
        }

        return response()->json($expandedItems);
    }

    /**
     * Simpan pengajuan peminjaman alat (Multi-item: Master DIR + Input Manual).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi Tanda Tangan Mahasiswa
        if ($user->signature_path == null || $user->signature_status !== 'approved') {
            if ($user->signature_status === 'pending') {
                $msg = 'Tanda tangan digital Anda masih menunggu verifikasi Admin.';
            } elseif ($user->signature_status === 'rejected') {
                $msg = 'Tanda tangan digital Anda ditolak oleh Admin. Harap unggah ulang di profil.';
            } else {
                $msg = 'Anda belum memiliki tanda tangan digital yang disetujui. Harap unggah tanda tangan di profil.';
            }
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        // 2. Validasi Input Form
        $request->validate([
            'lab_id' => 'required|exists:labs,id',
            'keperluan' => 'required|string|max:1000',
            'waktu_pinjam' => 'required|date',
            'rencana_kembali' => 'required|date|after:waktu_pinjam',
            'items' => 'required|array|min:1',
            'items.*.is_manual' => 'required|in:0,1',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.satuan' => 'nullable|string|max:20',
            'items.*.spesifikasi' => 'nullable|string|max:500',
            'items.*.keterangan' => 'nullable|string|max:500',
        ], [
            'lab_id.required' => 'Harap pilih laboratorium / ruangan asal alat.',
            'keperluan.required' => 'Keperluan peminjaman wajib diisi.',
            'waktu_pinjam.required' => 'Waktu pinjam wajib diisi.',
            'rencana_kembali.required' => 'Rencana pengembalian wajib diisi.',
            'rencana_kembali.after' => 'Rencana pengembalian harus setelah waktu pinjam.',
            'items.required' => 'Harap masukkan minimal satu alat yang ingin dipinjam.',
            'items.min' => 'Harap masukkan minimal satu alat yang ingin dipinjam.',
        ]);

        // 2a. Validasi Batas Maksimal Peminjaman Aktif Mahasiswa
        $maxActiveLoans = Setting::int(Setting::MAX_ACTIVE_LOANS, 'global', 2);
        $activeLoansCount = PeminjamanAlat::where('mahasiswa_id', $user->id)
            ->whereIn('status', ['pending_plp', 'disetujui', 'sedang_dipinjam'])
            ->count();

        if ($activeLoansCount >= $maxActiveLoans) {
            return response()->json([
                'success' => false,
                'message' => "Anda telah mencapai batas maksimal ({$maxActiveLoans}) peminjaman alat yang masih aktif/berjalan. Harap selesaikan pengembalian alat sebelumnya terlebih dahulu.",
            ], 422);
        }

        // 2b. Validasi Batas Durasi Peminjaman
        $maxLoanDays = Setting::int(Setting::MAX_LOAN_DAYS, 'global', 7);
        $waktuPinjam = Carbon::parse($request->waktu_pinjam);
        $rencanaKembali = Carbon::parse($request->rencana_kembali);
        $diffDays = $waktuPinjam->floatDiffInDays($rencanaKembali);

        if ($diffDays > $maxLoanDays) {
            return response()->json([
                'success' => false,
                'message' => "Durasi peminjaman tidak boleh melebihi batas maksimal {$maxLoanDays} hari (rencana Anda: " . round($diffDays, 1) . " hari).",
            ], 422);
        }

        // 2c. Validasi Batas Maksimal Alat per Pengajuan
        $maxItems = Setting::int(Setting::MAX_ITEMS_PER_REQUEST, 'global', 5);
        $itemsData = $request->input('items', []);
        if (count($itemsData) > $maxItems) {
            return response()->json([
                'success' => false,
                'message' => "Jumlah alat yang diajukan (" . count($itemsData) . " item) melebihi batas maksimal yang diperbolehkan ({$maxItems} item per pengajuan).",
            ], 422);
        }

        // 2d. Validasi Izin Opsi Input Manual
        $allowManual = Setting::boolean(Setting::ALLOW_MANUAL_INPUT, 'global', true);
        if (!$allowManual) {
            foreach ($itemsData as $item) {
                if (!empty($item['is_manual'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Opsi "Input Manual" sedang dinonaktifkan oleh Administrator. Mahasiswa hanya dapat meminjam alat yang terdaftar di Inventaris Ruangan.',
                    ], 422);
                }
            }
        }

        // Cek validasi per item & kumpulkan ID DIR untuk pessimistic locking
        $selectedUnits = [];
        $parsedItems = [];

        foreach ($itemsData as $index => $item) {
            $isManual = (bool) ($item['is_manual'] ?? false);
            if ($isManual) {
                if (empty(trim($item['nama_alat'] ?? ''))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nama alat pada baris ke-' . ($index + 1) . ' wajib diisi karena menggunakan opsi Input Manual.',
                    ], 422);
                }

                $parsedItems[] = [
                    'is_manual' => true,
                    'nama_alat' => trim($item['nama_alat']),
                    'jumlah' => (int) $item['jumlah'],
                    'satuan' => !empty($item['satuan']) ? $item['satuan'] : 'Unit',
                    'spesifikasi' => $item['spesifikasi'] ?? null,
                    'keterangan' => $item['keterangan'] ?? null,
                ];
            } else {
                if (empty($item['inventaris_ruangan_id'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Harap pilih alat dari inventaris pada baris ke-' . ($index + 1) . '.',
                    ], 422);
                }

                $rawId = (string) $item['inventaris_ruangan_id'];
                $selectedNup = !empty($item['nup']) ? trim((string) $item['nup']) : null;

                if (str_contains($rawId, '__')) {
                    [$dirId, $embeddedNup] = explode('__', $rawId, 2);
                    if (!$selectedNup || $selectedNup === '-') {
                        $selectedNup = ($embeddedNup !== 'none') ? trim($embeddedNup) : null;
                    }
                } else {
                    $dirId = $rawId;
                }

                $dirId = (int) $dirId;
                $normalizedNup = ($selectedNup && $selectedNup !== '-') ? $selectedNup : null;
                $unitKey = $dirId . '__' . ($normalizedNup ?? 'none');

                if (in_array($unitKey, $selectedUnits, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Alat dengan NUP yang sama dipilih lebih dari satu kali pada baris pengajuan Anda.',
                    ], 422);
                }
                $selectedUnits[] = $unitKey;

                $parsedItems[] = [
                    'is_manual' => false,
                    'dir_id' => $dirId,
                    'selected_nup' => $normalizedNup,
                    'unit_key' => $unitKey,
                    'spesifikasi' => $item['spesifikasi'] ?? null,
                    'keterangan' => $item['keterangan'] ?? null,
                ];
            }
        }

        // 3. Tentukan Ruangan & PLP Penanggung Jawab
        $labId = (int) $request->lab_id;
        $plpId = null;

        $labManager = LabManager::where('lab_id', $labId)->first();
        if ($labManager) {
            $plpId = $labManager->plp_id;
        }

        $dirIds = collect($parsedItems)
            ->where('is_manual', false)
            ->pluck('dir_id')
            ->unique()
            ->sort()
            ->values()
            ->all();

        // 4. Simpan ke Database dengan Transaksi & Pessimistic Locking (lockForUpdate)
        try {
            $peminjaman = DB::transaction(function () use ($user, $request, $parsedItems, $dirIds, $labId, $plpId, $maxActiveLoans) {
                User::where('id', $user->id)->lockForUpdate()->first();

                $lockedActiveCount = PeminjamanAlat::where('mahasiswa_id', $user->id)
                    ->whereIn('status', ['pending_plp', 'disetujui', 'sedang_dipinjam'])
                    ->lockForUpdate()
                    ->count();

                if ($lockedActiveCount >= $maxActiveLoans) {
                    throw ValidationException::withMessages([
                        'items' => ["Anda telah mencapai batas maksimal ({$maxActiveLoans}) peminjaman alat yang masih aktif/berjalan. Harap selesaikan pengembalian alat sebelumnya terlebih dahulu."],
                    ]);
                }

                $lockedDirs = collect();
                $lockedActiveUnits = [];

                if (!empty($dirIds)) {
                    $lockedDirs = InventarisRuangan::whereIn('id', $dirIds)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    $lockedActiveUnits = PeminjamanAlatDetail::query()
                        ->join('peminjaman_alats', 'peminjaman_alat_details.peminjaman_alat_id', '=', 'peminjaman_alats.id')
                        ->whereIn('peminjaman_alat_details.inventaris_ruangan_id', $dirIds)
                        ->whereIn('peminjaman_alats.status', ['pending_plp', 'disetujui', 'sedang_dipinjam'])
                        ->lockForUpdate()
                        ->get(['peminjaman_alat_details.inventaris_ruangan_id', 'peminjaman_alat_details.nup'])
                        ->map(function ($row) {
                            $nup = trim((string) ($row->nup ?? ''));
                            return $row->inventaris_ruangan_id . '__' . ($nup !== '' && $nup !== '-' ? $nup : 'none');
                        })
                        ->all();
                }

                $detailsPayload = [];
                foreach ($parsedItems as $item) {
                    if ($item['is_manual']) {
                        $detailsPayload[] = [
                            'inventaris_ruangan_id' => null,
                            'nama_alat' => $item['nama_alat'],
                            'kode_barang' => null,
                            'nup' => null,
                            'jumlah' => $item['jumlah'],
                            'satuan' => $item['satuan'],
                            'is_manual' => true,
                            'spesifikasi' => $item['spesifikasi'],
                            'keterangan' => $item['keterangan'],
                            'kondisi_pinjam' => 'baik',
                        ];
                        continue;
                    }

                    /** @var \App\Models\InventarisRuangan|null $dirItem */
                    $dirItem = $lockedDirs->get($item['dir_id']);
                    if (!$dirItem || (int) $dirItem->lab_id !== $labId) {
                        throw ValidationException::withMessages([
                            'items' => ['Alat inventaris tidak ditemukan pada laboratorium yang dipilih.'],
                        ]);
                    }

                    if (!$dirItem->is_bisa_dipinjam || $dirItem->kondisi === 'rusak_berat') {
                        throw ValidationException::withMessages([
                            'items' => ["Alat \"{$dirItem->nama_barang}\" saat ini tidak dapat dipinjam (status non-pinjam atau rusak berat)."],
                        ]);
                    }

                    $availableNups = $this->parseNupList($dirItem->nup);
                    $finalNup = $item['selected_nup'];
                    if (!empty($availableNups)) {
                        if ($finalNup === null || !in_array($finalNup, $availableNups, true)) {
                            throw ValidationException::withMessages([
                                'items' => ["NUP alat \"{$dirItem->nama_barang}\" tidak valid untuk inventaris ruangan ini."],
                            ]);
                        }
                    } elseif ($finalNup === null && !empty($dirItem->nup) && trim($dirItem->nup) !== '-') {
                        $finalNup = trim($dirItem->nup);
                    }

                    $conflictKey = $dirItem->id . '__' . ($finalNup ?? 'none');
                    if (in_array($conflictKey, $lockedActiveUnits, true)) {
                        $nupLabel = $finalNup ?? '-';
                        throw ValidationException::withMessages([
                            'items' => ["Unit {$dirItem->nama_barang} (NUP: {$nupLabel}) baru saja dipinjam atau sedang menunggu persetujuan. Silakan pilih NUP lain."],
                        ]);
                    }
                    $lockedActiveUnits[] = $conflictKey;

                    $detailsPayload[] = [
                        'inventaris_ruangan_id' => $dirItem->id,
                        'nama_alat' => $dirItem->nama_barang,
                        'kode_barang' => $dirItem->kode_barang,
                        'nup' => $finalNup,
                        'jumlah' => 1,
                        'satuan' => $dirItem->satuan ?? 'Unit',
                        'is_manual' => false,
                        'spesifikasi' => $dirItem->spesifikasi_merk_tipe ?? $item['spesifikasi'],
                        'keterangan' => $item['keterangan'],
                        'kondisi_pinjam' => $dirItem->kondisi ?? 'baik',
                    ];
                }

                // Generate kode peminjaman unik secara aman di dalam transaksi
                $prefix = 'ALAT-' . now()->format('Ymd') . '-';
                $lastCode = PeminjamanAlat::where('kode_peminjaman', 'like', $prefix . '%')
                    ->orderByDesc('kode_peminjaman')
                    ->lockForUpdate()
                    ->value('kode_peminjaman');
                $nextSeq = $lastCode ? ((int) substr($lastCode, strlen($prefix))) + 1 : 1;
                $kodePeminjaman = sprintf('%s%03d', $prefix, $nextSeq);

                $peminjaman = PeminjamanAlat::create([
                    'kode_peminjaman' => $kodePeminjaman,
                    'mahasiswa_id' => $user->id,
                    'lab_id' => $labId,
                    'plp_id' => $plpId,
                    'keperluan' => $request->keperluan,
                    'waktu_pinjam' => $request->waktu_pinjam,
                    'rencana_kembali' => $request->rencana_kembali,
                    'status' => 'pending_plp',
                    'ttd_mahasiswa_file' => $user->signature_path,
                ]);

                foreach ($detailsPayload as $detailData) {
                    $detailData['peminjaman_alat_id'] = $peminjaman->id;
                    PeminjamanAlatDetail::create($detailData);
                }

                return $peminjaman;
            });

            // Kirim notifikasi pengajuan baru ke PLP ruangan
            try {
                if ($plpId) {
                    $plpUser = User::find($plpId);
                    if ($plpUser) {
                        $plpUser->notify(new PeminjamanAlatNotification($peminjaman, 'create', $user));
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('PeminjamanAlatController: Gagal kirim notifikasi peminjaman baru - ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman alat berhasil dikirim dan menunggu persetujuan PLP.',
                'kode' => $peminjaman->kode_peminjaman,
            ], 200);

        } catch (ValidationException $e) {
            $firstMessage = collect($e->errors())->flatten()->first() ?? 'Data pengajuan alat tidak valid.';
            return response()->json([
                'success' => false,
                'message' => $firstMessage,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan pengajuan.',
            ], 500);
        }
    }

    /**
     * Tampilkan detail peminjaman alat untuk modal AJAX.
     */
    public function show($id)
    {
        $peminjaman = PeminjamanAlat::with([
            'mahasiswa.prodi',
            'lab',
            'plp',
            'details.inventarisRuangan.lab'
        ])->findOrFail($id);

        Gate::authorize('view', $peminjaman);

        $items = $peminjaman->details->map(function ($detail) {
            $labName = '-';
            if ($detail->inventarisRuangan && $detail->inventarisRuangan->lab) {
                $labName = $detail->inventarisRuangan->lab->nama_lab;
            }

            return [
                'id' => $detail->id,
                'nama_alat' => $detail->nama_alat,
                'kode_barang' => $detail->kode_barang ?? '-',
                'nup' => $detail->nup ?? '-',
                'jumlah' => $detail->jumlah,
                'satuan' => $detail->satuan,
                'is_manual' => (bool) $detail->is_manual,
                'spesifikasi' => $detail->spesifikasi ?? '-',
                'keterangan' => $detail->keterangan ?? '-',
                'kondisi_pinjam' => ucfirst(str_replace('_', ' ', $detail->kondisi_pinjam ?? 'baik')),
                'kondisi_kembali' => $detail->kondisi_kembali ? ucfirst(str_replace('_', ' ', $detail->kondisi_kembali)) : '-',
                'lokasi_lab' => $labName,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $peminjaman->id,
                'kode_peminjaman' => $peminjaman->kode_peminjaman,
                'mahasiswa_nama' => $peminjaman->mahasiswa->nama_asli ?? '-',
                'mahasiswa_nrp' => $peminjaman->mahasiswa->nrp ?? '-',
                'mahasiswa_prodi' => $peminjaman->mahasiswa->prodi->nama_prodi ?? '-',
                'lab_nama' => $peminjaman->lab ? $peminjaman->lab->nama_lab : '-',
                'plp_nama' => $peminjaman->plp ? ($peminjaman->plp->nama_asli ?? $peminjaman->plp->name) : 'Belum ditentukan',
                'keperluan' => $peminjaman->keperluan,
                'waktu_pinjam' => $peminjaman->waktu_pinjam ? $peminjaman->waktu_pinjam->format('d-m-Y H:i') : '-',
                'rencana_kembali' => $peminjaman->rencana_kembali ? $peminjaman->rencana_kembali->format('d-m-Y H:i') : '-',
                'waktu_kembali' => $peminjaman->waktu_kembali ? $peminjaman->waktu_kembali->format('d-m-Y H:i') : '-',
                'status' => $peminjaman->status,
                'status_badge' => $peminjaman->status_badge,
                'catatan_penolakan' => $peminjaman->catatan_penolakan ?? '-',
                'alasan_batal' => $peminjaman->alasan_batal ?? '-',
                'tgl_ttd_plp' => $peminjaman->tgl_ttd_plp ? $peminjaman->tgl_ttd_plp->format('d-m-Y H:i') : '-',
                'ttd_mahasiswa_file' => $peminjaman->ttd_mahasiswa_file,
                'ttd_plp_file' => $peminjaman->ttd_plp_file,
                'kondisi_kembali_catatan' => $peminjaman->kondisi_kembali_catatan ?? '-',
                'items' => $items,
            ]
        ]);
    }

    /**
     * Batalkan peminjaman oleh Mahasiswa (hanya jika masih pending).
     */
    public function cancel(Request $request, $id)
    {
        $peminjaman = PeminjamanAlat::findOrFail($id);

        Gate::authorize('cancel', $peminjaman);

        if ($peminjaman->status !== 'pending_plp') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak dapat dibatalkan karena sudah diproses atau selesai.'
            ], 422);
        }

        $peminjaman->update([
            'status' => 'dibatalkan',
            'alasan_batal' => $request->input('alasan', 'Dibatalkan oleh mahasiswa.'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan peminjaman alat berhasil dibatalkan.'
        ]);
    }

    /**
     * Halaman persetujuan peminjaman alat untuk PLP dan Admin.
     */
    public function approvalIndex(Request $request)
    {
        $title = 'Persetujuan Peminjaman Alat Lab';
        $user = Auth::user();

        if ($request->ajax()) {
            $query = PeminjamanAlat::with([
                'mahasiswa',
                'lab',
                'plp',
                'details.inventarisRuangan.lab'
            ])->orderBy('created_at', 'desc');

            // Jika PLP, tampilkan pengajuan untuk lab yang ditangani atau yang diarahkan ke dirinya
            if ($user->role_id === 3 || $user->jabatan_id === 4) {
                $managedLabIds = LabManager::where('plp_id', $user->id)->pluck('lab_id')->toArray();

                $query->where(function ($q) use ($user, $managedLabIds) {
                    $q->where('plp_id', $user->id)
                      ->orWhereIn('lab_id', $managedLabIds)
                      ->orWhereHas('details.inventarisRuangan', function ($sub) use ($managedLabIds) {
                          $sub->whereIn('lab_id', $managedLabIds);
                      })
                      ->orWhereNull('plp_id'); // Peminjaman manual yang belum ada PLP spesifik
                });
            }

            // Filter status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('kode_peminjaman', function ($row) {
                    return '<strong>' . e($row->kode_peminjaman) . '</strong>';
                })
                ->addColumn('mahasiswa', function ($row) {
                    return e($row->mahasiswa->nama_asli ?? '-') . '<br><small class="text-muted">' . e($row->mahasiswa->nrp ?? '-') . '</small>';
                })
                ->addColumn('lab', function ($row) {
                    return $row->lab ? e($row->lab->nama_lab) : '<span class="text-muted">-</span>';
                })
                ->addColumn('daftar_alat', function ($row) {
                    $details = $row->details;
                    $total = $details->count();
                    $shown = $details->take(2);
                    $remaining = $total - 2;

                    $html = '<ul class="list-unstyled mb-0">';
                    foreach ($shown as $d) {
                        $badge = $d->is_manual
                            ? ' <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Input Manual</span>'
                            : '';
                        $html .= '<li>• ' . e($d->nama_alat) . ' (' . $d->jumlah . ' ' . e($d->satuan) . ')' . $badge . '</li>';
                    }

                    if ($remaining > 0) {
                        $html .= '<li class="text-muted fst-italic" style="font-size:0.8rem;">• .... (+' . $remaining . ' alat lainnya)</li>';
                    }

                    $html .= '</ul>';
                    return $html;
                })
                ->addColumn('waktu_pinjam', function ($row) {
                    return $row->waktu_pinjam ? $row->waktu_pinjam->format('d-m-Y H:i') : '-';
                })
                ->addColumn('rencana_kembali', function ($row) {
                    return $row->rencana_kembali ? $row->rencana_kembali->format('d-m-Y H:i') : '-';
                })
                ->addColumn('status', function ($row) {
                    return $row->status_badge;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-inline-flex flex-nowrap gap-1 align-items-center">';
                    $btn .= '<button type="button" class="btn btn-sm btn-info detailBtn text-white" data-id="' . $row->id . '" title="Lihat Detail"><i class="bi bi-eye"></i> Detail</button>';

                    if ($row->status === 'pending_plp') {
                        $btn .= '<button type="button" class="btn btn-sm btn-success approveBtn" data-id="' . $row->id . '" title="Setujui Peminjaman"><i class="bi bi-check-circle"></i> Setujui</button>';
                        $btn .= '<button type="button" class="btn btn-sm btn-danger rejectBtn" data-id="' . $row->id . '" title="Tolak Peminjaman"><i class="bi bi-x-circle"></i> Tolak</button>';
                    } elseif ($row->status === 'disetujui' || $row->status === 'sedang_dipinjam') {
                        $btn .= '<button type="button" class="btn btn-sm btn-primary returnBtn" data-id="' . $row->id . '" title="Proses Pengembalian"><i class="bi bi-arrow-return-left"></i> Pengembalian</button>';
                    }

                    if (in_array($row->status, ['disetujui', 'sedang_dipinjam', 'selesai'])) {
                        $btn .= '<a href="' . route('peminjaman-alat.cetak', $row->id) . '" target="_blank" class="btn btn-sm btn-secondary" title="Cetak Bukti Peminjaman"><i class="bi bi-printer"></i> Cetak</a>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['kode_peminjaman', 'mahasiswa', 'lab', 'daftar_alat', 'status', 'action'])
                ->make(true);
        }

        return view('pages.peminjaman_alat.approval', compact('title'));
    }

    /**
     * Persetujuan pengajuan peminjaman alat oleh PLP.
     */
    public function approve(Request $request, $id)
    {
        $peminjaman = PeminjamanAlat::findOrFail($id);
        Gate::authorize('manageApproval', $peminjaman);
        $user = Auth::user();

        // Validasi TTD PLP
        if ($user->signature_path == null || $user->signature_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki tanda tangan digital yang disetujui Admin. Harap upload dan pastikan TTD disetujui di profil.'
            ], 422);
        }

        if ($peminjaman->status !== 'pending_plp') {
            return response()->json([
                'success' => false,
                'message' => 'Status pengajuan sudah tidak dalam status menunggu persetujuan PLP.'
            ], 422);
        }

        $peminjaman->update([
            'status' => 'disetujui',
            'plp_id' => $user->id,
            'ttd_plp_file' => $user->signature_path,
            'tgl_ttd_plp' => now(),
        ]);

        // Notifikasi ke mahasiswa
        try {
            if ($peminjaman->mahasiswa) {
                $peminjaman->mahasiswa->notify(new PeminjamanAlatNotification($peminjaman, 'disetujui', $user));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PeminjamanAlatController: Gagal kirim notifikasi approval - ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman alat berhasil disetujui.'
        ]);
    }

    /**
     * Penolakan pengajuan peminjaman alat oleh PLP.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_penolakan' => 'required|string|max:500',
        ], [
            'catatan_penolakan.required' => 'Harap berikan alasan/catatan penolakan.',
        ]);

        $peminjaman = PeminjamanAlat::findOrFail($id);
        Gate::authorize('manageApproval', $peminjaman);
        $user = Auth::user();

        if ($peminjaman->status !== 'pending_plp') {
            return response()->json([
                'success' => false,
                'message' => 'Status pengajuan sudah tidak dapat ditolak.'
            ], 422);
        }

        $peminjaman->update([
            'status' => 'ditolak',
            'plp_id' => $user->id,
            'catatan_penolakan' => $request->catatan_penolakan,
        ]);

        // Notifikasi ke mahasiswa
        try {
            if ($peminjaman->mahasiswa) {
                $peminjaman->mahasiswa->notify(new PeminjamanAlatNotification($peminjaman, 'ditolak', $user, $request->catatan_penolakan));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PeminjamanAlatController: Gagal kirim notifikasi penolakan - ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan peminjaman alat berhasil ditolak.'
        ]);
    }

    /**
     * Konfirmasi Pengembalian Alat oleh PLP / Admin.
     */
    public function kembalikan(Request $request, $id)
    {
        $peminjaman = PeminjamanAlat::findOrFail($id);
        Gate::authorize('manageApproval', $peminjaman);

        if (!in_array($peminjaman->status, ['disetujui', 'sedang_dipinjam'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peminjaman berstatus disetujui atau sedang dipinjam yang dapat dikonfirmasi pengembaliannya.'
            ], 422);
        }

        $peminjaman->update([
            'status' => 'selesai',
            'waktu_kembali' => now(),
            'kondisi_kembali_catatan' => $request->input('catatan', 'Alat telah dikembalikan dalam kondisi lengkap.'),
        ]);

        // Notifikasi ke mahasiswa
        try {
            if ($peminjaman->mahasiswa) {
                $peminjaman->mahasiswa->notify(new PeminjamanAlatNotification($peminjaman, 'selesai', Auth::user()));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PeminjamanAlatController: Gagal kirim notifikasi pengembalian - ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengembalian alat berhasil dikonfirmasi dan status diselesaikan.'
        ]);
    }

    /**
     * Cetak PDF Bukti Peminjaman Alat.
     */
    public function cetak($id)
    {
        $peminjaman = PeminjamanAlat::with([
            'mahasiswa.prodi',
            'lab',
            'plp',
            'details.inventarisRuangan.lab'
        ])->findOrFail($id);

        Gate::authorize('downloadPdf', $peminjaman);

        $pdf = Pdf::loadView('pages.peminjaman_alat.cetak', compact('peminjaman'));
        return $pdf->stream('Peminjaman_Alat_' . $peminjaman->kode_peminjaman . '.pdf');
    }
}

