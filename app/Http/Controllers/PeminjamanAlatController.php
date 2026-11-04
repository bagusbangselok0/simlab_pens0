<?php

namespace App\Http\Controllers;

use App\Models\InventarisRuangan;
use App\Models\Lab;
use App\Models\LabManager;
use App\Models\PeminjamanAlat;
use App\Models\PeminjamanAlatDetail;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                    $html = '<ul class="list-unstyled mb-0">';
                    foreach ($row->details as $d) {
                        $badge = $d->is_manual
                            ? ' <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Input Manual</span>'
                            : '';
                        $html .= '<li>• ' . e($d->nama_alat) . ' (' . $d->jumlah . ' ' . e($d->satuan) . ')' . $badge . '</li>';
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

        return view('pages.peminjaman_alat.index', compact('title', 'labs'));
    }

    /**
     * Ambil daftar alat inventaris yang bisa dipinjam berdasarkan ruangan (AJAX).
     */
    public function getAlatByLab($labId)
    {
        $items = InventarisRuangan::where('lab_id', $labId)
            ->where('is_bisa_dipinjam', true)
            ->where('kondisi', '!=', 'rusak_berat')
            ->orderBy('nama_barang')
            ->get(['id', 'nama_barang', 'kode_barang', 'nup', 'satuan', 'spesifikasi_merk_tipe', 'kondisi']);

        return response()->json($items);
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

        // Cek validasi per item
        $itemsData = $request->input('items', []);
        foreach ($itemsData as $index => $item) {
            $isManual = (bool) ($item['is_manual'] ?? false);
            if ($isManual) {
                if (empty(trim($item['nama_alat'] ?? ''))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nama alat pada baris ke-' . ($index + 1) . ' wajib diisi karena menggunakan opsi Input Manual.',
                    ], 422);
                }
            } else {
                if (empty($item['inventaris_ruangan_id'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Harap pilih alat dari inventaris pada baris ke-' . ($index + 1) . '.',
                    ], 422);
                }
            }
        }

        // 3. Tentukan Ruangan & PLP Penanggung Jawab
        $labId = $request->lab_id;
        $plpId = null;

        // Jika lab_id belum ditentukan, coba ambil dari item pertama yang berasal dari DIR
        if (!$labId) {
            foreach ($itemsData as $item) {
                if (empty($item['is_manual']) && !empty($item['inventaris_ruangan_id'])) {
                    $dirItem = InventarisRuangan::find($item['inventaris_ruangan_id']);
                    if ($dirItem && $dirItem->lab_id) {
                        $labId = $dirItem->lab_id;
                        break;
                    }
                }
            }
        }

        // Cari PLP penanggung jawab ruangan tersebut
        if ($labId) {
            $labManager = LabManager::where('lab_id', $labId)->first();
            if ($labManager) {
                $plpId = $labManager->plp_id;
            }
        }

        // 4. Generate Nomor/Kode Peminjaman Unik (Contoh: ALAT-20261104-001)
        $todayStr = now()->format('Ymd');
        $todayCount = PeminjamanAlat::whereDate('created_at', today())->count();
        $kodePeminjaman = sprintf('ALAT-%s-%03d', $todayStr, $todayCount + 1);

        // 5. Simpan ke Database dengan Transaksi
        DB::beginTransaction();
        try {
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

            foreach ($itemsData as $item) {
                $isManual = (bool) ($item['is_manual'] ?? false);

                if ($isManual) {
                    PeminjamanAlatDetail::create([
                        'peminjaman_alat_id' => $peminjaman->id,
                        'inventaris_ruangan_id' => null,
                        'nama_alat' => trim($item['nama_alat']),
                        'kode_barang' => null,
                        'nup' => null,
                        'jumlah' => (int) $item['jumlah'],
                        'satuan' => !empty($item['satuan']) ? $item['satuan'] : 'Unit',
                        'is_manual' => true,
                        'spesifikasi' => $item['spesifikasi'] ?? null,
                        'keterangan' => $item['keterangan'] ?? null,
                        'kondisi_pinjam' => 'baik',
                    ]);
                } else {
                    $dirItem = InventarisRuangan::findOrFail($item['inventaris_ruangan_id']);
                    PeminjamanAlatDetail::create([
                        'peminjaman_alat_id' => $peminjaman->id,
                        'inventaris_ruangan_id' => $dirItem->id,
                        'nama_alat' => $dirItem->nama_barang,
                        'kode_barang' => $dirItem->kode_barang,
                        'nup' => $dirItem->nup,
                        'jumlah' => 1, // Default pasti 1 per NUP dari inventaris
                        'satuan' => $dirItem->satuan ?? 'Unit',
                        'is_manual' => false,
                        'spesifikasi' => $dirItem->spesifikasi_merk_tipe ?? ($item['spesifikasi'] ?? null),
                        'keterangan' => $item['keterangan'] ?? null,
                        'kondisi_pinjam' => $dirItem->kondisi ?? 'baik',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman alat berhasil dikirim dan menunggu persetujuan PLP.',
                'kode' => $peminjaman->kode_peminjaman,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan pengajuan: ' . $e->getMessage(),
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
        $peminjaman = PeminjamanAlat::where('id', $id)
            ->where('mahasiswa_id', Auth::id())
            ->firstOrFail();

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
                    $html = '<ul class="list-unstyled mb-0">';
                    foreach ($row->details as $d) {
                        $badge = $d->is_manual
                            ? ' <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Input Manual</span>'
                            : '';
                        $html .= '<li>• ' . e($d->nama_alat) . ' (' . $d->jumlah . ' ' . e($d->satuan) . ')' . $badge . '</li>';
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
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-info detailBtn" data-id="' . $row->id . '"><i class="bi bi-eye"></i> Detail</button>';

                    if ($row->status === 'pending_plp') {
                        $btn .= '<button type="button" class="btn btn-sm btn-success approveBtn ms-1" data-id="' . $row->id . '"><i class="bi bi-check-circle"></i> Setujui</button>';
                        $btn .= '<button type="button" class="btn btn-sm btn-danger rejectBtn ms-1" data-id="' . $row->id . '"><i class="bi bi-x-circle"></i> Tolak</button>';
                    } elseif ($row->status === 'disetujui' || $row->status === 'sedang_dipinjam') {
                        $btn .= '<button type="button" class="btn btn-sm btn-primary returnBtn ms-1" data-id="' . $row->id . '"><i class="bi bi-arrow-return-left"></i> Pengembalian</button>';
                    }

                    if (in_array($row->status, ['disetujui', 'sedang_dipinjam', 'selesai'])) {
                        $btn .= '<a href="' . route('peminjaman-alat.cetak', $row->id) . '" target="_blank" class="btn btn-sm btn-secondary ms-1"><i class="bi bi-printer"></i> Cetak</a>';
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

        $pdf = Pdf::loadView('pages.peminjaman_alat.cetak', compact('peminjaman'));
        return $pdf->stream('Peminjaman_Alat_' . $peminjaman->kode_peminjaman . '.pdf');
    }
}

