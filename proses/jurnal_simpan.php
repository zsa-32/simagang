<?php
/**
 * proses/jurnal_simpan.php
 * CREATE — Mahasiswa submit jurnal harian
 */
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'mahasiswa') {
    header('Location: ../index.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../mahasiswa/tambahjurnal.php'); exit();
}

$id_user  = (int) $_SESSION['id_user'];
$tanggal  = trim($_POST['tanggal']   ?? '');
$judul    = trim($_POST['judul']     ?? '');
$deskripsi= trim($_POST['deskripsi'] ?? '');

if (empty($tanggal) || empty($judul) || empty($deskripsi)) {
    header('Location: ../mahasiswa/tambahjurnal.php?error=field_kosong'); exit();
}

// Upload bukti (opsional)
$bukti_path = null;
if (!empty($_FILES['bukti']['name'])) {
    $file     = $_FILES['bukti'];
    $maxSize  = 2 * 1024 * 1024; // 2 MB
    $allowed  = ['image/jpeg', 'image/png', 'image/jpg'];

    if ($file['size'] > $maxSize) {
        header('Location: ../mahasiswa/tambahjurnal.php?error=file_besar'); exit();
    }
    if (!in_array($file['type'], $allowed)) {
        header('Location: ../mahasiswa/tambahjurnal.php?error=file_format'); exit();
    }

    $uploadDir = __DIR__ . '/../assets/uploads/jurnal/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName  = 'jurnal_' . $id_user . '_' . date('Ymd_His') . '.' . $ext;
    $destPath  = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        header('Location: ../mahasiswa/tambahjurnal.php?error=upload_gagal'); exit();
    }
    $bukti_path = 'assets/uploads/jurnal/' . $fileName;
}

try {
    // kegiatan = judul + deskripsi (kolom DB hanya 1 text)
    $kegiatan = $judul . "\n\n" . $deskripsi;

    $stmt = $conn->prepare("
        INSERT INTO Daily_journal (id_user, tanggal, kegiatan, bukti, status)
        VALUES (:id_user, :tanggal, :kegiatan, :bukti, 'Menunggu')
    ");
    $stmt->execute([
        ':id_user'  => $id_user,
        ':tanggal'  => $tanggal,
        ':kegiatan' => $kegiatan,
        ':bukti'    => $bukti_path,
    ]);

    header('Location: ../mahasiswa/jurnal.php?success=jurnal_disimpan'); exit();

} catch (PDOException $e) {
    error_log('jurnal_simpan ERROR: ' . $e->getMessage());
    header('Location: ../mahasiswa/tambahjurnal.php?error=db_error'); exit();
}
