@extends('layouts.app')

@section('title', 'Analisis Hubungan Oseanografi dan CPUE')

@push('styles')
<style>
/* ================================================================
   ANALYSIS PAGE — SCOMAR
   Palet: Merlot #73343A | Deep Navy #24364B | Cashmere Blue #5B7A9C
================================================================ */

/* ── PAGE HEADER ── */
.analysis-page-header {
    padding: 24px 0 16px;
}
.analysis-page-header h1 {
    font-size: 20px;
    font-weight: 800;
    color: var(--deep-navy, #24364B);
    margin: 0 0 4px;
}
.analysis-page-header .subtitle {
    font-size: 12.5px;
    color: var(--text-muted, #6B7280);
    margin: 0;
}

/* ── FILTER BAR ── */
.analysis-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    padding: 14px 18px;
    background: var(--bg-surface, #FFFFFF);
    border: 1px solid var(--border, #E5E7EB);
    border-radius: 12px;
    margin-bottom: 20px;
}
.analysis-filter-bar label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted, #6B7280);
    white-space: nowrap;
}
.analysis-filter-bar select {
    height: 34px;
    padding: 0 10px;
    font-size: 13px;
    border: 1px solid var(--border, #E5E7EB);
    border-radius: 8px;
    background: var(--bg-surface, #FFFFFF);
    color: var(--text-primary, #1F2937);
    cursor: pointer;
}
.analysis-filter-bar select:focus {
    outline: 2px solid #5B7A9C;
    outline-offset: 1px;
}
.btn-filter-apply {
    height: 34px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 600;
    background: var(--deep-navy, #24364B);
    color: #FFFFFF;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
}
.btn-filter-apply:hover { background: #1a2a3a; }

/* ── SUMMARY CARDS ── */
.summary-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
@media (max-width: 1024px) { .summary-row { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px)  { .summary-row { grid-template-columns: 1fr; } }

.summary-card {
    padding: 16px;
    background: var(--bg-surface, #FFFFFF);
    border: 1px solid var(--border, #E5E7EB);
    border-radius: 12px;
    position: relative;
    overflow: hidden;
}
.summary-card.best { border-color: #24364B; }
.summary-card-label {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-muted, #6B7280);
    margin-bottom: 8px;
}
.summary-card-param {
    font-size: 13px;
    font-weight: 700;
    color: var(--deep-navy, #24364B);
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.summary-card-r {
    font-size: 22px;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 2px;
}
.r-positive { color: #16A34A; }
.r-negative { color: #DC2626; }
.r-neutral  { color: #6B7280; }
.summary-card-meta {
    font-size: 11px;
    color: var(--text-muted, #6B7280);
}
.badge-direction {
    display: inline-block;
    padding: 1px 7px;
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: 600;
}
.badge-positive { background: #DCFCE7; color: #16A34A; }
.badge-negative { background: #FEE2E2; color: #DC2626; }
.badge-neutral  { background: #F3F4F6; color: #6B7280; }
.badge-sig      { background: #DBEAFE; color: #1D4ED8; }
.badge-insig    { background: #F3F4F6; color: #9CA3AF; }
.badge-na       { background: #F3F4F6; color: #9CA3AF; }

/* ── CHART GRID 2×2 ── */
.chart-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}
@media (max-width: 900px) { .chart-grid { grid-template-columns: 1fr; } }

.chart-card {
    background: var(--bg-surface, #FFFFFF);
    border: 1px solid var(--border, #E5E7EB);
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.chart-card-header {
    padding: 14px 18px 10px;
    border-bottom: 1px solid var(--border, #E5E7EB);
    background: var(--bg-surface, #FFFFFF);
}
.chart-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--deep-navy, #24364B);
    margin: 0 0 2px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.param-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.pb-spl  { background: #EFF6FF; color: #1D4ED8; }
.pb-chla { background: #F0FDF4; color: #15803D; }
.pb-tss  { background: #FFFBEB; color: #B45309; }
.pb-arus { background: #F5F3FF; color: #6D28D9; }

.chart-card-subtitle {
    font-size: 11.5px;
    color: var(--text-muted, #6B7280);
    margin: 0;
}
.chart-canvas-wrap {
    padding: 12px 16px;
    flex: 1;
}
.chart-canvas-wrap canvas {
    width: 100% !important;
    max-height: 240px;
}
.chart-stats-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.chart-stats-table th,
.chart-stats-table td {
    padding: 7px 12px;
    text-align: left;
    border-top: 1px solid var(--border, #E5E7EB);
}
.chart-stats-table th {
    font-weight: 600;
    color: var(--text-muted, #6B7280);
    background: var(--bg-muted, #F9FAFB);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.chart-stats-table td {
    color: var(--text-primary, #1F2937);
    font-weight: 500;
}
.stat-value {
    font-weight: 700;
    font-size: 13px;
}

/* ── INTERPRETATION SECTION ── */
.interpretation-section {
    background: var(--bg-surface, #FFFFFF);
    border: 1px solid var(--border, #E5E7EB);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}
.interpretation-section h3 {
    font-size: 14px;
    font-weight: 700;
    color: var(--deep-navy, #24364B);
    margin: 0 0 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.interp-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
@media (max-width: 700px) { .interp-grid { grid-template-columns: 1fr; } }

.interp-item {
    padding: 12px 16px;
    border-radius: 10px;
    border-left: 4px solid;
    background: var(--bg-muted, #F9FAFB);
    font-size: 12.5px;
    line-height: 1.6;
}
.interp-item.strong   { border-color: #16A34A; }
.interp-item.moderate { border-color: #D97706; }
.interp-item.weak     { border-color: #9CA3AF; }
.interp-item.nodata   { border-color: #E5E7EB; color: #9CA3AF; }
.interp-param-name    { font-weight: 700; color: var(--deep-navy, #24364B); }

/* ── LOADING OVERLAY ── */
#analysisLoading {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(255,255,255,0.7);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
#analysisLoading.active { display: flex; }
.loading-spinner {
    width: 40px; height: 40px;
    border: 4px solid #E5E7EB;
    border-top-color: #24364B;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── DARK MODE KHUSUS HALAMAN ANALISIS ── */
[data-theme="dark"] .analysis-page-header h1 {
    color: var(--text-primary);
}
[data-theme="dark"] .analysis-filter-bar {
    background: var(--bg-surface);
    border-color: var(--border-subtle);
    box-shadow: var(--card-shadow);
}
[data-theme="dark"] .analysis-filter-bar label {
    color: var(--text-secondary);
}
[data-theme="dark"] .analysis-filter-bar select {
    background: #141F2B;
    border-color: var(--border-subtle);
    color: var(--text-primary);
}
[data-theme="dark"] .btn-filter-apply {
    background: #2563EB;
    color: #FFFFFF;
}
[data-theme="dark"] .btn-filter-apply:hover {
    background: #1D4ED8;
}

[data-theme="dark"] .summary-card {
    background: var(--bg-surface);
    border-color: var(--border-subtle);
    box-shadow: var(--card-shadow);
}
[data-theme="dark"] .summary-card.best {
    border-color: #38BDF8;
}
[data-theme="dark"] .summary-card-label {
    color: var(--text-muted);
}
[data-theme="dark"] .summary-card-param {
    color: var(--text-primary);
}
[data-theme="dark"] .summary-card-meta {
    color: var(--text-muted);
}
[data-theme="dark"] .r-positive { color: #34D399; }
[data-theme="dark"] .r-negative { color: #F87171; }
[data-theme="dark"] .r-neutral  { color: #94A3B8; }

[data-theme="dark"] .badge-positive {
    background: rgba(52, 211, 153, 0.2);
    color: #34D399;
    border: 1px solid rgba(52, 211, 153, 0.35);
}
[data-theme="dark"] .badge-negative {
    background: rgba(248, 113, 113, 0.2);
    color: #F87171;
    border: 1px solid rgba(248, 113, 113, 0.35);
}
[data-theme="dark"] .badge-neutral,
[data-theme="dark"] .badge-insig,
[data-theme="dark"] .badge-na {
    background: rgba(148, 163, 184, 0.15);
    color: #94A3B8;
    border: 1px solid rgba(148, 163, 184, 0.25);
}
[data-theme="dark"] .badge-sig {
    background: rgba(56, 189, 248, 0.2);
    color: #38BDF8;
    border: 1px solid rgba(56, 189, 248, 0.35);
}

[data-theme="dark"] .chart-card {
    background: var(--bg-surface);
    border-color: var(--border-subtle);
    box-shadow: var(--card-shadow);
}
[data-theme="dark"] .chart-card-header {
    background: var(--bg-surface);
    border-bottom-color: var(--border-subtle);
}
[data-theme="dark"] .chart-card-title {
    color: var(--text-primary);
}
[data-theme="dark"] .chart-card-subtitle {
    color: var(--text-secondary);
}

[data-theme="dark"] .pb-spl  { background: rgba(56, 189, 248, 0.2); color: #38BDF8; }
[data-theme="dark"] .pb-chla { background: rgba(52, 211, 153, 0.2); color: #34D399; }
[data-theme="dark"] .pb-tss  { background: rgba(251, 191, 36, 0.2); color: #FBBF24; }
[data-theme="dark"] .pb-arus { background: rgba(167, 139, 250, 0.2); color: #A78BFA; }

[data-theme="dark"] .chart-stats-table th {
    background: rgba(165, 184, 208, 0.08);
    color: var(--text-secondary);
    border-top-color: var(--border-subtle);
}
[data-theme="dark"] .chart-stats-table td {
    color: var(--text-primary);
    border-top-color: var(--border-subtle);
}

[data-theme="dark"] .interpretation-section {
    background: var(--bg-surface);
    border-color: var(--border-subtle);
    box-shadow: var(--card-shadow);
}
[data-theme="dark"] .interpretation-section h3 {
    color: var(--text-primary);
}
[data-theme="dark"] .interp-item {
    background: #141F2B;
    color: var(--text-primary);
    border: 1px solid var(--border-subtle);
    border-left-width: 4px;
}
[data-theme="dark"] .interp-param-name {
    color: var(--text-primary);
}
[data-theme="dark"] .interp-item.strong   { border-left-color: #34D399; }
[data-theme="dark"] .interp-item.moderate { border-left-color: #FBBF24; }
[data-theme="dark"] .interp-item.weak     { border-left-color: #94A3B8; }
[data-theme="dark"] .interp-item.nodata   { border-left-color: #64748B; color: #94A3B8; }

[data-theme="dark"] #analysisLoading {
    background: rgba(15, 23, 34, 0.75);
}
[data-theme="dark"] .loading-spinner {
    border-color: rgba(165, 184, 208, 0.2);
    border-top-color: #38BDF8;
}

/* ── RESPONSIVE ANALISIS OSEANOGRAFI ────────────────────── */
@media (max-width: 768px) {
    .summary-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .chart-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .interp-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .page-header-title {
        font-size: 18px;
    }
    .page-header-sub {
        font-size: 12px;
    }
}
@media (max-width: 640px) {
    .summary-row {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .summary-card {
        padding: 10px 12px;
    }
    .chart-card {
        padding: 12px;
    }
    .interp-card {
        padding: 10px 12px;
    }
    .chart-grid {
        gap: 12px;
    }
}
@media (max-width: 480px) {
    .summary-row {
        gap: 6px;
    }
    .stat-val {
        font-size: 20px;
    }
    .stat-label {
        font-size: 10px;
    }
    .chart-title {
        font-size: 12px;
    }
}
@media (max-width: 380px) {
    .summary-card {
        padding: 8px 10px;
    }
    .stat-val {
        font-size: 18px;
    }
}

</style>
@endpush

@section('content')

{{-- Loading Overlay --}}
<div id="analysisLoading">
    <div class="loading-spinner"></div>
</div>

<div class="container-fluid px-0">
    {{-- Page Header --}}
    <div class="analysis-page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     style="display:inline;vertical-align:-2px;margin-right:6px">
                    <circle cx="5" cy="18" r="2"/><circle cx="12" cy="10" r="2"/>
                    <circle cx="19" cy="14" r="2"/><circle cx="8" cy="6" r="2"/>
                    <polyline points="4 14 9 18 15 12 20 16"/>
                </svg>
                Analisis Hubungan Oseanografi dan CPUE
            </h1>
            <p class="subtitle">SCOMAR / Analisis Hubungan Oseanografi dan CPUE &mdash;
                Korelasi parameter oseanografi terhadap produktivitas hasil tangkapan ikan tenggiri
                berdasarkan data 2021&ndash;2026</p>
        </div>
        <div class="page-header-actions no-print">
            <button
                type="button"
                id="btnExportAnalysisCsvHeader"
                class="btn-filter-apply"
                onclick="exportAnalysisCsv()"
                style="background:var(--bg-surface, #FFFFFF); color:var(--deep-navy, #24364B); border:1px solid var(--border, #E5E7EB); cursor:pointer;"
                title="Unduh data analisis korelasi oseanografi & CPUE dalam format CSV (Excel Ready)"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span>Ekspor CSV</span>
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="analysis-filter-bar" id="filterBar">
        <label for="filterYear">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Tahun
        </label>
        <select id="filterYear" name="year">
            <option value="all" {{ $filterYear === 'all' ? 'selected' : '' }}>Semua Tahun</option>
            @foreach($availableYears as $yr)
                <option value="{{ $yr }}" {{ $filterYear === $yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>

        <label for="filterSeason" style="margin-left:6px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2z"/>
                <path d="M12 12v10M8 16l4 4 4-4"/>
            </svg>
            Musim
        </label>
        <select id="filterSeason" name="season">
            <option value="all"       {{ $filterSeason === 'all'       ? 'selected' : '' }}>Semua Musim</option>
            <option value="barat"     {{ $filterSeason === 'barat'     ? 'selected' : '' }}>Musim Barat</option>
            <option value="peralihan1"{{ $filterSeason === 'peralihan1'? 'selected' : '' }}>Peralihan I</option>
            <option value="timur"     {{ $filterSeason === 'timur'     ? 'selected' : '' }}>Musim Timur</option>
            <option value="peralihan2"{{ $filterSeason === 'peralihan2'? 'selected' : '' }}>Peralihan II</option>
        </select>

        <button class="btn-filter-apply" id="btnApplyFilter">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            Terapkan Filter
        </button>

        <button
            type="button"
            class="btn-filter-apply"
            id="btnExportAnalysisCsv"
            onclick="exportAnalysisCsv()"
            style="background:var(--bg-surface, #FFFFFF); color:var(--deep-navy, #24364B); border:1px solid var(--border, #E5E7EB); cursor:pointer;"
            title="Unduh data analisis korelasi oseanografi & CPUE dalam format CSV (Excel Ready)"
        >
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>Ekspor CSV</span>
        </button>

        <span style="margin-left:auto;font-size:11.5px;color:var(--text-muted)">
            Data: 2021&ndash;2026 &bull; n={{ collect($paramAnalysis)->first()['stats']['n'] ?? 0 }} pasang data
        </span>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-row" id="summaryRow">
        @foreach($paramAnalysis as $pk => $pa)
        @php
            $r = $pa['stats']['pearson'];
            $dir = $pa['stats']['direction'];
            $str = $pa['stats']['strength'];
            $sig = $pa['stats']['significant'];
            $n   = $pa['stats']['n'];
            $rClass = ($r === null) ? 'r-neutral' : ($r >= 0 ? 'r-positive' : 'r-negative');
            $badgeDir = ($dir === 'Positif') ? 'badge-positive' : (($dir === 'Negatif') ? 'badge-negative' : 'badge-neutral');
            $paramBadgeClass = ['spl'=>'pb-spl','chla'=>'pb-chla','tss'=>'pb-tss','arus'=>'pb-arus'][$pk];
            $isBest = ($pk === $bestParam);
        @endphp
        <div class="summary-card {{ $isBest ? 'best' : '' }}">
            @if($isBest)
            <div style="position:absolute;top:10px;right:10px;">
                <span style="font-size:9.5px;font-weight:700;background:#EEF2FF;color:#4338CA;padding:2px 7px;border-radius:20px">
                    Korelasi Tertinggi
                </span>
            </div>
            @endif
            <div class="summary-card-label">
                <span class="param-badge {{ $paramBadgeClass }}">{{ strtoupper($pk) }}</span>
            </div>
            <div class="summary-card-param" title="{{ $pa['label'] }}">{{ $pa['label'] }}</div>
            <div class="summary-card-r {{ $rClass }}">
                {{ $r !== null ? number_format($r, 3) : '—' }}
            </div>
            <div class="summary-card-meta" style="margin-bottom:6px">
                r Pearson &bull; n={{ $n }}
                @if($pa['stats']['pvalue'] !== null)
                    &bull; p={{ number_format($pa['stats']['pvalue'], 3) }}
                @endif
            </div>
            @if($dir)
                <span class="badge-direction {{ $badgeDir }}">{{ $dir }}</span>
                <span class="badge-direction" style="margin-left:4px;{{ $str==='Kuat'?'background:#EEF2FF;color:#4338CA':($str==='Sedang'?'background:#FFFBEB;color:#B45309':'background:#F3F4F6;color:#9CA3AF') }}">
                    {{ $str ?? '' }}
                </span>
                @if($sig !== null)
                    <span class="badge-direction {{ $sig ? 'badge-sig' : 'badge-insig' }}" style="margin-left:4px">
                        {{ $sig ? 'Signifikan' : 'Tdk Signifikan' }}
                    </span>
                @endif
            @else
                <span class="badge-na">Data tidak mencukupi</span>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Chart Grid 2x2 --}}
    <div class="chart-grid" id="chartGrid">
        @foreach($paramAnalysis as $pk => $pa)
        @php
            $paramBadgeClass = ['spl'=>'pb-spl','chla'=>'pb-chla','tss'=>'pb-tss','arus'=>'pb-arus'][$pk];
            $paramIcon = [
                'spl'  => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
                'chla' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
                'tss'  => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>',
                'arus' => '<path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 10 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/>',
            ][$pk];
            $r  = $pa['stats']['pearson'];
            $n  = $pa['stats']['n'];
            $slope = $pa['stats']['slope'];
            $intercept = $pa['stats']['intercept'];
            $r2 = $pa['stats']['r2'];
            $pv = $pa['stats']['pvalue'];
            $dir = $pa['stats']['direction'];
            $str = $pa['stats']['strength'];
            $sig = $pa['stats']['significant'];

            // Scatter points (valid only)
            $validPoints = collect($pa['points'])->where('valid', true)->values();
        @endphp
        <div class="chart-card">
            <div class="chart-card-header">
                <div class="chart-card-title">
                    <span class="param-badge {{ $paramBadgeClass }}">{{ strtoupper($pk) }}</span>
                    {{ $pa['label'] }} vs CPUE
                </div>
                <p class="chart-card-subtitle">
                    Sumbu X: {{ $pa['label'] }} ({{ $pa['unit'] }}) &bull; Sumbu Y: CPUE (kg/trip)
                    @if($n > 0) &bull; n={{ $n }} pasang data @endif
                </p>
            </div>

            <div class="chart-canvas-wrap">
                @if($n >= 3)
                    <canvas id="chart-{{ $pk }}" height="230"></canvas>
                @else
                    <div style="display:flex;align-items:center;justify-content:center;height:200px;color:var(--text-muted);font-size:13px;text-align:center;">
                        <div>
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB"
                                 stroke-width="2" style="display:block;margin:0 auto 8px">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            Data tidak mencukupi untuk analisis<br>
                            <small style="color:#D1D5DB">Diperlukan minimal 3 pasang data valid</small>
                        </div>
                    </div>
                @endif
            </div>

            <table class="chart-stats-table">
                <thead>
                    <tr>
                        <th>Statistik</th>
                        <th>Nilai</th>
                        <th>Interpretasi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Korelasi Pearson (r)</td>
                        <td><span class="stat-value">{{ $r !== null ? number_format($r, 4) : '—' }}</span></td>
                        <td>
                            @if($r !== null)
                                <span class="badge-direction {{ $dir==='Positif'?'badge-positive':'badge-negative' }}">{{ $dir }}</span>
                                <span class="badge-direction" style="margin-left:3px;{{ $str==='Kuat'?'background:#EEF2FF;color:#4338CA':($str==='Sedang'?'background:#FFFBEB;color:#B45309':'background:#F3F4F6;color:#9CA3AF') }}">{{ $str }}</span>
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Korelasi Spearman (&rho;)</td>
                        <td><span class="stat-value">{{ $pa['stats']['spearman'] !== null ? number_format($pa['stats']['spearman'], 4) : '—' }}</span></td>
                        <td><span style="color:var(--text-muted);font-size:11px">Rank-based</span></td>
                    </tr>
                    <tr>
                        <td>P-value (dua sisi)</td>
                        <td><span class="stat-value">{{ $pv !== null ? number_format($pv, 4) : '—' }}</span></td>
                        <td>
                            @if($sig !== null)
                                <span class="badge-direction {{ $sig ? 'badge-sig' : 'badge-insig' }}">
                                    {{ $sig ? 'Signifikan (p &lt; 0,05)' : 'Tidak Signifikan' }}
                                </span>
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Koefisien Determinasi (R&sup2;)</td>
                        <td><span class="stat-value">{{ $r2 !== null ? number_format($r2, 4) : '—' }}</span></td>
                        <td>
                            @if($r2 !== null)
                                <span style="font-size:11.5px;color:var(--text-muted)">
                                    {{ number_format($r2 * 100, 1) }}% variansi CPUE dijelaskan
                                </span>
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Persamaan Regresi</td>
                        <td colspan="2">
                            @if($slope !== null && $intercept !== null)
                                <span class="stat-value" style="font-size:12px">
                                    CPUE = {{ number_format($slope, 4) }} &times; {{ strtoupper($pk) }}
                                    {{ $intercept >= 0 ? '+' : '' }} {{ number_format($intercept, 4) }}
                                </span>
                            @else
                                <span style="color:var(--text-muted)">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Jumlah Sampel (n)</td>
                        <td><span class="stat-value">{{ $n }}</span></td>
                        <td><span style="color:var(--text-muted);font-size:11px">{{ $n < 3 ? 'Min. 3 diperlukan' : 'Pasang data valid' }}</span></td>
                    </tr>
                </tbody>
            </table>

            {{-- Catatan data kosong --}}
            @php
                $nullCount = collect($pa['points'])->where('valid', false)->count();
            @endphp
            @if($nullCount > 0)
            <div style="padding:8px 16px;font-size:11px;color:var(--text-muted);border-top:1px solid var(--border,#E5E7EB);background:var(--bg-muted,#F9FAFB)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" style="vertical-align:-1px;margin-right:4px">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $nullCount }} data tidak tersedia (Null/NaN) dikecualikan dari analisis statistik
            </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Interpretasi Section --}}
    <div class="interpretation-section">
        <h3>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            Ringkasan Interpretasi
        </h3>
        <p style="font-size:12px;color:var(--text-muted);margin:0 0 14px;line-height:1.6">
            Interpretasi berikut didasarkan pada nilai korelasi statistik dari data yang tersedia.
            Korelasi tidak menunjukkan hubungan sebab-akibat; faktor lain di luar data ini juga dapat berpengaruh.
        </p>
        <div class="interp-grid">
            @foreach($paramAnalysis as $pk => $pa)
            @php
                $r  = $pa['stats']['pearson'];
                $n  = $pa['stats']['n'];
                $dir = $pa['stats']['direction'];
                $str = $pa['stats']['strength'];
                $sig = $pa['stats']['significant'];
                $pv = $pa['stats']['pvalue'];

                if ($n < 3) {
                    $interpClass = 'nodata';
                    $interpText  = 'Data tidak mencukupi untuk menghasilkan interpretasi statistik yang valid (n=' . $n . '). Diperlukan minimal 3 pasang data.';
                } elseif ($str === 'Kuat') {
                    $interpClass = 'strong';
                } elseif ($str === 'Sedang') {
                    $interpClass = 'moderate';
                } else {
                    $interpClass = 'weak';
                }
            @endphp
            <div class="interp-item {{ $interpClass }}">
                <div class="interp-param-name" style="margin-bottom:4px">
                    {{ $pa['label'] }} ({{ $pa['unit'] }})
                </div>
                @if($n >= 3)
                    Hubungan antara {{ $pa['label'] }} dan CPUE menunjukkan korelasi
                    <strong>{{ strtolower($str) }}</strong> yang bersifat <strong>{{ strtolower($dir ?? '') }}</strong>
                    (r = {{ number_format($r, 3) }}, p = {{ number_format($pv ?? 0, 3) }}, n = {{ $n }}).
                    @if($sig)
                        Korelasi ini <strong>signifikan secara statistik</strong> pada taraf &alpha; = 0,05,
                        yang berarti terdapat kecenderungan hubungan linier antara kedua variabel dalam dataset ini.
                    @else
                        Korelasi ini <strong>tidak signifikan secara statistik</strong> (p &ge; 0,05),
                        sehingga perlu interpretasi yang hati-hati mengingat ukuran sampel yang terbatas.
                    @endif
                    Nilai R&sup2; = {{ number_format(($pa['stats']['r2'] ?? 0), 3) }} menunjukkan
                    bahwa {{ number_format(($pa['stats']['r2'] ?? 0)*100, 1) }}% variabilitas CPUE
                    dapat dijelaskan oleh variasi {{ $pa['label'] }} dalam dataset ini.
                @else
                    {{ $interpText }}
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Catatan Metodologi --}}
    <div style="padding:14px 18px;background:var(--bg-muted,#F9FAFB);border:1px solid var(--border,#E5E7EB);border-radius:10px;font-size:11.5px;color:var(--text-muted);line-height:1.7;margin-bottom:24px">
        <strong style="color:var(--deep-navy,#24364B)">Catatan Metodologi:</strong>
        Data oseanografi (SPL, Klorofil-a, TSS, Arus) dan CPUE dipasangkan berdasarkan kecocokan tahun dan musim pengamatan.
        Korelasi Pearson mengukur kekuatan hubungan linier; Korelasi Spearman mengukur hubungan monoton berbasis peringkat.
        P-value dihitung menggunakan aproksimasi distribusi-t dua sisi (df = n-2).
        Data dengan nilai kosong (Null/NaN) dikecualikan dari seluruh perhitungan statistik.
        Analisis ini bersifat eksploratif dan tidak menggantikan kajian ekologis yang lebih komprehensif.
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Data dari server ───────────────────────────────────────────
    const paramAnalysis = @json($paramAnalysis);

    // Warna per parameter dinamis sesuai tema (Light / Dark)
    function getAnalysisColors(isDark) {
        if (isDark) {
            return {
                spl:  { scatter: '#38BDF8', line: '#38BDF8', bg: 'rgba(56, 189, 248, 0.15)' },
                chla: { scatter: '#34D399', line: '#34D399', bg: 'rgba(52, 211, 153, 0.15)' },
                tss:  { scatter: '#FBBF24', line: '#FBBF24', bg: 'rgba(251, 191, 36, 0.15)' },
                arus: { scatter: '#A78BFA', line: '#A78BFA', bg: 'rgba(167, 139, 250, 0.15)' },
            };
        }
        return {
            spl:  { scatter: 'rgba(29,78,216,0.75)',   line: 'rgba(29,78,216,0.9)',    bg: 'rgba(29,78,216,0.08)' },
            chla: { scatter: 'rgba(21,128,61,0.75)',    line: 'rgba(21,128,61,0.9)',     bg: 'rgba(21,128,61,0.08)' },
            tss:  { scatter: 'rgba(180,83,9,0.75)',     line: 'rgba(180,83,9,0.9)',      bg: 'rgba(180,83,9,0.08)' },
            arus: { scatter: 'rgba(109,40,217,0.75)',   line: 'rgba(109,40,217,0.9)',    bg: 'rgba(109,40,217,0.08)' },
        };
    }

    const chartInstances = {};

    // ── Bangun dataset regresi ─────────────────────────────────────
    function buildRegressionLine(points, slope, intercept) {
        if (slope === null || intercept === null || points.length < 2) return [];
        const xs = points.map(p => p.x);
        const minX = Math.min(...xs);
        const maxX = Math.max(...xs);
        const step = (maxX - minX) / 50 || 0.001;
        const result = [];
        for (let x = minX; x <= maxX + step * 0.1; x += step) {
            result.push({ x: parseFloat(x.toFixed(5)), y: parseFloat((slope * x + intercept).toFixed(5)) });
        }
        return result;
    }

    // ── Render satu grafik scatter ─────────────────────────────────
    function renderChart(pk, pa) {
        const canvas = document.getElementById('chart-' + pk);
        if (!canvas) return;

        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const colors = getAnalysisColors(isDark);
        const stats  = pa.stats;
        const col    = colors[pk];
        const validPoints = pa.points.filter(p => p.valid);
        const scatterData = validPoints.map(p => ({ x: p.x, y: p.y, label: p.year + ' ' + p.season }));

        const datasets = [
            {
                label: pa.label + ' vs CPUE',
                type: 'scatter',
                data: scatterData,
                backgroundColor: col.scatter,
                borderColor: isDark ? '#1B293A' : col.line,
                borderWidth: isDark ? 2 : 1.5,
                pointRadius: 6,
                pointHoverRadius: 8.5,
                order: 2,
            }
        ];

        // Garis regresi
        if (stats.slope !== null && stats.intercept !== null && validPoints.length >= 3) {
            const regData = buildRegressionLine(scatterData, stats.slope, stats.intercept);
            datasets.push({
                label: 'Garis Regresi',
                type: 'line',
                data: regData,
                borderColor: col.line,
                borderWidth: 2.2,
                borderDash: [5, 3],
                pointRadius: 0,
                fill: false,
                tension: 0,
                order: 1,
            });
        }

        // Destroy jika sudah ada
        if (chartInstances[pk]) {
            chartInstances[pk].destroy();
        }

        const rLabel = stats.pearson !== null
            ? 'r = ' + stats.pearson.toFixed(3)
              + (stats.r2 !== null ? '  R² = ' + stats.r2.toFixed(3) : '')
              + (stats.significant !== null ? (stats.significant ? '  *Signifikan' : '  Tidak Signifikan') : '')
            : '';

        chartInstances[pk] = new Chart(canvas, {
            type: 'scatter',
            data: { datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 350 },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                            boxWidth: 14,
                            padding: 10,
                            color: isDark ? '#E2E8F0' : '#374151'
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#0F172A' : '#1E293B',
                        titleColor: '#FFFFFF',
                        bodyColor: '#F1F5F9',
                        borderColor: isDark ? 'rgba(148, 163, 184, 0.25)' : 'rgba(255, 255, 255, 0.15)',
                        borderWidth: 1,
                        callbacks: {
                            label(ctx) {
                                const d = ctx.raw;
                                if (d.label) {
                                    return [
                                        d.label,
                                        pa.label + ': ' + d.x + ' ' + pa.unit,
                                        'CPUE: ' + d.y + ' kg/trip'
                                    ];
                                }
                                return 'CPUE = ' + (pa.stats.slope * d.x + pa.stats.intercept).toFixed(3);
                            }
                        }
                    },
                    title: {
                        display: rLabel !== '',
                        text: rLabel,
                        font: { family: 'Plus Jakarta Sans', size: 11, weight: '700' },
                        color: isDark ? '#CBD5E1' : '#6B7280',
                        padding: { bottom: 4 }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: pa.label + ' (' + pa.unit + ')',
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                            color: isDark ? '#E2E8F0' : '#374151'
                        },
                        grid: {
                            color: isDark ? 'rgba(165, 184, 208, 0.10)' : 'rgba(0,0,0,0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 10, weight: '500' },
                            color: isDark ? '#94A3B8' : '#64748B'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'CPUE (kg/trip)',
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                            color: isDark ? '#E2E8F0' : '#374151'
                        },
                        grid: {
                            color: isDark ? 'rgba(165, 184, 208, 0.10)' : 'rgba(0,0,0,0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 10, weight: '500' },
                            color: isDark ? '#94A3B8' : '#64748B'
                        }
                    }
                }
            }
        });
    }

    // ── Init semua grafik ──────────────────────────────────────────
    function initAllCharts() {
        Object.keys(paramAnalysis).forEach(pk => {
            const pa = paramAnalysis[pk];
            if (pa.stats.n >= 3) {
                renderChart(pk, pa);
            }
        });
    }

    // Sinkronisasi saat pergantian mode Tema (Light / Dark)
    window.addEventListener('scomar:theme-changed', function () {
        initAllCharts();
    });
    window.addEventListener('themeChanged', function () {
        initAllCharts();
    });

    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            setTimeout(initAllCharts, 60);
        });
    }

    // ── Filter AJAX ────────────────────────────────────────────────
    document.getElementById('btnApplyFilter').addEventListener('click', function () {
        const year   = document.getElementById('filterYear').value;
        const season = document.getElementById('filterSeason').value;

        // Show loading
        document.getElementById('analysisLoading').classList.add('active');

        const url = '{{ route("charts.analysis.data") }}?year=' + encodeURIComponent(year)
                  + '&season=' + encodeURIComponent(season);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                // Redirect dengan parameter agar tabel dan summary cards ikut refresh
                window.location.href = '{{ route("charts.analysis") }}?year=' + encodeURIComponent(year)
                    + '&season=' + encodeURIComponent(season);
            })
            .catch(() => {
                window.location.href = '{{ route("charts.analysis") }}?year=' + encodeURIComponent(year)
                    + '&season=' + encodeURIComponent(season);
            });
    });

    // ── Init saat DOM siap ─────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', initAllCharts);
    if (document.readyState !== 'loading') initAllCharts();

})();

// ── Fungsi Ekspor Data Analisis Oseanografi & CPUE ke CSV (Resmi SCOMAR) ────
window.exportAnalysisCsv = function () {
    const btns = [document.getElementById('btnExportAnalysisCsv'), document.getElementById('btnExportAnalysisCsvHeader')];
    const origTexts = btns.map(b => b ? b.innerHTML : '');

    btns.forEach(b => {
        if (b) {
            b.innerHTML = '<span>Mengekspor CSV...</span>';
            b.disabled = true;
        }
    });

    const yr = document.getElementById('filterYear')?.value || 'all';
    const sn = document.getElementById('filterSeason')?.value || 'all';
    const url = "{{ route('charts.analysis.export-csv') }}?year=" + encodeURIComponent(yr) + "&season=" + encodeURIComponent(sn);

    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    document.body.appendChild(iframe);

    setTimeout(() => {
        btns.forEach((b, idx) => {
            if (b) {
                b.innerHTML = origTexts[idx];
                b.disabled = false;
            }
        });
        setTimeout(() => iframe.remove(), 2500);
    }, 1200);
};
</script>
@endpush
