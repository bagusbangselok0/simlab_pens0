<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Inventaris Ruangan - {{ $lab->nama_lab }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #000;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 3px;
        }
        .header-subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            color: #d00;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .fw-bold {
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            border: none;
            margin-top: 15px;
        }
        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <!-- Header DIR Sesuai Format Resmi Dokumen -->
    <div class="header-title">
        NAMA RUANGAN : {{ strtoupper($lab->nama_lab) }}
    </div>
    <div class="header-subtitle">
        KODE RUANGAN : {{ strtoupper($lab->kode_lab) }}
    </div>

    <!-- Tabel Daftar Inventaris Ruangan -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 15%;">KODE BARANG</th>
                <th style="width: 25%;">NAMA BARANG</th>
                <th style="width: 23%;">SPESIFIKASI / MERK TIPE</th>
                <th style="width: 11%;">TAHUN PEROLEHAN</th>
                <th style="width: 10%;">JUMLAH</th>
                <th style="width: 12%;">KONDISI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->kode_barang ?? '-' }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->spesifikasi_merk_tipe ?? '-' }}</td>
                    <td class="text-center">{{ $item->tahun_perolehan ?? '-' }}</td>
                    <td class="text-center">{{ $item->jumlah }} {{ $item->satuan }}</td>
                    <td class="text-center">{{ $item->kondisi_label }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px;">Belum ada data inventaris pada ruangan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Resmi Sesuai Ketentuan -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <p style="margin: 0; font-weight: bold;">Mengetahui,</p>
                <p style="margin: 0; font-weight: bold;">PENANGGUNG JAWAB UAKPB</p>
                <p style="margin: 0; font-weight: bold;">Wakil Direktur II</p>
                <div style="height: 65px;"></div>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">( .............................................................. )</p>
                <p style="margin: 0;">NIP. ...........................................................</p>
            </td>
            <td style="width: 50%; text-align: center;">
                <p style="margin: 0;">{{ now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 0; font-weight: bold;">Penanggung Jawab Ruangan</p>
                <div style="height: 65px;"></div>
                @if($lab->labManager && $lab->labManager->plp)
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $lab->labManager->plp->full_name }}</p>
                    <p style="margin: 0;">NIP. {{ $lab->labManager->plp->nip ?? '-' }}</p>
                @elseif($lab->labManager && $lab->labManager->kalab)
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $lab->labManager->kalab->full_name }}</p>
                    <p style="margin: 0;">NIP. {{ $lab->labManager->kalab->nip ?? '-' }}</p>
                @else
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">( .............................................................. )</p>
                    <p style="margin: 0;">NIP. ...........................................................</p>
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
