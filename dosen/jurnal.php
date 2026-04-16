<?php
    session_start();
    $role = 'dosen';
    $activePage = 'lihat_jurnal';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Jurnal - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .journal-row:hover { background-color: #f9fafb; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Top Page Header Bar -->
        <div class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 md:px-8 shrink-0">
            <h1 class="text-[18px] font-bold text-gray-900">Validasi Jurnal Dosen</h1>
            <div class="flex items-center gap-4">
                <!-- Bell -->
                <button class="relative p-2 rounded-full text-gray-500 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell text-[17px]"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <!-- User Info -->
                <?php include '../includes/header.php'; ?>
            </div>
        </div>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1000px] mx-auto space-y-5">

                <!-- Section Header Card -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-[16px] font-bold text-gray-800">Monitoring Jurnal</h2>
                        <p class="text-[13px] text-gray-400 mt-0.5">Pantau jurnal harian mahasiswa bimbingan</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[13px]"></i>
                            <input type="text" id="searchJurnal" placeholder="Cari mahasiswa atau kegiatan..."
                                   class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 w-64 transition-all">
                        </div>
                        <!-- Filter -->
                        <button class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition-colors text-[13px]">
                            <i class="fas fa-filter text-[12px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Menunggu Review -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-comment-dots text-orange-400 text-[20px]"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500 mb-0.5">Menunggu Review</p>
                            <p class="text-3xl font-bold text-gray-900">7</p>
                        </div>
                    </div>
                    <!-- Disetujui -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-[20px]"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500 mb-0.5">Disetujui</p>
                            <p class="text-3xl font-bold text-gray-900">4</p>
                        </div>
                    </div>
                    <!-- Perlu Revisi -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-times-circle text-red-400 text-[20px]"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500 mb-0.5">Perlu Revisi</p>
                            <p class="text-3xl font-bold text-gray-900">1</p>
                        </div>
                    </div>
                </div>

                <!-- Journal Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-[14px]" id="journalTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-500 text-[12px] uppercase tracking-wider">
                                    <th class="text-left px-6 py-4 font-semibold">Mahasiswa</th>
                                    <th class="text-left px-6 py-4 font-semibold">Tanggal</th>
                                    <th class="text-left px-6 py-4 font-semibold">Kegiatan</th>
                                    <th class="text-left px-6 py-4 font-semibold">Status</th>
                                    <th class="text-left px-6 py-4 font-semibold">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php
                                $journals = [
                                    ['nama'=>'Balmond','nim'=>'211134341','tanggal'=>'2026-03-03','kegiatan'=>'Implementasi fitur login dan registrasi pada sistem','status'=>'Menunggu'],
                                    ['nama'=>'Balmond','nim'=>'211134341','tanggal'=>'2026-03-02','kegiatan'=>'Membuat database schema dan migrasi tabel utama','status'=>'Disetujui'],
                                    ['nama'=>'Lesley','nim'=>'20133432','tanggal'=>'2026-03-03','kegiatan'=>'Desain UI halaman dashboard menggunakan Figma','status'=>'Menunggu'],
                                    ['nama'=>'Lesley','nim'=>'20133432','tanggal'=>'2026-03-02','kegiatan'=>'Riset komponen UI dan best practice untuk frontend','status'=>'Menunggu'],
                                    ['nama'=>'Harley','nim'=>'22123232','tanggal'=>'2026-03-03','kegiatan'=>'Setup CI/CD pipeline untuk project deployment','status'=>'Menunggu'],
                                    ['nama'=>'Budi Santoso','nim'=>'21140004','tanggal'=>'2026-03-03','kegiatan'=>'Testing dan debugging API endpoint modul transaksi','status'=>'Disetujui'],
                                    ['nama'=>'Joko','nim'=>'22130003','tanggal'=>'2026-03-02','kegiatan'=>'Menulis dokumentasi teknis untuk modul pembayaran','status'=>'Revisi'],
                                    ['nama'=>'Figor','nim'=>'22130043','tanggal'=>'2026-03-03','kegiatan'=>'Integrasi API pihak ketiga untuk fitur notifikasi','status'=>'Menunggu'],
                                    ['nama'=>'Meks Panda','nim'=>'22130043','tanggal'=>'2026-03-02','kegiatan'=>'Optimasi query database untuk performa yang lebih baik','status'=>'Disetujui'],
                                    ['nama'=>'Joko','nim'=>'22130003','tanggal'=>'2026-03-03','kegiatan'=>'Revisi dokumentasi sesuai feedback pembimbing','status'=>'Menunggu'],
                                ];
                                foreach ($journals as $j):
                                    $statusClass = match($j['status']) {
                                        'Disetujui' => 'bg-green-100 text-green-700',
                                        'Revisi'    => 'bg-red-100 text-red-600',
                                        default     => 'bg-orange-100 text-orange-600',
                                    };
                                ?>
                                <tr class="journal-row transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-gray-800 flex items-center justify-center text-white font-semibold text-[12px] shrink-0">
                                                <?= strtoupper(substr($j['nama'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($j['nama']) ?></p>
                                                <p class="text-[12px] text-gray-400"><?= $j['nim'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 whitespace-nowrap"><?= $j['tanggal'] ?></td>
                                    <td class="px-6 py-4 text-gray-700 max-w-[260px]">
                                        <span class="line-clamp-1"><?= htmlspecialchars($j['kegiatan']) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $statusClass ?>">
                                            <?= $j['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button class="flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-[13px] font-medium hover:underline transition-colors">
                                            <i class="fas fa-eye text-[12px]"></i> Lihat
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
        // Live search
        document.getElementById('searchJurnal').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#journalTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
