{{-- Halaman untuk presensi mahasiswa yang telah disetujui meminjam lab --}}
@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <p class="text-subtitle text-muted">Lakukan presensi masuk dan keluar peminjaman laboratorium</p>
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Daftar Kunjungan Lab</h4>
                        <p class="text-muted">Pilih Peminjaman Lab yang ingin Anda presensi</p>
                    </div>
                    <div class="card-body">
                        @if ($peminjamanLabs->count() > 0)
                            <div class="row">
                                @foreach ($peminjamanLabs as $peminjaman)
                                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                                        <div class="card border-primary">
                                            <div class="card-body">
                                                <h5 class="card-title">{{ $peminjaman->lab->nama_lab }}</h5>
                                                @php
                                                    $waktuMulaiTz = $peminjaman->waktu_mulai->setTimezone(
                                                        'Asia/Jakarta',
                                                    );
                                                    $waktuSelesaiTz = $peminjaman->waktu_selesai->setTimezone(
                                                        'Asia/Jakarta',
                                                    );
                                                @endphp
                                                <p class="card-text">
                                                    <strong>Tujuan:</strong> {{ $peminjaman->tujuan }}<br>
                                                    <strong>Waktu:</strong> {{ $waktuMulaiTz->format('d/m/Y H:i') }} -
                                                    {{ $waktuSelesaiTz->format('d/m/Y H:i') }}<br>
                                                    <strong>Status:</strong>
                                                    @if ($peminjaman->presensiHariIni && $peminjaman->presensiHariIni->status_presensi == 'didalam')
                                                        <span class="badge bg-success">Sedang Di Dalam Lab</span>
                                                    @elseif($peminjaman->presensiHariIni && $peminjaman->presensiHariIni->status_presensi == 'selesai')
                                                        <span class="badge bg-secondary">Sudah Selesai</span>
                                                    @elseif(
                                                        $peminjaman->presensiHariIni &&
                                                            in_array($peminjaman->presensiHariIni->status_presensi, [
                                                                'menunggu_konfirmasi_masuk',
                                                                'menunggu_konfirmasi_keluar',
                                                            ]))
                                                        <span class="badge bg-warning">Menunggu Konfirmasi</span>
                                                    @elseif($peminjaman->presensiHariIni && $peminjaman->presensiHariIni->status_presensi == 'tidak_hadir')
                                                        <span class="badge bg-danger">Tidak Hadir</span>
                                                    @else
                                                        <span class="badge bg-warning">Belum Presensi Masuk</span>
                                                    @endif
                                                </p>

                                                {{-- Informasi waktu --}}
                                                @php
                                                    $now = now('Asia/Jakarta'); // pastikan menggunakan zona waktu Indonesia
                                                    $batasMalam = now('Asia/Jakarta')->setTime(21, 0, 0);
                                                    $waktuMulai = $peminjaman->waktu_mulai->setTimezone('Asia/Jakarta');
                                                    $bisaPresensiMasuk =
                                                        $now->gte($waktuMulai) && $now->lte($batasMalam);
                                                    $bisaPresensiKeluar = $now->lte($batasMalam);
                                                @endphp

                                                @if (!$peminjaman->presensiHariIni || $peminjaman->presensiHariIni->status_presensi === 'belum_hadir')
                                                    @if ($bisaPresensiMasuk)
                                                        <button type="button" class="btn btn-primary btn-sm w-100 mb-2"
                                                            onclick="showPresensiModal('masuk', {{ $peminjaman->id }}, '{{ $peminjaman->lab->nama_lab }}')">
                                                            <i class="bi bi-box-arrow-in-right"></i> Presensi Masuk
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-secondary btn-sm w-100 mb-2"
                                                            disabled>
                                                            <i class="bi bi-clock"></i>
                                                            @if ($now->lt($peminjaman->waktu_mulai))
                                                                Presensi dibuka
                                                                {{ $peminjaman->waktu_mulai->locale('id')->diffForHumans() }}
                                                            @else
                                                                Presensi ditutup (lewat jam 21:00)
                                                            @endif
                                                        </button>
                                                    @endif
                                                @elseif($peminjaman->presensiHariIni->status_presensi === 'menunggu_konfirmasi_masuk')
                                                    <button type="button" class="btn btn-warning btn-sm w-100 mb-2"
                                                        disabled>
                                                        <i class="bi bi-clock"></i> Menunggu Konfirmasi Satpam
                                                    </button>
                                                @elseif($peminjaman->presensiHariIni->status_presensi === 'didalam')
                                                    @if ($bisaPresensiKeluar)
                                                        <button type="button" class="btn btn-danger btn-sm w-100 mb-2"
                                                            onclick="showPresensiModal('keluar', {{ $peminjaman->id }}, '{{ $peminjaman->lab->nama_lab }}')">
                                                            <i class="bi bi-box-arrow-right"></i> Presensi Keluar
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-secondary btn-sm w-100 mb-2"
                                                            disabled>
                                                            <i class="bi bi-clock"></i> Presensi ditutup (lewat jam 21:00)
                                                        </button>
                                                    @endif
                                                @elseif($peminjaman->presensiHariIni->status_presensi === 'menunggu_konfirmasi_keluar')
                                                    <button type="button" class="btn btn-warning btn-sm w-100 mb-2"
                                                        disabled>
                                                        <i class="bi bi-clock"></i> Menunggu Konfirmasi Satpam
                                                    </button>
                                                @elseif($peminjaman->presensiHariIni->status_presensi === 'tidak_hadir')
                                                    <button type="button" class="btn btn-danger btn-sm w-100 mb-2"
                                                        disabled>
                                                        <i class="bi bi-x-circle"></i> Tidak Hadir
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-sm w-100 mb-2"
                                                        disabled>
                                                        <i class="bi bi-check-circle"></i> Presensi Selesai
                                                    </button>
                                                @endif

                                                {{-- Status Presensi --}}
                                                @if ($peminjaman->presensiHariIni && $peminjaman->presensiHariIni->status_presensi !== 'belum_hadir')
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            @if ($peminjaman->presensiHariIni->status_presensi == 'menunggu_konfirmasi_masuk')
                                                                <i class="bi bi-clock text-warning"></i> Menunggu konfirmasi
                                                                satpam (Masuk)
                                                            @elseif($peminjaman->presensiHariIni->status_presensi == 'didalam')
                                                                <i class="bi bi-check-circle text-success"></i> Masuk:
                                                                {{ $peminjaman->presensiHariIni->jam_masuk ? $peminjaman->presensiHariIni->jam_masuk->setTimezone('Asia/Jakarta')->format('H:i') : '-' }}
                                                            @elseif($peminjaman->presensiHariIni->status_presensi == 'menunggu_konfirmasi_keluar')
                                                                <i class="bi bi-clock text-warning"></i> Menunggu konfirmasi
                                                                satpam (Keluar)
                                                            @elseif($peminjaman->presensiHariIni->status_presensi == 'selesai')
                                                                <i class="bi bi-check-circle text-secondary"></i> Keluar:
                                                                {{ $peminjaman->presensiHariIni->jam_keluar ? $peminjaman->presensiHariIni->jam_keluar->setTimezone('Asia/Jakarta')->format('H:i') : '-' }}
                                                            @elseif($peminjaman->presensiHariIni->status_presensi == 'tidak_hadir')
                                                                <i class="bi bi-x-circle text-danger"></i> Tidak Hadir
                                                            @endif
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-muted">Tidak ada peminjaman lab yang disetujui</h5>
                                <p class="text-muted">Anda belum memiliki peminjaman lab yang dapat dipresensi.</p>
                                <a href="{{ route('peminjaman.index') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Ajukan Peminjaman Lab
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Modal Presensi --}}
    <div class="modal fade" id="presensiModal" tabindex="-1" aria-labelledby="presensiModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="presensiModalLabel">Presensi <span id="tipePresensi"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="presensiForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="peminjaman_id" name="peminjaman_id">
                        <input type="hidden" id="tipe_presensi" name="tipe_presensi">

                        <div class="mb-3">
                            <label for="satpam_id" class="form-label">Pilih Satpam yang Bertugas</label>
                            <select class="form-select @error('satpam_id') is-invalid @enderror" id="satpam_id"
                                name="satpam_id" required>
                                <option value="">-- Pilih Satpam --</option>
                                @foreach ($satpamList as $satpam)
                                    <option value="{{ $satpam->id }}">{{ $satpam->full_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Silakan pilih satpam yang bertugas.</div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Informasi:</strong> Setelah Anda submit presensi, satpam yang dipilih akan menerima
                            notifikasi untuk konfirmasi.
                            Status akan berubah menjadi "Didalam" setelah satpam mengkonfirmasi.
                            <br><br>
                            <strong>Batas waktu presensi:</strong> Maksimal pukul 21:00 WIB
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            <span id="submitText">Submit Presensi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function showPresensiModal(tipe, peminjamanId, labName) {
            $('#peminjaman_id').val(peminjamanId);
            $('#tipe_presensi').val(tipe);

            if (tipe === 'masuk') {
                $('#presensiModalLabel').html('Presensi Masuk - ' + labName);
                $('#tipePresensi').text('Masuk');
                $('#submitText').text('Presensi Masuk');
                $('#submitBtn').removeClass('btn-danger').addClass('btn-primary');
            } else {
                $('#presensiModalLabel').html('Presensi Keluar - ' + labName);
                $('#tipePresensi').text('Keluar');
                $('#submitText').text('Presensi Keluar');
                $('#submitBtn').removeClass('btn-primary').addClass('btn-danger');
            }

            // Reset form
            $('#presensiForm')[0].reset();
            $('.is-invalid').removeClass('is-invalid');

            $('#presensiModal').modal('show');
        }

        $(document).ready(function() {
            // Handle form submission
            $('#presensiForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');

                // Show loading
                $('#submitBtn').prop('disabled', true);
                $('#submitBtn .spinner-border').removeClass('d-none');

                $.ajax({
                    url: '{{ route('presensi.store') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#presensiModal').modal('hide');

                        // Show success message
                        if (typeof Toastify !== 'undefined') {
                            Toastify({
                                text: response.message,
                                backgroundColor: "#28a745",
                                position: "right",
                            }).showToast();
                        } else {
                            alert(response.message);
                        }

                        // Reload page to update status
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        // Hide loading
                        $('#submitBtn').prop('disabled', false);
                        $('#submitBtn .spinner-border').addClass('d-none');

                        if (xhr.status === 422) {
                            // Validation errors
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                $('#' + field).addClass('is-invalid');
                            });

                            // Show general error if exists
                            if (xhr.responseJSON.message) {
                                if (typeof Toastify !== 'undefined') {
                                    Toastify({
                                        text: xhr.responseJSON.message,
                                        backgroundColor: "#dc3545",
                                        position: "right",
                                    }).showToast();
                                } else {
                                    alert(xhr.responseJSON.message);
                                }
                            }
                        } else {
                            // General error
                            let message = 'Terjadi kesalahan saat presensi.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            if (typeof Toastify !== 'undefined') {
                                Toastify({
                                    text: message,
                                    backgroundColor: "#dc3545",
                                    position: "right",
                                }).showToast();
                            } else {
                                alert(message);
                            }
                        }
                    }
                });
            });
        });
    </script>
@endsection
