<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("admin");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'admin';
$activePage = 'manajemen_user';

// Handle create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $name = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $roleInput = $_POST['role'];
        $nomorInduk = trim($_POST['nomor_induk']);

        $roleMap = ['mahasiswa' => 2, 'dosen' => 3, 'pembimbing' => 4];
        $roleId = $roleMap[$roleInput] ?? 2;

        $stmt = $conn->prepare("INSERT INTO users (role_id, name, email, password) VALUES (:rid, :name, :email, :pw)");
        $stmt->execute(['rid' => $roleId, 'name' => $name, 'email' => $email, 'pw' => $password]);
        $newUserId = $conn->lastInsertId();

        if ($roleInput === 'mahasiswa') {
            $conn->prepare("INSERT INTO mahasiswa (user_id, nama, no_ktm, status) VALUES (:uid, :nama, :nim, 'Aktif')")
                ->execute(['uid' => $newUserId, 'nama' => $name, 'nim' => $nomorInduk]);
        } elseif ($roleInput === 'dosen') {
            $conn->prepare("INSERT INTO dosen_pembimbing (user_id, nama, nip, email) VALUES (:uid, :nama, :nip, :email)")
                ->execute(['uid' => $newUserId, 'nama' => $name, 'nip' => $nomorInduk, 'email' => $email]);
        } elseif ($roleInput === 'pembimbing') {
            $conn->prepare("INSERT INTO pembimbing_lapang (user_id, nama, jabatan, email) VALUES (:uid, :nama, :jbt, :email)")
                ->execute(['uid' => $newUserId, 'nama' => $name, 'jbt' => $nomorInduk, 'email' => $email]);
        }
        header("Location: manajemen_user.php?created=1"); exit;
    }
    if ($_POST['action'] === 'edit') {
        $uid = (int)$_POST['id_user'];
        $name = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $conn->prepare("UPDATE users SET name = :n, email = :e, updated_at = NOW() WHERE id = :id")->execute(['n' => $name, 'e' => $email, 'id' => $uid]);
        if (!empty($_POST['password'])) {
            $pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $conn->prepare("UPDATE users SET password = :pw WHERE id = :id")->execute(['pw' => $pw, 'id' => $uid]);
        }
        header("Location: manajemen_user.php?updated=1"); exit;
    }
    if ($_POST['action'] === 'delete') {
        $uid = (int)$_POST['id_user'];
        if ($uid !== (int)$_SESSION['id_user']) {
            $conn->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $uid]);
        }
        header("Location: manajemen_user.php?deleted=1"); exit;
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalUsers = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role_id != 1")->fetchColumn();
$totalPages = max(1, ceil($totalUsers / $perPage));

$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, r.name as role_name,
           m.no_ktm as nim, d.nip, pl.jabatan as pl_jabatan,
           c.nama_perusahaan
    FROM users u
    JOIN roles r ON u.role_id = r.id
    LEFT JOIN mahasiswa m ON m.user_id = u.id
    LEFT JOIN dosen_pembimbing d ON d.user_id = u.id
    LEFT JOIN pembimbing_lapang pl ON pl.user_id = u.id -- no nik column
    LEFT JOIN `groups` g ON m.group_id = g.id
    LEFT JOIN companies c ON g.company_id = c.id
    WHERE u.role_id != 1
    ORDER BY u.id DESC
    LIMIT :lim OFFSET :off
");
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

