<?php
    require_once __DIR__ . "/../config/role_guard.php";
    checkRole("dosen_pembimbing");
    require_once __DIR__ . '/../config/db_connect.php';
    $role = 'dosen';
    $activePage = 'dashboard';

    // ====== DYNAMIC DATA QUERIES ======
    $userId = $_SESSION['id_user'];
    $dosenName = $_SESSION['nama'] ?? 'Dosen';

    // Get dosen_pembimbing record
    $stmt = $conn->prepare("SELECT id FROM dosen_pembimbing WHERE user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $dosen = $stmt->fetch();
    $dosenId = $dosen ? $dosen['id'] : 0;

    // Total mahasiswa bimbingan
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM mahasiswa m
        JOIN `groups` g ON m.group_id = g.id
        WHERE g.dosen_pembimbing_id = :did
    ");
    $stmt->execute(['did' => $dosenId]);
    $totalMhsBimbingan = (int)$stmt->fetchColumn();

    // Jurnal pending (logbooks from mahasiswa bimbingan with status 'pending')
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM logbooks l
        JOIN mahasiswa m ON l.mahasiswa_id = m.id
        JOIN `groups` g ON m.group_id = g.id
        WHERE g.dosen_pembimbing_id = :did AND l.status = 'pending'
    ");
    $stmt->execute(['did' => $dosenId]);
    $jurnalPending = (int)$stmt->fetchColumn();

    // Laporan masuk
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM final_reports fr
        JOIN mahasiswa m ON fr.mahasiswa_id = m.id
        JOIN `groups` g ON m.group_id = g.id
        WHERE g.dosen_pembimbing_id = :did
    ");
    $stmt->execute(['did' => $dosenId]);
    $laporanMasuk = (int)$stmt->fetchColumn();

    // Penilaian stats
    $stmt = $conn->prepare("
        SELECT m.id FROM mahasiswa m
        JOIN `groups` g ON m.group_id = g.id
        WHERE g.dosen_pembimbing_id = :did
    ");
    $stmt->execute(['did' => $dosenId]);
    $mhsIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $sudahDinilai = 0;
    $belumDinilai = 0;
    foreach ($mhsIds as $mid) {
        $chk = $conn->prepare("SELECT COUNT(*) FROM nilai_kriteria WHERE mahasiswa_id = :mid AND penilai_user_id = :uid");
        $chk->execute(['mid' => $mid, 'uid' => $userId]);
        if ((int)$chk->fetchColumn() > 0) {
            $sudahDinilai++;
        } else {
            $belumDinilai++;
        }
    }
    $totalPenilaianPct = $totalMhsBimbingan > 0 ? round($sudahDinilai / $totalMhsBimbingan * 100) : 0;
    $prosesPct = 0; // Simplified: either done or not
    $belumPct = $totalMhsBimbingan > 0 ? round($belumDinilai / $totalMhsBimbingan * 100) : 0;

    // Monthly logbook activity from mahasiswa bimbingan (current year)
    $currentYear = date('Y');
    $stmt = $conn->prepare("
        SELECT MONTH(l.tanggal) as bulan,
               COUNT(*) as total_masuk,
               SUM(CASE WHEN l.status != 'pending' THEN 1 ELSE 0 END) as total_reviewed
        FROM logbooks l
        JOIN mahasiswa m ON l.mahasiswa_id = m.id
        JOIN `groups` g ON m.group_id = g.id
        WHERE g.dosen_pembimbing_id = :did AND YEAR(l.tanggal) = :yr
        GROUP BY MONTH(l.tanggal) ORDER BY bulan
    ");
    $stmt->execute(['did' => $dosenId, 'yr' => $currentYear]);
    $monthlyLogbook = $stmt->fetchAll();

    $jurnalMasukData = array_fill(0, 12, 0);
    $jurnalReviewData = array_fill(0, 12, 0);
    foreach ($monthlyLogbook as $r) {
        $jurnalMasukData[(int)$r['bulan'] - 1] = (int)$r['total_masuk'];
        $jurnalReviewData[(int)$r['bulan'] - 1] = (int)$r['total_reviewed'];
    }

    // Mahasiswa bimbingan list with stats
    $stmt = $conn->prepare("
        SELECT m.id, m.nama, m.no_ktm, c.nama_perusahaan,
               (SELECT COUNT(*) FROM logbooks lb WHERE lb.mahasiswa_id = m.id) as total_jurnal,
               (SELECT COUNT(*) FROM attendances a WHERE a.mahasiswa_id = m.id AND a.status = 'Hadir') as total_hadir,
               (SELECT COUNT(*) FROM attendances a WHERE a.mahasiswa_id = m.id) as total_absen,
               (SELECT COUNT(*) FROM nilai_kriteria nk WHERE nk.mahasiswa_id = m.id AND nk.penilai_user_id = :uid) as sudah_dinilai
        FROM mahasiswa m
        JOIN `groups` g ON m.group_id = g.id
        LEFT JOIN companies c ON g.company_id = c.id
        WHERE g.dosen_pembimbing_id = :did
        ORDER BY m.nama ASC
    ");
    $stmt->execute(['did' => $dosenId, 'uid' => $userId]);
    $students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(0,0,0,0.1);
        }
        .student-row:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Header -->
        <?php include '../includes/header.php'; ?>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">

                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-[#1e40af] to-[#3b66f5] rounded-2xl p-8 flex items-center justify-between shadow-sm relative overflow-hidden">
                    <div class="text-white z-10">
                        <p class="text-blue-200 text-[13px] font-medium mb-1 uppercase tracking-wider">Dosen Pembimbing</p>
                        <h2 class="text-2xl font-bold mb-2 tracking-tight">Selamat Datang, <?= htmlspecialchars($dosenName) ?>!</h2>
                        <p class="text-blue-100 text-[15px]">Pantau perkembangan mahasiswa bimbingan Anda dengan mudah.</p>
                    </div>
                    <!-- Illustration shapes -->
                    <div class="hidden md:flex z-10 items-end justify-end w-52 relative h-32 mr-6">
                        <div class="w-24 h-32 bg-white/15 rounded-t-xl absolute bottom-0 shadow-lg backdrop-blur-sm"></div>
                        <div class="w-16 h-12 bg-white rounded-md absolute bottom-6 -left-4 shadow-lg flex items-center justify-center">
                            <i class="fas fa-user-graduate text-blue-500 text-lg"></i>
                        </div>
                        <div class="w-10 h-10 bg-yellow-400 rounded-full absolute top-2 right-4 shadow-lg shadow-yellow-500/40 flex items-center justify-center">
                            <i class="fas fa-star text-white text-xs"></i>
                        </div>
                    </div>
                    <!-- Decorative gradient overlay -->
                    <div class="absolute right-0 top-0 w-80 h-full bg-gradient-to-l from-[#1e3a8a] to-transparent z-0 opacity-60"></div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                    <!-- Card: Total Mahasiswa Bimbingan -->
                    <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-user-graduate text-[#3b66f5] text-[18px]"></i>
                            </div>
                            <span class="text-[11px] font-semibold text-green-500 bg-green-50 px-2 py-1 rounded-full">Aktif</span>
                        </div>
                        <p class="text-[13px] text-gray-500 mb-1">Mhs Bimbingan</p>
                        <p class="text-3xl font-bold text-gray-900"><?= $totalMhsBimbingan ?></p>
                        <p class="text-[12px] text-gray-400 mt-1">Mahasiswa aktif magang</p>
                    </div>

                    <!-- Card: Jurnal Perlu Review -->
                    <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center">
                                <i class="fas fa-book-reader text-orange-500 text-[18px]"></i>
                            </div>
                            <span class="text-[11px] font-semibold text-orange-500 bg-orange-50 px-2 py-1 rounded-full">Pending</span>
                        </div>
                        <p class="text-[13px] text-gray-500 mb-1">Jurnal Pending</p>
                        <p class="text-3xl font-bold text-gray-900"><?= $jurnalPending ?></p>
                        <p class="text-[12px] text-gray-400 mt-1">Menunggu review Anda</p>
                    </div>

                    <!-- Card: Laporan Akhir -->
                    <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center">
                                <i class="fas fa-file-alt text-purple-500 text-[18px]"></i>
                            </div>
                            <span class="text-[11px] font-semibold text-purple-500 bg-purple-50 px-2 py-1 rounded-full"><?= $laporanMasuk > 0 ? 'Baru' : '-' ?></span>
                        </div>
                        <p class="text-[13px] text-gray-500 mb-1">Laporan Masuk</p>
                        <p class="text-3xl font-bold text-gray-900"><?= $laporanMasuk ?></p>
                        <p class="text-[12px] text-gray-400 mt-1">Laporan akhir diterima</p>
                    </div>

                    <!-- Card: Penilaian Selesai -->
                    <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-500 text-[18px]"></i>
                            </div>
                            <span class="text-[11px] font-semibold text-green-500 bg-green-50 px-2 py-1 rounded-full">Selesai</span>
                        </div>
                        <p class="text-[13px] text-gray-500 mb-1">Penilaian Selesai</p>
                        <p class="text-3xl font-bold text-gray-900"><?= $sudahDinilai ?></p>
                        <p class="text-[12px] text-gray-400 mt-1">Dari <?= $totalMhsBimbingan ?> mahasiswa</p>
                    </div>

                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Bar Chart: Aktivitas Jurnal per Mahasiswa -->
                    <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-[17px] font-bold text-gray-800">Aktivitas Jurnal Mahasiswa</h3>
                                <p class="text-[13px] text-gray-400 mt-0.5">Jumlah jurnal disubmit per bulan</p>
                            </div>
                            <span class="text-[12px] text-gray-400 bg-gray-100 px-3 py-1 rounded-full"><?= $currentYear ?></span>
                        </div>
                        <div class="relative w-full h-[260px]">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>

                    <!-- Donut Chart: Status Penilaian -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col">
                        <div>
                            <h3 class="text-[17px] font-bold text-gray-800">Status Penilaian</h3>
                            <p class="text-[13px] text-gray-400 mt-0.5">Mahasiswa bimbingan</p>
                        </div>

                        <div class="relative flex-1 flex justify-center items-center min-h-[160px] my-6">
                            <canvas id="donutChart"></canvas>
                        </div>

                        <div class="space-y-3 mt-auto">
                            <div class="flex items-center justify-between text-[14px]">
                                <span class="flex items-center gap-2.5 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span> Sudah Dinilai
                                </span>
                                <span class="font-bold text-gray-800"><?= $totalPenilaianPct ?>%</span>
                            </div>
                            <div class="flex items-center justify-between text-[14px]">
                                <span class="flex items-center gap-2.5 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#ef4444]"></span> Belum Dinilai
                                </span>
                                <span class="font-bold text-gray-800"><?= $belumPct ?>%</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Daftar Mahasiswa Bimbingan -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 flex items-center justify-between border-b border-gray-100">
                        <div>
                            <h3 class="text-[17px] font-bold text-gray-800">Daftar Mahasiswa Bimbingan</h3>
                            <p class="text-[13px] text-gray-400 mt-0.5">Monitoring progres magang mahasiswa</p>
                        </div>
                        <a href="mhs_bimbingan.php" class="text-[13px] font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg transition-colors">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-[14px]">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-[12px] uppercase tracking-wider">
                                    <th class="text-left px-6 py-3 font-semibold">No</th>
                                    <th class="text-left px-6 py-3 font-semibold">Mahasiswa</th>
                                    <th class="text-left px-6 py-3 font-semibold">Instansi</th>
                                    <th class="text-left px-6 py-3 font-semibold">Jurnal</th>
                                    <th class="text-left px-6 py-3 font-semibold">Kehadiran</th>
                                    <th class="text-left px-6 py-3 font-semibold">Status</th>
                                    <th class="text-left px-6 py-3 font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">Belum ada mahasiswa bimbingan.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($students as $i => $s):
                                    $hadirPct = $s['total_absen'] > 0 ? round($s['total_hadir'] / $s['total_absen'] * 100) : 0;
                                    $statusLabel = $s['sudah_dinilai'] > 0 ? 'Dinilai' : 'Belum';
                                    $statusClass = $statusLabel === 'Dinilai' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600';
                                    $hadirColor = $hadirPct >= 90 ? 'text-green-600' : ($hadirPct >= 80 ? 'text-yellow-600' : 'text-red-500');
                                ?>
                                <tr class="student-row transition-colors">
                                    <td class="px-6 py-4 text-gray-400"><?= $i + 1 ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-[12px] shrink-0">
                                                <?= strtoupper(substr($s['nama'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($s['nama']) ?></p>
                                                <p class="text-[12px] text-gray-400"><?= htmlspecialchars($s['no_ktm'] ?? '-') ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($s['nama_perusahaan'] ?? '-') ?></td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-800"><?= $s['total_jurnal'] ?></span>
                                        <span class="text-gray-400"> jurnal</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold <?= $hadirColor ?>"><?= $hadirPct ?>%</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $statusClass ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="mhs_bimbingan.php?detail=<?= $s['id'] ?>" class="text-blue-600 hover:text-blue-800 text-[13px] font-medium hover:underline">Detail</a>
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

    <!-- Chart Scripts -->
    <script>
        const jurnalMasukData = <?= json_encode(array_values($jurnalMasukData)) ?>;
        const jurnalReviewData = <?= json_encode(array_values($jurnalReviewData)) ?>;
        const penilaianData = { dinilai: <?= $sudahDinilai ?>, belum: <?= $belumDinilai ?> };

        document.addEventListener('DOMContentLoaded', function () {
            const maxJurnal = Math.max(...jurnalMasukData, 5) + 10;

            // Bar Chart: Aktivitas Jurnal per Bulan
            new Chart(document.getElementById('barChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [
                        { label: 'Jurnal Masuk', data: jurnalMasukData, backgroundColor: '#3b66f5', hoverBackgroundColor: '#2350d4', borderRadius: 4, barPercentage: 0.6, categoryPercentage: 0.75 },
                        { label: 'Sudah Direview', data: jurnalReviewData, backgroundColor: '#10b981', hoverBackgroundColor: '#059669', borderRadius: 4, barPercentage: 0.6, categoryPercentage: 0.75 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, borderRadius: 5, useBorderRadius: true, font: { family: "'Inter'", size: 12 }, color: '#6b7280', padding: 16 } },
                        tooltip: { backgroundColor: 'rgba(17,24,39,0.9)', padding: 12, cornerRadius: 8 }
                    },
                    scales: {
                        y: { beginAtZero: true, max: maxJurnal, ticks: { stepSize: Math.ceil(maxJurnal / 5), color: '#9ca3af', font: { size: 11 }, padding: 10 }, grid: { color: '#e5e7eb', borderDash: [5,5] }, border: { display: false } },
                        x: { ticks: { color: '#9ca3af', font: { size: 11 }, padding: 8 }, grid: { display: false }, border: { display: false } }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });

            // Donut Chart: Status Penilaian
            new Chart(document.getElementById('donutChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Sudah Dinilai', 'Belum Dinilai'],
                    datasets: [{ data: [penilaianData.dinilai, penilaianData.belum], backgroundColor: ['#10b981', '#ef4444'], borderWidth: 0, hoverOffset: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '72%', layout: { padding: 10 },
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: 'rgba(17,24,39,0.9)', padding: 12, cornerRadius: 8,
                            callbacks: { label: ctx => { const total = ctx.dataset.data.reduce((a,b) => a+b, 0); const pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0; return ` ${ctx.label}: ${ctx.parsed} mhs (${pct}%)`; } }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
