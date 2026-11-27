<?php

namespace App\Http\Controllers;

use App\Models\JadwalLab;
use App\Models\Lab;
use App\Models\PeriodeJadwal;
use App\Models\User;
use App\Services\JadwalPerkuliahanParser;
use App\Support\ExcelSecurity;
use App\Support\SecurityAudit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Shuchkin\SimpleXLSXGen;
use Throwable;

class JadwalLabController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Jadwal Pemakaian Lab';

        $periodes = PeriodeJadwal::orderByDesc('is_active')
            ->orderByDesc('tahun_ajaran')
            ->orderByDesc('id')
            ->get();

        $selectedPeriode = null;
        if ($request->filled('periode_id')) {
            $selectedPeriode = $periodes->firstWhere('id', (int) $request->periode_id);
        }
        if (!$selectedPeriode) {
            $selectedPeriode = $periodes->firstWhere('is_active', true) ?? $periodes->first();
        }

        $entries = collect();
        if ($selectedPeriode) {
            $entries = JadwalLab::with(['lab', 'dosen'])
                ->where('periode_jadwal_id', $selectedPeriode->id)
                ->get();
        }

        $roomsData = $this->buildRoomScheduleData($entries);
        $conflicts = $this->detectSavedConflicts($entries);
        $conflictIds = $conflicts['ids'];
        $conflictMessages = $conflicts['messages'];

        $labs = Lab::orderBy('kode_lab')->get();
        $dosens = User::whereHas('role', fn($q) => $q->where('slug', 'dosen'))
            ->orderBy('nama_asli')
            ->get();

        $activeRoom = $request->get('ruangan');
        if (!$activeRoom || !isset($roomsData[$activeRoom])) {
            $activeRoom = array_key_first($roomsData);
        }

        return view('pages.jadwal_lab.index', compact(
            'title',
            'periodes',
            'selectedPeriode',
            'roomsData',
            'activeRoom',
            'conflictIds',
            'conflictMessages',
            'labs',
            'dosens'
        ));
    }

    public function previewImport(Request $request, JadwalPerkuliahanParser $parser)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . ExcelSecurity::MAX_IMPORT_KB,
                'mimes:xlsx',
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip',
            ],
        ], [
            'file.required' => 'Silakan pilih file Excel jadwal perkuliahan terlebih dahulu.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx).',
            'file.mimetypes' => 'Tipe konten file tidak valid. Unggah file Excel (.xlsx) asli.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $uploadedFile = $request->file('file');

        try {
            ExcelSecurity::validateImportFile($uploadedFile, ['xlsx']);
        } catch (ValidationException $e) {
            SecurityAudit::log('jadwal_import_rejected', [
                'filename' => $uploadedFile->getClientOriginalName(),
                'reason' => 'invalid_file_structure',
            ], 'warning');
            throw $e;
        }

        try {
            $parsed = $parser->parse($uploadedFile->getRealPath());
        } catch (Throwable $e) {
            return redirect()->route('jadwal-lab.index')
                ->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }

        if (empty($parsed['entries'])) {
            return redirect()->route('jadwal-lab.index')
                ->with('error', 'Tidak ada data jadwal yang berhasil terbaca dari file Excel tersebut. Pastikan format sesuai template jadwal perkuliahan.');
        }

        SecurityAudit::log('jadwal_import_previewed', [
            'filename' => $uploadedFile->getClientOriginalName(),
            'entries' => count($parsed['entries']),
        ]);

        $request->session()->put('jadwal_lab_import_preview', [
            'parsed' => $parsed,
            'sourceFilename' => $uploadedFile->getClientOriginalName(),
        ]);

        return redirect()->route('jadwal-lab.preview-show');
    }

    public function showPreview(Request $request)
    {
        $previewData = $request->session()->get('jadwal_lab_import_preview');
        if (!is_array($previewData) || empty($previewData['parsed']['entries'])) {
            return redirect()->route('jadwal-lab.index')
                ->with('error', 'Sesi preview import tidak ditemukan atau sudah kedaluwarsa. Silakan unggah ulang file Excel.');
        }

        $title = 'Preview Konversi Jadwal Lab';
        $parsed = $previewData['parsed'];
        $sourceFilename = $previewData['sourceFilename'] ?? 'jadwal.xlsx';
        $labs = Lab::orderBy('kode_lab')->get();
        $dosens = User::whereHas('role', fn($q) => $q->where('slug', 'dosen'))
            ->orderBy('nama_asli')
            ->get();

        return view('pages.jadwal_lab.preview', compact(
            'title',
            'parsed',
            'sourceFilename',
            'labs',
            'dosens'
        ));
    }

    public function commitImport(Request $request)
    {
        if ($request->filled('entries_payload') && !$request->has('entries')) {
            $decoded = json_decode((string) $request->input('entries_payload'), true);
            if (is_array($decoded)) {
                $request->merge(['entries' => $decoded]);
            }
        }

        $validated = $request->validate([
            'nama_periode' => ['required', 'string', 'max:100'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'source_filename' => ['nullable', 'string', 'max:255'],
            'set_active' => ['nullable', 'boolean'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.nama_ruangan' => ['required', 'string', 'max:100'],
            'entries.*.lab_id' => ['nullable', 'exists:labs,id'],
            'entries.*.hari' => ['required', Rule::in(JadwalLab::HARI_ORDER)],
            'entries.*.jam_ke_mulai' => ['nullable', 'integer', 'min:1', 'max:10'],
            'entries.*.jam_ke_selesai' => ['nullable', 'integer', 'min:1', 'max:10'],
            'entries.*.waktu_mulai' => ['required', 'string', 'max:10'],
            'entries.*.waktu_berakhir' => ['required', 'string', 'max:10'],
            'entries.*.mata_kuliah' => ['required', 'string', 'max:200'],
            'entries.*.jenis' => ['nullable', 'string', 'max:50'],
            'entries.*.kelas' => ['nullable', 'string', 'max:50'],
            'entries.*.semester' => ['nullable', 'string', 'max:10'],
            'entries.*.program_studi' => ['nullable', 'string', 'max:50'],
            'entries.*.dosen_id' => ['nullable', 'exists:users,id'],
            'entries.*.nama_dosen' => ['nullable', 'string', 'max:150'],
            'entries.*.catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'entries.required' => 'Minimal harus ada 1 baris jadwal untuk disimpan.',
        ]);

        foreach ($validated['entries'] as $idx => $entry) {
            if (!empty($entry['jam_ke_mulai']) && !empty($entry['jam_ke_selesai']) && (int) $entry['jam_ke_selesai'] < (int) $entry['jam_ke_mulai']) {
                throw ValidationException::withMessages([
                    "entries.{$idx}.jam_ke_selesai" => 'Jam ke selesai tidak boleh lebih kecil dari Jam ke mulai.',
                ]);
            }
        }

        $dosenMap = User::whereIn(
            'id',
            collect($validated['entries'])->pluck('dosen_id')->filter()->unique()->all()
        )->get()->keyBy('id');

        $periode = DB::transaction(function () use ($request, $validated, $dosenMap) {
            $isActive = $request->boolean('set_active');
            if ($isActive) {
                PeriodeJadwal::where('is_active', true)->update(['is_active' => false]);
            }

            $periode = PeriodeJadwal::create([
                'nama_periode' => $validated['nama_periode'],
                'semester' => $validated['semester'],
                'tahun_ajaran' => $validated['tahun_ajaran'],
                'is_active' => $isActive,
                'source_filename' => $validated['source_filename'] ?? null,
                'uploaded_by' => Auth::id(),
            ]);

            $now = now();
            $rowsToInsert = [];

            foreach ($validated['entries'] as $entry) {
                $dosenId = !empty($entry['dosen_id']) ? (int) $entry['dosen_id'] : null;
                $namaDosen = trim((string) ($entry['nama_dosen'] ?? ''));
                if ($namaDosen === '' && $dosenId && isset($dosenMap[$dosenId])) {
                    $namaDosen = $dosenMap[$dosenId]->nama_asli;
                }

                $rowsToInsert[] = [
                    'periode_jadwal_id' => $periode->id,
                    'lab_id' => !empty($entry['lab_id']) ? (int) $entry['lab_id'] : null,
                    'nama_ruangan' => trim($entry['nama_ruangan']),
                    'hari' => $entry['hari'],
                    'jam_ke_mulai' => $entry['jam_ke_mulai'] ?? null,
                    'jam_ke_selesai' => $entry['jam_ke_selesai'] ?? null,
                    'waktu_mulai' => trim($entry['waktu_mulai']),
                    'waktu_berakhir' => trim($entry['waktu_berakhir']),
                    'mata_kuliah' => trim($entry['mata_kuliah']),
                    'jenis' => !empty($entry['jenis']) ? $entry['jenis'] : null,
                    'kelas' => !empty($entry['kelas']) ? trim($entry['kelas']) : null,
                    'semester' => !empty($entry['semester']) ? trim($entry['semester']) : null,
                    'program_studi' => !empty($entry['program_studi']) ? trim($entry['program_studi']) : null,
                    'dosen_id' => $dosenId,
                    'nama_dosen' => $namaDosen !== '' ? $namaDosen : '-',
                    'catatan' => !empty($entry['catatan']) ? trim($entry['catatan']) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            JadwalLab::insert($rowsToInsert);

            return $periode;
        });

        $request->session()->forget('jadwal_lab_import_preview');

        SecurityAudit::log('jadwal_import_committed', [
            'periode_id' => $periode->id,
            'entries' => count($validated['entries']),
        ]);

        return redirect()->route('jadwal-lab.index', ['periode_id' => $periode->id])
            ->with('success', 'Berhasil mengimpor ' . count($validated['entries']) . ' sesi jadwal ke periode "' . $periode->nama_periode . '".');
    }

    public function store(Request $request)
    {
        $data = $this->validateEntry($request);
        $data['periode_jadwal_id'] = $request->validate([
            'periode_jadwal_id' => ['required', 'exists:periode_jadwals,id'],
        ])['periode_jadwal_id'];

        $jadwal = JadwalLab::create($data);

        return redirect()->route('jadwal-lab.index', [
            'periode_id' => $jadwal->periode_jadwal_id,
            'ruangan' => $jadwal->nama_ruangan,
        ])->with('success', 'Jadwal "' . $jadwal->mata_kuliah . '" berhasil ditambahkan.');
    }

    public function update(Request $request, JadwalLab $jadwalLab)
    {
        $data = $this->validateEntry($request);
        $jadwalLab->update($data);

        return redirect()->route('jadwal-lab.index', [
            'periode_id' => $jadwalLab->periode_jadwal_id,
            'ruangan' => $jadwalLab->nama_ruangan,
        ])->with('success', 'Jadwal "' . $jadwalLab->mata_kuliah . '" berhasil diperbarui.');
    }

    public function destroy(JadwalLab $jadwalLab)
    {
        $periodeId = $jadwalLab->periode_jadwal_id;
        $ruangan = $jadwalLab->nama_ruangan;
        $mk = $jadwalLab->mata_kuliah;

        $jadwalLab->delete();

        return redirect()->route('jadwal-lab.index', [
            'periode_id' => $periodeId,
            'ruangan' => $ruangan,
        ])->with('success', 'Jadwal "' . $mk . '" berhasil dihapus.');
    }

    public function activatePeriode(PeriodeJadwal $periode)
    {
        DB::transaction(function () use ($periode) {
            PeriodeJadwal::where('is_active', true)->update(['is_active' => false]);
            $periode->update(['is_active' => true]);
        });

        return redirect()->route('jadwal-lab.index', ['periode_id' => $periode->id])
            ->with('success', 'Periode "' . $periode->nama_periode . '" telah diaktifkan.');
    }

    public function destroyPeriode(PeriodeJadwal $periode)
    {
        $nama = $periode->nama_periode;
        $periode->delete();

        return redirect()->route('jadwal-lab.index')
            ->with('success', 'Periode "' . $nama . '" beserta seluruh jadwalnya telah dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $periode = $this->resolvePeriode($request->get('periode_id'));
        if (!$periode) {
            return redirect()->route('jadwal-lab.index')
                ->with('error', 'Belum ada periode jadwal untuk dicetak.');
        }

        $query = JadwalLab::with(['lab', 'dosen'])
            ->where('periode_jadwal_id', $periode->id);

        if ($request->filled('ruangan')) {
            $query->where('nama_ruangan', $request->get('ruangan'));
        }

        $entries = $query->get();
        if ($entries->isEmpty()) {
            return redirect()->route('jadwal-lab.index', ['periode_id' => $periode->id])
                ->with('error', 'Tidak ada data jadwal pada ruangan yang dipilih.');
        }

        $roomsData = $this->buildRoomScheduleData($entries);
        $jamKeMap = JadwalLab::JAM_KE_MAP;

        $suffix = $request->filled('ruangan')
            ? '_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $request->get('ruangan'))
            : '_Semua_Ruangan';
        $fileName = ExcelSecurity::safeFileName(
            'Jadwal_Pemakaian_Lab_' . str_replace('/', '-', $periode->tahun_ajaran) . $suffix,
            'pdf'
        );

        $pdf = Pdf::loadView('pages.jadwal_lab.pdf', compact('periode', 'roomsData', 'jamKeMap'))
            ->setOption('isRemoteEnabled', false)
            ->setPaper('a4', 'landscape');

        return $pdf->stream($fileName);
    }

    public function exportExcel(Request $request)
    {
        $periode = $this->resolvePeriode($request->get('periode_id'));
        if (!$periode) {
            return redirect()->route('jadwal-lab.index')
                ->with('error', 'Belum ada periode jadwal untuk diexport.');
        }

        $query = JadwalLab::with(['lab', 'dosen'])
            ->where('periode_jadwal_id', $periode->id);

        if ($request->filled('ruangan')) {
            $query->where('nama_ruangan', $request->get('ruangan'));
        }

        $entries = $query->get();
        if ($entries->isEmpty()) {
            return redirect()->route('jadwal-lab.index', ['periode_id' => $periode->id])
                ->with('error', 'Tidak ada data jadwal pada ruangan yang dipilih.');
        }

        $roomsData = $this->buildRoomScheduleData($entries);
        $xlsx = (new SimpleXLSXGen())
            ->setDefaultFont('Calibri')
            ->setDefaultFontSize(10);

        foreach ($roomsData as $roomName => $roomInfo) {
            $hasCatatan = collect($roomInfo['days'])
                ->flatten(1)
                ->contains(fn(JadwalLab $item) => trim((string) $item->catatan) !== '');

            $lastCol = $hasCatatan ? 'H' : 'G';
            $colCount = $hasCatatan ? 8 : 7;

            $padRow = static function (array $cells) use ($colCount): array {
                return array_pad($cells, $colCount, '');
            };

            $rows = [];
            $merges = ["A1:{$lastCol}1", "A2:{$lastCol}2"];

            // Baris 1: Judul Utama (Simpel tanpa blok warna penuh)
            $rows[] = $padRow([
                '<style font-size="14" height="24"><center><middle><b>' . ExcelSecurity::rawCell('Jadwal Penggunaan ' . $roomName) . '</b></middle></center></style>',
            ]);

            // Baris 2: Subjudul Semester & Tahun Ajaran
            $rows[] = $padRow([
                '<style font-size="11" height="20"><center><middle><b>' . ExcelSecurity::rawCell('Semester ' . ucfirst(strtolower($periode->semester)) . ' Tahun Ajaran ' . $periode->tahun_ajaran) . '</b></middle></center></style>',
            ]);

            // Baris 3: Spasi pemisah tipis
            $rows[] = $padRow(['<style height="8"></style>']);

            // Baris 4: Header Tabel (Simpel & Bersih)
            $headerStyle = 'bgcolor="#D9E1F2" color="#000000" font-size="10" border="thin#808080" height="22"';
            $headerRow = [
                "<style {$headerStyle}><center><middle><b>Hari</b></middle></center></style>",
                "<style {$headerStyle}><center><middle><b>Mata Kuliah</b></middle></center></style>",
                "<style {$headerStyle}><center><middle><b>Waktu Mulai</b></middle></center></style>",
                "<style {$headerStyle}><center><middle><b>Waktu Berakhir</b></middle></center></style>",
                "<style {$headerStyle}><center><middle><b>Dosen Pengajar</b></middle></center></style>",
                "<style {$headerStyle}><center><middle><b>Semester</b></middle></center></style>",
                "<style {$headerStyle}><center><middle><b>Program Studi</b></middle></center></style>",
            ];
            if ($hasCatatan) {
                $headerRow[] = "<style {$headerStyle}><center><middle><b>Catatan</b></middle></center></style>";
            }
            $rows[] = $headerRow;

            $currentRow = 5;
            $dataRowIdx = 0;
            foreach (JadwalLab::HARI_ORDER as $hari) {
                $dayEntries = $roomInfo['days'][$hari] ?? [];
                $hariCell = '<style bgcolor="#FFFFFF" color="#000000" font-size="10" border="thin#BFBFBF"><center><middle><b>' . ExcelSecurity::rawCell($hari) . '</b></middle></center></style>';

                if (empty($dayEntries)) {
                    $rowBg = $dataRowIdx % 2 === 0 ? '#FFFFFF' : '#EEF3FA';
                    $emptyCell = "<style bgcolor=\"{$rowBg}\" color=\"#7F7F7F\" font-size=\"10\" border=\"thin#BFBFBF\" height=\"20\"><center><middle>-</middle></center></style>";
                    $emptyRow = [
                        $hariCell,
                        $emptyCell,
                        $emptyCell,
                        $emptyCell,
                        $emptyCell,
                        $emptyCell,
                        $emptyCell,
                    ];
                    if ($hasCatatan) {
                        $emptyRow[] = "<style bgcolor=\"{$rowBg}\" color=\"#7F7F7F\" font-size=\"10\" border=\"thin#BFBFBF\"><center><middle>-</middle></center></style>";
                    }
                    $rows[] = $emptyRow;
                    $currentRow++;
                    $dataRowIdx++;
                    continue;
                }

                $startMerge = $currentRow;
                foreach ($dayEntries as $idx => $item) {
                    $rowBg = $dataRowIdx % 2 === 0 ? '#FFFFFF' : '#EEF3FA';
                    $row = [
                        $idx === 0 ? $hariCell : '<style bgcolor="#FFFFFF" border="thin#BFBFBF"></style>',
                        "<style bgcolor=\"{$rowBg}\" color=\"#000000\" font-size=\"10\" border=\"thin#BFBFBF\"><middle><wraptext>" . ExcelSecurity::rawCell($item->mata_kuliah, '-') . '</wraptext></middle></style>',
                        "<style bgcolor=\"{$rowBg}\" color=\"#000000\" font-size=\"10\" border=\"thin#BFBFBF\"><center><middle>" . ExcelSecurity::rawCell($item->waktu_mulai, '-') . '</middle></center></style>',
                        "<style bgcolor=\"{$rowBg}\" color=\"#000000\" font-size=\"10\" border=\"thin#BFBFBF\"><center><middle>" . ExcelSecurity::rawCell($item->waktu_berakhir, '-') . '</middle></center></style>',
                        "<style bgcolor=\"{$rowBg}\" color=\"#000000\" font-size=\"10\" border=\"thin#BFBFBF\"><middle><wraptext>" . ExcelSecurity::rawCell($item->display_dosen, '-') . '</wraptext></middle></style>',
                        "<style bgcolor=\"{$rowBg}\" color=\"#000000\" font-size=\"10\" border=\"thin#BFBFBF\"><center><middle>" . ExcelSecurity::rawCell($item->semester ?: '-', '-') . '</middle></center></style>',
                        "<style bgcolor=\"{$rowBg}\" color=\"#000000\" font-size=\"10\" border=\"thin#BFBFBF\"><center><middle>" . ExcelSecurity::rawCell($item->program_studi ?: '-', '-') . '</middle></center></style>',
                    ];
                    if ($hasCatatan) {
                        $row[] = "<style bgcolor=\"{$rowBg}\" color=\"#000000\" font-size=\"10\" border=\"thin#BFBFBF\"><middle><wraptext>" . ExcelSecurity::rawCell($item->catatan ?: '', '') . '</wraptext></middle></style>';
                    }
                    $rows[] = $row;
                    $currentRow++;
                    $dataRowIdx++;
                }

                if (count($dayEntries) > 1) {
                    $merges[] = 'A' . $startMerge . ':A' . ($currentRow - 1);
                }
            }

            // Baris kosong pemisah & Keterangan Jam Ke (polos seperti format acuan)
            $rows[] = $padRow(['']);
            $rows[] = $padRow(['', '<b>Hari Senin s/d Kamis</b>', '', '<b>Hari Jum\'at</b>']);
            $rows[] = $padRow(['', '<b>Jam ke</b>', '', '<b>Jam ke</b>']);

            foreach (range(1, 5) as $jam) {
                $map = JadwalLab::JAM_KE_MAP[$jam];
                $seninText = $jam . ' : ' . $map['start'] . ' - ' . $map['end'];
                $jumatText = $jam <= 4 ? ('<raw>' . $seninText . '</raw>') : '';
                $rows[] = $padRow([
                    '',
                    '<raw>' . $seninText . '</raw>',
                    '',
                    $jumatText,
                ]);
            }

            $rows[] = $padRow(['', '<b>ISTIRAHAT (12.10-13.00)</b>']);

            foreach (range(6, 10) as $jam) {
                $map = JadwalLab::JAM_KE_MAP[$jam];
                $seninText = $jam . ' : ' . $map['start'] . ' - ' . $map['end'];
                $rows[] = $padRow([
                    '',
                    '<raw>' . $seninText . '</raw>',
                ]);
            }

            $sheetTitle = str_replace(['[', ']', ':', '*', '?', '/', '\\'], ' ', $roomName);
            $xlsx->addSheet($rows, mb_substr(trim($sheetTitle), 0, 31));
            foreach ($merges as $range) {
                $xlsx->mergeCells($range);
            }
            $xlsx->setColWidth(1, 14)
                ->setColWidth(2, 40)
                ->setColWidth(3, 15)
                ->setColWidth(4, 16)
                ->setColWidth(5, 30)
                ->setColWidth(6, 12)
                ->setColWidth(7, 16);

            if ($hasCatatan) {
                $xlsx->setColWidth(8, 26);
            }
        }

        $fileName = ExcelSecurity::safeFileName(
            'Jadwal_Pemakaian_Lab_' . str_replace('/', '-', $periode->tahun_ajaran),
            'xlsx'
        );

        return response((string) $xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function resolvePeriode($periodeId): ?PeriodeJadwal
    {
        if ($periodeId) {
            return PeriodeJadwal::find($periodeId);
        }

        return PeriodeJadwal::active()->first() ?? PeriodeJadwal::orderByDesc('id')->first();
    }

    private function validateEntry(Request $request): array
    {
        $validated = $request->validate([
            'lab_id' => ['nullable', 'exists:labs,id'],
            'nama_ruangan' => ['required', 'string', 'max:100'],
            'hari' => ['required', Rule::in(JadwalLab::HARI_ORDER)],
            'jam_ke_mulai' => ['nullable', 'integer', 'min:1', 'max:10'],
            'jam_ke_selesai' => ['nullable', 'integer', 'min:1', 'max:10'],
            'waktu_mulai' => ['required', 'string', 'max:10'],
            'waktu_berakhir' => ['required', 'string', 'max:10'],
            'mata_kuliah' => ['required', 'string', 'max:200'],
            'jenis' => ['nullable', 'string', 'max:50'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:10'],
            'program_studi' => ['nullable', 'string', 'max:50'],
            'dosen_id' => ['nullable', 'exists:users,id'],
            'nama_dosen' => ['nullable', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        if (!empty($validated['jam_ke_mulai']) && !empty($validated['jam_ke_selesai']) && (int) $validated['jam_ke_selesai'] < (int) $validated['jam_ke_mulai']) {
            throw ValidationException::withMessages([
                'jam_ke_selesai' => 'Jam ke selesai harus lebih besar atau sama dengan Jam ke mulai.',
            ]);
        }

        if (!empty($validated['dosen_id']) && empty(trim((string) ($validated['nama_dosen'] ?? '')))) {
            $dosen = User::find($validated['dosen_id']);
            $validated['nama_dosen'] = $dosen?->nama_asli ?? '-';
        }

        return $validated;
    }

    private function buildRoomScheduleData(Collection $entries): array
    {
        $roomNames = JadwalLab::sortRoomNames(
            $entries->pluck('nama_ruangan')->unique()->values()->all()
        );

        $result = [];
        foreach ($roomNames as $roomName) {
            $roomEntries = $entries->where('nama_ruangan', $roomName);
            $days = [];
            foreach (JadwalLab::HARI_ORDER as $hari) {
                $days[$hari] = $roomEntries
                    ->where('hari', $hari)
                    ->sort(function (JadwalLab $a, JadwalLab $b) {
                        return (($a->jam_ke_mulai ?? 99) <=> ($b->jam_ke_mulai ?? 99))
                            ?: strcmp($a->waktu_mulai, $b->waktu_mulai)
                            ?: strcmp($a->mata_kuliah, $b->mata_kuliah);
                    })
                    ->values()
                    ->all();
            }

            $result[$roomName] = [
                'room' => $roomName,
                'days' => $days,
                'total' => $roomEntries->count(),
            ];
        }

        return $result;
    }

    private function detectSavedConflicts(Collection $entries): array
    {
        $conflictIds = [];
        $messages = [];

        $grouped = $entries->groupBy(fn(JadwalLab $item) => $item->nama_ruangan . '|' . $item->hari);

        foreach ($grouped as $group) {
            $list = $group->values();
            $count = $list->count();
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $a = $list[$i];
                    $b = $list[$j];

                    $overlap = ($a->jam_ke_mulai && $a->jam_ke_selesai && $b->jam_ke_mulai && $b->jam_ke_selesai)
                        ? ($a->jam_ke_mulai <= $b->jam_ke_selesai && $b->jam_ke_mulai <= $a->jam_ke_selesai)
                        : ($a->waktu_mulai < $b->waktu_berakhir && $b->waktu_mulai < $a->waktu_berakhir);

                    if ($overlap) {
                        $conflictIds[$a->id] = true;
                        $conflictIds[$b->id] = true;
                        $messages[] = sprintf(
                            '%s (%s): "%s" (%s-%s) bentrok dengan "%s" (%s-%s)',
                            $a->nama_ruangan,
                            $a->hari,
                            $a->mata_kuliah,
                            $a->waktu_mulai,
                            $a->waktu_berakhir,
                            $b->mata_kuliah,
                            $b->waktu_mulai,
                            $b->waktu_berakhir
                        );
                    }
                }
            }
        }

        return ['ids' => $conflictIds, 'messages' => $messages];
    }

    private function xmlSafe(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
