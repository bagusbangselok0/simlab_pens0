{{-- Isi Profile --}}
<div class="row">
    <div class="col-12 col-lg-12">
        <div class="card">
            <div class="card-body">
                {{-- gambar dan informasi profile sejajar --}}
                <div class="row">
                    <div class="col-12 col-lg-3">
                        {{-- Gambar bisa di edit langsung pilih foto --}}
                        <div class="d-flex flex-column align-items-center text-center">
                            <a href="" id="photoModalBtn" data-bs-toggle="modal" data-bs-target="#editPhotoModal">
                                <img src="{{ $user->photo_url }}" alt="Foto Profil" class="rounded-circle"
                                    width="150">
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Informasi Profil</h4>
                                <p><strong>Nama:</strong>
                                    {{ $user->full_name }}</p>
                                @if ($user->nip != null)
                                    <p><strong>NIP:</strong> {{ $user->nip }}</p>
                                @endif
                                @if ($user->nrp != null)
                                    <p><strong>NRP:</strong> {{ $user->nrp }}</p>
                                @endif
                                @if ($user->prodi != null)
                                    <p><strong>Prodi:</strong> {{ $user->prodi->nama_prodi ?? '-' }}</p>
                                @endif
                                @if ($user->jabatan != null)
                                    <p><strong>Jabatan:</strong> {{ $user->jabatan->nama_jabatan ?? '-' }}</p>
                                @endif

                                <hr>
                                <form id="formUpdateNoHp" method="POST" action="{{ route('profile.update_nohp') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="no_hp" class="form-label"><strong>No Handphone:</strong></label>
                                        <input type="text" class="form-control" id="no_hp" name="no_hp"
                                            value="{{ $user->no_hp }}" placeholder="Masukkan Nomor Handphone">
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
