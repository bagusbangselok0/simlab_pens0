<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\LabManager;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LabManagerController extends Controller
{
    public function index()
    {
        $title = 'Daftar Penanggung Jawab Lab';
        $labs = Lab::all();
        $plps = User::where('jabatan_id', 4)->get();
        $kalabs = User::where('jabatan_id', 3)->get();

        if (request()->ajax()) {
            // Mengambil daftar pinjaman milik mahasiswa
            $labManagers = LabManager::with(['lab', 'plp', 'kalab'])
                ->orderBy('created_at', 'desc')
                ->get();

            return DataTables::of($labManagers)
                ->addIndexColumn()
                ->addColumn('nama_lab', function ($row) {
                    return $row->lab->nama_lab;
                })
                ->addColumn('lokasi', function ($row) {
                    return $row->lab->lokasi;
                })
                ->addColumn('plp', function ($row) {
                    return $row->plp ? $row->plp->getFullNameAttribute() : '-';
                })
                ->addColumn('kalab', function ($row) {
                    return $row->kalab ? $row->kalab->getFullNameAttribute() : '-';
                })
                ->addColumn('action', function ($labManagers) {
                    $button = '<a href="#" class="btn btn-sm btn-info show-detail-modal mb-1" data-id="' . $labManagers->id . '">Detail</a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= ' <a href="#" class="btn btn-sm btn-danger delete-lab-manager" data-id="' . $labManagers->id . '">Hapus</a>';
                    return $button;

                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.lab_manager.index', compact('title', 'labs', 'plps', 'kalabs'));
    }

    public function store(Request $request)
    {
        $messages = [
            'lab_id.required' => 'Harap pilih lab.',
            'plp_id.required' => 'Harap pilih PLP.',
            'kalab_id.required' => 'Harap pilih kalab.',
            'lab_id.exists' => 'Laboratorium yang dipilih tidak valid.',
            'kalab_id.exists' => 'Kalab yang dipilih tidak valid.',
        ];

        $request->validate([
            'lab_id' => 'required|exists:labs,id',
            'plp_id' => 'required|exists:users,id',
            'kalab_id' => 'required|exists:users,id',
        ], $messages);
        // Cek apakah lab sudah memiliki penanggung jawab
        $existingLabManager = LabManager::where('lab_id', $request->lab_id)->first();
        if ($existingLabManager) {
            return response()->json(['message' => 'Laboratorium sudah memiliki penanggung jawab'], 422);
        }
        // Cek apakah Kalab sudah menjadi penanggung jawab laboratorium lain
        $existingKalab = LabManager::where('kalab_id', $request->kalab_id)->first();
        if ($existingKalab) {
            return response()->json([
                'success' => false,
                'message' => 'Kalab sudah menjadi penanggung jawab laboratorium lain'
            ], 422);
        }

        $labManager = LabManager::create([
            'lab_id' => $request->lab_id,
            'plp_id' => $request->plp_id,
            'kalab_id' => $request->kalab_id,
        ]);

        if ($labManager) {
            return response()->json([
                'success' => true,
                'message' => 'Penanggung jawab laboratorium berhasil ditambahkan'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Penanggung jawab laboratorium gagal ditambahkan'
            ], 500);
        }
    }

    public function detailAndEditModal($id)
    {
        $labManager = LabManager::with(['lab', 'plp', 'kalab'])
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($labManager, 200);
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $messages = [
            'lab_id.required' => 'Harap pilih lab.',
            'plp_id.required' => 'Harap pilih PLP.',
            'kalab_id.required' => 'Harap pilih kalab.',
            'lab_id.exists' => 'Laboratorium yang dipilih tidak valid.',
            'kalab_id.exists' => 'Kalab yang dipilih tidak valid.',
        ];

        $request->validate([
            'lab_id' => 'required|exists:labs,id',
            'plp_id' => 'required|exists:users,id',
            'kalab_id' => 'required|exists:users,id',
        ], $messages);

        $labManager = LabManager::findOrFail($id);
        $labManager->update([
            'lab_id' => $request->lab_id,
            'plp_id' => $request->plp_id,
            'kalab_id' => $request->kalab_id,
        ]);

        // cek apakah lab sudah memiliki penanggung jawab lain (kecuali dirinya sendiri)
        $existingLabManager = LabManager::where('lab_id', $request->lab_id)
            ->where('id', '!=', $id)
            ->first();
        if ($existingLabManager) {
            return response()->json(['message' => 'Laboratorium sudah memiliki penanggung jawab lain'], 422);
        }
        // Cek apakah Kalab sudah menjadi penanggung jawab laboratorium lain (kecuali dirinya sendiri)
        $existingKalab = LabManager::where('kalab_id', $request->kalab_id)
            ->where('id', '!=', $id)
            ->first();
        if ($existingKalab) {
            return response()->json([
                'success' => false,
                'message' => 'Kalab sudah menjadi penanggung jawab laboratorium lain'
            ], 422);
        }

        if ($labManager) {
            return response()->json([
                'success' => true,
                'message' => 'Penanggung jawab laboratorium berhasil diperbarui'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Penanggung jawab laboratorium gagal diperbarui'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $labManager = LabManager::findOrFail($id);
        $labManager->delete();

        return response()->json([
            'success' => true,
            'message' => 'Penanggung jawab laboratorium berhasil dihapus'
        ], 200);
    }
}
