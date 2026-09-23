@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1">Pengaturan Persetujuan</h5>
                <p class="text-muted mb-0">Atur perilaku approval untuk setiap laboratorium.</p>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
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
    </section>
@endsection