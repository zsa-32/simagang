<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("admin");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'admin';
$activePage = 'manajemen_group';

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO `groups` (name, company_id, pembimbing_lapang_id, dosen_pembimbing_id) VALUES (?,?,?,?)");
        $stmt->execute([
            trim($_POST['name']),
            $_POST['company_id'] ?: null,
            $_POST['pembimbing_lapang_id'] ?: null,
            $_POST['dosen_pembimbing_id'] ?: null
        ]);
        $msg = 'Kelompok magang berhasil ditambahkan.'; $msgType = 'success';

    } elseif ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE `groups` SET name=?, company_id=?, pembimbing_lapang_id=?, dosen_pembimbing_id=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([
            trim($_POST['name']),
            $_POST['company_id'] ?: null,
            $_POST['pembimbing_lapang_id'] ?: null,
            $_POST['dosen_pembimbing_id'] ?: null,
            (int)$_POST['id']
        ]);
        $msg = 'Kelompok berhasil diperbarui.'; $msgType = 'success';

    } elseif ($action === 'hapus') {
        $stmt = $conn->prepare("DELETE FROM `groups` WHERE id = ?");
        $stmt->execute([(int)$_POST['id']]);
        $msg = 'Kelompok berhasil dihapus.'; $msgType = 'danger';
    }
}

// Data for dropdowns
$companies      = $conn->query("SELECT id, nama_perusahaan FROM companies ORDER BY nama_perusahaan")->fetchAll();
$dosens         = $conn->query("SELECT id, nama, nip FROM dosen_pembimbing ORDER BY nama")->fetchAll();
$pembimbings    = $conn->query("SELECT pl.id, pl.nama, pl.jabatan, c.nama_perusahaan FROM pembimbing_lapang pl LEFT JOIN companies c ON pl.company_id = c.id ORDER BY pl.nama")->fetchAll();

