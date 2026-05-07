<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("admin");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'admin';
$activePage = 'manajemen_perusahaan';

// Handle POST actions
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO companies (nama_perusahaan, alamat_perusahaan, email_business, contact_person, no_hp, bidang_usaha, status_permodalan, latitude, longitude, radius) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['nama_perusahaan']), trim($_POST['alamat_perusahaan']),
            trim($_POST['email_business']), trim($_POST['contact_person']),
            trim($_POST['no_hp']), trim($_POST['bidang_usaha']),
            trim($_POST['status_permodalan']),
            $_POST['latitude'] ?: null, $_POST['longitude'] ?: null,
            $_POST['radius'] ?: 200
        ]);
        $msg = 'Perusahaan berhasil ditambahkan.'; $msgType = 'success';

    } elseif ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE companies SET nama_perusahaan=?, alamat_perusahaan=?, email_business=?, contact_person=?, no_hp=?, bidang_usaha=?, status_permodalan=?, latitude=?, longitude=?, radius=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([
            trim($_POST['nama_perusahaan']), trim($_POST['alamat_perusahaan']),
            trim($_POST['email_business']), trim($_POST['contact_person']),
            trim($_POST['no_hp']), trim($_POST['bidang_usaha']),
            trim($_POST['status_permodalan']),
            $_POST['latitude'] ?: null, $_POST['longitude'] ?: null,
            $_POST['radius'] ?: 200, (int)$_POST['id']
        ]);
        $msg = 'Data perusahaan berhasil diperbarui.'; $msgType = 'success';

    } elseif ($action === 'hapus') {
        $stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
        $stmt->execute([(int)$_POST['id']]);
        $msg = 'Perusahaan berhasil dihapus.'; $msgType = 'danger';
    }
}

// Search & Fetch
$search = trim($_GET['q'] ?? '');
$params = [];
$where  = '';
if ($search !== '') {
    $where  = "WHERE nama_perusahaan LIKE ? OR bidang_usaha LIKE ? OR email_business LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$stmt = $conn->prepare("SELECT c.*, (SELECT COUNT(*) FROM `groups` g WHERE g.company_id = c.id) as total_group FROM companies c $where ORDER BY c.created_at DESC");
$stmt->execute($params);
$companies = $stmt->fetchAll();

$adminName = $_SESSION['nama'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perusahaan Mitra - Magang TIF</title>
    <meta name="description" content="Manajemen data perusahaan mitra magang TIF.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

<?php include '../includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col h-screen overflow-hidden">
    <?php include '../includes/headeradmin.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
        <div class="max-w-[1200px] mx-auto space-y-6">

            <!-- Page Heading -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Perusahaan Mitra</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Kelola data perusahaan tempat magang mahasiswa</p>
                </div>
                <button onclick="openModal('modalTambah')" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                    <i class="fas fa-plus"></i> Tambah Perusahaan
                </button>
            </div>

            <?php if ($msg): ?>
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                <i class="fas <?= $msgType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Cari nama perusahaan, bidang usaha..."
                       class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search"></i>
                </button>
                <?php if ($search): ?>
                <a href="manajemen_perusahaan.php" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition-colors">Reset</a>
                <?php endif; ?>
            </form>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Daftar Perusahaan <span class="text-sm font-normal text-gray-400 ml-1">(<?= count($companies) ?> data)</span></h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">#</th>
                                <th class="px-6 py-3 text-left">Nama Perusahaan</th>
                                <th class="px-6 py-3 text-left">Bidang Usaha</th>
                                <th class="px-6 py-3 text-left">Contact Person</th>
                                <th class="px-6 py-3 text-left">No. HP</th>
                                <th class="px-6 py-3 text-center">Kelompok</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($companies)): ?>
                            <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-building text-3xl mb-2 block"></i>Belum ada data perusahaan</td></tr>
                            <?php else: ?>
                            <?php foreach ($companies as $i => $c): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-gray-400"><?= $i + 1 ?></td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($c['nama_perusahaan']) ?></p>
                                    <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($c['email_business']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['bidang_usaha'] ?: '-') ?></td>
                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['contact_person'] ?: '-') ?></td>
                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['no_hp'] ?: '-') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full"><?= (int)$c['total_group'] ?> Kelompok</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='openEdit(<?= json_encode($c) ?>)' class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button onclick="confirmHapus(<?= $c['id'] ?>, '<?= addslashes($c['nama_perusahaan']) ?>')" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors" title="Hapus"><i class="fas fa-trash"></i></button>
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
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Tambah Perusahaan</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="px-6 py-5 space-y-4">
            <input type="hidden" name="action" value="tambah">
            <?php include '_form_perusahaan.php'; ?>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('modalTambah')" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Edit Perusahaan</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="px-6 py-5 space-y-4" id="formEdit">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <?php include '_form_perusahaan.php'; ?>
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
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trash text-red-500 text-xl"></i>
        </div>
        <h3 class="font-bold text-gray-800 text-lg mb-2">Hapus Perusahaan?</h3>
        <p class="text-gray-500 text-sm mb-6">Perusahaan "<span id="hapusNama" class="font-semibold text-gray-700"></span>" akan dihapus. Data kelompok yang terhubung akan terpengaruh.</p>
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
    document.querySelector('#formEdit [name="nama_perusahaan"]').value = data.nama_perusahaan || '';
    document.querySelector('#formEdit [name="alamat_perusahaan"]').value = data.alamat_perusahaan || '';
    document.querySelector('#formEdit [name="email_business"]').value = data.email_business || '';
    document.querySelector('#formEdit [name="contact_person"]').value = data.contact_person || '';
    document.querySelector('#formEdit [name="no_hp"]').value = data.no_hp || '';
    document.querySelector('#formEdit [name="bidang_usaha"]').value = data.bidang_usaha || '';
    document.querySelector('#formEdit [name="status_permodalan"]').value = data.status_permodalan || '';
    document.querySelector('#formEdit [name="latitude"]').value = data.latitude || '';
    document.querySelector('#formEdit [name="longitude"]').value = data.longitude || '';
    document.querySelector('#formEdit [name="radius"]').value = data.radius || 200;
    openModal('modalEdit');
}
function confirmHapus(id, nama) {
    document.getElementById('hapusId').value = id;
    document.getElementById('hapusNama').textContent = nama;
    openModal('modalHapus');
}
window.addEventListener('click', function(e) {
    ['modalTambah','modalEdit','modalHapus'].forEach(id => {
        const modal = document.getElementById(id);
        if (e.target === modal) closeModal(id);
    });
});
</script>
</body>
</html>
