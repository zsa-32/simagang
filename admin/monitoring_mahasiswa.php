<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'admin') {
    header('Location: ../index.php'); exit();
}

$role = 'admin';
$activePage = 'monitoring_mahasiswa';
$today = date('Y-m-d');

// Filter tanggal dari GET, default hari ini
$selectedDate = (isset($_GET['tanggal']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tanggal']))
    ? $_GET['tanggal']
    : $today;
$isToday = ($selectedDate === $today);

// Presensi semua mahasiswa berdasarkan tanggal yang dipilih
$stmtP = $conn->prepare("
    SELECT u.nama, p.nim,
           c.nama_company AS perusahaan,
           a.waktu_masuk, a.waktu_keluar, a.keterangan
    FROM Users u
    JOIN Profile p ON u.id_user = p.id_user
    LEFT JOIN Internship_placement ip ON u.id_user = ip.id_user
    LEFT JOIN Company c ON ip.id_company = c.id_company
    LEFT JOIN Attendances a ON a.id_user = u.id_user AND a.tanggal = :tgl
    JOIN Users_role ur ON u.id_user = ur.id_user
    JOIN Roles r ON ur.id_role = r.id_role
    WHERE LOWER(r.nama_role) = 'mahasiswa'
    ORDER BY u.nama ASC
");
$stmtP->execute([':tgl' => $selectedDate]);
$presensiList = $stmtP->fetchAll();

// Jurnal semua mahasiswa
$stmtJ = $conn->prepare("
    SELECT u.nama, p.nim, dj.kegiatan, dj.tanggal, dj.status, dj.bukti
    FROM Daily_journal dj
    JOIN Users u ON dj.id_user = u.id_user
    JOIN Profile p ON u.id_user = p.id_user
    WHERE dj.tanggal = :tgl
    ORDER BY dj.tanggal DESC
");
$stmtJ->execute([':tgl' => $selectedDate]);
$jurnalList = $stmtJ->fetchAll();

// Hitung stats
$totalMhs          = count($presensiList);
$jmlHadir          = count(array_filter($presensiList, fn($r) => $r['keterangan'] === 'Hadir'));
$jmlTerlambat      = 0;
$jmlTidakHadir     = count(array_filter($presensiList, fn($r) => $r['keterangan'] === 'Alpha'));
$jmlJurnalDisetujui = count(array_filter($jurnalList, fn($j) => $j['status'] === 'Disetujui'));

$statusColors = [
    'Hadir'       => 'bg-green-100 text-green-700',
    'Tidak Hadir' => 'bg-red-100 text-red-600',
    'Alpha'       => 'bg-red-100 text-red-600',
    'Terlambat'   => 'bg-amber-100 text-amber-600',
    'Izin'        => 'bg-amber-100 text-amber-600',
    'Sakit'       => 'bg-amber-100 text-amber-600',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Mahasiswa - Magang TIF</title>
    <meta name="description" content="Pantau presensi dan jurnal harian seluruh mahasiswa magang pada sistem Magang TIF.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">

                <!-- Page Heading -->
                <div class="flex items-start justify-between flex-wrap gap-3">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Monitoring Mahasiswa</h2>
                        <p class="text-gray-500 text-sm mt-0.5">
                            Presensi &amp; jurnal tanggal
                            <span class="font-semibold text-gray-700">
                                <?= (new DateTime($selectedDate))->format('d M Y') ?>
                            </span>
                            <?php if ($isToday): ?>
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-600">Hari Ini</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Date Picker Filter -->
                        <form method="GET" id="formTanggal" class="flex items-center gap-2">
                            <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 bg-white rounded-xl shadow-sm cursor-pointer hover:bg-gray-50 transition-colors">
                                <i class="fas fa-calendar text-gray-400 text-[12px]"></i>
                                <input type="date" name="tanggal" id="inputTanggal"
                                       value="<?= htmlspecialchars($selectedDate) ?>"
                                       max="<?= $today ?>"
                                       class="text-[13px] font-medium text-gray-700 bg-transparent outline-none cursor-pointer"
                                       onchange="document.getElementById('formTanggal').submit()">
                            </label>
                            <?php if (!$isToday): ?>
                                <a href="?" class="flex items-center gap-1.5 px-3 py-2 border border-gray-200 bg-white rounded-xl text-[12px] font-medium text-gray-500 hover:bg-gray-50 transition-colors shadow-sm" title="Kembali ke hari ini">
                                    <i class="fas fa-rotate-left text-[11px]"></i> Hari Ini
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Total Mahasiswa</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $totalMhs ?></p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Hadir</p>
                        <p class="text-2xl font-bold text-green-500"><?= $jmlHadir ?></p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Terlambat</p>
                        <p class="text-2xl font-bold text-amber-500"><?= $jmlTerlambat ?></p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Tidak Hadir</p>
                        <p class="text-2xl font-bold text-red-500"><?= $jmlTidakHadir ?></p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Jurnal Disetujui</p>
                        <p class="text-2xl font-bold text-blue-600"><?= $jmlJurnalDisetujui ?>/<?= count($jurnalList) ?></p>
                    </div>
                </div>

                <!-- Data Table Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <!-- Tabs & Search -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-gray-100">
                        <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
                            <button id="tabPresensi" onclick="switchTab('presensi')" class="tab-btn px-4 py-2 rounded-lg text-[13px] font-semibold bg-white text-gray-800 shadow-sm transition-all">Data Presensi</button>
                            <button id="tabJurnal" onclick="switchTab('jurnal')" class="tab-btn px-4 py-2 rounded-lg text-[13px] font-medium text-gray-500 transition-all">Jurnal Harian</button>
                        </div>
                        <div class="relative">
                            <i class="fas fa-search text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-[12px]"></i>
                            <input id="searchMhs" type="text" placeholder="Cari mahasiswa..." oninput="filterRows()"
                                   class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-[13px] text-gray-700 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all w-56">
                        </div>
                    </div>

                    <!-- Presensi Table -->
                    <div id="presensiTable" class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Mahasiswa</th>
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Perusahaan</th>
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Check-In</th>
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Check-Out</th>
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if (empty($presensiList)): ?>
                                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-users text-3xl mb-2 block"></i>Belum ada data presensi.</td></tr>
                                <?php else: ?>
                                <?php foreach ($presensiList as $d):
                                    $ket = $d['keterangan'] ?? 'Belum Hadir';
                                    $colorClass = $statusColors[$ket] ?? 'bg-gray-100 text-gray-500';
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors mhs-row" data-search="<?= strtolower($d['nama'] . ' ' . $d['nim']) ?>">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($d['nama']) ?></p>
                                        <p class="text-[12px] text-gray-400"><?= $d['nim'] ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-[13px] text-gray-600"><?= htmlspecialchars($d['perusahaan'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-700 font-medium"><?= $d['waktu_masuk'] ?? '-' ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-700 font-medium"><?= $d['waktu_keluar'] ?? '-' ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $colorClass ?>"><?= htmlspecialchars($ket) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Jurnal Table (hidden by default) -->
                    <div id="jurnalTable" class="overflow-x-auto hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Mahasiswa</th>
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Judul Jurnal</th>
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Tanggal</th>
                                    <th class="text-center text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Bukti</th>
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if (empty($jurnalList)): ?>
                                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-book text-3xl mb-2 block"></i>Belum ada jurnal.</td></tr>
                                <?php else: ?>
                                <?php
                                $jurnalColors = [
                                    'Disetujui' => 'bg-green-100 text-green-700',
                                    'Menunggu'  => 'bg-amber-100 text-amber-600',
                                    'Ditolak'   => 'bg-red-100 text-red-600',
                                ];
                                foreach ($jurnalList as $j):
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors jurnal-row" data-search="<?= strtolower($j['nama'] . ' ' . $j['nim']) ?>">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($j['nama']) ?></p>
                                        <p class="text-[12px] text-gray-400"><?= $j['nim'] ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-[13px] text-gray-700"><?= htmlspecialchars(explode("\n", $j['kegiatan'])[0]) ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-500"><?= date('d M Y', strtotime($j['tanggal'])) ?></td>
                                    <!-- Kolom Bukti -->
                                    <td class="px-6 py-4 text-center">
                                        <?php if (!empty($j['bukti'])): ?>
                                            <a href="../<?= htmlspecialchars($j['bukti']) ?>" target="_blank"
                                               title="Lihat Bukti"
                                               class="group inline-block relative">
                                                <img src="../<?= htmlspecialchars($j['bukti']) ?>" alt="Bukti"
                                                     class="w-10 h-10 object-cover rounded-lg border border-gray-200 shadow-sm group-hover:opacity-80 transition-opacity">
                                                <span class="absolute -top-1 -right-1 w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <i class="fas fa-external-link-alt text-white text-[7px]"></i>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-50 border border-gray-100 text-gray-300" title="Tidak ada bukti">
                                                <i class="fas fa-image text-[14px]"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $jurnalColors[$j['status']] ?? 'bg-gray-100 text-gray-500' ?>"><?= $j['status'] ?></span>
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
        function switchTab(tab) {
            const isPresensi = tab === 'presensi';
            document.getElementById('presensiTable').classList.toggle('hidden', !isPresensi);
            document.getElementById('jurnalTable').classList.toggle('hidden', isPresensi);

            const tabPresensi = document.getElementById('tabPresensi');
            const tabJurnal   = document.getElementById('tabJurnal');

            if (isPresensi) {
                tabPresensi.className = 'tab-btn px-4 py-2 rounded-lg text-[13px] font-semibold bg-white text-gray-800 shadow-sm transition-all';
                tabJurnal.className   = 'tab-btn px-4 py-2 rounded-lg text-[13px] font-medium text-gray-500 transition-all';
            } else {
                tabJurnal.className   = 'tab-btn px-4 py-2 rounded-lg text-[13px] font-semibold bg-white text-gray-800 shadow-sm transition-all';
                tabPresensi.className = 'tab-btn px-4 py-2 rounded-lg text-[13px] font-medium text-gray-500 transition-all';
            }
        }

        function filterRows() {
            const q = document.getElementById('searchMhs').value.toLowerCase();
            document.querySelectorAll('.mhs-row, .jurnal-row').forEach(row => {
                row.style.display = row.dataset.search.includes(q) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
