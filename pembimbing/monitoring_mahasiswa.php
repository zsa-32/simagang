<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'pembimbing lapang') {
    header('Location: ../index.php'); exit();
}

$role = 'pembimbing';
$activePage = 'monitoring_mahasiswa';
$id_user = (int) $_SESSION['id_user'];
$today = date('Y-m-d');

// Ambil nama perusahaan pembimbing
$stmtPb = $conn->prepare("SELECT nama_perusahaan FROM Profile WHERE id_user = :id LIMIT 1");
$stmtPb->execute([':id' => $id_user]);
$pbData = $stmtPb->fetch();
$namaPerusahaan = $pbData['nama_perusahaan'] ?? null;

// Presensi mahasiswa hari ini di perusahaan yang sama
$stmtP = $conn->prepare("
    SELECT u.nama, p.nim,
           c.nama_company AS perusahaan,
           u2.nama AS nama_dosen,
           a.waktu_masuk, a.waktu_keluar, a.keterangan
    FROM Users u
    JOIN Profile p ON u.id_user = p.id_user
    LEFT JOIN Internship_placement ip ON u.id_user = ip.id_user
    LEFT JOIN Company c ON ip.id_company = c.id_company
    LEFT JOIN Users u2 ON p.id_dosen_pembimbing = u2.id_user
    LEFT JOIN Attendances a ON a.id_user = u.id_user AND a.tanggal = :today
    WHERE c.nama_company = :perusahaan
    AND u.id_user != :id_pb
    ORDER BY u.nama ASC
");
$stmtP->execute([':today' => $today, ':perusahaan' => $namaPerusahaan, ':id_pb' => $id_user]);
$presensiList = $stmtP->fetchAll();

// Jurnal mahasiswa
$stmtJ = $conn->prepare("
    SELECT u.nama, p.nim, dj.kegiatan, dj.tanggal, dj.status
    FROM Daily_journal dj
    JOIN Users u ON dj.id_user = u.id_user
    JOIN Profile p ON u.id_user = p.id_user
    LEFT JOIN Internship_placement ip ON u.id_user = ip.id_user
    LEFT JOIN Company c ON ip.id_company = c.id_company
    WHERE c.nama_company = :perusahaan AND u.id_user != :id_pb
    ORDER BY dj.tanggal DESC
");
$stmtJ->execute([':perusahaan' => $namaPerusahaan, ':id_pb' => $id_user]);
$jurnalList = $stmtJ->fetchAll();

// Hitung stats
$totalMhs    = count($presensiList);
$jmlHadir    = count(array_filter($presensiList, fn($r) => $r['keterangan'] === 'Hadir'));
$jmlTerlambat = 0; // perlu definisi kriteria terlambat jika ada
$jmlTidakHadir = count(array_filter($presensiList, fn($r) => $r['keterangan'] === 'Alpha' || is_null($r['keterangan'])));
$jmlJurnalDisetujui = count(array_filter($jurnalList, fn($j) => $j['status'] === 'Disetujui'));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Mahasiswa - Pembimbing Lapang | Magang TIF</title>
    <meta name="description" content="Pantau presensi dan jurnal harian seluruh mahasiswa magang.">
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

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-5">

                <!-- Summary Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <!-- Title Row -->
                    <div class="flex items-start justify-between mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Monitoring Mahasiswa</h2>
                            <p class="text-gray-500 text-sm mt-0.5">Pantau presensi dan jurnal harian seluruh mahasiswa magang</p>
                        </div>
                        <div class="flex items-center gap-2 text-[13px] text-gray-500 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                            <span id="todayDate"></span>
                        </div>
                    </div>

                    <!-- Stat Mini Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[12px] text-gray-500 font-medium mb-1">Total Mahasiswa</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalMhs ?></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[12px] text-green-600 font-medium mb-1">Hadir</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $jmlHadir ?></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[12px] text-amber-500 font-medium mb-1">Terlambat</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $jmlTerlambat ?></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[12px] text-red-500 font-medium mb-1">Tidak Hadir</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $jmlTidakHadir ?></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[12px] text-blue-600 font-medium mb-1">Jurnal Disetujui</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $jmlJurnalDisetujui ?>/<?= count($jurnalList) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                    <!-- Tabs + Search -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-1">
                            <button id="tabPresensi" onclick="switchTab('presensi')"
                                class="tab-btn px-4 py-2 rounded-lg text-[13px] font-semibold bg-white border border-blue-200 text-blue-600 transition-all">
                                Data Presensi
                            </button>
                            <button id="tabJurnal" onclick="switchTab('jurnal')"
                                class="tab-btn px-4 py-2 rounded-lg text-[13px] font-medium text-gray-500 hover:bg-gray-50 border border-transparent transition-all">
                                Jurnal Harian
                            </button>
                        </div>
                        <div class="relative">
                            <i class="fas fa-search text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 text-[12px]"></i>
                            <input id="searchInput" type="text" placeholder="Cari mahasiswa..."
                                oninput="filterRows()"
                                class="pl-8 pr-4 py-2 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all w-52">
                        </div>
                    </div>

                    <!-- Tab: Data Presensi -->
                    <div id="contentPresensi" class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-50">
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Mahasiswa</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Status Kehadiran</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Dosen Pembimbing</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Waktu Kehadiran</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="presensiTableBody" class="divide-y divide-gray-50">
                                <?php if (empty($presensiList)): ?>
                                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-users text-3xl mb-2 block"></i>Belum ada data presensi mahasiswa.</td></tr>
                                <?php else: ?>
                                <?php foreach ($presensiList as $mhs):
                                    $ket = $mhs['keterangan'] ?? null;
                                    $statusConfig = match($ket) {
                                        'Hadir'       => ['dot' => 'bg-green-500',  'text' => 'text-green-600', 'label' => 'Hadir'],
                                        'Izin','Sakit'=> ['dot' => 'bg-amber-400',  'text' => 'text-amber-600', 'label' => 'Izin/Sakit'],
                                        'Alpha'       => ['dot' => 'bg-red-500',    'text' => 'text-red-600',   'label' => 'Tidak Hadir'],
                                        default       => ['dot' => 'bg-gray-300',   'text' => 'text-gray-400',  'label' => 'Belum presensi'],
                                    };
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors data-row"
                                        data-search="<?= strtolower($mhs['nama'] . ' ' . $mhs['nim']) ?>">
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($mhs['nama']) ?></p>
                                            <p class="text-[12px] text-gray-400"><?= $mhs['nim'] ?> &middot; <?= htmlspecialchars($mhs['perusahaan'] ?? '-') ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if (!$ket): ?>
                                                <span class="text-[13px] text-gray-400 italic">Belum presensi</span>
                                            <?php else: ?>
                                                <span class="flex items-center gap-1.5 text-[13px] font-semibold <?= $statusConfig['text'] ?>">
                                                    <span class="w-2 h-2 rounded-full <?= $statusConfig['dot'] ?> inline-block"></span>
                                                    <?= $statusConfig['label'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-[13px] text-gray-600"><?= htmlspecialchars($mhs['nama_dosen'] ?? '-') ?></td>
                                        <td class="px-6 py-4 text-[13px] text-gray-600 font-medium"><?= $mhs['waktu_masuk'] ?? '-' ?></td>
                                        <td class="px-6 py-4">
                                            <button class="flex items-center gap-1.5 px-3 py-1.5 border border-blue-200 text-blue-600 rounded-lg text-[12px] font-medium hover:bg-blue-50 transition-colors">
                                                <i class="fas fa-eye text-[11px]"></i> Lihat
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tab: Jurnal Harian (hidden by default) -->
                    <div id="contentJurnal" class="overflow-x-auto hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-50">
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Mahasiswa</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Judul Jurnal</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Tanggal</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Status</th>
                                    <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="jurnalTableBody" class="divide-y divide-gray-50">
                                <?php if (empty($jurnalList)): ?>
                                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-book text-3xl mb-2 block"></i>Belum ada jurnal dari mahasiswa.</td></tr>
                                <?php else: ?>
                                <?php foreach ($jurnalList as $j):
                                    $jBadge = match($j['status']) {
                                        'Disetujui' => 'bg-green-100 text-green-700',
                                        'Ditolak'   => 'bg-red-100 text-red-600',
                                        default     => 'bg-amber-100 text-amber-700',
                                    };
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors jurnal-row"
                                        data-search="<?= strtolower($j['nama'] . ' ' . $j['nim']) ?>">
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($j['nama']) ?></p>
                                            <p class="text-[12px] text-gray-400"><?= $j['nim'] ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-[13px] text-gray-700"><?= htmlspecialchars(explode("\n", $j['kegiatan'])[0]) ?></td>
                                        <td class="px-6 py-4 text-[13px] text-gray-500"><?= date('d M Y', strtotime($j['tanggal'])) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded-lg text-[12px] font-semibold <?= $jBadge ?>"><?= $j['status'] ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button class="flex items-center gap-1.5 px-3 py-1.5 border border-blue-200 text-blue-600 rounded-lg text-[12px] font-medium hover:bg-blue-50 transition-colors">
                                                <i class="fas fa-eye text-[11px]"></i> Lihat
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
        // ======= Tanggal Hari Ini =======
        const opts = { day: '2-digit', month: 'long', year: 'numeric' };
        document.getElementById('todayDate').textContent =
            new Date().toLocaleDateString('id-ID', opts);

        // ======= Tab Switch =======
        let activeTab = 'presensi';

        function switchTab(tab) {
            activeTab = tab;
            const tabs = { presensi: 'tabPresensi', jurnal: 'tabJurnal' };
            const contents = { presensi: 'contentPresensi', jurnal: 'contentJurnal' };

            Object.keys(tabs).forEach(key => {
                const btnEl = document.getElementById(tabs[key]);
                const contentEl = document.getElementById(contents[key]);
                if (key === tab) {
                    btnEl.className = 'tab-btn px-4 py-2 rounded-lg text-[13px] font-semibold bg-white border border-blue-200 text-blue-600 transition-all';
                    contentEl.classList.remove('hidden');
                } else {
                    btnEl.className = 'tab-btn px-4 py-2 rounded-lg text-[13px] font-medium text-gray-500 hover:bg-gray-50 border border-transparent transition-all';
                    contentEl.classList.add('hidden');
                }
            });

            // Reset search
            document.getElementById('searchInput').value = '';
            filterRows();
        }

        // ======= Search / Filter =======
        function filterRows() {
            const q = document.getElementById('searchInput').value.toLowerCase();
            const selector = activeTab === 'presensi' ? '.data-row' : '.jurnal-row';
            document.querySelectorAll(selector).forEach(row => {
                row.style.display = row.dataset.search.includes(q) ? '' : 'none';
            });
        }
    </script>

</body>

</html>
