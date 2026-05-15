<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("pembimbing_lapang");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'pembimbing';
$activePage = 'absensi_mahasiswa';

$userId = $_SESSION['id_user'];
$plRow = $conn->prepare("SELECT pl.id, pl.company_id, c.nama_perusahaan FROM pembimbing_lapang pl LEFT JOIN companies c ON pl.company_id = c.id WHERE pl.user_id = ?");
$plRow->execute([$userId]);
$plRow = $plRow->fetch();
$plId      = $plRow['id'] ?? null;
$companyId = $plRow['company_id'] ?? null;
$companyName = $plRow['nama_perusahaan'] ?? '-';

// Filter tanggal
$filterTgl   = $_GET['tgl']   ?? date('Y-m');      // format Y-m
$filterMhsId = (int)($_GET['mhs_id'] ?? 0);
[$yr, $mn]   = explode('-', $filterTgl . '-01');

// Mahasiswa di kelompok perusahaan ini
$mhsList = [];
if ($companyId) {
    $stmt = $conn->prepare("
        SELECT m.id, m.nama FROM mahasiswa m
        JOIN `groups` g ON m.group_id = g.id
        WHERE g.company_id = ?
        ORDER BY m.nama
    ");
    $stmt->execute([$companyId]);
    $mhsList = $stmt->fetchAll();
}

// Rekap absensi
$rekapData = [];
if (!empty($mhsList)) {
    $mhsIds = array_column($mhsList, 'id');
    $inQ    = implode(',', array_fill(0, count($mhsIds), '?'));

    $params = $mhsIds;
    $params[] = "$yr-$mn%";
    if ($filterMhsId) {
        $stmt = $conn->prepare("SELECT a.*, m.nama as nama_mhs FROM attendances a JOIN mahasiswa m ON a.mahasiswa_id = m.id WHERE a.mahasiswa_id = ? AND a.date LIKE ? ORDER BY a.date DESC");
        $stmt->execute([$filterMhsId, "$yr-$mn%"]);
    } else {
        $stmt = $conn->prepare("SELECT a.*, m.nama as nama_mhs FROM attendances a JOIN mahasiswa m ON a.mahasiswa_id = m.id WHERE a.mahasiswa_id IN ($inQ) AND a.date LIKE ? ORDER BY a.date DESC, m.nama");
        $stmt->execute($params);
    }
    $rekapData = $stmt->fetchAll();
}

// Summary per mahasiswa bulan ini
$summary = [];
foreach ($mhsList as $m) {
    $stmt = $conn->prepare("SELECT status, COUNT(*) as total FROM attendances WHERE mahasiswa_id = ? AND date LIKE ? GROUP BY status");
    $stmt->execute([$m['id'], "$yr-$mn%"]);
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $summary[$m['id']] = [
        'nama'   => $m['nama'],
        'hadir'  => (int)($rows['Hadir']     ?? 0),
        'telat'  => (int)($rows['Terlambat'] ?? 0),
        'izin'   => (int)($rows['Izin']      ?? 0),
        'sakit'  => (int)($rows['Sakit']     ?? 0),
        'alpha'  => (int)($rows['Alpha']     ?? 0),
    ];
}

$userName = $_SESSION['nama'] ?? 'Pembimbing';
$statusColor = [
    'Hadir'     => 'green',
    'Terlambat' => 'yellow',
    'Izin'      => 'blue',
    'Sakit'     => 'orange',
    'Alpha'     => 'red',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Mahasiswa - Magang TIF</title>
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

            <div>
                <h2 class="text-2xl font-bold text-gray-900">Absensi Mahasiswa</h2>
                <p class="text-gray-500 text-sm mt-0.5">Rekap kehadiran mahasiswa di <?= htmlspecialchars($companyName) ?></p>
            </div>

            <!-- Filter -->
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Bulan</label>
                    <input type="month" name="tgl" value="<?= htmlspecialchars($filterTgl) ?>"
                           class="border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Mahasiswa</label>
                    <select name="mhs_id" class="border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Semua Mahasiswa</option>
                        <?php foreach ($mhsList as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $filterMhsId == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </form>


            <!-- Summary Per Mahasiswa -->
            <?php if (!empty($summary) && !$filterMhsId): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Rekap Per Mahasiswa — <?= date('F Y', strtotime("$filterTgl-01")) ?></h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">Nama</th>
                                <th class="px-6 py-3 text-center">Hadir</th>
                                <th class="px-6 py-3 text-center">Terlambat</th>
                                <th class="px-6 py-3 text-center">Sakit</th>
                                <th class="px-6 py-3 text-center">Izin</th>
                                <th class="px-6 py-3 text-center">Alpha</th>
                                <th class="px-6 py-3 text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($summary as $s): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-800"><?= htmlspecialchars($s['nama']) ?></td>
                                <td class="px-6 py-3 text-center"><span class="text-green-600 font-bold"><?= $s['hadir'] ?></span></td>
                                <td class="px-6 py-3 text-center"><span class="text-yellow-500 font-bold"><?= $s['telat'] ?></span></td>
                                <td class="px-6 py-3 text-center"><span class="text-orange-500 font-bold"><?= $s['sakit'] ?></span></td>
                                <td class="px-6 py-3 text-center"><span class="text-blue-600 font-bold"><?= $s['izin'] ?></span></td>
                                <td class="px-6 py-3 text-center"><span class="text-red-500 font-bold"><?= $s['alpha'] ?></span></td>
                                <td class="px-6 py-3 text-center text-gray-600"><?= $s['hadir']+$s['telat']+$s['sakit']+$s['izin']+$s['alpha'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Detail Absensi -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Detail Absensi <span class="text-sm font-normal text-gray-400">(<?= count($rekapData) ?> data)</span></h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">Tanggal</th>
                                <th class="px-6 py-3 text-left">Mahasiswa</th>
                                <th class="px-6 py-3 text-left">Check In</th>
                                <th class="px-6 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($rekapData)): ?>
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">Tidak ada data absensi untuk filter ini</td></tr>
                            <?php else: ?>
                            <?php foreach ($rekapData as $a):
                                $sc = $statusColor[$a['status']] ?? 'gray';
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-gray-700"><?= date('d M Y', strtotime($a['date'])) ?></td>
                                <td class="px-6 py-3 font-medium text-gray-800"><?= htmlspecialchars($a['nama_mhs']) ?></td>
                                <td class="px-6 py-3 text-gray-600"><?= $a['checkin_time'] ? date('H:i', strtotime($a['checkin_time'])) : '-' ?></td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        <?= $sc==='green' ?'bg-green-100 text-green-700' :'' ?>
                                        <?= $sc==='yellow'?'bg-yellow-100 text-yellow-700':'' ?>
                                        <?= $sc==='orange'?'bg-orange-100 text-orange-700':'' ?>
                                        <?= $sc==='blue'  ?'bg-blue-100 text-blue-700'   :'' ?>
                                        <?= $sc==='red'   ?'bg-red-100 text-red-700'     :'' ?>
                                        <?= $sc==='gray'  ?'bg-gray-100 text-gray-600'   :'' ?>">
                                        <?= ucfirst($a['status']) ?>
                                    </span>
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
</body>
</html>
