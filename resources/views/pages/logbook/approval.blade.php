@extends('layouts.app')

@section('content')
<section class="section">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Banner Status TTD Digital Dosen --}}
    @if ($user->role?->slug === 'dosen' && !$user->hasApprovedSignature())
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>TTD Digital Belum Siap:</strong> Status TTD Anda saat ini <strong>{{ $user->signature_status_label }}</strong>.
                Anda wajib memiliki TTD terverifikasi Admin untuk menyetujui logbook atau mengaktifkan Auto-Approve.
            </div>
            <a href="{{ route('profile.index') }}" class="btn btn-sm btn-warning text-dark fw-semibold">
                <i class="bi bi-person-badge me-1"></i> Kelola TTD di Profil
            </a>
        </div>
    @endif

    <div class="card">
        <div class="card-header pb-2">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabPending" type="button" role="tab">
                        Menunggu TTD
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingPertemuans->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'riwayat' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabRiwayat" type="button" role="tab">
                        Riwayat Approval
                        <span class="badge bg-secondary ms-1">{{ $historyPertemuans->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'auto' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabAutoApprove" type="button" role="tab">
                        <i class="bi bi-lightning-charge me-1"></i> Pengaturan Auto-Approve
                        @if ($globalAutoRule && $globalAutoRule->is_enabled)
                            <span class="badge bg-info text-dark ms-1">ON</span>
                        @endif
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body pt-3">
            <div class="tab-content">
                {{-- TAB 1: MENUNGGU TTD --}}
                <div class="tab-pane fade {{ $activeTab === 'pending' ? 'show active' : '' }}" id="tabPending" role="tabpanel">
                    @if ($pendingPertemuans->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-check2-all fs-1 d-block mb-2"></i>
                            Tidak ada logbook praktikum yang menunggu tanda tangan Anda saat ini.
                        </div>
                    @else
                        <form method="POST" action="{{ route('logbook.approval.bulk-approve') }}" id="bulkApproveForm">
                            @csrf
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="checkAllPending">
                                    <label class="form-check-label fw-semibold" for="checkAllPending">Pilih Semua ({{ $pendingPertemuans->count() }})</label>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm" id="btnBulkApprove" disabled>
                                    <i class="bi bi-check2-circle me-1"></i> Setujui & TTD Terpilih (<span id="bulkApproveCount">0</span>)
                                </button>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                @foreach ($pendingPertemuans as $item)
                                    <div class="border rounded p-3">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                            <div class="d-flex align-items-start gap-2">
                                                <input type="checkbox" name="pertemuan_ids[]" value="{{ $item->id }}" class="form-check-input pending-checkbox mt-1">
                                                <div>
                                                    <div class="fw-bold">
                                                        {{ $item->logbook->nama_mk }} ({{ $item->logbook->kelas }}) —
                                                        <span class="text-primary">Minggu ke-{{ $item->minggu_ke }}: {{ $item->judul_praktikum }}</span>
                                                    </div>
                                                    <div class="small text-muted">
                                                        Lab: <strong>{{ $item->logbook->lab->kode_lab ?? '-' }}</strong> &bull;
                                                        PLP: <strong>{{ $item->logbook->plp->nama_asli ?? '-' }}</strong> &bull;
                                                        Pelaksanaan: {{ $item->tanggal_pelaksanaan->translatedFormat('d M Y') }} ({{ $item->jam_pelaksanaan }})
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1">
                                                <a href="{{ route('logbook.export-pdf', [$item->logbook_praktikum_id, 'pertemuan_id' => $item->id]) }}" target="_blank"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i> Preview PDF
                                                </a>
                                                <button type="button" class="btn btn-sm btn-success btn-single-approve"
                                                        data-url="{{ route('logbook.approval.approve', $item->id) }}">
                                                    <i class="bi bi-pen me-1"></i> Setuju & TTD
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger btn-open-reject"
                                                        data-url="{{ route('logbook.approval.reject', $item->id) }}"
                                                        data-title="{{ $item->logbook->nama_mk }} (Minggu ke-{{ $item->minggu_ke }})">
                                                    <i class="bi bi-x-circle me-1"></i> Tolak
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row g-2 mt-2 pt-2 border-top small">
                                            <div class="col-md-4">
                                                <strong class="d-block">A. Peminjaman Alat:</strong>
                                                @if ($item->hasPeralatan())
                                                    <ul class="mb-0 ps-3">
                                                        @foreach ($item->peralatan as $alat)
                                                            <li>{{ $alat['nama_alat'] ?? '-' }} ({{ $alat['jumlah'] ?? '1' }}, {{ $alat['kondisi'] ?? 'Baik' }})</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">Tidak ada peminjaman alat</span>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <strong class="d-block">B. Aktivitas:</strong>
                                                @if (!empty($item->aktivitas))
                                                    <ol class="mb-0 ps-3">
                                                        @foreach ($item->aktivitas as $act)
                                                            <li>{{ $act }}</li>
                                                        @endforeach
                                                    </ol>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <strong class="d-block">C. Permasalahan:</strong>
                                                @if (!empty($item->permasalahan))
                                                    <ul class="mb-0 ps-3">
                                                        @foreach ($item->permasalahan as $pr)
                                                            <li>{{ $pr['masalah'] ?? '-' }} &rarr; <em>{{ $pr['solusi'] ?? '-' }}</em></li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">Tidak ada kendala</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    @endif
                </div>

                {{-- TAB 2: RIWAYAT APPROVAL --}}
                <div class="tab-pane fade {{ $activeTab === 'riwayat' ? 'show active' : '' }}" id="tabRiwayat" role="tabpanel">
                    @if ($historyPertemuans->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">Belum ada riwayat persetujuan logbook.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>MK & Pertemuan</th>
                                        <th>PLP & Lab</th>
                                        <th>Tanggal Pelaksanaan</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historyPertemuans as $h)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $h->logbook->nama_mk }} ({{ $h->logbook->kelas }})</div>
                                                <small class="text-muted">Minggu ke-{{ $h->minggu_ke }}: {{ $h->judul_praktikum }}</small>
                                            </td>
                                            <td>
                                                <div>{{ $h->logbook->plp->nama_asli ?? '-' }}</div>
                                                <small class="text-muted">{{ $h->logbook->lab->kode_lab ?? '-' }}</small>
                                            </td>
                                            <td>{{ $h->tanggal_pelaksanaan->translatedFormat('d M Y') }}</td>
                                            <td>
                                                <span class="badge {{ $h->status_badge_class }}">{{ $h->status_label }}</span>
                                                @if ($h->approved_at)
                                                    <small class="text-muted d-block">{{ $h->approved_at->format('d/m/Y H:i') }}</small>
                                                @elseif ($h->rejection_note)
                                                    <small class="text-danger d-block">{{ $h->rejection_note }}</small>
                                                @endif
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <a href="{{ route('logbook.export-pdf', [$h->logbook_praktikum_id, 'pertemuan_id' => $h->id]) }}" target="_blank"
                                                   class="btn btn-sm btn-outline-danger" title="Cetak PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                                @if ($h->status === \App\Models\LogbookPertemuan::STATUS_APPROVED)
                                                    <button type="button" class="btn btn-sm btn-outline-warning text-dark btn-open-revoke"
                                                            data-url="{{ route('logbook.approval.revoke', $h->id) }}"
                                                            data-title="{{ $h->logbook->nama_mk }} (Minggu ke-{{ $h->minggu_ke }})">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Batalkan TTD
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- TAB 3: PENGATURAN AUTO-APPROVE ATAS IZIN DOSEN --}}
                <div class="tab-pane fade {{ $activeTab === 'auto' ? 'show active' : '' }}" id="tabAutoApprove" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <form method="POST" action="{{ route('logbook.approval.auto-approve.save') }}" class="border rounded p-3 h-100">
                                @csrf
                                <h6 class="fw-bold mb-1">Izin Auto-Approve Global (Semua PLP)</h6>
                                <p class="text-muted small mb-3">
                                    Jika diaktifkan, setiap logbook praktikum yang menunjuk Anda sebagai Dosen Pengampu akan otomatis disetujui & dibubuhi TTD digital Anda tanpa harus klik satu per satu. Sistem tetap mengirimkan notifikasi dan menyediakan tombol <strong>Batalkan TTD</strong>.
                                </p>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_enabled" id="globalAutoEnabled" value="1"
                                           @checked($globalAutoRule && $globalAutoRule->is_enabled)>
                                    <label class="form-check-label fw-bold" for="globalAutoEnabled">Aktifkan Auto-Approve Logbook</label>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Berlaku Sampai Tanggal (Opsional)</label>
                                    <input type="date" name="valid_until" class="form-control form-control-sm"
                                           value="{{ $globalAutoRule?->valid_until?->format('Y-m-d') }}">
                                    <small class="text-muted">Kosongkan jika berlaku terus sampai Anda matikan.</small>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="consent" id="globalConsent" value="1"
                                           @checked($globalAutoRule && $globalAutoRule->is_enabled && $globalAutoRule->consented_at)>
                                    <label class="form-check-label small" for="globalConsent">
                                        Saya selaku Dosen Pengampu memberikan izin resmi kepada SIMLAB untuk membubuhkan tanda tangan digital saya secara otomatis pada pengajuan logbook praktikum atas nama saya.
                                    </label>
                                </div>

                                @if ($globalAutoRule && $globalAutoRule->consented_at)
                                    <div class="small text-muted mb-3">
                                        <i class="bi bi-clock-history me-1"></i>Persetujuan tercatat: {{ $globalAutoRule->consented_at->format('d M Y H:i') }}
                                    </div>
                                @endif

                                <button type="submit" class="btn btn-primary btn-sm" @if ($user->role?->slug === 'dosen' && !$user->hasApprovedSignature()) disabled @endif>
                                    Simpan Pengaturan Global
                                </button>
                            </form>
                        </div>

                        <div class="col-lg-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="fw-bold mb-1">Aturan Khusus Per PLP (Opsional)</h6>
                                <p class="text-muted small mb-3">Gunakan ini jika Anda hanya ingin mengizinkan Auto-Approve untuk PLP tertentu saja.</p>

                                <form method="POST" action="{{ route('logbook.approval.auto-approve.save') }}" class="row g-2 align-items-end mb-3">
                                    @csrf
                                    <input type="hidden" name="is_enabled" value="1">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">Pilih PLP</label>
                                        <select name="plp_id" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih PLP --</option>
                                            @foreach ($plps as $plp)
                                                <option value="{{ $plp->id }}">{{ $plp->nama_asli }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">Berlaku Sampai (Opsional)</label>
                                        <input type="date" name="valid_until" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="consent" id="plpConsent" value="1" required>
                                            <label class="form-check-label small" for="plpConsent">
                                                Saya menyetujui izin Auto-Approve TTD digital untuk PLP terpilih.
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-outline-primary btn-sm" @if ($user->role?->slug === 'dosen' && !$user->hasApprovedSignature()) disabled @endif>
                                            <i class="bi bi-plus-lg me-1"></i> Tambah Izin Per PLP
                                        </button>
                                    </div>
                                </form>

                                @if ($plpAutoRules->isNotEmpty())
                                    <ul class="list-group list-group-flush">
                                        @foreach ($plpAutoRules as $rule)
                                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-semibold small">{{ $rule->plp->nama_asli ?? '-' }}</div>
                                                    <small class="text-muted">
                                                        Status: {{ $rule->is_enabled ? 'Aktif' : 'Nonaktif' }}
                                                        @if ($rule->valid_until)
                                                            &bull; s/d {{ $rule->valid_until->format('d M Y') }}
                                                        @endif
                                                    </small>
                                                </div>
                                                <form method="POST" action="{{ route('logbook.approval.auto-approve.destroy', $rule->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<form method="POST" id="singleApproveForm" class="d-none">@csrf</form>

{{-- Modal Tolak dengan Catatan --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="rejectForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tolak Logbook — <span id="rejectTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">Catatan Revisi untuk PLP <span class="text-danger">*</span></label>
                <textarea name="rejection_note" class="form-control" rows="3" placeholder="Jelaskan bagian yang perlu diperbaiki..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Kembalikan untuk Revisi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Revoke / Batalkan TTD --}}
<div class="modal fade" id="revokeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="revokeForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Batalkan TTD — <span id="revokeTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Pembatalan akan mencabut pengesahan TTD digital Anda pada pertemuan ini dan mengembalikan logbook ke PLP untuk direvisi.</p>
                <label class="form-label fw-semibold">Alasan Pembatalan <span class="text-danger">*</span></label>
                <textarea name="revoked_reason" class="form-control" rows="3" placeholder="Alasan pembatalan..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning text-dark fw-semibold">Batalkan TTD Sekarang</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        function syncApproveCount() {
            const count = $('.pending-checkbox:checked').length;
            $('#bulkApproveCount').text(count);
            $('#btnBulkApprove').prop('disabled', count === 0);
        }

        $('#checkAllPending').on('change', function() {
            $('.pending-checkbox').prop('checked', $(this).is(':checked'));
            syncApproveCount();
        });
        $(document).on('change', '.pending-checkbox', syncApproveCount);

        $(document).on('click', '.btn-single-approve', function() {
            $('#singleApproveForm').attr('action', $(this).data('url')).submit();
        });

        $(document).on('click', '.btn-open-reject', function() {
            $('#rejectForm').attr('action', $(this).data('url'));
            $('#rejectTitle').text($(this).data('title') || '');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('rejectModal')).show();
        });

        $(document).on('click', '.btn-open-revoke', function() {
            $('#revokeForm').attr('action', $(this).data('url'));
            $('#revokeTitle').text($(this).data('title') || '');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('revokeModal')).show();
        });
    });
</script>
@endsection
