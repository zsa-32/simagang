<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole('koordinator');
require_once __DIR__ . '/../config/db_connect.php';

$role = 'koordinator';
$activePage = 'dashboard';

// --- QUERIES ---

// 1. Total Peserta Magang
$stmt = $conn->query("SELECT COUNT(*) FROM mahasiswa");
$totalPeserta = (int)$stmt->fetchColumn();

// 2. Total Dosen Pembimbing Aktif
$stmt = $conn->query("SELECT COUNT(*) FROM dosen_pembimbing");
$totalDosen = (int)$stmt->fetchColumn();

// 3. Total Instansi Mitra
$stmt = $conn->query("SELECT COUNT(*) FROM companies");
$totalInstansi = (int)$stmt->fetchColumn();

// 4. Ringkasan Status Magang
$stmt = $conn->query("
    SELECT 
        SUM(CASE WHEN status = 'Aktif' OR status = 'Berjalan' THEN 1 ELSE 0 END) as berjalan,
        SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai,
        SUM(CASE WHEN status = 'Bermasalah' THEN 1 ELSE 0 END) as bermasalah
    FROM mahasiswa
");
$statusData = $stmt->fetch();
$countBerjalan = (int)($statusData['berjalan'] ?? 0);
$countSelesai = (int)($statusData['selesai'] ?? 0);
$countBermasalah = (int)($statusData['bermasalah'] ?? 0);

$totalStatus = $countBerjalan + $countSelesai + $countBermasalah;
$pctBerjalan = $totalStatus > 0 ? round(($countBerjalan / $totalStatus) * 100, 1) : 0;
$pctSelesai = $totalStatus > 0 ? round(($countSelesai / $totalStatus) * 100, 1) : 0;
$pctBermasalah = $totalStatus > 0 ? round(($countBermasalah / $totalStatus) * 100, 1) : 0;

// 5. Log Aktivitas Sistem (Latest 4 logbooks for demonstration)
$stmt = $conn->query("
    SELECT l.id, l.tanggal, l.created_at, m.nama as mahasiswa_nama, c.nama_perusahaan 
    FROM logbooks l
    JOIN mahasiswa m ON l.mahasiswa_id = m.id
    LEFT JOIN `groups` g ON m.group_id = g.id
    LEFT JOIN companies c ON g.company_id = c.id
    ORDER BY l.created_at DESC
    LIMIT 4
");
$recentLogs = $stmt->fetchAll();

/**
 * Format waktu relatif (misal: "2 jam yang lalu")
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return "Baru saja";
    if ($diff < 3600) return floor($diff / 60) . " menit yang lalu";
    if ($diff < 86400) return floor($diff / 3600) . " jam yang lalu";
    if ($diff < 2592000) return floor($diff / 86400) . " hari yang lalu";
    return date('d M Y', $time);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Koordinator | Magang TIF</title>
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
                <!-- Header -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Dashboard Rekap Magang</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Rekapitulasi keseluruhan program magang</p>
                </div>

                <!-- Top Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Total Peserta -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[13px] text-gray-500 font-medium">Total Peserta Magang</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $totalPeserta ?></p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                        </div>
                        <p class="text-[12px] text-gray-400 font-medium mt-4">Seluruh mahasiswa terdaftar</p>
                    </div>

                    <!-- Total Dosen -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[13px] text-gray-500 font-medium">Total Dosen Pembimbing Aktif</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $totalDosen ?></p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                                <i class="fas fa-chalkboard-teacher text-green-600"></i>
                            </div>
                        </div>
                        <p class="text-[12px] text-gray-400 font-medium mt-4">Seluruh dosen pembimbing terdaftar</p>
                    </div>

                    <!-- Total Instansi -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[13px] text-gray-500 font-medium">Total Instansi Mitra</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $totalInstansi ?></p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                                <i class="fas fa-building text-purple-600"></i>
                            </div>
                        </div>
                        <p class="text-[12px] text-gray-400 font-medium mt-4">Seluruh mitra instansi aktif</p>
                    </div>
                </div>

                <!-- Middle Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <!-- Ringkasan Status Magang -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h3 class="text-[15px] font-bold text-gray-900">Ringkasan Status Magang</h3>
                        <p class="text-[12px] text-gray-500 mb-5 mt-1">Status peserta per kategori</p>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-4 rounded-xl bg-blue-50 border border-blue-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <p class="text-[13px] text-gray-600 font-medium">Magang Berjalan</p>
                                        <p class="text-[15px] font-bold text-gray-900"><?= $countBerjalan ?> Peserta</p>
                                    </div>
                                </div>
                                <span class="text-blue-500 font-medium text-[13px]"><?= $pctBerjalan ?>%</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 rounded-xl bg-green-50 border border-green-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div>
                                        <p class="text-[13px] text-gray-600 font-medium">Magang Selesai</p>
                                        <p class="text-[15px] font-bold text-gray-900"><?= $countSelesai ?> Peserta</p>
                                    </div>
                                </div>
                                <span class="text-green-500 font-medium text-[13px]"><?= $pctSelesai ?>%</span>
                            </div>

                            <div class="flex items-center justify-between p-4 rounded-xl bg-red-50 border border-red-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <p class="text-[13px] text-gray-600 font-medium">Bermasalah</p>
                                        <p class="text-[15px] font-bold text-gray-900"><?= $countBermasalah ?> Peserta</p>
                                    </div>
                                </div>
                                <span class="text-red-500 font-medium text-[13px]"><?= $pctBermasalah ?>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Log Aktivitas Sistem -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h3 class="text-[15px] font-bold text-gray-900">Log Aktivitas Sistem</h3>
                        <p class="text-[12px] text-gray-500 mb-5 mt-1">Aktivitas jurnal terbaru dari mahasiswa</p>
                        
                        <?php if(empty($recentLogs)): ?>
                            <p class="text-sm text-gray-400 italic">Belum ada aktivitas terekam.</p>
                        <?php else: ?>
                            <div class="relative pl-3 space-y-6 before:content-[''] before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-[2px] before:bg-gray-100">
                                <?php foreach($recentLogs as $log): ?>
                                <div class="relative flex gap-4">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0 z-10">
                                        <i class="fas fa-book text-blue-500 text-[11px]"></i>
                                    </div>
                                    <div>
                                        <p class="text-[13px] text-gray-600"><span class="font-semibold text-gray-800"><?= htmlspecialchars($log['mahasiswa_nama']) ?></span> mensubmit jurnal di <?= htmlspecialchars($log['nama_perusahaan'] ?? '-') ?></p>
                                        <p class="text-[11px] text-gray-400 mt-0.5"><?= timeAgo($log['created_at']) ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Akses Cepat -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-[15px] font-bold text-gray-900">Akses Cepat</h3>
                    <p class="text-[12px] text-gray-500 mb-5 mt-1">Navigasi ke fitur utama</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <a href="monitoring.php" class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-shadow group">
                            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                                <i class="fas fa-eye text-red-500"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800 text-[14px]">Monitoring Program</h4>
                            <p class="text-[12px] text-gray-500 mt-1">Pantau status dan progress magang</p>
                        </a>

                        <a href="statistik.php" class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-shadow group">
                            <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                                <i class="fas fa-chart-bar text-yellow-500"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800 text-[14px]">Statistik</h4>
                            <p class="text-[12px] text-gray-500 mt-1">Lihat statistik mahasiswa & perusahaan</p>
                        </a>

                        <a href="laporan.php" class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition-shadow group">
                            <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                                <i class="fas fa-file-download text-orange-500"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800 text-[14px]">Laporan Sistem</h4>
                            <p class="text-[12px] text-gray-500 mt-1">Export laporan keseluruhan</p>
                        </a>
                    </div>
                </div>

            </div>
        </main>
        
    </div>
</body>
</html>
