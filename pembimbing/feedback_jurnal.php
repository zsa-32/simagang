<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("pembimbing_lapang");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'pembimbing';
$activePage = 'feedback_jurnal';

$userId = $_SESSION['id_user'];
$plRow = $conn->prepare("SELECT pl.id, pl.company_id FROM pembimbing_lapang pl WHERE pl.user_id = ?");
$plRow->execute([$userId]);
$plRow = $plRow->fetch();
$plId      = $plRow['id'] ?? null;
$companyId = $plRow['company_id'] ?? null;

$msg = ''; $msgType = '';

// Handle submit feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logbook_id'])) {
    $logbookId = (int)$_POST['logbook_id'];
    $actionType = $_POST['action_type'] ?? 'feedback';
    $feedback  = trim($_POST['feedback'] ?? '');

    if ($actionType === 'approve' || $actionType === 'reject') {
        $newStatus = $actionType === 'approve' ? 'approved' : 'rejected';
        $conn->prepare("UPDATE logbooks SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$newStatus, $logbookId]);
        if ($feedback) {
            $conn->prepare("INSERT INTO feedback_logbooks (logbook_id, penilai_user_id, feedback) VALUES (?,?,?)")->execute([$logbookId, $userId, $feedback]);
        }
        $msg = $actionType === 'approve' ? 'Jurnal berhasil disetujui.' : 'Jurnal ditolak.';
        $msgType = 'success';
    } elseif ($feedback && $logbookId) {
        $conn->prepare("INSERT INTO feedback_logbooks (logbook_id, penilai_user_id, feedback) VALUES (?,?,?)")->execute([$logbookId, $userId, $feedback]);
        $msg = 'Feedback berhasil dikirim.';
        $msgType = 'success';
    }
    header("Location: feedback_jurnal.php?updated=1");
    exit;
}

