@extends('layouts.app')

@section('title', 'Verifikasi Tanda Tangan Digital')

@section('content')
<div class="page-heading">
    <div class="page-title mb-3">
        <div class="row align-items-center">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Verifikasi Tanda Tangan Digital</h3>
                <p class="text-subtitle text-muted">Tinjau dan verifikasi tanda tangan digital yang diunggah pengguna</p>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="row mb-4">
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card mb-0 shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body px-3 py-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon yellow me-3"><i class="bi bi-clock-history"></i></div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Menunggu Review</h6>
                            <h4 class="font-extrabold text-warning mb-0">{{ $stats['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card mb-0 shadow-sm border-0 border-start border-success border-4">
                <div class="card-body px-3 py-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon green me-3"><i class="bi bi-check-circle"></i></div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Disetujui</h6>
                            <h4 class="font-extrabold text-success mb-0">{{ $stats['approved'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mt-3 mt-md-0">
            <div class="card mb-0 shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body px-3 py-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon red me-3"><i class="bi bi-x-circle"></i></div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Ditolak</h6>
                            <h4 class="font-extrabold text-danger mb-0">{{ $stats['rejected'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6 mt-3 mt-md-0">
            <div class="card mb-0 shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body px-3 py-3">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon purple me-3"><i class="bi bi-people"></i></div>
                        <div>
                            <h6 class="text-muted font-semibold mb-1">Total Pengguna</h6>
                            <h4 class="font-extrabold mb-0">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Tabel Data -->
    <div class="card">
        <div class="card-header bg-light">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Filter Status Verifikasi</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="all">Semua Status</option>
                        <option value="pending" selected>Menunggu Review (Pending)</option>
                        <option value="approved">Disetujui (Approved)</option>
                        <option value="rejected">Ditolak (Rejected)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body mt-3">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle w-100" id="signatureTable">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>PENGGUNA</th>
                            <th class="text-center">PRATINJAU TTD</th>
                            <th class="text-center">STATUS VERIFIKASI</th>
                            <th class="text-center" style="width: 180px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Penolakan TTD -->
    <div class="modal fade" id="modalRejectSignature" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formRejectSignature" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white"><i class="bi bi-x-circle me-2"></i> Tolak Tanda Tangan Digital</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="alert alert-light-danger mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i> File gambar tanda tangan fisik pengguna akan <strong>dihapus permanen</strong> dari server, dan pengguna harus mengunggah ulang sesuai instruksi.
                        </div>
                        <p class="mb-2">Menolak tanda tangan milik: <strong id="rejectUserName">-</strong></p>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="rejection_note" id="rejection_note" class="form-control" rows="3" placeholder="Contoh: Background tidak putih polos / ada watermark / gambar buram" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i> Tolak & Hapus File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#signatureTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.signatures.index') }}",
            data: function(d) {
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'user_info', name: 'nama_asli' },
            { data: 'signature_preview', name: 'signature_path', orderable: false, searchable: false, className: 'text-center' },
            { data: 'status_badge', name: 'signature_status', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    $('#filterStatus').on('change', function() {
        table.draw();
    });

    // Handle Approve
    $(document).on('click', '.btn-approve-sign', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');

        Swal.fire({
            title: 'Setujui Tanda Tangan?',
            text: `Apakah Anda yakin menyetujui tanda tangan digital ${name}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/verifikasi-ttd/${id}/approve`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Berhasil', res.message, 'success');
                            table.draw();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    });

    // Handle Reject modal open
    let currentRejectId = null;
    $(document).on('click', '.btn-reject-sign', function() {
        currentRejectId = $(this).data('id');
        let name = $(this).data('name');
        $('#rejectUserName').text(name);
        $('#rejection_note').val('');
        $('#modalRejectSignature').modal('show');
    });

    // Submit Reject
    $('#formRejectSignature').on('submit', function(e) {
        e.preventDefault();
        if (!currentRejectId) return;

        let note = $('#rejection_note').val();
        let $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);

        $.ajax({
            url: `/admin/verifikasi-ttd/${currentRejectId}/reject`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                rejection_note: note
            },
            success: function(res) {
                $btn.prop('disabled', false);
                $('#modalRejectSignature').modal('hide');
                if (res.success) {
                    Swal.fire('Ditolak', res.message, 'success');
                    table.draw();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false);
                Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
            }
        });
    });
});
</script>
@endpush
