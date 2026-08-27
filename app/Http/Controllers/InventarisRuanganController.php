<?php

namespace App\Http\Controllers;

use App\Models\InventarisRuangan;
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

        $inventaris = $query->orderBy('nama_barang')->paginate(25)->withQueryString();

        // Ringkasan Statistik
        $stats = [
            'total_item' => $selectedLabId ? InventarisRuangan::where('lab_id', $selectedLabId)->count() : 0,
            'total_unit' => $selectedLabId ? (int) InventarisRuangan::where('lab_id', $selectedLabId)->sum('jumlah') : 0,
            'baik' => $selectedLabId ? (int) InventarisRuangan::where('lab_id', $selectedLabId)->where('kondisi', 'baik')->sum('jumlah') : 0,
            'rusak_ringan' => $selectedLabId ? (int) InventarisRuangan::where('lab_id', $selectedLabId)->where('kondisi', 'rusak_ringan')->sum('jumlah') : 0,
            'rusak_berat' => $selectedLabId ? (int) InventarisRuangan::where('lab_id', $selectedLabId)->where('kondisi', 'rusak_berat')->sum('jumlah') : 0,
        ];

        $title = 'Daftar Inventaris Ruangan (DIR)';
        return view('pages.inventaris_ruangan.index', compact('title', 'labs', 'selectedLab', 'inventaris', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lab_id' => 'required|exists:labs,id',
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
}
