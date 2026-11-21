<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SCOMAR</title>

    <style>
        /* ==================================
           PALET WARNA SCOMAR
        ================================== */

        :root {
            --cashmere-blue: #A5B8D0;
            --merlot: #73343A;
            --deep-navy: #24364B;
            --warm-ivory: #F5F5F2;
            --white: #FFFFFF;
        }

        /* ==================================
           RESET DAN FONT
        ================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* ==================================
           BACKGROUND HALAMAN
        ================================== */

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(
                135deg,
                var(--cashmere-blue),
                var(--warm-ivory)
            );
            padding: 25px 20px;
            color: var(--deep-navy);
        }

        /* ==================================
           LOGIN CONTAINER
        ================================== */

        .login-container {
            width: 100%;
            max-width: 480px;
            background: var(--white);
            padding: 35px 38px;
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(36, 54, 75, 0.15);
        }

        /* ==================================
           HEADER SCOMAR
        ================================== */

        .brand-header {
            display: grid;
            grid-template-columns: 65px minmax(0, 1fr) 65px;
            align-items: center;
            gap: 12px;

            padding-bottom: 20px;
            margin-bottom: 25px;

            border-bottom: 1px solid #E2E8F0;
        }

        /* LOGO SCOMAR DAN UPI */
        .brand-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            background: transparent;
            border: none;
            box-shadow: none;
        }
        
        .brand-logo img {
            width: 65px;
            height: 65px;
            object-fit: contain;
            display: block;
            background: transparent;
            border: none;
            box-shadow: none;
        }

        /* TULISAN HEADER */
        .brand-description {
            text-align: center;
        }
        .brand-description h1 {
            font-size: 23px;
            letter-spacing: 2px;
            font-weight: 700;
            color: var(--deep-navy);
            margin-bottom: 7px;
        }

        .brand-description p {
            font-size: 11px;
            font-weight: 600;
            line-height: 1.5;
            color: var(--deep-navy);
        }

        .brand-description p span {
            display: block;
            margin-top: 4px;
            color: var(--merlot);
        }

        /* ==================================
           JUDUL LOGIN
        ================================== */

        .login-title {
            margin-bottom: 25px;
            text-align: center;
        }

        .login-title h2 {
            color: var(--deep-navy);
            font-size: 23px;
            margin-bottom: 8px;
        }

        .login-title p {
            color: #64748B;
            font-size: 14px;
        }

        /* ==================================
           FORM INPUT
        ================================== */

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            color: var(--deep-navy);
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 13px;
            border: 1px solid var(--cashmere-blue);
            border-radius: 8px;
            outline: none;
            font-size: 14px;
            background: var(--white);
        }

        .form-group input:focus {
            border-color: var(--merlot);
            box-shadow: 0 0 0 3px rgba(115, 52, 58, 0.12);
        }

        /* WRAPPER TOGGLE PASSWORD */
        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .password-input-wrapper input {
            padding-right: 44px;
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #64748B;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.2s ease;
        }

        .toggle-password-btn:hover {
            color: var(--merlot);
        }

        /* ==================================
           PESAN ERROR
        ================================== */

        .error-message {
            color: #DC2626;
            font-size: 12px;
            margin-top: 6px;
        }

        /* ==================================
           REMEMBER ME
        ================================== */

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            color: var(--deep-navy);
            font-size: 13px;
        }

        .remember label {
            cursor: pointer;
        }

        /* ==================================
           TOMBOL LOGIN
        ================================== */

        .login-button {
            width: 100%;
            padding: 14px;
            background: var(--merlot);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .login-button:hover {
            background: #5C2930;
        }

        /* ==================================
           LINK REGISTER
        ================================== */

        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #64748B;
        }

        .register-link a {
            color: var(--merlot);
            font-weight: bold;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* ==================================
           FOOTER
        ================================== */

        .footer {
            text-align: center;
            color: #94A3B8;
            font-size: 11px;
            margin-top: 30px;
        }

        /* ==================================
           RESPONSIVE MOBILE
        ================================== */

        @media (max-width: 480px) {

            .login-container {
                padding: 28px 20px;
            }

            .brand-header {
                grid-template-columns: 52px minmax(0, 1fr) 52px;
                gap: 7px;
            }

            .brand-logo img {
                width: 50px;
                height: 50px;
            }

            .brand-description h1 {
                font-size: 19px;
            }

            .brand-description p {
                font-size: 9px;
            }

            .login-title h2 {
                font-size: 21px;
            }
        }

    
        @media (max-width: 380px) {
            .login-container {
                padding: 20px 14px;
            }
            .brand-logo img {
                width: 44px;
                height: 44px;
            }
            .brand-description h1 {
                font-size: 16px;
            }
            .brand-description p {
                font-size: 8.5px;
            }
            .login-title h2 {
                font-size: 18px;
            }
            .form-input {
                font-size: 12px;
                padding: 8px 10px;
            }
            .login-btn, .register-btn, .submit-btn {
                font-size: 12px;
                padding: 10px;
            }
        }

    </style>
</head>

<body>

    <div class="login-container">

        <!-- HEADER SCOMAR -->

        <header class="brand-header">

            <!-- LOGO SCOMAR -->

            <div class="brand-logo">
                <img
                    src="{{ asset('images/LOGO_SCOMAR.png') }}"
                    alt="Logo SCOMAR"
                >
            </div>

            <!-- IDENTITAS APLIKASI -->

            <div class="brand-description">

                <h1>SCOMAR</h1>

                <p>
                    Website Cerdas Pemantauan Ekosistem
                    dan Hasil Tangkapan

                    <span>
                        Scomberomus di Pulau Tunda
                    </span>
                </p>

            </div>

            <!-- LOGO UPI -->

            <div class="brand-logo">
                <img
                    src="{{ asset('images/LOGO_UPI.png') }}"
                    alt="Logo Universitas Pendidikan Indonesia"
                >
            </div>

        </header>

        <!-- JUDUL LOGIN -->

        <div class="login-title">

            <h2>Selamat Datang</h2>

            <p>
                Silakan masuk ke akun SCOMAR Anda
            </p>

        </div>

        <!-- PESAN ERROR -->

        @if ($errors->any())
            <div class="error-message" style="margin-bottom: 15px;">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- FORM LOGIN -->

        <form action="{{ route('login.store') }}" method="POST">

            @csrf

            <div class="form-group">

                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email Anda"
                    required
                    autofocus
                >

                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror

            </div>

            <div class="form-group">

                <label for="password">Password</label>

                <div class="password-input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password Anda"
                        required
                    >
                    <button
                        type="button"
                        class="toggle-password-btn"
                        onclick="togglePasswordVisibility('password', this)"
                        aria-label="Lihat password"
                        title="Tampilkan / sembunyikan password"
                    >
                        <!-- Ikon mata terbuka (default) -->
                        <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <!-- Ikon mata dicoret (sembunyikan) -->
                        <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>

                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror

            </div>

            <!-- INGAT SAYA -->

            <div class="remember">

                <input
                    type="checkbox"
                    name="remember"
                    id="remember"
                    value="1"
                >

                <label for="remember">Ingat saya</label>

            </div>

            <!-- TOMBOL LOGIN -->

            <button type="submit" class="login-button">
                Masuk
            </button>

        </form>

        <!-- LINK REGISTER -->

        <div class="register-link">

            Belum memiliki akun?

            <a href="{{ route('register') }}">
                Daftar di sini
            </a>

        </div>

        <!-- FOOTER -->

        <div class="footer">
            &copy; 2026 SCOMAR. All Rights Reserved.
        </div>

    </div>

    <!-- SCRIPT TOGGLE PASSWORD -->
    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const eyeIcon = button.querySelector('.eye-icon');
            const eyeOffIcon = button.querySelector('.eye-off-icon');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                if (eyeIcon && eyeOffIcon) {
                    eyeIcon.style.display = 'none';
                    eyeOffIcon.style.display = 'block';
                }
                button.setAttribute('aria-label', 'Sembunyikan password');
            } else {
                input.type = 'password';
                if (eyeIcon && eyeOffIcon) {
                    eyeIcon.style.display = 'block';
                    eyeOffIcon.style.display = 'none';
                }
                button.setAttribute('aria-label', 'Lihat password');
            }
        }
    </script>

</body>
</html>
