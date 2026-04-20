<?php
    session_start();
    require_once '../config/db_connect.php';

    if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'mahasiswa') {
        header('Location: ../index.php'); exit();
    }

    $role = 'mahasiswa';
    $activePage = 'jurnal';
    $id_user = (int) $_SESSION['id_user'];
    $userName = $_SESSION['nama'] ?? 'Mahasiswa';

    // Ambil semua jurnal milik mahasiswa ini
    $stmt = $conn->prepare("
        SELECT id_journal, tanggal, kegiatan, bukti, status, catatan_dosen
        FROM Daily_journal
        WHERE id_user = :id_user
        ORDER BY tanggal DESC
    ");
    $stmt->execute([':id_user' => $id_user]);
    $jurnal_list = $stmt->fetchAll();

    $totalJurnal   = count($jurnal_list);
    $totalDisetujui= count(array_filter($jurnal_list, fn($j) => $j['status'] === 'Disetujui'));
    $totalMenunggu = count(array_filter($jurnal_list, fn($j) => $j['status'] === 'Menunggu'));
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
                        <p class="text-blue-100 text-[15px]">Kelola dan pantau jurnal kegiatan magang kamu</p>
                    </div>
                    <div class="absolute right-0 top-0 w-96 h-full bg-gradient-to-l from-[#254bdb] to-transparent z-0 opacity-80"></div>
                </div>

                <!-- Notifikasi -->
                <?php if (isset($_GET['success']) && $_GET['success'] === 'jurnal_disimpan'): ?>
                    <div id="notifBox" class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-[13px] font-medium">
                        <i class="fas fa-check-circle text-green-500"></i> Jurnal berhasil disimpan dan menunggu persetujuan.
                        <button onclick="document.getElementById('notifBox').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>

                <!-- Stat Cards -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                            <i class="fas fa-book text-blue-500"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500">Total Jurnal</p>
                            <p class="text-xl font-bold text-gray-900"><?= $totalJurnal ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500">Disetujui</p>
                            <p class="text-xl font-bold text-gray-900"><?= $totalDisetujui ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                            <i class="fas fa-clock text-amber-500"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500">Menunggu Review</p>
                            <p class="text-xl font-bold text-gray-900"><?= $totalMenunggu ?></p>
                        </div>
                    </div>
                </div>

                <!-- Table Card Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col overflow-hidden">
                    
                    <!-- Card Header / Toolbar -->
                    <div class="p-6 md:px-8 md:py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-[18px] font-bold text-gray-800">Daftar Jurnal Kegiatan</h3>
                            <p class="text-[13px] text-gray-500 mt-1">Rekap jurnal harian magang kamu</p>
                        </div>
                        <a href="tambahjurnal.php"
                           class="self-start sm:self-auto bg-[#2563eb] hover:bg-[#1d4ed8] text-white px-5 py-2.5 rounded-[10px] flex items-center gap-2 font-medium text-[14px] transition-colors shadow-sm hover:shadow-md">
                            <i class="fas fa-plus text-[13px]"></i> Tambah
                        </a>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="bg-white border-b border-gray-100 text-gray-400 text-[12px] font-semibold">
                                    <th class="px-6 py-4 font-semibold w-14 text-center">No</th>
                                    <th class="px-6 py-4 font-semibold w-36 text-center">Tanggal</th>
                                    <th class="px-6 py-4 font-semibold text-center">Judul</th>
                                    <th class="px-6 py-4 font-semibold text-center w-24">Bukti</th>
                                    <th class="px-6 py-4 font-semibold text-center">Kegiatan</th>
                                    <th class="px-6 py-4 font-semibold text-center w-32">Status</th>
                                    <th class="px-6 py-4 font-semibold text-center w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-[14px] text-gray-600 divide-y divide-gray-100">
                                <?php if (empty($jurnal_list)): ?>
                                    <tr>
                                        <td colspan="7" class="px-8 py-12 text-center text-gray-400">
                                            <i class="fas fa-book-open text-3xl mb-3 block"></i>
                                            Belum ada jurnal. <a href="tambahjurnal.php" class="text-blue-600 hover:underline">Tambah sekarang</a>.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                        // Pagination setup
                                        $perPage     = 5;
                                        $currentPage = max(1, (int)($_GET['page'] ?? 1));
                                        $totalPages  = max(1, (int)ceil($totalJurnal / $perPage));
                                        $currentPage = min($currentPage, $totalPages);
                                        $offset      = ($currentPage - 1) * $perPage;
                                        $pagedList   = array_slice($jurnal_list, $offset, $perPage);
                                        $startNo     = $offset + 1;
                                        $endNo       = min($offset + $perPage, $totalJurnal);
                                    ?>
                                    <?php foreach ($pagedList as $no => $j):
                                        $parts     = explode("\n\n", $j['kegiatan'], 2);
                                        $judul     = htmlspecialchars($parts[0]);
                                        $deskripsi = htmlspecialchars($parts[1] ?? '');
                                        $tgl       = new DateTime($j['tanggal']);
                                    ?>
                                        <tr class="hover:bg-gray-50/80 transition-colors border-b border-gray-50">
                                            <td class="px-6 py-5 text-gray-500 text-center text-[14px]"><?= $offset + $no + 1 ?></td>
                                            <td class="px-6 py-5 text-center">
                                                <div class="flex flex-col items-center">
                                                    <span class="font-semibold text-gray-800 text-[14px]"><?= $tgl->format('d M') ?></span>
                                                    <span class="text-[12px] text-gray-400"><?= $tgl->format('Y') ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <p class="font-bold text-gray-800 text-[14px]"><?= $judul ?></p>
                                            </td>
                                            <!-- Kolom Bukti -->
                                            <td class="px-6 py-5 text-center">
                                                <?php if (!empty($j['bukti'])): ?>
                                                    <a href="../<?= htmlspecialchars($j['bukti']) ?>" target="_blank"
                                                       title="Lihat Bukti"
                                                       class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition-all">
                                                        <i class="fas fa-image text-[15px]"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 text-gray-300" title="Tidak ada bukti">
                                                        <i class="fas fa-image text-[15px]"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-5">
                                                <p class="text-[13px] text-gray-500 leading-relaxed line-clamp-3 max-w-[320px] mx-auto text-center"><?= $deskripsi ?></p>
                                            </td>
                                            <!-- Kolom Status Verifikasi -->
                                            <td class="px-6 py-5 text-center">
                                                <?php
                                                    $statusConf = match($j['status']) {
                                                        'Disetujui' => ['bg-green-100 text-green-700 border border-green-200', 'fa-check-circle', 'Disetujui'],
                                                        'Ditolak'   => ['bg-red-100 text-red-600 border border-red-200',     'fa-times-circle', 'Ditolak'],
                                                        default     => ['bg-amber-100 text-amber-600 border border-amber-200','fa-clock',        'Menunggu'],
                                                    };
                                                    [$badge, $icon, $label] = $statusConf;
                                                ?>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold <?= $badge ?>">
                                                    <i class="fas <?= $icon ?> text-[11px]"></i>
                                                    <?= $label ?>
                                                </span>
                                            </td>
                                            <!-- Kolom Aksi -->
                                            <td class="px-6 py-5 text-center">
                                                <button onclick="openDetail(<?= $j['id_journal'] ?>, `<?= addslashes($judul) ?>`, `<?= addslashes($deskripsi) ?>`, `<?= $j['tanggal'] ?>`, `<?= $j['status'] ?>`, `<?= addslashes($j['catatan_dosen'] ?? '') ?>`, `<?= addslashes($j['bukti'] ?? '') ?>`)"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-sm">
                                                    <i class="fas fa-eye text-[14px]"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="px-6 py-4 md:px-8 border-t border-gray-100 flex items-center justify-between bg-white rounded-b-2xl">
                        <div class="text-[13px] text-gray-500">
                            <?php if ($totalJurnal > 0): ?>
                                Menampilkan <span class="font-semibold text-gray-700"><?= $startNo ?></span> sampai
                                <span class="font-semibold text-gray-700"><?= $endNo ?></span> dari
                                <span class="font-semibold text-gray-700"><?= $totalJurnal ?></span> hasil
                            <?php else: ?>
                                Tidak ada data
                            <?php endif; ?>
                        </div>
                        <?php if ($totalPages > 1): ?>
                        <div class="flex items-center gap-1">
                            <!-- Previous -->
                            <a href="?page=<?= max(1, $currentPage - 1) ?>"
                               class="px-3 py-1.5 rounded-lg border border-gray-200 text-[13px] font-medium text-gray-600 hover:bg-gray-50 transition-colors <?= $currentPage <= 1 ? 'opacity-40 pointer-events-none' : '' ?>">
                                Previous
                            </a>
                            <!-- Page Numbers -->
                            <?php
                                $range = 2;
                                $start = max(1, $currentPage - $range);
                                $end   = min($totalPages, $currentPage + $range);
                                if ($start > 1): ?>
                                    <a href="?page=1" class="px-3 py-1.5 rounded-lg border border-gray-200 text-[13px] font-medium text-gray-600 hover:bg-gray-50 transition-colors">1</a>
                                    <?php if ($start > 2): ?><span class="px-1 text-gray-400">...</span><?php endif; ?>
                            <?php endif;
                                for ($p = $start; $p <= $end; $p++): ?>
                                    <a href="?page=<?= $p ?>"
                                       class="px-3 py-1.5 rounded-lg border text-[13px] font-medium transition-colors
                                              <?= $p === $currentPage
                                                    ? 'bg-blue-600 border-blue-600 text-white shadow-sm'
                                                    : 'border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                                        <?= $p ?>
                                    </a>
                            <?php endfor;
                                if ($end < $totalPages): ?>
                                    <?php if ($end < $totalPages - 1): ?><span class="px-1 text-gray-400">...</span><?php endif; ?>
                                    <a href="?page=<?= $totalPages ?>" class="px-3 py-1.5 rounded-lg border border-gray-200 text-[13px] font-medium text-gray-600 hover:bg-gray-50 transition-colors"><?= $totalPages ?></a>
                            <?php endif; ?>
                            <!-- Next -->
                            <a href="?page=<?= min($totalPages, $currentPage + 1) ?>"
                               class="px-3 py-1.5 rounded-lg border border-gray-200 text-[13px] font-medium text-gray-600 hover:bg-gray-50 transition-colors <?= $currentPage >= $totalPages ? 'opacity-40 pointer-events-none' : '' ?>">
                                Next
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
                
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- Modal Detail Jurnal -->
    <div id="modalDetail" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <h3 class="text-[16px] font-bold text-gray-900">Detail Jurnal</h3>
                <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Tanggal</p>
                    <p id="detailTanggal" class="text-[14px] text-gray-800"></p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Judul Kegiatan</p>
                    <p id="detailJudul" class="text-[14px] font-semibold text-gray-800"></p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Deskripsi</p>
                    <p id="detailDeskripsi" class="text-[14px] text-gray-700 leading-relaxed whitespace-pre-line"></p>
                </div>
                <!-- Bukti Kegiatan -->
                <div id="buktiWrap" class="hidden">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Bukti Kegiatan</p>
                    <a id="buktiLink" href="#" target="_blank"
                       class="group block overflow-hidden rounded-xl border border-gray-200 bg-gray-50 hover:border-indigo-300 transition-all">
                        <img id="buktiImg" src="#" alt="Bukti Jurnal"
                             class="w-full max-h-48 object-contain bg-gray-50">
                        <div class="flex items-center gap-2 px-3 py-2 border-t border-gray-100 text-[12px] text-indigo-600 font-medium group-hover:bg-indigo-50 transition-colors">
                            <i class="fas fa-external-link-alt text-[11px]"></i> Buka gambar penuh
                        </div>
                    </a>
                </div>
                <div id="noBuktiWrap" class="hidden">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Bukti Kegiatan</p>
                    <p class="text-[13px] text-gray-400 italic">Tidak ada bukti yang dilampirkan.</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                    <span id="detailStatus" class="px-3 py-1 rounded-full text-[12px] font-semibold"></span>
                </div>
                <div id="catatanWrap" class="hidden">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Catatan Dosen</p>
                    <p id="detailCatatan" class="text-[14px] text-gray-700 bg-amber-50 border border-amber-100 rounded-xl px-4 py-3"></p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button onclick="closeDetail()" class="px-5 py-2 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function openDetail(id, judul, deskripsi, tanggal, status, catatan, bukti) {
            document.getElementById('detailTanggal').textContent   = tanggal;
            document.getElementById('detailJudul').textContent     = judul;
            document.getElementById('detailDeskripsi').textContent = deskripsi;

            const statusEl = document.getElementById('detailStatus');
            const badges = { 'Disetujui': 'bg-green-100 text-green-700', 'Ditolak': 'bg-red-100 text-red-600', 'Menunggu': 'bg-amber-100 text-amber-600' };
            statusEl.className = 'px-3 py-1 rounded-full text-[12px] font-semibold ' + (badges[status] || badges['Menunggu']);
            statusEl.textContent = status;

            // Tampilkan bukti
            const buktiWrap   = document.getElementById('buktiWrap');
            const noBuktiWrap = document.getElementById('noBuktiWrap');
            if (bukti) {
                const baseUrl = window.location.origin + '/simagang/';
                document.getElementById('buktiImg').src  = baseUrl + bukti;
                document.getElementById('buktiLink').href = baseUrl + bukti;
                buktiWrap.classList.remove('hidden');
                noBuktiWrap.classList.add('hidden');
            } else {
                buktiWrap.classList.add('hidden');
                noBuktiWrap.classList.remove('hidden');
            }

            const catatanWrap = document.getElementById('catatanWrap');
            if (catatan) {
                document.getElementById('detailCatatan').textContent = catatan;
                catatanWrap.classList.remove('hidden');
            } else {
                catatanWrap.classList.add('hidden');
            }

            const modal = document.getElementById('modalDetail');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDetail() {
            const modal = document.getElementById('modalDetail');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('modalDetail').addEventListener('click', function(e) {
            if (e.target === this) closeDetail();
        });
    </script>
</body>
</html>

