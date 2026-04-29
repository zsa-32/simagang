<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("admin");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'admin';
$activePage = 'monitoring_dosen';

// Get all dosen with stats
$stmt = $conn->query("
    SELECT dp.id, dp.nama, dp.nip, dp.user_id,
           (SELECT COUNT(*) FROM mahasiswa m JOIN `groups` g ON m.group_id = g.id WHERE g.dosen_pembimbing_id = dp.id) as total_mhs,
           (SELECT COUNT(*) FROM penilaian_results pr JOIN mahasiswa m2 ON pr.mahasiswa_id = m2.id JOIN `groups` g2 ON m2.group_id = g2.id WHERE g2.dosen_pembimbing_id = dp.id AND pr.nilai_akhir IS NOT NULL) as dinilai,
           (SELECT COUNT(*) FROM feedback_logbooks fl WHERE fl.penilai_user_id = dp.user_id) as catatan
    FROM dosen_pembimbing dp ORDER BY dp.nama
");
$dosenList = $stmt->fetchAll();

$totalDosen = count($dosenList);
$selesai = 0; $perluTindak = 0;
$chartLabels = []; $chartDinilai = []; $chartBelum = [];
foreach ($dosenList as &$d) {
    $d['belum'] = $d['total_mhs'] - $d['dinilai'];
    $pct = $d['total_mhs'] > 0 ? round($d['dinilai'] / $d['total_mhs'] * 100) : 0;
    $d['pct'] = $pct;
    if ($d['total_mhs'] > 0 && $d['dinilai'] >= $d['total_mhs']) { $selesai++; $d['status'] = 'Selesai'; }
    elseif ($d['dinilai'] > 0) { $d['status'] = 'Proses'; }
    else { $perluTindak++; $d['status'] = 'Tertunda'; }
    $chartLabels[] = explode(',', $d['nama'])[0];
    $chartDinilai[] = (int)$d['dinilai'];
    $chartBelum[] = max(0, (int)$d['belum']);
}
unset($d);
$statusStyle = [
    'Proses'   => ['bg' => 'bg-blue-50 text-blue-600',   'icon' => 'fa-clock text-blue-500'],
    'Selesai'  => ['bg' => 'bg-green-50 text-green-600', 'icon' => 'fa-circle-check text-green-500'],
    'Tertunda' => ['bg' => 'bg-amber-50 text-amber-600', 'icon' => 'fa-triangle-exclamation text-amber-500'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Dosen - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../includes/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">
                <div><h2 class="text-2xl font-bold text-gray-900">Monitoring Dosen Pembimbing</h2><p class="text-gray-500 text-sm mt-0.5">Pantau plotting dosen dan status penilaian bimbingan</p></div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4"><div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><i class="fas fa-chalkboard-teacher text-xl"></i></div><div><p class="text-[13px] text-gray-500">Total Dosen</p><p class="text-2xl font-bold text-gray-900"><?= $totalDosen ?></p></div></div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4"><div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shrink-0"><i class="fas fa-circle-check text-xl"></i></div><div><p class="text-[13px] text-gray-500">Selesai Menilai</p><p class="text-2xl font-bold text-gray-900"><?= $selesai ?></p></div></div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4"><div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-500 flex items-center justify-center shrink-0"><i class="fas fa-triangle-exclamation text-xl"></i></div><div><p class="text-[13px] text-gray-500">Perlu Tindak Lanjut</p><p class="text-2xl font-bold text-gray-900"><?= $perluTindak ?></p></div></div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"><h3 class="text-[17px] font-bold text-gray-800 mb-5">Status Penilaian per Dosen</h3><div class="relative w-full h-[240px]"><canvas id="stackedBar"></canvas></div></div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100"><h3 class="font-bold text-gray-800 text-[16px]">Daftar Plotting Dosen</h3><div class="relative"><i class="fas fa-search text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-[12px]"></i><input type="text" id="searchDosen" placeholder="Cari dosen..." oninput="filterDosen()" class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 w-52"></div></div>
                    <div class="overflow-x-auto">
                        <table class="w-full"><thead><tr class="border-b border-gray-100">
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Dosen</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Mahasiswa</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Penilaian</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Catatan</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($dosenList)): ?>
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada data dosen.</td></tr>
                            <?php else: foreach ($dosenList as $d):
                                $barColor = $d['pct'] >= 100 ? 'bg-green-500' : ($d['pct'] >= 50 ? 'bg-blue-500' : 'bg-blue-300');
                                $s = $statusStyle[$d['status']];
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors dosen-row" data-search="<?= strtolower($d['nama'] . ' ' . $d['nip']) ?>">
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-purple-600 text-white flex items-center justify-center text-[13px] font-bold shrink-0"><?= strtoupper(substr($d['nama'], 0, 1)) ?></div><div><p class="font-semibold text-gray-800 text-[13px]"><?= htmlspecialchars($d['nama']) ?></p><p class="text-[11px] text-gray-400"><?= $d['nip'] ?></p></div></div></td>
                                <td class="px-4 py-4 text-[13px] text-gray-700 font-semibold"><?= $d['total_mhs'] ?></td>
                                <td class="px-4 py-4"><div class="flex items-center gap-2.5"><div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden"><div class="h-full <?= $barColor ?> rounded-full" style="width:<?= $d['pct'] ?>%"></div></div><span class="text-[12px] text-gray-500 font-medium"><?= $d['dinilai'] ?>/<?= $d['total_mhs'] ?></span></div></td>
                                <td class="px-4 py-4 text-[13px] text-gray-700"><?= $d['catatan'] ?></td>
                                <td class="px-4 py-4"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[12px] font-semibold <?= $s['bg'] ?>"><i class="fas <?= $s['icon'] ?> text-[10px]"></i> <?= $d['status'] ?></span></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody></table>
                    </div>
                </div>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Chart(document.getElementById('stackedBar').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [
                        { label: 'Selesai Menilai', data: <?= json_encode($chartDinilai) ?>, backgroundColor: '#10b981', stack: 'p' },
                        { label: 'Belum Menilai', data: <?= json_encode($chartBelum) ?>, backgroundColor: '#f59e0b', borderRadius: 4, stack: 'p' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', align: 'end', labels: { boxWidth: 12, boxHeight: 12, font: { family: "'Inter'", size: 11 }, color: '#6b7280' } } }, scales: { x: { stacked: true, ticks: { color: '#9ca3af', font: { size: 11 } }, grid: { display: false } }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 2, color: '#9ca3af', font: { size: 11 } }, grid: { color: '#e5e7eb', borderDash: [5,5] } } } }
            });
        });
        function filterDosen() { const q = document.getElementById('searchDosen').value.toLowerCase(); document.querySelectorAll('.dosen-row').forEach(r => { r.style.display = r.dataset.search.includes(q) ? '' : 'none'; }); }
    </script>
</body>
</html>
