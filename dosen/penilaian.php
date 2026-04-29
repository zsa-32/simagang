<?php
    require_once __DIR__ . "/../config/role_guard.php";
    checkRole("dosen_pembimbing");
    require_once __DIR__ . '/../config/db_connect.php';
    $role = 'dosen';
    $activePage = 'penilaian';

    $userId = $_SESSION['id_user'];
    $stmt = $conn->prepare("SELECT id FROM dosen_pembimbing WHERE user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $dosenId = ($stmt->fetch())['id'] ?? 0;

    function getGrade(int $n): string { return $n >= 85 ? 'A' : ($n >= 70 ? 'B' : ($n >= 55 ? 'C' : ($n >= 40 ? 'D' : 'E'))); }
    function gradeColor(string $g): string { return match($g) { 'A' => 'text-green-600', 'B' => 'text-blue-600', 'C' => 'text-yellow-600', default => 'text-red-500' }; }

    // Handle save
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mhs_id'])) {
        $mhsId = (int)$_POST['mhs_id'];
        $nilaiLaporan = $_POST['nilai_laporan'] !== '' ? (int)$_POST['nilai_laporan'] : null;
        $nilaiSeminar = $_POST['nilai_seminar'] !== '' ? (int)$_POST['nilai_seminar'] : null;
        $catatan = trim($_POST['catatan'] ?? '');

        // Verify ownership
        $chk = $conn->prepare("SELECT m.id FROM mahasiswa m JOIN `groups` g ON m.group_id = g.id WHERE m.id = :mid AND g.dosen_pembimbing_id = :did");
        $chk->execute(['mid' => $mhsId, 'did' => $dosenId]);
        if ($chk->fetch()) {
            // Get or create komponen "Laporan" and "Seminar"
            $getOrCreate = function($nama, $bobot) use ($conn) {
                $s = $conn->prepare("SELECT id FROM komponen_penilaian WHERE nama = :n");
                $s->execute(['n' => $nama]);
                $r = $s->fetch();
                if ($r) return $r['id'];
                $conn->prepare("INSERT INTO komponen_penilaian (nama, bobot_persen) VALUES (:n, :b)")->execute(['n' => $nama, 'b' => $bobot]);
                return $conn->lastInsertId();
            };

            $getOrCreateKriteria = function($kompId, $nama) use ($conn) {
                $s = $conn->prepare("SELECT id FROM kriteria_penilaian WHERE komponen_id = :kid AND nama = :n");
                $s->execute(['kid' => $kompId, 'n' => $nama]);
                $r = $s->fetch();
                if ($r) return $r['id'];
                $conn->prepare("INSERT INTO kriteria_penilaian (komponen_id, nama, bobot, nilai_max) VALUES (:kid, :n, 100, 100)")->execute(['kid' => $kompId, 'n' => $nama]);
                return $conn->lastInsertId();
            };

            if ($nilaiLaporan !== null) {
                $kompId = $getOrCreate('Laporan Akhir', 50);
                $krId = $getOrCreateKriteria($kompId, 'Nilai Laporan Akhir');
                $grade = getGrade($nilaiLaporan);
                $conn->prepare("INSERT INTO nilai_kriteria (mahasiswa_id, kriteria_id, penilai_user_id, nilai_angka, nilai_huruf) VALUES (:mid, :kid, :uid, :val, :gr) ON DUPLICATE KEY UPDATE nilai_angka = :val2, nilai_huruf = :gr2, updated_at = NOW()")
                    ->execute(['mid' => $mhsId, 'kid' => $krId, 'uid' => $userId, 'val' => $nilaiLaporan, 'gr' => $grade, 'val2' => $nilaiLaporan, 'gr2' => $grade]);
            }
            if ($nilaiSeminar !== null) {
                $kompId = $getOrCreate('Seminar/Presentasi', 50);
                $krId = $getOrCreateKriteria($kompId, 'Nilai Seminar');
                $grade = getGrade($nilaiSeminar);
                $conn->prepare("INSERT INTO nilai_kriteria (mahasiswa_id, kriteria_id, penilai_user_id, nilai_angka, nilai_huruf) VALUES (:mid, :kid, :uid, :val, :gr) ON DUPLICATE KEY UPDATE nilai_angka = :val2, nilai_huruf = :gr2, updated_at = NOW()")
                    ->execute(['mid' => $mhsId, 'kid' => $krId, 'uid' => $userId, 'val' => $nilaiSeminar, 'gr' => $grade, 'val2' => $nilaiSeminar, 'gr2' => $grade]);
            }

            // Update penilaian_results
            $avgStmt = $conn->prepare("SELECT AVG(nilai_angka) as avg_val FROM nilai_kriteria WHERE mahasiswa_id = :mid");
            $avgStmt->execute(['mid' => $mhsId]);
            $avg = (float)($avgStmt->fetchColumn() ?? 0);
            $finalGrade = getGrade((int)round($avg));
            $conn->prepare("INSERT INTO penilaian_results (mahasiswa_id, nilai_akhir, grade) VALUES (:mid, :val, :gr) ON DUPLICATE KEY UPDATE nilai_akhir = :val2, grade = :gr2, updated_at = NOW()")
                ->execute(['mid' => $mhsId, 'val' => $avg, 'gr' => $finalGrade, 'val2' => $avg, 'gr2' => $finalGrade]);
        }
        header("Location: penilaian.php?saved=1");
        exit;
    }

    // Get students
    $stmt = $conn->prepare("
        SELECT m.id, m.nama, m.no_ktm, c.nama_perusahaan,
               fr.judul_laporan as file, fr.created_at as fileDate, fr.status as fileStatus
        FROM mahasiswa m
        JOIN `groups` g ON m.group_id = g.id
        LEFT JOIN companies c ON g.company_id = c.id
        LEFT JOIN final_reports fr ON fr.mahasiswa_id = m.id
        WHERE g.dosen_pembimbing_id = :did
        ORDER BY m.nama
    ");
    $stmt->execute(['did' => $dosenId]);
    $studentsRaw = $stmt->fetchAll();

    // Enrich with scores
    $students = [];
    foreach ($studentsRaw as $s) {
        $nk = $conn->prepare("
            SELECT kp.nama as komponen, nk.nilai_angka
            FROM nilai_kriteria nk
            JOIN kriteria_penilaian kr ON nk.kriteria_id = kr.id
            JOIN komponen_penilaian kp ON kr.komponen_id = kp.id
            WHERE nk.mahasiswa_id = :mid
        ");
        $nk->execute(['mid' => $s['id']]);
        $scores = $nk->fetchAll();
        $nilaiLaporan = null; $nilaiSeminar = null;
        foreach ($scores as $sc) {
            if (stripos($sc['komponen'], 'Laporan') !== false) $nilaiLaporan = (int)$sc['nilai_angka'];
            if (stripos($sc['komponen'], 'Seminar') !== false) $nilaiSeminar = (int)$sc['nilai_angka'];
        }
        $students[] = [
            'id' => $s['id'], 'nama' => $s['nama'], 'nim' => $s['no_ktm'] ?? '-',
            'instansi' => $s['nama_perusahaan'] ?? '-',
            'file' => $s['file'] ?? '-', 'fileDate' => $s['fileDate'] ? date('d M Y', strtotime($s['fileDate'])) : '-',
            'fileStatus' => $s['fileStatus'] ?? 'Pending',
            'nilaiLaporan' => $nilaiLaporan, 'nilaiSeminar' => $nilaiSeminar,
        ];
    }
    $sudahLengkap  = count(array_filter($students, fn($s) => $s['nilaiLaporan'] !== null && $s['nilaiSeminar'] !== null));
    $laporanKosong = count(array_filter($students, fn($s) => $s['nilaiLaporan'] === null));
    $seminarKosong = count(array_filter($students, fn($s) => $s['nilaiSeminar'] === null));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Dosen - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .accordion-panel { max-height: 0; overflow: hidden; transition: max-height 0.35s ease, opacity 0.25s ease; opacity: 0; }
        .accordion-panel.open { max-height: 600px; opacity: 1; }
        .chevron-icon { transition: transform 0.3s ease; }
        .accordion-item.open .chevron-icon { transform: rotate(180deg); }
        .student-header { cursor: pointer; transition: background-color 0.15s ease; }
        .student-header:hover { background-color: #f9fafb; }
        .accordion-item.open .student-header { background-color: #f9fafb; }
        .grade-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <div class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 md:px-8 shrink-0">
            <h1 class="text-[18px] font-bold text-gray-900">Penilaian Dosen</h1>
            <div class="flex items-center gap-4"><?php include '../includes/header.php'; ?></div>
        </div>
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[900px] mx-auto space-y-5">

                <?php if (isset($_GET['saved'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Nilai berhasil disimpan.
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div><h2 class="text-[16px] font-bold text-gray-800">Penilaian Magang</h2><p class="text-[13px] text-gray-400 mt-0.5">Input nilai Laporan Akhir dan Seminar/Presentasi</p></div>
                    <div class="relative"><i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[13px]"></i><input type="text" id="searchPenilaian" placeholder="Cari mahasiswa..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 w-52 transition-all"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100"><p class="text-[12px] text-gray-400 mb-2">Sudah Dinilai Lengkap</p><p class="text-3xl font-bold text-gray-900"><?= $sudahLengkap ?></p></div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100"><p class="text-[12px] text-gray-400 mb-2">Nilai Laporan Kosong</p><p class="text-3xl font-bold text-orange-500"><?= $laporanKosong ?></p></div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100"><p class="text-[12px] text-gray-400 mb-2">Nilai Seminar Kosong</p><p class="text-3xl font-bold text-red-500"><?= $seminarKosong ?></p></div>
                </div>

                <div class="space-y-3" id="studentList">
                <?php if (empty($students)): ?>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center text-gray-400">Belum ada mahasiswa bimbingan.</div>
                <?php endif; ?>
                <?php foreach ($students as $s):
                    $hasL = $s['nilaiLaporan'] !== null; $hasS = $s['nilaiSeminar'] !== null; $isLengkap = $hasL && $hasS;
                    $gradeL = $hasL ? getGrade($s['nilaiLaporan']) : null; $gradeS = $hasS ? getGrade($s['nilaiSeminar']) : null;
                    $lD = $hasL ? '<span class="font-bold '.gradeColor($gradeL).'">'.$s['nilaiLaporan'].'<span class="text-[11px] ml-0.5">('.$gradeL.')</span></span>' : '<span class="text-gray-400">-</span>';
                    $sD = $hasS ? '<span class="font-bold '.gradeColor($gradeS).'">'.$s['nilaiSeminar'].'<span class="text-[11px] ml-0.5">('.$gradeS.')</span></span>' : '<span class="text-gray-400">-</span>';
                    $fsCls = $s['fileStatus'] === 'approved' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-600';
                    $fsLbl = $s['fileStatus'] === 'approved' ? 'Disetujui' : 'Pending';
                ?>
                    <div class="accordion-item bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden student-searchable" data-name="<?= strtolower($s['nama']) ?> <?= $s['nim'] ?> <?= strtolower($s['instansi']) ?>">
                        <div class="student-header px-6 py-4 flex items-center gap-4 select-none" onclick="toggleAccordion(<?= $s['id'] ?>)">
                            <div class="w-11 h-11 rounded-full bg-gray-800 flex items-center justify-center text-white font-bold text-[13px] shrink-0"><?= strtoupper(substr($s['nama'], 0, 2)) ?></div>
                            <div class="flex-1 min-w-0"><p class="font-semibold text-gray-900 text-[15px] truncate"><?= htmlspecialchars($s['nama']) ?></p><p class="text-[12px] text-gray-400 truncate"><?= $s['nim'] ?> · <?= htmlspecialchars($s['instansi']) ?></p></div>
                            <div class="hidden sm:flex items-center gap-6 shrink-0 text-[13px]">
                                <div class="text-right"><p class="text-[11px] text-gray-400 mb-0.5">Laporan</p><?= $lD ?></div>
                                <div class="text-right"><p class="text-[11px] text-gray-400 mb-0.5">Seminar</p><?= $sD ?></div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <?php if ($isLengkap): ?><span class="flex items-center gap-1.5 bg-green-100 text-green-700 text-[12px] font-semibold px-3 py-1.5 rounded-full"><i class="fas fa-check-circle text-[11px]"></i> Lengkap</span>
                                <?php else: ?><span class="bg-orange-100 text-orange-600 text-[12px] font-semibold px-3 py-1.5 rounded-full">Belum Lengkap</span><?php endif; ?>
                                <i class="fas fa-chevron-down text-gray-400 text-[12px] chevron-icon"></i>
                            </div>
                        </div>
                        <div class="accordion-panel" id="panel-<?= $s['id'] ?>">
                            <div class="px-6 pb-6 pt-1">
                                <div class="border-t border-gray-100 mb-5"></div>
                                <div class="mb-5"><p class="text-[14px] font-semibold text-gray-800 mb-3">Laporan Akhir</p>
                                    <div class="bg-gray-50 rounded-xl px-4 py-3 flex items-center justify-between border border-gray-100">
                                        <div class="flex items-center gap-3"><div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center shrink-0"><i class="fas fa-file-pdf text-red-500 text-[14px]"></i></div><div><p class="text-[13px] font-semibold text-gray-700"><?= htmlspecialchars($s['file']) ?></p><p class="text-[11px] text-gray-400"><?= $s['fileDate'] ?></p></div></div>
                                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full <?= $fsCls ?>"><?= $fsLbl ?></span>
                                    </div>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="mhs_id" value="<?= $s['id'] ?>">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                        <div><label class="block text-[13px] font-semibold text-gray-700 mb-2">Nilai Laporan Akhir</label><input type="number" name="nilai_laporan" min="0" max="100" value="<?= $s['nilaiLaporan'] ?? '' ?>" placeholder="0 – 100" class="grade-input w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] text-gray-800 transition-all"></div>
                                        <div><label class="block text-[13px] font-semibold text-gray-700 mb-2">Nilai Seminar / Presentasi</label><input type="number" name="nilai_seminar" min="0" max="100" value="<?= $s['nilaiSeminar'] ?? '' ?>" placeholder="0 – 100" class="grade-input w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] text-gray-800 transition-all"></div>
                                    </div>
                                    <div class="mb-5"><label class="block text-[13px] font-semibold text-gray-700 mb-2">Catatan</label><textarea name="catatan" rows="3" placeholder="Umpan balik..." class="grade-input w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] text-gray-700 resize-none transition-all"></textarea></div>
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" onclick="toggleAccordion(<?= $s['id'] ?>)" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-[13px] font-semibold hover:bg-gray-50 transition-colors">Batal</button>
                                        <button type="submit" class="flex items-center gap-2 px-5 py-2.5 bg-[#3b66f5] hover:bg-[#2d53d4] text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm"><i class="fas fa-save text-[12px]"></i> Simpan Nilai</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
    <script>
        let currentOpen = null;
        function toggleAccordion(id) {
            const item = document.getElementById(`panel-${id}`).closest('.accordion-item');
            const panel = document.getElementById(`panel-${id}`);
            if (currentOpen !== null && currentOpen !== id) {
                const p = document.getElementById(`panel-${currentOpen}`);
                p.closest('.accordion-item').classList.remove('open'); p.classList.remove('open');
            }
            const isOpen = item.classList.contains('open');
            item.classList.toggle('open', !isOpen); panel.classList.toggle('open', !isOpen);
            currentOpen = isOpen ? null : id;
        }
        document.getElementById('searchPenilaian').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.student-searchable').forEach(el => { el.style.display = el.dataset.name.includes(q) ? '' : 'none'; });
        });
    </script>
</body>
</html>
