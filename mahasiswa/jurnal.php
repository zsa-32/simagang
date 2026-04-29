<?php
    require_once __DIR__ . "/../config/role_guard.php";
    checkRole("mahasiswa");
    require_once __DIR__ . '/../config/db_connect.php';
    $role = 'mahasiswa';
    $activePage = 'jurnal';

    // ====== DYNAMIC DATA ======
    $userId = $_SESSION['id_user'];
    $stmt = $conn->prepare("SELECT id FROM mahasiswa WHERE user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $mhs = $stmt->fetch();
    $mhsId = $mhs ? $mhs['id'] : 0;

    // Pagination
    $perPage = 5;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $perPage;

    // Count total logbooks
    $stmt = $conn->prepare("SELECT COUNT(*) FROM logbooks WHERE mahasiswa_id = :mid");
    $stmt->execute(['mid' => $mhsId]);
    $totalJurnal = (int)$stmt->fetchColumn();
    $totalPages = max(1, ceil($totalJurnal / $perPage));

    // Fetch logbooks for current page
    $stmt = $conn->prepare("
        SELECT id, tanggal, kegiatan, hasil, dokumentasi, status
        FROM logbooks
        WHERE mahasiswa_id = :mid
        ORDER BY tanggal DESC
        LIMIT :lmt OFFSET :ofs
    ");
    $stmt->bindValue('mid', $mhsId, PDO::PARAM_INT);
    $stmt->bindValue('lmt', $perPage, PDO::PARAM_INT);
    $stmt->bindValue('ofs', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logbooks = $stmt->fetchAll();

    // Handle delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $delId = (int)$_POST['delete_id'];
        $chk = $conn->prepare("SELECT id FROM logbooks WHERE id = :id AND mahasiswa_id = :mid");
        $chk->execute(['id' => $delId, 'mid' => $mhsId]);
        if ($chk->fetch()) {
            $conn->prepare("DELETE FROM logbooks WHERE id = :id")->execute(['id' => $delId]);
        }
        header("Location: jurnal.php?page=$page&deleted=1");
        exit;
    }
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
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <?php include '../includes/header.php'; ?>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">

                <?php if (isset($_GET['deleted'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Jurnal berhasil dihapus.
                </div>
                <?php endif; ?>
                
                <!-- Page Banner -->
                <div class="bg-[#3b66f5] rounded-2xl p-8 flex items-center gap-6 shadow-sm overflow-hidden relative">
                    <div class="w-[72px] h-[72px] bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-inner shrink-0 z-10 border border-white/10">
                        <i class="fas fa-book text-white text-[28px] drop-shadow-md"></i>
                    </div>
                    <div class="text-white z-10">
                        <h2 class="text-[26px] font-bold mb-1.5 tracking-tight">Data Jurnal</h2>
                        <p class="text-blue-100 text-[15px]">Kelola dan pantau jurnal kegiatan magang siswa</p>
                    </div>
                    <div class="absolute right-0 top-0 w-96 h-full bg-gradient-to-l from-[#254bdb] to-transparent z-0 opacity-80"></div>
                </div>

                <!-- Table Card Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col overflow-hidden">
                    
                    <!-- Card Header / Toolbar -->
                    <div class="p-6 md:px-8 md:py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-[18px] font-bold text-gray-800">Daftar Jurnal Kegiatan</h3>
                            <p class="text-[13px] text-gray-500 mt-1">Total: <?= $totalJurnal ?> jurnal</p>
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
                                    <th class="px-6 py-5 font-semibold w-64">Kegiatan</th>
                                    <th class="px-6 py-5 font-semibold text-center w-24">Bukti</th>
                                    <th class="px-6 py-5 font-semibold">Hasil</th>
                                    <th class="px-6 py-5 font-semibold text-center w-24">Status</th>
                                    <th class="px-8 py-5 font-semibold text-center w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-[14px] text-gray-600 divide-y divide-gray-100">
                                <?php if (empty($logbooks)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">Belum ada data jurnal.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($logbooks as $i => $lb):
                                    $tgl = strtotime($lb['tanggal']);
                                    $statusClass = match($lb['status']) {
                                        'approved' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-600',
                                        default => 'bg-yellow-100 text-yellow-700',
                                    };
                                    $statusLabel = match($lb['status']) {
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        default => 'Pending',
                                    };
                                ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-5 lg:py-6 text-gray-500"><?= $offset + $i + 1 ?></td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center justify-center">
                                            <div class="font-medium text-gray-800 text-[14px]"><?= date('d M', $tgl) ?></div>
                                            <div class="text-[12px] text-gray-500 mt-0.5"><?= date('Y', $tgl) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars(mb_strimwidth($lb['kegiatan'] ?? '-', 0, 60, '...')) ?></td>
                                    <td class="px-6 py-5">
                                        <div class="w-11 h-11 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 mx-auto border border-gray-200/60">
                                            <?php if ($lb['dokumentasi']): ?>
                                                <i class="fas fa-image text-[16px] text-blue-400"></i>
                                            <?php else: ?>
                                                <i class="fas fa-image text-[16px]"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-[13.5px] leading-relaxed text-gray-500 pr-12">
                                        <?= htmlspecialchars(mb_strimwidth($lb['hasil'] ?? '-', 0, 80, '...')) ?>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[12px] font-semibold <?= $statusClass ?>"><?= $statusLabel ?></span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button title="Lihat Detail" class="text-blue-600 hover:text-blue-800 bg-blue-50/50 hover:bg-blue-100 border border-blue-100 p-2.5 rounded-[8px] transition-all">
                                                <i class="fas fa-eye text-[14px]"></i>
                                            </button>
                                            <?php if ($lb['status'] === 'pending'): ?>
                                            <form method="POST" onsubmit="return confirm('Hapus jurnal ini?')">
                                                <input type="hidden" name="delete_id" value="<?= $lb['id'] ?>">
                                                <button type="submit" title="Hapus" class="text-red-500 hover:text-red-700 bg-red-50/50 hover:bg-red-100 border border-red-100 p-2.5 rounded-[8px] transition-all">
                                                    <i class="fas fa-trash text-[13px]"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="px-6 py-5 md:px-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50/50 rounded-b-2xl">
                        <div class="text-[13px] text-gray-500 text-center sm:text-left">
                            Menampilkan <span class="font-semibold text-gray-700"><?= $offset + 1 ?></span> sampai <span class="font-semibold text-gray-700"><?= min($offset + $perPage, $totalJurnal) ?></span> dari <span class="font-semibold text-gray-700"><?= $totalJurnal ?></span> hasil
                        </div>
                        <div class="flex items-center gap-1.5">
                            <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="px-3.5 py-1.5 border border-gray-200 rounded-lg text-[13px] font-medium text-gray-500 bg-white hover:bg-gray-50 transition-colors shadow-sm">Previous</a>
                            <?php endif; ?>
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a href="?page=<?= $p ?>" class="w-8 h-8 border rounded-lg text-[13px] font-medium flex items-center justify-center shadow-sm <?= $p === $page ? 'border-[#3b82f6] bg-[#3b82f6] text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-[#3b82f6]' ?>"><?= $p ?></a>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="px-3.5 py-1.5 border border-gray-200 rounded-lg text-[13px] font-medium text-gray-600 bg-white hover:bg-gray-50 transition-colors shadow-sm">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

</body>
</html>
