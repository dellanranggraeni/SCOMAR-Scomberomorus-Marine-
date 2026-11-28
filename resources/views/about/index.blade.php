{{-- ================================================================
     HALAMAN TENTANG KAMI (ABOUT US)
     File: resources/views/about/index.blade.php

     Halaman informasi mengenai aplikasi SCOMAR, tim peneliti,
     dan program pemantauan ekosistem laut di Pulau Tunda.
================================================================ --}}

@extends('layouts.app')

{{-- Judul tab browser --}}
@section('title', 'Tentang Kami')

{{-- Judul halaman di navbar --}}
@section('page-title', 'Tentang Kami')
@section('page-breadcrumb', 'SCOMAR / Tentang Kami')

@section('content')

<style>
    /* ── CONTAINER UTAMA TENTANG KAMI ─────────────────────────────── */
    .about-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
        max-width: 1080px;
        margin: 0 auto;
    }

    /* ── HERO BANNER ──────────────────────────────────────────────── */
    .about-hero {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 36px 36px;
        box-shadow: var(--card-shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
        flex-wrap: wrap;
        transition: background var(--transition), border-color var(--transition);
    }

    .about-hero-text {
        flex: 1;
        min-width: 280px;
    }

    .about-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(115, 52, 58, 0.1);
        color: var(--merlot);
        border: 1px solid rgba(115, 52, 58, 0.25);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 12px;
    }

    [data-theme="dark"] .about-hero-badge {
        color: var(--cashmere-blue);
        background: rgba(165, 184, 208, 0.12);
        border-color: rgba(165, 184, 208, 0.25);
    }

    .about-hero-text h1 {
        font-size: 26px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.3;
        margin-bottom: 10px;
    }

    .about-hero-text p {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.7;
    }

    .about-hero-logos {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-shrink: 0;
        background: #FFFFFF;
        padding: 12px 22px;
        border-radius: 14px;
        border: 1px solid var(--border-subtle);
        box-shadow: 0 2px 8px rgba(36, 54, 75, 0.06);
        transition: background var(--transition), border-color var(--transition), box-shadow var(--transition);
    }

    .about-hero-logos img {
        height: 56px;
        object-fit: contain;
        display: block;
    }

    /* Latar belakang putih kontras logo SCOMAR & UPI saat mode malam (dark mode) */
    [data-theme="dark"] .about-hero-logos {
        background: #FFFFFF !important;
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }

    /* ── CARD SECTIONS ────────────────────────────────────────────── */
    .about-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 28px 32px;
        box-shadow: var(--card-shadow);
        transition: background var(--transition), border-color var(--transition);
    }

    .about-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border-subtle);
    }

    .about-card-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(115, 52, 58, 0.12);
        color: var(--merlot);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    [data-theme="dark"] .about-card-icon {
        color: var(--cashmere-blue);
        background: rgba(165, 184, 208, 0.15);
    }

    .about-card-header h2 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* ── AREA KONTEN EDITABLE PENGGUNA ────────────────────────────── */
    .editable-box {
        background: var(--bg-body);
        border: 1.5px dashed var(--cashmere-blue);
        border-radius: 12px;
        padding: 24px;
        color: var(--text-primary);
        font-size: 14px;
        line-height: 1.8;
        position: relative;
    }

    .editable-tag {
        display: inline-block;
        background: var(--merlot);
        color: var(--white);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 3px 8px;
        border-radius: 6px;
        margin-bottom: 12px;
    }

    /* ── INFO KONTAK / TIM ────────────────────────────────────────── */
    .info-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 10px;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13.5px;
        color: var(--text-secondary);
    }

    .info-row strong {
        color: var(--text-primary);
        min-width: 140px;
    }

    /* ── RESPONSIVE TENTANG KAMI ─────────────────────────────────── */
    @media (max-width: 768px) {
        .about-hero {
            padding: 26px 22px;
        }
        .about-hero-logos {
            width: 100%;
            justify-content: center;
        }
        .about-card {
            padding: 22px 20px;
        }
    }

    @media (max-width: 640px) {
        .about-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 20px 16px;
            gap: 20px;
        }
        .about-hero-text h1 {
            font-size: 22px;
        }
        .about-hero-logos {
            padding: 10px 16px;
            gap: 14px;
        }
        .about-hero-logos img {
            height: 44px;
        }
        .about-card {
            padding: 18px 14px;
        }
        .editable-box {
            padding: 16px 14px;
            font-size: 13px;
        }
        .info-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .info-row strong {
            min-width: unset;
        }
    }

    @media (max-width: 380px) {
        .about-hero-logos img {
            height: 38px;
        }
        .about-hero-text h1 {
            font-size: 20px;
        }
    }
</style>

