{{-- ================================================================
     HALAMAN GRAFIK PARAMETER OSEANOGRAFI SCOMAR
     File: resources/views/charts/oceanographic.blade.php

     Fitur Halaman:
     1. 4 Kartu Metrik Status Terkini (Suhu, Klorofil-a, TSS, Kecepatan Arus)
     2. 4 Grafik Terpisah (Chart.js Grid 2x2):
        - Grafik 1: Suhu Permukaan Laut (SPL / SST) (°C)
        - Grafik 2: Konsentrasi Klorofil-a (mg/m³)
        - Grafik 3: Total Suspended Solids / TSS (mg/L)
        - Grafik 4: Kecepatan Arus Laut (m/s)
     3. Sumbu-X: Tahun / Periode Pengamatan (2021 s/d 2026)
     4. Sumbu-Y: Angka pengukuran riil masing-masing parameter
     5. Fitur Pop-Up Interaktif Modal: Menampilkan informasi angka fix/pasti
        dari parameter di tahun yang dipilih saat titik grafik atau tombol diklik
     6. Tabel Ringkasan Rekapitulasi Data Angka Fix Tahunan
     7. Fitur Unduh Laporan PDF (Clean Print Stylesheet)
================================================================ --}}

@extends('layouts.app')

{{-- Judul tab browser --}}
@section('title', 'Parameter Oseanografi')

{{-- Judul halaman & breadcrumb di navbar --}}
@section('page-title', 'Parameter Oseanografi')
@section('page-breadcrumb', 'SCOMAR / Parameter Oseanografi')

@section('content')

<div
    class="ocean-container"
    id="exportPdfArea"
    x-data="{
        // ── DATABASE PARAMETER OSEANOGRAFI PULAU TUNDA (ANGKA FIX PER TAHUN) ──
        // Data pengukuran tahunan (Suhu, Klorofil-a, TSS, Arus)
        // ── DATABASE PARAMETER OSEANOGRAFI PULAU TUNDA DARI MYSQL ──────
        oceanData: {{ Js::from($oceanDataForAlpine) }},
        searchOcean: '',

        // ── KONTROL STATE MODAL CRUD ───────────────────────────────────
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false,
        activeItem: {
            id: '',
            year: '2026',
            temp: 29.60,
            chlorophyll: 0.88,
            tss: 14.20,
            current: 0.35,
            status: 'Subur / Optimal',
            habitatScore: 'Optimal',
            source: 'Pemantauan Terkini SCOMAR UPI',
            notes: ''
        },

        openEdit(item) {
            this.activeItem = { ...item };
            this.showEditModal = true;
        },
        openDelete(item) {
            this.activeItem = { ...item };
            this.showDeleteModal = true;
        },

        filteredOcean() {
            if (!this.searchOcean || this.searchOcean.trim() === '') {
                return this.oceanData;
            }
            const q = this.searchOcean.toLowerCase();
            return this.oceanData.filter(d =>
                String(d.year).includes(q) ||
                (d.habitatScore && d.habitatScore.toLowerCase().includes(q)) ||
                (d.source && d.source.toLowerCase().includes(q))
            );
        },

        showFixModal: false,
        selectedYearData: null,

        // Buka Pop-up untuk tahun tertentu
        openFixDetail(year) {
            const targetYear = String(year || (this.selectedYearData ? this.selectedYearData.year : this.selectedYear) || '2026');
            const found = this.oceanData.find(d => String(d.year) === targetYear);
            if (found) {
                this.selectedYearData = found;
                this.showFixModal = true;
            }
        },
        // ── KONTROL FILTER TAHUN AKTIF & DATA MUSIMAN ──────────────────
        selectedYear: {{ Js::from($years->first() ?? '2026') }},
        availableYears: {{ Js::from($years ?? ['2026', '2025', '2024', '2023', '2022', '2021']) }},

        // Data musiman SPL bersumber dinamis dari 4 file CSV di storage/app/private/datasets/
        seasonalSplData: {{ Js::from($seasonalSplFromCsv ?? []) }},
        seasonalSplComparison: {{ Js::from($seasonalSplComparison ?? []) }},
        splViewMode: 'year', // 'year' (Per Musim Tahun Aktif) | 'comparison' (Komparasi 4 Musim 2021-2026)

        setSplMode(mode) {
            this.splViewMode = mode;
            if (window.switchSplViewMode) {
                window.switchSplViewMode(mode);
            }
        },

        // Data musiman Klorofil-a bersumber dinamis dari 4 file CSV di storage/app/private/datasets/klorofil-a/
        seasonalChlaData: {{ Js::from($seasonalChlaFromCsv ?? []) }},
        seasonalChlaComparison: {{ Js::from($seasonalChlaComparison ?? []) }},
        chlaViewMode: 'comparison', // 'comparison' (Komparasi 4 Musim 2021-2026) | 'year' (Per Musim Tahun Aktif)

        setChlaMode(mode) {
            this.chlaViewMode = mode;
            if (window.switchChlaViewMode) {
                window.switchChlaViewMode(mode);
            }
        },

        // Data musiman Kecepatan & Arah Arus bersumber dinamis dari data_arus_clean.csv di storage/app/private/datasets/arus/
        seasonalCurrentData: {{ Js::from($seasonalCurrentFromCsv ?? []) }},
        seasonalCurrentComparison: {{ Js::from($seasonalCurrentComparison ?? []) }},
        currentDetailsByYear: {{ Js::from($currentDetailsByYear ?? []) }},
        currentViewMode: 'comparison', // 'comparison' (Komparasi 4 Musim 2021-2026) | 'year' (Per Musim Tahun Aktif)

        setCurrentMode(mode) {
            this.currentViewMode = mode;
            if (window.switchCurrentViewMode) {
                window.switchCurrentViewMode(mode);
            }
        },

        // Data musiman TSS bersumber dinamis dari rata-rata nilai tss.csv di storage/app/private/datasets/tss/
        seasonalTssData: {{ Js::from($seasonalTssFromCsv ?? []) }},
        seasonalTssComparison: {{ Js::from($seasonalTssComparison ?? []) }},
        tssAnnualAverages: {{ Js::from($tssAnnualAverages ?? []) }},
        tssOverallStats: {{ Js::from($tssOverallStats ?? []) }},
        seasonalRecap24: {{ Js::from($seasonalRecap24 ?? []) }},
        tssViewMode: 'comparison', // 'comparison' (Komparasi 4 Musim 2021-2026) | 'year' (Per Musim Tahun Aktif)
        tableRecapMode: 'annual', // 'annual' (6 Periode) | 'seasonal' (24 Periode)

        setTssMode(mode) {
            this.tssViewMode = mode;
            if (window.switchTssViewMode) {
                window.switchTssViewMode(mode);
            }
        },

        hasSeasonalTssData(year = null) {
            const yr = String(year || this.selectedYear);
            return Boolean(this.seasonalTssData && this.seasonalTssData[yr] && this.seasonalTssData[yr].some(v => v !== null));
        },

        getTssStats() {
            const yr = String(this.selectedYear || '2026');
            const dataYear = (this.seasonalTssData && this.seasonalTssData[yr]) ? this.seasonalTssData[yr] : null;
            if (!dataYear) {
                return { latest: '-', avg: '-', max: '-', min: '-', latestSeason: '-', minSeason: '-', maxSeason: '-', trend: 'stable', trendLabel: 'Stabil', trendClass: 'trend-stable', diffFormatted: '0.00' };
            }
            const seasons = ['Musim Barat', 'Peralihan I', 'Musim Timur', 'Peralihan II'];
            const valid = [];
            dataYear.forEach((v, idx) => {
                if (v !== null && !isNaN(v)) valid.push({ val: parseFloat(v), season: seasons[idx], idx: idx });
            });
            if (valid.length === 0) return { latest: '-', avg: '-', max: '-', min: '-', latestSeason: '-', minSeason: '-', maxSeason: '-', trend: 'stable', trendLabel: 'Stabil', trendClass: 'trend-stable', diffFormatted: '0.00' };
            const latest = valid[valid.length - 1];
            let minVal = Infinity, maxVal = -Infinity;
            let minSeason = '', maxSeason = '';
            valid.forEach(item => {
                if (item.val > maxVal) { maxVal = item.val; maxSeason = item.season; }
                if (item.val < minVal) { minVal = item.val; minSeason = item.season; }
            });
            const sum = valid.reduce((a, b) => a + b.val, 0);
            const avg = sum / valid.length;
            const diff = maxVal - minVal;
            return {
                latest: latest.val.toFixed(2),
                latestSeason: latest.season,
                avg: avg.toFixed(2),
                max: maxVal.toFixed(2),
                maxSeason: maxSeason,
                min: minVal.toFixed(2),
                minSeason: minSeason,
                diffFormatted: (diff > 0 ? '+' : '') + diff.toFixed(2),
                trend: diff > 2.0 ? 'increasing' : 'stable',
                trendLabel: 'Variasi Musiman',
                trendClass: diff > 2.0 ? 'trend-increasing' : 'trend-stable'
            };
        },

        getTssComparisonStats() {
            if (!this.seasonalTssComparison || !this.seasonalTssComparison.series) {
                return { latest: '26.52', avg: '14.69', max: '26.52', min: '6.44', maxYear: '2026', minYear: '2022' };
            }
            const s = this.seasonalTssComparison.series;
            const allVals = [];
            let minVal = Infinity, maxVal = -Infinity;
            let minYear = '2022', maxYear = '2026';
            const years = this.seasonalTssComparison.years || ['2021', '2022', '2023', '2024', '2025', '2026'];

            ['barat', 'peralihan1', 'timur', 'peralihan2'].forEach(k => {
                if (s[k] && s[k].data) {
                    s[k].data.forEach((v, idx) => {
                        if (v !== null && !isNaN(v)) {
                            const num = parseFloat(v);
                            allVals.push(num);
                            if (num > maxVal) { maxVal = num; maxYear = years[idx] || '2026'; }
                            if (num < minVal) { minVal = num; minYear = years[idx] || '2022'; }
                        }
                    });
                }
            });
            if (allVals.length === 0) return { latest: '-', avg: '-', max: '-', min: '-', maxYear: '-', minYear: '-' };
            const avg = allVals.reduce((a, b) => a + b, 0) / allVals.length;
            const yr2026 = this.seasonalTssData && this.seasonalTssData['2026'] ? this.seasonalTssData['2026'] : null;
            let latest = '-';
            if (yr2026) {
                for (let i = yr2026.length - 1; i >= 0; i--) {
                    if (yr2026[i] !== null && !isNaN(yr2026[i])) {
                        latest = parseFloat(yr2026[i]).toFixed(2);
                        break;
                    }
                }
            }
            return {
                latest: latest !== '-' ? latest : (allVals[allVals.length - 1]).toFixed(2),
                avg: avg.toFixed(2),
                max: maxVal.toFixed(2),
                maxYear: maxYear,
                min: minVal.toFixed(2),
                minYear: minYear
            };
        },

        getSeasonalRecapForYear(year = null) {
            const yr = String(year || (this.selectedYearData ? this.selectedYearData.year : this.selectedYear) || '2026');
            if (!this.seasonalRecap24 || !Array.isArray(this.seasonalRecap24)) return [];
            return this.seasonalRecap24
                .filter(r => String(r.year) === yr)
                .sort((a, b) => Number(a.season_idx) - Number(b.season_idx));
        },

        filteredSeasonalRecap() {
            if (!this.seasonalRecap24) return [];
            if (!this.searchOcean || this.searchOcean.trim() === '') {
                return this.seasonalRecap24;
            }
            const q = this.searchOcean.toLowerCase();
            return this.seasonalRecap24.filter(r =>
                r.period_label.toLowerCase().includes(q) ||
                r.season_name.toLowerCase().includes(q) ||
                r.season_code.toLowerCase().includes(q) ||
                r.year.includes(q) ||
                r.habitatScore.toLowerCase().includes(q)
            );
        },

        formatNullVal(val, unit = '', decimals = 2) {
            if (val === null || val === undefined || isNaN(val)) {
                return 'Null';
            }
            return parseFloat(val).toFixed(decimals) + (unit ? ' ' + unit : '');
        },

        getParamBadgeClass(status) {
            if (status === 'Optimal') return 'badge-optimal';
            if (status === 'Tidak Optimal') return 'badge-suboptimal';
            if (status === 'Di Atas Optimal') return 'badge-above-optimal';
            return 'badge-unavailable';
        },

        getHabitatBadgeClass(score) {
            if (score === 'Optimal') return 'badge-score-optimal';
            if (score === 'Belum Optimal') return 'badge-score-suboptimal';
            return 'badge-score-pending';
        },

        getCurrentDetails(year = null) {
            const yr = String(year || (this.selectedYearData ? this.selectedYearData.year : this.selectedYear) || '2026');
            if (!this.currentDetailsByYear) return [];
            const details = this.currentDetailsByYear[yr] || this.currentDetailsByYear[Number(yr)] || [];
            return Array.isArray(details) ? details : Object.values(details);
        },

        getCurrentStats() {
            const yr = String(this.selectedYear || '2026');
            const dataYear = (this.seasonalCurrentData && this.seasonalCurrentData[yr]) ? this.seasonalCurrentData[yr] : null;
            if (!dataYear) {
                return { latest: '0.35', avg: '0.34', max: '0.42', min: '0.28', latestSeason: 'Peralihan II', minSeason: 'Barat', maxSeason: 'Timur', trend: 'stable', trendLabel: 'Stabil', trendClass: 'trend-stable', diffFormatted: '0.00', pctFormatted: '0.0%' };
            }
            const seasons = ['Musim Barat', 'Peralihan I', 'Musim Timur', 'Peralihan II'];
            const valid = [];
            dataYear.forEach((v, idx) => {
                if (v !== null && !isNaN(v)) valid.push({ val: parseFloat(v), season: seasons[idx], idx: idx });
            });
            if (valid.length === 0) return { latest: '-', avg: '-', max: '-', min: '-', latestSeason: '-', minSeason: '-', maxSeason: '-', trend: 'stable', trendLabel: 'Stabil', trendClass: 'trend-stable', diffFormatted: '0.00', pctFormatted: '0.0%' };
            const latest = valid[valid.length - 1];
            let minVal = Infinity, maxVal = -Infinity;
            let minSeason = '', maxSeason = '';
            valid.forEach(item => {
                if (item.val > maxVal) { maxVal = item.val; maxSeason = item.season; }
                if (item.val < minVal) { minVal = item.val; minSeason = item.season; }
            });
            const sum = valid.reduce((a, b) => a + b.val, 0);
            const avg = sum / valid.length;
            const diff = maxVal - minVal;
            return {
                latest: latest.val.toFixed(3),
                latestSeason: latest.season,
                avg: avg.toFixed(3),
                max: maxVal.toFixed(3),
                maxSeason: maxSeason,
                min: minVal.toFixed(3),
                minSeason: minSeason,
                diffFormatted: (diff > 0 ? '+' : '') + diff.toFixed(3),
                pctFormatted: minVal > 0 ? (((maxVal - minVal) / minVal) * 100).toFixed(1) + '%' : '0.0%',
                trend: diff > 0.05 ? 'increasing' : 'stable',
                trendLabel: 'Variasi Musiman',
                trendClass: diff > 0.05 ? 'trend-increasing' : 'trend-stable'
            };
        },

        getCurrentComparisonStats() {
            if (!this.seasonalCurrentComparison || !this.seasonalCurrentComparison.series) {
                return { latest: '0.375', avg: '0.198', max: '0.424', min: '0.011' };
            }
            const s = this.seasonalCurrentComparison.series;
            const allVals = [];
            ['barat', 'peralihan1', 'timur', 'peralihan2'].forEach(k => {
                if (s[k] && s[k].data) {
                    s[k].data.forEach(v => {
                        if (v !== null && !isNaN(v)) allVals.push(parseFloat(v));
                    });
                }
            });
            if (allVals.length === 0) return { latest: '-', avg: '-', max: '-', min: '-' };
            const min = Math.min(...allVals);
            const max = Math.max(...allVals);
            const avg = allVals.reduce((a, b) => a + b, 0) / allVals.length;
            const yr2026 = this.seasonalCurrentData && this.seasonalCurrentData['2026'] ? this.seasonalCurrentData['2026'] : null;
            let latest = '-';
            if (yr2026) {
                for (let i = yr2026.length - 1; i >= 0; i--) {
                    if (yr2026[i] !== null && !isNaN(yr2026[i])) {
                        latest = parseFloat(yr2026[i]).toFixed(3);
                        break;
                    }
                }
            }
            return {
                latest: latest !== '-' ? latest : (allVals[allVals.length - 1]).toFixed(3),
                avg: avg.toFixed(3),
                max: max.toFixed(3),
                min: min.toFixed(3)
            };
        },

        changeYear(year) {
            this.selectedYear = String(year);
            if (window.updateOceanCharts) {
                window.updateOceanCharts(year);
            }
        },

        hasSeasonalData(year = null) {
            const yr = String(year || this.selectedYear);
            return Boolean(this.seasonalSplData && this.seasonalSplData[yr]);
        },

        hasSeasonalChlaData(year = null) {
            const yr = String(year || this.selectedYear);
            return Boolean(this.seasonalChlaData && this.seasonalChlaData[yr] && this.seasonalChlaData[yr].some(v => v !== null));
        },

        getChlaComparisonStats() {
            if (!this.seasonalChlaComparison || !this.seasonalChlaComparison.series) {
                return { latest: '0.998', avg: '0.865', max: '1.208', min: '0.627' };
            }
            const s = this.seasonalChlaComparison.series;
            const allVals = [];
            ['barat', 'peralihan1', 'timur', 'peralihan2'].forEach(k => {
                if (s[k] && s[k].data) {
                    s[k].data.forEach(v => {
                        if (v !== null && !isNaN(v)) allVals.push(parseFloat(v));
                    });
                }
            });
            if (allVals.length === 0) {
                return { latest: '-', avg: '-', max: '-', min: '-' };
            }
            const min = Math.min(...allVals);
            const max = Math.max(...allVals);
            const avg = allVals.reduce((a, b) => a + b, 0) / allVals.length;
            const yr2026 = this.seasonalChlaData && this.seasonalChlaData['2026'] ? this.seasonalChlaData['2026'] : null;
            let latest = '-';
            if (yr2026) {
                for (let i = yr2026.length - 1; i >= 0; i--) {
                    if (yr2026[i] !== null && !isNaN(yr2026[i])) {
                        latest = parseFloat(yr2026[i]).toFixed(3);
                        break;
                    }
                }
            }
            return {
                latest: latest !== '-' ? latest : (allVals[allVals.length - 1]).toFixed(3),
                avg: avg.toFixed(3),
                max: max.toFixed(3),
                min: min.toFixed(3)
            };
        },

        getSeasonalStats(field) {
            const yr = String(this.selectedYear || '2026');
            const isSpl = field === 'temp';
            const dataset = isSpl ? this.seasonalSplData : this.seasonalChlaData;
            const unit = isSpl ? '°C' : 'mg/m³';

            // 1. Jika ada data musiman untuk tahun ini (2021 s/d 2026 dari CSV)
            if (dataset && dataset[yr]) {
                const vals = dataset[yr];
                const seasons = ['Musim Barat', 'Peralihan I', 'Musim Timur', 'Peralihan II'];
                const validPairs = [];
                vals.forEach((v, idx) => {
                    if (v !== null && !isNaN(v)) {
                        validPairs.push({ val: parseFloat(v), season: seasons[idx], idx: idx });
                    }
                });

                if (validPairs.length > 0) {
                    const latestItem = validPairs[validPairs.length - 1];
                    let minVal = Infinity, maxVal = -Infinity;
                    let minSeason = '', maxSeason = '';
                    validPairs.forEach(item => {
                        if (item.val > maxVal) { maxVal = item.val; maxSeason = item.season; }
                        if (item.val < minVal) { minVal = item.val; minSeason = item.season; }
                    });

                    const sum = validPairs.reduce((a, b) => a + b.val, 0);
                    const avg = sum / validPairs.length;

                    // Perbandingan Musim Timur (idx 2) vs Musim Barat (idx 0)
                    const firstVal = (vals[0] !== null && vals[0] !== undefined) ? parseFloat(vals[0]) : validPairs[0].val;
                    const peakVal  = (vals[2] !== null && vals[2] !== undefined) ? parseFloat(vals[2]) : maxVal;
                    const diff = peakVal - firstVal;
                    const diffFormatted = (diff > 0 ? '+' : '') + diff.toFixed(2);
                    const pctChange = firstVal !== 0 ? (diff / firstVal) * 100 : 0;
                    const pctFormatted = (pctChange > 0 ? '+' : '') + pctChange.toFixed(1) + '%';

                    let trend = 'stable', trendLabel = 'Stabil', trendClass = 'trend-stable';
                    if (diff > 0.05) {
                        trend = 'increasing'; trendLabel = 'Puncak Musim Timur'; trendClass = 'trend-increasing';
                    } else if (diff < -0.05) {
                        trend = 'decreasing'; trendLabel = 'Menurun'; trendClass = 'trend-decreasing';
                    }

                    return {
                        hasSeasonal: true,
                        latest: latestItem.val.toFixed(2),
                        latestSeason: latestItem.season,
                        avg: avg.toFixed(2),
                        max: maxVal.toFixed(2),
                        maxSeason: maxSeason,
                        min: minVal.toFixed(2),
                        minSeason: minSeason,
                        diffFormatted: diffFormatted,
                        pctFormatted: pctFormatted,
                        trend: trend,
                        trendLabel: trendLabel,
                        trendClass: trendClass
                    };
                }
            }

            // 2. Jika TIDAK ADA data musiman (misal 2021, 2022, 2023):
            // Ambil data tahunan fix yang sebenarnya dari database!
            const annualItem = this.oceanData.find(d => String(d.year) === yr);
            const val = annualItem ? parseFloat(annualItem[field]) : 0;
            return {
                hasSeasonal: false,
                latest: val ? val.toFixed(2) : '-',
                latestSeason: 'Tahunan Fix',
                avg: val ? val.toFixed(2) : '-',
                max: val ? val.toFixed(2) : '-',
                maxSeason: '-',
                min: val ? val.toFixed(2) : '-',
                minSeason: '-',
                diffFormatted: '0.00',
                pctFormatted: '0.0%',
                trend: 'stable',
                trendLabel: 'Tahunan Fix',
                trendClass: 'trend-stable'
            };
        },

        // Ambil data tahun terakhir untuk kartu ringkasan
        getLatest() {
            if (!this.oceanData || this.oceanData.length === 0) {
                return { temp: 0, chlorophyll: 0, tss: 0, current: 0, year: '-' };
            }
            return this.oceanData[this.oceanData.length - 1];
        },

        // ── STATISTICAL SUMMARY & TREND ANALYSIS OTOMATIS (DATA AKTUAL) ──
        getStats(field, year = null) {
            if (!this.oceanData || this.oceanData.length === 0) {
                return {
                    latest: '0.00', latestYear: '-',
                    prev: '0.00', prevYear: '-',
                    diff: 0, diffFormatted: '0.00',
                    pctChange: 0, pctFormatted: '0.0%',
                    trend: 'stable', trendLabel: 'Stabil', trendClass: 'trend-stable',
                    avg: '0.00', max: '0.00', maxYear: '-', min: '0.00', minYear: '-'
                };
            }

            const targetYear = String(year || this.selectedYear);
            let targetIdx = this.oceanData.findIndex(d => String(d.year) === targetYear);
            if (targetIdx === -1) {
                targetIdx = this.oceanData.length - 1;
            }

            const len = this.oceanData.length;
            const latestObj = this.oceanData[targetIdx];
            const latestVal = parseFloat(latestObj[field]) || 0;

            const prevObj = targetIdx > 0 ? this.oceanData[targetIdx - 1] : latestObj;
            const prevVal = parseFloat(prevObj[field]) || 0;

            const diff = latestVal - prevVal;
            const diffFormatted = (diff > 0 ? '+' : '') + diff.toFixed(2);

            let pctChange = 0;
            if (prevVal !== 0 && targetIdx > 0) {
                pctChange = (diff / Math.abs(prevVal)) * 100;
            }
            const pctFormatted = (pctChange > 0 ? '+' : '') + pctChange.toFixed(2) + '%';

            let trend = 'stable';
            let trendLabel = 'Stabil';
            let trendClass = 'trend-stable';

            if (Math.abs(diff) >= 0.005 && targetIdx > 0) {
                if (diff > 0) {
                    trend = 'increasing';
                    trendLabel = 'Meningkat';
                    trendClass = 'trend-increasing';
                } else {
                    trend = 'decreasing';
                    trendLabel = 'Menurun';
                    trendClass = 'trend-decreasing';
                }
            }

            let sum = 0;
            let maxVal = -Infinity;
            let maxYear = '-';
            let minVal = Infinity;
            let minYear = '-';

            this.oceanData.forEach(d => {
                const v = parseFloat(d[field]) || 0;
                sum += v;
                if (v > maxVal) {
                    maxVal = v;
                    maxYear = d.year;
                }
                if (v < minVal) {
                    minVal = v;
                    minYear = d.year;
                }
            });

            const avg = sum / len;

            return {
                latest: latestVal.toFixed(2),
                latestYear: latestObj.year,
                prev: prevVal.toFixed(2),
                prevYear: prevObj.year,
                diff: diff,
                diffFormatted: diffFormatted,
                pctChange: pctChange,
                pctFormatted: pctFormatted,
                trend: trend,
                trendLabel: trendLabel,
                trendClass: trendClass,
                avg: avg.toFixed(2),
                max: maxVal.toFixed(2),
                maxYear: maxYear,
                min: minVal.toFixed(2),
                minYear: minYear
            };
        }
    }"
