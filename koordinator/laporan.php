<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole('koordinator');
require_once __DIR__ . '/../config/db_connect.php';

$role = 'koordinator';
$activePage = 'laporan';

// Bottom Stats Queries
$totalMahasiswa = (int)$conn->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
$totalLulus = (int)$conn->query("SELECT COUNT(*) FROM mahasiswa WHERE status = 'Selesai'")->fetchColumn();

$stmtStatus = $conn->query("
    SELECT 
        SUM(CASE WHEN status = 'Aktif' OR status = 'Berjalan' THEN 1 ELSE 0 END) as berjalan,
        SUM(CASE WHEN status = 'Bermasalah' THEN 1 ELSE 0 END) as bermasalah
    FROM mahasiswa
");
$statusData = $stmtStatus->fetch();
$countBerjalan = (int)($statusData['berjalan'] ?? 0);
$countBermasalah = (int)($statusData['bermasalah'] ?? 0);

$tingkatKelulusan = $totalMahasiswa > 0 ? round(($totalLulus / $totalMahasiswa) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keseluruhan Sistem | Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../includes/header.php'; ?>
        
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">
                <!-- Header -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Laporan Keseluruhan Sistem</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Export dan generate laporan program magang</p>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    
                    <!-- Laporan Keseluruhan -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-chart-bar text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-gray-900">Laporan Keseluruhan</h3>
                                <p class="text-[12px] text-gray-500 mt-0.5">Rekapitulasi semua data</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <button class="w-full flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-[13px] font-semibold text-gray-700">
                                <i class="fas fa-file-excel text-gray-400 w-4"></i> Download Excel
                            </button>
                            <button class="w-full flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-[13px] font-semibold text-gray-700">
                                <i class="fas fa-file-pdf text-gray-400 w-4"></i> Download PDF
                            </button>
                            <button class="w-full flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-[13px] font-semibold text-gray-700">
                                <i class="fas fa-file-csv text-gray-400 w-4"></i> Download CSV
                            </button>
                        </div>
                    </div>

                    <!-- Laporan Per Peserta -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-user-graduate text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-gray-900">Laporan Per Peserta</h3>
                                <p class="text-[12px] text-gray-500 mt-0.5">Data individual peserta</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <button class="w-full flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-[13px] font-semibold text-gray-700">
                                <i class="fas fa-file-excel text-gray-400 w-4"></i> Download Excel
                            </button>
                            <button class="w-full flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-[13px] font-semibold text-gray-700">
                                <i class="fas fa-file-pdf text-gray-400 w-4"></i> Download PDF
                            </button>
                        </div>
                    </div>

                    <!-- Laporan Per Instansi -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-building text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-gray-900">Laporan Per Instansi</h3>
                                <p class="text-[12px] text-gray-500 mt-0.5">Data per mitra instansi</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <button class="w-full flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-[13px] font-semibold text-gray-700">
                                <i class="fas fa-file-excel text-gray-400 w-4"></i> Download Excel
                            </button>
                            <button class="w-full flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-[13px] font-semibold text-gray-700">
                                <i class="fas fa-file-pdf text-gray-400 w-4"></i> Download PDF
                            </button>
                        </div>
                    </div>

                    <!-- Laporan Per Dosen -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-chalkboard-teacher text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-gray-900">Laporan Per Dosen</h3>
                                <p class="text-[12px] text-gray-500 mt-0.5">Data pembimbingan dosen</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <button class="w-full flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-[13px] font-semibold text-gray-700">
                                <i class="fas fa-file-excel text-gray-400 w-4"></i> Download Excel
                            </button>
                            <button class="w-full flex items-center gap-3 px-4 py-3 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-[13px] font-semibold text-gray-700">
                                <i class="fas fa-file-pdf text-gray-400 w-4"></i> Download PDF
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Bottom Stats -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-[16px] font-bold text-gray-900 mb-5">Statistik Program Magang</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-50">
                            <p class="text-[12px] text-blue-600 font-medium">Total Magang Selesai</p>
                            <p class="text-[15px] font-bold text-gray-900 mt-1"><?= $totalLulus ?> peserta</p>
                        </div>
                        <div class="bg-green-50/50 rounded-xl p-4 border border-green-50">
                            <p class="text-[12px] text-green-600 font-medium">Sedang Berjalan</p>
                            <p class="text-[15px] font-bold text-gray-900 mt-1"><?= $countBerjalan ?> peserta</p>
                        </div>
                        <div class="bg-red-50/50 rounded-xl p-4 border border-red-50">
                            <p class="text-[12px] text-red-600 font-medium">Bermasalah</p>
                            <p class="text-[15px] font-bold text-gray-900 mt-1"><?= $countBermasalah ?> peserta</p>
                        </div>
                        <div class="bg-purple-50/50 rounded-xl p-4 border border-purple-50">
                            <p class="text-[12px] text-purple-600 font-medium">Tingkat Kelulusan</p>
                            <p class="text-[15px] font-bold text-gray-900 mt-1"><?= $tingkatKelulusan ?>%</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>
        
    </div>
</body>
</html>
