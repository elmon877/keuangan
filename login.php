<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Keuangan</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- SCRIPT UTAMA UNTUK MERENDER ANIMASI 3D SPLINE -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.0/build/spline-viewer.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:   #0F1B2D;
            --card:   #1A2B42;
            --panel:  #162438;
            --gold:   #C9A84C;
            --gold2:  #E8C96A;
            --text:   #E8EDF4;
            --muted:  #6B7A8D;
            --border: rgba(201,168,76,.25);
            --error:  #E05C5C;
        }

        html, body {
            height: 100%;
            background: var(--navy);
            font-family: 'Inter', sans-serif;
            color: var(--text);
        }

        /* ── Layout ── */
        .wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* ── Left panel ── */
        .panel {
            background: var(--panel);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px 56px;
            position: relative;
            overflow: hidden;
        }

        /* Geometric background */
        .panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 60px,
                    rgba(201,168,76,.04) 60px,
                    rgba(201,168,76,.04) 61px
                ),
                repeating-linear-gradient(
                    -45deg,
                    transparent,
                    transparent 60px,
                    rgba(201,168,76,.04) 60px,
                    rgba(201,168,76,.04) 61px
                );
        }

        /* Radial glow top-right */
        .panel::after {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(201,168,76,.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .panel-content { position: relative; z-index: 1; }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }
        .logo-icon {
            width: 40px; height: 40px;
            background: var(--gold);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-icon svg { width: 22px; height: 22px; fill: var(--navy); }
        .logo-name { font-size: 18px; font-weight: 600; letter-spacing: .02em; }

        /* Container Pembungkus Animasi 3D */
        .3d-container {
            width: 100%;
            height: 350px;
            margin-bottom: 40px;
            position: relative;
        }
        spline-viewer {
            width: 100%;
            height: 100%;
            mix-blend-mode: screen;
        }

        /* Stats row */
        .stats {
            display: flex;
            gap: 32px;
        }
        .stat-num {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            color: var(--gold);
        }
        .stat-label {
            font-size: 12px;
            color: var(--muted);
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* Gold animated line */
        .gold-line {
            margin: 48px 0 0;
            height: 1px;
            background: linear-gradient(90deg, var(--gold), transparent);
            transform-origin: left;
            animation: drawLine 1.4s cubic-bezier(.4,0,.2,1) forwards;
        }
        @keyframes drawLine {
            from { transform: scaleX(0); opacity: 0; }
            to   { transform: scaleX(1); opacity: 1; }
        }

        /* ── Right / form side ── */
        .form-side {
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
        }

        .form-box {
            width: 100%;
            max-width: 400px;
            animation: fadeUp .6s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-box h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            margin-bottom: 6px;
        }
        .form-box .subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 36px;
        }

        /* Error */
        .alert-error {
            background: rgba(224,92,92,.12);
            border: 1px solid rgba(224,92,92,.35);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            color: #f08080;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }
        .alert-error svg { flex-shrink: 0; width: 18px; height: 18px; fill: #f08080; }

        /* Field */
        .field { margin-bottom: 20px; }
        .field label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--muted);
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input-wrap { position: relative; }
        
        /* FIX 1: Menggunakan tanda '>' agar posisi absolute ini HANYA mengatur icon kiri (Lock & User) */
        .input-wrap > svg {
            position: absolute;
            left: 16px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px;
            fill: var(--muted);
            transition: fill .2s;
            pointer-events: none;
        }

        .field input {
            width: 100%;
            background: var(--card);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 12px;
            /* FIX 2: Mengubah padding kanan dari 16px ke 46px agar teks ketikan tidak menabrak ikon mata */
            padding: 14px 46px 14px 46px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            color: var(--text);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .field input::placeholder { color: var(--muted); }
        .field input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,168,76,.15);
        }
        
        /* FIX 3: Memastikan efek fokus warna emas hanya memengaruhi icon kiri */
        .field input:focus + svg,
        .input-wrap:focus-within > svg { fill: var(--gold); }

        /* FIX 4: Kalibrasi posisi tombol kontainer Mata Password agar presisi di dalam input */
        .toggle-pw {
            position: absolute;
            right: 16px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            padding: 0;
            color: var(--muted);
            display: flex; align-items: center;
            z-index: 5;
        }
        /* FIX 5: Mengunci dimensi SVG mata agar tidak terdistorsi */
        .toggle-pw svg { 
            width: 20px; 
            height: 20px; 
            fill: currentColor; 
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--gold); }

        /* Submit */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--gold);
            color: var(--navy);
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 8px;
            letter-spacing: .02em;
            transition: background .2s, transform .15s, box-shadow .2s;
            position: relative;
            overflow: hidden;
        }
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.15), transparent);
        }
        .btn-login:hover {
            background: var(--gold2);
            box-shadow: 0 8px 24px rgba(201,168,76,.35);
            transform: translateY(-1px);
        }
        .btn-login:active { transform: translateY(0); }

        /* Link Autentikasi Tambahan */
        .auth-link {
            text-align: center;
            font-size: 14px;
            margin-top: 20px;
            color: var(--muted);
        }
        .auth-link a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
            transition: color .2s;
        }
        .auth-link a:hover {
            color: var(--gold2);
            text-decoration: underline;
        }

        /* Divider */
        .divider {
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin: 28px 0 0;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .wrapper { grid-template-columns: 1fr; }
            .panel { display: none; }
            .form-side { padding: 60px 24px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .gold-line, .form-box { animation: none; }
        }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- LEFT: Brand Panel -->
    <div class="panel">
        <div class="panel-content">

            <div class="logo">
                <div class="logo-icon">
                    <!-- Chart icon -->
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 3v18h18M7 16l4-4 4 4 4-6" stroke="#0F1B2D" stroke-width="2.2"
                              stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                </div>
                <span class="logo-name">Fisclux</span>
            </div>

            <!-- ANIMASI 3D SPLINE -->
            <div class="3d-container">
                <spline-viewer url="https://prod.spline.design/2eXUHv51bsL2yQip/scene.splinecode"></spline-viewer>
            </div>

            <div class="stats">
                <div class="stat-item">
                    <div class="stat-num">99.9%</div>
                    <div class="stat-label">Uptime</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">256-bit</div>
                    <div class="stat-label">Enkripsi</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">Real-time</div>
                    <div class="stat-label">Sinkronisasi</div>
                </div>
            </div>

            <div class="gold-line"></div>

        </div>
    </div>

    <!-- RIGHT: Login Form -->
    <div class="form-side">
        <div class="form-box">

            <h2>Selamat datang</h2>
            <p class="subtitle">Masuk untuk melanjutkan ke dasbor Anda</p>

            <?php if(isset($_GET['pesan']) && $_GET['pesan'] == "gagal"): ?>
            <div class="alert-error">
                <svg viewBox="0 0 24 24"><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z"/></svg>
                Username atau password salah. Silakan coba lagi.
            </div>
            <?php endif; ?>

            <form action="proses/proses_login.php" method="POST" autocomplete="off">

                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <input type="text" id="username" name="username"
                               placeholder="Masukkan username" required autocomplete="username">
                        <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                               placeholder="Masukkan password" required autocomplete="current-password">
                        <svg viewBox="0 0 24 24"><path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zM9 6a3 3 0 0 1 6 0v2H9V6zm3 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>
                        <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Tampilkan password">
                            <svg id="eye-icon" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5C21.27 7.61 17 4.5 12 4.5zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">Masuk Sekarang</button>
                
                <!-- LINK PENDAFTARAN BARU -->
                <p class="auth-link">Belum punya akun? <a href="register.php">Daftar di sini</a></p>

            </form>

            <p class="divider">Sistem terlindungi &amp; terenkripsi</p>

        </div>
    </div>

</div>

<script>
    function togglePassword() {
        const pw = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        const isHidden = pw.type === 'password';
        pw.type = isHidden ? 'text' : 'password';
        icon.innerHTML = isHidden
            ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-5 0-9.27-3.11-11-7.5a10.06 10.06 0 0 1 2.92-4.04M6.53 6.53A9.93 9.93 0 0 1 12 4.5c5 0 9.27 3.11 11 7.5a10.07 10.07 0 0 1-1.56 2.94M3 3l18 18"/>'
            : '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5C21.27 7.61 17 4.5 12 4.5zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>';
    }
</script>

</body>
</html>
