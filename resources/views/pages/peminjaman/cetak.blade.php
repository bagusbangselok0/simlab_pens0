<!DOCTYPE html>
<html>

<head>
    <title>Form Peminjaman Lab {{ $peminjaman->lab->nama_lab }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
        }

        .header-table {
            width: 100%;
            border-bottom: 3px solid black;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .header-text {
            text-align: center;
            color: #17365D;
            font-size: 14pt;
        }

        .header-text p {
            font-weight: 200;
        }

        .header-text p,
        .header-text h3 {
            margin: 0;
            padding: 0;
        }

        .logo-pens {
            width: 120px;
        }

        .logo-blu {
            width: 60px;
        }

        .title {
            text-align: center;
            /* text-decoration: underline; */
            font-weight: bold;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .content-table {
            width: 100%;
            margin-left: 20px;
            line-height: 1.5;
        }

        .content-table td {
            vertical-align: top;
        }

        .signature-table {
            width: 100%;
            margin-top: 10px;
            text-align: center;
        }

        .footer-note {
            margin-top: 30px;
            text-align: justify;
            line-height: 1.5;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
        }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td width="15%"><img
                    src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo/logo_PENS.png'))) }}"
                    class="logo-pens"></td>
            <td class="header-text">
                <p>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,</p>
                <p>RISET DAN TEKNOLOGI</p>
                <h3>POLITEKNIK ELEKTRONIKA NEGERI SURABAYA</h3>
                <h3 style="letter-spacing: 2px;">KAMPUS SUMENEP</h3>
                <p style="font-size: 10pt; margin-top: 5px; font-weight: 200;">
                    Jl. Raya Lenteng KM.2 Batuan Kabupaten Sumenep<br>
                    Telepon: 032867419, WA: 081394646263<br> Laman: https://www.pens.ac.id
                </p>
            </td>
        </tr>
    </table>

    <div class="title">
        FORM PEMINJAMAN RUANG<br>
        LAB. KOMPUTER PSDKU SUMENEP
    </div>

    <p>Saya yang bertanda tangan di bawah ini :</p>
    <table class="content-table">
        <tr>
            <td width="30%">Nama</td>
            <td width="2%">:</td>
            <td>{{ $peminjaman->mahasiswa->nama_asli }}</td>
        </tr>
        <tr>
            <td>Unit Kerja / Jurusan</td>
            <td>:</td>
            <td>{{ $peminjaman->mahasiswa->prodi->nama_prodi ?? '' }}</td>
        </tr>
        <tr>
            <td>NRP</td>
            <td>:</td>
            <td>{{ $peminjaman->mahasiswa->nrp }}</td>
        </tr>
    </table>

    <p>Dengan ini mengajukan peminjaman Ruang Laboratorium :</p>
    <table class="content-table">
        <tr>
            <td width="30%">Nama Lab</td>
            <td width="2%">:</td>
            <td>{{ $peminjaman->lab->nama_lab }} ({{ $peminjaman->lab->kode_lab }})</td>
        </tr>
        <tr>
            <td>Untuk Kepentingan</td>
            <td>:</td>
            <td>{{ $peminjaman->tujuan }}</td>
        </tr>
        @php
            $hari_mulai = Carbon\Carbon::parse($peminjaman->waktu_mulai)->locale('id')->translatedFormat('l');
            $hari_selesai = Carbon\Carbon::parse($peminjaman->waktu_selesai)->locale('id')->translatedFormat('l');
            $tgl_mulai = Carbon\Carbon::parse($peminjaman->waktu_mulai)->locale('id')->translatedFormat('d F Y');
            $tgl_selesai = Carbon\Carbon::parse($peminjaman->waktu_selesai)->locale('id')->translatedFormat('d F Y');
        @endphp
        <tr>
            <td>Hari</td>
            <td>:</td>
            <td>{{ $hari_mulai }}, {{ $tgl_mulai }} s/d {{ $tgl_selesai }}</td>
        </tr>
        <tr>
            <td>Waktu / Jam</td>
            <td>:</td>
            <td>{{ Carbon\Carbon::parse($peminjaman->waktu_mulai)->format('H:i') }} s/d
                {{ Carbon\Carbon::parse($peminjaman->waktu_selesai)->format('H:i') }} WIB</td>
        </tr>
    </table>

    <p class="footer-note">
        Demikian permohonan ini dibuat, dan saya menyatakan bertanggung jawab sepenuhnya atas peminjaman ruang tersebut
        di atas dan bersedia memenuhi semua syarat dan ketentuan yang berlaku.
    </p>

    <p style="text-align: right; padding-top: 15px">Sumenep,
        {{ $peminjaman->tgl_ttd_kalab->locale('id')->translatedFormat('d F Y') }}</p>

    <table class="signature-table">
        <tr>
            <td colspan="2" style="padding-left: 60%;">Peminjam,</td>
        </tr>
        <tr style="height: 80px;">
            <td colspan="2" style="padding-left: 60%;">
                @if ($peminjaman->ttd_mahasiswa_file)
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/signatures/' . $peminjaman->ttd_mahasiswa_file))) }}"
                        style="height: 50px;">
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-left: 60%;">
                <strong>{{ $peminjaman->mahasiswa->nama_asli }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-left: 60%;">NRP. {{ $peminjaman->mahasiswa->nrp }}
            </td>
        </tr>
        </div>
        <tr>
            <td colspan="2" style="padding-top: 20px;">Mengetahui,</td>
        </tr>
        <tr>
            <td>Kepala {{ $peminjaman->lab->nama_lab }}, </td>
            <td style="padding-top: 30px;">Teknisi Lab PSDKU Sumenep, </td>
        </tr>
        <tr style="height: 80px;">
            <td>
                @if ($peminjaman->ttd_kalab_file)
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/signatures/' . $peminjaman->ttd_kalab_file))) }}"
                        style="height: 50px;">
                @endif
            </td>
            <td>
                @if ($peminjaman->ttd_plp_file)
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('storage/signatures/' . $peminjaman->ttd_plp_file))) }}"
                        style="height: 50px;">
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>{{ $peminjaman->labManager->kalab->full_name ?? '' }}</strong> </td>
            <td>
                <strong>{{ $peminjaman->labManager->plp->full_name ?? 'Bagus Edi Fathorrasi, A.Md.Kom.' }}</strong>
            </td>
        </tr>
        <tr>
            <td>NIP. {{ $peminjaman->labManager->kalab->nip ?? '' }} </td>
            <td>NIP. {{ $peminjaman->labManager->plp->nip ?? '200203302025061005' }} </td>
        </tr>
    </table>

    <div class="footer">
        {{-- logo blu --}}
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo/Logo_BLU_Speed.png'))) }}"
            class="logo-blu">
    </div>

</body>

</html>
