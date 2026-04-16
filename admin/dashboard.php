<?php
session_start();
$role = 'admin';
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Magang TIF</title>
    <meta name="description" content="Dashboard Admin Sistem Manajemen Magang TIF - Overview penuh data mahasiswa, dosen, dan perusahaan mitra.">
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

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">

                <!-- Page Heading -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Selamat datang kembali, Admin</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Mahasiswa -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-start justify-between">
                        <div>
                            <p class="text-[13px] text-gray-500 font-medium mb-1">Total Mahasiswa Magang</p>
                            <p class="text-3xl font-bold text-gray-900">248</p>
                            <p class="text-[12px] text-green-500 font-medium mt-1.5 flex items-center gap-1">
                                <i class="fas fa-arrow-trend-up text-[11px]"></i> +12 dari bulan lalu
                            </p>
                        </div>
                        <div class="bg-blue-100 text-blue-600 w-11 h-11 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fas fa-graduation-cap text-lg"></i>
                        </div>
                    </div>
                    <!-- Dosen Pembimbing -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-start justify-between">
                        <div>
                            <p class="text-[13px] text-gray-500 font-medium mb-1">Dosen Pembimbing</p>
                            <p class="text-3xl font-bold text-gray-900">32</p>
                            <p class="text-[12px] text-green-500 font-medium mt-1.5 flex items-center gap-1">
                                <i class="fas fa-arrow-trend-up text-[11px]"></i> +2 dari bulan lalu
                            </p>
                        </div>
                        <div class="bg-blue-100 text-blue-600 w-11 h-11 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fas fa-chalkboard-teacher text-lg"></i>
                        </div>
                    </div>
                    <!-- Perusahaan Mitra -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-start justify-between">
                        <div>
                            <p class="text-[13px] text-gray-500 font-medium mb-1">Perusahaan Mitra</p>
                            <p class="text-3xl font-bold text-gray-900">45</p>
                            <p class="text-[12px] text-green-500 font-medium mt-1.5 flex items-center gap-1">
                                <i class="fas fa-arrow-trend-up text-[11px]"></i> +5 dari bulan lalu
                            </p>
                        </div>
                        <div class="bg-blue-100 text-blue-600 w-11 h-11 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fas fa-building text-lg"></i>
                        </div>
                    </div>
                    <!-- Magang Selesai -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-start justify-between">
                        <div>
                            <p class="text-[13px] text-gray-500 font-medium mb-1">Magang Selesai</p>
                            <p class="text-3xl font-bold text-gray-900">198</p>
                            <p class="text-[12px] text-green-500 font-medium mt-1.5 flex items-center gap-1">
                                <i class="fas fa-arrow-trend-up text-[11px]"></i> 80% dari bulan lalu
                            </p>
                        </div>
                        <div class="bg-blue-100 text-blue-600 w-11 h-11 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fas fa-circle-check text-lg"></i>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Line Chart: Progres Magang Per Bulan -->
                    <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-[17px] font-bold text-gray-800">Progres Magang Per Bulan</h3>
                            <span class="text-[12px] text-gray-400 bg-gray-100 px-3 py-1 rounded-full">Periode 2025/2026</span>
                        </div>
                        <!-- Legend -->
                        <div class="flex items-center gap-4 mb-4">
                            <span class="flex items-center gap-1.5 text-[12px] text-gray-500">
                                <span class="w-8 h-[2px] bg-blue-500 inline-block rounded"></span> Total Mahasiswa
                            </span>
                            <span class="flex items-center gap-1.5 text-[12px] text-gray-500">
                                <span class="w-8 h-[2px] bg-green-400 inline-block rounded"></span> Selesai
                            </span>
                        </div>
                        <div class="relative w-full h-[250px]">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>

                    <!-- Donut Chart: Status Magang -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col">
                        <h3 class="text-[17px] font-bold text-gray-800 mb-4">Status Magang</h3>

                        <div class="relative flex-1 flex justify-center items-center min-h-[180px]">
                            <canvas id="donutChart"></canvas>
                        </div>

                        <div class="space-y-3 mt-4">
                            <div class="flex items-center justify-between text-[13px]">
                                <span class="flex items-center gap-2 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span> Selesai
                                </span>
                                <span class="font-bold text-gray-800">198</span>
                            </div>
                            <div class="flex items-center justify-between text-[13px]">
                                <span class="flex items-center gap-2 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></span> Berlangsung
                                </span>
                                <span class="font-bold text-gray-800">38</span>
                            </div>
                            <div class="flex items-center justify-between text-[13px]">
                                <span class="flex items-center gap-2 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></span> Belum Mulai
                                </span>
                                <span class="font-bold text-gray-800">12</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Bar Chart: Distribusi per Perusahaan -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-[17px] font-bold text-gray-800 mb-6">Distribusi Mahasiswa per Perusahaan</h3>
                        <div class="relative w-full h-[220px]">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>

                    <!-- Perlu Perhatian -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-[17px] font-bold text-gray-800 mb-5 flex items-center gap-2">
                            <i class="fas fa-circle-info text-amber-400"></i> Perlu Perhatian
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-3.5 bg-red-50 rounded-xl border border-red-100">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 mt-1 shrink-0"></span>
                                <p class="text-[13px] text-gray-700">12 jurnal mahasiswa menunggu persetujuan PL</p>
                            </div>
                            <div class="flex items-start gap-3 p-3.5 bg-amber-50 rounded-xl border border-amber-100">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 mt-1 shrink-0"></span>
                                <p class="text-[13px] text-gray-700">5 dosen belum menginput nilai bimbingan</p>
                            </div>
                            <div class="flex items-start gap-3 p-3.5 bg-orange-50 rounded-xl border border-orange-100">
                                <span class="w-2.5 h-2.5 rounded-full bg-orange-400 mt-1 shrink-0"></span>
                                <p class="text-[13px] text-gray-700">3 mahasiswa belum presensi hari ini</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Line Chart
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul'],
                datasets: [
                    {
                        label: 'Total Mahasiswa',
                        data: [10, 45, 90, 120, 160, 210, 248],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.08)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 5,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7,
                    },
                    {
                        label: 'Selesai',
                        data: [0, 5, 20, 50, 90, 140, 198],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.06)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 5,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { family: "'Inter', sans-serif", size: 12 },
                        bodyFont: { family: "'Inter', sans-serif", size: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 260,
                        ticks: { stepSize: 65, color: '#9ca3af', font: { family: "'Inter', sans-serif", size: 11 }, padding: 10 },
                        grid: { color: '#e5e7eb', borderDash: [5,5] },
                        border: { display: false }
                    },
                    x: {
                        ticks: { color: '#9ca3af', font: { family: "'Inter', sans-serif", size: 11 }, padding: 8 },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });

        // Donut Chart
        const donutCtx = document.getElementById('donutChart').getContext('2d');
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Berlangsung', 'Belum Mulai'],
                datasets: [{
                    data: [198, 38, 12],
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.parsed}`;
                            }
                        }
                    }
                }
            }
        });

        // Bar Chart (horizontal)
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['PT Telkom', 'PT PLN', 'Gojek', 'Tokopedia', 'Bukalapak', 'Traveloka'],
                datasets: [{
                    label: 'Mahasiswa',
                    data: [28, 22, 18, 15, 10, 9],
                    backgroundColor: '#3b82f6',
                    hoverBackgroundColor: '#2563eb',
                    borderRadius: 4,
                    barPercentage: 0.55,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.parsed.x} mahasiswa`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 30,
                        ticks: { stepSize: 7, color: '#9ca3af', font: { family: "'Inter', sans-serif", size: 11 }, padding: 8 },
                        grid: { color: '#e5e7eb', borderDash: [5,5] },
                        border: { display: false }
                    },
                    y: {
                        ticks: { color: '#6b7280', font: { family: "'Inter', sans-serif", size: 12 }, padding: 8 },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    });
    </script>
</body>
</html>
