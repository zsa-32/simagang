<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'admin') {
    header('Location: ../index.php'); exit();
}

$role = 'admin';
$activePage = 'monitoring_dosen';

// Daftar dosen + stats bimbingan
$stmt = $conn->query("
    SELECT
        u.id_user, u.nama,
        p.nip,
        -- Jumlah mahasiswa yang dibimbing
        (SELECT COUNT(*) FROM Profile WHERE id_dosen_pembimbing = u.id_user) AS total_mhs,
        -- Jumlah yang sudah dinilai (ada nilai_akhir)
        (SELECT COUNT(*) FROM Final_evaluation fe
         JOIN Profile pm ON fe.id_user = pm.id_user
         WHERE pm.id_dosen_pembimbing = u.id_user AND fe.nilai_akhir IS NOT NULL) AS sudah_dinilai,
        -- Jumlah catatan bimbingan (jurnal yg ada catatan_dosen)
        (SELECT COUNT(*) FROM Daily_journal dj
         JOIN Profile pm ON dj.id_user = pm.id_user
         WHERE pm.id_dosen_pembimbing = u.id_user AND dj.catatan_dosen IS NOT NULL) AS jml_catatan,
        -- Jurnal terakhir dikomentari
        (SELECT MAX(dj.tanggal) FROM Daily_journal dj
         JOIN Profile pm ON dj.id_user = pm.id_user
         WHERE pm.id_dosen_pembimbing = u.id_user AND dj.catatan_dosen IS NOT NULL) AS last_aktif
    FROM Users u
    JOIN Profile p ON u.id_user = p.id_user
    JOIN Users_role ur ON u.id_user = ur.id_user
    JOIN Roles r ON ur.id_role = r.id_role
    WHERE LOWER(r.nama_role) = 'dosen pembimbing'
    ORDER BY u.nama ASC
");
$dosenList = $stmt->fetchAll();

// Hitung stat cards
$totalDosen    = count($dosenList);
$selesaiMenilai = count(array_filter($dosenList, fn($d) =>
    $d['total_mhs'] > 0 && $d['sudah_dinilai'] >= $d['total_mhs']
));
$perluTindak   = count(array_filter($dosenList, fn($d) =>
    $d['total_mhs'] > 0 && $d['sudah_dinilai'] < $d['total_mhs']
));

// Data untuk stacked bar chart
$chartLabels  = json_encode(array_map(fn($d) => explode(',', $d['nama'])[0], $dosenList));
$chartDinilai = json_encode(array_map(fn($d) => (int)$d['sudah_dinilai'], $dosenList));
$chartBelum   = json_encode(array_map(fn($d) => max(0, (int)$d['total_mhs'] - (int)$d['sudah_dinilai']), $dosenList));

$avatarColors = ['bg-blue-600','bg-purple-500','bg-indigo-500','bg-teal-600','bg-rose-500','bg-orange-500'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Dosen - Magang TIF</title>
    <meta name="description" content="Pantau plotting dosen dan status penilaian bimbingan mahasiswa magang.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Header -->
        <?php include '../includes/header.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">

                <!-- Heading -->
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Monitoring Dosen Pembimbing</h2>
                        <p class="text-gray-500 text-sm mt-0.5">Pantau plotting dosen dan status penilaian bimbingan</p>
                    </div>
                    <button class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 bg-white rounded-xl text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm shrink-0">
                        <i class="fas fa-download text-gray-500 text-[12px]"></i> Export Laporan
                    </button>
                </div>

                <!-- Stat Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Total Dosen -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-chalkboard-teacher text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[13px] text-gray-500">Total Dosen</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalDosen ?></p>
                        </div>
                    </div>
                    <!-- Selesai Menilai -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-circle-check text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[13px] text-gray-500">Selesai Menilai</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $selesaiMenilai ?></p>
                        </div>
                    </div>
                    <!-- Perlu Tindak Lanjut -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-500 flex items-center justify-center shrink-0">
                            <i class="fas fa-triangle-exclamation text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[13px] text-gray-500">Perlu Tindak Lanjut</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $perluTindak ?></p>
                        </div>
                    </div>
                </div>

                <!-- Stacked Bar Chart -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-[17px] font-bold text-gray-800 mb-5">Status Penilaian per Dosen</h3>
                    <div class="relative w-full h-[240px]">
                        <canvas id="stackedBar"></canvas>
                    </div>
                </div>

                <!-- Daftar Plotting Dosen Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <!-- Table Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800 text-[16px]">Daftar Plotting Dosen</h3>
                        <div class="relative">
                            <i class="fas fa-search text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-[12px]"></i>
                            <input type="text" id="searchDosen" placeholder="Cari dosen..."
                                   oninput="filterDosen()"
                                   class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-[13px] text-gray-700 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all w-52">
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Dosen</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Mahasiswa</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Penilaian</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Catatan Bimbingan</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Status</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Aktivitas Terakhir</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3.5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if (empty($dosenList)): ?>
                                    <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-chalkboard-teacher text-3xl mb-2 block"></i>Belum ada dosen terdaftar.</td></tr>
                                <?php else: ?>
                                <?php foreach ($dosenList as $i => $d):
                                    $totalMhs   = (int)$d['total_mhs'];
                                    $dinilai    = (int)$d['sudah_dinilai'];
                                    $pct        = $totalMhs > 0 ? round($dinilai / $totalMhs * 100) : 0;
                                    $barColor   = $pct >= 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-blue-500' : 'bg-blue-300');
                                    $avatarColor = $avatarColors[$i % count($avatarColors)];

                                    if ($totalMhs === 0)             { $status = 'Belum';    $s = ['bg'=>'bg-gray-100 text-gray-500',   'icon'=>'fa-minus-circle text-gray-400']; }
                                    elseif ($dinilai >= $totalMhs)  { $status = 'Selesai';  $s = ['bg'=>'bg-green-50 text-green-600', 'icon'=>'fa-circle-check text-green-500']; }
                                    elseif ($dinilai > 0)           { $status = 'Proses';   $s = ['bg'=>'bg-blue-50 text-blue-600',   'icon'=>'fa-clock text-blue-500']; }
                                    else                            { $status = 'Tertunda'; $s = ['bg'=>'bg-amber-50 text-amber-600', 'icon'=>'fa-triangle-exclamation text-amber-500']; }

                                    $lastAktif = $d['last_aktif']
                                        ? date('d M Y', strtotime($d['last_aktif']))
                                        : '-';
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors dosen-row" data-search="<?= strtolower($d['nama'] . ' ' . ($d['nip'] ?? '')) ?>">
                                    <!-- Dosen -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full <?= $avatarColor ?> text-white flex items-center justify-center text-[13px] font-bold shrink-0">
                                                <?= strtoupper(substr($d['nama'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800 text-[13px]"><?= htmlspecialchars($d['nama']) ?></p>
                                                <p class="text-[11px] text-gray-400"><?= htmlspecialchars($d['nip'] ?? '-') ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Mahasiswa -->
                                    <td class="px-4 py-4 text-[13px] text-gray-700 font-semibold"><?= $totalMhs ?></td>
                                    <!-- Penilaian -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full <?= $barColor ?> rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                                            </div>
                                            <span class="text-[12px] text-gray-500 font-medium whitespace-nowrap"><?= $dinilai ?>/<?= $totalMhs ?></span>
                                        </div>
                                    </td>
                                    <!-- Catatan Bimbingan -->
                                    <td class="px-4 py-4 text-[13px] text-gray-700"><?= (int)$d['jml_catatan'] ?></td>
                                    <!-- Status -->
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[12px] font-semibold <?= $s['bg'] ?>">
                                            <i class="fas <?= $s['icon'] ?> text-[10px]"></i>
                                            <?= $status ?>
                                        </span>
                                    </td>
                                    <!-- Aktivitas Terakhir -->
                                    <td class="px-4 py-4 text-[13px] text-gray-500"><?= $lastAktif ?></td>
                                    <!-- Aksi -->
                                    <td class="px-4 py-4">
                                        <button class="inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-blue-200 text-blue-600 text-[12px] font-semibold rounded-lg hover:bg-blue-50 transition-colors">
                                            <i class="fas fa-eye text-[11px]"></i> Detail
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
        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
        // Stacked Bar Chart
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('stackedBar').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= $chartLabels ?>,
                    datasets: [
                        {
                            label: 'Selesai Menilai',
                            data: <?= $chartDinilai ?>,
                            backgroundColor: '#10b981',
                            borderRadius: 0,
                            stack: 'penilaian',
                        },
                        {
                            label: 'Belum Menilai',
                            data: <?= $chartBelum ?>,
                            backgroundColor: '#f59e0b',
                            borderRadius: 4,
                            stack: 'penilaian',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                borderRadius: 3,
                                useBorderRadius: true,
                                font: { family: "'Inter', sans-serif", size: 11 },
                                color: '#6b7280',
                                padding: 16,
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 10,
                            cornerRadius: 8,
                            titleFont: { family: "'Inter', sans-serif", size: 12 },
                            bodyFont: { family: "'Inter', sans-serif", size: 12 },
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            ticks: { color: '#9ca3af', font: { family: "'Inter', sans-serif", size: 11 }, padding: 8 },
                            grid: { display: false },
                            border: { display: false }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            max: 10,
                            ticks: { stepSize: 2, color: '#9ca3af', font: { family: "'Inter', sans-serif", size: 11 }, padding: 10 },
                            grid: { color: '#e5e7eb', borderDash: [5, 5] },
                            border: { display: false }
                        }
                    }
                }
            });
        });

        function filterDosen() {
            const q = document.getElementById('searchDosen').value.toLowerCase();
            document.querySelectorAll('.dosen-row').forEach(row => {
                row.style.display = row.dataset.search.includes(q) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
