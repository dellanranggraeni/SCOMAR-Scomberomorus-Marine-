<?php

namespace App\Services;

/**
 * Service untuk membaca data hasil tangkapan ikan tenggiri dari file CSV.
 *
 * File CSV: storage/app/private/datasets/hasil tangkapan/data hasil tangkapan.csv
 * Kolom: Tahun, Musim, Produksi tenggiri (kg), Trip, CPUE (kg/trip),
 *        Harga rata-rata tahunan (Rp/kg), Estimasi nilai (Rp juta)
 *
 * Ketentuan:
 * 1. Membaca seluruh data dari tahun 2021 sampai tahun terakhir (2026).
 * 2. Memisahkan data 4 musim (Barat, Peralihan I, Timur, Peralihan II) dari baris "Total tahun".
 * 3. Menghindari penghitungan ganda (total tahun tidak dimasukkan ke dalam akumulasi musiman).
 * 4. Nilai NAN / kosong diperlakukan sebagai null / "Null".
 * 5. Angka dalam format teks dengan koma ribuan (e.g. "13,486") dikonversi ke numerik.
 * 6. Menyediakan nilai dalam format kg dan ton (1 ton = 1.000 kg).
 */
class CatchCsvService
{
    protected string $csvPath;

    // Mapping normalisasi musim
    protected array $seasonMap = [
        'barat'        => ['key' => 'barat',      'code' => 'DJF', 'name' => 'Musim Barat',        'period' => 'Desember - Februari',   'idx' => 0],
        'peralihan i'  => ['key' => 'peralihan1',  'code' => 'MAM', 'name' => 'Musim Peralihan I',  'period' => 'Maret - Mei',           'idx' => 1],
        'peralihan 1'  => ['key' => 'peralihan1',  'code' => 'MAM', 'name' => 'Musim Peralihan I',  'period' => 'Maret - Mei',           'idx' => 1],
        'timur'        => ['key' => 'timur',       'code' => 'JJA', 'name' => 'Musim Timur',        'period' => 'Juni - Agustus',        'idx' => 2],
        'peralihan ii' => ['key' => 'peralihan2',  'code' => 'SON', 'name' => 'Musim Peralihan II', 'period' => 'September - November', 'idx' => 3],
        'peralihan 2'  => ['key' => 'peralihan2',  'code' => 'SON', 'name' => 'Musim Peralihan II', 'period' => 'September - November', 'idx' => 3],
    ];

    public function __construct(?string $csvPath = null)
    {
        $this->csvPath = $csvPath
            ?: storage_path('app/private/datasets/hasil tangkapan/data hasil tangkapan.csv');
    }

