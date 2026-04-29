<?php
    require_once __DIR__ . "/../config/role_guard.php";
    checkRole("mahasiswa");
    require_once __DIR__ . '/../config/db_connect.php';
    $role = 'mahasiswa';
    $activePage = 'laporan';

    $userId = $_SESSION['id_user'];
    $stmt = $conn->prepare("SELECT id FROM mahasiswa WHERE user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $mhs = $stmt->fetch();
    $mhsId = $mhs ? $mhs['id'] : 0;

    $message = '';
    $msgType = 'green';

    // Handle upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['laporan'])) {
        $file = $_FILES['laporan'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf', 'doc', 'docx'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $message = 'Format file tidak didukung. Gunakan PDF, DOC, atau DOCX.';
                $msgType = 'red';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $message = 'Ukuran file melebihi 10MB.';
                $msgType = 'red';
            } else {
                $uploadDir = __DIR__ . '/../uploads/reports/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = 'report_' . $mhsId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $stmt = $conn->prepare("
                        INSERT INTO final_reports (mahasiswa_id, judul_laporan, file_path, status)
                        VALUES (:mid, :judul, :path, 'pending')
                    ");
                    $stmt->execute([
                        'mid' => $mhsId,
                        'judul' => $file['name'],
                        'path' => 'uploads/reports/' . $filename,
                    ]);
                    header("Location: laporan.php?uploaded=1");
                    exit;
                }
            }
        }
    }
    if (isset($_GET['uploaded'])) { $message = 'Laporan berhasil diupload!'; $msgType = 'green'; }

    // Get report history
    $stmt = $conn->prepare("
        SELECT id, judul_laporan, file_path, status, created_at
        FROM final_reports
        WHERE mahasiswa_id = :mid
        ORDER BY created_at DESC
    ");
    $stmt->execute(['mid' => $mhsId]);
    $reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Akhir - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .dropzone { transition: background-color 0.2s, border-color 0.2s; }
        .dropzone.drag-over { background-color: #eff6ff; border-color: #3b82f6; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <?php include '../includes/header.php'; ?>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1100px] mx-auto space-y-6">

                <?php if ($message): ?>
                <div class="bg-<?= $msgType ?>-50 border border-<?= $msgType ?>-200 text-<?= $msgType ?>-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2">
                    <i class="fas fa-<?= $msgType === 'green' ? 'check' : 'exclamation' ?>-circle"></i> <?= htmlspecialchars($message) ?>
                </div>
                <?php endif; ?>

                <!-- Page Banner -->
                <div class="bg-[#3b66f5] rounded-2xl p-8 flex items-center justify-between shadow-sm relative overflow-hidden">
                    <div class="text-white z-10">
                        <h2 class="text-[24px] font-bold mb-2 tracking-tight">Laporan Akhir PKL</h2>
                        <p class="text-blue-100 text-[15px]">Upload dan kelola laporan akhir praktik kerja lapangan Anda</p>
                    </div>
                    <div class="hidden md:flex z-10 items-center justify-center relative mr-4">
                        <div class="relative w-28 h-28">
                            <div class="w-16 h-20 bg-white/20 rounded-t-2xl absolute bottom-0 left-4 backdrop-blur-sm border border-white/10"></div>
                            <div class="w-10 h-10 bg-blue-300/60 rounded-full absolute top-2 left-8 border-2 border-white/30"></div>
                            <div class="w-12 h-8 bg-white/30 rounded-md absolute bottom-6 left-0 shadow-md flex items-center justify-center">
                                <i class="fas fa-file-pdf text-white text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="absolute right-0 top-0 w-80 h-full bg-gradient-to-l from-[#254bdb] to-transparent z-0 opacity-80"></div>
                </div>

                <!-- Upload Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <h3 class="text-[17px] font-bold text-gray-800 mb-5">Upload Laporan Akhir</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div id="dropzone"
                             class="dropzone border-2 border-dashed border-gray-300 rounded-xl p-10 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-blue-50/40 hover:border-blue-400 transition-all"
                             onclick="document.getElementById('fileInput').click()"
                             ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
                            <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-cloud-upload-alt text-[22px] text-blue-500"></i>
                            </div>
                            <p class="text-[15px] font-medium text-gray-700 mb-1">Drag & drop file laporan Anda di sini</p>
                            <p class="text-[13px] text-gray-400 mb-5">atau klik untuk memilih file</p>
                            <button type="button" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white px-6 py-2.5 rounded-lg font-medium text-[14px] transition-colors shadow-sm">Pilih File</button>
                            <p class="text-[12px] text-gray-400 mt-4">Format yang didukung: PDF, DOC, DOCX (Max: 10MB)</p>
                            <input type="file" id="fileInput" name="laporan" class="hidden" accept=".pdf,.doc,.docx" onchange="handleFileSelect(event)">
                        </div>
                        <div id="fileSelected" class="hidden mt-4 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center"><i class="fas fa-file-pdf text-red-500 text-[16px]"></i></div>
                                <div>
                                    <p id="fileName" class="text-[14px] font-medium text-gray-800">-</p>
                                    <p id="fileSize" class="text-[12px] text-gray-400">-</p>
                                </div>
                            </div>
                            <button type="button" onclick="clearFile()" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-[16px]"></i></button>
                        </div>
                        <div class="mt-5 flex justify-end">
                            <button type="submit" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white px-6 py-2.5 rounded-lg font-medium text-[14px] transition-colors shadow-sm flex items-center gap-2">
                                <i class="fas fa-upload text-[13px]"></i> Upload Laporan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Riwayat Laporan -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 md:px-8 py-5 border-b border-gray-100">
                        <h3 class="text-[17px] font-bold text-gray-800">Riwayat Laporan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[14px] border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-500 text-[13px]">
                                    <th class="px-6 md:px-8 py-4 font-semibold text-center">Nama File</th>
                                    <th class="px-6 py-4 font-semibold text-center">Tanggal Upload</th>
                                    <th class="px-6 py-4 font-semibold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($reports)): ?>
                                <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">Belum ada laporan yang diupload.</td></tr>
                                <?php else: ?>
                                <?php foreach ($reports as $rpt):
                                    $statusColor = match($rpt['status']) { 'approved' => 'text-[#10b981]', 'rejected' => 'text-[#ef4444]', default => 'text-[#f97316]' };
                                    $statusLabel = match($rpt['status']) { 'approved' => 'Approved', 'rejected' => 'Rejected', default => 'Pending' };
                                ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 md:px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center shrink-0"><i class="fas fa-file-pdf text-red-500 text-[15px]"></i></div>
                                            <span class="font-medium text-gray-800"><?= htmlspecialchars($rpt['judul_laporan']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-gray-500 text-center"><?= date('d M Y', strtotime($rpt['created_at'])) ?></td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="<?= $statusColor ?> font-semibold text-[13px]"><?= $statusLabel ?></span>
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

    <script>
        function handleDragOver(e) { e.preventDefault(); document.getElementById('dropzone').classList.add('drag-over', 'bg-blue-50', 'border-blue-400'); }
        function handleDragLeave(e) { document.getElementById('dropzone').classList.remove('drag-over', 'bg-blue-50', 'border-blue-400'); }
        function handleDrop(e) { e.preventDefault(); handleDragLeave(e); if (e.dataTransfer.files.length > 0) showFile(e.dataTransfer.files[0]); }
        function handleFileSelect(e) { if (e.target.files.length > 0) showFile(e.target.files[0]); }
        function showFile(file) {
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            document.getElementById('fileSelected').classList.remove('hidden');
        }
        function clearFile() { document.getElementById('fileInput').value = ''; document.getElementById('fileSelected').classList.add('hidden'); }
    </script>
</body>
</html>
