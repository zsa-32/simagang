<?php
session_start();
$role = 'pembimbing';
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pembimbing Lapang | Magang TIF</title>
    <meta name="description" content="Dashboard Pembimbing Lapang Sistem Manajemen Magang TIF.">
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
            <div class="max-w-[1200px] mx-auto space-y-6">

                <!-- Page Heading -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Selamat datang di Sistem Manajemen Magang TIF</p>
                </div>

                <!-- Stat Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                    <!-- Total Mahasiswa -->
                    <a href="monitoring_mahasiswa.php"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <i class="fas fa-user-graduate text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[13px] text-gray-500 font-medium">Total Mahasiswa</p>
                                <p class="text-3xl font-bold text-gray-900 mt-0.5">7</p>
                            </div>
                        </div>
                        <p class="text-[13px] text-blue-600 font-medium">
                            Lihat detail monitoring <i class="fas fa-arrow-right text-[11px] ml-1"></i>
                        </p>
                    </a>

                    <!-- Presensi Hari Ini -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-green-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-check-circle text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[13px] text-gray-500 font-medium">Presensi Hari Ini</p>
                                <p class="text-3xl font-bold text-gray-900 mt-0.5">5/7</p>
                            </div>
                        </div>
                        <p class="text-[13px] text-green-600 font-medium">Kehadiran baik</p>
                    </div>

                    <!-- Jurnal Menunggu -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-clipboard-list text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[13px] text-gray-500 font-medium">Jurnal Menunggu</p>
                                <p class="text-3xl font-bold text-gray-900 mt-0.5">4</p>
                            </div>
                        </div>
                        <p class="text-[13px] text-amber-600 font-medium">Perlu direview</p>
                    </div>

                </div>

            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

</body>

</html>
