<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
$host = "localhost";
$db   = "simagang"; // Nama database Anda
$user = "root";      // Default XAMPP adalah root
$pass = "";          

try {
    // Membuat koneksi dengan PDO
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Sesuaikan BASE_URL dengan nama folder proyek Anda di htdocs
define('BASE_URL', 'http://localhost:8888/simagang/simagang/'); 
?>