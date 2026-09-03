<?php

namespace App\Http\Controllers;

use App\Models\InventarisRuangan;
use App\Models\InventarisBarang;
use App\Models\Lab;
use App\Models\LabManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventarisRuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Tentukan lab yang dapat diakses user
        if ($user->role_id === 1) { // Admin
            $labs = Lab::orderBy('nama_lab')->get();
        } else { // PLP / Kalab
            $managedLabIds = LabManager::where('plp_id', $user->id)
                ->orWhere('kalab_id', $user->id)
                ->pluck('lab_id');
            $labs = Lab::whereIn('id', $managedLabIds)->orderBy('nama_lab')->get();
        }

        // Tentukan lab terpilih
        $selectedLabId = $request->get('lab_id') ?? ($labs->first()->id ?? null);
        $selectedLab = $selectedLabId ? Lab::with(['labManager.plp', 'labManager.kalab'])->find($selectedLabId) : null;
        $masterInventaris = InventarisBarang::orderBy('nama_barang')->get();
        $assignedNupKeys = InventarisRuangan::query()
            ->whereNotNull('nup')
            ->get(['kode_barang', 'nama_barang', 'spesifikasi_merk_tipe', 'nup'])
            ->flatMap(function ($item) {
                return collect(preg_split('/\s*,\s*/', (string) $item->nup, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn ($nup) => implode('|', [$item->kode_barang, $item->nama_barang, $item->spesifikasi_merk_tipe, trim($nup)]));
            })->all();
        $masterInventaris = $masterInventaris->filter(function ($master) use ($assignedNupKeys) {
            $identity = $master->merk_tipe !== '-' ? $master->merk_tipe : $master->spesifikasi;
            $key = implode('|', [$master->kode_barang, $master->nama_barang, $identity, trim((string) $master->nup)]);
            return !in_array($key, $assignedNupKeys, true);
        })->values();

        // Query data inventaris
        $query = InventarisRuangan::query();

        if ($selectedLabId) {
            $query->where('lab_id', $selectedLabId);
        } else {
            $query->whereRaw('1 = 0'); // Empty if no lab available
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('spesifikasi_merk_tipe', 'like', "%{$search}%");
            });
        }

        foreach (['kode_barang', 'nup', 'nama_barang', 'spesifikasi_merk_tipe', 'tahun_perolehan', 'jumlah'] as $column) {
            if ($request->filled('filter_' . $column)) {
                $query->where($column, 'like', '%' . $request->input('filter_' . $column) . '%');
            }
        }

        if ($request->filled('filter_dapat_dipinjam')) {
            $query->where('is_bisa_dipinjam', $request->input('filter_dapat_dipinjam') === 'ya');
        }

        $perPage = min(max((int) $request->input('per_page', 25), 10), 100);
        $inventaris = $query->orderBy('nama_barang')->paginate($perPage)->withQueryString();

        // Ringkasan Statistik
        $stats = [
            'total_item' => $selectedLabId ? InventarisRuangan::where('lab_id', $selectedLabId)->count() : 0,
            'total_unit' => $selectedLabId ? (int) InventarisRuangan::where('lab_id', $selectedLabId)->sum('jumlah') : 0,
            'baik' => $selectedLabId ? (int) InventarisRuangan::where('lab_id', $selectedLabId)->where('kondisi', 'baik')->sum('jumlah') : 0,
            'rusak_ringan' => $selectedLabId ? (int) InventarisRuangan::where('lab_id', $selectedLabId)->where('kondisi', 'rusak_ringan')->sum('jumlah') : 0,
            'rusak_berat' => $selectedLabId ? (int) InventarisRuangan::where('lab_id', $selectedLabId)->where('kondisi', 'rusak_berat')->sum('jumlah') : 0,
        ];

        $title = 'Daftar Inventaris Ruangan (DIR)';
        return view('pages.inventaris_ruangan.index', compact('title', 'labs', 'selectedLab', 'inventaris', 'stats', 'masterInventaris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lab_id' => 'required|exists:labs,id',
            'inventaris_barang_ids' => 'required|array|min:1',
            'inventaris_barang_ids.*' => 'integer|distinct|exists:inventaris_barangs,id',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:20',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'nup' => 'nullable|string|max:50',
            'is_bisa_dipinjam' => 'nullable|boolean',
            'keterangan' => 'nullable|string',
        ]);

        $validated['is_bisa_dipinjam'] = $request->has('is_bisa_dipinjam');

        $masters = InventarisBarang::whereIn('id', $validated['inventaris_barang_ids'])->get();
        $master = $masters->first();
        $sameIdentity = $masters->every(fn ($item) => $item->kode_barang === $master->kode_barang
            && $item->nama_barang === $master->nama_barang
            && $item->merk === $master->merk
            && $item->tipe === $master->tipe);

        if ($masters->count() !== count($validated['inventaris_barang_ids']) || !$sameIdentity) {
            return back()->withInput()->withErrors([
                'inventaris_barang_ids' => 'NUP yang dipilih harus berasal dari barang yang sama.',
            ]);
        }

        $identity = $master->merk_tipe !== '-' ? $master->merk_tipe : $master->spesifikasi;
        $selectedNups = $masters->pluck('nup')->map(fn ($nup) => trim((string) $nup))->filter()->unique()->values()->all();

        $existingDirItem = InventarisRuangan::where('lab_id', $validated['lab_id'])
            ->where('kode_barang', $master->kode_barang)
            ->where('nama_barang', $master->nama_barang)
            ->where('spesifikasi_merk_tipe', $identity)
            ->where('tahun_perolehan', $master->tgl_perolehan?->year)
            ->where('kondisi', $validated['kondisi'])
            ->first();

        if ($existingDirItem) {
            $existingNups = $this->normalizeNupList($existingDirItem->nup);
            $mergedNups = array_values(array_unique(array_merge($existingNups, $selectedNups)));
            $existingDirItem->update([
                'nup' => implode(', ', $mergedNups),
                'jumlah' => count($mergedNups),
                'satuan' => $validated['satuan'],
                'kondisi' => $validated['kondisi'],
                'is_bisa_dipinjam' => $validated['is_bisa_dipinjam'],
                'keterangan' => $validated['keterangan'] ?? $existingDirItem->keterangan,
            ]);

            return redirect()->route('inventaris-ruangan.index', ['lab_id' => $validated['lab_id']])
                ->with('success', 'NUP barang berhasil digabungkan ke data yang sudah ada di laboratorium ini.');
        }

        $assignedNupKeys = InventarisRuangan::whereNotNull('nup')
            ->get(['kode_barang', 'nama_barang', 'spesifikasi_merk_tipe', 'nup'])
            ->flatMap(function ($item) {
                return collect(preg_split('/\s*,\s*/', (string) $item->nup, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn ($nup) => implode('|', [$item->kode_barang, $item->nama_barang, $item->spesifikasi_merk_tipe, trim($nup)]));
            })->all();
        $selectedKeys = array_map(fn ($nup) => implode('|', [$master->kode_barang, $master->nama_barang, $identity, $nup]), $selectedNups);
        if (count(array_intersect($selectedKeys, $assignedNupKeys)) > 0) {
            return back()->withInput()->withErrors([
                'inventaris_barang_ids' => 'Salah satu NUP sudah ditempatkan di ruangan lain.',
            ]);
        }

        $validated = array_merge($validated, [
            'inventaris_barang_id' => $master->id,
            'kode_barang' => $master->kode_barang,
            'nup' => implode(', ', $selectedNups),
            'nama_barang' => $master->nama_barang,
            'spesifikasi_merk_tipe' => $identity,
            'tahun_perolehan' => $master->tgl_perolehan?->year,
            'jumlah' => count($selectedNups),
        ]);

        InventarisRuangan::create($validated);

        return redirect()->route('inventaris-ruangan.index', ['lab_id' => $validated['lab_id']])
            ->with('success', 'Data inventaris berhasil ditambahkan ke Daftar Inventaris Ruangan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $inventaris = InventarisRuangan::findOrFail($id);

        $validated = $request->validate([
            'kode_barang' => 'nullable|string|max:50',
            'nama_barang' => 'required|string|max:255',
            'spesifikasi_merk_tipe' => 'nullable|string',
            'tahun_perolehan' => 'nullable|integer|min:1900|max:2100',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:20',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'nup' => 'nullable|string|max:50',
            'is_bisa_dipinjam' => 'nullable|boolean',
            'keterangan' => 'nullable|string',
        ]);

        $validated['is_bisa_dipinjam'] = $request->has('is_bisa_dipinjam');

        $inventaris->update($validated);

        return redirect()->route('inventaris-ruangan.index', ['lab_id' => $inventaris->lab_id])
            ->with('success', 'Data inventaris berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $inventaris = InventarisRuangan::findOrFail($id);
        $labId = $inventaris->lab_id;
        $inventaris->delete();

        return redirect()->route('inventaris-ruangan.index', ['lab_id' => $labId])
            ->with('success', 'Data inventaris berhasil dihapus dari ruangan.');
    }

    /**
     * Export Daftar Inventaris Ruangan (DIR) to PDF.
     */
    public function exportPdf(Request $request, $lab_id)
    {
        $lab = Lab::with(['labManager.plp', 'labManager.kalab'])->findOrFail($lab_id);
        $items = InventarisRuangan::where('lab_id', $lab_id)->orderBy('nama_barang')->get();

        $pdf = Pdf::loadView('pages.inventaris_ruangan.pdf_dir', compact('lab', 'items'))
            ->setPaper('a4', 'landscape');

        $cleanKodeLab = preg_replace('/[^A-Za-z0-9_\-]/', '_', $lab->kode_lab ?? 'LAB');
        return $pdf->stream("DIR_{$cleanKodeLab}.pdf");
    }

    private function normalizeNupList(?string $nup): array
    {
        if (empty($nup)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(function ($value) {
            return trim((string) $value);
        }, preg_split('/\s*,\s*/', $nup, -1, PREG_SPLIT_NO_EMPTY)), fn ($value) => $value !== '')));
    }
}
