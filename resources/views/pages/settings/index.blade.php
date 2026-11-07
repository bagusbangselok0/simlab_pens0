@extends('layouts.app')

@section('styles')
<style>
    .btn-color-swatch {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        position: relative;
    }
    .btn-color-swatch:hover {
        transform: scale(1.15);
    }
    .btn-color-swatch.active-swatch {
        transform: scale(1.18);
        box-shadow: 0 0 0 3px #ffffff, 0 0 0 5px var(--bs-primary, #435ebe) !important;
    }
</style>
@endsection

@section('content')
    <section class="section">
        {{-- Flash Messages --}}
        @if (session('success_theme'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success_theme') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success_notif'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success_notif') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success_alat'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success_alat') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Terdapat kesalahan pengisian data:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $activeTab = request('tab', 'theme');
        @endphp

        {{-- Navigasi Tab Pengaturan --}}
        <ul class="nav nav-pills mb-4 gap-2" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'theme' ? 'active' : '' }} fw-semibold px-4 py-2" 
                    id="tab-theme-btn" data-bs-toggle="pill" data-bs-target="#tab-theme" type="button" role="tab"
                    aria-controls="tab-theme" aria-selected="{{ $activeTab === 'theme' ? 'true' : 'false' }}">
                    <i class="bi bi-palette-fill me-2"></i>Tema & Tampilan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'notif' ? 'active' : '' }} fw-semibold px-4 py-2" 
                    id="tab-notif-btn" data-bs-toggle="pill" data-bs-target="#tab-notif" type="button" role="tab"
                    aria-controls="tab-notif" aria-selected="{{ $activeTab === 'notif' ? 'true' : 'false' }}">
                    <i class="bi bi-bell-fill me-2"></i>Gateway & Notifikasi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'alat' ? 'active' : '' }} fw-semibold px-4 py-2" 
                    id="tab-alat-btn" data-bs-toggle="pill" data-bs-target="#tab-alat" type="button" role="tab"
                    aria-controls="tab-alat" aria-selected="{{ $activeTab === 'alat' ? 'true' : 'false' }}">
                    <i class="bi bi-tools me-2"></i>Kebijakan Peminjaman Alat
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'approval' ? 'active' : '' }} fw-semibold px-4 py-2" 
                    id="tab-approval-btn" data-bs-toggle="pill" data-bs-target="#tab-approval" type="button" role="tab"
                    aria-controls="tab-approval" aria-selected="{{ $activeTab === 'approval' ? 'true' : 'false' }}">
                    <i class="bi bi-shield-check me-2"></i>Persetujuan Ruang Lab
                </button>
            </li>
        </ul>

        {{-- Konten Tab --}}
        <div class="tab-content" id="settingsTabContent">

            {{-- =========================================================================
                 TAB 1: TEMA & TAMPILAN (THEME & LOADER)
                 ========================================================================= --}}
            <div class="tab-pane fade {{ $activeTab === 'theme' ? 'show active' : '' }}" id="tab-theme" role="tabpanel" aria-labelledby="tab-theme-btn">
                <form method="POST" action="{{ route('settings.theme-loader') }}">
                    @csrf
                    <div class="row g-4">
                        {{-- Kartu 1: Mode Tampilan & Warna Aksen --}}
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100 border">
                                <div class="card-header border-bottom bg-transparent d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary text-white me-3 p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="bi bi-moon-stars-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Mode Tema & Warna Aksen</h5>
                                            <small class="text-muted">Atur nuansa visual dan palet warna antarmuka sistem</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body pt-4">
                                    {{-- Mode Default Pengguna Baru --}}
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-2">
                                            <i class="bi bi-sun-fill text-warning me-1"></i> Mode Tampilan Bawaan (Default Theme)
                                        </label>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <input type="radio" class="btn-check" name="default_mode" id="theme_light" value="light" 
                                                    {{ old('default_mode', $defaultMode) === 'light' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100" for="theme_light">
                                                    <i class="bi bi-sun fs-2 mb-1 text-warning"></i>
                                                    <span class="fw-bold small">Terang</span>
                                                    <small class="text-muted" style="font-size: 0.7rem;">Light Mode</small>
                                                </label>
                                            </div>
                                            <div class="col-4">
                                                <input type="radio" class="btn-check" name="default_mode" id="theme_dark" value="dark"
                                                    {{ old('default_mode', $defaultMode) === 'dark' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100" for="theme_dark">
                                                    <i class="bi bi-moon-stars fs-2 mb-1 text-primary"></i>
                                                    <span class="fw-bold small">Gelap</span>
                                                    <small class="text-muted" style="font-size: 0.7rem;">Dark Mode</small>
                                                </label>
                                            </div>
                                            <div class="col-4">
                                                <input type="radio" class="btn-check" name="default_mode" id="theme_system" value="system"
                                                    {{ old('default_mode', $defaultMode) === 'system' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100" for="theme_system">
                                                    <i class="bi bi-display fs-2 mb-1 text-info"></i>
                                                    <span class="fw-bold small">Otomatis</span>
                                                    <small class="text-muted" style="font-size: 0.7rem;">Ikuti OS</small>
                                                </label>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Menentukan mode warna awal bagi pengunjung baru atau saat pengguna belum memilih tema sendiri via saklar sidebar.
                                        </small>
                                    </div>

                                    {{-- Warna Aksen Utama (Primary Color) --}}
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-2" for="accent_color">
                                            <i class="bi bi-brush-fill text-primary me-1"></i> Warna Aksen Utama (Brand / Primary Accent)
                                        </label>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <button type="button" class="btn btn-sm btn-color-swatch p-2 border rounded-circle shadow-sm" data-color="#435ebe" style="background-color: #435ebe; width: 34px; height: 34px;" title="Mazer Royal Blue (#435ebe)"></button>
                                            <button type="button" class="btn btn-sm btn-color-swatch p-2 border rounded-circle shadow-sm" data-color="#0f3a68" style="background-color: #0f3a68; width: 34px; height: 34px;" title="PENS Navy Blue (#0f3a68)"></button>
                                            <button type="button" class="btn btn-sm btn-color-swatch p-2 border rounded-circle shadow-sm" data-color="#198754" style="background-color: #198754; width: 34px; height: 34px;" title="Emerald Green (#198754)"></button>
                                            <button type="button" class="btn btn-sm btn-color-swatch p-2 border rounded-circle shadow-sm" data-color="#6366f1" style="background-color: #6366f1; width: 34px; height: 34px;" title="Modern Indigo (#6366f1)"></button>
                                            <button type="button" class="btn btn-sm btn-color-swatch p-2 border rounded-circle shadow-sm" data-color="#7952b3" style="background-color: #7952b3; width: 34px; height: 34px;" title="Deep Purple (#7952b3)"></button>
                                            <button type="button" class="btn btn-sm btn-color-swatch p-2 border rounded-circle shadow-sm" data-color="#dc3545" style="background-color: #dc3545; width: 34px; height: 34px;" title="Crimson Red (#dc3545)"></button>
                                            <button type="button" class="btn btn-sm btn-color-swatch p-2 border rounded-circle shadow-sm" data-color="#212529" style="background-color: #212529; width: 34px; height: 34px;" title="Dark Charcoal (#212529)"></button>
                                        </div>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" id="accent_color_picker" 
                                                value="{{ old('accent_color', $accentColor) }}" title="Pilih warna bebas" style="max-width: 50px; cursor: pointer;">
                                            <input type="text" class="form-control font-monospace" id="accent_color" name="accent_color" 
                                                value="{{ old('accent_color', $accentColor) }}" placeholder="#435ebe" maxlength="7" required>
                                            <span class="input-group-text preview-accent-badge text-white fw-bold" id="previewColorBadge" style="background-color: {{ old('accent_color', $accentColor) }};">
                                                Preview
                                            </span>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            Warna aksen akan diterapkan pada tombol utama, navigasi menu aktif, header tabel, dan sorotan antarmuka.
                                        </small>
                                    </div>

                                    {{-- Status Bawaan Sidebar Desktop --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold mb-2" for="sidebar_default">
                                            <i class="bi bi-layout-sidebar-inset text-primary me-1"></i> Status Bawaan Sidebar (Desktop)
                                        </label>
                                        <select class="form-select" id="sidebar_default" name="sidebar_default" required>
                                            <option value="expanded" {{ old('sidebar_default', $sidebarDefault) === 'expanded' ? 'selected' : '' }}>
                                                Terbuka Penuh (Expanded - Standar 300px)
                                            </option>
                                            <option value="collapsed" {{ old('sidebar_default', $sidebarDefault) === 'collapsed' ? 'selected' : '' }}>
                                                Tertutup / Sembunyi (Collapsed - Hanya Hamburger Bar)
                                            </option>
                                        </select>
                                        <small class="text-muted d-block mt-1">
                                            Pilihan ini mengatur apakah menu samping kiri langsung terbuka saat pertama kali halaman diakses di layar komputer/laptop.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kartu 2: Pengaturan Global Page Loader --}}
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100 border">
                                <div class="card-header border-bottom bg-transparent d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-info text-white me-3 p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="bi bi-hourglass-split fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Global Page Loader</h5>
                                            <small class="text-muted">Animasi transisi halus saat halaman sedang dimuat</small>
                                        </div>
                                    </div>
                                    <div>
                                        @if ($enableLoader)
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body pt-4">
                                    {{-- Switch Enable Loader --}}
                                    <div class="p-3 border rounded mb-3 bg-light-subtle">
                                        <div class="form-check form-switch d-flex align-items-center gap-2 ps-0 mb-0">
                                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" 
                                                id="enable_loader" name="enable_loader" value="1"
                                                style="width: 2.75rem; height: 1.4rem; cursor: pointer;"
                                                {{ old('enable_loader', $enableLoader) ? 'checked' : '' }}>
                                            <div>
                                                <label class="form-check-label fw-bold mb-0 cursor-pointer" for="enable_loader">
                                                    Aktifkan Animasi Page Loader (Glassmorphism & Pulsing Logo)
                                                </label>
                                                <div class="text-muted small">
                                                    Menampilkan overlay transparan blur dengan logo SIMLAB berdenyut saat browser memuat halaman.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Failsafe Timeout Speed --}}
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-2" for="loader_speed">
                                            <i class="bi bi-stopwatch text-info me-1"></i> Batas Waktu Maksimal Loader (Failsafe Timeout)
                                        </label>
                                        <select class="form-select" id="loader_speed" name="loader_speed" required>
                                            <option value="1000" {{ old('loader_speed', $loaderSpeed) == '1000' ? 'selected' : '' }}>
                                                1 Detik (Sangat Singkat / Cepat)
                                            </option>
                                            <option value="3000" {{ old('loader_speed', $loaderSpeed) == '3000' ? 'selected' : '' }}>
                                                3 Detik (Standar / Rekomendasi)
                                            </option>
                                            <option value="5000" {{ old('loader_speed', $loaderSpeed) == '5000' ? 'selected' : '' }}>
                                                5 Detik (Maksimal untuk Koneksi Lambat)
                                            </option>
                                        </select>
                                        <small class="text-muted d-block mt-1">
                                            Loader akan otomatis menghilang begitu seluruh halaman siap, atau dipaksa hilang jika mencapai batas waktu ini.
                                        </small>
                                    </div>

                                    <hr class="my-3">

                                    {{-- Live Preview Box Loader --}}
                                    <div class="p-3 border rounded bg-light-subtle text-center">
                                        <h6 class="fw-bold mb-1 text-info"><i class="bi bi-eye-fill me-1"></i> Pratinjau Efek Animasi Loader</h6>
                                        <p class="text-muted small mb-3">
                                            Uji coba bagaimana tampilan animasi layar loading bekerja di browser Anda selama 2 detik.
                                        </p>
                                        <button type="button" class="btn btn-outline-info px-4 py-2" id="btnPreviewLoader">
                                            <i class="bi bi-play-circle me-1"></i> Tes Animasi Loader Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Simpan Tema & Loader --}}
                        <div class="col-12 text-end mt-2">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                <i class="bi bi-save me-1"></i> Simpan Pengaturan Tema & Loader
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- =========================================================================
                 TAB 2: GATEWAY & NOTIFIKASI (WHATSAPP & WEB PUSH)
                 ========================================================================= --}}
            <div class="tab-pane fade {{ $activeTab === 'notif' ? 'show active' : '' }}" id="tab-notif" role="tabpanel" aria-labelledby="tab-notif-btn">
                <form method="POST" action="{{ route('settings.notification-gateway') }}" id="formNotifSettings">
                    @csrf
                    <div class="row g-4">
                        {{-- Kartu WhatsApp Gateway --}}
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100 border">
                                <div class="card-header border-bottom bg-transparent d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-success text-white me-3 p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="bi bi-whatsapp fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Gateway WhatsApp (Fonnte API)</h5>
                                            <small class="text-muted">Kirim pemberitahuan otomatis langsung ke WhatsApp pengguna</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        @if ($waEnabled)
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body pt-4">
                                    {{-- Switch Status WhatsApp --}}
                                    <div class="p-3 border rounded mb-3 bg-light-subtle">
                                        <div class="form-check form-switch d-flex align-items-center gap-2 ps-0 mb-0">
                                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" 
                                                id="wa_enabled" name="wa_enabled" value="1"
                                                style="width: 2.75rem; height: 1.4rem; cursor: pointer;"
                                                {{ old('wa_enabled', $waEnabled) ? 'checked' : '' }}>
                                            <div>
                                                <label class="form-check-label fw-bold mb-0 cursor-pointer" for="wa_enabled">
                                                    Aktifkan Pengiriman WhatsApp Gateway
                                                </label>
                                                <div class="text-muted small">
                                                    Pemberitahuan akan otomatis dikirimkan ke nomor WhatsApp user terdaftar (format 62xxx / 08xxx).
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- API Token Fonnte --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="wa_token">
                                            <i class="bi bi-key-fill text-success me-1"></i> API Token Fonnte
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control font-monospace" 
                                                id="wa_token" name="wa_token" 
                                                value="{{ old('wa_token', $waToken) }}" 
                                                placeholder="Masukkan token dari fonnte.com...">
                                            <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="wa_token" title="Lihat/Sembunyikan Token">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            Dapatkan token API resmi dari akun perangkat Anda di portal <a href="https://fonnte.com" target="_blank" class="fw-semibold">Fonnte.com</a>.
                                        </small>
                                    </div>

                                    {{-- Endpoint URL --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="wa_api_url">
                                            <i class="bi bi-link-45deg text-success me-1"></i> API Endpoint URL
                                        </label>
                                        <input type="url" class="form-control" 
                                            id="wa_api_url" name="wa_api_url" 
                                            value="{{ old('wa_api_url', $waApiUrl) }}" 
                                            placeholder="https://api.fonnte.com/send">
                                        <small class="text-muted d-block mt-1">
                                            Default: <code>https://api.fonnte.com/send</code>
                                        </small>
                                    </div>

                                    {{-- Event Triggers WhatsApp --}}
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-2">
                                            <i class="bi bi-sliders text-success me-1"></i> Transaksi yang Memicu WhatsApp
                                        </label>
                                        <div class="list-group">
                                            <label class="list-group-item d-flex gap-3 align-items-center">
                                                <input class="form-check-input flex-shrink-0" type="checkbox" 
                                                    name="wa_peminjaman_lab" value="1" 
                                                    {{ old('wa_peminjaman_lab', $waPeminjamanLab) ? 'checked' : '' }}>
                                                <span>
                                                    <strong>Peminjaman Ruang Laboratorium</strong>
                                                    <small class="d-block text-muted">Kirim WA saat pengajuan baru, approval PLP/Kalab, atau penolakan ruang.</small>
                                                </span>
                                            </label>
                                            <label class="list-group-item d-flex gap-3 align-items-center">
                                                <input class="form-check-input flex-shrink-0" type="checkbox" 
                                                    name="wa_peminjaman_alat" value="1" 
                                                    {{ old('wa_peminjaman_alat', $waPeminjamanAlat) ? 'checked' : '' }}>
                                                <span>
                                                    <strong>Peminjaman Alat Laboratorium</strong>
                                                    <small class="d-block text-muted">Kirim WA saat pengajuan alat baru, disetujui PLP, serah terima, dan selesai.</small>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    {{-- Kotak Uji Coba WhatsApp --}}
                                    <div class="p-3 border rounded bg-light-subtle">
                                        <h6 class="fw-bold mb-1 text-success"><i class="bi bi-send-check me-1"></i> Uji Coba Pengiriman Pesan WhatsApp</h6>
                                        <p class="text-muted small mb-2">Kirim pesan uji coba untuk memverifikasi koneksi API Fonnte Anda.</p>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="test_wa_phone" 
                                                value="{{ Auth::user()->no_hp }}" placeholder="Nomor WA (contoh: 081234567890)">
                                            <button type="button" class="btn btn-outline-success" id="btnTestWA">
                                                <i class="bi bi-whatsapp me-1"></i> Kirim Tes WA
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kartu Web Push Gateway --}}
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100 border">
                                <div class="card-header border-bottom bg-transparent d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary text-white me-3 p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="bi bi-app-indicator fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Browser Web Push (VAPID)</h5>
                                            <small class="text-muted">Notifikasi langsung pada desktop & browser mobile pengguna</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        @if ($pushEnabled)
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body pt-4">
                                    {{-- Switch Status Web Push --}}
                                    <div class="p-3 border rounded mb-3 bg-light-subtle">
                                        <div class="form-check form-switch d-flex align-items-center gap-2 ps-0 mb-0">
                                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" 
                                                id="push_enabled" name="push_enabled" value="1"
                                                style="width: 2.75rem; height: 1.4rem; cursor: pointer;"
                                                {{ old('push_enabled', $pushEnabled) ? 'checked' : '' }}>
                                            <div>
                                                <label class="form-check-label fw-bold mb-0 cursor-pointer" for="push_enabled">
                                                    Aktifkan Browser Web Push Notification
                                                </label>
                                                <div class="text-muted small">
                                                    Menampilkan pop-up notifikasi realtime di peramban (Chrome, Edge, Firefox, Safari iOS/macOS).
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- VAPID Subject --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="vapid_subject">
                                            <i class="bi bi-envelope-at text-primary me-1"></i> VAPID Subject (Email Kontak Admin)
                                        </label>
                                        <input type="text" class="form-control" 
                                            id="vapid_subject" name="vapid_subject" 
                                            value="{{ old('vapid_subject', $vapidSubject) }}" 
                                            placeholder="mailto:admin@simlab.com">
                                        <small class="text-muted d-block mt-1">
                                            Format wajib diawali dengan <code>mailto:</code> (contoh: <code>mailto:admin@kampus.ac.id</code>).
                                        </small>
                                    </div>

                                    {{-- VAPID Public Key --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="vapid_public_key">
                                            <i class="bi bi-shield-check text-primary me-1"></i> VAPID Public Key
                                        </label>
                                        <div class="input-group">
                                            <textarea class="form-control font-monospace" rows="2" 
                                                id="vapid_public_key" name="vapid_public_key" 
                                                placeholder="Kunci publik VAPID...">{{ old('vapid_public_key', $vapidPublicKey) }}</textarea>
                                            <button class="btn btn-outline-secondary btn-copy" type="button" data-target="vapid_public_key" title="Salin Kunci">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- VAPID Private Key & Generator --}}
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold mb-0" for="vapid_private_key">
                                                <i class="bi bi-shield-lock text-primary me-1"></i> VAPID Private Key
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnGenerateVapid">
                                                <i class="bi bi-magic me-1"></i> Buat Kunci Baru Otomatis
                                            </button>
                                        </div>
                                        <div class="input-group">
                                            <input type="password" class="form-control font-monospace" 
                                                id="vapid_private_key" name="vapid_private_key" 
                                                value="{{ old('vapid_private_key', $vapidPrivateKey) }}" 
                                                placeholder="Kunci privat VAPID...">
                                            <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="vapid_private_key" title="Lihat/Sembunyikan">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary btn-copy" type="button" data-target="vapid_private_key" title="Salin Kunci">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Event Triggers Web Push --}}
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-2">
                                            <i class="bi bi-sliders text-primary me-1"></i> Transaksi yang Memicu Web Push
                                        </label>
                                        <div class="list-group">
                                            <label class="list-group-item d-flex gap-3 align-items-center">
                                                <input class="form-check-input flex-shrink-0" type="checkbox" 
                                                    name="push_peminjaman_lab" value="1" 
                                                    {{ old('push_peminjaman_lab', $pushPeminjamanLab) ? 'checked' : '' }}>
                                                <span>
                                                    <strong>Peminjaman Ruang Laboratorium</strong>
                                                    <small class="d-block text-muted">Kirim Web Push ke mahasiswa dan penanggung jawab ruangan.</small>
                                                </span>
                                            </label>
                                            <label class="list-group-item d-flex gap-3 align-items-center">
                                                <input class="form-check-input flex-shrink-0" type="checkbox" 
                                                    name="push_peminjaman_alat" value="1" 
                                                    {{ old('push_peminjaman_alat', $pushPeminjamanAlat) ? 'checked' : '' }}>
                                                <span>
                                                    <strong>Peminjaman Alat Laboratorium</strong>
                                                    <small class="d-block text-muted">Kirim Web Push status peminjaman, pengambilan, dan serah terima alat.</small>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    {{-- Kotak Uji Coba Web Push --}}
                                    <div class="p-3 border rounded bg-light-subtle">
                                        <h6 class="fw-bold mb-1 text-primary"><i class="bi bi-broadcast me-1"></i> Uji Coba Web Push Perangkat Ini</h6>
                                        <p class="text-muted small mb-2">Pastikan izin notifikasi browser telah aktif via tombol lonceng di pojok kanan atas.</p>
                                        <button type="button" class="btn btn-outline-primary w-100" id="btnTestPush">
                                            <i class="bi bi-bell-fill me-1"></i> Kirim Notifikasi Tes ke Browser Saya
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Simpan Gateway & Notifikasi --}}
                        <div class="col-12 text-end mt-2">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                <i class="bi bi-save me-1"></i> Simpan Pengaturan Gateway & Notifikasi
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- =========================================================================
                 TAB 3: KEBIJAKAN PEMINJAMAN ALAT
                 ========================================================================= --}}
            <div class="tab-pane fade {{ $activeTab === 'alat' ? 'show active' : '' }}" id="tab-alat" role="tabpanel" aria-labelledby="tab-alat-btn">
                <div class="card shadow-sm border">
                    <div class="card-header border-bottom d-flex align-items-center justify-content-between py-3">
                        <div>
                            <h5 class="card-title mb-1 text-primary">
                                <i class="bi bi-tools me-2"></i>Kebijakan & Batasan Peminjaman Alat
                            </h5>
                            <p class="text-muted mb-0 small">Atur batas kuota, durasi, dan aturan pengajuan alat laboratorium untuk mahasiswa.</p>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <form method="POST" action="{{ route('settings.peminjaman-alat') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-bold" for="max_items_per_request">
                                        <i class="bi bi-collection me-1 text-primary"></i> Maksimal Alat per Pengajuan
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('max_items_per_request') is-invalid @enderror" 
                                            id="max_items_per_request" name="max_items_per_request" 
                                            value="{{ old('max_items_per_request', $maxItems) }}" min="1" max="50" required>
                                        <span class="input-group-text">Item / Baris</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Batas maksimal variasi atau unit alat yang dapat dimasukkan dalam 1 formulir (rekomendasi: 3 - 10).
                                    </small>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-bold" for="max_loan_days">
                                        <i class="bi bi-calendar-range me-1 text-primary"></i> Maksimal Durasi Peminjaman
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('max_loan_days') is-invalid @enderror" 
                                            id="max_loan_days" name="max_loan_days" 
                                            value="{{ old('max_loan_days', $maxLoanDays) }}" min="1" max="90" required>
                                        <span class="input-group-text">Hari</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Rentang batas waktu antara tanggal pinjam dan rencana pengembalian (rekomendasi: 3 - 14 hari).
                                    </small>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-bold" for="max_active_loans">
                                        <i class="bi bi-person-check me-1 text-primary"></i> Maksimal Peminjaman Aktif
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('max_active_loans') is-invalid @enderror" 
                                            id="max_active_loans" name="max_active_loans" 
                                            value="{{ old('max_active_loans', $maxActiveLoans) }}" min="1" max="20" required>
                                        <span class="input-group-text">Pengajuan</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Batas pengajuan peminjaman alat aktif (status diajukan, disetujui, atau dipinjam) per mahasiswa.
                                    </small>
                                </div>

                                <div class="col-12 mt-3">
                                    <div class="p-3 border rounded bg-light-subtle">
                                        <div class="form-check form-switch d-flex align-items-center gap-2 ps-0 mb-0">
                                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" 
                                                id="allow_manual_input" name="allow_manual_input" value="1"
                                                style="width: 2.75rem; height: 1.4rem; cursor: pointer;"
                                                {{ old('allow_manual_input', $allowManual) ? 'checked' : '' }}>
                                            <div>
                                                <label class="form-check-label fw-bold mb-0 cursor-pointer" for="allow_manual_input">
                                                    Izinkan Opsi "Input Manual" pada Formulir Mahasiswa
                                                </label>
                                                <div class="text-muted small">
                                                    Jika dinonaktifkan, opsi "Input Manual" disembunyikan. Mahasiswa hanya diizinkan memilih alat yang terdaftar di Inventaris Ruangan (DIR).
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-save me-1"></i> Simpan Kebijakan Alat
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 TAB 4: PERSETUJUAN RUANG LAB (AUTO-APPROVE KALAB)
                 ========================================================================= --}}
            <div class="tab-pane fade {{ $activeTab === 'approval' ? 'show active' : '' }}" id="tab-approval" role="tabpanel" aria-labelledby="tab-approval-btn">
                <div class="card shadow-sm border">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title mb-1 text-primary">
                            <i class="bi bi-shield-check me-2"></i>Pengaturan Persetujuan Ruang Laboratorium
                        </h5>
                        <p class="text-muted mb-0 small">Atur perilaku persetujuan (approval) peminjaman ruangan dan laboratorium oleh Kepala Laboratorium (Kalab).</p>
                    </div>
                    <div class="card-body pt-3">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Laboratorium</th>
                                        <th>Kalab</th>
                                        <th>Auto-approve Kalab</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($labs as $lab)
                                        @php
                                            $setting = $settings->get(\App\Models\Setting::labScope($lab->id));
                                            $enabled = $setting && $setting->is_active && filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $lab->nama_lab }}</strong><br>
                                                <small class="text-muted">{{ $lab->kode_lab }} - {{ $lab->lokasi }}</small>
                                            </td>
                                            <td>{{ $lab->labManager->kalab->full_name ?? '-' }}</td>
                                            <td>
                                                <span class="badge {{ $enabled ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $enabled ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                @if ($lab->labManager?->kalab)
                                                    <form method="POST" action="{{ route('settings.auto-approve-kalab', $lab) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="enabled" value="{{ $enabled ? '0' : '1' }}">
                                                        <button type="submit" class="btn btn-sm {{ $enabled ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                            <i class="bi {{ $enabled ? 'bi-toggle-off' : 'bi-toggle-on' }} me-1"></i>
                                                            {{ $enabled ? 'Matikan' : 'Aktifkan' }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">Belum ada Kalab</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada data laboratorium.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // 1. Sinkronisasi Tab dengan URL
    $('#settingsTab button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
        const targetId = $(e.target).attr('data-bs-target').replace('#tab-', '');
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('tab', targetId);
        window.history.replaceState({}, '', newUrl);
    });

    // 2. Toggle Show/Hide Password/Token
    $(document).on('click', '.btn-toggle-password', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // 3. Salin ke Clipboard
    $(document).on('click', '.btn-copy', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const val = input.val();

        if (!val) {
            Swal.fire('Info', 'Teks masih kosong.', 'info');
            return;
        }

        navigator.clipboard.writeText(val).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Disalin!',
                text: 'Kunci berhasil disalin ke papan klip.',
                timer: 1500,
                showConfirmButton: false
            });
        });
    });

    // 4. Generate Pasangan Kunci VAPID Otomatis
    $('#btnGenerateVapid').on('click', function() {
        Swal.fire({
            title: 'Generate Kunci VAPID Baru?',
            text: 'Ini akan membuat pasangan kunci VAPID baru untuk browser Web Push. Pastikan Anda menekan tombol "Simpan Pengaturan" setelah digenerate.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Generate Kunci Baru',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $('#btnGenerateVapid');
                btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Sedang membuat kunci...');

                $.ajax({
                    url: "{{ route('settings.notification.generate-vapid') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Buat Kunci Baru Otomatis');
                        if (res.success) {
                            $('#vapid_public_key').val(res.publicKey);
                            $('#vapid_private_key').val(res.privateKey);
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Digenerate!',
                                text: res.message + ' Jangan lupa klik tombol "Simpan Pengaturan Gateway" di bawah untuk mengaktifkannya.'
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Buat Kunci Baru Otomatis');
                        const res = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res?.message || 'Terjadi kesalahan saat generate kunci VAPID.'
                        });
                    }
                });
            }
        });
    });

    // 5. Uji Coba Kirim WhatsApp
    $('#btnTestWA').on('click', function() {
        const phone = $('#test_wa_phone').val();
        if (!phone) {
            Swal.fire('Perhatian', 'Harap masukkan nomor WhatsApp penerima uji coba.', 'warning');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Mengirim...');

        $.ajax({
            url: "{{ route('settings.notification.test-wa') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                target_phone: phone
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-whatsapp me-1"></i> Kirim Tes WA');
                Swal.fire({
                    icon: 'success',
                    title: 'Terkirim!',
                    text: res.message
                });
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-whatsapp me-1"></i> Kirim Tes WA');
                const res = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Terkirim',
                    text: res?.message || 'Terjadi kesalahan saat mengirim pesan uji coba WhatsApp.'
                });
            }
        });
    });

    // 6. Uji Coba Kirim Web Push
    $('#btnTestPush').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Mengirim Web Push...');

        $.ajax({
            url: "{{ route('settings.notification.test-push') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-bell-fill me-1"></i> Kirim Notifikasi Tes ke Browser Saya');
                Swal.fire({
                    icon: 'success',
                    title: 'Web Push Terkirim!',
                    text: res.message
                });
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-bell-fill me-1"></i> Kirim Notifikasi Tes ke Browser Saya');
                const res = xhr.responseJSON;
                Swal.fire({
                    icon: 'warning',
                    title: 'Periksa Pengaturan Web Push',
                    text: res?.message || 'Gagal mengirim push notification ke browser.'
                });
            }
        });
    });

    // 7. Pengaturan Palet Warna Aksen (Color Swatches) & Real-time Live Dynamic Preview
    function applyAccentColorPreview(hex) {
        if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;

        hex = hex.toLowerCase();

        // Update nilai form control
        $('#accent_color').val(hex);
        $('#accent_color_picker').val(hex);
        $('#previewColorBadge').css('background-color', hex);

        // Highlight swatch yang aktif
        $('.btn-color-swatch').removeClass('active-swatch');
        $(`.btn-color-swatch[data-color="${hex}"]`).addClass('active-swatch');

        // Parse RGB values
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);

        // Update atau sisipkan elemen style dinamis pada <head>
        let styleEl = document.getElementById('dynamic-accent-style');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'dynamic-accent-style';
            document.head.appendChild(styleEl);
        }

        styleEl.innerHTML = `
            :root,
            :root[data-bs-theme="light"],
            :root[data-bs-theme="dark"],
            html,
            html[data-bs-theme="light"],
            html[data-bs-theme="dark"],
            body {
                --bs-primary: ${hex} !important;
                --bs-primary-rgb: ${r}, ${g}, ${b} !important;
                --bs-link-color: ${hex} !important;
                --bs-link-hover-color: ${hex} !important;
            }
            .btn-primary {
                --bs-btn-bg: ${hex} !important;
                --bs-btn-border-color: ${hex} !important;
                --bs-btn-hover-bg: ${hex} !important;
                --bs-btn-hover-border-color: ${hex} !important;
                --bs-btn-active-bg: ${hex} !important;
                --bs-btn-active-border-color: ${hex} !important;
                --bs-btn-disabled-bg: ${hex} !important;
                --bs-btn-disabled-border-color: ${hex} !important;
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
            }
            .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
                filter: brightness(0.92);
            }
            .btn-outline-primary {
                --bs-btn-color: ${hex} !important;
                --bs-btn-border-color: ${hex} !important;
                --bs-btn-hover-bg: ${hex} !important;
                --bs-btn-hover-border-color: ${hex} !important;
                --bs-btn-active-bg: ${hex} !important;
                --bs-btn-active-border-color: ${hex} !important;
                color: ${hex} !important;
                border-color: ${hex} !important;
            }
            .btn-outline-primary:hover, .btn-outline-primary:active, .btn-check:checked + .btn-outline-primary {
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
            }
            .sidebar-wrapper .menu .sidebar-item.active > .sidebar-link {
                background-color: ${hex} !important;
                color: #ffffff !important;
            }
            .sidebar-wrapper .menu .sidebar-item.active > .sidebar-link i,
            .sidebar-wrapper .menu .sidebar-item.active > .sidebar-link svg {
                color: #ffffff !important;
                fill: #ffffff !important;
            }
            .sidebar-wrapper .menu .submenu .submenu-item.active > a,
            .sidebar-wrapper .menu .submenu .submenu-item.active > .submenu-link,
            .submenu-item.active > a,
            .submenu-item.active > .submenu-link,
            html[data-bs-theme=dark] .sidebar-wrapper .menu .submenu .submenu-item.active > a,
            html[data-bs-theme=dark] .sidebar-wrapper .menu .submenu .submenu-item.active > .submenu-link {
                color: ${hex} !important;
                font-weight: 700 !important;
            }
            .sidebar-wrapper .menu .submenu .submenu-item a:hover,
            .sidebar-wrapper .menu .submenu .submenu-item .submenu-link:hover {
                color: ${hex} !important;
            }
            .nav-pills .nav-link.active,
            .nav-pills .show > .nav-link {
                background-color: ${hex} !important;
                color: #ffffff !important;
            }
            .badge.bg-primary,
            .bg-primary {
                background-color: ${hex} !important;
                color: #ffffff !important;
            }
            .text-primary,
            a.text-primary {
                color: ${hex} !important;
            }
            .border-primary {
                border-color: ${hex} !important;
            }
            .form-check-input:checked,
            .form-switch .form-check-input:checked,
            .sidebar-wrapper .sidebar-header .theme-toggle .form-check-input:checked {
                background-color: ${hex} !important;
                border-color: ${hex} !important;
            }
            .form-control:focus,
            .form-select:focus {
                border-color: ${hex} !important;
                box-shadow: 0 0 0 0.25rem rgba(${r}, ${g}, ${b}, 0.25) !important;
            }
            .page-item.active .page-link {
                background-color: ${hex} !important;
                border-color: ${hex} !important;
                color: #ffffff !important;
            }
        `;
    }

    // Inisialisasi awal swatch & active state
    const initialAccent = $('#accent_color').val();
    if (initialAccent && /^#[0-9a-fA-F]{6}$/.test(initialAccent)) {
        applyAccentColorPreview(initialAccent);
    }

    $('.btn-color-swatch').on('click', function() {
        const color = $(this).data('color');
        applyAccentColorPreview(color);
    });

    $('#accent_color_picker').on('input change', function() {
        const color = $(this).val();
        applyAccentColorPreview(color);
    });

    $('#accent_color').on('input', function() {
        const color = $(this).val();
        if (/^#[0-9a-fA-F]{6}$/.test(color)) {
            applyAccentColorPreview(color);
        }
    });

    // 8. Pratinjau Efek Animasi Loader
    $('#btnPreviewLoader').on('click', function() {
        const existingLoader = document.getElementById('global-loader');

        if (existingLoader) {
            existingLoader.classList.remove('hidden');
            setTimeout(() => {
                existingLoader.classList.add('hidden');
            }, 2000);
        } else {
            // Jika loader dimatikan di setting, buat elemen dummy sementara untuk preview
            const previewEl = document.createElement('div');
            previewEl.id = 'preview-loader-temp';
            previewEl.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background-color:rgba(255,255,255,0.4);backdrop-filter:blur(10px);z-index:999999;display:flex;justify-content:center;align-items:center;transition:opacity 0.4s ease;';
            previewEl.innerHTML = `
                <div class="loader-card" style="background-color:rgba(255,255,255,0.85);padding:2rem 3rem;border-radius:24px;box-shadow:0 10px 40px rgba(0,0,0,0.1);display:flex;justify-content:center;align-items:center;">
                    <img src="{{ asset('images/logo/SIMLAB_logo1.svg') }}" style="width:150px;animation:pulse 1.5s infinite ease-in-out alternate;">
                </div>
            `;
            document.body.appendChild(previewEl);

            setTimeout(() => {
                previewEl.style.opacity = '0';
                setTimeout(() => {
                    previewEl.remove();
                }, 400);
            }, 2000);
        }
    });
});
</script>
@endsection
