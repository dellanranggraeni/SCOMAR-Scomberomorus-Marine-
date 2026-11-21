<?php

namespace App\Services;

class OceanographicCsvService
{
    protected string $baseDir;
    protected string $suhuDir;
    protected string $chlaDir;
    protected string $tssDir;

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = $baseDir ?: storage_path('app/private/datasets');
        $this->suhuDir = $this->baseDir . DIRECTORY_SEPARATOR . 'suhu';
        $this->chlaDir = $this->baseDir . DIRECTORY_SEPARATOR . 'klorofil-a';
        $this->tssDir  = $this->baseDir . DIRECTORY_SEPARATOR . 'tss';
    }

    /**
     * Membaca dan memproses 4 file CSV dataset oseanografi SPL secara dinamis.
     * Mengembalikan data musiman per-tahun dan per-musim (2021–2026).
     *
     * @return array{
     *     byYear: array<string, array<int, float|null>>,
     *     bySeason: array{years: array<string>, series: array<string, array{key: string, label: string, file: string, color: string, data: array<float|null>}>},
     *     years: array<string>,
     *     files: array<string, string>,
     *     annualAverages: array<string, float|null>
     * }
     */
    public function getSeasonalSplData(): array
    {
        $fileMap = [
            'barat'      => 'MUSIM BARAT RATA2.csv',
            'peralihan1' => 'RATA RATA MUSIM PERALIHAN 1.csv',
            'timur'      => 'MUSIM TIMUR RATA RATA.csv',
            'peralihan2' => 'MUSIM PERALIHAN 2 RATA RATA.csv',
        ];

        $seasonalData = [
            'barat'      => [],
            'peralihan1' => [],
            'timur'      => [],
            'peralihan2' => [],
        ];

        $resolvedFiles = [];
        $allYears = [];

        foreach ($fileMap as $key => $fileName) {
            $candidates = [
                $this->suhuDir . DIRECTORY_SEPARATOR . $fileName,
                $this->baseDir . DIRECTORY_SEPARATOR . $fileName,
                $this->baseDir . DIRECTORY_SEPARATOR . 'suhu' . DIRECTORY_SEPARATOR . $fileName,
                $this->baseDir . DIRECTORY_SEPARATOR . 'spl' . DIRECTORY_SEPARATOR . $fileName,
            ];

            $filePath = null;
            foreach ($candidates as $c) {
                if (file_exists($c)) {
                    $filePath = $c;
                    break;
                }
            }

            if (!$filePath) {
                $resolvedFiles[$key] = $fileName;
                continue;
            }

            $resolvedFiles[$key] = basename($filePath);
            $extracted = $this->parseSeasonalCsv($filePath, 2);
            $seasonalData[$key] = $extracted;

            foreach (array_keys($extracted) as $y) {
                $sY = (string) $y;
                if (!in_array($sY, $allYears, true)) {
                    $allYears[] = $sY;
                }
            }
        }

        $allYears = array_values(array_unique($allYears));
        sort($allYears);
        if (empty($allYears)) {
            $allYears = ['2021', '2022', '2023', '2024', '2025', '2026'];
        }

        // Struktur per tahun: [ '2021' => [barat, p1, timur, p2], ... ]
        $byYear = [];
        foreach ($allYears as $y) {
            $byYear[$y] = [
                isset($seasonalData['barat'][$y]) ? $seasonalData['barat'][$y] : null,
                isset($seasonalData['peralihan1'][$y]) ? $seasonalData['peralihan1'][$y] : null,
                isset($seasonalData['timur'][$y]) ? $seasonalData['timur'][$y] : null,
                isset($seasonalData['peralihan2'][$y]) ? $seasonalData['peralihan2'][$y] : null,
            ];
        }

        // Hitung rata-rata tahunan SPL berdasarkan 4 musim
        $annualAverages = [];
        foreach ($allYears as $y) {
            $vals = array_filter($byYear[$y], fn($v) => $v !== null);
            $annualAverages[$y] = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : null;
        }

        // Struktur per musim: untuk grafik komparasi 4 seri
        $bySeason = [
            'years' => $allYears,
            'series' => [
                'barat' => [
                    'key'   => 'barat',
                    'label' => 'Musim Barat',
                    'file'  => $resolvedFiles['barat'] ?? $fileMap['barat'],
                    'color' => '#24364B', // Deep Navy Blue
                    'data'  => array_map(fn($y) => $seasonalData['barat'][$y] ?? null, $allYears),
                ],
                'peralihan1' => [
                    'key'   => 'peralihan1',
                    'label' => 'Musim Peralihan I',
                    'file'  => $resolvedFiles['peralihan1'] ?? $fileMap['peralihan1'],
                    'color' => '#5B7A9C', // Cashmere Slate
                    'data'  => array_map(fn($y) => $seasonalData['peralihan1'][$y] ?? null, $allYears),
                ],
                'timur' => [
                    'key'   => 'timur',
                    'label' => 'Musim Timur',
                    'file'  => $resolvedFiles['timur'] ?? $fileMap['timur'],
                    'color' => '#73343A', // Merlot
                    'data'  => array_map(fn($y) => $seasonalData['timur'][$y] ?? null, $allYears),
                ],
                'peralihan2' => [
                    'key'   => 'peralihan2',
                    'label' => 'Musim Peralihan II',
                    'file'  => $resolvedFiles['peralihan2'] ?? $fileMap['peralihan2'],
                    'color' => '#8E9FA3', // Cashmere Soft Slate
                    'data'  => array_map(fn($y) => $seasonalData['peralihan2'][$y] ?? null, $allYears),
                ],
            ]
        ];

        return [
            'byYear'         => $byYear,
            'bySeason'       => $bySeason,
            'years'          => $allYears,
            'files'          => $resolvedFiles,
            'annualAverages' => $annualAverages,
        ];
    }

    /**
     * Membaca dan memproses 4 file CSV dataset Klorofil-a secara dinamis dari folder:
     * storage/app/private/datasets/klorofil-a/
     * Mengembalikan data musiman per-tahun dan per-musim (2021–2026).
     *
     * @return array{
     *     byYear: array<string, array<int, float|null>>,
     *     bySeason: array{years: array<string>, series: array<string, array{key: string, label: string, file: string, color: string, data: array<float|null>}>},
     *     years: array<string>,
     *     files: array<string, string>
     * }
     */
    public function getSeasonalChlaData(): array
    {
        $candidateFiles = [
            'barat'      => ['BARAT KROLOFIL A.csv', 'MUSIM BARAT RATA2.csv'],
            'peralihan1' => ['CHL PERALIHAN 1.csv', 'RATA RATA MUSIM PERALIHAN 1.csv'],
            'timur'      => ['CHL RATA RATA MUSIM TIMUR.csv', 'MUSIM TIMUR RATA RATA.csv'],
            'peralihan2' => ['RATA RATA MUSIM PERALIHAN 2.csv', 'MUSIM PERALIHAN 2 RATA RATA.csv'],
        ];

        $seasonalData = [
            'barat'      => [],
            'peralihan1' => [],
            'timur'      => [],
            'peralihan2' => [],
        ];

        $resolvedFiles = [];
        $allYears = [];

        foreach ($candidateFiles as $key => $candidates) {
            $foundPath = null;
            $foundName = null;

            foreach ($candidates as $name) {
                $p1 = $this->chlaDir . DIRECTORY_SEPARATOR . $name;
                if (file_exists($p1)) {
                    $foundPath = $p1;
                    $foundName = $name;
                    break;
                }
                $p2 = $this->baseDir . DIRECTORY_SEPARATOR . $name;
                if (file_exists($p2)) {
                    $foundPath = $p2;
                    $foundName = $name;
                    break;
                }
            }

            if ($foundPath && file_exists($foundPath)) {
                $resolvedFiles[$key] = $foundName;
                $extracted = $this->parseSeasonalCsv($foundPath, 6);
                $seasonalData[$key] = $extracted;

                foreach (array_keys($extracted) as $y) {
                    $sY = (string) $y;
                    if (!in_array($sY, $allYears, true)) {
                        $allYears[] = $sY;
                    }
                }
            } else {
                $resolvedFiles[$key] = $candidates[0];
            }
        }

        $allYears = array_values(array_unique($allYears));
        sort($allYears);
        if (empty($allYears)) {
            $allYears = ['2021', '2022', '2023', '2024', '2025', '2026'];
        }

        // Struktur per tahun: [ '2021' => [barat, p1, timur, p2], ... ]
        $byYear = [];
        foreach ($allYears as $y) {
            $byYear[$y] = [
                array_key_exists($y, $seasonalData['barat']) ? $seasonalData['barat'][$y] : null,
                array_key_exists($y, $seasonalData['peralihan1']) ? $seasonalData['peralihan1'][$y] : null,
                array_key_exists($y, $seasonalData['timur']) ? $seasonalData['timur'][$y] : null,
                array_key_exists($y, $seasonalData['peralihan2']) ? $seasonalData['peralihan2'][$y] : null,
            ];
        }

        // Struktur per musim: untuk grafik komparasi 4 seri
        $bySeason = [
            'years' => $allYears,
            'series' => [
                'barat' => [
                    'key'   => 'barat',
                    'label' => 'Musim Barat',
                    'file'  => $resolvedFiles['barat'] ?? 'BARAT KROLOFIL A.csv',
                    'color' => '#24364B', // Deep Navy Blue
                    'data'  => array_map(fn($y) => $seasonalData['barat'][$y] ?? null, $allYears),
                ],
                'peralihan1' => [
                    'key'   => 'peralihan1',
                    'label' => 'Musim Peralihan I',
                    'file'  => $resolvedFiles['peralihan1'] ?? 'CHL PERALIHAN 1.csv',
                    'color' => '#5B7A9C', // Cashmere Slate
                    'data'  => array_map(fn($y) => $seasonalData['peralihan1'][$y] ?? null, $allYears),
                ],
                'timur' => [
                    'key'   => 'timur',
                    'label' => 'Musim Timur',
                    'file'  => $resolvedFiles['timur'] ?? 'CHL RATA RATA MUSIM TIMUR.csv',
                    'color' => '#73343A', // Merlot
                    'data'  => array_map(fn($y) => $seasonalData['timur'][$y] ?? null, $allYears),
                ],
                'peralihan2' => [
                    'key'   => 'peralihan2',
                    'label' => 'Musim Peralihan II',
                    'file'  => $resolvedFiles['peralihan2'] ?? 'RATA RATA MUSIM PERALIHAN 2.csv',
                    'color' => '#8E9FA3', // Cashmere Soft Slate
                    'data'  => array_map(fn($y) => $seasonalData['peralihan2'][$y] ?? null, $allYears),
                ],
            ]
        ];

        return [
            'byYear'   => $byYear,
            'bySeason' => $bySeason,
            'years'    => $allYears,
            'files'    => $resolvedFiles,
        ];
    }

    /**
     * Membaca satu file CSV dan mengekstrak pasangan [tahun => nilai_rata_rata].
     * Menggunakan baris ringkasan rata-rata musim yang sudah dihitung di bagian bawah file CSV.
     */
    protected function parseSeasonalCsv(string $path, int $precision = 2): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return [];
        }

        $rows = [];
        while (($data = fgetcsv($handle, 10000, ',')) !== false) {
            $rows[] = $data;
        }
        fclose($handle);

        if (empty($rows)) {
            return [];
        }

        // Cari baris yang mengandung 'RATA RATA' atau 'RATA2' atau 'MUSIM' atau 'NUSIM' dari baris terbawah
        $avgRowIndex = null;
        for ($i = count($rows) - 1; $i >= 0; $i--) {
            foreach ($rows[$i] as $cell) {
                if (
                    stripos($cell, 'RATA RATA') !== false ||
                    stripos($cell, 'RATA2') !== false ||
                    stripos($cell, 'MUSIM') !== false ||
                    stripos($cell, 'NUSIM') !== false
                ) {
                    $avgRowIndex = $i;
                    break 2;
                }
            }
        }

        if ($avgRowIndex === null) {
            return [];
        }

        $row = $rows[$avgRowIndex];
        $colCount = count($row);
        $detectedYearValues = [];

        for ($col = 0; $col < $colCount; $col++) {
            $val = trim($row[$col]);
            if ($val === '') {
                continue;
            }

            if (is_numeric($val)) {
                $numVal = round((float) $val, $precision);

                // Cari label tahun di kolom-kolom sebelah kanannya (radius 1-3 sel)
                $year = null;
                for ($k = 1; $k <= 3; $k++) {
                    if (isset($row[$col + $k]) && preg_match('/(20\d\d)/', $row[$col + $k], $m)) {
                        $year = $m[1];
                        break;
                    }
                }

                $detectedYearValues[] = [
                    'col'  => $col,
                    'val'  => $numVal,
                    'year' => $year,
                ];
            } elseif (stripos($val, 'nan') !== false || stripos($val, 'null') !== false) {
                // Tangani nilai kosong/null/NaN seperti 'NAN DI BULAN SEP DAN OKT'
                $year = null;
                for ($k = 1; $k <= 3; $k++) {
                    if (isset($row[$col + $k]) && preg_match('/(20\d\d)/', $row[$col + $k], $m)) {
                        $year = $m[1];
                        break;
                    }
                }

                $detectedYearValues[] = [
                    'col'  => $col,
                    'val'  => null,
                    'year' => $year,
                ];
            }
        }

        // Normalisasi urutan tahun (menangani kemungkinan typo tahun seperti 2025 ganda pada kolom ke-6)
        $standardYears = ['2021', '2022', '2023', '2024', '2025', '2026'];
        $results = [];

        foreach ($detectedYearValues as $idx => $item) {
            $y = $item['year'];
            // Jika label tidak ditemukan atau terduplikasi dengan tahun sebelumnya, gunakan urutan standar
            if (!$y || (isset($results[$y]) && isset($standardYears[$idx]))) {
                $y = $standardYears[$idx] ?? (string)(2021 + $idx);
            }
            $results[(string) $y] = $item['val'];
        }

        return $results;
    }

    /**
     * Mengonversi arah derajat (0-360) ke 8 arah mata angin standar Indonesia.
     */
    public static function getCompassDirection(float $deg): string
    {
        $deg = fmod(fmod($deg, 360) + 360, 360);
        if ($deg >= 337.5 || $deg < 22.5) return 'Utara';
        if ($deg >= 22.5 && $deg < 67.5) return 'Timur Laut';
        if ($deg >= 67.5 && $deg < 112.5) return 'Timur';
        if ($deg >= 112.5 && $deg < 157.5) return 'Tenggara';
        if ($deg >= 157.5 && $deg < 202.5) return 'Selatan';
        if ($deg >= 202.5 && $deg < 247.5) return 'Barat Daya';
        if ($deg >= 247.5 && $deg < 292.5) return 'Barat';
        return 'Barat Laut';
    }

    /**
     * Membaca dan memproses file CSV dataset arus laut (data_arus_clean.csv) secara dinamis.
     * Mengelompokkan data berdasarkan tahun (2021-2026) dan 4 musim (DJF, MAM, JJA, SON).
     *
     * @return array{
     *     byYear: array<string, array<int, float|null>>,
     *     detailsByYear: array<string, array<int, array{season_code: string, season_name: string, speed: float, speed_knot: float, direction: int, direction_str: string, u: float, v: float, date: string}>>,
     *     bySeason: array{years: array<string>, series: array<string, array{key: string, label: string, file: string, color: string, data: array<float|null>}>},
     *     years: array<string>,
     *     file: string
     * }
     */
    public function getSeasonalCurrentData(): array
    {
        $candidates = [
            $this->baseDir . DIRECTORY_SEPARATOR . 'arus' . DIRECTORY_SEPARATOR . 'data_arus_clean.csv',
            $this->baseDir . DIRECTORY_SEPARATOR . 'data_arus_clean.csv',
        ];

        $filePath = null;
        foreach ($candidates as $c) {
            if (file_exists($c)) {
                $filePath = $c;
                break;
            }
        }

        $allYears = ['2021', '2022', '2023', '2024', '2025', '2026'];
        $byYear = [];
        $detailsByYear = [];
        $seasonalSeries = [
            'barat'      => [],
            'peralihan1' => [],
            'timur'      => [],
            'peralihan2' => [],
        ];

        foreach ($allYears as $y) {
            $byYear[$y] = [null, null, null, null];
            $detailsByYear[$y] = [];
        }

        if (!$filePath || !file_exists($filePath)) {
            return [
                'byYear'        => $byYear,
                'detailsByYear' => $detailsByYear,
                'bySeason'      => ['years' => $allYears, 'series' => []],
                'years'         => $allYears,
                'file'          => 'data_arus_clean.csv',
            ];
        }

        $seasonOrder = [
            'DJF' => ['idx' => 0, 'key' => 'barat',      'name' => 'Musim Barat'],
            'MAM' => ['idx' => 1, 'key' => 'peralihan1', 'name' => 'Musim Peralihan I'],
            'JJA' => ['idx' => 2, 'key' => 'timur',      'name' => 'Musim Timur'],
            'SON' => ['idx' => 3, 'key' => 'peralihan2', 'name' => 'Musim Peralihan II'],
        ];

        $rows = array_map('str_getcsv', file($filePath));
        if (!empty($rows)) {
            array_shift($rows); // Hapus baris header
        }

        foreach ($rows as $r) {
            if (count($r) < 6) continue;
            $tgl = trim($r[0]);
            $u = (float)$r[1];
            $v = (float)$r[2];
            $speed = (float)$r[3];
            $arah = (float)$r[4];
            $musimCode = trim($r[5]);

            if (!isset($seasonOrder[$musimCode])) continue;

            $dt = new \DateTime($tgl);
            $year = (int)$dt->format('Y');
            $month = (int)$dt->format('m');
            // DJF pada bulan Desember (tahun Y-1) masuk ke siklus musim pengamatan tahun Y
            if ($musimCode === 'DJF' && $month === 12) {
                $year += 1;
            }
            $yearStr = (string)$year;

            if (!in_array($yearStr, $allYears, true)) continue;

            $sInfo = $seasonOrder[$musimCode];
            $dirLabel = self::getCompassDirection($arah);

            $byYear[$yearStr][$sInfo['idx']] = $speed;
            $seasonalSeries[$sInfo['key']][$yearStr] = $speed;

            $detailsByYear[$yearStr][$sInfo['idx']] = [
                'season_code'  => $musimCode,
                'season_name'  => $sInfo['name'],
                'speed'        => $speed,
                'speed_knot'   => round($speed * 1.94384, 2),
                'direction'    => (int)round($arah),
                'direction_str'=> $dirLabel,
                'u'            => $u,
                'v'            => $v,
                'date'         => $tgl,
            ];
        }

        foreach ($allYears as $y) {
            ksort($detailsByYear[$y]);
            $detailsByYear[$y] = array_values($detailsByYear[$y]);
        }

        $fileName = basename($filePath);
        $bySeason = [
            'years' => $allYears,
            'series' => [
                'barat' => [
                    'key'   => 'barat',
                    'label' => 'Musim Barat (DJF)',
                    'file'  => $fileName,
                    'color' => '#24364B', // Deep Navy Blue
                    'data'  => array_map(fn($y) => $seasonalSeries['barat'][$y] ?? null, $allYears),
                ],
                'peralihan1' => [
                    'key'   => 'peralihan1',
                    'label' => 'Musim Peralihan I (MAM)',
                    'file'  => $fileName,
                    'color' => '#5B7A9C', // Cashmere Slate
                    'data'  => array_map(fn($y) => $seasonalSeries['peralihan1'][$y] ?? null, $allYears),
                ],
                'timur' => [
                    'key'   => 'timur',
                    'label' => 'Musim Timur (JJA)',
                    'file'  => $fileName,
                    'color' => '#73343A', // Merlot
                    'data'  => array_map(fn($y) => $seasonalSeries['timur'][$y] ?? null, $allYears),
                ],
                'peralihan2' => [
                    'key'   => 'peralihan2',
                    'label' => 'Musim Peralihan II (SON)',
                    'file'  => $fileName,
                    'color' => '#8E9FA3', // Cashmere Soft Slate
                    'data'  => array_map(fn($y) => $seasonalSeries['peralihan2'][$y] ?? null, $allYears),
                ],
            ]
        ];

        return [
            'byYear'        => $byYear,
            'detailsByYear' => $detailsByYear,
            'bySeason'      => $bySeason,
            'years'         => $allYears,
            'file'          => $fileName,
        ];
    }

    /**
     * Membaca dan memproses file CSV dataset TSS (rata-rata nilai tss.csv) secara dinamis dari folder:
     * storage/app/private/datasets/tss/
     * Mengelompokkan data berdasarkan tahun (2021-2026) dan 4 musim.
     *
     * @return array{
     *     byYear: array<string, array<int, float|null>>,
     *     bySeason: array{years: array<string>, series: array<string, array{key: string, label: string, file: string, color: string, data: array<float|null>}>},
     *     years: array<string>,
     *     file: string,
     *     annualAverages: array<string, float|null>,
     *     overallStats: array{latest: float|null, latestLabel: string, avg: float|null, max: float|null, maxLabel: string, min: float|null, minLabel: string, count: int, missingCount: int}
     * }
     */
    public function getSeasonalTssData(): array
    {
        $candidates = [
            $this->tssDir . DIRECTORY_SEPARATOR . 'rata-rata nilai tss.csv',
            $this->baseDir . DIRECTORY_SEPARATOR . 'rata-rata nilai tss.csv',
            $this->baseDir . DIRECTORY_SEPARATOR . 'tss' . DIRECTORY_SEPARATOR . 'rata-rata nilai tss.csv',
        ];

        $filePath = null;
        foreach ($candidates as $c) {
            if (file_exists($c)) {
                $filePath = $c;
                break;
            }
        }

        $allYears = ['2021', '2022', '2023', '2024', '2025', '2026'];
        $byYear = [];
        $seasonalSeries = [
            'barat'      => [],
            'peralihan1' => [],
            'timur'      => [],
            'peralihan2' => [],
        ];

        foreach ($allYears as $y) {
            $byYear[$y] = [null, null, null, null];
        }

        if (!$filePath || !file_exists($filePath)) {
            return [
                'byYear'         => $byYear,
                'bySeason'       => ['years' => $allYears, 'series' => []],
                'years'          => $allYears,
                'file'           => 'rata-rata nilai tss.csv',
                'annualAverages' => array_fill_keys($allYears, null),
                'overallStats'   => [
                    'latest'       => null,
                    'latestLabel'  => '-',
                    'avg'          => null,
                    'max'          => null,
                    'maxLabel'     => '-',
                    'min'          => null,
                    'minLabel'     => '-',
                    'count'        => 0,
                    'missingCount' => 24
                ],
            ];
        }

        $rows = array_map('str_getcsv', file($filePath));
        if (!empty($rows)) {
            array_shift($rows); // Hapus baris header
        }

        $allValidValues = [];
        $latestVal = null;
        $latestLabel = '-';

        foreach ($rows as $r) {
            if (count($r) < 2) continue;
            $label = trim($r[0]);
            $valStr = trim($r[1]);
            $num = ($valStr === '' || !is_numeric($valStr)) ? null : (float)$valStr;

            preg_match('/^(\d{4})/', $label, $m);
            $year = $m[1] ?? null;
            if (!$year || !in_array($year, $allYears, true)) continue;

            $seasonKey = null;
            $seasonIdx = null;
            if (stripos($label, 'Barat') !== false) {
                $seasonKey = 'barat'; $seasonIdx = 0;
            } elseif (stripos($label, 'Peralihan1') !== false || stripos($label, 'Peralihan 1') !== false || stripos($label, 'PeralihanI') !== false) {
                $seasonKey = 'peralihan1'; $seasonIdx = 1;
            } elseif (stripos($label, 'Timur') !== false) {
                $seasonKey = 'timur'; $seasonIdx = 2;
            } elseif (stripos($label, 'Peralihan2') !== false || stripos($label, 'Peralihan 2') !== false || stripos($label, 'PeralihanII') !== false) {
                $seasonKey = 'peralihan2'; $seasonIdx = 3;
            }

            if ($seasonIdx !== null) {
                $byYear[$year][$seasonIdx] = $num;
                $seasonalSeries[$seasonKey][$year] = $num;

                if ($num !== null) {
                    $allValidValues[] = ['val' => $num, 'label' => $label, 'year' => $year];
                    $latestVal = $num;
                    $latestLabel = $label;
                }
            }
        }

        $annualAverages = [];
        foreach ($allYears as $y) {
            $validInYear = array_filter($byYear[$y], fn($v) => $v !== null);
            if (count($validInYear) > 0) {
                $annualAverages[$y] = round(array_sum($validInYear) / count($validInYear), 3);
            } else {
                $annualAverages[$y] = null;
            }
        }

        $valsOnly = array_column($allValidValues, 'val');
        $overallMin = !empty($valsOnly) ? min($valsOnly) : null;
        $overallMax = !empty($valsOnly) ? max($valsOnly) : null;
        $overallAvg = !empty($valsOnly) ? round(array_sum($valsOnly) / count($valsOnly), 3) : null;

        $minItem = null;
        $maxItem = null;
        foreach ($allValidValues as $item) {
            if ($item['val'] === $overallMin && $minItem === null) $minItem = $item;
            if ($item['val'] === $overallMax && $maxItem === null) $maxItem = $item;
        }

        $fileName = basename($filePath);
        $bySeason = [
            'years' => $allYears,
            'series' => [
                'barat' => [
                    'key'   => 'barat',
                    'label' => 'Musim Barat',
                    'file'  => $fileName,
                    'color' => '#24364B', // Deep Navy Blue
                    'data'  => array_map(fn($y) => $seasonalSeries['barat'][$y] ?? null, $allYears),
                ],
                'peralihan1' => [
                    'key'   => 'peralihan1',
                    'label' => 'Musim Peralihan I',
                    'file'  => $fileName,
                    'color' => '#5B7A9C', // Cashmere Slate
                    'data'  => array_map(fn($y) => $seasonalSeries['peralihan1'][$y] ?? null, $allYears),
                ],
                'timur' => [
                    'key'   => 'timur',
                    'label' => 'Musim Timur',
                    'file'  => $fileName,
                    'color' => '#73343A', // Merlot
                    'data'  => array_map(fn($y) => $seasonalSeries['timur'][$y] ?? null, $allYears),
                ],
                'peralihan2' => [
                    'key'   => 'peralihan2',
                    'label' => 'Musim Peralihan II',
                    'file'  => $fileName,
                    'color' => '#8E9FA3', // Cashmere Soft Slate
                    'data'  => array_map(fn($y) => $seasonalSeries['peralihan2'][$y] ?? null, $allYears),
                ],
            ]
        ];

        return [
            'byYear'         => $byYear,
            'bySeason'       => $bySeason,
            'years'          => $allYears,
            'file'           => $fileName,
            'annualAverages' => $annualAverages,
            'overallStats'   => [
                'latest'       => $latestVal,
                'latestLabel'  => $latestLabel,
                'avg'          => $overallAvg,
                'max'          => $overallMax,
                'maxLabel'     => $maxItem ? $maxItem['label'] : '-',
                'min'          => $overallMin,
                'minLabel'     => $minItem ? $minItem['label'] : '-',
                'count'        => count($allValidValues),
                'missingCount' => 24 - count($allValidValues),
            ]
        ];
    }

    /**
     * Mengklasifikasikan nilai parameter oseanografi berdasarkan baku mutu optimal:
     * - Klorofil-a : 0.1 – 5 mg/m³
     * - Suhu       : 24 – 31 °C
     * - TSS        : 25 – 50 mg/L
     * - Arus       : 0.1 – 0.5 m/s
     *
     * @return array{status: string, badge: string, color: string}
     */
    public static function classifyOceanParam(?float $val, string $param): array
    {
        if ($val === null) {
            return [
                'status' => 'Tidak Tersedia',
                'badge'  => 'badge-unavailable',
                'color'  => 'gray'
            ];
        }

        $ranges = [
            'temp'        => ['min' => 24.0, 'max' => 31.0, 'unit' => '°C'],
            'chlorophyll' => ['min' => 0.1,  'max' => 5.0,  'unit' => 'mg/m³'],
            'tss'         => ['min' => 0.0, 'max' => 50.0, 'unit' => 'mg/L'],
            'current'     => ['min' => 0.1,  'max' => 0.5,  'unit' => 'm/s'],
        ];

        if (!isset($ranges[$param])) {
            return ['status' => 'Normal', 'badge' => 'badge-optimal', 'color' => 'green'];
        }

        $r = $ranges[$param];
        if ($val < $r['min']) {
            return [
                'status' => 'Tidak Optimal',
                'badge'  => 'badge-suboptimal',
                'color'  => 'merlot'
            ];
        }
        if ($val > $r['max']) {
            return [
                'status' => 'Di Atas Optimal',
                'badge'  => 'badge-above-optimal',
                'color'  => 'amber'
            ];
        }
        return [
            'status' => 'Optimal',
            'badge'  => 'badge-optimal',
            'color'  => 'emerald'
        ];
    }

    /**
     * Menilai kesesuaian habitat ikan tenggiri:
     * - Optimal          : Seluruh 4 parameter memenuhi rentang optimal
     * - Belum Optimal    : Seluruh data tersedia namun belum semua parameter memenuhi rentang
     * - Belum Dapat Dinilai : Terdapat parameter yang datanya tidak tersedia
     *
     * @return array{score: string, badge: string, desc: string}
     */
    public static function evaluateHabitatSuitability(?float $temp, ?float $chl, ?float $tss, ?float $cur): array
    {
        if ($temp === null || $chl === null || $tss === null || $cur === null) {
            return [
                'score'  => 'Belum Dapat Dinilai',
                'badge'  => 'badge-score-pending',
                'desc'   => 'Terdapat parameter oseanografi yang datanya tidak tersedia'
            ];
        }

        $cTemp = self::classifyOceanParam($temp, 'temp')['status'];
        $cChl  = self::classifyOceanParam($chl, 'chlorophyll')['status'];
        $cTss  = self::classifyOceanParam($tss, 'tss')['status'];
        $cCur  = self::classifyOceanParam($cur, 'current')['status'];

        if ($cTemp === 'Optimal' && $cChl === 'Optimal' && $cTss === 'Optimal' && $cCur === 'Optimal') {
            return [
                'score'  => 'Optimal',
                'badge'  => 'badge-score-optimal',
                'desc'   => 'Seluruh 4 parameter oseanografi memenuhi rentang baku mutu optimal'
            ];
        }

        return [
            'score'  => 'Belum Optimal',
            'badge'  => 'badge-score-suboptimal',
            'desc'   => 'Seluruh data tersedia namun belum semua parameter memenuhi rentang optimal'
        ];
    }

    /**
     * Menggabungkan 4 dataset oseanografi menjadi rekapitulasi komprehensif 24 periode (2021-2026 x 4 musim).
     *
     * @return array<int, array{
     *     year: string,
     *     season_idx: int,
     *     season_name: string,
     *     season_code: string,
     *     period_label: string,
     *     temp: float|null,
     *     temp_eval: array{status: string, badge: string, color: string},
     *     chlorophyll: float|null,
     *     chla_eval: array{status: string, badge: string, color: string},
     *     tss: float|null,
     *     tss_eval: array{status: string, badge: string, color: string},
     *     current: float|null,
     *     current_eval: array{status: string, badge: string, color: string},
     *     habitatScore: string,
     *     habitatBadge: string,
     *     habitatDesc: string
     * }>
     */
    public function getComprehensiveSeasonalRecap(): array
    {
        $spl = $this->getSeasonalSplData()['byYear'];
        $chla = $this->getSeasonalChlaData()['byYear'];
        $current = $this->getSeasonalCurrentData()['byYear'];
        $tss = $this->getSeasonalTssData()['byYear'];

        $seasonNames = [
            0 => ['name' => 'Musim Barat',         'code' => 'DJF'],
            1 => ['name' => 'Musim Peralihan I',    'code' => 'MAM'],
            2 => ['name' => 'Musim Timur',          'code' => 'JJA'],
            3 => ['name' => 'Musim Peralihan II',   'code' => 'SON'],
        ];

        $recap = [];
        $years = ['2026', '2025', '2024', '2023', '2022', '2021'];

        foreach ($years as $y) {
            foreach ([3, 2, 1, 0] as $sIdx) {
                $sInfo = $seasonNames[$sIdx];
                $t = $spl[$y][$sIdx] ?? null;
                $c = $chla[$y][$sIdx] ?? null;
                $ts = $tss[$y][$sIdx] ?? null;
                $cu = $current[$y][$sIdx] ?? null;

                $eval = self::evaluateHabitatSuitability($t, $c, $ts, $cu);

                $recap[] = [
                    'year'         => $y,
                    'season_idx'   => $sIdx,
                    'season_name'  => $sInfo['name'],
                    'season_code'  => $sInfo['code'],
                    'period_label' => $y . ' ' . $sInfo['name'],
                    'temp'         => $t,
                    'temp_eval'    => self::classifyOceanParam($t, 'temp'),
                    'chlorophyll'  => $c,
                    'chla_eval'    => self::classifyOceanParam($c, 'chlorophyll'),
                    'tss'          => $ts,
                    'tss_eval'     => self::classifyOceanParam($ts, 'tss'),
                    'current'      => $cu,
                    'current_eval' => self::classifyOceanParam($cu, 'current'),
                    'habitatScore' => $eval['score'],
                    'habitatBadge' => $eval['badge'],
                    'habitatDesc'  => $eval['desc'],
                ];
            }
        }

        return $recap;
    }
}
