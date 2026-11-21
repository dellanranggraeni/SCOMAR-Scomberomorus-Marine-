<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatchData;
use App\Models\OceanographicParameter;
use App\Models\FishingGround;
use App\Models\CoralReef;
use App\Models\FishingGear;

class InitialScomarDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Hasil Tangkapan
        if (CatchData::count() === 0) {
            $catches = [
                // 2024
                ['year' => '2024', 'season' => 'Musim Barat',  'period' => 'Desember - Februari', 'total_catch' => 1450.0, 'total_count' => 360,  'trips' => 15, 'cpue' => 96.67],
                ['year' => '2024', 'season' => 'Peralihan I',  'period' => 'Maret - Mei',        'total_catch' => 2180.5, 'total_count' => 545,  'trips' => 19, 'cpue' => 114.76],
                ['year' => '2024', 'season' => 'Musim Timur',  'period' => 'Juni - Agustus',     'total_catch' => 3620.0, 'total_count' => 905,  'trips' => 24, 'cpue' => 150.83],
                ['year' => '2024', 'season' => 'Peralihan II', 'period' => 'September - November', 'total_catch' => 2410.0, 'total_count' => 602,  'trips' => 21, 'cpue' => 114.76],
                // 2025
                ['year' => '2025', 'season' => 'Musim Barat',  'period' => 'Desember - Februari', 'total_catch' => 1620.0, 'total_count' => 405,  'trips' => 16, 'cpue' => 101.25],
                ['year' => '2025', 'season' => 'Peralihan I',  'period' => 'Maret - Mei',        'total_catch' => 2540.0, 'total_count' => 635,  'trips' => 22, 'cpue' => 115.45],
                ['year' => '2025', 'season' => 'Musim Timur',  'period' => 'Juni - Agustus',     'total_catch' => 4280.0, 'total_count' => 1070, 'trips' => 27, 'cpue' => 158.52],
                ['year' => '2025', 'season' => 'Peralihan II', 'period' => 'September - November', 'total_catch' => 2890.5, 'total_count' => 722,  'trips' => 23, 'cpue' => 125.67],
                // 2026
                ['year' => '2026', 'season' => 'Musim Barat',  'period' => 'Desember - Februari', 'total_catch' => 1780.0, 'total_count' => 445,  'trips' => 17, 'cpue' => 104.71],
                ['year' => '2026', 'season' => 'Peralihan I',  'period' => 'Maret - Mei',        'total_catch' => 2810.0, 'total_count' => 702,  'trips' => 24, 'cpue' => 117.08],
                ['year' => '2026', 'season' => 'Musim Timur',  'period' => 'Juni - Agustus',     'total_catch' => 4150.0, 'total_count' => 1038, 'trips' => 26, 'cpue' => 159.62],
                ['year' => '2026', 'season' => 'Peralihan II', 'period' => 'September - November', 'total_catch' => 1980.5, 'total_count' => 495,  'trips' => 18, 'cpue' => 110.03],
            ];
            foreach ($catches as $c) {
                CatchData::create($c);
            }
        }

        // 2. Parameter Oseanografi
        if (OceanographicParameter::count() === 0) {
            $ocean = [
                ['year' => '2021', 'temp' => 28.75, 'chlorophyll' => 0.62, 'tss' => 16.40, 'current' => 0.28, 'status' => 'Normal / Subur',         'habitat_score' => 'Optimal',     'source' => 'Satelit Aqua MODIS & Copernicus Marine Service'],
                ['year' => '2022', 'temp' => 29.10, 'chlorophyll' => 0.74, 'tss' => 15.20, 'current' => 0.31, 'status' => 'Subur',                  'habitat_score' => 'Optimal',     'source' => 'Satelit Aqua MODIS & Copernicus Marine Service'],
                ['year' => '2023', 'temp' => 29.80, 'chlorophyll' => 0.91, 'tss' => 14.80, 'current' => 0.35, 'status' => 'Sangat Subur (Upwelling)', 'habitat_score' => 'Sangat Baik', 'source' => 'Satelit Aqua MODIS & Copernicus Marine Service'],
                ['year' => '2024', 'temp' => 29.40, 'chlorophyll' => 0.82, 'tss' => 15.60, 'current' => 0.30, 'status' => 'Subur / Stabil',          'habitat_score' => 'Optimal',     'source' => 'Satelit Aqua MODIS & Copernicus Marine Service'],
                ['year' => '2025', 'temp' => 30.20, 'chlorophyll' => 1.05, 'tss' => 13.90, 'current' => 0.42, 'status' => 'Puncak Kesuburan',        'habitat_score' => 'Sangat Baik', 'source' => 'Satelit Aqua MODIS & Copernicus Marine Service'],
                ['year' => '2026', 'temp' => 29.60, 'chlorophyll' => 0.88, 'tss' => 14.20, 'current' => 0.35, 'status' => 'Subur / Optimal',         'habitat_score' => 'Optimal',     'source' => 'Satelit Aqua MODIS & Copernicus Marine Service'],
            ];
            foreach ($ocean as $o) {
                OceanographicParameter::create($o);
            }
        }

        // 3. Fishing Grounds
        if (FishingGround::count() === 0) {
            $grounds = [
                [
                    'name'              => 'Fishing Ground A (Utara)',
                    'latitude'          => -5.7950,
                    'longitude'         => 106.2600,
                    'coordinates_label' => '5°47\'42"S, 106°15\'36"E',
                    'depth_meters'      => '25 - 35 m',
                    'recommended_gear'  => 'Pancing tangan, jaring insang hanyut',
                    'status'            => 'Aktif',
                    'description'       => 'Zona penangkapan perairan utara Pulau Tunda dengan kedalaman batimetri optimal untuk gerombolan tenggiri.',
                ],
                [
                    'name'              => 'Fishing Ground B (Timur Laut)',
                    'latitude'          => -5.8100,
                    'longitude'         => 106.2850,
                    'coordinates_label' => '5°48\'36"S, 106°17\'6"E',
                    'depth_meters'      => '20 - 30 m',
                    'recommended_gear'  => 'Bubu selektif, pancing tonda',
                    'status'            => 'Aktif',
                    'description'       => 'Dekat tubir karang sisi timur dengan arus laut sedang dan konsentrasi klorofil-a stabil.',
                ],
                [
                    'name'              => 'Fishing Ground C (Selatan)',
                    'latitude'          => -5.8200,
                    'longitude'         => 106.2600,
                    'coordinates_label' => '5°49\'12"S, 106°15\'36"E',
                    'depth_meters'      => '18 - 28 m',
                    'recommended_gear'  => 'Jaring insang, pancing ulur',
                    'status'            => 'Aktif',
                    'description'       => 'Perairan selatan Pulau Tunda, arus lebih tenang dan terlindung gelombang saat musim barat.',
                ],
            ];
            foreach ($grounds as $g) {
                FishingGround::create($g);
            }
        }

        // 4. Terumbu Karang
        if (CoralReef::count() === 0) {
            $reefs = [
                [
                    'name'             => 'Terumbu Karang Barat',
                    'latitude'         => -5.8050,
                    'longitude'        => 106.2720,
                    'condition_status' => 'Baik',
                    'coverage_percent' => 72.50,
                    'friendly_gear'    => 'Pancing tangan (ramah lingkungan), bubu selektif',
                    'description'      => 'Ekosistem terumbu karang tepi (fringing reef) sehat, didominasi Acropora dan Porites, kawasan nursery ground utama.',
                ],
                [
                    'name'             => 'Terumbu Karang Timur',
                    'latitude'         => -5.8130,
                    'longitude'        => 106.2780,
                    'condition_status' => 'Sedang',
                    'coverage_percent' => 54.00,
                    'friendly_gear'    => 'Pancing tangan, jaring insang bermata besar (>3 inch)',
                    'description'      => 'Zona transisi dengan tutupan karang hidup kategori sedang, memerlukan monitoring berkala dari aktivitas perikanan destruktif.',
                ],
            ];
            foreach ($reefs as $r) {
                CoralReef::create($r);
            }
        }

        // 5. Alat Tangkap
        if (FishingGear::count() === 0) {
            $gears = [
                [
                    'name'              => 'Pancing Ulur (Handline)',
                    'category'          => 'Ramah Lingkungan',
                    'target_species'    => 'Scomberomorus commerson (Tenggiri)',
                    'selectivity_level' => 'Tinggi (Target Tertentu)',
                    'status'            => 'Direkomendasikan',
                    'description'       => 'Alat tangkap pasif yang sangat selektif ukuran dan spesies tanpa menghasilkan bycatch masif. Tidak merusak struktur koloni terumbu karang jika dioperasikan pada kolom air (midwater drift).',
                ],
                [
                    'name'              => 'Pancing Tonda (Troll Line)',
                    'category'          => 'Ramah Lingkungan',
                    'target_species'    => 'Ikan Pelagis Cepat (Tenggiri, Tongkol)',
                    'selectivity_level' => 'Sangat Tinggi (Permukaan)',
                    'status'            => 'Direkomendasikan',
                    'description'       => 'Alat tangkap aktif permukaan yang ditarik perahu (kecepatan 3–5 knot). Sangat efektif dan selektif untuk pemangsa pelagis cepat di lapisan atas, tanpa interaksi fisik dengan substrat dasar laut.',
                ],
                [
                    'name'              => 'Rawai (Longline)',
                    'category'          => 'Selektif Bersyarat',
                    'target_species'    => 'Ikan Pelagis Besar',
                    'selectivity_level' => 'Sedang – Tinggi',
                    'status'            => 'Direkomendasikan Bersyarat',
                    'description'       => 'Memiliki kapasitas tangkap tinggi untuk ikan pelagis besar. Memiliki risiko bentik bila kail menyentuh karang. Wajib dioperasikan sebagai rawai apung pelagis di atas rataan karang.',
                ],
            ];
            foreach ($gears as $gear) {
                FishingGear::create($gear);
            }
        }
    }
}
