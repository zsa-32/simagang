<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole('koordinator');
require_once __DIR__ . '/../config/db_connect.php';

$role = 'koordinator';
$activePage = 'statistik';

// 1. Top Stats
$totalMahasiswa = (int)$conn->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
$totalLulus = (int)$conn->query("SELECT COUNT(*) FROM mahasiswa WHERE status = 'Selesai'")->fetchColumn();
$totalPerusahaan = (int)$conn->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$totalDosen = (int)$conn->query("SELECT COUNT(*) FROM dosen_pembimbing")->fetchColumn();

$tingkatKelulusan = $totalMahasiswa > 0 ? round(($totalLulus / $totalMahasiswa) * 100, 1) : 0;

// 2. Distribusi Status
$stmtStatus = $conn->query("
    SELECT 
        SUM(CASE WHEN status = 'Aktif' OR status = 'Berjalan' THEN 1 ELSE 0 END) as berjalan,
        SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai,
        SUM(CASE WHEN status = 'Bermasalah' THEN 1 ELSE 0 END) as bermasalah
    FROM mahasiswa
");
$statusData = $stmtStatus->fetch();
$countBerjalan = (int)($statusData['berjalan'] ?? 0);
$countSelesai = (int)($statusData['selesai'] ?? 0);
$countBermasalah = (int)($statusData['bermasalah'] ?? 0);
$totalStatus = $countBerjalan + $countSelesai + $countBermasalah;

$pctBerjalan = $totalStatus > 0 ? round(($countBerjalan / $totalStatus) * 100, 1) : 0;
$pctSelesai = $totalStatus > 0 ? round(($countSelesai / $totalStatus) * 100, 1) : 0;
$pctBermasalah = $totalStatus > 0 ? round(($countBermasalah / $totalStatus) * 100, 1) : 0;

// 3. Top 5 Perusahaan
$stmtTop5 = $conn->query("
    SELECT c.nama_perusahaan, c.bidang_usaha, COUNT(m.id) as total 
    FROM companies c 
    JOIN `groups` g ON c.id = g.company_id 
    JOIN mahasiswa m ON g.id = m.group_id 
    GROUP BY c.id 
    ORDER BY total DESC 
    LIMIT 5
");
$topCompanies = $stmtTop5->fetchAll();

// 4. Distribusi Mahasiswa per Perusahaan
$stmtDistCompany = $conn->query("
    SELECT 
        c.nama_perusahaan, 
        c.bidang_usaha, 
        c.alamat_perusahaan as lokasi, 
        SUM(CASE WHEN m.status = 'Aktif' OR m.status = 'Berjalan' THEN 1 ELSE 0 END) as peserta_aktif, 
        COUNT(m.id) as total_peserta 
    FROM companies c 
    LEFT JOIN `groups` g ON c.id = g.company_id 
    LEFT JOIN mahasiswa m ON g.id = m.group_id 
    GROUP BY c.id
    ORDER BY total_peserta DESC
");
$distCompanies = $stmtDistCompany->fetchAll();
$maxPesertaPerusahaan = 0;
foreach($distCompanies as $dc) {
    if ($dc['total_peserta'] > $maxPesertaPerusahaan) {
        $maxPesertaPerusahaan = $dc['total_peserta'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik | Magang TIF</title>
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
                    <h2 class="text-2xl font-bold text-gray-900">Statistik Mahasiswa & Perusahaan</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Data statistik dan analisis program magang</p>
                </div>

                <!-- Top Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- Total Mahasiswa -->
                    <div class="bg-blue-50 rounded-2xl border border-blue-100 shadow-sm p-6 flex flex-col justify-between">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-500 flex items-center justify-center text-white shrink-0">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <p class="text-[12px] text-blue-600 font-medium">Total Mahasiswa</p>
                                <p class="text-xl font-bold text-blue-900 mt-1"><?= $totalMahasiswa ?> Peserta</p>
                            </div>
                        </div>
                        <p class="text-[12px] text-blue-500 font-medium mt-6">Semester ini</p>
                    </div>

                    <!-- Tingkat Kelulusan -->
                    <div class="bg-green-50 rounded-2xl border border-green-100 shadow-sm p-6 flex flex-col justify-between">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-green-500 flex items-center justify-center text-white shrink-0">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <p class="text-[12px] text-green-600 font-medium">Tingkat Kelulusan</p>
                                <p class="text-xl font-bold text-green-900 mt-1"><?= $tingkatKelulusan ?>%</p>
                            </div>
                        </div>
                        <p class="text-[12px] text-green-500 font-medium mt-6"><?= $totalLulus ?> dari <?= $totalMahasiswa ?> lulus</p>
                    </div>

                    <!-- Total Perusahaan -->
                    <div class="bg-purple-50 rounded-2xl border border-purple-100 shadow-sm p-6 flex flex-col justify-between">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-purple-500 flex items-center justify-center text-white shrink-0">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <p class="text-[12px] text-purple-600 font-medium">Total Perusahaan</p>
                                <p class="text-xl font-bold text-purple-900 mt-1"><?= $totalPerusahaan ?> Mitra</p>
                            </div>
                        </div>
                        <p class="text-[12px] text-purple-500 font-medium mt-6">Partner aktif</p>
                    </div>

                    <!-- Dosen Pembimbing -->
                    <div class="bg-orange-50 rounded-2xl border border-orange-100 shadow-sm p-6 flex flex-col justify-between">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white shrink-0">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div>
                                <p class="text-[12px] text-orange-600 font-medium">Dosen Pembimbing</p>
                                <p class="text-xl font-bold text-orange-900 mt-1"><?= $totalDosen ?> Dosen</p>
                            </div>
                        </div>
                        <p class="text-[12px] text-orange-500 font-medium mt-6">Aktif membimbing</p>
                    </div>
                </div>

                <!-- Middle Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <!-- Distribusi Status -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h3 class="text-[15px] font-bold text-gray-900">Distribusi Mahasiswa per Status</h3>
                        <p class="text-[12px] text-gray-500 mb-6 mt-1">Breakdown status magang</p>
                        
                        <div class="space-y-6">
                            <!-- Berjalan -->
                            <div>
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-[13px] font-medium text-gray-700">Berjalan</span>
                                    <span class="text-[13px] font-bold text-gray-900"><?= $countBerjalan ?> (<?= $pctBerjalan ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?= $pctBerjalan ?>%"></div>
                                </div>
                            </div>
                            
                            <!-- Selesai -->
                            <div>
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-[13px] font-medium text-gray-700">Selesai</span>
                                    <span class="text-[13px] font-bold text-gray-900"><?= $countSelesai ?> (<?= $pctSelesai ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="bg-green-500 h-2.5 rounded-full" style="width: <?= $pctSelesai ?>%"></div>
                                </div>
                            </div>

                            <!-- Bermasalah -->
                            <div>
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-[13px] font-medium text-gray-700">Bermasalah</span>
                                    <span class="text-[13px] font-bold text-gray-900"><?= $countBermasalah ?> (<?= $pctBermasalah ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="bg-red-500 h-2.5 rounded-full" style="width: <?= $pctBermasalah ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 Perusahaan -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h3 class="text-[15px] font-bold text-gray-900">Top 5 Perusahaan Mitra</h3>
                        <p class="text-[12px] text-gray-500 mb-5 mt-1">Berdasarkan jumlah peserta</p>
                        
                        <?php if(empty($topCompanies)): ?>
                            <p class="text-sm text-gray-400 italic">Belum ada data perusahaan.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php $rank = 1; foreach($topCompanies as $tc): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-[13px]"><?= $rank ?></div>
                                        <div>
                                            <p class="text-[13px] font-semibold text-gray-900"><?= htmlspecialchars($tc['nama_perusahaan']) ?></p>
                                            <p class="text-[11px] text-gray-500"><?= htmlspecialchars($tc['bidang_usaha'] ?? 'Umum') ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[14px] font-bold text-gray-900"><?= $tc['total'] ?></p>
                                        <p class="text-[11px] text-gray-500">peserta</p>
                                    </div>
                                </div>
                                <?php $rank++; endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bottom Table Section -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-[15px] font-bold text-gray-900">Distribusi Mahasiswa per Perusahaan</h3>
                        <p class="text-[12px] text-gray-500 mt-1">Total dan peserta aktif per instansi mitra</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100">
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Perusahaan</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Tipe</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Lokasi</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Peserta Aktif</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600">Total Peserta</th>
                                    <th class="py-3 px-6 text-[12px] font-semibold text-gray-600 min-w-[150px]">Persentase (relatif)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if(empty($distCompanies)): ?>
                                <tr><td colspan="6" class="py-6 text-center text-gray-400 text-sm">Tidak ada data.</td></tr>
                                <?php else: ?>
                                    <?php foreach($distCompanies as $dc): 
                                        // Calculate percentage relative to the company with most students for the progress bar visual
                                        $barPct = $maxPesertaPerusahaan > 0 ? round(($dc['total_peserta'] / $maxPesertaPerusahaan) * 100) : 0;
                                        // Absolute percentage over all students
                                        $absPct = $totalMahasiswa > 0 ? round(($dc['total_peserta'] / $totalMahasiswa) * 100) : 0;
                                        
                                        // Simple city extraction (first word of address or default)
                                        $lokasiArr = explode(',', $dc['lokasi'] ?? 'Indonesia');
                                        $kota = trim(end($lokasiArr));
                                        if (strlen($kota) > 20) $kota = substr($kota, 0, 20) . '...';
                                    ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3 px-6 text-[13px] font-semibold text-gray-900"><?= htmlspecialchars($dc['nama_perusahaan']) ?></td>
                                        <td class="py-3 px-6"><span class="inline-flex px-2 py-1 bg-purple-50 text-purple-600 text-[11px] font-medium rounded border border-purple-100"><?= htmlspecialchars($dc['bidang_usaha'] ?? 'Umum') ?></span></td>
                                        <td class="py-3 px-6 text-[13px] text-gray-600"><?= htmlspecialchars($kota) ?></td>
                                        <td class="py-3 px-6 text-[13px] text-blue-600 font-medium"><?= $dc['peserta_aktif'] ?></td>
                                        <td class="py-3 px-6 text-[13px] text-gray-600"><?= $dc['total_peserta'] ?></td>
                                        <td class="py-3 px-6">
                                            <div class="flex items-center gap-2" title="<?= $absPct ?>% dari total mahasiswa">
                                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: <?= $barPct ?>%"></div>
                                                </div>
                                                <span class="text-[12px] text-gray-600"><?= $absPct ?>%</span>
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
        
    </div>
</body>
</html>
