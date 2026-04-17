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

// Ambil & sanitasi input
$id_user      = (int) ($_POST['id_user']      ?? 0);
$nama         = trim($_POST['nama']            ?? '');
$email        = trim($_POST['email']           ?? '');
$role         = trim($_POST['role']            ?? '');
$nomor_induk  = trim($_POST['nomor_induk']     ?? '');
$password     = trim($_POST['password']        ?? '');

if (!$id_user || empty($nama) || empty($email) || empty($role)) {
    header('Location: manajemen_user.php?error=field_kosong');
    exit();
}

// Mapping nama role ke id_role
$roleMap = [
    'Mahasiswa'        => 4,
    'Dosen Pembimbing' => 2,
    'Pembimbing Lapang'=> 3,
    'Admin'            => 1,
];

if (!array_key_exists($role, $roleMap)) {
    header('Location: manajemen_user.php?error=role_tidak_valid');
    exit();
}

$id_role = $roleMap[$role];

try {
    // 1. Cek email duplikat — boleh sama dengan milik diri sendiri
    $stmtCek = $conn->prepare(
        "SELECT id_user FROM Users WHERE email = :email AND id_user != :id_user LIMIT 1"
    );
    $stmtCek->execute([':email' => $email, ':id_user' => $id_user]);
    if ($stmtCek->fetch()) {
        header('Location: manajemen_user.php?error=email_duplikat');
        exit();
    }

    // 2. Update tabel Users
    if (!empty($password)) {
        if (strlen($password) < 6) {
            header('Location: manajemen_user.php?error=password_pendek');
            exit();
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmtUser = $conn->prepare(
            "UPDATE Users SET nama = :nama, email = :email, password = :password WHERE id_user = :id_user"
        );
        $stmtUser->execute([
            ':nama'     => $nama,
            ':email'    => $email,
            ':password' => $hashedPassword,
            ':id_user'  => $id_user,
        ]);
    } else {
        $stmtUser = $conn->prepare(
            "UPDATE Users SET nama = :nama, email = :email WHERE id_user = :id_user"
        );
        $stmtUser->execute([
            ':nama'    => $nama,
            ':email'   => $email,
            ':id_user' => $id_user,
        ]);
    }

    // 3. Update role di Users_role
    $stmtCekRole = $conn->prepare("SELECT id_user_role FROM Users_role WHERE id_user = :id_user LIMIT 1");
    $stmtCekRole->execute([':id_user' => $id_user]);
    $existingRole = $stmtCekRole->fetch();

    if ($existingRole) {
        $stmtRole = $conn->prepare(
            "UPDATE Users_role SET id_role = :id_role WHERE id_user = :id_user"
        );
    } else {
        $stmtRole = $conn->prepare(
            "INSERT INTO Users_role (id_user, id_role) VALUES (:id_user, :id_role)"
        );
    }
    $stmtRole->execute([':id_user' => $id_user, ':id_role' => $id_role]);

    // 4. Update Profile (NIM/NIP)
    if (!empty($nomor_induk)) {
        $nim = ($id_role === 4) ? $nomor_induk : null;
        $nip = ($id_role !== 4) ? $nomor_induk : null;

        $stmtCekProfile = $conn->prepare("SELECT id_profile FROM Profile WHERE id_user = :id_user LIMIT 1");
        $stmtCekProfile->execute([':id_user' => $id_user]);
        $existingProfile = $stmtCekProfile->fetch();

        if ($existingProfile) {
            $stmtProfile = $conn->prepare(
                "UPDATE Profile SET nim = :nim, nip = :nip WHERE id_user = :id_user"
            );
        } else {
            $stmtProfile = $conn->prepare(
                "INSERT INTO Profile (id_user, nim, nip) VALUES (:id_user, :nim, :nip)"
            );
        }
        $stmtProfile->execute([':id_user' => $id_user, ':nim' => $nim, ':nip' => $nip]);
    }

    header('Location: manajemen_user.php?success=user_diperbarui');
    exit();

} catch (PDOException $e) {
    error_log('proses_edit_user ERROR: ' . $e->getMessage());
    header('Location: manajemen_user.php?error=db_error');
    exit();
}
