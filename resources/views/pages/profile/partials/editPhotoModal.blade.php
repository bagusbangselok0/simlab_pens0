{{-- Lihat  foto dan edit foto --}}
<div class="modal fade" id="editPhotoModal" tabindex="-1" aria-labelledby="editPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPhotoModalLabel">Edit Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                @csrf
                {{-- @method('PUT') --}}
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <img id="photoPreview" src="{{ $user->photo_url }}"
                            alt="Foto Profil" class="img-fluid mb-3" style="max-width: 200px; max-height: 200px;">
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Pilih Foto Profil Baru (PNG/JPG, max 1MB)</label>
                        <input type="file" class="form-control" id="photo" name="photo"
                            accept=".png,.jpg,.jpeg" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
