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
    $feedback  = trim($_POST['feedback'] ?? '');
    if ($feedback && $logbookId) {
        $stmt = $conn->prepare("INSERT INTO feedback_logbooks (logbook_id, penilai_user_id, feedback) VALUES (?,?,?)");
        $stmt->execute([$logbookId, $userId, $feedback]);
        // Update status logbook ke 'disetujui'
        $conn->prepare("UPDATE logbooks SET status='disetujui', updated_at=NOW() WHERE id=?")->execute([$logbookId]);
        $msg = 'Feedback berhasil dikirim.'; $msgType = 'success';
    }
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
        SELECT lb.*, m.nama as nama_mhs,
               (SELECT COUNT(*) FROM feedback_logbooks fb WHERE fb.logbook_id = lb.id) as total_feedback,
               (SELECT fb2.feedback FROM feedback_logbooks fb2 WHERE fb2.logbook_id = lb.id ORDER BY fb2.created_at DESC LIMIT 1) as feedback_terakhir
        FROM logbooks lb
        JOIN mahasiswa m ON lb.mahasiswa_id = m.id
        WHERE lb.mahasiswa_id IN ($inQ) $whereExtra
        ORDER BY lb.tanggal DESC
    ");
    $stmt->execute($params);
    $jurnal = $stmt->fetchAll();
}

$userName = $_SESSION['nama'] ?? 'Pembimbing';
$statusBadge = ['pending'=>'yellow','disetujui'=>'green','ditolak'=>'red'];
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
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

<?php include '../includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col h-screen overflow-hidden">
    <?php include '../includes/header.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
        <div class="max-w-[1100px] mx-auto space-y-6">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">Feedback Jurnal</h2>
                <p class="text-gray-500 text-sm mt-0.5">Review dan berikan feedback jurnal harian mahasiswa magang</p>
            </div>

            <?php if ($msg): ?>
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                <i class="fas fa-circle-check"></i> <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <!-- Filter -->
            <form method="GET" class="flex flex-wrap gap-3 items-end">
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
                        <option value="disetujui" <?= $filterStatus === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="ditolak" <?= $filterStatus === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="feedback_jurnal.php" class="bg-gray-100 text-gray-600 px-5 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition-colors">Reset</a>
            </form>

            <!-- Jurnal List -->
            <?php if (empty($jurnal)): ?>
            <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow-sm border border-gray-100">
                <i class="fas fa-book text-5xl mb-4 block text-gray-200"></i>
                <p class="font-semibold text-gray-600 text-lg">Tidak ada jurnal</p>
                <p class="text-sm mt-2">Belum ada jurnal mahasiswa yang perlu direview.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($jurnal as $j):
                    $sc = $statusBadge[$j['status']] ?? 'gray';
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
                            <div>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="font-bold text-gray-800"><?= date('d M Y', strtotime($j['tanggal'])) ?></span>
                                    <span class="text-sm text-gray-500"><?= htmlspecialchars($j['nama_mhs']) ?></span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        <?= $sc==='yellow'?'bg-yellow-100 text-yellow-700':'' ?>
                                        <?= $sc==='green' ?'bg-green-100 text-green-700'  :'' ?>
                                        <?= $sc==='red'   ?'bg-red-100 text-red-700'      :'' ?>
                                        <?= $sc==='gray'  ?'bg-gray-100 text-gray-600'    :'' ?>">
                                        <?= ucfirst($j['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Kegiatan</p>
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($j['kegiatan'] ?? '-')) ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Hasil</p>
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($j['hasil'] ?? '-')) ?></p>
                            </div>
                            <?php if ($j['kendala']): ?>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Kendala</p>
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($j['kendala'])) ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($j['solusi']): ?>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Solusi</p>
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($j['solusi'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($j['feedback_terakhir']): ?>
                        <div class="mb-4 bg-green-50 border border-green-100 rounded-xl p-3.5">
                            <p class="text-xs font-semibold text-green-600 mb-1"><i class="fas fa-check-circle mr-1"></i>Feedback Terakhir</p>
                            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($j['feedback_terakhir'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Form Feedback -->
                        <div class="border-t border-gray-100 pt-4">
                            <form method="POST" class="flex gap-3">
                                <input type="hidden" name="logbook_id" value="<?= $j['id'] ?>">
                                <textarea name="feedback" rows="2" placeholder="Tulis feedback untuk jurnal ini..."
                                          class="flex-1 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors self-end whitespace-nowrap">
                                    <i class="fas fa-paper-plane mr-1"></i> Kirim
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</div>
</body>
</html>
