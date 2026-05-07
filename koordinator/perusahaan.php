<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("koordinator");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'koordinator';
$activePage = 'perusahaan';

$msg = ''; $msgType = '';

// Handle tambah perusahaan (rekomendasi oleh koordinator)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah') {
    $stmt = $conn->prepare("INSERT INTO companies (nama_perusahaan, alamat_perusahaan, email_business, contact_person, no_hp, bidang_usaha, status_permodalan) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([
        trim($_POST['nama_perusahaan']), trim($_POST['alamat_perusahaan']),
        trim($_POST['email_business']), trim($_POST['contact_person']),
        trim($_POST['no_hp']), trim($_POST['bidang_usaha']),
        trim($_POST['status_permodalan'] ?? '-'),
    ]);
    $msg = 'Perusahaan berhasil ditambahkan.'; $msgType = 'success';
}

// Search & Fetch
$search = trim($_GET['q'] ?? '');
$where  = $search ? "WHERE nama_perusahaan LIKE ? OR bidang_usaha LIKE ?" : '';
$params = $search ? ["%$search%", "%$search%"] : [];
$stmt = $conn->prepare("SELECT c.*, (SELECT COUNT(*) FROM `groups` g WHERE g.company_id = c.id) as total_group FROM companies c $where ORDER BY c.created_at DESC");
$stmt->execute($params);
$companies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perusahaan Mitra - Magang TIF</title>
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

            <!-- Page Heading -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Perusahaan Mitra</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Daftar perusahaan tempat magang mahasiswa</p>
                </div>
                <button onclick="openModal('modalTambah')" class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                    <i class="fas fa-plus"></i> Tambahkan Perusahaan
                </button>
            </div>

            <?php if ($msg): ?>
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                <i class="fas fa-circle-check"></i> <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Search -->
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Cari nama perusahaan atau bidang usaha..."
                       class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2.5 rounded-xl text-sm hover:bg-blue-600 transition-colors">
                    <i class="fas fa-search"></i>
                </button>
                <?php if ($search): ?>
                <a href="perusahaan.php" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl text-sm hover:bg-gray-200">Reset</a>
                <?php endif; ?>
            </form>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-blue-500"><?= count($companies) ?></p>
                    <p class="text-[12px] text-gray-500 mt-1">Total Perusahaan</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-blue-600"><?= array_sum(array_column($companies, 'total_group')) ?></p>
                    <p class="text-[12px] text-gray-500 mt-1">Total Kelompok</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-green-600"><?= count(array_filter($companies, fn($c) => (int)$c['total_group'] > 0)) ?></p>
                    <p class="text-[12px] text-gray-500 mt-1">Aktif Menerima</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-gray-400"><?= count(array_filter($companies, fn($c) => (int)$c['total_group'] === 0)) ?></p>
                    <p class="text-[12px] text-gray-500 mt-1">Belum Ada Mhs</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($companies)): ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada data perusahaan</td></tr>
                            <?php else: ?>
                            <?php foreach ($companies as $i => $c): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-gray-400"><?= $i + 1 ?></td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($c['nama_perusahaan']) ?></p>
                                    <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($c['email_business'] ?? '-') ?></p>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['bidang_usaha'] ?: '-') ?></td>
                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['contact_person'] ?: '-') ?></td>
                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($c['no_hp'] ?: '-') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="<?= (int)$c['total_group'] > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' ?> text-xs font-semibold px-2.5 py-1 rounded-full">
                                        <?= (int)$c['total_group'] ?> Kelompok
                                    </span>
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

<!-- Modal Tambah Perusahaan -->
<div id="modalTambah" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Rekomendasikan Perusahaan Baru</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="px-6 py-5 space-y-4">
            <input type="hidden" name="action" value="tambah">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Nama Perusahaan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_perusahaan" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Bidang Usaha</label>
                    <input type="text" name="bidang_usaha" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Contact Person</label>
                    <input type="text" name="contact_person" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">No. HP</label>
                    <input type="text" name="no_hp" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Email Bisnis <span class="text-red-500">*</span></label>
                    <input type="email" name="email_business" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Alamat</label>
                    <input type="text" name="alamat_perusahaan" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('modalTambah')" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-500 text-white text-sm font-semibold hover:bg-blue-600">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
window.addEventListener('click', function(e) {
    ['modalTambah'].forEach(id => {
        const m = document.getElementById(id);
        if (e.target === m) closeModal(id);
    });
});
</script>
</body>
</html>
