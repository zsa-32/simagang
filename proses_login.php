<?php
// Panggil file koneksi database
require_once 'config/db_connect.php';

// Pastikan form di-submit menggunakan metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Tangkap inputan dari form, gunakan trim() untuk menghapus spasi berlebih
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Cek apakah username dan password tidak kosong
    if (empty($username) || empty($password)) {
        // Jika kosong, kembalikan ke halaman login dengan pesan error
        header("Location: index.php?error=empty");
        exit();
    }

    try {
        // Siapkan query menggunakan Prepared Statement PDO (Mencegah SQL Injection)
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Ambil data user
        $user = $stmt->fetch();

        // Cek apakah user ditemukan
        if ($user) {
            // Cek password (karena data dummy kita pakai teks biasa, kita pakai == dulu)
            // Catatan: Jika nanti pakai password_hash(), ganti baris ini menjadi:
            // if (password_verify($password, $user['password'])) {
            if ($password == $user['password']) {
                
                // Password benar, simpan data ke Session
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Arahkan ke folder/dashboard sesuai role masing-masing
                if ($user['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif ($user['role'] == 'dosen') {
                    header("Location: dosen/dashboard.php");
                } elseif ($user['role'] == 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                }
                exit();
                
            } else {
                // Password salah
                header("Location: index.php?error=wrongpassword");
                exit();
            }
        } else {
            // Username tidak ditemukan
            header("Location: index.php?error=usernotfound");
            exit();
        }

    } catch(PDOException $e) {
        // Jika terjadi error pada database
        die("Error: " . $e->getMessage());
    }
} else {
    // Jika ada yang mencoba akses file ini lewat URL secara langsung
    header("Location: index.php");
    exit();
}
?>