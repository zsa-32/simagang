<?php
    session_start();
    $role = 'mahasiswa';
    $activePage = 'absen';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
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
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-white">
            <div class="max-w-[1200px] mx-auto space-y-6">
                
                <!-- Page Banner -->
                <div class="bg-[#2563eb] rounded-xl p-8 flex items-center gap-6 shadow-sm overflow-hidden relative">
                    <div class="text-white z-10 flex gap-4 items-start w-full">
                        <div class="mt-1">
                            <i class="fas fa-clipboard-list text-white text-[32px] drop-shadow-md"></i>
                        </div>
                        <div>
                            <h2 class="text-[22px] font-bold mb-2 tracking-tight leading-tight max-w-2xl">Optimalkan Kehadiran dan Kedisiplinan Siswa: Inovasi Terkini dalam Manajemen Absensi</h2>
                            <p class="text-blue-200 text-[13px] flex items-center gap-2">
                                Dashboard <span class="w-1 h-1 rounded-full bg-blue-200"></span> Absensi
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 4 Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Absensi -->
                    <div class="bg-[#e0e7ff] rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <p class="text-[#3b82f6] text-[13px] font-semibold mb-1">Total Absensi</p>
                            <h3 class="text-[#1e3a8a] text-2xl font-bold">111 Kali</h3>
                        </div>
                        <div class="w-10 h-10 bg-[#bfdbfe] rounded-lg flex items-center justify-center text-[#2563eb]">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                    
                    <!-- Total Hadir -->
                    <div class="bg-[#d1fae5] rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <p class="text-[#10b981] text-[13px] font-semibold mb-1">Total Hadir</p>
                            <h3 class="text-[#064e3b] text-2xl font-bold">111 Kali</h3>
                        </div>
                        <div class="w-10 h-10 bg-[#a7f3d0] rounded-lg flex items-center justify-center text-[#059669]">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>

                    <!-- Total Izin & Sakit -->
                    <div class="bg-[#ffedd5] rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <p class="text-[#f97316] text-[13px] font-semibold mb-1">Total Izin & Sakit</p>
                            <h3 class="text-[#7c2d12] text-2xl font-bold">0 Kali</h3>
                        </div>
                        <div class="w-10 h-10 bg-[#fed7aa] rounded-lg flex items-center justify-center text-[#ea580c]">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>

                    <!-- Total Alpha -->
                    <div class="bg-[#ffe4e6] rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <p class="text-[#e11d48] text-[13px] font-semibold mb-1">Total Alpha</p>
                            <h3 class="text-[#881337] text-2xl font-bold">0 Kali</h3>
                        </div>
                        <div class="w-10 h-10 bg-[#fecdd3] rounded-lg flex items-center justify-center text-[#be123c]">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button class="bg-[#10b981] hover:bg-[#059669] text-white px-6 py-2 rounded-md font-medium text-[14px] transition-colors shadow-sm">
                        Absen
                    </button>
                    <a href="ijinabsen.php"
                       class="bg-[#3b82f6] hover:bg-[#2563eb] text-white px-5 py-2 rounded-md font-medium text-[14px] transition-colors shadow-sm flex items-center gap-2">
                        <i class="fas fa-plus text-[12px]"></i> Buat Izin
                    </a>
                </div>

                <!-- Table Container -->
                <div class="bg-white border rounded-xl overflow-hidden mt-6 mb-8 border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-center border-collapse text-[14px]">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-100 text-gray-500 text-[13px]">
                                    <th class="px-6 py-5 font-semibold text-left">Nama</th>
                                    <th class="px-6 py-5 font-semibold">Tanggal</th>
                                    <th class="px-6 py-5 font-semibold">Keterangan</th>
                                    <th class="px-6 py-5 font-semibold">Masuk</th>
                                    <th class="px-6 py-5 font-semibold">Istirahat</th>
                                    <th class="px-6 py-5 font-semibold">Kembali</th>
                                    <th class="px-6 py-5 font-semibold">Pulang</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 divide-y divide-gray-100">
                                
                                <!-- Row 1 -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-5 text-gray-800 text-left">Ahmad Rizki</td>
                                    <td class="px-6 py-5 text-gray-500">2024-01-15</td>
                                    <td class="px-6 py-5">Hadir</td>
                                    <td class="px-6 py-5">23:52</td>
                                    <td class="px-6 py-5">12:00</td>
                                    <td class="px-6 py-5">13:00</td>
                                    <td class="px-6 py-5 text-[#10b981] font-medium">10:29</td>
                                </tr>

                                <!-- Row 2 -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-5 text-gray-800 text-left">Ahmad Rizki</td>
                                    <td class="px-6 py-5 text-gray-500">2024-01-16</td>
                                    <td class="px-6 py-5">Hadir</td>
                                    <td class="px-6 py-5">07:45</td>
                                    <td class="px-6 py-5">12:00</td>
                                    <td class="px-6 py-5">13:00</td>
                                    <td class="px-6 py-5 text-[#10b981] font-medium">15:30</td>
                                </tr>

                                <!-- Row 3 (Izin) -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-5 text-gray-800 text-left">Ahmad Rizki</td>
                                    <td class="px-6 py-5 text-gray-500">2024-01-17</td>
                                    <td class="px-6 py-5">Izin</td>
                                    <td class="px-6 py-5 text-gray-400">-</td>
                                    <td class="px-6 py-5 text-gray-400">-</td>
                                    <td class="px-6 py-5 text-gray-400">-</td>
                                    <td class="px-6 py-5 text-gray-400">-</td>
                                </tr>

                                <!-- Row 4 -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-5 text-gray-800 text-left">Ahmad Rizki</td>
                                    <td class="px-6 py-5 text-gray-500">2024-01-18</td>
                                    <td class="px-6 py-5">Hadir</td>
                                    <td class="px-6 py-5">08:15</td>
                                    <td class="px-6 py-5">12:00</td>
                                    <td class="px-6 py-5">13:00</td>
                                    <td class="px-6 py-5 text-[#10b981] font-medium">15:45</td>
                                </tr>

                                <!-- Row 5 -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-5 text-gray-800 text-left">Ahmad Rizki</td>
                                    <td class="px-6 py-5 text-gray-500">2024-01-19</td>
                                    <td class="px-6 py-5">Hadir</td>
                                    <td class="px-6 py-5">07:30</td>
                                    <td class="px-6 py-5">12:00</td>
                                    <td class="px-6 py-5">13:00</td>
                                    <td class="px-6 py-5 text-[#10b981] font-medium">15:20</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Download PDF Button -->
                <div class="flex justify-end pb-8">
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-md font-medium text-[14px] transition-colors shadow-sm flex items-center gap-2">
                        <i class="fas fa-download text-[13px]"></i> Download PDF
                    </button>
                </div>

            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

</body>
</html>
