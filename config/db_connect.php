<?php
// Memulai session agar bisa digunakan di seluruh halaman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
$host = "localhost";
$db   = "db_magang_tif";
$user = "root";
$pass = "";

try {
    // Membuat koneksi dengan PDO
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    // Set error mode ke Exception agar jika ada error SQL langsung terlihat
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode ke Associative Array agar mudah memanggil kolom (misal: $row['nama'])
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // Jika koneksi gagal, tampilkan pesan error
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi Helper Sederhana untuk Keamanan Base URL (Opsional)
// Membantu mengarahkan path aset agar tidak pecah saat di dalam sub-folder
define('BASE_URL', 'http://localhost/simagang_polije/'); 
?>