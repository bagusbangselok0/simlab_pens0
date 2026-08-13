<div class="modal fade" id="addLabManagerModal" tabindex="-1" role="dialog" aria-labelledby="addLabMaanagerModalLabel"
    aria-hidden="true">
    <div class="modal-lg modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Penanggung Jawab Laboratorium</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addLabManagerForm" method="POST" action="javascript:void(0);">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="lab_id" class="form-label">Pilih Laboratorium</label>
                        <select name="lab_id" class="form-control" id="lab_id">
                            <option value="">-- Pilih Lab --</option>
                            @foreach ($labs as $lab)
                                <option value="{{ $lab->id }}">{{ $lab->nama_lab }} ({{ $lab->kode_lab }})</option>
                            @endforeach
                        </select>
                        <span class="text-danger" id="labError"></span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="plp_id" class="form-label">Pilih PLP</label>
                        <select name="plp_id" class="form-control" id="plp_id">
                            <option value="">-- Pilih PLP --</option>
                            @foreach ($plps as $plp)
                                <option value="{{ $plp->id }}">{{ $plp->nama_asli }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger" id="plpError"></span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="kalab_id" class="form-label">Pilih Kalab</label>
                        <select name="kalab_id" class="form-control" id="kalab_id">
                            <option value="">-- Pilih Kalab --</option>
                            @foreach ($kalabs as $kalab)
                                <option value="{{ $kalab->id }}">{{ $kalab->nama_asli }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger" id="kalabError"></span>
                    </div>


                    <button type="submit" class="btn btn-success">Tambah Data</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </form>
            </div>
        </div>
    </div>
</div>
