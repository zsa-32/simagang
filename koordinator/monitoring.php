<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole('koordinator');
require_once __DIR__ . '/../config/db_connect.php';

$role = 'koordinator';
$activePage = 'monitoring';

// 1. Ringkasan Status Magang
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

// 2. Daftar Mahasiswa
// We join with logbooks to calculate progress (assuming 60 logs is 100%)
$stmt = $conn->query("
    SELECT 
        m.id, m.nama, m.no_ktm, m.no_hp, m.status,
        u.email,
        c.nama_perusahaan,
        dp.nama as dosen_pembimbing,
        (SELECT COUNT(*) FROM logbooks l WHERE l.mahasiswa_id = m.id) as total_jurnal
    FROM mahasiswa m
    LEFT JOIN users u ON m.user_id = u.id
    LEFT JOIN `groups` g ON m.group_id = g.id
    LEFT JOIN companies c ON g.company_id = c.id
    LEFT JOIN dosen_pembimbing dp ON g.dosen_pembimbing_id = dp.id
    ORDER BY m.nama ASC
");
$mahasiswaList = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Program | Magang TIF</title>
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
                    <h2 class="text-2xl font-bold text-gray-900">Monitoring Program Magang Lebih Mudah</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Pantau status dan progress peserta magang secara real-time</p>
                </div>

                <!-- Top Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Berjalan -->
                    <div class="bg-blue-50 rounded-2xl border border-blue-100 shadow-sm p-6 flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[13px] text-blue-600 font-medium">Magang Berjalan</p>
                                <p class="text-2xl font-bold text-blue-900 mt-1"><?= $countBerjalan ?> Peserta</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-blue-500 flex items-center justify-center text-white shadow-sm">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <p class="text-[12px] text-blue-500 font-medium mt-6"><?= $pctBerjalan ?>% dari total</p>
                    </div>

                    <!-- Selesai -->
                    <div class="bg-green-50 rounded-2xl border border-green-100 shadow-sm p-6 flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[13px] text-green-600 font-medium">Selesai</p>
                                <p class="text-2xl font-bold text-green-900 mt-1"><?= $countSelesai ?> Peserta</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-green-500 flex items-center justify-center text-white shadow-sm">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <p class="text-[12px] text-green-500 font-medium mt-6"><?= $pctSelesai ?>% dari total</p>
                    </div>

                    <!-- Bermasalah -->
                    <div class="bg-red-50 rounded-2xl border border-red-100 shadow-sm p-6 flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[13px] text-red-600 font-medium">Bermasalah</p>
                                <p class="text-2xl font-bold text-red-900 mt-1"><?= $countBermasalah ?> Peserta</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center text-white shadow-sm">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <p class="text-[12px] text-red-500 font-medium mt-6">Perlu perhatian khusus</p>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-[16px] font-bold text-gray-900">Daftar Monitoring Peserta</h3>
                            <p class="text-[12px] text-gray-500 mt-1">Monitor seluruh peserta magang</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[13px]"></i>
                                <input type="text" id="searchInput" placeholder="Cari mahasiswa..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 w-full md:w-64 transition-shadow">
                            </div>
                            <button class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-50 flex items-center gap-2 transition-colors">
                                <i class="fas fa-file-export"></i> Export
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="dataTable">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100">
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Mahasiswa</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Kontak</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Instansi</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Pembimbing</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Status</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Progress</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if(empty($mahasiswaList)): ?>
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-gray-500 text-sm">Tidak ada data mahasiswa.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach($mahasiswaList as $m): 
                                        // Progress Calculation (Estimating 60 journals = 100%)
                                        $progress = min(100, round(($m['total_jurnal'] / 60) * 100));
                                        
                                        // Status Styling
                                        $statusStr = strtolower($m['status']);
                                        if ($statusStr === 'aktif' || $statusStr === 'berjalan') {
                                            $statusClass = 'bg-blue-50 text-blue-600 border-blue-100';
                                            $statusLabel = 'Berjalan';
                                            $progColor = 'bg-blue-500';
                                        } elseif ($statusStr === 'selesai') {
                                            $statusClass = 'bg-green-50 text-green-600 border-green-100';
                                            $statusLabel = 'Selesai';
                                            $progress = 100; // Force 100% if done
                                            $progColor = 'bg-green-500';
                                        } else {
                                            $statusClass = 'bg-red-50 text-red-600 border-red-100';
                                            $statusLabel = 'Bermasalah';
                                            $progColor = 'bg-red-500';
                                        }
                                    ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3 px-6 search-target">
                                            <p class="text-[13px] font-semibold text-gray-900"><?= htmlspecialchars($m['nama']) ?></p>
                                            <p class="text-[12px] text-gray-500"><?= htmlspecialchars($m['no_ktm'] ?? '-') ?></p>
                                        </td>
                                        <td class="py-3 px-6">
                                            <p class="text-[12px] text-gray-600 flex items-center gap-1.5"><i class="far fa-envelope text-gray-400"></i> <?= htmlspecialchars($m['email'] ?? '-') ?></p>
                                            <p class="text-[12px] text-gray-600 flex items-center gap-1.5 mt-0.5"><i class="fas fa-phone-alt text-gray-400"></i> <?= htmlspecialchars($m['no_hp'] ?? '-') ?></p>
                                        </td>
                                        <td class="py-3 px-6 text-[13px] text-gray-600"><?= htmlspecialchars($m['nama_perusahaan'] ?? 'Belum ada instansi') ?></td>
                                        <td class="py-3 px-6 text-[13px] text-gray-600"><?= htmlspecialchars($m['dosen_pembimbing'] ?? 'Belum ada dosen') ?></td>
                                        <td class="py-3 px-6">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-medium border <?= $statusClass ?>"><?= $statusLabel ?></span>
                                        </td>
                                        <td class="py-3 px-6">
                                            <div class="flex items-center gap-2">
                                                <div class="w-full bg-gray-200 rounded-full h-1.5 max-w-[80px]">
                                                    <div class="<?= $progColor ?> h-1.5 rounded-full" style="width: <?= $progress ?>%"></div>
                                                </div>
                                                <span class="text-[12px] text-gray-600 font-medium"><?= $progress ?>%</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-6 text-center">
                                            <button class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-500 transition-colors flex items-center justify-center mx-auto border border-gray-100">
                                                <i class="fas fa-eye text-[13px]"></i>
                                            </button>
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
        
    </div>
    
    <script>
        // Simple client-side search
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#dataTable tbody tr');
            
            rows.forEach(row => {
                let nameCell = row.querySelector('.search-target');
                if (nameCell) {
                    let text = nameCell.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                }
            });
        });
    </script>
</body>
</html>
