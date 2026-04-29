<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("admin");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'admin';
$activePage = 'monitoring_mahasiswa';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$d = new DateTime($tanggal);
$displayDate = $days[(int)$d->format('w')].', '.$d->format('j').' '.$months[(int)$d->format('n')-1].' '.$d->format('Y');

// All mahasiswa
$mhsList = $conn->query("
    SELECT m.id, m.nama, m.no_ktm, c.nama_perusahaan
    FROM mahasiswa m
    LEFT JOIN `groups` g ON m.group_id = g.id
    LEFT JOIN companies c ON g.company_id = c.id
    ORDER BY m.nama
")->fetchAll();

// Attendance for selected date
$attMap = [];
if (!empty($mhsList)) {
    $ids = array_column($mhsList, 'id');
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("SELECT mahasiswa_id, status, TIME_FORMAT(checkin_time, '%H:%i') as jam_masuk FROM attendances WHERE mahasiswa_id IN ($ph) AND date = ?");
    $stmt->execute(array_merge($ids, [$tanggal]));
    foreach ($stmt->fetchAll() as $a) $attMap[$a['mahasiswa_id']] = $a;
}

$presensiData = []; $statH = 0; $statT = 0; $statTH = 0;
foreach ($mhsList as $m) {
    $att = $attMap[$m['id']] ?? null;
    $status = $att ? $att['status'] : 'Belum Absen';
    $checkin = $att['jam_masuk'] ?? '-';
    match($status) { 'Hadir' => $statH++, 'Terlambat' => $statT++, 'Alpha', 'Tidak Hadir' => $statTH++, default => null };
    $presensiData[] = ['nama' => $m['nama'], 'nim' => $m['no_ktm'] ?? '-', 'perusahaan' => $m['nama_perusahaan'] ?? '-', 'checkin' => $checkin, 'checkout' => '-', 'status' => $status];
}

// Jurnal data
$jurnalData = $conn->prepare("
    SELECT m.nama, m.no_ktm as nim, l.kegiatan as judul, DATE_FORMAT(l.tanggal, '%d %b %Y') as tanggal_fmt, l.status
    FROM logbooks l JOIN mahasiswa m ON l.mahasiswa_id = m.id
    ORDER BY l.tanggal DESC LIMIT 20
");
$jurnalData->execute();
$jurnals = $jurnalData->fetchAll();

$totalMhs = count($mhsList);
$jurnalApproved = (int)$conn->query("SELECT COUNT(*) FROM logbooks WHERE status = 'approved'")->fetchColumn();
$jurnalTotal = (int)$conn->query("SELECT COUNT(*) FROM logbooks")->fetchColumn();

$statusColors = ['Hadir' => 'bg-green-100 text-green-700', 'Terlambat' => 'bg-amber-100 text-amber-600', 'Alpha' => 'bg-red-100 text-red-600', 'Tidak Hadir' => 'bg-red-100 text-red-600', 'Izin' => 'bg-orange-100 text-orange-600', 'Sakit' => 'bg-orange-100 text-orange-600', 'Belum Absen' => 'bg-gray-100 text-gray-500'];
$jurnalColors = ['approved' => 'bg-green-100 text-green-700', 'pending' => 'bg-amber-100 text-amber-600', 'rejected' => 'bg-red-100 text-red-600'];
$jurnalLabels = ['approved' => 'Disetujui', 'pending' => 'Menunggu', 'rejected' => 'Ditolak'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Mahasiswa - Magang TIF</title>
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
            <div class="max-w-[1200px] mx-auto space-y-6">
                <div class="flex items-start justify-between">
                    <div><h2 class="text-2xl font-bold text-gray-900">Monitoring Mahasiswa</h2><p class="text-gray-500 text-sm mt-0.5">Pantau presensi dan jurnal harian seluruh mahasiswa magang</p></div>
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 bg-white rounded-xl text-[13px] font-medium text-gray-700">
                            <a href="?tanggal=<?= (clone $d)->modify('-1 day')->format('Y-m-d') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-chevron-left text-[11px]"></i></a>
                            <i class="fas fa-calendar text-gray-400 text-[12px]"></i> <?= $displayDate ?>
                            <a href="?tanggal=<?= (clone $d)->modify('+1 day')->format('Y-m-d') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-chevron-right text-[11px]"></i></a>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"><p class="text-[12px] text-gray-500 mb-1">Total Mahasiswa</p><p class="text-2xl font-bold text-gray-900"><?= $totalMhs ?></p></div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"><p class="text-[12px] text-gray-500 mb-1">Hadir</p><p class="text-2xl font-bold text-green-500"><?= $statH ?></p></div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"><p class="text-[12px] text-gray-500 mb-1">Terlambat</p><p class="text-2xl font-bold text-amber-500"><?= $statT ?></p></div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"><p class="text-[12px] text-gray-500 mb-1">Tidak Hadir</p><p class="text-2xl font-bold text-red-500"><?= $statTH ?></p></div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"><p class="text-[12px] text-gray-500 mb-1">Jurnal Disetujui</p><p class="text-2xl font-bold text-blue-600"><?= $jurnalApproved ?>/<?= $jurnalTotal ?></p></div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-gray-100">
                        <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
                            <button id="tabPresensi" onclick="switchTab('presensi')" class="tab-btn px-4 py-2 rounded-lg text-[13px] font-semibold bg-white text-gray-800 shadow-sm">Data Presensi</button>
                            <button id="tabJurnal" onclick="switchTab('jurnal')" class="tab-btn px-4 py-2 rounded-lg text-[13px] font-medium text-gray-500">Jurnal Harian</button>
                        </div>
                        <div class="relative"><i class="fas fa-search text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-[12px]"></i><input id="searchMhs" type="text" placeholder="Cari mahasiswa..." oninput="filterRows()" class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 w-56"></div>
                    </div>
                    <div id="presensiTable" class="overflow-x-auto">
                        <table class="w-full"><thead><tr class="border-b border-gray-100">
                            <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Mahasiswa</th>
                            <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Perusahaan</th>
                            <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Check-In</th>
                            <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($presensiData as $p): ?>
                            <tr class="hover:bg-gray-50 transition-colors mhs-row" data-search="<?= strtolower($p['nama'].' '.$p['nim']) ?>">
                                <td class="px-6 py-4"><p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($p['nama']) ?></p><p class="text-[12px] text-gray-400"><?= $p['nim'] ?></p></td>
                                <td class="px-6 py-4 text-[13px] text-gray-600"><?= htmlspecialchars($p['perusahaan']) ?></td>
                                <td class="px-6 py-4 text-[13px] text-gray-700 font-medium"><?= $p['checkin'] ?></td>
                                <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $statusColors[$p['status']] ?? 'bg-gray-100 text-gray-500' ?>"><?= $p['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody></table>
                    </div>
                    <div id="jurnalTable" class="overflow-x-auto hidden">
                        <table class="w-full"><thead><tr class="border-b border-gray-100">
                            <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Mahasiswa</th>
                            <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Kegiatan</th>
                            <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Tanggal</th>
                            <th class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($jurnals)): ?>
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada jurnal.</td></tr>
                            <?php else: foreach ($jurnals as $j): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4"><p class="font-semibold text-gray-800 text-[14px]"><?= htmlspecialchars($j['nama']) ?></p><p class="text-[12px] text-gray-400"><?= $j['nim'] ?? '-' ?></p></td>
                                <td class="px-6 py-4 text-[13px] text-gray-700"><?= htmlspecialchars($j['judul']) ?></td>
                                <td class="px-6 py-4 text-[13px] text-gray-500"><?= $j['tanggal_fmt'] ?></td>
                                <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-[12px] font-semibold <?= $jurnalColors[$j['status']] ?? 'bg-gray-100 text-gray-500' ?>"><?= $jurnalLabels[$j['status']] ?? $j['status'] ?></span></td>
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
        function switchTab(tab) {
            const isP = tab === 'presensi';
            document.getElementById('presensiTable').classList.toggle('hidden', !isP);
            document.getElementById('jurnalTable').classList.toggle('hidden', isP);
            document.getElementById('tabPresensi').className = 'tab-btn px-4 py-2 rounded-lg text-[13px] ' + (isP ? 'font-semibold bg-white text-gray-800 shadow-sm' : 'font-medium text-gray-500');
            document.getElementById('tabJurnal').className = 'tab-btn px-4 py-2 rounded-lg text-[13px] ' + (!isP ? 'font-semibold bg-white text-gray-800 shadow-sm' : 'font-medium text-gray-500');
        }
        function filterRows() { const q = document.getElementById('searchMhs').value.toLowerCase(); document.querySelectorAll('.mhs-row').forEach(r => { r.style.display = r.dataset.search.includes(q) ? '' : 'none'; }); }
    </script>
</body>
</html>