<div class="about-container">

    {{-- ================================================================
         1. HERO HEADER: IDENTITAS SCOMAR & UPI
    ================================================================ --}}
    <section class="about-hero">
        <div class="about-hero-text">
            <span class="about-hero-badge">ABOUT US DEVELOPMENT TEAM</span>
            <h1>SCOMAR</h1>
            <p>
                <strong>
                    <span style="text-align: justify; display: block;">
                        Platform Digital dalam Melakukan Pemantauan Ekosistem dan Hasil Tangkapan Scomberomus commerson di Pulau Tunda</strong>
                        
                        Platform digital kelautan dan perikanan terpadu yang mengintegrasikan sistem pemantauan zona potensi penangkapan ikan (ZPPI), analisis hasil tangkapan,
                        analisis parameter oseanografi, hubungan parameter oseanografi dengan CPUE (Catch Per Unit Effort) 
                        serta teknologi kecerdasan buatan yang diterapkan dengan alat pendeteksi tingkat kesegaran dan identifikasi genus ikan Scomberomorus atau Non-Scomberomorus.
                    </span>
            </p>
        </div>
        <div class="about-hero-logos">
            <img src="{{ asset('images/LOGO_SCOMAR.png') }}" alt="Logo SCOMAR" title="SCOMAR">
            <img src="{{ asset('images/LOGO_UPI.png') }}" alt="Logo UPI" title="Universitas Pendidikan Indonesia">
        </div>
    </section>

    {{-- ================================================================
         2. PROFIL / LATAR BELAKANG SCOMAR
         ----------------------------------------------------------------
         [CATATAN UNTUK PENGGUNA]:
         Anda dapat mengganti teks di dalam tag <div class="editable-box">
         di bawah ini dengan tulisan latar belakang lengkap Anda.
    ================================================================ --}}
    <section class="about-card">
        <div class="about-card-header">
            <div class="about-card-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
            </div>
            <h2>Latar Belakang & Deskripsi Proyek</h2>
        </div>

        <div class="editable-box">
            <span class="editable-tag">Latar Belakang Project SCOMAR</span>
            <p>
                Pulau Tunda merupakan salah satu pulau di Kabupaten Serang, Provinsi Banten yang memiliki potensi sumber daya perikanan pelagis yang melimpah, khususnya ikan tenggiri (<em>Scomberomorus</em> commerson). Keberlanjutan ekosistem laut dan terumbu karang di kawasan ini memerlukan pemantauan berkala yang efektif, terukur, dan berbasis data ilmiah.
            </p>
            <p style="margin-top: 12px;">
                SCOMAR dikembangkan sebagai solusi digital cerdas untuk membantu nelayan, peneliti, mahasiswa, serta pengambil kebijakan dalam melakukan pemantauan zona potensi penangkapan ikan (ZPPI), memahami kondisi oseanografi perairan, serta dapat mengetahui tingkat kesegaran dan identifikasi genus Scomberomorus atau Non-Scomberomorus hasil tangkapan nelayan secara berkelanjutan.
            </p>
            <!-- Anda bisa menambahkan paragraf baru di sini -->
        </div>
        <!-- ============================================================ -->
    </section>

    <!-- 4. TIM PENGEMBANG & PENELITI (UPI) -->
    <section class="about-card">
        <div class="about-card-header">
            <div class="about-card-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h2>Tim Peneliti & Pengembang</h2>
        </div>

        <!-- >>> [TEMPAT ANDA MEMASUKKAN NAMA TIM / DOSEN / MAHASISWA] <<< -->
        <div class="editable-box">
            <span class="editable-tag">TIM PENGEMBANG WEBSITE SCOMAR</span>
            <div class="info-list">
                <div class="info-row">
                    <strong>Institusi:</strong>
                    <span>Universitas Pendidikan Indonesia (UPI) Kampus Serang</span>
                </div>
                <div class="info-row">
                    <strong>Fokus Riset:</strong>
                    <span style="text-align: justify; display: block;">
                        Fokus riset SCOMAR adalah perancangan dan pengembangan sistem WebGIS yang terintegrasi AI untuk memetakan sebaran ekosistem terumbu karang, 
                        menganalisis potensi daerah penangkapan ikan berdasarkan parameter oseanografi, serta memberikan rekomendasi alat tangkap yang selektif dan efektif untuk mendukung penangkapan berkelanjutan di perairan Pulau Tunda. 
                        Sistem ini juga dilengkapi fitur analisis hasil tangkapan, analisis parameter oseanografi, hubungan antara parameter oseanografi dengan CPUE (Catch Per Unit Effort) serta pemindaian berbasis AI (Artificial Intelligent) untuk identifikasi genus dan tingkat kesegaran ikan.</span>
                </div>
                <div class="info-row">
                    <strong>Dosen Pembimbing:</strong>
                    <span> Dr. Novi Sofia Fitriasari, S.Si., M.T. </span>
                </div>
                <div class="info-row">
                    <strong>Ketua Tim :</strong>
                    <span> Della Nur Anggraeni </span>
                </div>
                <div class="info-row">
                    <strong>Anggota Tim:</strong>
                    <span>
                            <ol>
                                <li>Wahyu Rina Dwi Oktavia - Pengolahan Data Utama dan Pendukung (GIS)</li>
                                <li>Muhammad Muqit - Pengolahan Data Utama dan Administrasi</li>
                                <li>Alexander Ryan Dokeng - Pembuatan Design Poster</li>
                            </ol>
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. INFORMASI KONTAK & ALAMAT --}}
    <section class="about-card">
        <div class="about-card-header">
            <div class="about-card-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
            <h2>Lokasi & Kontak</h2>
        </div>

        <div class="editable-box">
            <span class="editable-tag">Lokasi Penelitian</span>
            <div class="info-list">
                <div class="info-row">
                    <strong>Lokasi Riset:</strong>
                    <span>Pulau Tunda, Kecamatan Tirtayasa, Kabupaten Serang, Banten, Indonesia</span>
                </div>
                <div class="info-row">
                    <strong>Kampus:</strong>
                    <span>Universitas Pendidikan Indonesia (UPI) Kampus Serang</span>
                </div>
                <div class="info-row">
                    <strong>Email Kontak:</strong>
                    <span>Scomar14@gmail.com</span>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection
