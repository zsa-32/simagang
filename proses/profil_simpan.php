<?php
/**
 * proses/profil_simpan.php
 * UPDATE — Simpan perubahan profil user (mahasiswa, dosen, pembimbing)
 */
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php'); exit();
}

$id_user  = (int) $_SESSION['id_user'];
$role     = strtolower($_SESSION['role_name'] ?? '');

// Ambil data form
$nama     = trim($_POST['nama']     ?? '');
$email    = trim($_POST['email']    ?? '');
$no_hp    = trim($_POST['no_hp']    ?? '');
$alamat   = trim($_POST['alamat']   ?? '');
$prodi    = trim($_POST['prodi']    ?? '');
$semester = (int) ($_POST['semester'] ?? 0);

// Tentukan redirect balik sesuai role
$back = match($role) {
    'mahasiswa'        => '../mahasiswa/profil.php',
    'dosen pembimbing' => '../dosen/profil.php',
    'pembimbing lapang'=> '../pembimbing/profil.php',
    default            => '../index.php',
};

if (empty($nama) || empty($email)) {
    header("Location: $back?error=field_kosong"); exit();
}

try {
    // 1. Update tabel Users (nama & email)
    $stmtCekEmail = $conn->prepare(
        "SELECT id_user FROM Users WHERE email = :email AND id_user != :id_user LIMIT 1"
    );
    $stmtCekEmail->execute([':email' => $email, ':id_user' => $id_user]);
    if ($stmtCekEmail->fetch()) {
        header("Location: $back?error=email_duplikat"); exit();
    }

    $conn->prepare("UPDATE Users SET nama = :nama, email = :email WHERE id_user = :id")
         ->execute([':nama' => $nama, ':email' => $email, ':id' => $id_user]);

    // 2. Update session nama
    $_SESSION['nama'] = $nama;

    // 3. Upload foto profil (opsional)
    $foto_path = null;
    if (!empty($_FILES['foto']['name'])) {
        $file     = $_FILES['foto'];
        $maxSize  = 2 * 1024 * 1024;
        $allowed  = ['image/jpeg', 'image/png', 'image/jpg'];

        if ($file['size'] > $maxSize) {
            header("Location: $back?error=file_besar"); exit();
        }
        if (!in_array($file['type'], $allowed)) {
            header("Location: $back?error=file_format"); exit();
        }

        $uploadDir = '../assets/uploads/profile/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'profil_' . $id_user . '.' . $ext;
        $dest     = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            header("Location: $back?error=upload_gagal"); exit();
        }
        $foto_path = 'assets/uploads/profile/' . $fileName;
    }

    // 4. Upsert tabel Profile
    $stmtCek = $conn->prepare("SELECT id_profile FROM Profile WHERE id_user = :id LIMIT 1");
    $stmtCek->execute([':id' => $id_user]);
    $existing = $stmtCek->fetch();

    if ($existing) {
        $sql = "UPDATE Profile SET no_hp = :no_hp, alamat = :alamat, prodi = :prodi, semester = :semester";
        if ($foto_path) $sql .= ", foto = :foto";
        $sql .= " WHERE id_user = :id_user";
    } else {
        $sql = "INSERT INTO Profile (id_user, no_hp, alamat, prodi, semester" . ($foto_path ? ", foto" : "") . ")
                VALUES (:id_user, :no_hp, :alamat, :prodi, :semester" . ($foto_path ? ", :foto" : "") . ")";
    }

    $params = [
        ':id_user'  => $id_user,
        ':no_hp'    => $no_hp    ?: null,
        ':alamat'   => $alamat   ?: null,
        ':prodi'    => $prodi    ?: null,
        ':semester' => $semester ?: null,
    ];
    if ($foto_path) $params[':foto'] = $foto_path;

    $conn->prepare($sql)->execute($params);

    // 5. Ganti password (opsional)
    $password_baru = trim($_POST['password_baru'] ?? '');
    if (!empty($password_baru)) {
        if (strlen($password_baru) < 6) {
            header("Location: $back?error=password_pendek"); exit();
        }
        $hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $conn->prepare("UPDATE Users SET password = :pw WHERE id_user = :id")
             ->execute([':pw' => $hash, ':id' => $id_user]);
    }

    header("Location: $back?success=profil_disimpan"); exit();

} catch (PDOException $e) {
    error_log('profil_simpan ERROR: ' . $e->getMessage());
    header("Location: $back?error=db_error"); exit();
}
