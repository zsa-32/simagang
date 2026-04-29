<?php
    require_once __DIR__ . "/../config/role_guard.php";
    checkRole("dosen_pembimbing");
    require_once __DIR__ . '/../config/db_connect.php';
    $role = 'dosen';
    $activePage = 'lihat_jurnal';

    $userId = $_SESSION['id_user'];
    $stmt = $conn->prepare("SELECT id FROM dosen_pembimbing WHERE user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $dosen = $stmt->fetch();
    $dosenId = $dosen ? $dosen['id'] : 0;

    // Handle approve/reject
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logbook_id'], $_POST['action_type'])) {
        $lbId = (int)$_POST['logbook_id'];
        $actionType = $_POST['action_type'];
        // Verify ownership
        $chk = $conn->prepare("
            SELECT l.id FROM logbooks l
            JOIN mahasiswa m ON l.mahasiswa_id = m.id
            JOIN `groups` g ON m.group_id = g.id
            WHERE l.id = :lid AND g.dosen_pembimbing_id = :did
        ");
        $chk->execute(['lid' => $lbId, 'did' => $dosenId]);
        if ($chk->fetch()) {
            $newStatus = $actionType === 'approve' ? 'approved' : 'rejected';
            $conn->prepare("UPDATE logbooks SET status = :st, updated_at = NOW() WHERE id = :id")->execute(['st' => $newStatus, 'id' => $lbId]);

            // Add feedback if provided
            $feedback = trim($_POST['feedback'] ?? '');
            if ($feedback) {
                $conn->prepare("INSERT INTO feedback_logbooks (logbook_id, penilai_user_id, feedback) VALUES (:lid, :uid, :fb)")
                    ->execute(['lid' => $lbId, 'uid' => $userId, 'fb' => $feedback]);
            }
        }
        header("Location: jurnal.php?updated=1");
        exit;
    }

    // Stats
    $stmtStats = $conn->prepare("
        SELECT l.status, COUNT(*) as total FROM logbooks l
        JOIN mahasiswa m ON l.mahasiswa_id = m.id
        JOIN `groups` g ON m.group_id = g.id
        WHERE g.dosen_pembimbing_id = :did
        GROUP BY l.status
    ");
    $stmtStats->execute(['did' => $dosenId]);
    $statsRows = $stmtStats->fetchAll();
    $statPending = 0; $statApproved = 0; $statRejected = 0;
    foreach ($statsRows as $r) {
        match($r['status']) {
            'pending' => $statPending += (int)$r['total'],
            'approved' => $statApproved += (int)$r['total'],
            'rejected' => $statRejected += (int)$r['total'],
            default => null,
        };
    }

    // Journal list
    $stmt = $conn->prepare("
        SELECT l.id, l.tanggal, l.kegiatan, l.hasil, l.status, l.dokumentasi,
               m.nama as mhs_nama, m.no_ktm as mhs_nim
        FROM logbooks l
        JOIN mahasiswa m ON l.mahasiswa_id = m.id
        JOIN `groups` g ON m.group_id = g.id
        WHERE g.dosen_pembimbing_id = :did
        ORDER BY l.tanggal DESC, l.created_at DESC
        LIMIT 30
    ");
    $stmt->execute(['did' => $dosenId]);
    $journals = $stmt->fetchAll();
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
        #detailModal { transition: opacity 0.25s ease; }
        #detailModal.hidden { opacity: 0; pointer-events: none; }
        #detailBox { transition: transform 0.3s cubic-bezier(.34,1.56,.64,1), opacity 0.25s ease; }
        #detailModal.hidden #detailBox { transform: scale(0.92) translateY(20px); opacity: 0; }
        #detailModal:not(.hidden) #detailBox { transform: scale(1) translateY(0); opacity: 1; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <div class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 md:px-8 shrink-0">
            <h1 class="text-[18px] font-bold text-gray-900">Validasi Jurnal Dosen</h1>
            <div class="flex items-center gap-4"><?php include '../includes/header.php'; ?></div>
        </div>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1000px] mx-auto space-y-5">

                <?php if (isset($_GET['updated'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Status jurnal berhasil diperbarui.
                </div>
                <?php endif; ?>

                <!-- Section Header -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-[16px] font-bold text-gray-800">Monitoring Jurnal</h2>
                        <p class="text-[13px] text-gray-400 mt-0.5">Pantau jurnal harian mahasiswa bimbingan</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[13px]"></i>
                            <input type="text" id="searchJurnal" placeholder="Cari mahasiswa atau kegiatan..."
                                   class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 w-64 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center shrink-0"><i class="fas fa-comment-dots text-orange-400 text-[20px]"></i></div>
                        <div><p class="text-[12px] text-gray-500 mb-0.5">Menunggu Review</p><p class="text-3xl font-bold text-gray-900"><?= $statPending ?></p></div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center shrink-0"><i class="fas fa-check-circle text-green-500 text-[20px]"></i></div>
                        <div><p class="text-[12px] text-gray-500 mb-0.5">Disetujui</p><p class="text-3xl font-bold text-gray-900"><?= $statApproved ?></p></div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center shrink-0"><i class="fas fa-times-circle text-red-400 text-[20px]"></i></div>
                        <div><p class="text-[12px] text-gray-500 mb-0.5">Perlu Revisi</p><p class="text-3xl font-bold text-gray-900"><?= $statRejected ?></p></div>
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
                                    <th class="text-left px-6 py-4 font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($journals)): ?>
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada data jurnal.</td></tr>
                                <?php else: ?>
                                <?php foreach ($journals as $j):
                                    $statusClass = match($j['status']) {
                                        'approved' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-600',
                                        default => 'bg-orange-100 text-orange-600',
                                    };
                                    $statusLabel = match($j['status']) {
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Revisi',
                                        default => 'Menunggu',
                                    };
                                ?>
                                <tr class="journal-row transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-gray-800 flex items-center justify-center text-white font-semibold text-[12px] shrink-0"><?= strtoupper(substr($j['mhs_nama'], 0, 2)) ?></div>
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($j['mhs_nama']) ?></p>
                                                <p class="text-[12px] text-gray-400"><?= $j['mhs_nim'] ?? '-' ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 whitespace-nowrap"><?= date('d M Y', strtotime($j['tanggal'])) ?></td>
                                    <td class="px-6 py-4 text-gray-700 max-w-[260px]"><span class="line-clamp-1"><?= htmlspecialchars($j['kegiatan']) ?></span></td>
                                    <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                    <td class="px-6 py-4">
                                        <button onclick='openDetail(<?= json_encode($j, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-[13px] font-medium hover:underline transition-colors">
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

    <!-- Detail / Approve Modal -->
    <div id="detailModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this)closeDetail()">
        <div id="detailBox" class="bg-white rounded-2xl w-full max-w-[500px] max-h-[85vh] overflow-y-auto shadow-2xl">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-[17px] font-bold text-gray-900">Detail Jurnal</h3>
                <button onclick="closeDetail()" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors"><i class="fas fa-times text-gray-400 text-[14px]"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Mahasiswa</p><p id="d-nama" class="text-[15px] font-bold text-gray-800"></p></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Tanggal</p><p id="d-tanggal" class="text-[14px] text-gray-700"></p></div>
                    <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Status</p><p id="d-status"></p></div>
                </div>
                <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Kegiatan</p><p id="d-kegiatan" class="text-[14px] text-gray-700 leading-relaxed"></p></div>
                <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Hasil</p><p id="d-hasil" class="text-[14px] text-gray-600 leading-relaxed"></p></div>

                <form method="POST" id="d-form">
                    <input type="hidden" name="logbook_id" id="d-id">
                    <input type="hidden" name="action_type" id="d-action-type">
                    <div class="border-t border-gray-100 pt-4">
                        <label class="block text-[13px] font-semibold text-gray-700 mb-2">Feedback (opsional)</label>
                        <textarea name="feedback" rows="2" placeholder="Berikan catatan..."
                                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] text-gray-700 resize-none outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all"></textarea>
                    </div>
                    <div id="d-actions" class="flex items-center justify-end gap-3 pt-4">
                        <button type="button" onclick="submitAction('reject')" class="px-5 py-2.5 border border-red-200 text-red-600 rounded-xl text-[13px] font-semibold hover:bg-red-50 transition-colors">
                            <i class="fas fa-times text-[11px] mr-1"></i> Tolak
                        </button>
                        <button type="button" onclick="submitAction('approve')" class="px-5 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm">
                            <i class="fas fa-check text-[11px] mr-1"></i> Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDetail(j) {
            document.getElementById('d-nama').textContent = j.mhs_nama;
            document.getElementById('d-tanggal').textContent = j.tanggal;
            document.getElementById('d-kegiatan').textContent = j.kegiatan || '-';
            document.getElementById('d-hasil').textContent = j.hasil || '-';
            document.getElementById('d-id').value = j.id;

            const statusMap = { approved: ['Disetujui','bg-green-100 text-green-700'], rejected: ['Revisi','bg-red-100 text-red-600'], pending: ['Menunggu','bg-orange-100 text-orange-600'] };
            const [label, cls] = statusMap[j.status] || statusMap.pending;
            document.getElementById('d-status').innerHTML = `<span class="px-3 py-1 rounded-full text-[12px] font-semibold ${cls}">${label}</span>`;

            // Hide approve/reject buttons if already processed
            document.getElementById('d-actions').style.display = j.status === 'pending' ? 'flex' : 'none';

            document.getElementById('detailModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeDetail() { document.getElementById('detailModal').classList.add('hidden'); document.body.style.overflow = ''; }
        function submitAction(type) {
            document.getElementById('d-action-type').value = type;
            document.getElementById('d-form').submit();
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });
        document.getElementById('searchJurnal').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#journalTable tbody tr').forEach(row => { row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
        });
    </script>
</body>
</html>
