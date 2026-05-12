<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Magang TIF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            overflow: hidden; 
        }
        .bg-login-left { background-color: #E8F1FF; position: relative; overflow: hidden; }
        .login-card { border-radius: 16px; box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05); }
        .input-group-text { background-color: #ffffff; }
        .form-control:focus { box-shadow: none; border-color: #0d6efd; }
        .btn-primary { background-color: #1a65ff; border: none; border-radius: 8px; font-weight: 500; }
        .btn-primary:hover { background-color: #0d52d6; }

        /* === Modern Toast Notification === */
        #toast-container {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            pointer-events: none;
        }
        .toast-alert {
            pointer-events: all;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 320px;
            max-width: 420px;
            padding: 14px 18px 10px 16px;
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 1.5px 6px rgba(0,0,0,0.07);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            animation: toastSlideIn 0.38s cubic-bezier(.22,1,.36,1) forwards;
            position: relative;
            overflow: hidden;
        }
        .toast-alert.toast-hide {
            animation: toastSlideOut 0.32s cubic-bezier(.55,0,1,.45) forwards;
        }
        @keyframes toastSlideIn {
            from { opacity: 0; transform: translateY(-28px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes toastSlideOut {
            from { opacity: 1; transform: translateY(0) scale(1); }
            to   { opacity: 0; transform: translateY(-20px) scale(0.95); }
        }
        .toast-danger  { background: rgba(255,235,235,0.97); border-left: 4px solid #e74c3c; color: #7b1a1a; }
        .toast-warning { background: rgba(255,248,230,0.97); border-left: 4px solid #f39c12; color: #7a5000; }
        .toast-success { background: rgba(230,255,240,0.97); border-left: 4px solid #27ae60; color: #0e5c2a; }
        .toast-icon {
            font-size: 1.35rem;
            margin-top: 1px;
            flex-shrink: 0;
        }
        .toast-body { flex: 1; }
        .toast-title { font-weight: 700; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px; }
        .toast-msg   { font-size: 0.9rem; line-height: 1.45; }
        .toast-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-left: 4px;
            font-size: 1rem;
            opacity: 0.45;
            transition: opacity 0.2s;
            color: inherit;
            flex-shrink: 0;
        }
        .toast-close:hover { opacity: 0.9; }
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            border-radius: 0 0 14px 14px;
            width: 100%;
            animation: toastProgress 3s linear forwards;
        }
        .toast-danger  .toast-progress { background: #e74c3c; }
        .toast-warning .toast-progress { background: #f39c12; }
        .toast-success .toast-progress { background: #27ae60; }
        @keyframes toastProgress {
            from { width: 100%; }
            to   { width: 0%; }
        }
        /* Input shake animation */
        @keyframes inputShake {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-6px); }
            40%       { transform: translateX(6px); }
            60%       { transform: translateX(-4px); }
            80%       { transform: translateX(4px); }
        }
        .input-shake { animation: inputShake 0.4s ease; border-color: #e74c3c !important; }
    </style>
</head>

<body>
    <div class="container-fluid h-100 p-0">
        <div class="row h-100 m-0">
            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center bg-login-left p-5">
                <img src="assets/img/login1.png" alt="Ilustrasi Magang" class="img-fluid mb-4" style="max-width: 65%;">
                <h2 class="fw-bold text-primary text-center" style="color: #1a65ff !important;">Selamat Datang di<br>Magang TIF</h2>
                <p class="text-center text-muted mt-3 px-4" style="font-size: 0.95rem; max-width: 500px;">
                    Langkah awalmu menuju dunia profesional dimulai di sini. Kelola aktivitas magang, pantau progres, dan raih pengalaman industri terbaikmu.
                </p>
            </div>

            <div class="col-lg-6 d-flex flex-column justify-content-center align-items-center p-4">
                
                <div class="w-100 mb-4 d-flex justify-content-center align-items-center gap-3" style="max-width: 400px;">
                    <div class="bg-primary text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; border-radius: 12px; background-color: #1a65ff !important;">
                        <i class="bi bi-mortarboard-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Magang TIF</h5>
                        <small class="text-muted" style="font-size: 0.75rem;">Internship Management System</small>
                    </div>
                </div>

                <div class="card login-card border-0 w-100 p-4 p-md-5" style="max-width: 400px;">
                    <h3 class="fw-bold mb-1" style="color: #1e1e1e;">LOGIN</h3>
                    
                    <?php
                    // Toast data dikirim ke JS via hidden input
                    $toastType = '';
                    $toastMsg  = '';
                    if (isset($_GET['error'])) {
                        if ($_GET['error'] == 'empty') {
                            $toastType = 'warning';
                            $toastMsg  = 'Email dan Password tidak boleh kosong!';
                        } elseif ($_GET['error'] == 'wrongpassword' || $_GET['error'] == 'usernotfound') {
                            $toastType = 'danger';
                            $toastMsg  = 'Email atau Password Anda salah.';
                        }
                    }
                    if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
                        $toastType = 'success';
                        $toastMsg  = 'Berhasil logout. Silakan login kembali.';
                    }
                    if ($toastType) {
                        echo '<input type="hidden" id="toast-type" value="' . htmlspecialchars($toastType) . '">';
                        echo '<input type="hidden" id="toast-msg"  value="' . htmlspecialchars($toastMsg) . '">';
                    }
                    ?>

                    <p class="text-muted mb-4 mt-2" style="font-size: 0.85rem;">Silakan masuk ke akun Anda</p>

                    <form action="proses_login.php" method="POST" id="loginForm" novalidate>
                        <div class="mb-3">
                            <label class="form-label text-muted" style="font-size: 0.8rem; font-weight: 500;">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" name="email" placeholder="contoh@student.polije.ac.id" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted" style="font-size: 0.8rem; font-weight: 500;">Password</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control border-start-0 border-end-0 ps-0" name="password" id="password" placeholder="Masukkan password" required>
                                <span class="input-group-text bg-white border-start-0" id="togglePassword" style="cursor: pointer;">
                                    <i class="bi bi-eye-slash text-muted" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-4 d-flex justify-content-between">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe">
                                <label class="form-check-label text-muted" for="rememberMe" style="font-size: 0.85rem;">Ingat Saya</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fs-6">Masuk Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* ===== Toggle Password ===== */
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            eyeIcon.classList.toggle('bi-eye');
            eyeIcon.classList.toggle('bi-eye-slash');
        });

        /* ===== Modern Toast System ===== */
        const TOAST_ICONS = {
            danger:  { icon: 'bi-x-circle-fill',       label: 'Gagal' },
            warning: { icon: 'bi-exclamation-triangle-fill', label: 'Perhatian' },
            success: { icon: 'bi-check-circle-fill',   label: 'Berhasil' },
        };

        function showToast(type, message, duration = 3000) {
            const container = document.getElementById('toast-container');
            const meta = TOAST_ICONS[type] || TOAST_ICONS.danger;

            const el = document.createElement('div');
            el.className = `toast-alert toast-${type}`;
            el.innerHTML = `
                <span class="toast-icon"><i class="bi ${meta.icon}"></i></span>
                <div class="toast-body">
                    <div class="toast-title">${meta.label}</div>
                    <div class="toast-msg">${message}</div>
                </div>
                <button class="toast-close" aria-label="Tutup"><i class="bi bi-x"></i></button>
                <div class="toast-progress"></div>
            `;

            container.appendChild(el);

            // Close button
            el.querySelector('.toast-close').addEventListener('click', () => dismissToast(el));

            // Auto dismiss
            const timer = setTimeout(() => dismissToast(el), duration);
            el._timer = timer;
        }

        function dismissToast(el) {
            clearTimeout(el._timer);
            el.classList.add('toast-hide');
            el.addEventListener('animationend', () => el.remove(), { once: true });
        }

        /* ===== Show PHP-driven toast on page load ===== */
        window.addEventListener('DOMContentLoaded', function () {
            const typeEl = document.getElementById('toast-type');
            const msgEl  = document.getElementById('toast-msg');
            if (typeEl && msgEl) {
                showToast(typeEl.value, msgEl.value);
            }
        });

        /* ===== Client-side form validation ===== */
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const emailInput    = this.querySelector('[name="email"]');
            const passwordInput = this.querySelector('[name="password"]');
            const emailVal      = emailInput.value.trim();
            const passVal       = passwordInput.value.trim();

            // Regex format email valid: harus ada @, karakter sebelum & sesudahnya, serta domain
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let blocked = false;

            // 1. Cek field kosong dulu
            if (!emailVal && !passVal) {
                e.preventDefault(); blocked = true;
                shakeInput(emailInput);
                shakeInput(passwordInput);
                showToast('warning', 'Email dan Password tidak boleh kosong!');

            } else if (!emailVal) {
                e.preventDefault(); blocked = true;
                shakeInput(emailInput);
                showToast('warning', 'Email tidak boleh kosong!');

            } else if (!passVal) {
                e.preventDefault(); blocked = true;
                shakeInput(passwordInput);
                showToast('warning', 'Password tidak boleh kosong!');

            // 2. Cek ada karakter @ pada email
            } else if (!emailVal.includes('@')) {
                e.preventDefault(); blocked = true;
                shakeInput(emailInput);
                showToast('warning', 'Email harus mengandung karakter <b>@</b>. Contoh: nama@email.com');

            // 3. Cek format email lengkap (ada domain setelah @)
            } else if (!emailRegex.test(emailVal)) {
                e.preventDefault(); blocked = true;
                shakeInput(emailInput);
                showToast('warning', 'Format email tidak valid. Contoh: nama@student.polije.ac.id');
            }
        });

        function shakeInput(input) {
            input.classList.add('input-shake');
            input.addEventListener('animationend', () => input.classList.remove('input-shake'), { once: true });
        }
    </script>
</body>
</html>