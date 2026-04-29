<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
$host = "localhost";
$port = "8889";       // MAMP default MySQL port
$db   = "simagang";
$user = "root";
$pass = "root";

try {
    // Membuat koneksi dengan PDO
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Base URL untuk frontend
define('BASE_URL', 'http://localhost:8888/simagang/simagang/');
?>