<?php
    session_start();
    $role = 'mahasiswa';
    $activePage = 'jurnal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Jurnal - Magang TIF</title>
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
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">
                
                <!-- Page Banner -->
                <div class="bg-[#3b66f5] rounded-2xl p-8 flex items-center gap-6 shadow-sm overflow-hidden relative">
                    <div class="w-[72px] h-[72px] bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-inner shrink-0 z-10 border border-white/10">
                        <i class="fas fa-book text-white text-[28px] drop-shadow-md"></i>
                    </div>
                    <div class="text-white z-10">
                        <h2 class="text-[26px] font-bold mb-1.5 tracking-tight">Data Jurnal</h2>
                        <p class="text-blue-100 text-[15px]">Kelola dan pantau jurnal kegiatan magang siswa</p>
                    </div>
                    <!-- Decorative Background Overlay -->
                    <div class="absolute right-0 top-0 w-96 h-full bg-gradient-to-l from-[#254bdb] to-transparent z-0 opacity-80"></div>
                </div>

                <!-- Table Card Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col overflow-hidden">
                    
                    <!-- Card Header / Toolbar -->
                    <div class="p-6 md:px-8 md:py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-[18px] font-bold text-gray-800">Daftar Jurnal Kegiatan</h3>
                            <p class="text-[13px] text-gray-500 mt-1">Kelola data jurnal harian siswa magang</p>
                        </div>
                        <a href="tambahjurnal.php"
                           class="self-start sm:self-auto bg-[#2563eb] hover:bg-[#1d4ed8] text-white px-5 py-2.5 rounded-[10px] flex items-center gap-2 font-medium text-[14px] transition-colors shadow-sm hover:shadow-md">
                            <i class="fas fa-plus text-[13px]"></i> Tambah
                        </a>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="bg-white border-b border-gray-200 text-gray-500 text-[12px] uppercase tracking-wider">
                                    <th class="px-8 py-5 font-semibold w-20">No</th>
                                    <th class="px-6 py-5 font-semibold w-36 text-center">Tanggal</th>
                                    <th class="px-6 py-5 font-semibold w-64">Judul</th>
                                    <th class="px-6 py-5 font-semibold text-center w-24">Bukti</th>
                                    <th class="px-6 py-5 font-semibold">Kegiatan</th>
                                    <th class="px-8 py-5 font-semibold text-center w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-[14px] text-gray-600 divide-y divide-gray-100">
                                
                                <!-- Row 1 -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-5 lg:py-6 text-gray-500">1</td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <div class="font-medium text-gray-800 text-[14px]">15 Jan</div>
                                            <div class="text-[12px] text-gray-500 mt-0.5">2024</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 font-semibold text-gray-800 text-[14px]">Orientasi Perusahaan</td>
                                    <td class="px-6 py-5">
                                        <div class="w-11 h-11 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 mx-auto border border-gray-200/60">
                                            <i class="fas fa-image text-[16px]"></i>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-[13.5px] leading-relaxed text-gray-500 pr-12">
                                        Mengikuti orientasi perusahaan dan pengenalan lingkungan kerja. Mempelajari budaya perusahaan dan struktur organisasi.
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <button title="Lihat Detail" class="text-blue-600 hover:text-blue-800 bg-blue-50/50 hover:bg-blue-100 border border-blue-100 p-2.5 rounded-[8px] transition-all dropdown-toggle focus:ring-2 focus:ring-blue-100 focus:outline-none">
                                            <i class="fas fa-eye text-[14px]"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Row 2 -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-5 lg:py-6 text-gray-500">2</td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <div class="font-medium text-gray-800 text-[14px]">16 Jan</div>
                                            <div class="text-[12px] text-gray-500 mt-0.5">2024</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 font-semibold text-gray-800 text-[14px]">Training Dasar IT</td>
                                    <td class="px-6 py-5">
                                        <div class="w-11 h-11 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 mx-auto border border-gray-200/60">
                                            <i class="fas fa-image text-[16px]"></i>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-[13.5px] leading-relaxed text-gray-500 pr-12">
                                        Mengikuti pelatihan dasar penggunaan sistem informasi perusahaan dan tools development yang akan digunakan.
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <button title="Lihat Detail" class="text-blue-600 hover:text-blue-800 bg-blue-50/50 hover:bg-blue-100 border border-blue-100 p-2.5 rounded-[8px] transition-all dropdown-toggle focus:ring-2 focus:ring-blue-100 focus:outline-none">
                                            <i class="fas fa-eye text-[14px]"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Row 3 -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-5 lg:py-6 text-gray-500">3</td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <div class="font-medium text-gray-800 text-[14px]">17 Jan</div>
                                            <div class="text-[12px] text-gray-500 mt-0.5">2024</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 font-semibold text-gray-800 text-[14px]">Observasi Tim Development</td>
                                    <td class="px-6 py-5">
                                        <div class="w-11 h-11 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 mx-auto border border-gray-200/60">
                                            <i class="fas fa-image text-[16px]"></i>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-[13.5px] leading-relaxed text-gray-500 pr-12">
                                        Melakukan observasi terhadap alur kerja tim development dan mempelajari metodologi yang digunakan dalam pengembangan software.
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <button title="Lihat Detail" class="text-blue-600 hover:text-blue-800 bg-blue-50/50 hover:bg-blue-100 border border-blue-100 p-2.5 rounded-[8px] transition-all dropdown-toggle focus:ring-2 focus:ring-blue-100 focus:outline-none">
                                            <i class="fas fa-eye text-[14px]"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Row 4 -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-5 lg:py-6 text-gray-500">4</td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <div class="font-medium text-gray-800 text-[14px]">18 Jan</div>
                                            <div class="text-[12px] text-gray-500 mt-0.5">2024</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 font-semibold text-gray-800 text-[14px]">Praktik Coding HTML/CSS</td>
                                    <td class="px-6 py-5">
                                        <div class="w-11 h-11 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 mx-auto border border-gray-200/60">
                                            <i class="fas fa-image text-[16px]"></i>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-[13.5px] leading-relaxed text-gray-500 pr-12">
                                        Mulai praktik coding dengan membuat halaman web sederhana menggunakan HTML dan CSS sesuai dengan panduan mentor.
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <button title="Lihat Detail" class="text-blue-600 hover:text-blue-800 bg-blue-50/50 hover:bg-blue-100 border border-blue-100 p-2.5 rounded-[8px] transition-all dropdown-toggle focus:ring-2 focus:ring-blue-100 focus:outline-none">
                                            <i class="fas fa-eye text-[14px]"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Row 5 -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-5 lg:py-6 text-gray-500">5</td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <div class="font-medium text-gray-800 text-[14px]">19 Jan</div>
                                            <div class="text-[12px] text-gray-500 mt-0.5">2024</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 font-semibold text-gray-800 text-[14px]">Review dan Feedback</td>
                                    <td class="px-6 py-5">
                                        <div class="w-11 h-11 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 mx-auto border border-gray-200/60">
                                            <i class="fas fa-image text-[16px]"></i>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-[13.5px] leading-relaxed text-gray-500 pr-12">
                                        Sesi review hasil kerja minggu pertama dengan mentor dan mendapat feedback untuk perbaikan selanjutnya.
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <button title="Lihat Detail" class="text-blue-600 hover:text-blue-800 bg-blue-50/50 hover:bg-blue-100 border border-blue-100 p-2.5 rounded-[8px] transition-all dropdown-toggle focus:ring-2 focus:ring-blue-100 focus:outline-none">
                                            <i class="fas fa-eye text-[14px]"></i>
                                        </button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="px-6 py-5 md:px-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50/50 rounded-b-2xl">
                        <div class="text-[13px] text-gray-500 text-center sm:text-left">
                            Menampilkan <span class="font-semibold text-gray-700">1</span> sampai <span class="font-semibold text-gray-700">5</span> dari <span class="font-semibold text-gray-700">25</span> hasil
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button class="px-3.5 py-1.5 border border-gray-200 rounded-lg text-[13px] font-medium text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 transition-colors shadow-sm">Previous</button>
                            <button class="w-8 h-8 border border-[#3b82f6] bg-[#3b82f6] text-white rounded-lg text-[13px] font-medium flex items-center justify-center shadow-sm">1</button>
                            <button class="w-8 h-8 border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-[#3b82f6] rounded-lg text-[13px] font-medium flex items-center justify-center transition-colors shadow-sm">2</button>
                            <button class="w-8 h-8 border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-[#3b82f6] rounded-lg text-[13px] font-medium flex items-center justify-center transition-colors shadow-sm">3</button>
                            <button class="px-3.5 py-1.5 border border-gray-200 rounded-lg text-[13px] font-medium text-gray-600 bg-white hover:bg-gray-50 hover:text-gray-700 transition-colors shadow-sm">Next</button>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

</body>
</html>
