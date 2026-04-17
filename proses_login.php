<?php
session_start();
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input    = trim($_POST['email'] ?? '');   // field bisa berisi email, nim, atau nip
    $password = trim($_POST['password'] ?? '');

    if (empty($input) || empty($password)) {
        header("Location: index.php?error=empty"); exit();
    }

    try {
        /*
         * Coba cari user berdasarkan:
         *  1. email  (kolom Users.email)
         *  2. NIM    (kolom Profile.nim)
         *  3. NIP    (kolom Profile.nip)
         *  4. username jika kolom sudah ditambahkan (opsional)
         */
        $query = "
            SELECT u.*, r.nama_role
            FROM Users u
            JOIN Users_role ur ON u.id_user = ur.id_user
            JOIN Roles r ON ur.id_role = r.id_role
            LEFT JOIN Profile p ON u.id_user = p.id_user
            WHERE u.email = :input
               OR p.nim   = :input
               OR p.nip   = :input
            LIMIT 1
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':input', $input);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Cek password — mendukung password_hash() maupun teks biasa (data lama)
            $passwordValid = password_verify($password, $user['password'])
                          || $password === $user['password'];

            if ($passwordValid) {
                $_SESSION['id_user']   = $user['id_user'];
                $_SESSION['nama']      = $user['nama'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['role_name'] = $user['nama_role'];

                // Redirect berdasarkan role
                $role = strtolower($user['nama_role']);
                if ($role === 'admin')                header("Location: admin/dashboard.php");
                elseif ($role === 'dosen pembimbing') header("Location: dosen/dashboard.php");
                elseif ($role === 'pembimbing lapang') header("Location: pembimbing/dashboard.php");
                elseif ($role === 'mahasiswa')        header("Location: mahasiswa/dashboard.php");
                else                                 header("Location: index.php?error=norole");
                exit();

            } else {
                header("Location: index.php?error=wrongpassword"); exit();
            }
        } else {
            header("Location: index.php?error=usernotfound"); exit();
        }

    } catch(PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        header("Location: index.php?error=server"); exit();
    }
} else {
    header("Location: index.php"); exit();
}