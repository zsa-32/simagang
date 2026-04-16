<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simpan role sebelum session di-destroy (untuk keperluan redirect)
$role = $_SESSION['role'] ?? 'mahasiswa';

// Hapus semua data session
$_SESSION = [];

// Hancurkan session cookie jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login dengan pesan sukses
header("Location: index.php?logout=success");
exit();
?>
