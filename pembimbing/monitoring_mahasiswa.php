<?php
session_start();
$role = 'pembimbing';
$activePage = 'monitoring_mahasiswa';
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
                            <p class="text-2xl font-bold text-gray-900">7</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[12px] text-green-600 font-medium mb-1">Hadir</p>
                            <p class="text-2xl font-bold text-gray-900">4</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[12px] text-amber-500 font-medium mb-1">Terlambat</p>
                            <p class="text-2xl font-bold text-gray-900">1</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[12px] text-red-500 font-medium mb-1">Tidak Hadir</p>
                            <p class="text-2xl font-bold text-gray-900">1</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[12px] text-blue-600 font-medium mb-1">Jurnal Disetujui</p>
                            <p class="text-2xl font-bold text-gray-900">3/7</p>
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
                                <?php
                                $mahasiswa = [
                                    ['nama' => 'Balmond',      'nim' => '21173431', 'perusahaan' => 'PT Telkom Indonesia',   'status' => 'Hadir',        'dosen' => 'Dr. Budi Santoso',  'waktu' => '08:15'],
                                    ['nama' => 'Lesley',       'nim' => '20193432', 'perusahaan' => 'CV Digital Kreatif',    'status' => 'Hadir',        'dosen' => 'Dr. Siti Rahayu',   'waktu' => '08:05'],
                                    ['nama' => 'Harley',       'nim' => '22123532', 'perusahaan' => 'PT Bank BRI',           'status' => 'Terlambat',    'dosen' => 'Ir. Made Wirawan',  'waktu' => '08:35'],
                                    ['nama' => 'Budi Santoso', 'nim' => '21140024', 'perusahaan' => 'PT Astra International','status' => 'Belum presensi','dosen' => 'Dr. Budi Santoso', 'waktu' => '-'],
                                    ['nama' => 'Joko',         'nim' => '21130003', 'perusahaan' => 'PT Tokopedia',          'status' => 'Tidak Hadir',  'dosen' => 'Dr. Siti Rahayu',   'waktu' => '-'],
                                    ['nama' => 'Meks',         'nim' => '22130043', 'perusahaan' => 'PT Gojek Indonesia',    'status' => 'Hadir',        'dosen' => 'Ir. Made Wirawan',  'waktu' => '08:10'],
                                    ['nama' => 'Nana',         'nim' => '21130043', 'perusahaan' => 'PT Bukalapak',          'status' => 'Hadir',        'dosen' => 'Dr. Budi Santoso',  'waktu' => '08:00'],
                                ];
                                foreach ($mahasiswa as $mhs):
                                    $statusConfig = match($mhs['status']) {
                                        'Hadir'         => ['dot' => 'bg-green-500',  'text' => 'text-green-600', 'label' => 'Hadir'],
                                        'Terlambat'     => ['dot' => 'bg-amber-400',  'text' => 'text-amber-600', 'label' => 'Terlambat'],
                                        'Tidak Hadir'   => ['dot' => 'bg-red-500',    'text' => 'text-red-600',   'label' => 'Tidak Hadir'],
                                        default         => ['dot' => 'bg-gray-300',   'text' => 'text-gray-400',  'label' => 'Belum presensi'],
                                    };
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors data-row"
                                        data-search="<?= strtolower($mhs['nama'] . ' ' . $mhs['nim']) ?>">
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($mhs['nama']) ?></p>
                                            <p class="text-[12px] text-gray-400"><?= $mhs['nim'] ?> &middot; <?= htmlspecialchars($mhs['perusahaan']) ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($mhs['status'] === 'Belum presensi'): ?>
                                                <span class="text-[13px] text-gray-400 italic">Belum presensi</span>
                                            <?php else: ?>
                                                <span class="flex items-center gap-1.5 text-[13px] font-semibold <?= $statusConfig['text'] ?>">
                                                    <span class="w-2 h-2 rounded-full <?= $statusConfig['dot'] ?> inline-block"></span>
                                                    <?= $statusConfig['label'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-[13px] text-gray-600"><?= htmlspecialchars($mhs['dosen']) ?></td>
                                        <td class="px-6 py-4 text-[13px] text-gray-600 font-medium"><?= $mhs['waktu'] ?></td>
                                        <td class="px-6 py-4">
                                            <button class="flex items-center gap-1.5 px-3 py-1.5 border border-blue-200 text-blue-600 rounded-lg text-[12px] font-medium hover:bg-blue-50 transition-colors">
                                                <i class="fas fa-eye text-[11px]"></i> Lihat
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
                                <?php
                                $jurnal = [
                                    ['nama' => 'Balmond',      'nim' => '21173431', 'judul' => 'Setup Environment & Onboarding',     'tanggal' => '04 Mar 2026', 'status' => 'Disetujui'],
                                    ['nama' => 'Lesley',       'nim' => '20193432', 'judul' => 'Belajar Framework Laravel',           'tanggal' => '04 Mar 2026', 'status' => 'Disetujui'],
                                    ['nama' => 'Harley',       'nim' => '22123532', 'judul' => 'Meeting dengan tim backend',          'tanggal' => '04 Mar 2026', 'status' => 'Menunggu'],
                                    ['nama' => 'Budi Santoso', 'nim' => '21140024', 'judul' => 'Desain UI halaman utama',            'tanggal' => '04 Mar 2026', 'status' => 'Menunggu'],
                                    ['nama' => 'Joko',         'nim' => '21130003', 'judul' => 'Integrasi API pembayaran',           'tanggal' => '04 Mar 2026', 'status' => 'Menunggu'],
                                    ['nama' => 'Meks',         'nim' => '22130043', 'judul' => 'Debugging fitur cart',               'tanggal' => '04 Mar 2026', 'status' => 'Disetujui'],
                                    ['nama' => 'Nana',         'nim' => '21130043', 'judul' => 'Review code dengan mentor',          'tanggal' => '04 Mar 2026', 'status' => 'Menunggu'],
                                ];
                                foreach ($jurnal as $j):
                                    $jBadge = $j['status'] === 'Disetujui'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-amber-100 text-amber-700';
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors jurnal-row"
                                        data-search="<?= strtolower($j['nama'] . ' ' . $j['nim']) ?>">
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($j['nama']) ?></p>
                                            <p class="text-[12px] text-gray-400"><?= $j['nim'] ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-[13px] text-gray-700"><?= htmlspecialchars($j['judul']) ?></td>
                                        <td class="px-6 py-4 text-[13px] text-gray-500"><?= $j['tanggal'] ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded-lg text-[12px] font-semibold <?= $jBadge ?>">
                                                <?= $j['status'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button class="flex items-center gap-1.5 px-3 py-1.5 border border-blue-200 text-blue-600 rounded-lg text-[12px] font-medium hover:bg-blue-50 transition-colors">
                                                <i class="fas fa-eye text-[11px]"></i> Lihat
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
