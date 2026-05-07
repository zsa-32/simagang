<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("pembimbing_lapang");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'pembimbing';
$activePage = 'profil';

$userId   = $_SESSION['id_user'];
$userName = $_SESSION['nama'] ?? 'Pembimbing';

$stmt = $conn->prepare("
    SELECT pl.*, u.email, c.nama_perusahaan
    FROM pembimbing_lapang pl
    JOIN users u ON pl.user_id = u.id
    LEFT JOIN companies c ON pl.company_id = c.id
    WHERE pl.user_id = :uid
");
$stmt->execute(['uid' => $userId]);
$profil = $stmt->fetch();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $conn->prepare("UPDATE pembimbing_lapang SET no_hp=:hp, jabatan=:jbt, updated_at=NOW() WHERE user_id=:uid")
         ->execute(['hp' => $_POST['no_hp'], 'jbt' => $_POST['jabatan'], 'uid' => $userId]);
    header("Location: profil.php?saved=1"); exit;
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
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }</style>
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

                <!-- Profile Header -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#0f766e] to-[#14b8a6] h-28 relative"></div>
                    <div class="px-6 pb-6 relative">
                        <div class="absolute -top-12 left-6">
                            <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-gray-200 flex items-center justify-center overflow-hidden">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($profil['nama'] ?? $userName) ?>&background=0f766e&color=fff&size=128" alt="Avatar" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="pt-14 flex items-end justify-between">
                            <div>
                                <h2 class="text-[22px] font-bold text-gray-900"><?= htmlspecialchars($profil['nama'] ?? $userName) ?></h2>
                                <p class="text-[14px] text-teal-600 font-medium mt-0.5"><?= htmlspecialchars($profil['jabatan'] ?? 'Pembimbing Lapang') ?></p>
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
                        <i class="fas fa-user-tie text-teal-600 text-[16px]"></i>
                        <h3 class="text-[16px] font-bold text-gray-800">Informasi Pribadi</h3>
                    </div>

                    <div id="viewMode" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Email</p>
                            <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['email'] ?? '-') ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Nomor HP</p>
                            <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['no_hp'] ?? '-') ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Jabatan</p>
                            <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['jabatan'] ?? '-') ?></p>
                        </div>
                        <div class="bg-teal-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-teal-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-building text-white text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-teal-400 mb-1">Perusahaan</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($profil['nama_perusahaan'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>

                    <form id="editMode" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4" method="POST">
                        <input type="hidden" name="action" value="update">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Nomor HP</label>
                            <input type="tel" name="no_hp" value="<?= htmlspecialchars($profil['no_hp'] ?? '') ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] outline-none focus:ring-2 focus:ring-teal-100 focus:border-teal-400">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Jabatan</label>
                            <input type="text" name="jabatan" value="<?= htmlspecialchars($profil['jabatan'] ?? '') ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] outline-none focus:ring-2 focus:ring-teal-100 focus:border-teal-400">
                        </div>
                    </form>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pb-2">
                    <button id="cancelBtn" onclick="cancelEdit()" class="hidden px-5 py-2.5 border border-gray-200 rounded-xl text-[13px] font-semibold text-gray-600 hover:bg-gray-50">Batal</button>
                    <button id="saveBtn" onclick="document.getElementById('editMode').submit()" class="hidden flex items-center gap-2 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-[13px] font-semibold">
                        <i class="fas fa-save text-[12px]"></i> Simpan
                    </button>
                    <a href="dashboard.php" id="selesaiBtn" class="px-6 py-2.5 bg-[#3b66f5] hover:bg-[#2d53d4] text-white rounded-xl text-[13px] font-semibold">Selesai</a>
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
