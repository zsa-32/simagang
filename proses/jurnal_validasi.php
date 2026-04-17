<?php
/**
 * proses/jurnal_validasi.php
 * UPDATE — Dosen/Pembimbing setujui atau tolak jurnal + tambah catatan
 */
session_start();
require_once '../config/db_connect.php';

$allowedRoles = ['dosen pembimbing', 'pembimbing lapang'];
$userRole = strtolower($_SESSION['role_name'] ?? '');

if (!isset($_SESSION['id_user']) || !in_array($userRole, $allowedRoles)) {
    header('Location: ../index.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dosen/jurnal.php'); exit();
}

$id_journal   = (int) ($_POST['id_journal']   ?? 0);
$aksi         = trim($_POST['aksi']           ?? '');   // 'setujui' atau 'tolak'
$catatan      = trim($_POST['catatan_dosen']  ?? '');

if (!$id_journal || !in_array($aksi, ['setujui', 'tolak'])) {
    header('Location: ../dosen/jurnal.php?error=input_tidak_valid'); exit();
}

$status = ($aksi === 'setujui') ? 'Disetujui' : 'Ditolak';
$back   = ($userRole === 'pembimbing lapang')
        ? '../pembimbing/monitoring_mahasiswa.php'
        : '../dosen/jurnal.php';

try {
    $stmt = $conn->prepare("
        UPDATE Daily_journal
        SET status        = :status,
            catatan_dosen = :catatan
        WHERE id_journal  = :id_journal
    ");
    $stmt->execute([
        ':status'     => $status,
        ':catatan'    => $catatan ?: null,
        ':id_journal' => $id_journal,
    ]);

    header("Location: $back?success=jurnal_$aksi"); exit();

} catch (PDOException $e) {
    error_log('jurnal_validasi ERROR: ' . $e->getMessage());
    header("Location: $back?error=db_error"); exit();
}
