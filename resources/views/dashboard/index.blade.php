{{-- ================================================================
     DASHBOARD SCOMAR
     File: resources/views/dashboard/index.blade.php

     Mendukung penuh Mode Cerah dan Mode Gelap dengan palet SCOMAR,
     kartu ringkasan interaktif, peta Leaflet.js dinamis dari database,
     serta integrasi CRUD Fishing Ground, Terumbu Karang, dan Alat Tangkap.
================================================================ --}}

@extends('layouts.app')

{{-- Judul tab browser --}}
@section('title', 'Dashboard')

{{-- Judul halaman di navbar --}}
@section('page-title', 'Dashboard')
@section('page-breadcrumb', 'SCOMAR / Dashboard')

@section('content')

<style>
    /* ── MODERN MARINE DASHBOARD THEME & ELEVATED SURFACES ─────────── */
    .dashboard-hero {
        background: var(--bg-surface);
        border-radius: 16px;
        padding: 26px 30px;
        margin-bottom: 24px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 4px 20px -2px rgba(36, 54, 75, 0.07), 0 2px 6px -1px rgba(36, 54, 75, 0.04);
        position: relative;
        overflow: hidden;
        transition: background var(--transition), border-color var(--transition), box-shadow var(--transition);
    }

    .dashboard-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: linear-gradient(90deg, #1B2E4B 0%, #0284C7 30%, #06B6D4 65%, #73343A 100%);
    }

    [data-theme="dark"] .dashboard-hero {
        background: #1B293A;
        border-color: rgba(165, 184, 208, 0.22);
        box-shadow: 0 10px 30px -4px rgba(0, 0, 0, 0.45), 0 4px 12px -2px rgba(0, 0, 0, 0.3);
    }

    [data-theme="dark"] .dashboard-hero::before {
        background: linear-gradient(90deg, #0EA5E9 0%, #06B6D4 40%, #A5B8D0 75%, #E07A82 100%);
    }

    .hero-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .hero-text-col {
        flex: 1 1 540px;
        min-width: 280px;
    }

    .hero-top-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(2, 132, 199, 0.08);
        border: 1px solid rgba(2, 132, 199, 0.22);
        color: #0369a1;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        padding: 4px 10px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    [data-theme="dark"] .hero-top-badge {
        background: rgba(14, 165, 233, 0.15);
        border-color: rgba(56, 189, 248, 0.3);
        color: #38bdf8;
    }

    .hero-main-title {
        font-size: 23px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.25;
        margin: 0 0 8px 0;
        letter-spacing: -0.3px;
    }

    .hero-main-desc {
        color: var(--text-secondary);
        font-size: 13.5px;
        line-height: 1.65;
        margin: 0;
        max-width: 720px;
    }

    .hero-main-desc strong {
        color: var(--text-primary);
    }

    /* Kartu status akun pengguna */
    .hero-access-box {
        background: rgba(165, 184, 208, 0.08);
        border: 1px solid var(--border-subtle);
        border-radius: 12px;
        padding: 14px 18px;
        min-width: 240px;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(36, 54, 75, 0.04);
        transition: border-color var(--transition), background var(--transition);
    }

    [data-theme="dark"] .hero-access-box {
        background: rgba(15, 23, 34, 0.5);
        border-color: rgba(165, 184, 208, 0.2);
    }

    .hab-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }

    .hab-label {
        font-size: 10.5px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .hab-status-dot {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: #059669;
    }

    .hab-status-dot::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10B981;
        box-shadow: 0 0 6px rgba(16, 185, 129, 0.6);
    }

    .hab-role {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14.5px;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .hab-role svg {
        color: var(--merlot);
    }

    [data-theme="dark"] .hab-role svg {
        color: #e07a82;
    }

    .hab-subtext {
        font-size: 11px;
        color: var(--text-secondary);
        line-height: 1.4;
        display: flex;
        flex-direction: column;
        gap: 2px;
        border-top: 1px dashed var(--border-subtle);
        padding-top: 6px;
        margin-top: 6px;
    }

    /* Grid 6 Modul Utama SCOMAR */
    .hero-features-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 12px;
        padding-top: 14px;
        border-top: 1px solid var(--border-subtle);
    }

    .hero-features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    @media (max-width: 992px) {
        .hero-features-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .hero-features-grid {
            grid-template-columns: 1fr;
        }
    }

    .hero-feat-card {
        background: rgba(165, 184, 208, 0.05);
        border: 1px solid var(--border-subtle);
        border-radius: 10px;
        padding: 12px 14px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .hero-feat-card:hover {
        transform: translateY(-2px);
        background: rgba(165, 184, 208, 0.1);
        border-color: var(--border-hover);
    }

    [data-theme="dark"] .hero-feat-card {
        background: rgba(15, 23, 34, 0.4);
        border-color: rgba(165, 184, 208, 0.16);
    }

    [data-theme="dark"] .hero-feat-card:hover {
        background: rgba(15, 23, 34, 0.7);
        border-color: rgba(165, 184, 208, 0.4);
    }

    .feat-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .icon-coral-theme {
        background: rgba(224, 109, 83, 0.12);
        color: #c2410c;
    }
    .icon-ground-theme {
        background: rgba(2, 132, 199, 0.12);
        color: #0284c7;
    }
    .icon-ocean-theme {
        background: rgba(6, 182, 212, 0.12);
        color: #0891b2;
    }
    .icon-catch-theme {
        background: rgba(115, 52, 58, 0.12);
        color: var(--merlot);
    }
    .icon-gear-theme {
        background: rgba(36, 54, 75, 0.1);
        color: var(--deep-navy);
    }
    .icon-ai-theme {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
    }

    [data-theme="dark"] .icon-coral-theme { background: rgba(244, 63, 94, 0.2); color: #fb7185; }
    [data-theme="dark"] .icon-ground-theme { background: rgba(56, 189, 248, 0.2); color: #38bdf8; }
    [data-theme="dark"] .icon-ocean-theme { background: rgba(6, 182, 212, 0.2); color: #22d3ee; }
    [data-theme="dark"] .icon-catch-theme { background: rgba(224, 122, 130, 0.25); color: #f87171; }
    [data-theme="dark"] .icon-gear-theme { background: rgba(165, 184, 208, 0.2); color: #cbd5e1; }
    [data-theme="dark"] .icon-ai-theme { background: rgba(52, 211, 153, 0.2); color: #34d399; }

    .feat-body {
        flex: 1;
        min-width: 0;
    }

    .feat-title {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.3;
        margin-bottom: 2px;
    }

    .feat-desc {
        font-size: 11px;
        color: var(--text-secondary);
        line-height: 1.45;
    }

    /* ── ELEVATED STAT CARDS ───────────────────────────────────────── */
    .status-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1100px) {
        .status-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 580px) {
        .status-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card-elevated {
        background: var(--bg-surface);
        border-radius: 14px;
        padding: 20px 22px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 4px 18px rgba(36, 54, 75, 0.05), 0 1px 3px rgba(36, 54, 75, 0.03);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
    }

    .stat-card-elevated::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--card-accent-gradient);
    }

    .stat-card-elevated:hover {
        transform: translateY(-3px);
        border-color: var(--border-hover);
        box-shadow: 0 12px 28px -4px rgba(36, 54, 75, 0.12), 0 4px 8px -2px rgba(36, 54, 75, 0.05);
    }

    [data-theme="dark"] .stat-card-elevated {
        background: #1B293A;
        border-color: rgba(165, 184, 208, 0.2);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4), 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    [data-theme="dark"] .stat-card-elevated:hover {
        border-color: rgba(165, 184, 208, 0.45);
        box-shadow: 0 14px 32px rgba(0, 0, 0, 0.55), 0 4px 12px rgba(0, 0, 0, 0.35);
    }

    /* Varian aksen kartu */
    .card-accent-catch  { --card-accent-gradient: linear-gradient(90deg, #1E40AF, #3B82F6); }
    .card-accent-temp   { --card-accent-gradient: linear-gradient(90deg, #73343A, #E06D53); }
    .card-accent-chla   { --card-accent-gradient: linear-gradient(90deg, #0891B2, #06B6D4); }
    .card-accent-grounds{ --card-accent-gradient: linear-gradient(90deg, #24364B, #0284C7); }

    [data-theme="dark"] .card-accent-catch  { --card-accent-gradient: linear-gradient(90deg, #3B82F6, #60A5FA); }
    [data-theme="dark"] .card-accent-temp   { --card-accent-gradient: linear-gradient(90deg, #E07A82, #F87171); }
    [data-theme="dark"] .card-accent-chla   { --card-accent-gradient: linear-gradient(90deg, #06B6D4, #38BDF8); }
    [data-theme="dark"] .card-accent-grounds{ --card-accent-gradient: linear-gradient(90deg, #38BDF8, #818CF8); }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .stat-header-text {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .stat-icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-main-figure {
        display: flex;
        align-items: baseline;
        gap: 4px;
        font-size: 23px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.15;
        margin-bottom: 4px;
    }

    .stat-unit-text {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
    }

    .stat-context-desc {
        font-size: 11.5px;
        color: var(--text-secondary);
        margin-bottom: 12px;
    }

    .stat-bottom-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        background: rgba(165, 184, 208, 0.12);
        border: 1px solid rgba(165, 184, 208, 0.22);
        padding: 3px 9px;
        border-radius: 6px;
        width: fit-content;
    }

    [data-theme="dark"] .stat-bottom-pill {
        background: rgba(15, 23, 34, 0.45);
        border-color: rgba(165, 184, 208, 0.2);
    }

    .pill-indicator-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .dot-catch  { background: #2563EB; }
    .dot-temp   { background: #E06D53; }
    .dot-chla   { background: #0891B2; }
    .dot-grounds{ background: #059669; }

    /* ── PETA INTERAKTIF LEAFLET WEBGIS ──────────────────────────── */
    .map-placeholder {
        background: var(--bg-surface);
        border-radius: 16px;
        padding: 24px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 4px 20px -2px rgba(36, 54, 75, 0.06), 0 2px 6px -1px rgba(36, 54, 75, 0.03);
        margin-bottom: 26px;
        transition: background var(--transition), border-color var(--transition), box-shadow var(--transition);
    }

    [data-theme="dark"] .map-placeholder {
        background: #1B293A;
        border-color: rgba(165, 184, 208, 0.2);
        box-shadow: 0 10px 28px -4px rgba(0, 0, 0, 0.45);
    }

    .map-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .map-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .map-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(2, 132, 199, 0.1);
        color: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    [data-theme="dark"] .map-icon-box {
        background: rgba(56, 189, 248, 0.18);
        color: #38bdf8;
    }

    .map-header h3 {
        font-size: 16.5px;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0;
    }

    .map-subtitle {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 2px;
    }

    .map-header-badges {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .map-badge-item {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--text-secondary);
        background: rgba(165, 184, 208, 0.12);
        border: 1px solid var(--border-subtle);
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    [data-theme="dark"] .map-badge-item {
        background: rgba(15, 23, 34, 0.5);
    }

    #scomarMap {
        height: 560px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid var(--border-subtle);
        z-index: 10;
        box-shadow: inset 0 0 0 1px rgba(165, 184, 208, 0.15);
    }

    /* ── STYLING KONTROL PETA & LEGENDA SCOMAR ── */
    .scomar-control-panel {
        background: #FFFFFF;
        border: 1px solid rgba(165, 184, 208, 0.35);
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(36, 54, 75, 0.12);
        font-family: inherit;
        color: #24364B;
        transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease;
    }

    [data-theme="dark"] .scomar-control-panel {
        background: #1B293A !important;
        border-color: rgba(165, 184, 208, 0.22) !important;
        box-shadow: 0 6px 22px rgba(0, 0, 0, 0.45) !important;
        color: #F8FAFC !important;
    }

    [data-theme="dark"] .scomar-control-panel select {
        background: #141F2B !important;
        color: #F8FAFC !important;
        border-color: rgba(165, 184, 208, 0.25) !important;
    }

    .map-wrapper-box {
        position: relative;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
    }

    .map-loading-indicator {
        position: absolute;
        top: 14px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        background: rgba(27, 46, 75, 0.92);
        backdrop-filter: blur(8px);
        color: #FFFFFF;
        padding: 8px 18px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.2);
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .map-spinner {
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #38BDF8;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Modern Map Filter Control */
    .zppi-map-filter-control {
        background: var(--bg-surface);
        padding: 10px 14px;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
        border: 1px solid var(--border-subtle);
        min-width: 220px;
        font-family: inherit;
    }

    [data-theme="dark"] .zppi-map-filter-control {
        background: #1B293A;
        border-color: rgba(165, 184, 208, 0.25);
    }

    .zmfc-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-primary);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .zmfc-select-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .zmfc-select {
        width: 100%;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid var(--border-subtle);
        background: var(--bg-body);
        color: var(--text-primary);
        font-size: 12px;
        font-weight: 600;
        outline: none;
        cursor: pointer;
    }

    [data-theme="dark"] .zmfc-select {
        background: #141F2B;
        border-color: rgba(165, 184, 208, 0.2);
        color: #F8FAFC;
    }

    .zmfc-select:focus {
        border-color: #0284c7;
    }

    /* Dynamic Map Legend */
    .scomar-map-legend {
        background: var(--bg-surface);
        padding: 12px 16px;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
        border: 1px solid var(--border-subtle);
        font-family: inherit;
        font-size: 11.5px;
        color: var(--text-primary);
        line-height: 1.5;
        min-width: 210px;
    }

    [data-theme="dark"] .scomar-map-legend {
        background: #1B293A;
        border-color: rgba(165, 184, 208, 0.25);
        color: #F8FAFC;
    }

    .sml-header {
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-muted);
        margin-bottom: 8px;
        border-bottom: 1px solid var(--border-subtle);
        padding-bottom: 4px;
    }

    .sml-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
    }

    .sml-icon-wrapper {
        width: 22px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-shrink: 0;
    }

    .sml-dot-coral {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #A5B8D0;
        border: 2px solid #FFFFFF;
        box-shadow: 0 1px 4px rgba(0,0,0,0.25);
    }

    .sml-dot-fg {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #73343A;
        border: 2px solid #FFFFFF;
        box-shadow: 0 1px 4px rgba(0,0,0,0.25);
    }

    .sml-text-main {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 11.5px;
        line-height: 1.2;
    }

    .sml-text-sub {
        font-size: 10px;
        color: var(--text-muted);
    }

    .sml-source {
        font-size: 9.5px;
        color: var(--text-muted);
        border-top: 1px dashed var(--border-subtle);
        padding-top: 6px;
        margin-top: 6px;
        line-height: 1.35;
    }

    /* Pop-up Marker Modern */
    .leaflet-popup-content-wrapper {
        background: var(--bg-surface) !important;
        color: var(--text-primary) !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.25) !important;
        border: 1px solid var(--border-subtle) !important;
        padding: 0 !important;
    }

    [data-theme="dark"] .leaflet-popup-content-wrapper {
        background: #1B293A !important;
        border-color: rgba(165, 184, 208, 0.25) !important;
        color: #F8FAFC !important;
    }

    .leaflet-popup-tip {
        background: var(--bg-surface) !important;
    }

    [data-theme="dark"] .leaflet-popup-tip {
        background: #1B293A !important;
    }

    .leaflet-popup-content {
        margin: 14px 16px !important;
        line-height: 1.5 !important;
    }

    .scomar-map-popup {
        font-family: inherit;
        font-size: 12px;
    }

    .smp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
        border-bottom: 1px solid var(--border-subtle);
        padding-bottom: 6px;
    }

    .smp-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(2, 132, 199, 0.12);
        color: #0284c7;
        padding: 2px 7px;
        border-radius: 4px;
    }

    [data-theme="dark"] .smp-badge {
        background: rgba(56, 189, 248, 0.2);
        color: #38bdf8;
    }

    .smp-title {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--text-primary);
    }

    .smp-body {
        display: flex;
        flex-direction: column;
        gap: 4px;
        color: var(--text-secondary);
        font-size: 11.5px;
    }

    .smp-footer {
        margin-top: 8px;
        padding-top: 6px;
        border-top: 1px dashed var(--border-subtle);
        font-size: 10px;
        color: var(--text-muted);
    }

    /* ZPPI Custom Marker hover enhancement */
    .zppi-custom-marker {
        transition: transform 0.2s ease, filter 0.2s ease;
        filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.3));
    }

    .zppi-custom-marker:hover {
        transform: scale(1.15) translateY(-4px);
        z-index: 10000 !important;
        filter: drop-shadow(0 6px 12px rgba(8, 145, 178, 0.5));
    }

    /* DMS Coordinate Badge */
    .dms-badge {
        font-family: monospace;
        font-size: 11px;
        background: rgba(165, 184, 208, 0.14);
        padding: 2px 6px;
        border-radius: 4px;
        color: var(--text-secondary);
        display: inline-block;
    }

    /* ============================================================
       SCOMAR ZPPI & FISHING GEAR RECOMMENDATION POPUP
    ============================================================ */
    .scomar-zppi-rec-popup .leaflet-popup-content {
        margin: 10px 12px !important;
        max-width: 370px !important;
    }
    .scomar-zppi-popup {
        font-family: inherit;
        font-size: 11.5px;
        line-height: 1.45;
        color: var(--text-primary);
        max-width: 360px;
        max-height: 440px;
        overflow-y: auto;
        padding-right: 3px;
        box-sizing: border-box;
    }
    .scomar-zppi-popup::-webkit-scrollbar {
        width: 4px;
    }
    .scomar-zppi-popup::-webkit-scrollbar-track {
        background: rgba(165, 184, 208, 0.1);
        border-radius: 4px;
    }
    .scomar-zppi-popup::-webkit-scrollbar-thumb {
        background: rgba(165, 184, 208, 0.4);
        border-radius: 4px;
    }
    .szp-header {
        border-bottom: 1px solid var(--border-subtle);
        padding-bottom: 7px;
        margin-bottom: 7px;
    }
    .szp-badge-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        margin-bottom: 4px;
    }
    .szp-tag-zppi {
        background: #0284C7;
        color: #ffffff;
        font-size: 10.5px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 4px;
        letter-spacing: 0.3px;
    }
    .szp-tag-season {
        font-size: 11px;
        color: var(--text-secondary);
        font-weight: 600;
    }
    .szp-coords-box {
        background: rgba(165, 184, 208, 0.08);
        border: 1px solid var(--border-subtle);
        border-radius: 6px;
        padding: 5px 8px;
        margin-top: 5px;
    }
    .szp-coords-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        font-family: monospace;
        font-size: 11px;
    }
    .szp-spatial-box {
        border-radius: 8px;
        border: 1px solid;
        padding: 8px 10px;
        margin: 8px 0;
    }
    .szp-spatial-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 5px;
        letter-spacing: 0.3px;
    }
    .szp-stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
        margin-bottom: 2px;
    }
    .szp-dist-val {
        font-weight: 800;
        font-size: 12px;
    }
    .szp-gears-section {
        margin-top: 8px;
    }
    .szp-gears-header {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-primary);
        letter-spacing: 0.4px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .szp-toggle-btn {
        font-size: 10px;
        color: #0284c7;
        background: transparent;
        border: 1px solid rgba(2, 132, 199, 0.3);
        border-radius: 4px;
        padding: 1px 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.15s ease;
    }
    .szp-toggle-btn:hover {
        background: rgba(2, 132, 199, 0.08);
    }
    .szp-tab-bar {
        display: flex;
        gap: 3px;
        background: rgba(165, 184, 208, 0.12);
        border-radius: 6px;
        padding: 2px;
        margin-bottom: 7px;
    }
    .szp-tab-btn {
        flex: 1;
        font-size: 10.5px;
        font-weight: 600;
        padding: 5px 3px;
        border: none;
        background: transparent;
        border-radius: 4px;
        color: var(--text-secondary);
        cursor: pointer;
        text-align: center;
        transition: all 0.15s ease;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .szp-tab-btn.active {
        background: #ffffff;
        color: #0284c7;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    [data-theme="dark"] .szp-tab-btn.active {
        background: #1B293A;
        color: #38bdf8;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    .szp-gear-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 7px;
        padding: 8px 9px;
        margin-bottom: 6px;
    }
    .szp-gear-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
        gap: 4px;
    }
    .szp-gear-name {
        font-weight: 800;
        font-size: 11.5px;
        color: var(--text-primary);
    }
    .szp-badge-rec {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
        font-size: 9.5px;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 4px;
        white-space: nowrap;
    }
    .szp-badge-cond {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
        font-size: 9.5px;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 4px;
        white-space: nowrap;
    }
    .szp-spec-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        font-size: 10px;
        margin-bottom: 5px;
        background: rgba(165, 184, 208, 0.06);
        padding: 4px 6px;
        border-radius: 5px;
    }
    .szp-spec-item {
        display: flex;
        flex-direction: column;
    }
    .szp-spec-label {
        color: var(--text-muted);
        font-size: 9.5px;
    }
    .szp-spec-val {
        font-weight: 700;
        color: var(--text-primary);
    }
    .szp-detail-block {
        margin-bottom: 4px;
    }
    .szp-detail-label {
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }
    .szp-detail-text {
        font-size: 10.5px;
        line-height: 1.4;
        color: var(--text-secondary);
    }
    .szp-gear-legal-badge {
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 9.5px;
        font-weight: 700;
        color: #d97706;
        background: rgba(245, 158, 11, 0.08);
        border: 1px dashed rgba(245, 158, 11, 0.3);
        border-radius: 4px;
        padding: 3px 6px;
    }
    [data-theme="dark"] .szp-gear-legal-badge {
        color: #fbbf24;
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(245, 158, 11, 0.3);
    }
    .szp-reg-alert {
        background: rgba(239, 68, 68, 0.08);
        border: 1px dashed rgba(239, 68, 68, 0.35);
        border-radius: 6px;
        padding: 6px 8px;
        margin-top: 7px;
        font-size: 10px;
        line-height: 1.35;
        color: #b91c1c;
    }
    [data-theme="dark"] .szp-reg-alert {
        background: rgba(239, 68, 68, 0.12);
        color: #f87171;
        border-color: rgba(239, 68, 68, 0.3);
    }

    [data-theme="dark"] .dms-badge {
        background: rgba(15, 23, 34, 0.45);
        color: #cbd5e1;
    }

    .sub-toggle-box {
        display: inline-flex;
        background: rgba(165, 184, 208, 0.12);
        border: 1px solid var(--border-subtle);
        border-radius: 8px;
        padding: 3px;
        gap: 4px;
    }

    .sub-toggle-btn {
        background: none;
        border: none;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .sub-toggle-btn.active {
        background: var(--bg-surface);
        color: var(--text-primary);
        font-weight: 700;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }

    [data-theme="dark"] .sub-toggle-btn.active {
        background: #1B293A;
        color: #F8FAFC;
    }

    /* ── TABS KELOLA DATA PEMETAAN & EKOSISTEM ─────────────────────── */
    .crud-section-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 4px 20px -2px rgba(36, 54, 75, 0.06), 0 2px 6px -1px rgba(36, 54, 75, 0.03);
        padding: 24px;
        margin-bottom: 30px;
        transition: background var(--transition), border-color var(--transition);
    }

    [data-theme="dark"] .crud-section-card {
        background: #1B293A;
        border-color: rgba(165, 184, 208, 0.2);
    }

    .crud-nav-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 2px solid var(--border-subtle);
        margin-bottom: 20px;
        overflow-x: auto;
    }

    .crud-tab-btn {
        background: none;
        border: none;
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .crud-tab-btn:hover {
        color: var(--merlot);
    }

    .crud-tab-btn.active {
        color: var(--merlot);
        border-bottom-color: var(--merlot);
        font-weight: 700;
    }

    [data-theme="dark"] .crud-tab-btn.active {
        color: var(--cashmere-blue);
        border-bottom-color: var(--cashmere-blue);
    }

    .tab-badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 12px;
        background: rgba(165, 184, 208, 0.2);
        color: var(--text-primary);
        font-weight: 700;
    }

    .crud-tab-btn.active .tab-badge {
        background: var(--merlot);
        color: #FFFFFF;
    }

    [data-theme="dark"] .crud-tab-btn.active .tab-badge {
        background: var(--cashmere-blue);
        color: var(--deep-navy);
    }

    /* ================================================================
       RESPONSIVE DASHBOARD SCOMAR
    ================================================================ */

    /* Peta responsif */
    @media (max-width: 1024px) {
        #scomarMap {
            height: 480px;
        }
    }

    @media (max-width: 768px) {
        #scomarMap {
            height: 400px;
        }
        .crud-section-card {
            padding: 18px 16px;
        }
        .crud-tab-btn {
            padding: 9px 12px;
            font-size: 12.5px;
        }
    }

    @media (max-width: 640px) {
        #scomarMap {
            height: 360px;
            border-radius: 10px;
        }
        .scomar-zppi-rec-popup .leaflet-popup-content {
            max-width: calc(100vw - 40px) !important;
        }
        .scomar-zppi-popup {
            max-width: calc(100vw - 40px);
            max-height: 360px;
            font-size: 11px;
        }
        .szp-coords-grid {
            grid-template-columns: 1fr;
        }
        .szp-spec-grid {
            grid-template-columns: 1fr;
        }
        .crud-section-card {
            padding: 14px 12px;
        }
        .crud-nav-tabs {
            gap: 4px;
        }
        .crud-tab-btn {
            padding: 8px 10px;
            font-size: 12px;
        }
        .tab-badge {
            font-size: 10px;
            padding: 1px 6px;
        }
    }

    @media (max-width: 480px) {
        #scomarMap {
            height: 320px;
        }
        .scomar-control-panel {
            font-size: 10.5px !important;
        }
        .scomar-map-legend {
            font-size: 10.5px !important;
            min-width: 150px !important;
            padding: 8px 10px !important;
        }
        .crud-nav-tabs {
            overflow-x: auto;
            white-space: nowrap;
        }
        .crud-tab-btn span:first-child {
            font-size: 11.5px;
        }
        .sub-toggle-btn {
            padding: 4px 8px;
            font-size: 11px;
        }
    }

    @media (max-width: 380px) {
        #scomarMap {
            height: 290px;
        }
        .scomar-zppi-popup {
            max-height: 310px;
        }
    }

    /* Pastikan popup Leaflet tidak overflow pada mobile */
    .leaflet-popup-content-wrapper {
        max-width: calc(100vw - 32px) !important;
    }

    .leaflet-popup-content {
        max-width: calc(100vw - 64px) !important;
    }

    /* Wrapper tabel responsif pada section CRUD */
    .scomar-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
    }

    @media (max-width: 640px) {
        .scomar-table-wrap table {
            min-width: 580px;
        }
        .btn-action-icon {
            width: 28px;
            height: 28px;
        }
    }
</style>

{{-- ── 1. HERO PENGENALAN SCOMAR & STATUS AKSES AKUN ────────────── --}}
<div class="dashboard-hero">
    <div class="hero-top-row">
        <div class="hero-text-col">
            <div class="hero-top-badge">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="m4.93 4.93 4.24 4.24"/>
                    <path d="m14.83 9.17 4.24-4.24"/>
                    <path d="m14.83 14.83 4.24 4.24"/>
                    <path d="m9.17 14.83-4.24 4.24"/>
                    <circle cx="12" cy="12" r="4"/>
                </svg>
                <span>Sistem Informasi Perikanan &amp; Oseanografi Terpadu</span>
            </div>
            <h1 class="hero-main-title">Selamat Datang di SCOMAR, {{ auth()->user()->name }}</h1>
            <p class="hero-main-desc">
                <strong>SCOMAR (Scomberomorus Marine)</strong> merupakan Sistem Cerdas dalam Mendukung Pemantauan Ekosistem Laut dan Informasi Perikanan di Pulau Tunda, Banten. Platform WebGIS terintegrasi untuk mendukung observasi dinamika oseanografi, sebaran daerah penangkapan ikan, serta keberlanjutan perikanan tangkap.
            </p>
        </div>

        {{-- Kartu Status Akses Akun Pengguna --}}
        <div class="hero-access-box">
            <div class="hab-header">
                <span class="hab-label">Status Akses Akun</span>
                <span class="hab-status-dot">Online</span>
            </div>
            <div class="hab-role">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span>
                    @if(auth()->user()->isMasterAdmin())
                        Master Admin
                    @elseif(auth()->user()->isAdmin())
                        Admin Lapangan
                    @else
                        Pengguna / Nelayan
                    @endif
                </span>
            </div>
            <div class="hab-subtext">
                <span>Wilayah: Perairan Pulau Tunda</span>
                <span style="font-family: monospace; font-size: 10px; color: var(--text-muted);">5°48'29" LS • 106°16'17" BT</span>
            </div>
        </div>
    </div>

    {{-- Grid 6 Pilar / Modul Informasi Utama SCOMAR --}}
    <div class="hero-features-title">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="12 2 2 7 12 12 22 7 12 2"/>
            <polyline points="2 17 12 22 22 17"/>
            <polyline points="2 12 12 17 22 12"/>
        </svg>
        <span>Modul &amp; Kapabilitas Utama Sistem SCOMAR</span>
    </div>

    <div class="hero-features-grid">
        {{-- Modul 1: Terumbu Karang --}}
        <div class="hero-feat-card">
            <div class="feat-icon-box icon-coral-theme">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="feat-body">
                <div class="feat-title">Ekosistem Terumbu Karang</div>
                <div class="feat-desc">Pemetaan sebaran zona konservasi dan tutupan ekosistem terumbu karang.</div>
            </div>
        </div>

        {{-- Modul 2: Daerah Penangkapan Ikan --}}
        <div class="hero-feat-card">
            <div class="feat-icon-box icon-ground-theme">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                    <path d="M2 12h20"/>
                </svg>
            </div>
            <div class="feat-body">
                <div class="feat-title">Zona Potensial Penangkapan Ikan (ZPPI)</div>
                <div class="feat-desc">Pemetaan daerah penangkapan potensial dan sebaran spasial ZPPI.</div>
            </div>
        </div>

        {{-- Modul 3: Parameter Oseanografi --}}
        <div class="hero-feat-card">
            <div class="feat-icon-box icon-ocean-theme">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                    <path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                    <path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                </svg>
            </div>
            <div class="feat-body">
                <div class="feat-title">Informasi Parameter Oseanografi</div>
                <div class="feat-desc">Monitoring Suhu Permukaan Laut (SPL), Klorofil-a, TSS, dan Kecepatan Arus.</div>
            </div>
        </div>

        {{-- Modul 4: Hasil Tangkapan & CPUE --}}
        <div class="hero-feat-card">
            <div class="feat-icon-box icon-catch-theme">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6"  y1="20" x2="6"  y2="14"/>
                    <line x1="2"  y1="20" x2="22" y2="20"/>
                </svg>
            </div>
            <div class="feat-body">
                <div class="feat-title">Hasil Tangkapan &amp; CPUE</div>
                <div class="feat-desc">Data produksi ikan tenggiri dan analisis efisiensi Catch Per Unit Effort.</div>
            </div>
        </div>

        {{-- Modul 5: Alat Tangkap Ramah Lingkungan --}}
        <div class="hero-feat-card">
            <div class="feat-icon-box icon-gear-theme">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="5" r="3"/>
                    <line x1="12" y1="22" x2="12" y2="8"/>
                    <path d="M5 12H2a10 10 0 0 0 20 0h-3"/>
                </svg>
            </div>
            <div class="feat-body">
                <div class="feat-title">Alat Tangkap Ramah Lingkungan</div>
                <div class="feat-desc">Rekomendasi alat tangkap selektif berkelanjutan bagi nelayan perairan Pulau Tunda.</div>
            </div>
        </div>

        {{-- Modul 6: Pemindai Kesegaran Ikan AI --}}
        <div class="hero-feat-card">
            <div class="feat-icon-box icon-ai-theme">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
            </div>
            <div class="feat-body">
                <div class="feat-title">Pemindai Kesegaran Ikan AI</div>
                <div class="feat-desc">Deteksi klasifikasi jenis dan tingkat kesegaran ikan berbasis kecerdasan buatan.</div>
            </div>
        </div>
    </div>
</div>

{{-- ── 2. ELEVATED STAT CARDS (REAL DATABASE SCOMAR) ────────────── --}}
<div class="status-grid">

    {{-- Kartu 1: Total Hasil Tangkapan --}}
    <div class="stat-card-elevated card-accent-catch">
        <div>
            <div class="stat-header">
                <span class="stat-header-text">Total Hasil Tangkapan</span>
                <div class="stat-icon-wrapper icon-catch-theme">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6"  y1="20" x2="6"  y2="14"/>
                        <line x1="2"  y1="20" x2="22" y2="20"/>
                    </svg>
                </div>
            </div>
            <div class="stat-main-figure">
                  <span id="statCatchValue">{{ isset($catchByPeriod['2021']['total']) ? number_format($catchByPeriod['2021']['total'], 1, ',', '.') : '30.844,0' }}</span>
                  <span class="stat-unit-text" id="statCatchUnit">kg</span>
              </div>
              <div class="stat-context-desc" id="statCatchDesc">Total Produksi Scomberomorus (2021)</div>
          </div>
          <div class="stat-bottom-pill">
              <span class="pill-indicator-dot dot-catch"></span>
              <span id="statCatchPill">Data Tangkapan 2021</span>
          </div>
    </div>

    {{-- Kartu 2: Suhu Permukaan Laut (SPL) --}}
    <div class="stat-card-elevated card-accent-temp">
        <div>
            <div class="stat-header">
                <span class="stat-header-text">Suhu Permukaan (SPL)</span>
                <div class="stat-icon-wrapper icon-coral-theme">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-main-figure">
                <span id="statTempValue">{{ number_format($oceanByYear['2021']['temp'] ?? 30.08, 2) }}</span>
                <span class="stat-unit-text" id="statTempUnit">°C</span>
            </div>
            <div class="stat-context-desc" id="statTempDesc">Rata-rata Pengukuran (2021)</div>
        </div>
        <div class="stat-bottom-pill">
            <span class="pill-indicator-dot dot-temp"></span>
            <span id="statTempPill">Satelit Aqua MODIS: {{ $oceanByYear['2021']['status'] ?? 'Normal / Subur' }}</span>
        </div>
    </div>

    {{-- Kartu 3: Konsentrasi Klorofil-a (Netral, Baku Mutu Biota Laut) --}}
    <div class="stat-card-elevated card-accent-chla">
        <div>
            <div class="stat-header">
                <span class="stat-header-text">Konsentrasi Klorofil-a</span>
                <div class="stat-icon-wrapper icon-ocean-theme">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 2a15 15 0 0 0 0 20"/>
                        <path d="M2 12a15 15 0 0 0 20 0"/>
                    </svg>
                </div>
            </div>
            <div class="stat-main-figure">
                <span id="statChlaValue">{{ number_format($oceanByYear['2021']['chlorophyll'] ?? 0.62, 2) }}</span>
                <span class="stat-unit-text" id="statChlaUnit">mg/m³</span>
            </div>
            <div class="stat-context-desc" id="statChlaDesc">Satelit Aqua MODIS (2021)</div>
        </div>
        <div class="stat-bottom-pill">
            <span class="pill-indicator-dot dot-chla"></span>
            <span id="statChlaPill">Rentang Acuan: 0.10 – 5.00 mg/m³</span>
        </div>
    </div>

    {{-- Kartu 4: Lokasi ZPPI --}}
    <div class="stat-card-elevated card-accent-grounds">
        <div>
            <div class="stat-header">
                <span class="stat-header-text">Lokasi ZPPI</span>
                <div class="stat-icon-wrapper icon-ground-theme">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
            </div>
            <div class="stat-main-figure">
                <span id="statZppiValue">90</span>
                <span class="stat-unit-text" id="statZppiUnit">Titik</span>
            </div>
            <div class="stat-context-desc" id="statZppiDesc">Zona Tangkap Terpetakan (2021 • Tahunan)</div>
        </div>
        <div class="stat-bottom-pill">
            <span class="pill-indicator-dot dot-grounds"></span>
            <span id="statZppiPill">90 Titik Aktif di WebGIS</span>
        </div>
    </div>

</div>

{{-- ── 3. PETA INTERAKTIF LEAFLET.JS WEBGIS ──────────────────────── --}}
<div class="map-placeholder">
    <div class="map-header">
        <div class="map-header-left">
            <div class="map-icon-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
                    <line x1="8" y1="2" x2="8" y2="18"/>
                    <line x1="16" y1="6" x2="16" y2="22"/>
                </svg>
            </div>
            <div>
                <h3>Peta Interaktif WebGIS Pulau Tunda</h3>
                <div class="map-subtitle">
                    Visualisasi spasial sebaran Fishing Ground, ekosistem Terumbu Karang, dan Zona Potensi Penangkapan Ikan (ZPPI)
                </div>
            </div>
        </div>
        <div class="map-header-badges">
            <span id="mapCenterCoords" class="map-badge-item">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Koordinat Pusat: 5°48'29" LS, 106°16'17" BT
            </span>
            <span class="map-badge-item">
                Sistem Geodesi: WGS 84
            </span>
        </div>
    </div>
    {{-- Container peta WebGIS dengan Loading Indicator --}}
    <div class="map-wrapper-box">
        <div id="mapLoadingOverlay" class="map-loading-indicator" style="display:none;">
            <div class="map-spinner"></div>
            <span id="mapLoadingText">Memuat layer spasial ZPPI...</span>
        </div>
        <div id="scomarMap"></div>
    </div>
</div>

{{-- ── 4. INTEGRASI CRUD: FISHING GROUND, TERUMBU KARANG, ALAT TANGKAP ── --}}
<div
    class="crud-section-card"
    id="fishing-ecosystem-section"
    x-data="{
        activeTab: 'zppi',
        // State ZPPI
        zppiPoints: [],
        zppiCount: 0,
        zppiYear: '2021',
        zppiSeason: 'Tahunan',
        searchZppi: '',
        searchReefs: '',
        searchGears: '',
        searchCatches: '',
        searchOcean: '',
        searchUsers: '',
        searchGrounds: '',
        searchDbReefs: '',

        // Subtabs
        zppiSubTab: 'geojson',
        reefSubTab: 'lyzenga',
        lyzengaYear: '2021',
        lyzengaClasses: [],
        lyzengaCount: 5,
        searchLyzenga: '',

        // State Modals Coral Reef
        showCreateReef: false,
        showEditReef: false,
        showDeleteReef: false,
        showViewReef: false,
        reefItem: { id: '', name: '', latitude: '', longitude: '', condition_status: 'Baik', coverage_percent: '', friendly_gear: '', description: '' },

        // State Modals Fishing Gear
        showCreateGear: false,
        showEditGear: false,
        showDeleteGear: false,
        showViewGear: false,
        gearItem: { id: '', name: '', category: 'Ramah Lingkungan', target_species: '', selectivity_level: 'Tinggi', status: 'Direkomendasikan', description: '' },

        // State Modals Fishing Ground
        showCreateGround: false,
        showEditGround: false,
        showDeleteGround: false,
        showViewGround: false,
        groundItem: { id: '', name: '', latitude: '', longitude: '', coordinates_label: '', depth_meters: '', recommended_gear: '', status: 'Aktif', description: '' },

        // State Modals Catches
        showCreateCatch: false,
        showEditCatch: false,
        showDeleteCatch: false,
        showViewCatch: false,
        catchItem: { id: '', year: '2024', season: 'Musim Barat', period: 'Januari - Maret 2024', total_catch: '', total_count: '', trips: '1', cpue: '', notes: '' },

        // State Modals Oceanographic
        showCreateOcean: false,
        showEditOcean: false,
        showDeleteOcean: false,
        showViewOcean: false,
        oceanItem: { id: '', year: '2024', temp: '29.5', chlorophyll: '0.85', tss: '12.0', current: '0.45', status: 'Normal / Subur', habitat_score: 'Optimal', source: 'Satelit Aqua MODIS', notes: '' },

        // State Modals Users (Master Admin)
        showCreateUser: false,
        showEditUser: false,
        showDeleteUser: false,
        userItem: { id: '', name: '', email: '', role: 'admin', password: '' },

        init() {
            window.addEventListener('zppi-data-loaded', (e) => {
                this.zppiPoints = e.detail.points || [];
                this.zppiCount = e.detail.count || 0;
                this.zppiYear = e.detail.year || '2021';
                this.zppiSeason = e.detail.season || 'Tahunan';
            });
            window.addEventListener('lyzenga-data-loaded', (e) => {
                this.lyzengaYear = e.detail.year || '2021';
                this.lyzengaClasses = e.detail.classes || [];
                this.lyzengaCount = this.lyzengaClasses.length;
            });
        },

        filteredZppi() {
            if (!this.searchZppi) return this.zppiPoints;
            const q = this.searchZppi.toLowerCase();
            return this.zppiPoints.filter(p => 
                String(p.id).includes(q) ||
                String(p.lat).includes(q) ||
                String(p.lng).includes(q) ||
                (p.dmsLat && p.dmsLat.toLowerCase().includes(q)) ||
                (p.dmsLng && p.dmsLng.toLowerCase().includes(q)) ||
                (p.contour && String(p.contour).toLowerCase().includes(q)) ||
                (p.season && String(p.season).toLowerCase().includes(q)) ||
                (p.year && String(p.year).toLowerCase().includes(q))
            );
        },

        filteredLyzenga() {
            if (!this.searchLyzenga) return this.lyzengaClasses;
            const q = this.searchLyzenga.toLowerCase();
            return this.lyzengaClasses.filter(c =>
                String(c.gridcode).includes(q) ||
                (c.label && c.label.toLowerCase().includes(q)) ||
                (c.areaHaFormatted && c.areaHaFormatted.toLowerCase().includes(q)) ||
                (c.areaM2Formatted && c.areaM2Formatted.toLowerCase().includes(q))
            );
        },

        changeLyzengaYear(y) {
            this.lyzengaYear = y;
            if (window.switchLyzengaYear) {
                window.switchLyzengaYear(y);
            }
        },

        // Helper Modal Openers
        openViewReef(r) { this.reefItem = { ...r }; this.showViewReef = true; },
        openEditReef(r) { this.reefItem = { ...r }; this.showEditReef = true; },
        openDeleteReef(r) { this.reefItem = { ...r }; this.showDeleteReef = true; },

        openViewGear(gr) { this.gearItem = { ...gr }; this.showViewGear = true; },
        openEditGear(gr) { this.gearItem = { ...gr }; this.showEditGear = true; },
        openDeleteGear(gr) { this.gearItem = { ...gr }; this.showDeleteGear = true; },

        openViewGround(g) { this.groundItem = { ...g }; this.showViewGround = true; },
        openEditGround(g) { this.groundItem = { ...g }; this.showEditGround = true; },
        openDeleteGround(g) { this.groundItem = { ...g }; this.showDeleteGround = true; },

        openViewCatch(c) { this.catchItem = { ...c }; this.showViewCatch = true; },
        openEditCatch(c) { this.catchItem = { ...c }; this.showEditCatch = true; },
        openDeleteCatch(c) { this.catchItem = { ...c }; this.showDeleteCatch = true; },

        openViewOcean(o) { this.oceanItem = { ...o }; this.showViewOcean = true; },
        openEditOcean(o) { this.oceanItem = { ...o }; this.showEditOcean = true; },
        openDeleteOcean(o) { this.oceanItem = { ...o }; this.showDeleteOcean = true; },

        openCreateUser() { this.userItem = { id: '', name: '', email: '', role: 'admin', password: '' }; this.showCreateUser = true; },
        openEditUser(u) { this.userItem = { ...u, password: '' }; this.showEditUser = true; },
        openDeleteUser(u) { this.userItem = { ...u }; this.showDeleteUser = true; }
    }"
>
    {{-- Header Section CRUD --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:14px;">
        <div>
            <h2 style="font-size:18px; font-weight:800; color:var(--text-primary); margin:0 0 4px 0;">
                Data Pemetaan &amp; Sumberdaya Perairan Pulau Tunda
            </h2>
            <p style="font-size:13px; color:var(--text-secondary); margin:0;">
                Penyelarasan spasial data ZPPI GeoJSON, zona terumbu karang klasifikasi Lyzenga (2021–2026), alat tangkap, serta integrasi manajemen data sistem SCOMAR.
            </p>
        </div>
        @if(auth()->user()->isAdmin())
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="background:rgba(23,107,135,0.12); color:#176B87; font-size:12px; font-weight:700; padding:5px 12px; border-radius:20px; border:1px solid rgba(23,107,135,0.25); display:inline-flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Akses CRUD: {{ auth()->user()->isMasterAdmin() ? 'Master Admin' : 'Admin' }} Aktif
                </span>
            </div>
        @endif
    </div>

    {{-- Tab Navigation Utama --}}
    <div class="crud-nav-tabs">
        <button
            type="button"
            class="crud-tab-btn"
            :class="{ 'active': activeTab === 'zppi' }"
            @click="activeTab = 'zppi'"
        >
            <span>Zona Potensi Penangkapan Ikan (ZPPI)</span>
            <span class="tab-badge" x-text="zppiCount ? zppiCount + ' Titik' : '90 Titik'">90 Titik</span>
        </button>

        <button
            type="button"
            class="crud-tab-btn"
            :class="{ 'active': activeTab === 'reefs' }"
            @click="activeTab = 'reefs'"
        >
            <span>Terumbu Karang (Ekosistem)</span>
            <span class="tab-badge">{{ isset($coralReefs) ? $coralReefs->count() : 0 }} Data</span>
        </button>

        <button
            type="button"
            class="crud-tab-btn"
            :class="{ 'active': activeTab === 'gears' }"
            @click="activeTab = 'gears'"
        >
            <span>Alat Tangkap Rekomendasi</span>
            <span class="tab-badge">{{ isset($fishingGears) ? $fishingGears->count() : 0 }}</span>
        </button>

        <button
            type="button"
            class="crud-tab-btn"
            :class="{ 'active': activeTab === 'catches' }"
            @click="activeTab = 'catches'"
        >
            <span>Hasil Tangkapan &amp; CPUE</span>
            <span class="tab-badge">{{ isset($catchesList) ? $catchesList->count() : (isset($catches) ? $catches->count() : 0) }} Data</span>
        </button>

        <button
            type="button"
            class="crud-tab-btn"
            :class="{ 'active': activeTab === 'ocean' }"
            @click="activeTab = 'ocean'"
        >
            <span>Parameter Oseanografi</span>
            <span class="tab-badge">{{ isset($oceanParamsList) ? $oceanParamsList->count() : (isset($oceanParams) ? $oceanParams->count() : 0) }} Data</span>
        </button>

        @if(auth()->user()->isMasterAdmin())
        <button
            type="button"
            class="crud-tab-btn"
            :class="{ 'active': activeTab === 'users' }"
            @click="activeTab = 'users'"
            style="border-left:2px solid var(--border-subtle); padding-left:14px;"
        >
            <span style="color:#0284c7; font-weight:700;">Pengelolaan Akun Admin</span>
            <span class="tab-badge" style="background:rgba(2,132,199,0.15); color:#0284c7;">{{ isset($allUsers) ? $allUsers->count() : 0 }}</span>
        </button>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         TAB 1: ZONA POTENSI PENANGKAPAN IKAN (ZPPI)
    ════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab === 'zppi'" x-transition>
        {{-- 1.A GEOJSON ZPPI --}}
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:14px; font-weight:800; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#176B87" stroke-width="2.5">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Data Spasial ZPPI Pulau Tunda
                    </span>
                    <span style="background:rgba(23,107,135,0.1); color:#176B87; font-size:11.5px; font-weight:700; padding:3px 10px; border-radius:20px; border:1px solid rgba(23,107,135,0.2);">
                        <span x-text="zppiCount"></span> Titik Terpetakan
                    </span>
                </div>

                <div style="position:relative; width:280px;">
                    <input
                        type="text"
                        x-model="searchZppi"
                        placeholder="Cari koordinat, kontur, musim..."
                        class="scomar-input"
                        style="padding-left:34px; height:38px; font-size:13px;"
                    >
                    <svg style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
            </div>

            <div style="overflow-x:auto; border:1px solid var(--border-subtle); border-radius:10px;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                    <thead>
                        <tr style="background:rgba(165,184,208,0.12); border-bottom:1px solid var(--border-subtle); color:var(--text-primary);">
                            <th style="padding:10px 14px; font-weight:700; width:50px;">No</th>
                            <th style="padding:10px 14px; font-weight:700;">Periode &amp; Musim</th>
                            <th style="padding:10px 14px; font-weight:700;">Koordinat Desimal</th>
                            <th style="padding:10px 14px; font-weight:700;">Koordinat Geografis (DMS)</th>
                            <th style="padding:10px 14px; font-weight:700;">Data Oseanografi</th>
                            <th style="padding:10px 14px; font-weight:700; text-align:center;">Geometri</th>
                            <th style="padding:10px 14px; font-weight:700; text-align:center; width:90px;">Fokus Peta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="pt in filteredZppi()" :key="pt.id">
                            <tr
                                style="border-bottom:1px solid var(--border-subtle); transition:background 0.15s; cursor:pointer;"
                                onmouseover="this.style.background='rgba(165,184,208,0.08)'"
                                onmouseout="this.style.background='transparent'"
                                @click="window.focusZppiPoint(pt.lat, pt.lng, pt.markerIndex)"
                            >
                                <td style="padding:10px 14px; font-weight:700; color:var(--merlot);">
                                    <span x-text="'#' + pt.id"></span>
                                </td>
                                <td style="padding:10px 14px; color:var(--text-primary); font-weight:600;">
                                    <span x-text="'ZPPI ' + (pt.year || zppiYear) + ' \u2013 ' + (pt.season || zppiSeason)"></span>
                                </td>
                                <td style="padding:10px 14px; font-family:monospace; font-size:12px; color:var(--text-secondary);">
                                    <span x-text="pt.lat.toFixed(5) + ', ' + pt.lng.toFixed(5)"></span>
                                </td>
                                <td style="padding:10px 14px;">
                                    <span class="dms-badge" x-text="pt.dmsLat + ' • ' + pt.dmsLng"></span>
                                </td>
                                <td style="padding:10px 14px; color:var(--text-primary);">
                                    <span x-text="pt.contour !== '-' && pt.contour !== null ? 'Kontur: ' + pt.contour : 'Zona Terverifikasi'"></span>
                                </td>
                                <td style="padding:10px 14px; text-align:center;">
                                    <span style="background:rgba(8,145,178,0.12); color:#0891b2; font-size:10.5px; font-weight:700; padding:2px 7px; border-radius:4px;" x-text="pt.geomType || 'Point'"></span>
                                </td>
                                <td style="padding:10px 14px; text-align:center;" @click.stop>
                                    <button
                                        type="button"
                                        class="btn-action-icon view"
                                        title="Fokuskan Peta ke Titik ZPPI ini"
                                        @click="window.focusZppiPoint(pt.lat, pt.lng, pt.markerIndex)"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredZppi().length === 0">
                            <tr>
                                <td colspan="7" style="padding:28px; text-align:center; color:var(--text-muted);">
                                    Memuat data koordinat spasial ZPPI atau tidak ada titik yang sesuai dengan pencarian...
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         TAB 2: TERUMBU KARANG (EKOSISTEM)
    ════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab === 'reefs'" x-transition style="display:none;">
        {{-- 2.A KLASIFIKASI LYZENGA --}}
        <div x-show="true">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <span style="font-size:14px; font-weight:800; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="2.5">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                            <polyline points="2 17 12 22 22 17"/>
                            <polyline points="2 12 12 17 22 12"/>
                        </svg>
                        Klasifikasi Ekosistem Terumbu Karang Metode Lyzenga
                    </span>
                    <span style="background:rgba(13,148,136,0.1); color:#0D9488; font-size:11.5px; font-weight:700; padding:3px 10px; border-radius:20px; border:1px solid rgba(13,148,136,0.2);">
                        <span x-text="lyzengaClasses.length || 5"></span> Kelas Spasial
                    </span>
                    <div style="display:inline-flex; align-items:center; gap:6px; margin-left:8px;">
                        <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Tahun:</span>
                        <select
                            x-model="lyzengaYear"
                            @change="changeLyzengaYear($event.target.value)"
                            class="scomar-input"
                            style="height:34px; padding:0 8px; font-size:12px; font-weight:700; color:#0D9488; border-color:rgba(13,148,136,0.3); background:var(--bg-surface); cursor:pointer;"
                        >
                            @foreach(($lyzengaManifest ?? []) as $yr => $info)
                                <option value="{{ $yr }}">{{ $info['label'] ?? ('Tahun ' . $yr) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="position:relative; width:280px;">
                    <input
                        type="text"
                        x-model="searchLyzenga"
                        placeholder="Cari kelas, gridcode, luas..."
                        class="scomar-input"
                        style="padding-left:34px; height:38px; font-size:13px;"
                    >
                    <svg style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
            </div>

            <div style="overflow-x:auto; border:1px solid var(--border-subtle); border-radius:10px;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                    <thead>
                        <tr style="background:rgba(165,184,208,0.12); border-bottom:1px solid var(--border-subtle); color:var(--text-primary);">
                            <th style="padding:10px 14px; font-weight:700; width:50px;">No</th>
                            <th style="padding:10px 14px; font-weight:700;">Kategori Klasifikasi</th>
                            <th style="padding:10px 14px; font-weight:700; text-align:center;">Nilai Gridcode</th>
                            <th style="padding:10px 14px; font-weight:700; text-align:right;">Luas Wilayah (m²)</th>
                            <th style="padding:10px 14px; font-weight:700; text-align:right;">Keliling (m)</th>
                            <th style="padding:10px 14px; font-weight:700; text-align:center;">Geometri</th>
                            <th style="padding:10px 14px; font-weight:700; text-align:center; width:90px;">Fokus Peta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(c, idx) in filteredLyzenga()" :key="c.gridcode">
                            <tr
                                style="border-bottom:1px solid var(--border-subtle); transition:background 0.15s; cursor:pointer;"
                                onmouseover="this.style.background='rgba(165,184,208,0.08)'"
                                onmouseout="this.style.background='transparent'"
                                @click="window.focusLyzengaClass(c.gridcode)"
                            >
                                <td style="padding:10px 14px; font-weight:700; color:var(--merlot);">
                                    <span x-text="'#' + (idx + 1)"></span>
                                </td>
                                <td style="padding:10px 14px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span
                                            style="width:14px; height:14px; border-radius:3px; flex-shrink:0; display:inline-block;"
                                            :style="'background:' + c.color + '; border:1px solid ' + c.border"
                                        ></span>
                                        <span style="font-weight:700; color:var(--text-primary);" x-text="c.label"></span>
                                    </div>
                                </td>
                                <td style="padding:10px 14px; text-align:center;">
                                    <span
                                        style="font-family:monospace; font-weight:800; font-size:12px; padding:2px 8px; border-radius:4px;"
                                        :style="'background:' + c.color + '22; color:' + c.border + '; border:1px solid ' + c.color + '44;'"
                                        x-text="c.gridcode"
                                    ></span>
                                </td>
                                <td style="padding:10px 14px; text-align:right; font-family:monospace; font-size:12.5px; font-weight:700; color:#0D9488;">
                                    <span x-text="c.areaM2Formatted + ' m²'"></span>
                                </td>
                                <td style="padding:10px 14px; text-align:right; font-family:monospace; font-size:12px; color:var(--text-secondary);">
                                    <span x-text="c.lengthFormatted + ' m'"></span>
                                </td>
                                <td style="padding:10px 14px; text-align:center;">
                                    <span style="background:rgba(13,148,136,0.12); color:#0D9488; font-size:10.5px; font-weight:700; padding:2px 7px; border-radius:4px;">
                                        MultiPolygon
                                    </span>
                                </td>
                                <td style="padding:10px 14px; text-align:center;" @click.stop>
                                    <button
                                        type="button"
                                        class="btn-action-icon view"
                                        title="Fokuskan Peta ke Area Poligon Ini"
                                        @click="window.focusLyzengaClass(c.gridcode)"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                                            <polyline points="2 17 12 22 22 17"/>
                                            <polyline points="2 12 12 17 22 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredLyzenga().length === 0">
                            <tr>
                                <td colspan="7" style="padding:28px; text-align:center; color:var(--text-muted);">
                                    Memuat data klasifikasi Lyzenga atau tidak ada kelas yang sesuai pencarian...
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════════
         TAB 3: ALAT TANGKAP REKOMENDASI
    ════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab === 'gears'" x-transition style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
            <div style="position:relative; width:260px;">
                <input
                    type="text"
                    x-model="searchGears"
                    placeholder="Cari nama atau target ikan..."
                    class="scomar-input"
                    style="padding-left:34px; height:38px; font-size:13px;"
                >
                <svg style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            @if(auth()->user()->isAdmin())
                <button
                    type="button"
                    class="btn-scomar-primary"
                    @click="gearItem = { id: '', name: '', category: 'Ramah Lingkungan', target_species: 'Scomberomorus commerson', selectivity_level: 'Tinggi', status: 'Direkomendasikan', description: '' }; showCreateGear = true;"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Alat Tangkap</span>
                </button>
            @endif
        </div>

        <div style="overflow-x:auto; border:1px solid var(--border-subtle); border-radius:10px;">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                <thead>
                    <tr style="background:rgba(165,184,208,0.12); border-bottom:1px solid var(--border-subtle); color:var(--text-primary);">
                        <th style="padding:10px 14px; font-weight:700;">Nama Alat Tangkap</th>
                        <th style="padding:10px 14px; font-weight:700;">Kategori</th>
                        <th style="padding:10px 14px; font-weight:700;">Target Tangkapan</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center;">Selektivitas</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center;">Status Rekomendasi</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center; width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($fishingGears ?? []) as $gear)
                        <tr
                            style="border-bottom:1px solid var(--border-subtle); transition:background 0.15s;"
                            x-show="!searchGears || '{{ strtolower($gear->name . ' ' . $gear->category . ' ' . $gear->target_species) }}'.includes(searchGears.toLowerCase())"
                            onmouseover="this.style.background='rgba(165,184,208,0.06)'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding:12px 14px; font-weight:700; color:var(--text-primary);">
                                {{ $gear->name }}
                            </td>
                            <td style="padding:12px 14px; color:var(--text-secondary);">
                                {{ $gear->category }}
                            </td>
                            <td style="padding:12px 14px; color:var(--text-primary);">
                                {{ $gear->target_species ?: '-' }}
                            </td>
                            <td style="padding:12px 14px; text-align:center;">
                                <span style="background:rgba(165,184,208,0.18); color:var(--text-primary); font-size:11px; font-weight:700; padding:3px 8px; border-radius:5px;">
                                    {{ $gear->selectivity_level }}
                                </span>
                            </td>
                            <td style="padding:12px 14px; text-align:center;">
                                @if($gear->status === 'Direkomendasikan')
                                    <span style="background:rgba(5,150,105,0.12); color:#059669; font-size:11px; font-weight:700; padding:3px 8px; border-radius:5px;">
                                        Direkomendasikan
                                    </span>
                                @elseif($gear->status === 'Direkomendasikan Bersyarat')
                                    <span style="background:rgba(245,158,11,0.12); color:#d97706; font-size:11px; font-weight:700; padding:3px 8px; border-radius:5px;">
                                        Direkomendasikan Bersyarat
                                    </span>
                                @elseif($gear->status === 'Diizinkan')
                                    <span style="background:rgba(2,132,199,0.12); color:#0284C7; font-size:11px; font-weight:700; padding:3px 8px; border-radius:5px;">
                                        Diizinkan
                                    </span>
                                @else
                                    <span style="background:rgba(220,38,38,0.12); color:#DC2626; font-size:11px; font-weight:700; padding:3px 8px; border-radius:5px;">
                                        {{ $gear->status }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding:12px 14px; text-align:center;">
                                <div style="display:inline-flex; gap:5px; justify-content:center;">
                                    <button
                                        type="button"
                                        class="btn-action-icon view"
                                        title="Lihat Detail"
                                        @click="openViewGear({
                                            id: '{{ $gear->id }}',
                                            name: '{{ addslashes($gear->name) }}',
                                            category: '{{ addslashes($gear->category) }}',
                                            target_species: '{{ addslashes($gear->target_species) }}',
                                            selectivity_level: '{{ $gear->selectivity_level }}',
                                            status: '{{ $gear->status }}',
                                            description: '{{ addslashes($gear->description) }}'
                                        })"
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
                                            title="Edit Data"
                                            @click="openEditGear({
                                                id: '{{ $gear->id }}',
                                                name: '{{ addslashes($gear->name) }}',
                                                category: '{{ addslashes($gear->category) }}',
                                                target_species: '{{ addslashes($gear->target_species) }}',
                                                selectivity_level: '{{ $gear->selectivity_level }}',
                                                status: '{{ $gear->status }}',
                                                description: '{{ addslashes($gear->description) }}'
                                            })"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn-action-icon delete"
                                            title="Hapus Data"
                                            @click="openDeleteGear({
                                                id: '{{ $gear->id }}',
                                                name: '{{ addslashes($gear->name) }}'
                                            })"
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
                    @empty
                        <tr>
                            <td colspan="6" style="padding:28px; text-align:center; color:var(--text-muted);">
                                Belum ada data Alat Tangkap yang tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         TAB 4: HASIL TANGKAPAN & CPUE
    ════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab === 'catches'" x-transition style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:14px; font-weight:800; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#E07A5F" stroke-width="2.5">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    Data Produksi Hasil Tangkapan &amp; CPUE
                </span>
                <span style="background:rgba(224,122,95,0.1); color:#E07A5F; font-size:11.5px; font-weight:700; padding:3px 10px; border-radius:20px; border:1px solid rgba(224,122,95,0.2);">
                    {{ isset($catchesList) ? $catchesList->count() : (isset($catches) ? $catches->count() : 0) }} Rekaman
                </span>
            </div>

            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="position:relative; width:260px;">
                    <input
                        type="text"
                        x-model="searchCatches"
                        placeholder="Cari tahun, musim, periode..."
                        class="scomar-input"
                        style="padding-left:34px; height:38px; font-size:13px;"
                    >
                    <svg style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                @if(auth()->user()->isAdmin())
                    <button
                        type="button"
                        class="btn-scomar-primary"
                        @click="catchItem = { id: '', year: '2024', season: 'Musim Barat', period: 'Januari - Maret 2024', total_catch: '1500.00', total_count: '250', trips: '15', cpue: '100.00', notes: '' }; showCreateCatch = true;"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Tambah Hasil Tangkapan</span>
                    </button>
                @endif
            </div>
        </div>

        <div style="overflow-x:auto; border:1px solid var(--border-subtle); border-radius:10px;">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                <thead>
                    <tr style="background:rgba(165,184,208,0.12); border-bottom:1px solid var(--border-subtle); color:var(--text-primary);">
                        <th style="padding:10px 14px; font-weight:700; width:50px;">No</th>
                        <th style="padding:10px 14px; font-weight:700;">Tahun</th>
                        <th style="padding:10px 14px; font-weight:700;">Musim Penangkapan</th>
                        <th style="padding:10px 14px; font-weight:700;">Periode Bulan</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:right;">Total Tangkapan</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:right;">Jumlah Ekor</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center;">Trip</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:right;">CPUE (kg/trip)</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center; width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($catchesList ?? $catches ?? []) as $idx => $c)
                        <tr
                            style="border-bottom:1px solid var(--border-subtle); transition:background 0.15s;"
                            x-show="!searchCatches || '{{ strtolower($c->year . ' ' . $c->season . ' ' . $c->period . ' ' . $c->notes) }}'.includes(searchCatches.toLowerCase())"
                            onmouseover="this.style.background='rgba(165,184,208,0.06)'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding:10px 14px; font-weight:700; color:var(--merlot);">
                                #{{ $idx + 1 }}
                            </td>
                            <td style="padding:10px 14px; font-weight:800; color:var(--text-primary);">
                                {{ $c->year }}
                            </td>
                            <td style="padding:10px 14px; font-weight:600; color:var(--text-primary);">
                                {{ $c->season }}
                            </td>
                            <td style="padding:10px 14px; color:var(--text-secondary);">
                                {{ $c->period }}
                            </td>
                            <td style="padding:10px 14px; text-align:right; font-weight:800; color:#E07A5F; font-family:monospace;">
                                {{ number_format($c->total_catch, 2, ',', '.') }} kg
                            </td>
                            <td style="padding:10px 14px; text-align:right; font-family:monospace; color:var(--text-primary);">
                                {{ number_format($c->total_count, 0, ',', '.') }} ekor
                            </td>
                            <td style="padding:10px 14px; text-align:center; font-family:monospace;">
                                {{ $c->trips }}
                            </td>
                            <td style="padding:10px 14px; text-align:right; font-weight:700; color:#059669; font-family:monospace;">
                                {{ number_format($c->cpue, 2, ',', '.') }}
                            </td>
                            <td style="padding:10px 14px; text-align:center;">
                                <div style="display:inline-flex; gap:5px; justify-content:center;">
                                    <button
                                        type="button"
                                        class="btn-action-icon view"
                                        title="Lihat Detail"
                                        @click="openViewCatch({
                                            id: '{{ $c->id }}',
                                            year: '{{ $c->year }}',
                                            season: '{{ addslashes($c->season) }}',
                                            period: '{{ addslashes($c->period) }}',
                                            total_catch: '{{ $c->total_catch }}',
                                            total_count: '{{ $c->total_count }}',
                                            trips: '{{ $c->trips }}',
                                            cpue: '{{ $c->cpue }}',
                                            notes: '{{ addslashes($c->notes ?? '') }}'
                                        })"
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
                                            title="Edit Data"
                                            @click="openEditCatch({
                                                id: '{{ $c->id }}',
                                                year: '{{ $c->year }}',
                                                season: '{{ addslashes($c->season) }}',
                                                period: '{{ addslashes($c->period) }}',
                                                total_catch: '{{ $c->total_catch }}',
                                                total_count: '{{ $c->total_count }}',
                                                trips: '{{ $c->trips }}',
                                                cpue: '{{ $c->cpue }}',
                                                notes: '{{ addslashes($c->notes ?? '') }}'
                                            })"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn-action-icon delete"
                                            title="Hapus Data"
                                            @click="openDeleteCatch({
                                                id: '{{ $c->id }}',
                                                info: '{{ $c->season }} {{ $c->year }}'
                                            })"
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
                    @empty
                        <tr>
                            <td colspan="9" style="padding:28px; text-align:center; color:var(--text-muted);">
                                Belum ada data hasil tangkapan tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         TAB 5: PARAMETER OSEANOGRAFI
    ════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab === 'ocean'" x-transition style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:14px; font-weight:800; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0891B2" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                        <path d="M2 12h20"></path>
                    </svg>
                    Data Parameter Oseanografi &amp; Kualitas Perairan
                </span>
                <span style="background:rgba(8,145,178,0.1); color:#0891B2; font-size:11.5px; font-weight:700; padding:3px 10px; border-radius:20px; border:1px solid rgba(8,145,178,0.2);">
                    {{ isset($oceanParamsList) ? $oceanParamsList->count() : (isset($oceanParams) ? $oceanParams->count() : 0) }} Rekaman
                </span>
            </div>

            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="position:relative; width:260px;">
                    <input
                        type="text"
                        x-model="searchOcean"
                        placeholder="Cari tahun, status, sumber..."
                        class="scomar-input"
                        style="padding-left:34px; height:38px; font-size:13px;"
                    >
                    <svg style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                @if(auth()->user()->isAdmin())
                    <button
                        type="button"
                        class="btn-scomar-primary"
                        @click="oceanItem = { id: '', year: '2024', temp: '29.80', chlorophyll: '0.88', tss: '12.50', current: '0.42', status: 'Normal / Subur', habitat_score: 'Optimal', source: 'Satelit Aqua MODIS & Pemantauan SCOMAR', notes: '' }; showCreateOcean = true;"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Tambah Parameter</span>
                    </button>
                @endif
            </div>
        </div>

        <div style="overflow-x:auto; border:1px solid var(--border-subtle); border-radius:10px;">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                <thead>
                    <tr style="background:rgba(165,184,208,0.12); border-bottom:1px solid var(--border-subtle); color:var(--text-primary);">
                        <th style="padding:10px 14px; font-weight:700; width:50px;">No</th>
                        <th style="padding:10px 14px; font-weight:700;">Tahun</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:right;">Suhu SPL (°C)</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:right;">Klorofil-a (mg/m³)</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:right;">TSS (mg/L)</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:right;">Arus (m/s)</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center;">Status Kesuburan</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center;">Skor Habitat</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center; width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($oceanParamsList ?? $oceanParams ?? []) as $idx => $op)
                        <tr
                            style="border-bottom:1px solid var(--border-subtle); transition:background 0.15s;"
                            x-show="!searchOcean || '{{ strtolower($op->year . ' ' . $op->status . ' ' . $op->habitat_score . ' ' . $op->source) }}'.includes(searchOcean.toLowerCase())"
                            onmouseover="this.style.background='rgba(165,184,208,0.06)'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding:10px 14px; font-weight:700; color:var(--merlot);">
                                #{{ $idx + 1 }}
                            </td>
                            <td style="padding:10px 14px; font-weight:800; color:var(--text-primary);">
                                {{ $op->year }}
                            </td>
                            <td style="padding:10px 14px; text-align:right; font-weight:700; color:#E07A5F; font-family:monospace;">
                                {{ number_format($op->temp, 2) }}°C
                            </td>
                            <td style="padding:10px 14px; text-align:right; font-weight:700; color:#059669; font-family:monospace;">
                                {{ number_format($op->chlorophyll, 2) }}
                            </td>
                            <td style="padding:10px 14px; text-align:right; font-family:monospace; color:var(--text-primary);">
                                {{ number_format($op->tss, 2) }}
                            </td>
                            <td style="padding:10px 14px; text-align:right; font-family:monospace; color:var(--text-secondary);">
                                {{ number_format($op->current, 2) }}
                            </td>
                            <td style="padding:10px 14px; text-align:center;">
                                <span style="background:rgba(5,150,105,0.12); color:#059669; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">
                                    {{ $op->status }}
                                </span>
                            </td>
                            <td style="padding:10px 14px; text-align:center;">
                                <span style="background:rgba(2,132,199,0.12); color:#0284C7; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">
                                    {{ $op->habitat_score }}
                                </span>
                            </td>
                            <td style="padding:10px 14px; text-align:center;">
                                <div style="display:inline-flex; gap:5px; justify-content:center;">
                                    <button
                                        type="button"
                                        class="btn-action-icon view"
                                        title="Lihat Detail"
                                        @click="openViewOcean({
                                            id: '{{ $op->id }}',
                                            year: '{{ $op->year }}',
                                            temp: '{{ $op->temp }}',
                                            chlorophyll: '{{ $op->chlorophyll }}',
                                            tss: '{{ $op->tss }}',
                                            current: '{{ $op->current }}',
                                            status: '{{ addslashes($op->status) }}',
                                            habitat_score: '{{ addslashes($op->habitat_score) }}',
                                            source: '{{ addslashes($op->source) }}',
                                            notes: '{{ addslashes($op->notes ?? '') }}'
                                        })"
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
                                            title="Edit Data"
                                            @click="openEditOcean({
                                                id: '{{ $op->id }}',
                                                year: '{{ $op->year }}',
                                                temp: '{{ $op->temp }}',
                                                chlorophyll: '{{ $op->chlorophyll }}',
                                                tss: '{{ $op->tss }}',
                                                current: '{{ $op->current }}',
                                                status: '{{ addslashes($op->status) }}',
                                                habitat_score: '{{ addslashes($op->habitat_score) }}',
                                                source: '{{ addslashes($op->source) }}',
                                                notes: '{{ addslashes($op->notes ?? '') }}'
                                            })"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn-action-icon delete"
                                            title="Hapus Data"
                                            @click="openDeleteOcean({
                                                id: '{{ $op->id }}',
                                                year: '{{ $op->year }}'
                                            })"
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
                    @empty
                        <tr>
                            <td colspan="9" style="padding:28px; text-align:center; color:var(--text-muted);">
                                Belum ada data parameter oseanografi tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         TAB 6: PENGELOLAAN AKUN (KHUSUS MASTER ADMIN)
    ════════════════════════════════════════════════════════════════ --}}
    @if(auth()->user()->isMasterAdmin())
    <div x-show="activeTab === 'users'" x-transition style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:14px; font-weight:800; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Daftar Akun Pengguna &amp; Hak Akses Administrator
                </span>
                <span style="background:rgba(2,132,199,0.1); color:#0284c7; font-size:11.5px; font-weight:700; padding:3px 10px; border-radius:20px; border:1px solid rgba(2,132,199,0.2);">
                    {{ isset($allUsers) ? $allUsers->count() : 0 }} Akun
                </span>
            </div>

            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="position:relative; width:260px;">
                    <input
                        type="text"
                        x-model="searchUsers"
                        placeholder="Cari nama, email, role..."
                        class="scomar-input"
                        style="padding-left:34px; height:38px; font-size:13px;"
                    >
                    <svg style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                <button
                    type="button"
                    class="btn-scomar-primary"
                    style="background:#0284c7;"
                    @click="openCreateUser()"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Akun Baru</span>
                </button>
            </div>
        </div>

        <div style="overflow-x:auto; border:1px solid var(--border-subtle); border-radius:10px;">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                <thead>
                    <tr style="background:rgba(165,184,208,0.12); border-bottom:1px solid var(--border-subtle); color:var(--text-primary);">
                        <th style="padding:10px 14px; font-weight:700; width:50px;">No</th>
                        <th style="padding:10px 14px; font-weight:700;">Nama Lengkap</th>
                        <th style="padding:10px 14px; font-weight:700;">Email</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center;">Peran / Role</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center;">Terdaftar</th>
                        <th style="padding:10px 14px; font-weight:700; text-align:center; width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($allUsers ?? []) as $idx => $u)
                        <tr
                            style="border-bottom:1px solid var(--border-subtle); transition:background 0.15s;"
                            x-show="!searchUsers || '{{ strtolower($u->name . ' ' . $u->email . ' ' . $u->role) }}'.includes(searchUsers.toLowerCase())"
                            onmouseover="this.style.background='rgba(165,184,208,0.06)'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding:10px 14px; font-weight:700; color:var(--merlot);">
                                #{{ $idx + 1 }}
                            </td>
                            <td style="padding:10px 14px; font-weight:700; color:var(--text-primary);">
                                {{ $u->name }}
                                @if($u->id === auth()->id())
                                    <span style="font-size:10px; background:rgba(2,132,199,0.12); color:#0284c7; padding:1px 6px; border-radius:4px; margin-left:4px;">Anda</span>
                                @endif
                            </td>
                            <td style="padding:10px 14px; color:var(--text-secondary); font-family:monospace;">
                                {{ $u->email }}
                            </td>
                            <td style="padding:10px 14px; text-align:center;">
                                @if($u->role === 'master_admin')
                                    <span style="background:rgba(115,52,58,0.12); color:var(--merlot); font-size:11px; font-weight:800; padding:3px 10px; border-radius:20px; border:1px solid rgba(115,52,58,0.25);">
                                        Master Admin
                                    </span>
                                @elseif($u->role === 'admin')
                                    <span style="background:rgba(2,132,199,0.12); color:#0284c7; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; border:1px solid rgba(2,132,199,0.25);">
                                        Admin
                                    </span>
                                @else
                                    <span style="background:rgba(100,116,139,0.12); color:#64748b; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px;">
                                        Pengguna
                                    </span>
                                @endif
                            </td>
                            <td style="padding:10px 14px; text-align:center; font-size:12px; color:var(--text-secondary);">
                                {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                            </td>
                            <td style="padding:10px 14px; text-align:center;">
                                <div style="display:inline-flex; gap:5px; justify-content:center;">
                                    <button
                                        type="button"
                                        class="btn-action-icon edit"
                                        title="Edit Akun &amp; Role"
                                        @click="openEditUser({
                                            id: '{{ $u->id }}',
                                            name: '{{ addslashes($u->name) }}',
                                            email: '{{ addslashes($u->email) }}',
                                            role: '{{ $u->role }}'
                                        })"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    @if($u->id !== auth()->id())
                                        <button
                                            type="button"
                                            class="btn-action-icon delete"
                                            title="Hapus Akun"
                                            @click="openDeleteUser({
                                                id: '{{ $u->id }}',
                                                name: '{{ addslashes($u->name) }}',
                                                email: '{{ addslashes($u->email) }}'
                                            })"
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
                    @empty
                        <tr>
                            <td colspan="6" style="padding:28px; text-align:center; color:var(--text-muted);">
                                Belum ada akun pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         MODALS TERUMBU KARANG
    ════════════════════════════════════════════════════════════════ --}}
    {{-- Modal Lihat Terumbu Karang --}}
    <div class="scomar-modal-backdrop" x-show="showViewReef" x-transition.opacity style="display:none;" @keydown.escape.window="showViewReef = false">
        <div class="scomar-modal-card" @click.outside="showViewReef = false">
            <div class="scomar-modal-header">
                <div class="scomar-modal-title">
                    <span>Rincian Zona Terumbu Karang</span>
                </div>
                <button type="button" class="scomar-alert-close" @click="showViewReef = false">&times;</button>
            </div>
            <div class="scomar-modal-body">
                <h3 style="font-size:17px; font-weight:800; color:var(--text-primary); margin:0 0 8px 0;" x-text="reefItem.name"></h3>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:11px; color:var(--text-muted);">Status Kondisi</div>
                        <div style="font-size:14px; font-weight:700; color:var(--deep-navy);" x-text="reefItem.condition_status"></div>
                    </div>
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:11px; color:var(--text-muted);">Tutupan Karang Hidup</div>
                        <div style="font-size:14px; font-weight:800; color:var(--merlot);" x-text="reefItem.coverage_percent ? (reefItem.coverage_percent + '%') : '-'"></div>
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <strong style="font-size:12.5px; color:var(--text-primary);">Koordinat Spasial:</strong>
                    <div style="font-size:13px; font-family:monospace; color:var(--text-secondary); margin-top:2px;" x-text="reefItem.latitude + ', ' + reefItem.longitude"></div>
                </div>
                <div style="margin-bottom:12px;">
                    <strong style="font-size:12.5px; color:var(--text-primary);">Alat Ramah Lingkungan yang Direkomendasikan:</strong>
                    <div style="font-size:13px; color:var(--text-primary); margin-top:2px;" x-text="reefItem.friendly_gear || '-'"></div>
                </div>
                <div style="margin-bottom:6px;">
                    <strong style="font-size:12.5px; color:var(--text-primary);">Deskripsi Habitat:</strong>
                    <p style="font-size:13px; color:var(--text-secondary); margin:4px 0 0 0; line-height:1.6;" x-text="reefItem.description || '-'"></p>
                </div>
            </div>
            <div class="scomar-modal-footer">
                <button type="button" class="btn-scomar-secondary" @click="showViewReef = false">Tutup</button>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        {{-- Modal Tambah Terumbu Karang --}}
        <div class="scomar-modal-backdrop" x-show="showCreateReef" x-transition.opacity style="display:none;" @keydown.escape.window="showCreateReef = false">
            <div class="scomar-modal-card" @click.outside="showCreateReef = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Tambah Zona Terumbu Karang</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showCreateReef = false">&times;</button>
                </div>
                <form method="POST" action="{{ route('coral-reefs.store') }}">
                    @csrf
                    <div class="scomar-modal-body">
                        <div class="scomar-form-group">
                            <label class="scomar-label">Nama Zona Terumbu Karang <span class="req">*</span></label>
                            <input type="text" name="name" x-model="reefItem.name" placeholder="Contoh: Rataan Karang Barat Pulau Tunda" class="scomar-input" required>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Latitude <span class="req">*</span></label>
                                <input type="number" step="any" name="latitude" x-model="reefItem.latitude" placeholder="-5.81200" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Longitude <span class="req">*</span></label>
                                <input type="number" step="any" name="longitude" x-model="reefItem.longitude" placeholder="106.31500" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Kondisi Terumbu Karang <span class="req">*</span></label>
                                <select name="condition_status" x-model="reefItem.condition_status" class="scomar-select" required>
                                    <option value="Sangat Baik">Sangat Baik (75-100%)</option>
                                    <option value="Baik">Baik (50-74.9%)</option>
                                    <option value="Sedang">Sedang (25-49.9%)</option>
                                    <option value="Rusak">Rusak (0-24.9%)</option>
                                </select>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Persentase Tutupan (%)</label>
                                <input type="number" step="0.1" min="0" max="100" name="coverage_percent" x-model="reefItem.coverage_percent" placeholder="55.5" class="scomar-input">
                            </div>
                        </div>
                        <div class="scomar-form-group">
                            <label class="scomar-label">Alat Tangkap Ramah Lingkungan</label>
                            <input type="text" name="friendly_gear" x-model="reefItem.friendly_gear" placeholder="Pancing Ulur, Rawai Hanyut" class="scomar-input">
                        </div>
                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Deskripsi Zona Habitat</label>
                            <textarea name="description" x-model="reefItem.description" rows="2" class="scomar-textarea" placeholder="Kondisi substrat, dominasi karang masif / bercabang..."></textarea>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showCreateReef = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Simpan Zona Karang</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edit Terumbu Karang --}}
        <div class="scomar-modal-backdrop" x-show="showEditReef" x-transition.opacity style="display:none;" @keydown.escape.window="showEditReef = false">
            <div class="scomar-modal-card" @click.outside="showEditReef = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Edit Zona Terumbu Karang</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showEditReef = false">&times;</button>
                </div>
                <form method="POST" :action="`{{ url('/coral-reefs') }}/${reefItem.id}`">
                    @csrf
                    @method('PUT')
                    <div class="scomar-modal-body">
                        <div class="scomar-form-group">
                            <label class="scomar-label">Nama Zona Terumbu Karang <span class="req">*</span></label>
                            <input type="text" name="name" x-model="reefItem.name" class="scomar-input" required>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Latitude <span class="req">*</span></label>
                                <input type="number" step="any" name="latitude" x-model="reefItem.latitude" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Longitude <span class="req">*</span></label>
                                <input type="number" step="any" name="longitude" x-model="reefItem.longitude" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Kondisi Terumbu Karang <span class="req">*</span></label>
                                <select name="condition_status" x-model="reefItem.condition_status" class="scomar-select" required>
                                    <option value="Sangat Baik">Sangat Baik (75-100%)</option>
                                    <option value="Baik">Baik (50-74.9%)</option>
                                    <option value="Sedang">Sedang (25-49.9%)</option>
                                    <option value="Rusak">Rusak (0-24.9%)</option>
                                </select>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Persentase Tutupan (%)</label>
                                <input type="number" step="0.1" min="0" max="100" name="coverage_percent" x-model="reefItem.coverage_percent" class="scomar-input">
                            </div>
                        </div>
                        <div class="scomar-form-group">
                            <label class="scomar-label">Alat Tangkap Ramah Lingkungan</label>
                            <input type="text" name="friendly_gear" x-model="reefItem.friendly_gear" class="scomar-input">
                        </div>
                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Deskripsi Zona Habitat</label>
                            <textarea name="description" x-model="reefItem.description" rows="2" class="scomar-textarea"></textarea>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showEditReef = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Perbarui Zona Karang</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Hapus Terumbu Karang --}}
        <div class="scomar-modal-backdrop" x-show="showDeleteReef" x-transition.opacity style="display:none;" @keydown.escape.window="showDeleteReef = false">
            <div class="scomar-modal-card" style="max-width:440px;" @click.outside="showDeleteReef = false">
                <div class="scomar-modal-header" style="border-bottom:none;">
                    <div class="scomar-modal-title" style="color:#DC2626;">
                        <span>Hapus Zona Karang</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showDeleteReef = false">&times;</button>
                </div>
                <div class="scomar-modal-body" style="padding-top:10px;">
                    <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0 0 12px 0;">
                        Apakah Anda yakin ingin menghapus data zona terumbu karang <strong style="color:var(--text-primary);" x-text="reefItem.name"></strong>?
                    </p>
                    <div style="background:rgba(220,38,38,0.08); border-left:3px solid #DC2626; padding:10px 14px; border-radius:6px; font-size:12px; color:#DC2626;">
                        Data monitoring zona terumbu karang ini akan dihapus secara permanen dari sistem.
                    </div>
                </div>
                <form method="POST" :action="`{{ url('/coral-reefs') }}/${reefItem.id}`">
                    @csrf
                    @method('DELETE')
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showDeleteReef = false">Batal</button>
                        <button type="submit" class="btn-scomar-danger">Ya, Hapus Data</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         MODALS ALAT TANGKAP
    ════════════════════════════════════════════════════════════════ --}}
    {{-- Modal Lihat Alat Tangkap --}}
    <div class="scomar-modal-backdrop" x-show="showViewGear" x-transition.opacity style="display:none;" @keydown.escape.window="showViewGear = false">
        <div class="scomar-modal-card" @click.outside="showViewGear = false">
            <div class="scomar-modal-header">
                <div class="scomar-modal-title">
                    <span>Rincian Rekomendasi Alat Tangkap</span>
                </div>
                <button type="button" class="scomar-alert-close" @click="showViewGear = false">&times;</button>
            </div>
            <div class="scomar-modal-body">
                <h3 style="font-size:17px; font-weight:800; color:var(--text-primary); margin:0 0 8px 0;" x-text="gearItem.name"></h3>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:11px; color:var(--text-muted);">Kategori Gear</div>
                        <div style="font-size:13px; font-weight:700; color:var(--text-primary);" x-text="gearItem.category"></div>
                    </div>
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:11px; color:var(--text-muted);">Status Rekomendasi</div>
                        <div style="font-size:13px; font-weight:700; color:var(--deep-navy);" x-text="gearItem.status"></div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <strong style="font-size:12px; color:var(--text-muted);">Target Tangkapan:</strong>
                        <div style="font-size:13px; color:var(--text-primary); font-weight:600; margin-top:2px;" x-text="gearItem.target_species || '-'"></div>
                    </div>
                    <div>
                        <strong style="font-size:12px; color:var(--text-muted);">Tingkat Selektivitas:</strong>
                        <div style="font-size:13px; color:var(--text-primary); font-weight:600; margin-top:2px;" x-text="gearItem.selectivity_level"></div>
                    </div>
                </div>
                <div style="margin-bottom:6px;">
                    <strong style="font-size:12.5px; color:var(--text-primary);">Deskripsi Teknis:</strong>
                    <p style="font-size:13px; color:var(--text-secondary); margin:4px 0 0 0; line-height:1.6;" x-text="gearItem.description || '-'"></p>
                </div>
            </div>
            <div class="scomar-modal-footer">
                <button type="button" class="btn-scomar-secondary" @click="showViewGear = false">Tutup</button>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        {{-- Modal Tambah Alat Tangkap --}}
        <div class="scomar-modal-backdrop" x-show="showCreateGear" x-transition.opacity style="display:none;" @keydown.escape.window="showCreateGear = false">
            <div class="scomar-modal-card" @click.outside="showCreateGear = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Tambah Rekomendasi Alat Tangkap</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showCreateGear = false">&times;</button>
                </div>
                <form method="POST" action="{{ route('fishing-gears.store') }}">
                    @csrf
                    <div class="scomar-modal-body">
                        <div class="scomar-form-group">
                            <label class="scomar-label">Nama Alat Tangkap <span class="req">*</span></label>
                            <input type="text" name="name" x-model="gearItem.name" placeholder="Contoh: Pancing Ulur (Handline)" class="scomar-input" required>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Kategori Gear <span class="req">*</span></label>
                                <input type="text" name="category" x-model="gearItem.category" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Target Spesies</label>
                                <input type="text" name="target_species" x-model="gearItem.target_species" placeholder="Scomberomorus commerson" class="scomar-input">
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tingkat Selektivitas <span class="req">*</span></label>
                                <select name="selectivity_level" x-model="gearItem.selectivity_level" class="scomar-select" required>
                                    <option value="Tinggi">Tinggi</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Rendah">Rendah</option>
                                </select>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Status Rekomendasi <span class="req">*</span></label>
                                <select name="status" x-model="gearItem.status" class="scomar-select" required>
                                    <option value="Direkomendasikan">Direkomendasikan</option>
                                    <option value="Direkomendasikan Bersyarat">Direkomendasikan Bersyarat</option>
                                    <option value="Diizinkan">Diizinkan</option>
                                    <option value="Dibatasi">Dibatasi</option>
                                    <option value="Dilarang">Dilarang</option>
                                </select>
                            </div>
                        </div>
                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Deskripsi Teknis &amp; Cara Operasi</label>
                            <textarea name="description" x-model="gearItem.description" rows="2" class="scomar-textarea" placeholder="Informasi ukuran mata pancing, cara pengoperasian..."></textarea>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showCreateGear = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Simpan Alat Tangkap</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edit Alat Tangkap --}}
        <div class="scomar-modal-backdrop" x-show="showEditGear" x-transition.opacity style="display:none;" @keydown.escape.window="showEditGear = false">
            <div class="scomar-modal-card" @click.outside="showEditGear = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Edit Alat Tangkap</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showEditGear = false">&times;</button>
                </div>
                <form method="POST" :action="`{{ url('/fishing-gears') }}/${gearItem.id}`">
                    @csrf
                    @method('PUT')
                    <div class="scomar-modal-body">
                        <div class="scomar-form-group">
                            <label class="scomar-label">Nama Alat Tangkap <span class="req">*</span></label>
                            <input type="text" name="name" x-model="gearItem.name" class="scomar-input" required>
                        </div>
                        <div style="display: grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Kategori Gear <span class="req">*</span></label>
                                <input type="text" name="category" x-model="gearItem.category" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Target Spesies</label>
                                <input type="text" name="target_species" x-model="gearItem.target_species" class="scomar-input">
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tingkat Selektivitas <span class="req">*</span></label>
                                <select name="selectivity_level" x-model="gearItem.selectivity_level" class="scomar-select" required>
                                    <option value="Tinggi">Tinggi</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Rendah">Rendah</option>
                                </select>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Status Rekomendasi <span class="req">*</span></label>
                                <select name="status" x-model="gearItem.status" class="scomar-select" required>
                                    <option value="Direkomendasikan">Direkomendasikan</option>
                                    <option value="Direkomendasikan Bersyarat">Direkomendasikan Bersyarat</option>
                                    <option value="Diizinkan">Diizinkan</option>
                                    <option value="Dibatasi">Dibatasi</option>
                                    <option value="Dilarang">Dilarang</option>
                                </select>
                            </div>
                        </div>
                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Deskripsi Teknis</label>
                            <textarea name="description" x-model="gearItem.description" rows="2" class="scomar-textarea"></textarea>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showEditGear = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Perbarui Alat Tangkap</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Hapus Alat Tangkap --}}
        <div class="scomar-modal-backdrop" x-show="showDeleteGear" x-transition.opacity style="display:none;" @keydown.escape.window="showDeleteGear = false">
            <div class="scomar-modal-card" style="max-width:440px;" @click.outside="showDeleteGear = false">
                <div class="scomar-modal-header" style="border-bottom:none;">
                    <div class="scomar-modal-title" style="color:#DC2626;">
                        <span>Hapus Alat Tangkap</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showDeleteGear = false">&times;</button>
                </div>
                <div class="scomar-modal-body" style="padding-top:10px;">
                    <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0 0 12px 0;">
                        Apakah Anda yakin ingin menghapus rekomendasi <strong style="color:var(--text-primary);" x-text="gearItem.name"></strong>?
                    </p>
                    <div style="background:rgba(220,38,38,0.08); border-left:3px solid #DC2626; padding:10px 14px; border-radius:6px; font-size:12px; color:#DC2626;">
                        Data alat tangkap ini akan dihapus dari rekomendasi sistem SCOMAR.
                    </div>
                </div>
                <form method="POST" :action="`{{ url('/fishing-gears') }}/${gearItem.id}`">
                    @csrf
                    @method('DELETE')
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showDeleteGear = false">Batal</button>
                        <button type="submit" class="btn-scomar-danger">Ya, Hapus Gear</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         MODALS HASIL TANGKAPAN
    ════════════════════════════════════════════════════════════════ --}}
    {{-- Modal Lihat Catch Data --}}
    <div class="scomar-modal-backdrop" x-show="showViewCatch" x-transition.opacity style="display:none;" @keydown.escape.window="showViewCatch = false">
        <div class="scomar-modal-card" @click.outside="showViewCatch = false">
            <div class="scomar-modal-header">
                <div class="scomar-modal-title">
                    <span>Rincian Hasil Tangkapan &amp; CPUE</span>
                </div>
                <button type="button" class="scomar-alert-close" @click="showViewCatch = false">&times;</button>
            </div>
            <div class="scomar-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h3 style="font-size:17px; font-weight:800; color:var(--text-primary); margin:0;" x-text="catchItem.season + ' ' + catchItem.year"></h3>
                    <span style="font-size:12px; background:rgba(224,122,95,0.12); color:#E07A5F; font-weight:700; padding:3px 10px; border-radius:6px;" x-text="catchItem.period"></span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:11px; color:var(--text-muted);">Total Produksi</div>
                        <div style="font-size:16px; font-weight:800; color:#E07A5F;" x-text="Number(catchItem.total_catch).toLocaleString('id-ID') + ' kg'"></div>
                    </div>
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:11px; color:var(--text-muted);">Efisiensi CPUE</div>
                        <div style="font-size:16px; font-weight:800; color:#059669;" x-text="Number(catchItem.cpue).toLocaleString('id-ID') + ' kg/trip'"></div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <strong style="font-size:12px; color:var(--text-muted);">Jumlah Ekor Tangkapan:</strong>
                        <div style="font-size:14px; color:var(--text-primary); font-weight:700; margin-top:2px;" x-text="Number(catchItem.total_count).toLocaleString('id-ID') + ' ekor'"></div>
                    </div>
                    <div>
                        <strong style="font-size:12px; color:var(--text-muted);">Jumlah Trip Operasi:</strong>
                        <div style="font-size:14px; color:var(--text-primary); font-weight:700; margin-top:2px;" x-text="catchItem.trips + ' trip'"></div>
                    </div>
                </div>
                <div style="margin-bottom:6px;">
                    <strong style="font-size:12.5px; color:var(--text-primary);">Catatan Penangkapan:</strong>
                    <p style="font-size:13px; color:var(--text-secondary); margin:4px 0 0 0; line-height:1.6;" x-text="catchItem.notes || 'Tidak ada catatan tambahan.'"></p>
                </div>
            </div>
            <div class="scomar-modal-footer">
                <button type="button" class="btn-scomar-secondary" @click="showViewCatch = false">Tutup</button>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        {{-- Modal Tambah Catch Data --}}
        <div class="scomar-modal-backdrop" x-show="showCreateCatch" x-transition.opacity style="display:none;" @keydown.escape.window="showCreateCatch = false">
            <div class="scomar-modal-card" @click.outside="showCreateCatch = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Tambah Data Hasil Tangkapan</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showCreateCatch = false">&times;</button>
                </div>
                <form method="POST" action="{{ route('catches.store') }}">
                    @csrf
                    <div class="scomar-modal-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tahun Pengamatan <span class="req">*</span></label>
                                <input type="number" min="2000" max="2035" name="year" x-model="catchItem.year" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Musim Penangkapan <span class="req">*</span></label>
                                <select name="season" x-model="catchItem.season" class="scomar-select" required>
                                    <option value="Musim Barat">Musim Barat</option>
                                    <option value="Musim Peralihan I">Musim Peralihan I</option>
                                    <option value="Musim Timur">Musim Timur</option>
                                    <option value="Musim Peralihan II">Musim Peralihan II</option>
                                </select>
                            </div>
                        </div>
                        <div class="scomar-form-group">
                            <label class="scomar-label">Periode Bulan <span class="req">*</span></label>
                            <input type="text" name="period" x-model="catchItem.period" placeholder="Contoh: Januari - Maret 2024" class="scomar-input" required>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Total Tangkapan (kg) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="0" name="total_catch" x-model="catchItem.total_catch" placeholder="1500.00" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Jumlah Ekor <span class="req">*</span></label>
                                <input type="number" min="0" name="total_count" x-model="catchItem.total_count" placeholder="250" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Jumlah Trip Operasi <span class="req">*</span></label>
                                <input type="number" min="1" name="trips" x-model="catchItem.trips" placeholder="15" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">CPUE (kg/trip)</label>
                                <input type="number" step="0.01" min="0" name="cpue" x-model="catchItem.cpue" placeholder="Otomatis dihitung jika kosong" class="scomar-input">
                            </div>
                        </div>
                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Catatan Tambahan</label>
                            <textarea name="notes" x-model="catchItem.notes" rows="2" class="scomar-textarea" placeholder="Informasi ukuran ikan, kondisi cuaca, armada..."></textarea>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showCreateCatch = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Simpan Data Tangkapan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edit Catch Data --}}
        <div class="scomar-modal-backdrop" x-show="showEditCatch" x-transition.opacity style="display:none;" @keydown.escape.window="showEditCatch = false">
            <div class="scomar-modal-card" @click.outside="showEditCatch = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Edit Data Hasil Tangkapan</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showEditCatch = false">&times;</button>
                </div>
                <form method="POST" :action="`{{ url('/catches') }}/${catchItem.id}`">
                    @csrf
                    @method('PUT')
                    <div class="scomar-modal-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tahun Pengamatan <span class="req">*</span></label>
                                <input type="number" min="2000" max="2035" name="year" x-model="catchItem.year" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Musim Penangkapan <span class="req">*</span></label>
                                <select name="season" x-model="catchItem.season" class="scomar-select" required>
                                    <option value="Musim Barat">Musim Barat</option>
                                    <option value="Musim Peralihan I">Musim Peralihan I</option>
                                    <option value="Musim Timur">Musim Timur</option>
                                    <option value="Musim Peralihan II">Musim Peralihan II</option>
                                </select>
                            </div>
                        </div>
                        <div class="scomar-form-group">
                            <label class="scomar-label">Periode Bulan <span class="req">*</span></label>
                            <input type="text" name="period" x-model="catchItem.period" class="scomar-input" required>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Total Tangkapan (kg) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="0" name="total_catch" x-model="catchItem.total_catch" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Jumlah Ekor <span class="req">*</span></label>
                                <input type="number" min="0" name="total_count" x-model="catchItem.total_count" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Jumlah Trip Operasi <span class="req">*</span></label>
                                <input type="number" min="1" name="trips" x-model="catchItem.trips" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">CPUE (kg/trip)</label>
                                <input type="number" step="0.01" min="0" name="cpue" x-model="catchItem.cpue" class="scomar-input">
                            </div>
                        </div>
                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Catatan Tambahan</label>
                            <textarea name="notes" x-model="catchItem.notes" rows="2" class="scomar-textarea"></textarea>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showEditCatch = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Perbarui Data Tangkapan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Hapus Catch Data --}}
        <div class="scomar-modal-backdrop" x-show="showDeleteCatch" x-transition.opacity style="display:none;" @keydown.escape.window="showDeleteCatch = false">
            <div class="scomar-modal-card" style="max-width:440px;" @click.outside="showDeleteCatch = false">
                <div class="scomar-modal-header" style="border-bottom:none;">
                    <div class="scomar-modal-title" style="color:#DC2626;">
                        <span>Hapus Hasil Tangkapan</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showDeleteCatch = false">&times;</button>
                </div>
                <div class="scomar-modal-body" style="padding-top:10px;">
                    <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0 0 12px 0;">
                        Apakah Anda yakin ingin menghapus data tangkapan <strong style="color:var(--text-primary);" x-text="catchItem.info"></strong>?
                    </p>
                    <div style="background:rgba(220,38,38,0.08); border-left:3px solid #DC2626; padding:10px 14px; border-radius:6px; font-size:12px; color:#DC2626;">
                        Data produksi dan CPUE ini akan dihapus dari perhitungan statistik SCOMAR.
                    </div>
                </div>
                <form method="POST" :action="`{{ url('/catches') }}/${catchItem.id}`">
                    @csrf
                    @method('DELETE')
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showDeleteCatch = false">Batal</button>
                        <button type="submit" class="btn-scomar-danger">Ya, Hapus Data</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         MODALS PARAMETER OSEANOGRAFI
    ════════════════════════════════════════════════════════════════ --}}
    {{-- Modal Lihat Oceanographic Parameter --}}
    <div class="scomar-modal-backdrop" x-show="showViewOcean" x-transition.opacity style="display:none;" @keydown.escape.window="showViewOcean = false">
        <div class="scomar-modal-card" @click.outside="showViewOcean = false">
            <div class="scomar-modal-header">
                <div class="scomar-modal-title">
                    <span>Rincian Parameter Oseanografi</span>
                </div>
                <button type="button" class="scomar-alert-close" @click="showViewOcean = false">&times;</button>
            </div>
            <div class="scomar-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h3 style="font-size:17px; font-weight:800; color:var(--text-primary); margin:0;" x-text="'Tahun Pengamatan ' + oceanItem.year"></h3>
                    <span style="font-size:12px; background:rgba(8,145,178,0.12); color:#0891B2; font-weight:700; padding:3px 10px; border-radius:6px;" x-text="oceanItem.status"></span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px;">
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:11px; color:var(--text-muted);">Suhu SPL</div>
                        <div style="font-size:16px; font-weight:800; color:#E07A5F;" x-text="oceanItem.temp + ' °C'"></div>
                    </div>
                    <div style="background:var(--bg-body); padding:10px; border-radius:8px;">
                        <div style="font-size:11px; color:var(--text-muted);">Konsentrasi Klorofil-a</div>
                        <div style="font-size:16px; font-weight:800; color:#059669;" x-text="oceanItem.chlorophyll + ' mg/m³'"></div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <strong style="font-size:12px; color:var(--text-muted);">Total Suspended Solids (TSS):</strong>
                        <div style="font-size:14px; color:var(--text-primary); font-weight:700; margin-top:2px;" x-text="oceanItem.tss + ' mg/L'"></div>
                    </div>
                    <div>
                        <strong style="font-size:12px; color:var(--text-muted);">Kecepatan Arus:</strong>
                        <div style="font-size:14px; color:var(--text-primary); font-weight:700; margin-top:2px;" x-text="oceanItem.current + ' m/s'"></div>
                    </div>
                </div>
                <div style="margin-bottom:10px;">
                    <strong style="font-size:12.5px; color:var(--text-primary);">Kesesuaian Habitat:</strong>
                    <div style="font-size:13px; color:#0284C7; font-weight:700; margin-top:2px;" x-text="oceanItem.habitat_score"></div>
                </div>
                <div style="margin-bottom:10px;">
                    <strong style="font-size:12.5px; color:var(--text-primary);">Sumber Data / Validasi:</strong>
                    <div style="font-size:13px; color:var(--text-primary); margin-top:2px;" x-text="oceanItem.source"></div>
                </div>
                <div style="margin-bottom:6px;">
                    <strong style="font-size:12.5px; color:var(--text-primary);">Catatan Oseanografi:</strong>
                    <p style="font-size:13px; color:var(--text-secondary); margin:4px 0 0 0; line-height:1.6;" x-text="oceanItem.notes || 'Tidak ada catatan tambahan.'"></p>
                </div>
            </div>
            <div class="scomar-modal-footer">
                <button type="button" class="btn-scomar-secondary" @click="showViewOcean = false">Tutup</button>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        {{-- Modal Tambah Oceanographic Parameter --}}
        <div class="scomar-modal-backdrop" x-show="showCreateOcean" x-transition.opacity style="display:none;" @keydown.escape.window="showCreateOcean = false">
            <div class="scomar-modal-card" @click.outside="showCreateOcean = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Tambah Parameter Oseanografi</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showCreateOcean = false">&times;</button>
                </div>
                <form method="POST" action="{{ route('oceanographic.store') }}">
                    @csrf
                    <div class="scomar-modal-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tahun Pengamatan <span class="req">*</span></label>
                                <input type="number" min="2000" max="2035" name="year" x-model="oceanItem.year" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Suhu SPL (°C) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="20" max="40" name="temp" x-model="oceanItem.temp" placeholder="29.50" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Klorofil-a (mg/m³) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" name="chlorophyll" x-model="oceanItem.chlorophyll" placeholder="0.85" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">TSS (mg/L) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="0" max="100" name="tss" x-model="oceanItem.tss" placeholder="12.00" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Kecepatan Arus (m/s) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" name="current" x-model="oceanItem.current" placeholder="0.45" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Status Kesuburan <span class="req">*</span></label>
                                <input type="text" name="status" x-model="oceanItem.status" placeholder="Normal / Subur" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Skor Kesesuaian Habitat <span class="req">*</span></label>
                                <input type="text" name="habitat_score" x-model="oceanItem.habitat_score" placeholder="Optimal" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Sumber Validasi Data <span class="req">*</span></label>
                                <input type="text" name="source" x-model="oceanItem.source" placeholder="Satelit Aqua MODIS" class="scomar-input" required>
                            </div>
                        </div>
                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Catatan Tambahan</label>
                            <textarea name="notes" x-model="oceanItem.notes" rows="2" class="scomar-textarea" placeholder="Informasi sensor citra, anomali oseanografi..."></textarea>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showCreateOcean = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Simpan Parameter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edit Oceanographic Parameter --}}
        <div class="scomar-modal-backdrop" x-show="showEditOcean" x-transition.opacity style="display:none;" @keydown.escape.window="showEditOcean = false">
            <div class="scomar-modal-card" @click.outside="showEditOcean = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Edit Parameter Oseanografi</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showEditOcean = false">&times;</button>
                </div>
                <form method="POST" :action="`{{ url('/oceanographic') }}/${oceanItem.id}`">
                    @csrf
                    @method('PUT')
                    <div class="scomar-modal-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Tahun Pengamatan <span class="req">*</span></label>
                                <input type="number" min="2000" max="2035" name="year" x-model="oceanItem.year" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Suhu SPL (°C) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="20" max="40" name="temp" x-model="oceanItem.temp" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Klorofil-a (mg/m³) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" name="chlorophyll" x-model="oceanItem.chlorophyll" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">TSS (mg/L) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="0" max="100" name="tss" x-model="oceanItem.tss" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Kecepatan Arus (m/s) <span class="req">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" name="current" x-model="oceanItem.current" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Status Kesuburan <span class="req">*</span></label>
                                <input type="text" name="status" x-model="oceanItem.status" class="scomar-input" required>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Skor Kesesuaian Habitat <span class="req">*</span></label>
                                <input type="text" name="habitat_score" x-model="oceanItem.habitat_score" class="scomar-input" required>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Sumber Validasi Data <span class="req">*</span></label>
                                <input type="text" name="source" x-model="oceanItem.source" class="scomar-input" required>
                            </div>
                        </div>
                        <div class="scomar-form-group" style="margin-bottom:0;">
                            <label class="scomar-label">Catatan Tambahan</label>
                            <textarea name="notes" x-model="oceanItem.notes" rows="2" class="scomar-textarea"></textarea>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showEditOcean = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary">Perbarui Parameter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Hapus Oceanographic Parameter --}}
        <div class="scomar-modal-backdrop" x-show="showDeleteOcean" x-transition.opacity style="display:none;" @keydown.escape.window="showDeleteOcean = false">
            <div class="scomar-modal-card" style="max-width:440px;" @click.outside="showDeleteOcean = false">
                <div class="scomar-modal-header" style="border-bottom:none;">
                    <div class="scomar-modal-title" style="color:#DC2626;">
                        <span>Hapus Parameter Oseanografi</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showDeleteOcean = false">&times;</button>
                </div>
                <div class="scomar-modal-body" style="padding-top:10px;">
                    <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0 0 12px 0;">
                        Apakah Anda yakin ingin menghapus data parameter oseanografi tahun <strong style="color:var(--text-primary);" x-text="oceanItem.year"></strong>?
                    </p>
                    <div style="background:rgba(220,38,38,0.08); border-left:3px solid #DC2626; padding:10px 14px; border-radius:6px; font-size:12px; color:#DC2626;">
                        Data suhu, klorofil-a, TSS, dan arus tahun ini akan dihapus dari pemantauan SCOMAR.
                    </div>
                </div>
                <form method="POST" :action="`{{ url('/oceanographic') }}/${oceanItem.id}`">
                    @csrf
                    @method('DELETE')
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showDeleteOcean = false">Batal</button>
                        <button type="submit" class="btn-scomar-danger">Ya, Hapus Data</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         MODALS PENGELOLAAN AKUN (KHUSUS MASTER ADMIN)
    ════════════════════════════════════════════════════════════════ --}}
    @if(auth()->user()->isMasterAdmin())
        {{-- Modal Tambah User --}}
        <div class="scomar-modal-backdrop" x-show="showCreateUser" x-transition.opacity style="display:none;" @keydown.escape.window="showCreateUser = false">
            <div class="scomar-modal-card" @click.outside="showCreateUser = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Tambah Akun Baru SCOMAR</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showCreateUser = false">&times;</button>
                </div>
                <form method="POST" action="{{ route('master-admin.accounts.store') }}">
                    @csrf
                    <div class="scomar-modal-body">
                        <div class="scomar-form-group">
                            <label class="scomar-label">Nama Lengkap <span class="req">*</span></label>
                            <input type="text" name="name" x-model="userItem.name" placeholder="Nama Lengkap Petugas" class="scomar-input" required>
                        </div>
                        <div class="scomar-form-group">
                            <label class="scomar-label">Alamat Email <span class="req">*</span></label>
                            <input type="email" name="email" x-model="userItem.email" placeholder="email@scomar.id" class="scomar-input" required>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Peran / Role Akun <span class="req">*</span></label>
                                <select name="role" x-model="userItem.role" class="scomar-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="user">Pengguna / Nelayan</option>
                                    <option value="master_admin">Master Admin</option>
                                </select>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Kata Sandi (Min 6 Karakter) <span class="req">*</span></label>
                                <input type="password" name="password" x-model="userItem.password" placeholder="Minimal 6 karakter" class="scomar-input" required minlength="6">
                            </div>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showCreateUser = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary" style="background:#0284c7;">Simpan Akun Baru</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edit User --}}
        <div class="scomar-modal-backdrop" x-show="showEditUser" x-transition.opacity style="display:none;" @keydown.escape.window="showEditUser = false">
            <div class="scomar-modal-card" @click.outside="showEditUser = false">
                <div class="scomar-modal-header">
                    <div class="scomar-modal-title">
                        <span>Edit Akun &amp; Peran SCOMAR</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showEditUser = false">&times;</button>
                </div>
                <form method="POST" :action="`{{ url('/master-admin/accounts') }}/${userItem.id}`">
                    @csrf
                    @method('PUT')
                    <div class="scomar-modal-body">
                        <div class="scomar-form-group">
                            <label class="scomar-label">Nama Lengkap <span class="req">*</span></label>
                            <input type="text" name="name" x-model="userItem.name" class="scomar-input" required>
                        </div>
                        <div class="scomar-form-group">
                            <label class="scomar-label">Alamat Email <span class="req">*</span></label>
                            <input type="email" name="email" x-model="userItem.email" class="scomar-input" required>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="scomar-form-group">
                                <label class="scomar-label">Peran / Role Akun <span class="req">*</span></label>
                                <select name="role" x-model="userItem.role" class="scomar-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="user">Pengguna / Nelayan</option>
                                    <option value="master_admin">Master Admin</option>
                                </select>
                            </div>
                            <div class="scomar-form-group">
                                <label class="scomar-label">Kata Sandi Baru (Opsional)</label>
                                <input type="password" name="password" x-model="userItem.password" placeholder="Kosongkan jika tidak diubah" class="scomar-input">
                            </div>
                        </div>
                    </div>
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showEditUser = false">Batal</button>
                        <button type="submit" class="btn-scomar-primary" style="background:#0284c7;">Perbarui Data Akun</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Hapus User --}}
        <div class="scomar-modal-backdrop" x-show="showDeleteUser" x-transition.opacity style="display:none;" @keydown.escape.window="showDeleteUser = false">
            <div class="scomar-modal-card" style="max-width:440px;" @click.outside="showDeleteUser = false">
                <div class="scomar-modal-header" style="border-bottom:none;">
                    <div class="scomar-modal-title" style="color:#DC2626;">
                        <span>Hapus Akun Pengguna</span>
                    </div>
                    <button type="button" class="scomar-alert-close" @click="showDeleteUser = false">&times;</button>
                </div>
                <div class="scomar-modal-body" style="padding-top:10px;">
                    <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0 0 12px 0;">
                        Apakah Anda yakin ingin menghapus akun <strong style="color:var(--text-primary);" x-text="userItem.name"></strong> (<span x-text="userItem.email"></span>)?
                    </p>
                    <div style="background:rgba(220,38,38,0.08); border-left:3px solid #DC2626; padding:10px 14px; border-radius:6px; font-size:12px; color:#DC2626;">
                        Akun ini tidak akan dapat login lagi ke sistem SCOMAR.
                    </div>
                </div>
                <form method="POST" :action="`{{ url('/master-admin/accounts') }}/${userItem.id}`">
                    @csrf
                    @method('DELETE')
                    <div class="scomar-modal-footer">
                        <button type="button" class="btn-scomar-secondary" @click="showDeleteUser = false">Batal</button>
                        <button type="submit" class="btn-scomar-danger">Ya, Hapus Akun</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@endsection

