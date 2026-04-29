<?php
/**
 * Frontend Role Guard
 * Include this file at the top of any PHP page to protect it by role.
 *
 * Usage:
 *   require_once '../config/role_guard.php';
 *   checkRole('admin');           // Only admin
 *   checkRole('mahasiswa');       // Only mahasiswa
 *   checkRole(['admin', 'dosen_pembimbing']); // Multiple roles
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in and has the required role.
 * Redirects to login page with error if not authorized.
 *
 * @param string|array $allowedRoles Single role name or array of allowed role names
 */
function checkRole(string|array $allowedRoles): void
{
    // Check if user is logged in
    if (!isset($_SESSION['id_user']) || !isset($_SESSION['role_name'])) {
        header('Location: ' . getBaseUrl() . 'index.php?error=notloggedin');
        exit();
    }

    // Normalize to array
    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    $userRole = strtolower($_SESSION['role_name']);

    if (!in_array($userRole, $allowedRoles, true)) {
        // HTTP 403 — Akses ditolak
        http_response_code(403);
        echo '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>403 - Akses Ditolak</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
            <style>body { font-family: "Inter", sans-serif; }</style>
        </head>
        <body class="bg-gray-50 flex items-center justify-center min-h-screen">
            <div class="text-center p-8 bg-white rounded-2xl shadow-lg max-w-md mx-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">403 — Akses Ditolak</h1>
                <p class="text-gray-500 mb-6">Akses Ditolak: Anda tidak memiliki otoritas untuk halaman ini.</p>
                <a href="javascript:history.back()" class="inline-block px-6 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition">Kembali</a>
            </div>
        </body>
        </html>';
        exit();
    }
}

/**
 * Get current logged-in user data from session
 */
function getSessionUser(): ?array
{
    if (!isset($_SESSION['id_user'])) {
        return null;
    }

    return [
        'id'        => $_SESSION['id_user'],
        'name'      => $_SESSION['nama'] ?? '',
        'email'     => $_SESSION['email'] ?? '',
        'role_id'   => $_SESSION['role_id'] ?? null,
        'role_name' => $_SESSION['role_name'] ?? '',
    ];
}

/**
 * Check if user is logged in (without role check)
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['id_user']);
}

/**
 * Get base URL for redirects
 */
function getBaseUrl(): string
{
    return defined('BASE_URL') ? BASE_URL : '/simagang/simagang/';
}
