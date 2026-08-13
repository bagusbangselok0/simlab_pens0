{{-- Upload TTD --}}
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Upload Tanda Tangan</h4>
    </div>
    <div class="card-body">
        <img id="signaturePreview" src="{{ $user->signature_url }}" alt="Tanda Tangan" class="mb-3"
            style="max-width: 200px; max-height: 200px;">
        <form id="uploadTtdForm" action="{{ route('profile.upload_ttd', ['id' => Auth::id()]) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="signature_path" class="form-label">Pilih File Tanda Tangan (PNG/JPG, max 2MB)</label>
                <input type="file" class="form-control" id="signature_path" name="signature_path"
                    accept=".png,.jpg,.jpeg" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>
