<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SCOMAR — Platform Digital AI Pemantauan Ekosistem Laut & Perikanan Berkelanjutan di Pulau Tunda</title>
    <meta name="description" content="Platform digital AI untuk konservasi ekosistem laut, analisis daerah penangkapan ikan (ZPPI), pemantauan parameter oseanografi, dan perikanan berkelanjutan Pulau Tunda, Serang, Banten.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/LOGO_SCOMAR.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Landing Page Dedicated Stylesheet (versi otomatis mengikuti waktu ubah file, anti-cache) -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    @if(request()->has('hide_hero'))
    <style>
        .hero-section { display: none !important; }
        .landing-nav { position: sticky !important; background: rgba(20, 34, 51, 0.98) !important; }
    </style>
    @endif
    @if(request()->has('hide_cards'))
    <style>
        .cards-section { display: none !important; }
    </style>
    @endif
</head>
<body>

    <!-- ============================================================
         1. NAVIGATION BAR
         - Transparan pada Hero section, solid/blur saat scroll.
         - Teks navigasi murni (tanpa ikon/emoji).
         - Responsif dengan drawer navigasi pada layar mobile.
         ============================================================ -->
    <nav class="landing-nav" role="navigation" aria-label="Navigasi Utama">
        <div class="container nav-container">
            <a href="#beranda" class="nav-brand" aria-label="SCOMAR Beranda">
                <img src="{{ asset('images/LOGO_SCOMAR.png') }}" alt="Logo SCOMAR" class="nav-brand-logo">
                <div class="nav-brand-text">
                    <span class="brand-title">SCOMAR</span>
                    <span class="brand-subtitle">Scomberomorus Marine</span>
                </div>
            </a>

            <!-- Menu Tautan Desktop -->
            <ul class="nav-links">
                <li><a href="#beranda" class="nav-link">Beranda</a></li>
                <li><a href="#pulau-tunda" class="nav-link">Pulau Tunda</a></li>
                <li><a href="#ikan-tenggiri" class="nav-link">Ikan Tenggiri</a></li>
                <li><a href="#terumbu-karang" class="nav-link">Terumbu Karang</a></li>
                <li><a href="#komoditas-makrozoobentos" class="nav-link">Makrozoobentos</a></li>
                <li><a href="#fitur-sistem" class="nav-link">Layanan</a></li>
            </ul>

            <!-- Tombol Aksi Autentikasi Desktop -->
            <div class="nav-auth">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-nav-dashboard">Buka Dashboard</a>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline; margin:0;">
                        @csrf
                        <button type="submit" class="btn-nav-login" style="padding: 8px 14px;">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-nav-login">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-nav-register">Daftar</a>
                @endauth
            </div>

            <!-- Hamburger Button (Mobile) -->
            <button type="button" class="mobile-toggle" aria-label="Buka menu navigasi" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Mobile Navigation Drawer -->
    <div class="mobile-drawer" role="dialog" aria-modal="true" aria-label="Menu Navigasi Mobile">
        <div class="drawer-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-subtle);">
            <div style="display:flex; align-items:center; gap:10px;">
                <img src="{{ asset('images/LOGO_SCOMAR.png') }}" alt="Logo SCOMAR" style="width:32px; height:32px; object-fit:contain;">
                <div>
                    <div style="font-size:15px; font-weight:800; color:var(--deep-navy);">SCOMAR</div>
                    <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em;">Scomberomorus Marine</div>
                </div>
            </div>
        </div>
        <ul style="list-style:none; padding:0; margin:0 0 24px 0; display:flex; flex-direction:column; gap:6px;">
            <li><a href="#beranda" class="nav-link">Beranda</a></li>
            <li><a href="#pulau-tunda" class="nav-link">Tentang Pulau Tunda</a></li>
            <li><a href="#ikan-tenggiri" class="nav-link">Ikan Tenggiri</a></li>
            <li><a href="#terumbu-karang" class="nav-link">Kawasan Terumbu Karang</a></li>
            <li><a href="#komoditas-makrozoobentos" class="nav-link">Komoditas Makrozoobentos</a></li>
            <li><a href="#fitur-sistem" class="nav-link">Layanan Sistem</a></li>
        </ul>
        <div class="nav-auth" style="display:flex; flex-direction:column; gap:10px;">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-nav-dashboard" style="text-align:center;">Buka Dashboard</a>
                <form action="{{ route('logout') }}" method="POST" style="width:100%; margin:0;">
                    @csrf
                    <button type="submit" class="btn-nav-login" style="width:100%; text-align:center;">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-nav-login" style="text-align:center;">Masuk</a>
                <a href="{{ route('register') }}" class="btn-nav-register" style="text-align:center;">Daftar</a>
            @endauth
        </div>
    </div>

    <!-- ============================================================
         2. HERO SECTION
         - Foto latar asli perairan Pulau Tunda, Serang, Banten.
         - Overlay gradasi gelap untuk keterbacaan teks maksimal.
         - Layout full viewport responsif.
         ============================================================ -->
    <header id="beranda" class="hero-section" style="background-image: url('{{ asset('images/landing/PULAU-TUNDA2.jpg') }}');">
        <div class="hero-overlay"></div>
        <div class="container hero-container">
            <div class="hero-content">
                <span class="hero-tag">Platform Digital AI untuk Konservasi Laut dan Perikanan Berkelanjutan</span>
                <h1 class="hero-title">
                    Mengenal Potensi Laut, Menjaga Konservasi Perairan Pulau Tunda.
                </h1>
                <p class="hero-description">
                    SCOMAR adalah platform digital AI untuk memetakan terumbu karang, menganalisis daerah penangkapan ikan, dan memantau parameter oseanografi guna mendukung konservasi laut dan perikanan berkelanjutan di Pulau Tunda.
                </p>
                <div class="hero-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-dashboard-hero">Masuk ke Dashboard Sistem</a>
                    @else
                        <a href="#fitur-sistem" class="btn-primary">Jelajahi Layanan</a>
                        <a href="#pulau-tunda" class="btn-outline">Kenali Pulau Tunda</a>
                    @endauth
                </div>
            </div>
        </div>
        <div class="hero-attribution">
            Latar Belakang: Panorama perairan Pulau Tunda, Kabupaten Serang, Provinsi Banten.
        </div>
    </header>

    <!-- ============================================================
         3. SECTION INFORMASI (DUA KARTU UTAMA SEIMBANG)
         - Kartu 1: Pulau Tunda (Geografi, pesisir, karang, perikanan, kelestarian).
         - Kartu 2: Ikan Tenggiri / Scomberomorus commerson (Klasifikasi, morfologi, pola warna, habitat, nilai ekonomis).
         - Komposisi visual dan tinggi elemen presisi dan simetris.
         ============================================================ -->
    <section id="informasi" class="cards-section">
        <div class="container">
            
            <div class="section-header reveal-on-scroll">
                <span class="section-tag">Eksplorasi Wilayah & Komoditas Utama</span>
                <h2 class="section-title">Mengenal Potensi dan Karakteristik Perairan Pulau Tunda</h2>
                <p class="section-desc">
                    Kajian terpadu kondisi oseanografi, formasi ekosistem bentik, serta biologi komoditas unggulan Scomberomorus commerson demi terwujudnya ketahanan sumber daya kelautan dan perikanan yang bertanggung jawab.
                </p>
            </div>

            <div class="cards-grid">
                
                <!-- KARTU 1: PULAU TUNDA -->
                <article id="pulau-tunda" class="info-card reveal-on-scroll">
                    <div class="card-media">
                        <img src="{{ asset('images/landing/PULAU TUNDA.jpg') }}" alt="Panorama Pesisir dan Perairan Pulau Tunda, Serang" class="card-image" loading="lazy">
                        <div class="card-media-overlay"></div>
                        <span class="card-category-badge">Wilayah Riset & Kawasan Konservasi</span>
                        <span class="card-photo-note">Perairan Pulau Tunda, Kabupaten Serang</span>
                    </div>

                    <div class="card-body">
                        <div class="card-header-group">
                            <h3 class="card-title">Pulau Tunda</h3>
                            <div class="card-location-meta">Desa: Wargasara, Kecamatan Tirtayasa, Kabupaten Serang, Provinsi Banten</div>
                        </div>

                        <div class="card-aspects-list">
                            <!-- Aspek 1: Geografi -->
                            <div class="aspect-block">
                                <span class="aspect-label">Letak Geografis & Gambaran Umum</span>
                                <p class="aspect-text">
                                    Pulau Tunda merupakan pulau kecil berpenghuni di Laut Jawa sebelah utara Teluk Banten, dengan titik koordinat sekitar 05°48'43" LS dan 106°16'47" BT, secara administratif berada dalam wilayah Kecamatan Tirtayasa, Kabupaten Serang. Wilayah ini menempati posisi strategis di lintasan pesisir utara Banten. Hamparan laut tropisnya memiliki kualitas air yang optimal sebagai lokasi pemijahan ikan pelagis, didukung perairan jernih, topografi landai berpasir putih, serta ekosistem terumbu karang yang masih melimpah.
                                </p>
                            </div>

                            <!-- Aspek 2: Pesisir & Oseanografi -->
                            <div class="aspect-block">
                                <span class="aspect-label">Karakteristik Wilayah Pesisir & Perairan</span>
                                <p class="aspect-text">
                                    Morfologi pesisirnya memiliki struktur terumbu tepi yang beralih menjadi lereng tubir dengan batimetri kedalaman antara 5 hingga 40 meter. Dinamika oseanografi perairan Pulau Tunda dipengaruhi kuat oleh sirkulasi arus monsun Asia-Australia, variasi suhu permukaan laut musiman, serta pola pasang surut semidiurnal.
                                </p>
                            </div>

                            <!-- Aspek 3: Ekosistem Terumbu Karang -->
                            <div class="aspect-block">
                                <span class="aspect-label">Ekosistem Laut & Terumbu Karang</span>
                                <p class="aspect-text">
                                    Memiliki ekosistem terumbu karang tepi (fringing reef) yang mengelilingi hampir seluruh pulau. Formasi karang keras (hard coral) dan karang lunak berfungsi sebagai benteng perlindungan pantai alami, daerah asuhan (nursery ground), dan tempat pemijahan (spawning ground) bagi ratusan spesies ikan karang dan organisme bentik.
                                </p>
                            </div>

                            <!-- Aspek 4: Perikanan & Masyarakat -->
                            <div class="aspect-block">
                                <span class="aspect-label">Potensi Perikanan & Aktivitas Pesisir</span>
                                <p class="aspect-text">
                                    Mata pencaharian utama masyarakat Pulau Tunda berakar pada perikanan tangkap skala kecil dan budidaya perairan. Komoditas pelagis utama seperti ikan tenggiri, tongkol, dan kembung menjadi penggerak ekonomi nelayan tradisional dengan armada perahu motor berkapasitas di bawah 5 GT.
                                </p>
                            </div>

                            <!-- Aspek 5: Pengelolaan Berkelanjutan -->
                            <div class="aspect-block">
                                <span class="aspect-label">Pengelolaan Ekosistem Berkelanjutan</span>
                                <p class="aspect-text">
                                    Tingginya interaksi aktivitas penangkapan dengan zona terumbu karang menuntut pengawasan berbasis data spasial akurat. Pengelolaan terpadu melalui zonasi konservasi, pemantauan wilayah persebaran terumbu karang dengan metode Lyzenga, serta penerapan alat tangkap efektif dan selektif menjadi kunci menjaga ketahanan pangan dan kelestarian hayati Pulau Tunda.
                                </p>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- KARTU 2: IKAN TENGGIRI (Scomberomorus commerson) -->
                <article id="ikan-tenggiri" class="info-card reveal-on-scroll">
                    <div class="card-media">
                        <img src="{{ asset('images/landing/ScomberomorCommerson2.jpg') }}" alt="Spesimen Ikan Tenggiri (Scomberomorus commerson)" class="card-image" loading="lazy">
                        <div class="card-media-overlay"></div>
                        <span class="card-category-badge">Spesies Target & Komoditas Unggulan</span>
                        <span class="card-photo-note">Spesies Scomberomorus commerson (Sumber: FishBase / FAO)</span>
                    </div>

                    <div class="card-body">
                        <div class="card-header-group">
                            <h3 class="card-title">Ikan Tenggiri</h3>
                            <div class="card-scientific-name">Scomberomorus commerson</div>
                            <div class="card-location-meta">Famili Scombridae &bull; Ordo Scombriformes</div>
                        </div>

                        <div class="card-aspects-list">
                            <!-- Aspek 1: Klasifikasi -->
                            <div class="aspect-block">
                                <span class="aspect-label">Pengenalan & Klasifikasi Umum</span>
                                <p class="aspect-text">
                                    Ikan Tenggiri (Narrow-barred Spanish Mackerel) merupakan anggota famili Scombridae yang menduduki posisi penting sebagai predator puncak (top trophic level) pada rantai makanan perairan pesisir. Spesies ini hidup soliter atau dalam kelompok kecil perenang cepat saat mengejar kawanan ikan pelagis kecil.
                                </p>
                            </div>

                            <!-- Aspek 2: Morfologi -->
                            <div class="aspect-block">
                                <span class="aspect-label">Karakteristik Morfologi & Ciri Fisik</span>
                                <p class="aspect-text">
                                    Mempunyai bangun tubuh memanjang, ramping, dan hidrodinamis menyerupai torpedo (fusiform) berkulit licin dengan corak garis vertikal bergelombang yang memfasilitasi renang berkecepatan tinggi. Moncong meruncing dengan celah mulut lebar berisi barisan gigi segitiga pipih yang tajam dan kokoh, serta deretan sirip tambahan (finlet) dan sirip ekor kokoh bercabang dalam (deeply forked).
                                </p>
                            </div>

                            <!-- Aspek 3: Bentuk & Warna -->
                            <div class="aspect-block">
                                <span class="aspect-label">Bentuk Tubuh & Pola Warna</span>
                                <p class="aspect-text">
                                    Permukaan punggung berwarna biru keperakan gelap hingga abu-abu kehijauan metalik, bertransisi menjadi putih perak di sisi perut. Karakter pembeda utamanya adalah kehadiran puluhan garis tipis vertikal bergelombang gelap (narrow bars) pada kedua sisi lambung dari belakang insang hingga pangkal ekor.
                                </p>
                            </div>

                            <!-- Aspek 4: Habitat & Persebaran -->
                            <div class="aspect-block">
                                <span class="aspect-label">Habitat & Persebaran Spasial</span>
                                <p class="aspect-text">
                                    Terdistribusi luas di zona perairan tropis dan subtropis Indo-Pasifik Barat. Menghuni perairan neritik hangat dari batas pesisir hingga tubir terumbu karang pada kedalaman 10 sampai 70 meter. Sangat responsif terhadap parameter oseanografi seperti gradien suhu permukaan laut dan kelimpahan klorofil-a.
                                </p>
                            </div>

                            <!-- Aspek 5: Nilai Ekonomis & Kelestarian -->
                            <div class="aspect-block">
                                <span class="aspect-label">Karakteristik Hidup & Nilai Ekonomis</span>
                                <p class="aspect-text">
                                    Merupakan komoditas perikanan berdaya jual tinggi dengan kualitas daging tebal kaya omega-3 dan protein. Di perairan Pulau Tunda, spesies ini menjadi target penangkapan nelayan pancing ulur dan tonda. Pengelolaan kuota tangkap serta kepatuhan zonasi terumbu karang esensial demi menghindari penangkapan berlebih (overfishing). Harga jual dari ikan tenggiri terdapat di angka 70 ribu/kg tergantung dengan kondisi ikan yang didapatkan, dengan ukuran tubuh ikan sekitar 30cm hingga 1 meter per-ekor.
                                </p>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- KARTU 3: KAWASAN TERUMBU KARANG -->
                <article id="terumbu-karang" class="info-card reveal-on-scroll">
                    <div class="card-media">
                        <img src="{{ asset('images/landing/terumbu-karang.jpg') }}" alt="Panorama Bawah Laut Pulau Tunda" class="card-image" loading="lazy">
                        <div class="card-media-overlay"></div>
                        <span class="card-category-badge"> Kawasan Potensi KOnservasi Terumbu Karang</span>
                        <span class="card-photo-note">Perairan Pulau Tunda, Kabupaten Serang</span>
                    </div>

                    <div class="card-body">
                        <div class="card-header-group">
                            <h3 class="card-title">Kawasan Potensi Konservasi Terumbu Karang</h3>
                            <div class="card-location-meta">Perairan Pulau Tunda, Desa Wargasara, Kecamatan Tirtayasa, Kabupaten Serang, Provinsi Banten</div>
                        </div>

                        <div class="card-aspects-list">
                                <!-- Aspek 1 -->
                                <div class="aspect-block">
                                    <span class="aspect-label">Gambaran Umum dan Potensi Wilayah Konservasi Terumbu Karang</span>
                                    <p class="aspect-text">
                                        Pulau Tunda ada di pesisir utara Banten, tepatnya di Desa Wargasara, Kecamatan Tirtayasa, Kabupaten Serang. Pulau ini tidak terlalu besar, luas daratannya sekitar 289,79 hektare. Di sekelilingnya terbentang terumbu karang seluas sekitar 224 hektare, dan lebih dari separuhnya, sekitar 120 hektare, masih berupa karang hidup. Sisanya karang mati dan pecahan karang. Di sisi timur, tempat ombak biasa pecah, karang yang tumbuh kebanyakan berbentuk masif seperti <em>Goniastrea</em>, sedangkan di sisi utara jenis karangnya lebih beragam, dari <em>Acropora</em> bercabang sampai karang foliose (karang berbentuk lembaran). Terumbu karang dapat menjadi rumah bagi banyak ikan karang, terutama ikan <em>Chromis viridis</em> (betok hijau), ikan kakatua (parrot fish), ikan lolosi cantik (<em>Caesio teres</em>), dan juga tempat favorit untuk snorkeling. Dengan kekayaan seperti itu, Pulau Tunda punya potensi besar untuk dikembangkan sebagai wilayah konservasi.
                                    </p>
                                </div>

                                <!-- Aspek 2 -->
                                <div class="aspect-block">
                                    <span class="aspect-label">Kondisi Terumbu Karang</span>
                                    <p class="aspect-text">
                                        Kondisi tutupan karang hidup di Pulau Tunda tergolong sedang hingga baik, yaitu sekitar 26% hingga 68% tergantung lokasi dan waktu pengamatan. Tutupan terendah cenderung terdapat di sekitar lokasi snorkeling yang ramai, dan karang mati paling banyak ditemukan di dekat dermaga. Parameter perairannya masih dapat ditoleransi karang, dengan suhu sekitar 28–32°C, salinitas sekitar 29–33‰, dan kecerahan yang baik sehingga cahaya mencapai dasar perairan. Kandungan padatan tersuspensi (jumlah partikel yang berada di air) juga rendah, sekitar 0,06–0,17 mg/L. Pemantauan dilakukan dengan Underwater Photo Transect (UPT) untuk tutupan karang, Underwater Visual Census (UVC) untuk ikan karang, serta pengukuran parameter perairan seperti suhu, salinitas, dan kecerahan.
                                    </p>
                                </div>
                            </div>
                        </div>
                </article>

                <!-- KARTU 4: KOMODITAS MAKROZOOBENTOS -->
                <article id="komoditas-makrozoobentos" class="info-card reveal-on-scroll">
                    <div class="card-media">
                        <img src="{{ asset('images/landing/makrozoobentos.jpg') }}" alt="Dokumentasi Makrozoobentos" class="card-image" loading="lazy">
                        <div class="card-media-overlay"></div>
                        <span class="card-category-badge">Komoditas Makrozoobentos</span>
                        <span class="card-photo-note">Perairan Pulau Tunda, Kabupaten Serang</span>
                    </div>

                    <div class="card-body">
                        <div class="card-header-group">
                            <h3 class="card-title">Komoditas Makrozoobentos</h3>
                            <div class="card-location-meta">Perairan Pulau Tunda, Desa Wargasara, Kecamatan Tirtayasa, Kabupaten Serang, Provinsi Banten</div>
                        </div>

                        <div class="card-aspects-list">
                                <!-- Aspek 1 -->
                                <div class="aspect-block">
                                    <span class="aspect-label">Gambaran Umum Makrozoobentos</span>
                                    <p class="aspect-text">
                                        Makrozoobentos adalah organisme dasar perairan berukuran lebih dari 0,5 mm yang hidup menetap dan peka terhadap perubahan lingkungan sehingga dipakai sebagai bioindikator kualitas air, dan di Pulau Tunda tercatat sekitar 28 jenis yang didominasi Gastropoda (antara lain Lambis lambis, Cypraea moneta, dan Tectus pyramis) dengan keanekaragaman lebih tinggi di sisi utara, yang pH (7,59) dan DO-nya (6,7 mg/L) memenuhi baku mutu air laut untuk biota laut (pH 7–8,5; DO >5 mg/L), dibandingkan sisi selatan dekat permukiman yang DO-nya (4,2 mg/L) berada di bawah baku mutu.
                                    </p>
                                </div>
                            </div>
                        </div>
                </article>

            </div>
        </div>
    </section>

    <!-- 4. OVERVIEW SISTEM SCOMAR (LAYANAN MODUL & TEKNOLOGI) -->
    <section id="fitur-sistem" class="overview-section">
        <div class="container">
            <div class="section-header reveal-on-scroll">
                <span class="section-tag">Arsitektur & Layanan Digital</span>
                <h2 class="section-title">Kapabilitas Analitik Spasial & Kecerdasan Buatan SCOMAR</h2>
                <p class="section-desc">
                    Integrasi komputasi sains data kelautan, penginderaan jauh satelit, serta pemrosesan citra AI dalam satu platform terpadu untuk efisiensi perikanan dan perlindungan habitat perairan Pulau Tunda.
                </p>
            </div>

            <div class="overview-grid">
                <!-- Modul 1 -->
                <div class="overview-card reveal-on-scroll">
                    <span class="overview-number">01</span>
                    <h3 class="overview-title">Pemetaan WebGIS Terumbu Karang</h3>
                    <p class="overview-text">
                        Koreksi radiometrik dan atenuasi kolom air algoritma Lyzenga pada citra satelit resolusi tinggi untuk memetakan luasan serta kondisi 5 kelas substrat dasar perairan laut Pulau Tunda secara presisi.
                    </p>
                </div>

                <!-- Modul 2 -->
                <div class="overview-card reveal-on-scroll">
                    <span class="overview-number">02</span>
                    <h3 class="overview-title">Prediksi Zona Potensi Penangkapan (ZPPI)</h3>
                    <p class="overview-text">
                        Estimasi koordinat fishing ground musiman berbasis perpaduan gradien suhu permukaan laut (SPL) dan konsentrasi klorofil-a untuk meningkatkan produktivitas serta efisiensi bahan bakar nelayan.
                    </p>
                </div>

                <!-- Modul 3 -->
                <div class="overview-card reveal-on-scroll">
                    <span class="overview-number">03</span>
                    <h3 class="overview-title">Rekomendasi Alat Tangkap Efektif dan Selektif</h3>
                    <p class="overview-text">
                        Sistem rekomendasi ramah lingkungan berdasarkan kedekatan spasial titik tangkap dengan habitat terumbu karang hidup guna mencegah kontak bentik dan memitigasi risiko degradasi ekosistem dasar laut.
                    </p>
                </div>

                <!-- Modul 4 -->
                <div class="overview-card reveal-on-scroll">
                    <span class="overview-number">04</span>
                    <h3 class="overview-title">Pemindai Kesegaran Ikan Berbasis AI</h3>
                    <p class="overview-text">
                        Model deep learning computer vision untuk mengenali klasifikasi morfologi jenis ikan serta mengevaluasi indeks kesegaran mutu hasil tangkapan secara otomatis dan objektif melalui citra kamera.
                    </p>
                </div>

                <!-- Modul 5 -->
                <div class="overview-card reveal-on-scroll">
                    <span class="overview-number">05</span>
                    <h3 class="overview-title">Pemantauan Kondisi Oseanografi</h3>
                    <p class="overview-text">
                        Sistem pemantauan dan analisis kondisi oseanografi berdasarkan empat parameter lingkungan yang memengaruhi habitat ikan tenggiri (Scomberomorus commerson), lengkap dengan acuan nilai optimalnya yang dibagi berdasarkan empat musim.
                    </p>
                </div>

                <!-- Modul 6 -->
                <div class="overview-card reveal-on-scroll">
                    <span class="overview-number">06</span>
                    <h3 class="overview-title">Analisis Hasil Tangkapan Ikan</h3>
                    <p class="overview-text">
                        Sistem pemantauan dan analisis hasil tangkapan ikan tenggiri (Scomberomorus commerson) berdasarkan empat musim, untuk melihat serta mengevaluasi hasil tangkapan dengan jumlah effort tangkapan terbanyak.
                    </p>
                </div>

                <!-- Modul 7 -->
                <div class="overview-card reveal-on-scroll">
                    <span class="overview-number">07</span>
                    <h3 class="overview-title">Hubungan Parameter Oseanografi dengan CPUE (Catch Per Unit Effort)</h3>
                    <p class="overview-text">
                        Sistem analisis untuk menyajikan informasi mengenai pengaruh masing-masing parameter oseanografi terhadap CPUE ikan tenggiri (Scomberomorus commerson) berdasarkan empat musim.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. CALL TO ACTION (CTA) -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-box reveal-on-scroll">
                <span class="hero-tag" style="background:rgba(255,255,255,0.12); color:#FFFFFF; margin-bottom:14px;">Akses Digital Terpadu</span>
                <h2 class="cta-title">Siap Memulai Pemantauan Kelautan yang Bertanggung Jawab?</h2>
                <p class="cta-desc">
                    Masuk ke platform SCOMAR untuk mengeksplorasi peta interaktif WebGIS, mengunduh data oseanografi terkini, serta melihat rekomendasi zonasi penangkapan ikan di perairan Pulau Tunda.
                </p>
                <div class="cta-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary" style="background:var(--teal-accent); color:#FFFFFF;">Buka Dashboard Sistem</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary" style="background:#FFFFFF; color:var(--deep-navy);">Masuk ke Sistem</a>
                        <a href="{{ route('register') }}" class="btn-outline" style="border-color:#FFFFFF; color:#FFFFFF;">Registrasi Akun Baru</a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- 6. FOOTER -->
    <footer class="landing-footer" role="contentinfo">
        <div class="container">
            <div class="footer-main">
                <!-- Identitas Sistem -->
                <div class="footer-brand-col">
                    <div class="footer-brand-title">SCOMAR</div>
                    <div class="footer-brand-sub">Scomberomorus Marine</div>
                    <p class="footer-brand-desc">
                        Platform Digital AI untuk konservasi ekosistem laut, pemantauan oseanografi, dan pendampingan perikanan tangkap berkelanjutan di wilayah perairan Pulau Tunda, Kabupaten Serang, Provinsi Banten.
                    </p>
                    <div class="footer-affiliation">
                        Dirancang dan Dikembangkan oleh Tim Pengembang SCOMAR &bull; Universitas Pendidikan Indonesia (UPI)
                    </div>
                </div>

                <!-- Tautan Navigasi Halaman -->
                <div class="footer-col">
                    <h4 class="footer-col-title">Navigasi Halaman</h4>
                    <ul class="footer-list">
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#pulau-tunda">Tentang Pulau Tunda</a></li>
                        <li><a href="#ikan-tenggiri">Ikan Tenggiri</a></li>
                        <li><a href="#fitur-sistem">Layanan Sistem</a></li>
                    </ul>
                </div>

                <!-- Modul Sistem -->
                <div class="footer-col">
                    <h4 class="footer-col-title">Layanan Sistem</h4>
                    <ul class="footer-list">
                        <li><a href="{{ route('login') }}">Peta Interaktif WebGIS</a></li>
                        <li><a href="{{ route('login') }}">Analisis Terumbu Karang Lyzenga</a></li>
                        <li><a href="{{ route('login') }}">Zona Potensi Tangkap (ZPPI)</a></li>
                        <li><a href="{{ route('login') }}">Analisis Hasil Tangkapan</a></li>
                        <li><a href="{{ route('login') }}">Pemantauan Kondisi Oseanografi</a></li>
                        <li><a href="{{ route('login') }}">Hubungan Parameter Oseanografi dengan CPUE</a></li>
                        <li><a href="{{ route('login') }}">Pemindai Kesegaran Ikan AI</a></li>
                    </ul>
                </div>

                <!-- Akses Pengguna & Kebijakan -->
                <div class="footer-col">
                    <h4 class="footer-col-title">Akses & Informasi</h4>
                    <ul class="footer-list">
                        @auth
                            <li><a href="{{ route('dashboard') }}">Dashboard Pengguna</a></li>
                        @else
                            <li><a href="{{ route('login') }}">Masuk Pengguna</a></li>
                            <li><a href="{{ route('register') }}">Registrasi Akun</a></li>
                        @endauth
                            <li><a href="#beranda">Panduan Pengoperasian</a></li>
                            <li><a href="#pulau-tunda">Dokumentasi Konservasi</a></li>
                    </ul>
                </div>
            </div>

            <!-- Baris Hak Cipta -->
            <div class="footer-bottom">
                <div>
                    &copy; {{ date('Y') }} SCOMAR — Scomberomorus Marine. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div style="font-size: 12px; color: var(--text-light);">
                    Perairan Pulau Tunda, Desa Wargasara, Kecamatan Tirtayasa, Kabupaten Serang, Banten
                </div>
            </div>
        </div>
    </footer>

    <!-- Dedicated Landing Page JavaScript -->
    <script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>