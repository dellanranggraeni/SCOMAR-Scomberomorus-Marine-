<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>

    {{-- ============================================================
         META & CHARSET
    ============================================================ --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ============================================================
         JUDUL HALAMAN (diatur tiap halaman via @yield)
    ============================================================ --}}
    <title>@yield('title', 'SCOMAR') — SCOMAR</title>

    {{-- ============================================================
         FONT RESMI: PLUS JAKARTA SANS (Modern, Elegan, Profesional)
    ============================================================ --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    {{-- ============================================================
         INLINE SCRIPT: PREFERENSI TEMA (Mencegah Layar Berkedip)
    ============================================================ --}}
    <script>
        (function () {
            const savedTheme = localStorage.getItem('scomar_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    {{-- ============================================================
         PALET WARNA & VARIABEL CSS SCOMAR (LIGHT & DARK MODE)
    ============================================================ --}}
    <style>
        :root {
            /* ── PALET ASLI SCOMAR ─────────────────────────────────── */
            --cashmere-blue : #A5B8D0;
            --merlot        : #73343A;
            --deep-navy     : #24364B;
            --warm-ivory    : #F5F5F2;
            --white         : #FFFFFF;

            /* ── DIMENSI LAYOUT ────────────────────────────────────── */
            --sidebar-width          : 220px;
            --sidebar-collapsed-width: 64px;
            --navbar-height          : 64px;
            --transition             : 0.25s ease;

            /* ── VARIABEL TEMA: MODE CERAH (LIGHT) ─────────────────── */
            --bg-body            : #F5F5F2; /* Warm Ivory */
            --bg-surface         : #FFFFFF; /* Pure White */
            --bg-navbar          : #FFFFFF;
            --bg-card            : #FFFFFF;
            --text-primary       : #24364B; /* Deep Navy */
            --text-secondary     : #64748B;
            --text-muted         : #94A3B8;
            --border-subtle      : rgba(165, 184, 208, 0.4); /* Cashmere Blue tint */
            --border-hover       : var(--cashmere-blue);
            --card-shadow        : 0 4px 16px rgba(36, 54, 75, 0.06);

            /* ── SIDEBAR SCOMAR (DEEP NAVY DENGAN TEKS CASHMERE BLUE & MERLOT AKTIF) ── */
            --bg-sidebar         : #24364B; /* Deep Navy Asli */
            --sidebar-border     : rgba(165, 184, 208, 0.16);
            --sidebar-text       : #F5F5F2; /* Warm Ivory */
            --sidebar-text-muted : #A5B8D0; /* Cashmere Blue */
            --sidebar-hover-bg   : rgba(165, 184, 208, 0.14); /* Cashmere Blue tint */
            --sidebar-active-bg  : #73343A; /* Merlot Asli aksen kontras */
            --sidebar-active-text: #FFFFFF;
            --sidebar-active-bar : #A5B8D0; /* Cashmere Blue accent bar */
        }

        /* ── VARIABEL TEMA: MODE GELAP (DARK) ──────────────────────── */
        [data-theme="dark"] {
            --bg-body            : #0F1722; /* Deep Oceanic Navy Gelap */
            --bg-surface         : #1B293A; /* Kartu Navy Gelap Timbul / Elevated */
            --bg-navbar          : #1B293A;
            --bg-card            : #1B293A;
            --text-primary       : #F8FAFC; /* Warm Ivory Terang */
            --text-secondary     : #CBD5E1; /* Cashmere Blue Jelas */
            --text-muted         : #94A3B8;
            --border-subtle      : rgba(165, 184, 208, 0.22);
            --border-hover       : rgba(165, 184, 208, 0.55);
            --card-shadow        : 0 8px 24px rgba(0, 0, 0, 0.38), 0 2px 6px rgba(0, 0, 0, 0.24);

            /* Warna Teks Semantik Dark Mode untuk Angka Statistik & Indikator */
            --color-merlot-text  : #FF858B; /* Merlot Terang Kontras Tinggi */
            --color-navy-text    : #F1F5F9; /* Navy Terang / White Cashmere */
            --color-accent-blue  : #60A5FA; /* Sky Blue Terang */
            --color-accent-teal  : #34D399; /* Emerald Terang */
            --color-accent-amber : #FBBF24; /* Amber Terang */

            /* Sidebar Dark Mode (Deep Oceanic Navy Gelap) */
            --bg-sidebar         : #141F2B;
            --sidebar-border     : rgba(245, 245, 242, 0.12);
            --sidebar-text       : #F5F5F2;
            --sidebar-text-muted : #A5B8D0;
            --sidebar-hover-bg   : rgba(165, 184, 208, 0.12);
            --sidebar-active-bg  : #73343A;
            --sidebar-active-text: #FFFFFF;
            --sidebar-active-bar : #A5B8D0;
        }

        /* ── PERBAIKAN ELEVASI KARTU & KONTRAS DARK MODE GLOBAL ── */
        [data-theme="dark"] .card,
        [data-theme="dark"] .status-card,
        [data-theme="dark"] .metric-card,
        [data-theme="dark"] .chart-card,
        [data-theme="dark"] .table-card,
        [data-theme="dark"] .crud-section-card,
        [data-theme="dark"] .summary-card,
        [data-theme="dark"] .fix-current-season-box,
        [data-theme="dark"] .modal-content,
        [data-theme="dark"] .modal-dialog-ocean {
            background-color: var(--bg-surface) !important;
            border-color: var(--border-subtle) !important;
            box-shadow: var(--card-shadow) !important;
        }

        [data-theme="dark"] .card:hover,
        [data-theme="dark"] .status-card:hover,
        [data-theme="dark"] .metric-card:hover,
        [data-theme="dark"] .chart-card:hover {
            border-color: var(--border-hover) !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45) !important;
        }

        /* Penyesuaian Angka Statistik Inline di Dark Mode */
        [data-theme="dark"] [style*="color: var(--merlot)"],
        [data-theme="dark"] [style*="color: #73343A"],
        [data-theme="dark"] [style*="color:#73343A"],
        [data-theme="dark"] .text-merlot,
        [data-theme="dark"] .burgundy {
            color: #FF858B !important;
        }

        [data-theme="dark"] [style*="color: var(--deep-navy)"],
        [data-theme="dark"] [style*="color: #24364B"],
        [data-theme="dark"] [style*="color:#24364B"],
        [data-theme="dark"] .text-deep-navy {
            color: #E2E8F0 !important;
        }

        [data-theme="dark"] [style*="color: #4B6A8A"],
        [data-theme="dark"] [style*="color:#4B6A8A"] {
            color: #93C5FD !important;
        }

        /* Header Tabel di Dark Mode */
        [data-theme="dark"] .scomar-table th,
        [data-theme="dark"] .fix-current-season-table th,
        [data-theme="dark"] .chart-stats-table th {
            background-color: rgba(165, 184, 208, 0.08) !important;
            color: var(--text-secondary) !important;
            border-bottom-color: var(--border-subtle) !important;
        }

        [data-theme="dark"] .scomar-table td,
        [data-theme="dark"] .fix-current-season-table td,
        [data-theme="dark"] .chart-stats-table td {
            border-top-color: var(--border-subtle) !important;
            border-bottom-color: var(--border-subtle) !important;
        }

        /* Modal Dialog di Dark Mode */
        [data-theme="dark"] .modal-dialog-ocean,
        [data-theme="dark"] .modal-content {
            background: #1B293A !important;
            border: 1px solid rgba(165, 184, 208, 0.28) !important;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6) !important;
        }

        /* Kotak Parameter di Modal Dark Mode */
        [data-theme="dark"] .fix-box-item {
            background: #141F2B !important;
            border-color: rgba(165, 184, 208, 0.25) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }
        [data-theme="dark"] .fix-box-item.temp    { border-left: 3.5px solid #FF858B !important; background: rgba(255, 133, 139, 0.08) !important; }
        [data-theme="dark"] .fix-box-item.chla    { border-left: 3.5px solid #34D399 !important; background: rgba(52, 211, 153, 0.08) !important; }
        [data-theme="dark"] .fix-box-item.tss     { border-left: 3.5px solid #60A5FA !important; background: rgba(96, 165, 250, 0.08) !important; }
        [data-theme="dark"] .fix-box-item.current { border-left: 3.5px solid #A78BFA !important; background: rgba(167, 139, 250, 0.08) !important; }

        /* Badge status musim di tabel hasil tangkapan */
        [data-theme="dark"] .badge-musim-barat {
            background: rgba(255, 133, 139, 0.18) !important;
            color: #FF858B !important;
            border: 1px solid rgba(255, 133, 139, 0.35) !important;
        }
        [data-theme="dark"] .badge-musim-peralihan {
            background: rgba(165, 184, 208, 0.18) !important;
            color: #CBD5E1 !important;
            border: 1px solid rgba(165, 184, 208, 0.35) !important;
        }
        [data-theme="dark"] .badge-musim-timur {
            background: rgba(52, 211, 153, 0.18) !important;
            color: #34D399 !important;
            border: 1px solid rgba(52, 211, 153, 0.35) !important;
        }
    </style>

    {{-- ============================================================
         CSS UTAMA LAYOUT SCOMAR
    ============================================================ --}}
    <style>

        /* ----------------------------------------------------------
           RESET & FONT
        ---------------------------------------------------------- */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
        }

        html, body {
            height: 100%;
            background-color: var(--bg-body);
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.6;
            transition: background-color var(--transition), color var(--transition);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button {
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
        }

        /* ----------------------------------------------------------
           STRUKTUR UTAMA: SIDEBAR + CONTENT WRAPPER
           (Full Responsive Fluid Width Calculation)
        ---------------------------------------------------------- */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        .app-wrapper {
            display: flex;
            width: 100%;
            max-width: 100%;
            min-height: 100vh;
            position: relative;
            box-sizing: border-box;
        }

        /* ----------------------------------------------------------
           SIDEBAR
        ---------------------------------------------------------- */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: width var(--transition), left var(--transition), background var(--transition), border-color var(--transition);
            overflow: hidden;
            box-sizing: border-box;
        }

        /* State sidebar diciutkan (collapsed) */
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* ----------------------------------------------------------
           MAIN WRAPPER (navbar + konten + footer)
           (Pencegahan Content Overflow: width disesuaikan persis dengan sisa viewport)
        ---------------------------------------------------------- */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            max-width: calc(100% - var(--sidebar-width));
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            box-sizing: border-box;
            transition: margin-left var(--transition), width var(--transition), max-width var(--transition);
        }

        .main-wrapper.expanded {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
            max-width: calc(100% - var(--sidebar-collapsed-width));
        }

        /* ----------------------------------------------------------
           NAVBAR (header atas konten)
        ---------------------------------------------------------- */
        .app-navbar {
            height: var(--navbar-height);
            background: var(--bg-navbar);
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 900;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05);
            transition: background var(--transition), border-color var(--transition);
        }

        /* ----------------------------------------------------------
           AREA KONTEN UTAMA
        ---------------------------------------------------------- */
        .page-content {
            flex: 1;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            padding: 24px 28px;
            background: var(--bg-body);
            box-sizing: border-box;
            transition: background var(--transition);
        }

        /* ----------------------------------------------------------
           FOOTER
        ---------------------------------------------------------- */
        .app-footer {
            background: var(--bg-surface);
            border-top: 1px solid var(--border-subtle);
            padding: 14px 28px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            transition: background var(--transition), border-color var(--transition);
        }

        /* ----------------------------------------------------------
           MOBILE OVERLAY
        ---------------------------------------------------------- */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(18, 28, 38, 0.6);
            backdrop-filter: blur(2px);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* ----------------------------------------------------------
           RESPONSIVE — TABLET & MOBILE
        ---------------------------------------------------------- */
        @media (max-width: 1024px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
                width: var(--sidebar-width) !important;
            }

            .sidebar.mobile-open {
                left: 0;
                box-shadow: 0 0 30px rgba(0, 0, 0, 0.4);
            }

            .main-wrapper,
            .main-wrapper.expanded {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .page-content {
                padding: 20px 20px;
            }
        }

        @media (max-width: 768px) {
            .page-content {
                padding: 16px 16px;
            }
            .app-navbar {
                padding: 0 16px;
            }
        }

        @media (max-width: 640px) {
            .app-navbar {
                padding: 0 14px;
            }
            .page-content {
                padding: 14px 12px;
            }
            .app-footer {
                padding: 12px 14px;
                font-size: 11px;
            }
        }

        @media (max-width: 480px) {
            .page-content {
                padding: 12px 10px;
            }
            .app-navbar {
                padding: 0 10px;
            }
            .scomar-modal-backdrop {
                padding: 10px !important;
            }
            .scomar-modal-card {
                max-height: 94vh !important;
                border-radius: 12px !important;
            }
            .scomar-modal-header {
                padding: 12px 16px !important;
            }
            .scomar-modal-body {
                padding: 14px 16px !important;
            }
            .scomar-modal-footer {
                padding: 10px 16px !important;
            }
        }

        @media (max-width: 380px) {
            .page-content {
                padding: 10px 8px;
            }
            .app-navbar {
                padding: 0 8px;
            }
        }

        /* Generic Touch & Fluid Table Utilities */
        .table-responsive,
        [style*="overflow-x:auto"],
        [style*="overflow-x: auto"] {
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }

        /* Prevent image/svg overflow */
        img, svg {
            max-width: 100%;
        }

        /* ----------------------------------------------------------
           SCROLLBAR KUSTOM
        ---------------------------------------------------------- */
        ::-webkit-scrollbar            { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track      { background: var(--bg-body); }
        ::-webkit-scrollbar-thumb      { background: var(--cashmere-blue); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover{ background: var(--merlot); }

        /* ----------------------------------------------------------
           SCOMAR SHARED CRUD & NOTIFICATION STYLES
        ---------------------------------------------------------- */
        .scomar-alert {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 13.5px;
            font-weight: 500;
            line-height: 1.5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            animation: fadeInAlert 0.3s ease;
        }

        @keyframes fadeInAlert {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .scomar-alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-left: 4px solid #10B981;
            color: #065F46;
        }
        [data-theme="dark"] .scomar-alert-success {
            background: rgba(16, 185, 129, 0.16);
            border-color: rgba(16, 185, 129, 0.4);
            color: #6EE7B7;
        }

        .scomar-alert-error {
            background: rgba(115, 52, 58, 0.1);
            border: 1px solid rgba(115, 52, 58, 0.3);
            border-left: 4px solid var(--merlot);
            color: var(--merlot);
        }
        [data-theme="dark"] .scomar-alert-error {
            background: rgba(220, 38, 38, 0.16);
            border-color: rgba(220, 38, 38, 0.35);
            color: #FCA5A5;
        }

        .scomar-alert-close {
            background: none;
            border: none;
            color: currentColor;
            opacity: 0.6;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: opacity 0.2s;
        }
        .scomar-alert-close:hover { opacity: 1; }

        /* Tombol & Elemen Form SCOMAR */
        .btn-scomar-primary {
            background: var(--merlot);
            color: #FFFFFF !important;
            padding: 9px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(115, 52, 58, 0.25);
            text-decoration: none;
        }
        .btn-scomar-primary:hover {
            filter: brightness(1.12);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(115, 52, 58, 0.35);
        }

        .btn-scomar-secondary {
            background: var(--bg-surface);
            color: var(--text-primary) !important;
            border: 1px solid var(--border-subtle);
            padding: 9px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-scomar-secondary:hover {
            border-color: var(--cashmere-blue);
            background: rgba(165, 184, 208, 0.08);
        }

        .btn-scomar-danger {
            background: #DC2626;
            color: #FFFFFF !important;
            padding: 9px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25);
        }
        .btn-scomar-danger:hover {
            background: #B91C1C;
            transform: translateY(-1px);
        }

        /* Tombol aksi di tabel */
        .btn-action-icon {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-subtle);
            background: var(--bg-surface);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-action-icon:hover {
            transform: translateY(-1px);
        }
        .btn-action-icon.view:hover {
            color: #0284C7;
            border-color: #0284C7;
            background: rgba(2, 132, 199, 0.08);
        }
        .btn-action-icon.edit:hover {
            color: #D97706;
            border-color: #D97706;
            background: rgba(217, 119, 6, 0.08);
        }
        .btn-action-icon.delete:hover {
            color: #DC2626;
            border-color: #DC2626;
            background: rgba(220, 38, 38, 0.08);
        }

        /* SCOMAR Form Controls */
        .scomar-form-group {
            margin-bottom: 16px;
        }
        .scomar-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
        }
        .scomar-label .req {
            color: #DC2626;
            margin-left: 2px;
        }
        .scomar-input, .scomar-select, .scomar-textarea {
            width: 100%;
            padding: 9px 13px;
            border-radius: 8px;
            border: 1.5px solid var(--border-subtle);
            background: var(--bg-surface);
            color: var(--text-primary);
            font-size: 13.5px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        .scomar-input:focus, .scomar-select:focus, .scomar-textarea:focus {
            border-color: var(--merlot);
            box-shadow: 0 0 0 3px rgba(115, 52, 58, 0.12);
        }

        /* Modal SCOMAR */
        .scomar-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(18, 28, 38, 0.65);
            backdrop-filter: blur(4px);
            z-index: 2500;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .scomar-modal-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            width: 100%;
            max-width: 580px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalScale 0.2s ease-out;
        }
        .scomar-modal-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-surface);
        }
        .scomar-modal-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .scomar-modal-body {
            padding: 20px 22px;
            overflow-y: auto;
            flex: 1;
        }
        .scomar-modal-footer {
            padding: 14px 22px;
            border-top: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            background: rgba(165, 184, 208, 0.04);
        }

    </style>

    {{-- ============================================================
         SLOT CSS TAMBAHAN DARI HALAMAN ANAK
    ============================================================ --}}
    @stack('styles')

</head>
<body>

{{-- ================================================================
     PEMBUNGKUS UTAMA APLIKASI
================================================================ --}}
<div class="app-wrapper" id="appWrapper">

    {{-- SIDEBAR --}}
    @include('partials.sidebar')

    {{-- OVERLAY MOBILE --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- MAIN WRAPPER --}}
    <div class="main-wrapper" id="mainWrapper">

        {{-- NAVBAR --}}
        @include('partials.navbar')

        {{-- AREA KONTEN --}}
        <main class="page-content" id="pageContent">

            {{-- Flash Alert Notifikasi Berhasil --}}
            @if(session('success'))
                <div class="scomar-alert scomar-alert-success" x-data="{ show: true }" x-show="show" x-transition>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button class="scomar-alert-close" @click="show = false" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            @endif

            {{-- Flash Alert Notifikasi Error --}}
            @if(session('error'))
                <div class="scomar-alert scomar-alert-error" x-data="{ show: true }" x-show="show" x-transition>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button class="scomar-alert-close" @click="show = false" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            @endif

            {{-- Alert Validasi Error Form --}}
            @if($errors->any())
                <div class="scomar-alert scomar-alert-error" x-data="{ show: true }" x-show="show" x-transition>
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <div style="display:flex; align-items:center; gap:8px; font-weight:700;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span>Terjadi kesalahan pengisian formulir:</span>
                        </div>
                        <ul style="margin:4px 0 0 26px; padding:0; font-size:12.5px;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button class="scomar-alert-close" @click="show = false" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>

        {{-- FOOTER --}}
        @include('partials.footer')

    </div>

</div>

{{-- ================================================================
     JAVASCRIPT PUSTAKA EKSTERNAL
================================================================ --}}

{{-- Alpine.js --}}
<script
    defer
    src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js">
</script>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

{{-- Leaflet.js --}}
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

{{-- ================================================================
     JAVASCRIPT UTAMA LAYOUT: SIDEBAR & THEME TOGGLE
================================================================ --}}
<script>
(function () {
    'use strict';

    // ── 1. INISIALISASI ELEMEN SIDEBAR ────────────────────────────
    const sidebar       = document.getElementById('appSidebar');
    const mainWrapper   = document.getElementById('mainWrapper');
    const overlay       = document.getElementById('sidebarOverlay');
    const toggleDesktop = document.getElementById('sidebarToggleDesktop');
    const toggleMobile  = document.getElementById('sidebarToggleMobile');

    function isMobile() {
        return window.innerWidth <= 1024;
    }

    function saveSidebarPref(collapsed) {
        localStorage.setItem('scomarsidebar', collapsed ? '1' : '0');
    }

    function loadSidebarPref() {
        return localStorage.getItem('scomarsidebar') === '1';
    }

    if (!isMobile() && loadSidebarPref()) {
        sidebar.classList.add('collapsed');
        mainWrapper.classList.add('expanded');
    }

    if (toggleDesktop) {
        toggleDesktop.addEventListener('click', function () {
            if (isMobile()) return;
            const isCollapsed = sidebar.classList.toggle('collapsed');
            mainWrapper.classList.toggle('expanded', isCollapsed);
            saveSidebarPref(isCollapsed);

            // Trigger window resize agar grafik Chart.js & peta Leaflet menyesuaikan ukuran baru
            setTimeout(function () {
                window.dispatchEvent(new Event('resize'));
            }, 50);
            setTimeout(function () {
                window.dispatchEvent(new Event('resize'));
            }, 270);
        });
    }

    if (toggleMobile) {
        toggleMobile.addEventListener('click', function () {
            const isOpen = sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active', isOpen);
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');

            if (loadSidebarPref()) {
                sidebar.classList.add('collapsed');
                mainWrapper.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainWrapper.classList.remove('expanded');
            }
        }
    });

    // ── 2. LOGIKA TEMA (DARK / LIGHT MODE) ─────────────────────────
    const themeToggleBtn = document.getElementById('themeToggleBtn');

    function updateThemeIcons(theme) {
        const sunIcons  = document.querySelectorAll('.theme-icon-sun');
        const moonIcons = document.querySelectorAll('.theme-icon-moon');

        if (theme === 'dark') {
            sunIcons.forEach(el => el.style.display = 'block');
            moonIcons.forEach(el => el.style.display = 'none');
        } else {
            sunIcons.forEach(el => el.style.display = 'none');
            moonIcons.forEach(el => el.style.display = 'block');
        }
    }

    const currentTheme = localStorage.getItem('scomar_theme') || 'light';
    updateThemeIcons(currentTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const oldTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = oldTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('scomar_theme', newTheme);
            updateThemeIcons(newTheme);

            // Dispatch global event agar seluruh grafik Chart.js & widget terupdate instan
            window.dispatchEvent(new CustomEvent('scomar:theme-changed', { detail: { theme: newTheme } }));
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: newTheme } }));
        });
    }
}());
</script>

{{-- ================================================================
     SLOT JAVASCRIPT TAMBAHAN
================================================================ --}}
@stack('scripts')

</body>
</html>
