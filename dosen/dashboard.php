<?php
    session_start();
    $role = 'dosen';
    $activePage = 'dashboard';
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
                        <h2 class="text-2xl font-bold mb-2 tracking-tight">Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Dosen') ?>!</h2>
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
                        <p class="text-3xl font-bold text-gray-900">12</p>
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
                        <p class="text-3xl font-bold text-gray-900">7</p>
                        <p class="text-[12px] text-gray-400 mt-1">Menunggu review Anda</p>
                    </div>

                    <!-- Card: Laporan Akhir -->
                    <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center">
                                <i class="fas fa-file-alt text-purple-500 text-[18px]"></i>
                            </div>
                            <span class="text-[11px] font-semibold text-purple-500 bg-purple-50 px-2 py-1 rounded-full">Baru</span>
                        </div>
                        <p class="text-[13px] text-gray-500 mb-1">Laporan Masuk</p>
                        <p class="text-3xl font-bold text-gray-900">4</p>
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
                        <p class="text-3xl font-bold text-gray-900">8</p>
                        <p class="text-[12px] text-gray-400 mt-1">Dari 12 mahasiswa</p>
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
                            <div class="relative">
                                <select id="yearSelect" class="appearance-none bg-white border border-gray-200 text-gray-700 py-1.5 pl-4 pr-10 rounded-lg text-[14px] font-medium outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500 cursor-pointer shadow-sm transition-all">
                                    <option>2024</option>
                                    <option>2023</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
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
                                <span class="font-bold text-gray-800">67%</span>
                            </div>
                            <div class="flex items-center justify-between text-[14px]">
                                <span class="flex items-center gap-2.5 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#fbbf24]"></span> Dalam Proses
                                </span>
                                <span class="font-bold text-gray-800">16%</span>
                            </div>
                            <div class="flex items-center justify-between text-[14px]">
                                <span class="flex items-center gap-2.5 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#ef4444]"></span> Belum Dinilai
                                </span>
                                <span class="font-bold text-gray-800">17%</span>
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
                        <a href="#" class="text-[13px] font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg transition-colors">
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

                                <?php
                                $students = [
                                    ['no'=>1,'nama'=>'Ahmad Fauzi','nim'=>'20210001','instansi'=>'PT. Telkom Indonesia','jurnal'=>28,'hadir'=>92,'status'=>'Dinilai'],
                                    ['no'=>2,'nama'=>'Budi Santoso','nim'=>'20210002','instansi'=>'CV. Karya Digital','jurnal'=>24,'hadir'=>87,'status'=>'Proses'],
                                    ['no'=>3,'nama'=>'Citra Dewi','nim'=>'20210003','instansi'=>'PT. Bank BRI','jurnal'=>30,'hadir'=>95,'status'=>'Dinilai'],
                                    ['no'=>4,'nama'=>'Deni Setiawan','nim'=>'20210004','instansi'=>'Dinas Kominfo Kota','jurnal'=>15,'hadir'=>76,'status'=>'Belum'],
                                    ['no'=>5,'nama'=>'Eka Rahmawati','nim'=>'20210005','instansi'=>'PT. Indosat Ooredoo','jurnal'=>22,'hadir'=>89,'status'=>'Proses'],
                                ];
                                foreach ($students as $s):
                                    $statusClass = match($s['status']) {
                                        'Dinilai' => 'bg-green-100 text-green-700',
                                        'Proses'  => 'bg-yellow-100 text-yellow-700',
                                        default   => 'bg-red-100 text-red-600',
                                    };
                                    $hadirColor = $s['hadir'] >= 90 ? 'text-green-600' : ($s['hadir'] >= 80 ? 'text-yellow-600' : 'text-red-500');
                                ?>
                                <tr class="student-row transition-colors">
                                    <td class="px-6 py-4 text-gray-400"><?= $s['no'] ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-[12px] shrink-0">
                                                <?= strtoupper(substr($s['nama'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($s['nama']) ?></p>
                                                <p class="text-[12px] text-gray-400"><?= $s['nim'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($s['instansi']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-800"><?= $s['jurnal'] ?></span>
                                        <span class="text-gray-400"> jurnal</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold <?= $hadirColor ?>"><?= $s['hadir'] ?>%</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $statusClass ?>">
                                            <?= $s['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="#" class="text-blue-600 hover:text-blue-800 text-[13px] font-medium hover:underline">Detail</a>
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

    <!-- Chart Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Bar Chart: Aktivitas Jurnal per Bulan
            const barCtx = document.getElementById('barChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [
                        {
                            label: 'Jurnal Masuk',
                            data: [45, 52, 60, 58, 72, 68, 80, 85, 78, 88, 92, 95],
                            backgroundColor: '#3b66f5',
                            hoverBackgroundColor: '#2350d4',
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.75
                        },
                        {
                            label: 'Sudah Direview',
                            data: [38, 44, 55, 50, 65, 60, 74, 79, 70, 80, 85, 88],
                            backgroundColor: '#10b981',
                            hoverBackgroundColor: '#059669',
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.75
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                borderRadius: 5,
                                useBorderRadius: true,
                                font: { family: "'Inter', sans-serif", size: 12 },
                                color: '#6b7280',
                                padding: 16
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17,24,39,0.9)',
                            padding: 12,
                            titleFont: { family: "'Inter', sans-serif", size: 13, weight: '600' },
                            bodyFont: { family: "'Inter', sans-serif", size: 13 },
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 110,
                            ticks: {
                                stepSize: 20,
                                color: '#9ca3af',
                                font: { family: "'Inter', sans-serif", size: 11 },
                                padding: 10
                            },
                            grid: {
                                color: '#e5e7eb',
                                drawBorder: false,
                                borderDash: [5, 5]
                            },
                            border: { display: false }
                        },
                        x: {
                            ticks: {
                                color: '#9ca3af',
                                font: { family: "'Inter', sans-serif", size: 11 },
                                padding: 8
                            },
                            grid: { display: false, drawBorder: false },
                            border: { display: false }
                        }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });

            // Donut Chart: Status Penilaian
            const donutCtx = document.getElementById('donutChart').getContext('2d');
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Sudah Dinilai', 'Dalam Proses', 'Belum Dinilai'],
                    datasets: [{
                        data: [8, 2, 2],
                        backgroundColor: ['#10b981', '#fbbf24', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    layout: { padding: 10 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17,24,39,0.9)',
                            padding: 12,
                            titleFont: { family: "'Inter', sans-serif", size: 13, weight: '600' },
                            bodyFont: { family: "'Inter', sans-serif", size: 13 },
                            cornerRadius: 8,
                            callbacks: {
                                label: function (ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = Math.round(ctx.parsed / total * 100);
                                    return ` ${ctx.label}: ${ctx.parsed} mhs (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });

        });
    </script>
</body>
</html>
