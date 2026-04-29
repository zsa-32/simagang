<?php
    require_once __DIR__ . "/../config/role_guard.php";
    checkRole("mahasiswa");
    require_once __DIR__ . '/../config/db_connect.php';
    $role = 'mahasiswa';
    $activePage = 'profil';

    $userId = $_SESSION['id_user'];
    $userName = $_SESSION['nama'] ?? 'Mahasiswa';

    // Get mahasiswa data with related info
    $stmt = $conn->prepare("
        SELECT m.*, u.email,
               g.name as group_name,
               c.nama_perusahaan, c.alamat_perusahaan, c.bidang_usaha,
               dp.nama as dosen_nama,
               pl.nama as pl_nama, pl.jabatan as pl_jabatan,
               g.created_at as magang_mulai
        FROM mahasiswa m
        JOIN users u ON m.user_id = u.id
        LEFT JOIN `groups` g ON m.group_id = g.id
        LEFT JOIN companies c ON g.company_id = c.id
        LEFT JOIN dosen_pembimbing dp ON g.dosen_pembimbing_id = dp.id
        LEFT JOIN pembimbing_lapang pl ON g.pembimbing_lapang_id = pl.id
        WHERE m.user_id = :uid
    ");
    $stmt->execute(['uid' => $userId]);
    $profil = $stmt->fetch();

    // Settings for kampus name
    $settings = $conn->query("SELECT nama_kampus FROM settings LIMIT 1")->fetch();
    $kampus = $settings['nama_kampus'] ?? 'Politeknik Negeri Jember';

    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
        $noHp = $_POST['no_hp'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $stmt = $conn->prepare("UPDATE mahasiswa SET no_hp = :hp, alamat_asal = :alm, updated_at = NOW() WHERE user_id = :uid");
        $stmt->execute(['hp' => $noHp, 'alm' => $alamat, 'uid' => $userId]);
        header("Location: profil.php?saved=1");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .info-card { transition: background-color 0.15s ease; }
        .info-card:hover { background-color: #eef2ff; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <?php include '../includes/header.php'; ?>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[860px] mx-auto space-y-5">

                <?php if (isset($_GET['saved'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Profil berhasil diperbarui.
                </div>
                <?php endif; ?>

                <!-- Profile Header Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#1e40af] to-[#3b66f5] h-28 relative"></div>
                    <div class="px-6 pb-6 relative">
                        <div class="absolute -top-12 left-6">
                            <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-gray-200 overflow-hidden flex items-center justify-center">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($profil['nama'] ?? $userName) ?>&background=3b66f5&color=fff&size=128"
                                     alt="Foto Profil" class="w-full h-full object-cover" id="avatarImg">
                            </div>
                        </div>
                        <div class="pt-14 flex items-end justify-between">
                            <div>
                                <h2 class="text-[22px] font-bold text-gray-900"><?= htmlspecialchars($profil['nama'] ?? $userName) ?></h2>
                                <p class="text-[14px] text-blue-600 font-medium mt-0.5">NIM: <?= htmlspecialchars($profil['no_ktm'] ?? '-') ?></p>
                            </div>
                            <button id="editBtn" onclick="toggleEdit()" class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-xl text-[13px] font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-pen text-[12px]"></i> Edit Profil
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pribadi -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <i class="fas fa-user text-[#3b66f5] text-[16px]"></i>
                        <h3 class="text-[16px] font-bold text-gray-800">Informasi Pribadi</h3>
                    </div>

                    <!-- View Mode -->
                    <div id="viewMode" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Kampus</p>
                            <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($kampus) ?></p>
                        </div>
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Jenis Kelamin</p>
                            <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['jenis_kelamin'] ?? '-') ?></p>
                        </div>
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Email Aktif</p>
                            <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['email'] ?? '-') ?></p>
                        </div>
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Nomor Telepon / WhatsApp</p>
                            <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['no_hp'] ?? '-') ?></p>
                        </div>
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Tempat, Tanggal Lahir</p>
                            <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars(($profil['tempat_lahir'] ?? '-') . ', ' . ($profil['tanggal_lahir'] ? date('d M Y', strtotime($profil['tanggal_lahir'])) : '-')) ?></p>
                        </div>
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Alamat</p>
                            <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['alamat_asal'] ?? '-') ?></p>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <form id="editMode" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4" method="POST">
                        <input type="hidden" name="action" value="update">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Nomor HP</label>
                            <input type="tel" name="no_hp" value="<?= htmlspecialchars($profil['no_hp'] ?? '') ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Alamat</label>
                            <input type="text" name="alamat" value="<?= htmlspecialchars($profil['alamat_asal'] ?? '') ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                    </form>
                </div>

                <!-- Informasi Penempatan Magang -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <i class="fas fa-building text-[#3b66f5] text-[16px]"></i>
                        <h3 class="text-[16px] font-bold text-gray-800">Informasi Penempatan Magang</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-building text-white text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-blue-400 mb-1">Nama Perusahaan/Instansi</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['nama_perusahaan'] ?? '-') ?></p>
                            </div>
                        </div>

                        <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-users text-white text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-blue-400 mb-1">Grup Magang</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['group_name'] ?? '-') ?></p>
                            </div>
                        </div>

                        <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-chalkboard-teacher text-white text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-blue-400 mb-1">Dosen Pembimbing</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['dosen_nama'] ?? '-') ?></p>
                            </div>
                        </div>

                        <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-user-tie text-white text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-blue-400 mb-1">Pembimbing Lapang</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['pl_nama'] ?? '-') ?></p>
                                <?php if ($profil['pl_jabatan']): ?>
                                <p class="text-[12px] text-gray-400 mt-0.5"><?= htmlspecialchars($profil['pl_jabatan']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pb-2">
                    <button id="cancelBtn" onclick="cancelEdit()" class="hidden px-5 py-2.5 border border-gray-200 rounded-xl text-[13px] font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                    <button id="saveBtn" onclick="document.getElementById('editMode').submit()" class="hidden flex items-center gap-2 px-5 py-2.5 bg-[#3b66f5] hover:bg-[#2d53d4] text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm shadow-blue-200">
                        <i class="fas fa-save text-[12px]"></i> Simpan Perubahan
                    </button>
                    <a href="dashboard.php" id="selesaiBtn" class="px-6 py-2.5 bg-[#3b66f5] hover:bg-[#2d53d4] text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm shadow-blue-200">Selesai</a>
                </div>

            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
        function toggleEdit() {
            document.getElementById('viewMode').classList.add('hidden');
            document.getElementById('editMode').classList.remove('hidden');
            document.getElementById('editBtn').classList.add('hidden');
            document.getElementById('selesaiBtn').classList.add('hidden');
            document.getElementById('cancelBtn').classList.remove('hidden');
            document.getElementById('saveBtn').classList.remove('hidden');
        }
        function cancelEdit() {
            document.getElementById('viewMode').classList.remove('hidden');
            document.getElementById('editMode').classList.add('hidden');
            document.getElementById('editBtn').classList.remove('hidden');
            document.getElementById('selesaiBtn').classList.remove('hidden');
            document.getElementById('cancelBtn').classList.add('hidden');
            document.getElementById('saveBtn').classList.add('hidden');
        }
    </script>
</body>
</html>
