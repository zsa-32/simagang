<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("admin");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'admin';
$activePage = 'manajemen_pembimbing';

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        // Buat akun user dulu
        $hashedPw = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
        $stmtUser = $conn->prepare("INSERT INTO users (role_id, name, email, password) VALUES (4, ?, ?, ?)");
        $stmtUser->execute([trim($_POST['nama']), trim($_POST['email']), $hashedPw]);
        $userId = $conn->lastInsertId();

        $stmt = $conn->prepare("INSERT INTO pembimbing_lapang (user_id, company_id, nama, jabatan, no_hp, email) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$userId, $_POST['company_id'] ?: null, trim($_POST['nama']), trim($_POST['jabatan']), trim($_POST['no_hp']), trim($_POST['email'])]);
        $msg = 'Pembimbing lapang berhasil ditambahkan.'; $msgType = 'success';

    } elseif ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE pembimbing_lapang SET nama=?, jabatan=?, no_hp=?, company_id=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([trim($_POST['nama']), trim($_POST['jabatan']), trim($_POST['no_hp']), $_POST['company_id'] ?: null, (int)$_POST['id']]);
        // Update name di users juga
        $stmt2 = $conn->prepare("UPDATE users SET name=?, updated_at=NOW() WHERE id=(SELECT user_id FROM pembimbing_lapang WHERE id=?)");
        $stmt2->execute([trim($_POST['nama']), (int)$_POST['id']]);
        $msg = 'Data pembimbing berhasil diperbarui.'; $msgType = 'success';

    } elseif ($action === 'hapus') {
        // Hapus user juga (cascade)
        $row = $conn->prepare("SELECT user_id FROM pembimbing_lapang WHERE id=?");
        $row->execute([(int)$_POST['id']]);
        $uid = $row->fetchColumn();
        $conn->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        $msg = 'Pembimbing berhasil dihapus.'; $msgType = 'danger';
    }
}

$search = trim($_GET['q'] ?? '');
$params = []; $where = '';
if ($search !== '') {
    $where  = "WHERE pl.nama LIKE ? OR pl.jabatan LIKE ? OR c.nama_perusahaan LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$pembimbings = $conn->prepare("
    SELECT pl.*, c.nama_perusahaan,
           (SELECT COUNT(*) FROM `groups` g WHERE g.pembimbing_lapang_id = pl.id) as total_group
    FROM pembimbing_lapang pl
    LEFT JOIN companies c ON pl.company_id = c.id
    $where
    ORDER BY pl.created_at DESC
");
$pembimbings->execute($params);
$pembimbings = $pembimbings->fetchAll();

$companies = $conn->query("SELECT id, nama_perusahaan FROM companies ORDER BY nama_perusahaan")->fetchAll();
$adminName = $_SESSION['nama'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembimbing Lapang - Magang TIF</title>
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

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Pembimbing Lapang</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Kelola data pembimbing lapangan dari perusahaan mitra</p>
                </div>
                <button onclick="openModal('modalTambah')" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                    <i class="fas fa-plus"></i> Tambah Pembimbing
                </button>
            </div>

            <?php if ($msg): ?>
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                <i class="fas <?= $msgType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama, jabatan, perusahaan..."
                       class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm hover:bg-blue-700 transition-colors"><i class="fas fa-search"></i></button>
                <?php if ($search): ?><a href="manajemen_pembimbing.php" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition-colors">Reset</a><?php endif; ?>
            </form>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Daftar Pembimbing Lapang <span class="text-sm font-normal text-gray-400 ml-1">(<?= count($pembimbings) ?> data)</span></h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">#</th>
                                <th class="px-6 py-3 text-left">Nama / Email</th>
                                <th class="px-6 py-3 text-left">Jabatan</th>
                                <th class="px-6 py-3 text-left">Perusahaan</th>
                                <th class="px-6 py-3 text-left">No. HP</th>
                                <th class="px-6 py-3 text-center">Kelompok</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($pembimbings)): ?>
                            <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-user-tie text-3xl mb-2 block"></i>Belum ada pembimbing lapang</td></tr>
                            <?php else: ?>
                            <?php foreach ($pembimbings as $i => $p): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-gray-400"><?= $i + 1 ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold shrink-0">
                                            <?= strtoupper(substr($p['nama'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['nama']) ?></p>
                                            <p class="text-xs text-gray-400"><?= htmlspecialchars($p['email']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($p['jabatan'] ?: '-') ?></td>
                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($p['nama_perusahaan'] ?: '-') ?></td>
                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($p['no_hp'] ?: '-') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full"><?= (int)$p['total_group'] ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='openEdit(<?= json_encode($p) ?>)' class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors"><i class="fas fa-edit"></i></button>
                                        <button onclick="confirmHapus(<?= $p['id'] ?>, '<?= addslashes($p['nama']) ?>')" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Tambah Pembimbing Lapang</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="px-6 py-5 space-y-4">
            <input type="hidden" name="action" value="tambah">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Nama pembimbing" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required placeholder="email@perusahaan.com" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required placeholder="Min. 8 karakter" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jabatan</label>
                    <input type="text" name="jabatan" placeholder="Senior Engineer" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">No. HP</label>
                    <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Perusahaan</label>
                    <select name="company_id" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Pilih Perusahaan --</option>
                        <?php foreach ($companies as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama_perusahaan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('modalTambah')" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Edit Pembimbing</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="px-6 py-5 space-y-4" id="formEdit">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="nama" id="editNama" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jabatan</label>
                    <input type="text" name="jabatan" id="editJabatan" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">No. HP</label>
                    <input type="text" name="no_hp" id="editNoHp" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Perusahaan</label>
                    <select name="company_id" id="editCompany" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Pilih Perusahaan --</option>
                        <?php foreach ($companies as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama_perusahaan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('modalEdit')" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div id="modalHapus" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-trash text-red-500 text-xl"></i></div>
        <h3 class="font-bold text-gray-800 text-lg mb-2">Hapus Pembimbing?</h3>
        <p class="text-gray-500 text-sm mb-6">Akun dan data "<span id="hapusNama" class="font-semibold text-gray-700"></span>" akan dihapus permanen.</p>
        <form method="POST" class="flex gap-3 justify-center">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id" id="hapusId">
            <button type="button" onclick="closeModal('modalHapus')" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</button>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-500 text-white text-sm font-semibold hover:bg-red-600">Ya, Hapus</button>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openEdit(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('editNama').value = data.nama || '';
    document.getElementById('editJabatan').value = data.jabatan || '';
    document.getElementById('editNoHp').value = data.no_hp || '';
    document.getElementById('editCompany').value = data.company_id || '';
    openModal('modalEdit');
}
function confirmHapus(id, nama) {
    document.getElementById('hapusId').value = id;
    document.getElementById('hapusNama').textContent = nama;
    openModal('modalHapus');
}
window.addEventListener('click', function(e) {
    ['modalTambah','modalEdit','modalHapus'].forEach(id => {
        if (e.target === document.getElementById(id)) closeModal(id);
    });
});
</script>
</body>
</html>
