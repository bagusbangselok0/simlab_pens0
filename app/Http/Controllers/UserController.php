<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Jabatan;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role_id = $request->input('role_id');
        $users = User::with('role')->orderBy('is_verified', 'asc')->orderBy('id', 'desc');
        $roles = Role::all();
        $prodis = Prodi::all();
        $jabatans = Jabatan::all();

        if ($role_id) {
            $users->where('role_id', $role_id);
        }

        $title = 'Daftar Pengguna';
        if (request()->ajax()) {
            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('nama_asli', function ($row) {
                    return "$row->gelar_depan $row->nama_asli $row->gelar_belakang";
                })
                ->addColumn('roles', function ($row) {
                    return $row->role->nama_role;
                })
                ->addColumn('nip', function ($row) {
                    return $row->nip ?? '-';
                })
                ->addColumn('nrp', function ($row) {
                    return $row->nrp ?? '-';
                })
                ->addColumn('email', function ($row) {
                    return $row->email;
                })
                ->addColumn('status', function ($row) {
                    if ($row->is_verified) {
                        return '<span class="badge bg-success">Terverifikasi</span>';
                    }
                    return '<span class="badge bg-warning text-dark">Belum Terverifikasi</span>';
                })
                ->addColumn('updated_at', function ($row) {
                    return $row->updated_at->format('d M Y H:i:s');
                })
                ->addColumn('last_login_at', function ($row) {
                    if (!$row->last_login_at) {
                        return '<span class="badge bg-secondary"><i class="bi bi-clock-history me-1"></i>Belum pernah login</span>';
                    }

                    $time = $row->last_login_at->translatedFormat('d M Y H:i');
                    $platform = strtolower($row->last_login_platform ?? 'web');
                    
                    if ($platform === 'mobile') {
                        $platformBadge = '<span class="badge bg-info text-white"><i class="bi bi-phone me-1"></i>Mobile</span>';
                    } else {
                        $platformBadge = '<span class="badge bg-primary text-white"><i class="bi bi-laptop me-1"></i>Web</span>';
                    }

                    $ipHtml = $row->last_login_ip 
                        ? '<div class="small text-muted mt-1"><i class="bi bi-geo-alt me-1"></i>' . e($row->last_login_ip) . '</div>' 
                        : '';

                    return '<div class="d-flex flex-column">' .
                                '<div class="d-flex align-items-center gap-1 flex-wrap">' . 
                                    $platformBadge . 
                                    '<span class="fw-semibold small me-1"><i class="bi bi-clock me-1 text-secondary"></i>' . $time . '</span>' .
                                '</div>' .
                                $ipHtml .
                           '</div>';
                })
                ->addColumn('action', function ($user) {
                    $button = '';
                    if (!$user->is_verified) {
                        $button .= '<a href="javascript:void(0)" data-id="' . $user->id . '" class="btn btn-sm btn-circle btn-info verifyUserBtn" title="Verifikasi User"><i class="bi bi-check-circle-fill"></i></a>';
                        $button .= '&nbsp;';
                    }
                    $button .= '<a href="javascript:void(0)" data-id="' . $user->id . '" class="btn btn-sm btn-circle btn-primary" id="editData" title="Edit Data"><i class="bi bi-pen"></i></a>';
                    $button .= '&nbsp;';
                    $button .= '<a href="javascript:void(0)" data-id="' . $user->id . '" data-email="' . $user->email . '" class="btn btn-sm btn-circle btn-warning generateResetBtn" title="Reset Password"><i class="bi bi-key-fill"></i></a>';
                    $button .= '&nbsp;';
                    $button .= '<a href="javascript:void(0)" data-id="' . $user->id . '" class="btn btn-sm btn-circle btn-danger" id="deleteData" title="Hapus Data"><i class="bi bi-trash"></i></a>';

                    return $button;
                })
                ->rawColumns(['action', 'status', 'last_login_at'])
                ->addIndexColumn()
                ->make(true);
        }
        // Logic to retrieve and display all users
        return view('pages.users.index', compact('title', 'users', 'roles', 'prodis', 'jabatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $role_id = $request->input('role_id');

        $rules = [
            'nama_asli' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
        ];

        // Conditional Validation based on role
        if ($role_id == 5) { // Mahasiswa
            $rules['nrp'] = 'required|string|max:20|unique:users,nrp';
            $rules['prodi_id'] = 'required|exists:prodi,id';
        } elseif (in_array($role_id, [2, 3, 4])) { // Dosen, PLP, Satpam (need NIP)
            $rules['nip'] = 'required|string|max:20|unique:users,nip';

            if (in_array($role_id, [2, 3])) { // Dosen, PLP (need Prodi, Gelar, Jabatan)
                $rules['prodi_id'] = 'required|exists:prodi,id';
                $rules['jabatan_id'] = 'required|exists:jabatans,id';
            }
            if ($role_id == 4) { // Satpam (need Jabatan)
                $rules['jabatan_id'] = 'required|exists:jabatans,id';
            }
        }

        $messages = [
            'nama_asli.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'nrp.required' => 'NRP wajib diisi.',
            'nrp.unique' => 'NRP sudah terdaftar.',
            'prodi_id.required' => 'Program Studi wajib dipilih.',
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = $request->only([
                'nama_asli',
                'email',
                'gelar_depan',
                'gelar_belakang',
                'nip',
                'nrp',
                'jabatan_id',
                'role_id',
                'prodi_id'
            ]);

            // Set empty strings and unneeded attributes to null based on role
            if ($role_id == 5) { // Mahasiswa
                $userData['nip'] = null;
                $userData['gelar_depan'] = null;
                $userData['gelar_belakang'] = null;
                $userData['jabatan_id'] = null;
            } elseif ($role_id == 4) { // Satpam
                $userData['nrp'] = null;
                $userData['gelar_depan'] = null;
                $userData['gelar_belakang'] = null;
                $userData['prodi_id'] = null;
            } elseif (in_array($role_id, [2, 3])) { // Dosen, PLP
                $userData['nrp'] = null;
            } elseif ($role_id == 1) { // Admin
                $userData['nip'] = null;
                $userData['nrp'] = null;
                $userData['gelar_depan'] = null;
                $userData['gelar_belakang'] = null;
                $userData['prodi_id'] = null;
                $userData['jabatan_id'] = null;
            }

            foreach ($userData as $key => $value) {
                if ($value === '') {
                    $userData[$key] = null;
                }
            }

            $userData['password'] = Hash::make($request->password);
            $userData['is_verified'] = true;
            $userData['is_active'] = true;

            User::create($userData);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $role_id = $request->input('role_id');

        $rules = [
            'nama_asli' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
        ];

        // Conditional Validation based on role
        if ($role_id == 5) { // Mahasiswa
            $rules['nrp'] = 'required|string|max:20|unique:users,nrp,' . $id;
            $rules['prodi_id'] = 'required|exists:prodi,id';
        } elseif (in_array($role_id, [2, 3, 4])) { // Dosen, PLP, Satpam (need NIP)
            $rules['nip'] = 'required|string|max:20|unique:users,nip,' . $id;

            if (in_array($role_id, [2, 3])) { // Dosen, PLP (need Prodi, Gelar, Jabatan)
                $rules['prodi_id'] = 'required|exists:prodi,id';
                $rules['jabatan_id'] = 'required|exists:jabatans,id';
            }
            if ($role_id == 4) { // Satpam (need Jabatan)
                $rules['jabatan_id'] = 'required|exists:jabatans,id';
            }
        }

        $messages = [
            'nama_asli.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'nrp.required' => 'NRP wajib diisi.',
            'nrp.unique' => 'NRP sudah terdaftar.',
            'prodi_id.required' => 'Program Studi wajib dipilih.',
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = $request->only([
                'nama_asli',
                'email',
                'gelar_depan',
                'gelar_belakang',
                'nip',
                'nrp',
                'jabatan_id',
                'role_id',
                'prodi_id'
            ]);

            // Set empty strings and unneeded attributes to null based on role
            if ($role_id == 5) { // Mahasiswa
                $userData['nip'] = null;
                $userData['gelar_depan'] = null;
                $userData['gelar_belakang'] = null;
                $userData['jabatan_id'] = null;
            } elseif ($role_id == 4) { // Satpam
                $userData['nrp'] = null;
                $userData['gelar_depan'] = null;
                $userData['gelar_belakang'] = null;
                $userData['prodi_id'] = null;
            } elseif (in_array($role_id, [2, 3])) { // Dosen, PLP
                $userData['nrp'] = null;
            } elseif ($role_id == 1) { // Admin
                $userData['nip'] = null;
                $userData['nrp'] = null;
                $userData['gelar_depan'] = null;
                $userData['gelar_belakang'] = null;
                $userData['prodi_id'] = null;
                $userData['jabatan_id'] = null;
            }

            foreach ($userData as $key => $value) {
                if ($value === '') {
                    $userData[$key] = null;
                }
            }

            $user->update($userData);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifikasi user (mengubah is_verified menjadi true).
     */
    public function verifyUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->is_verified = true;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diverifikasi!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate link reset password untuk dikirim via WA oleh Admin.
     */
    public function generateResetLink($id)
    {
        $user = User::findOrFail($id);

        // Buat token secara manual menggunakan Facade Password
        $token = Password::createToken($user);

        // Susun URL reset password
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email
        ]);

        return response()->json([
            'success' => true,
            'email' => $user->email,
            'reset_url' => $resetUrl,
            'message' => 'Tautan reset password berhasil dibuat!'
        ]);
    }
}
