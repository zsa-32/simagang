<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("dosen_pembimbing");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'dosen';
$activePage = 'laporan_akhir';

$userId = $_SESSION['id_user'];
$dosenRow = $conn->prepare("SELECT id FROM dosen_pembimbing WHERE user_id = ?");
$dosenRow->execute([$userId]);
$dosenRow = $dosenRow->fetch();
$dosenId = $dosenRow['id'] ?? null;

$msg = ''; $msgType = '';

// Handle submit feedback laporan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['laporan_id'])) {
    $laporanId = (int)$_POST['laporan_id'];
    $feedback  = trim($_POST['feedback'] ?? '');
    if ($feedback) {
        $stmt = $conn->prepare("INSERT INTO final_report_feedbacks (final_report_id, penilai_user_id, feedback) VALUES (?,?,?)");
        $stmt->execute([$laporanId, $userId, $feedback]);

        // Update status laporan ke 'review'
        $conn->prepare("UPDATE final_reports SET status='review', updated_at=NOW() WHERE id=?")->execute([$laporanId]);
        $msg = 'Feedback berhasil dikirim.'; $msgType = 'success';
    }
}

// Fetch laporan mahasiswa bimbingan dosen ini
$laporan = [];
if ($dosenId) {
    $stmt = $conn->prepare("
        SELECT fr.*, m.nama as nama_mhs, c.nama_perusahaan,
               (SELECT COUNT(*) FROM final_report_feedbacks frf WHERE frf.final_report_id = fr.id) as total_feedback
        FROM final_reports fr
        JOIN mahasiswa m ON fr.mahasiswa_id = m.id
        JOIN `groups` g ON m.group_id = g.id
        LEFT JOIN companies c ON g.company_id = c.id
        WHERE g.dosen_pembimbing_id = :did
        ORDER BY fr.created_at DESC
    ");
    $stmt->execute(['did' => $dosenId]);
    $laporan = $stmt->fetchAll();
}

$userName = $_SESSION['nama'] ?? 'Dosen';
$statusBadge = ['pending' => 'yellow', 'review' => 'blue', 'disetujui' => 'green', 'revisi' => 'red'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Akhir - Magang TIF</title>
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
                <h2 class="text-2xl font-bold text-gray-900">Laporan Akhir</h2>
                <p class="text-gray-500 text-sm mt-0.5">Review dan berikan feedback laporan akhir mahasiswa bimbingan</p>
            </div>

            <?php if ($msg): ?>
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium <?= $msgType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                <i class="fas fa-circle-check"></i> <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif; ?>

            <?php if (empty($laporan)): ?>
            <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow-sm border border-gray-100">
                <i class="fas fa-book-open text-5xl mb-4 block text-gray-200"></i>
                <p class="font-semibold text-gray-600 text-lg">Belum ada laporan akhir</p>
                <p class="text-sm mt-2">Mahasiswa bimbingan Anda belum mengumpulkan laporan akhir.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($laporan as $l):
                    $sc = $statusBadge[$l['status']] ?? 'gray';
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <h3 class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($l['judul_laporan']) ?></h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        <?= $sc==='yellow'?'bg-yellow-100 text-yellow-700':'' ?>
                                        <?= $sc==='blue'  ?'bg-blue-100 text-blue-700'    :'' ?>
                                        <?= $sc==='green' ?'bg-green-100 text-green-700'  :'' ?>
                                        <?= $sc==='red'   ?'bg-red-100 text-red-700'      :'' ?>
                                        <?= $sc==='gray'  ?'bg-gray-100 text-gray-600'    :'' ?>">
                                        <?= ucfirst($l['status']) ?>
                                    </span>
                                </div>
                                <div class="flex gap-4 mt-2 text-sm text-gray-500">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-user-graduate text-xs"></i><?= htmlspecialchars($l['nama_mhs']) ?></span>
                                    <?php if (!empty($l['nama_perusahaan'])): ?>
                                    <span class="flex items-center gap-1.5"><i class="fas fa-building text-xs"></i><?= htmlspecialchars($l['nama_perusahaan']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($l['ringkasan']): ?>
                                <p class="text-sm text-gray-600 mt-3 leading-relaxed"><?= nl2br(htmlspecialchars($l['ringkasan'])) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-gray-400"><?= date('d M Y', strtotime($l['created_at'])) ?></p>
                                <?php if ($l['file_path']): ?>
                                <a href="../uploads/<?= htmlspecialchars($l['file_path']) ?>" target="_blank"
                                   class="inline-flex items-center gap-1.5 mt-2 text-xs text-blue-600 hover:text-blue-800 font-medium bg-blue-50 px-3 py-1.5 rounded-lg">
                                    <i class="fas fa-download"></i> Download File
                                </a>
                                <?php else: ?>
                                <span class="inline-block mt-2 text-xs text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg">Tidak ada file</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Form Feedback -->
                        <div class="mt-5 border-t border-gray-100 pt-5">
                            <?php if ($l['total_feedback'] > 0): ?>
                            <p class="text-xs text-gray-400 mb-3"><?= $l['total_feedback'] ?> feedback sudah diberikan</p>
                            <?php endif; ?>
                            <form method="POST" class="flex gap-3">
                                <input type="hidden" name="laporan_id" value="<?= $l['id'] ?>">
                                <textarea name="feedback" rows="2" placeholder="Tulis feedback atau catatan untuk laporan ini..."
                                          class="flex-1 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" required></textarea>
                                <div class="flex flex-col gap-2">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors whitespace-nowrap">
                                        <i class="fas fa-paper-plane mr-1"></i> Kirim
                                    </button>
                                    <?php if ($l['status'] !== 'disetujui'): ?>
                                    <button type="submit" name="setujui" value="1" onclick="this.form.querySelector('[name=feedback]').required=false"
                                            formaction="?setujui=<?= $l['id'] ?>"
                                            class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-xl hover:bg-green-700 transition-colors whitespace-nowrap">
                                        <i class="fas fa-check mr-1"></i> Setujui
                                    </button>
                                    <?php endif; ?>
                                </div>
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
<?php
// Handle setujui action via GET
if (isset($_GET['setujui'])) {
    $id = (int)$_GET['setujui'];
    $conn->prepare("UPDATE final_reports SET status='disetujui', updated_at=NOW() WHERE id=?")->execute([$id]);
    header("Location: laporan_akhir.php?ok=1");
    exit;
}
?>
</body>
</html>
