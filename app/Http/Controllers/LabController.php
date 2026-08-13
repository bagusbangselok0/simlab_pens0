<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LabController extends Controller
{
    public function index()
    {
        $lab = Lab::all();
        $title = 'Daftar Lab';

        if (request()->ajax()) {
            return DataTables::of($lab)
                ->addIndexColumn()
                ->addColumn('nama_lab', function ($row) {
                    return $row->nama_lab;
                })
                ->addColumn('kode_lab', function ($row) {
                    return $row->kode_lab;
                })
                ->addColumn('lokasi', function ($row) {
                    return $row->lokasi;
                })
                ->addColumn('updated_at', function ($row) {
                    return $row->updated_at->format('d M Y H:i:s');
                })
                ->addColumn('action', function ($lab) {
                    $button  = '<a href="javascript:void(0)" data-id="' . $lab->id . '" class="btn btn-sm btn-circle btn-primary" id="editData" data-bs-toggle="modal" data-bs-target="#addLabModal"><i class="bi bi-pen"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<a href="javascript:void(0)" data-id="' . $lab->id . '" class="btn btn-sm btn-circle btn-danger" id="deleteData"><i class="bi bi-trash"></i></a>';

                    return $button;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }
        return view('pages.lab.index', compact('title'));
    }

    public function store(Request $request)
    {
        $message = [
            'nama_lab.required' => 'Nama Lab wajib diisi.',
            'kode_lab.required' => 'Kode Lab wajib diisi.',
            'lokasi.required' => 'Lokasi Lab wajib diisi.',
        ];

        $request->validate([
            'nama_lab' => 'required|string',
            'kode_lab' => 'required|string',
            'lokasi' => 'required|string',
        ], $message);

        $checkData = Lab::where('kode_lab', $request->kode_lab)->first();
        if ($checkData) {
            return response()->json([
                'success' => false,
                'message' => 'Kode Lab sudah digunakan.'
            ], 400);
        }

        $checkData = Lab::where('nama_lab', $request->nama_lab)->first();
        if ($checkData) {
            return response()->json([
                'success' => false,
                'message' => 'Nama Lab sudah digunakan.'
            ], 400);
        }

        $data = $request->all();

        $lab = Lab::create([
            'nama_lab' => $data['nama_lab'],
            'kode_lab' => $data['kode_lab'],
            'lokasi' => $data['lokasi'],
            'created_at' => now(
                date_default_timezone_set('Asia/Jakarta')
            ),
            'updated_at' => now(
                date_default_timezone_set('Asia/Jakarta')
            ),
        ]);

        if($lab) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan.'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan.'
            ], 500);
        }
    }

    public function edit($id)
    {
        $lab = Lab::find($id);
        return response()->json($lab);
    }

    public function update(Request $request, $id)
    {
        $message = [
            'nama_lab.required' => 'Nama Lab wajib diisi.',
            'kode_lab.required' => 'Kode Lab wajib diisi.',
            'lokasi.required' => 'Lokasi Lab wajib diisi.',
        ];

        $request->validate([
            'nama_lab' => 'required|string',
            'kode_lab' => 'required|string',
            'lokasi' => 'required|string',
        ], $message);

        $lab = Lab::find($id);
        if (!$lab) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        $checkData = Lab::where('kode_lab', $request->kode_lab)->where('id', '!=', $id)->first();
        if ($checkData) {
            return response()->json([
                'success' => false,
                'message' => 'Kode Lab sudah digunakan.'
            ], 400);
        }

        $checkData = Lab::where('nama_lab', $request->nama_lab)->where('id', '!=', $id)->first();
        if ($checkData) {
            return response()->json([
                'success' => false,
                'message' => 'Nama Lab sudah digunakan.'
            ], 400);
        }

        $data = $request->all();

        $lab->update([
            'nama_lab' => $data['nama_lab'],
            'kode_lab' => $data['kode_lab'],
            'lokasi' => $data['lokasi'],
            'updated_at' => now(
                date_default_timezone_set('Asia/Jakarta')
            ),
        ]);

        if($lab) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui.'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal diperbarui.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $lab = Lab::find($id);
        if (!$lab) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        $lab->delete();

        if($lab) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal dihapus.'
            ], 500);
        }
    }
}
