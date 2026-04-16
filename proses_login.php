<?php
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Di index.php, ganti name="username" menjadi name="email" agar sesuai DB
    $email = trim($_POST['email']); 
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }

    try {
        // Query Join untuk mengambil data User sekaligus Nama Role-nya
        $query = "SELECT u.*, r.nama_role 
                  FROM Users u
                  JOIN Users_role ur ON u.id_user = ur.id_user
                  JOIN Roles r ON ur.id_role = r.id_role
                  WHERE u.email = :email LIMIT 1";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Cek password (masih teks biasa sesuai data dummy sebelumnya)
            if ($password == $user['password']) {
                
                $_SESSION['id_user']   = $user['id_user'];
                $_SESSION['nama']      = $user['nama'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['role_name'] = $user['nama_role']; // Menyimpan nama role

                // Pengalihan halaman berdasarkan NAMA ROLE di database
                $role = strtolower($user['nama_role']);
                
                if ($role == 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif ($role == 'dosen pembimbing' || $role == 'pembimbing lapang') {
                    header("Location: dosen/dashboard.php");
                } elseif ($role == 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                }
                exit();
                
            } else {
                header("Location: index.php?error=wrongpassword");
                exit();
            }
        } else {
            header("Location: index.php?error=usernotfound");
            exit();
        }

    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit();
}