>

    {{-- ============================================================
         1. HEADER HALAMAN & ACTIONS
    ============================================================ --}}
    <div class="page-header-row">
        <div class="page-header-left">
            <h1 class="page-title">
                Grafik Parameter Oseanografi
            </h1>
            <p class="page-subtitle">
                Pemantauan data oseanografi perairan Pulau Tunda: Suhu Permukaan Laut (SPL), Konsentrasi Klorofil-a, <em>Total Suspended Solids</em> (TSS), dan Kecepatan Arus Laut.
            </p>
        </div>

        <div class="page-header-actions no-print">
            {{-- Tombol Ekspor CSV --}}
            <button
                type="button"
                id="btnExportOceanCsv"
                class="btn-scomar btn-outline"
                onclick="exportOceanToCsv()"
                title="Unduh data parameter oseanografi dalam format CSV (Excel Ready)"
            >
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span>Ekspor CSV</span>
            </button>

            {{-- Tombol Unduh Laporan PDF --}}
            <button
                type="button"
                id="btnDownloadPdf"
                class="btn-scomar btn-primary"
                onclick="exportOceanToPdf()"
                title="Unduh langsung laporan parameter oseanografi dalam format PDF"
            >
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>Unduh Laporan (PDF)</span>
            </button>
        </div>
    </div>

    {{-- ============================================================
         2. BANNER STATUS DEMONSTRASI & PETUNJUK POP-UP
    ============================================================ --}}
    <div class="status-banner no-print">
        <div class="status-banner-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
        </div>
        <div class="status-banner-text">
            <strong>Petunjuk Fitur Interaktif:</strong> Anda dapat <strong>mengeklik titik grafik</strong> pada tahun mana saja atau menekan tombol <strong>"Lihat Angka Fix"</strong> pada kartu grafik untuk memunculkan pop-up rincian angka pasti (*fix value*).
        </div>
    </div>

    {{-- ============================================================
         3. 4 KARTU METRIK STATUS PARAMETER TERKINI
         - Menampilkan nilai aktual tahun terakhir, tren dinamis, & konteks baku mutu
    ============================================================ --}}
    <div class="metrics-grid">

        {{-- Kartu 1: Suhu Permukaan Laut --}}
        <div class="metric-card ocean-temp">
            <div class="metric-icon-box merlot">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path>
                </svg>
            </div>
            <div class="metric-body">
                <div class="metric-header-row">
                    <span class="metric-label">Suhu Permukaan Laut (SPL)</span>
                    <span class="metric-period-pill" x-text="'Thn ' + getStats('temp', selectedYear).latestYear">Thn 2026</span>
                </div>
                <div class="metric-value-row">
                    <span class="metric-value">
                        <span x-text="getStats('temp', selectedYear).latest">29.60</span>
                        <small>°C</small>
                    </span>
                    <div class="trend-pill" :class="getStats('temp', selectedYear).trendClass">
                        <template x-if="getStats('temp', selectedYear).trend === 'increasing'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </template>
                        <template x-if="getStats('temp', selectedYear).trend === 'decreasing'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </template>
                        <template x-if="getStats('temp', selectedYear).trend === 'stable'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </template>
                        <span x-text="getStats('temp', selectedYear).pctFormatted + ' vs ' + getStats('temp', selectedYear).prevYear"></span>
                    </div>
                </div>
                <div class="metric-context-bar">
                    <span class="metric-context-dot optimal"></span>
                    <span class="metric-context-text">Rentang Optimal: 28.0 - 30.5 °C</span>
                </div>
            </div>
        </div>

        {{-- Kartu 2: Klorofil-a --}}
        <div class="metric-card ocean-chla">
            <div class="metric-icon-box teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    <path d="M2 12h20"></path>
                </svg>
            </div>
            <div class="metric-body">
                <div class="metric-header-row">
                    <span class="metric-label">Konsentrasi Klorofil-a</span>
                    <span class="metric-period-pill" x-text="'Thn ' + getStats('chlorophyll', selectedYear).latestYear">Thn 2026</span>
                </div>
                <div class="metric-value-row">
                    <span class="metric-value">
                        <span x-text="getStats('chlorophyll', selectedYear).latest">0.88</span>
                        <small>mg/m³</small>
                    </span>
                    <div class="trend-pill" :class="getStats('chlorophyll', selectedYear).trendClass">
                        <template x-if="getStats('chlorophyll', selectedYear).trend === 'increasing'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </template>
                        <template x-if="getStats('chlorophyll', selectedYear).trend === 'decreasing'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </template>
                        <template x-if="getStats('chlorophyll', selectedYear).trend === 'stable'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </template>
                        <span x-text="getStats('chlorophyll', selectedYear).pctFormatted + ' vs ' + getStats('chlorophyll', selectedYear).prevYear"></span>
                    </div>
                </div>
                <div class="metric-context-bar">
                    <span class="metric-context-dot optimal"></span>
                    <span class="metric-context-text">Rentang Optimal: 0.1 – 5.0 mg/m³</span>
                </div>
            </div>
        </div>

        {{-- Kartu 3: Total Suspended Solids (TSS) --}}
        <div class="metric-card ocean-tss">
            <div class="metric-icon-box amber">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="metric-body">
                <div class="metric-header-row">
                    <span class="metric-label">Padatan Tersuspensi (TSS)</span>
                    <span class="metric-period-pill" x-text="'Thn ' + getStats('tss', selectedYear).latestYear">Thn 2026</span>
                </div>
                <div class="metric-value-row">
                    <span class="metric-value">
                        <span x-text="getStats('tss', selectedYear).latest">14.20</span>
                        <small>mg/L</small>
                    </span>
                    <div class="trend-pill" :class="getStats('tss', selectedYear).trendClass">
                        <template x-if="getStats('tss', selectedYear).trend === 'increasing'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </template>
                        <template x-if="getStats('tss', selectedYear).trend === 'decreasing'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </template>
                        <template x-if="getStats('tss', selectedYear).trend === 'stable'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </template>
                        <span x-text="getStats('tss', selectedYear).pctFormatted + ' vs ' + getStats('tss', selectedYear).prevYear"></span>
                    </div>
                </div>
                <div class="metric-context-bar">
                    <span class="metric-context-dot safe"></span>
                    <span class="metric-context-text">Baku Mutu Aman (&lt; 20 mg/L)</span>
                </div>
            </div>
        </div>

        {{-- Kartu 4: Kecepatan Arus --}}
        <div class="metric-card ocean-current">
            <div class="metric-icon-box navy">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"></path>
                    <path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"></path>
                    <path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"></path>
                </svg>
            </div>
            <div class="metric-body">
                <div class="metric-header-row">
                    <span class="metric-label">Kecepatan Arus Laut</span>
                    <span class="metric-period-pill" x-text="'Thn ' + getStats('current', selectedYear).latestYear">Thn 2026</span>
                </div>
                <div class="metric-value-row">
                    <span class="metric-value">
                        <span x-text="getStats('current', selectedYear).latest">0.35</span>
                        <small>m/s</small>
                    </span>
                    <div class="trend-pill" :class="getStats('current', selectedYear).trendClass">
                        <template x-if="getStats('current', selectedYear).trend === 'increasing'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </template>
                        <template x-if="getStats('current', selectedYear).trend === 'decreasing'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </template>
                        <template x-if="getStats('current', selectedYear).trend === 'stable'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </template>
                        <span x-text="getStats('current', selectedYear).pctFormatted + ' vs ' + getStats('current', selectedYear).prevYear"></span>
                    </div>
                </div>
                <div class="metric-context-bar">
                    <span class="metric-context-dot moderate"></span>
                    <span class="metric-context-text">Rentang Optimal: 0.1 – 0.5 m/s</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================================
         SECTION FILTER: ANALISIS PARAMETER OSEANOGRAFI BERDASARKAN TAHUN & MUSIM
         + DROPDOWN PEMILIHAN TAHUN
    ============================================================ --}}
    <div class="filter-card no-print">
        <div class="filter-header">
            <div class="filter-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Analisis Parameter Oseanografi Berdasarkan Tahun dan Musim</span>
            </div>
            <div class="filter-status-info">
                Tahun Pengamatan Aktif: <strong x-text="selectedYear">2026</strong>
            </div>
        </div>

        <div class="seasonal-filter-toolbar">
            <div class="seasonal-filter-group">
                <span class="seasonal-filter-label">Pilih Tahun Pengamatan:</span>
                {{-- ── DROPDOWN TAHUN PENGAMATAN ───────────────────────── --}}
                <div class="year-dropdown-wrapper" x-data="{ open: false }" @click.outside="open = false">
                    <button
                        type="button"
                        class="year-dropdown-trigger"
                        @click="open = !open"
                        :aria-expanded="open"
                        aria-haspopup="listbox"
                    >
                        <span class="year-dropdown-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </span>
                        <span class="year-dropdown-label" x-text="'Tahun ' + selectedYear">Tahun 2026</span>
                        <span class="year-dropdown-chevron" :class="{ 'rotated': open }">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </button>

                    <div
                        class="year-dropdown-menu"
                        x-show="open"
                        x-transition:enter="dropdown-enter"
                        x-transition:enter-start="dropdown-enter-start"
                        x-transition:enter-end="dropdown-enter-end"
                        x-transition:leave="dropdown-leave"
                        x-transition:leave-start="dropdown-leave-start"
                        x-transition:leave-end="dropdown-leave-end"
                        role="listbox"
                        style="display:none;"
                    >
                        <template x-for="yr in availableYears" :key="yr">
                            <button
                                type="button"
                                class="year-dropdown-item"
                                :class="selectedYear === yr ? 'selected' : ''"
                                @click="changeYear(yr); open = false"
                                role="option"
                                :aria-selected="selectedYear === yr"
                            >
                                <span class="year-dropdown-item-check" x-show="selectedYear === yr">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </span>
                                <span class="year-dropdown-item-check placeholder" x-show="selectedYear !== yr"></span>
                                <span x-text="'Tahun ' + yr"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="seasonal-season-legend">
                <span class="season-badge barat">Musim Barat (Des - Feb)</span>
                <span class="season-badge peralihan1">Peralihan I (Mar - Mei)</span>
                <span class="season-badge timur">Musim Timur (Jun - Agu)</span>
                <span class="season-badge peralihan2">Peralihan II (Sep - Nov)</span>
            </div>
        </div>
    </div>

    {{-- ============================================================
         4. 4 GRAFIK PARAMETER OSEANOGRAFI (SCIENTIFIC RESEARCH SUITE)
         - Sumbu X: Tahun Pengamatan (2021 - 2026)
         - Statistical Summary: Latest, Average, Maximum, Minimum
         - Trend Analysis: Dynamically computed from actual data
         - Interaksi: Klik titik grafik memunculkan pop-up angka fix
    ============================================================ --}}
    <div class="ocean-charts-grid">

        {{-- GRAFIK 1: SUHU PERMUKAAN LAUT (SPL) --}}
        <div class="chart-card ocean-card-temp">
            <div class="chart-header">
                <div class="chart-header-top-row">
                    <div class="chart-title-badge-row">
                        <span class="chart-code-badge merlot">SPL</span>
                        <h2 class="chart-title">1. Suhu Permukaan Laut</h2>
                    </div>
                    <div class="spl-mode-toggle-group">
                        <button
                            type="button"
                            class="spl-mode-btn"
                            :class="splViewMode === 'year' ? 'active' : ''"
                            @click="setSplMode('year')"
                            title="Tampilkan variasi 4 musim untuk tahun terpilih (Sumbu X: Musim)"
                        >
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Per Musim</span>
                        </button>
                        <button
                            type="button"
                            class="spl-mode-btn"
                            :class="splViewMode === 'comparison' ? 'active' : ''"
                            @click="setSplMode('comparison')"
                            title="Tampilkan komparasi 4 musim dari 2021 sampai 2026 (4 Seri Dataset CSV)"
                        >
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                            <span>Komparasi</span>
                        </button>
                    </div>
                </div>

                <div class="chart-header-sub-row">
                    <p class="chart-subtitle">
                        <span x-show="splViewMode === 'year'">Rata-rata 4 musim perairan Pulau Tunda Tahun <strong x-text="selectedYear"></strong> (°C) terintegrasi dari dataset CSV.</span>
                        <span x-show="splViewMode === 'comparison'">Komparasi 4 musim perairan Pulau Tunda (2021–2026) dari 4 file CSV resmi.</span>
                    </p>
                    <div class="chart-header-badges">
                        <span class="chart-tag merlot">Suhu (°C)</span>
                        <div class="trend-badge" :class="getSeasonalStats('temp').trendClass" x-show="splViewMode === 'year'">
                            <template x-if="getSeasonalStats('temp').trend === 'increasing'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                            </template>
                            <template x-if="getSeasonalStats('temp').trend === 'decreasing'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </template>
                            <template x-if="getSeasonalStats('temp').trend === 'stable'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </template>
                            <span x-text="getSeasonalStats('temp').trendLabel + ' (' + getSeasonalStats('temp').diffFormatted + ' °C)'"></span>
                        </div>
                        <div class="trend-badge trend-stable" x-show="splViewMode === 'comparison'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>4 Dataset CSV</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistical Summary: Teks Minimalis Tanpa Card/Border/Shadow --}}
            <div class="stats-text-row">
                <div class="stat-text-item stat-item-prominent">
                    <div class="stat-text-label">
                        <span>Nilai Terkini</span>
                        <span class="stat-text-year" x-text="'(' + getSeasonalStats('temp').latestSeason + ')'">(Peralihan II)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num prominent burgundy" x-text="getSeasonalStats('temp').latest">29.40</span>
                        <span class="stat-text-unit">°C</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span x-show="hasSeasonalData(selectedYear)">Rata-rata 4 Musim</span>
                        <span x-show="!hasSeasonalData(selectedYear)">Rata-rata</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-text="getSeasonalStats('temp').avg">29.60</span>
                        <span class="stat-text-unit">°C</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span>Maksimum</span>
                        <span class="stat-text-year" x-show="hasSeasonalData(selectedYear)" x-text="'(' + getSeasonalStats('temp').maxSeason + ')'">(Musim Timur)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-text="getSeasonalStats('temp').max">30.80</span>
                        <span class="stat-text-unit">°C</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span>Minimum</span>
                        <span class="stat-text-year" x-show="hasSeasonalData(selectedYear)" x-text="'(' + getSeasonalStats('temp').minSeason + ')'">(Musim Barat)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-text="getSeasonalStats('temp').min">28.90</span>
                        <span class="stat-text-unit">°C</span>
                    </div>
                </div>
            </div>

            <div class="chart-canvas-wrapper" style="position: relative; height: 260px;">
                <canvas id="tempChartCanvas"></canvas>
            </div>

            {{-- Keterangan jika data musiman belum tersedia --}}
            <div class="seasonal-empty-hint" x-show="!hasSeasonalData(selectedYear)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>Data musiman SPL belum tersedia untuk <strong>Tahun <span x-text="selectedYear"></span></strong>. Rekapitulasi angka fix tahunan dapat dilihat pada tabel di bawah.</span>
            </div>

            <div class="chart-footer-hint no-print">
                <div class="chart-hint-left">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span x-show="splViewMode === 'year'">Klik titik musim pada grafik untuk melihat rincian <strong>angka fix</strong> tahun <strong x-text="selectedYear"></strong>.</span>
                    <span x-show="splViewMode === 'comparison'">Data terintegrasi dari 4 CSV: <strong>Barat</strong>, <strong>Peralihan 1</strong>, <strong>Timur</strong>, &amp; <strong>Peralihan 2</strong>.</span>
                </div>
                <button
                    type="button"
                    class="btn-quick-detail"
                    @click="openFixDetail(selectedYear)"
                    title="Buka rincian angka fix untuk tahun terpilih"
                >
                    Rincian Thn <span x-text="selectedYear"></span>
                </button>
            </div>
        </div>

        {{-- GRAFIK 2: KONSENTRASI KLOROFIL-A --}}
        <div class="chart-card ocean-card-chla">
            <div class="chart-header">
                <div class="chart-header-top-row">
                    <div class="chart-title-badge-row">
                        <span class="chart-code-badge teal">Chl-a</span>
                        <h2 class="chart-title">2. Konsentrasi Klorofil-a</h2>
                    </div>
                    <div class="spl-mode-toggle-group">
                        <button
                            type="button"
                            class="spl-mode-btn"
                            :class="chlaViewMode === 'comparison' ? 'active' : ''"
                            @click="setChlaMode('comparison')"
                            title="Tampilkan komparasi 4 musim dari 2021 sampai 2026 (4 Seri Dataset CSV)"
                        >
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                            <span>Komparasi</span>
                        </button>
                        <button
                            type="button"
                            class="spl-mode-btn"
                            :class="chlaViewMode === 'year' ? 'active' : ''"
                            @click="setChlaMode('year')"
                            title="Tampilkan variasi 4 musim untuk tahun terpilih (Sumbu X: Musim)"
                        >
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Per Musim</span>
                        </button>
                    </div>
                </div>

                <div class="chart-header-sub-row">
                    <p class="chart-subtitle">
                        <span x-show="chlaViewMode === 'comparison'">Komparasi 4 musim perairan Pulau Tunda (2021–2026) dari 4 file CSV resmi.</span>
                        <span x-show="chlaViewMode === 'year' && hasSeasonalChlaData(selectedYear)">Rata-rata 4 musim konsentrasi Klorofil-a Tahun <strong x-text="selectedYear"></strong> (mg/m³) dari dataset CSV.</span>
                        <span x-show="chlaViewMode === 'year' && !hasSeasonalChlaData(selectedYear)">Indikator biomassa fitoplankton dan produktivitas primer (mg/m³).</span>
                    </p>
                    <div class="chart-header-badges">
                        <span class="chart-tag teal">Klorofil-a (mg/m³)</span>
                        <div class="trend-badge" :class="getSeasonalStats('chlorophyll').trendClass" x-show="chlaViewMode === 'year'">
                            <template x-if="getSeasonalStats('chlorophyll').trend === 'increasing'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                            </template>
                            <template x-if="getSeasonalStats('chlorophyll').trend === 'decreasing'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </template>
                            <template x-if="getSeasonalStats('chlorophyll').trend === 'stable'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </template>
                            <span x-text="getSeasonalStats('chlorophyll').trendLabel + ' (' + getSeasonalStats('chlorophyll').diffFormatted + ' mg/m³)'"></span>
                        </div>
                        <div class="trend-badge trend-stable" x-show="chlaViewMode === 'comparison'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>4 Dataset CSV</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistical Summary: Teks Minimalis Tanpa Card/Border/Shadow --}}
            <div class="stats-text-row">
                <div class="stat-text-item stat-item-prominent">
                    <div class="stat-text-label">
                        <span>Nilai Terkini</span>
                        <span class="stat-text-year" x-show="chlaViewMode === 'year'" x-text="'(' + getSeasonalStats('chlorophyll').latestSeason + ')'">(Peralihan II)</span>
                        <span class="stat-text-year" x-show="chlaViewMode === 'comparison'">(2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num prominent emerald" x-show="chlaViewMode === 'year'" x-text="getSeasonalStats('chlorophyll').latest">0.86</span>
                        <span class="stat-text-num prominent emerald" x-show="chlaViewMode === 'comparison'" x-text="getChlaComparisonStats().latest">0.998</span>
                        <span class="stat-text-unit">mg/m³</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span x-show="chlaViewMode === 'year' && hasSeasonalChlaData(selectedYear)">Rata-rata 4 Musim</span>
                        <span x-show="chlaViewMode === 'year' && !hasSeasonalChlaData(selectedYear)">Rata-rata</span>
                        <span x-show="chlaViewMode === 'comparison'">Rata-rata (2021–2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-show="chlaViewMode === 'year'" x-text="getSeasonalStats('chlorophyll').avg">0.88</span>
                        <span class="stat-text-num" x-show="chlaViewMode === 'comparison'" x-text="getChlaComparisonStats().avg">0.865</span>
                        <span class="stat-text-unit">mg/m³</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span>Maksimum</span>
                        <span class="stat-text-year" x-show="chlaViewMode === 'year' && hasSeasonalChlaData(selectedYear)" x-text="'(' + getSeasonalStats('chlorophyll').maxSeason + ')'">(Musim Timur)</span>
                        <span class="stat-text-year" x-show="chlaViewMode === 'comparison'">(Puncak 2021–2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-show="chlaViewMode === 'year'" x-text="getSeasonalStats('chlorophyll').max">1.08</span>
                        <span class="stat-text-num" x-show="chlaViewMode === 'comparison'" x-text="getChlaComparisonStats().max">1.208</span>
                        <span class="stat-text-unit">mg/m³</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span>Minimum</span>
                        <span class="stat-text-year" x-show="chlaViewMode === 'year' && hasSeasonalChlaData(selectedYear)" x-text="'(' + getSeasonalStats('chlorophyll').minSeason + ')'">(Musim Barat)</span>
                        <span class="stat-text-year" x-show="chlaViewMode === 'comparison'">(Terendah 2021–2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-show="chlaViewMode === 'year'" x-text="getSeasonalStats('chlorophyll').min">0.72</span>
                        <span class="stat-text-num" x-show="chlaViewMode === 'comparison'" x-text="getChlaComparisonStats().min">0.627</span>
                        <span class="stat-text-unit">mg/m³</span>
                    </div>
                </div>
            </div>

            <div class="chart-canvas-wrapper" style="position: relative; height: 260px;">
                <canvas id="chlaChartCanvas"></canvas>
            </div>

            {{-- Keterangan jika data musiman belum tersedia --}}
            <div class="seasonal-empty-hint" x-show="chlaViewMode === 'year' && !hasSeasonalChlaData(selectedYear)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>Data musiman Klorofil-a belum tersedia untuk <strong>Tahun <span x-text="selectedYear"></span></strong>. Rekapitulasi angka fix tahunan dapat dilihat pada tabel di bawah.</span>
            </div>

            <div class="chart-footer-hint no-print">
                <div class="chart-hint-left">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span x-show="chlaViewMode === 'comparison'">Data terintegrasi dari 4 CSV Klorofil-a: <strong>Barat</strong>, <strong>Peralihan 1</strong>, <strong>Timur</strong>, &amp; <strong>Peralihan 2</strong>.</span>
                    <span x-show="chlaViewMode === 'year'">Klik titik musim pada grafik untuk melihat rincian <strong>angka fix</strong> tahun <strong x-text="selectedYear"></strong>.</span>
                </div>
                <button
                    type="button"
                    class="btn-quick-detail"
                    @click="openFixDetail(selectedYear)"
                    title="Buka rincian angka fix untuk tahun terpilih"
                >
                    Rincian Thn <span x-text="selectedYear"></span>
                </button>
            </div>
        </div>

        {{-- GRAFIK 3: TOTAL SUSPENDED SOLIDS (TSS) --}}
        <div class="chart-card ocean-card-tss pdf-page-break">
            <div class="chart-header">
                <div class="chart-header-top-row">
                    <div class="chart-title-badge-row">
                        <span class="chart-code-badge amber">TSS</span>
                        <h2 class="chart-title">3. Total Suspended Solids (TSS)</h2>
                    </div>
                    <div class="spl-mode-toggle-group">
                        <button
                            type="button"
                            class="spl-mode-btn"
                            :class="tssViewMode === 'comparison' ? 'active' : ''"
                            @click="setTssMode('comparison')"
                            title="Tampilkan perbandingan 4 seri musim selama periode 2021–2026 (Sumbu X: Tahun)"
                        >
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                            <span>Komparasi</span>
                        </button>
                        <button
                            type="button"
                            class="spl-mode-btn"
                            :class="tssViewMode === 'year' ? 'active' : ''"
                            @click="setTssMode('year')"
                            title="Tampilkan variasi 4 musim untuk tahun terpilih (Sumbu X: Musim)"
                        >
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Per Musim</span>
                        </button>
                    </div>
                </div>

                <div class="chart-header-sub-row">
                    <p class="chart-subtitle">
                        <span x-show="tssViewMode === 'comparison'">Komparasi 4 musim TSS perairan Pulau Tunda (2021–2026) dari <code>rata-rata nilai tss.csv</code>.</span>
                        <span x-show="tssViewMode === 'year' && hasSeasonalTssData(selectedYear)">Rata-rata 4 musim TSS Tahun <strong x-text="selectedYear"></strong> (mg/L) terintegrasi dari dataset CSV.</span>
                        <span x-show="tssViewMode === 'year' && !hasSeasonalTssData(selectedYear)">Konsentrasi padatan tersuspensi dan kejernihan perairan Pulau Tunda (mg/L).</span>
                    </p>
                    <div class="chart-header-badges">
                        <span class="chart-tag amber">TSS (mg/L)</span>
                        <div class="trend-badge" :class="getTssStats().trendClass" x-show="tssViewMode === 'year'">
                            <template x-if="getTssStats().trend === 'increasing'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                            </template>
                            <template x-if="getTssStats().trend === 'decreasing'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </template>
                            <template x-if="getTssStats().trend === 'stable'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </template>
                            <span x-text="getTssStats().trendLabel + ' (' + getTssStats().diffFormatted + ' mg/L)'"></span>
                        </div>
                        <div class="trend-badge trend-stable" x-show="tssViewMode === 'comparison'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Dataset TSS CSV</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistical Summary: Teks Minimalis Tanpa Card/Border/Shadow --}}
            <div class="stats-text-row">
                <div class="stat-text-item stat-item-prominent">
                    <div class="stat-text-label">
                        <span>Nilai Terkini</span>
                        <span class="stat-text-year" x-show="tssViewMode === 'year'" x-text="'(' + getTssStats().latestSeason + ')'">(Peralihan II)</span>
                        <span class="stat-text-year" x-show="tssViewMode === 'comparison'">(2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num prominent amber" x-show="tssViewMode === 'year'" x-text="getTssStats().latest">26.52</span>
                        <span class="stat-text-num prominent amber" x-show="tssViewMode === 'comparison'" x-text="getTssComparisonStats().latest">26.52</span>
                        <span class="stat-text-unit">mg/L</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span x-show="tssViewMode === 'year'">Rata-rata 4 Musim</span>
                        <span x-show="tssViewMode === 'comparison'">Rata-rata (2021–2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-show="tssViewMode === 'year'" x-text="getTssStats().avg">19.82</span>
                        <span class="stat-text-num" x-show="tssViewMode === 'comparison'" x-text="getTssComparisonStats().avg">14.69</span>
                        <span class="stat-text-unit">mg/L</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span>Maksimum</span>
                        <span class="stat-text-year" x-show="tssViewMode === 'year'" x-text="'(' + getTssStats().maxSeason + ')'">(Peralihan II)</span>
                        <span class="stat-text-year" x-show="tssViewMode === 'comparison'" x-text="'(' + getTssComparisonStats().maxYear + ')'">(2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-show="tssViewMode === 'year'" x-text="getTssStats().max">26.52</span>
                        <span class="stat-text-num" x-show="tssViewMode === 'comparison'" x-text="getTssComparisonStats().max">26.52</span>
                        <span class="stat-text-unit">mg/L</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span>Minimum</span>
                        <span class="stat-text-year" x-show="tssViewMode === 'year'" x-text="'(' + getTssStats().minSeason + ')'">(Peralihan I)</span>
                        <span class="stat-text-year" x-show="tssViewMode === 'comparison'" x-text="'(' + getTssComparisonStats().minYear + ')'">(2022)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-show="tssViewMode === 'year'" x-text="getTssStats().min">14.15</span>
                        <span class="stat-text-num" x-show="tssViewMode === 'comparison'" x-text="getTssComparisonStats().min">6.44</span>
                        <span class="stat-text-unit">mg/L</span>
                    </div>
                </div>
            </div>

            <div class="chart-canvas-wrapper" style="position: relative; height: 260px;">
                <canvas id="tssChartCanvas"></canvas>
            </div>
            <div class="chart-footer-hint no-print">
                <div class="chart-hint-left">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span x-show="tssViewMode === 'comparison'">Data terintegrasi dari 4 seri musim <strong>rata-rata nilai tss.csv</strong>.</span>
                    <span x-show="tssViewMode === 'year'">Klik titik musim pada grafik untuk melihat rincian <strong>angka fix</strong> tahun <strong x-text="selectedYear"></strong>.</span>
                </div>
                <button
                    type="button"
                    class="btn-quick-detail"
                    @click="openFixDetail(selectedYear)"
                    title="Buka rincian angka fix untuk tahun terpilih"
                >
                    Rincian Thn <span x-text="selectedYear"></span>
                </button>
            </div>
        </div>

        {{-- GRAFIK 4: KECEPATAN ARUS LAUT --}}
        <div class="chart-card ocean-card-current">
            <div class="chart-header">
                <div class="chart-header-top-row">
                    <div class="chart-title-badge-row">
                        <span class="chart-code-badge navy">ARUS</span>
                        <h2 class="chart-title">4. Kecepatan Arus Laut</h2>
                    </div>
                    <div class="spl-mode-toggle-group">
                        <button
                            type="button"
                            class="spl-mode-btn"
                            :class="currentViewMode === 'comparison' ? 'active' : ''"
                            @click="setCurrentMode('comparison')"
                            title="Tampilkan perbandingan 4 seri musim selama periode 2021–2026 (Sumbu X: Tahun)"
                        >
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                            <span>Komparasi</span>
                        </button>
                        <button
                            type="button"
                            class="spl-mode-btn"
                            :class="currentViewMode === 'year' ? 'active' : ''"
                            @click="setCurrentMode('year')"
                            title="Tampilkan variasi 4 musim untuk tahun terpilih (Sumbu X: Musim)"
                        >
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Per Musim</span>
                        </button>
                    </div>
                </div>

                <div class="chart-header-sub-row">
                    <p class="chart-subtitle">
                        <span x-show="currentViewMode === 'comparison'">Kecepatan arus 4 musim (2021–2026) dari <code>data_arus_clean.csv</code>.</span>
                        <span x-show="currentViewMode === 'year'">Laju perpindahan massa air laut per musim tahun <strong x-text="selectedYear"></strong> (m/s).</span>
                    </p>
                    <div class="chart-header-badges">
                        <span class="chart-tag navy">Arus (m/s)</span>
                        <div class="trend-badge" :class="getCurrentStats().trendClass" x-show="currentViewMode === 'year'">
                            <template x-if="getCurrentStats().trend === 'increasing'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                            </template>
                            <template x-if="getCurrentStats().trend === 'decreasing'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </template>
                            <template x-if="getCurrentStats().trend === 'stable'">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </template>
                            <span x-text="getCurrentStats().trendLabel + ' (' + getCurrentStats().diffFormatted + ' m/s)'"></span>
                        </div>
                        <div class="trend-badge trend-stable" x-show="currentViewMode === 'comparison'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Dataset Arus CSV</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistical Summary: Teks Minimalis Tanpa Card/Border/Shadow --}}
            <div class="stats-text-row">
                <div class="stat-text-item stat-item-prominent">
                    <div class="stat-text-label">
                        <span>Nilai Terkini</span>
                        <span class="stat-text-year" x-show="currentViewMode === 'year'" x-text="'(' + getCurrentStats().latestSeason + ')'">(Peralihan II)</span>
                        <span class="stat-text-year" x-show="currentViewMode === 'comparison'">(2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num prominent blue" x-show="currentViewMode === 'year'" x-text="getCurrentStats().latest">0.375</span>
                        <span class="stat-text-num prominent blue" x-show="currentViewMode === 'comparison'" x-text="getCurrentComparisonStats().latest">0.375</span>
                        <span class="stat-text-unit">m/s</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span x-show="currentViewMode === 'year'">Rata-rata 4 Musim</span>
                        <span x-show="currentViewMode === 'comparison'">Rata-rata (2021–2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-show="currentViewMode === 'year'" x-text="getCurrentStats().avg">0.232</span>
                        <span class="stat-text-num" x-show="currentViewMode === 'comparison'" x-text="getCurrentComparisonStats().avg">0.198</span>
                        <span class="stat-text-unit">m/s</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span>Maksimum</span>
                        <span class="stat-text-year" x-show="currentViewMode === 'year'" x-text="'(' + getCurrentStats().maxSeason + ')'">(Peralihan II)</span>
                        <span class="stat-text-year" x-show="currentViewMode === 'comparison'">(Puncak 2021–2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-show="currentViewMode === 'year'" x-text="getCurrentStats().max">0.375</span>
                        <span class="stat-text-num" x-show="currentViewMode === 'comparison'" x-text="getCurrentComparisonStats().max">0.424</span>
                        <span class="stat-text-unit">m/s</span>
                    </div>
                </div>

                <div class="stat-text-divider"></div>

                <div class="stat-text-item">
                    <div class="stat-text-label">
                        <span>Minimum</span>
                        <span class="stat-text-year" x-show="currentViewMode === 'year'" x-text="'(' + getCurrentStats().minSeason + ')'">(Peralihan I)</span>
                        <span class="stat-text-year" x-show="currentViewMode === 'comparison'">(Terendah 2021–2026)</span>
                    </div>
                    <div class="stat-text-val-row">
                        <span class="stat-text-num" x-show="currentViewMode === 'year'" x-text="getCurrentStats().min">0.089</span>
                        <span class="stat-text-num" x-show="currentViewMode === 'comparison'" x-text="getCurrentComparisonStats().min">0.011</span>
                        <span class="stat-text-unit">m/s</span>
                    </div>
                </div>
            </div>

            <div class="chart-canvas-wrapper" style="position: relative; height: 260px;">
                <canvas id="currentChartCanvas"></canvas>
            </div>
            <div class="chart-footer-hint no-print">
                <div class="chart-hint-left">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span x-show="currentViewMode === 'comparison'">Data terintegrasi dari <code>data_arus_clean.csv</code>: 4 musim (Barat, Peralihan I, Timur, &amp; Peralihan II).</span>
                    <span x-show="currentViewMode === 'year'">Klik titik musim pada grafik untuk melihat rincian <strong>angka fix</strong> dan <strong>arah arus</strong> tahun <strong x-text="selectedYear"></strong>.</span>
                </div>
                <button
                    type="button"
                    class="btn-quick-detail"
                    @click="openFixDetail(selectedYear)"
                    title="Buka rincian angka fix untuk tahun terpilih"
                >
                    Rincian Thn <span x-text="selectedYear"></span>
                </button>
            </div>
        </div>

    </div>

    {{-- ============================================================
         5. TABEL REKAPITULASI DATA PENGUKURAN ANGKA FIX TAHUNAN & 4 MUSIM
    ============================================================ --}}
    <div class="table-card">
        <div class="table-header">
            <div class="table-header-left">
                <h2 class="table-title">Tabel Rekapitulasi Angka Fix Parameter Oseanografi</h2>
                <p class="table-subtitle">Data pengukuran numerik pasti antartahun dan 4 musim (2021 - 2026) di perairan Pulau Tunda.</p>
            </div>
            <div class="table-header-right no-print" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                {{-- Toggle Tampilan Tabel: Tahunan vs Rincian 4 Musim --}}
                <div class="spl-mode-toggle-group">
                    <button
                        type="button"
                        class="spl-mode-btn"
                        :class="tableRecapMode === 'annual' ? 'active' : ''"
                        @click="tableRecapMode = 'annual'"
                        title="Tampilkan rekapitulasi numerik tahunan (6 Periode)"
                    >
                        <span>Rekap Tahunan</span>
                    </button>
                    <button
                        type="button"
                        class="spl-mode-btn"
                        :class="tableRecapMode === 'seasonal' ? 'active' : ''"
                        @click="tableRecapMode = 'seasonal'"
                        title="Tampilkan rincian data 4 musim lengkap (24 Periode) dari dataset CSV"
                    >
                        <span>Rincian 4 Musim (24 Periode)</span>
                    </button>
                </div>

                {{-- Pencarian --}}
                <div style="position:relative; width:220px;">
                    <input
                        type="text"
                        x-model="searchOcean"
                        placeholder="Cari tahun/musim/habitat..."
                        class="scomar-input"
                        style="padding:7px 10px 7px 32px; font-size:12.5px; height:36px;"
                    >
                    <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                <span class="table-count-badge">
                    <strong x-text="tableRecapMode === 'annual' ? filteredOcean().length : filteredSeasonalRecap().length"></strong> Periode
                </span>

                @if(auth()->user()->isAdmin())
                    <button
                        type="button"
                        class="btn-scomar-primary"
                        style="padding:7px 14px; font-size:12.5px; height:36px;"
                        @click="showCreateModal = true"
                    >
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Tambah Parameter</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="scomar-table">
                <thead>
                    <tr>
                        <th style="width: 140px;" x-text="tableRecapMode === 'annual' ? 'Tahun' : 'Periode & Musim'">Tahun</th>
                        <th style="text-align: right;">Suhu Permukaan (°C)</th>
                        <th style="text-align: right;">Klorofil-a (mg/m³)</th>
                        <th style="text-align: right;">TSS (mg/L)</th>
                        <th style="text-align: right;">Arus (m/s)</th>
                        <th style="text-align: center;">Kesesuaian Habitat</th>
                        <th class="no-print" style="text-align: center; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. Rekap Tahunan (6 Periode) --}}
                    <template x-if="tableRecapMode === 'annual'">
                        <template x-for="row in filteredOcean()" :key="row.year">
                            <tr>
                                {{-- Tahun --}}
                                <td class="cell-date">
                                    <span class="year-tag" x-text="row.year"></span>
                                </td>

                                {{-- Suhu --}}
                                <td style="text-align: right;">
                                    <strong style="color: var(--merlot);" x-text="row.temp.toFixed(2)"></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);">°C</span>
                                    <div style="margin-top: 2px;">
                                        <span class="badge-ocean-mini" :class="getParamBadgeClass(row.tempEval ? row.tempEval.status : 'Optimal')" x-text="row.tempEval ? row.tempEval.status : 'Optimal'"></span>
                                    </div>
                                </td>

                                {{-- Klorofil-a --}}
                                <td style="text-align: right;">
                                    <strong style="color: var(--deep-navy);" x-text="row.chlorophyll.toFixed(2)"></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);">mg/m³</span>
                                    <div style="margin-top: 2px;">
                                        <span class="badge-ocean-mini" :class="getParamBadgeClass(row.chlaEval ? row.chlaEval.status : 'Optimal')" x-text="row.chlaEval ? row.chlaEval.status : 'Optimal'"></span>
                                    </div>
                                </td>

                                {{-- TSS --}}
                                <td style="text-align: right;">
                                    <strong style="color: #4B6A8A;" x-text="row.tss !== null ? row.tss.toFixed(2) : 'Null'"></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);">mg/L</span>
                                    <div style="margin-top: 2px;">
                                        <span class="badge-ocean-mini" :class="getParamBadgeClass(row.tssEval ? row.tssEval.status : 'Tidak Optimal')" x-text="row.tssEval ? row.tssEval.status : 'Tidak Optimal'"></span>
                                    </div>
                                </td>

                                {{-- Arus --}}
                                <td style="text-align: right;">
                                    <strong style="color: var(--deep-navy);" x-text="row.current.toFixed(2)"></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);">m/s</span>
                                    <div style="margin-top: 2px;">
                                        <span class="badge-ocean-mini" :class="getParamBadgeClass(row.currentEval ? row.currentEval.status : 'Optimal')" x-text="row.currentEval ? row.currentEval.status : 'Optimal'"></span>
                                    </div>
                                </td>

                                {{-- Kesesuaian Habitat --}}
                                <td style="text-align: center;">
                                    <span class="badge-score-ocean" :class="getHabitatBadgeClass(row.habitatScore)" x-text="row.habitatScore"></span>
                                </td>

                                {{-- Aksi (Lihat, Edit, Hapus) --}}
                                <td class="no-print" style="text-align: center;">
                                    <div style="display:inline-flex; gap:6px; justify-content:center; align-items:center;">
                                        <button
                                            type="button"
                                            class="btn-action-icon view"
                                            @click="openFixDetail(row.year)"
                                            title="Lihat rincian angka fix &amp; 4 musim"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>

                                        @if(auth()->user()->isAdmin())
                                            <button
                                                type="button"
                                                class="btn-action-icon edit"
                                                title="Edit Parameter Oseanografi"
                                                @click="openEdit(row)"
                                            >
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>

                                            <button
                                                type="button"
                                                class="btn-action-icon delete"
                                                title="Hapus Parameter Oseanografi"
                                                @click="openDelete(row)"
                                            >
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    {{-- 2. Rincian 4 Musim (24 Periode) --}}
                    <template x-if="tableRecapMode === 'seasonal'">
                        <template x-for="(sRow, sIdx) in filteredSeasonalRecap()" :key="sIdx">
                            <tr>
                                <td class="cell-date">
                                    <div><strong style="color: var(--text-primary); font-size: 12px;" x-text="sRow.period_label"></strong></div>
                                    <span style="font-size: 10px; color: var(--text-muted);" x-text="sRow.season_code"></span>
                                </td>
                                <td style="text-align: right;">
                                    <strong style="color: var(--merlot);" x-text="formatNullVal(sRow.temp, '', 2)"></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);" x-show="sRow.temp !== null">°C</span>
                                    <div style="margin-top: 2px;">
                                        <span class="badge-ocean-mini" :class="sRow.temp_eval.badge" x-text="sRow.temp_eval.status"></span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <strong style="color: var(--deep-navy);" x-text="formatNullVal(sRow.chlorophyll, '', 3)"></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);" x-show="sRow.chlorophyll !== null">mg/m³</span>
                                    <div style="margin-top: 2px;">
                                        <span class="badge-ocean-mini" :class="sRow.chla_eval.badge" x-text="sRow.chla_eval.status"></span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <strong style="color: #4B6A8A;" x-text="formatNullVal(sRow.tss, '', 3)"></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);" x-show="sRow.tss !== null">mg/L</span>
                                    <div style="margin-top: 2px;">
                                        <span class="badge-ocean-mini" :class="sRow.tss_eval.badge" x-text="sRow.tss_eval.status"></span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <strong style="color: var(--deep-navy);" x-text="formatNullVal(sRow.current, '', 3)"></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);" x-show="sRow.current !== null">m/s</span>
                                    <div style="margin-top: 2px;">
                                        <span class="badge-ocean-mini" :class="sRow.current_eval.badge" x-text="sRow.current_eval.status"></span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-score-ocean" :class="sRow.habitatBadge" x-text="sRow.habitatScore"></span>
                                </td>
                                <td class="no-print" style="text-align: center;">
                                    <div style="display:inline-flex; gap:6px; justify-content:center; align-items:center;">
                                        <button
                                            type="button"
                                            class="btn-action-icon view"
                                            @click="openFixDetail(sRow.year)"
                                            title="Lihat rincian tahun ini"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        {{-- ============================================================
             MODAL TAMBAH PARAMETER OSEANOGRAFI
        ============================================================ --}}
        <div
            class="scomar-modal-backdrop no-print"
            x-show="showCreateModal"
            x-transition.opacity
            style="display:none;"
            @keydown.escape.window="showCreateModal = false"
        >
            <div class="scomar-modal-card" @click.outside="showCreateModal = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--merlot)" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Tambah Parameter Oseanografi</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showCreateModal = false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('oceanographic.store') }}">
                    @csrf
                    <div class="scomar-modal-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tahun Pengamatan <span class="req">*</span></label>
                                <input type="number" name="year" value="2027" min="2000" max="2099" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Suhu SPL (°C) <span class="req">*</span></label>
                                <input type="number" step="0.01" name="temp" placeholder="Contoh: 29.50" class="scomar-input" required>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Klorofil-a (mg/m³) <span class="req">*</span></label>
                                <input type="number" step="0.01" name="chlorophyll" placeholder="Contoh: 0.85" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">TSS (mg/L) <span class="req">*</span></label>
                                <input type="number" step="0.01" name="tss" placeholder="Contoh: 14.50" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Arus (m/s) <span class="req">*</span></label>
                                <input type="number" step="0.01" name="current" placeholder="Contoh: 0.35" class="scomar-input" required>
                            </div>
                        </div>

                        <input type="hidden" name="status" value="Optimal">
                        <div class="scomar-form-group">
                            <label class="scomar-label">Evaluasi Habitat Pelagis <span class="req">*</span></label>
                            <input type="text" name="habitat_score" value="Optimal" class="scomar-input" required>
                        </div>

                        <div class="scomar-form-group">
                            <label class="scomar-label">Sumber Data / Validasi <span class="req">*</span></label>
                            <input type="text" name="source" value="Pemantauan Terkini SCOMAR UPI" class="scomar-input" required>
                        </div>

                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Catatan Tambahan (Opsional)</label>
                            <textarea name="notes" rows="2" class="scomar-textarea" placeholder="Keterangan fenomena oseanografi..."></textarea>
                        </div>
                    </div>

                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showCreateModal = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Simpan Parameter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================
             MODAL EDIT PARAMETER OSEANOGRAFI
        ============================================================ --}}
        <div
            class="scomar-modal-backdrop no-print"
            x-show="showEditModal"
            x-transition.opacity
            style="display:none;"
            @keydown.escape.window="showEditModal = false"
        >
            <div class="scomar-modal-card" @click.outside="showEditModal = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--merlot)" stroke-width="2.5">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span>Edit Parameter Oseanografi</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showEditModal = false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('/oceanographic') }}/${activeItem.id}`">
                    @csrf
                    @method('PUT')
                    <div class="scomar-modal-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tahun Pengamatan <span class="req">*</span></label>
                                <input type="number" name="year" x-model="activeItem.year" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Suhu SPL (°C) <span class="req">*</span></label>
                                <input type="number" step="0.01" name="temp" x-model="activeItem.temp" class="scomar-input" required>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Klorofil-a (mg/m³) <span class="req">*</span></label>
                                <input type="number" step="0.01" name="chlorophyll" x-model="activeItem.chlorophyll" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">TSS (mg/L) <span class="req">*</span></label>
                                <input type="number" step="0.01" name="tss" x-model="activeItem.tss" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Arus (m/s) <span class="req">*</span></label>
                                <input type="number" step="0.01" name="current" x-model="activeItem.current" class="scomar-input" required>
                            </div>
                        </div>

                        <input type="hidden" name="status" :value="activeItem.status || 'Optimal'">
                        <div class="scomar-form-group">
                            <label class="scomar-label">Evaluasi Habitat Pelagis <span class="req">*</span></label>
                            <input type="text" name="habitat_score" x-model="activeItem.habitatScore" class="scomar-input" required>
                        </div>

                        <div class="scomar-form-group">
                            <label class="scomar-label">Sumber Data / Validasi <span class="req">*</span></label>
                            <input type="text" name="source" x-model="activeItem.source" class="scomar-input" required>
                        </div>

                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Catatan Tambahan</label>
                            <textarea name="notes" x-model="activeItem.notes" rows="2" class="scomar-textarea"></textarea>
                        </div>
                    </div>

                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showEditModal = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Perbarui Parameter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================
             MODAL HAPUS PARAMETER OSEANOGRAFI
        ============================================================ --}}
        <div
            class="scomar-modal-backdrop no-print"
            x-show="showDeleteModal"
            x-transition.opacity
            style="display:none;"
            @keydown.escape.window="showDeleteModal = false"
        >
            <div class="scomar-modal-card" style="max-width:440px;" @click.outside="showDeleteModal = false">
                <div class="scomar-modal-header" style="border-bottom:none;">
                    <div class="scomar-modal-title" style="color:#DC2626;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>Hapus Parameter Oseanografi</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showDeleteModal = false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <div class="scomar-modal-body" style="padding-top:10px;">
                    <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0 0 12px 0;">
                        Apakah Anda yakin ingin menghapus data parameter oseanografi tahun <strong style="color:var(--text-primary);" x-text="activeItem.year"></strong>?
                    </p>
                    <div style="background:rgba(220,38,38,0.08); border-left:3px solid #DC2626; padding:10px 14px; border-radius:6px; font-size:12px; color:#DC2626;">
                        Data parameter ini akan dihapus dari grafik SPL, Klorofil-a, TSS, Arus, dan laporan PDF.
                    </div>
                </div>

                <form method="POST" :action="`{{ url('/oceanographic') }}/${activeItem.id}`">
                    @csrf
                    @method('DELETE')
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showDeleteModal = false">Batal</button>
                        <button type="submit" class="btn-scomar-danger">Ya, Hapus Parameter</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ============================================================
         6. POP-UP MODAL: INFORMASI ANGKA FIX PENGUKURAN TAHUNAN
    ============================================================ --}}
    <div
        class="modal-backdrop no-print"
        x-show="showFixModal"
        x-transition.opacity
        style="display: none;"
        @keydown.escape.window="showFixModal = false"
    >
        <div class="modal-dialog modal-dialog-ocean" @click.outside="showFixModal = false">
            <template x-if="selectedYearData">
                <div>
                    {{-- Header Modal --}}
                    <div class="modal-header">
                        <div class="modal-title-group">
                            <div class="modal-icon-badge merlot">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h3 class="modal-title">
                                    Informasi Angka Fix Pengukuran — Tahun <span x-text="selectedYearData.year"></span>
                                </h3>
                                <p class="modal-subtitle">
                                    Nilai pasti rekaman oseanografi terverifikasi di perairan Pulau Tunda.
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close-modal" @click="showFixModal = false">&times;</button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="modal-body">

                        {{-- 4 Kotak Angka Fix --}}
                        <div class="fix-grid-values">

                            {{-- 1. Suhu --}}
                            <div class="fix-box-item temp">
                                <span class="fix-box-label">Suhu Permukaan Laut (SPL)</span>
                                <div class="fix-box-number">
                                    <span class="number-val" x-text="selectedYearData.temp.toFixed(2)"></span>
                                    <span class="number-unit">°C</span>
                                </div>
                                <div style="margin-top: 3px;">
                                    <span class="badge-ocean-mini" :class="getParamBadgeClass(selectedYearData.tempEval ? selectedYearData.tempEval.status : 'Optimal')" x-text="selectedYearData.tempEval ? selectedYearData.tempEval.status : 'Optimal'"></span>
                                </div>
                                <span class="fix-box-note">Rentang optimal: 24 – 31 °C</span>
                            </div>

                            {{-- 2. Klorofil-a --}}
                            <div class="fix-box-item chla">
                                <span class="fix-box-label">Konsentrasi Klorofil-a</span>
                                <div class="fix-box-number">
                                    <span class="number-val" x-text="selectedYearData.chlorophyll.toFixed(2)"></span>
                                    <span class="number-unit">mg/m³</span>
                                </div>
                                <div style="margin-top: 3px;">
                                    <span class="badge-ocean-mini" :class="getParamBadgeClass(selectedYearData.chlaEval ? selectedYearData.chlaEval.status : 'Optimal')" x-text="selectedYearData.chlaEval ? selectedYearData.chlaEval.status : 'Optimal'"></span>
                                </div>
                                <span class="fix-box-note">Rentang optimal: 0,1 – 5 mg/m³</span>
                            </div>

                            {{-- 3. TSS --}}
                            <div class="fix-box-item tss">
                                <span class="fix-box-label">Total Suspended Solids (TSS)</span>
                                <div class="fix-box-number">
                                    <span class="number-val" x-text="selectedYearData.tss !== null ? selectedYearData.tss.toFixed(2) : 'Tidak Tersedia'"></span>
                                    <span class="number-unit">mg/L</span>
                                </div>
                                <div style="margin-top: 3px;">
                                    <span class="badge-ocean-mini" :class="getParamBadgeClass(selectedYearData.tssEval ? selectedYearData.tssEval.status : 'Belum Dinilai')" x-text="selectedYearData.tssEval ? selectedYearData.tssEval.status : 'Belum Dinilai'"></span>
                                </div>
                                <span class="fix-box-note">Rentang optimal: ≤ 50 mg/L</span>
                            </div>

                            {{-- 4. Arus --}}
                            <div class="fix-box-item current">
                                <span class="fix-box-label">Kecepatan Arus Laut</span>
                                <div class="fix-box-number">
                                    <span class="number-val" x-text="selectedYearData.current.toFixed(2)"></span>
                                    <span class="number-unit">m/s</span>
                                </div>
                                <div style="margin-top: 3px;">
                                    <span class="badge-ocean-mini" :class="getParamBadgeClass(selectedYearData.currentEval ? selectedYearData.currentEval.status : 'Optimal')" x-text="selectedYearData.currentEval ? selectedYearData.currentEval.status : 'Optimal'"></span>
                                </div>
                                <span class="fix-box-note">Rentang optimal: 0,1 – 0,5 m/s (~ <span x-text="(selectedYearData.current * 1.94384).toFixed(2)"></span> knot)</span>
                            </div>

                        </div>

                        {{-- Rincian 4 Parameter per Musim Tahun Terpilih --}}
                        <div class="fix-current-season-box" style="margin-bottom: 12px;" x-show="getSeasonalRecapForYear(selectedYearData.year).length > 0">
                            <div class="fix-current-season-header">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary);">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <span style="font-size: 11.5px; font-weight: 700; color: var(--text-primary);">Rincian 4 Parameter Oseanografi per Musim — Tahun <span x-text="selectedYearData.year"></span></span>
                                </div>
                                <span style="font-size: 10px; color: var(--text-muted); font-weight: 500;">Baku Mutu Optimal SCOMAR</span>
                            </div>
                            <div class="fix-table-scroll-container">
                                <table class="fix-current-season-table">
                                    <thead>
                                        <tr>
                                            <th>Musim</th>
                                            <th style="text-align: right;">Suhu (24–31 °C)</th>
                                            <th style="text-align: right;">Chl-a (0,1–5 mg/m³)</th>
                                            <th style="text-align: right;">TSS (25–50 mg/L)</th>
                                            <th style="text-align: right;">Arus (0,1–0,5 m/s)</th>
                                            <th style="text-align: center;">Penilaian Habitat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(sItem, sIdx) in getSeasonalRecapForYear(selectedYearData.year)" :key="'season-param-' + (selectedYearData ? selectedYearData.year : '') + '-' + sIdx">
                                            <tr>
                                                <td style="font-weight: 600; color: var(--text-primary); white-space: nowrap;">
                                                    <span x-text="sItem.season_name"></span>
                                                    <span style="font-size: 10px; color: var(--text-muted);" x-text="'(' + sItem.season_code + ')'"></span>
                                                </td>
                                                <td style="text-align: right; white-space: nowrap;">
                                                    <span class="fix-table-num val-temp" x-text="formatNullVal(sItem.temp, '°C')"></span>
                                                    <div><span class="badge-ocean-mini" :class="sItem.temp_eval ? sItem.temp_eval.badge : 'badge-optimal'" x-text="sItem.temp_eval ? sItem.temp_eval.status : 'Optimal'"></span></div>
                                                </td>
                                                <td style="text-align: right; white-space: nowrap;">
                                                    <span class="fix-table-num val-chla" x-text="formatNullVal(sItem.chlorophyll, 'mg/m³', 3)"></span>
                                                    <div><span class="badge-ocean-mini" :class="sItem.chla_eval ? sItem.chla_eval.badge : 'badge-optimal'" x-text="sItem.chla_eval ? sItem.chla_eval.status : 'Optimal'"></span></div>
                                                </td>
                                                <td style="text-align: right; white-space: nowrap;">
                                                    <span class="fix-table-num val-tss" x-text="formatNullVal(sItem.tss, 'mg/L', 3)"></span>
                                                    <div><span class="badge-ocean-mini" :class="sItem.tss_eval ? sItem.tss_eval.badge : 'badge-unavailable'" x-text="sItem.tss_eval ? sItem.tss_eval.status : 'Tidak Tersedia'"></span></div>
                                                </td>
                                                <td style="text-align: right; white-space: nowrap;">
                                                    <span class="fix-table-num val-current" x-text="formatNullVal(sItem.current, 'm/s', 3)"></span>
                                                    <div><span class="badge-ocean-mini" :class="sItem.current_eval ? sItem.current_eval.badge : 'badge-optimal'" x-text="sItem.current_eval ? sItem.current_eval.status : 'Optimal'"></span></div>
                                                </td>
                                                <td style="text-align: center; white-space: nowrap;">
                                                    <span class="badge-score-ocean" :class="sItem.habitatBadge || 'badge-score-pending'" x-text="sItem.habitatScore || '-'"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Rincian Dinamika Kecepatan & Arah Arus 4 Musim (Sumber: data_arus_clean.csv) --}}
                        <div class="fix-current-season-box" x-show="getCurrentDetails(selectedYearData.year).length > 0">
                            <div class="fix-current-season-header">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary);">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                                    </svg>
                                    <span style="font-size: 11.5px; font-weight: 700; color: var(--text-primary);">Rincian Arus Laut per Musim — Tahun <span x-text="selectedYearData.year"></span></span>
                                </div>
                                <span style="font-size: 10px; color: var(--text-muted); font-weight: 500;">Sumber: <code>data_arus_clean.csv</code></span>
                            </div>
                            <div class="fix-table-scroll-container">
                                <table class="fix-current-season-table">
                                    <thead>
                                        <tr>
                                            <th>Musim</th>
                                            <th>Kode</th>
                                            <th style="text-align: right;">Kecepatan (m/s)</th>
                                            <th style="text-align: right;">Kecepatan (knot)</th>
                                            <th style="text-align: right;">Arah (°)</th>
                                            <th>Keterangan Arah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, idx) in getCurrentDetails(selectedYearData.year)" :key="'season-curr-' + (selectedYearData ? selectedYearData.year : '') + '-' + idx">
                                            <tr>
                                                <td style="font-weight: 600; color: var(--text-primary); white-space: nowrap;" x-text="item.season_name"></td>
                                                <td style="color: var(--text-secondary); white-space: nowrap;" x-text="item.season_code"></td>
                                                <td class="fix-table-num val-current" style="text-align: right; white-space: nowrap;" x-text="item.speed !== null && item.speed !== undefined ? item.speed.toFixed(3) : '-'"></td>
                                                <td style="text-align: right; color: var(--text-secondary); white-space: nowrap;" x-text="item.speed_knot !== null && item.speed_knot !== undefined ? item.speed_knot.toFixed(2) : '-'"></td>
                                                <td style="text-align: right; font-weight: 600; color: var(--text-primary); white-space: nowrap;" x-text="item.direction !== null && item.direction !== undefined ? (item.direction + '°') : '-'"></td>
                                                <td style="white-space: nowrap;">
                                                    <span class="fix-direction-badge" x-text="item.direction_str || '-'"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Keterangan Analisis Ekosistem --}}
                        <div class="fix-analysis-box">
                            <div class="analysis-row">
                                <span class="analysis-label">Potensi Habitat Ikan Tenggiri:</span>
                                <span class="badge-score-ocean" :class="getHabitatBadgeClass(selectedYearData.habitatScore)" x-text="selectedYearData.habitatScore"></span>
                            </div>
                            <div class="analysis-row" x-show="selectedYearData.habitatDesc">
                                <span class="analysis-label">Keterangan Evaluasi:</span>
                                <span class="analysis-val" style="font-size: 11px; color: var(--text-secondary);" x-text="selectedYearData.habitatDesc"></span>
                            </div>
                            <div class="analysis-row">
                                <span class="analysis-label">Sumber & Metode Pengambilan:</span>
                                <span class="analysis-val text-muted" x-text="selectedYearData.source"></span>
                            </div>
                        </div>

                    </div>

                    {{-- Footer Modal --}}
                    <div class="modal-footer">
                        <button type="button" class="btn-scomar btn-outline" @click="showFixModal = false">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

{{-- ================================================================
     HIDDEN PDF CONTAINER — KHUSUS EKSPOR (TIDAK TAMPAK DI LAYAR)
     Menggunakan flexbox murni untuk menghindari bug CSS Grid + html2pdf
================================================================ --}}
<div id="scomarPdfOceanReport" style="display:none; width:1122px; background:#FFFFFF; font-family:'Plus Jakarta Sans', sans-serif; color:#1A1A2E; box-sizing:border-box;">

    {{-- ══════════════════════════════════════════════════════
         HALAMAN 1: Header + 4 Kartu + Grafik SPL & Klorofil-a
    ══════════════════════════════════════════════════════ --}}
    <div id="pdf-ocean-page1" style="width:1122px; height:794px; max-height:794px; padding:28px 36px 24px 36px; box-sizing:border-box; background:#FFFFFF; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden; page-break-after:always; break-after:page;">
        <div>
            {{-- Header Dokumen --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding-bottom:10px; border-bottom:2.5px solid #73343A;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:38px; height:38px; background:#73343A; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:17px; font-weight:800; color:#73343A; letter-spacing:-0.3px; line-height:1.2;">Grafik Parameter Oseanografi</div>
                        <div style="font-size:10.5px; color:#6B7280; font-weight:500; margin-top:2px;">SCOMAR — Pemantauan Ekosistem Laut Pulau Tunda, Banten | Periode 2021 – 2026</div>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:9.5px; color:#6B7280; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Tanggal Cetak</div>
                    <div id="pdfOceanDate" style="font-size:11.5px; font-weight:700; color:#24364B; margin-top:1px;">—</div>
                    <div style="font-size:9.5px; color:#9CA3AF; margin-top:1px;">Hal. 1 / 3</div>
                </div>
            </div>

            {{-- Deskripsi Laporan --}}
            <div style="background:rgba(165,184,208,0.12); border-radius:8px; padding:8px 14px; margin-bottom:12px; border-left:3.5px solid #A5B8D0;">
                <div style="font-size:11px; color:#374151; line-height:1.5;">
                    Pemantauan data oseanografi perairan Pulau Tunda: <strong>Suhu Permukaan Laut (SPL)</strong>, <strong>Konsentrasi Klorofil-a</strong>, <strong>Total Suspended Solids (TSS)</strong>, dan <strong>Kecepatan Arus Laut</strong>. Data bersumber dari citra satelit dan validasi <em>in-situ</em> oleh Konsorsium SCOMAR UPI.
                </div>
            </div>

            {{-- 4 Kartu Metrik (Flexbox) --}}
            <div style="display:flex; gap:10px; margin-bottom:12px;">
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-left:4px solid #73343A; border-radius:10px; padding:10px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px;">Suhu Permukaan Laut (SPL)</div>
                    <div style="font-size:20px; font-weight:800; color:#73343A; line-height:1.1;">29.60<small style="font-size:11px; font-weight:600; color:#6B7280;">°C</small></div>
                    <div style="font-size:10px; color:var(--merlot); font-weight:600; margin-top:4px;">Rentang Optimal: 28.0 – 30.5°C</div>
                </div>
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-left:4px solid var(--deep-navy); border-radius:10px; padding:10px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px;">Konsentrasi Klorofil-a</div>
                    <div style="font-size:20px; font-weight:800; color:var(--deep-navy); line-height:1.1;">0.88<small style="font-size:11px; font-weight:600; color:#6B7280;">mg/m³</small></div>
                    <div style="font-size:10px; color:#10B981; font-weight:600; margin-top:4px;">Rentang Baku Mutu: 0.1 – 5.0 mg/m³</div>
                </div>
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-left:4px solid #4B6A8A; border-radius:10px; padding:10px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px;">Padatan Tersuspensi (TSS)</div>
                    <div style="font-size:20px; font-weight:800; color:#4B6A8A; line-height:1.1;">14.20<small style="font-size:11px; font-weight:600; color:#6B7280;">mg/L</small></div>
                    <div style="font-size:10px; color:#6B7280; font-weight:600; margin-top:4px;">Baku Mutu Aman (&lt; 20 mg/L)</div>
                </div>
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-left:4px solid #24364B; border-radius:10px; padding:10px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px;">Kecepatan Arus Laut</div>
                    <div style="font-size:20px; font-weight:800; color:#24364B; line-height:1.1;">0.35<small style="font-size:11px; font-weight:600; color:#6B7280;">m/s</small></div>
                    <div style="font-size:10px; color:#6B7280; font-weight:600; margin-top:4px;">Arus Sedang (~ 0.68 knot)</div>
                </div>
            </div>

            {{-- Baris Grafik 1 & 2 (Flexbox 50%-50%) --}}
            <div style="display:flex; gap:12px;">
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-radius:10px; padding:12px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <div>
                            <div style="font-size:12.5px; font-weight:700; color:#1A1A2E;">1. Suhu Permukaan Laut (SPL)</div>
                            <div style="font-size:10px; color:#6B7280; margin-top:1px;">Pengukuran suhu rata-rata tahunan (°C)</div>
                        </div>
                        <span style="font-size:9.5px; font-weight:700; background:rgba(115,52,58,0.1); color:#73343A; padding:2px 8px; border-radius:4px;">Suhu (°C)</span>
                    </div>
                    <img id="pdfImgTemp" src="" style="width:100%; height:190px; object-fit:fill; display:block;" alt="Grafik Suhu" />
                </div>
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-radius:10px; padding:12px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <div>
                            <div style="font-size:12.5px; font-weight:700; color:#1A1A2E;">2. Konsentrasi Klorofil-a</div>
                            <div style="font-size:10px; color:#6B7280; margin-top:1px;">Indikator biomassa fitoplankton (mg/m³)</div>
                        </div>
                        <span style="font-size:9.5px; font-weight:700; background:rgba(5,150,105,0.1); color:var(--deep-navy); padding:2px 8px; border-radius:4px;">Klorofil-a (mg/m³)</span>
                    </div>
                    <img id="pdfImgChla" src="" style="width:100%; height:190px; object-fit:fill; display:block;" alt="Grafik Klorofil-a" />
                </div>
            </div>
        </div>

        {{-- Footer Halaman 1 --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:8px; border-top:1px solid #E5E7EB; font-size:9.5px; color:#9CA3AF;">
            <span>SCOMAR — Sistem Pemantauan Ekosistem Laut Pulau Tunda | Universitas Pendidikan Indonesia</span>
            <span>Halaman 1 dari 3</span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         HALAMAN 2: Grafik TSS & Arus + Statistik 6 Tahun
    ══════════════════════════════════════════════════════ --}}
    <div id="pdf-ocean-page2" style="width:1122px; height:794px; max-height:794px; padding:28px 36px 24px 36px; box-sizing:border-box; background:#FFFFFF; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden; page-break-after:always; break-after:page;">
        <div>
            {{-- Header Lanjutan --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding-bottom:10px; border-bottom:2px solid #73343A;">
                <div>
                    <div style="font-size:14px; font-weight:800; color:#73343A;">Grafik Parameter Oseanografi — Halaman 2</div>
                    <div style="font-size:10.5px; color:#6B7280; margin-top:1px;">TSS & Kecepatan Arus Laut | SCOMAR Pulau Tunda 2021–2026</div>
                </div>
                <div style="text-align:right; font-size:9.5px; color:#9CA3AF; font-weight:600;">Hal. 2 / 3</div>
            </div>

            {{-- Baris Grafik 3 & 4 (Flexbox 50%-50%) --}}
            <div style="display:flex; gap:12px; margin-bottom:12px;">
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-radius:10px; padding:12px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <div>
                            <div style="font-size:12.5px; font-weight:700; color:#1A1A2E;">3. Total Suspended Solids (TSS)</div>
                            <div style="font-size:10px; color:#6B7280; margin-top:1px;">Konsentrasi padatan tersuspensi (mg/L)</div>
                        </div>
                        <span style="font-size:9.5px; font-weight:700; background:rgba(217,119,6,0.1); color:#4B6A8A; padding:2px 8px; border-radius:4px;">TSS (mg/L)</span>
                    </div>
                    <img id="pdfImgTss" src="" style="width:100%; height:190px; object-fit:fill; display:block;" alt="Grafik TSS" />
                </div>
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-radius:10px; padding:12px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <div>
                            <div style="font-size:12.5px; font-weight:700; color:#1A1A2E;">4. Kecepatan Arus Laut</div>
                            <div style="font-size:10px; color:#6B7280; margin-top:1px;">Laju perpindahan massa air laut (m/s)</div>
                        </div>
                        <span style="font-size:9.5px; font-weight:700; background:rgba(36,54,75,0.1); color:#24364B; padding:2px 8px; border-radius:4px;">Arus (m/s)</span>
                    </div>
                    <img id="pdfImgCurrent" src="" style="width:100%; height:190px; object-fit:fill; display:block;" alt="Grafik Arus" />
                </div>
            </div>

            {{-- Statistik Ringkasan 6 Tahun --}}
            <div style="background:rgba(36,54,75,0.04); border-radius:10px; padding:12px 16px; border:1.5px solid #E5E7EB;">
                <div style="font-size:12px; font-weight:800; color:#24364B; margin-bottom:10px; padding-bottom:6px; border-bottom:1.5px solid #E5E7EB;">Ringkasan Statistik 6 Tahun (2021 – 2026)</div>
                <div style="display:flex; gap:10px;">
                    <div style="flex:1; background:#FFFFFF; border-radius:8px; padding:10px 12px; border:1px solid #E5E7EB; text-align:center;">
                        <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:4px;">Suhu Permukaan (SPL)</div>
                        <div style="font-size:12.5px; font-weight:800; color:#73343A;">28.75 – 30.15°C</div>
                        <div style="font-size:9.5px; color:#6B7280; margin-top:3px;">Puncak: 2025 (30.15°C)</div>
                    </div>
                    <div style="flex:1; background:#FFFFFF; border-radius:8px; padding:10px 12px; border:1px solid #E5E7EB; text-align:center;">
                        <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:4px;">Konsentrasi Klorofil-a</div>
                        <div style="font-size:12.5px; font-weight:800; color:var(--deep-navy);">0.62 – 1.05 mg/m³</div>
                        <div style="font-size:9.5px; color:#6B7280; margin-top:3px;">Puncak: 2025 (1.05 mg/m³)</div>
                    </div>
                    <div style="flex:1; background:#FFFFFF; border-radius:8px; padding:10px 12px; border:1px solid #E5E7EB; text-align:center;">
                        <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:4px;">Total Suspended Solids (TSS)</div>
                        <div style="font-size:12.5px; font-weight:800; color:#4B6A8A;">12.80 – 16.40 mg/L</div>
                        <div style="font-size:9.5px; color:#6B7280; margin-top:3px;">Terendah: 2025 (12.80 mg/L)</div>
                    </div>
                    <div style="flex:1; background:#FFFFFF; border-radius:8px; padding:10px 12px; border:1px solid #E5E7EB; text-align:center;">
                        <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:4px;">Kecepatan Arus Laut</div>
                        <div style="font-size:12.5px; font-weight:800; color:#24364B;">0.28 – 0.42 m/s</div>
                        <div style="font-size:9.5px; color:#6B7280; margin-top:3px;">Puncak: 2025 (0.42 m/s)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Halaman 2 --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:8px; border-top:1px solid #E5E7EB; font-size:9.5px; color:#9CA3AF;">
            <span>SCOMAR — Sistem Pemantauan Ekosistem Laut Pulau Tunda | Universitas Pendidikan Indonesia</span>
            <span>Halaman 2 dari 3</span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         HALAMAN 3: Tabel Rekapitulasi Angka Fix + Evaluasi
    ══════════════════════════════════════════════════════ --}}
    <div id="pdf-ocean-page3" style="width:1122px; height:794px; max-height:794px; padding:28px 36px 24px 36px; box-sizing:border-box; background:#FFFFFF; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden;">
        <div>
            {{-- Header Lanjutan --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding-bottom:10px; border-bottom:2px solid #73343A;">
                <div>
                    <div style="font-size:14px; font-weight:800; color:#73343A;">Tabel Rekapitulasi Angka Fix — Halaman 3</div>
                    <div style="font-size:10.5px; color:#6B7280; margin-top:1px;">Data pengukuran numerik pasti antartahun (2021–2026) di perairan Pulau Tunda</div>
                </div>
                <div style="text-align:right; font-size:9.5px; color:#9CA3AF; font-weight:600;">Hal. 3 / 3</div>
            </div>

            {{-- Tabel Rekapitulasi --}}
            <table id="pdfOceanTableBody" style="width:100%; border-collapse:collapse; font-size:11.5px; margin-bottom:14px;">
                <thead>
                    <tr style="background:#24364B; color:#FFFFFF;">
                        <th style="padding:8px 10px; text-align:left; font-weight:700; font-size:10.5px;">Tahun</th>
                        <th style="padding:8px 10px; text-align:right; font-weight:700; font-size:10.5px;">Suhu SPL (°C)</th>
                        <th style="padding:8px 10px; text-align:right; font-weight:700; font-size:10.5px;">Klorofil-a (mg/m³)</th>
                        <th style="padding:8px 10px; text-align:right; font-weight:700; font-size:10.5px;">TSS (mg/L)</th>
                        <th style="padding:8px 10px; text-align:right; font-weight:700; font-size:10.5px;">Arus (m/s)</th>
                        <th style="padding:8px 10px; text-align:center; font-weight:700; font-size:10.5px;">Kesesuaian Habitat</th>
                        <th style="padding:8px 10px; text-align:center; font-weight:700; font-size:10.5px;">Sumber Data</th>
                    </tr>
                </thead>
                <tbody id="pdfOceanTableRows">
                    <tr style="background:#F9FAFB;">
                        <td style="padding:7px 10px; color:#24364B; font-weight:700;">2021</td>
                        <td style="padding:7px 10px; text-align:right; color:#73343A; font-weight:700;">28.75°C</td>
                        <td style="padding:7px 10px; text-align:right; color:var(--deep-navy); font-weight:700;">0.62</td>
                        <td style="padding:7px 10px; text-align:right; color:#4B6A8A; font-weight:700;">16.40</td>
                        <td style="padding:7px 10px; text-align:right; color:#24364B; font-weight:700;">0.28</td>
                        <td style="padding:7px 10px; text-align:center; font-size:10px; color:#374151;">Optimal</td>
                        <td style="padding:7px 10px; text-align:center; font-size:9.5px; color:#6B7280;">Satelit Aqua MODIS & In-situ UPI</td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="padding:7px 10px; color:#24364B; font-weight:700;">2022</td>
                        <td style="padding:7px 10px; text-align:right; color:#73343A; font-weight:700;">29.10°C</td>
                        <td style="padding:7px 10px; text-align:right; color:var(--deep-navy); font-weight:700;">0.74</td>
                        <td style="padding:7px 10px; text-align:right; color:#4B6A8A; font-weight:700;">14.80</td>
                        <td style="padding:7px 10px; text-align:right; color:#24364B; font-weight:700;">0.32</td>
                        <td style="padding:7px 10px; text-align:center; font-size:10px; color:#374151;">Sangat Sesuai</td>
                        <td style="padding:7px 10px; text-align:center; font-size:9.5px; color:#6B7280;">Satelit Aqua MODIS & In-situ UPI</td>
                    </tr>
                    <tr style="background:#F9FAFB;">
                        <td style="padding:7px 10px; color:#24364B; font-weight:700;">2023</td>
                        <td style="padding:7px 10px; text-align:right; color:#73343A; font-weight:700;">29.85°C</td>
                        <td style="padding:7px 10px; text-align:right; color:var(--deep-navy); font-weight:700;">0.91</td>
                        <td style="padding:7px 10px; text-align:right; color:#4B6A8A; font-weight:700;">13.50</td>
                        <td style="padding:7px 10px; text-align:right; color:#24364B; font-weight:700;">0.38</td>
                        <td style="padding:7px 10px; text-align:center; font-size:10px; color:#374151;">Sangat Sesuai (Puncak)</td>
                        <td style="padding:7px 10px; text-align:center; font-size:9.5px; color:#6B7280;">Satelit Sentinel-3 & In-situ UPI</td>
                    </tr>
                    <tr style="background:#FFFFFF;">
                        <td style="padding:7px 10px; color:#24364B; font-weight:700;">2024</td>
                        <td style="padding:7px 10px; text-align:right; color:#73343A; font-weight:700;">29.40°C</td>
                        <td style="padding:7px 10px; text-align:right; color:var(--deep-navy); font-weight:700;">0.83</td>
                        <td style="padding:7px 10px; text-align:right; color:#4B6A8A; font-weight:700;">15.10</td>
                        <td style="padding:7px 10px; text-align:right; color:#24364B; font-weight:700;">0.34</td>
                        <td style="padding:7px 10px; text-align:center; font-size:10px; color:#374151;">Sangat Sesuai</td>
                        <td style="padding:7px 10px; text-align:center; font-size:9.5px; color:#6B7280;">Satelit Sentinel-3 & In-situ UPI</td>
                    </tr>
                    <tr style="background:#F9FAFB;">
                        <td style="padding:7px 10px; color:#24364B; font-weight:700;">2025</td>
                        <td style="padding:7px 10px; text-align:right; color:#73343A; font-weight:700; background:rgba(115,52,58,0.06);">30.15°C</td>
                        <td style="padding:7px 10px; text-align:right; color:var(--deep-navy); font-weight:700; background:rgba(5,150,105,0.06);">1.05</td>
                        <td style="padding:7px 10px; text-align:right; color:#4B6A8A; font-weight:700;">12.80</td>
                        <td style="padding:7px 10px; text-align:right; color:#24364B; font-weight:700;">0.42</td>
                        <td style="padding:7px 10px; text-align:center; font-size:10px; color:#374151;">Sangat Sesuai (Puncak Tangkapan)</td>
                        <td style="padding:7px 10px; text-align:center; font-size:9.5px; color:#6B7280;">Satelit Aqua MODIS & In-situ UPI</td>
                    </tr>
                    <tr style="background:#FFFFFF; border-top:2px solid #24364B;">
                        <td style="padding:7px 10px; color:#24364B; font-weight:800;">2026</td>
                        <td style="padding:7px 10px; text-align:right; color:#73343A; font-weight:800;">29.60°C</td>
                        <td style="padding:7px 10px; text-align:right; color:var(--deep-navy); font-weight:800;">0.88</td>
                        <td style="padding:7px 10px; text-align:right; color:#4B6A8A; font-weight:800;">14.20</td>
                        <td style="padding:7px 10px; text-align:right; color:#24364B; font-weight:800;">0.35</td>
                        <td style="padding:7px 10px; text-align:center; font-size:10px; color:#374151; font-weight:700;">Optimal untuk Scomberomorus</td>
                        <td style="padding:7px 10px; text-align:center; font-size:9.5px; color:#6B7280;">Pemantauan Terkini SCOMAR UPI</td>
                    </tr>
                </tbody>
            </table>

            {{-- Evaluasi & Verifikasi --}}
            <div style="display:flex; gap:12px;">
                <div style="flex:1.5; background:rgba(165,184,208,0.1); border-radius:10px; padding:12px 14px; border:1.5px solid #E5E7EB;">
                    <div style="font-size:11px; font-weight:800; color:#24364B; margin-bottom:6px;">Evaluasi Baku Mutu (KepMen LH No. 51/2004)</div>
                    <div style="font-size:10px; color:#374151; line-height:1.6;">
                        - <strong>Suhu (C):</strong> Seluruh data berada pada rentang 28.0 - 32.0 C (Sesuai baku mutu biota laut)<br/>
                        - <strong>Klorofil-a:</strong> Seluruh nilai berada pada rentang baku mutu optimal biota perairan (0.10 - 5.00 mg/m3)<br/>
                        - <strong>TSS (mg/L):</strong> Di bawah ambang batas 20 mg/L untuk terumbu karang seluruh tahun<br/>
                        - <strong>Arus Laut:</strong> Kisaran 0.28 - 0.42 m/s mendukung sirkulasi nutrien dan distribusi larva ikan
                    </div>
                </div>
                <div style="flex:1; background:rgba(115,52,58,0.05); border-radius:10px; padding:12px 14px; border:1.5px solid rgba(115,52,58,0.2);">
                    <div style="font-size:11px; font-weight:800; color:#73343A; margin-bottom:6px;">Verifikasi Riset</div>
                    <div style="font-size:10px; color:#374151; line-height:1.6;">
                        Data telah diverifikasi oleh tim Konsorsium SCOMAR Universitas Pendidikan Indonesia.<br/><br/>
                        <strong>Sistem:</strong> SCOMAR v1.0 — Pemantauan Digital<br/>
                        <strong>Lokasi:</strong> Perairan Pulau Tunda, Banten (-5.86°LS, 106.26°BT)
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Halaman 3 --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:8px; border-top:1px solid #E5E7EB; font-size:9.5px; color:#9CA3AF;">
            <span>SCOMAR — Sistem Pemantauan Ekosistem Laut Pulau Tunda | Universitas Pendidikan Indonesia</span>
            <span>Halaman 3 dari 3 — Akhir Dokumen</span>
        </div>
    </div>

</div>

{{-- ================================================================
     CSS KHUSUS HALAMAN PARAMETER OSEANOGRAFI
================================================================ --}}
@push('styles')
<style>
    /* ── CONTAINER UTAMA ─────────────────────────────────────────── */
    .ocean-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    /* ── HEADER ROW ──────────────────────────────────────────────── */
    .page-header-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .page-header-left {
        flex: 1;
        min-width: 280px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.5px;
        line-height: 1.25;
        margin-bottom: 6px;
    }

    .page-subtitle {
        font-size: 13.5px;
        color: var(--text-secondary);
        line-height: 1.55;
        max-width: 780px;
    }

    /* ── TOMBOL SCOMAR ───────────────────────────────────────────── */
    .btn-scomar {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition);
        border: none;
        outline: none;
        white-space: nowrap;
    }

    .btn-outline {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        color: var(--text-primary);
    }

    .btn-outline:hover {
        border-color: var(--cashmere-blue);
        background: rgba(165, 184, 208, 0.1);
        color: var(--text-primary);
    }
    .btn-primary {
        background: var(--merlot);
        border: 1px solid var(--merlot);
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(115, 52, 58, 0.25);
    }
    .btn-primary:hover {
        background: #5E2328;
        border-color: #5E2328;
        color: #FFFFFF;
        box-shadow: 0 4px 14px rgba(115, 52, 58, 0.35);
        transform: translateY(-1px);
    }

    /* ── STATUS BANNER ───────────────────────────────────────────── */
    .status-banner {
        background: rgba(165, 184, 208, 0.15);
        border: 1px solid var(--border-subtle);
        border-radius: 12px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-primary);
        font-size: 13px;
        line-height: 1.5;
    }

    .status-banner-icon {
        color: var(--merlot);
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    [data-theme="dark"] .status-banner-icon {
        color: var(--cashmere-blue);
    }

    .status-banner-text {
        flex: 1;
    }

    /* ── METRICS GRID (MODERN SCIENTIFIC CARDS) ─────────────────── */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
        margin-bottom: 20px;
    }

    @media (max-width: 1024px) {
        .metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 640px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }
    }

    .metric-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 14px 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        box-shadow: 0 4px 18px rgba(36, 54, 75, 0.05), 0 1px 3px rgba(36, 54, 75, 0.03);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        overflow: hidden;
        min-width: 0;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        border-color: rgba(165, 184, 208, 0.45);
        box-shadow: 0 8px 24px rgba(36, 54, 75, 0.09);
    }

    .metric-card.ocean-temp    { border-top: 3.5px solid var(--merlot); }
    .metric-card.ocean-chla    { border-top: 3.5px solid var(--deep-navy); }
    .metric-card.ocean-tss     { border-top: 3.5px solid #4B6A8A; }
    .metric-card.ocean-current { border-top: 3.5px solid var(--cashmere-blue); }

    .metric-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .metric-card:hover .metric-icon-box {
        transform: scale(1.05);
    }

    .metric-icon-box.merlot   { background: rgba(115, 52, 58, 0.12); color: var(--merlot); }
    .metric-icon-box.teal     { background: rgba(36, 54, 75, 0.10); color: var(--deep-navy); }
    .metric-icon-box.amber    { background: rgba(75, 106, 138, 0.12); color: #4B6A8A; }
    .metric-icon-box.navy     { background: rgba(165, 184, 208, 0.22); color: var(--deep-navy); }

    [data-theme="dark"] .metric-icon-box.merlot { background: rgba(128, 0, 32, 0.35); color: #FFA4A8; }
    [data-theme="dark"] .metric-icon-box.teal   { background: rgba(5, 150, 105, 0.25); color: #34D399; }
    [data-theme="dark"] .metric-icon-box.amber  { background: rgba(217, 119, 6, 0.25); color: #FBBF24; }
    [data-theme="dark"] .metric-icon-box.navy   { background: rgba(2, 132, 199, 0.25); color: #38BDF8; }

    .metric-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 0;
    }

    .metric-header-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 4px;
        margin-bottom: 3px;
    }

    .metric-label {
        font-size: 10px;
        color: var(--text-secondary);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        line-height: 1.25;
        white-space: normal;
        word-break: break-word;
    }

    .metric-period-pill {
        font-size: 9.5px;
        font-weight: 700;
        padding: 1px 5px;
        border-radius: 4px;
        background: rgba(165, 184, 208, 0.15);
        color: var(--text-secondary);
        white-space: nowrap;
        flex-shrink: 0;
    }

    .metric-value-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 6px;
        margin: 3px 0 6px 0;
        flex-wrap: wrap;
    }

    .metric-value {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.15;
        letter-spacing: -0.3px;
        white-space: nowrap;
    }

    .metric-value small {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-left: 2px;
    }

    /* Trend pills for top cards - proportional, compact, crisp */
    .trend-pill {
        display: inline-flex;
        align-items: center;
        gap: 2.5px;
        font-size: 9px;
        font-weight: 700;
        padding: 1.5px 5.5px;
        border-radius: 5px;
        white-space: nowrap;
        line-height: 1.2;
        flex-shrink: 0;
    }

    .trend-pill.trend-increasing {
        background: rgba(36, 54, 75, 0.08);
        color: var(--deep-navy);
    }
    .trend-pill.trend-decreasing {
        background: rgba(115, 52, 58, 0.08);
        color: var(--merlot);
    }
    .trend-pill.trend-stable {
        background: rgba(165, 184, 208, 0.15);
        color: var(--deep-navy);
    }

    [data-theme="dark"] .trend-pill.trend-increasing { background: rgba(165, 184, 208, 0.15); color: var(--cashmere-blue); }
    [data-theme="dark"] .trend-pill.trend-decreasing { background: rgba(115, 52, 58, 0.25); color: #e07a82; }
    [data-theme="dark"] .trend-pill.trend-stable     { background: rgba(165, 184, 208, 0.12); color: var(--cashmere-blue); }

    .metric-context-bar {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 10.5px;
        font-weight: 600;
        color: var(--text-secondary);
        border-top: 1px dashed var(--border-subtle);
        padding-top: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .metric-context-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .metric-context-dot.optimal  { background: #10B981; }
    .metric-context-dot.high     { background: var(--deep-navy); }
    .metric-context-dot.safe     { background: #4B6A8A; }
    .metric-context-dot.moderate { background: var(--cashmere-blue); }

        /* ── FILTER CARD: ANALISIS PARAMETER OSEANOGRAFI BERDASARKAN TAHUN DAN MUSIM ── */
    .filter-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 18px 22px;
        box-shadow: var(--card-shadow);
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-subtle);
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .filter-title svg {
        color: var(--merlot);
    }

    [data-theme="dark"] .filter-title svg {
        color: var(--cashmere-blue);
    }

    .filter-status-info {
        font-size: 12.5px;
        color: var(--text-secondary);
    }

    .filter-status-info strong {
        color: var(--merlot);
    }

    [data-theme="dark"] .filter-status-info strong {
        color: var(--cashmere-blue);
    }

    .seasonal-filter-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .seasonal-filter-group {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .seasonal-filter-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    /* ── YEAR DROPDOWN STYLES ─────────────────────────────────── */
    .year-dropdown-wrapper {
        position: relative;
        display: inline-block;
    }

    .year-dropdown-trigger {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        border: 1.5px solid var(--merlot);
        background: var(--merlot);
        color: #FFFFFF;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 148px;
        box-shadow: 0 2px 8px rgba(115, 52, 58, 0.22);
        white-space: nowrap;
    }

    .year-dropdown-trigger:hover {
        background: #5E2328;
        border-color: #5E2328;
        box-shadow: 0 4px 14px rgba(115, 52, 58, 0.35);
        transform: translateY(-1px);
    }

    .year-dropdown-icon {
        display: flex;
        align-items: center;
        opacity: 0.85;
        flex-shrink: 0;
    }

    .year-dropdown-label {
        flex: 1;
        text-align: left;
    }

    .year-dropdown-chevron {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .year-dropdown-chevron.rotated {
        transform: rotate(180deg);
    }

    .year-dropdown-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        min-width: 100%;
        background: var(--bg-surface);
        border: 1.5px solid var(--border-subtle);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(36, 54, 75, 0.14), 0 2px 8px rgba(36, 54, 75, 0.08);
        overflow: hidden;
        z-index: 100;
        padding: 4px;
    }

    .year-dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        padding: 9px 12px;
        border: none;
        border-radius: 7px;
        background: transparent;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-primary);
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
        text-align: left;
        white-space: nowrap;
    }

    .year-dropdown-item:hover {
        background: rgba(115, 52, 58, 0.08);
        color: var(--merlot);
    }

    .year-dropdown-item.selected {
        background: rgba(115, 52, 58, 0.12);
        color: var(--merlot);
        font-weight: 700;
    }

    .year-dropdown-item-check {
        display: flex;
        align-items: center;
        width: 16px;
        height: 16px;
        color: var(--merlot);
        flex-shrink: 0;
    }

    .year-dropdown-item-check.placeholder {
        visibility: hidden;
    }

    .seasonal-season-legend {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .season-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        background: rgba(165, 184, 208, 0.15);
        color: var(--text-secondary);
    }

    .season-badge.barat      { border-left: 3px solid #24364B; }
    .season-badge.peralihan1 { border-left: 3px solid #A5B8D0; }
    .season-badge.timur      { border-left: 3px solid #73343A; }
    .season-badge.peralihan2 { border-left: 3px solid #4B6A8A; }

    /* Keterangan Data Musiman Belum Tersedia */
    .seasonal-empty-hint {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 8px;
        background: rgba(165, 184, 208, 0.14);
        border: 1px dashed var(--cashmere-blue);
        color: var(--text-secondary);
        font-size: 11.5px;
        line-height: 1.45;
        margin-top: 10px;
    }

    .seasonal-empty-hint svg {
        color: var(--merlot);
        flex-shrink: 0;
    }

    [data-theme="dark"] .seasonal-empty-hint svg {
        color: var(--cashmere-blue);
    }

    /* ── CHARTS GRID (2x2 MODERN RESEARCH SUITE) ─────────────────── */
    .ocean-charts-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
        margin-bottom: 22px;
    }

    /* Pemisah halaman khusus ekspor PDF (tidak tampak di layar normal) */
    .html2pdf__page-break {
        display: block;
        height: 0;
        margin: 0;
        padding: 0;
        border: none;
        line-height: 0;
        page-break-before: always;
        break-before: page;
    }

    .chart-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 22px 24px;
        box-shadow: 0 4px 20px rgba(36, 54, 75, 0.05), 0 1px 3px rgba(36, 54, 75, 0.03);
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .chart-card:hover {
        border-color: rgba(165, 184, 208, 0.45);
        box-shadow: 0 8px 28px rgba(36, 54, 75, 0.08), 0 2px 6px rgba(36, 54, 75, 0.04);
        transform: translateY(-2px);
    }

    .chart-header {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 8px;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .chart-header-top-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        min-width: 0;
        flex-wrap: nowrap;
    }

    .chart-title-badge-row {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 0;
        flex-shrink: 0;
        min-width: 0;
    }

    .chart-code-badge {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        padding: 2px 6px;
        border-radius: 4px;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .chart-code-badge.merlot { background: rgba(115, 52, 58, 0.12); color: var(--merlot); }
    .chart-code-badge.teal   { background: rgba(36, 54, 75, 0.10); color: var(--deep-navy); }
    .chart-code-badge.amber  { background: rgba(75, 106, 138, 0.12); color: #4B6A8A; }
    .chart-code-badge.navy   { background: rgba(165, 184, 208, 0.22); color: var(--deep-navy); }

    [data-theme="dark"] .chart-code-badge.merlot { background: rgba(128, 0, 32, 0.35); color: #FFA4A8; }
    [data-theme="dark"] .chart-code-badge.teal   { background: rgba(5, 150, 105, 0.25); color: #34D399; }
    [data-theme="dark"] .chart-code-badge.amber  { background: rgba(217, 119, 6, 0.25); color: #FBBF24; }
    [data-theme="dark"] .chart-code-badge.navy   { background: rgba(2, 132, 199, 0.25); color: #38BDF8; }

    .chart-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: -0.2px;
        margin: 0;
        white-space: nowrap;
    }

    .chart-header-sub-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        width: 100%;
        min-width: 0;
    }

    .chart-subtitle {
        font-size: 11px;
        color: var(--text-secondary);
        line-height: 1.4;
        margin: 0;
        flex: 1;
        min-width: 0;
    }

    .chart-header-badges {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .spl-mode-toggle-group {
        display: inline-flex;
        align-items: center;
        background: #F1F5F9;
        padding: 2px;
        border-radius: 6px;
        border: 1px solid #E2E8F0;
        gap: 2px;
        flex-shrink: 0;
    }

    [data-theme="dark"] .spl-mode-toggle-group {
        background: #1E293B;
        border-color: #334155;
    }

    .spl-mode-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2.5px 7px;
        font-size: 10px;
        font-weight: 600;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #64748B;
        background: transparent;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .spl-mode-btn:hover {
        color: var(--merlot);
    }

    .spl-mode-btn.active {
        background: #FFFFFF;
        color: var(--merlot);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        font-weight: 700;
    }

    [data-theme="dark"] .spl-mode-btn.active {
        background: #0F172A;
        color: #FFA4A8;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }

    @media (max-width: 768px) {
        .chart-header-top-row {
            flex-wrap: wrap;
            gap: 6px;
        }
        .chart-header-sub-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .chart-header-badges {
            flex-wrap: wrap;
        }
    }

    .chart-tag {
        font-size: 10px;
        font-weight: 700;
        padding: 2.5px 7px;
        border-radius: 5px;
        white-space: nowrap;
    }

    .chart-tag.merlot { background: rgba(115, 52, 58, 0.12); color: var(--merlot); }
    .chart-tag.teal   { background: rgba(36, 54, 75, 0.10); color: var(--deep-navy); }
    .chart-tag.amber  { background: rgba(75, 106, 138, 0.12); color: #4B6A8A; }
    .chart-tag.navy   { background: rgba(165, 184, 208, 0.22); color: var(--deep-navy); }

    [data-theme="dark"] .chart-tag.merlot { background: rgba(128, 0, 32, 0.35); color: #FFA4A8; }
    [data-theme="dark"] .chart-tag.teal   { background: rgba(5, 150, 105, 0.25); color: #34D399; }
    [data-theme="dark"] .chart-tag.amber  { background: rgba(217, 119, 6, 0.25); color: #FBBF24; }
    [data-theme="dark"] .chart-tag.navy   { background: rgba(2, 132, 199, 0.25); color: #38BDF8; }

    /* ── TREND BADGE IN HEADER ───────────────────────────────────── */
    .trend-badge {
        display: inline-flex;
        align-items: center;
        gap: 3.5px;
        padding: 2px 7px;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .trend-badge.trend-increasing {
        background: rgba(36, 54, 75, 0.08);
        color: var(--deep-navy);
        border: 1px solid rgba(36, 54, 75, 0.20);
    }
    .trend-badge.trend-decreasing {
        background: rgba(115, 52, 58, 0.08);
        color: var(--merlot);
        border: 1px solid rgba(115, 52, 58, 0.20);
    }
    .trend-badge.trend-stable {
        background: rgba(165, 184, 208, 0.15);
        color: var(--deep-navy);
        border: 1px solid rgba(165, 184, 208, 0.35);
    }

    [data-theme="dark"] .trend-badge.trend-increasing { background: rgba(165, 184, 208, 0.15); color: var(--cashmere-blue); }
    [data-theme="dark"] .trend-badge.trend-decreasing { background: rgba(115, 52, 58, 0.25);   color: #e07a82; }
    [data-theme="dark"] .trend-badge.trend-stable     { background: rgba(165, 184, 208, 0.12); color: var(--cashmere-blue); }

    /* ── STATISTICAL SUMMARY (COMPACT FLUID GRID NEVER OVERFLOWS) ── */
    .stats-text-row {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 6px !important;
        padding: 6px 0 10px 0 !important;
        margin-bottom: 8px !important;
        border-bottom: 1px solid var(--border-subtle) !important;
        background: none !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        box-shadow: none !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
    }

    .stat-text-divider {
        display: none !important;
    }

    .stat-text-item {
        display: flex !important;
        flex-direction: column !important;
        gap: 1px !important;
        min-width: 0 !important;
        overflow: hidden !important;
        border-right: 1px solid var(--border-subtle) !important;
        padding-right: 6px !important;
        background: none !important;
        border-top: none !important;
        border-left: none !important;
        border-bottom: none !important;
        box-shadow: none !important;
    }

    .stat-text-item:last-child {
        border-right: none !important;
        padding-right: 0 !important;
    }

    .stat-text-label {
        font-size: 8.5px !important;
        font-weight: 700 !important;
        color: var(--text-secondary) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.2px !important;
        display: flex !important;
        align-items: center !important;
        gap: 2px !important;
        line-height: 1.15 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .stat-text-year {
        font-size: 8px !important;
        font-weight: 600 !important;
        color: var(--text-muted) !important;
        text-transform: none !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .stat-text-val-row {
        display: flex !important;
        align-items: baseline !important;
        gap: 2px !important;
        margin-top: 1px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
    }

    .stat-text-num {
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--text-primary) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        letter-spacing: -0.2px !important;
        line-height: 1.15 !important;
    }

    /* Nilai Terkini: Dibuat Lebih Menonjol tapi tetap pas dalam kartu */
    .stat-text-num.prominent {
        font-size: 15.5px !important;
        font-weight: 800 !important;
        letter-spacing: -0.3px !important;
    }

    .stat-text-unit {
        font-size: 9px !important;
        font-weight: 600 !important;
        color: var(--text-secondary) !important;
    }

    /* Warna Akses Ilmiah Premium */
    .stat-text-num.prominent.burgundy { color: var(--merlot); }
    .stat-text-num.prominent.emerald  { color: var(--deep-navy); }
    .stat-text-num.prominent.amber    { color: #4B6A8A; }
    .stat-text-num.prominent.blue     { color: #24364B; }

    [data-theme="dark"] .stat-text-num.prominent.burgundy { color: #e07a82; }
    [data-theme="dark"] .stat-text-num.prominent.emerald  { color: var(--cashmere-blue); }
    [data-theme="dark"] .stat-text-num.prominent.amber    { color: #A5B8D0; }
    [data-theme="dark"] .stat-text-num.prominent.blue     { color: #CBD8E6; }

    .stat-text-unit {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .stat-text-divider {
        width: 1px;
        height: 30px;
        background: var(--border-subtle);
        opacity: 0.6;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .stats-text-row {
            flex-wrap: wrap;
            gap: 14px 18px;
        }
        .stat-text-divider {
            display: none;
        }
    }

    /* ── CARD FOOTER HINT & ACTION ───────────────────────────────── */
    .chart-footer-hint {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px dashed var(--border-subtle);
    }

    .chart-hint-left {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-quick-detail {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        background: rgba(165, 184, 208, 0.15);
        border: 1px solid var(--border-subtle);
        color: var(--text-primary);
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
    }

    .btn-quick-detail:hover {
        background: var(--merlot);
        color: #FFFFFF;
        border-color: var(--merlot);
        box-shadow: 0 2px 6px rgba(115, 52, 58, 0.25);
    }

    /* ── TABLE CARD ──────────────────────────────────────────────── */
    .table-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .table-header {
        padding: 18px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid var(--border-subtle);
    }

    .table-title {
        font-size: 15.5px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .table-subtitle {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .table-count-badge {
        font-size: 12px;
        color: var(--text-secondary);
        background: rgba(165, 184, 208, 0.15);
        padding: 4px 10px;
        border-radius: 6px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .scomar-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .scomar-table th {
        background: rgba(165, 184, 208, 0.1);
        color: var(--text-secondary);
        font-weight: 700;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 18px;
        border-bottom: 1px solid var(--border-subtle);
        white-space: nowrap;
    }

    .scomar-table td {
        padding: 13px 18px;
        border-bottom: 1px solid var(--border-subtle);
        color: var(--text-primary);
        vertical-align: middle;
    }

    .scomar-table tbody tr:hover {
        background: rgba(165, 184, 208, 0.08);
    }

    .year-tag {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 5px;
        background: rgba(165, 184, 208, 0.2);
        font-weight: 700;
        font-size: 12px;
        color: var(--deep-navy);
    }

    [data-theme="dark"] .year-tag {
        color: var(--cashmere-blue);
    }

    .badge-status-ocean {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(5, 150, 105, 0.12);
        color: var(--deep-navy);
    }

    .badge-score-ocean {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(165, 184, 208, 0.15);
        color: var(--text-secondary);
    }

    .badge-ocean-mini {
        display: inline-block;
        padding: 1.5px 5.5px;
        border-radius: 4px;
        font-size: 9.5px;
        font-weight: 600;
        letter-spacing: -0.1px;
        white-space: nowrap;
    }
    .badge-optimal {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.28);
    }
    .badge-suboptimal {
        background: rgba(115, 52, 58, 0.12);
        color: var(--merlot);
        border: 1px solid rgba(115, 52, 58, 0.28);
    }
    .badge-above-optimal {
        background: rgba(217, 119, 6, 0.12);
        color: #D97706;
        border: 1px solid rgba(217, 119, 6, 0.28);
    }
    .badge-unavailable {
        background: rgba(100, 116, 139, 0.12);
        color: #64748B;
        border: 1px solid rgba(100, 116, 139, 0.28);
    }

    .badge-score-optimal {
        background: rgba(16, 185, 129, 0.15) !important;
        color: #059669 !important;
        border: 1px solid rgba(16, 185, 129, 0.35) !important;
        font-weight: 700 !important;
    }
    .badge-score-suboptimal {
        background: rgba(115, 52, 58, 0.15) !important;
        color: var(--merlot) !important;
        border: 1px solid rgba(115, 52, 58, 0.35) !important;
        font-weight: 700 !important;
    }
    .badge-score-pending {
        background: rgba(100, 116, 139, 0.15) !important;
        color: #475569 !important;
        border: 1px solid rgba(100, 116, 139, 0.35) !important;
        font-weight: 700 !important;
    }

    [data-theme="dark"] .badge-optimal {
        background: rgba(5, 150, 105, 0.25);
        color: #34D399;
        border-color: rgba(5, 150, 105, 0.40);
    }
    [data-theme="dark"] .badge-suboptimal {
        background: rgba(128, 0, 32, 0.30);
        color: #FFA4A8;
        border-color: rgba(128, 0, 32, 0.45);
    }
    [data-theme="dark"] .badge-above-optimal {
        background: rgba(217, 119, 6, 0.25);
        color: #FBBF24;
        border-color: rgba(217, 119, 6, 0.40);
    }
    [data-theme="dark"] .badge-unavailable {
        background: rgba(100, 116, 139, 0.25);
        color: #94A3B8;
        border-color: rgba(100, 116, 139, 0.40);
    }
    [data-theme="dark"] .badge-score-optimal {
        background: rgba(5, 150, 105, 0.30) !important;
        color: #34D399 !important;
    }
    [data-theme="dark"] .badge-score-suboptimal {
        background: rgba(128, 0, 32, 0.35) !important;
        color: #FFA4A8 !important;
    }
    [data-theme="dark"] .badge-score-pending {
        background: rgba(100, 116, 139, 0.30) !important;
        color: #CBD5E1 !important;
    }

    .btn-view-popup {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 600;
        border: 1px solid var(--border-subtle);
        background: var(--bg-surface);
        color: var(--text-primary);
        cursor: pointer;
        transition: all var(--transition);
    }

    .btn-view-popup:hover {
        background: var(--merlot);
        color: var(--white);
        border-color: var(--merlot);
    }

    /* ── POP-UP MODAL INFORMASI ANGKA FIX ────────────────────────── */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(18, 28, 38, 0.65);
        backdrop-filter: blur(4px);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-dialog-ocean {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        width: 95%;
        max-width: 900px;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        animation: modalScale 0.2s ease-out;
    }

    @keyframes modalScale {
        from { transform: scale(0.95); opacity: 0; }
        to   { transform: scale(1); opacity: 1; }
    }

    .modal-header {
        padding: 12px 20px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-shrink: 0;
    }

    .modal-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-icon-badge {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .modal-icon-badge.merlot { background: rgba(115, 52, 58, 0.12); color: var(--merlot); }

    [data-theme="dark"] .modal-icon-badge.merlot { background: rgba(115, 52, 58, 0.35); color: #FFA4A8; }

    .modal-title {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1px;
    }

    .modal-subtitle {
        font-size: 11px;
        color: var(--text-secondary);
    }

    .btn-close-modal {
        background: none;
        border: none;
        font-size: 22px;
        color: var(--text-muted);
        cursor: pointer;
        padding: 2px 6px;
        line-height: 1;
        transition: color var(--transition);
    }

    .btn-close-modal:hover {
        color: var(--text-primary);
    }

    .modal-body {
        padding: 14px 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        overflow-y: auto;
        overflow-x: hidden;
        max-height: calc(92vh - 95px);
        flex: 1 1 auto;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    .modal-body::-webkit-scrollbar-thumb {
        background: rgba(165, 184, 208, 0.35);
        border-radius: 4px;
    }
        max-height: calc(90vh - 100px);
    }

    /* Kotak 4 Angka Fix di Dalam Pop-up: Tampil 4 Kolom di Layar Laptop */
    .fix-grid-values {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }

    @media (max-width: 720px) {
        .fix-grid-values {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .fix-box-item {
        border-radius: 8px;
        padding: 7px 10px;
        display: flex;
        flex-direction: column;
        gap: 2px;
        border: 1px solid var(--border-subtle);
    }

    .fix-box-item.temp    { background: rgba(115, 52, 58, 0.06); border-left: 3.5px solid var(--merlot); }
    .fix-box-item.chla    { background: rgba(5, 150, 105, 0.06); border-left: 3.5px solid var(--deep-navy); }
    .fix-box-item.tss     { background: rgba(217, 119, 6, 0.06); border-left: 3.5px solid #4B6A8A; }
    .fix-box-item.current { background: rgba(36, 54, 75, 0.06); border-left: 3.5px solid var(--deep-navy); }

    .fix-box-label {
        font-size: 9px;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .fix-box-number {
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .fix-box-number .number-val {
        font-size: 17px;
        font-weight: 800;
        color: var(--text-primary);
        font-feature-settings: "tnum";
        line-height: 1.2;
    }

    .fix-box-number .number-unit {
        font-size: 10.5px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .fix-box-note {
        font-size: 9px;
        color: var(--text-muted);
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Scroll container untuk tabel di dalam modal angka fix */
    .fix-table-scroll-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0;
    }

    .fix-table-scroll-container::-webkit-scrollbar {
        height: 6px;
    }
    .fix-table-scroll-container::-webkit-scrollbar-thumb {
        background: rgba(165, 184, 208, 0.4);
        border-radius: 4px;
    }

    /* Rincian 4 Parameter & Arus per Musim di Modal */
    .fix-current-season-box {
        margin: 4px 0;
        border: 1px solid var(--border-subtle);
        border-radius: 10px;
        background: var(--bg-surface);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .fix-current-season-header {
        background: rgba(165, 184, 208, 0.08);
        padding: 8px 14px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px;
    }

    .fix-current-season-table {
        width: 100%;
        min-width: 580px;
        border-collapse: collapse;
        font-size: 11.5px;
        text-align: left;
    }

    .fix-current-season-table th {
        padding: 8px 12px;
        background: rgba(165, 184, 208, 0.12);
        color: var(--text-secondary);
        font-weight: 700;
        border-bottom: 1px solid var(--border-subtle);
        font-size: 11px;
        white-space: nowrap;
        letter-spacing: 0.2px;
    }

    .fix-current-season-table td {
        padding: 8px 12px;
        border-bottom: 1px solid var(--border-subtle);
        color: var(--text-primary);
        font-size: 11.5px;
        vertical-align: middle;
    }

    .fix-current-season-table tr:last-child td {
        border-bottom: none;
    }

    .fix-current-season-table tbody tr:hover td {
        background: rgba(165, 184, 208, 0.06);
    }

    .fix-direction-badge {
        display: inline-block;
        padding: 1.5px 6px;
        border-radius: 4px;
        font-size: 10.5px;
        font-weight: 600;
        background: rgba(36, 54, 75, 0.08);
        color: var(--text-primary);
        border: 1px solid var(--border-subtle);
    }

    [data-theme="dark"] .fix-direction-badge {
        background: rgba(165, 184, 208, 0.15);
        color: #E2E8F0;
        border-color: rgba(165, 184, 208, 0.28);
    }

    .fix-table-num {
        font-weight: 700;
        font-feature-settings: "tnum";
    }
    .fix-table-num.val-temp    { color: var(--merlot); }
    .fix-table-num.val-chla    { color: var(--deep-navy); }
    .fix-table-num.val-tss     { color: #4B6A8A; }
    .fix-table-num.val-current { color: var(--deep-navy); }

    [data-theme="dark"] .fix-table-num.val-temp    { color: #FF858B !important; }
    [data-theme="dark"] .fix-table-num.val-chla    { color: #34D399 !important; }
    [data-theme="dark"] .fix-table-num.val-tss     { color: #60A5FA !important; }
    [data-theme="dark"] .fix-table-num.val-current { color: #A78BFA !important; }

    /* Keterangan Analisis */
    .fix-analysis-box {
        background: rgba(165, 184, 208, 0.08);
        border: 1px solid var(--border-subtle);
        border-radius: 8px;
        padding: 8px 12px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .analysis-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11.5px;
        gap: 10px;
    }

    .analysis-label {
        color: var(--text-secondary);
    }

    .analysis-val {
        color: var(--text-primary);
        text-align: right;
    }

    .modal-footer {
        padding: 8px 18px;
        border-top: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        background: rgba(165, 184, 208, 0.05);
        flex-shrink: 0;
    }

    /* ── ANIMASI LOADING SPINNER ────────────────────────────────── */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    /* ── RESPONSIVE DESIGN (FLUID PROPORTIONAL) ──────────────────── */
    @media (max-width: 1280px) {
        .metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1100px) {
        .ocean-charts-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 640px) {
        .metrics-grid {
            grid-template-columns: minmax(0, 1fr);
        }
        .fix-grid-values {
            grid-template-columns: 1fr;
        }
    }

    /* ── PRINT STYLESHEET (CLEAN PDF PRINT) ──────────────────────── */
    @media print {
        .sidebar,
        .app-navbar,
        .app-footer,
        .no-print,
        .page-header-actions,
        .modal-backdrop,
        .chart-footer-hint,
        .btn-view-popup {
            display: none !important;
        }

        .main-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
        }

        .page-content {
            padding: 0 !important;
            background: #FFFFFF !important;
        }

        body {
            background: #FFFFFF !important;
            color: #000000 !important;
        }

        .html2pdf__page-break {
            display: block !important;
            height: 0 !important;
            page-break-before: always !important;
            break-before: page !important;
        }

        .metric-card, .chart-card, .table-card {
            box-shadow: none !important;
            border: 1px solid #CCCCCC !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
            margin-bottom: 16px;
        }

        /* Pastikan tabel tidak terpotong antar halaman */
        .table-responsive, table, tbody tr {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        /* Grafik selalu utuh satu blok */
        .ocean-charts-grid, .charts-grid, .chart-canvas-wrapper {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        /* Pindahkan ke halaman baru agar tabel & grafik tidak terpotong */
        .pdf-page-break, .table-card {
            break-before: page !important;
            page-break-before: always !important;
        }
    }

    /* ── RESPONSIVE PARAMETER OSEANOGRAFI ─────────────────────── */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        .page-header-actions {
            width: 100%;
            flex-wrap: wrap;
        }
    }

    @media (max-width: 640px) {
        .page-header h1 {
            font-size: 18px;
        }
        .metric-card, .chart-card {
            padding: 14px 14px;
        }
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }
    }

    @media (max-width: 480px) {
        .page-header h1 {
            font-size: 16px;
        }
        .metrics-grid {
            gap: 10px;
        }
    }
</style>
@endpush

{{-- ================================================================
     SCRIPTS: INISIALISASI 4 GRAFIK CHART.JS, KLIK POP-UP & UNDUH PDF
================================================================ --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
// ── Fungsi Ekspor Data Parameter Oseanografi ke CSV (Resmi SCOMAR) ────
window.exportOceanToCsv = function () {
    const btn = document.getElementById('btnExportOceanCsv');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = `
            <svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
            </svg>
            <span>Mengekspor CSV...</span>
        `;
        btn.disabled = true;
    }

    let selectedYear = '2026';
    try {
        const oceanArea = document.getElementById('exportOceanPdfArea');
        if (oceanArea && oceanArea._x_dataStack && oceanArea._x_dataStack[0]?.selectedYear) {
            selectedYear = String(oceanArea._x_dataStack[0].selectedYear);
        } else {
            const triggerLabel = document.querySelector('.year-dropdown-label');
            if (triggerLabel) {
                const match = triggerLabel.textContent.match(/\d{4}/);
                if (match) selectedYear = match[0];
            }
        }
    } catch (e) {
        selectedYear = '2026';
    }

    const downloadUrl = "{{ route('charts.oceanographic.export-csv') }}?year=" + encodeURIComponent(selectedYear);
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = downloadUrl;
    document.body.appendChild(iframe);

    setTimeout(() => {
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
        setTimeout(() => iframe.remove(), 2500);
    }, 1200);
};

// ── Fungsi Unduh Laporan PDF Parameter Oseanografi ──
window.exportOceanToPdf = async function () {
    const btn = document.getElementById('btnDownloadPdf');
    const originalContent = btn ? btn.innerHTML : '';

    if (btn) {
        btn.innerHTML = `
            <svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
            </svg>
            <span>Mengunduh PDF HD...</span>
        `;
        btn.disabled = true;
    }

    const prevScrollX = window.scrollX;
    const prevScrollY = window.scrollY;
    const pdfContainer = document.getElementById('scomarPdfOceanReport');

    if (!pdfContainer) {
        if (btn) { btn.innerHTML = originalContent; btn.disabled = false; }
        return;
    }

    const originalParent      = pdfContainer.parentNode;
    const originalNextSibling = pdfContainer.nextSibling;
    const originalStyle       = pdfContainer.getAttribute('style') || '';

    try {
        // ── 1. Ambil dataURL dari 4 canvas Chart.js (HD PNG) ──────────────
        const canvasMap = {
            temp:    document.getElementById('tempChartCanvas'),
            chla:    document.getElementById('chlaChartCanvas'),
            tss:     document.getElementById('tssChartCanvas'),
            current: document.getElementById('currentChartCanvas')
        };
        const imgTemp    = document.getElementById('pdfImgTemp');
        const imgChla    = document.getElementById('pdfImgChla');
        const imgTss     = document.getElementById('pdfImgTss');
        const imgCurrent = document.getElementById('pdfImgCurrent');

        if (canvasMap.temp    && imgTemp)    imgTemp.src    = canvasMap.temp.toDataURL('image/png', 1.0);
        if (canvasMap.chla    && imgChla)    imgChla.src    = canvasMap.chla.toDataURL('image/png', 1.0);
        if (canvasMap.tss     && imgTss)     imgTss.src     = canvasMap.tss.toDataURL('image/png', 1.0);
        if (canvasMap.current && imgCurrent) imgCurrent.src = canvasMap.current.toDataURL('image/png', 1.0);

        await Promise.all(
            [imgTemp, imgChla, imgTss, imgCurrent]
                .filter(Boolean)
                .map(img => img.decode ? img.decode().catch(() => {}) : Promise.resolve())
        );

        // ── 2. Isi tanggal cetak ──────────────────────────────────────────
        const pdfDate = document.getElementById('pdfOceanDate');
        if (pdfDate) {
            pdfDate.textContent = new Date().toLocaleDateString('id-ID', {
                day: '2-digit', month: 'long', year: 'numeric'
            });
        }

        // ── 3. Pindahkan ke body sebagai elemen biasa (BUKAN position:absolute) ──
        // html2pdf.js mengkloning elemen sumber lalu merender klon di dalam overlay-nya sendiri.
        // Jika kita set position:absolute pada elemen sumber, klon akan mewarisi properti itu
        // dan lepas dari container html2pdf → halaman PDF jadi kosong.
        // Solusi: pindahkan ke body sebagai block biasa sehingga html2pdf bisa mengkloning
        // dengan benar tanpa terpengaruh margin sidebar (260px) + padding wrapper (28px).
        document.body.appendChild(pdfContainer);
        pdfContainer.setAttribute('style',
            'display:block !important; visibility:visible !important; ' +
            'width:1122px; background:#FFFFFF; ' +
            'font-family:"Plus Jakarta Sans",sans-serif; color:#1A1A2E; ' +
            'box-sizing:border-box; margin:0; padding:0;'
        );

        window.scrollTo(0, 0);
        await new Promise(resolve => setTimeout(resolve, 150));

        // ── 4. Ekspor dengan html2pdf ─────────────────────────────────────
        const opt = {
            margin:      [0, 0, 0, 0],
            filename:    'Laporan-Parameter-Oseanografi-Pulau-Tunda-SCOMAR.pdf',
            image:       { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale:           2,
                useCORS:         true,
                logging:         false,
                allowTaint:      false,
                imageTimeout:    0,
                backgroundColor: '#FFFFFF',
                scrollX:         0,
                scrollY:         0,
                width:           1122,
                windowWidth:     1122,
                onclone: function(clonedDoc) {
                    clonedDoc.body.style.margin   = '0';
                    clonedDoc.body.style.padding  = '0';
                    clonedDoc.body.style.overflow = 'visible';
                    const el = clonedDoc.getElementById('scomarPdfOceanReport');
                    if (el) {
                        el.style.position  = 'relative';
                        el.style.left      = '0';
                        el.style.top       = '0';
                        el.style.transform = 'none';
                    }
                }
            },
            jsPDF: {
                unit:        'mm',
                format:      'a4',
                orientation: 'landscape',
                compress:    true
            },
            pagebreak: { mode: ['css', 'legacy'] }
        };

        await html2pdf().set(opt).from(pdfContainer).save();

    } catch (err) {
        console.error('Gagal mengunduh PDF:', err);
        alert('Gagal mengunduh PDF. Silakan coba lagi.');
    } finally {
        if (pdfContainer) {
            pdfContainer.setAttribute('style', originalStyle);
            pdfContainer.style.display = 'none';
            if (originalParent) {
                originalParent.insertBefore(pdfContainer, originalNextSibling);
            }
        }
        window.scrollTo(prevScrollX, prevScrollY);
        if (btn) { btn.innerHTML = originalContent; btn.disabled = false; }
    }
};

document.addEventListener('DOMContentLoaded', function () {

    const dbOcean = @json($oceanData);
    let years = ['2021', '2022', '2023', '2024', '2025', '2026'];
    let dataTemp        = [28.75, 29.10, 29.85, 29.40, 30.15, 29.60];
    let dataChlorophyll = [0.62, 0.74, 0.91, 0.83, 1.05, 0.88];
    let dataTss         = [16.40, 14.80, 13.50, 15.10, 12.80, 14.20];
    let dataCurrent     = [0.28, 0.32, 0.38, 0.34, 0.42, 0.35];

    if (Array.isArray(dbOcean) && dbOcean.length > 0) {
        years = dbOcean.map(d => String(d.year));
        dataTemp = dbOcean.map(d => parseFloat(d.temp));
        dataChlorophyll = dbOcean.map(d => parseFloat(d.chlorophyll));
        dataTss = dbOcean.map(d => parseFloat(d.tss));
        dataCurrent = dbOcean.map(d => parseFloat(d.current));
    }

    // ── DATASET MUSIMAN SPL, KLOROFIL-A, & ARUS PULAU TUNDA (4 MUSIM DALAM SETIAP TAHUN) ──
    const seasonalSplData = {{ Js::from($seasonalSplFromCsv ?? []) }};
    const seasonalSplComparison = {{ Js::from($seasonalSplComparison ?? ['years' => [], 'series' => []]) }};

    const seasonalChlaData = {{ Js::from($seasonalChlaFromCsv ?? []) }};
    const seasonalChlaComparison = {{ Js::from($seasonalChlaComparison ?? ['years' => [], 'series' => []]) }};

    const seasonalCurrentData = {{ Js::from($seasonalCurrentFromCsv ?? []) }};
    const seasonalCurrentComparison = {{ Js::from($seasonalCurrentComparison ?? ['years' => [], 'series' => []]) }};
    const currentDetailsByYear = {{ Js::from($currentDetailsByYear ?? []) }};

    const seasonalTssData = {{ Js::from($seasonalTssFromCsv ?? []) }};
    const seasonalTssComparison = {{ Js::from($seasonalTssComparison ?? ['years' => [], 'series' => []]) }};

    const seasonLabels = ['Musim Barat', 'Peralihan I', 'Musim Timur', 'Peralihan II'];
    const seasonPeriods = [
        'Desember - Februari',
        'Maret - Mei',
        'Juni - Agustus',
        'September - November'
    ];

    window.hasSeasonalDataset = function (yr) {
        return Boolean(seasonalSplData && seasonalSplData[String(yr)]);
    };

    window.getSeasonalOceanStats = function (field, targetYear) {
        const yr = String(targetYear || '2026');
        const isSpl = field === 'temp';
        const dataset = isSpl ? seasonalSplData : seasonalChlaData;
        const unit = isSpl ? '°C' : 'mg/m³';

        if (!dataset || !dataset[yr]) {
            let annualItem = dbOcean ? dbOcean.find(d => String(d.year) === yr) : null;
            const val = annualItem ? parseFloat(annualItem[field]) : 0;
            return {
                hasSeasonal: false,
                latest: val ? val.toFixed(2) : '0.00',
                latestSeason: 'Rata-rata Tahunan',
                avg: val ? val.toFixed(2) : '0.00',
                max: val ? val.toFixed(2) : '0.00',
                maxSeason: '-',
                min: val ? val.toFixed(2) : '0.00',
                minSeason: '-',
                diffFormatted: '0.00',
                pctFormatted: '0.0%',
                trend: 'stable',
                trendLabel: 'Tahunan Fix',
                trendClass: 'trend-stable'
            };
        }

        const vals = dataset[yr];
        const seasons = ['Musim Barat', 'Peralihan I', 'Musim Timur', 'Peralihan II'];
        const latestVal = vals[3]; // Peralihan II sebagai pengukuran penutup
        const latestSeason = 'Peralihan II';

        let minVal = Infinity, maxVal = -Infinity;
        let minSeason = '', maxSeason = '';
        vals.forEach((v, idx) => {
            if (v > maxVal) { maxVal = v; maxSeason = seasons[idx]; }
            if (v < minVal) { minVal = v; minSeason = seasons[idx]; }
        });

        const sum = vals.reduce((a, b) => a + b, 0);
        const avg = sum / vals.length;

        const diff = vals[2] - vals[0]; // Puncak Musim Timur vs Musim Barat
        const diffFormatted = (diff > 0 ? '+' : '') + diff.toFixed(2);
        const pctChange = vals[0] !== 0 ? (diff / vals[0]) * 100 : 0;
        const pctFormatted = (pctChange > 0 ? '+' : '') + pctChange.toFixed(1) + '%';

        let trend = 'stable', trendLabel = 'Stabil', trendClass = 'trend-stable';
        if (diff > 0.05) {
            trend = 'increasing'; trendLabel = 'Puncak Musim Timur'; trendClass = 'trend-increasing';
        } else if (diff < -0.05) {
            trend = 'decreasing'; trendLabel = 'Menurun'; trendClass = 'trend-decreasing';
        }

        return {
            hasSeasonal: true,
            latest: latestVal.toFixed(2),
            latestSeason: latestSeason,
            avg: avg.toFixed(2),
            max: maxVal.toFixed(2),
            maxSeason: maxSeason,
            min: minVal.toFixed(2),
            minSeason: minSeason,
            diffFormatted: diffFormatted,
            pctFormatted: pctFormatted,
            trend: trend,
            trendLabel: trendLabel,
            trendClass: trendClass
        };
    };

    let chartInstances = [];
    let currentSelectedYear = '2026';

    // Fungsi warna tema dinamis (Light / Dark) untuk tampilan saintifik
    function getChartThemeColors() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            isDark:         isDark,
            textColor:      isDark ? '#CBD5E1' : '#334155',
            textColorMuted: isDark ? '#94A3B8' : '#64748B',
            gridColor:      isDark ? 'rgba(165, 184, 208, 0.10)' : 'rgba(165, 184, 208, 0.22)',
            axisBorder:     isDark ? 'rgba(165, 184, 208, 0.20)' : 'rgba(165, 184, 208, 0.35)',
            tooltipBg:      isDark ? '#0F172A' : '#1E293B',
            tooltipBorder:  isDark ? 'rgba(148, 163, 184, 0.25)' : 'rgba(255, 255, 255, 0.15)',
            tooltipText:    '#F8FAFC'
        };
    }

    function getComparisonSeriesColors(isDark) {
        if (isDark) {
            return {
                barat:      { border: '#38BDF8', bg: 'rgba(56, 189, 248, 0.12)' },
                peralihan1: { border: '#818CF8', bg: 'rgba(129, 140, 248, 0.12)' },
                timur:      { border: '#FF7582', bg: 'rgba(255, 117, 130, 0.15)' },
                peralihan2: { border: '#CBD5E1', bg: 'rgba(203, 213, 225, 0.12)' }
            };
        }
        return {
            barat:      { border: '#24364B', bg: 'rgba(36, 54, 75, 0.04)' },
            peralihan1: { border: '#5B7A9C', bg: 'rgba(91, 122, 156, 0.04)' },
            timur:      { border: '#73343A', bg: 'rgba(115, 52, 58, 0.06)' },
            peralihan2: { border: '#8E9FA3', bg: 'rgba(142, 159, 163, 0.04)' }
        };
    }

    function getParamSingleColor(param, isDark) {
        if (isDark) {
            switch (param) {
                case 'temp':    return '#FF7582';
                case 'chla':    return '#34D399';
                case 'tss':     return '#60A5FA';
                case 'current': return '#A78BFA';
                default:        return '#38BDF8';
            }
        }
        switch (param) {
            case 'temp':    return '#73343A';
            case 'chla':    return '#24364B';
            case 'tss':     return '#4B6A8A';
            case 'current': return '#24364B';
            default:        return '#24364B';
        }
    }

    // Handler klik pada titik grafik untuk membuka pop-up
    function triggerPopupForYear(index, isSeasonal) {
        if (window.Alpine) {
            const container = document.querySelector('.ocean-container');
            if (container && container.__x) {
                const targetYr = isSeasonal ? container.__x.$data.selectedYear : years[index];
                if (targetYr) {
                    container.__x.$data.openFixDetail(targetYr);
                }
            }
        }
    }

    // Pembuat Grafik Universal Berkualitas Tinggi (Smooth Curve & Soft Transparent Gradient)
    function buildOceanChart(canvasId, label, initialLabels, initialData, color, unit, yMin, yMax, isSeasonal) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        const colors = getChartThemeColors();
        const chartCtx = ctx.getContext('2d');

        // Gradien halus transparan multi-stop berstandar ilmiah premium
        const gradient = chartCtx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, color + '34');   // ~20% opasitas di puncak kurva
        gradient.addColorStop(0.45, color + '10'); // ~6% opasitas di tengah
        gradient.addColorStop(1, color + '00');   // 0% opasitas di dasar

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: initialLabels,
                datasets: [{
                    label: label,
                    data: initialData,
                    borderColor: color,
                    backgroundColor: gradient,
                    borderWidth: 2.8,
                    pointBackgroundColor: colors.isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: color,
                    pointBorderWidth: 2.5,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: color,
                    pointHoverBorderColor: colors.isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 16,
                    fill: true,
                    tension: 0.42,
                    capBezierPoints: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: 3,
                animation: {
                    duration: 900,
                    easing: 'easeOutQuart'
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                onClick: function (evt, elements) {
                    if (elements && elements.length > 0) {
                        const clickedIndex = elements[0].index;
                        if (canvasId === 'tempChartCanvas' && splViewMode === 'comparison') {
                            const yearsList = (seasonalSplComparison && seasonalSplComparison.years) ? seasonalSplComparison.years : ['2021', '2022', '2023', '2024', '2025', '2026'];
                            const clickedYear = yearsList[clickedIndex];
                            if (window.Alpine) {
                                const container = document.querySelector('.ocean-container');
                                if (container && container.__x && clickedYear) {
                                    container.__x.$data.openFixDetail(clickedYear);
                                }
                            }
                        } else if (canvasId === 'chlaChartCanvas' && chlaViewMode === 'comparison') {
                            const yearsList = (seasonalChlaComparison && seasonalChlaComparison.years) ? seasonalChlaComparison.years : ['2021', '2022', '2023', '2024', '2025', '2026'];
                            const clickedYear = yearsList[clickedIndex];
                            if (window.Alpine) {
                                const container = document.querySelector('.ocean-container');
                                if (container && container.__x && clickedYear) {
                                    container.__x.$data.openFixDetail(clickedYear);
                                }
                            }
                        } else if (canvasId === 'currentChartCanvas' && currentViewMode === 'comparison') {
                            const yearsList = (seasonalCurrentComparison && seasonalCurrentComparison.years) ? seasonalCurrentComparison.years : ['2021', '2022', '2023', '2024', '2025', '2026'];
                            const clickedYear = yearsList[clickedIndex];
                            if (window.Alpine) {
                                const container = document.querySelector('.ocean-container');
                                if (container && container.__x && clickedYear) {
                                    container.__x.$data.openFixDetail(clickedYear);
                                }
                            }
                        } else if (canvasId === 'tssChartCanvas' && tssViewMode === 'comparison') {
                            const yearsList = (seasonalTssComparison && seasonalTssComparison.years) ? seasonalTssComparison.years : ['2021', '2022', '2023', '2024', '2025', '2026'];
                            const clickedYear = yearsList[clickedIndex];
                            if (window.Alpine) {
                                const container = document.querySelector('.ocean-container');
                                if (container && container.__x && clickedYear) {
                                    container.__x.$data.openFixDetail(clickedYear);
                                }
                            }
                        } else {
                            triggerPopupForYear(clickedIndex, isSeasonal);
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: true,
                        backgroundColor: colors.tooltipBg,
                        titleColor: '#FFFFFF',
                        titleFont: { family: 'Plus Jakarta Sans', size: 12.5, weight: '700' },
                        bodyColor: '#F1F5F9',
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12, weight: '500' },
                        borderColor: colors.tooltipBorder,
                        borderWidth: 1,
                        padding: { top: 10, right: 14, bottom: 10, left: 14 },
                        cornerRadius: 10,
                        displayColors: true,
                        boxPadding: 5,
                        usePointStyle: true,
                        callbacks: {
                            title: function (items) {
                                if (canvasId === 'tempChartCanvas' && splViewMode === 'comparison') {
                                    return 'Tahun Pengamatan ' + items[0].label;
                                }
                                if (canvasId === 'chlaChartCanvas' && chlaViewMode === 'comparison') {
                                    return 'Tahun Pengamatan ' + items[0].label;
                                }
                                if (canvasId === 'currentChartCanvas' && currentViewMode === 'comparison') {
                                    return 'Tahun Pengamatan ' + items[0].label;
                                }
                                if (canvasId === 'tssChartCanvas' && tssViewMode === 'comparison') {
                                    return 'Tahun Pengamatan ' + items[0].label;
                                }
                                if (isSeasonal) {
                                    const container = document.querySelector('.ocean-container');
                                    const activeYr = container && container.__x ? container.__x.$data.selectedYear : currentSelectedYear;
                                    const p = seasonPeriods[items[0].dataIndex] || '';
                                    return items[0].label + ' ' + activeYr + (p ? ' (' + p + ')' : '');
                                }
                                return 'Tahun Pengamatan ' + items[0].label;
                            },
                            label: function (context) {
                                if (canvasId === 'tempChartCanvas' && splViewMode === 'comparison') {
                                    return `  ${context.dataset.label}: ${context.parsed.y} °C`;
                                }
                                if (canvasId === 'chlaChartCanvas' && chlaViewMode === 'comparison') {
                                    const yVal = context.parsed.y !== null && !isNaN(context.parsed.y) ? context.parsed.y : 'Data Belum Tersedia';
                                    return `  ${context.dataset.label}: ${yVal} mg/m³`;
                                }
                                if (canvasId === 'currentChartCanvas') {
                                    if (currentViewMode === 'comparison') {
                                        const yVal = context.parsed.y !== null && !isNaN(context.parsed.y) ? context.parsed.y : 'Data Belum Tersedia';
                                        return `  ${context.dataset.label}: ${yVal} m/s`;
                                    } else {
                                        const container = document.querySelector('.ocean-container');
                                        const activeYr = container && container.__x ? container.__x.$data.selectedYear : currentSelectedYear;
                                        const detailsList = (currentDetailsByYear && currentDetailsByYear[activeYr]) ? currentDetailsByYear[activeYr] : [];
                                        const itemDetail = detailsList[context.dataIndex];
                                        const knotStr = (itemDetail && itemDetail.speed_knot !== undefined && itemDetail.speed_knot !== null) ? ` (${itemDetail.speed_knot.toFixed(2)} knot)` : '';
                                        const dirStr = (itemDetail && itemDetail.direction !== undefined && itemDetail.direction !== null) ? ` — Arah: ${itemDetail.direction}° (${itemDetail.direction_str})` : '';
                                        return `  ${label}: ${context.parsed.y} m/s${knotStr}${dirStr}`;
                                    }
                                }
                                if (canvasId === 'tssChartCanvas') {
                                    const rawVal = context.raw;
                                    if (rawVal === null || rawVal === undefined || isNaN(rawVal)) {
                                        return `  ${context.dataset.label || label}: Null (Data tidak tersedia)`;
                                    }
                                    const yVal = typeof rawVal === 'number' ? rawVal.toFixed(3) : rawVal;
                                    return `  ${context.dataset.label || label}: ${yVal} mg/L`;
                                }
                                return `  ${label}: ${context.parsed.y} ${unit}`;
                            },
                            afterLabel: function () {
                                if (canvasId === 'tempChartCanvas' && splViewMode === 'comparison') {
                                    return '  (Sumber: dataset CSV storage/app/private/datasets/)';
                                }
                                if (canvasId === 'chlaChartCanvas' && chlaViewMode === 'comparison') {
                                    return '  (Sumber: dataset CSV storage/app/private/datasets/klorofil-a/)';
                                }
                                if (canvasId === 'currentChartCanvas' && currentViewMode === 'comparison') {
                                    return '  (Sumber: data_arus_clean.csv storage/app/private/datasets/arus/)';
                                }
                                if (canvasId === 'tssChartCanvas') {
                                    return '  (Sumber: rata-rata nilai tss.csv storage/app/private/datasets/tss/)';
                                }
                                return '  (Klik titik untuk rincian data ilmiah)';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: {
                            display: true,
                            color: colors.axisBorder
                        },
                        ticks: {
                            color: colors.textColor,
                            font: { family: 'Plus Jakarta Sans', size: 11.5, weight: '600' },
                            padding: 8
                        }
                    },
                    y: {
                        min: yMin,
                        max: yMax,
                        grid: {
                            color: colors.gridColor,
                            borderDash: [5, 5],
                            drawBorder: false,
                            drawTicks: false
                        },
                        border: { display: false },
                        ticks: {
                            color: colors.textColorMuted,
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' },
                            padding: 8,
                            callback: function (val) {
                                return val + ' ' + unit;
                            }
                        }
                    }
                }
            }
        });

        return chart;
    }

    // Inisialisasi 4 Grafik: Menggunakan Palet Resmi SCOMAR Dinamis (Light / Dark)
    function initAllCharts() {
        const colors = getChartThemeColors();
        const isDark = colors.isDark;
        const defaultYear = '2026';
        const initSplData = seasonalSplData[defaultYear] || [28.89, 30.28, 29.67, 29.38];
        const initChlaData = seasonalChlaData[defaultYear] || [0.997973, 0.709759, 0.627030, null];
        const initCurrentData = seasonalCurrentData[defaultYear] || [0.130, 0.089, 0.324, 0.375];
        const initTssData = (seasonalTssData && seasonalTssData[defaultYear]) ? seasonalTssData[defaultYear] : [null, 14.153, 18.785, 26.522];

        chartInstances = [
            // 1. Suhu Permukaan Laut (SPL) (Mode Musiman)
            buildOceanChart('tempChartCanvas', 'Suhu Permukaan Laut', seasonLabels, initSplData, getParamSingleColor('temp', isDark), '°C', 27.5, 32.0, true),

            // 2. Klorofil-a (Mode Musiman / Komparasi)
            buildOceanChart('chlaChartCanvas', 'Konsentrasi Klorofil-a', seasonLabels, initChlaData, getParamSingleColor('chla', isDark), 'mg/m³', 0.5, 1.35, true),

            // 3. TSS (Mode Musiman / Komparasi)
            buildOceanChart('tssChartCanvas', 'Total Suspended Solids', seasonLabels, initTssData, getParamSingleColor('tss', isDark), 'mg/L', 0.0, 30.0, true),

            // 4. Kecepatan Arus (Mode Musiman / Komparasi)
            buildOceanChart('currentChartCanvas', 'Kecepatan Arus Laut', seasonLabels, initCurrentData, getParamSingleColor('current', isDark), 'm/s', 0.0, 0.50, true)
        ];
    }

    initAllCharts();

    let splViewMode = 'year';
    let chlaViewMode = 'comparison';
    let currentViewMode = 'comparison';
    let tssViewMode = 'comparison';

    // ── KONTROL MODE TAMPILAN GRAFIK SPL (PER MUSIM vs KOMPARASI 4 MUSIM) ─
    window.switchSplViewMode = function (mode) {
        splViewMode = mode;
        const chartTemp = chartInstances[0];
        if (!chartTemp) return;

        const colors = getChartThemeColors();
        const ctx = chartTemp.ctx;
        const isDark = colors.isDark;

        if (mode === 'year') {
            const yr = currentSelectedYear;
            const dataYear = seasonalSplData[yr] || [null, null, null, null];
            const splColor = getParamSingleColor('temp', isDark);

            const gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, splColor + '34');
            gradient.addColorStop(0.45, splColor + '10');
            gradient.addColorStop(1, splColor + '00');

            chartTemp.data.labels = seasonLabels;
            chartTemp.data.datasets = [{
                label: 'Suhu Permukaan Laut (' + yr + ')',
                data: dataYear,
                borderColor: splColor,
                backgroundColor: gradient,
                borderWidth: 2.8,
                pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                pointBorderColor: splColor,
                pointBorderWidth: 2.5,
                pointRadius: 5.5,
                pointHoverRadius: 8.5,
                pointHoverBackgroundColor: splColor,
                pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                pointHoverBorderWidth: 3,
                hitRadius: 16,
                fill: true,
                tension: 0.42,
                capBezierPoints: true
            }];

            chartTemp.options.plugins.legend.display = false;
            chartTemp.options.scales.x.ticks.color = colors.textColor;
            chartTemp.options.scales.y.ticks.color = colors.textColorMuted;
            chartTemp.options.scales.y.grid.color = colors.gridColor;
            chartTemp.options.scales.x.border.color = colors.axisBorder;
            chartTemp.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            chartTemp.options.plugins.tooltip.borderColor = colors.tooltipBorder;
            chartTemp.options.scales.y.min = 27.5;
            chartTemp.options.scales.y.max = 32.0;
            chartTemp.update();
        } else if (mode === 'comparison') {
            const yearsList = (seasonalSplComparison && seasonalSplComparison.years) ? seasonalSplComparison.years : ['2021', '2022', '2023', '2024', '2025', '2026'];
            const s = (seasonalSplComparison && seasonalSplComparison.series) ? seasonalSplComparison.series : {};
            const sc = getComparisonSeriesColors(isDark);

            chartTemp.data.labels = yearsList;
            chartTemp.data.datasets = [
                {
                    label: 'Musim Barat (MUSIM BARAT RATA2.csv)',
                    data: (s.barat && s.barat.data) ? s.barat.data : [],
                    borderColor: sc.barat.border,
                    backgroundColor: sc.barat.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.barat.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.barat.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35
                },
                {
                    label: 'Musim Peralihan I (RATA RATA MUSIM PERALIHAN 1.csv)',
                    data: (s.peralihan1 && s.peralihan1.data) ? s.peralihan1.data : [],
                    borderColor: sc.peralihan1.border,
                    backgroundColor: sc.peralihan1.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.peralihan1.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.peralihan1.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35
                },
                {
                    label: 'Musim Timur (MUSIM TIMUR RATA RATA.csv)',
                    data: (s.timur && s.timur.data) ? s.timur.data : [],
                    borderColor: sc.timur.border,
                    backgroundColor: sc.timur.bg,
                    borderWidth: 3.0,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.timur.border,
                    pointBorderWidth: 2.6,
                    pointRadius: 6.0,
                    pointHoverRadius: 9.0,
                    pointHoverBackgroundColor: sc.timur.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35
                },
                {
                    label: 'Musim Peralihan II (MUSIM PERALIHAN 2 RATA RATA.csv)',
                    data: (s.peralihan2 && s.peralihan2.data) ? s.peralihan2.data : [],
                    borderColor: sc.peralihan2.border,
                    backgroundColor: sc.peralihan2.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.peralihan2.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.peralihan2.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35
                }
            ];

            chartTemp.options.plugins.legend.display = true;
            chartTemp.options.plugins.legend.position = 'top';
            chartTemp.options.plugins.legend.align = 'end';
            chartTemp.options.plugins.legend.labels = {
                usePointStyle: true,
                boxWidth: 8,
                boxHeight: 8,
                padding: 12,
                color: colors.textColor,
                font: { family: 'Plus Jakarta Sans', size: 10.5, weight: '600' }
            };
            chartTemp.options.scales.x.ticks.color = colors.textColor;
            chartTemp.options.scales.y.ticks.color = colors.textColorMuted;
            chartTemp.options.scales.y.grid.color = colors.gridColor;
            chartTemp.options.scales.x.border.color = colors.axisBorder;
            chartTemp.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            chartTemp.options.plugins.tooltip.borderColor = colors.tooltipBorder;
            chartTemp.options.scales.y.min = 28.0;
            chartTemp.options.scales.y.max = 31.5;

            chartTemp.update();
        }
    };

    // ── KONTROL MODE TAMPILAN GRAFIK KLOROFIL-A (KOMPARASI 4 MUSIM vs PER MUSIM) ─
    window.switchChlaViewMode = function (mode) {
        chlaViewMode = mode;
        const chartChla = chartInstances[1];
        if (!chartChla) return;

        const colors = getChartThemeColors();
        const ctx = chartChla.ctx;
        const isDark = colors.isDark;

        if (mode === 'year') {
            const yr = currentSelectedYear;
            const dataYear = seasonalChlaData[yr] || [null, null, null, null];
            const chlaColor = getParamSingleColor('chla', isDark);

            const gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, chlaColor + '34');
            gradient.addColorStop(0.45, chlaColor + '10');
            gradient.addColorStop(1, chlaColor + '00');

            chartChla.data.labels = seasonLabels;
            chartChla.data.datasets = [{
                label: 'Konsentrasi Klorofil-a (' + yr + ')',
                data: dataYear,
                borderColor: chlaColor,
                backgroundColor: gradient,
                borderWidth: 2.8,
                pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                pointBorderColor: chlaColor,
                pointBorderWidth: 2.5,
                pointRadius: 5.5,
                pointHoverRadius: 8.5,
                pointHoverBackgroundColor: chlaColor,
                pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                pointHoverBorderWidth: 3,
                hitRadius: 16,
                fill: true,
                tension: 0.42,
                capBezierPoints: true
            }];

            chartChla.options.plugins.legend.display = false;
            chartChla.options.scales.x.ticks.color = colors.textColor;
            chartChla.options.scales.y.ticks.color = colors.textColorMuted;
            chartChla.options.scales.y.grid.color = colors.gridColor;
            chartChla.options.scales.x.border.color = colors.axisBorder;
            chartChla.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            chartChla.options.plugins.tooltip.borderColor = colors.tooltipBorder;
            chartChla.options.scales.y.min = 0.5;
            chartChla.options.scales.y.max = 1.35;
            chartChla.update();
        } else if (mode === 'comparison') {
            const yearsList = (seasonalChlaComparison && seasonalChlaComparison.years) ? seasonalChlaComparison.years : ['2021', '2022', '2023', '2024', '2025', '2026'];
            const s = (seasonalChlaComparison && seasonalChlaComparison.series) ? seasonalChlaComparison.series : {};
            const sc = getComparisonSeriesColors(isDark);

            chartChla.data.labels = yearsList;
            chartChla.data.datasets = [
                {
                    label: 'Musim Barat (' + (s.barat?.file || 'BARAT KROLOFIL A.csv') + ')',
                    data: (s.barat && s.barat.data) ? s.barat.data : [],
                    borderColor: sc.barat.border,
                    backgroundColor: sc.barat.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.barat.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.barat.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                },
                {
                    label: 'Musim Peralihan I (' + (s.peralihan1?.file || 'CHL PERALIHAN 1.csv') + ')',
                    data: (s.peralihan1 && s.peralihan1.data) ? s.peralihan1.data : [],
                    borderColor: sc.peralihan1.border,
                    backgroundColor: sc.peralihan1.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.peralihan1.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.peralihan1.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                },
                {
                    label: 'Musim Timur (' + (s.timur?.file || 'CHL RATA RATA MUSIM TIMUR.csv') + ')',
                    data: (s.timur && s.timur.data) ? s.timur.data : [],
                    borderColor: sc.timur.border,
                    backgroundColor: sc.timur.bg,
                    borderWidth: 3.0,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.timur.border,
                    pointBorderWidth: 2.6,
                    pointRadius: 6.0,
                    pointHoverRadius: 9.0,
                    pointHoverBackgroundColor: sc.timur.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                },
                {
                    label: 'Musim Peralihan II (' + (s.peralihan2?.file || 'RATA RATA MUSIM PERALIHAN 2.csv') + ')',
                    data: (s.peralihan2 && s.peralihan2.data) ? s.peralihan2.data : [],
                    borderColor: sc.peralihan2.border,
                    backgroundColor: sc.peralihan2.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.peralihan2.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.peralihan2.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                }
            ];

            chartChla.options.plugins.legend.display = true;
            chartChla.options.plugins.legend.position = 'top';
            chartChla.options.plugins.legend.align = 'end';
            chartChla.options.plugins.legend.labels = {
                usePointStyle: true,
                boxWidth: 8,
                boxHeight: 8,
                padding: 12,
                color: colors.textColor,
                font: { family: 'Plus Jakarta Sans', size: 10.5, weight: '600' }
            };
            chartChla.options.scales.x.ticks.color = colors.textColor;
            chartChla.options.scales.y.ticks.color = colors.textColorMuted;
            chartChla.options.scales.y.grid.color = colors.gridColor;
            chartChla.options.scales.x.border.color = colors.axisBorder;
            chartChla.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            chartChla.options.plugins.tooltip.borderColor = colors.tooltipBorder;
            chartChla.options.scales.y.min = 0.5;
            chartChla.options.scales.y.max = 1.35;

            chartChla.update();
        }
    };

    // Aktifkan default tampilan Komparasi 4 Musim (2021-2026) untuk Klorofil-a
    window.switchChlaViewMode('comparison');

    // ── KONTROL MODE TAMPILAN GRAFIK KECEPATAN ARUS (KOMPARASI 4 MUSIM vs PER MUSIM) ─
    window.switchCurrentViewMode = function (mode) {
        currentViewMode = mode;
        const chartCur = chartInstances[3];
        if (!chartCur) return;

        const colors = getChartThemeColors();
        const ctx = chartCur.ctx;
        const isDark = colors.isDark;

        if (mode === 'year') {
            const yr = currentSelectedYear;
            const dataYear = seasonalCurrentData[yr] || [null, null, null, null];
            const currentColor = getParamSingleColor('current', isDark);

            const gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, currentColor + '34');
            gradient.addColorStop(0.45, currentColor + '10');
            gradient.addColorStop(1, currentColor + '00');

            chartCur.data.labels = seasonLabels;
            chartCur.data.datasets = [{
                label: 'Kecepatan Arus Laut (' + yr + ')',
                data: dataYear,
                borderColor: currentColor,
                backgroundColor: gradient,
                borderWidth: 2.8,
                pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                pointBorderColor: currentColor,
                pointBorderWidth: 2.5,
                pointRadius: 5.5,
                pointHoverRadius: 8.5,
                pointHoverBackgroundColor: currentColor,
                pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                pointHoverBorderWidth: 3,
                hitRadius: 16,
                fill: true,
                tension: 0.42,
                capBezierPoints: true
            }];

            chartCur.options.plugins.legend.display = false;
            chartCur.options.scales.x.ticks.color = colors.textColor;
            chartCur.options.scales.y.ticks.color = colors.textColorMuted;
            chartCur.options.scales.y.grid.color = colors.gridColor;
            chartCur.options.scales.x.border.color = colors.axisBorder;
            chartCur.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            chartCur.options.plugins.tooltip.borderColor = colors.tooltipBorder;
            chartCur.options.scales.y.min = 0.0;
            chartCur.options.scales.y.max = 0.50;
            chartCur.update();
        } else if (mode === 'comparison') {
            const yearsList = (seasonalCurrentComparison && seasonalCurrentComparison.years) ? seasonalCurrentComparison.years : ['2021', '2022', '2023', '2024', '2025', '2026'];
            const s = (seasonalCurrentComparison && seasonalCurrentComparison.series) ? seasonalCurrentComparison.series : {};
            const sc = getComparisonSeriesColors(isDark);

            chartCur.data.labels = yearsList;
            chartCur.data.datasets = [
                {
                    label: 'Musim Barat (DJF)',
                    data: (s.barat && s.barat.data) ? s.barat.data : [],
                    borderColor: sc.barat.border,
                    backgroundColor: sc.barat.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.barat.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.barat.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                },
                {
                    label: 'Musim Peralihan I (MAM)',
                    data: (s.peralihan1 && s.peralihan1.data) ? s.peralihan1.data : [],
                    borderColor: sc.peralihan1.border,
                    backgroundColor: sc.peralihan1.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.peralihan1.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.peralihan1.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                },
                {
                    label: 'Musim Timur (JJA)',
                    data: (s.timur && s.timur.data) ? s.timur.data : [],
                    borderColor: sc.timur.border,
                    backgroundColor: sc.timur.bg,
                    borderWidth: 3.0,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.timur.border,
                    pointBorderWidth: 2.6,
                    pointRadius: 6.0,
                    pointHoverRadius: 9.0,
                    pointHoverBackgroundColor: sc.timur.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                },
                {
                    label: 'Musim Peralihan II (SON)',
                    data: (s.peralihan2 && s.peralihan2.data) ? s.peralihan2.data : [],
                    borderColor: sc.peralihan2.border,
                    backgroundColor: sc.peralihan2.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.peralihan2.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.peralihan2.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                }
            ];

            chartCur.options.plugins.legend.display = true;
            chartCur.options.plugins.legend.position = 'top';
            chartCur.options.plugins.legend.align = 'end';
            chartCur.options.plugins.legend.labels = {
                usePointStyle: true,
                boxWidth: 8,
                boxHeight: 8,
                padding: 12,
                color: colors.textColor,
                font: { family: 'Plus Jakarta Sans', size: 10.5, weight: '600' }
            };
            chartCur.options.scales.x.ticks.color = colors.textColor;
            chartCur.options.scales.y.ticks.color = colors.textColorMuted;
            chartCur.options.scales.y.grid.color = colors.gridColor;
            chartCur.options.scales.x.border.color = colors.axisBorder;
            chartCur.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            chartCur.options.plugins.tooltip.borderColor = colors.tooltipBorder;
            chartCur.options.scales.y.min = 0.0;
            chartCur.options.scales.y.max = 0.50;

            chartCur.update();
        }
    };

    // Aktifkan default tampilan Komparasi 4 Musim (2021-2026) untuk Kecepatan Arus
    window.switchCurrentViewMode('comparison');

    // ── KONTROL MODE TAMPILAN GRAFIK TSS (KOMPARASI 4 MUSIM vs PER MUSIM) ─
    window.switchTssViewMode = function (mode) {
        tssViewMode = mode;
        const chartTss = chartInstances[2];
        if (!chartTss) return;

        const colors = getChartThemeColors();
        const ctx = chartTss.ctx;
        const isDark = colors.isDark;

        if (mode === 'year') {
            const yr = currentSelectedYear;
            const dataYear = (seasonalTssData && seasonalTssData[yr]) ? seasonalTssData[yr] : [null, null, null, null];
            const tssColor = getParamSingleColor('tss', isDark);

            const gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, tssColor + '34');
            gradient.addColorStop(0.45, tssColor + '10');
            gradient.addColorStop(1, tssColor + '00');

            chartTss.data.labels = seasonLabels;
            chartTss.data.datasets = [{
                label: 'Total Suspended Solids (' + yr + ')',
                data: dataYear,
                borderColor: tssColor,
                backgroundColor: gradient,
                borderWidth: 2.8,
                pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                pointBorderColor: tssColor,
                pointBorderWidth: 2.5,
                pointRadius: 5.5,
                pointHoverRadius: 8.5,
                pointHoverBackgroundColor: tssColor,
                pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                pointHoverBorderWidth: 3,
                hitRadius: 16,
                fill: true,
                tension: 0.42,
                capBezierPoints: true,
                spanGaps: true
            }];

            chartTss.options.plugins.legend.display = false;
            chartTss.options.scales.x.ticks.color = colors.textColor;
            chartTss.options.scales.y.ticks.color = colors.textColorMuted;
            chartTss.options.scales.y.grid.color = colors.gridColor;
            chartTss.options.scales.x.border.color = colors.axisBorder;
            chartTss.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            chartTss.options.plugins.tooltip.borderColor = colors.tooltipBorder;
            chartTss.options.scales.y.min = 0.0;
            chartTss.options.scales.y.max = 30.0;
            chartTss.update();
        } else if (mode === 'comparison') {
            const yearsList = (seasonalTssComparison && seasonalTssComparison.years) ? seasonalTssComparison.years : ['2021', '2022', '2023', '2024', '2025', '2026'];
            const s = (seasonalTssComparison && seasonalTssComparison.series) ? seasonalTssComparison.series : {};
            const sc = getComparisonSeriesColors(isDark);

            chartTss.data.labels = yearsList;
            chartTss.data.datasets = [
                {
                    label: 'Musim Barat',
                    data: (s.barat && s.barat.data) ? s.barat.data : [],
                    borderColor: sc.barat.border,
                    backgroundColor: sc.barat.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.barat.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.barat.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                },
                {
                    label: 'Musim Peralihan I',
                    data: (s.peralihan1 && s.peralihan1.data) ? s.peralihan1.data : [],
                    borderColor: sc.peralihan1.border,
                    backgroundColor: sc.peralihan1.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.peralihan1.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.peralihan1.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                },
                {
                    label: 'Musim Timur',
                    data: (s.timur && s.timur.data) ? s.timur.data : [],
                    borderColor: sc.timur.border,
                    backgroundColor: sc.timur.bg,
                    borderWidth: 3.0,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.timur.border,
                    pointBorderWidth: 2.6,
                    pointRadius: 6.0,
                    pointHoverRadius: 9.0,
                    pointHoverBackgroundColor: sc.timur.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                },
                {
                    label: 'Musim Peralihan II',
                    data: (s.peralihan2 && s.peralihan2.data) ? s.peralihan2.data : [],
                    borderColor: sc.peralihan2.border,
                    backgroundColor: sc.peralihan2.bg,
                    borderWidth: 2.6,
                    pointBackgroundColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointBorderColor: sc.peralihan2.border,
                    pointBorderWidth: 2.4,
                    pointRadius: 5.5,
                    pointHoverRadius: 8.5,
                    pointHoverBackgroundColor: sc.peralihan2.border,
                    pointHoverBorderColor: isDark ? '#1B293A' : '#FFFFFF',
                    pointHoverBorderWidth: 3,
                    hitRadius: 14,
                    fill: false,
                    tension: 0.35,
                    spanGaps: true
                }
            ];

            chartTss.options.plugins.legend.display = true;
            chartTss.options.plugins.legend.position = 'top';
            chartTss.options.plugins.legend.align = 'end';
            chartTss.options.plugins.legend.labels = {
                usePointStyle: true,
                boxWidth: 8,
                boxHeight: 8,
                padding: 12,
                color: colors.textColor,
                font: { family: 'Plus Jakarta Sans', size: 10.5, weight: '600' }
            };
            chartTss.options.scales.x.ticks.color = colors.textColor;
            chartTss.options.scales.y.ticks.color = colors.textColorMuted;
            chartTss.options.scales.y.grid.color = colors.gridColor;
            chartTss.options.scales.x.border.color = colors.axisBorder;
            chartTss.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            chartTss.options.plugins.tooltip.borderColor = colors.tooltipBorder;
            chartTss.options.scales.y.min = 0.0;
            chartTss.options.scales.y.max = 30.0;

            chartTss.update();
        }
    };

    // Aktifkan default tampilan Komparasi 4 Musim (2021-2026) untuk TSS
    window.switchTssViewMode('comparison');

    // ── FUNGSI REAKTIF UPDATE GRAFIK SAAT DROPDOWN TAHUN BERUBAH ───────
    window.updateOceanCharts = function (year) {
        const yr = String(year);
        currentSelectedYear = yr;

        // 1. Update Grafik SPL (Mode Musiman atau Komparasi)
        if (chartInstances[0]) {
            if (splViewMode === 'year') {
                window.switchSplViewMode('year');
            } else {
                window.switchSplViewMode('comparison');
            }
        }

        // 2. Update Grafik Klorofil-a (Mode Musiman atau Komparasi)
        if (chartInstances[1]) {
            if (chlaViewMode === 'year') {
                window.switchChlaViewMode('year');
            } else {
                window.switchChlaViewMode('comparison');
            }
        }

        // 3. Update Grafik TSS (Mode Musiman atau Komparasi)
        if (chartInstances[2]) {
            if (tssViewMode === 'year') {
                window.switchTssViewMode('year');
            } else {
                window.switchTssViewMode('comparison');
            }
        }

        // 4. Update Highlight Grafik Kecepatan Arus (Mode Musiman atau Komparasi)
        if (chartInstances[3]) {
            if (currentViewMode === 'year') {
                window.switchCurrentViewMode('year');
            } else {
                window.switchCurrentViewMode('comparison');
            }
        }
    };

    // Sinkronisasi dinamis saat pergantian mode Tema (Light / Dark)
    function refreshAllOceanCharts() {
        if (!chartInstances || chartInstances.length < 4) return;
        window.switchSplViewMode(splViewMode);
        window.switchChlaViewMode(chlaViewMode);
        window.switchTssViewMode(tssViewMode);
        window.switchCurrentViewMode(currentViewMode);
    }

    window.addEventListener('scomar:theme-changed', refreshAllOceanCharts);
    window.addEventListener('themeChanged', refreshAllOceanCharts);

    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            setTimeout(refreshAllOceanCharts, 60);
        });
    }

});
</script>
@endpush
@endsection
