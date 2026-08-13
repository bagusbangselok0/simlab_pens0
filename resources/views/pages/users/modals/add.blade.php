<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel"
    aria-hidden="true">
    <div class="modal-lg modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" method="POST" action="javascript:void(0);">
                    @csrf
                    <div class="row">
                        <!-- Role Selection -->
                        <div class="col-md-12 mb-3">
                            <label for="role_id" class="form-label">Pilih Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-select">
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" data-slug="{{ $role->slug }}">{{ $role->nama_role }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text role_id_error"></span>
                        </div>

                        <!-- Common Fields -->
                        <div class="col-md-6 mb-3">
                            <label for="nama_asli" class="form-label">Nama Lengkap (Tanpa Gelar) <span class="text-danger">*</span></label>
                            <input type="text" name="nama_asli" id="nama_asli" class="form-control" placeholder="Contoh: Budi Utomo">
                            <span class="text-danger error-text nama_asli_error"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Contoh: budi@gmail.com">
                            <span class="text-danger error-text email_error"></span>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter">
                            <span class="text-danger error-text password_error"></span>
                        </div>

                        <!-- Role Specific Fields -->
                        
                        <!-- NIP (For Dosen, PLP, Satpam) -->
                        <div class="col-md-6 mb-3 field-group nip-field" style="display: none;">
                            <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" id="nip" class="form-control" placeholder="Masukkan NIP">
                            <span class="text-danger error-text nip_error"></span>
                        </div>

                        <!-- NRP (For Mahasiswa) -->
                        <div class="col-md-6 mb-3 field-group nrp-field" style="display: none;">
                            <label for="nrp" class="form-label">NRP <span class="text-danger">*</span></label>
                            <input type="text" name="nrp" id="nrp" class="form-control" placeholder="Masukkan NRP">
                            <span class="text-danger error-text nrp_error"></span>
                        </div>

                        <!-- Gelar (For Dosen, PLP) -->
                        <div class="col-md-6 mb-3 field-group gelar-field" style="display: none;">
                            <label for="gelar_depan" class="form-label">Gelar Depan</label>
                            <input type="text" name="gelar_depan" id="gelar_depan" class="form-control" placeholder="Contoh: Dr.">
                            <span class="text-danger error-text gelar_depan_error"></span>
                        </div>

                        <div class="col-md-6 mb-3 field-group gelar-field" style="display: none;">
                            <label for="gelar_belakang" class="form-label">Gelar Belakang</label>
                            <input type="text" name="gelar_belakang" id="gelar_belakang" class="form-control" placeholder="Contoh: S.Kom., M.T.">
                            <span class="text-danger error-text gelar_belakang_error"></span>
                        </div>

                        <!-- Prodi (For Mahasiswa, Dosen, PLP) -->
                        <div class="col-md-6 mb-3 field-group prodi-field" style="display: none;">
                            <label for="prodi_id" class="form-label">Program Studi <span class="text-danger">*</span></label>
                            <select name="prodi_id" id="prodi_id" class="form-select">
                                <option value="">-- Pilih Prodi --</option>
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text prodi_id_error"></span>
                        </div>

                        <!-- Jabatan (For Dosen, PLP, Satpam) -->
                        <div class="col-md-6 mb-3 field-group jabatan-field" style="display: none;">
                            <label for="jabatan_id" class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <select name="jabatan_id" id="jabatan_id" class="form-select">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach ($jabatans as $jabatan)
                                    <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text jabatan_id_error"></span>
                        </div>
                    </div>

                    <div class="modal-footer px-0 pb-0 mt-3">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Batal</span>
                        </button>
                        <button type="submit" class="btn btn-primary ms-1" id="saveUserBtn">
                            <i class="bx bx-check d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Simpan Pengguna</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
