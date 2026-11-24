<?php

namespace App\Services;

use App\Models\JadwalLab;
use App\Models\Lab;
use App\Models\User;
use RuntimeException;
use Shuchkin\SimpleXLSX;

class JadwalPerkuliahanParser
{
    /**
     * Parse file Excel Jadwal Perkuliahan (multi-sheet per prodi) menjadi data jadwal per ruangan.
     *
     * @throws RuntimeException
     */
    public function parse(string $filePath): array
    {
        if (!$xlsx = SimpleXLSX::parse($filePath)) {
            throw new RuntimeException('Gagal membaca file Excel: ' . SimpleXLSX::parseError());
        }

        $labs = Lab::all();
        $dosens = User::with('role')
            ->whereHas('role', fn ($q) => $q->where('slug', 'dosen'))
            ->get();

        $meta = [
            'semester' => 'Ganjil',
            'tahun_ajaran' => date('Y') . '/' . (date('Y') + 1),
            'nama_periode' => '',
        ];

        $entries = [];
        $sheetNames = $xlsx->sheetNames();

        foreach (array_keys($sheetNames) as $sheetIndex) {
            $sheetName = (string) ($sheetNames[$sheetIndex] ?? '');
            $rows = $xlsx->rows($sheetIndex);

            if (empty($rows)) {
                continue;
            }

            $sheetMeta = $this->extractMetaFromRows($rows, $sheetName);
            if ($sheetMeta['semester']) {
                $meta['semester'] = $sheetMeta['semester'];
            }
            if ($sheetMeta['tahun_ajaran']) {
                $meta['tahun_ajaran'] = $sheetMeta['tahun_ajaran'];
            }

            $sheetEntries = $this->parseSheet(
                $rows,
                $sheetMeta['program_studi'],
                $labs,
                $dosens
            );

            $entries = array_merge($entries, $sheetEntries);
        }

        $meta['nama_periode'] = 'Semester ' . $meta['semester'] . ' ' . $meta['tahun_ajaran'];

        $entries = $this->sortEntries($entries);
        [$entries, $conflicts] = $this->detectConflicts($entries);

        $rooms = JadwalLab::sortRoomNames(
            array_values(array_unique(array_column($entries, 'nama_ruangan')))
        );

        $unmappedLabs = count(array_unique(array_column(
            array_filter($entries, fn ($e) => empty($e['lab_id'])),
            'nama_ruangan'
        )));

        $unmappedDosens = count(array_unique(array_column(
            array_filter($entries, fn ($e) => empty($e['dosen_id']) && !empty($e['nama_dosen']) && $e['nama_dosen'] !== '-'),
            'nama_dosen'
        )));

        return [
            'meta' => $meta,
            'entries' => $entries,
            'rooms' => $rooms,
            'conflicts' => $conflicts,
            'stats' => [
                'total_entries' => count($entries),
                'total_rooms' => count($rooms),
                'total_conflicts' => count($conflicts),
                'unmapped_labs' => $unmappedLabs,
                'unmapped_dosens' => $unmappedDosens,
            ],
        ];
    }

    /**
     * Ekstrak semester, tahun ajaran, dan program studi dari baris awal sheet.
     */
    private function extractMetaFromRows(array $rows, string $sheetName): array
    {
        $semester = null;
        $tahunAjaran = null;
        $programStudi = null;

        $scanLimit = min(6, count($rows));
        for ($i = 0; $i < $scanLimit; $i++) {
            $line = trim(implode(' ', array_map(fn ($v) => trim((string) $v), $rows[$i] ?? [])));
            if ($line === '') {
                continue;
            }

            if (!$semester && preg_match('/\b(GASAL|GANJIL|GENAP)\b/i', $line, $m)) {
                $semWord = strtoupper($m[1]);
                $semester = in_array($semWord, ['GASAL', 'GANJIL'], true) ? 'Ganjil' : 'Genap';
            }

            if (!$tahunAjaran && preg_match('/(\d{4}\s*\/\s*\d{4})/', $line, $m)) {
                $tahunAjaran = preg_replace('/\s+/', '', $m[1]);
            }

            if (!$programStudi) {
                $programStudi = $this->detectProgramStudi($line);
            }
        }

        if (!$programStudi) {
            $programStudi = $this->detectProgramStudi($sheetName) ?: strtoupper(strtok(trim($sheetName), ' ') ?: 'UMUM');
        }

        return [
            'semester' => $semester,
            'tahun_ajaran' => $tahunAjaran,
            'program_studi' => $programStudi,
        ];
    }

