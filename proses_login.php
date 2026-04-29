<?php
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }

    try {
        // Query menggunakan skema baru (users + roles)
        $query = "SELECT u.id, u.name, u.email, u.password, u.role_id, r.name AS role_name
                  FROM users u
                  JOIN roles r ON u.role_id = r.id
                  WHERE u.email = :email LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Detect bcrypt hash by prefix ($2y$ or $2a$ or $2b$)
            $isBcrypt = str_starts_with($user['password'], '$2y$') ||
                        str_starts_with($user['password'], '$2a$') ||
                        str_starts_with($user['password'], '$2b$');

            $passwordValid = $isBcrypt
                ? password_verify($password, $user['password'])
                : ($password === $user['password']);

            if ($passwordValid) {

                $_SESSION['id_user']   = $user['id'];
                $_SESSION['nama']      = $user['name'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['role_id']   = $user['role_id'];
                $_SESSION['role_name'] = $user['role_name'];

                // Log login ke database
                try {
                    $logStmt = $conn->prepare("INSERT INTO log_logins (user_id, email, status, ip_address, device_info) VALUES (:uid, :email, 'success', :ip, :device)");
                    $logStmt->execute([
                        'uid'    => $user['id'],
                        'email'  => $user['email'],
                        'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    ]);
                } catch(PDOException $e) {
                    error_log('Failed to log login: ' . $e->getMessage());
                }

                // Redirect berdasarkan role
                $role = strtolower($user['role_name']);

                if ($role === 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif ($role === 'dosen_pembimbing') {
                    header("Location: dosen/dashboard.php");
                } elseif ($role === 'pembimbing_lapang') {
                    header("Location: pembimbing/dashboard.php");
                } elseif ($role === 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                }
                exit();

            } else {
                // Log failed login
                try {
                    $logStmt = $conn->prepare("INSERT INTO log_logins (user_id, email, status, ip_address, device_info) VALUES (:uid, :email, 'failed', :ip, :device)");
                    $logStmt->execute([
                        'uid'    => $user['id'],
                        'email'  => $email,
                        'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    ]);
                } catch(PDOException $e) {
                    error_log('Failed to log login: ' . $e->getMessage());
                }

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