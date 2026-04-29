<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("pembimbing_lapang");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'pembimbing';
$activePage = 'dashboard';

$userId = $_SESSION['id_user'];
$plData = $conn->prepare("SELECT pl.id, pl.nama, pl.company_id, c.nama_perusahaan FROM pembimbing_lapang pl LEFT JOIN companies c ON pl.company_id = c.id WHERE pl.user_id = :uid");
$plData->execute(['uid' => $userId]);
$pl = $plData->fetch();
$plId = $pl['id'] ?? 0;
$companyName = $pl['nama_perusahaan'] ?? '-';

// Count mahasiswa in groups supervised by this PL
$totalMhs = (int)$conn->prepare("SELECT COUNT(*) FROM mahasiswa m JOIN `groups` g ON m.group_id = g.id WHERE g.pembimbing_lapang_id = :pid")->execute(['pid' => $plId]) ? $conn->prepare("SELECT COUNT(*) FROM mahasiswa m JOIN `groups` g ON m.group_id = g.id WHERE g.pembimbing_lapang_id = :pid") : null;
$stmt = $conn->prepare("SELECT COUNT(*) FROM mahasiswa m JOIN `groups` g ON m.group_id = g.id WHERE g.pembimbing_lapang_id = :pid");
$stmt->execute(['pid' => $plId]);
$totalMhs = (int)$stmt->fetchColumn();

// Attendance today
$today = date('Y-m-d');
$stmt2 = $conn->prepare("
    SELECT COUNT(*) FROM attendances a
    JOIN mahasiswa m ON a.mahasiswa_id = m.id
    JOIN `groups` g ON m.group_id = g.id
    WHERE g.pembimbing_lapang_id = :pid AND a.date = :dt AND a.status = 'Hadir'
");
$stmt2->execute(['pid' => $plId, 'dt' => $today]);
$hadirToday = (int)$stmt2->fetchColumn();

// Pending logbooks
$stmt3 = $conn->prepare("
    SELECT COUNT(*) FROM logbooks l
    JOIN mahasiswa m ON l.mahasiswa_id = m.id
    JOIN `groups` g ON m.group_id = g.id
    WHERE g.pembimbing_lapang_id = :pid AND l.status = 'pending'
");
$stmt3->execute(['pid' => $plId]);
$pendingJurnal = (int)$stmt3->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pembimbing Lapang | Magang TIF</title>
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
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Selamat datang, <?= htmlspecialchars($pl['nama'] ?? $_SESSION['nama'] ?? 'Pembimbing') ?> — <?= htmlspecialchars($companyName) ?></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <a href="monitoring_mahasiswa.php" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform"><i class="fas fa-user-graduate text-white text-xl"></i></div>
                            <div><p class="text-[13px] text-gray-500 font-medium">Total Mahasiswa</p><p class="text-3xl font-bold text-gray-900 mt-0.5"><?= $totalMhs ?></p></div>
                        </div>
                        <p class="text-[13px] text-blue-600 font-medium">Lihat detail monitoring <i class="fas fa-arrow-right text-[11px] ml-1"></i></p>
                    </a>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-green-500 flex items-center justify-center shrink-0"><i class="fas fa-check-circle text-white text-xl"></i></div>
                            <div><p class="text-[13px] text-gray-500 font-medium">Presensi Hari Ini</p><p class="text-3xl font-bold text-gray-900 mt-0.5"><?= $hadirToday ?>/<?= $totalMhs ?></p></div>
                        </div>
                        <p class="text-[13px] text-green-600 font-medium"><?= $hadirToday == $totalMhs ? 'Semua hadir' : 'Kehadiran hari ini' ?></p>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-500 flex items-center justify-center shrink-0"><i class="fas fa-clipboard-list text-white text-xl"></i></div>
                            <div><p class="text-[13px] text-gray-500 font-medium">Jurnal Menunggu</p><p class="text-3xl font-bold text-gray-900 mt-0.5"><?= $pendingJurnal ?></p></div>
                        </div>
                        <p class="text-[13px] text-amber-600 font-medium"><?= $pendingJurnal > 0 ? 'Perlu direview' : 'Semua sudah direview' ?></p>
                    </div>
                </div>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>
</html>
