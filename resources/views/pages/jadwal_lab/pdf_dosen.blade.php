<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Mengajar — {{ $dosen->full_name }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 12mm 14mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.25;
        }
        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 10.5pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table.schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.schedule-table th,
        table.schedule-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        table.schedule-table th {
            background-color: #d9e1f2;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt;
        }
        .text-center {
            text-align: center;
        }
        .hari-col {
            text-align: center;
            font-weight: bold;
        }
        .mk-meta {
            font-size: 7.5pt;
            color: #444;
        }
        table.legend-table {
            border-collapse: collapse;
            width: auto;
            font-size: 8pt;
        }
        table.legend-table td {
            border: none;
            padding: 1px 18px 1px 0;
            vertical-align: top;
        }
        .fw-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $hasCatatan = collect($mySchedule['days'] ?? [])
            ->flatten(1)
            ->contains(fn ($item) => trim((string) $item->catatan) !== '');
    @endphp

    <div class="doc-title">Jadwal Mengajar — {{ $dosen->full_name }}</div>
    <div class="doc-subtitle">Semester {{ strtolower($periode->semester) }} Tahun Ajaran {{ $periode->tahun_ajaran }}</div>

    <table class="schedule-table">
        <thead>
            <tr>
                <th style="width: 9%;">Hari</th>
                <th style="width: 13%;">Ruangan / Lab</th>
                <th style="width: {{ $hasCatatan ? '31%' : '38%' }};">Mata Kuliah</th>
                <th style="width: 9%;">Waktu Mulai</th>
                <th style="width: 10%;">Waktu Berakhir</th>
                <th style="width: 8%;">Semester</th>
                <th style="width: 10%;">Program Studi</th>
                @if ($hasCatatan)
                    <th style="width: 10%;">Catatan</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach (\App\Models\JadwalLab::HARI_ORDER as $hari)
                @php $dayItems = $mySchedule['days'][$hari] ?? []; @endphp
                @foreach ($dayItems as $idx => $item)
                    @php
                        $metaParts = array_filter([$item->jenis, $item->kelas]);
                    @endphp
                    <tr>
                        @if ($idx === 0)
                            <td class="hari-col" rowspan="{{ count($dayItems) }}">{{ $hari }}</td>
                        @endif
                        <td class="text-center fw-bold">{{ $item->nama_ruangan }}</td>
                        <td>
                            {{ $item->mata_kuliah }}
                            @if (!empty($metaParts))
                                <div class="mk-meta">{{ implode(' • ', $metaParts) }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->waktu_mulai }}</td>
                        <td class="text-center">{{ $item->waktu_berakhir }}</td>
                        <td class="text-center">{{ $item->semester ?: '-' }}</td>
                        <td class="text-center">{{ $item->program_studi ?: '-' }}</td>
                        @if ($hasCatatan)
                            <td>{{ $item->catatan ?: '' }}</td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <table class="legend-table">
        <tr>
            <td class="fw-bold">Hari Senin s/d Kamis</td>
            <td class="fw-bold">Hari Jum'at</td>
        </tr>
        <tr>
            <td class="fw-bold">Jam ke</td>
            <td class="fw-bold">Jam ke</td>
        </tr>
        @foreach (range(1, 5) as $jam)
            <tr>
                <td>{{ $jam }} : {{ $jamKeMap[$jam]['start'] }} - {{ $jamKeMap[$jam]['end'] }}</td>
                <td>
                    @if ($jam <= 4)
                        {{ $jam }} : {{ $jamKeMap[$jam]['start'] }} - {{ $jamKeMap[$jam]['end'] }}
                    @endif
                </td>
            </tr>
        @endforeach
        <tr>
            <td class="fw-bold">ISTIRAHAT (12.10-13.00)</td>
            <td></td>
        </tr>
        @foreach (range(6, 10) as $jam)
            <tr>
                <td>{{ $jam }} : {{ $jamKeMap[$jam]['start'] }} - {{ $jamKeMap[$jam]['end'] }}</td>
                <td></td>
            </tr>
        @endforeach
    </table>
</body>
</html>
