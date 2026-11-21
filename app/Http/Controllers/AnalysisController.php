<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OceanographicCsvService;
use App\Services\CatchCsvService;

class AnalysisController extends Controller
{
    protected OceanographicCsvService $oceanSvc;
    protected CatchCsvService $catchSvc;

    public function __construct(OceanographicCsvService $oceanSvc, CatchCsvService $catchSvc)
    {
        $this->oceanSvc = $oceanSvc;
        $this->catchSvc = $catchSvc;
    }

    // ─── Halaman Utama ────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $filterYear   = $request->input('year', 'all');
        $filterSeason = $request->input('season', 'all');

        $analysisData = $this->buildAnalysis($filterYear, $filterSeason);

        return view('charts.analysis', array_merge($analysisData, [
            'filterYear'     => $filterYear,
            'filterSeason'   => $filterSeason,
            'availableYears' => ['2021','2022','2023','2024','2025','2026'],
        ]));
    }

    // ─── JSON endpoint untuk filter AJAX ─────────────────────────────────────
    public function getData(Request $request)
    {
        $filterYear   = $request->input('year', 'all');
        $filterSeason = $request->input('season', 'all');

        return response()->json($this->buildAnalysis($filterYear, $filterSeason));
    }

    // ─── Bangun data analisis ─────────────────────────────────────────────────
    protected function buildAnalysis(string $filterYear, string $filterSeason): array
    {
        $allYears   = ['2021','2022','2023','2024','2025','2026'];
        $seasonIdx  = ['barat' => 0, 'peralihan1' => 1, 'timur' => 2, 'peralihan2' => 3];
        $seasonKeys = ['barat', 'peralihan1', 'timur', 'peralihan2'];
        $seasonLabels = [
            'barat'      => 'Musim Barat',
            'peralihan1' => 'Peralihan I',
            'timur'      => 'Musim Timur',
            'peralihan2' => 'Peralihan II',
        ];

        // 1. Ambil data oseanografi — SPL dari service (membaca dari folder suhu)
        $splData    = $this->oceanSvc->getSeasonalSplData();
        $splByYear  = $this->normalizeByYear($splData['byYear'], $allYears);

        // Klorofil-a dari service
        $chlaData   = $this->oceanSvc->getSeasonalChlaData();
        $chlaByYear = $this->normalizeByYear($chlaData['byYear'], $allYears);

        // TSS dari service
        $tssData    = $this->oceanSvc->getSeasonalTssData();
        $tssByYear  = $this->normalizeByYear($tssData['byYear'], $allYears);

        // Arus dari service
        $arusData   = $this->oceanSvc->getSeasonalCurrentData();
        $arusByYear = $this->normalizeByYear($arusData['byYear'], $allYears);

        // 2. Ambil data CPUE dari CSV
        $catchData  = $this->catchSvc->getCatchData();
        $seasonal   = $catchData['seasonalFlat'] ?? [];

        // Bangun lookup CPUE [year][season_key] => cpue|null
        $cpueLookup = [];
        foreach ($seasonal as $row) {
            $y  = (string)($row['year'] ?? '');
            $sk = $row['season_key'] ?? '';
            $cpue = (isset($row['cpue']) && $row['cpue'] !== '' && $row['cpue'] !== null)
                ? (float)$row['cpue'] : null;
            if ($y && $sk) {
                $cpueLookup[$y][$sk] = $cpue;
            }
        }

        // 3. Definisi parameter
        $params = [
            'spl'  => ['label' => 'Suhu Permukaan Laut (SPL)', 'unit' => '°C',    'byYear' => $splByYear],
            'chla' => ['label' => 'Konsentrasi Klorofil-a',    'unit' => 'mg/m³', 'byYear' => $chlaByYear],
            'tss'  => ['label' => 'Total Suspended Solids (TSS)', 'unit' => 'mg/L','byYear' => $tssByYear],
            'arus' => ['label' => 'Kecepatan Arus Laut',        'unit' => 'm/s',   'byYear' => $arusByYear],
        ];

        // 4. Bangun pasangan data & hitung statistik
        $paramAnalysis = [];
        foreach ($params as $pk => $pInfo) {
            $xArr   = [];
            $yArr   = [];
            $points = [];

            foreach ($allYears as $yr) {
                if ($filterYear !== 'all' && $yr !== $filterYear) continue;

                $oceanByYear = $pInfo['byYear'][$yr] ?? [null, null, null, null];

                foreach ($seasonKeys as $sk) {
                    if ($filterSeason !== 'all' && $sk !== $filterSeason) continue;

                    $idx  = $seasonIdx[$sk];
                    $pVal = $oceanByYear[$idx] ?? null;
                    $cpue = $cpueLookup[$yr][$sk] ?? null;

                    if ($pVal === null || $cpue === null) {
                        $points[] = [
                            'year'   => $yr,
                            'season' => $seasonLabels[$sk],
                            'x'      => $pVal !== null ? round((float)$pVal, 4) : null,
                            'y'      => $cpue !== null ? round((float)$cpue, 4) : null,
                            'valid'  => false,
                        ];
                        continue;
                    }

                    $xArr[] = (float)$pVal;
                    $yArr[] = (float)$cpue;
                    $points[] = [
                        'year'   => $yr,
                        'season' => $seasonLabels[$sk],
                        'x'      => round((float)$pVal, 4),
                        'y'      => round((float)$cpue, 4),
                        'valid'  => true,
                    ];
                }
            }

            $n = count($xArr);
            $stats = [
                'n'           => $n,
                'pearson'     => null,
                'spearman'    => null,
                'pvalue'      => null,
                'slope'       => null,
                'intercept'   => null,
                'r2'          => null,
                'direction'   => null,
                'strength'    => null,
                'significant' => null,
            ];

            if ($n >= 3) {
                $pearson  = $this->pearsonR($xArr, $yArr);
                $spearman = $this->spearmanRho($xArr, $yArr);
                $pvalue   = ($pearson !== null) ? $this->pValue($pearson, $n) : null;
                $reg      = $this->linearRegression($xArr, $yArr);

                $stats['pearson']     = $pearson   !== null ? round($pearson, 4)   : null;
                $stats['spearman']    = $spearman  !== null ? round($spearman, 4)  : null;
                $stats['pvalue']      = $pvalue    !== null ? round($pvalue, 4)    : null;
                $stats['slope']       = $reg['slope']       !== null ? round($reg['slope'], 4)      : null;
                $stats['intercept']   = $reg['intercept']   !== null ? round($reg['intercept'], 4)  : null;
                $stats['r2']          = $reg['r2']          !== null ? round($reg['r2'], 4)          : null;

                if ($pearson !== null) {
                    $stats['direction'] = $pearson >= 0 ? 'Positif' : 'Negatif';
                    $abs = abs($pearson);
                    $stats['strength']  = $abs >= 0.7 ? 'Kuat' : ($abs >= 0.4 ? 'Sedang' : 'Lemah');
                }
                $stats['significant'] = ($pvalue !== null && $pvalue < 0.05);
            }

            $paramAnalysis[$pk] = [
                'key'    => $pk,
                'label'  => $pInfo['label'],
                'unit'   => $pInfo['unit'],
                'points' => $points,
                'stats'  => $stats,
            ];
        }

        // 5. Parameter dengan korelasi Pearson tertinggi (abs)
        $best = null;
        foreach ($paramAnalysis as $pk => $pa) {
            $r = $pa['stats']['pearson'];
            if ($r === null) continue;
            if ($best === null || abs($r) > abs($paramAnalysis[$best]['stats']['pearson'] ?? 0)) {
                $best = $pk;
            }
        }

        return [
            'paramAnalysis' => $paramAnalysis,
            'bestParam'     => $best,
        ];
    }

    // ─── Normalisasi byYear agar semua tahun standar ada ─────────────────────
    protected function normalizeByYear(array $byYear, array $allYears): array
    {
        $result = [];
        foreach ($allYears as $y) {
            $result[$y] = $byYear[$y] ?? [null, null, null, null];
        }
        return $result;
    }

    // ─── Statistik ───────────────────────────────────────────────────────────

    protected function pearsonR(array $x, array $y): ?float
    {
        $n = count($x);
        if ($n < 2) return null;

        $mx = array_sum($x) / $n;
        $my = array_sum($y) / $n;

        $num = 0.0; $dx2 = 0.0; $dy2 = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $dx = $x[$i] - $mx;
            $dy = $y[$i] - $my;
            $num += $dx * $dy;
            $dx2 += $dx * $dx;
            $dy2 += $dy * $dy;
        }

        $denom = sqrt($dx2 * $dy2);
        if ($denom == 0.0) return null;

        return $num / $denom;
    }

    protected function getRanks(array $data): array
    {
        $n       = count($data);
        $indexed = array_map(null, $data, range(0, $n - 1));
        usort($indexed, fn($a, $b) => $a[0] <=> $b[0]);

        $ranks = array_fill(0, $n, 0.0);
        $i = 0;
        while ($i < $n) {
            $j = $i;
            while ($j + 1 < $n && $indexed[$j + 1][0] == $indexed[$i][0]) $j++;
            $avgRank = ($i + $j) / 2.0 + 1.0;
            for ($k = $i; $k <= $j; $k++) $ranks[$indexed[$k][1]] = $avgRank;
            $i = $j + 1;
        }
        return $ranks;
    }

    protected function spearmanRho(array $x, array $y): ?float
    {
        return $this->pearsonR($this->getRanks($x), $this->getRanks($y));
    }

    protected function pValue(float $r, int $n): ?float
    {
        if ($n <= 2)       return null;
        if (abs($r) >= 1.0) return 0.0;

        $t  = $r * sqrt(($n - 2) / (1 - $r * $r));
        $df = $n - 2;
        $t2 = $t * $t;
        $x  = $df / ($df + $t2);
        $p  = $this->incompleteBetaReg($x, $df / 2.0, 0.5) * 2.0;

        return min(max($p, 0.0), 1.0);
    }

    /**
     * Log-gamma via Lanczos approximation (PHP 8.x compat — lgamma() may not exist).
     */
    protected function logGamma(float $z): float
    {
        // Lanczos g=7, n=9
        $p = [
            0.99999999999980993,
            676.5203681218851,
            -1259.1392167224028,
            771.32342877765313,
            -176.61502916214059,
            12.507343278686905,
            -0.13857109526572012,
            9.9843695780195716e-6,
            1.5056327351493116e-7,
        ];
        if ($z < 0.5) {
            return log(M_PI / sin(M_PI * $z)) - $this->logGamma(1 - $z);
        }
        $z -= 1;
        $a  = $p[0];
        $t  = $z + 7.5;
        for ($i = 1; $i <= 8; $i++) $a += $p[$i] / ($z + $i);
        return 0.5 * log(2 * M_PI) + ($z + 0.5) * log($t) - $t + log($a);
    }

    protected function incompleteBetaReg(float $x, float $a, float $b): float
    {
        if ($x <= 0.0) return 0.0;
        if ($x >= 1.0) return 1.0;

        $lbeta = $this->logGamma($a) + $this->logGamma($b) - $this->logGamma($a + $b);
        $front = exp(log($x) * $a + log(1 - $x) * $b - $lbeta) / $a;

        $f = 1.0; $c = 1.0;
        $d = 1.0 - ($a + $b) * $x / ($a + 1);
        if (abs($d) < 1e-30) $d = 1e-30;
        $d = 1.0 / $d;
        $f = $d;

        for ($m = 1; $m <= 200; $m++) {
            $mm    = 2 * $m;
            $coeff = $m * ($b - $m) * $x / (($a + $mm - 1) * ($a + $mm));
            $d = 1.0 + $coeff * $d; if (abs($d) < 1e-30) $d = 1e-30;
            $c = 1.0 + $coeff / $c; if (abs($c) < 1e-30) $c = 1e-30;
            $d = 1.0 / $d; $f *= $d * $c;

            $coeff = -($a + $m) * ($a + $b + $m) * $x / (($a + $mm) * ($a + $mm + 1));
            $d = 1.0 + $coeff * $d; if (abs($d) < 1e-30) $d = 1e-30;
            $c = 1.0 + $coeff / $c; if (abs($c) < 1e-30) $c = 1e-30;
            $d = 1.0 / $d; $delta = $d * $c; $f *= $delta;

            if (abs($delta - 1.0) < 1e-10) break;
        }

        return $front * $f;
    }

    protected function linearRegression(array $x, array $y): array
    {
        $n = count($x);
        if ($n < 2) return ['slope' => null, 'intercept' => null, 'r2' => null];

        $mx = array_sum($x) / $n;
        $my = array_sum($y) / $n;

        $num = 0.0; $den = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $num += ($x[$i] - $mx) * ($y[$i] - $my);
            $den += ($x[$i] - $mx) ** 2;
        }

        if ($den == 0) return ['slope' => null, 'intercept' => null, 'r2' => null];

        $slope     = $num / $den;
        $intercept = $my - $slope * $mx;
        $r         = $this->pearsonR($x, $y);
        $r2        = $r !== null ? $r * $r : null;

        return ['slope' => $slope, 'intercept' => $intercept, 'r2' => $r2];
    }

    /**
     * Ekspor data analisis korelasi oseanografi dan CPUE ke format CSV.
     */
    public function exportAnalysisCsv(Request $request)
    {
        $filterYear   = $request->input('year', 'all');
        $filterSeason = $request->input('season', 'all');

        $analysisData  = $this->buildAnalysis($filterYear, $filterSeason);
        $paramAnalysis = $analysisData['paramAnalysis'] ?? [];

        $filename = "SCOMAR-Analisis-Oseanografi-CPUE-{$filterYear}-{$filterSeason}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($paramAnalysis, $filterYear, $filterSeason) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM untuk Microsoft Excel di Windows
            fputs($file, "\xEF\xBB\xBF");

            // Header Dokumen
            fputcsv($file, ['SCOMAR - SISTEM INFORMASI KELAUTAN DAN PERIKANAN PULAU TUNDA']);
            fputcsv($file, ['ANALISIS HUBUNGAN PARAMETER OSEANOGRAFI DAN CPUE IKAN TENGGIRI']);
            fputcsv($file, ['Wilayah: Perairan Pulau Tunda, Kabupaten Serang, Banten']);
            fputcsv($file, ['Filter Tahun: ' . ($filterYear === 'all' ? 'Semua Tahun (2021-2026)' : $filterYear)]);
            fputcsv($file, ['Filter Musim: ' . ($filterSeason === 'all' ? 'Semua Musim' : ucfirst($filterSeason))]);
            fputcsv($file, ['Waktu Ekspor: ' . date('d-m-Y H:i:s') . ' WIB']);
            fputcsv($file, []);

            // 1. Tabel Ringkasan Statistik Korelasi
            fputcsv($file, ['--- RINGKASAN STATISTIK KORELASI & REGRESI LINEAR ---']);
            fputcsv($file, [
                'Parameter Oseanografi',
                'Satuan',
                'Jumlah Sampel (n)',
                'Pearson (r)',
                'Spearman (rho)',
                'P-Value',
                'R-Squared (R2)',
                'Slope (Kemiringan)',
                'Intercept (Konstanta)',
                'Arah Hubungan',
                'Kekuatan Hubungan',
                'Signifikansi (p < 0.05)'
            ]);

            foreach ($paramAnalysis as $pk => $pa) {
                $st = $pa['stats'] ?? [];
                fputcsv($file, [
                    $pa['label'] ?? '-',
                    $pa['unit'] ?? '-',
                    $st['n'] ?? 0,
                    $st['pearson'] !== null ? number_format((float) $st['pearson'], 4, '.', '') : 'Null',
                    $st['spearman'] !== null ? number_format((float) $st['spearman'], 4, '.', '') : 'Null',
                    $st['pvalue'] !== null ? number_format((float) $st['pvalue'], 4, '.', '') : 'Null',
                    $st['r2'] !== null ? number_format((float) $st['r2'], 4, '.', '') : 'Null',
                    $st['slope'] !== null ? number_format((float) $st['slope'], 4, '.', '') : 'Null',
                    $st['intercept'] !== null ? number_format((float) $st['intercept'], 4, '.', '') : 'Null',
                    $st['direction'] ?? 'Null',
                    $st['strength'] ?? 'Null',
                    isset($st['significant']) ? ($st['significant'] ? 'Signifikan' : 'Tidak Signifikan') : 'Null'
                ]);
            }

            fputcsv($file, []);

            // 2. Tabel Rincian Titik Data Observasi
            fputcsv($file, ['--- RINCIAN TITIK DATA OBSERVASI SPASIAL-TEMPORAL ---']);
            fputcsv($file, [
                'Tahun',
                'Musim',
                'Parameter Oseanografi',
                'Nilai Parameter (X)',
                'Satuan',
                'CPUE (kg/trip) (Y)',
                'Status Validitas'
            ]);

            foreach ($paramAnalysis as $pk => $pa) {
                $pLabel = $pa['label'] ?? '-';
                $pUnit  = $pa['unit'] ?? '-';
                $pts    = $pa['points'] ?? [];

                foreach ($pts as $pt) {
                    fputcsv($file, [
                        $pt['year'] ?? '-',
                        $pt['season'] ?? '-',
                        $pLabel,
                        $pt['x'] !== null ? number_format((float) $pt['x'], 4, '.', '') : 'Null',
                        $pUnit,
                        $pt['y'] !== null ? number_format((float) $pt['y'], 4, '.', '') : 'Null',
                        ($pt['valid'] ?? false) ? 'Valid' : 'Data Tidak Lengkap'
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
