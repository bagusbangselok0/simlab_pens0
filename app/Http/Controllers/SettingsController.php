<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $title = 'Pengaturan Sistem';
        $labs = Lab::with('labManager.kalab')->orderBy('nama_lab')->get();
        $settings = Setting::query()
            ->where('setting_key', Setting::AUTO_APPROVE_KALAB)
            ->whereIn('scope_key', $labs->map(fn (Lab $lab) => Setting::labScope($lab->id)))
            ->get()
            ->keyBy('scope_key');

        return view('pages.settings.index', compact('title', 'labs', 'settings'));
    }

    public function updateAutoApproveKalab(Request $request, Lab $lab)
    {
        $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        if (!$lab->labManager || !$lab->labManager->kalab) {
            return back()->with('error', 'Laboratorium tersebut belum memiliki Kalab.');
        }

        Setting::updateOrCreate(
            [
                'setting_key' => Setting::AUTO_APPROVE_KALAB,
                'scope_key' => Setting::labScope($lab->id),
            ],
            [
                'value' => $request->boolean('enabled') ? 'true' : 'false',
                'value_type' => 'boolean',
                'description' => 'Otomatis menyetujui peminjaman setelah approval PLP.',
                'is_active' => true,
                'updated_by' => Auth::id(),
            ]
        );

        return back()->with('success', 'Pengaturan auto-approve Kalab berhasil diperbarui.');
    }
}