    private function detectProgramStudi(string $text): ?string
    {
        $upper = strtoupper($text);
        if (str_contains($upper, 'TEKNIK INFORMATIKA') || preg_match('/\bD?3?TI\b/', $upper)) {
            return 'TI';
        }
        if (str_contains($upper, 'MULTIMEDIA BROADCASTING') || preg_match('/\bD?3?MMB\b/', $upper)) {
            return 'MMB';
        }
        if (preg_match('/PROGRAM\s+STUDI\s+(?:DIPLOMA\s+\d+\s+)?([A-Z0-9\s]+?)(?:\s*-|$)/i', $text, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    /**
     * Parse 1 sheet program studi dengan pola blok 3 baris per Jam Ke.
     */
    private function parseSheet(array $rows, string $programStudi, $labs, $dosens): array
    {
        $headerIdx = $this->findHeaderRowIndex($rows);
        if ($headerIdx === null) {
            return [];
        }

        $headerRow = $rows[$headerIdx] ?? [];
        $classRow = $rows[$headerIdx + 1] ?? [];

        $columns = [];
        $maxCols = max(count($headerRow), count($classRow));
        for ($col = 2; $col < $maxCols; $col++) {
            $semText = trim((string) ($headerRow[$col] ?? ''));
            $kelasText = trim((string) ($classRow[$col] ?? ''));

            if ($semText === '' && $kelasText === '') {
                continue;
            }

            $semNumber = null;
            if (preg_match('/(\d+)/', $semText, $m)) {
                $semNumber = $m[1];
            } elseif (preg_match('/^(\d+)/', $kelasText, $m)) {
                $semNumber = $m[1];
            }

            $cleanKelas = trim((string) preg_replace('/\s*-\s*PSDKU.*$/i', '', $kelasText));
            $colProdi = $programStudi;
            // Jika sheet punya kolom kelas milik prodi lain secara eksplisit (misal 5D3TI di sheet MMB)
            $explicitProdi = $this->detectProgramStudi($cleanKelas);
            if ($explicitProdi && $explicitProdi !== $programStudi) {
                $colProdi = $explicitProdi;
            }

            $columns[$col] = [
                'semester' => $semNumber ?: '-',
                'kelas' => $cleanKelas ?: null,
                'program_studi' => $colProdi,
            ];
        }

        if (empty($columns)) {
            return [];
        }

        $entries = [];
        $activeSessions = [];
        $dayIdx = 0;
        $prevJamKe = 0;
        $rowCount = count($rows);

        for ($r = $headerIdx + 2; $r < $rowCount; $r++) {
            $row = $rows[$r] ?? [];
            $rowJoined = implode(' ', array_map(fn ($v) => trim((string) $v), $row));

            // Hentikan pembacaan ketika mencapai area legenda atau tanda tangan bawah
            if (preg_match('/Hari\s+Senin|Wakil\s+Direktur|Jam\s+ke/i', $rowJoined)) {
                break;
            }

            $jamRaw = trim((string) ($row[1] ?? ''));
            if (!preg_match('/^(\d+)(?:\.0+)?$/', $jamRaw, $jamMatch)) {
                continue;
            }

            $jamKe = (int) $jamMatch[1];
            if ($jamKe < 1 || $jamKe > 10) {
                continue;
            }

            // Pergantian hari ditandai siklus Jam Ke kembali ke 1
            if ($jamKe === 1 && $prevJamKe > 1) {
                foreach ($activeSessions as $session) {
                    $entries[] = $session;
                }
                $activeSessions = [];
                $dayIdx++;
            }
            $prevJamKe = $jamKe;

            $hari = JadwalLab::HARI_ORDER[$dayIdx] ?? null;
            if (!$hari) {
                continue;
            }

            foreach ($columns as $colIdx => $colMeta) {
                $cellMk = trim((string) ($row[$colIdx] ?? ''));

                if ($cellMk === '') {
                    if (isset($activeSessions[$colIdx])) {
                        $entries[] = $activeSessions[$colIdx];
                        unset($activeSessions[$colIdx]);
                    }
                    continue;
                }

                if (strcasecmp($cellMk, 'idem') === 0) {
                    if (isset($activeSessions[$colIdx])) {
                        $activeSessions[$colIdx]['jam_ke_selesai'] = $jamKe;
                        $activeSessions[$colIdx]['waktu_berakhir'] = JadwalLab::JAM_KE_MAP[$jamKe]['end']
                            ?? $activeSessions[$colIdx]['waktu_berakhir'];
                    }
                    continue;
                }

                // Tutup sesi sebelumnya jika ada mata kuliah baru langsung menyambung
                if (isset($activeSessions[$colIdx])) {
                    $entries[] = $activeSessions[$colIdx];
                    unset($activeSessions[$colIdx]);
                }

                $rawJenis = trim((string) ($rows[$r - 1][$colIdx] ?? ''));
                $rawDosen = trim((string) ($rows[$r + 1][$colIdx] ?? ''));
                $rawRuangan = trim((string) ($rows[$r + 2][$colIdx] ?? ''));

                $roomInfo = $this->normalizeAndMatchRoom($rawRuangan, $labs);
                $dosenId = $this->matchDosen($rawDosen, $dosens);

                $activeSessions[$colIdx] = [
                    'lab_id' => $roomInfo['lab_id'],
                    'nama_ruangan' => $roomInfo['nama_ruangan'],
                    'hari' => $hari,
                    'jam_ke_mulai' => $jamKe,
                    'jam_ke_selesai' => $jamKe,
                    'waktu_mulai' => JadwalLab::JAM_KE_MAP[$jamKe]['start'] ?? '08.00',
                    'waktu_berakhir' => JadwalLab::JAM_KE_MAP[$jamKe]['end'] ?? '08.50',
                    'mata_kuliah' => $cellMk,
                    'jenis' => $this->normalizeJenis($rawJenis),
                    'kelas' => $colMeta['kelas'],
                    'semester' => (string) $colMeta['semester'],
                    'program_studi' => $colMeta['program_studi'],
                    'dosen_id' => $dosenId,
                    'nama_dosen' => $rawDosen !== '' ? $rawDosen : '-',
                    'catatan' => '',
                    'is_conflict' => false,
                ];
            }
        }

        foreach ($activeSessions as $session) {
            $entries[] = $session;
        }

        return $entries;
    }

    private function findHeaderRowIndex(array $rows): ?int
    {
        $limit = min(15, count($rows));
        for ($i = 0; $i < $limit; $i++) {
            $colA = preg_replace('/\s+/', '', strtoupper((string) ($rows[$i][0] ?? '')));
            $colB = preg_replace('/\s+/', '', strtoupper((string) ($rows[$i][1] ?? '')));
            if (str_contains($colA, 'HARI') && str_contains($colB, 'JAM')) {
                return $i;
            }
        }

        return null;
    }

    private function normalizeJenis(string $raw): ?string
    {
        $clean = preg_replace('/\s+/', ' ', strtolower(trim($raw)));
        if ($clean === '') {
            return null;
        }
        if (str_contains($clean, 'teori') && str_contains($clean, 'praktek')) {
            return 'Teori & Praktek';
        }
        if (str_contains($clean, 'praktek') || str_contains($clean, 'praktikum')) {
            return 'Praktek';
        }
        if (str_contains($clean, 'teori')) {
            return 'Teori';
        }

        return ucfirst($clean);
    }

    private function normalizeAndMatchRoom(string $raw, $labs): array
    {
        $clean = trim(preg_replace('/\s+/', ' ', $raw));
        if ($clean === '') {
            return ['nama_ruangan' => 'Ruang Belum Diisi', 'lab_id' => null];
        }

        // Hapus prefix kampus seperti "PSDKU-SM ", "PSDKU- ", "PSDKU "
        $stripped = trim((string) preg_replace('/^PSDKU(?:[\s\-]+SM)?[\s\-]*/i', '', $clean));

        $codeCandidate = null;
        $namaRuangan = $stripped;

        if (preg_match('/^(?:Lab\.?\s*)?L[\s.\-]*(\d+[A-Z]?)$/i', $stripped, $m)) {
            $code = 'L-' . strtoupper($m[1]);
            $namaRuangan = 'Lab ' . $code;
            $codeCandidate = $code;
        } elseif (preg_match('/^(?:Kelas\.?\s*)?K[\s.\-]*(\d+[A-Z]?)$/i', $stripped, $m)) {
            $code = 'K-' . strtoupper($m[1]);
            $namaRuangan = 'Kelas ' . $code;
            $codeCandidate = $code;
        } elseif (stripos($stripped, 'studio') !== false) {
            $namaRuangan = 'Studio';
        }

        $matchedLabId = null;
        foreach ($labs as $lab) {
            $labCodeNorm = preg_replace('/[^A-Z0-9]/i', '', (string) $lab->kode_lab);
            if ($codeCandidate !== null) {
                $candNorm = preg_replace('/[^A-Z0-9]/i', '', $codeCandidate);
                if ($labCodeNorm !== '' && strcasecmp($labCodeNorm, $candNorm) === 0) {
                    $matchedLabId = $lab->id;
                    break;
                }
            } elseif (stripos($namaRuangan, 'studio') !== false && stripos((string) $lab->nama_lab, 'studio') !== false) {
                $matchedLabId = $lab->id;
                break;
            } elseif (strcasecmp((string) $lab->nama_lab, $namaRuangan) === 0) {
                $matchedLabId = $lab->id;
                break;
            }
        }

        return [
            'nama_ruangan' => $namaRuangan,
            'lab_id' => $matchedLabId,
        ];
    }

    private function matchDosen(string $rawDosen, $dosens): ?int
    {
        $target = $this->normalizePersonName($rawDosen);
        if ($target === '') {
            return null;
        }

        foreach ($dosens as $dosen) {
            $candidate = $this->normalizePersonName((string) $dosen->nama_asli);
            if ($candidate !== '' && $candidate === $target) {
                return $dosen->id;
            }
        }

        // Fallback partial match jika salah satu nama menyertakan nama lengkap / singkatan tengah
        $matches = [];
        foreach ($dosens as $dosen) {
            $candidate = $this->normalizePersonName((string) $dosen->nama_asli);
            if ($candidate !== '' && (str_contains($candidate, $target) || str_contains($target, $candidate))) {
                $matches[] = $dosen->id;
            }
        }

        return count($matches) === 1 ? $matches[0] : null;
    }

    private function normalizePersonName(string $name): string
    {
        // Potong gelar belakang setelah koma (misal ", M.Pd" atau ", S.Kom.")
        $base = explode(',', $name)[0];
        // Hilangkan gelar depan umum
        $base = preg_replace('/\b(prof|dr|ir|h|hj|drs|dra)\b\.?/i', '', $base);
        $base = preg_replace('/[^a-z0-9\s]/i', ' ', $base);

        return strtolower(trim((string) preg_replace('/\s+/', ' ', $base)));
    }

    private function sortEntries(array $entries): array
    {
        $roomOrder = array_flip(JadwalLab::sortRoomNames(
            array_values(array_unique(array_column($entries, 'nama_ruangan')))
        ));

        usort($entries, function (array $a, array $b) use ($roomOrder) {
            $ra = $roomOrder[$a['nama_ruangan']] ?? 999;
            $rb = $roomOrder[$b['nama_ruangan']] ?? 999;
            if ($ra !== $rb) {
                return $ra <=> $rb;
            }

            $ha = JadwalLab::hariOrderIndex($a['hari']);
            $hb = JadwalLab::hariOrderIndex($b['hari']);
            if ($ha !== $hb) {
                return $ha <=> $hb;
            }

            return ($a['jam_ke_mulai'] <=> $b['jam_ke_mulai'])
                ?: strcmp($a['waktu_mulai'], $b['waktu_mulai'])
                ?: strcmp($a['mata_kuliah'], $b['mata_kuliah']);
        });

        return $entries;
    }

    private function detectConflicts(array $entries): array
    {
        $conflicts = [];
        $groupedIndices = [];

        foreach ($entries as $idx => $entry) {
            $key = $entry['nama_ruangan'] . '|' . $entry['hari'];
            $groupedIndices[$key][] = $idx;
        }

        foreach ($groupedIndices as $indices) {
            $count = count($indices);
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $aIdx = $indices[$i];
                    $bIdx = $indices[$j];
                    $a = $entries[$aIdx];
                    $b = $entries[$bIdx];

                    if ($a['jam_ke_mulai'] <= $b['jam_ke_selesai'] && $b['jam_ke_mulai'] <= $a['jam_ke_selesai']) {
                        $entries[$aIdx]['is_conflict'] = true;
                        $entries[$bIdx]['is_conflict'] = true;
                        $conflicts[] = sprintf(
                            '%s (%s): "%s" (%s-%s) bentrok dengan "%s" (%s-%s)',
                            $a['nama_ruangan'],
                            $a['hari'],
                            $a['mata_kuliah'],
                            $a['waktu_mulai'],
                            $a['waktu_berakhir'],
                            $b['mata_kuliah'],
                            $b['waktu_mulai'],
                            $b['waktu_berakhir']
                        );
                    }
                }
            }
        }

        return [$entries, $conflicts];
    }
}
