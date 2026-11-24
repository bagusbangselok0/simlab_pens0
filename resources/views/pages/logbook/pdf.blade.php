<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>FM. LAB-02 — {{ $logbook->nama_mk }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 14mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.25;
        }
        .page-break {
            page-break-after: always;
        }
        table.fm-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.fm-table td,
        table.fm-table th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .section-header {
            background-color: #efefef;
            font-weight: bold;
            text-align: left;
        }
        .ttd-box {
            height: 54px;
            text-align: center;
            vertical-align: middle;
        }
        .ttd-img {
            max-height: 48px;
            max-width: 150px;
        }
    </style>
</head>
<body>
@foreach ($pages as $page)
    @php
        $p = $page['pertemuan'];
        $peralatan = $page['peralatan'];
        $aktivitas = $page['aktivitas'];
        $permasalahan = $page['permasalahan'];
        $minAlatRows = max(6, count($peralatan));
        $minAktivitasRows = max(3, count($aktivitas));
        $minMasalahRows = max(2, count($permasalahan));
    @endphp
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <table class="fm-table">
            {{-- Kop Dokumen Resmi FM. LAB-02 Rev.00 --}}
            <tr>
                <td rowspan="3" class="text-center" style="width: 14%;">
                    @if ($logoPensBase64)
                        <img src="{{ $logoPensBase64 }}" alt="Logo PENS" style="width: 56px; height: auto;">
                    @else
                        <strong>PENS</strong>
                    @endif
                </td>
                <td rowspan="3" colspan="3" class="text-center" style="width: 52%;">
                    <div class="fw-bold" style="font-size: 11pt; margin-bottom: 4px;">FORM LABORATORIUM</div>
                    <div class="fw-bold" style="font-size: 10pt;">LOGBOOK PELAKSANAAN PRAKTIKUM<br>PLP/LABORAN</div>
                </td>
                <td style="width: 17%; font-size: 8.5pt;">No. Identifikasi</td>
                <td style="width: 17%; font-size: 8.5pt;" class="fw-bold">FM. LAB-02</td>
            </tr>
            <tr>
                <td style="font-size: 8.5pt;">No. Revisi</td>
                <td style="font-size: 8.5pt;">00</td>
            </tr>
            <tr>
                <td style="font-size: 8.5pt;">Tanggal Terbit</td>
                <td style="font-size: 8.5pt;">27/07/2015</td>
            </tr>
            <tr>
                <td class="text-center fw-bold" style="font-size: 8.5pt;">FM.LAB-02<br>Rev.00</td>
                <td colspan="3" style="font-size: 8.5pt;">Area : Semua laboratorium di Politeknik Elektronika Negeri Surabaya</td>
                <td style="font-size: 8.5pt;">Halaman</td>
                <td class="text-center" style="font-size: 8.5pt;">1</td>
            </tr>

            {{-- Identitas MK & Pertemuan --}}
            <tr>
                <td style="width: 18%; font-size: 8.5pt;">Nama MK Praktikum</td>
                <td colspan="2" style="width: 32%; font-size: 8.5pt;" class="fw-bold">{{ $logbook->nama_mk }}</td>
                <td style="width: 16%; font-size: 8.5pt;">Judul Praktikum</td>
                <td colspan="2" style="width: 34%; font-size: 8.5pt;">{{ $p->judul_praktikum }}</td>
            </tr>
            <tr>
                <td style="font-size: 8.5pt;">Kelas</td>
                <td colspan="2" style="font-size: 8.5pt;">{{ $logbook->kelas }}</td>
                <td style="font-size: 8.5pt;">Minggu Ke-</td>
                <td colspan="2" style="font-size: 8.5pt;">{{ $p->minggu_ke }}</td>
            </tr>
            <tr>
                <td style="font-size: 8.5pt;">Dosen Pengampu</td>
                <td colspan="2" style="font-size: 8.5pt;">{{ $logbook->display_dosen_name }}</td>
                <td style="font-size: 8.5pt;">PLP</td>
                <td colspan="2" style="font-size: 8.5pt;">{{ $logbook->display_plp_name }}</td>
            </tr>
            <tr>
                <td style="font-size: 8.5pt;">Tanggal Pelaksanaan</td>
                <td colspan="2" style="font-size: 8.5pt;">{{ $p->tanggal_pelaksanaan->locale('id')->translatedFormat('d F Y') }}</td>
                <td style="font-size: 8.5pt;">Jam Pelaksanaan</td>
                <td colspan="2" style="font-size: 8.5pt;">{{ $p->jam_pelaksanaan }}</td>
            </tr>

            {{-- Bagian A: Peminjaman Peralatan Praktikum --}}
            <tr>
                <td colspan="6" class="section-header">A. Peminjaman Peralatan Praktikum</td>
            </tr>
            <tr>
                <td style="font-size: 8.5pt;">Nama/NRP Peminjam</td>
                <td colspan="5" style="font-size: 8.5pt;">{{ $page['mahasiswa_display'] }}</td>
            </tr>
            <tr class="text-center fw-bold" style="font-size: 8pt; background-color: #f8f8f8;">
                <td style="width: 6%;">No.</td>
                <td colspan="2" style="width: 44%;">Nama Alat</td>
                <td style="width: 24%;">Kategori (Alat 1/Alat 2/Alat 3/Bahan Umum/Bahan Khusus)</td>
                <td style="width: 11%;">Jumlah</td>
                <td style="width: 15%;">Kondisi (Baik/Rusak)</td>
            </tr>
            @for ($i = 0; $i < $minAlatRows; $i++)
                @php $row = $peralatan[$i] ?? null; @endphp
                <tr style="font-size: 8.5pt; height: 18px;">
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td colspan="2">{{ $row['nama_alat'] ?? '' }}</td>
                    <td class="text-center">{{ $row['kategori'] ?? '' }}</td>
                    <td class="text-center">{{ $row['jumlah'] ?? '' }}</td>
                    <td class="text-center">{{ $row['kondisi'] ?? '' }}</td>
                </tr>
            @endfor
            <tr>
                <td colspan="6" style="font-size: 8.5pt; padding: 6px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border: none; width: 35%; vertical-align: middle;">TTD Perwakilan Mahasiswa</td>
                            <td style="border: none; width: 65%; height: 42px; vertical-align: middle;">
                                @if ($page['has_peralatan'] && $page['mahasiswa_ttd_base64'])
                                    <img src="{{ $page['mahasiswa_ttd_base64'] }}" alt="TTD Mahasiswa" style="max-height: 38px;">
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            {{-- Bagian B: Aktivitas --}}
            <tr>
                <td colspan="6" class="section-header">B. Aktivitas dalam Praktikum/Praktek/Workshop</td>
            </tr>
            <tr class="text-center fw-bold" style="font-size: 8.5pt; background-color: #f8f8f8;">
                <td>No.</td>
                <td colspan="5" style="text-align: left;">Aktivitas</td>
            </tr>
            @for ($i = 0; $i < $minAktivitasRows; $i++)
                <tr style="font-size: 8.5pt; height: 20px;">
                    <td class="text-center">{{ $i + 1 }}.</td>
                    <td colspan="5">{{ $aktivitas[$i] ?? '' }}</td>
                </tr>
            @endfor

            {{-- Bagian C: Permasalahan --}}
            <tr>
                <td colspan="6" class="section-header">C. Permasalahan (peralatan, mahasiswa, system, dll)</td>
            </tr>
            <tr class="text-center fw-bold" style="font-size: 8.5pt; background-color: #f8f8f8;">
                <td>No.</td>
                <td colspan="2">Masalah</td>
                <td colspan="3">Solusi</td>
            </tr>
            @for ($i = 0; $i < $minMasalahRows; $i++)
                @php $prob = $permasalahan[$i] ?? null; @endphp
                <tr style="font-size: 8.5pt; height: 20px;">
                    <td class="text-center">{{ $i + 1 }}.</td>
                    <td colspan="2">{{ $prob['masalah'] ?? '' }}</td>
                    <td colspan="3">{{ $prob['solusi'] ?? '' }}</td>
                </tr>
            @endfor

            {{-- Blok Tanda Tangan Bawah --}}
            <tr>
                <td colspan="3" class="text-center" style="vertical-align: top; padding: 8px;">
                    <div style="margin-bottom: 4px;">Dosen Pengampu Praktikum</div>
                    <div class="ttd-box">
                        @if ($page['dosen_ttd_base64'])
                            <img src="{{ $page['dosen_ttd_base64'] }}" alt="TTD Dosen" class="ttd-img">
                        @endif
                    </div>
                    <div class="fw-bold" style="margin-top: 4px;">{{ $logbook->display_dosen_name }}</div>
                </td>
                <td colspan="3" class="text-center" style="vertical-align: top; padding: 8px;">
                    <div style="margin-bottom: 4px;">PLP/Laboran</div>
                    <div class="ttd-box">
                        @if ($plpTtdBase64)
                            <img src="{{ $plpTtdBase64 }}" alt="TTD PLP" class="ttd-img">
                        @endif
                    </div>
                    <div class="fw-bold" style="margin-top: 4px;">{{ trim($logbook->plp?->full_name ?? '-') }}</div>
                </td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
