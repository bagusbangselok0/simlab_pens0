{{-- Upload TTD dengan Petunjuk & Status Verifikasi --}}
<style>
    .signature-header {
        gap: .75rem;
        flex-wrap: wrap;
    }

    .signature-header h5 {
        overflow-wrap: anywhere;
    }

    .signature-preview-box {
        max-width: 100%;
    }

    .signature-preview-box img {
        max-width: min(180px, 100%) !important;
        height: auto;
    }

    @media (max-width: 575.98px) {
        .signature-card-body {
            padding: 1rem .75rem !important;
        }

        .signature-header {
            align-items: flex-start !important;
        }

        .signature-header>* {
            width: 100%;
        }

        .signature-header .badge {
            white-space: normal;
            display: inline-block;
        }

        .signature-guide ul {
            padding-left: 1.25rem !important;
        }

        .signature-upload-form .btn {
            width: 100%;
        }

        .signature-preview-box {
            display: block !important;
            width: 100%;
        }
    }
</style>
<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center signature-header">
        <h5 class="card-title mb-0"><i class="bi bi-pen me-2"></i> Tanda Tangan Digital</h5>
        <div>
            <span class="{{ $user->signature_status_badge_class }} px-3 py-2 fs-7" id="signatureStatusBadge">
                <i class="bi bi-shield-check me-1"></i> {{ $user->signature_status_label }}
            </span>
        </div>
    </div>
    <div class="card-body mt-3 signature-card-body">

        @if ($user->signature_status === 'rejected' && $user->signature_rejection_note)
            <div class="alert alert-danger d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                <div>
                    <strong>Tanda Tangan Ditolak Admin:</strong>
                    <p class="mb-0 small">{{ $user->signature_rejection_note }}</p>
                    <small class="text-muted fst-italic">Silakan unggah kembali tanda tangan digital yang sesuai dengan
                        petunjuk di bawah ini.</small>
                </div>
            </div>
        @elseif($user->signature_status === 'pending')
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-clock-history fs-5 flex-shrink-0"></i>
                <div class="small">
                    <strong>Menunggu Persetujuan Admin:</strong> Tanda tangan digital Anda telah diunggah dan sedang
                    dalam proses peninjauan oleh Administrator sebelum dapat digunakan untuk transaksi laboratorium.
                </div>
            </div>
        @elseif($user->signature_status === 'approved')
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
                <div class="small">
                    <strong>Tanda Tangan Siap Digunakan:</strong> Tanda tangan digital Anda telah disetujui dan aktif
                    untuk pengajuan serta persetujuan peminjaman laboratorium.
                </div>
            </div>
        @endif

        {{-- Petunjuk Format Upload TTD --}}
        <div class="card bg-light-primary border-primary border-1 mb-4 signature-guide">
            <div class="card-body p-3">
                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-info-circle-fill me-1"></i> Petunjuk Pengunggahan
                    Tanda Tangan Digital:</h6>
                <ul class="mb-0 small text-dark ps-3">
                    <li class="mb-1"><strong>Format JPG / JPEG:</strong> Wajib berlatar belakang (background)
                        <strong>putih polos</strong>, tanpa bayangan, coretan kotor, atau noise kamera.
                    </li>
                    <li class="mb-1"><strong>Format PNG:</strong> Wajib berlatar belakang <strong>transparan</strong>
                        (hanya guratan tanda tangan digital saja).</li>
                    <li class="mb-1"><strong>Ketentuan Bersih:</strong> <strong>DILARANG</strong> menyertakan
                        watermark, stempel instansi, tanggal manual, atau bingkai kotak apa pun.</li>
                    <li><strong>Ukuran & Resolusi:</strong> Maksimal ukuran file <strong>2MB</strong> dengan jarak
                        atas-bawah ttd yang tidak terlalu jauh dari garis ttd.</li>
                    <li><strong>Contoh ttd seperti ini : </strong></li>
                    <img src="{{ asset('images/default/contoh_ttd.png') }}" alt="Tanda Tangan Contoh"
                        style="max-width: 180px; max-height: 120px; object-fit: contain; background-color: white;">
                </ul>
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col-md-4 text-center mb-3 mb-md-0">
                <label class="form-label fw-bold d-block mb-2">Pratinjau Tanda Tangan</label>
                <div class="p-3 bg-white border rounded d-inline-block shadow-sm signature-preview-box">
                    <img id="signaturePreview" src="{{ $user->signature_url }}" alt="Tanda Tangan"
                        style="max-width: 180px; max-height: 120px; object-fit: contain;">
                </div>
            </div>
            <div class="col-md-8">
                <form id="uploadTtdForm" class="signature-upload-form"
                    action="{{ route('profile.upload_ttd', ['id' => Auth::id()]) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="signature_path" class="form-label fw-bold">Pilih File Tanda Tangan Baru
                            (PNG/JPG/JPEG, maks 2MB) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="signature_path" name="signature_path"
                            accept=".png,.jpg,.jpeg" required>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-upload me-1"></i> Unggah Tanda Tangan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
