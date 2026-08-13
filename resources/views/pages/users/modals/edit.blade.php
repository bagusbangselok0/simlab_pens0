<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel"
    aria-hidden="true">
    <div class="modal-lg modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST" action="javascript:void(0);">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <!-- Role Selection -->
                        <div class="col-md-12 mb-3">
                            <label for="edit_role_id" class="form-label">Pilih Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="edit_role_id" class="form-select">
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" data-slug="{{ $role->slug }}">{{ $role->nama_role }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text edit_role_id_error"></span>
                        </div>

                        <!-- Common Fields -->
                        <div class="col-md-6 mb-3">
                            <label for="edit_nama_asli" class="form-label">Nama Lengkap (Tanpa Gelar) <span class="text-danger">*</span></label>
                            <input type="text" name="nama_asli" id="edit_nama_asli" class="form-control">
                            <span class="text-danger error-text edit_nama_asli_error"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                            <span class="text-danger error-text edit_email_error"></span>
                        </div>

                        <!-- Role Specific Fields -->
                        
                        <!-- NIP (For Dosen, PLP, Satpam) -->
                        <div class="col-md-6 mb-3 edit-field-group edit-nip-field" style="display: none;">
                            <label for="edit_nip" class="form-label">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" id="edit_nip" class="form-control">
                            <span class="text-danger error-text edit_nip_error"></span>
                        </div>

                        <!-- NRP (For Mahasiswa) -->
                        <div class="col-md-6 mb-3 edit-field-group edit-nrp-field" style="display: none;">
                            <label for="edit_nrp" class="form-label">NRP <span class="text-danger">*</span></label>
                            <input type="text" name="nrp" id="edit_nrp" class="form-control">
                            <span class="text-danger error-text edit_nrp_error"></span>
                        </div>

                        <!-- Gelar (For Dosen, PLP) -->
                        <div class="col-md-6 mb-3 edit-field-group edit-gelar-field" style="display: none;">
                            <label for="edit_gelar_depan" class="form-label">Gelar Depan</label>
                            <input type="text" name="gelar_depan" id="edit_gelar_depan" class="form-control">
                            <span class="text-danger error-text edit_gelar_depan_error"></span>
                        </div>

                        <div class="col-md-6 mb-3 edit-field-group edit-gelar-field" style="display: none;">
                            <label for="edit_gelar_belakang" class="form-label">Gelar Belakang</label>
                            <input type="text" name="gelar_belakang" id="edit_gelar_belakang" class="form-control">
                            <span class="text-danger error-text edit_gelar_belakang_error"></span>
                        </div>

                        <!-- Prodi (For Mahasiswa, Dosen, PLP) -->
                        <div class="col-md-6 mb-3 edit-field-group edit-prodi-field" style="display: none;">
                            <label for="edit_prodi_id" class="form-label">Program Studi <span class="text-danger">*</span></label>
                            <select name="prodi_id" id="edit_prodi_id" class="form-select">
                                <option value="">-- Pilih Prodi --</option>
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text edit_prodi_id_error"></span>
                        </div>

                        <!-- Jabatan (For Dosen, PLP, Satpam) -->
                        <div class="col-md-6 mb-3 edit-field-group edit-jabatan-field" style="display: none;">
                            <label for="edit_jabatan_id" class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <select name="jabatan_id" id="edit_jabatan_id" class="form-select">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach ($jabatans as $jabatan)
                                    <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text edit_jabatan_id_error"></span>
                        </div>
                    </div>

                    <div class="modal-footer px-0 pb-0 mt-3">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            <span class="">Batal</span>
                        </button>
                        <button type="submit" class="btn btn-primary ms-1" id="updateUserBtn">
                            <span class="">Perbarui Pengguna</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
