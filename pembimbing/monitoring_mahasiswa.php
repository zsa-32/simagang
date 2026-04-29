<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("pembimbing_lapang");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'pembimbing';
$activePage = 'monitoring_mahasiswa';

$userId = $_SESSION['id_user'];
$pl = $conn->prepare("SELECT id, company_id FROM pembimbing_lapang WHERE user_id = :uid");
$pl->execute(['uid' => $userId]);
$plRow = $pl->fetch();
$plId = $plRow['id'] ?? 0;

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$dt = new DateTime($tanggal);
$displayDate = $days[(int)$dt->format('w')].', '.$dt->format('j').' '.$months[(int)$dt->format('n')-1].' '.$dt->format('Y');

// Mahasiswa under this PL's groups
$mhsList = $conn->prepare("
    SELECT m.id, m.nama, m.no_ktm, c.nama_perusahaan, dp.nama as dosen_nama
    FROM mahasiswa m
    JOIN `groups` g ON m.group_id = g.id
    LEFT JOIN companies c ON g.company_id = c.id
    LEFT JOIN dosen_pembimbing dp ON g.dosen_pembimbing_id = dp.id
    WHERE g.pembimbing_lapang_id = :pid
    ORDER BY m.nama
");
$mhsList->execute(['pid' => $plId]);
$mahasiswaList = $mhsList->fetchAll();

// Attendance for selected date
$attMap = [];
if (!empty($mahasiswaList)) {
    $ids = array_column($mahasiswaList, 'id');
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $attStmt = $conn->prepare("SELECT mahasiswa_id, status, TIME_FORMAT(checkin_time, '%H:%i') as jam FROM attendances WHERE mahasiswa_id IN ($ph) AND date = ?");
    $attStmt->execute(array_merge($ids, [$tanggal]));
    foreach ($attStmt->fetchAll() as $a) $attMap[$a['mahasiswa_id']] = $a;
}

// Stats
$statH = 0; $statT = 0; $statTH = 0;
$presensiData = [];
foreach ($mahasiswaList as $m) {
    $att = $attMap[$m['id']] ?? null;
    $status = $att ? $att['status'] : 'Belum presensi';
    $waktu = $att['jam'] ?? '-';
    match($status) { 'Hadir' => $statH++, 'Terlambat' => $statT++, 'Alpha', 'Tidak Hadir' => $statTH++, default => null };
    $presensiData[] = ['id' => $m['id'], 'nama' => $m['nama'], 'nim' => $m['no_ktm'] ?? '-', 'perusahaan' => $m['nama_perusahaan'] ?? '-', 'dosen' => $m['dosen_nama'] ?? '-', 'status' => $status, 'waktu' => $waktu];
}
$totalMhs = count($mahasiswaList);

// Jurnal data
$jurnalStmt = $conn->prepare("
    SELECT m.nama, m.no_ktm as nim, l.kegiatan as judul, DATE_FORMAT(l.tanggal, '%d %b %Y') as tgl, l.status
    FROM logbooks l
    JOIN mahasiswa m ON l.mahasiswa_id = m.id
    JOIN `groups` g ON m.group_id = g.id
    WHERE g.pembimbing_lapang_id = :pid
    ORDER BY l.tanggal DESC LIMIT 20
");
$jurnalStmt->execute(['pid' => $plId]);
$jurnalData = $jurnalStmt->fetchAll();

$jurnalApproved = 0; $jurnalTotal = count($jurnalData);
foreach ($jurnalData as $j) { if ($j['status'] === 'approved') $jurnalApproved++; }

$statusConfig = [
    'Hadir' => ['dot' => 'bg-green-500', 'text' => 'text-green-600'],
    'Terlambat' => ['dot' => 'bg-amber-400', 'text' => 'text-amber-600'],
    'Tidak Hadir' => ['dot' => 'bg-red-500', 'text' => 'text-red-600'],
    'Alpha' => ['dot' => 'bg-red-500', 'text' => 'text-red-600'],
    'Izin' => ['dot' => 'bg-orange-400', 'text' => 'text-orange-600'],
    'Sakit' => ['dot' => 'bg-orange-400', 'text' => 'text-orange-600'],
    'Belum presensi' => ['dot' => 'bg-gray-300', 'text' => 'text-gray-400'],
];
$jurnalColors = ['approved' => 'bg-green-100 text-green-700', 'pending' => 'bg-amber-100 text-amber-700', 'rejected' => 'bg-red-100 text-red-600'];
$jurnalLabels = ['approved' => 'Disetujui', 'pending' => 'Menunggu', 'rejected' => 'Ditolak'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Mahasiswa - Pembimbing Lapang | Magang TIF</title>
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
            <div class="max-w-[1200px] mx-auto space-y-5">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-start justify-between mb-5">
                        <div><h2 class="text-xl font-bold text-gray-900">Monitoring Mahasiswa</h2><p class="text-gray-500 text-sm mt-0.5">Pantau presensi dan jurnal harian mahasiswa magang</p></div>
                        <div class="flex items-center gap-2 text-[13px] text-gray-500 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2">
                            <a href="?tanggal=<?= (clone $dt)->modify('-1 day')->format('Y-m-d') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-chevron-left text-[10px]"></i></a>
                            <i class="fas fa-calendar-alt text-gray-400"></i> <?= $displayDate ?>
                            <a href="?tanggal=<?= (clone $dt)->modify('+1 day')->format('Y-m-d') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-chevron-right text-[10px]"></i></a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><p class="text-[12px] text-gray-500 font-medium mb-1">Total Mahasiswa</p><p class="text-2xl font-bold text-gray-900"><?= $totalMhs ?></p></div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><p class="text-[12px] text-green-600 font-medium mb-1">Hadir</p><p class="text-2xl font-bold text-gray-900"><?= $statH ?></p></div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><p class="text-[12px] text-amber-500 font-medium mb-1">Terlambat</p><p class="text-2xl font-bold text-gray-900"><?= $statT ?></p></div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><p class="text-[12px] text-red-500 font-medium mb-1">Tidak Hadir</p><p class="text-2xl font-bold text-gray-900"><?= $statTH ?></p></div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><p class="text-[12px] text-blue-600 font-medium mb-1">Jurnal Disetujui</p><p class="text-2xl font-bold text-gray-900"><?= $jurnalApproved ?>/<?= $jurnalTotal ?></p></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-1">
                            <button id="tabPresensi" onclick="switchTab('presensi')" class="tab-btn px-4 py-2 rounded-lg text-[13px] font-semibold bg-white border border-blue-200 text-blue-600">Data Presensi</button>
                            <button id="tabJurnal" onclick="switchTab('jurnal')" class="tab-btn px-4 py-2 rounded-lg text-[13px] font-medium text-gray-500 hover:bg-gray-50 border border-transparent">Jurnal Harian</button>
                        </div>
                        <div class="relative"><i class="fas fa-search text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 text-[12px]"></i><input id="searchInput" type="text" placeholder="Cari mahasiswa..." oninput="filterRows()" class="pl-8 pr-4 py-2 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 w-52"></div>
                    </div>
                    <!-- Presensi -->
                    <div id="contentPresensi" class="overflow-x-auto">
                        <table class="w-full"><thead><tr class="border-b border-gray-50">
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Mahasiswa</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Status Kehadiran</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Dosen Pembimbing</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Waktu</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($presensiData)): ?>
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada data mahasiswa.</td></tr>
                            <?php else: foreach ($presensiData as $mhs):
                                $sc = $statusConfig[$mhs['status']] ?? $statusConfig['Belum presensi'];
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors data-row" data-search="<?= strtolower($mhs['nama'].' '.$mhs['nim']) ?>">
                                <td class="px-6 py-4"><p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($mhs['nama']) ?></p><p class="text-[12px] text-gray-400"><?= $mhs['nim'] ?> &middot; <?= htmlspecialchars($mhs['perusahaan']) ?></p></td>
                                <td class="px-6 py-4"><?php if ($mhs['status'] === 'Belum presensi'): ?><span class="text-[13px] text-gray-400 italic">Belum presensi</span><?php else: ?><span class="flex items-center gap-1.5 text-[13px] font-semibold <?= $sc['text'] ?>"><span class="w-2 h-2 rounded-full <?= $sc['dot'] ?> inline-block"></span><?= $mhs['status'] ?></span><?php endif; ?></td>
                                <td class="px-6 py-4 text-[13px] text-gray-600"><?= htmlspecialchars($mhs['dosen']) ?></td>
                                <td class="px-6 py-4 text-[13px] text-gray-600 font-medium"><?= $mhs['waktu'] ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody></table>
                    </div>
                    <!-- Jurnal -->
                    <div id="contentJurnal" class="overflow-x-auto hidden">
                        <table class="w-full"><thead><tr class="border-b border-gray-50">
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Mahasiswa</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Kegiatan</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Tanggal</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider px-6 py-3.5">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($jurnalData)): ?>
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada jurnal.</td></tr>
                            <?php else: foreach ($jurnalData as $j): ?>
                            <tr class="hover:bg-gray-50 transition-colors jurnal-row" data-search="<?= strtolower($j['nama'].' '.($j['nim']??'')) ?>">
                                <td class="px-6 py-4"><p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($j['nama']) ?></p><p class="text-[12px] text-gray-400"><?= $j['nim'] ?? '-' ?></p></td>
                                <td class="px-6 py-4 text-[13px] text-gray-700"><?= htmlspecialchars($j['judul']) ?></td>
                                <td class="px-6 py-4 text-[13px] text-gray-500"><?= $j['tgl'] ?></td>
                                <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-lg text-[12px] font-semibold <?= $jurnalColors[$j['status']] ?? 'bg-gray-100 text-gray-500' ?>"><?= $jurnalLabels[$j['status']] ?? $j['status'] ?></span></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody></table>
                    </div>
                </div>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
    <script>
        let activeTab = 'presensi';
        function switchTab(tab) {
            activeTab = tab;
            document.getElementById('contentPresensi').classList.toggle('hidden', tab !== 'presensi');
            document.getElementById('contentJurnal').classList.toggle('hidden', tab !== 'jurnal');
            document.getElementById('tabPresensi').className = 'tab-btn px-4 py-2 rounded-lg text-[13px] ' + (tab === 'presensi' ? 'font-semibold bg-white border border-blue-200 text-blue-600' : 'font-medium text-gray-500 hover:bg-gray-50 border border-transparent');
            document.getElementById('tabJurnal').className = 'tab-btn px-4 py-2 rounded-lg text-[13px] ' + (tab === 'jurnal' ? 'font-semibold bg-white border border-blue-200 text-blue-600' : 'font-medium text-gray-500 hover:bg-gray-50 border border-transparent');
            document.getElementById('searchInput').value = '';
            filterRows();
        }
        function filterRows() {
            const q = document.getElementById('searchInput').value.toLowerCase();
            const sel = activeTab === 'presensi' ? '.data-row' : '.jurnal-row';
            document.querySelectorAll(sel).forEach(r => { r.style.display = r.dataset.search.includes(q) ? '' : 'none'; });
        }
    </script>
</body>
</html>
