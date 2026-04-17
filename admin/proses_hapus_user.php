<?php
session_start();
require_once '../config/db_connect.php';

// Session Guard
if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manajemen_user.php');
    exit();
}

$id_user = (int) ($_POST['id_user'] ?? 0);

if (!$id_user) {
    header('Location: manajemen_user.php?error=id_tidak_valid');
    exit();
}

// Jangan boleh hapus diri sendiri
if ($id_user === (int) $_SESSION['id_user']) {
    header('Location: manajemen_user.php?error=hapus_diri_sendiri');
    exit();
}

try {
    // Hapus secara berurutan sesuai foreign key:
    // Profile → Users_role → Attendances → Daily_journal → Final_evaluation → 
    // Internship_placement → Reports → Users

    $tables = [
        "DELETE FROM Profile              WHERE id_user = :id",
        "DELETE FROM Users_role           WHERE id_user = :id",
        "DELETE FROM Attendance_validation WHERE id_pembimbing = :id",
        "DELETE FROM Attendances          WHERE id_user = :id",
        "DELETE FROM Daily_journal        WHERE id_user = :id",
        "DELETE FROM Final_evaluation     WHERE id_user = :id",
        "DELETE FROM Internship_placement WHERE id_user = :id",
        "DELETE FROM Reports              WHERE id_user = :id",
        "DELETE FROM Users                WHERE id_user = :id",
    ];

    foreach ($tables as $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id_user]);
    }

    header('Location: manajemen_user.php?success=user_dihapus');
    exit();

} catch (PDOException $e) {
    error_log('proses_hapus_user ERROR: ' . $e->getMessage());
    header('Location: manajemen_user.php?error=db_error');
    exit();
}
