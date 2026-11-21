{{-- ================================================================
     HALAMAN GRAFIK HASIL TANGKAPAN IKAN SCOMBEROMORUS (TENGGIRI)
     File: resources/views/charts/catches.blade.php

     Fitur Halaman:
     1. Kartu "Volume Tangkapan Tertinggi Seluruh Periode" (All-Time Peak)
     2. Kartu "Total Tangkapan Tahunan" & "Puncak Musiman Tahun Terpilih"
     3. Fitur "Analisis Hasil Tangkapan Berdasarkan Tahun dan Musim" dengan Filter Tahun
     4. Grafik Batang Sumbu X (4 Musim: Barat, Peralihan I, Timur, Peralihan II) vs Sumbu Y (kg)
     5. Grafik Perbandingan Hasil Tangkapan Antartahun (2021 - 2026)
     6. Tabel Rekapitulasi Hasil Tangkapan per Musim (Tahun, Musim, Total Tangkapan, Ekor, Trip, CPUE)
     7. Fitur Unduh Laporan PDF (Clean Print Stylesheet)
================================================================ --}}

@extends('layouts.app')

{{-- Judul tab browser --}}
@section('title', 'Grafik Hasil Tangkapan')

{{-- Judul halaman & breadcrumb di navbar --}}
@section('page-title', 'Grafik Hasil Tangkapan')
@section('page-breadcrumb', 'SCOMAR / Grafik Hasil Tangkapan')

@section('content')

<div
    class="catches-container"
    id="exportCatchesPdfArea"
    x-data="{
        // ── KONTROL FILTER TAHUN & SEARCH ─────────────────────────────────
        selectedYear: {{ Js::from($years->first() ?? '2026') }},
        availableYears: {{ Js::from($years) }},
        search: '',

        // ── DATA RESMI CSV (2021-2026) ────────────────────────────────────
        seasonalFlat: {{ Js::from($seasonalFlat ?? []) }},
        annualTotals: {{ Js::from($annualTotals ?? []) }},
        byYear: {{ Js::from($byYear ?? []) }},
        comparison: {{ Js::from($comparison ?? ['years' => [], 'series' => [], 'annual' => []]) }},
        overallStats: {{ Js::from($overallStats ?? []) }},

        // ── DATA DB (UNTUK DUKUNGAN CRUD ADMIN) ──────────────────────────
        dbCatches: {{ Js::from($catchesForAlpine ?? []) }},

        // ── MODE TAMPILAN TABEL & METRIK GRAFIK ─────────────────────────
        tableRecapMode: 'seasonal', // 'seasonal' (Rincian 4 Musim) | 'annual' (Rekapitulasi Tahunan)
        activeMetric: 'produksi',   // 'produksi' (ton) | 'trip' (trip) | 'cpue' (kg/trip)
        prodUnit: 'ton',            // 'ton' | 'kg'

        // ── STATE MODAL CRUD ─────────────────────────────────────────────
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false,
        showViewModal: false,
        activeItem: {
            id: '',
            year: '2026',
            season: 'Musim Barat',
            season_name: 'Musim Barat',
            period: 'Desember - Februari',
            produksi_ton: 0,
            produksi_kg: 0,
            trip: 1,
            cpue: 0,
            harga: null,
            nilai_juta: null,
            notes: ''
        },

        openView(item) {
            this.activeItem = {
                id: item.id || '',
                year: item.year || '2026',
                season: item.season || item.season_name || '-',
                season_name: item.season_name || item.season || '-',
                period: item.period || '-',
                produksi_ton: item.produksi_ton !== undefined ? item.produksi_ton : (item.totalCatch ? item.totalCatch / 1000 : null),
                produksi_kg: item.produksi_kg !== undefined ? item.produksi_kg : (item.totalCatch !== undefined ? item.totalCatch : null),
                trip: item.trip !== undefined ? item.trip : (item.trips !== undefined ? item.trips : null),
                cpue: item.cpue !== undefined ? item.cpue : null,
                harga: item.harga !== undefined ? item.harga : null,
                nilai_juta: item.nilai_juta !== undefined ? item.nilai_juta : null,
                notes: item.notes || ''
            };
            this.showViewModal = true;
        },
        openEdit(item) {
            this.activeItem = { ...item };
            this.showEditModal = true;
        },
        openDelete(item) {
            this.activeItem = { ...item };
            this.showDeleteModal = true;
        },

        // ── 1. HITUNG VOLUME TERTINGGI SELURUH PERIODE (ALL-TIME PEAK) ──
        getAllTimePeak() {
            if (this.overallStats && this.overallStats.peak) {
                return this.overallStats.peak;
            }
            if (this.seasonalFlat && this.seasonalFlat.length > 0) {
                const valid = this.seasonalFlat.filter(d => d.produksi_kg !== null);
                if (valid.length > 0) {
                    return valid.reduce((prev, curr) => (curr.produksi_kg > prev.produksi_kg) ? curr : prev);
                }
            }
            return { produksi_ton: 0, produksi_kg: 0, year: '-', season: '-' };
        },

        // ── 2. HITUNG TOTAL TANGKAPAN TAHUNAN (TAHUN TERPILIH) ──────
        getAnnualTotal() {
            const yr = String(this.selectedYear);
            if (this.annualTotals && this.annualTotals[yr]) {
                return this.annualTotals[yr];
            }
            const filtered = (this.seasonalFlat || []).filter(d => String(d.year) === yr && d.produksi_kg !== null);
            const totalKg = filtered.reduce((acc, curr) => acc + (curr.produksi_kg || 0), 0);
            const totalTrip = filtered.reduce((acc, curr) => acc + (curr.trip || 0), 0);
            return {
                year: yr,
                label: 'Total Tahun ' + yr,
                produksi_ton: totalKg > 0 ? Number((totalKg / 1000).toFixed(3)) : null,
                produksi_kg: totalKg > 0 ? totalKg : null,
                trip: totalTrip > 0 ? totalTrip : null,
                cpue: totalTrip > 0 ? Number((totalKg / totalTrip).toFixed(2)) : null,
                harga: null,
                nilai_juta: null
            };
        },

        // ── 3. HITUNG MUSIM TERTINGGI PADA TAHUN TERPILIH ────────────
        getPeakSeasonOfYear() {
            const yr = String(this.selectedYear);
            const filtered = (this.seasonalFlat || []).filter(d => String(d.year) === yr && d.produksi_kg !== null);
            if (!filtered.length) {
                return { season_name: '-', produksi_ton: null, produksi_kg: null };
            }
            const peak = filtered.reduce((prev, curr) => (curr.produksi_kg > prev.produksi_kg) ? curr : prev);
            return {
                season_name: peak.season_name,
                produksi_ton: peak.produksi_ton,
                produksi_kg: peak.produksi_kg
            };
        },

        // ── 4. DATA TABEL REKAPITULASI SESUAI FILTER TAHUN & PENCARIAN ──
        filteredSeasonalRecap() {
            let list = this.seasonalFlat || [];
            if (this.selectedYear !== 'all') {
                list = list.filter(d => String(d.year) === String(this.selectedYear));
            }
            if (this.search && this.search.trim() !== '') {
                const q = this.search.toLowerCase();
                list = list.filter(d =>
                    (d.season_name && d.season_name.toLowerCase().includes(q)) ||
                    (d.period && d.period.toLowerCase().includes(q)) ||
                    String(d.year).includes(q)
                );
            }
            return list;
        },

        filteredAnnualRecap() {
            let list = Object.values(this.annualTotals || {});
            if (this.search && this.search.trim() !== '') {
                const q = this.search.toLowerCase();
                list = list.filter(d =>
                    String(d.year).includes(q) ||
                    (d.label && d.label.toLowerCase().includes(q))
                );
            }
            return list.sort((a, b) => b.year - a.year);
        },

        // Ubah tahun aktif & sinkronisasi grafik
        changeYear(year) {
            this.selectedYear = String(year);
            if (window.updateSeasonalCharts) {
                window.updateSeasonalCharts(year);
            }
        },

        // Ubah metrik aktif (produksi, trip, cpue)
        setMetric(metric) {
            this.activeMetric = metric;
            if (window.updateSeasonalMetric) {
                window.updateSeasonalMetric(metric);
            }
        },

        formatNullVal(val, unit = '', decimals = 2) {
            if (val === null || val === undefined || isNaN(val)) {
                return 'Null';
            }
            return Number(val).toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + (unit ? ' ' + unit : '');
        }
    }"
