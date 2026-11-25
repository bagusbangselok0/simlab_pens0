<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class JabatanController extends Controller
{
    public function index()
    {
        $title = 'Daftar Jabatan';

        if (request()->ajax()) {
            $jabatans = Jabatan::withCount('users')->orderBy('id');

            return DataTables::of($jabatans)
                ->addIndexColumn()
                ->editColumn('slug', function ($row) {
                    return '<span class="badge bg-light-secondary text-dark">' . e($row->slug) . '</span>';
                })
                ->editColumn('users_count', function ($row) {
                    $badgeClass = $row->users_count > 0
                        ? 'bg-light-primary text-primary'
                        : 'bg-light-secondary text-muted';

                    return '<span class="badge ' . $badgeClass . '">' . $row->users_count . ' User</span>';
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('d M Y H:i:s') : '-';
                })
                ->addColumn('action', function ($jabatan) {
                    $button  = '<button type="button" data-id="' . $jabatan->id . '" class="btn btn-sm btn-primary btn-edit-jabatan" title="Edit Jabatan"><i class="bi bi-pen"></i></button>';
                    $button .= '&nbsp;';
                    $button .= '<button type="button" data-id="' . $jabatan->id . '" data-nama="' . e($jabatan->nama_jabatan) . '" data-users-count="' . $jabatan->users_count . '" class="btn btn-sm btn-danger btn-delete-jabatan" title="Hapus Jabatan"><i class="bi bi-trash"></i></button>';

                    return $button;
                })
                ->rawColumns(['slug', 'users_count', 'action'])
                ->make(true);
        }

        return view('pages.jabatan.index', compact('title'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateJabatan($request);

        $jabatan = Jabatan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data jabatan berhasil disimpan.',
            'data' => $jabatan,
        ], 200);
    }

    public function edit($id): JsonResponse
    {
        $jabatan = Jabatan::find($id);
        if (!$jabatan) {
            return response()->json([
                'success' => false,
                'message' => 'Data jabatan tidak ditemukan.',
            ], 404);
        }

        return response()->json($jabatan);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $jabatan = Jabatan::find($id);
        if (!$jabatan) {
            return response()->json([
                'success' => false,
                'message' => 'Data jabatan tidak ditemukan.',
            ], 404);
        }

        $validated = $this->validateJabatan($request, $jabatan);

        $jabatan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data jabatan berhasil diperbarui.',
            'data' => $jabatan->fresh(),
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $jabatan = Jabatan::withCount('users')->find($id);
        if (!$jabatan) {
            return response()->json([
                'success' => false,
                'message' => 'Data jabatan tidak ditemukan.',
            ], 404);
        }

        if ($jabatan->users_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan "' . $jabatan->nama_jabatan . '" tidak dapat dihapus karena masih digunakan oleh ' . $jabatan->users_count . ' user.',
            ], 422);
        }

        $jabatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data jabatan berhasil dihapus.',
        ], 200);
    }

    private function validateJabatan(Request $request, ?Jabatan $jabatan = null): array
    {
        $namaJabatan = trim((string) $request->input('nama_jabatan'));
        $slugInput = trim((string) $request->input('slug'));

        $request->merge([
            'nama_jabatan' => $namaJabatan,
            'slug' => Str::slug($slugInput !== '' ? $slugInput : $namaJabatan),
        ]);

        $ignoreId = $jabatan?->id;

        return $request->validate([
            'nama_jabatan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('jabatans', 'nama_jabatan')->ignore($ignoreId),
            ],
            'slug' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('jabatans', 'slug')->ignore($ignoreId),
            ],
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.max' => 'Nama jabatan maksimal 100 karakter.',
            'nama_jabatan.unique' => 'Nama jabatan sudah digunakan.',
            'slug.required' => 'Slug jabatan wajib diisi.',
            'slug.max' => 'Slug jabatan maksimal 100 karakter.',
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung (-).',
            'slug.unique' => 'Slug jabatan sudah digunakan.',
        ]);
    }
}
