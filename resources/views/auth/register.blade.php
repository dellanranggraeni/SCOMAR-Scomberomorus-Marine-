<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SCOMAR</title>

    <style>
        /* PALET WARNA SCOMAR */
        :root {
            --cashmere-blue: #A5B8D0;
            --merlot: #73343A;
            --deep-navy: #24364B;
            --warm-ivory: #F5F5F2;
            --white: #FFFFFF;
        }

        /* RESET DAN FONT */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* BACKGROUND HALAMAN */
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

        /* REGISTER CONTAINER */
        .register-container {
            width: 100%;
            max-width: 480px;
            background: var(--white);
            padding: 35px 38px;
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(36, 54, 75, 0.15);
        }

        /* HEADER SCOMAR */
        .brand-header {
            display: grid;
            grid-template-columns: 75px minmax(0, 1fr) 75px;
            align-items: center;
            gap: 12px;
            padding-bottom: 22px;
            margin-bottom: 25px;
            border-bottom: 1px solid #E2E8F0;
        }

        .brand-logo {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .brand-logo img {
            width: 72px;
            height: 72px;
            object-fit: contain;
            display: block;
        }

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

        /* JUDUL REGISTER */
        .register-title {
            margin-bottom: 25px;
            text-align: center;
        }

        .register-title h2 {
            color: var(--deep-navy);
            font-size: 23px;
            margin-bottom: 8px;
        }

        .register-title p {
            color: #64748B;
            font-size: 14px;
        }

        /* FORM INPUT */
        .form-group {
            margin-bottom: 17px;
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
        .form-group input::placeholder {
            color: #94A3B8;
        }

        /* PESAN ERROR */
        .error-message {
            color: #DC2626;
            font-size: 12px;
            margin-top: 6px;
        }

        /* TOMBOL REGISTER */
        .register-button {
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
            margin-top: 5px;
        }

        .register-button:hover {
            background: #5C2930;
        }

        /* LINK LOGIN */
        .login-link {
            text-align: center;
            margin-top: 23px;
            font-size: 13px;
            color: #64748B;
        }

        .login-link a {
            color: var(--merlot);
            font-weight: bold;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            color: #94A3B8;
            font-size: 11px;
            margin-top: 25px;
        }

        /* RESPONSIVE MOBILE */
        @media (max-width: 480px) {
            .register-container {
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
            .register-title h2 {
                font-size: 21px;
            }
        }

    
        @media (max-width: 380px) {
            .register-container {
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
            .register-title h2 {
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
    <div class="register-container">

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

        <!-- JUDUL REGISTER -->
        <div class="register-title">
            <h2>Buat Akun Baru</h2>
            <p>
                Daftarkan diri Anda untuk menggunakan SCOMAR
            </p>
        </div>

        <!-- PESAN ERROR -->
        @if ($errors->any())
            <div class="error-message" style="margin-bottom: 15px;">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- FORM REGISTER -->
        <form action="{{ route('register.store') }}" method="POST">
            @csrf

            <!-- NAMA LENGKAP -->
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Masukkan nama lengkap Anda"
                    autocomplete="name"
                    required
                >
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- EMAIL -->
            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email Anda"
                    autocomplete="email"
                    required
                >

                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror

            </div>

            <!-- PASSWORD -->
            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimal 8 karakter"
                    autocomplete="new-password"
                    required
                >

                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- KONFIRMASI PASSWORD -->
            <div class="form-group">
                <label for="password_confirmation">
                    Konfirmasi Password
                </label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Masukkan ulang password Anda"
                    autocomplete="new-password"
                    required
                >

            </div>

            <!-- TOMBOL REGISTER -->
            <button type="submit" class="register-button">
                Daftar Sekarang
            </button>
        </form>

        <!-- LINK LOGIN -->
        <div class="login-link">
            Sudah memiliki akun?
            <a href="{{ route('login') }}">
                Masuk di sini
            </a>    
        </div>

        <!-- FOOTER -->
        <div class="footer">
            &copy; 2026 SCOMAR. All Rights Reserved.
        </div>

    </div>

</body>
</html>