>

    {{-- ============================================================
         1. HEADER HALAMAN & ACTIONS
    ============================================================ --}}
    <div class="page-header-row">
        <div class="page-header-left">
            <h1 class="page-title">
                Grafik Hasil Tangkapan Ikan Scomberomorus
            </h1>
            <p class="page-subtitle">
                Analisis variasi musiman hasil tangkapan ikan Tenggiri (<em>Scomberomorus commerson</em>) di perairan Pulau Tunda berdasarkan Musim Barat, Peralihan I, Musim Timur, dan Peralihan II.
            </p>
        </div>

        <div class="page-header-actions no-print">
            {{-- Tombol Ekspor CSV --}}
            <button
                type="button"
                id="btnExportCatchesCsv"
                class="btn-scomar btn-outline"
                onclick="exportCatchesToCsv()"
                title="Unduh data hasil tangkapan dalam format CSV (Excel Ready)"
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
                id="btnDownloadCatchesPdf"
                class="btn-scomar btn-primary"
                onclick="exportCatchesToPdf()"
                title="Unduh langsung laporan analisis hasil tangkapan dalam format PDF"
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
         2. BANNER STATUS DATASET
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
            <strong>Dataset Resmi CSV:</strong> Data terintegrasi dari <code>storage/app/private/datasets/hasil tangkapan/data hasil tangkapan.csv</code> (Periode 2021–2026). Data 4 musim dan rekapitulasi tahunan dipisahkan secara otomatis.
        </div>
    </div>

    {{-- ============================================================
         3. RINGKASAN METRIK KPI (4 KARTU STATISTIK)
         - Kartu 1: Volume Tertinggi Seluruh Periode (All-Time Peak)
         - Kartu 2: Total Tangkapan Tahunan (Tahun Terpilih)
         - Kartu 3: Puncak Musim pada Tahun Terpilih
         - Kartu 4: CPUE Tahunan Tahun Terpilih
    ============================================================ --}}
    <div class="metrics-grid">

        {{-- KARTU 1: VOLUME TANGKAPAN TERTINGGI SELURUH PERIODE --}}
        <div class="metric-card peak-all-time">
            <div class="metric-icon-box merlot">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                    <path d="M4 22h16"></path>
                    <path d="M10 14.66V17c0 .55-.45 1-1 1H7.5"></path>
                    <path d="M14 14.66V17c0 .55.45 1 1 1h1.5"></path>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                </svg>
            </div>
            <div class="metric-body">
                <span class="metric-label">Volume Tertinggi Seluruh Periode</span>
                <span class="metric-value">
                    <span x-text="getAllTimePeak() && getAllTimePeak().produksi_ton !== undefined ? getAllTimePeak().produksi_ton.toFixed(3) : '-'">13.486</span>
                    <small>ton</small>
                </span>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                    <span x-text="getAllTimePeak() && getAllTimePeak().produksi_kg !== undefined ? '(' + Number(getAllTimePeak().produksi_kg).toLocaleString('id-ID') + ' kg)' : ''">(13.486 kg)</span>
                </div>
                <div class="metric-trend peak-badge" style="margin-top:6px;">
                    <span class="peak-dot"></span>
                    <span x-text="getAllTimePeak() ? getAllTimePeak().season + ' (' + getAllTimePeak().year + ')' : '-'">Musim Barat (2021)</span>
                </div>
            </div>
        </div>

        {{-- KARTU 2: TOTAL TANGKAPAN TAHUNAN (SESUAI TAHUN TERPILIH) --}}
        <div class="metric-card">
            <div class="metric-icon-box cashmere">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6"  y1="20" x2="6"  y2="14"></line>
                    <line x1="2"  y1="20" x2="22" y2="20"></line>
                </svg>
            </div>
            <div class="metric-body">
                <span class="metric-label">
                    Total Tangkapan Tahun <span x-text="selectedYear"></span>
                </span>
                <span class="metric-value">
                    <span x-text="getAnnualTotal() && getAnnualTotal().produksi_ton !== null ? getAnnualTotal().produksi_ton.toFixed(3) : 'Null'">13.029</span>
                    <small>ton</small>
                </span>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                    <span x-text="getAnnualTotal() && getAnnualTotal().produksi_kg !== null ? '(' + Number(getAnnualTotal().produksi_kg).toLocaleString('id-ID') + ' kg)' : ''">(13.029 kg)</span>
                </div>
                <div class="metric-trend neutral" style="margin-top:6px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                    <span>Total Rekapitulasi CSV</span>
                </div>
            </div>
        </div>

        {{-- KARTU 3: MUSIM DENGAN TANGKAPAN TERTINGGI (TAHUN TERPILIH) --}}
        <div class="metric-card">
            <div class="metric-icon-box navy">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
            </div>
            <div class="metric-body">
                <span class="metric-label">
                    Musim Tertinggi (<span x-text="selectedYear"></span>)
                </span>
                <span class="metric-value font-title" x-text="getPeakSeasonOfYear().season_name">
                    Musim Peralihan I
                </span>
                <div class="metric-trend positive" style="margin-top:6px;">
                    <span>Sebesar <strong x-text="getPeakSeasonOfYear().produksi_ton !== null ? getPeakSeasonOfYear().produksi_ton.toFixed(3) + ' ton' : 'Null'">6.246 ton</strong></span>
                </div>
            </div>
        </div>

        {{-- KARTU 4: NILAI CPUE TAHUN TERPILIH --}}
        <div class="metric-card">
            <div class="metric-icon-box amber">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    <path d="M2 12h20"></path>
                </svg>
            </div>
            <div class="metric-body">
                <span class="metric-label">CPUE Tahun <span x-text="selectedYear"></span></span>
                <span class="metric-value">
                    <span x-text="getAnnualTotal() && getAnnualTotal().cpue !== null ? Number(getAnnualTotal().cpue).toFixed(2) : 'Null'">6.88</span>
                    <small>kg/trip</small>
                </span>
                <div class="metric-trend neutral" style="margin-top:6px;">
                    <span>Trip: <strong x-text="getAnnualTotal() && getAnnualTotal().trip !== null ? Number(getAnnualTotal().trip).toLocaleString('id-ID') + ' trip' : 'Null'">5.579 trip</strong></span>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================================
         4. FITUR ANALISIS HASIL TANGKAPAN BERDASARKAN TAHUN & MUSIM
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
                <span>Analisis Hasil Tangkapan Berdasarkan Tahun dan Musim</span>
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
         5. VISUALISASI GRAFIK UTAMA (CHART.JS)
         - Grafik 1 (Kiri): Grafik Batang 4 Musim (Tahun Terpilih)
         - Grafik 2 (Kanan): Grafik Perbandingan Antartahun
    ============================================================ --}}
    <div class="charts-grid">

        {{-- GRAFIK 1: GRAFIK BATANG 4 MUSIM UNTUK TAHUN TERPILIH --}}
        <div class="chart-card chart-card-large">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">
                        Distribusi 4 Musim (Tahun <span x-text="selectedYear"></span>)
                    </h2>
                    <p class="chart-subtitle">
                        <span x-show="activeMetric === 'produksi'">Perbandingan volume produksi hasil tangkapan (ton & kg) pada 4 musim.</span>
                        <span x-show="activeMetric === 'trip'">Jumlah trip penangkapan ikan pada 4 musim tahun <strong x-text="selectedYear"></strong>.</span>
                        <span x-show="activeMetric === 'cpue'">Tingkat produktivitas penangkapan (CPUE kg/trip) pada 4 musim tahun <strong x-text="selectedYear"></strong>.</span>
                    </p>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    {{-- Toggle Parameter Grafik 1: Produksi, Trip, CPUE --}}
                    <div class="catches-toggle-group no-print">
                        <button
                            type="button"
                            class="catches-metric-pill"
                            :class="activeMetric === 'produksi' ? 'active' : ''"
                            @click="setMetric('produksi')"
                            title="Tampilkan grafik produksi hasil tangkapan (ton)"
                        >
                            Produksi (ton)
                        </button>
                        <button
                            type="button"
                            class="catches-metric-pill"
                            :class="activeMetric === 'trip' ? 'active' : ''"
                            @click="setMetric('trip')"
                            title="Tampilkan grafik jumlah trip penangkapan"
                        >
                            Trip
                        </button>
                        <button
                            type="button"
                            class="catches-metric-pill"
                            :class="activeMetric === 'cpue' ? 'active' : ''"
                            @click="setMetric('cpue')"
                            title="Tampilkan grafik CPUE (Catch Per Unit Effort)"
                        >
                            CPUE
                        </button>
                    </div>
                </div>
            </div>
            <div class="chart-canvas-wrapper" style="position: relative; height: 320px;">
                <canvas id="seasonalBarChartCanvas"></canvas>
            </div>
        </div>

        {{-- GRAFIK 2: GRAFIK PERBANDINGAN HASIL TANGKAPAN ANTARTAHUN --}}
        <div class="chart-card chart-card-small">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Perbandingan Antartahun</h2>
                    <p class="chart-subtitle">
                        <span x-show="activeMetric === 'produksi'">Tren total produksi tahunan resmi (2021–2026).</span>
                        <span x-show="activeMetric === 'trip'">Tren total trip penangkapan tahunan (2021–2026).</span>
                        <span x-show="activeMetric === 'cpue'">Tren rata-rata CPUE tahunan (2021–2026).</span>
                    </p>
                </div>
            </div>
            <div class="chart-canvas-wrapper" style="position: relative; height: 320px;">
                <canvas id="annualComparisonChartCanvas"></canvas>
            </div>
        </div>

    </div>

    {{-- ============================================================
         6. TABEL REKAPITULASI HASIL TANGKAPAN
         (Rincian 4 Musim & Rekapitulasi Tahunan 2021–2026)
    ============================================================ --}}
    <div class="table-card">
        <div class="table-header">
            <div class="table-header-left">
                <h2 class="table-title">
                    Tabel Rekapitulasi Hasil Tangkapan
                </h2>
                <p class="table-subtitle">
                    <span x-show="tableRecapMode === 'seasonal'">Data rincian produksi (ton), jumlah trip, dan nilai CPUE per musim pada tahun <strong x-text="selectedYear"></strong>.</span>
                    <span x-show="tableRecapMode === 'annual'">Rekapitulasi total tahunan resmi periode 2021–2026 yang terpisah dari data 4 musim.</span>
                </p>
            </div>
            <div class="table-header-right no-print" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                {{-- Toggle Mode Tabel: Rincian 4 Musim vs Rekapitulasi Tahunan --}}
                <div class="catches-toggle-group">
                    <button
                        type="button"
                        class="catches-metric-pill"
                        :class="tableRecapMode === 'seasonal' ? 'active' : ''"
                        @click="tableRecapMode = 'seasonal'"
                        title="Tampilkan rincian data per musim"
                    >
                        Rincian 4 Musim
                    </button>
                    <button
                        type="button"
                        class="catches-metric-pill"
                        :class="tableRecapMode === 'annual' ? 'active' : ''"
                        @click="tableRecapMode = 'annual'"
                        title="Tampilkan rekapitulasi total tahunan (2021–2026)"
                    >
                        Rekap Tahunan (2021–2026)
                    </button>
                </div>

                {{-- Input Pencarian --}}
                <div style="position:relative; width:180px;">
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Cari data..."
                        class="scomar-input"
                        style="padding:7px 10px 7px 32px; font-size:12.5px; height:36px;"
                    >
                    <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                <span class="table-count-badge" x-show="tableRecapMode === 'seasonal'">
                    Tahun <strong x-text="selectedYear"></strong>
                </span>
                <span class="table-count-badge" x-show="tableRecapMode === 'annual'">
                    6 Tahun Resmi
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
                        <span>Tambah Data</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            {{-- TABEL 1: RINCIAN 4 MUSIM --}}
            <table class="scomar-table" x-show="tableRecapMode === 'seasonal'">
                <thead>
                    <tr>
                        <th style="width: 90px;">Tahun</th>
                        <th>Musim Pengamatan</th>
                        <th>Periode Bulan</th>
                        <th style="text-align: right;">Produksi (ton)</th>
                        <th style="text-align: right;">Jumlah Trip</th>
                        <th style="text-align: right;">CPUE (kg/trip)</th>
                        <th style="text-align: center;">Status Musim</th>
                        <th style="text-align: center; width: 100px;" class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in filteredSeasonalRecap()" :key="item.year + '-' + item.season_key">
                        <tr>
                            <td class="cell-date">
                                <span class="year-tag" x-text="item.year"></span>
                            </td>
                            <td>
                                <strong x-text="item.season_name" style="color: var(--text-primary);"></strong>
                                <span style="font-size: 11px; color: var(--text-muted);" x-text="'(' + item.season_code + ')'"></span>
                            </td>
                            <td style="color: var(--text-secondary); font-size: 12.5px;" x-text="item.period"></td>
                            <td style="text-align: right;">
                                <template x-if="item.produksi_ton !== null">
                                    <div>
                                        <strong class="scomar-ton-val" style="font-size: 14px;" x-text="item.produksi_ton.toFixed(3) + ' ton'"></strong>
                                        <div style="font-size: 11px; color: var(--text-muted);" x-text="'(' + Number(item.produksi_kg).toLocaleString('id-ID') + ' kg)'"></div>
                                    </div>
                                </template>
                                <template x-if="item.produksi_ton === null">
                                    <span class="badge-ocean-mini badge-unavailable">Null (Data Kosong)</span>
                                </template>
                            </td>
                            <td style="text-align: right;">
                                <template x-if="item.trip !== null">
                                    <div>
                                        <strong x-text="Number(item.trip).toLocaleString('id-ID')"></strong>
                                        <span style="font-size: 11px; color: var(--text-muted);">trip</span>
                                    </div>
                                </template>
                                <template x-if="item.trip === null">
                                    <span style="color: var(--text-muted); font-size: 12px;">Null</span>
                                </template>
                            </td>
                            <td style="text-align: right;">
                                <template x-if="item.cpue !== null">
                                    <span class="cpue-badge" x-text="Number(item.cpue).toFixed(2) + ' kg/trip'"></span>
                                </template>
                                <template x-if="item.cpue === null">
                                    <span class="badge-ocean-mini badge-unavailable">Null</span>
                                </template>
                            </td>
                            <td style="text-align: center;">
                                <template x-if="item.produksi_kg === null">
                                    <span class="status-pill low" style="color:#64748B; background:rgba(100,116,139,0.12);">Data Kosong</span>
                                </template>
                                <template x-if="item.produksi_kg !== null && item.season_key === 'timur'">
                                    <span class="status-pill peak">Musim Timur</span>
                                </template>
                                <template x-if="item.produksi_kg !== null && item.season_key === 'barat'">
                                    <span class="status-pill low">Musim Barat</span>
                                </template>
                                <template x-if="item.produksi_kg !== null && item.season_key.includes('peralihan')">
                                    <span class="status-pill moderate">Peralihan</span>
                                </template>
                            </td>
                            <td style="text-align: center;" class="no-print">
                                <div style="display:inline-flex; gap:5px; justify-content:center;">
                                    <button
                                        type="button"
                                        class="btn-action-icon view"
                                        title="Lihat Rincian Data"
                                        @click="openView(item)"
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
                </tbody>
            </table>

            {{-- TABEL 2: REKAPITULASI TAHUNAN (2021-2026) --}}
            <table class="scomar-table" x-show="tableRecapMode === 'annual'">
                <thead>
                    <tr>
                        <th style="width: 90px;">Tahun</th>
                        <th>Keterangan Rekapitulasi</th>
                        <th style="text-align: right;">Total Produksi (ton)</th>
                        <th style="text-align: right;">Total Trip</th>
                        <th style="text-align: right;">CPUE Tahunan (kg/trip)</th>
                        <th style="text-align: right;">Harga Rata-rata</th>
                        <th style="text-align: right;">Estimasi Nilai</th>
                        <th style="text-align: center; width: 100px;" class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="aItem in filteredAnnualRecap()" :key="'ann-' + aItem.year">
                        <tr>
                            <td class="cell-date">
                                <span class="year-tag" x-text="aItem.year"></span>
                            </td>
                            <td>
                                <strong x-text="aItem.label" style="color: var(--text-primary);"></strong>
                                <div style="font-size: 11px; color: var(--text-muted);">Rekap resmi CSV</div>
                            </td>
                            <td style="text-align: right;">
                                <template x-if="aItem.produksi_ton !== null">
                                    <div>
                                        <strong class="scomar-ton-val" style="font-size: 14px;" x-text="aItem.produksi_ton.toFixed(3) + ' ton'"></strong>
                                        <div style="font-size: 11px; color: var(--text-muted);" x-text="'(' + Number(aItem.produksi_kg).toLocaleString('id-ID') + ' kg)'"></div>
                                    </div>
                                </template>
                                <template x-if="aItem.produksi_ton === null">
                                    <span class="badge-ocean-mini badge-unavailable">Null</span>
                                </template>
                            </td>
                            <td style="text-align: right;">
                                <template x-if="aItem.trip !== null">
                                    <div>
                                        <strong x-text="Number(aItem.trip).toLocaleString('id-ID')"></strong>
                                        <span style="font-size: 11px; color: var(--text-muted);">trip</span>
                                    </div>
                                </template>
                                <template x-if="aItem.trip === null">
                                    <span style="color: var(--text-muted); font-size: 12px;">Null</span>
                                </template>
                            </td>
                            <td style="text-align: right;">
                                <template x-if="aItem.cpue !== null">
                                    <span class="cpue-badge" x-text="Number(aItem.cpue).toFixed(2) + ' kg/trip'"></span>
                                </template>
                                <template x-if="aItem.cpue === null">
                                    <span class="badge-ocean-mini badge-unavailable">Null</span>
                                </template>
                            </td>
                            <td style="text-align: right;">
                                <span x-text="aItem.harga ? 'Rp ' + Number(aItem.harga).toLocaleString('id-ID') : '-'"></span>
                            </td>
                            <td style="text-align: right;">
                                <strong class="scomar-val-juta" x-text="aItem.nilai_juta ? 'Rp ' + Number(aItem.nilai_juta).toLocaleString('id-ID') + ' jt' : '-'"></strong>
                            </td>
                            <td style="text-align: center;" class="no-print">
                                <div style="display:inline-flex; gap:5px; justify-content:center;">
                                    <button
                                        type="button"
                                        class="btn-action-icon view"
                                        title="Lihat Rincian Rekapitulasi Tahunan"
                                        @click="openView({
                                            year: aItem.year,
                                            season: aItem.label,
                                            season_name: aItem.label,
                                            period: 'Akumulasi 4 Musim',
                                            produksi_ton: aItem.produksi_ton,
                                            produksi_kg: aItem.produksi_kg,
                                            trip: aItem.trip,
                                            cpue: aItem.cpue,
                                            harga: aItem.harga,
                                            nilai_juta: aItem.nilai_juta,
                                            notes: 'Rekapitulasi total tahunan dari file CSV data hasil tangkapan.csv'
                                        })"
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
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================
         MODAL LIHAT RINCIAN DATA TANGKAPAN
    ============================================================ --}}
    <div
        class="scomar-modal-backdrop no-print"
        x-show="showViewModal"
        x-transition.opacity
        style="display:none;"
        @keydown.escape.window="showViewModal = false"
    >
        <div class="scomar-modal-card" @click.outside="showViewModal = false">
            <div class="scomar-modal-header">
                <div class="scomar-modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--merlot)" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>Rincian Hasil Tangkapan</span>
                </div>
                <button type="button" class="scomar-alert-close" @click="showViewModal = false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="scomar-modal-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
                    <div style="background:rgba(165,184,208,0.08); padding:12px; border-radius:10px; border:1px solid var(--border-subtle);">
                        <div style="font-size:11px; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Tahun & Musim</div>
                        <div style="font-size:15px; font-weight:800; color:var(--text-primary); margin-top:4px;" x-text="activeItem.season + ' ' + activeItem.year"></div>
                        <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;" x-text="activeItem.period"></div>
                    </div>
                    <div style="background:rgba(115,52,58,0.06); padding:12px; border-radius:10px; border:1px solid rgba(115,52,58,0.18);">
                        <div style="font-size:11px; color:var(--merlot); font-weight:700; text-transform:uppercase;">Produksi Hasil Tangkapan</div>
                        <div style="font-size:18px; font-weight:800; color:var(--merlot); margin-top:4px;">
                            <span x-text="activeItem.produksi_ton !== null && activeItem.produksi_ton !== undefined ? Number(activeItem.produksi_ton).toFixed(3) + ' ton' : 'Null'"></span>
                        </div>
                        <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;" x-show="activeItem.produksi_kg !== null && activeItem.produksi_kg !== undefined">
                            <span x-text="'(' + Number(activeItem.produksi_kg).toLocaleString('id-ID') + ' kg)'"></span>
                        </div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-bottom:16px;">
                    <div style="border:1px solid var(--border-subtle); border-radius:8px; padding:10px; text-align:center;">
                        <div style="font-size:11px; color:var(--text-muted);">Jumlah Trip</div>
                        <div style="font-size:15px; font-weight:700; color:var(--text-primary); margin-top:3px;" x-text="activeItem.trip !== null && activeItem.trip !== undefined ? Number(activeItem.trip).toLocaleString('id-ID') + ' trip' : 'Null'"></div>
                    </div>
                    <div style="border:1px solid var(--border-subtle); border-radius:8px; padding:10px; text-align:center;">
                        <div style="font-size:11px; color:var(--text-muted);">Nilai CPUE</div>
                        <div style="font-size:15px; font-weight:800; color:var(--deep-navy); margin-top:3px;" x-text="activeItem.cpue !== null && activeItem.cpue !== undefined ? Number(activeItem.cpue).toFixed(2) + ' kg/trip' : 'Null'"></div>
                    </div>
                    <div style="border:1px solid var(--border-subtle); border-radius:8px; padding:10px; text-align:center;">
                        <div style="font-size:11px; color:var(--text-muted);">Harga Rata-rata</div>
                        <div style="font-size:13.5px; font-weight:700; color:var(--text-primary); margin-top:3px;" x-text="activeItem.harga ? 'Rp ' + Number(activeItem.harga).toLocaleString('id-ID') : '-'"></div>
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(36,54,75,0.04); border:1px solid var(--border-subtle); border-radius:8px; padding:10px 14px; margin-bottom:16px;" x-show="activeItem.nilai_juta">
                    <span style="font-size:12px; font-weight:600; color:var(--text-secondary);">Estimasi Nilai Ekonomi:</span>
                    <strong style="font-size:14px; color:var(--deep-navy);" x-text="'Rp ' + Number(activeItem.nilai_juta).toLocaleString('id-ID') + ' Juta'"></strong>
                </div>

                <template x-if="activeItem.notes">
                    <div style="background:var(--bg-body); padding:10px 14px; border-radius:8px; font-size:12.5px; color:var(--text-secondary);">
                        <strong style="color:var(--text-primary);">Catatan:</strong> <span x-text="activeItem.notes"></span>
                    </div>
                </template>
            </div>
            <div class="scomar-modal-footer">
                <button type="button" class="btn-scomar-secondary" @click="showViewModal = false">Tutup</button>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        {{-- ============================================================
             MODAL TAMBAH DATA TANGKAPAN
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
                        <span>Tambah Data Hasil Tangkapan</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showCreateModal = false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('catches.store') }}">
                    @csrf
                    <div class="scomar-modal-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tahun Pengamatan <span class="req">*</span></label>
                                <input type="number" name="year" value="2026" min="2000" max="2099" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Musim Penangkapan <span class="req">*</span></label>
                                <select name="season" class="scomar-select" required>
                                    <option value="Musim Barat">Musim Barat</option>
                                    <option value="Peralihan I">Peralihan I</option>
                                    <option value="Musim Timur">Musim Timur</option>
                                    <option value="Peralihan II">Peralihan II</option>
                                </select>
                            </div>
                        </div>

                        <div class="scomar-form-group">
                            <label class="scomar-label">Periode Bulan <span class="req">*</span></label>
                            <input type="text" name="period" placeholder="Contoh: Desember - Februari" class="scomar-input" required>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Total Tangkapan (kg) <span class="req">*</span></label>
                                <input type="number" step="0.1" name="total_catch" placeholder="0.0" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Jumlah (Ekor) <span class="req">*</span></label>
                                <input type="number" name="total_count" placeholder="0" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Jumlah Trip <span class="req">*</span></label>
                                <input type="number" name="trips" value="1" min="1" class="scomar-input" required>
                            </div>
                        </div>

                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Catatan Tambahan (Opsional)</label>
                            <textarea name="notes" rows="2" class="scomar-textarea" placeholder="Keterangan kondisi cuaca atau armada..."></textarea>
                        </div>
                    </div>

                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showCreateModal = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================
             MODAL EDIT DATA TANGKAPAN
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
                        <span>Edit Data Hasil Tangkapan</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showEditModal = false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('/catches') }}/${activeItem.id}`">
                    @csrf
                    @method('PUT')
                    <div class="scomar-modal-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tahun Pengamatan <span class="req">*</span></label>
                                <input type="number" name="year" x-model="activeItem.year" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Musim Penangkapan <span class="req">*</span></label>
                                <select name="season" x-model="activeItem.season" class="scomar-select" required>
                                    <option value="Musim Barat">Musim Barat</option>
                                    <option value="Peralihan I">Peralihan I</option>
                                    <option value="Musim Timur">Musim Timur</option>
                                    <option value="Peralihan II">Peralihan II</option>
                                </select>
                            </div>
                        </div>

                        <div class="scomar-form-group">
                            <label class="scomar-label">Periode Bulan <span class="req">*</span></label>
                            <input type="text" name="period" x-model="activeItem.period" class="scomar-input" required>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Total Tangkapan (kg) <span class="req">*</span></label>
                                <input type="number" step="0.1" name="total_catch" x-model="activeItem.totalCatch" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Jumlah (Ekor) <span class="req">*</span></label>
                                <input type="number" name="total_count" x-model="activeItem.totalCount" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Jumlah Trip <span class="req">*</span></label>
                                <input type="number" name="trips" x-model="activeItem.trips" min="1" class="scomar-input" required>
                            </div>
                        </div>

                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Catatan Tambahan</label>
                            <textarea name="notes" x-model="activeItem.notes" rows="2" class="scomar-textarea"></textarea>
                        </div>
                    </div>

                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showEditModal = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Perbarui Data</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================
             MODAL HAPUS DATA TANGKAPAN
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
                        <span>Hapus Data Tangkapan</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showDeleteModal = false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <div class="scomar-modal-body" style="padding-top:10px;">
                    <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0 0 12px 0;">
                        Apakah Anda yakin ingin menghapus data hasil tangkapan <strong style="color:var(--text-primary);" x-text="activeItem.season + ' ' + activeItem.year"></strong>?
                    </p>
                    <div style="background:rgba(220,38,38,0.08); border-left:3px solid #DC2626; padding:10px 14px; border-radius:6px; font-size:12px; color:#DC2626;">
                        Data tangkapan ini akan dihapus dari rekapitulasi, grafik tren, dan laporan PDF.
                    </div>
                </div>

                <form method="POST" :action="`{{ url('/catches') }}/${activeItem.id}`">
                    @csrf
                    @method('DELETE')
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showDeleteModal = false">Batal</button>
                        <button type="submit" class="btn-scomar-danger">Ya, Hapus Data</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

{{-- ================================================================
     HIDDEN PDF CONTAINER — KHUSUS EKSPOR HASIL TANGKAPAN
     Menggunakan flexbox murni untuk menghindari bug CSS Grid + html2pdf
================================================================ --}}
<div id="scomarPdfCatchesReport" style="display:none; width:1122px; background:#FFFFFF; font-family:'Plus Jakarta Sans', sans-serif; color:#1A1A2E; box-sizing:border-box;">

    {{-- ══════════════════════════════════════════════════════
         HALAMAN 1: Header + 4 Kartu KPI + 2 Grafik
    ══════════════════════════════════════════════════════ --}}
    <div id="pdf-catches-page1" style="width:1122px; height:794px; max-height:794px; padding:28px 36px 24px 36px; box-sizing:border-box; background:#FFFFFF; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden; page-break-after:always; break-after:page;">
        <div>
            {{-- Header Dokumen --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding-bottom:10px; border-bottom:2.5px solid #73343A;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:38px; height:38px; background:#73343A; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:17px; font-weight:800; color:#73343A; letter-spacing:-0.3px; line-height:1.2;">Grafik Analisis Hasil Tangkapan</div>
                        <div style="font-size:10.5px; color:#6B7280; font-weight:500; margin-top:2px;">SCOMAR — Pemantauan Hasil Tangkapan <em>Scomberomorus commerson</em> (Ikan Tenggiri) Pulau Tunda, Banten</div>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:9.5px; color:#6B7280; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Tanggal Cetak</div>
                    <div id="pdfCatchesDate" style="font-size:11.5px; font-weight:700; color:#24364B; margin-top:1px;">—</div>
                    <div id="pdfCatchesYear" style="font-size:10.5px; color:#73343A; font-weight:700; margin-top:1px;">Tahun: —</div>
                    <div style="font-size:9.5px; color:#9CA3AF; margin-top:1px;">Hal. 1 / 2</div>
                </div>
            </div>

            {{-- Deskripsi Laporan --}}
            <div style="background:rgba(165,184,208,0.12); border-radius:8px; padding:8px 14px; margin-bottom:12px; border-left:3.5px solid #A5B8D0;">
                <div style="font-size:11px; color:#374151; line-height:1.5;">
                    Rekapitulasi dan analisis <strong>hasil tangkapan ikan tenggiri (<em>Scomberomorus commerson</em>)</strong> berdasarkan empat musim penangkapan di perairan Pulau Tunda.
                    Data mencakup distribusi volume tangkapan (kg), jumlah ekor, jumlah trip, dan <strong>CPUE</strong> (<em>Catch Per Unit Effort</em> — kg/trip).
                </div>
            </div>

            {{-- 4 Kartu KPI (Flexbox) — diisi JS saat ekspor --}}
            <div style="display:flex; gap:10px; margin-bottom:12px;">
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-left:4px solid #73343A; border-radius:10px; padding:10px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px;">Volume Tertinggi Seluruh Periode</div>
                    <div id="pdfCatchesPeak" style="font-size:20px; font-weight:800; color:#73343A; line-height:1.1;">4.280,0<small style="font-size:11px; font-weight:600; color:#6B7280;">kg</small></div>
                    <div style="font-size:10px; color:#73343A; font-weight:700; margin-top:4px;" id="pdfCatchesPeakSeason">Musim Timur (2025)</div>
                </div>
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-left:4px solid #A5B8D0; border-radius:10px; padding:10px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px;" id="pdfCatchesAnnualLabel">Total Tangkapan Tahun —</div>
                    <div id="pdfCatchesAnnual" style="font-size:20px; font-weight:800; color:#24364B; line-height:1.1;">—<small style="font-size:11px; font-weight:600; color:#6B7280;">kg</small></div>
                    <div style="font-size:10px; color:#6B7280; font-weight:600; margin-top:4px;">Akumulasi 4 Musim</div>
                </div>
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-left:4px solid #24364B; border-radius:10px; padding:10px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px;" id="pdfCatchesPeakSeasonLabel">Musim Tertinggi (—)</div>
                    <div id="pdfCatchesPeakSeasonVal" style="font-size:18px; font-weight:800; color:#24364B; line-height:1.2;">Musim Timur</div>
                    <div style="font-size:10px; color:#10B981; font-weight:600; margin-top:4px;" id="pdfCatchesPeakSeasonKg">Sebesar — kg</div>
                </div>
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-left:4px solid #4B6A8A; border-radius:10px; padding:10px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:9px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px;">Spesies Utama Target</div>
                    <div style="font-size:16px; font-weight:800; color:#4B6A8A; line-height:1.3; font-style:italic;">S. commerson</div>
                    <div style="font-size:10px; color:#6B7280; font-weight:600; margin-top:4px;">Rata-rata CPUE: <strong id="pdfCatchesCpue">—</strong> kg/trip</div>
                </div>
            </div>

            {{-- 2 Grafik (Flexbox 55%-45%) --}}
            <div style="display:flex; gap:12px;">
                <div style="flex:1.2; background:#FFFFFF; border:1.5px solid #E5E7EB; border-radius:10px; padding:12px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <div>
                            <div id="pdfCatchesChart1Title" style="font-size:12.5px; font-weight:700; color:#1A1A2E;">Distribusi Tangkapan 4 Musim (Tahun —)</div>
                            <div style="font-size:10px; color:#6B7280; margin-top:1px;">Volume tangkapan (kg) per musim penangkapan</div>
                        </div>
                        <div style="display:flex; align-items:center; gap:5px; background:rgba(165,184,208,0.15); padding:3px 8px; border-radius:5px;">
                            <span style="width:7px; height:7px; border-radius:50%; background:#73343A; display:inline-block;"></span>
                            <span style="font-size:9.5px; font-weight:600; color:#6B7280;">Total Tangkapan (kg)</span>
                        </div>
                    </div>
                    <img id="pdfImgSeasonal" src="" style="width:100%; height:195px; object-fit:fill; display:block;" alt="Grafik 4 Musim" />
                </div>
                <div style="flex:1; background:#FFFFFF; border:1.5px solid #E5E7EB; border-radius:10px; padding:12px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="margin-bottom:8px;">
                        <div style="font-size:12.5px; font-weight:700; color:#1A1A2E;">Perbandingan Antartahun</div>
                        <div style="font-size:10px; color:#6B7280; margin-top:1px;">Tren akumulasi tangkapan tahunan (2021–2026)</div>
                    </div>
                    <img id="pdfImgAnnual" src="" style="width:100%; height:195px; object-fit:fill; display:block;" alt="Grafik Antartahun" />
                </div>
            </div>
        </div>

        {{-- Footer Halaman 1 --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:8px; border-top:1px solid #E5E7EB; font-size:9.5px; color:#9CA3AF;">
            <span>SCOMAR — Sistem Pemantauan Ekosistem Laut Pulau Tunda | Universitas Pendidikan Indonesia</span>
            <span>Halaman 1 dari 2</span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         HALAMAN 2: Tabel Rekapitulasi + Analisis
    ══════════════════════════════════════════════════════ --}}
    <div id="pdf-catches-page2" style="width:1122px; height:794px; max-height:794px; padding:28px 36px 24px 36px; box-sizing:border-box; background:#FFFFFF; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden;">
        <div>
            {{-- Header Lanjutan --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding-bottom:10px; border-bottom:2px solid #73343A;">
                <div>
                    <div style="font-size:14px; font-weight:800; color:#73343A;">Tabel Rekapitulasi Hasil Tangkapan — Halaman 2</div>
                    <div id="pdfCatchesTableDesc" style="font-size:10.5px; color:#6B7280; margin-top:1px;">Data rekapitulasi volume, jumlah ekor, trip, dan CPUE per musim | Tahun —</div>
                </div>
                <div style="text-align:right; font-size:9.5px; color:#9CA3AF; font-weight:600;">Hal. 2 / 2</div>
            </div>

            {{-- Tabel Rekapitulasi --}}
            <div style="margin-bottom:14px; overflow:hidden; border-radius:8px; border:1.5px solid #E5E7EB;">
                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                    <thead>
                        <tr style="background:#24364B; color:#FFFFFF;">
                            <th style="padding:8px 10px; text-align:left; font-weight:700; font-size:10.5px; white-space:nowrap;">Tahun</th>
                            <th style="padding:8px 10px; text-align:left; font-weight:700; font-size:10.5px;">Musim Pengamatan</th>
                            <th style="padding:8px 10px; text-align:left; font-weight:700; font-size:10.5px;">Periode Bulan</th>
                            <th style="padding:8px 10px; text-align:right; font-weight:700; font-size:10.5px;">Produksi (ton)</th>
                            <th style="padding:8px 10px; text-align:right; font-weight:700; font-size:10.5px;">Produksi (kg)</th>
                            <th style="padding:8px 10px; text-align:right; font-weight:700; font-size:10.5px;">Jumlah Trip</th>
                            <th style="padding:8px 10px; text-align:right; font-weight:700; font-size:10.5px;">CPUE (kg/trip)</th>
                            <th style="padding:8px 10px; text-align:center; font-weight:700; font-size:10.5px;">Status Musim</th>
                        </tr>
                    </thead>
                    <tbody id="pdfCatchesTableRows">
                        {{-- Diisi dinamis oleh JavaScript --}}
                    </tbody>
                    <tfoot id="pdfCatchesTableFoot">
                        {{-- Total row diisi oleh JavaScript --}}
                    </tfoot>
                </table>
            </div>

            {{-- Analisis & Verifikasi --}}
            <div style="display:flex; gap:12px;">
                <div style="flex:1.5; background:rgba(165,184,208,0.1); border-radius:10px; padding:12px 14px; border:1.5px solid #E5E7EB;">
                    <div style="font-size:11px; font-weight:800; color:#24364B; margin-bottom:6px;">Analisis Dinamika Musim & Efisiensi Penangkapan</div>
                    <div id="pdfCatchesAnalysis" style="font-size:10px; color:#374151; line-height:1.65;">
                        • <strong>Musim Timur</strong> (Jun–Agu): Produktivitas tertinggi, didorong oleh upwelling dan peningkatan klorofil-a.<br/>
                        • <strong>Peralihan I &amp; II</strong> (Mar–Mei, Sep–Nov): Volume sedang, kondisi perairan transisi mendukung migrasi ikan.<br/>
                        • <strong>Musim Barat</strong> (Des–Feb): Volume terendah, kondisi gelombang tinggi membatasi aktivitas penangkapan.<br/>
                        • Nilai CPUE tertinggi diraih pada Musim Timur, mengindikasikan efisiensi trip yang optimal saat musim puncak.
                    </div>
                </div>
                <div style="flex:1; background:rgba(115,52,58,0.05); border-radius:10px; padding:12px 14px; border:1.5px solid rgba(115,52,58,0.2);">
                    <div style="font-size:11px; font-weight:800; color:#73343A; margin-bottom:6px;">Verifikasi Riset</div>
                    <div style="font-size:10px; color:#374151; line-height:1.65;">
                        Data telah diverifikasi oleh tim Konsorsium SCOMAR Universitas Pendidikan Indonesia.<br/><br/>
                        <strong>Spesies:</strong> <em>Scomberomorus commerson</em> (Ikan Tenggiri)<br/>
                        <strong>Lokasi:</strong> Pulau Tunda, Kabupaten Serang, Banten<br/>
                        <strong>Sistem:</strong> SCOMAR v1.0 — Pemantauan Digital<br/>
                        <strong>Koordinat:</strong> -5.86°LS, 106.26°BT
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Halaman 2 --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:8px; border-top:1px solid #E5E7EB; font-size:9.5px; color:#9CA3AF;">
            <span>SCOMAR — Sistem Pemantauan Ekosistem Laut Pulau Tunda | Universitas Pendidikan Indonesia</span>
            <span>Halaman 2 dari 2 — Akhir Dokumen</span>
        </div>
    </div>

</div>

{{-- ================================================================
     CSS KHUSUS HALAMAN GRAFIK HASIL TANGKAPAN
================================================================ --}}
@push('styles')
<style>
    /* ── CONTAINER UTAMA ─────────────────────────────────────────── */
    .catches-container {
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

    .page-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
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
        border-color: var(--cashmere-blue);
        background: rgba(165, 184, 208, 0.1);
        color: var(--text-primary);
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

    /* ── METRICS GRID (RESPONSIVE FLUID) ────────────────────────── */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .metric-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        box-shadow: var(--card-shadow);
        transition: transform var(--transition), border-color var(--transition);
        min-width: 0;
        width: 100%;
        box-sizing: border-box;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        border-color: var(--border-hover);
    }

    /* Highlight khusus kartu Peak All-Time */
    .metric-card.peak-all-time {
        border-left: 4px solid var(--merlot);
    }

    .metric-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .metric-icon-box.merlot   { background: rgba(115, 52, 58, 0.12); color: var(--merlot); }
    .metric-icon-box.cashmere { background: rgba(165, 184, 208, 0.25); color: #2B4C7E; }
    .metric-icon-box.navy     { background: rgba(36, 54, 75, 0.12); color: var(--deep-navy); }
    .metric-icon-box.amber    { background: rgba(165, 184, 208, 0.22); color: var(--deep-navy); }

    [data-theme="dark"] .metric-icon-box.merlot   { background: rgba(115, 52, 58, 0.35); color: #FFA4A8; }
    [data-theme="dark"] .metric-icon-box.cashmere { background: rgba(165, 184, 208, 0.2); color: var(--cashmere-blue); }
    [data-theme="dark"] .metric-icon-box.navy     { background: rgba(245, 245, 242, 0.1); color: var(--warm-ivory); }

    .metric-body {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .metric-label {
        font-size: 11.5px;
        color: var(--text-secondary);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .metric-value {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .metric-value small {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .metric-value.font-title {
        font-size: 17px;
        line-height: 1.3;
    }

    .metric-trend {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11.5px;
        font-weight: 600;
        margin-top: 6px;
    }

    .metric-trend.positive { color: var(--merlot); }
    .metric-trend.neutral  { color: var(--text-muted); }

    .metric-trend.peak-badge {
        color: var(--merlot);
        font-weight: 700;
    }

    [data-theme="dark"] .metric-trend.peak-badge {
        color: #FFA4A8;
    }

    .peak-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--merlot);
        flex-shrink: 0;
    }

    [data-theme="dark"] .peak-dot {
        background: #FFA4A8;
    }

    /* ── FILTER CARD: ANALISIS HASIL TANGKAPAN BERDASARKAN TAHUN DAN MUSIM ── */
    .filter-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 18px 22px;
        box-shadow: var(--card-shadow);
    }

    .filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-subtle);
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
        flex-shrink: 0;
        color: var(--merlot);
    }

    .year-dropdown-item-check.placeholder {
        width: 16px;
    }

    /* Alpine.js transition classes */
    .dropdown-enter        { transition: opacity 0.18s ease, transform 0.18s ease; }
    .dropdown-enter-start  { opacity: 0; transform: translateY(-6px) scaleY(0.95); }
    .dropdown-enter-end    { opacity: 1; transform: translateY(0) scaleY(1); }
    .dropdown-leave        { transition: opacity 0.14s ease, transform 0.14s ease; }
    .dropdown-leave-start  { opacity: 1; transform: translateY(0) scaleY(1); }
    .dropdown-leave-end    { opacity: 0; transform: translateY(-4px) scaleY(0.97); }

    /* Dark mode adjustments */
    [data-theme="dark"] .year-dropdown-trigger {
        background: var(--merlot);
        border-color: var(--merlot);
        color: #FFFFFF;
    }

    [data-theme="dark"] .year-dropdown-trigger:hover {
        background: #5E2328;
        border-color: #5E2328;
    }

    [data-theme="dark"] .year-dropdown-menu {
        background: #1E2D3D;
        border-color: rgba(165, 184, 208, 0.2);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
    }

    [data-theme="dark"] .year-dropdown-item {
        color: #F5F5F2;
    }

    [data-theme="dark"] .year-dropdown-item:hover {
        background: rgba(165, 184, 208, 0.12);
        color: var(--cashmere-blue);
    }

    [data-theme="dark"] .year-dropdown-item.selected {
        background: rgba(115, 52, 58, 0.25);
        color: #F5A5AA;
    }

    [data-theme="dark"] .year-dropdown-item-check {
        color: #F5A5AA;
    }

    /* Legacy pill classes (backward compat untuk PDF JS) */
    .seasonal-pill-group { display: none; }
    .seasonal-pill-btn   { display: none; }
    .seasonal-pill-btn.active { display: none; }

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

    /* ── CATCHES TOGGLE GROUP & METRIC PILLS ───────────────────── */
    .catches-toggle-group {
        display: inline-flex;
        align-items: center;
        padding: 3px;
        background: rgba(165, 184, 208, 0.15);
        border: 1px solid var(--border-subtle);
        border-radius: 8px;
        gap: 2px;
    }

    [data-theme="dark"] .catches-toggle-group {
        background: rgba(15, 23, 42, 0.6);
        border-color: rgba(165, 184, 208, 0.15);
    }

    .catches-metric-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 11px;
        font-size: 11.5px;
        font-weight: 600;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-muted);
        background: transparent;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .catches-metric-pill:hover {
        color: var(--merlot);
    }

    .catches-metric-pill.active {
        background: #FFFFFF;
        color: var(--merlot);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        font-weight: 700;
    }

    [data-theme="dark"] .catches-metric-pill.active {
        background: #1E2D3D;
        color: #FFA4A8;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
    }

    /* ── BADGE UNAVAILABLE / NULL ───────────────────────────────── */
    .badge-unavailable {
        background: rgba(148, 163, 184, 0.15) !important;
        color: #64748B !important;
        border: 1px solid rgba(148, 163, 184, 0.3) !important;
    }

    [data-theme="dark"] .badge-unavailable {
        background: rgba(148, 163, 184, 0.12) !important;
        color: #94A3B8 !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
    }

    /* ── CHARTS GRID (RESPONSIVE FLUID) ─────────────────────────── */
    .charts-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(0, 1.3fr);
        gap: 18px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .chart-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .chart-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .chart-title {
        font-size: 15.5px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .chart-subtitle {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .chart-legend-custom {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        background: rgba(165, 184, 208, 0.15);
        padding: 4px 10px;
        border-radius: 6px;
        white-space: nowrap;
    }

    .legend-dot.merlot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--merlot);
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

    .scomar-ton-val {
        color: var(--merlot);
    }
    [data-theme="dark"] .scomar-ton-val {
        color: #FF858B !important;
    }

    .scomar-val-juta {
        color: var(--deep-navy);
        font-weight: 700;
    }
    [data-theme="dark"] .scomar-val-juta {
        color: #E2E8F0 !important;
    }

    .cpue-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(115, 52, 58, 0.12);
        color: var(--merlot);
    }

    [data-theme="dark"] .cpue-badge {
        background: rgba(255, 133, 139, 0.20) !important;
        color: #FFA4A8 !important;
        border: 1px solid rgba(255, 133, 139, 0.40) !important;
    }

    .status-pill {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-pill.peak {
        background: rgba(16, 185, 129, 0.15);
        color: var(--deep-navy);
    }

    .status-pill.low {
        background: rgba(239, 68, 68, 0.12);
        color: #DC2626;
    }

    .status-pill.moderate {
        background: rgba(245, 158, 11, 0.15);
        color: #4B6A8A;
    }

    [data-theme="dark"] .status-pill.peak {
        background: rgba(52, 211, 153, 0.22) !important;
        color: #34D399 !important;
        border: 1px solid rgba(52, 211, 153, 0.40) !important;
    }

    [data-theme="dark"] .status-pill.low {
        background: rgba(248, 113, 113, 0.20) !important;
        color: #FCA5A5 !important;
        border: 1px solid rgba(248, 113, 113, 0.40) !important;
    }

    [data-theme="dark"] .status-pill.moderate {
        background: rgba(251, 191, 36, 0.20) !important;
        color: #FCD34D !important;
        border: 1px solid rgba(251, 191, 36, 0.40) !important;
    }

    [data-theme="dark"] .status-pill.low[style*="background:rgba(100,116,139"] {
        background: rgba(148, 163, 184, 0.18) !important;
        color: #CBD5E1 !important;
        border: 1px solid rgba(148, 163, 184, 0.35) !important;
    }

    .cell-date {
        font-weight: 600;
        white-space: nowrap;
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
        .charts-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 640px) {
        .metrics-grid {
            grid-template-columns: minmax(0, 1fr);
        }
        .seasonal-filter-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    /* ── PRINT STYLESHEET (CLEAN PDF PRINT) ──────────────────────── */
    @media print {
        .sidebar,
        .app-navbar,
        .app-footer,
        .no-print,
        .page-header-actions,
        .filter-card {
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
        .charts-grid, .chart-canvas-wrapper {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        /* Jika konten terlalu panjang, pindah ke halaman baru */
        .table-card {
            break-before: page !important;
            page-break-before: always !important;
        }
    }

    /* ── RESPONSIVE GRAFIK TANGKAPAN ─────────────────────────── */
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
        .filter-card {
            padding: 14px 16px;
        }
    }

    @media (max-width: 640px) {
        .page-header h1 {
            font-size: 18px;
        }
        .metric-card {
            padding: 14px 16px;
        }
        .chart-card {
            padding: 14px 14px;
        }
        .seasonal-filter-toolbar {
            gap: 10px;
        }
    }

    @media (max-width: 480px) {
        .page-header h1 {
            font-size: 16px;
        }
        .metrics-grid {
            gap: 10px;
        }
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }
    }
</style>
@endpush

{{-- ================================================================
     SCRIPTS: INISIALISASI CHART.JS (4 MUSIM, PERBANDINGAN TAHUN, UNDUH PDF)
================================================================ --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
// ── DATA RESMI DARI CONTROLLER / CSV (2021-2026) ────────────────────────
const csvCatchByYear       = {{ Js::from($byYear ?? []) }};
const csvCatchAnnualTotals = {{ Js::from($annualTotals ?? []) }};
const csvCatchSeasonalFlat = {{ Js::from($seasonalFlat ?? []) }};
const csvCatchOverallStats = {{ Js::from($overallStats ?? []) }};
const csvYearsDesc         = {{ Js::from($years ?? ['2026', '2025', '2024', '2023', '2022', '2021']) }};
const csvYearsAsc          = [...csvYearsDesc].sort((a, b) => parseInt(a) - parseInt(b));

// ── Fungsi Ekspor Data Hasil Tangkapan ke CSV (Resmi SCOMAR) ──────────
window.exportCatchesToCsv = function () {
    const btn = document.getElementById('btnExportCatchesCsv');
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
        const pdfArea = document.getElementById('exportCatchesPdfArea');
        if (pdfArea && pdfArea._x_dataStack && pdfArea._x_dataStack[0]?.selectedYear) {
            selectedYear = String(pdfArea._x_dataStack[0].selectedYear);
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

    // Arahkan browser untuk mengunduh dataset CSV resmi secara instan
    const downloadUrl = "{{ route('charts.catches.export-csv') }}?year=" + encodeURIComponent(selectedYear);
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

// ── Fungsi Unduh Laporan PDF Hasil Tangkapan (Reinstalled & Fresh Engine) ──
window.exportCatchesToPdf = async function () {
    const btn = document.getElementById('btnDownloadCatchesPdf');
    const originalContent = btn ? btn.innerHTML : '';

    if (btn) {
        btn.innerHTML = `
            <svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
            </svg>
            <span>Membuat PDF...</span>
        `;
        btn.disabled = true;
    }

    const pdfContainer = document.getElementById('scomarPdfCatchesReport');
    if (!pdfContainer) {
        if (btn) {
            btn.innerHTML = originalContent;
            btn.disabled = false;
        }
        return;
    }

    try {
        // 1. Ambil Tahun Aktif dari Filter
        let selectedYear = '2026';
        try {
            const pdfArea = document.getElementById('exportCatchesPdfArea');
            if (pdfArea && pdfArea._x_dataStack && pdfArea._x_dataStack[0]?.selectedYear) {
                selectedYear = String(pdfArea._x_dataStack[0].selectedYear);
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

        // 2. Data Musiman & Rekapitulasi dari Dataset Resmi
        const filteredRows = csvCatchSeasonalFlat.filter(d => String(d.year) === String(selectedYear));
        const annualData = csvCatchAnnualTotals[selectedYear] || {};

        const totalCatchTon = annualData.produksi_ton !== undefined && annualData.produksi_ton !== null
            ? annualData.produksi_ton
            : filteredRows.reduce((a, b) => a + (b.produksi_ton || 0), 0);

        const totalCatchKg = annualData.produksi_kg !== undefined && annualData.produksi_kg !== null
            ? annualData.produksi_kg
            : filteredRows.reduce((a, b) => a + (b.produksi_kg || 0), 0);

        const totalTrips = annualData.trip !== undefined && annualData.trip !== null
            ? annualData.trip
            : filteredRows.reduce((a, b) => a + (b.trip || 0), 0);

        const avgCpue = annualData.cpue !== undefined && annualData.cpue !== null
            ? Number(annualData.cpue).toFixed(2)
            : (totalTrips > 0 ? (totalCatchKg / totalTrips).toFixed(2) : '0.00');

        const validRows = filteredRows.filter(r => r.produksi_kg !== null);
        const peakRow = validRows.length > 0
            ? validRows.reduce((p, c) => ((c.produksi_kg || 0) > (p.produksi_kg || 0) ? c : p))
            : { season_name: '-', produksi_ton: null, produksi_kg: null };

        const allTimePeak = csvCatchOverallStats.peak || (
            csvCatchSeasonalFlat.filter(r => r.produksi_kg !== null).reduce((p, c) => (c.produksi_kg > (p.produksi_kg || 0) ? c : p), csvCatchSeasonalFlat[0])
        );

        // 3. Konversi Chart Canvas Aktif ke Image Data URL
        const canvasSeasonal = document.getElementById('seasonalBarChartCanvas');
        const canvasAnnual   = document.getElementById('annualComparisonChartCanvas');
        const imgSeasonal = document.getElementById('pdfImgSeasonal');
        const imgAnnual   = document.getElementById('pdfImgAnnual');

        if (canvasSeasonal && imgSeasonal) {
            imgSeasonal.src = canvasSeasonal.toDataURL('image/png', 1.0);
        }
        if (canvasAnnual && imgAnnual) {
            imgAnnual.src = canvasAnnual.toDataURL('image/png', 1.0);
        }

        await Promise.all(
            [imgSeasonal, imgAnnual]
                .filter(Boolean)
                .map(img => img.decode ? img.decode().catch(() => {}) : Promise.resolve())
        );

        // 4. Update Kartu Metrik KPI pada Template PDF
        const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

        if (allTimePeak && allTimePeak.produksi_ton !== undefined) {
            setEl('pdfCatchesPeak', allTimePeak.produksi_ton.toFixed(3) + ' ton');
            setEl('pdfCatchesPeakSeason', (allTimePeak.season_name || allTimePeak.season) + ' (' + allTimePeak.year + ')');
        } else {
            setEl('pdfCatchesPeak', '13.486 ton');
            setEl('pdfCatchesPeakSeason', 'Musim Barat (2021)');
        }

        setEl('pdfCatchesAnnualLabel', 'Total Tangkapan Tahun ' + selectedYear);
        setEl('pdfCatchesAnnual', totalCatchTon !== null ? (totalCatchTon.toFixed(3) + ' ton') : 'Null');
        setEl('pdfCatchesPeakSeasonLabel', 'Musim Tertinggi (' + selectedYear + ')');
        setEl('pdfCatchesPeakSeasonVal', peakRow.season_name || '-');
        setEl('pdfCatchesPeakSeasonKg', peakRow.produksi_ton !== null
            ? ('Sebesar ' + peakRow.produksi_ton.toFixed(3) + ' ton (' + Number(peakRow.produksi_kg).toLocaleString('id-ID') + ' kg)')
            : 'Data tidak tersedia'
        );
        setEl('pdfCatchesCpue', avgCpue);
        setEl('pdfCatchesChart1Title', 'Distribusi Tangkapan 4 Musim (Tahun ' + selectedYear + ')');
        setEl('pdfCatchesTableDesc', 'Data rekapitulasi volume (ton & kg), trip, dan CPUE per musim | Tahun ' + selectedYear);

        const now = new Date();
        setEl('pdfCatchesDate', now.toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'}));
        setEl('pdfCatchesYear', 'Tahun: ' + selectedYear);

        // 5. Update Isi Tabel Rekapitulasi PDF
        const tbody = document.getElementById('pdfCatchesTableRows');
        const tfoot = document.getElementById('pdfCatchesTableFoot');
        if (tbody) {
            const rowColors = ['#F9FAFB', '#FFFFFF', '#F9FAFB', '#FFFFFF'];
            const getStatus = (s) => {
                if (!s) return '-';
                if (s.includes('Timur')) return '<span style="font-size:9.5px; font-weight:700; color:#73343A; background:rgba(115,52,58,0.1); padding:2px 7px; border-radius:5px;">Puncak Musim</span>';
                if (s.includes('Barat')) return '<span style="font-size:9.5px; font-weight:700; color:#24364B; background:rgba(36,54,75,0.1); padding:2px 7px; border-radius:5px;">Musim Barat</span>';
                return '<span style="font-size:9.5px; font-weight:700; color:#4B6A8A; background:rgba(75,106,138,0.1); padding:2px 7px; border-radius:5px;">Peralihan</span>';
            };

            tbody.innerHTML = filteredRows.map((r, i) => {
                const prodTonStr = r.produksi_ton !== null
                    ? `${r.produksi_ton.toFixed(3)} ton`
                    : '<span style="color:#94A3B8; font-weight:600;">Null</span>';
                const prodKgStr = r.produksi_kg !== null
                    ? `${Number(r.produksi_kg).toLocaleString('id-ID')} kg`
                    : '<span style="color:#94A3B8; font-weight:600;">Null</span>';
                const tripStr = r.trip !== null
                    ? `${Number(r.trip).toLocaleString('id-ID')} trip`
                    : '<span style="color:#94A3B8; font-weight:600;">Null</span>';
                const cpueStr = r.cpue !== null
                    ? `${Number(r.cpue).toFixed(2)} kg/trip`
                    : '<span style="color:#94A3B8; font-weight:600;">Null</span>';
                const statusBadge = r.produksi_kg === null
                    ? '<span style="font-size:9.5px; font-weight:700; color:#64748B; background:#F1F5F9; padding:2px 7px; border-radius:5px;">Null (Kosong)</span>'
                    : getStatus(r.season_name);

                return `
                    <tr style="background:${rowColors[i % 2]};">
                        <td style="padding:8px 10px; color:#24364B; font-weight:700; white-space:nowrap;">${r.year}</td>
                        <td style="padding:8px 10px; font-weight:700; color:#1A1A2E;">${r.season_name}</td>
                        <td style="padding:8px 10px; color:#6B7280; font-size:10px;">${r.period}</td>
                        <td style="padding:8px 10px; text-align:right; color:#73343A; font-weight:800; font-size:11.5px;">${prodTonStr}</td>
                        <td style="padding:8px 10px; text-align:right; color:#374151; font-size:11px;">${prodKgStr}</td>
                        <td style="padding:8px 10px; text-align:right; color:#374151; font-weight:700;">${tripStr}</td>
                        <td style="padding:8px 10px; text-align:right; font-size:10.5px; font-weight:700; color:#24364B;">${cpueStr}</td>
                        <td style="padding:8px 10px; text-align:center;">${statusBadge}</td>
                    </tr>
                `;
            }).join('');
        }
        if (tfoot) {
            tfoot.innerHTML = `
                <tr style="background:#24364B; color:#FFFFFF; font-weight:800;">
                    <td colspan="3" style="padding:9px 10px; font-size:10.5px;">Total Tahun ${selectedYear}</td>
                    <td style="padding:9px 10px; text-align:right; font-size:11.5px;">${totalCatchTon !== null ? totalCatchTon.toFixed(3) + ' ton' : 'Null'}</td>
                    <td style="padding:9px 10px; text-align:right; font-size:11px;">${totalCatchKg !== null ? Number(totalCatchKg).toLocaleString('id-ID') + ' kg' : 'Null'}</td>
                    <td style="padding:9px 10px; text-align:right;">${totalTrips !== null ? Number(totalTrips).toLocaleString('id-ID') + ' trip' : 'Null'}</td>
                    <td style="padding:9px 10px; text-align:right;">${avgCpue} kg/trip</td>
                    <td style="padding:9px 10px; text-align:center; font-size:9.5px;">4 Musim</td>
                </tr>
            `;
        }

        // 6. Jalankan html2pdf dengan klon terisolasi (menghindari layout break & halaman kosong)
        if (typeof html2pdf !== 'undefined') {
            const wrapper = document.createElement('div');
            wrapper.style.position = 'fixed';
            wrapper.style.left = '-99999px';
            wrapper.style.top = '0';
            wrapper.style.width = '1122px';
            wrapper.style.zIndex = '-9999';
            wrapper.style.background = '#FFFFFF';

            const clone = pdfContainer.cloneNode(true);
            clone.style.display = 'block';
            clone.style.visibility = 'visible';
            clone.style.width = '1122px';
            clone.style.margin = '0';
            clone.style.padding = '0';

            const cloneImgSeasonal = clone.querySelector('#pdfImgSeasonal');
            const cloneImgAnnual = clone.querySelector('#pdfImgAnnual');
            if (cloneImgSeasonal && imgSeasonal) cloneImgSeasonal.src = imgSeasonal.src;
            if (cloneImgAnnual && imgAnnual) cloneImgAnnual.src = imgAnnual.src;

            wrapper.appendChild(clone);
            document.body.appendChild(wrapper);

            await new Promise(r => setTimeout(r, 120));

            const opt = {
                margin: [0, 0, 0, 0],
                filename: `Laporan-Analisis-Hasil-Tangkapan-${selectedYear}-SCOMAR.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#FFFFFF',
                    width: 1122,
                    windowWidth: 1122
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'landscape',
                    compress: true
                },
                pagebreak: {
                    mode: ['css', 'legacy']
                }
            };

            await html2pdf().set(opt).from(clone).save();
            if (wrapper.parentNode) {
                document.body.removeChild(wrapper);
            }
        } else {
            // Fallback cetak dokumen langsung jika CDN html2pdf belum selesai terload
            window.print();
        }

    } catch (err) {
        console.error('Gagal mengunduh PDF:', err);
        if (confirm('Terjadi kendala saat merender PDF otomatis. Apakah Anda ingin mencetak dokumen secara langsung melalui jendela cetak browser?')) {
            window.print();
        }
    } finally {
        if (btn) {
            btn.innerHTML = originalContent;
            btn.disabled = false;
        }
    }
};
document.addEventListener('DOMContentLoaded', function () {
    let seasonalBarChartInstance = null;
    let annualComparisonChartInstance = null;

    let activeYear   = String(csvYearsDesc[0] || '2026');
    let activeMetric = 'produksi'; // 'produksi' | 'trip' | 'cpue'

    // Fungsi warna tema dinamis (Light / Dark)
    function getChartThemeColors() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            isDark: isDark,
            textColor: isDark ? '#E2E8F0' : '#24364B',
            textMuted: isDark ? '#94A3B8' : '#64748B',
            gridColor: isDark ? 'rgba(165, 184, 208, 0.15)' : 'rgba(165, 184, 208, 0.28)',
            tooltipBg: isDark ? '#0F1722' : '#24364B',
            tooltipText: '#F8FAFC'
        };
    }

    // Warna batang 4 musim yang kontras di Light vs Dark Mode
    function getSeasonalBarColors(isDark) {
        if (isDark) {
            return [
                '#38BDF8', // Musim Barat (Sky Blue cerah, kontras tinggi di dark mode)
                '#818CF8', // Peralihan I (Indigo/Cashmere terang)
                '#FF7582', // Musim Timur (Merlot Coral Rose cerah - Peak)
                '#94A3B8'  // Peralihan II (Cashmere Slate terang)
            ];
        }
        return [
            '#24364B', // Musim Barat (Navy asli SCOMAR)
            '#5B7A9C', // Peralihan I (Cashmere Blue asli)
            '#73343A', // Musim Timur (Merlot asli - Peak)
            '#8E9FA3'  // Peralihan II (Cashmere Slate asli)
        ];
    }

    // Warna batang komparasi tahunan yang kontras di Light vs Dark Mode
    function getAnnualComparisonBarColors(selectedYear, isDark) {
        if (isDark) {
            return csvYearsAsc.map(yr => String(yr) === String(selectedYear) ? '#FF7582' : '#4B6A8A');
        }
        return csvYearsAsc.map(yr => String(yr) === String(selectedYear) ? '#73343A' : '#A5B8D0');
    }

    // Helper data untuk Grafik 1 (4 Musim)
    function getSeasonalMetricData(year, metric) {
        const yrData = csvCatchByYear[year] || { produksi_ton: [0,0,0,0], trip: [0,0,0,0], cpue: [0,0,0,0] };
        if (metric === 'trip') {
            return {
                data: yrData.trip || [0,0,0,0],
                label: `Jumlah Trip Penangkapan Tahun ${year} (trip)`,
                yCallback: (val) => val.toLocaleString('id-ID') + ' trip',
                tooltipLabel: (ctx) => {
                    if (ctx.raw === null || ctx.raw === undefined) return ' Data tidak tersedia (Null)';
                    return ` Jumlah Trip: ${Number(ctx.raw).toLocaleString('id-ID')} trip`;
                }
            };
        } else if (metric === 'cpue') {
            return {
                data: yrData.cpue || [0,0,0,0],
                label: `CPUE Hasil Tangkapan Tahun ${year} (kg/trip)`,
                yCallback: (val) => val.toLocaleString('id-ID') + ' kg/trip',
                tooltipLabel: (ctx) => {
                    if (ctx.raw === null || ctx.raw === undefined) return ' Data tidak tersedia (Null)';
                    return ` CPUE: ${Number(ctx.raw).toFixed(2)} kg/trip`;
                }
            };
        }
        // Default: 'produksi' (ton)
        return {
            data: yrData.produksi_ton || [0,0,0,0],
            label: `Produksi Hasil Tangkapan Tahun ${year} (ton)`,
            yCallback: (val) => val.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 3 }) + ' ton',
            tooltipLabel: (ctx) => {
                if (ctx.raw === null || ctx.raw === undefined) return ' Data tidak tersedia (Null)';
                const valTon = Number(ctx.raw);
                const valKg = Math.round(valTon * 1000);
                return ` Produksi: ${valTon.toFixed(3)} ton (${valKg.toLocaleString('id-ID')} kg)`;
            }
        };
    }

    // Helper data untuk Grafik 2 (Perbandingan Antartahun 2021-2026)
    function getAnnualMetricData(selectedYear, metric) {
        if (metric === 'trip') {
            return {
                data: csvYearsAsc.map(y => csvCatchAnnualTotals[y]?.trip ?? null),
                label: 'Total Trip Tahunan (trip)',
                yCallback: (val) => val.toLocaleString('id-ID') + ' trip',
                tooltipLabel: (ctx) => {
                    if (ctx.raw === null || ctx.raw === undefined) return ' Data tidak tersedia (Null)';
                    return ` Total Trip: ${Number(ctx.raw).toLocaleString('id-ID')} trip`;
                }
            };
        } else if (metric === 'cpue') {
            return {
                data: csvYearsAsc.map(y => csvCatchAnnualTotals[y]?.cpue ?? null),
                label: 'CPUE Tahunan (kg/trip)',
                yCallback: (val) => val.toLocaleString('id-ID') + ' kg/trip',
                tooltipLabel: (ctx) => {
                    if (ctx.raw === null || ctx.raw === undefined) return ' Data tidak tersedia (Null)';
                    return ` CPUE Tahunan: ${Number(ctx.raw).toFixed(2)} kg/trip`;
                }
            };
        }
        // Default: 'produksi' (ton)
        return {
            data: csvYearsAsc.map(y => csvCatchAnnualTotals[y]?.produksi_ton ?? null),
            label: 'Total Produksi Tahunan (ton)',
            yCallback: (val) => val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 1 }) + ' ton',
            tooltipLabel: (ctx) => {
                if (ctx.raw === null || ctx.raw === undefined) return ' Data tidak tersedia (Null)';
                const valTon = Number(ctx.raw);
                const valKg = Math.round(valTon * 1000);
                return ` Total Produksi: ${valTon.toFixed(3)} ton (${valKg.toLocaleString('id-ID')} kg)`;
            }
        };
    }

    // ── 1. INISIALISASI GRAFIK BATANG 4 MUSIM ─────────────────────
    function initSeasonalBarChart(year, metric) {
        const ctx = document.getElementById('seasonalBarChartCanvas');
        if (!ctx) return;

        const colors = getChartThemeColors();
        const metricConfig = getSeasonalMetricData(year, metric);

        seasonalBarChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Musim Barat', 'Peralihan I', 'Musim Timur', 'Peralihan II'],
                datasets: [{
                    label: metricConfig.label,
                    data: metricConfig.data,
                    backgroundColor: getSeasonalBarColors(colors.isDark),
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 54
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: 3,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: colors.tooltipBg,
                        titleColor: colors.tooltipText,
                        bodyColor: colors.tooltipText,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: metricConfig.tooltipLabel
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: colors.textColor,
                            font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: colors.gridColor },
                        ticks: {
                            color: colors.textColor,
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' },
                            callback: metricConfig.yCallback
                        }
                    }
                }
            }
        });
    }

    // ── 2. INISIALISASI GRAFIK PERBANDINGAN ANTARTAHUN (2021-2026) ─
    function initAnnualComparisonChart(selectedYear, metric) {
        const ctx = document.getElementById('annualComparisonChartCanvas');
        if (!ctx) return;

        const colors = getChartThemeColors();
        const metricConfig = getAnnualMetricData(selectedYear, metric);
        const bgColors = getAnnualComparisonBarColors(selectedYear, colors.isDark);

        annualComparisonChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: csvYearsAsc.map(y => 'Tahun ' + y),
                datasets: [{
                    label: metricConfig.label,
                    data: metricConfig.data,
                    backgroundColor: bgColors,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 46
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: 3,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: colors.tooltipBg,
                        titleColor: colors.tooltipText,
                        bodyColor: colors.tooltipText,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: metricConfig.tooltipLabel
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: colors.textColor,
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: colors.gridColor },
                        ticks: {
                            color: colors.textColor,
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' },
                            callback: metricConfig.yCallback
                        }
                    }
                }
            }
        });
    }

    // ── 3. FUNGSI UPDATE GRAFIK UTAMA ──────────────────────────────
    function updateCharts() {
        const colors = getChartThemeColors();

        // Update Grafik 1 (4 Musim)
        if (seasonalBarChartInstance) {
            const config = getSeasonalMetricData(activeYear, activeMetric);
            seasonalBarChartInstance.data.datasets[0].data = config.data;
            seasonalBarChartInstance.data.datasets[0].label = config.label;
            seasonalBarChartInstance.data.datasets[0].backgroundColor = getSeasonalBarColors(colors.isDark);
            seasonalBarChartInstance.options.scales.x.ticks.color = colors.textColor;
            seasonalBarChartInstance.options.scales.y.ticks.color = colors.textColor;
            seasonalBarChartInstance.options.scales.y.grid.color  = colors.gridColor;
            seasonalBarChartInstance.options.scales.y.ticks.callback = config.yCallback;
            seasonalBarChartInstance.options.plugins.tooltip.callbacks.label = config.tooltipLabel;
            seasonalBarChartInstance.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            seasonalBarChartInstance.options.plugins.tooltip.titleColor = colors.tooltipText;
            seasonalBarChartInstance.options.plugins.tooltip.bodyColor = colors.tooltipText;
            seasonalBarChartInstance.update();
        }

        // Update Grafik 2 (Perbandingan Antartahun)
        if (annualComparisonChartInstance) {
            const config = getAnnualMetricData(activeYear, activeMetric);
            annualComparisonChartInstance.data.datasets[0].data = config.data;
            annualComparisonChartInstance.data.datasets[0].label = config.label;
            annualComparisonChartInstance.data.datasets[0].backgroundColor = getAnnualComparisonBarColors(activeYear, colors.isDark);
            annualComparisonChartInstance.options.scales.x.ticks.color = colors.textColor;
            annualComparisonChartInstance.options.scales.y.ticks.color = colors.textColor;
            annualComparisonChartInstance.options.scales.y.grid.color  = colors.gridColor;
            annualComparisonChartInstance.options.scales.y.ticks.callback = config.yCallback;
            annualComparisonChartInstance.options.plugins.tooltip.callbacks.label = config.tooltipLabel;
            annualComparisonChartInstance.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
            annualComparisonChartInstance.options.plugins.tooltip.titleColor = colors.tooltipText;
            annualComparisonChartInstance.options.plugins.tooltip.bodyColor = colors.tooltipText;
            annualComparisonChartInstance.update();
        }
    }

    // Bridge fungsi global untuk dipanggil Alpine.js
    window.updateSeasonalCharts = function (year) {
        activeYear = String(year);
        updateCharts();
    };

    window.updateSeasonalMetric = function (metric) {
        activeMetric = metric;
        updateCharts();
    };

    // Jalankan Chart Awal
    initSeasonalBarChart(activeYear, activeMetric);
    initAnnualComparisonChart(activeYear, activeMetric);

    // Sinkronisasi saat pergantian mode Tema (Light / Dark) secara real-time
    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            setTimeout(function () {
                updateCharts();
            }, 50);
        });
    }

    window.addEventListener('scomar:theme-changed', function () {
        updateCharts();
    });
    window.addEventListener('themeChanged', function () {
        updateCharts();
    });
});
</script>
@endpush
@endsection
