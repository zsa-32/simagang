<?php
    session_start();
    require_once '../config/db_connect.php';

    if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'dosen pembimbing') {
        header('Location: ../index.php'); exit();
    }

    $role = 'dosen';
    $activePage = 'lihat_jurnal';
    $id_dosen = (int) $_SESSION['id_user'];

    // Ambil semua mahasiswa bimbingan dosen ini (via id_dosen_pembimbing di Profile)
    $stmtMhs = $conn->prepare("
        SELECT u.id_user FROM Users u
        JOIN Profile p ON u.id_user = p.id_user
        WHERE p.id_dosen_pembimbing = :id_dosen
    ");
    $stmtMhs->execute([':id_dosen' => $id_dosen]);
    $mhsIds = $stmtMhs->fetchAll(PDO::FETCH_COLUMN);

    // Ambil semua jurnal dari mahasiswa bimbingan
    $journals = [];
    if (!empty($mhsIds)) {
        $placeholders = implode(',', array_fill(0, count($mhsIds), '?'));
        $stmtJ = $conn->prepare("
            SELECT dj.id_journal, u.nama, p.nim, dj.tanggal, dj.kegiatan, dj.status, dj.catatan_dosen
            FROM Daily_journal dj
            JOIN Users u ON dj.id_user = u.id_user
            LEFT JOIN Profile p ON u.id_user = p.id_user
            WHERE dj.id_user IN ($placeholders)
            ORDER BY dj.tanggal DESC
        ");
        $stmtJ->execute($mhsIds);
        $journals = $stmtJ->fetchAll();
    }

    $statMenunggu  = count(array_filter($journals, fn($j) => $j['status'] === 'Menunggu'));
    $statDisetujui = count(array_filter($journals, fn($j) => $j['status'] === 'Disetujui'));
    $statDitolak   = count(array_filter($journals, fn($j) => $j['status'] === 'Ditolak'));
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
                            <p class="text-3xl font-bold text-gray-900"><?= $statMenunggu ?></p>
                        </div>
                    </div>
                    <!-- Disetujui -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-[20px]"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500 mb-0.5">Disetujui</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $statDisetujui ?></p>
                        </div>
                    </div>
                    <!-- Ditolak / Revisi -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-times-circle text-red-400 text-[20px]"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500 mb-0.5">Perlu Revisi</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $statDitolak ?></p>
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
                                <?php if (empty($journals)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                            <i class="fas fa-book-open text-3xl mb-3 block"></i>
                                            Belum ada jurnal dari mahasiswa bimbingan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($journals as $j):
                                        $statusClass = match($j['status']) {
                                            'Disetujui' => 'bg-green-100 text-green-700',
                                            'Ditolak'   => 'bg-red-100 text-red-600',
                                            default     => 'bg-orange-100 text-orange-600',
                                        };
                                        $parts   = explode("\n\n", $j['kegiatan'], 2);
                                        $judul   = $parts[0];
                                        $deskrob = $parts[1] ?? $parts[0];
                                    ?>
                                    <tr class="journal-row transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-gray-800 flex items-center justify-center text-white font-semibold text-[12px] shrink-0">
                                                    <?= strtoupper(substr($j['nama'], 0, 2)) ?>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($j['nama']) ?></p>
                                                    <p class="text-[12px] text-gray-400"><?= htmlspecialchars($j['nim'] ?? '-') ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap"><?= $j['tanggal'] ?></td>
                                        <td class="px-6 py-4 text-gray-700 max-w-[240px]">
                                            <span class="line-clamp-1"><?= htmlspecialchars($judul) ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $statusClass ?>">
                                                <?= $j['status'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button onclick="openValidasi(<?= $j['id_journal'] ?>, `<?= addslashes(htmlspecialchars($j['nama'])) ?>`, `<?= addslashes(htmlspecialchars($judul)) ?>`, `<?= $j['tanggal'] ?>`, `<?= addslashes(htmlspecialchars($deskrob)) ?>`, `<?= $j['status'] ?>`, `<?= addslashes(htmlspecialchars($j['catatan_dosen'] ?? '')) ?>`)"
                                                class="flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-[13px] font-medium hover:underline transition-colors">
                                                <i class="fas fa-eye text-[12px]"></i> Lihat
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- Modal Validasi Jurnal -->
    <div id="modalValidasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <h3 class="text-[16px] font-bold text-gray-900">Review Jurnal</h3>
                <button onclick="closeValidasi()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Mahasiswa</p>
                        <p id="vNama" class="text-[14px] font-semibold text-gray-800"></p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Tanggal</p>
                        <p id="vTanggal" class="text-[14px] text-gray-700"></p>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Kegiatan</p>
                    <p id="vJudul" class="text-[14px] font-semibold text-gray-800"></p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Deskripsi</p>
                    <p id="vDeskripsi" class="text-[14px] text-gray-700 leading-relaxed bg-gray-50 rounded-xl px-4 py-3 whitespace-pre-line"></p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Status Saat Ini</p>
                    <span id="vStatus" class="px-3 py-1 rounded-full text-[12px] font-semibold"></span>
                </div>
            </div>

            <!-- Form Validasi -->
            <form method="POST" action="../proses/jurnal_validasi.php" class="px-6 pb-6 space-y-3">
                <input type="hidden" name="id_journal" id="vIdJurnal">
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">
                        Catatan (opsional)
                    </label>
                    <textarea name="catatan_dosen" id="vCatatan" rows="3"
                        placeholder="Berikan catatan atau arahan untuk mahasiswa..."
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-[13px] text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 resize-none"></textarea>
                </div>
                <div class="flex items-center gap-3 pt-1">
                    <button type="button" onclick="closeValidasi()" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" name="aksi" value="tolak"
                        class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl text-[13px] font-semibold transition-colors">
                        <i class="fas fa-times mr-1"></i> Tolak
                    </button>
                    <button type="submit" name="aksi" value="setujui"
                        class="flex-1 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl text-[13px] font-semibold transition-colors">
                        <i class="fas fa-check mr-1"></i> Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Live search
        document.getElementById('searchJurnal').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#journalTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        // ===== Modal Validasi =====
        function openValidasi(id, nama, judul, tanggal, deskripsi, status, catatan) {
            document.getElementById('vIdJurnal').value    = id;
            document.getElementById('vNama').textContent  = nama;
            document.getElementById('vJudul').textContent = judul;
            document.getElementById('vTanggal').textContent = tanggal;
            document.getElementById('vDeskripsi').textContent = deskripsi;
            document.getElementById('vCatatan').value     = catatan;

            const statusEl = document.getElementById('vStatus');
            const badges = { 'Disetujui': 'bg-green-100 text-green-700', 'Ditolak': 'bg-red-100 text-red-600', 'Menunggu': 'bg-amber-100 text-amber-600' };
            statusEl.className = 'px-3 py-1 rounded-full text-[12px] font-semibold ' + (badges[status] || badges['Menunggu']);
            statusEl.textContent = status;

            const modal = document.getElementById('modalValidasi');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeValidasi() {
            const modal = document.getElementById('modalValidasi');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('modalValidasi').addEventListener('click', function(e) {
            if (e.target === this) closeValidasi();
        });
    </script>
</body>
</html>
