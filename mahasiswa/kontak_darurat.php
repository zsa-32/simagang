<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("mahasiswa");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'mahasiswa';
$activePage = 'kontak_darurat';

$userId = $_SESSION['id_user'];
$mhs = $conn->prepare("SELECT id FROM mahasiswa WHERE user_id = ?");
$mhs->execute([$userId]);
$mhs = $mhs->fetch();
$mhsId = $mhs['id'] ?? null;

$msg = ''; $msgType = '';

if ($mhsId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO kontak_darurat (mahasiswa_id, nama, hubungan, no_telepon, alamat) VALUES (?,?,?,?,?)");
        $stmt->execute([$mhsId, trim($_POST['nama']), trim($_POST['hubungan']), trim($_POST['no_telepon']), trim($_POST['alamat'])]);
        $msg = 'Kontak darurat berhasil ditambahkan.'; $msgType = 'success';

    } elseif ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE kontak_darurat SET nama=?, hubungan=?, no_telepon=?, alamat=?, updated_at=NOW() WHERE id=? AND mahasiswa_id=?");
        $stmt->execute([trim($_POST['nama']), trim($_POST['hubungan']), trim($_POST['no_telepon']), trim($_POST['alamat']), (int)$_POST['id'], $mhsId]);
        $msg = 'Kontak darurat berhasil diperbarui.'; $msgType = 'success';

    } elseif ($action === 'hapus') {
        $stmt = $conn->prepare("DELETE FROM kontak_darurat WHERE id=? AND mahasiswa_id=?");
        $stmt->execute([(int)$_POST['id'], $mhsId]);
        $msg = 'Kontak darurat berhasil dihapus.'; $msgType = 'danger';
    }
}

$kontaks = [];
if ($mhsId) {
    $stmt = $conn->prepare("SELECT * FROM kontak_darurat WHERE mahasiswa_id = ? ORDER BY created_at");
    $stmt->execute([$mhsId]);
    $kontaks = $stmt->fetchAll();
}

$userName = $_SESSION['nama'] ?? 'Mahasiswa';
$hubunganOptions = ['Ayah', 'Ibu', 'Kakak', 'Adik', 'Suami', 'Istri', 'Paman', 'Bibi', 'Kakek', 'Nenek', 'Lainnya'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Darurat - Magang TIF</title>
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
        <div class="max-w-[800px] mx-auto space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Kontak Darurat</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Data kontak yang dihubungi jika terjadi keadaan darurat</p>
                </div>
                <?php if ($mhsId && count($kontaks) < 3): ?>
                <button onclick="openModal('modalTambah')" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                    <i class="fas fa-plus"></i> Tambah Kontak
                </button>
                <?php endif; ?>
            </div>

            <?php if ($msg): ?>
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                <i class="fas <?= $msgType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <?php if (!$mhsId): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center text-amber-700">
                <i class="fas fa-triangle-exclamation text-2xl mb-2 block"></i>
                <p class="font-semibold">Data mahasiswa belum terdaftar</p>
            </div>
            <?php elseif (empty($kontaks)): ?>
            <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow-sm border border-gray-100">
                <i class="fas fa-phone-alt text-5xl mb-4 block text-gray-200"></i>
                <p class="font-semibold text-gray-600 text-lg">Belum ada kontak darurat</p>
                <p class="text-sm mt-2">Tambahkan minimal 1 kontak yang bisa dihubungi saat darurat.</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($kontaks as $k): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                <i class="fas fa-phone-alt text-red-500"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800"><?= htmlspecialchars($k['nama']) ?></h4>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full"><?= htmlspecialchars($k['hubungan']) ?></span>
                            </div>
                        </div>
                        <div class="flex gap-1.5">
                            <button onclick='openEdit(<?= json_encode($k) ?>)' class="text-blue-600 hover:bg-blue-50 p-1.5 rounded-lg transition-colors text-sm"><i class="fas fa-edit"></i></button>
                            <button onclick="confirmHapus(<?= $k['id'] ?>, '<?= addslashes($k['nama']) ?>')" class="text-red-500 hover:bg-red-50 p-1.5 rounded-lg transition-colors text-sm"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="mt-4 space-y-1.5 text-sm">
                        <p class="flex items-center gap-2 text-gray-600"><i class="fas fa-phone w-4 text-gray-400"></i><?= htmlspecialchars($k['no_telepon']) ?></p>
                        <?php if ($k['alamat']): ?>
                        <p class="flex items-start gap-2 text-gray-600"><i class="fas fa-map-marker-alt w-4 text-gray-400 mt-0.5"></i><?= htmlspecialchars($k['alamat']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($kontaks) >= 3): ?>
            <p class="text-xs text-center text-gray-400">Maksimal 3 kontak darurat</p>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Tambah Kontak Darurat</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="px-6 py-5 space-y-4">
            <input type="hidden" name="action" value="tambah">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required placeholder="Nama kontak" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Hubungan <span class="text-red-500">*</span></label>
                <select name="hubungan" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">-- Pilih Hubungan --</option>
                    <?php foreach ($hubunganOptions as $h): ?>
                    <option value="<?= $h ?>"><?= $h ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">No. Telepon <span class="text-red-500">*</span></label>
                <input type="text" name="no_telepon" required placeholder="08xxxxxxxxxx" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alamat</label>
                <textarea name="alamat" rows="2" placeholder="Alamat lengkap" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
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
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg">Edit Kontak Darurat</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="px-6 py-5 space-y-4" id="formEdit">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama" id="editNama" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Hubungan</label>
                <select name="hubungan" id="editHubungan" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <?php foreach ($hubunganOptions as $h): ?>
                    <option value="<?= $h ?>"><?= $h ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">No. Telepon</label>
                <input type="text" name="no_telepon" id="editTelp" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alamat</label>
                <textarea name="alamat" id="editAlamat" rows="2" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
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
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-trash text-red-500"></i></div>
        <h3 class="font-bold text-gray-800 mb-2">Hapus kontak "<span id="hapusNama"></span>"?</h3>
        <form method="POST" class="flex gap-3 justify-center mt-4">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id" id="hapusId">
            <button type="button" onclick="closeModal('modalHapus')" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600">Batal</button>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-500 text-white text-sm font-semibold">Hapus</button>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openEdit(d) {
    document.getElementById('editId').value = d.id;
    document.getElementById('editNama').value = d.nama || '';
    document.getElementById('editHubungan').value = d.hubungan || '';
    document.getElementById('editTelp').value = d.no_telepon || '';
    document.getElementById('editAlamat').value = d.alamat || '';
    openModal('modalEdit');
}
function confirmHapus(id, nama) {
    document.getElementById('hapusId').value = id;
    document.getElementById('hapusNama').textContent = nama;
    openModal('modalHapus');
}
</script>
</body>
</html>
