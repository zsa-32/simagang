<?php
session_start();
$role = 'admin';
$activePage = 'monitoring_mahasiswa';
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
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Monitoring Mahasiswa</h2>
                        <p class="text-gray-500 text-sm mt-0.5">Pantau presensi dan jurnal harian seluruh mahasiswa magang</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <button class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 bg-white rounded-xl text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                            <i class="fas fa-calendar text-gray-400 text-[12px]"></i> 04 Maret 2026
                        </button>
                        <button class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 bg-white rounded-xl text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                            <i class="fas fa-download text-gray-500 text-[12px]"></i> Export
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Total Mahasiswa</p>
                        <p class="text-2xl font-bold text-gray-900">7</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Hadir</p>
                        <p class="text-2xl font-bold text-green-500">5</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Terlambat</p>
                        <p class="text-2xl font-bold text-amber-500">1</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Tidak Hadir</p>
                        <p class="text-2xl font-bold text-red-500">1</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-500 mb-1">Jurnal Disetujui</p>
                        <p class="text-2xl font-bold text-blue-600">3/7</p>
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
                                <?php
                                $presensiData = [
                                    ['nama' => 'Balmond', 'nim' => '21173431', 'perusahaan' => 'PT Telkom Indonesia',  'checkin' => '08:02', 'checkout' => '16:55', 'status' => 'Hadir'],
                                    ['nama' => 'Lesley',  'nim' => '20193432', 'perusahaan' => 'CV Digital Kreatif',   'checkin' => '07:58', 'checkout' => '17:05', 'status' => 'Hadir'],
                                    ['nama' => 'Harley',  'nim' => '22123532', 'perusahaan' => 'PT Bank BRI',          'checkin' => '08:15', 'checkout' => '-',      'status' => 'Hadir'],
                                    ['nama' => 'Budi Santoso', 'nim' => '21140024', 'perusahaan' => 'PT Astra International', 'checkin' => '-', 'checkout' => '-',   'status' => 'Tidak Hadir'],
                                    ['nama' => 'Joko',    'nim' => '21130003', 'perusahaan' => 'PT Tokopedia',         'checkin' => '08:05', 'checkout' => '16:30', 'status' => 'Hadir'],
                                    ['nama' => 'Meks',    'nim' => '22130043', 'perusahaan' => 'PT Gojek Indonesia',   'checkin' => '09:10', 'checkout' => '-',      'status' => 'Terlambat'],
                                    ['nama' => 'Nana',    'nim' => '21130043', 'perusahaan' => 'PT Bukalapak',         'checkin' => '07:50', 'checkout' => '17:00', 'status' => 'Hadir'],
                                ];
                                $statusColors = [
                                    'Hadir'       => 'bg-green-100 text-green-700',
                                    'Tidak Hadir' => 'bg-red-100 text-red-600',
                                    'Terlambat'   => 'bg-amber-100 text-amber-600',
                                ];
                                foreach ($presensiData as $d):
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors mhs-row" data-search="<?= strtolower($d['nama'] . ' ' . $d['nim']) ?>">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($d['nama']) ?></p>
                                        <p class="text-[12px] text-gray-400"><?= $d['nim'] ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-[13px] text-gray-600"><?= htmlspecialchars($d['perusahaan']) ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-700 font-medium"><?= $d['checkin'] ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-700 font-medium"><?= $d['checkout'] ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $statusColors[$d['status']] ?>"><?= $d['status'] ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                                    <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php
                                $jurnalData = [
                                    ['nama' => 'Balmond', 'nim' => '21173431', 'judul' => 'Konfigurasi Jaringan Server', 'tanggal' => '04 Mar 2026', 'status' => 'Disetujui'],
                                    ['nama' => 'Lesley',  'nim' => '20193432', 'judul' => 'Desain UI Dashboard Marketing', 'tanggal' => '04 Mar 2026', 'status' => 'Disetujui'],
                                    ['nama' => 'Harley',  'nim' => '22123532', 'judul' => 'Analisis Keamanan Sistem Bank', 'tanggal' => '04 Mar 2026', 'status' => 'Menunggu'],
                                    ['nama' => 'Joko',    'nim' => '21130003', 'judul' => 'Pengembangan API E-Commerce', 'tanggal' => '04 Mar 2026', 'status' => 'Disetujui'],
                                    ['nama' => 'Meks',    'nim' => '22130043', 'judul' => 'Testing Fitur Ride-Hailing', 'tanggal' => '04 Mar 2026', 'status' => 'Menunggu'],
                                    ['nama' => 'Nana',    'nim' => '21130043', 'judul' => 'Manajemen Database Penjual', 'tanggal' => '04 Mar 2026', 'status' => 'Menunggu'],
                                ];
                                $jurnalColors = [
                                    'Disetujui' => 'bg-green-100 text-green-700',
                                    'Menunggu'  => 'bg-amber-100 text-amber-600',
                                    'Ditolak'   => 'bg-red-100 text-red-600',
                                ];
                                foreach ($jurnalData as $j):
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($j['nama']) ?></p>
                                        <p class="text-[12px] text-gray-400"><?= $j['nim'] ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-[13px] text-gray-700"><?= htmlspecialchars($j['judul']) ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-500"><?= $j['tanggal'] ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $jurnalColors[$j['status']] ?>"><?= $j['status'] ?></span>
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
            document.querySelectorAll('.mhs-row').forEach(row => {
                row.style.display = row.dataset.search.includes(q) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
