{{-- Halaman untuk monitoring presensi mahasiswa oleh PLP dan Kalab --}}
@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <p class="text-subtitle text-muted">Pantau daftar presensi masuk dan keluar mahasiswa</p>
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="col-md-6">
                                <h4 class="card-title">Daftar Presensi Mahasiswa</h4>
                                <p class="text-muted">Presensi yang sudah diajukan dan dikonfirmasi</p>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end align-items-center gap-2">
                                <label for="filter_approval_with_status"
                                    class="form-label mb-0 fw-mediumbold">Status</label>
                                <select class="form-select w-auto" id="filter_approval_with_status">
                                    <option value="didalam" selected>Di Dalam Lab</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="">Semua</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="monitoringLoading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Memuat data monitoring presensi...</p>
                        </div>

                        <div id="monitoringEmptyState" class="text-center py-5 d-none">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">Belum ada presensi</h5>
                            <p class="text-muted">Belum ada mahasiswa yang melakukan presensi saat ini.</p>
                        </div>

                        <div class="table-responsive d-none" id="monitoringTableWrapper">
                            <table class="table table-striped" id="presensiTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Mahasiswa</th>
                                        <th>Laboratorium</th>
                                        <th>Tujuan</th>
                                        <th>Jenis Presensi</th>
                                        <th>Satpam</th>
                                        <th>Waktu Request</th>
                                        <th>Status</th>
                                        <th>Waktu Konfirmasi</th>
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
            const monitoringUrl = '{{ route('presensi.monitoring') }}';
            let dataTableInstance = null;

            function getBadge(status) {
                switch (status) {
                    case 'menunggu_konfirmasi_masuk':
                        return '<span class="badge bg-primary">Menunggu Konfirmasi Masuk</span>';
                    case 'menunggu_konfirmasi_keluar':
                        return '<span class="badge bg-warning">Menunggu Konfirmasi Keluar</span>';
                    case 'didalam':
                        return '<span class="badge bg-success">Di Dalam Lab</span>';
                    case 'selesai':
                        return '<span class="badge bg-secondary">Selesai</span>';
                    default:
                        return '<span class="badge bg-light text-dark">' + status + '</span>';
                }
            }

            function getSatpamName(item) {
                if (item.status_presensi === 'didalam') {
                    return item.satpam_masuk || 'N/A';
                }
                if (item.status_presensi === 'selesai') {
                    return item.satpam_keluar || 'N/A';
                }
                if (item.status_presensi === 'menunggu_konfirmasi_keluar') {
                    return item.satpam_keluar || 'N/A';
                }
                return item.satpam_masuk || 'N/A';
            }

            function getKonfirmasiTime(item) {
                if (item.status_presensi === 'didalam') {
                    return item.jam_masuk || '-';
                }
                if (item.status_presensi === 'selesai') {
                    return item.jam_keluar || '-';
                }
                return '-';
            }

            function renderTable(rows) {
                // Destroy existing DataTable instance before re-rendering
                if (dataTableInstance) {
                    dataTableInstance.destroy();
                    dataTableInstance = null;
                }

                const tbody = $('#presensiTable tbody');
                tbody.empty();

                rows.forEach((item, index) => {
                    const row = `
                        <tr data-status="${item.status_presensi}">
                            <td>${index + 1}</td>
                            <td>
                                <strong>${item.mahasiswa_name}</strong><br>
                                <small class="text-muted">${item.mahasiswa_email}</small>
                            </td>
                            <td>${item.lab_name}</td>
                            <td>${item.tujuan}</td>
                            <td>${getBadge(item.status_presensi)}</td>
                            <td>${getSatpamName(item)}</td>
                            <td>${item.created_at}</td>
                            <td>${item.status_presensi === 'didalam' ? '<span class="badge bg-success">Dikonfirmasi Masuk</span>' : item.status_presensi === 'selesai' ? '<span class="badge bg-secondary">Dikonfirmasi Keluar</span>' : '<span class="badge bg-warning">Menunggu Konfirmasi</span>'}</td>
                            <td>${getKonfirmasiTime(item)}</td>
                            <td>
                                <a href="${'{{ route('presensi.monitoring.cetak', ':id') }}'.replace(':id', item.peminjaman_lab_id)}" target="_blank" class="btn btn-sm btn-outline-danger" title="Cetak PDF">
                                    <i class="bi bi-file-earmark-pdf"></i> Cetak PDF
                                </a>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Initialize DataTable after data is rendered
                dataTableInstance = $('#presensiTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [[6, 'desc']], // Order by Waktu Request
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
                    }
                });
            }

            function loadMonitoringData() {
                const status = $('#filter_approval_with_status').val();
                $('#monitoringLoading').removeClass('d-none');
                $('#monitoringEmptyState').addClass('d-none');
                $('#monitoringTableWrapper').addClass('d-none');

                $.ajax({
                    url: monitoringUrl,
                    method: 'GET',
                    data: {
                        status
                    },
                    success(response) {
                        $('#monitoringLoading').addClass('d-none');

                        if (!response.data || response.data.length === 0) {
                            $('#monitoringEmptyState').removeClass('d-none');
                            return;
                        }

                        renderTable(response.data);
                        $('#monitoringTableWrapper').removeClass('d-none');
                    },
                    error() {
                        $('#monitoringLoading').addClass('d-none');
                        $('#monitoringEmptyState').removeClass('d-none');
                        $('#monitoringEmptyState').find('h5').text('Gagal memuat data');
                        $('#monitoringEmptyState').find('p').text(
                            'Silakan muat ulang halaman atau coba lagi nanti.');
                    }
                });
            }

            $('#filter_approval_with_status').on('change', loadMonitoringData);
            loadMonitoringData();
        });
    </script>
@endsection

