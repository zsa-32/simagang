<?php
session_start();
require_once '../config/db_connect.php';

// Session Guard
if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$role = 'admin';
$activePage = 'manajemen_user';

// Ambil semua user dari database dengan JOIN ke role dan profil
$stmt = $conn->query("
    SELECT 
        u.id_user, u.nama, u.email,
        r.nama_role,
        COALESCE(p.nim, p.nip, '-')  AS nomor_induk,
        COALESCE(c.nama_company, '-') AS perusahaan,
        p.id_dosen_pembimbing, p.id_pembimbing_lapang
    FROM Users u
    LEFT JOIN Users_role   ur ON u.id_user   = ur.id_user
    LEFT JOIN Roles         r  ON ur.id_role  = r.id_role
    LEFT JOIN Profile       p  ON u.id_user   = p.id_user
    LEFT JOIN Internship_placement ip ON u.id_user = ip.id_user
    LEFT JOIN Company       c  ON ip.id_company = c.id_company
    ORDER BY r.id_role ASC, u.nama ASC
");
$usersDB = $stmt->fetchAll();

// Ambil daftar Dosen Pembimbing
$stmtDosen = $conn->query("
    SELECT u.id_user, u.nama FROM Users u
    JOIN Users_role ur ON u.id_user = ur.id_user
    JOIN Roles r ON ur.id_role = r.id_role
    WHERE LOWER(r.nama_role) = 'dosen pembimbing'
    ORDER BY u.nama ASC
");
$dosenList = $stmtDosen->fetchAll();

// Ambil daftar Pembimbing Lapang
$stmtPembimbing = $conn->query("
    SELECT u.id_user, u.nama FROM Users u
    JOIN Users_role ur ON u.id_user = ur.id_user
    JOIN Roles r ON ur.id_role = r.id_role
    WHERE LOWER(r.nama_role) = 'pembimbing lapang'
    ORDER BY u.nama ASC
");
$pembimbingList = $stmtPembimbing->fetchAll();

// Palet warna avatar bergilir
$avatarColors = ['bg-blue-600','bg-indigo-500','bg-green-600','bg-blue-500','bg-purple-600','bg-purple-500','bg-indigo-600','bg-blue-700'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Magang TIF</title>
    <meta name="description" content="Kelola akun Mahasiswa dan Dosen Pembimbing pada sistem Magang TIF.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden text-gray-800">

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Header -->
        <?php include '../includes/header.php'; ?>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">

                <!-- Notifikasi -->
                <?php if (isset($_GET['success'])): ?>
                    <?php
                    $msgs = [
                        'akun_dibuat'   => 'Akun berhasil dibuat.',
                        'user_diperbarui'=> 'Data user berhasil diperbarui.',
                        'user_dihapus'  => 'User berhasil dihapus.',
                    ];
                    $msg = $msgs[$_GET['success']] ?? 'Berhasil.';
                    ?>
                    <div id="notifBox" class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-[13px] font-medium">
                        <i class="fas fa-check-circle text-green-500"></i> <?= htmlspecialchars($msg) ?>
                        <button onclick="document.getElementById('notifBox').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
                    </div>
                <?php elseif (isset($_GET['error'])): ?>
                    <?php
                    $errs = [
                        'field_kosong'       => 'Harap lengkapi semua field yang wajib diisi.',
                        'password_pendek'    => 'Password minimal 6 karakter.',
                        'email_duplikat'     => 'Email sudah terdaftar, gunakan email lain.',
                        'role_tidak_valid'   => 'Role yang dipilih tidak valid.',
                        'id_tidak_valid'     => 'ID user tidak valid.',
                        'hapus_diri_sendiri' => 'Anda tidak dapat menghapus akun sendiri.',
                        'db_error'           => 'Terjadi kesalahan pada database, silakan coba lagi.',
                    ];
                    $err = $errs[$_GET['error']] ?? 'Terjadi kesalahan.';
                    ?>
                    <div id="notifBox" class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-[13px] font-medium">
                        <i class="fas fa-exclamation-circle text-red-500"></i> <?= htmlspecialchars($err) ?>
                        <button onclick="document.getElementById('notifBox').remove()" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>
                <!-- Page Heading -->
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Manajemen User</h2>
                        <p class="text-gray-500 text-sm mt-0.5">Kelola akun Mahasiswa dan Dosen Pembimbing</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button id="btnExport"
                            class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                            <i class="fas fa-download text-gray-500"></i> Export
                        </button>
                        <button id="btnBuatAkun" onclick="openModal()"
                            class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-[13px] font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                            <i class="fas fa-user-plus"></i> Buat Akun
                        </button>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <!-- Search & Filter -->
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-5 border-b border-gray-100">
                        <div class="relative flex-1 w-full">
                            <i
                                class="fas fa-search text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-[13px]"></i>
                            <input id="searchInput" type="text" placeholder="Cari nama atau email..."
                                oninput="filterTable()"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-[13px] text-gray-700 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button id="filterAll" onclick="setFilter('semua')"
                                class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-blue-600 text-white transition-all">Semua</button>
                            <button id="filterMhs" onclick="setFilter('mahasiswa')"
                                class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all">Mahasiswa</button>
                            <button id="filterDosen" onclick="setFilter('dosen')"
                                class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all">Dosen
                                Pembimbing</button>
                            <button id="filterpembimbing" onclick="setFilter('pembimbing')"
                                class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all">
                                Pembimbing Lapang</button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full" id="userTable">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        Nama</th>
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        Role</th>
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        ID</th>
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        Perusahaan</th>
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        Aksi</th>
                                </tr>
                            </thead>
                                <?php
                                $colorIdx = 0;
                                foreach ($usersDB as $u):
                                    $initials   = strtoupper(substr($u['nama'], 0, 1));
                                    $roleLabel  = $u['nama_role'] ?? '-';
                                    $roleLower  = strtolower($roleLabel);
                                    $color      = $avatarColors[$colorIdx % count($avatarColors)];
                                    $colorIdx++;

                                    // Badge warna berdasarkan role
                                    if ($roleLower === 'mahasiswa') {
                                        $roleBadge = 'bg-blue-100 text-blue-700';
                                        $dataRole  = 'mahasiswa';
                                    } elseif ($roleLower === 'dosen pembimbing') {
                                        $roleBadge = 'bg-purple-100 text-purple-700';
                                        $dataRole  = 'dosen';
                                    } elseif ($roleLower === 'pembimbing lapang') {
                                        $roleBadge = 'bg-green-100 text-green-700';
                                        $dataRole  = 'pembimbing';
                                    } else {
                                        $roleBadge = 'bg-gray-100 text-gray-600';
                                        $dataRole  = 'admin';
                                    }
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors user-row"
                                        data-role="<?= $dataRole ?>"
                                        data-search="<?= strtolower($u['nama'] . ' ' . $u['email']) ?>">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full <?= $color ?> text-white flex items-center justify-center text-[13px] font-bold shrink-0">
                                                    <?= $initials ?></div>
                                                <div>
                                                    <p class="font-semibold text-gray-800 text-[14px]">
                                                        <?= htmlspecialchars($u['nama']) ?></p>
                                                    <p class="text-[12px] text-gray-400">
                                                        <?= htmlspecialchars($u['email']) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-lg text-[12px] font-semibold <?= $roleBadge ?>"><?= htmlspecialchars($roleLabel) ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-[13px] text-gray-600 font-medium"><?= htmlspecialchars($u['nomor_induk']) ?></td>
                                        <td class="px-6 py-4 text-[13px] text-gray-600"><?= htmlspecialchars($u['perusahaan']) ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    class="w-8 h-8 rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 transition-colors flex items-center justify-center"
                                                    title="Edit"
                                                    onclick="openEditModal('<?= $u['id_user'] ?>', '<?= htmlspecialchars($u['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($roleLabel, ENT_QUOTES) ?>', '<?= htmlspecialchars($u['nomor_induk'], ENT_QUOTES) ?>', '<?= (int)($u['id_dosen_pembimbing'] ?? 0) ?>', '<?= (int)($u['id_pembimbing_lapang'] ?? 0) ?>')">
                                                    <i class="fas fa-pen text-[12px]"></i>
                                                </button>
                                                <!-- Hapus via form POST -->
                                                <form method="POST" action="proses_hapus_user.php" style="display:inline">
                                                    <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                                                    <button type="submit"
                                                        class="w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors flex items-center justify-center"
                                                        title="Hapus"
                                                        onclick="return confirm('Hapus user <?= htmlspecialchars($u['nama'], ENT_QUOTES) ?>?')">
                                                        <i class="fas fa-trash text-[12px]"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
                        <p class="text-[13px] text-gray-500">Menampilkan 1-8 dari 11 user</p>
                        <div class="flex items-center gap-1">
                            <button
                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 flex items-center justify-center text-[12px]"><i
                                    class="fas fa-chevron-left"></i></button>
                            <button
                                class="w-8 h-8 rounded-lg bg-blue-600 text-white font-semibold text-[13px] flex items-center justify-center">1</button>
                            <button
                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 text-[13px] flex items-center justify-center">2</button>
                            <button
                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 flex items-center justify-center text-[12px]"><i
                                    class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- Modal Buat Akun -->
    <div id="modalBuatAkun" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Buat Akun Baru</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="proses_buat_akun.php" method="POST" class="space-y-4">
    
    <div>
        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
        <input type="text" name="nama" placeholder="Masukkan nama lengkap" required
            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
    </div>

    <div class="mb-4">
        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Email</label>
        <input type="email" name="email" placeholder="contoh@polije.ac.id" required
            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="mb-4">
            <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Role</label>
            <select name="role" id="roleSelectBuat" required onchange="toggleMhsFields(this.value, 'buat')"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-white">
                <option value="">Pilih role...</option>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="dosen">Dosen Pembimbing</option>
                <option value="pembimbing">Pembimbing Lapang</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-[13px] font-medium text-gray-700 mb-1.5">NIM/NIP</label>
            <input type="text" name="nomor_induk" placeholder="Masukan NIM/NIP" required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
        </div>
    </div>

    <!-- Dropdown relasi (hanya muncul jika role = Mahasiswa) -->
    <div id="mhsFieldsBuat" class="hidden space-y-3 p-3 bg-blue-50 rounded-xl border border-blue-100">
        <p class="text-[12px] font-semibold text-blue-600"><i class="fas fa-link mr-1"></i>Hubungkan dengan Pembimbing</p>
        <div>
            <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Dosen Pembimbing</label>
            <select name="id_dosen_pembimbing"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-white">
                <option value="">-- Pilih Dosen Pembimbing --</option>
                <?php foreach ($dosenList as $d): ?>
                <option value="<?= $d['id_user'] ?>"><?= htmlspecialchars($d['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Pembimbing Lapang</label>
            <select name="id_pembimbing_lapang"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-white">
                <option value="">-- Pilih Pembimbing Lapang --</option>
                <?php foreach ($pembimbingList as $pb): ?>
                <option value="<?= $pb['id_user'] ?>"><?= htmlspecialchars($pb['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div>
    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Password</label>
    <div class="relative">
        <input type="password" id="passwordInput" name="password" placeholder="Minimal 8 karakter" required
            class="w-full pl-4 pr-10 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
        
        <span id="togglePasswordAkun" class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-eye-slash" id="eyeIconAkun"></i>
        </span>
    </div>
</div>

    <div class="flex items-center gap-3 pt-2">
        <button type="button" onclick="closeModal()"
            class="flex-1 py-2.5 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
        <button type="submit"
            class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-[13px] font-semibold hover:bg-blue-700 transition-colors">Simpan
            Akun</button>
    </div>
</form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div id="modalEditUser" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Edit User</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <!-- Form -->
            <form action="proses_edit_user.php" method="POST" class="px-6 py-5 space-y-4">
                <input type="hidden" name="id_user" id="editUserId">

                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="nama" id="editNama" placeholder="Nama lengkap" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" id="editEmail" placeholder="contoh@polije.ac.id" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Role</label>
                        <select name="role" id="editRole" required onchange="toggleMhsFields(this.value, 'edit')"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                            <option value="">Pilih role...</option>
                            <option value="Mahasiswa">Mahasiswa</option>
                            <option value="Dosen Pembimbing">Dosen Pembimbing</option>
                            <option value="Pembimbing Lapang">Pembimbing Lapang</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">NIM / NIP</label>
                        <input type="text" name="nomor_induk" id="editNomorInduk" placeholder="NIM / NIP"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                    </div>
                </div>

                <!-- Dropdown relasi Mahasiswa -->
                <div id="mhsFieldsEdit" class="hidden space-y-3 p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-[12px] font-semibold text-blue-600"><i class="fas fa-link mr-1"></i>Hubungkan dengan Pembimbing</p>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Dosen Pembimbing</label>
                        <select name="id_dosen_pembimbing" id="editIdDosen"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-white">
                            <option value="">-- Pilih Dosen Pembimbing --</option>
                            <?php foreach ($dosenList as $d): ?>
                            <option value="<?= $d['id_user'] ?>"><?= htmlspecialchars($d['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Pembimbing Lapang</label>
                        <select name="id_pembimbing_lapang" id="editIdPembimbing"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-white">
                            <option value="">-- Pilih Pembimbing Lapang --</option>
                            <?php foreach ($pembimbingList as $pb): ?>
                            <option value="<?= $pb['id_user'] ?>"><?= htmlspecialchars($pb['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Ganti Password</label>
                    <input type="password" name="password" id="editPassword"
                        placeholder="Kosongkan jika tidak ingin mengubah"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()"
                        class="px-5 py-2.5 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-[13px] font-semibold hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentFilter = 'semua';

        function openModal() {
            document.getElementById('modalBuatAkun').classList.remove('hidden');
            document.getElementById('modalBuatAkun').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('modalBuatAkun').classList.add('hidden');
            document.getElementById('modalBuatAkun').classList.remove('flex');
        }

        function setFilter(filter) {
            currentFilter = filter;
            const btns = { 'semua': 'filterAll', 'mahasiswa': 'filterMhs', 'dosen': 'filterDosen','pembimbing':'filterpembimbing' };
            Object.values(btns).forEach(id => {
                document.getElementById(id).className = 'filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all';
            });
            document.getElementById(btns[filter]).className = 'filter-btn px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-blue-600 text-white transition-all';
            filterTable();
        }

        function filterTable() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.user-row');
            rows.forEach(row => {
                const roleMatch = currentFilter === 'semua' || row.dataset.role === currentFilter;
                const searchMatch = row.dataset.search.includes(search);
                row.style.display = (roleMatch && searchMatch) ? '' : 'none';
            });
        }

        // ===== Toggle Mhs Fields =====
        function toggleMhsFields(roleVal, scope) {
            const isMhs = roleVal.toLowerCase() === 'mahasiswa';
            const el = document.getElementById(scope === 'buat' ? 'mhsFieldsBuat' : 'mhsFieldsEdit');
            if (!el) return;
            el.classList.toggle('hidden', !isMhs);
        }

        // ===== Edit User Modal =====
        function openEditModal(idUser, nama, email, role, nomorInduk, idDosen, idPembimbing) {
            document.getElementById('editUserId').value = idUser;
            document.getElementById('editNama').value = nama;
            document.getElementById('editEmail').value = email;
            document.getElementById('editNomorInduk').value = nomorInduk;
            document.getElementById('editPassword').value = '';

            // Set selected role
            const selectRole = document.getElementById('editRole');
            for (let i = 0; i < selectRole.options.length; i++) {
                if (selectRole.options[i].value === role) {
                    selectRole.selectedIndex = i;
                    break;
                }
            }

            // Tampilkan/sembunyikan dropdown relasi
            toggleMhsFields(role, 'edit');

            // Set dropdown dosen & pembimbing
            const selDosen = document.getElementById('editIdDosen');
            const selPembimbing = document.getElementById('editIdPembimbing');
            selDosen.value = idDosen || '';
            selPembimbing.value = idPembimbing || '';

            const modal = document.getElementById('modalEditUser');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditModal() {
            const modal = document.getElementById('modalEditUser');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Toggle password visibility
        document.getElementById('togglePasswordAkun').addEventListener('click', function () {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIconAkun');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });

        // Close modal on backdrop click
        document.getElementById('modalBuatAkun').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        document.getElementById('modalEditUser').addEventListener('click', function (e) {
            if (e.target === this) closeEditModal();
        });
    </script>
</body>

</html>