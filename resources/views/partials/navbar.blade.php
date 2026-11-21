{{-- ================================================================
     NAVBAR SCOMAR
     File: resources/views/partials/navbar.blade.php

     Navbar menempel di bagian atas area konten.
     Berisi: toggle sidebar, judul halaman, tombol tema cerah/gelap,
     dan kartu informasi pengguna.
================================================================ --}}

<style>

    /* â”€â”€ ANIMASI & EFEK NAVBAR â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    @keyframes navbarEntry {
        0% {
            opacity: 0.85;
            transform: translateY(-4px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes navShimmerLine {
        0%   { background-position: 200% 0; }
        50%  { background-position: 0% 0; }
        100% { background-position: -200% 0; }
    }

    @keyframes dropdownPop {
        0% {
            opacity: 0;
            transform: translateY(-8px) scale(0.96);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .app-navbar {
        position: sticky !important;
        top: 0 !important;
        z-index: 1050 !important;
        background: var(--bg-navbar) !important;
        border-bottom: 1px solid var(--border-subtle) !important;
        height: var(--navbar-height) !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 24px !important;
        box-sizing: border-box !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06) !important;
        overflow: visible !important;
        backdrop-filter: blur(8px);
        transition: background var(--transition), border-color var(--transition);
    }

    /* Garis aksen animasi di bagian bawah navbar */
    .app-navbar::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(23, 107, 135, 0.35), rgba(56, 189, 248, 0.7), rgba(23, 107, 135, 0.35), transparent);
        background-size: 200% 100%;
        animation: navShimmerLine 6s ease-in-out infinite;
        pointer-events: none;
    }

    /* â”€â”€ NAVBAR INNER WRAPPER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .navbar-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 12px;
        opacity: 1;
        animation: navbarEntry 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* â”€â”€ BAGIAN KIRI: Tombol toggle + Judul halaman â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .navbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        flex: 1;
    }

    .btn-toggle-sidebar {
        background: none;
        border: none;
        padding: 7px;
        border-radius: 8px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        flex-shrink: 0;
        cursor: pointer;
    }

    .btn-toggle-sidebar:hover {
        background: var(--sidebar-hover-bg);
        transform: scale(1.08);
    }

    .btn-toggle-sidebar:active {
        transform: scale(0.94);
    }

    .btn-toggle-mobile {
        display: none;
        background: none;
        border: none;
        padding: 7px;
        border-radius: 8px;
        color: var(--text-primary);
        align-items: center;
        justify-content: center;
        transition: background var(--transition);
        flex-shrink: 0;
    }

    .btn-toggle-mobile:hover {
        background: var(--sidebar-hover-bg);
    }

    @media (max-width: 1024px) {
        .btn-toggle-desktop { display: none; }
        .btn-toggle-mobile  { display: flex; }
    }

    /* Judul halaman */
    .navbar-page-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Breadcrumb di bawah judul */
    .navbar-breadcrumb {
        font-size: 12px;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* â”€â”€ BAGIAN KANAN: Tombol Tema & Info Pengguna â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .navbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    /* â”€â”€ TOMBOL GANTI TEMA (DARK / LIGHT MODE) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .btn-theme-toggle {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: 1px solid var(--border-subtle);
        background: var(--bg-surface);
        color: var(--text-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .btn-theme-toggle:hover {
        border-color: #176B87;
        background: var(--sidebar-hover-bg);
        transform: rotate(20deg) scale(1.08);
        box-shadow: 0 4px 14px rgba(23, 107, 135, 0.22);
    }

    .btn-theme-toggle:active {
        transform: rotate(20deg) scale(0.95);
    }

    .theme-icon-sun {
        color: #F59E0B;
    }

    .theme-icon-moon {
        color: var(--deep-navy);
    }

    [data-theme="dark"] .theme-icon-moon {
        color: var(--cashmere-blue);
    }

    /* â”€â”€ KARTU INFO PENGGUNA â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .user-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 5px 12px 5px 6px;
        border-radius: 50px;
        border: 1px solid var(--border-subtle);
        background: var(--bg-surface);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }

    .user-card:hover {
        border-color: #176B87;
        box-shadow: 0 4px 14px rgba(23, 107, 135, 0.16);
        transform: translateY(-2px);
    }

    /* Avatar inisial */
    .user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--deep-navy);
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
        letter-spacing: 0.5px;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
    }

    .user-card:hover .user-avatar {
        transform: scale(1.08);
        box-shadow: 0 0 10px rgba(23, 107, 135, 0.4);
    }

    [data-theme="dark"] .user-avatar {
        background: var(--merlot);
        color: var(--white);
    }

    /* Teks nama & role */
    .user-info {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
    }

    .user-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        max-width: 130px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .user-role-tag {
        font-size: 10px;
        color: var(--merlot);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    [data-theme="dark"] .user-role-tag {
        color: var(--cashmere-blue);
    }

    /* Chevron dropdown */
    .user-chevron {
        color: var(--text-muted);
        flex-shrink: 0;
        transition: transform var(--transition);
    }

    /* â”€â”€ DROPDOWN MENU AKUN â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .user-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        min-width: 220px;
        z-index: 950;
        overflow: hidden;
        animation: dropdownPop 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        transform-origin: top right;
    }

    .dropdown-header {
        padding: 14px 16px 10px;
        border-bottom: 1px solid var(--border-subtle);
    }

    .dropdown-header .d-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .dropdown-header .d-email {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 2px;
        word-break: break-all;
    }

    .dropdown-item-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        font-size: 13px;
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .dropdown-item-link:hover {
        background: var(--sidebar-hover-bg);
        transform: translateX(4px);
    }

    .dropdown-item-link svg {
        color: var(--text-muted);
        flex-shrink: 0;
    }

    .dropdown-divider {
        height: 1px;
        background: var(--border-subtle);
        margin: 4px 0;
    }

    .dropdown-item-logout {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        font-size: 13px;
        color: var(--merlot);
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        transition: all 0.2s ease;
        font-family: inherit;
        font-weight: 600;
    }

    .dropdown-item-logout:hover {
        background: rgba(115, 52, 58, 0.1);
        transform: translateX(4px);
    }

    /* â”€â”€ RESPONSIVE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    @media (max-width: 640px) {
        .navbar-page-title {
            font-size: 14.5px;
            max-width: 180px;
        }
        .navbar-breadcrumb {
            font-size: 11px;
            max-width: 180px;
        }
        .navbar-right {
            gap: 8px;
        }
    }

    @media (max-width: 480px) {
        .user-info, .user-chevron {
            display: none !important;
        }
        .user-card {
            padding: 3px !important;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            font-size: 12px;
        }
        .navbar-breadcrumb {
            display: none !important;
        }
        .navbar-page-title {
            font-size: 14px;
            max-width: 140px;
        }
        .btn-theme-toggle {
            width: 34px;
            height: 34px;
        }
        .btn-toggle-mobile {
            padding: 6px;
        }
        .user-dropdown {
            min-width: 200px;
            max-width: calc(100vw - 20px);
            right: 0;
        }
    }

    @media (max-width: 380px) {
        .navbar-page-title {
            font-size: 13px;
            max-width: 110px;
        }
        .navbar-left {
            gap: 8px;
        }
        .btn-theme-toggle {
            width: 32px;
            height: 32px;
        }
    }


    /* == NAVBAR AI SCANNER BADGE == */
    @keyframes navAiBadgePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(23, 107, 135, 0.6); transform: scale(1); }
        50%       { box-shadow: 0 0 0 5px rgba(23, 107, 135, 0); transform: scale(1.04); }
    }
    @keyframes navAiDotBlink {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.4; }
    }
    .navbar-ai-scanner-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: linear-gradient(135deg, rgba(36,54,75,0.9) 0%, rgba(23,107,135,0.9) 100%);
        border: 1px solid rgba(165, 184, 208, 0.4);
        border-radius: 20px;
        padding: 4px 10px 4px 7px;
        font-size: 11px;
        font-weight: 700;
        color: #A5B8D0;
        letter-spacing: 0.3px;
        animation: navAiBadgePulse 2.5s ease-in-out infinite;
        backdrop-filter: blur(6px);
        white-space: nowrap;
    }
    .navbar-ai-scanner-badge .nav-ai-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #38BDF8;
        flex-shrink: 0;
        animation: navAiDotBlink 1.4s ease-in-out infinite;
        box-shadow: 0 0 5px rgba(56, 189, 248, 0.8);
    }
    @media (prefers-reduced-motion: reduce) {
        .navbar-ai-scanner-badge,
        .navbar-ai-scanner-badge .nav-ai-dot { animation: none !important; }
    }
    @media (max-width: 640px) {
        .navbar-ai-scanner-badge { display: none; }
    }
