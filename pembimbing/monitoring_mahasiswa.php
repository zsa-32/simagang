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

// Presensi mahasiswa yang dibimbing oleh pembimbing ini (by id_pembimbing_lapang)
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
    WHERE p.id_pembimbing_lapang = :id_pb
    ORDER BY u.nama ASC
");
$stmtP->execute([':today' => $today, ':id_pb' => $id_user]);
$presensiList = $stmtP->fetchAll();

// Jurnal mahasiswa bimbingan pembimbing ini
$stmtJ = $conn->prepare("
    SELECT u.nama, p.nim, dj.kegiatan, dj.bukti, dj.tanggal, dj.status
    FROM Daily_journal dj
    JOIN Users u ON dj.id_user = u.id_user
    JOIN Profile p ON u.id_user = p.id_user
    WHERE p.id_pembimbing_lapang = :id_pb
    ORDER BY dj.tanggal DESC
");
$stmtJ->execute([':id_pb' => $id_user]);
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
                                            <button
                                                onclick="openPresensiDetail(`<?= htmlspecialchars($mhs['nama'], ENT_QUOTES) ?>`, `<?= $mhs['nim'] ?>`, `<?= htmlspecialchars($mhs['perusahaan'] ?? '-', ENT_QUOTES) ?>`, `<?= $mhs['nama_dosen'] ?? '-' ?>`, `<?= $mhs['keterangan'] ?? '' ?>`, `<?= $mhs['waktu_masuk'] ?? '' ?>`, `<?= $mhs['waktu_keluar'] ?? '' ?>`)"
                                                class="flex items-center gap-1.5 px-3 py-1.5 border border-blue-200 text-blue-600 rounded-lg text-[12px] font-medium hover:bg-blue-50 transition-colors">
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
                                    <th class="text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5 w-20">Bukti</th>
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
                                        <!-- Kolom Bukti -->
                                        <td class="px-6 py-4 text-center">
                                            <?php if (!empty($j['bukti'])): ?>
                                                <a href="../<?= htmlspecialchars($j['bukti']) ?>" target="_blank"
                                                   title="Lihat Bukti"
                                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-500 hover:bg-indigo-100 hover:border-indigo-300 transition-all">
                                                    <i class="fas fa-image text-[14px]"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-50 border border-gray-100 text-gray-300" title="Tidak ada bukti">
                                                    <i class="fas fa-image text-[14px]"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded-lg text-[12px] font-semibold <?= $jBadge ?>"><?= $j['status'] ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button
                                                onclick="openJurnalDetail(`<?= htmlspecialchars($j['nama'], ENT_QUOTES) ?>`, `<?= $j['nim'] ?>`, `<?= addslashes(htmlspecialchars(explode('\n', $j['kegiatan'])[0])) ?>`, `<?= addslashes(htmlspecialchars($j['kegiatan'])) ?>`, `<?= date('d M Y', strtotime($j['tanggal'])) ?>`, `<?= $j['status'] ?>`, `<?= addslashes($j['bukti'] ?? '') ?>`)"
                                                class="flex items-center gap-1.5 px-3 py-1.5 border border-blue-200 text-blue-600 rounded-lg text-[12px] font-medium hover:bg-blue-50 transition-colors">
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

    <!-- ===== Modal Detail Presensi ===== -->
    <div id="modalPresensi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <h3 class="text-[16px] font-bold text-gray-900">Detail Presensi</h3>
                <button onclick="closeModal('modalPresensi')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-white font-bold text-[16px] shrink-0" id="pAvatar"></div>
                    <div>
                        <p id="pNama" class="font-bold text-gray-900 text-[15px]"></p>
                        <p id="pNim" class="text-[13px] text-gray-400"></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1">Perusahaan</p>
                        <p id="pPerusahaan" class="text-[13px] font-medium text-gray-800"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1">Dosen Pembimbing</p>
                        <p id="pDosen" class="text-[13px] font-medium text-gray-800"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1">Status Kehadiran</p>
                        <p id="pStatus" class="text-[13px] font-semibold"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1">Waktu Masuk</p>
                        <p id="pWaktuMasuk" class="text-[13px] font-medium text-gray-800"></p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button onclick="closeModal('modalPresensi')" class="px-5 py-2 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ===== Modal Detail Jurnal ===== -->
    <div id="modalJurnal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white z-10">
                <h3 class="text-[16px] font-bold text-gray-900">Detail Jurnal</h3>
                <button onclick="closeModal('modalJurnal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Mahasiswa</p>
                        <p id="jNama" class="text-[14px] font-semibold text-gray-800"></p>
                        <p id="jNim" class="text-[12px] text-gray-400 mt-0.5"></p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Tanggal</p>
                        <p id="jTanggal" class="text-[14px] text-gray-700"></p>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Judul Kegiatan</p>
                    <p id="jJudul" class="text-[14px] font-semibold text-gray-800"></p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Detail Kegiatan</p>
                    <p id="jKegiatan" class="text-[14px] text-gray-700 bg-gray-50 rounded-xl px-4 py-3 leading-relaxed whitespace-pre-line"></p>
                </div>
                <!-- Bukti -->
                <div id="jBuktiWrap" class="hidden">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Bukti Kegiatan</p>
                    <a id="jBuktiLink" href="#" target="_blank"
                       class="group block overflow-hidden rounded-xl border border-gray-200 bg-gray-50 hover:border-indigo-300 transition-all">
                        <img id="jBuktiImg" src="#" alt="Bukti Jurnal" class="w-full max-h-48 object-contain bg-gray-50">
                        <div class="flex items-center gap-2 px-3 py-2 border-t border-gray-100 text-[12px] text-indigo-600 font-medium group-hover:bg-indigo-50 transition-colors">
                            <i class="fas fa-external-link-alt text-[11px]"></i> Buka gambar penuh
                        </div>
                    </a>
                </div>
                <div id="jNoBuktiWrap" class="hidden">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Bukti Kegiatan</p>
                    <p class="text-[13px] text-gray-400 italic bg-gray-50 rounded-xl px-4 py-3">Tidak ada bukti yang dilampirkan.</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                    <span id="jStatus" class="px-3 py-1 rounded-full text-[12px] font-semibold"></span>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end sticky bottom-0 bg-white">
                <button onclick="closeModal('modalJurnal')" class="px-5 py-2 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50">Tutup</button>
            </div>
        </div>
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

        // ======= Modal Helper =======
        function closeModal(id) {
            const m = document.getElementById(id);
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
        function openModal(id) {
            const m = document.getElementById(id);
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        // Klik backdrop tutup modal
        ['modalPresensi', 'modalJurnal'].forEach(id => {
            document.getElementById(id).addEventListener('click', function(e) {
                if (e.target === this) closeModal(id);
            });
        });

        // ======= Modal Detail Presensi =======
        function openPresensiDetail(nama, nim, perusahaan, dosen, status, masuk, keluar) {
            document.getElementById('pAvatar').textContent      = nama.substring(0,2).toUpperCase();
            document.getElementById('pNama').textContent        = nama;
            document.getElementById('pNim').textContent         = nim;
            document.getElementById('pPerusahaan').textContent  = perusahaan || '-';
            document.getElementById('pDosen').textContent       = dosen || '-';
            document.getElementById('pWaktuMasuk').textContent  = masuk || '-';

            const statusEl = document.getElementById('pStatus');
            const statusMap = {
                'Hadir':  { cls: 'text-green-600', label: '● Hadir' },
                'Izin':   { cls: 'text-amber-600', label: '● Izin' },
                'Sakit':  { cls: 'text-amber-600', label: '● Sakit' },
                'Alpha':  { cls: 'text-red-600',   label: '● Tidak Hadir' },
            };
            const s = statusMap[status] || { cls: 'text-gray-400', label: '● Belum presensi' };
            statusEl.className = 'text-[13px] font-semibold ' + s.cls;
            statusEl.textContent = s.label;

            openModal('modalPresensi');
        }

        // ======= Modal Detail Jurnal =======
        function openJurnalDetail(nama, nim, judul, kegiatan, tanggal, status, bukti) {
            document.getElementById('jNama').textContent    = nama;
            document.getElementById('jNim').textContent     = nim;
            document.getElementById('jJudul').textContent   = judul;
            document.getElementById('jKegiatan').textContent = kegiatan;
            document.getElementById('jTanggal').textContent = tanggal;

            const statusEl = document.getElementById('jStatus');
            const badges = { 'Disetujui': 'bg-green-100 text-green-700', 'Ditolak': 'bg-red-100 text-red-600', 'Menunggu': 'bg-amber-100 text-amber-700' };
            statusEl.className = 'px-3 py-1 rounded-full text-[12px] font-semibold ' + (badges[status] || badges['Menunggu']);
            statusEl.textContent = status;

            const buktiWrap   = document.getElementById('jBuktiWrap');
            const noBuktiWrap = document.getElementById('jNoBuktiWrap');
            if (bukti) {
                const baseUrl = window.location.origin + '/simagang/';
                document.getElementById('jBuktiImg').src   = baseUrl + bukti;
                document.getElementById('jBuktiLink').href = baseUrl + bukti;
                buktiWrap.classList.remove('hidden');
                noBuktiWrap.classList.add('hidden');
            } else {
                buktiWrap.classList.add('hidden');
                noBuktiWrap.classList.remove('hidden');
            }

            openModal('modalJurnal');
        }
    </script>

</body>

</html>
