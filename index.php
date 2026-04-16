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
    </style>
</head>

<body>
    <div class="container-fluid h-100 p-0">
        <div class="row h-100 m-0">
            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center bg-login-left p-5">
                <img src="assets/img/login1.png" alt="Ilustrasi Magang" class="img-fluid mb-4" style="max-width: 65%;">
                <h2 class="fw-bold text-primary text-center" style="color: #1a65ff !important;">Selamat Datang di<br>Magang TIF</h2>
                <p class="text-center text-muted mt-3 px-4" style="font-size: 0.95rem; max-width: 500px;">
                    Langkah awalmu menuju dunia profesional dimulai di sini.
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
                    if (isset($_GET['error'])) {
                        $msg = "";
                        $class = "alert-danger";
                        
                        if ($_GET['error'] == 'empty') {
                            $msg = "Email dan Password tidak boleh kosong!";
                            $class = "alert-warning";
                        } elseif ($_GET['error'] == 'wrongpassword' || $_GET['error'] == 'usernotfound') {
                            $msg = "Email atau Password Anda salah.";
                        }
                        
                        echo '<div class="alert ' . $class . ' text-center small p-2 mb-3 mt-2">' . $msg . '</div>';
                    }

                    if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
                        echo '<div class="alert alert-success text-center small p-2 mb-3 mt-2">Berhasil logout. Silakan login kembali.</div>';
                    }
                    ?>

                    <p class="text-muted mb-4 mt-2" style="font-size: 0.85rem;">Silakan masuk ke akun Anda</p>

                    <form action="proses_login.php" method="POST">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            eyeIcon.classList.toggle('bi-eye');
            eyeIcon.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>