// Mahasiswa di perusahaan ini
$mhsList = [];
if ($companyId) {
    $stmt = $conn->prepare("
        SELECT m.id, m.nama FROM mahasiswa m
        JOIN `groups` g ON m.group_id = g.id
        WHERE g.company_id = ? ORDER BY m.nama
    ");
    $stmt->execute([$companyId]);
    $mhsList = $stmt->fetchAll();
}

// Filter
$filterMhsId = (int)($_GET['mhs_id'] ?? 0);
$filterStatus = $_GET['status'] ?? '';

// Fetch jurnal
$jurnal = [];
if (!empty($mhsList)) {
    $mhsIds = array_column($mhsList, 'id');
    $inQ = implode(',', array_fill(0, count($mhsIds), '?'));
    $params = $mhsIds;

    $whereExtra = '';
    if ($filterMhsId) { $whereExtra .= " AND lb.mahasiswa_id = ?"; $params[] = $filterMhsId; }
    if ($filterStatus) { $whereExtra .= " AND lb.status = ?"; $params[] = $filterStatus; }

    $stmt = $conn->prepare("
        SELECT lb.*, m.nama as nama_mhs, m.no_ktm as mhs_nim,
               (SELECT COUNT(*) FROM feedback_logbooks fb WHERE fb.logbook_id = lb.id) as total_feedback,
               (SELECT fb2.feedback FROM feedback_logbooks fb2 WHERE fb2.logbook_id = lb.id ORDER BY fb2.created_at DESC LIMIT 1) as feedback_terakhir
        FROM logbooks lb
        JOIN mahasiswa m ON lb.mahasiswa_id = m.id
        WHERE lb.mahasiswa_id IN ($inQ) $whereExtra
        ORDER BY lb.tanggal DESC
        LIMIT 30
    ");
    $stmt->execute($params);
    $jurnal = $stmt->fetchAll();
}

// Stats — dihitung langsung dari DB (tanpa filter status & tanpa LIMIT)
$statPending = 0; $statApproved = 0; $statRejected = 0;
if (!empty($mhsList)) {
    $mhsIdsForStat = array_column($mhsList, 'id');
    $inQStat = implode(',', array_fill(0, count($mhsIdsForStat), '?'));
    $paramsStat = $mhsIdsForStat;
    if ($filterMhsId) { 
        $inQStat = '?'; 
        $paramsStat = [$filterMhsId]; 
    }
    $stmtStat = $conn->prepare("
        SELECT status, COUNT(*) as total
        FROM logbooks
        WHERE mahasiswa_id IN ($inQStat)
        GROUP BY status
    ");
    $stmtStat->execute($paramsStat);
    foreach ($stmtStat->fetchAll() as $row) {
        match($row['status']) {
            'pending'  => $statPending  = (int)$row['total'],
            'approved' => $statApproved = (int)$row['total'],
            'rejected' => $statRejected = (int)$row['total'],
            default    => null,
        };
    }
}

$userName = $_SESSION['nama'] ?? 'Pembimbing';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Jurnal - Magang TIF</title>
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
    <?php include '../includes/header.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
        <div class="max-w-[1100px] mx-auto space-y-5">

            <?php if (isset($_GET['updated'])): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2">
                <i class="fas fa-check-circle"></i> Status jurnal berhasil diperbarui.
            </div>
            <?php endif; ?>

            <!-- Section Header -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-[16px] font-bold text-gray-800">Feedback Jurnal Mahasiswa</h2>
                    <p class="text-[13px] text-gray-400 mt-0.5">Review dan berikan feedback jurnal harian mahasiswa magang</p>
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
                    <div><p class="text-[12px] text-gray-500 mb-0.5">Pending</p><p class="text-3xl font-bold text-gray-900"><?= $statPending ?></p></div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center shrink-0"><i class="fas fa-check-circle text-green-500 text-[20px]"></i></div>
                    <div><p class="text-[12px] text-gray-500 mb-0.5">Disetujui</p><p class="text-3xl font-bold text-gray-900"><?= $statApproved ?></p></div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center shrink-0"><i class="fas fa-times-circle text-red-400 text-[20px]"></i></div>
                    <div><p class="text-[12px] text-gray-500 mb-0.5">Ditolak</p><p class="text-3xl font-bold text-gray-900"><?= $statRejected ?></p></div>
                </div>
            </div>

            <!-- Filter -->
            <form method="GET" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Mahasiswa</label>
                    <select name="mhs_id" class="border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Semua Mahasiswa</option>
                        <?php foreach ($mhsList as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $filterMhsId == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                    <select name="status" class="border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $filterStatus === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="feedback_jurnal.php" class="bg-gray-100 text-gray-600 px-5 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition-colors">Reset</a>
            </form>

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
                            <?php if (empty($jurnal)): ?>
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada data jurnal.</td></tr>
                            <?php else: ?>
                            <?php foreach ($jurnal as $j):
                                $statusClass = match($j['status']) {
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-600',
                                    default => 'bg-orange-100 text-orange-600',
                                };
                                $statusLabel = match($j['status']) {
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    default => 'Pending',
                                };
                            ?>
                            <tr class="journal-row transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-gray-800 flex items-center justify-center text-white font-semibold text-[12px] shrink-0"><?= strtoupper(substr($j['nama_mhs'], 0, 2)) ?></div>
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($j['nama_mhs']) ?></p>
                                            <p class="text-[12px] text-gray-400"><?= $j['mhs_nim'] ?? '-' ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500 whitespace-nowrap"><?= date('d M Y', strtotime($j['tanggal'])) ?></td>
                                <td class="px-6 py-4 text-gray-700 max-w-[260px]"><span class="line-clamp-1"><?= htmlspecialchars($j['kegiatan']) ?></span></td>
                                <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                <td class="px-6 py-4">
                                    <button onclick='openDetail(<?= json_encode($j, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG) ?>)' class="flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-[13px] font-medium hover:underline transition-colors">
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

            <!-- Feedback terakhir -->
            <div id="d-feedback-wrapper" style="display:none;">
                <div class="bg-green-50 border border-green-100 rounded-xl p-3.5">
                    <p class="text-xs font-semibold text-green-600 mb-1"><i class="fas fa-check-circle mr-1"></i>Feedback Terakhir</p>
                    <p id="d-feedback" class="text-sm text-gray-700"></p>
                </div>
            </div>

            <!-- Dokumentasi Image -->
            <div id="d-dokumentasi-wrapper" style="display:none;">
                <p class="text-[11px] font-semibold text-gray-400 mb-2">Dokumentasi</p>
                <a id="d-dokumentasi-link" href="#" target="_blank">
                    <img id="d-dokumentasi" src="" alt="Dokumentasi Jurnal" class="w-full max-h-[300px] object-contain rounded-xl border border-gray-200 bg-gray-50 cursor-pointer hover:opacity-90 transition-opacity">
                </a>
            </div>

            <form method="POST" id="d-form">
                <input type="hidden" name="logbook_id" id="d-id">
                <input type="hidden" name="action_type" id="d-action-type" value="feedback">
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
        document.getElementById('d-nama').textContent = j.nama_mhs;
        document.getElementById('d-tanggal').textContent = j.tanggal;
        document.getElementById('d-kegiatan').textContent = j.kegiatan || '-';
        document.getElementById('d-hasil').textContent = j.hasil || '-';
        document.getElementById('d-id').value = j.id;

        const statusMap = { approved: ['Disetujui','bg-green-100 text-green-700'], rejected: ['Ditolak','bg-red-100 text-red-600'], pending: ['Pending','bg-orange-100 text-orange-600'] };
        const [label, cls] = statusMap[j.status] || statusMap.pending;
        document.getElementById('d-status').innerHTML = `<span class="px-3 py-1 rounded-full text-[12px] font-semibold ${cls}">${label}</span>`;

        // Show feedback if available
        const fbWrapper = document.getElementById('d-feedback-wrapper');
        const fbText = document.getElementById('d-feedback');
        if (j.feedback_terakhir) {
            fbText.textContent = j.feedback_terakhir;
            fbWrapper.style.display = 'block';
        } else {
            fbWrapper.style.display = 'none';
        }

        // Show dokumentasi image if available
        const dokWrapper = document.getElementById('d-dokumentasi-wrapper');
        const dokImg = document.getElementById('d-dokumentasi');
        const dokLink = document.getElementById('d-dokumentasi-link');
        if (j.dokumentasi) {
            const imgSrc = '../' + j.dokumentasi;
            dokImg.src = imgSrc;
            dokLink.href = imgSrc;
            dokWrapper.style.display = 'block';
        } else {
            dokImg.src = '';
            dokLink.href = '#';
            dokWrapper.style.display = 'none';
        }

        // Always show approve/reject buttons
        document.getElementById('d-actions').style.display = 'flex';

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
