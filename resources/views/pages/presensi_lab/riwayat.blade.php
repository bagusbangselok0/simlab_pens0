{{-- Halaman riwayat presensi mahasiswa --}}
@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <p class="text-subtitle text-muted">Lihat riwayat presensi laboratorium Anda</p>
    </div>
    <div class="page-content">
        {{-- Statistik Ringkasan --}}
        <section class="row">
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon purple mb-2">
                                    <i class="bi bi-clipboard-data"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Total Presensi</h6>
                                <h6 class="font-extrabold mb-0">{{ $totalPresensi }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon green mb-2">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Selesai</h6>
                                <h6 class="font-extrabold mb-0">{{ $totalSelesai }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon blue mb-2">
                                    <i class="bi bi-door-open"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Di Dalam Lab</h6>
                                <h6 class="font-extrabold mb-0">{{ $totalDidalam }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon red mb-2">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Tidak Hadir</h6>
                                <h6 class="font-extrabold mb-0">{{ $totalTidakHadir }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Filter --}}
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><i class="bi bi-funnel"></i> Filter</h4>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('presensi.riwayat') }}" id="filterForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-md-3">
                                    <label for="filter_status" class="form-label">Status</label>
                                    <select class="form-select" id="filter_status" name="status">
                                        <option value="semua" {{ request('status', 'semua') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                                        <option value="belum_hadir" {{ request('status') == 'belum_hadir' ? 'selected' : '' }}>Belum Hadir</option>
                                        <option value="menunggu_konfirmasi_masuk" {{ request('status') == 'menunggu_konfirmasi_masuk' ? 'selected' : '' }}>Menunggu Konfirmasi Masuk</option>
                                        <option value="menunggu_konfirmasi_keluar" {{ request('status') == 'menunggu_konfirmasi_keluar' ? 'selected' : '' }}>Menunggu Konfirmasi Keluar</option>
                                        <option value="didalam" {{ request('status') == 'didalam' ? 'selected' : '' }}>Di Dalam Lab</option>
                                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                        <option value="tidak_hadir" {{ request('status') == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <label for="filter_lab" class="form-label">Laboratorium</label>
                                    <select class="form-select" id="filter_lab" name="lab">
                                        <option value="">Semua Lab</option>
                                        @foreach ($labList as $lab)
                                            <option value="{{ $lab->id }}" {{ request('lab') == $lab->id ? 'selected' : '' }}>{{ $lab->nama_lab }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <label for="filter_tanggal_mulai" class="form-label">Dari Tanggal</label>
                                    <input type="date" class="form-control" id="filter_tanggal_mulai" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                                </div>
                                <div class="col-12 col-md-2">
                                    <label for="filter_tanggal_selesai" class="form-label">Sampai Tanggal</label>
                                    <input type="date" class="form-control" id="filter_tanggal_selesai" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search"></i> Cari
                                        </button>
                                        <a href="{{ route('presensi.riwayat') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        {{-- Tabel Riwayat --}}
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Riwayat Presensi</h4>
                            <p class="text-muted mb-0">Menampilkan {{ $riwayatPresensi->total() }} data presensi</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($riwayatPresensi->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-striped" id="tabelRiwayat">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Laboratorium</th>
                                            <th>Tujuan</th>
                                            <th>Jam Masuk</th>
                                            <th>Jam Keluar</th>
                                            <th>Durasi</th>
                                            <th>Satpam Masuk</th>
                                            <th>Satpam Keluar</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($riwayatPresensi as $index => $presensi)
                                            <tr>
                                                <td>{{ $riwayatPresensi->firstItem() + $index }}</td>
                                                <td>
                                                    <span class="fw-semibold">{{ $presensi->tanggal_presensi->format('d/m/Y') }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $presensi->tanggal_presensi->locale('id')->dayName }}</small>
                                                </td>
                                                <td>
                                                    @if ($presensi->peminjamanLab && $presensi->peminjamanLab->lab)
                                                        <span class="badge bg-light-primary">{{ $presensi->peminjamanLab->lab->nama_lab }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($presensi->peminjamanLab)
                                                        <span title="{{ $presensi->peminjamanLab->tujuan }}">
                                                            {{ Str::limit($presensi->peminjamanLab->tujuan, 30) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($presensi->jam_masuk)
                                                        <span class="text-success fw-semibold">
                                                            <i class="bi bi-box-arrow-in-right"></i>
                                                            {{ $presensi->jam_masuk->setTimezone('Asia/Jakarta')->format('H:i') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($presensi->jam_keluar)
                                                        <span class="text-danger fw-semibold">
                                                            <i class="bi bi-box-arrow-right"></i>
                                                            {{ $presensi->jam_keluar->setTimezone('Asia/Jakarta')->format('H:i') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($presensi->jam_masuk && $presensi->jam_keluar)
                                                        @php
                                                            $diffMinutes = $presensi->jam_masuk->diffInMinutes($presensi->jam_keluar);
                                                            $hours = floor($diffMinutes / 60);
                                                            $minutes = $diffMinutes % 60;
                                                        @endphp
                                                        <span class="badge bg-light-info">
                                                            {{ $hours > 0 ? $hours . ' jam ' : '' }}{{ $minutes }} menit
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($presensi->satpamMasuk)
                                                        <small>{{ $presensi->satpamMasuk->nama_asli }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($presensi->satpamKeluar)
                                                        <small>{{ $presensi->satpamKeluar->nama_asli }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @switch($presensi->status_presensi)
                                                        @case('belum_hadir')
                                                            <span class="badge bg-warning">Belum Hadir</span>
                                                            @break
                                                        @case('menunggu_konfirmasi_masuk')
                                                            <span class="badge bg-info">Menunggu Konfirmasi Masuk</span>
                                                            @break
                                                        @case('menunggu_konfirmasi_keluar')
                                                            <span class="badge bg-info">Menunggu Konfirmasi Keluar</span>
                                                            @break
                                                        @case('didalam')
                                                            <span class="badge bg-success">Di Dalam Lab</span>
                                                            @break
                                                        @case('selesai')
                                                            <span class="badge bg-secondary">Selesai</span>
                                                            @break
                                                        @case('tidak_hadir')
                                                            <span class="badge bg-danger">Tidak Hadir</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-light">{{ $presensi->status_presensi }}</span>
                                                    @endswitch
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <div class="d-flex justify-content-center mt-4">
                                {{ $riwayatPresensi->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-muted">Belum ada riwayat presensi</h5>
                                <p class="text-muted">Riwayat presensi Anda akan muncul di sini setelah melakukan presensi.</p>
                                <a href="{{ route('presensi.index') }}" class="btn btn-primary">
                                    <i class="bi bi-calendar-check"></i> Ke Halaman Presensi
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
