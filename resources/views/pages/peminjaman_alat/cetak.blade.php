<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Form Peminjaman Alat {{ $peminjaman->kode_peminjaman }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #111;
            margin: 15px 25px;
        }

        .header-table {
            width: 100%;
            border-bottom: 3px solid black;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }

        .header-text {
            text-align: center;
            color: #17365D;
            font-size: 13pt;
        }

        .header-text p {
            font-weight: 200;
            margin: 0;
            padding: 0;
        }

        .header-text h3 {
            margin: 0;
            padding: 0;
        }

        .logo-pens {
            width: 110px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 15px;
            line-height: 1.4;
            text-decoration: underline;
        }

        .content-table {
            width: 100%;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .content-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 10.5pt;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 6px 8px;
        }

        .items-table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .signature-table {
            width: 100%;
            margin-top: 25px;
            text-align: center;
        }

        .signature-table td {
            vertical-align: top;
            width: 50%;
        }

        .signature-img {
            height: 70px;
            margin: 5px 0;
        }

        .footer-note {
            margin-top: 15px;
            text-align: justify;
            font-size: 10pt;
            line-height: 1.4;
        }

        .badge-manual {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9pt;
            border: 1px solid #ffeeba;
        }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td width="15%" style="text-align: center;">
                @php
                    $logoPath = public_path('images/logo/logo_PENS.png');
                @endphp
                @if (file_exists($logoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" class="logo-pens">
                @endif
            </td>
            <td class="header-text">
                <p>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</p>
                <h3>POLITEKNIK ELEKTRONIKA NEGERI SURABAYA</h3>
                <h3 style="letter-spacing: 1.5px;">KAMPUS SUMENEP</h3>
                <p style="font-size: 9pt; margin-top: 4px; font-weight: 200;">
                    Jl. Raya Lenteng KM.2 Batuan Kabupaten Sumenep<br>
                    Telepon: 032867419, WA: 081394646263 | Laman: https://www.pens.ac.id
                </p>
            </td>
        </tr>
    </table>

    <div class="title">
        FORM BUKTI PEMINJAMAN ALAT LABORATORIUM<br>
        <span style="font-size: 10.5pt; font-weight: normal; text-decoration: none;">Nomor:
            {{ $peminjaman->kode_peminjaman }}</span>
    </div>

    <p style="margin-bottom: 5px;">Saya yang bertanda tangan di bawah ini:</p>
    <table class="content-table">
        <tr>
            <td width="28%">Nama Mahasiswa</td>
            <td width="2%">:</td>
            <td><strong>{{ $peminjaman->mahasiswa->nama_asli ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>NRP</td>
            <td>:</td>
            <td>{{ $peminjaman->mahasiswa->nrp ?? '-' }}</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ $peminjaman->mahasiswa->prodi->nama_prodi ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tujuan / Keperluan</td>
            <td>:</td>
            <td>{{ $peminjaman->keperluan }}</td>
        </tr>
        @php
            $wMulai = Carbon\Carbon::parse($peminjaman->waktu_pinjam)->locale('id');
            $wSelesai = Carbon\Carbon::parse($peminjaman->rencana_kembali)->locale('id');
        @endphp
        <tr>
            <td>Waktu Pinjam</td>
            <td>:</td>
            <td>{{ $wMulai->translatedFormat('l, d F Y') }} (Pukul {{ $wMulai->format('H:i') }} WIB)</td>
        </tr>
        <tr>
            <td>Rencana Pengembalian</td>
            <td>:</td>
            <td>{{ $wSelesai->translatedFormat('l, d F Y') }} (Pukul {{ $wSelesai->format('H:i') }} WIB)</td>
        </tr>
        <tr>
            <td>Ruang / Lab Tujuan</td>
            <td>:</td>
            <td>{{ $peminjaman->lab ? $peminjaman->lab->nama_lab : 'Umum / Teknisi' }}</td>
        </tr>
    </table>

    <p style="margin-top: 10px; margin-bottom: 3px;">Mengajukan peminjaman alat laboratorium dengan rincian sebagai
        berikut:</p>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Nama Alat & Spesifikasi</th>
                <th width="20%">Kode Barang / NUP</th>
                <th width="10%">Jumlah</th>
                <th width="10%">Satuan</th>
                <th width="20%">Keterangan Sumber</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($peminjaman->details as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->nama_alat }}</strong>
                        @if ($item->spesifikasi)
                            <br><small style="color: #444;">{{ $item->spesifikasi }}</small>
                        @endif
                    </td>
                    <td>
                        {{ $item->kode_barang ?? '-' }}
                        @if ($item->nup)
                            <br><small>NUP: {{ $item->nup }}</small>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $item->jumlah }}</td>
                    <td style="text-align: center;">{{ $item->satuan }}</td>
                    <td style="text-align: center;">
                        @if ($item->is_manual)
                            <span class="badge-manual">Input Manual</span>
                        @else
                            <span>DIR: {{ $item->inventarisRuangan->lab->nama_lab ?? 'Lab' }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer-note">
        <strong>Pernyataan:</strong> Saya menyatakan bertanggung jawab penuh atas pemeliharaan dan keutuhan alat-alat
        laboratorium tersebut di atas selama masa peminjaman, serta bersedia mengembalikan tepat waktu dalam kondisi
        baik dan lengkap sesuai peraturan yang berlaku di SIMLAB Politeknik Elektronika Negeri Surabaya.
    </p>

    <table class="signature-table">
        <tr>
            <td>
                <br>
                <div style="padding-bottom:10px;"></div>
                Menyetujui,<br>
                <strong>Pranata Laboratorium Pendidikan</strong><br>
                @if ($peminjaman->ttd_plp_file && file_exists(storage_path('app/public/signatures/' . $peminjaman->ttd_plp_file)))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public/signatures/' . $peminjaman->ttd_plp_file))) }}"
                        class="signature-img"><br>
                @else
                    <div style="height: 70px;"></div>
                @endif
                <u><strong>{{ $peminjaman->plp ? $peminjaman->plp->full_name : '(............................................)' }}</strong></u><br>
                NIP. {{ $peminjaman->plp->nip ?? '-' }}
            </td>
            <td>
                <div style="text-align:end;padding-bottom:10px">
                    Sumenep,
                    {{ Carbon\Carbon::parse($peminjaman->created_at)->locale('id')->translatedFormat('d F Y') }}<br>
                </div>
                Mengetahui,<br>
                <strong>Peminjam (Mahasiswa)</strong><br>
                @if (
                    $peminjaman->ttd_mahasiswa_file &&
                        file_exists(storage_path('app/public/signatures/' . $peminjaman->ttd_mahasiswa_file)))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public/signatures/' . $peminjaman->ttd_mahasiswa_file))) }}"
                        class="signature-img"><br>
                @else
                    <div style="height: 70px;"></div>
                @endif
                <u><strong>{{ $peminjaman->mahasiswa->nama_asli }}</strong></u><br>
                NRP. {{ $peminjaman->mahasiswa->nrp }}
            </td>
        </tr>
    </table>

</body>

</html>
