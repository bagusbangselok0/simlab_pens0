<?php

namespace App\Http\Controllers;

use App\Models\InventarisBarang;
use App\Models\InventarisRuangan;
use App\Models\Lab;
use App\Models\LabManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Shuchkin\SimpleXLSX;

class InventarisBarangController extends Controller
{
    /**
     * Display a listing of the master inventory.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Labs available for assignment dropdown
        if ($user->role_id === 1) {
            $labs = Lab::orderBy('nama_lab')->get();
        } else {
            $managedLabIds = LabManager::where('plp_id', $user->id)
                ->orWhere('kalab_id', $user->id)
                ->pluck('lab_id');
            $labs = Lab::whereIn('id', $managedLabIds)->orderBy('nama_lab')->get();
        }

        $query = InventarisBarang::with('inventarisRuangan.lab');

        // Filter status penempatan (unassigned / assigned)
        if ($request->filled('status')) {
            if ($request->status === 'unassigned') {
                $query->whereDoesntHave('inventarisRuangan');
            } elseif ($request->status === 'assigned') {
                $query->whereHas('inventarisRuangan');
            }
        }

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('nup', 'like', "%{$search}%")
                  ->orWhere('merk', 'like', "%{$search}%")
                  ->orWhere('tipe', 'like', "%{$search}%");
            });
        }

        $items = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        // Ringkasan Statistik
        $stats = [
            'total_item' => InventarisBarang::count(),
            'unassigned' => InventarisBarang::doesntHave('inventarisRuangan')->count(),
            'assigned' => InventarisBarang::has('inventarisRuangan')->count(),
        ];

        $title = 'Master Data Inventaris';
        return view('pages.inventaris_barang.index', compact('title', 'items', 'labs', 'stats'));
    }

    /**
     * Store a newly created master item in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'nullable|string|max:50',
            'nup' => 'nullable|string|max:50',
            'nama_barang' => 'required|string|max:255',
            'merk' => 'nullable|string|max:100',
            'tipe' => 'nullable|string|max:100',
            'tgl_buku_pertama' => 'nullable|date',
            'tgl_perolehan' => 'nullable|date',
            'spesifikasi' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        InventarisBarang::create($validated);

        return redirect()->route('inventaris.index')
            ->with('success', 'Data master inventaris berhasil ditambahkan.');
    }

    /**
     * Update the specified master item in storage.
     */
    public function update(Request $request, $id)
    {
        $item = InventarisBarang::findOrFail($id);

        $validated = $request->validate([
            'kode_barang' => 'nullable|string|max:50',
            'nup' => 'nullable|string|max:50',
            'nama_barang' => 'required|string|max:255',
            'merk' => 'nullable|string|max:100',
            'tipe' => 'nullable|string|max:100',
            'tgl_buku_pertama' => 'nullable|date',
            'tgl_perolehan' => 'nullable|date',
            'spesifikasi' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $item->update($validated);

        // Jika barang sudah ditempatkan di DIR, perbarui juga nama & kode di DIR
        if ($item->inventarisRuangan) {
            $item->inventarisRuangan->update([
                'kode_barang' => $item->kode_barang,
                'nup' => $item->nup,
                'nama_barang' => $item->nama_barang,
                'spesifikasi_merk_tipe' => $item->merk_tipe != '-' ? $item->merk_tipe : $item->spesifikasi,
            ]);
        }

        return redirect()->route('inventaris.index')
            ->with('success', 'Data master inventaris berhasil diperbarui.');
    }

    /**
     * Remove the specified master item from storage.
     */
    public function destroy($id)
    {
        $item = InventarisBarang::findOrFail($id);
        $item->delete();

        return redirect()->route('inventaris.index')
            ->with('success', 'Data master inventaris berhasil dihapus.');
    }

    /**
     * Assign / Tempatkan master item ke Ruangan (DIR).
     */
    public function assignToRuangan(Request $request, $id)
    {
        $barang = InventarisBarang::findOrFail($id);

        if ($barang->inventarisRuangan) {
            return redirect()->route('inventaris.index')
                ->with('error', 'Barang ini sudah ditempatkan di ruangan ' . $barang->inventarisRuangan->lab->nama_lab);
        }

        $validated = $request->validate([
            'lab_id' => 'required|exists:labs,id',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:20',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'is_bisa_dipinjam' => 'nullable|boolean',
            'keterangan' => 'nullable|string',
        ]);

        $tahunPerolehan = $barang->tgl_perolehan ? (int) date('Y', strtotime($barang->tgl_perolehan)) : null;
        $spesifikasi = $barang->merk_tipe != '-' ? $barang->merk_tipe : ($barang->spesifikasi ?? null);

        InventarisRuangan::create([
            'inventaris_barang_id' => $barang->id,
            'lab_id' => $validated['lab_id'],
            'kode_barang' => $barang->kode_barang,
            'nup' => $barang->nup,
            'nama_barang' => $barang->nama_barang,
            'spesifikasi_merk_tipe' => $spesifikasi,
            'tahun_perolehan' => $tahunPerolehan,
            'jumlah' => $validated['jumlah'],
            'satuan' => $validated['satuan'],
            'kondisi' => $validated['kondisi'],
            'is_bisa_dipinjam' => $request->has('is_bisa_dipinjam'),
            'keterangan' => $validated['keterangan'] ?? $barang->keterangan,
        ]);

        return redirect()->route('inventaris.index')
            ->with('success', 'Barang berhasil ditempatkan ke ruangan.');
    }

    /**
     * Import Excel / CSV to Master Inventaris.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            if ($xlsx = SimpleXLSX::parse($file->getRealPath())) {
                $rows = $xlsx->rows();
            } else {
                return redirect()->route('inventaris.index')
                    ->with('error', 'Gagal membaca file Excel: ' . SimpleXLSX::parseError());
            }
        } else {
            // CSV / TXT fallback
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                    // Check if semicolon separated
                    if (count($data) === 1 && str_contains($data[0], ';')) {
                        $data = str_getcsv($data[0], ';');
                    }
                    $rows[] = $data;
                }
                fclose($handle);
            }
        }

        if (empty($rows)) {
            return redirect()->route('inventaris.index')
                ->with('error', 'File Excel/CSV tidak memiliki data.');
        }

        // Cari baris header (Kolom: Kode Barang, NUP, Nama Barang, Merk, Tipe, Tanggal Buku Pertama, Tanggal Perolehan)
        $headerIndex = null;
        $colMap = [
            'kode_barang' => null,
            'nup' => null,
            'nama_barang' => null,
            'merk' => null,
            'tipe' => null,
            'tgl_buku_pertama' => null,
            'tgl_perolehan' => null,
        ];

        foreach ($rows as $index => $row) {
            $rowLower = array_map(fn($v) => strtolower(trim((string)$v)), $row);
            
            foreach ($rowLower as $colIdx => $colName) {
                if (str_contains($colName, 'kode')) {
                    $colMap['kode_barang'] = $colIdx;
                } elseif ($colName === 'nup' || str_contains($colName, 'nup')) {
                    $colMap['nup'] = $colIdx;
                } elseif (str_contains($colName, 'nama')) {
                    $colMap['nama_barang'] = $colIdx;
                } elseif ($colName === 'merk' || str_contains($colName, 'merk')) {
                    $colMap['merk'] = $colIdx;
                } elseif ($colName === 'tipe' || str_contains($colName, 'tipe')) {
                    $colMap['tipe'] = $colIdx;
                } elseif (str_contains($colName, 'buku')) {
                    $colMap['tgl_buku_pertama'] = $colIdx;
                } elseif (str_contains($colName, 'perolehan')) {
                    $colMap['tgl_perolehan'] = $colIdx;
                }
            }

            // Jika minimal kolom nama_barang ditemukan
            if ($colMap['nama_barang'] !== null) {
                $headerIndex = $index;
                break;
            }
        }

        // Fallback default index 0 jika tidak terdeteksi nama kolom
        if ($headerIndex === null) {
            $headerIndex = 0;
            $colMap = [
                'kode_barang' => 0,
                'nup' => 1,
                'nama_barang' => 2,
                'merk' => 3,
                'tipe' => 4,
                'tgl_buku_pertama' => 5,
                'tgl_perolehan' => 6,
            ];
        }

        $importedCount = 0;
        $updatedCount = 0;

        for ($i = $headerIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $namaBarang = isset($colMap['nama_barang'], $row[$colMap['nama_barang']]) ? trim((string)$row[$colMap['nama_barang']]) : '';
            
            // Lewati baris kosong
            if (empty($namaBarang)) {
                continue;
            }

            $kodeBarang = isset($colMap['kode_barang'], $row[$colMap['kode_barang']]) ? trim((string)$row[$colMap['kode_barang']]) : null;
            $nup = isset($colMap['nup'], $row[$colMap['nup']]) ? trim((string)$row[$colMap['nup']]) : null;
            $merk = isset($colMap['merk'], $row[$colMap['merk']]) ? trim((string)$row[$colMap['merk']]) : null;
            $tipe = isset($colMap['tipe'], $row[$colMap['tipe']]) ? trim((string)$row[$colMap['tipe']]) : null;
            
            $rawTglBuku = isset($colMap['tgl_buku_pertama'], $row[$colMap['tgl_buku_pertama']]) ? trim((string)$row[$colMap['tgl_buku_pertama']]) : null;
            $rawTglPerolehan = isset($colMap['tgl_perolehan'], $row[$colMap['tgl_perolehan']]) ? trim((string)$row[$colMap['tgl_perolehan']]) : null;

            $tglBuku = $this->parseDate($rawTglBuku);
            $tglPerolehan = $this->parseDate($rawTglPerolehan);

            // Simpan / update ke database
            if (!empty($kodeBarang) && !empty($nup)) {
                $existing = InventarisBarang::where('kode_barang', $kodeBarang)->where('nup', $nup)->first();
                if ($existing) {
                    $existing->update([
                        'nama_barang' => $namaBarang,
                        'merk' => $merk,
                        'tipe' => $tipe,
                        'tgl_buku_pertama' => $tglBuku,
                        'tgl_perolehan' => $tglPerolehan,
                    ]);
                    $updatedCount++;
                    continue;
                }
            }

            InventarisBarang::create([
                'kode_barang' => $kodeBarang,
                'nup' => $nup,
                'nama_barang' => $namaBarang,
                'merk' => $merk,
                'tipe' => $tipe,
                'tgl_buku_pertama' => $tglBuku,
                'tgl_perolehan' => $tglPerolehan,
            ]);
            $importedCount++;
        }

        return redirect()->route('inventaris.index')
            ->with('success', "Proses import selesai. {$importedCount} data baru ditambahkan, {$updatedCount} data diperbarui.");
    }

    /**
     * Download Excel template for import.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_master_inventaris.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // Header baris 1
            fputcsv($file, ['Kode Barang', 'NUP', 'Nama Barang', 'Merk', 'Tipe', 'Tanggal Buku Pertama', 'Tanggal Perolehan']);
            // Contoh baris data
            fputcsv($file, ['2010104002', '1', 'Tanah Bangunan Pendidikan Dan Latihan', '', '', '2021-12-31', '2004-03-03']);
            fputcsv($file, ['3030101033', '1', 'Mesin Laser Cutting', 'CUTTING STICKER JINKA PRO 1351', 'CUTTING STICKER JINKA PRO 1351', '2021-12-31', '2018-05-21']);
            fputcsv($file, ['3030205014', '1', 'Crimping Tools', 'DIGILINK', 'DIGILINK', '2021-12-31', '2012-12-12']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper to parse dates from various formats (string or Excel serial).
     */
    private function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // If numeric Excel timestamp
        if (is_numeric($value) && (float)$value > 20000) {
            try {
                return Carbon::createFromTimestamp(((float)$value - 25569) * 86400)->format('Y-m-d');
            } catch (\Exception $e) {
                // Ignore
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
