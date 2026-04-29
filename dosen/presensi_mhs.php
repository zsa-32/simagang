<?php
    require_once __DIR__ . "/../config/role_guard.php";
    checkRole("dosen_pembimbing");
    require_once __DIR__ . '/../config/db_connect.php';
    $role = 'dosen';
    $activePage = 'presensi';

    $userId = $_SESSION['id_user'];
    $stmt = $conn->prepare("SELECT id FROM dosen_pembimbing WHERE user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $dosen = $stmt->fetch();
    $dosenId = $dosen ? $dosen['id'] : 0;

    // Date navigation
    $tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
    $dateObj = new DateTime($tanggal);
    $prevDate = (clone $dateObj)->modify('-1 day')->format('Y-m-d');
    $nextDate = (clone $dateObj)->modify('+1 day')->format('Y-m-d');
    $days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $dayName   = $days[(int)$dateObj->format('w')];
    $monthName = $months[(int)$dateObj->format('n') - 1];
    $displayDate = $dayName . ', ' . $dateObj->format('j') . ' ' . $monthName . ' ' . $dateObj->format('Y');

    // Get all mahasiswa bimbingan
    $stmt = $conn->prepare("
        SELECT m.id, m.nama, m.no_ktm,
               c.nama_perusahaan
        FROM mahasiswa m
        JOIN `groups` g ON m.group_id = g.id
        LEFT JOIN companies c ON g.company_id = c.id
        WHERE g.dosen_pembimbing_id = :did
        ORDER BY m.nama
    ");
    $stmt->execute(['did' => $dosenId]);
    $allMhs = $stmt->fetchAll();

    // Get attendance for the selected date
    $mhsIds = array_column($allMhs, 'id');
    $attMap = [];
    if (!empty($mhsIds)) {
        $placeholders = implode(',', array_fill(0, count($mhsIds), '?'));
        $stmt = $conn->prepare("
            SELECT mahasiswa_id, status, checkin_time,
                   TIME_FORMAT(checkin_time, '%H:%i') as jam_masuk
            FROM attendances
            WHERE mahasiswa_id IN ($placeholders) AND date = ?
        ");
        $params = array_merge($mhsIds, [$tanggal]);
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $a) {
            $attMap[$a['mahasiswa_id']] = $a;
        }
    }

    // Build presences array
    $presences = [];
    $statHadir = 0; $statIzin = 0; $statAlpha = 0; $statBelum = 0;
    foreach ($allMhs as $m) {
        $att = $attMap[$m['id']] ?? null;
        if ($att) {
            $status = $att['status'];
            $masuk = $att['jam_masuk'] ?? '-';
            match($status) {
                'Hadir', 'Terlambat' => $statHadir++,
                'Izin', 'Sakit' => $statIzin++,
                'Alpha' => $statAlpha++,
                default => $statBelum++,
            };
        } else {
            $status = 'Belum Absen';
            $masuk = '-';
            $statBelum++;
        }
        $presences[] = [
            'nama' => $m['nama'],
            'nim' => $m['no_ktm'] ?? '-',
            'instansi' => $m['nama_perusahaan'] ?? '-',
            'status' => $status,
            'masuk' => $masuk,
            'keluar' => '-',
        ];
    }

    $statusConfig = [
        'Hadir'       => ['icon'=>'fa-check-circle',   'class'=>'text-green-600 bg-green-50',  'text'=>'text-green-600'],
        'Terlambat'   => ['icon'=>'fa-clock',          'class'=>'text-yellow-600 bg-yellow-50','text'=>'text-yellow-600'],
        'Izin'        => ['icon'=>'fa-clock',          'class'=>'text-orange-500 bg-orange-50','text'=>'text-orange-500'],
        'Sakit'       => ['icon'=>'fa-medkit',         'class'=>'text-orange-500 bg-orange-50','text'=>'text-orange-500'],
        'Alpha'       => ['icon'=>'fa-times-circle',   'class'=>'text-red-500 bg-red-50',      'text'=>'text-red-500'],
        'Belum Absen' => ['icon'=>'fa-question-circle','class'=>'text-gray-400 bg-gray-50',    'text'=>'text-gray-400'],
    ];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Mahasiswa - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .presence-row:hover { background-color: #f9fafb; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <div class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 md:px-8 shrink-0">
            <h1 class="text-[18px] font-bold text-gray-900">Presensi Dosen</h1>
            <div class="flex items-center gap-4"><?php include '../includes/header.php'; ?></div>
        </div>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1000px] mx-auto space-y-5">

                <!-- Section Header -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-[16px] font-bold text-gray-800">Presensi Mahasiswa</h2>
                        <p class="text-[13px] text-gray-400 mt-0.5">Pantau kehadiran harian mahasiswa bimbingan</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[13px]"></i>
                            <input type="text" id="searchPresensi" placeholder="Cari mahasiswa..."
                                   class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 w-52 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Date Navigation -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between px-6 py-4">
                        <a href="?tanggal=<?= $prevDate ?>" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500 transition-colors"><i class="fas fa-chevron-left text-[13px]"></i></a>
                        <div class="flex items-center gap-2 text-[15px] font-semibold text-gray-800"><i class="fas fa-calendar text-blue-500 text-[14px]"></i> <?= $displayDate ?></div>
                        <a href="?tanggal=<?= $nextDate ?>" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500 transition-colors"><i class="fas fa-chevron-right text-[13px]"></i></a>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center shrink-0"><i class="fas fa-check-circle text-green-500 text-[18px]"></i></div>
                        <div><p class="text-[12px] text-gray-500">Hadir</p><p class="text-2xl font-bold text-gray-900"><?= $statHadir ?></p></div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center shrink-0"><i class="fas fa-clock text-orange-400 text-[18px]"></i></div>
                        <div><p class="text-[12px] text-gray-500">Izin/Sakit</p><p class="text-2xl font-bold text-gray-900"><?= $statIzin ?></p></div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center shrink-0"><i class="fas fa-times-circle text-red-500 text-[18px]"></i></div>
                        <div><p class="text-[12px] text-gray-500">Alpha</p><p class="text-2xl font-bold text-gray-900"><?= $statAlpha ?></p></div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center shrink-0"><i class="fas fa-question-circle text-gray-400 text-[18px]"></i></div>
                        <div><p class="text-[12px] text-gray-500">Belum Absen</p><p class="text-2xl font-bold text-gray-900"><?= $statBelum ?></p></div>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-[14px]" id="presensiTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-500 text-[12px] uppercase tracking-wider">
                                    <th class="text-left px-6 py-4 font-semibold">Mahasiswa</th>
                                    <th class="text-left px-6 py-4 font-semibold">Status</th>
                                    <th class="text-left px-6 py-4 font-semibold">Jam Masuk</th>
                                    <th class="text-left px-6 py-4 font-semibold">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($presences)): ?>
                                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada mahasiswa bimbingan.</td></tr>
                                <?php else: ?>
                                <?php foreach ($presences as $p):
                                    $cfg = $statusConfig[$p['status']] ?? $statusConfig['Belum Absen'];
                                ?>
                                <tr class="presence-row transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-white font-semibold text-[13px] shrink-0"><?= strtoupper(substr($p['nama'], 0, 2)) ?></div>
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['nama']) ?></p>
                                                <p class="text-[12px] text-gray-400"><?= $p['nim'] ?> · <?= htmlspecialchars($p['instansi']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold <?= $cfg['class'] ?>">
                                            <i class="fas <?= $cfg['icon'] ?> text-[11px]"></i> <?= $p['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 font-medium"><?= $p['masuk'] ?></td>
                                    <td class="px-6 py-4 text-gray-500 text-[13px]"><span class="text-gray-300">-</span></td>
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
        document.getElementById('searchPresensi').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#presensiTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