// Fetch all groups
$kelompokList = $conn->query("
    SELECT g.*, c.nama_perusahaan, dp.nama as nama_dosen, pl.nama as nama_pembimbing,
           (SELECT COUNT(*) FROM mahasiswa m WHERE m.group_id = g.id) as total_mhs
    FROM `groups` g
    LEFT JOIN companies c ON g.company_id = c.id
    LEFT JOIN dosen_pembimbing dp ON g.dosen_pembimbing_id = dp.id
    LEFT JOIN pembimbing_lapang pl ON g.pembimbing_lapang_id = pl.id
    ORDER BY g.created_at DESC
")->fetchAll();

$adminName = $_SESSION['nama'] ?? 'Admin';

// Fetch mahasiswa per group for member modal
$mahasiswaPerGroup = [];
$mhsRows = $conn->query("
    SELECT m.group_id, m.nama, m.no_ktm, u.email
    FROM mahasiswa m
    LEFT JOIN users u ON m.user_id = u.id
    WHERE m.group_id IS NOT NULL
    ORDER BY m.nama
")->fetchAll();
foreach ($mhsRows as $mRow) {
    $mahasiswaPerGroup[$mRow['group_id']][] = $mRow;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelompok Magang - Magang TIF</title>
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
                    <h2 class="text-2xl font-bold text-gray-900">Kelompok Magang</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Atur pengelompokan mahasiswa, perusahaan, dan pembimbing</p>
                </div>
                <button onclick="openModal('modalTambah')" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                    <i class="fas fa-plus"></i> Tambah Kelompok
                </button>
            </div>

            <?php if ($msg): ?>
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                <i class="fas <?= $msgType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Group Cards -->
            <?php if (empty($kelompokList)): ?>
            <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm border border-gray-100">
                <i class="fas fa-layer-group text-4xl mb-3 block"></i>
                <p class="font-medium">Belum ada kelompok magang</p>
                <p class="text-sm mt-1">Klik "Tambah Kelompok" untuk membuat kelompok pertama</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($kelompokList as $g): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas fa-layer-group text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm leading-tight"><?= htmlspecialchars($g['name']) ?></h4>
                                <span class="text-xs text-blue-600 font-medium bg-blue-50 px-2 py-0.5 rounded-full mt-1 inline-block"><?= $g['total_mhs'] ?> Mahasiswa</span>
                            </div>
                        </div>
                    <div class="flex gap-1.5">
                            <button onclick='openEdit(<?= json_encode($g) ?>)' class="text-blue-600 hover:bg-blue-50 p-1.5 rounded-lg transition-colors text-xs" title="Edit"><i class="fas fa-edit"></i></button>
                            <button onclick="openAnggota(<?= $g['id'] ?>, '<?= addslashes($g['name']) ?>')" class="text-green-600 hover:bg-green-50 p-1.5 rounded-lg transition-colors text-xs" title="Lihat Anggota"><i class="fas fa-users"></i></button>
                            <button onclick="confirmHapus(<?= $g['id'] ?>, '<?= addslashes($g['name']) ?>')" class="text-red-500 hover:bg-red-50 p-1.5 rounded-lg transition-colors text-xs" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>
                    </div>
                    <div class="space-y-2 text-xs text-gray-600">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-building w-4 text-gray-400"></i>
                            <span><?= htmlspecialchars($g['nama_perusahaan'] ?? 'Belum ada perusahaan') ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-chalkboard-teacher w-4 text-gray-400"></i>
                            <span><?= htmlspecialchars($g['nama_dosen'] ?? 'Belum ada dosen') ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user-tie w-4 text-gray-400"></i>
                            <span><?= htmlspecialchars($g['nama_pembimbing'] ?? 'Belum ada pembimbing lapang') ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Tambah Kelompok</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="px-6 py-5 space-y-4">
            <input type="hidden" name="action" value="tambah">
            <?php include '_form_group.php'; ?>
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
            <h3 class="font-bold text-gray-800 text-lg">Edit Kelompok</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="px-6 py-5 space-y-4" id="formEdit">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <?php include '_form_group.php'; ?>
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
        <h3 class="font-bold text-gray-800 text-lg mb-2">Hapus Kelompok?</h3>
        <p class="text-gray-500 text-sm mb-6">Kelompok "<span id="hapusNama" class="font-semibold text-gray-700"></span>" akan dihapus. Mahasiswa di kelompok ini tidak akan memiliki kelompok.</p>
        <form method="POST" class="flex gap-3 justify-center">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id" id="hapusId">
            <button type="button" onclick="closeModal('modalHapus')" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</button>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-500 text-white text-sm font-semibold hover:bg-red-600">Ya, Hapus</button>
        </form>
    </div>
</div>

<!-- Modal Anggota Kelompok -->
<div id="modalAnggota" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="font-bold text-gray-800 text-lg" id="anggotaTitle">Anggota Kelompok</h3>
                <p class="text-xs text-gray-400 mt-0.5" id="anggotaSubtitle"></p>
            </div>
            <button onclick="closeModal('modalAnggota')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <div class="px-6 py-5 max-h-80 overflow-y-auto" id="anggotaList">
            <p class="text-sm text-gray-400 text-center py-6">Tidak ada mahasiswa di kelompok ini.</p>
        </div>
        <div class="px-6 pb-5">
            <button onclick="closeModal('modalAnggota')" class="w-full py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Tutup</button>
        </div>
    </div>
</div>

<?php
// Encode mahasiswa per group data to JS
$mahasiswaJson = json_encode($mahasiswaPerGroup);
?>

<script>
const mahasiswaData = <?= $mahasiswaJson ?>;
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openEdit(data) {
    document.getElementById('editId').value = data.id;
    document.querySelector('#formEdit [name="name"]').value = data.name || '';
    document.querySelector('#formEdit [name="company_id"]').value = data.company_id || '';
    document.querySelector('#formEdit [name="dosen_pembimbing_id"]').value = data.dosen_pembimbing_id || '';
    document.querySelector('#formEdit [name="pembimbing_lapang_id"]').value = data.pembimbing_lapang_id || '';
    openModal('modalEdit');
}
function confirmHapus(id, nama) {
    document.getElementById('hapusId').value = id;
    document.getElementById('hapusNama').textContent = nama;
    openModal('modalHapus');
}
function openAnggota(groupId, groupName) {
    const list = mahasiswaData[groupId] || [];
    document.getElementById('anggotaTitle').textContent = groupName;
    document.getElementById('anggotaSubtitle').textContent = list.length + ' mahasiswa terdaftar';
    const container = document.getElementById('anggotaList');
    if (list.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400 text-center py-6"><i class="fas fa-user-slash block text-2xl mb-2"></i>Belum ada mahasiswa di kelompok ini.</p>';
    } else {
        container.innerHTML = list.map((m, i) => `
            <div class="flex items-center gap-3 py-2.5 ${i < list.length-1 ? 'border-b border-gray-50' : ''}">
                <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shrink-0">${m.nama.charAt(0).toUpperCase()}</div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">${m.nama}</p>
                    <p class="text-xs text-gray-400">${m.no_ktm ? 'NIM: ' + m.no_ktm : m.email || '-'}</p>
                </div>
                <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">#${i+1}</span>
            </div>
        `).join('');
    }
    openModal('modalAnggota');
}
window.addEventListener('click', function(e) {
    ['modalTambah','modalEdit','modalHapus','modalAnggota'].forEach(id => {
        if (e.target === document.getElementById(id)) closeModal(id);
    });
});
</script>
</body>
</html>
