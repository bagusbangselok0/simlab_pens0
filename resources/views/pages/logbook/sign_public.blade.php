<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TTD Perwakilan Mahasiswa — SIMLAB</title>
    <link rel="stylesheet" href="{{ asset('mazer/compiled/css/app.css') }}">
    <style>
        body {
            background-color: #f4f6f9;
            padding: 16px;
        }
        .sign-card {
            max-width: 520px;
            margin: 0 auto;
        }
        #publicSignCanvas {
            border: 2px dashed #6c757d;
            border-radius: 10px;
            background: #ffffff;
            width: 100%;
            height: 240px;
            touch-action: none;
            cursor: crosshair;
        }
    </style>
</head>
<body>
<div class="sign-card">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="text-center mb-3">
                <h5 class="fw-bold mb-1">Tanda Tangan Perwakilan Mahasiswa</h5>
                <div class="small text-muted">
                    {{ $logbook->nama_mk }} ({{ $logbook->kelas }}) &bull; {{ $logbook->lab->kode_lab ?? '-' }}
                    @if ($pertemuan)
                        <br>Minggu ke-{{ $pertemuan->minggu_ke }} ({{ $pertemuan->tanggal_pelaksanaan->format('d/m/Y') }})
                    @else
                        <br><em>Berlaku sebagai Perwakilan 1 Semester</em>
                    @endif
                </div>
            </div>

            @if (!empty($signedSuccess))
                <div class="alert alert-success text-center mb-0">
                    <h6 class="fw-bold mb-1">Tanda Tangan Berhasil Disimpan!</h6>
                    <p class="small mb-0">Terima kasih, <strong>{{ $signedNama }} ({{ $signedNrp }})</strong>. Tanda tangan Anda sudah masuk ke Logbook Praktikum PLP. Anda dapat menutup halaman ini.</p>
                </div>
            @else
                @if (session('error'))
                    <div class="alert alert-danger small">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger small">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ $submitUrl }}" id="publicSignForm">
                    @csrf
                    <input type="hidden" name="signature_data" id="publicSignatureData">

                    <div class="mb-2">
                        <label class="form-label fw-semibold small mb-1">Nama Lengkap Mahasiswa <span class="text-danger">*</span></label>
                        <input type="text" name="mahasiswa_nama" class="form-control" value="{{ old('mahasiswa_nama', $defaultName ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">NRP <span class="text-danger">*</span></label>
                        <input type="text" name="mahasiswa_nrp" class="form-control" value="{{ old('mahasiswa_nrp', $defaultNrp ?? '') }}" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold small mb-0">Goreskan Tanda Tangan di Bawah:</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0" id="btnResetPublicCanvas">Ulangi</button>
                    </div>
                    <canvas id="publicSignCanvas" class="mb-3"></canvas>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Simpan Tanda Tangan</button>
                </form>
            @endif
        </div>
    </div>
</div>

@if (empty($signedSuccess))
<script>
    (function() {
        const canvas = document.getElementById('publicSignCanvas');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let hasDrawn = false;
        let lastCanvasWidth = 0;

        function initCanvas(preserveDrawing = false) {
            const rect = canvas.getBoundingClientRect();
            const cssW = rect.width || 420;
            const cssH = rect.height || 240;
            const newWidth = Math.round(cssW);

            if (preserveDrawing && newWidth === lastCanvasWidth) {
                return;
            }

            const snapshot = (preserveDrawing && hasDrawn) ? canvas.toDataURL('image/png') : null;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            canvas.width = cssW * ratio;
            canvas.height = cssH * ratio;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.6;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#111827';
            lastCanvasWidth = newWidth;

            if (snapshot) {
                const img = new Image();
                img.onload = () => {
                    ctx.drawImage(img, 0, 0, cssW, cssH);
                };
                img.src = snapshot;
            } else if (!preserveDrawing) {
                hasDrawn = false;
            }
        }
        initCanvas(false);
        window.addEventListener('resize', () => initCanvas(true));

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const pt = e.touches ? e.touches[0] : e;
            return { x: pt.clientX - rect.left, y: pt.clientY - rect.top };
        }

        canvas.addEventListener('pointerdown', (e) => {
            drawing = true;
            hasDrawn = true;
            const p = getPos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        });
        canvas.addEventListener('pointermove', (e) => {
            if (!drawing) return;
            const p = getPos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        });
        window.addEventListener('pointerup', () => { drawing = false; });

        document.getElementById('btnResetPublicCanvas').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
        });

        document.getElementById('publicSignForm').addEventListener('submit', (e) => {
            if (!hasDrawn) {
                e.preventDefault();
                alert('Silakan bubuhkan tanda tangan Anda terlebih dahulu.');
                return;
            }
            document.getElementById('publicSignatureData').value = canvas.toDataURL('image/png');
        });
    })();
</script>
@endif
</body>
</html>