{{-- ================================================================
     JAVASCRIPT PETA LEAFLET DENGAN DATA DINAMIS DARI DATABASE
================================================================ --}}
@push('scripts')
<script>
(function () {
    'use strict';

    /* ============================================================
       0. MANIFEST ZPPI & LYZENGA (Dinamis dari PHP Controller)
    ============================================================ */
    const zppiManifest = @json($zppiManifest ?? []);
    const lyzengaManifest = @json($lyzengaManifest ?? []);
    const catchByPeriodData = @json($catchByPeriod ?? []);
    const oceanByYearData   = @json($oceanByYear ?? []);
    const coralReefsDb      = @json($coralReefs ?? []);
    const fishingGearsDb    = @json($fishingGears ?? []);
    const lyzengaCoralSpatialCache = {};

    /* Fallback data tangkapan & oseanografi database jika dev offline */
    const CATCH_FALLBACK = {
        '2024': { total: 9660.5, seasons: { barat: 1450, peralihan1: 2180.5, timur: 3620, peralihan2: 2410 } },
        '2025': { total: 11330.5, seasons: { barat: 1620, peralihan1: 2540, timur: 4280, peralihan2: 2890.5 } },
        '2026': { total: 10720.5, seasons: { barat: 1780, peralihan1: 2810, timur: 4150, peralihan2: 1980.5 } }
    };

    const OCEAN_FALLBACK = {
        '2021': { temp: 30.08, chlorophyll: 0.62, status: 'Optimal', source: 'Satelit Aqua MODIS' },
        '2022': { temp: 30.10, chlorophyll: 0.74, status: 'Optimal', source: 'Satelit Aqua MODIS' },
        '2023': { temp: 29.70, chlorophyll: 0.91, status: 'Optimal', source: 'Satelit Aqua MODIS' },
        '2024': { temp: 30.47, chlorophyll: 0.82, status: 'Optimal', source: 'Satelit Aqua MODIS' },
        '2025': { temp: 30.05, chlorophyll: 1.05, status: 'Optimal', source: 'Satelit Aqua MODIS' },
        '2026': { temp: 29.56, chlorophyll: 0.88, status: 'Optimal', source: 'Satelit Aqua MODIS' }
    };

    const catchDbData = (catchByPeriodData && Object.keys(catchByPeriodData).length > 0) ? catchByPeriodData : CATCH_FALLBACK;
    const oceanDbData = (oceanByYearData && Object.keys(oceanByYearData).length > 0) ? oceanByYearData : OCEAN_FALLBACK;

    /* Fallback hardcoded jika manifest kosong (dev tanpa filesystem) */
    const ZPPI_FALLBACK = {
        '2021': {
            label: 'Tahun 2021',
            seasons: {
                tahunan:    { label: 'Tahunan',           url: '/DatafishingGround/2021/ZPPI2021_FeaturesToJSON.geojson' },
                barat:      { label: 'Musim Barat',       url: '/DatafishingGround/2021/BARAT_2021_FeaturesToJSON.geojson' },
                peralihan1: { label: 'Musim Peralihan I',  url: '/DatafishingGround/2021/PERALIHAN1_2021_FeaturesToJSO.geojson' },
                timur:      { label: 'Musim Timur',        url: '/DatafishingGround/2021/TIMUR2021_FeaturesToJSON.geojson' },
                peralihan2: { label: 'Musim Peralihan II', url: '/DatafishingGround/2021/PERALIHAN2_2021_FeaturesToJSO.geojson' },
            }
        },
        '2022': {
            label: 'Tahun 2022',
            seasons: {
                tahunan:    { label: 'Tahunan',           url: '/DatafishingGround/2022/ZPPI22_FeaturesToJSON.geojson' },
                barat:      { label: 'Musim Barat',       url: '/DatafishingGround/2022/BARAT22_FeaturesToJSON.geojson' },
                peralihan1: { label: 'Musim Peralihan I',  url: '/DatafishingGround/2022/PERALIHAN1_2022_FeaturesToJSO.geojson' },
                timur:      { label: 'Musim Timur',        url: '/DatafishingGround/2022/TIMUR22_FeaturesToJSON.geojson' },
                peralihan2: { label: 'Musim Peralihan II', url: '/DatafishingGround/2022/PERALIHAN2_2022_FeaturesToJSO.geojson' },
            }
        },
        '2023': {
            label: 'Tahun 2023',
            seasons: {
                tahunan:    { label: 'Tahunan',           url: '/DatafishingGround/2023/ZPPI2023_FeaturesToJSON.geojson' },
                barat:      { label: 'Musim Barat',       url: '/DatafishingGround/2023/BARAT23_FeaturesToJSON.geojson' },
                peralihan1: { label: 'Musim Peralihan I',  url: '/DatafishingGround/2023/peralihan1_23_FeaturesToJSON.geojson' },
                timur:      { label: 'Musim Timur',        url: '/DatafishingGround/2023/TIMUR23_FeaturesToJSON.geojson' },
                peralihan2: { label: 'Musim Peralihan II', url: '/DatafishingGround/2023/peralihan2_23_FeaturesToJSON.geojson' },
            }
        },
        '2024': {
            label: 'Tahun 2024',
            seasons: {
                tahunan:    { label: 'Tahunan',           url: '/DatafishingGround/2024/ZPPI_2024_FeaturesToJSON.geojson' },
                barat:      { label: 'Musim Barat',       url: '/DatafishingGround/2024/BARAT_2024_FeaturesToJSON.geojson' },
                peralihan1: { label: 'Musim Peralihan I',  url: '/DatafishingGround/2024/Peralihan1_2024_FeaturesToJSON.geojson' },
                timur:      { label: 'Musim Timur',        url: '/DatafishingGround/2024/TIMUR_2024_FeaturesToJSON.geojson' },
                peralihan2: { label: 'Musim Peralihan II', url: '/DatafishingGround/2024/Peralihan2_2024_FeaturesToJSON.geojson' },
            }
        },
        '2025': {
            label: 'Tahun 2025',
            seasons: {
                tahunan:    { label: 'Tahunan',           url: '/DatafishingGround/2025/ZPPI_2025_FeaturesToJSON.geojson' },
                barat:      { label: 'Musim Barat',       url: '/DatafishingGround/2025/BARAT_2025_FeaturesToJSON.geojson' },
                peralihan1: { label: 'Musim Peralihan I',  url: '/DatafishingGround/2025/Peralihan1_2025_FeaturesToJSON.geojson' },
                timur:      { label: 'Musim Timur',        url: '/DatafishingGround/2025/TIMUR_2025_FeaturesToJSON.geojson' },
                peralihan2: { label: 'Musim Peralihan II', url: '/DatafishingGround/2025/Peralihan2_2025_FeaturesToJSON.geojson' },
            }
        },
        '2026': {
            label: 'Tahun 2026',
            seasons: {
                tahunan:    { label: 'Tahunan',           url: '/DatafishingGround/2026/ZPPI_2026_FeaturesToJSON.geojson' },
                barat:      { label: 'Musim Barat',       url: '/DatafishingGround/2026/BARAT_2026_FeaturesToJSON.geojson' },
                peralihan1: { label: 'Musim Peralihan I',  url: '/DatafishingGround/2026/Peralihan1_2026_FeaturesToJSON.geojson' },
                timur:      { label: 'Musim Timur',        url: '/DatafishingGround/2026/TIMUR_2026_FeaturesToJSON.geojson' },
                peralihan2: { label: 'Musim Peralihan II', url: '/DatafishingGround/2026/Peralihan2_2026_FeaturesToJSON.geojson' },
            }
        }
    };

    const LYZENGA_FALLBACK = {
        '2021': { year: '2021', label: 'Tahun 2021', file: 'lyzenga2021_FeaturesToJSO.geojson', url: '/DataLyzenga/2021/lyzenga2021_FeaturesToJSO.geojson' },
        '2022': { year: '2022', label: 'Tahun 2022', file: 'RasterT_Reclas_FeaturesToJSO.geojson', url: '/DataLyzenga/2022/RasterT_Reclas_FeaturesToJSO.geojson' },
        '2023': { year: '2023', label: 'Tahun 2023', file: 'RasterT_Reclas_FeaturesToJSO.geojson', url: '/DataLyzenga/2023/RasterT_Reclas_FeaturesToJSO.geojson' },
        '2024': { year: '2024', label: 'Tahun 2024', file: 'lyzenga2024_FeaturesToJSO.geojson', url: '/DataLyzenga/2024/lyzenga2024_FeaturesToJSO.geojson' },
        '2025': { year: '2025', label: 'Tahun 2025', file: 'RasterT_Reclas_FeaturesToJSO.geojson', url: '/DataLyzenga/2025/RasterT_Reclas_FeaturesToJSO.geojson' },
        '2026': { year: '2026', label: 'Tahun 2026', file: 'lyzenga2026_FeaturesToJSO.geojson', url: '/DataLyzenga/2026/lyzenga2026_FeaturesToJSO.geojson' },
    };

    const manifest    = (zppiManifest && Object.keys(zppiManifest).length > 0) ? zppiManifest : ZPPI_FALLBACK;
    const lyzManifest = (lyzengaManifest && Object.keys(lyzengaManifest).length > 0) ? lyzengaManifest : LYZENGA_FALLBACK;

    /* Konfigurasi Warna dan Estetika 5 Kelas Hasil Klasifikasi Lyzenga */
    const LYZENGA_CONFIG = {
        1: { color: '#0077B6', border: '#023E8A', fillOpacity: 0.32, label: 'Lautan' },
        2: { color: '#06B6D4', border: '#0891B2', fillOpacity: 0.55, label: 'Terumbu Karang Hidup' },
        3: { color: '#10B981', border: '#059669', fillOpacity: 0.60, label: 'Rubble (Karang Mati)' },
        4: { color: '#F59E0B', border: '#D97706', fillOpacity: 0.65, label: 'Lamun' },
        5: { color: '#E11D48', border: '#BE123C', fillOpacity: 0.70, label: 'Pasir' }
    };

    /* ============================================================
       1. INISIALISASI PETA
    ============================================================ */
    const MAP_CENTER = [-5.8082, 106.2714];
    const MAP_ZOOM   = 13;

    const map = L.map('scomarMap', {
        center       : MAP_CENTER,
        zoom         : MAP_ZOOM,
        zoomControl  : true,
        tap          : true,
        touchZoom    : true,
        dragging     : true,
        tapTolerance : 15,
    });

    /* ============================================================
       2. BASEMAP
    ============================================================ */
    const osmLayer = L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }
    );

    const satelliteLayer = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        {
            attribution: 'Sources: Esri, DigitalGlobe, GeoEye, i-cubed, USDA FSA, USGS, AEX, Getmapping, Aerogrid, IGN, IGP, swisstopo, and the GIS User Community',
            maxZoom: 19
        }
    );

    // Citra satelit sebagai default
    satelliteLayer.addTo(map);

    // Kontrol basemap di topleft (di bawah kontrol zoom) agar sudut bawah bebas untuk legenda
    L.control.layers(
        { 'Citra Satelit': satelliteLayer, 'Peta Jalan (OSM)': osmLayer },
        null,
        { position: 'topleft', collapsed: true }
    ).addTo(map);

    /* ============================================================
       3. HELPER: DMS CONVERTER & BADGE KOORDINAT PUSAT
    ============================================================ */
    function toDMS(deg, isLat) {
        const abs   = Math.abs(deg);
        const d     = Math.floor(abs);
        const mFull = (abs - d) * 60;
        const m     = Math.floor(mFull);
        const s     = Math.round((mFull - m) * 60);
        const dir   = isLat ? (deg >= 0 ? 'LU' : 'LS') : (deg >= 0 ? 'BT' : 'BB');
        return `${d}\u00b0${m}'${s}" ${dir}`;
    }

    function updateCenterBadge() {
        const c   = map.getCenter();
        const el  = document.getElementById('mapCenterCoords');
        if (el) {
            el.innerHTML =
                '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">' +
                '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>' +
                '<circle cx="12" cy="10" r="3"/></svg>' +
                ' ' + toDMS(c.lat, true) + ', ' + toDMS(c.lng, false);
        }
    }
    map.on('moveend', updateCenterBadge);
    updateCenterBadge();

    /* ============================================================
       4. IKON MARKER ZPPI (PIN TEARDROP TURQUOISE RESMI)
    ============================================================ */
    const zppiPinIcon = L.divIcon({
        className: 'zppi-custom-marker',
        html: `
            <svg width="44" height="54" viewBox="0 0 50 60"
                 xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M25 2 C12.3 2 2 12.3 2 25
                       C2 41 25 58 25 58
                       C25 58 48 41 48 25
                       C48 12.3 37.7 2 25 2 Z"
                    fill="#176B87"
                    stroke="#FFFFFF"
                    stroke-width="2.5"
                />
                <text
                    x="25"
                    y="29"
                    text-anchor="middle"
                    dominant-baseline="middle"
                    font-size="12"
                    font-weight="bold"
                    fill="#FFFFFF">
                    ZPPI
                </text>
            </svg>
        `,
        iconSize: [44, 54],
        iconAnchor: [22, 54],
        popupAnchor: [0, -52]
    });

    /* ============================================================
       5. LEGENDA LYZENGA TERUMBU KARANG (BOTTOM-LEFT)
    ============================================================ */
    const lyzengaLegendControl = L.control({ position: 'bottomleft' });
    let lyzengaLegendDiv = null;

    lyzengaLegendControl.onAdd = function () {
        lyzengaLegendDiv = L.DomUtil.create('div', 'scomar-control-panel');
        lyzengaLegendDiv.id = 'lyzengaLegendContainer';
        lyzengaLegendDiv.style.cssText = `
            padding: 9px 12px;
            font-size: 11px;
            line-height: 1.5;
            min-width: 200px;
            max-width: 230px;
            box-sizing: border-box;
        `;
        updateLyzengaLegend('2021', []);
        L.DomEvent.disableClickPropagation(lyzengaLegendDiv);
        L.DomEvent.disableScrollPropagation(lyzengaLegendDiv);
        return lyzengaLegendDiv;
    };

    function updateLyzengaLegend(year, classStats) {
        if (!lyzengaLegendDiv) return;
        let itemsHtml = '';
        if (classStats && classStats.length > 0) {
            classStats.forEach(function (c) {
                itemsHtml += `
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; margin-bottom:3px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="display:inline-block; width:11px; height:11px; border-radius:2px; background:${c.color}; border:1px solid ${c.border}; flex-shrink:0;"></span>
                            <span style="font-weight:600; font-size:11px;">Kelas ${c.gridcode}</span>
                        </div>
                        <span style="font-family:monospace; font-size:10.5px; color:#64748B;">${c.areaM2Formatted} m²</span>
                    </div>
                `;
            });
        } else {
            itemsHtml = '<div style="color:#94A3B8; font-size:11px;">Memuat rincian kelas...</div>';
        }

        lyzengaLegendDiv.innerHTML = `
            <div style="font-size:10px; font-weight:800; color:#0D9488; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:5px; border-bottom:1px solid rgba(165,184,208,0.3); padding-bottom:3px;">
                Sebaran Terumbu Karang \u2014 Lyzenga ${year}
            </div>
            ${itemsHtml}
        `;
    }

    lyzengaLegendControl.addTo(map);

    /* ============================================================
       6. LEGENDA ZPPI (BOTTOM-RIGHT)
    ============================================================ */
    const zppiLegendControl = L.control({ position: 'bottomright' });
    let zppiLegendDiv = null;

    zppiLegendControl.onAdd = function () {
        zppiLegendDiv = L.DomUtil.create('div', 'scomar-control-panel');
        zppiLegendDiv.id = 'zppiLegendContainer';
        zppiLegendDiv.style.cssText = `
            padding: 9px 12px;
            font-size: 11px;
            line-height: 1.5;
            min-width: 175px;
            box-sizing: border-box;
        `;
        updateZppiLegend('--', '', 0);
        L.DomEvent.disableClickPropagation(zppiLegendDiv);
        L.DomEvent.disableScrollPropagation(zppiLegendDiv);
        return zppiLegendDiv;
    };

    function updateZppiLegend(year, seasonLabel, count) {
        if (!zppiLegendDiv) return;
        zppiLegendDiv.innerHTML = `
            <div style="font-size:10px; font-weight:800; color:#176B87; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:5px; border-bottom:1px solid rgba(165,184,208,0.3); padding-bottom:3px;">
                LEGENDA ZPPI
            </div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">
                <svg width="18" height="22" viewBox="0 0 50 60" style="flex-shrink:0;">
                    <path d="M25 2 C12.3 2 2 12.3 2 25 C2 41 25 58 25 58 C25 58 48 41 48 25 C48 12.3 37.7 2 25 2 Z" fill="#176B87" stroke="#FFFFFF" stroke-width="3"/>
                    <text x="25" y="29" text-anchor="middle" dominant-baseline="middle" font-size="12" font-weight="bold" fill="#FFFFFF">ZPPI</text>
                </svg>
                <div style="line-height:1.2;">
                    <div style="font-size:11.5px; font-weight:700;">${year !== '--' ? `ZPPI ${year}` : 'ZPPI'}</div>
                    <div style="font-size:10.5px; color:#64748B;">${seasonLabel || '-'}</div>
                </div>
            </div>
            <div style="background:rgba(165,184,208,0.12); border-radius:4px; padding:3px 6px; font-size:10.5px; display:flex; justify-content:space-between; align-items:center;">
                <span style="color:#64748B;">Sebaran Titik:</span>
                <strong style="color:#176B87;">${count} Titik</strong>
            </div>
        `;
    }

    zppiLegendControl.addTo(map);

    /* ============================================================
       7. OVERLAY LOADING INDIKATOR
    ============================================================ */
    function showMapLoading(show, text) {
        const overlay = document.getElementById('mapLoadingOverlay');
        const textEl  = document.getElementById('mapLoadingText');
        if (overlay) overlay.style.display = show ? 'flex' : 'none';
        if (textEl && text) textEl.textContent = text;
    }

    /* ============================================================
       8. LAYER ZPPI (STATE & LOAD FUNCTION)
    ============================================================ */
    let activeZppiLayer   = null;
    let zppiRequestId     = 0;
    let currentYear       = '2021';
    let currentSeasonKey  = 'tahunan';
    let currentSeason     = 'Tahunan';
    let currentZppiCount  = 0;
    let isZppiVisible     = true;

    /* ============================================================
       FUNGSI PEMBARUAN OTOMATIS 4 KARTU STATISTIK SCOMAR
    ============================================================ */
    function updateStatCards(year, seasonKey, zppiCount, seasonLabel) {
        currentYear        = year;
        currentSeasonKey   = seasonKey;
        currentSeason      = seasonLabel;
        currentZppiCount   = zppiCount;

        // 1. Total Hasil Tangkapan (kg) — dari database catch_data
        const catchEl     = document.getElementById('statCatchValue');
        const catchUnitEl = document.getElementById('statCatchUnit');
        const catchDescEl = document.getElementById('statCatchDesc');
        const catchPillEl = document.getElementById('statCatchPill');

        const yearCatch = catchDbData[year];
        let catchVal = null;
        if (yearCatch) {
            if (seasonKey === 'tahunan' || !seasonKey) {
                catchVal = yearCatch.total;
            } else if (yearCatch.seasons && yearCatch.seasons[seasonKey] !== undefined) {
                catchVal = yearCatch.seasons[seasonKey];
            }
        }

        if (catchEl) {
            if (catchVal !== null && catchVal !== undefined) {
                catchEl.textContent = catchVal.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
                if (catchUnitEl) { catchUnitEl.textContent = 'kg'; catchUnitEl.style.display = 'inline'; }
                if (catchDescEl) {
                    catchDescEl.textContent = (seasonKey === 'tahunan' || !seasonKey)
                        ? `Total Produksi Scomberomorus (${year})`
                        : `Produksi ${seasonLabel || seasonKey} (${year})`;
                }
                if (catchPillEl) catchPillEl.textContent = `Data Tangkapan ${year}`;
            } else {
                catchEl.textContent = '—';
                if (catchUnitEl) { catchUnitEl.style.display = 'none'; }
                if (catchDescEl) catchDescEl.textContent = `Data tidak tersedia (${year})`;
                if (catchPillEl) catchPillEl.textContent = 'Data belum tercatat';
            }
        }

        // 2. Suhu Permukaan Laut (SPL) (°C) — dari database oceanographic_parameters (Aqua MODIS)
        const tempEl     = document.getElementById('statTempValue');
        const tempUnitEl = document.getElementById('statTempUnit');
        const tempDescEl = document.getElementById('statTempDesc');
        const tempPillEl = document.getElementById('statTempPill');

        const oceanData = oceanDbData[year];
        if (tempEl) {
            if (oceanData && oceanData.temp !== undefined && oceanData.temp !== null) {
                tempEl.textContent = Number(oceanData.temp).toFixed(2);
                if (tempUnitEl) { tempUnitEl.textContent = '°C'; tempUnitEl.style.display = 'inline'; }
                if (tempDescEl) tempDescEl.textContent = `Rata-rata Pengukuran (${year})`;
                if (tempPillEl) tempPillEl.textContent = `Aqua MODIS: ${oceanData.status || 'Optimal'}`;
            } else {
                tempEl.textContent = '—';
                if (tempUnitEl) { tempUnitEl.style.display = 'none'; }
                if (tempDescEl) tempDescEl.textContent = `Data tidak tersedia (${year})`;
                if (tempPillEl) tempPillEl.textContent = 'Baku Mutu: 28.0 – 30.5 °C';
            }
        }

        // 3. Konsentrasi Klorofil-a (mg/m³) — dari database oceanographic_parameters (Aqua MODIS)
        const chlaEl     = document.getElementById('statChlaValue');
        const chlaUnitEl = document.getElementById('statChlaUnit');
        const chlaDescEl = document.getElementById('statChlaDesc');
        const chlaPillEl = document.getElementById('statChlaPill');

        if (chlaEl) {
            if (oceanData && oceanData.chlorophyll !== undefined && oceanData.chlorophyll !== null) {
                chlaEl.textContent = Number(oceanData.chlorophyll).toFixed(2);
                if (chlaUnitEl) { chlaUnitEl.textContent = 'mg/m³'; chlaUnitEl.style.display = 'inline'; }
                if (chlaDescEl) chlaDescEl.textContent = `Satelit Aqua MODIS (${year})`;
                if (chlaPillEl) chlaPillEl.textContent = `Rentang Acuan: 0.10 – 5.00 mg/m³`;
            } else {
                chlaEl.textContent = '—';
                if (chlaUnitEl) { chlaUnitEl.style.display = 'none'; }
                if (chlaDescEl) chlaDescEl.textContent = `Data tidak tersedia (${year})`;
                if (chlaPillEl) chlaPillEl.textContent = 'Rentang Acuan: 0.10 – 5.00 mg/m³';
            }
        }

        // 4. Lokasi ZPPI (titik) — dari data GeoJSON spasial yang sedang aktif di peta
        const zppiEl     = document.getElementById('statZppiValue');
        const zppiUnitEl = document.getElementById('statZppiUnit');
        const zppiDescEl = document.getElementById('statZppiDesc');
        const zppiPillEl = document.getElementById('statZppiPill');

        if (zppiEl) {
            const countNum = parseInt(zppiCount !== undefined ? zppiCount : 0, 10);
            zppiEl.textContent = countNum.toLocaleString('id-ID');
            if (zppiUnitEl) { zppiUnitEl.textContent = 'Titik'; zppiUnitEl.style.display = 'inline'; }
            if (zppiDescEl) {
                zppiDescEl.textContent = countNum > 0
                    ? `Zona Tangkap Terpetakan (${year} • ${seasonLabel || seasonKey})`
                    : `Tidak ada titik terpetakan (${year})`;
            }
            if (zppiPillEl) zppiPillEl.textContent = `${countNum} Titik Aktif di WebGIS`;
        }
    }

    /* ============================================================
       ANALISIS SPASIAL JARAK & REKOMENDASI ALAT TANGKAP SCOMAR
    ============================================================ */
    function computeZppiCoralDistance(lat, lng, year) {
        let minDistCoralDegSq = Infinity;
        let minDistRubbleDegSq = Infinity;
        const cosLat = Math.cos(lat * Math.PI / 180);

        const spatialData = lyzengaCoralSpatialCache[year] || lyzengaCoralSpatialCache['2021'];
        if (spatialData && spatialData.coral && spatialData.coral.length > 0) {
            const coral = spatialData.coral;
            for (let i = 0; i < coral.length; i++) {
                const dLat = coral[i][1] - lat;
                const dLon = (coral[i][0] - lng) * cosLat;
                const d2 = dLat * dLat + dLon * dLon;
                if (d2 < minDistCoralDegSq) minDistCoralDegSq = d2;
            }
            if (spatialData.rubble && spatialData.rubble.length > 0) {
                const rubble = spatialData.rubble;
                for (let i = 0; i < rubble.length; i++) {
                    const dLat = rubble[i][1] - lat;
                    const dLon = (rubble[i][0] - lng) * cosLat;
                    const d2 = dLat * dLat + dLon * dLon;
                    if (d2 < minDistRubbleDegSq) minDistRubbleDegSq = d2;
                }
            }
        } else if (Array.isArray(coralReefsDb) && coralReefsDb.length > 0) {
            coralReefsDb.forEach(function (cr) {
                const dLat = cr.latitude - lat;
                const dLon = (cr.longitude - lng) * cosLat;
                const d2 = dLat * dLat + dLon * dLon;
                if (d2 < minDistCoralDegSq) minDistCoralDegSq = d2;
            });
        }

        // 1 derajat lintang ~ 111.139 meter
        const distCoralMeters = Math.round(Math.sqrt(minDistCoralDegSq) * 111139);
        const distRubbleMeters = isFinite(minDistRubbleDegSq) ? Math.round(Math.sqrt(minDistRubbleDegSq) * 111139) : Infinity;

        let closestClass = 'Terumbu Karang Hidup (Lyzenga Kelas 2)';
        let closestGridcode = 2;

        if (distRubbleMeters < distCoralMeters) {
            closestClass = 'Rubble / Karang Mati (Lyzenga Kelas 3)';
            closestGridcode = 3;
        }

        let zoneTitle = '';
        let zoneColor = '';
        let zoneBg = '';
        let zoneBorder = '';
        let zoneDesc = '';
        let isNearReef = false;

        if (distCoralMeters < 500) {
            zoneTitle = 'Zona Penyangga Inti Karang (< 500 m)';
            zoneColor = '#DC2626';
            zoneBg = 'rgba(220, 38, 38, 0.09)';
            zoneBorder = 'rgba(220, 38, 38, 0.28)';
            zoneDesc = 'Sensitivitas Ekologis Tinggi — Sangat dekat dengan koloni karang hidup. Sangat berisiko tinggi bila ada kontak fisik alat tangkap dengan substrat dasar.';
            isNearReef = true;
        } else if (distCoralMeters <= 1500) {
            zoneTitle = 'Zona Penyangga Terumbu Karang (500–1.500 m)';
            zoneColor = '#D97706';
            zoneBg = 'rgba(217, 119, 6, 0.09)';
            zoneBorder = 'rgba(217, 119, 6, 0.28)';
            zoneDesc = 'Sensitivitas Menengah — Koridor ruaya ikan pelagis di sekitar gugusan terumbu karang. Wajib pengaturan kedalaman operasi alat agar tidak menyentuh substrat.';
            isNearReef = true;
        } else {
            zoneTitle = 'Zona Perairan Terbuka / Pelagis Lepas (> 1,5 km)';
            zoneColor = '#0D9488';
            zoneBg = 'rgba(13, 148, 136, 0.09)';
            zoneBorder = 'rgba(13, 148, 136, 0.28)';
            zoneDesc = 'Sensitivitas Rendah — Berada di kolom air bebas di luar jangkauan ekosistem bentik karang. Risiko kontak fisik dengan karang sangat minimal.';
            isNearReef = false;
        }

        const distFormatted = distCoralMeters < 1000
            ? (distCoralMeters + ' m')
            : ((distCoralMeters / 1000).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' km (' + distCoralMeters.toLocaleString('id-ID') + ' m)');

        return {
            distCoralMeters: distCoralMeters,
            distRubbleMeters: distRubbleMeters,
            distFormatted: distFormatted,
            closestClass: closestClass,
            closestGridcode: closestGridcode,
            zoneTitle: zoneTitle,
            zoneColor: zoneColor,
            zoneBg: zoneBg,
            zoneBorder: zoneBorder,
            zoneDesc: zoneDesc,
            isNearReef: isNearReef
        };
    }

    function generateGearRecommendations(analysis) {
        const isNear = analysis.isNearReef;
        const distText = analysis.distFormatted;

        return [
            {
                key: 'handline',
                name: 'Pancing Ulur (Handline)',
                statusLabel: 'Direkomendasikan',
                statusClass: 'szp-badge-rec',
                selectivityScore: 'Tinggi (Target Tertentu)',
                impactLevel: 'Rendah (Ramah Habitat)',
                reason: 'Alat tangkap pasif yang sangat selektif ukuran dan spesies (khususnya ikan pelagis target seperti Tenggiri / Scomberomorus commerson) tanpa menghasilkan tangkapan sampingan (bycatch) masif. Tidak merusak struktur koloni terumbu karang jika dioperasikan pada kolom air.',
                usageNote: isNear
                    ? ('Jarak ke karang hidup ' + distText + '. Wajib dioperasikan melayang di kolom air (midwater drift). Dilarang menjatuhkan pemberat timah tenggelam menyentuh dasar karang untuk mencegah sangkutan (snagging) dan mematahkan koloni karang. Disarankan menggunakan circle hook.')
                    : ('Jarak ke karang hidup ' + distText + ' (perairan terbuka). Pengoperasian bebas pada kolom air lapisan ruaya ikan target. Risiko kontak fisik dengan substrat dasar sangat minimal.'),
                legalNote: 'Perlu verifikasi regulasi dan zonasi'
            },
            {
                key: 'troll',
                name: 'Pancing Tonda (Troll Line)',
                statusLabel: 'Direkomendasikan',
                statusClass: 'szp-badge-rec',
                selectivityScore: 'Sangat Tinggi (Permukaan)',
                impactLevel: 'Sangat Rendah (Bebas Kontak Dasar)',
                reason: 'Alat tangkap aktif permukaan yang ditarik perahu (kecepatan 3–5 knot). Sangat efektif dan selektif untuk pemangsa pelagis cepat di lapisan atas (epipelagic). Tidak ada interaksi maupun sentuhan fisik dengan substrat dasar laut.',
                usageNote: isNear
                    ? ('Jarak ke karang hidup ' + distText + '. Waspadai alur navigasi perahu dan draft kedalaman lunas/mesin perahu saat bermanuver agar tidak mengikis puncak terumbu karang dangkal (reef crest). Jaga tali pancing tetap melayang di permukaan.')
                    : ('Jarak ke karang hidup ' + distText + '. Sangat optimal ditarik melintasi batas gradien suhu permukaan laut (SPL) dan front klorofil-a perairan terbuka. Operasional aman dari bahaya kandas.'),
                legalNote: 'Perlu verifikasi regulasi dan zonasi'
            },
            {
                key: 'longline',
                name: 'Rawai (Longline)',
                statusLabel: 'Direkomendasikan Bersyarat',
                statusClass: 'szp-badge-cond',
                selectivityScore: 'Sedang – Tinggi',
                impactLevel: isNear ? 'Tinggi (Bila Rawai Dasar) / Rendah (Rawai Apung)' : 'Rendah - Sedang',
                reason: 'Memiliki kapasitas tangkap tinggi untuk ikan pelagis besar. Namun, memiliki risiko bentik signifikan bila tali utama atau mata pancing tersangkut di koloni karang (ghost fishing, patahnya karang bercabang, atau melilit megafauna laut).',
                usageNote: isNear
                    ? ('Jarak ke karang hidup ' + distText + '. DILARANG KERAS menggunakan rawai dasar (bottom set longline). Wajib disetel sebagai Rawai Hanyut Melayang (Drifting Pelagic Longline) dengan pelampung gantung yang diatur kedalamannya aman jauh di atas rataan karang. Hindari setting tali di malam hari di dekat karang.')
                    : ('Jarak ke karang hidup ' + distText + '. Dapat dioperasikan sebagai rawai hanyut pelagis dengan pengawasan rutin koordinat hanyut (drift trajectory) agar tali utama tidak terbawa arus ke arah kompleks terumbu karang.'),
                legalNote: 'Perlu verifikasi regulasi dan zonasi'
            }
        ];
    }

    function createZppiRecommendationPopup(info) {
        const analysis = computeZppiCoralDistance(info.lat, info.lng, info.year);
        const gears = generateGearRecommendations(analysis);

        let tabsHtml = '';
        let panesHtml = '';

        gears.forEach(function (g, idx) {
            const isActive = (idx === 0);
            tabsHtml += '<button type="button" class="szp-tab-btn ' + (isActive ? 'active' : '') + '" onclick="scomarSelectGearTab(this, \'' + g.key + '\')">' +
                '<span>' + g.name.split(' (')[0] + '</span>' +
            '</button>';

            panesHtml += '<div class="szp-gear-pane pane-' + g.key + '" style="display: ' + (isActive ? 'block' : 'none') + ';">' +
                '<div class="szp-gear-card">' +
                    '<div class="szp-gear-top">' +
                        '<span class="szp-gear-name">' + g.name + '</span>' +
                        '<span class="' + g.statusClass + '">' + g.statusLabel + '</span>' +
                    '</div>' +
                    '<div class="szp-spec-grid">' +
                        '<div class="szp-spec-item">' +
                            '<span class="szp-spec-label">Selektivitas:</span>' +
                            '<span class="szp-spec-val" style="color:#059669;">' + g.selectivityScore + '</span>' +
                        '</div>' +
                        '<div class="szp-spec-item">' +
                            '<span class="szp-spec-label">Dampak Ekologis:</span>' +
                            '<span class="szp-spec-val">' + g.impactLevel + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="szp-detail-block">' +
                        '<div class="szp-detail-label">Alasan Rekomendasi (Selektivitas & Ekologis)</div>' +
                        '<div class="szp-detail-text">' + g.reason + '</div>' +
                    '</div>' +
                    '<div class="szp-detail-block" style="background:rgba(165,184,208,0.08); border-radius:6px; padding:6px 8px; margin-top:5px;">' +
                        '<div class="szp-detail-label" style="color:#0284c7;">Catatan Penggunaan & Mitigasi Risiko</div>' +
                        '<div class="szp-detail-text">' + g.usageNote + '</div>' +
                    '</div>' +
                    '<div class="szp-gear-legal-badge">' +
                        '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                            '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>' +
                            '<line x1="12" y1="9" x2="12" y2="13"/>' +
                            '<line x1="12" y1="17" x2="12.01" y2="17"/>' +
                        '</svg>' +
                        '<span>' + g.legalNote + '</span>' +
                    '</div>' +
                '</div>' +
            '</div>';
        });

        const contourHtml = (info.contourVal !== null && info.contourVal !== undefined && info.contourVal !== '-')
            ? ('<div class="szp-stat-row"><span style="color:var(--text-secondary);">Kontur Kedalaman:</span><strong>' + info.contourVal + ' m</strong></div>')
            : '';

        return '<div class="scomar-zppi-popup">' +
            '<!-- Header Titik ZPPI -->' +
            '<div class="szp-header">' +
                '<div class="szp-badge-row">' +
                    '<span class="szp-tag-zppi">ZPPI ' + info.year + ' &bull; Titik #' + info.idx + '</span>' +
                    '<span class="szp-tag-season">' + info.seasonLabel + '</span>' +
                '</div>' +
                '<div class="szp-coords-box">' +
                    '<div class="szp-coords-grid">' +
                        '<div><span style="color:var(--text-muted);">Lat:</span> ' + info.lat.toFixed(5) + '&deg;</div>' +
                        '<div><span style="color:var(--text-muted);">Lng:</span> ' + info.lng.toFixed(5) + '&deg;</div>' +
                    '</div>' +
                    '<div style="color:var(--text-secondary); font-size:10px; margin-top:3px;">' +
                        info.dmsLat + ', ' + info.dmsLng +
                    '</div>' +
                '</div>' +
            '</div>' +

            '<!-- Analisis Spasial Jarak Ekosistem Terumbu Karang -->' +
            '<div class="szp-spatial-box" style="border-color:' + analysis.zoneBorder + '; background:' + analysis.zoneBg + ';">' +
                '<div class="szp-spatial-title" style="color:' + analysis.zoneColor + ';">' +
                    '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">' +
                        '<circle cx="12" cy="12" r="10"/>' +
                        '<path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>' +
                        '<path d="M2 12h20"/>' +
                    '</svg>' +
                    '<span>Analisis Spasial Terumbu Karang</span>' +
                '</div>' +
                '<div class="szp-stat-row">' +
                    '<span style="color:var(--text-secondary);">Jarak ke Terumbu Karang:</span>' +
                    '<span class="szp-dist-val" style="color:' + analysis.zoneColor + ';">' + analysis.distFormatted + '</span>' +
                '</div>' +
                '<div class="szp-stat-row">' +
                    '<span style="color:var(--text-secondary);">Substrat Terdekat:</span>' +
                    '<strong style="font-size:11px;">' + analysis.closestClass + '</strong>' +
                '</div>' +
                contourHtml +
                '<div style="margin-top:6px; padding-top:5px; border-top:1px dashed ' + analysis.zoneBorder + ';">' +
                    '<div style="font-size:10px; font-weight:800; color:' + analysis.zoneColor + '; text-transform:uppercase; margin-bottom:2px;">' +
                        analysis.zoneTitle +
                    '</div>' +
                    '<div style="font-size:10.5px; color:var(--text-secondary); line-height:1.35;">' +
                        analysis.zoneDesc +
                    '</div>' +
                '</div>' +
            '</div>' +

            '<!-- Rekomendasi 3 Alat Tangkap SCOMAR -->' +
            '<div class="szp-gears-section">' +
                '<div class="szp-gears-header">' +
                    '<span>Rekomendasi Alat Tangkap</span>' +
                    '<button type="button" class="szp-toggle-btn" data-all="false" onclick="scomarToggleAllGears(this)">' +
                        'Lihat Semua (3)' +
                    '</button>' +
                '</div>' +
                '<div class="szp-tab-bar">' +
                    tabsHtml +
                '</div>' +
                '<div class="szp-panes-wrapper">' +
                    panesHtml +
                '</div>' +
            '</div>' +

            '<!-- Keterangan Regulasi & Kepatuhan Legalitas -->' +
            '<div class="szp-reg-alert">' +
                '<div style="display:flex; align-items:flex-start; gap:5px;">' +
                    '<span style="display:inline-flex; align-items:center; color:#D97706;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>' +
                    '<div>' +
                        '<strong>Ketentuan Regulasi & Zonasi:</strong> Status rekomendasi berbasis aspek selektivitas dan risiko ekologis. Nelayan/pengguna wajib melakukan <u>verifikasi regulasi dan zonasi</u> pada otoritas kawasan setempat sebelum beroperasi.' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    window.scomarSelectGearTab = function (btn, gearKey) {
        const root = btn.closest('.scomar-zppi-popup');
        if (!root) return;
        root.querySelectorAll('.szp-tab-btn').forEach(function (b) { b.classList.remove('active'); });
        root.querySelectorAll('.szp-gear-pane').forEach(function (p) { p.style.display = 'none'; });
        btn.classList.add('active');
        const target = root.querySelector('.pane-' + gearKey);
        if (target) target.style.display = 'block';
    };

    window.scomarToggleAllGears = function (btn) {
        const root = btn.closest('.scomar-zppi-popup');
        if (!root) return;
        const panes = root.querySelectorAll('.szp-gear-pane');
        const tabs  = root.querySelector('.szp-tab-bar');
        const isAll = (btn.getAttribute('data-all') === 'true');
        if (isAll) {
            btn.setAttribute('data-all', 'false');
            btn.textContent = 'Lihat Semua (3)';
            if (tabs) tabs.style.display = 'flex';
            panes.forEach(function (p, idx) {
                p.style.display = (idx === 0) ? 'block' : 'none';
            });
            const firstTab = root.querySelector('.szp-tab-btn');
            if (firstTab) firstTab.classList.add('active');
        } else {
            btn.setAttribute('data-all', 'true');
            btn.textContent = 'Mode Tab';
            if (tabs) tabs.style.display = 'none';
            panes.forEach(function (p) { p.style.display = 'block'; });
        }
    };

    function loadZppi(year, seasonKey) {
        const yearData = manifest[year];
        if (!yearData) {
            console.error(`[ZPPI Filter] Data tahun ${year} tidak ditemukan dalam manifest.`);
            alert(`Data ZPPI untuk tahun ${year} tidak ditemukan.`);
            return;
        }
        const seasonData = yearData.seasons ? yearData.seasons[seasonKey] : null;
        if (!seasonData) {
            console.error(`[ZPPI Filter] Opsi musim/tahunan '${seasonKey}' tidak ditemukan untuk tahun ${year}.`);
            alert(`Data ZPPI opsi '${seasonKey}' untuk tahun ${year} tidak ditemukan.`);
            return;
        }

        const url         = seasonData.url;
        const seasonLabel = seasonData.label;
        const requestId   = ++zppiRequestId;

        currentYear   = year;
        currentSeason = seasonLabel;

        console.log(`[ZPPI Filter] Memuat Data: Tahun = ${year}, Opsi = ${seasonLabel} (${seasonKey}), URL = ${url}`);
        showMapLoading(true, `Memuat ZPPI ${year} \u2014 ${seasonLabel}...`);

        fetch(url)
            .then(function (resp) {
                console.log(`[ZPPI Filter] Respons HTTP: Status = ${resp.status} ${resp.statusText}, URL = ${url}`);
                if (!resp.ok) throw new Error('File tidak ditemukan di server: ' + url + ' (HTTP ' + resp.status + ')');
                return resp.json();
            })
            .then(function (data) {
                if (requestId !== zppiRequestId) return;
                if (!Array.isArray(data.features)) throw new Error('Format GeoJSON tidak valid (features bukan array)');
                console.log(`[ZPPI Filter] GeoJSON Berhasil Diparsing: Tahun = ${year}, Opsi = ${seasonLabel}, Jumlah Fitur = ${data.features.length}`);

                const zppiMarkers = [];
                const newLayer    = L.layerGroup();
                let   ptIndex     = 0;

                data.features.forEach(function (feature) {
                    const geom   = feature.geometry || {};
                    const props  = feature.properties || {};
                    const type   = geom.type;
                    const coords = geom.coordinates || [];
                    const contourVal = props.Contour_1 ?? props.Contour ?? null;

                    function addZppiMarker(lngLat) {
                        const lat = lngLat[1];
                        const lng = lngLat[0];
                        if (isNaN(lat) || isNaN(lng)) return;

                        ptIndex++;
                        const idx    = ptIndex;
                        const dmsLat = toDMS(lat, true);
                        const dmsLng = toDMS(lng, false);

                        const marker = L.marker([lat, lng], { icon: zppiPinIcon });
                        marker.bindPopup(function () {
                            return createZppiRecommendationPopup({
                                idx: idx,
                                year: year,
                                seasonLabel: seasonLabel,
                                lat: lat,
                                lng: lng,
                                dmsLat: dmsLat,
                                dmsLng: dmsLng,
                                contourVal: contourVal
                            });
                        }, {
                            maxWidth: 370,
                            minWidth: 310,
                            className: 'scomar-custom-popup scomar-zppi-rec-popup'
                        });
                        newLayer.addLayer(marker);

                        zppiMarkers.push({
                            id: idx,
                            year: year,
                            season: seasonLabel,
                            lat: parseFloat(lat.toFixed(6)),
                            lng: parseFloat(lng.toFixed(6)),
                            dmsLat: dmsLat,
                            dmsLng: dmsLng,
                            contour: contourVal,
                            geomType: type || 'Point',
                            markerIndex: idx
                        });
                    }

                    if (type === 'Point') {
                        addZppiMarker(coords);
                    } else if (type === 'MultiPoint') {
                        coords.forEach(function (c) { addZppiMarker(c); });
                    }
                });

                if (activeZppiLayer) {
                    map.removeLayer(activeZppiLayer);
                }
                activeZppiLayer = newLayer;
                if (isZppiVisible) {
                    activeZppiLayer.addTo(map);
                }

                updateZppiLegend(year, seasonLabel, zppiMarkers.length);
                updateStatCards(year, seasonKey, zppiMarkers.length, seasonLabel);

                window.dispatchEvent(new CustomEvent('zppi-data-loaded', {
                    detail: {
                        points: zppiMarkers,
                        count: zppiMarkers.length,
                        year: year,
                        season: seasonLabel
                    }
                }));

                showMapLoading(false);
            })
            .catch(function (err) {
                showMapLoading(false);
                console.error('[ZPPI Filter] Gagal memuat ZPPI:', err);
                updateStatCards(year, seasonKey, 0, seasonLabel);
                alert(`Gagal memuat data ZPPI ${year} (${seasonLabel}): ${err.message}`);
            });
    }

    /* ============================================================
       9. LAYER LYZENGA TERUMBU KARANG (STATE, CACHE, LOAD FUNCTION)
    ============================================================ */
    const lyzengaCache = {};
    let activeLyzengaLayer = null;
    let lyzengaRequestId   = 0;
    let currentLyzengaYear = '2021';
    let isLyzengaVisible   = true;
    const lyzengaLayerRefs = {};

    function loadLyzenga(year) {
        const item = lyzManifest[year];
        if (!item) {
            showMapLoading(false);
            alert(`Data Terumbu Karang Lyzenga untuk tahun ${year} belum tersedia.`);
            return;
        }

        currentLyzengaYear = year;
        const requestId    = ++lyzengaRequestId;
        const url          = item.url;
        const sizeInfo     = item.size_mb ? ` (${item.size_mb} MB)` : '';

        showMapLoading(true, `Memuat Layer Terumbu Karang Lyzenga ${year}${sizeInfo}...`);

        function renderLyzengaData(data) {
            if (requestId !== lyzengaRequestId) return;
            if (!Array.isArray(data.features)) throw new Error('Format GeoJSON Lyzenga tidak valid');

            // Ekstraksi spasial koordinat batas terumbu karang (gridcode 2: Terumbu Karang Hidup, gridcode 3: Rubble)
            const extractedCoral = [];
            const extractedRubble = [];
            data.features.forEach(function (f) {
                const gc = (f.properties && f.properties.gridcode) ? f.properties.gridcode : 0;
                if (gc !== 2 && gc !== 3) return;
                const geom = f.geometry || {};
                const coords = geom.coordinates || [];
                function harvestRing(ring) {
                    if (!Array.isArray(ring)) return;
                    const step = ring.length > 500 ? 5 : (ring.length > 80 ? 2 : 1);
                    for (let i = 0; i < ring.length; i += step) {
                        const p = ring[i];
                        if (Array.isArray(p) && p.length >= 2) {
                            if (gc === 2) extractedCoral.push([p[0], p[1]]);
                            else if (gc === 3) extractedRubble.push([p[0], p[1]]);
                        }
                    }
                }
                if (geom.type === 'MultiPolygon') {
                    coords.forEach(function (poly) {
                        if (Array.isArray(poly)) poly.forEach(harvestRing);
                    });
                } else if (geom.type === 'Polygon') {
                    coords.forEach(harvestRing);
                }
            });
            lyzengaCoralSpatialCache[year] = {
                coral: extractedCoral,
                rubble: extractedRubble,
                year: year
            };

            const classStats = [];
            for (const k in lyzengaLayerRefs) delete lyzengaLayerRefs[k];

            const newLyzLayer = L.geoJSON(data, {
                style: function (feature) {
                    const props = feature.properties || {};
                    const gc    = props.gridcode || 1;
                    const cfg   = LYZENGA_CONFIG[gc] || LYZENGA_CONFIG[1];
                    return {
                        fillColor   : cfg.color,
                        fillOpacity : cfg.fillOpacity,
                        color       : cfg.border,
                        weight      : 1.5,
                        opacity     : 0.9,
                        lineCap     : 'round',
                        lineJoin    : 'round'
                    };
                },
                onEachFeature: function (feature, layer) {
                    const props = feature.properties || {};
                    const gc    = props.gridcode || 1;
                    const cfg   = LYZENGA_CONFIG[gc] || LYZENGA_CONFIG[1];

                    const areaM2  = props.Shape_Area ? parseFloat(props.Shape_Area) : 0;
                    const lengthM = props.Shape_Length ? parseFloat(props.Shape_Length) : 0;

                    const areaM2Str = Math.round(areaM2).toLocaleString('id-ID');
                    const lengthStr = Math.round(lengthM).toLocaleString('id-ID');

                    lyzengaLayerRefs[gc] = layer;

                    classStats.push({
                        gridcode       : gc,
                        label          : cfg.label,
                        color          : cfg.color,
                        border         : cfg.border,
                        areaM2Formatted: areaM2Str,
                        lengthFormatted: lengthStr
                    });

                    // Efek hover interaktif poligon
                    layer.on({
                        mouseover: function (e) {
                            const l = e.target;
                            l.setStyle({
                                weight     : 2.8,
                                fillOpacity: Math.min(cfg.fillOpacity + 0.22, 0.95)
                            });
                        },
                        mouseout: function (e) {
                            newLyzLayer.resetStyle(e.target);
                        }
                    });

                    // Pop-up informasi saat poligon diklik (tanpa asumsi ekologis, tanpa satuan ha yang belum tervalidasi)
                    layer.bindPopup(
                        '<div style="font-family:inherit; min-width:210px; font-size:12px; line-height:1.6;">' +
                            '<div style="font-size:10.5px; font-weight:800; color:#0D9488; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:5px; border-bottom:1px solid rgba(165,184,208,0.3); padding-bottom:4px;">' +
                                'Ekosistem Terumbu Karang \u2014 Lyzenga' +
                            '</div>' +
                            '<div style="display:flex; align-items:center; gap:6px; margin-bottom:6px;">' +
                                '<span style="display:inline-block; width:12px; height:12px; border-radius:3px; background:' + cfg.color + '; border:1px solid ' + cfg.border + ';"></span>' +
                                '<strong style="font-size:13px;">' + cfg.label + '</strong>' +
                            '</div>' +
                            '<div style="background:rgba(165,184,208,0.08); border:1px solid rgba(165,184,208,0.25); border-radius:6px; padding:6px 10px; margin-bottom:6px;">' +
                                '<div style="display:flex; justify-content:space-between; margin-bottom:2px;">' +
                                    '<span style="color:#64748B;">Tahun Pengamatan:</span>' +
                                    '<strong>' + year + '</strong>' +
                                '</div>' +
                                '<div style="display:flex; justify-content:space-between; margin-bottom:2px;">' +
                                    '<span style="color:#64748B;">Luas Wilayah:</span>' +
                                    '<strong style="color:#0D9488;">' + areaM2Str + ' m&sup2;</strong>' +
                                '</div>' +
                                '<div style="display:flex; justify-content:space-between;">' +
                                    '<span style="color:#64748B;">Keliling (Perimeter):</span>' +
                                    '<span style="font-family:monospace; font-size:11px;">' + lengthStr + ' m</span>' +
                                '</div>' +
                            '</div>' +
                            '<div style="font-size:10px; color:#94A3B8; text-align:right;">' +
                                'Metode Penginderaan Jauh Lyzenga &bull; MultiPolygon' +
                            '</div>' +
                        '</div>'
                    );
                }
            });

            classStats.sort((a, b) => a.gridcode - b.gridcode);

            if (activeLyzengaLayer) {
                map.removeLayer(activeLyzengaLayer);
            }
            activeLyzengaLayer = newLyzLayer;
            if (isLyzengaVisible) {
                activeLyzengaLayer.addTo(map);
            }

            updateLyzengaLegend(year, classStats);

            const selMap = document.getElementById('lyzengaYearSelect');
            if (selMap && selMap.value !== year) selMap.value = year;

            window.dispatchEvent(new CustomEvent('lyzenga-data-loaded', {
                detail: {
                    year: year,
                    classes: classStats
                }
            }));

            showMapLoading(false);
        }

        if (lyzengaCache[year]) {
            renderLyzengaData(lyzengaCache[year]);
            return;
        }

        fetch(url)
            .then(function (resp) {
                if (!resp.ok) throw new Error(`HTTP Error ${resp.status} saat memuat ${url}`);
                return resp.json();
            })
            .then(function (jsonData) {
                lyzengaCache[year] = jsonData;
                renderLyzengaData(jsonData);
            })
            .catch(function (err) {
                showMapLoading(false);
                alert(`Gagal memuat layer Lyzenga ${year}: ${err.message}`);
                console.error(err);
            });
    }

    /* ============================================================
       10. KONTROL FILTER SPASIAL TERPADU (TOP-RIGHT MAP CONTROL)
    ============================================================ */
    const filterControl = L.control({ position: 'topright' });

    filterControl.onAdd = function () {
        const container = L.DomUtil.create('div', 'scomar-control-panel');
        container.style.cssText = `
            padding: 10px 12px;
            min-width: 195px;
            max-width: 220px;
            box-sizing: border-box;
        `;

        // Judul Utama Kontrol
        const mainTitle = document.createElement('div');
        mainTitle.style.cssText = `
            font-size: 10.5px; font-weight: 800; color: #176B87;
            text-transform: uppercase; letter-spacing: 0.6px;
            margin-bottom: 8px; border-bottom: 1.5px solid rgba(165,184,208,0.3);
            padding-bottom: 4px;
        `;
        mainTitle.textContent = 'Filter Layer Spasial';
        container.appendChild(mainTitle);

        /* ── SEKSI 1: TERUMBU KARANG (LYZENGA) ── */
        const lyzHeader = document.createElement('div');
        lyzHeader.style.cssText = `
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 4px;
        `;
        lyzHeader.innerHTML = `
            <label style="display:flex; align-items:center; gap:6px; font-size:11px; font-weight:800; color:#0D9488; text-transform:uppercase; cursor:pointer; margin:0;">
                <input type="checkbox" id="toggleLyzengaLayer" checked style="accent-color:#0D9488; cursor:pointer;">
                <span>Terumbu Karang</span>
            </label>
            <span style="font-size:9.5px; background:rgba(13,148,136,0.12); color:#0D9488; font-weight:700; padding:1px 5px; border-radius:3px;">Lyzenga</span>
        `;
        container.appendChild(lyzHeader);

        const lyzBox = document.createElement('div');
        lyzBox.id = 'lyzengaControlBox';
        lyzBox.style.cssText = 'margin-bottom:8px;';

        const lyzYearLabel = document.createElement('div');
        lyzYearLabel.style.cssText = 'font-size:9.5px; color:#64748B; margin-bottom:2px; font-weight:600;';
        lyzYearLabel.textContent = 'TAHUN PENGAMATAN';

        const lyzYearSelect = document.createElement('select');
        lyzYearSelect.id = 'lyzengaYearSelect';
        lyzYearSelect.style.cssText = `
            width: 100%; padding: 4px 6px;
            border: 1px solid #CBD5E1; border-radius: 5px;
            font-size: 11.5px; background: #F8FAFC; color: #24364B;
        `;

        // Populasi dinamis HANYA tahun yang benar-benar ada di manifest
        const actualLyzYears = Object.keys(lyzManifest).sort();
        actualLyzYears.forEach(function (y) {
            const opt = document.createElement('option');
            opt.value = y;
            opt.textContent = lyzManifest[y].label || ('Tahun ' + y);
            if (y === '2021') opt.selected = true;
            lyzYearSelect.appendChild(opt);
        });

        lyzYearSelect.addEventListener('change', function () {
            loadLyzenga(this.value);
        });

        lyzBox.appendChild(lyzYearLabel);
        lyzBox.appendChild(lyzYearSelect);
        container.appendChild(lyzBox);

        setTimeout(function () {
            const chk = document.getElementById('toggleLyzengaLayer');
            if (chk) {
                chk.addEventListener('change', function () {
                    isLyzengaVisible = this.checked;
                    if (isLyzengaVisible) {
                        if (activeLyzengaLayer) map.addLayer(activeLyzengaLayer);
                        if (lyzengaLegendDiv) lyzengaLegendDiv.style.display = 'block';
                        lyzBox.style.opacity = '1';
                        lyzYearSelect.disabled = false;
                    } else {
                        if (activeLyzengaLayer) map.removeLayer(activeLyzengaLayer);
                        if (lyzengaLegendDiv) lyzengaLegendDiv.style.display = 'none';
                        lyzBox.style.opacity = '0.45';
                        lyzYearSelect.disabled = true;
                    }
                });
            }
        }, 100);

        /* ── SEKSI 2: ZONA POTENSI PENANGKAPAN IKAN (ZPPI) ── */
        const zppiDivider = document.createElement('div');
        zppiDivider.style.cssText = 'height:1px; background:rgba(165,184,208,0.3); margin:6px 0;';
        container.appendChild(zppiDivider);

        const zppiHeader = document.createElement('div');
        zppiHeader.style.cssText = `
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 4px;
        `;
        zppiHeader.innerHTML = `
            <label style="display:flex; align-items:center; gap:6px; font-size:11px; font-weight:800; color:#176B87; text-transform:uppercase; cursor:pointer; margin:0;">
                <input type="checkbox" id="toggleZppiLayer" checked style="accent-color:#176B87; cursor:pointer;">
                <span>Zona Tangkap</span>
            </label>
            <span style="font-size:9.5px; background:rgba(23,107,135,0.12); color:#176B87; font-weight:700; padding:1px 5px; border-radius:3px;">ZPPI</span>
        `;
        container.appendChild(zppiHeader);

        const zppiBox = document.createElement('div');
        zppiBox.id = 'zppiControlBox';

        const yearLabel = document.createElement('div');
        yearLabel.style.cssText = 'font-size:9.5px; color:#64748B; margin-bottom:2px; font-weight:600;';
        yearLabel.textContent = 'TAHUN';

        const yearSelect = document.createElement('select');
        yearSelect.id = 'zppiYearSelect';
        yearSelect.style.cssText = `
            width: 100%; padding: 4px 6px;
            border: 1px solid #CBD5E1; border-radius: 5px;
            font-size: 11.5px; margin-bottom: 6px;
            background: #F8FAFC; color: #24364B;
        `;

        Object.keys(manifest).sort().forEach(function (y) {
            const opt = document.createElement('option');
            opt.value = y;
            opt.textContent = manifest[y].label || ('Tahun ' + y);
            if (y === '2021') opt.selected = true;
            yearSelect.appendChild(opt);
        });

        const seasonLabel = document.createElement('div');
        seasonLabel.style.cssText = 'font-size:9.5px; color:#64748B; margin-bottom:2px; font-weight:600;';
        seasonLabel.textContent = 'MUSIM';

        const seasonSelect = document.createElement('select');
        seasonSelect.id = 'zppiSeasonSelect';
        seasonSelect.style.cssText = `
            width: 100%; padding: 4px 6px;
            border: 1px solid #CBD5E1; border-radius: 5px;
            font-size: 11.5px;
            background: #F8FAFC; color: #24364B;
        `;

        const SEASON_ORDER = ['tahunan', 'barat', 'peralihan1', 'timur', 'peralihan2'];

        function populateSeasons(year, keepSeason) {
            const currentSelected = keepSeason !== undefined ? keepSeason : seasonSelect.value;
            seasonSelect.innerHTML = '';
            const seasons = manifest[year] ? manifest[year].seasons : {};
            let hasSelected = false;
            SEASON_ORDER.forEach(function (key) {
                if (seasons[key]) {
                    const opt = document.createElement('option');
                    opt.value = key;
                    opt.textContent = seasons[key].label;
                    if (currentSelected && key === currentSelected) {
                        opt.selected = true;
                        hasSelected = true;
                    }
                    seasonSelect.appendChild(opt);
                }
            });
            if (!hasSelected && seasonSelect.options.length > 0) {
                seasonSelect.selectedIndex = 0;
            }
        }

        populateSeasons('2021');

        yearSelect.addEventListener('change', function () {
            populateSeasons(this.value, seasonSelect.value);
            loadZppi(this.value, seasonSelect.value);
        });

        seasonSelect.addEventListener('change', function () {
            loadZppi(yearSelect.value, this.value);
        });

        zppiBox.appendChild(yearLabel);
        zppiBox.appendChild(yearSelect);
        zppiBox.appendChild(seasonLabel);
        zppiBox.appendChild(seasonSelect);
        container.appendChild(zppiBox);

        setTimeout(function () {
            const chkZ = document.getElementById('toggleZppiLayer');
            if (chkZ) {
                chkZ.addEventListener('change', function () {
                    isZppiVisible = this.checked;
                    if (isZppiVisible) {
                        if (activeZppiLayer) map.addLayer(activeZppiLayer);
                        if (zppiLegendDiv) zppiLegendDiv.style.display = 'block';
                        zppiBox.style.opacity = '1';
                        yearSelect.disabled = false;
                        seasonSelect.disabled = false;
                        updateStatCards(currentYear, currentSeasonKey, currentZppiCount, currentSeason);
                    } else {
                        if (activeZppiLayer) map.removeLayer(activeZppiLayer);
                        if (zppiLegendDiv) zppiLegendDiv.style.display = 'none';
                        zppiBox.style.opacity = '0.45';
                        yearSelect.disabled = true;
                        seasonSelect.disabled = true;
                        updateStatCards(currentYear, currentSeasonKey, 0, currentSeason);
                    }
                });
            }
        }, 100);

        L.DomEvent.disableClickPropagation(container);
        L.DomEvent.disableScrollPropagation(container);

        return container;
    };

    filterControl.addTo(map);

    /* ============================================================
       11. FUNGSI FOCUS GLOBAL (DIKONEKSIKAN KE TABEL ALPINE)
    ============================================================ */
    window.switchLyzengaYear = function (year) {
        const sel = document.getElementById('lyzengaYearSelect');
        if (sel) sel.value = year;
        loadLyzenga(year);
    };

    window.focusLyzengaClass = function (gridcode) {
        const mapEl = document.getElementById('scomarMap');
        if (mapEl) {
            mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        setTimeout(function () {
            const layer = lyzengaLayerRefs[gridcode];
            if (layer && layer.getBounds) {
                map.fitBounds(layer.getBounds(), { padding: [35, 35], maxZoom: 15 });
                setTimeout(function () {
                    if (layer.openPopup) layer.openPopup();
                }, 600);
            }
        }, 250);
    };

    window.focusZppiPoint = function (lat, lng, markerIndex) {
        const mapEl = document.getElementById('scomarMap');
        if (mapEl) {
            mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        setTimeout(function () {
            map.flyTo([lat, lng], 16, { animate: true, duration: 0.8 });
            if (activeZppiLayer) {
                let best = null, bestDist = Infinity;
                activeZppiLayer.eachLayer(function (m) {
                    if (m.getLatLng) {
                        const ll = m.getLatLng();
                        const d  = Math.abs(ll.lat - lat) + Math.abs(ll.lng - lng);
                        if (d < bestDist) { bestDist = d; best = m; }
                    }
                });
                if (best && bestDist < 0.001) {
                    setTimeout(function () { best.openPopup(); }, 850);
                }
            }
        }, 250);
    };

    window.focusFgPoint = function (lat, lng) {
        const mapEl = document.getElementById('scomarMap');
        if (mapEl) {
            mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        setTimeout(function () {
            map.flyTo([lat, lng], 15, { animate: true, duration: 0.8 });
        }, 250);
    };

    window.focusCoralPoint = function (lat, lng) {
        const mapEl = document.getElementById('scomarMap');
        if (mapEl) {
            mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        setTimeout(function () {
            map.flyTo([lat, lng], 15, { animate: true, duration: 0.8 });
        }, 250);
    };

    /* ============================================================
       12. LOAD DEFAULT: LYZENGA 2021 & ZPPI 2021 MUSIM BARAT
    ============================================================ */
    setTimeout(function () {
        loadLyzenga('2021');
        const sSelect = document.getElementById('zppiSeasonSelect');
        loadZppi('2021', (sSelect && sSelect.value) ? sSelect.value : 'tahunan');
    }, 350);

    /* ============================================================
       12. ANIMASI COUNTING STAT CARDS
    ============================================================ */
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function animateCount(el, target, decimals, suffix, duration) {
        if (prefersReduced) {
            el.textContent = target.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + suffix;
            return;
        }
        const start = performance.now();
        function step(ts) {
            const progress = Math.min((ts - start) / duration, 1);
            const eased    = 1 - Math.pow(1 - progress, 3);
            const value    = eased * target;
            el.textContent = value.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const statEls = document.querySelectorAll('[data-count-target]');
        if (statEls.length === 0) return;
        const obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    const el       = entry.target;
                    const target   = parseFloat(el.dataset.countTarget || 0);
                    const decimals = parseInt(el.dataset.countDecimals || 0);
                    const suffix   = el.dataset.countSuffix || '';
                    animateCount(el, target, decimals, suffix, 1800);
                    obs.unobserve(el);
                }
            });
        }, { threshold: 0.3 });
        statEls.forEach(function (el) { obs.observe(el); });
    });

}());
</script>
@endpush
