<div class="modal fade" id="detailAndEditLabManagerModal" tabindex="-1" role="dialog" aria-labelledby="detailAndEditLabManagerModalLabel"
        aria-hidden="true">
        <div class="modal-lg modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailAndEditModalTitle">Detail & Edit Penanggung Jawab Laboratorium</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="detailAndEditLabManagerForm" method="POST" action="javascript:void(0);">
                        @csrf
                        {{-- Button edit posisi kanan --}}
                        <div class="d-flex justify-content-end align-items-end">
                            <button type="button" class="btn btn-warning" id="editBtn">
                                <i class="bi bi-pencil pe-1"></i> Edit
                            </button>
                            <button type="button" class="btn btn-secondary ms-2" id="cancelEditBtn" style="display: none;">
                                <i class="bi bi-x-circle pe-1"></i> Batal Edit
                            </button>
                        </div>
                        <input type="hidden" name="lab_manager_id" id="lab_manager_id">
                        <div class="form-group mb-3">
                            <label for="edit_lab_id" class="form-label">Pilih Laboratorium</label>
                            <select name="lab_id" class="form-control" id="edit_lab_id" disabled>
                                <option value="">-- Pilih Lab --</option>
                                @foreach ($labs as $lab)
                                    <option value="{{ $lab->id }}">{{ $lab->nama_lab }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="detailLabError"></span>
                            </div>
                        <div class="form-group mb-3">
                            <label for="edit_plp_id" class="form-label">Pilih PLP</label>
                            <select name="plp_id" class="form-control" id="edit_plp_id" disabled>
                                <option value="">-- Pilih PLP --</option>
                                @foreach ($plps as $plp)
                                    <option value="{{ $plp->id }}">{{ $plp->nama_asli }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="plpError"></span>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_kalab_id" class="form-label">Pilih Kalab</label>
                            <select name="kalab_id" class="form-control" id="edit_kalab_id" disabled>
                                <option value="">-- Pilih Kalab --</option>
                                @foreach ($kalabs as $kalab)
                                    <option value="{{ $kalab->id }}">{{ $kalab->nama_asli }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="kalabError"></span>
                        </div>


                        <button type="submit" class="btn btn-success d-none" id="updateBtn">Update Data</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </form>
                </div>
            </div>
        </div>

</div>
