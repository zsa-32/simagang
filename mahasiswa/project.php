<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("mahasiswa");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'mahasiswa';
$activePage = 'project';

$userId = $_SESSION['id_user'];
$mhs = $conn->prepare("SELECT id FROM mahasiswa WHERE user_id = ?");
$mhs->execute([$userId]);
$mhs = $mhs->fetch();
$mhsId = $mhs['id'] ?? null;

$projects = [];
if ($mhsId) {
    $stmt = $conn->prepare("
        SELECT p.*, dp.nama as nama_dosen,
               (SELECT COUNT(*) FROM project_feedbacks pf WHERE pf.project_id = p.id) as total_feedback,
               (SELECT pf2.feedback FROM project_feedbacks pf2 WHERE pf2.project_id = p.id ORDER BY pf2.created_at DESC LIMIT 1) as feedback_terakhir
        FROM projects p
        LEFT JOIN dosen_pembimbing dp ON p.dosen_pembimbing_id = dp.id
        WHERE p.mahasiswa_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$mhsId]);
    $projects = $stmt->fetchAll();
}

$userName = $_SESSION['nama'] ?? 'Mahasiswa';
$statusColor = ['pending' => 'yellow', 'proses' => 'blue', 'selesai' => 'green', 'revisi' => 'red'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Magang - Magang TIF</title>
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
        <div class="max-w-[1000px] mx-auto space-y-6">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">Project Magang</h2>
                <p class="text-gray-500 text-sm mt-0.5">Tugas dan project yang diberikan oleh dosen pembimbing</p>
            </div>

            <?php if (!$mhsId): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center text-amber-700">
                <i class="fas fa-triangle-exclamation text-2xl mb-2 block"></i>
                <p class="font-semibold">Data mahasiswa belum terdaftar</p>
                <p class="text-sm mt-1">Hubungi admin untuk mendaftarkan data mahasiswa Anda.</p>
            </div>
            <?php elseif (empty($projects)): ?>
            <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow-sm border border-gray-100">
                <i class="fas fa-tasks text-5xl mb-4 block text-gray-200"></i>
                <p class="font-semibold text-gray-600 text-lg">Belum ada project</p>
                <p class="text-sm mt-2">Dosen pembimbing belum memberikan project/tugas untuk Anda.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($projects as $p):
                    $sc = $statusColor[$p['status']] ?? 'gray';
                    $statusLabel = ucfirst($p['status']);
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($p['nama_project']) ?></h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    <?= $sc === 'yellow' ? 'bg-yellow-100 text-yellow-700' : '' ?>
                                    <?= $sc === 'blue'   ? 'bg-blue-100 text-blue-700'     : '' ?>
                                    <?= $sc === 'green'  ? 'bg-green-100 text-green-700'   : '' ?>
                                    <?= $sc === 'red'    ? 'bg-red-100 text-red-700'       : '' ?>
                                    <?= $sc === 'gray'   ? 'bg-gray-100 text-gray-600'     : '' ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 flex items-center gap-2">
                                <i class="fas fa-chalkboard-teacher text-gray-400"></i>
                                <?= htmlspecialchars($p['nama_dosen'] ?? 'Dosen tidak diketahui') ?>
                            </p>
                            <?php if ($p['deskripsi']): ?>
                            <p class="text-sm text-gray-600 mt-3 leading-relaxed"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="text-xs text-gray-400 shrink-0">
                            <?= date('d M Y', strtotime($p['created_at'])) ?>
                        </div>
                    </div>

                    <?php if ($p['feedback_terakhir']): ?>
                    <div class="mt-4 bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <p class="text-xs font-semibold text-blue-600 mb-1.5 flex items-center gap-1.5">
                            <i class="fas fa-comment-dots"></i> Feedback Dosen
                        </p>
                        <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($p['feedback_terakhir'])) ?></p>
                        <?php if ((int)$p['total_feedback'] > 1): ?>
                        <p class="text-xs text-blue-500 mt-1"><?= $p['total_feedback'] ?> feedback total</p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
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
