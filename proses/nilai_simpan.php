<?php
/**
 * proses/nilai_simpan.php
 * Dosen simpan/update nilai laporan & seminar mahasiswa ke Final_evaluation
 */
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'dosen pembimbing') {
    header('Location: ../index.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dosen/penilaian.php'); exit();
}

$id_dosen    = (int) $_SESSION['id_user'];
$id_mhs      = (int) ($_POST['id_user'] ?? 0);
$nilaiLaporan = $_POST['nilai_laporan'] !== '' ? (int) $_POST['nilai_laporan'] : null;
$nilaiSeminar = $_POST['nilai_seminar'] !== '' ? (int) $_POST['nilai_seminar'] : null;
$catatan      = trim($_POST['catatan'] ?? '');

if (!$id_mhs) {
    header('Location: ../dosen/penilaian.php?error=invalid'); exit();
}

// Validasi range nilai
if ($nilaiLaporan !== null && ($nilaiLaporan < 0 || $nilaiLaporan > 100)) {
    header('Location: ../dosen/penilaian.php?error=range'); exit();
}
if ($nilaiSeminar !== null && ($nilaiSeminar < 0 || $nilaiSeminar > 100)) {
    header('Location: ../dosen/penilaian.php?error=range'); exit();
}

try {
    // Cek apakah record sudah ada di Final_evaluation
    $cek = $conn->prepare("SELECT id_evaluation FROM Final_evaluation WHERE id_user = :id LIMIT 1");
    $cek->execute([':id' => $id_mhs]);
    $existing = $cek->fetch();

    if ($existing) {
        // UPDATE
        $stmt = $conn->prepare("
            UPDATE Final_evaluation
            SET nilai_laporan = :laporan,
                nilai_seminar = :seminar,
                catatan       = :catatan,
                nilai_akhir   = ROUND((COALESCE(:laporan2,0) + COALESCE(:seminar2,0)) / 2, 2)
            WHERE id_user = :id
        ");
        $stmt->execute([
            ':id'      => $id_mhs,
            ':laporan' => $nilaiLaporan,
            ':seminar' => $nilaiSeminar,
            ':catatan' => $catatan ?: null,
            ':laporan2'=> $nilaiLaporan,
            ':seminar2'=> $nilaiSeminar,
        ]);
    } else {
        // INSERT baru
        $stmt = $conn->prepare("
            INSERT INTO Final_evaluation (id_user, nilai_laporan, nilai_seminar, catatan, nilai_akhir)
            VALUES (:id, :laporan, :seminar, :catatan,
                   ROUND((COALESCE(:laporan2,0) + COALESCE(:seminar2,0)) / 2, 2))
        ");
        $stmt->execute([
            ':id'      => $id_mhs,
            ':laporan' => $nilaiLaporan,
            ':seminar' => $nilaiSeminar,
            ':catatan' => $catatan ?: null,
            ':laporan2'=> $nilaiLaporan,
            ':seminar2'=> $nilaiSeminar,
        ]);
    }

    header('Location: ../dosen/penilaian.php?success=1'); exit();

} catch (PDOException $e) {
    error_log('nilai_simpan ERROR: ' . $e->getMessage());
    header('Location: ../dosen/penilaian.php?error=db_error'); exit();
}
