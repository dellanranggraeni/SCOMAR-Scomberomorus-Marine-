<?php

namespace App\Http\Controllers;

use App\Models\CatchData;
use App\Models\OceanographicParameter;
use App\Models\FishingGround;
use App\Models\CoralReef;
use App\Models\FishingGear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman Dashboard utama SCOMAR.
     * Mengambil data statistik terintegrasi dari database MySQL SCOMAR.
     */
    public function index(Request $request): View
    {
        // 1. Ambil data entitas dari database
        $fishingGrounds = FishingGround::orderBy('id', 'asc')->get();
        $coralReefs     = CoralReef::orderBy('id', 'asc')->get();
        $fishingGears   = FishingGear::orderBy('id', 'asc')->get();

        // 2. Hitung statistik ringkasan kartu status
        $totalCatchKg = (float) (CatchData::sum('total_catch') ?: 31711.50);

        // Parameter oseanografi tahun terkini
        $latestOcean = OceanographicParameter::orderBy('year', 'desc')->first();
        $avgTemp = $latestOcean ? (float) $latestOcean->temp : (float) (OceanographicParameter::avg('temp') ?: 29.60);
        $avgChla = $latestOcean ? (float) $latestOcean->chlorophyll : (float) (OceanographicParameter::avg('chlorophyll') ?: 0.88);

        $stats = [
            'total_catch_kg' => $totalCatchKg,
            'avg_temp'       => $avgTemp,
            'avg_chla'       => $avgChla,
            'grounds_count'  => $fishingGrounds->count(),
        ];

        // 3. Scan dinamis folder dan manifest GeoJSON ZPPI
        $zppiManifest = [];
        $zppiBase = public_path('DatafishingGround');
        if (is_dir($zppiBase)) {
            $yearDirs = glob($zppiBase . '/*', GLOB_ONLYDIR);
            sort($yearDirs);
            foreach ($yearDirs as $ydir) {
                $yearName = basename($ydir);
                $files = glob($ydir . '/*.geojson');
                $seasons = [];
                // Tahap 1: Cari file gabungan tahunan (ZPPI*) terlebih dahulu
                $annualFile = null;
                foreach ($files as $f) {
                    $fname = basename($f);
                    $lower = strtolower($fname);
                    // File tahunan: diawali 'zppi' dan mengandung tahun (bukan file musiman)
                    if (
                        str_starts_with($lower, 'zppi') &&
                        !str_contains($lower, 'barat') &&
                        !str_contains($lower, 'peralihan') &&
                        !str_contains($lower, 'timur')
                    ) {
                        $annualFile = $fname;
                        break;
                    }
                }
                if ($annualFile) {
                    $seasons['tahunan'] = [
                        'label' => 'Tahunan',
                        'file'  => $annualFile,
                        'url'   => '/DatafishingGround/' . $yearName . '/' . $annualFile,
                    ];
                }
                // Tahap 2: Kenali file per musim
                foreach ($files as $f) {
                    $fname = basename($f);
                    $lower = strtolower($fname);
                    $key = null;
                    $label = null;
                    if (str_contains($lower, 'barat')) {
                        $key = 'barat';
                        $label = 'Musim Barat';
                    } elseif (str_contains($lower, 'peralihan1') || str_contains($lower, 'peralihan_1') || str_contains($lower, 'peralihan 1')) {
                        $key = 'peralihan1';
                        $label = 'Musim Peralihan I';
                    } elseif (str_contains($lower, 'peralihan2') || str_contains($lower, 'peralihan_2') || str_contains($lower, 'peralihan 2')) {
                        $key = 'peralihan2';
                        $label = 'Musim Peralihan II';
                    } elseif (str_contains($lower, 'timur')) {
                        $key = 'timur';
                        $label = 'Musim Timur';
                    }
                    if ($key) {
                        $seasons[$key] = [
                            'label' => $label,
                            'file'  => $fname,
                            'url'   => '/DatafishingGround/' . $yearName . '/' . $fname,
                        ];
                    }
                }
                if (!empty($seasons)) {
                    $order = ['tahunan', 'barat', 'peralihan1', 'timur', 'peralihan2'];
                    uksort($seasons, fn($a, $b) => (array_search($a, $order) !== false ? array_search($a, $order) : 99) - (array_search($b, $order) !== false ? array_search($b, $order) : 99));
                    $zppiManifest[$yearName] = [
                        'label'   => 'Tahun ' . $yearName,
                        'seasons' => $seasons,
                    ];
                }
            }
        }

        // 4. Scan dinamis folder dan manifest GeoJSON Lyzenga (2021-2026)
        $lyzengaManifest = [];
        $lyzengaBase = public_path('DataLyzenga');
        if (is_dir($lyzengaBase)) {
            $lyzengaDirs = glob($lyzengaBase . '/*', GLOB_ONLYDIR);
            sort($lyzengaDirs);
            foreach ($lyzengaDirs as $ldir) {
                $yearName = basename($ldir);
                $files = glob($ldir . '/*.geojson');
                if (!empty($files)) {
                    $f = $files[0];
                    $fname = basename($f);
                    $lyzengaManifest[$yearName] = [
                        'year'    => $yearName,
                        'label'   => 'Tahun ' . $yearName,
                        'file'    => $fname,
                        'url'     => '/DataLyzenga/' . $yearName . '/' . $fname,
                        'size_mb' => round(filesize($f) / (1024 * 1024), 2),
                    ];
                }
            }
        }

        // 5. Data terstruktur CatchData per tahun & musim untuk 4 kartu statistik dinamis
        $catchByPeriod = [];
        $catches = CatchData::all();
        foreach ($catches as $c) {
            $y = (string) $c->year;
            if (!isset($catchByPeriod[$y])) {
                $catchByPeriod[$y] = [
                    'total' => 0,
                    'seasons' => [],
                ];
            }
            $catchVal = (float) $c->total_catch;
            $catchByPeriod[$y]['total'] += $catchVal;

            $sName = strtolower(trim($c->season ?? ''));
            $sKey = null;
            if (str_contains($sName, 'barat')) {
                $sKey = 'barat';
            } elseif (str_contains($sName, 'peralihan ii') || str_contains($sName, 'peralihan 2') || str_contains($sName, 'peralihan2')) {
                $sKey = 'peralihan2';
            } elseif (str_contains($sName, 'peralihan i') || str_contains($sName, 'peralihan 1') || str_contains($sName, 'peralihan1')) {
                $sKey = 'peralihan1';
            } elseif (str_contains($sName, 'timur')) {
                $sKey = 'timur';
            }
            if ($sKey) {
                $catchByPeriod[$y]['seasons'][$sKey] = $catchVal;
            }
        }

        // 6. Data terstruktur OceanographicParameter per tahun untuk 4 kartu statistik dinamis
        $oceanByYear = [];
        $oceanParams = OceanographicParameter::all();
        foreach ($oceanParams as $op) {
            $y = (string) $op->year;
            $oceanByYear[$y] = [
                'temp'        => (float) $op->temp,
                'chlorophyll' => (float) $op->chlorophyll,
                'status'      => $op->status,
                'source'      => $op->source,
            ];
        }

        // Data tabel CRUD lengkap untuk dashboard
        $catchesList = CatchData::orderBy('year', 'desc')->orderBy('id', 'desc')->get();
        $oceanParamsList = OceanographicParameter::orderBy('year', 'desc')->get();
        $allUsers = (auth()->check() && auth()->user()->isMasterAdmin())
            ? User::orderBy('created_at', 'desc')->get()
            : collect();

        return view('dashboard.index', compact(
            'stats',
            'fishingGrounds',
            'coralReefs',
            'fishingGears',
            'zppiManifest',
            'lyzengaManifest',
            'catchByPeriod',
            'oceanByYear',
            'catchesList',
            'oceanParamsList',
            'allUsers'
        ));
    }
}
