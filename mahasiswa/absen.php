<?php
    session_start();
    require_once '../config/db_connect.php';

    if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'mahasiswa') {
        header('Location: ../index.php'); exit();
    }

    $role = 'mahasiswa';
    $activePage = 'absen';
    $id_user  = (int) $_SESSION['id_user'];
    $userName = $_SESSION['nama'] ?? 'Mahasiswa';

    // Ambil riwayat absensi
    $stmt = $conn->prepare("
        SELECT tanggal, keterangan, waktu_masuk, waktu_keluar
        FROM Attendances
        WHERE id_user = :id
        ORDER BY tanggal DESC
    ");
    $stmt->execute([':id' => $id_user]);
    $absenList = $stmt->fetchAll();

    $totalAbsen = count($absenList);
    $totalHadir = count(array_filter($absenList, fn($a) => $a['keterangan'] === 'Hadir'));
    $totalIzin  = count(array_filter($absenList, fn($a) => in_array($a['keterangan'], ['Izin', 'Sakit'])));
    $totalAlpha = count(array_filter($absenList, fn($a) => $a['keterangan'] === 'Alpha'));

    // Cek apakah sudah absen hari ini
    $stmtCek = $conn->prepare(
        "SELECT id_attendance FROM Attendances WHERE id_user = :id AND tanggal = :tgl LIMIT 1"
    );
    $stmtCek->execute([':id' => $id_user, ':tgl' => date('Y-m-d')]);
    $sudahAbsen = (bool) $stmtCek->fetch();
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

                <!-- Notifikasi -->
                <?php if (isset($_GET['success'])): ?>
                    <div id="notifBox" class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-[13px] font-medium">
                        <i class="fas fa-check-circle text-green-500"></i> Absensi berhasil dicatat untuk hari ini.
                        <button onclick="document.getElementById('notifBox').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
                    </div>
                <?php elseif (isset($_GET['error'])): ?>
                    <?php $errMap = ['sudah_absen'=>'Anda sudah absen hari ini.','db_error'=>'Terjadi kesalahan database.']; ?>
                    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-[13px] font-medium">
                        <i class="fas fa-exclamation-circle text-red-500"></i> <?= htmlspecialchars($errMap[$_GET['error']] ?? 'Terjadi kesalahan.') ?>
                    </div>
                <?php endif; ?>

                <!-- 4 Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-[#e0e7ff] rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <p class="text-[#3b82f6] text-[13px] font-semibold mb-1">Total Absensi</p>
                            <h3 class="text-[#1e3a8a] text-2xl font-bold"><?= $totalAbsen ?> Kali</h3>
                        </div>
                        <div class="w-10 h-10 bg-[#bfdbfe] rounded-lg flex items-center justify-center text-[#2563eb]">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                    <div class="bg-[#d1fae5] rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <p class="text-[#10b981] text-[13px] font-semibold mb-1">Total Hadir</p>
                            <h3 class="text-[#064e3b] text-2xl font-bold"><?= $totalHadir ?> Kali</h3>
                        </div>
                        <div class="w-10 h-10 bg-[#a7f3d0] rounded-lg flex items-center justify-center text-[#059669]">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="bg-[#ffedd5] rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <p class="text-[#f97316] text-[13px] font-semibold mb-1">Total Izin &amp; Sakit</p>
                            <h3 class="text-[#7c2d12] text-2xl font-bold"><?= $totalIzin ?> Kali</h3>
                        </div>
                        <div class="w-10 h-10 bg-[#fed7aa] rounded-lg flex items-center justify-center text-[#ea580c]">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="bg-[#ffe4e6] rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <p class="text-[#e11d48] text-[13px] font-semibold mb-1">Total Alpha</p>
                            <h3 class="text-[#881337] text-2xl font-bold"><?= $totalAlpha ?> Kali</h3>
                        </div>
                        <div class="w-10 h-10 bg-[#fecdd3] rounded-lg flex items-center justify-center text-[#be123c]">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <?php if ($sudahAbsen): ?>
                        <button disabled class="bg-gray-300 text-gray-500 px-6 py-2 rounded-md font-medium text-[14px] cursor-not-allowed shadow-sm">
                            <i class="fas fa-check mr-1"></i> Sudah Absen
                        </button>
                    <?php else: ?>
                        <form action="../proses/absen_checkin.php" method="POST">
                            <button type="submit" class="bg-[#10b981] hover:bg-[#059669] text-white px-6 py-2 rounded-md font-medium text-[14px] transition-colors shadow-sm">
                                <i class="fas fa-fingerprint mr-1"></i> Absen Sekarang
                            </button>
                        </form>
                    <?php endif; ?>
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
                                <?php if (empty($absenList)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                            <i class="fas fa-clipboard-list text-3xl mb-3 block"></i>
                                            Belum ada data absensi.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($absenList as $a):
                                        $ket = $a['keterangan'];
                                        $ketColor = match($ket) {
                                            'Hadir' => 'text-green-600',
                                            'Izin','Sakit' => 'text-orange-500',
                                            'Alpha' => 'text-red-500',
                                            default => 'text-gray-500',
                                        };
                                    ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-5 text-gray-800 text-left"><?= htmlspecialchars($userName) ?></td>
                                        <td class="px-6 py-5 text-gray-500"><?= $a['tanggal'] ?></td>
                                        <td class="px-6 py-5 font-semibold <?= $ketColor ?>"><?= $ket ?></td>
                                        <td class="px-6 py-5"><?= $a['waktu_masuk'] ?? '-' ?></td>
                                        <td class="px-6 py-5 text-gray-400">-</td>
                                        <td class="px-6 py-5 text-gray-400">-</td>
                                        <td class="px-6 py-5 <?= $a['waktu_keluar'] ? 'text-[#10b981] font-medium' : 'text-gray-400' ?>"><?= $a['waktu_keluar'] ?? '-' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
