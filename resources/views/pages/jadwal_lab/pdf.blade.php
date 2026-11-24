<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Pemakaian Lab — {{ $periode->nama_periode }}</title>
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
        .page-break {
            page-break-after: always;
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
@foreach ($roomsData as $roomName => $roomInfo)
    @php
        $hasCatatan = collect($roomInfo['days'])
            ->flatten(1)
            ->contains(fn ($item) => trim((string) $item->catatan) !== '');
    @endphp
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <div class="doc-title">Jadwal Penggunaan {{ $roomName }}</div>
        <div class="doc-subtitle">Semester {{ strtolower($periode->semester) }} Tahun Ajaran {{ $periode->tahun_ajaran }}</div>

        <table class="schedule-table">
            <thead>
                <tr>
                    <th style="width: 9%;">Hari</th>
                    <th style="width: {{ $hasCatatan ? '28%' : '33%' }};">Mata Kuliah</th>
                    <th style="width: 9%;">Waktu Mulai</th>
                    <th style="width: 10%;">Waktu Berakhir</th>
                    <th style="width: {{ $hasCatatan ? '19%' : '23%' }};">Dosen Pengajar</th>
                    <th style="width: 7%;">Semester</th>
                    <th style="width: 9%;">Program Studi</th>
                    @if ($hasCatatan)
                        <th style="width: 9%;">Catatan</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach (\App\Models\JadwalLab::HARI_ORDER as $hari)
                    @php $dayItems = $roomInfo['days'][$hari] ?? []; @endphp
                    @if (empty($dayItems))
                        <tr>
                            <td class="hari-col">{{ $hari }}</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            @if ($hasCatatan)
                                <td></td>
                            @endif
                        </tr>
                    @else
                        @foreach ($dayItems as $idx => $item)
                            <tr>
                                @if ($idx === 0)
                                    <td class="hari-col" rowspan="{{ count($dayItems) }}">{{ $hari }}</td>
                                @endif
                                <td>{{ $item->mata_kuliah }}</td>
                                <td class="text-center">{{ $item->waktu_mulai }}</td>
                                <td class="text-center">{{ $item->waktu_berakhir }}</td>
                                <td>{{ $item->display_dosen }}</td>
                                <td class="text-center">{{ $item->semester ?: '-' }}</td>
                                <td class="text-center">{{ $item->program_studi ?: '-' }}</td>
                                @if ($hasCatatan)
                                    <td>{{ $item->catatan ?: '' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @endif
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
    </div>
@endforeach
</body>
</html>
