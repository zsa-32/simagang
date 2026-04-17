<?php
/**
 * proses/absen_checkin.php
 * CREATE — Mahasiswa melakukan check-in absensi harian
 */
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'mahasiswa') {
    header('Location: ../index.php'); exit();
}

$id_user = (int) $_SESSION['id_user'];
$today   = date('Y-m-d');

// Cek apakah sudah absen hari ini
$stmtCek = $conn->prepare(
    "SELECT id_attendance FROM Attendances WHERE id_user = :id AND tanggal = :tgl LIMIT 1"
);
$stmtCek->execute([':id' => $id_user, ':tgl' => $today]);
if ($stmtCek->fetch()) {
    header('Location: ../mahasiswa/absen.php?error=sudah_absen'); exit();
}

try {
    $conn->prepare("
        INSERT INTO Attendances (id_user, tanggal, keterangan, waktu_masuk)
        VALUES (:id, :tgl, 'Hadir', :wm)
    ")->execute([
        ':id'  => $id_user,
        ':tgl' => $today,
        ':wm'  => date('H:i:s'),
    ]);
    header('Location: ../mahasiswa/absen.php?success=absen_berhasil'); exit();
} catch (PDOException $e) {
    error_log('absen_checkin ERROR: ' . $e->getMessage());
    header('Location: ../mahasiswa/absen.php?error=db_error'); exit();
}
