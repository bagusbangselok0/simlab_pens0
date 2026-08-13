{{-- Halaman untuk satpam konfirmasi presensi mahasiswa --}}
@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <p class="text-subtitle text-muted">Kelola konfirmasi presensi masuk dan keluar mahasiswa</p>
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Daftar Presensi Menunggu Konfirmasi</h4>
                        <p class="text-muted">Presensi yang perlu dikonfirmasi oleh satpam</p>
                    </div>
                    <div class="card-body">
                        @if ($presensiMenunggu->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped" id="presensiTable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Mahasiswa</th>
                                            <th>Laboratorium</th>
                                            <th>Tujuan</th>
                                            <th>Jenis Presensi</th>
                                            <th>Satpam Dipilih</th>
                                            <th>Waktu Request</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($presensiMenunggu as $index => $presensi)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $presensi->mahasiswa->full_name }}</strong><br>
                                                    <small class="text-muted">{{ $presensi->mahasiswa->email }}</small>
                                                </td>
                                                <td>{{ $presensi->peminjamanLab->lab->nama_lab }}</td>
                                                <td>{{ $presensi->peminjamanLab->tujuan }}</td>
                                                <td>
                                                    @if (in_array($presensi->status_presensi, ['menunggu_konfirmasi_masuk']))
                                                        <span class="badge bg-primary">Presensi Masuk</span>
                                                    @elseif($presensi->status_presensi === 'menunggu_konfirmasi_keluar')
                                                        <span class="badge bg-warning">Presensi Keluar</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (in_array($presensi->status_presensi, ['menunggu_konfirmasi_masuk']))
                                                        {{ $presensi->satpamMasuk->full_name ?? 'N/A' }}
                                                    @elseif($presensi->status_presensi === 'menunggu_konfirmasi_keluar')
                                                        {{ $presensi->satpamKeluar->full_name ?? 'N/A' }}
                                                    @endif
                                                </td>
                                                <td>{{ $presensi->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <div class="d-flex flex-column gap-2">
                                                        {{-- Tombol klaim sendiri sebagai satpam bertugas jika belum ditugaskan ke satpam saat ini --}}
                                                        @if (
                                                            ($presensi->status_presensi === 'menunggu_konfirmasi_masuk' && Auth::user()->id !== $presensi->satpamMasuk?->id) ||
                                                            ($presensi->status_presensi === 'menunggu_konfirmasi_keluar' && Auth::user()->id !== $presensi->satpamKeluar?->id)
                                                        )
                                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                                onclick="confirmAssign({{ $presensi->id }}, '{{ $presensi->status_presensi === 'menunggu_konfirmasi_masuk' ? 'masuk' : 'keluar' }}')">
                                                                <i class="bi bi-person-check"></i> Klaim Satpam
                                                            </button>
                                                        @endif

                                                        {{-- Tampilkan tombol konfirmasi hanya jika satpam yang login adalah yang dipilih untuk konfirmasi --}}
                                                        @if (
                                                            (Auth::user()->id === $presensi->satpamMasuk?->id && $presensi->status_presensi === 'menunggu_konfirmasi_masuk') ||
                                                            (Auth::user()->id === $presensi->satpamKeluar?->id && $presensi->status_presensi === 'menunggu_konfirmasi_keluar')
                                                        )
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-sm btn-success"
                                                                    onclick="confirmPresensi({{ $presensi->id }}, 'approve', '{{ $presensi->status_presensi === 'menunggu_konfirmasi_masuk' ? 'masuk' : 'keluar' }}', {{ $presensi->peminjaman_lab_id }})">
                                                                    <i class="bi bi-check-circle"></i> Setuju
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger"
                                                                    onclick="confirmPresensi({{ $presensi->id }}, 'reject', '{{ $presensi->status_presensi === 'menunggu_konfirmasi_masuk' ? 'masuk' : 'keluar' }}', {{ $presensi->peminjaman_lab_id }})">
                                                                    <i class="bi bi-x-circle"></i> Tolak
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-muted">Tidak ada presensi menunggu konfirmasi</h5>
                                <p class="text-muted">Semua presensi sudah dikonfirmasi atau tidak ada request presensi saat
                                    ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Modal Konfirmasi --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Konfirmasi Presensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage"></p>
                    <div class="mb-3">
                        <a id="btnCetakPdf" href="#" target="_blank" class="btn btn-sm btn-primary">
                            <i class="bi bi-printer"></i> Cek Peminjaman
                        </a>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Pastikan:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Mahasiswa benar-benar hadir di lokasi</li>
                            <li>Identitas mahasiswa sesuai dengan data peminjaman</li>
                            <li>Waktu presensi sesuai dengan jadwal peminjaman</li>
                            <li>Anda bisa cek peminjaman terlebih dahulu sebelum konfirmasi</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn" id="confirmBtn" onclick="executeConfirm()">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <span id="confirmBtnText">Konfirmasi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Klaim Satpam --}}
    <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignModalLabel">Klaim Satpam Bertugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="assignMessage"></p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Catatan:</strong> Setelah diklaim, presensi akan menjadi tanggung jawab Anda untuk dikonfirmasi.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="assignBtn" onclick="executeAssign()">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <span id="assignBtnText">Klaim Satpam</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let currentPresensiId = null;
        let currentAction = null;

        function confirmPresensi(presensiId, action, jenis, peminjamanId) {
            currentPresensiId = presensiId;
            currentAction = action;

            // Set data-peminjaman-id on the button
            $('#btnCetakPdf').attr('data-peminjaman-id', peminjamanId);
            $('#btnCetakPdf').data('peminjaman-id', peminjamanId);

            const jenisText = jenis === 'masuk' ? 'masuk' : 'keluar';
            const actionText = action === 'approve' ? 'menyetujui' : 'menolak';

            $('#confirmMessage').html(`
                Apakah Anda yakin ingin <strong>${actionText}</strong> presensi ${jenisText}
                untuk mahasiswa ini?
            `);

            if (action === 'approve') {
                $('#confirmBtn').removeClass('btn-danger').addClass('btn-success');
                $('#confirmBtnText').text('Setujui');
            } else {
                $('#confirmBtn').removeClass('btn-success').addClass('btn-danger');
                $('#confirmBtnText').text('Tolak');
            }

            $('#confirmModal').modal('show');
        }

        function confirmAssign(presensiId, jenis) {
            currentAssignId = presensiId;
            const jenisText = jenis === 'masuk' ? 'presensi masuk' : 'presensi keluar';
            $('#assignMessage').html(`
                Anda akan mengambil alih <strong>${jenisText}</strong> ini sebagai satpam bertugas.
                Setelah diklaim, hanya Anda yang dapat menyetujui atau menolak presensi ini.
            `);
            $('#assignModal').modal('show');
        }

        $('#btnCetakPdf').click(function() {
            const peminjamanId = $(this).data('peminjaman-id');
            window.open('{{ route('satpam.cetak', ':id') }}'.replace(':id', peminjamanId), '_blank');
        });

        function executeConfirm() {
            if (!currentPresensiId || !currentAction) return;

            // Show loading
            $('#confirmBtn').prop('disabled', true);
            $('#confirmBtn .spinner-border').removeClass('d-none');

            $.ajax({
                url: '{{ route('satpam.confirm', ':id') }}'.replace(':id', currentPresensiId),
                method: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: currentAction
                },
                success: function(response) {
                    $('#confirmModal').modal('hide');

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

                    // Reload page to update table
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    // Hide loading
                    $('#confirmBtn').prop('disabled', false);
                    $('#confirmBtn .spinner-border').addClass('d-none');

                    let message = 'Terjadi kesalahan saat memproses konfirmasi.';
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
            });
        }

        function executeAssign() {
            if (!currentAssignId) return;

            $('#assignBtn').prop('disabled', true);
            $('#assignBtn .spinner-border').removeClass('d-none');

            $.ajax({
                url: '{{ route('satpam.assign', ':id') }}'.replace(':id', currentAssignId),
                method: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#assignModal').modal('hide');

                    if (typeof Toastify !== 'undefined') {
                        Toastify({
                            text: response.message,
                            backgroundColor: "#0d6efd",
                            position: "right",
                        }).showToast();
                    } else {
                        alert(response.message);
                    }

                    setTimeout(function() {
                        location.reload();
                    }, 800);
                },
                error: function(xhr) {
                    $('#assignBtn').prop('disabled', false);
                    $('#assignBtn .spinner-border').addClass('d-none');

                    let message = 'Terjadi kesalahan saat mengklaim satpam bertugas.';
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
            });
        }

        $(document).ready(function() {
            // Initialize DataTable if needed
            if ($('#presensiTable').length && typeof $.fn.DataTable !== 'undefined') {
                $('#presensiTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [
                        [6, 'desc']
                    ], // Order by waktu request
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
                    }
                });
            }
        });
    </script>
@endsection
