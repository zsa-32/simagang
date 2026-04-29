<?php
    require_once __DIR__ . "/../config/role_guard.php";
    checkRole("dosen_pembimbing");
    require_once __DIR__ . '/../config/db_connect.php';
    $role = 'dosen';
    $activePage = 'bimbingan';

    $userId = $_SESSION['id_user'];
    $stmt = $conn->prepare("SELECT id FROM dosen_pembimbing WHERE user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $dosen = $stmt->fetch();
    $dosenId = $dosen ? $dosen['id'] : 0;

    // Get all mahasiswa bimbingan with related data
    $stmt = $conn->prepare("
        SELECT m.id, m.nama, m.no_ktm, m.no_hp, m.status,
               u.email,
               c.nama_perusahaan, c.alamat_perusahaan,
               pl.nama as pl_nama,
               g.created_at as magang_mulai
        FROM mahasiswa m
        JOIN `groups` g ON m.group_id = g.id
        JOIN users u ON m.user_id = u.id
        LEFT JOIN companies c ON g.company_id = c.id
        LEFT JOIN pembimbing_lapang pl ON g.pembimbing_lapang_id = pl.id
        WHERE g.dosen_pembimbing_id = :did
        ORDER BY m.nama
    ");
    $stmt->execute(['did' => $dosenId]);
    $studentsRaw = $stmt->fetchAll();

    // Enrich with attendance data and logbook status
    $students = [];
    foreach ($studentsRaw as $s) {
        // Attendance stats
        $att = $conn->prepare("SELECT status, COUNT(*) as total FROM attendances WHERE mahasiswa_id = :mid GROUP BY status");
        $att->execute(['mid' => $s['id']]);
        $attData = $att->fetchAll();
        $hadir = 0; $izin = 0; $alpha = 0;
        foreach ($attData as $a) {
            switch ($a['status']) {
                case 'Hadir': case 'Terlambat': $hadir += (int)$a['total']; break;
                case 'Izin': case 'Sakit': $izin += (int)$a['total']; break;
                case 'Alpha': $alpha += (int)$a['total']; break;
            }
        }

        // Today's attendance
        $todayAtt = $conn->prepare("SELECT status FROM attendances WHERE mahasiswa_id = :mid AND date = CURDATE()");
        $todayAtt->execute(['mid' => $s['id']]);
        $today = $todayAtt->fetch();
        $todayStatus = $today ? $today['status'] : null;

        // Pending logbook
        $pendingLb = $conn->prepare("SELECT COUNT(*) FROM logbooks WHERE mahasiswa_id = :mid AND status = 'pending'");
        $pendingLb->execute(['mid' => $s['id']]);
        $pendingCount = (int)$pendingLb->fetchColumn();

        // Latest feedback
        $latestFb = $conn->prepare("
            SELECT fl.feedback FROM feedback_logbooks fl
            JOIN logbooks l ON fl.logbook_id = l.id
            WHERE l.mahasiswa_id = :mid
            ORDER BY fl.created_at DESC LIMIT 1
        ");
        $latestFb->execute(['mid' => $s['id']]);
        $feedback = $latestFb->fetchColumn() ?: 'Belum ada catatan bimbingan.';

        // Build badges
        $badges = [];
        if ($todayStatus === 'Hadir' || $todayStatus === 'Terlambat') {
            $badges[] = ['Hadir', 'green'];
        } elseif ($todayStatus === 'Izin' || $todayStatus === 'Sakit') {
            $badges[] = ['Izin', 'orange'];
        } elseif ($todayStatus === 'Alpha') {
            $badges[] = ['Tidak Hadir', 'red'];
        } else {
            $badges[] = ['Belum Absen', 'gray'];
        }
        if ($pendingCount > 0) {
            $badges[] = ["$pendingCount Pending", 'orange'];
        } else {
            $badges[] = ['Semua Reviewed', 'green'];
        }

        $magang_mulai = $s['magang_mulai'] ? date('d M Y', strtotime($s['magang_mulai'])) : '-';

        $students[] = [
            'nama' => $s['nama'],
            'nim' => $s['no_ktm'] ?? '-',
            'instansi' => $s['nama_perusahaan'] ?? '-',
            'alamat' => $s['alamat_perusahaan'] ?? '-',
            'pembimbing' => $s['pl_nama'] ?? '-',
            'periode' => $magang_mulai,
            'status' => $s['status'] ?? 'Aktif',
            'telp' => $s['no_hp'] ?? '-',
            'email' => $s['email'] ?? '-',
            'hadir' => $hadir,
            'izin' => $izin,
            'tidakHadir' => $alpha,
            'catatan' => $feedback,
            'badges' => $badges,
        ];
    }

    $totalMhs = count($students);
    $totalAktif = count(array_filter($students, fn($s) => $s['status'] === 'Aktif'));

    $badgeMap = [
        'green'  => 'bg-green-100 text-green-700',
        'orange' => 'bg-orange-100 text-orange-600',
        'red'    => 'bg-red-100 text-red-600',
        'gray'   => 'bg-gray-100 text-gray-500',
    ];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa Bimbingan - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .student-card { transition: transform 0.2s ease, box-shadow 0.2s ease; cursor: pointer; }
        .student-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px -8px rgba(0,0,0,0.13); }
        #studentModal { transition: opacity 0.25s ease; }
        #studentModal.hidden { opacity: 0; pointer-events: none; }
        #studentModal:not(.hidden) { opacity: 1; }
        #modalBox { transition: transform 0.3s cubic-bezier(.34,1.56,.64,1), opacity 0.25s ease; }
        #studentModal.hidden #modalBox { transform: scale(0.92) translateY(20px); opacity: 0; }
        #studentModal:not(.hidden) #modalBox { transform: scale(1) translateY(0); opacity: 1; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <div class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 md:px-8 shrink-0">
            <h1 class="text-[18px] font-bold text-gray-900">Mahasiswa Bimbingan Dosen</h1>
            <div class="flex items-center gap-4">
                <?php include '../includes/header.php'; ?>
            </div>
        </div>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1000px] mx-auto space-y-5">

                <!-- Section Header Card -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-[16px] font-bold text-gray-800">Mahasiswa Bimbingan</h2>
                        <p class="text-[13px] text-gray-400 mt-0.5">Daftar seluruh mahasiswa yang dibimbing oleh Anda</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[13px]"></i>
                            <input type="text" id="searchMhs" placeholder="Cari nama, NIM, atau perusahaan..."
                                   class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 w-64 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0"><i class="fas fa-user-friends text-blue-500 text-[20px]"></i></div>
                        <div><p class="text-[12px] text-gray-500 mb-0.5">Total Mahasiswa</p><p class="text-3xl font-bold text-gray-900"><?= $totalMhs ?></p></div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center shrink-0"><i class="fas fa-check-circle text-green-500 text-[20px]"></i></div>
                        <div><p class="text-[12px] text-gray-500 mb-0.5">Magang Aktif</p><p class="text-3xl font-bold text-gray-900"><?= $totalAktif ?></p></div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center shrink-0"><i class="fas fa-clock text-gray-400 text-[20px]"></i></div>
                        <div><p class="text-[12px] text-gray-500 mb-0.5">Selesai / Belum Mulai</p><p class="text-3xl font-bold text-gray-900"><?= $totalMhs - $totalAktif ?> <span class="text-gray-300 text-2xl">/</span> 0</p></div>
                    </div>
                </div>

                <!-- Student Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="studentGrid">
                    <?php if (empty($students)): ?>
                    <div class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center text-gray-400">
                        Belum ada mahasiswa bimbingan.
                    </div>
                    <?php endif; ?>
                    <?php foreach ($students as $idx => $s): ?>
                    <div class="student-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-3 student-searchable"
                         data-name="<?= strtolower($s['nama']) ?> <?= $s['nim'] ?> <?= strtolower($s['instansi']) ?>"
                         onclick="openModal(<?= $idx ?>)">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-gray-800 flex items-center justify-center text-white font-bold text-[14px] shrink-0"><?= strtoupper(substr($s['nama'], 0, 2)) ?></div>
                                <div>
                                    <p class="font-bold text-gray-900 text-[15px]"><?= htmlspecialchars($s['nama']) ?></p>
                                    <p class="text-[12px] text-gray-400"><?= $s['nim'] ?></p>
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold text-green-600 bg-green-50 px-2.5 py-1 rounded-full"><?= $s['status'] ?></span>
                        </div>
                        <div class="space-y-1.5 text-[13px] text-gray-500">
                            <div class="flex items-start gap-2"><i class="fas fa-building mt-0.5 w-4 text-center text-gray-400 text-[12px]"></i><span><?= htmlspecialchars($s['instansi']) ?></span></div>
                            <div class="flex items-start gap-2"><i class="fas fa-map-marker-alt mt-0.5 w-4 text-center text-gray-400 text-[12px]"></i><span class="line-clamp-1"><?= htmlspecialchars($s['alamat']) ?></span></div>
                            <div class="flex items-start gap-2"><i class="fas fa-calendar-alt mt-0.5 w-4 text-center text-gray-400 text-[12px]"></i><span>Mulai: <?= $s['periode'] ?></span></div>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                            <div class="flex flex-wrap gap-1.5">
                                <?php foreach ($s['badges'] as [$label, $color]): ?>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?= $badgeMap[$color] ?>"><?= $label ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:border-blue-400 hover:text-blue-500 transition-colors shrink-0">
                                <i class="fas fa-chevron-right text-[11px]"></i>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- MODAL -->
    <div id="studentModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="closeModalOnBackdrop(event)">
        <div id="modalBox" class="bg-white rounded-3xl w-full max-w-[520px] max-h-[90vh] overflow-y-auto shadow-2xl relative">
            <div class="bg-gradient-to-r from-[#1e40af] to-[#3b66f5] h-28 rounded-t-3xl relative">
                <button onclick="closeModal()" class="absolute top-4 right-4 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-times text-white text-[13px]"></i></button>
                <div id="m-avatar" class="absolute -bottom-7 left-6 w-16 h-16 rounded-full bg-gray-800 border-4 border-white flex items-center justify-center text-white font-bold text-[18px] shadow-lg"></div>
            </div>
            <div class="pt-10 pb-6 px-6 space-y-5">
                <div class="flex items-start justify-between"><div><h3 id="m-nama" class="text-[18px] font-bold text-gray-900"></h3><p id="m-nim" class="text-[13px] text-gray-400 mt-0.5"></p></div><span id="m-status" class="mt-1 text-[11px] font-semibold text-green-600 bg-green-50 px-3 py-1.5 rounded-full"></span></div>
                <div class="border-t border-gray-100"></div>
                <div><p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Informasi Magang</p>
                    <div class="space-y-3 text-[14px]">
                        <div class="flex items-start gap-3"><div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0"><i class="fas fa-building text-blue-500 text-[13px]"></i></div><div><p class="text-[11px] text-gray-400">Perusahaan</p><p id="m-instansi" class="font-semibold text-gray-800"></p></div></div>
                        <div class="flex items-start gap-3"><div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center shrink-0"><i class="fas fa-map-marker-alt text-red-400 text-[13px]"></i></div><div><p class="text-[11px] text-gray-400">Alamat</p><p id="m-alamat" class="font-semibold text-gray-800"></p></div></div>
                        <div class="flex items-start gap-3"><div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0"><i class="fas fa-user-tie text-purple-500 text-[13px]"></i></div><div><p class="text-[11px] text-gray-400">Pembimbing Lapang</p><p id="m-pembimbing" class="font-semibold text-gray-800"></p></div></div>
                    </div>
                </div>
                <div class="border-t border-gray-100"></div>
                <div><p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Kontak</p>
                    <div class="space-y-2.5 text-[14px]">
                        <div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0"><i class="fas fa-phone-alt text-emerald-500 text-[12px]"></i></div><p id="m-telp" class="font-medium text-gray-700"></p></div>
                        <div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0"><i class="fas fa-envelope text-indigo-500 text-[12px]"></i></div><p id="m-email" class="font-medium text-gray-700"></p></div>
                    </div>
                </div>
                <div class="border-t border-gray-100"></div>
                <div><p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Ringkasan Kehadiran</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-green-50 rounded-2xl p-4 text-center"><p id="m-hadir" class="text-2xl font-bold text-green-600"></p><p class="text-[12px] text-green-500 mt-0.5">Hadir</p></div>
                        <div class="bg-orange-50 rounded-2xl p-4 text-center"><p id="m-izin" class="text-2xl font-bold text-orange-500"></p><p class="text-[12px] text-orange-400 mt-0.5">Izin</p></div>
                        <div class="bg-red-50 rounded-2xl p-4 text-center"><p id="m-tidakhadir" class="text-2xl font-bold text-red-500"></p><p class="text-[12px] text-red-400 mt-0.5">Alpha</p></div>
                    </div>
                </div>
                <div class="border-t border-gray-100"></div>
                <div><p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Catatan Terakhir</p>
                    <div class="bg-blue-50 rounded-2xl px-4 py-3"><p id="m-catatan" class="text-[14px] text-blue-700 italic leading-relaxed"></p></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const students = <?= json_encode($students, JSON_UNESCAPED_UNICODE) ?>;
        function openModal(idx) {
            const s = students[idx];
            document.getElementById('m-avatar').textContent = s.nama.substring(0,2).toUpperCase();
            document.getElementById('m-nama').textContent = s.nama;
            document.getElementById('m-nim').textContent = s.nim;
            document.getElementById('m-status').textContent = s.status;
            document.getElementById('m-instansi').textContent = s.instansi;
            document.getElementById('m-alamat').textContent = s.alamat;
            document.getElementById('m-pembimbing').textContent = s.pembimbing;
            document.getElementById('m-telp').textContent = s.telp;
            document.getElementById('m-email').textContent = s.email;
            document.getElementById('m-hadir').textContent = s.hadir;
            document.getElementById('m-izin').textContent = s.izin;
            document.getElementById('m-tidakhadir').textContent = s.tidakHadir;
            document.getElementById('m-catatan').textContent = s.catatan;
            document.getElementById('studentModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() { document.getElementById('studentModal').classList.add('hidden'); document.body.style.overflow = ''; }
        function closeModalOnBackdrop(e) { if (e.target === document.getElementById('studentModal')) closeModal(); }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
        document.getElementById('searchMhs').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.student-searchable').forEach(card => { card.style.display = card.dataset.name.includes(q) ? '' : 'none'; });
        });
    </script>
</body>
</html>
