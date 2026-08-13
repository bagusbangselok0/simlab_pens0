@extends('layouts.app')

@section('content')
    <div class="page-heading">
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12 col lg-12">
                <div class="card">
                    <div class="card-header">
                        {{-- dropdown filter status --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 d-flex justify-content-end align-items-center gap-2">
                                <label for="filter_approval_with_status" class="form-label mb-0 fw-mediumbold">Status</label>
                                <select class="form-select w-auto" id="filter_approval_with_status">
                                    <option value="all">Semua</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                    <option value="kadaluarsa">Kadaluarsa</option>
                                </select>
                            </div>
                        </div>
                        <div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="approvalTable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Peminjam</th>
                                            <th>Laboratorium</th>
                                            <th>Tujuan</th>
                                            <th>Waktu Mulai</th>
                                            <th>Waktu Selesai</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#approvalTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('approval.index') }}',
                    data: function(d) {
                        d.status = $('#filter_approval_with_status').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'peminjam',
                        name: 'peminjam'
                    },
                    {
                        data: 'lab',
                        name: 'lab'
                    },
                    {
                        data: 'tujuan',
                        name: 'tujuan'
                    },
                    {
                        data: 'waktu_mulai',
                        name: 'waktu_mulai'
                    },
                    {
                        data: 'waktu_selesai',
                        name: 'waktu_selesai'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // Handle approve/reject actions
            $('#approvalTable').on('click', '.approve-btn, .reject-btn', function() {
                var peminjamanId = $(this).data('id');
                var action = $(this).hasClass('approve-btn') ? 'approve' : 'reject';
                var url = '/approval/' + peminjamanId + '/' + action;

                Swal.fire({
                    title: action === 'approve' ? 'Setujui Peminjaman?' : 'Tolak Peminjaman?',
                    text: "Anda yakin ingin " + (action === 'approve' ? 'menyetujui' : 'menolak') +
                        " peminjaman ini?",
                    icon: action === 'approve' ? 'success' : 'error',
                    showCancelButton: true,
                    confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: action === 'approve' ? 'Ya, setujui!' : 'Ya, tolak!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (action === 'approve') {
                            $.ajax({
                                url: url,
                                type: 'PATCH',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    $('#approvalTable').DataTable().ajax.reload();
                                    Toastify({
                                        text: response.message,
                                        backgroundColor: "#28a745",
                                        position: "right",
                                    }).showToast();
                                },
                                error: function(xhr) {
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        Toastify({
                                            text: xhr.responseJSON.message,
                                            backgroundColor: "#dc3545",
                                            position: "right",
                                        }).showToast();
                                    } else {
                                        Toastify({
                                            text: 'Terjadi kesalahan. Silakan coba lagi.',
                                            backgroundColor: "#dc3545",
                                            position: "right",
                                        }).showToast();
                                    }
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Catatan Penolakan',
                                input: 'text',
                                inputAttributes: {
                                    autocapitalize: 'off',
                                    placeholder: 'Masukkan alasan penolakan...',
                                },
                                inputValidator: (value) => {
                                    if (!value) {
                                        return 'Catatan penolakan wajib diisi!';
                                    }
                                },
                                showCancelButton: true,
                                confirmButtonText: 'Kirim',
                                cancelButtonText: 'Batal'
                            }).then((noteResult) => {
                                if (noteResult.isConfirmed) {
                                    $.ajax({
                                        url: '/approval/' + peminjamanId +
                                            '/rejection-note',
                                        type: 'PATCH',
                                        data: {
                                            _token: '{{ csrf_token() }}',
                                            catatan_tolak: noteResult.value
                                        },
                                        success: function(response) {
                                            $('#approvalTable').DataTable().ajax
                                                .reload();
                                            Toastify({
                                                text: response.message,
                                                backgroundColor: "#28a745",
                                                position: "right",
                                            }).showToast();
                                        },
                                        error: function(xhr) {
                                            var message =
                                                'Gagal menyimpan catatan penolakan.';
                                            if (xhr.responseJSON && xhr
                                                .responseJSON.message) {
                                                message = xhr.responseJSON
                                                    .message;
                                            }
                                            Toastify({
                                                text: message,
                                                backgroundColor: "#dc3545",
                                                position: "right",
                                            }).showToast();
                                        }
                                    });
                                }
                            });
                        }
                    }
                });
            });

            // Handle filter status change
            $('#filter_approval_with_status').on('change', function() {
                $('#approvalTable').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
