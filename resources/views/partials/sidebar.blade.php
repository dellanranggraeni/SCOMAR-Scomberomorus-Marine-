{{-- ================================================================
     SIDEBAR SCOMAR
     File: resources/views/partials/sidebar.blade.php

     Menggunakan palet warna asli SCOMAR:
     - Deep Navy (#24364B) sebagai warna latar sidebar
     - Cashmere Blue (#A5B8D0) untuk label, garis pembatas, dan hover
     - Merlot (#73343A) untuk status aktif dan aksen
     - Warm Ivory (#F5F5F2) / White (#FFFFFF) untuk teks menu
================================================================ --}}

<style>

    /* â”€â”€ BRAND HEADER (area logo di atas sidebar) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 14px;
        height: var(--navbar-height);
        border-bottom: 1px solid var(--sidebar-border);
        flex-shrink: 0;
        overflow: hidden;
        white-space: nowrap;
        background: var(--bg-sidebar);
        transition: background var(--transition), border-color var(--transition);
    }

    /* Kartu putih penyangga logo agar ikan & gelombang navy terlihat tajam */
    .sidebar-brand-logo-wrapper {
        width: 36px;
        height: 32px;
        background: #FFFFFF;
        border-radius: 8px;
        padding: 3px 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }

    .sidebar-brand-logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .sidebar-brand-text {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .sidebar-brand-text .brand-name {
        font-size: 15px;
        font-weight: 800;
        color: var(--white);
        letter-spacing: 1.2px;
        line-height: 1.2;
    }

    .sidebar-brand-text .brand-tagline {
        font-size: 9px;
        font-weight: 500;
        color: var(--cashmere-blue);
        line-height: 1.3;
        white-space: normal;
        max-width: 140px;
    }

    /* â”€â”€ AREA SCROLL MENU â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 10px 8px 8px;
    }

    .sidebar-nav::-webkit-scrollbar       { width: 4px; }
    .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(165, 184, 208, 0.35); border-radius: 2px; }

    /* â”€â”€ LABEL GRUP MENU (CASHMERE BLUE) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .nav-group-label {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: 1.1px;
        text-transform: uppercase;
        color: var(--cashmere-blue);
        padding: 12px 10px 4px;
        white-space: nowrap;
        overflow: hidden;
        opacity: 0.9;
    }

    /* â”€â”€ ITEM MENU â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        margin-bottom: 2px;
        color: var(--sidebar-text);
        font-size: 13px;
        font-weight: 500;
        border-radius: 8px;
        transition: background var(--transition), color var(--transition);
        position: relative;
        white-space: nowrap;
        overflow: hidden;
    }

    .nav-item:hover {
        background: var(--sidebar-hover-bg);
        color: var(--white);
    }

    /* State aktif: Merlot (#73343A) */
    .nav-item.active {
        background: var(--sidebar-active-bg);
        color: var(--white);
        font-weight: 700;
    }

    /* Garis aksen kiri item aktif: Cashmere Blue */
    .nav-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 5px;
        bottom: 5px;
        width: 3px;
        background: var(--sidebar-active-bar);
        border-radius: 0 3px 3px 0;
    }

    /* â”€â”€ IKON MENU â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .nav-icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    /* â”€â”€ TEKS MENU â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .nav-label {
        overflow: hidden;
        text-overflow: ellipsis;
        transition: opacity var(--transition), width var(--transition);
    }

    /* â”€â”€ GARIS PEMISAH GRUP â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .nav-divider {
        height: 1px;
        background: var(--sidebar-border);
        margin: 10px 4px;
    }

    /* â”€â”€ TOOLTIP SAAT SIDEBAR COLLAPSED â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .nav-item[data-tooltip] {
        position: relative;
    }
    .nav-item[data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        left: calc(var(--sidebar-collapsed-width) + 8px);
        top: 50%;
        transform: translateY(-50%);
        background: var(--deep-navy);
        color: var(--white);
        border: 1px solid var(--cashmere-blue);
        font-size: 12px;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 6px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
        z-index: 1100;
        box-shadow: 0 4px 14px rgba(0,0,0,0.3);
    }
    .sidebar.collapsed .nav-item[data-tooltip]:hover::after {
        opacity: 1;
    }

    /* â”€â”€ SIDEBAR COLLAPSED â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .sidebar.collapsed .nav-label,
    .sidebar.collapsed .nav-group-label,
    .sidebar.collapsed .sidebar-brand-text {
        opacity: 0;
        width: 0;
        overflow: hidden;
    }

    .sidebar.collapsed .nav-item {
        padding: 10px;
        justify-content: center;
        gap: 0;
        margin-bottom: 4px;
    }

    .sidebar.collapsed .sidebar-brand {
        justify-content: center;
        padding: 0;
    }

    /* == ANIMASI AI SCANNER MENU == */
    @keyframes aiRadarPulse {
        0%   { box-shadow: 0 0 0 0 rgba(165, 184, 208, 0.7); }
        50%  { box-shadow: 0 0 0 5px rgba(165, 184, 208, 0); }
        100% { box-shadow: 0 0 0 0 rgba(165, 184, 208, 0); }
    }
    @keyframes aiGlow {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.65; }
    }
    @keyframes aiBadgePop {
        0%, 100% { transform: scale(1); }
        50%       { transform: scale(1.15); }
    }
    @keyframes aiScanLine {
        0%   { transform: translateY(-100%); opacity: 0; }
        30%  { opacity: 0.9; }
        70%  { opacity: 0.5; }
        100% { transform: translateY(100%); opacity: 0; }
    }

    .nav-item.scanner-ai-menu .nav-icon {
        position: relative;
        overflow: visible;
    }
    .nav-item.scanner-ai-menu .nav-icon svg {
        animation: aiGlow 2.5s ease-in-out infinite;
        filter: drop-shadow(0 0 3px rgba(165, 184, 208, 0.7));
    }
    .nav-item.scanner-ai-menu.active .nav-icon svg {
        filter: drop-shadow(0 0 5px rgba(165, 184, 208, 0.95));
    }
    .scanner-ai-badge {
        position: absolute;
        top: -4px;
        right: -6px;
        background: linear-gradient(135deg, #24364B 0%, #176B87 100%);
        color: #fff;
        font-size: 7px;
        font-weight: 800;
        letter-spacing: 0.3px;
        padding: 1px 4px;
        border-radius: 4px;
        line-height: 1.4;
        pointer-events: none;
        animation: aiBadgePop 2.2s ease-in-out infinite;
        box-shadow: 0 1px 4px rgba(23, 107, 135, 0.6);
        z-index: 10;
    }
    .scanner-pulse-ring {
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 1.5px solid rgba(165, 184, 208, 0.45);
        animation: aiRadarPulse 2s ease-out infinite;
        pointer-events: none;
    }
    .scanner-nav-scanline {
        position: absolute;
        left: 0; right: 0;
        height: 1.5px;
        background: linear-gradient(90deg, transparent, rgba(165, 184, 208, 0.65), transparent);
        pointer-events: none;
        animation: aiScanLine 3s ease-in-out infinite;
    }
    @media (prefers-reduced-motion: reduce) {
        .nav-item.scanner-ai-menu .nav-icon svg,
        .scanner-ai-badge,
        .scanner-pulse-ring,
        .scanner-nav-scanline { animation: none !important; }
    }
    .sidebar.collapsed .scanner-ai-badge {
        font-size: 0;
        padding: 3px;
        border-radius: 50%;
        min-width: 7px;
        min-height: 7px;
        top: -3px;
        right: -3px;
    }
</style>

{{-- ================================================================
     ELEMEN SIDEBAR
================================================================ --}}
<aside class="sidebar" id="appSidebar">

    {{-- â”€â”€ BRAND / LOGO SCOMAR â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="sidebar-brand">
        <div class="sidebar-brand-logo-wrapper">
            <img
                src="{{ asset('images/LOGO_SCOMAR.png') }}"
                alt="Logo SCOMAR"
                class="sidebar-brand-logo"
            >
        </div>
        <div class="sidebar-brand-text">
            <span class="brand-name">SCOMAR</span>
            <span class="brand-tagline">Pemantauan Ekosistem Laut Pulau Tunda</span>
        </div>
    </div>

    {{-- â”€â”€ NAVIGASI UTAMA â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <nav class="sidebar-nav" aria-label="Navigasi Utama">

        {{-- LABEL: MENU UTAMA --}}
        <div class="nav-group-label">Menu Utama</div>

        {{-- 1. Dashboard --}}
        <a
            href="{{ route('dashboard') }}"
            class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            data-tooltip="Dashboard"
        >
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
            </span>
            <span class="nav-label">Dashboard</span>
        </a>

        {{-- 2. Grafik Hasil Tangkapan --}}
        <a
            href="{{ route('charts.catches') }}"
            class="nav-item {{ request()->routeIs('charts.catches') ? 'active' : '' }}"
            data-tooltip="Grafik Hasil Tangkapan"
        >
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6"  y1="20" x2="6"  y2="14"/>
                    <line x1="2"  y1="20" x2="22" y2="20"/>
                </svg>
            </span>
            <span class="nav-label">Grafik Hasil Tangkapan</span>
        </a>

        {{-- 3. Grafik Parameter Oseanografi --}}
        <a
            href="{{ route('charts.oceanographic') }}"
            class="nav-item {{ request()->routeIs('charts.oceanographic') ? 'active' : '' }}"
            data-tooltip="Parameter Oseanografi"
        >
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </span>
            <span class="nav-label">Parameter Oseanografi</span>
        </a>


        {{-- 4. Analisis Hubungan Oseanografi dan CPUE --}}
        <a
            href="{{ route('charts.analysis') }}"
            class="nav-item {{ request()->routeIs('charts.analysis*') ? 'active' : '' }}"
            data-tooltip="Analisis Oseanografi & CPUE"
        >
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="5" cy="18" r="2"/>
                    <circle cx="12" cy="10" r="2"/>
                    <circle cx="19" cy="14" r="2"/>
                    <circle cx="8" cy="6" r="2"/>
                    <line x1="7" y1="7" x2="10" y2="9"/>
                    <line x1="14" y1="10" x2="17" y2="13"/>
                    <line x1="13" y1="11" x2="6" y2="17"/>
                    <polyline points="4 14 9 18 15 12 20 16"/>
                </svg>
            </span>
            <span class="nav-label">Analisis Oseanografi &amp; CPUE</span>
        </a>

        {{-- 5. Pemindai Kesegaran Ikan (Animasi AI) --}}
        <a
            href="{{ route('scanner.index') }}"
            class="nav-item scanner-ai-menu {{ request()->routeIs('scanner.index') ? 'active' : '' }}"
            data-tooltip="Pemindai Kesegaran Ikan"
        >
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
                <span class="scanner-ai-badge">AI</span>
                <span class="scanner-pulse-ring"></span>
            </span>
            <span class="nav-label">Pemindai Kesegaran Ikan<span class="scanner-nav-scanline"></span></span>
        </a>

        {{-- 5. Tentang Kami --}}
        <a
            href="{{ route('about') }}"
            class="nav-item {{ request()->routeIs('about') ? 'active' : '' }}"
            data-tooltip="Tentang Kami"
        >
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
            </span>
            <span class="nav-label">Tentang Kami</span>
        </a>

        {{-- â”€â”€ MENU KHUSUS MASTER ADMIN â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        @if(auth()->user()->isMasterAdmin())

            <div class="nav-divider"></div>
            <div class="nav-group-label">Administrasi</div>

            {{-- Pengelolaan Akun Admin --}}
            <a
                href="{{ route('master-admin.accounts.index') }}"
                class="nav-item {{ request()->routeIs('master-admin.accounts.*') ? 'active' : '' }}"
                data-tooltip="Pengelolaan Akun Admin"
            >
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
                <span class="nav-label">Pengelolaan Akun Admin</span>
            </a>

        @endif

        {{-- â”€â”€ PEMISAH SEBELUM MENU PENGGUNA â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div class="nav-divider"></div>
        <div class="nav-group-label">Akun Saya</div>

        {{-- Profil --}}
        <a
            href="{{ route('profile') }}"
            class="nav-item {{ request()->routeIs('profile') ? 'active' : '' }}"
            data-tooltip="Profil"
        >
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </span>
            <span class="nav-label">Profil</span>
        </a>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
            @csrf
            <button
                type="submit"
                class="nav-item"
                style="width:100%; background:none; border:none; text-align:left;"
                data-tooltip="Keluar"
            >
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </span>
                <span class="nav-label">Keluar</span>
            </button>
        </form>

    </nav>



</aside>



