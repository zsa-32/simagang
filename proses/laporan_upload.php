<?php
/**
 * proses/laporan_upload.php
 * CREATE — Mahasiswa upload laporan akhir PKL
 */
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'mahasiswa') {
    header('Location: ../index.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../mahasiswa/laporan.php'); exit();
}

$id_user       = (int) $_SESSION['id_user'];
$jenis_laporan = trim($_POST['jenis_laporan'] ?? 'Laporan Akhir PKL');

if (empty($_FILES['file_laporan']['name'])) {
    header('Location: ../mahasiswa/laporan.php?error=no_file'); exit();
}

$file    = $_FILES['file_laporan'];
$maxSize = 10 * 1024 * 1024; // 10 MB
$allowed = ['application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$allowedExt = ['pdf', 'doc', 'docx'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($file['size'] > $maxSize) {
    header('Location: ../mahasiswa/laporan.php?error=file_besar'); exit();
}
if (!in_array($ext, $allowedExt)) {
    header('Location: ../mahasiswa/laporan.php?error=file_format'); exit();
}

$uploadDir = '../assets/uploads/laporan/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$fileName  = 'laporan_' . $id_user . '_' . date('Ymd_His') . '.' . $ext;
$destPath  = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    header('Location: ../mahasiswa/laporan.php?error=upload_gagal'); exit();
}

try {
    $conn->prepare("
        INSERT INTO Reports (id_user, jenis_laporan, file, tanggal_upload)
        VALUES (:id, :jenis, :file, NOW())
    ")->execute([
        ':id'    => $id_user,
        ':jenis' => $jenis_laporan,
        ':file'  => 'assets/uploads/laporan/' . $fileName,
    ]);
    header('Location: ../mahasiswa/laporan.php?success=laporan_diupload'); exit();
} catch (PDOException $e) {
    error_log('laporan_upload ERROR: ' . $e->getMessage());
    header('Location: ../mahasiswa/laporan.php?error=db_error'); exit();
}
