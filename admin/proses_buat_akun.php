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
$nama                  = trim($_POST['nama']                 ?? '');
$email                 = trim($_POST['email']                ?? '');
$role                  = trim($_POST['role']                 ?? '');
$nomor_induk           = trim($_POST['nomor_induk']          ?? '');
$password              = trim($_POST['password']             ?? '');
$id_dosen_pembimbing   = (int) ($_POST['id_dosen_pembimbing'] ?? 0) ?: null;
$id_pembimbing_lapang  = (int) ($_POST['id_pembimbing_lapang'] ?? 0) ?: null;

// Validasi dasar
if (empty($nama) || empty($email) || empty($role) || empty($password)) {
    header('Location: manajemen_user.php?error=field_kosong');
    exit();
}

if (strlen($password) < 6) {
    header('Location: manajemen_user.php?error=password_pendek');
    exit();
}

// Mapping nama role ke id_role di DB
$roleMap = [
    'mahasiswa'  => 4,
    'dosen'      => 2,
    'pembimbing' => 3,
    'admin'      => 1,
];

if (!array_key_exists($role, $roleMap)) {
    header('Location: manajemen_user.php?error=role_tidak_valid');
    exit();
}

$id_role = $roleMap[$role];

try {
    // 1. Cek apakah email sudah ada
    $stmtCek = $conn->prepare("SELECT id_user FROM Users WHERE email = :email LIMIT 1");
    $stmtCek->execute([':email' => $email]);
    if ($stmtCek->fetch()) {
        header('Location: manajemen_user.php?error=email_duplikat');
        exit();
    }

    // 2. Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 3. Insert ke tabel Users
    $stmtUser = $conn->prepare(
        "INSERT INTO Users (nama, email, password) VALUES (:nama, :email, :password)"
    );
    $stmtUser->execute([
        ':nama'     => $nama,
        ':email'    => $email,
        ':password' => $hashedPassword,
    ]);
    $newId = $conn->lastInsertId();

    // 4. Insert ke Users_role
    $stmtRole = $conn->prepare(
        "INSERT INTO Users_role (id_user, id_role) VALUES (:id_user, :id_role)"
    );
    $stmtRole->execute([':id_user' => $newId, ':id_role' => $id_role]);

    // 5. Insert ke Profile — selalu buat row Profile agar user bisa edit profil
    $nim = ($id_role === 4 && !empty($nomor_induk)) ? $nomor_induk : null;   // Mahasiswa
    $nip = ($id_role !== 4 && !empty($nomor_induk)) ? $nomor_induk : null;   // Non-mahasiswa

    // Hanya simpan relasi dosen & pembimbing jika role = Mahasiswa
    $dosenRel    = ($id_role === 4) ? $id_dosen_pembimbing  : null;
    $pembimbingRel = ($id_role === 4) ? $id_pembimbing_lapang : null;

    $stmtProfile = $conn->prepare(
        "INSERT INTO Profile (id_user, nim, nip, id_dosen_pembimbing, id_pembimbing_lapang)
         VALUES (:id_user, :nim, :nip, :id_dosen, :id_pembimbing)"
    );
    $stmtProfile->execute([
        ':id_user'      => $newId,
        ':nim'          => $nim,
        ':nip'          => $nip,
        ':id_dosen'     => $dosenRel,
        ':id_pembimbing'=> $pembimbingRel,
    ]);

    header('Location: manajemen_user.php?success=akun_dibuat');
    exit();

} catch (PDOException $e) {
    // Log error ke file (jangan tampilkan detail ke user)
    error_log('proses_buat_akun ERROR: ' . $e->getMessage());
    header('Location: manajemen_user.php?error=db_error');
    exit();
}
