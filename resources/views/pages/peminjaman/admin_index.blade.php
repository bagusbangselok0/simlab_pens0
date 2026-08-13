@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('select2/css/select2-bootstrap-5-theme.min.css') }}">
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.375rem;
        }

        /* Dark Mode Select2 Optimization */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
            background-color: #1b1b29;
            border-color: #3b3b54;
            color: #eee;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #eee;
        }

        [data-bs-theme="dark"] .select2-dropdown {
            background-color: #1b1b29;
            border-color: #3b3b54;
            color: #eee;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field {
            background-color: #1b1b29;
            border-color: #3b3b54;
            color: #eee;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
            background-color: #435ebe;
            color: #fff;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #adb5bd;
        }
    </style>
@endsection

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center mt-3">
                    <div class="col-md-6 mb-3">
                        <label for="mahasiswa_filter" class="form-label fw-bold">Filter Berdasarkan Mahasiswa</label>
                        <select id="mahasiswa_filter" class="form-select select2">
                            <option value="">-- Semua Mahasiswa --</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}">{{ $student->nama_asli }} ({{ $student->nrp ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body mt-2">
                <table id="adminPeminjamanTable" class="table table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Mahasiswa</th>
                            <th>Lab</th>
                            <th>Keperluan</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Modal Detail Peminjaman (Reused from index) -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
        aria-hidden="true">
        <div class="modal-lg modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Peminjaman Lab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Nama Lab</th>
                            <td id="detailNamaLab"></td>
                        </tr>
                        <tr>
                            <th>PLP</th>
                            <td id="detailPLP"></td>
                        </tr>
                        <tr>
                            <th>Waktu Disetujui PLP</th>
                            <td id="detailWaktuDisetujuiPLP"></td>
                        </tr>
                        <tr>
                            <th>Kalab</th>
                            <td id="detailKalab"></td>
                        </tr>
                        <tr>
                            <th>Waktu Disetujui Kalab</th>
                            <td id="detailWaktuDisetujuiKalab"></td>
                        </tr>
                        <tr>
                            <th>Catatan Tolak</th>
                            <td id="detailCatatanTolak"></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td id="detailStatus"></td>
                        </tr>
                        <tr>
                            <th>Waktu Pengajuan</th>
                            <td id="detailCreatedAt"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('select2/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Initialize DataTable
            let table = $('#adminPeminjamanTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('peminjaman.admin') }}",
                    data: function(d) {
                        d.mahasiswa_id = $('#mahasiswa_filter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'mahasiswa',
                        name: 'mahasiswa'
                    },
                    {
                        data: 'nama_lab',
                        name: 'nama_lab',
                        className: 'text-nowrap'
                    },
                    {
                        data: 'keperluan',
                        name: 'keperluan'
                    },
                    {
                        data: 'waktu_mulai',
                        name: 'waktu_mulai',
                    },
                    {
                        data: 'waktu_selesai',
                        name: 'waktu_selesai',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        className: 'text-center text-nowrap'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Handle filter change
            $('#mahasiswa_filter').on('change', function() {
                table.ajax.reload();
            });

            // Handle Detail Button Click
            $('body').on('click', '.detailData', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: "/peminjaman/detail/" + id,
                    method: "GET",
                    success: function(response) {
                        let detail = response.data;
                        $('#detailNamaLab').text(detail.nama_lab);
                        $('#detailPLP').text(detail.plp ?? '-');
                        $('#detailWaktuDisetujuiPLP').text(detail.tg_ttd_plp ?? '-');
                        $('#detailKalab').text(detail.kalab ?? '-');
                        $('#detailWaktuDisetujuiKalab').text(detail.tg_ttd_kalab ?? '-');
                        $('#detailCatatanTolak').text(detail.catatan_tolak + ' ' + (detail
                            .penolak ?? ''));
                        $('#detailStatus').html(detail.status_badge);
                        $('#detailCreatedAt').text(detail.created_at);
                        $('#detailModal').modal('show');
                    },
                    error: function() {
                        Toastify({
                            text: 'Gagal mengambil detail peminjaman.',
                            backgroundColor: "#dc3545",
                            position: "right",
                        }).showToast();
                    }
                });
            });
        });
    </script>
@endsection