$roleLabels = ['mahasiswa' => 'Mahasiswa', 'dosen_pembimbing' => 'Dosen Pembimbing', 'pembimbing_lapang' => 'Pembimbing Lapang'];
$roleBadges = ['mahasiswa' => 'bg-blue-100 text-blue-700', 'dosen_pembimbing' => 'bg-purple-100 text-purple-700', 'pembimbing_lapang' => 'bg-teal-100 text-teal-700'];
$roleColors = ['mahasiswa' => 'bg-blue-600', 'dosen_pembimbing' => 'bg-purple-600', 'pembimbing_lapang' => 'bg-teal-600'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../includes/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">

                <?php if (isset($_GET['created'])): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2"><i class="fas fa-check-circle"></i> Akun berhasil dibuat.</div><?php endif; ?>
                <?php if (isset($_GET['updated'])): ?><div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2"><i class="fas fa-check-circle"></i> Data user berhasil diperbarui.</div><?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2"><i class="fas fa-check-circle"></i> User berhasil dihapus.</div><?php endif; ?>

                <div class="flex items-center justify-between">
                    <div><h2 class="text-2xl font-bold text-gray-900">Manajemen User</h2><p class="text-gray-500 text-sm mt-0.5">Kelola akun Mahasiswa dan Dosen Pembimbing</p></div>
                    <button onclick="openModal()" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-[13px] font-semibold hover:bg-blue-700 transition-colors shadow-sm"><i class="fas fa-user-plus"></i> Buat Akun</button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-5 border-b border-gray-100">
                        <div class="relative flex-1 w-full"><i class="fas fa-search text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-[13px]"></i><input id="searchInput" type="text" placeholder="Cari nama atau email..." oninput="filterTable()" class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-[13px] text-gray-700 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all"></div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button id="filterAll" onclick="setFilter('semua')" class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-blue-600 text-white transition-all">Semua</button>
                            <button id="filterMhs" onclick="setFilter('mahasiswa')" class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all">Mahasiswa</button>
                            <button id="filterDosen" onclick="setFilter('dosen_pembimbing')" class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all">Dosen</button>
                            <button id="filterPL" onclick="setFilter('pembimbing_lapang')" class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all">Pemb. Lapang</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full" id="userTable">
                            <thead><tr class="border-b border-gray-100">
                                <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Nama</th>
                                <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Role</th>
                                <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">ID</th>
                                <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Perusahaan</th>
                                <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Aksi</th>
                            </tr></thead>
                            <tbody id="userTableBody" class="divide-y divide-gray-50">
                                <?php foreach ($users as $u):
                                    $rn = $u['role_name'];
                                    $label = $roleLabels[$rn] ?? $rn;
                                    $badge = $roleBadges[$rn] ?? 'bg-gray-100 text-gray-700';
                                    $color = $roleColors[$rn] ?? 'bg-gray-600';
                                    $nid = $u['nim'] ?? $u['nip'] ?? $u['pl_jabatan'] ?? '-';
                                    $company = $u['nama_perusahaan'] ?? '-';
                                    $initials = strtoupper(substr($u['name'], 0, 1));
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors user-row" data-role="<?= $rn ?>" data-search="<?= strtolower($u['name'] . ' ' . $u['email']) ?>">
                                    <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full <?= $color ?> text-white flex items-center justify-center text-[13px] font-bold shrink-0"><?= $initials ?></div><div><p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($u['name']) ?></p><p class="text-[12px] text-gray-400"><?= htmlspecialchars($u['email']) ?></p></div></div></td>
                                    <td class="px-6 py-4"><span class="px-3 py-1 rounded-lg text-[12px] font-semibold <?= $badge ?>"><?= $label ?></span></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-600 font-medium"><?= $nid ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-600"><?= htmlspecialchars($company) ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <button onclick='openEditModal(<?= json_encode(["id"=>$u["id"],"name"=>$u["name"],"email"=>$u["email"],"role"=>$rn,"nid"=>$nid]) ?>)' class="w-8 h-8 rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 transition-colors flex items-center justify-center" title="Edit"><i class="fas fa-pen text-[12px]"></i></button>
                                            <form method="POST" onsubmit="return confirm('Hapus user ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id_user" value="<?= $u['id'] ?>"><button type="submit" class="w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors flex items-center justify-center" title="Hapus"><i class="fas fa-trash text-[12px]"></i></button></form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
                        <p class="text-[13px] text-gray-500">Menampilkan <?= $offset+1 ?>-<?= min($offset+$perPage, $totalUsers) ?> dari <?= $totalUsers ?> user</p>
                        <div class="flex items-center gap-1">
                            <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 flex items-center justify-center text-[12px]"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>" class="w-8 h-8 rounded-lg <?= $i === $page ? 'bg-blue-600 text-white font-semibold' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' ?> text-[13px] flex items-center justify-center"><?= $i ?></a>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?><a href="?page=<?= $page+1 ?>" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 flex items-center justify-center text-[12px]"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
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
            <div class="flex items-center justify-between mb-5"><h3 class="text-lg font-bold text-gray-900">Buat Akun Baru</h3><button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors"><i class="fas fa-times text-lg"></i></button></div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <div><label class="block text-[13px] font-medium text-gray-700 mb-1.5">Nama Lengkap</label><input type="text" name="nama" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"></div>
                <div><label class="block text-[13px] font-medium text-gray-700 mb-1.5">Email</label><input type="email" name="email" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-[13px] font-medium text-gray-700 mb-1.5">Role</label><select name="role" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 bg-white"><option value="">Pilih role...</option><option value="mahasiswa">Mahasiswa</option><option value="dosen">Dosen Pembimbing</option><option value="pembimbing">Pembimbing Lapang</option></select></div>
                    <div><label class="block text-[13px] font-medium text-gray-700 mb-1.5">NIM/NIP</label><input type="text" name="nomor_induk" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"></div>
                </div>
                <div><label class="block text-[13px] font-medium text-gray-700 mb-1.5">Password</label><input type="password" name="password" required minlength="8" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"></div>
                <div class="flex items-center gap-3 pt-2"><button type="button" onclick="closeModal()" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50">Batal</button><button type="submit" class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-[13px] font-semibold hover:bg-blue-700">Simpan</button></div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div id="modalEditUser" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100"><h3 class="text-lg font-bold text-gray-900">Edit User</h3><button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button></div>
            <form method="POST" class="px-6 py-5 space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_user" id="editUserId">
                <div><label class="block text-[13px] font-medium text-gray-700 mb-1.5">Nama</label><input type="text" name="nama" id="editNama" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 bg-gray-50"></div>
                <div><label class="block text-[13px] font-medium text-gray-700 mb-1.5">Email</label><input type="email" name="email" id="editEmail" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 bg-gray-50"></div>
                <div><label class="block text-[13px] font-medium text-gray-700 mb-1.5">Ganti Password</label><input type="password" name="password" placeholder="Kosongkan jika tidak mengubah" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 bg-gray-50"></div>
                <div class="flex items-center justify-end gap-3 pt-2"><button type="button" onclick="closeEditModal()" class="px-5 py-2.5 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50">Batal</button><button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-[13px] font-semibold hover:bg-blue-700">Simpan</button></div>
            </form>
        </div>
    </div>

    <script>
        let currentFilter = 'semua';
        function openModal() { document.getElementById('modalBuatAkun').classList.remove('hidden'); document.getElementById('modalBuatAkun').classList.add('flex'); }
        function closeModal() { document.getElementById('modalBuatAkun').classList.add('hidden'); document.getElementById('modalBuatAkun').classList.remove('flex'); }
        function setFilter(f) {
            currentFilter = f;
            document.querySelectorAll('.filter-btn').forEach(b => b.className = 'filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all');
            event.target.className = 'filter-btn px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-blue-600 text-white transition-all';
            filterTable();
        }
        function filterTable() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.user-row').forEach(row => {
                const roleMatch = currentFilter === 'semua' || row.dataset.role === currentFilter;
                const searchMatch = row.dataset.search.includes(search);
                row.style.display = (roleMatch && searchMatch) ? '' : 'none';
            });
        }
        function openEditModal(u) {
            document.getElementById('editUserId').value = u.id;
            document.getElementById('editNama').value = u.name;
            document.getElementById('editEmail').value = u.email;
            document.getElementById('modalEditUser').classList.remove('hidden');
            document.getElementById('modalEditUser').classList.add('flex');
        }
        function closeEditModal() { document.getElementById('modalEditUser').classList.add('hidden'); document.getElementById('modalEditUser').classList.remove('flex'); }
        document.getElementById('modalBuatAkun').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
        document.getElementById('modalEditUser').addEventListener('click', function(e) { if (e.target === this) closeEditModal(); });
    </script>
</body>
</html>