    /**
     * Parse nilai numerik dari string CSV secara aman.
     */
    private function parseNum(?string $raw): ?float
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $clean = strtoupper(trim($raw));
        if ($clean === 'NAN' || $clean === 'N/A' || $clean === '-' || $clean === 'NULL') {
            return null;
        }
        // Hapus tanda kutip, spasi, dan koma ribuan
        $clean = str_replace(['"', "'", ' ', ','], ['', '', '', ''], $clean);
        return is_numeric($clean) ? (float) $clean : null;
    }

    /**
     * Dapatkan metadata musim dari string nama musim di CSV.
     */
    private function getSeasonMeta(string $raw): ?array
    {
        $lower = strtolower(trim($raw));
        if (isset($this->seasonMap[$lower])) {
            return $this->seasonMap[$lower];
        }
        if (str_contains($lower, 'total')) {
            return null; // Baris rekap tahunan
        }
        if (str_contains($lower, 'barat')) {
            return $this->seasonMap['barat'];
        }
        if (str_contains($lower, 'peralihan ii') || str_contains($lower, 'peralihan 2')) {
            return $this->seasonMap['peralihan ii'];
        }
        if (str_contains($lower, 'peralihan i') || str_contains($lower, 'peralihan 1')) {
            return $this->seasonMap['peralihan i'];
        }
        if (str_contains($lower, 'timur')) {
            return $this->seasonMap['timur'];
        }
        return null;
    }

    /**
     * Baca data hasil tangkapan CSV secara lengkap dan terstruktur.
     */
    public function getCatchData(): array
    {
        if (!file_exists($this->csvPath)) {
            return $this->emptyResult();
        }

        $handle = fopen($this->csvPath, 'r');
        if (!$handle) {
            return $this->emptyResult();
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return $this->emptyResult();
        }

        // Mapping index kolom secara presisi (hindari false match 'tahunan')
        $colMap = [
            'tahun'    => 0,
            'musim'    => 1,
            'produksi' => 2,
            'trip'     => 3,
            'cpue'     => 4,
            'harga'    => 5,
            'nilai'    => 6,
        ];

        foreach ($header as $i => $col) {
            $cleanCol = preg_replace('/^\xEF\xBB\xBF/', '', trim($col));
            $key = strtolower($cleanCol);
            if ($key === 'tahun') {
                $colMap['tahun'] = $i;
            } elseif (str_contains($key, 'tahun') && !str_contains($key, 'tahunan') && !str_contains($key, 'rata')) {
                $colMap['tahun'] = $i;
            }
            if (str_contains($key, 'musim')) {
                $colMap['musim'] = $i;
            }
            if (str_contains($key, 'produksi') || str_contains($key, 'volume')) {
                $colMap['produksi'] = $i;
            }
            if (str_contains($key, 'trip') && !str_contains($key, 'cpue')) {
                $colMap['trip'] = $i;
            }
            if (str_contains($key, 'cpue')) {
                $colMap['cpue'] = $i;
            }
            if (str_contains($key, 'harga')) {
                $colMap['harga'] = $i;
            }
            if (str_contains($key, 'nilai') || str_contains($key, 'estimasi')) {
                $colMap['nilai'] = $i;
            }
        }

        $seasonalByYear = []; // [year][key] = data
        $annualTotals   = []; // [year] = total data
        $seasonalFlat   = []; // 24 baris terurut
        $yearsList      = [];

        while (($row = fgetcsv($handle)) !== false) {
            // Hentikan jika baris kosong
            if (!isset($row[0]) || trim($row[0]) === '') {
                break;
            }

            $tahunRaw = trim($row[$colMap['tahun']] ?? '');
            if (!is_numeric($tahunRaw) || (int) $tahunRaw < 2000) {
                break; // Melewati tabel bulanan jika ada di bagian bawah
            }

            $tahun = (string) (int) $tahunRaw;
            if (!in_array($tahun, $yearsList, true)) {
                $yearsList[] = $tahun;
            }

            $musimRaw = trim($row[$colMap['musim']] ?? '');
            $isTotal  = str_contains(strtolower($musimRaw), 'total');

            $prodKg   = $this->parseNum($row[$colMap['produksi']] ?? null);
            $prodTon  = $prodKg !== null ? round($prodKg / 1000, 3) : null;
            $trip     = $this->parseNum($row[$colMap['trip']] ?? null);
            $tripInt  = $trip !== null ? (int) round($trip) : null;
            $cpue     = $this->parseNum($row[$colMap['cpue']] ?? null);
            $harga    = $this->parseNum($row[$colMap['harga']] ?? null);
            $nilai    = $this->parseNum($row[$colMap['nilai']] ?? null);

            if ($isTotal) {
                // Rekapitulasi Tahunan resmi dari CSV
                $annualTotals[$tahun] = [
                    'year'         => $tahun,
                    'label'        => "Total Tahun {$tahun}",
                    'produksi_kg'  => $prodKg,
                    'produksi_ton' => $prodTon,
                    'trip'         => $tripInt,
                    'cpue'         => $cpue,
                    'harga'        => $harga,
                    'nilai_juta'   => $nilai,
                ];
            } else {
                // Data Musiman
                $meta = $this->getSeasonMeta($musimRaw);
                $key = $meta ? $meta['key'] : strtolower(trim($musimRaw));

                $item = [
                    'year'         => $tahun,
                    'season_key'   => $key,
                    'season_name'  => $meta ? $meta['name'] : $musimRaw,
                    'season_code'  => $meta ? $meta['code'] : '',
                    'period'       => $meta ? $meta['period'] : '',
                    'season_idx'   => $meta ? $meta['idx'] : 0,
                    'produksi_kg'  => $prodKg,
                    'produksi_ton' => $prodTon,
                    'trip'         => $tripInt,
                    'cpue'         => $cpue,
                    'harga'        => $harga,
                    'nilai_juta'   => $nilai,
                    'status'       => $prodKg !== null ? 'Tersedia' : 'Null',
                ];

                if (!isset($seasonalByYear[$tahun])) {
                    $seasonalByYear[$tahun] = [];
                }
                $seasonalByYear[$tahun][$key] = $item;
                $seasonalFlat[] = $item;
            }
        }

        fclose($handle);

        sort($yearsList);

        // Susun dataset 4 musim per-tahun dalam format array index 0-3 (Barat, Peralihan I, Timur, Peralihan II)
        // Mendukung satuan kg dan ton
        $seasonKeys = ['barat', 'peralihan1', 'timur', 'peralihan2'];
        $byYear = [];

        foreach ($yearsList as $yr) {
            $byYear[$yr] = [
                'produksi_kg'  => [],
                'produksi_ton' => [],
                'trip'         => [],
                'cpue'         => [],
            ];

            foreach ($seasonKeys as $sk) {
                $entry = $seasonalByYear[$yr][$sk] ?? null;
                $byYear[$yr]['produksi_kg'][]  = $entry ? $entry['produksi_kg'] : null;
                $byYear[$yr]['produksi_ton'][] = $entry ? $entry['produksi_ton'] : null;
                $byYear[$yr]['trip'][]         = $entry ? $entry['trip'] : null;
                $byYear[$yr]['cpue'][]         = $entry ? $entry['cpue'] : null;
            }
        }

        // Susun struktur grafik perbandingan antartahun (2021-2026)
        // 4 seri musim untuk Produksi, Trip, dan CPUE
        $comparison = [
            'years'  => $yearsList,
            'series' => [
                'barat'      => ['label' => 'Musim Barat',        'color' => '#24364B', 'produksi_ton' => [], 'produksi_kg' => [], 'trip' => [], 'cpue' => []],
                'peralihan1' => ['label' => 'Musim Peralihan I',  'color' => '#5B7A9C', 'produksi_ton' => [], 'produksi_kg' => [], 'trip' => [], 'cpue' => []],
                'timur'      => ['label' => 'Musim Timur',        'color' => '#73343A', 'produksi_ton' => [], 'produksi_kg' => [], 'trip' => [], 'cpue' => []],
                'peralihan2' => ['label' => 'Musim Peralihan II', 'color' => '#8E9FA3', 'produksi_ton' => [], 'produksi_kg' => [], 'trip' => [], 'cpue' => []],
            ],
            // Seri akumulasi tahunan (2021-2026)
            'annual' => [
                'produksi_ton' => [],
                'produksi_kg'  => [],
                'trip'         => [],
                'cpue'         => [],
            ],
        ];

        foreach ($yearsList as $yr) {
            foreach ($seasonKeys as $sk) {
                $entry = $seasonalByYear[$yr][$sk] ?? null;
                $comparison['series'][$sk]['produksi_ton'][] = $entry ? $entry['produksi_ton'] : null;
                $comparison['series'][$sk]['produksi_kg'][]  = $entry ? $entry['produksi_kg'] : null;
                $comparison['series'][$sk]['trip'][]         = $entry ? $entry['trip'] : null;
                $comparison['series'][$sk]['cpue'][]         = $entry ? $entry['cpue'] : null;
            }

            $ann = $annualTotals[$yr] ?? null;
            $comparison['annual']['produksi_ton'][] = $ann ? $ann['produksi_ton'] : null;
            $comparison['annual']['produksi_kg'][]  = $ann ? $ann['produksi_kg'] : null;
            $comparison['annual']['trip'][]         = $ann ? $ann['trip'] : null;
            $comparison['annual']['cpue'][]         = $ann ? $ann['cpue'] : null;
        }

        // Statistik All-Time (dari data musiman murni, tidak tercampur total tahun)
        $validSeasonal = array_filter($seasonalFlat, fn($r) => $r['produksi_kg'] !== null);
        $peakSeasonal = null;
        $lowestSeasonal = null;

        foreach ($validSeasonal as $r) {
            if ($peakSeasonal === null || $r['produksi_kg'] > $peakSeasonal['produksi_kg']) {
                $peakSeasonal = $r;
            }
            if ($lowestSeasonal === null || $r['produksi_kg'] < $lowestSeasonal['produksi_kg']) {
                $lowestSeasonal = $r;
            }
        }

        $allCpue = array_filter(array_column($validSeasonal, 'cpue'), fn($v) => $v !== null);
        $avgCpue = !empty($allCpue) ? round(array_sum($allCpue) / count($allCpue), 2) : 0;

        $overallStats = [
            'peak' => $peakSeasonal ? [
                'year'         => $peakSeasonal['year'],
                'season'       => $peakSeasonal['season_name'],
                'produksi_kg'  => $peakSeasonal['produksi_kg'],
                'produksi_ton' => $peakSeasonal['produksi_ton'],
                'trip'         => $peakSeasonal['trip'],
                'cpue'         => $peakSeasonal['cpue'],
            ] : null,
            'lowest' => $lowestSeasonal ? [
                'year'         => $lowestSeasonal['year'],
                'season'       => $lowestSeasonal['season_name'],
                'produksi_kg'  => $lowestSeasonal['produksi_kg'],
                'produksi_ton' => $lowestSeasonal['produksi_ton'],
                'trip'         => $lowestSeasonal['trip'],
                'cpue'         => $lowestSeasonal['cpue'],
            ] : null,
            'avgCpue' => $avgCpue,
        ];

        return [
            'years'          => $yearsList,
            'seasonalByYear' => $seasonalByYear,
            'byYear'         => $byYear,
            'annualTotals'   => $annualTotals,
            'comparison'     => $comparison,
            'seasonalFlat'   => $seasonalFlat,
            'overallStats'   => $overallStats,
        ];
    }

    private function emptyResult(): array
    {
        return [
            'years'          => [],
            'seasonalByYear' => [],
            'byYear'         => [],
            'annualTotals'   => [],
            'comparison'     => ['years' => [], 'series' => [], 'annual' => []],
            'seasonalFlat'   => [],
            'overallStats'   => ['peak' => null, 'lowest' => null, 'avgCpue' => 0],
        ];
    }
}
