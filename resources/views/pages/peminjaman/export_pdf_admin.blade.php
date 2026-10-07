<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Peminjaman Laboratorium</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 12mm 15mm 15mm 15mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.3;
        }

        .header-table {
            width: 100%;
            border-bottom: 3px solid black;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-text {
            text-align: center;
            color: #17365D;
            font-size: 13pt;
        }

        .header-text p {
            margin: 0;
            padding: 0;
            font-weight: 200;
        }

        .header-text h3 {
            margin: 0;
            padding: 0;
            font-size: 14pt;
        }

        .logo-pens {
            width: 110px;
        }

        .logo-blu {
            width: 70px;
        }

        .title-section {
            text-align: center;
            margin-bottom: 14px;
        }

        .title-section h4 {
            margin: 0 0 4px 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .title-section p {
            margin: 0;
            font-size: 9.5pt;
            color: #333;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 9pt;
        }

        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        table.data-table td {
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-nowrap {
            white-space: nowrap;
        }

        .footer-table {
            width: 100%;
            margin-top: 15px;
            border: none;
        }

        .footer-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .footer-info {
            font-size: 8.5pt;
            color: #555;
            font-style: italic;
        }
    </style>
</head>

<body>
    {{-- Header Kop Surat (Sesuai format resmi cetak PDF peminjaman) --}}
    <table class="header-table">
        <tr>
            <td width="10%">
                @php
                    $pensLogoPath = public_path('images/logo/logo_PENS.png');
                    $pensLogoBase64 = file_exists($pensLogoPath) ? base64_encode(file_get_contents($pensLogoPath)) : '';
                @endphp
                @if ($pensLogoBase64)
                    <img src="data:image/png;base64,{{ $pensLogoBase64 }}" class="logo-pens">
                @endif
            </td>
            <td class="header-text">
                <p>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI</p>
                <h3>POLITEKNIK ELEKTRONIKA NEGERI SURABAYA</h3>
                <h3 style="letter-spacing: 2px;">KAMPUS SUMENEP</h3>
                <p style="font-size: 9.5pt; margin-top: 4px; font-weight: 200;">
                    Jl. Raya Lenteng KM.2 Batuan Kabupaten Sumenep<br>
                    Telepon: 032867419, WA: 081394646263<br> Laman: https://www.pens.ac.id
                </p>
            </td>
            <td width="10%" style="text-align: right;">
                @php
                    $bluLogoPath = public_path('images/logo/Logo_BLU_Speed.png');
                    $bluLogoBase64 = file_exists($bluLogoPath) ? base64_encode(file_get_contents($bluLogoPath)) : '';
                @endphp
                @if ($bluLogoBase64)
                    <img src="data:image/png;base64,{{ $bluLogoBase64 }}" class="logo-blu">
                @endif
            </td>
        </tr>
    </table>

    <div class="title-section">
        <h4>DAFTAR PEMINJAMAN LABORATORIUM</h4>
        @if ($selectedStudent)
            <p><strong>Filter Mahasiswa:</strong> {{ $selectedStudent->nama_asli }} (NRP:
                {{ $selectedStudent->nrp ?? '-' }})</p>
        @else
            <p><strong>Filter:</strong> Semua Mahasiswa</p>
        @endif
        <p style="font-size: 8.5pt; color: #666; margin-top: 2px;">
            Waktu Cetak: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB
        </p>
    </div>

    {{-- Tabel Peminjaman (Kolom status ditiadakan sesuai permintaan user) --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 18%;">NAMA MAHASISWA</th>
                <th style="width: 12%;">NRP</th>
                <th style="width: 18%;">LABORATORIUM</th>
                <th style="width: 26%;">KEPERLUAN</th>
                <th style="width: 11%;">WAKTU MULAI</th>
                <th style="width: 11%;">WAKTU SELESAI</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($peminjamans as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->mahasiswa->nama_asli ?? '-' }}</td>
                    <td class="text-center">{{ $row->mahasiswa->nrp ?? '-' }}</td>
                    <td>
                        {{ $row->lab->nama_lab ?? '-' }}<br>
                        <small style="color: #555;">({{ $row->lab->kode_lab ?? '-' }})</small>
                    </td>
                    <td>{{ $row->tujuan ?? '-' }}</td>
                    <td class="text-center text-nowrap">
                        {{ $row->waktu_mulai ? $row->waktu_mulai->format('d-m-Y H:i') : '-' }}
                    </td>
                    <td class="text-center text-nowrap">
                        {{ $row->waktu_selesai ? $row->waktu_selesai->format('d-m-Y H:i') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 16px;">
                        Tidak ada data peminjaman dengan status Disetujui atau Selesai.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td width="60%">
                <span class="footer-info">
                    Dicetak secara otomatis oleh SIMLAB PENS Kampus Sumenep.<br>
                    Total Data: {{ count($peminjamans) }} transaksi peminjaman (Disetujui &amp; Selesai).
                </span>
            </td>
            <td width="40%" style="text-align: right;">
                <span class="footer-info">
                    Sumenep, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
                    Administrator SIMLAB
                </span>
            </td>
        </tr>
    </table>
</body>

</html>
