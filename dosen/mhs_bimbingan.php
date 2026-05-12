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
               m.jenis_kelamin, m.tempat_lahir, m.tanggal_lahir, m.agama, m.golongan_darah,
               m.alamat_asal, m.alamat_jember,
               u.email,
               c.nama_perusahaan, c.alamat_perusahaan,
               pl.nama as pl_nama, pl.jabatan as pl_jabatan,
               g.name as nama_kelompok,
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
            'jenis_kelamin' => $s['jenis_kelamin'] ?? '-',
            'tempat_lahir' => $s['tempat_lahir'] ?? '-',
            'tanggal_lahir' => $s['tanggal_lahir'] ?? null,
            'agama' => $s['agama'] ?? '-',
            'golongan_darah' => $s['golongan_darah'] ?? '-',
            'instansi' => $s['nama_perusahaan'] ?? '-',
            'alamat' => $s['alamat_perusahaan'] ?? '-',
            'pembimbing' => $s['pl_nama'] ?? '-',
            'jabatan_pl' => $s['pl_jabatan'] ?? '-',
            'nama_kelompok' => $s['nama_kelompok'] ?? '-',
            'periode' => $magang_mulai,
            'status' => $s['status'] ?? 'Aktif',
            'telp' => $s['no_hp'] ?? '-',
            'email' => $s['email'] ?? '-',
            'hadir' => $hadir,
            'izin' => $izin,
            'tidakHadir' => $alpha,
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
        /* Detail Modal */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 50;
            background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-panel {
            background: #fff; border-radius: 1.25rem; width: 95%; max-width: 620px;
            max-height: 85vh; overflow-y: auto;
            box-shadow: 0 25px 60px -12px rgba(0,0,0,0.25);
            transform: translateY(20px) scale(0.97);
            transition: transform 0.3s cubic-bezier(.22,1,.36,1);
        }
        .modal-overlay.active .modal-panel {
            transform: translateY(0) scale(1);
        }
        .modal-panel::-webkit-scrollbar { width: 6px; }
        .modal-panel::-webkit-scrollbar-track { background: transparent; }
        .modal-panel::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
        .detail-label { font-size: 12px; color: #9ca3af; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; }
        .detail-value { font-size: 14px; color: #1f2937; font-weight: 500; margin-top: 2px; }
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

    <!-- Detail Modal -->
    <div id="studentModal" class="modal-overlay" onclick="if(event.target===this)closeModal()">
        <div class="modal-panel">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-gradient-to-r from-[#1e40af] to-[#3b66f5] rounded-t-[1.25rem] px-7 py-5 flex items-center justify-between z-10">
                <div class="flex items-center gap-3">
                    <div id="m-avatar" class="w-11 h-11 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-[14px] ring-2 ring-white/30"></div>
                    <div>
                        <h3 id="m-nama" class="text-white text-[17px] font-bold"></h3>
                        <p id="m-nim" class="text-blue-200 text-[13px]"></p>
                    </div>
                </div>
                <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-white transition-colors cursor-pointer border-0">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-7 space-y-6">

                <!-- Informasi Pribadi -->
                <div>
                    <h4 class="text-[14px] font-bold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center"><i class="fas fa-user text-blue-500 text-xs"></i></span>
                        Informasi Pribadi
                    </h4>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                        <div><p class="detail-label">Jenis Kelamin</p><p id="m-jk" class="detail-value">-</p></div>
                        <div><p class="detail-label">Tempat, Tgl Lahir</p><p id="m-ttl" class="detail-value">-</p></div>
                        <div><p class="detail-label">Agama</p><p id="m-agama" class="detail-value">-</p></div>
                        <div><p class="detail-label">Golongan Darah</p><p id="m-goldar" class="detail-value">-</p></div>
                        <div><p class="detail-label">No. HP</p><p id="m-telp" class="detail-value">-</p></div>
                        <div><p class="detail-label">Email</p><p id="m-email" class="detail-value">-</p></div>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Informasi Magang -->
                <div>
                    <h4 class="text-[14px] font-bold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center"><i class="fas fa-building text-purple-500 text-xs"></i></span>
                        Informasi Magang
                    </h4>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                        <div><p class="detail-label">Kelompok</p><p id="m-kelompok" class="detail-value">-</p></div>
                        <div><p class="detail-label">Instansi / Perusahaan</p><p id="m-instansi" class="detail-value">-</p></div>
                        <div class="col-span-2"><p class="detail-label">Alamat Perusahaan</p><p id="m-alamat" class="detail-value">-</p></div>
                        <div><p class="detail-label">Pembimbing Lapang</p><p id="m-pembimbing" class="detail-value">-</p></div>
                        <div><p class="detail-label">Jabatan PL</p><p id="m-jabatanpl" class="detail-value">-</p></div>
                    </div>
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
            document.getElementById('m-nim').textContent = s.nim || 'NIM belum diisi';

            // Informasi Pribadi
            document.getElementById('m-jk').textContent = s.jenis_kelamin || '-';
            const tgl = s.tanggal_lahir ? new Date(s.tanggal_lahir).toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'}) : '-';
            document.getElementById('m-ttl').textContent = (s.tempat_lahir || '-') + ', ' + tgl;
            document.getElementById('m-agama').textContent = s.agama || '-';
            document.getElementById('m-goldar').textContent = s.golongan_darah || '-';
            document.getElementById('m-telp').textContent = s.telp || '-';
            document.getElementById('m-email').textContent = s.email || '-';

            // Informasi Magang
            document.getElementById('m-kelompok').textContent = s.nama_kelompok || '-';
            document.getElementById('m-instansi').textContent = s.instansi || '-';
            document.getElementById('m-alamat').textContent = s.alamat || '-';
            document.getElementById('m-pembimbing').textContent = s.pembimbing || '-';
            document.getElementById('m-jabatanpl').textContent = s.jabatan_pl || '-';

            document.getElementById('studentModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() { document.getElementById('studentModal').classList.remove('active'); document.body.style.overflow = ''; }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
        document.getElementById('searchMhs').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.student-searchable').forEach(card => { card.style.display = card.dataset.name.includes(q) ? '' : 'none'; });
        });
    </script>
</body>
</html>
