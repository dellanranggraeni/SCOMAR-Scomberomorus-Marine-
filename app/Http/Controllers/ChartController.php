<?php

namespace App\Http\Controllers;

use App\Models\CatchData;
use App\Models\OceanographicParameter;
use App\Services\CatchCsvService;
use App\Services\OceanographicCsvService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChartController extends Controller
{
    /**
     * Menampilkan halaman Grafik Hasil Tangkapan Ikan Scomberomorus (Tenggiri).
     */
    public function catches(Request $request, CatchCsvService $catchService): View
    {
        // 1. Ambil data hasil tangkapan riil dari CSV storage/app/private/datasets/hasil tangkapan/
        $csvCatchData = $catchService->getCatchData();

        // 2. Data dari Database untuk mendukung fitur CRUD admin
        $catches = CatchData::orderBy('year', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $catchesForAlpine = $catches->map(function ($item) {
            return [
                'id'         => $item->id,
                'year'       => (string) $item->year,
                'season'     => $item->season,
                'period'     => $item->period,
                'totalCatch' => (float) $item->total_catch,
                'totalCount' => (int) $item->total_count,
                'trips'      => (int) $item->trips,
                'cpue'       => (float) $item->cpue,
                'notes'      => $item->notes ?? '',
            ];
        })->values();

        // Gunakan daftar tahun dari CSV (2021-2026), fallback ke database jika kosong
        $years = !empty($csvCatchData['years'])
            ? collect($csvCatchData['years'])->sortDesc()->values()
            : $catches->pluck('year')->unique()->sortDesc()->values();

        if ($years->isEmpty()) {
            $years = collect(['2026', '2025', '2024', '2023', '2022', '2021']);
        }

        $seasonalFlat = $csvCatchData['seasonalFlat'];
        $annualTotals = $csvCatchData['annualTotals'];
        $byYear       = $csvCatchData['byYear'];
        $comparison   = $csvCatchData['comparison'];
        $overallStats = $csvCatchData['overallStats'];

        return view('charts.catches', compact(
            'catches',
            'catchesForAlpine',
            'years',
            'csvCatchData',
            'seasonalFlat',
            'annualTotals',
            'byYear',
            'comparison',
            'overallStats'
        ));
    }

    /**
     * Menampilkan halaman Grafik Parameter Oseanografi.
     */
    public function oceanographic(Request $request, OceanographicCsvService $csvService): View
    {
        $oceanData = OceanographicParameter::orderBy('year', 'asc')
            ->get();

        // Ambil data musiman SPL riil dari 4 file CSV secara dinamis
        $seasonalSpl = $csvService->getSeasonalSplData();
        $seasonalSplFromCsv = $seasonalSpl['byYear'];
        $seasonalSplComparison = $seasonalSpl['bySeason'];
        $splAnnualAverages = $seasonalSpl['annualAverages'] ?? [];

        // Ambil data musiman Klorofil-a riil dari 4 file CSV secara dinamis
        $seasonalChla = $csvService->getSeasonalChlaData();
        $seasonalChlaFromCsv = $seasonalChla['byYear'];
        $seasonalChlaComparison = $seasonalChla['bySeason'];

        // Ambil data arus laut riil (kecepatan & arah) dari data_arus_clean.csv secara dinamis
        $seasonalCurrent = $csvService->getSeasonalCurrentData();
        $seasonalCurrentFromCsv = $seasonalCurrent['byYear'];
        $seasonalCurrentComparison = $seasonalCurrent['bySeason'];
        $currentDetailsByYear = $seasonalCurrent['detailsByYear'];

        // Ambil data TSS riil dari rata-rata nilai tss.csv secara dinamis
        $seasonalTss = $csvService->getSeasonalTssData();
        $seasonalTssFromCsv = $seasonalTss['byYear'];
        $seasonalTssComparison = $seasonalTss['bySeason'];
        $tssAnnualAverages = $seasonalTss['annualAverages'];
        $tssOverallStats = $seasonalTss['overallStats'];

        // Rekapitulasi 24 periode komprehensif (4 musim x 6 tahun)
        $seasonalRecap24 = $csvService->getComprehensiveSeasonalRecap();

        $oceanDataForAlpine = $oceanData->map(function ($item) use ($tssAnnualAverages, $splAnnualAverages) {
            $yearStr = (string) $item->year;
            // Gunakan nilai SPL rata-rata tahunan dari CSV jika tersedia
            $tempVal = isset($splAnnualAverages[$yearStr]) && $splAnnualAverages[$yearStr] !== null
                ? (float) $splAnnualAverages[$yearStr]
                : (float) $item->temp;

            // Gunakan nilai TSS rata-rata tahunan dari CSV jika tersedia
            $tssVal = isset($tssAnnualAverages[$yearStr]) && $tssAnnualAverages[$yearStr] !== null
                ? (float) $tssAnnualAverages[$yearStr]
                : (float) $item->tss;

            $chlVal  = (float) $item->chlorophyll;
            $curVal  = (float) $item->current;

            $habitatEval = OceanographicCsvService::evaluateHabitatSuitability($tempVal, $chlVal, $tssVal, $curVal);

            return [
                'id'           => $item->id,
                'year'         => $yearStr,
                'temp'         => $tempVal,
                'tempEval'     => OceanographicCsvService::classifyOceanParam($tempVal, 'temp'),
                'chlorophyll'  => $chlVal,
                'chlaEval'     => OceanographicCsvService::classifyOceanParam($chlVal, 'chlorophyll'),
                'tss'          => $tssVal,
                'tssEval'      => OceanographicCsvService::classifyOceanParam($tssVal, 'tss'),
                'current'      => $curVal,
                'currentEval'  => OceanographicCsvService::classifyOceanParam($curVal, 'current'),
                'status'       => $item->status,
                'habitatScore' => $habitatEval['score'],
                'habitatBadge' => $habitatEval['badge'],
                'habitatDesc'  => $habitatEval['desc'],
                'source'       => $item->source,
                'notes'        => $item->notes ?? '',
            ];
        })->values();

        $years = $oceanData->pluck('year')->unique()->sortDesc()->values();
        if ($years->isEmpty()) {
            $years = collect(['2026', '2025', '2024', '2023', '2022', '2021']);
        }

        return view('charts.oceanographic', compact(
            'oceanData',
            'oceanDataForAlpine',
            'years',
            'seasonalSplFromCsv',
            'seasonalSplComparison',
            'splAnnualAverages',
            'seasonalChlaFromCsv',
            'seasonalChlaComparison',
            'seasonalCurrentFromCsv',
            'seasonalCurrentComparison',
            'currentDetailsByYear',
            'seasonalTssFromCsv',
            'seasonalTssComparison',
            'tssAnnualAverages',
            'tssOverallStats',
            'seasonalRecap24'
        ));
    }

    /**
     * Ekspor data hasil tangkapan ikan ke format CSV.
     * Mendukung filter tahun atau seluruh periode (2021-2026).
     */
    public function exportCatchesCsv(Request $request, CatchCsvService $catchService)
    {
        $year = $request->query('year');
        $csvCatchData = $catchService->getCatchData();
        $seasonalFlat = $csvCatchData['seasonalFlat'] ?? [];
        $annualTotals = $csvCatchData['annualTotals'] ?? [];

        // Filter berdasarkan tahun jika diminta
        if ($year && is_numeric($year)) {
            $seasonalRows = array_filter($seasonalFlat, function ($row) use ($year) {
                return (string) ($row['year'] ?? '') === (string) $year;
            });
            $annualRows = isset($annualTotals[$year]) ? [$annualTotals[$year]] : [];
            $filename = "SCOMAR-Hasil-Tangkapan-{$year}.csv";
        } else {
            $seasonalRows = $seasonalFlat;
            $annualRows = array_values($annualTotals);
            $filename = "SCOMAR-Hasil-Tangkapan-2021-2026.csv";
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($seasonalRows, $annualRows) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM untuk Microsoft Excel di Windows
            fputs($file, "\xEF\xBB\xBF");

            // Header Dokumen
            fputcsv($file, ['SCOMAR - SISTEM INFORMASI PERIKANAN LAUT PULAU TUNDA']);
            fputcsv($file, ['DATA HASIL TANGKAPAN IKAN TENGGIRI (Scomberomorus commerson)']);
            fputcsv($file, ['Lokasi: Perairan Pulau Tunda, Kabupaten Serang, Banten']);
            fputcsv($file, ['Waktu Ekspor: ' . date('d-m-Y H:i:s') . ' WIB']);
            fputcsv($file, []);

            // Header Tabel
            fputcsv($file, [
                'Tahun',
                'Musim',
                'Kode Musim',
                'Periode Bulan',
                'Produksi (Ton)',
                'Produksi (Kg)',
                'Jumlah Trip',
                'CPUE (Kg/Trip)',
                'Harga Rata-rata (Rp/Kg)',
                'Estimasi Nilai (Juta Rp)',
                'Status Musim'
            ]);

            // Data Musiman
            foreach ($seasonalRows as $row) {
                $status = 'Normal';
                $sName = $row['season_name'] ?? '';
                if (str_contains($sName, 'Timur')) {
                    $status = 'Puncak Musim (High Season)';
                } elseif (str_contains($sName, 'Barat')) {
                    $status = 'Musim Barat (Ombak Besar)';
                } elseif (str_contains($sName, 'Peralihan')) {
                    $status = 'Musim Peralihan';
                }
                if (($row['produksi_kg'] ?? null) === null) {
                    $status = 'Data Tidak Tersedia (Null)';
                }

                fputcsv($file, [
                    $row['year'] ?? '-',
                    $row['season_name'] ?? '-',
                    $row['season_code'] ?? '-',
                    $row['period'] ?? '-',
                    $row['produksi_ton'] !== null ? number_format((float) $row['produksi_ton'], 3, '.', '') : 'Null',
                    $row['produksi_kg'] !== null ? number_format((float) $row['produksi_kg'], 0, '.', '') : 'Null',
                    $row['trip'] !== null ? $row['trip'] : 'Null',
                    $row['cpue'] !== null ? number_format((float) $row['cpue'], 2, '.', '') : 'Null',
                    $row['harga'] !== null ? number_format((float) $row['harga'], 0, '.', '') : 'Null',
                    $row['nilai_juta'] !== null ? number_format((float) $row['nilai_juta'], 2, '.', '') : 'Null',
                    $status
                ]);
            }

            // Rekapitulasi Tahunan Resmi
            if (!empty($annualRows)) {
                fputcsv($file, []);
                fputcsv($file, ['--- REKAPITULASI TAHUNAN RESMI ---']);
                foreach ($annualRows as $ann) {
                    fputcsv($file, [
                        $ann['year'] ?? '-',
                        $ann['label'] ?? "Total Tahun {$ann['year']}",
                        'TAHUNAN',
                        'Januari - Desember',
                        $ann['produksi_ton'] !== null ? number_format((float) $ann['produksi_ton'], 3, '.', '') : 'Null',
                        $ann['produksi_kg'] !== null ? number_format((float) $ann['produksi_kg'], 0, '.', '') : 'Null',
                        $ann['trip'] !== null ? $ann['trip'] : 'Null',
                        $ann['cpue'] !== null ? number_format((float) $ann['cpue'], 2, '.', '') : 'Null',
                        $ann['harga'] !== null ? number_format((float) $ann['harga'], 0, '.', '') : 'Null',
                        $ann['nilai_juta'] !== null ? number_format((float) $ann['nilai_juta'], 2, '.', '') : 'Null',
                        'Total Tahunan Resmi'
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ekspor data parameter oseanografi ke format CSV.
     * Mendukung filter tahun atau seluruh periode (2021-2026).
     */
    public function exportOceanographicCsv(Request $request, OceanographicCsvService $csvService)
    {
        $year = $request->query('year');
        $recap = $csvService->getComprehensiveSeasonalRecap();

        if ($year && is_numeric($year)) {
            $filteredRecap = array_values(array_filter($recap, function ($item) use ($year) {
                return (string) ($item['year'] ?? '') === (string) $year;
            }));
            $filename = "SCOMAR-Parameter-Oseanografi-{$year}.csv";
        } else {
            $filteredRecap = $recap;
            $filename = "SCOMAR-Parameter-Oseanografi-2021-2026.csv";
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($filteredRecap, $year) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM untuk Microsoft Excel di Windows
            fputs($file, "\xEF\xBB\xBF");

            // Header Dokumen
            fputcsv($file, ['SCOMAR - SISTEM INFORMASI KELAUTAN DAN PERIKANAN PULAU TUNDA']);
            fputcsv($file, ['DATA PARAMETER OSEANOGRAFI PERAIRAN PULAU TUNDA (2021-2026)']);
            fputcsv($file, ['Wilayah: Perairan Pulau Tunda, Kabupaten Serang, Banten']);
            fputcsv($file, ['Filter Tahun: ' . ($year ?: 'Seluruh Periode (2021-2026)')]);
            fputcsv($file, ['Waktu Ekspor: ' . date('d-m-Y H:i:s') . ' WIB']);
            fputcsv($file, []);

            // Header Tabel
            fputcsv($file, [
                'Tahun',
                'Musim',
                'Kode Musim',
                'Periode Pengamatan',
                'Suhu Permukaan Laut (SPL C)',
                'Evaluasi SPL',
                'Klorofil-a (mg/m3)',
                'Evaluasi Klorofil-a',
                'Total Suspended Solids (TSS mg/L)',
                'Evaluasi TSS',
                'Kecepatan Arus (m/s)',
                'Evaluasi Arus',
                'Skor Kesesuaian Habitat (0-100)',
                'Predikat Kesesuaian Habitat',
                'Deskripsi Evaluasi Lingkungan'
            ]);

            // Baris Data
            foreach ($filteredRecap as $row) {
                $tempEval = is_array($row['temp_eval']) ? ($row['temp_eval']['status'] ?? '-') : ($row['temp_eval'] ?? '-');
                $chlaEval = is_array($row['chla_eval']) ? ($row['chla_eval']['status'] ?? '-') : ($row['chla_eval'] ?? '-');
                $tssEval  = is_array($row['tss_eval']) ? ($row['tss_eval']['status'] ?? '-') : ($row['tss_eval'] ?? '-');
                $curEval  = is_array($row['current_eval']) ? ($row['current_eval']['status'] ?? '-') : ($row['current_eval'] ?? '-');

                $scoreVal = is_numeric($row['habitatScore'])
                    ? number_format((float) $row['habitatScore'], 1, '.', '')
                    : (string) ($row['habitatScore'] ?? '-');

                $badgeVal = $row['habitatBadge'] ?? '-';
                if ($badgeVal === 'badge-score-optimal') {
                    $badgeVal = 'Optimal';
                } elseif ($badgeVal === 'badge-score-suboptimal') {
                    $badgeVal = 'Belum Optimal';
                } elseif ($badgeVal === 'badge-score-pending') {
                    $badgeVal = 'Belum Dapat Dinilai';
                }

                fputcsv($file, [
                    $row['year'] ?? '-',
                    $row['season_name'] ?? '-',
                    $row['season_code'] ?? '-',
                    $row['period_label'] ?? '-',
                    $row['temp'] !== null ? number_format((float) $row['temp'], 3, '.', '') : 'Null',
                    $tempEval,
                    $row['chlorophyll'] !== null ? number_format((float) $row['chlorophyll'], 3, '.', '') : 'Null',
                    $chlaEval,
                    $row['tss'] !== null ? number_format((float) $row['tss'], 3, '.', '') : 'Null',
                    $tssEval,
                    $row['current'] !== null ? number_format((float) $row['current'], 3, '.', '') : 'Null',
                    $curEval,
                    $scoreVal,
                    $badgeVal,
                    $row['habitatDesc'] ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
