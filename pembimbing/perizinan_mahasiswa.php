<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("pembimbing_lapang");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'pembimbing';
$activePage = 'perizinan_mahasiswa';

$userId = $_SESSION['id_user'];
$plRow = $conn->prepare("SELECT pl.id, pl.company_id, c.nama_perusahaan FROM pembimbing_lapang pl LEFT JOIN companies c ON pl.company_id = c.id WHERE pl.user_id = ?");
$plRow->execute([$userId]);
$plRow = $plRow->fetch();
$plId      = $plRow['id'] ?? null;
$companyId = $plRow['company_id'] ?? null;
$companyName = $plRow['nama_perusahaan'] ?? '-';

$msg = ''; $msgType = '';

// Handle approve / reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action_type'])) {
    $reqId = (int)$_POST['request_id'];
    $actionType = $_POST['action_type'];
    $catatan = trim($_POST['catatan'] ?? '');

    // Verify the request belongs to a student in this company
    $chk = $conn->prepare("
        SELECT lr.id, lr.kategori, lr.dari_tanggal, lr.sampai_tanggal, lr.mahasiswa_id
        FROM leave_requests lr
        JOIN mahasiswa m ON lr.mahasiswa_id = m.id
        JOIN `groups` g ON m.group_id = g.id
        WHERE lr.id = ? AND g.company_id = ? AND lr.status = 'pending'
    ");
    $chk->execute([$reqId, $companyId]);
    $req = $chk->fetch();

    if ($req) {
        if ($actionType === 'approve') {
            // Update leave request status
            $conn->prepare("UPDATE leave_requests SET status = 'approved', reviewed_by = ?, reviewed_at = NOW(), catatan_reviewer = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$userId, $catatan ?: null, $reqId]);

            // Insert into attendances for each date in range
            $status = ($req['kategori'] === 'sakit') ? 'Sakit' : 'Izin';
            $startDate = new DateTime($req['dari_tanggal']);
            $endDate = new DateTime($req['sampai_tanggal']);
            $endDate->modify('+1 day');
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($startDate, $interval, $endDate);

            $insertStmt = $conn->prepare("
                INSERT INTO attendances (mahasiswa_id, date, status, created_at)
                VALUES (:mid, :dt, :st, NOW())
                ON DUPLICATE KEY UPDATE status = :st2
            ");

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $insertStmt->execute([
                    'mid' => $req['mahasiswa_id'],
                    'dt' => $dateStr,
                    'st' => $status,
                    'st2' => $status,
                ]);
            }

            $msg = 'Izin berhasil disetujui. Status absensi telah diperbarui.';
            $msgType = 'success';
        } else {
            // Reject
            $conn->prepare("UPDATE leave_requests SET status = 'ditolak', reviewed_by = ?, reviewed_at = NOW(), catatan_reviewer = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$userId, $catatan ?: null, $reqId]);

            $msg = 'Izin berhasil ditolak.';
            $msgType = 'success';
        }
    } else {
        $msg = 'Pengajuan tidak ditemukan atau sudah diproses.';
        $msgType = 'error';
    }
}

// Fetch students in this company
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
$filterStatus = $_GET['status'] ?? '';

// Fetch leave requests
$leaveRequests = [];
if (!empty($mhsList)) {
    $mhsIds = array_column($mhsList, 'id');
    $inQ = implode(',', array_fill(0, count($mhsIds), '?'));
    $params = $mhsIds;

    $whereExtra = '';
    if ($filterStatus) { $whereExtra .= " AND lr.status = ?"; $params[] = $filterStatus; }

    $stmt = $conn->prepare("
        SELECT lr.*, m.nama as nama_mhs, m.no_ktm as nim_mhs
        FROM leave_requests lr
        JOIN mahasiswa m ON lr.mahasiswa_id = m.id
        WHERE lr.mahasiswa_id IN ($inQ) $whereExtra
        ORDER BY FIELD(lr.status, 'pending', 'approved', 'ditolak'), lr.created_at DESC
    ");
    $stmt->execute($params);
    $leaveRequests = $stmt->fetchAll();
}

// Stats
$statPending = 0; $statApproved = 0; $statRejected = 0;
foreach ($leaveRequests as $lr) {
    match($lr['status']) {
        'pending' => $statPending++,
        'approved' => $statApproved++,
        'ditolak' => $statRejected++,
        default => null,
    };
}

$kategoriLabel = [
    'sakit' => 'Sakit',
    'keperluan_kampus' => 'Keperluan Kampus',
    'keperluan_keluarga' => 'Keperluan Keluarga',
    'lainnya' => 'Lainnya',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perizinan Mahasiswa - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
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

            <?php if ($msg): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNotifModal('<?= $msgType === 'success' ? 'success' : 'error' ?>', '<?= addslashes($msg) ?>');
                });
            </script>
            <?php endif; ?>

            <!-- Header -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div>
                    <h2 class="text-[18px] font-bold text-gray-800">Perizinan Mahasiswa</h2>
                    <p class="text-[13px] text-gray-400 mt-0.5">Kelola pengajuan izin mahasiswa di <?= htmlspecialchars($companyName) ?></p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center shrink-0"><i class="fas fa-clock text-orange-400 text-[20px]"></i></div>
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
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                    <select name="status" class="border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $filterStatus === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="ditolak" <?= $filterStatus === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="perizinan_mahasiswa.php" class="bg-gray-100 text-gray-600 px-5 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition-colors">Reset</a>
            </form>

            <!-- Leave Requests Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-[14px]">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-500 text-[12px] uppercase tracking-wider">
                                <th class="text-left px-6 py-4 font-semibold">Mahasiswa</th>
                                <th class="text-left px-6 py-4 font-semibold">Kategori</th>
                                <th class="text-left px-6 py-4 font-semibold">Tanggal</th>
                                <th class="text-left px-6 py-4 font-semibold">Alasan</th>
                                <th class="text-center px-6 py-4 font-semibold">Status</th>
                                <th class="text-center px-6 py-4 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($leaveRequests)): ?>
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada pengajuan izin.</td></tr>
                            <?php else: ?>
                            <?php foreach ($leaveRequests as $lr):
                                $statusClass = match($lr['status']) {
                                    'approved' => 'bg-green-100 text-green-700',
                                    'ditolak' => 'bg-red-100 text-red-600',
                                    default => 'bg-yellow-100 text-yellow-700',
                                };
                                $statusLabel = match($lr['status']) {
                                    'approved' => 'Disetujui',
                                    'ditolak' => 'Ditolak',
                                    default => 'Pending',
                                };
                            ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-gray-800 flex items-center justify-center text-white font-semibold text-[12px] shrink-0"><?= strtoupper(substr($lr['nama_mhs'], 0, 2)) ?></div>
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($lr['nama_mhs']) ?></p>
                                            <p class="text-[12px] text-gray-400"><?= $lr['nim_mhs'] ?? '-' ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[12px] font-medium <?= $lr['kategori'] === 'sakit' ? 'bg-orange-50 text-orange-600' : 'bg-blue-50 text-blue-600' ?>">
                                        <?= htmlspecialchars($kategoriLabel[$lr['kategori']] ?? ucfirst($lr['kategori'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap text-[13px]">
                                    <?= date('d M Y', strtotime($lr['dari_tanggal'])) ?>
                                    <?php if ($lr['dari_tanggal'] !== $lr['sampai_tanggal']): ?>
                                        — <?= date('d M Y', strtotime($lr['sampai_tanggal'])) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-gray-600 max-w-[200px]"><span class="line-clamp-1"><?= htmlspecialchars($lr['alasan']) ?></span></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[12px] font-semibold <?= $statusClass ?>"><?= $statusLabel ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick='openDetail(<?= json_encode($lr, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                            class="flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-[13px] font-medium hover:underline transition-colors mx-auto">
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
    <div id="detailBox" class="bg-white rounded-2xl w-full max-w-[520px] max-h-[85vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-[17px] font-bold text-gray-900">Detail Pengajuan Izin</h3>
            <button onclick="closeDetail()" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors"><i class="fas fa-times text-gray-400 text-[14px]"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Mahasiswa</p><p id="d-nama" class="text-[15px] font-bold text-gray-800"></p></div>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Kategori</p><p id="d-kategori" class="text-[14px] text-gray-700"></p></div>
                <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Status</p><p id="d-status"></p></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Dari Tanggal</p><p id="d-dari" class="text-[14px] text-gray-700"></p></div>
                <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Sampai Tanggal</p><p id="d-sampai" class="text-[14px] text-gray-700"></p></div>
            </div>
            <div><p class="text-[11px] font-semibold text-gray-400 mb-1">Alasan</p><p id="d-alasan" class="text-[14px] text-gray-700 leading-relaxed bg-gray-50 rounded-xl px-4 py-3"></p></div>

            <!-- Bukti -->
            <div id="d-bukti-wrap" style="display:none;">
                <p class="text-[11px] font-semibold text-gray-400 mb-2">Bukti Pendukung</p>
                <a id="d-bukti-link" href="#" target="_blank" class="inline-flex items-center gap-2 text-[13px] text-blue-600 hover:text-blue-800 font-medium bg-blue-50 px-4 py-2.5 rounded-xl border border-blue-100 hover:border-blue-200 transition-colors">
                    <i class="fas fa-file-download"></i> <span id="d-bukti-name">Lihat Bukti</span>
                </a>
            </div>

            <!-- Action form (only for pending) -->
            <form method="POST" id="d-form">
                <input type="hidden" name="request_id" id="d-id">
                <input type="hidden" name="action_type" id="d-action-type">
                <div id="d-action-section" class="border-t border-gray-100 pt-4 space-y-3">
                    <div>
                        <label class="block text-[13px] font-semibold text-gray-700 mb-2">Catatan (opsional)</label>
                        <textarea name="catatan" rows="2" placeholder="Berikan catatan..."
                                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] text-gray-700 resize-none outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" onclick="submitAction('reject')" class="px-5 py-2.5 border border-red-200 text-red-600 rounded-xl text-[13px] font-semibold hover:bg-red-50 transition-colors">
                            <i class="fas fa-times text-[11px] mr-1"></i> Tolak
                        </button>
                        <button type="button" onclick="submitAction('approve')" class="px-5 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm">
                            <i class="fas fa-check text-[11px] mr-1"></i> Setujui
                        </button>
                    </div>
                </div>

                <!-- Catatan reviewer (for already reviewed) -->
                <div id="d-reviewed-section" style="display:none;" class="border-t border-gray-100 pt-4">
                    <p class="text-[11px] font-semibold text-gray-400 mb-1">Catatan Reviewer</p>
                    <p id="d-catatan" class="text-[14px] text-gray-600 bg-gray-50 rounded-xl px-4 py-3"></p>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div id="notifModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-[60] flex items-center justify-center p-4" onclick="if(event.target===this)closeNotifModal()">
    <div class="bg-white rounded-2xl w-full max-w-[400px] shadow-2xl overflow-hidden" style="transition: transform 0.3s cubic-bezier(.34,1.56,.64,1), opacity 0.25s ease;">
        <div class="p-8 text-center">
            <div id="notifIcon" class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center"></div>
            <h3 id="notifTitle" class="text-[18px] font-bold text-gray-900 mb-2"></h3>
            <p id="notifMessage" class="text-[14px] text-gray-500 leading-relaxed"></p>
        </div>
        <div class="px-8 pb-6 flex justify-center">
            <button onclick="closeNotifModal()" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[14px] font-semibold transition-colors shadow-sm">OK</button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-[60] flex items-center justify-center p-4" onclick="if(event.target===this)closeConfirmModal()">
    <div class="bg-white rounded-2xl w-full max-w-[420px] shadow-2xl overflow-hidden" style="transition: transform 0.3s cubic-bezier(.34,1.56,.64,1), opacity 0.25s ease;">
        <div class="p-8 text-center">
            <div id="confirmIcon" class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center"></div>
            <h3 id="confirmTitle" class="text-[18px] font-bold text-gray-900 mb-2"></h3>
            <p id="confirmMessage" class="text-[14px] text-gray-500 leading-relaxed"></p>
        </div>
        <div class="px-8 pb-6 flex justify-center gap-3">
            <button onclick="closeConfirmModal()" class="px-6 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-[14px] font-semibold hover:bg-gray-50 transition-colors">Batal</button>
            <button id="confirmBtn" onclick="" class="px-6 py-2.5 rounded-xl text-[14px] font-semibold transition-colors shadow-sm">Konfirmasi</button>
        </div>
    </div>
</div>

<script>
    const kategoriMap = <?= json_encode($kategoriLabel) ?>;

    // Notification Modal
    function showNotifModal(type, message) {
        const icon = document.getElementById('notifIcon');
        const title = document.getElementById('notifTitle');
        const msg = document.getElementById('notifMessage');
        if (type === 'success') {
            icon.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-green-100';
            icon.innerHTML = '<i class="fas fa-check-circle text-green-500 text-[32px]"></i>';
            title.textContent = 'Berhasil';
        } else {
            icon.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-red-100';
            icon.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 text-[32px]"></i>';
            title.textContent = 'Perhatian';
        }
        msg.textContent = message;
        document.getElementById('notifModal').classList.remove('hidden');
    }
    function closeNotifModal() { document.getElementById('notifModal').classList.add('hidden'); }

    // Confirmation Modal
    function showConfirmModal(type, message, onConfirm) {
        const icon = document.getElementById('confirmIcon');
        const title = document.getElementById('confirmTitle');
        const msg = document.getElementById('confirmMessage');
        const btn = document.getElementById('confirmBtn');
        if (type === 'approve') {
            icon.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-green-100';
            icon.innerHTML = '<i class="fas fa-check-circle text-green-500 text-[32px]"></i>';
            title.textContent = 'Setujui Izin?';
            btn.className = 'px-6 py-2.5 rounded-xl text-[14px] font-semibold transition-colors shadow-sm bg-green-500 hover:bg-green-600 text-white';
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Ya, Setujui';
        } else {
            icon.className = 'w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center bg-red-100';
            icon.innerHTML = '<i class="fas fa-times-circle text-red-500 text-[32px]"></i>';
            title.textContent = 'Tolak Izin?';
            btn.className = 'px-6 py-2.5 rounded-xl text-[14px] font-semibold transition-colors shadow-sm bg-red-500 hover:bg-red-600 text-white';
            btn.innerHTML = '<i class="fas fa-times mr-1"></i> Ya, Tolak';
        }
        msg.textContent = message;
        btn.onclick = function() { closeConfirmModal(); onConfirm(); };
        document.getElementById('confirmModal').classList.remove('hidden');
    }
    function closeConfirmModal() { document.getElementById('confirmModal').classList.add('hidden'); }

    function openDetail(lr) {
        document.getElementById('d-nama').textContent = lr.nama_mhs;
        document.getElementById('d-kategori').textContent = kategoriMap[lr.kategori] || lr.kategori;
        document.getElementById('d-dari').textContent = lr.dari_tanggal;
        document.getElementById('d-sampai').textContent = lr.sampai_tanggal;
        document.getElementById('d-alasan').textContent = lr.alasan;
        document.getElementById('d-id').value = lr.id;

        const statusMap = {
            'approved': ['Disetujui', 'bg-green-100 text-green-700'],
            'ditolak': ['Ditolak', 'bg-red-100 text-red-600'],
            'pending': ['Pending', 'bg-yellow-100 text-yellow-700']
        };
        const [label, cls] = statusMap[lr.status] || statusMap.pending;
        document.getElementById('d-status').innerHTML = `<span class="px-3 py-1 rounded-full text-[12px] font-semibold ${cls}">${label}</span>`;

        // Bukti
        const buktiWrap = document.getElementById('d-bukti-wrap');
        if (lr.bukti) {
            document.getElementById('d-bukti-link').href = '../' + lr.bukti;
            document.getElementById('d-bukti-name').textContent = lr.bukti.split('/').pop();
            buktiWrap.style.display = 'block';
        } else {
            buktiWrap.style.display = 'none';
        }

        // Show/hide action buttons
        const actionSection = document.getElementById('d-action-section');
        const reviewedSection = document.getElementById('d-reviewed-section');
        if (lr.status === 'pending') {
            actionSection.style.display = 'block';
            reviewedSection.style.display = 'none';
        } else {
            actionSection.style.display = 'none';
            if (lr.catatan_reviewer) {
                document.getElementById('d-catatan').textContent = lr.catatan_reviewer;
                reviewedSection.style.display = 'block';
            } else {
                reviewedSection.style.display = 'none';
            }
        }

        document.getElementById('detailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDetail() {
        document.getElementById('detailModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function submitAction(type) {
        document.getElementById('d-action-type').value = type;
        const actionLabel = type === 'approve' ? 'menyetujui' : 'menolak';
        showConfirmModal(type, 'Apakah Anda yakin ingin ' + actionLabel + ' pengajuan izin ini?', function() {
            document.getElementById('d-form').submit();
        });
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeDetail(); closeNotifModal(); closeConfirmModal(); }
    });
</script>
</body>
</html>
