<?php
    require_once __DIR__ . "/../config/role_guard.php";
    checkRole("mahasiswa");
    require_once __DIR__ . '/../config/db_connect.php';
    $role = 'mahasiswa';
    $activePage = 'nilai';

    $userId = $_SESSION['id_user'];
    $userName = $_SESSION['nama'] ?? 'Mahasiswa';

    // Get mahasiswa
    $stmt = $conn->prepare("SELECT m.id, m.nama, m.no_ktm FROM mahasiswa m WHERE m.user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $mhs = $stmt->fetch();
    $mhsId = $mhs ? $mhs['id'] : 0;

    // Settings
    $settings = $conn->query("SELECT tahun_ajaran FROM settings LIMIT 1")->fetch();
    $tahunAjaran = $settings['tahun_ajaran'] ?? date('Y');

    // Get penilaian result
    $stmt = $conn->prepare("SELECT nilai_akhir, grade FROM penilaian_results WHERE mahasiswa_id = :mid ORDER BY id DESC LIMIT 1");
    $stmt->execute(['mid' => $mhsId]);
    $hasilNilai = $stmt->fetch();
    $nilaiAkhir = $hasilNilai ? number_format((float)$hasilNilai['nilai_akhir'], 2) : '-';
    $grade = $hasilNilai['grade'] ?? '-';

    // SVG ring calculation (circumference = 2*PI*35 ≈ 220)
    $nilaiFloat = $hasilNilai ? (float)$hasilNilai['nilai_akhir'] : 0;
    $pct = min($nilaiFloat / 100, 1);
    $dashOffset = 220 - (220 * $pct);

    // Get detailed scores per kriteria
    $stmt = $conn->prepare("
        SELECT kp.nama as komponen, kp.bobot_persen,
               kr.nama as kriteria, kr.bobot, kr.nilai_max,
               nk.nilai_angka, nk.nilai_huruf, nk.catatan
        FROM kriteria_penilaian kr
        JOIN komponen_penilaian kp ON kr.komponen_id = kp.id
        LEFT JOIN nilai_kriteria nk ON nk.kriteria_id = kr.id AND nk.mahasiswa_id = :mid
        ORDER BY kp.id, kr.id
    ");
    $stmt->execute(['mid' => $mhsId]);
    $nilaiRows = $stmt->fetchAll();

    // Group by komponen
    $komponenList = [];
    foreach ($nilaiRows as $r) {
        $key = $r['komponen'];
        if (!isset($komponenList[$key])) {
            $komponenList[$key] = ['bobot' => $r['bobot_persen'], 'kriteria' => []];
        }
        $komponenList[$key]['kriteria'][] = $r;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Nilai - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .ips-ring { width: 90px; height: 90px; position: relative; }
        .ips-ring svg { transform: rotate(-90deg); }
        .ips-ring .track { fill: none; stroke: #e0e7ff; stroke-width: 6; }
        .ips-ring .progress { fill: none; stroke: #3b82f6; stroke-width: 6; stroke-linecap: round; stroke-dasharray: 220; stroke-dashoffset: <?= $dashOffset ?>; transition: stroke-dashoffset 1s ease; }
        .ips-label { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <?php include '../includes/header.php'; ?>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1100px] mx-auto space-y-6">

                <!-- Page Banner -->
                <div class="bg-[#3b66f5] rounded-2xl p-8 shadow-sm relative overflow-hidden">
                    <div class="text-white z-10 relative">
                        <h2 class="text-[26px] font-bold mb-1.5 tracking-tight">Transkrip Nilai Mahasiswa</h2>
                        <p class="text-blue-200 text-[14px] mb-5"><?= htmlspecialchars($tahunAjaran) ?></p>
                        <div class="flex flex-wrap items-center gap-6 text-[13px] text-blue-100">
                            <span class="flex items-center gap-2"><i class="fas fa-user text-blue-300"></i> <?= htmlspecialchars($mhs['nama'] ?? $userName) ?></span>
                            <span class="flex items-center gap-2"><i class="fas fa-id-card text-blue-300"></i> NIM: <?= htmlspecialchars($mhs['no_ktm'] ?? '-') ?></span>
                        </div>
                    </div>
                    <div class="absolute right-0 top-0 w-80 h-full bg-gradient-to-l from-[#254bdb] to-transparent z-0 opacity-80"></div>
                </div>

                <!-- Nilai Akhir Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-[20px] font-bold text-gray-800 mb-1">Nilai Akhir</h3>
                        <p class="text-[14px] text-gray-400">Hasil Penilaian Magang</p>
                    </div>
                    <div class="ips-ring shrink-0">
                        <svg viewBox="0 0 80 80" width="90" height="90">
                            <circle class="track" cx="40" cy="40" r="35"/>
                            <circle class="progress" cx="40" cy="40" r="35"/>
                        </svg>
                        <div class="ips-label">
                            <span class="text-[20px] font-bold text-[#3b82f6] leading-none"><?= htmlspecialchars($grade) ?></span>
                            <span class="text-[11px] text-gray-500 font-medium mt-0.5"><?= $nilaiAkhir ?></span>
                        </div>
                    </div>
                </div>

                <!-- Detail Penilaian Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 md:px-8 py-5 border-b border-gray-100">
                        <h3 class="text-[17px] font-bold text-gray-800">Detail Penilaian</h3>
                        <p class="text-[13px] text-gray-400 mt-1">Breakdown penilaian berdasarkan kriteria</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-[14px] border-collapse min-w-[680px]">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-500 text-[12px] uppercase tracking-wider">
                                    <th class="px-6 md:px-8 py-4 font-semibold text-center w-20">No</th>
                                    <th class="px-6 py-4 font-semibold text-left">Kriteria Penilaian</th>
                                    <th class="px-6 py-4 font-semibold text-center">Bobot (%)</th>
                                    <th class="px-6 py-4 font-semibold text-center">Nilai</th>
                                    <th class="px-6 py-4 font-semibold text-center">Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($nilaiRows)): ?>
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada data penilaian.</td></tr>
                                <?php else: ?>
                                <?php $no = 0; foreach ($komponenList as $komponen => $data): ?>
                                <?php foreach ($data['kriteria'] as $kr): $no++;
                                    $nilai = $kr['nilai_angka'] !== null ? number_format((float)$kr['nilai_angka'], 0) : '-';
                                    $huruf = $kr['nilai_huruf'] ?? '-';
                                    $colorClass = match(true) {
                                        $nilai === '-' => 'bg-gray-100 text-gray-400',
                                        (int)$nilai >= 85 => 'bg-green-100 text-green-700',
                                        (int)$nilai >= 70 => 'bg-blue-100 text-blue-700',
                                        (int)$nilai >= 55 => 'bg-yellow-100 text-yellow-700',
                                        default => 'bg-red-100 text-red-600',
                                    };
                                ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 md:px-8 py-5 text-center text-gray-500"><?= $no ?></td>
                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($kr['kriteria']) ?></div>
                                        <div class="text-[12px] text-gray-400 mt-0.5"><?= htmlspecialchars($komponen) ?></div>
                                    </td>
                                    <td class="px-6 py-5 text-center text-gray-600"><?= $kr['bobot'] ?? '-' ?>%</td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 <?= $colorClass ?> rounded-lg text-[13px] font-bold"><?= $nilai ?></span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 <?= $colorClass ?> rounded-lg text-[13px] font-bold"><?= htmlspecialchars($huruf) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; endforeach; ?>
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