</style>

{{-- ================================================================
     ELEMEN NAVBAR
================================================================ --}}
<header class="app-navbar" x-data="{ dropdownOpen: false }">
    <div class="navbar-inner">

        {{-- â”€â”€ KIRI: Tombol toggle + Judul halaman â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div class="navbar-left">

            {{-- Tombol toggle sidebar â€” DESKTOP --}}
            <button
                class="btn-toggle-sidebar btn-toggle-desktop"
                id="sidebarToggleDesktop"
                aria-label="Toggle Sidebar"
                title="Ciutkan / perlebar sidebar"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="6"  x2="21" y2="6"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>

            {{-- Tombol toggle sidebar â€” MOBILE --}}
            <button
                class="btn-toggle-mobile"
                id="sidebarToggleMobile"
                aria-label="Buka Menu"
                title="Buka navigasi"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="6"  x2="21" y2="6"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>

            {{-- Judul halaman --}}
            <div>
                <div class="navbar-page-title">
                    @yield('page-title', 'SCOMAR')
                </div>
                @hasSection('page-breadcrumb')
                    <div class="navbar-breadcrumb">
                        @yield('page-breadcrumb')
                    </div>
                @endif
            </div>

        </div>

        {{-- â”€â”€ KANAN: Tombol Ganti Tema + Kartu Pengguna â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div class="navbar-right">

            {{-- Badge AI Scanner Aktif (hanya tampil di halaman scanner) --}}
            @if(request()->routeIs('scanner*'))
            <span class="navbar-ai-scanner-badge">
                <span class="nav-ai-dot"></span>
                AI Scanner Aktif
            </span>
            @endif

            {{-- 1. TOMBOL MODE GELAP / CERAH --}}
            <button
                type="button"
                id="themeToggleBtn"
                class="btn-theme-toggle"
                aria-label="Ubah mode tema"
                title="Ubah mode tampilan (Cerah / Gelap)"
            >
                {{-- Ikon Bulan (Aktif saat Light Mode) --}}
                <svg class="theme-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
                {{-- Ikon Matahari (Aktif saat Dark Mode) --}}
                <svg class="theme-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
            </button>

            @php
                $user     = auth()->user();
                $initials = strtoupper(substr($user->name, 0, 1));
                $nameParts = explode(' ', trim($user->name));
                if (count($nameParts) > 1) {
                    $initials = strtoupper($nameParts[0][0] . end($nameParts)[0]);
                }

                $roleLabelMap = [
                    'user'         => 'Pengguna',
                    'admin'        => 'Admin',
                    'master_admin' => 'Master Admin',
                ];
                $roleLabel = $roleLabelMap[$user->role ?? 'user'] ?? 'Pengguna';
            @endphp

            {{-- 2. KARTU INFO PENGGUNA DENGAN DROPDOWN --}}
            <div class="user-card" @click="dropdownOpen = !dropdownOpen" @click.outside="dropdownOpen = false">

                {{-- Avatar --}}
                <div class="user-avatar">{{ $initials }}</div>

                {{-- Nama & role --}}
                <div class="user-info">
                    <span class="user-name">{{ $user->name }}</span>
                    <span class="user-role-tag">{{ $roleLabel }}</span>
                </div>

                {{-- Chevron --}}
                <svg class="user-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round"
                     :style="dropdownOpen ? 'transform:rotate(180deg)' : ''">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>

                {{-- Dropdown menu --}}
                <div class="user-dropdown" x-show="dropdownOpen" style="display:none;">

                    {{-- Info akun --}}
                    <div class="dropdown-header">
                        <div class="d-name">{{ $user->name }}</div>
                        <div class="d-email">{{ $user->email }}</div>
                    </div>

                    {{-- Link ke profil --}}
                    <a href="{{ route('profile') }}" class="dropdown-item-link">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Profil Saya
                    </a>

                    {{-- Link ke Tentang Kami --}}
                    <a href="{{ route('about') }}" class="dropdown-item-link">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        Tentang SCOMAR
                    </a>

                    <div class="dropdown-divider"></div>

                    {{-- Tombol logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item-logout">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Keluar
                        </button>
                    </form>

                </div>

            </div>

        </div>

    </div>
</header>


