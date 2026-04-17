<?php
    session_start();
    require_once '../config/db_connect.php';

    if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'mahasiswa') {
        header('Location: ../index.php'); exit();
    }

    $role = 'mahasiswa';
    $activePage = 'nilai';
    $id_user = (int) $_SESSION['id_user'];

    // Ambil data profil mahasiswa
    $stmtP = $conn->prepare("
        SELECT u.nama, p.nim, p.prodi, p.semester
        FROM Users u
        LEFT JOIN Profile p ON u.id_user = p.id_user
        WHERE u.id_user = :id LIMIT 1
    ");
    $stmtP->execute([':id' => $id_user]);
    $profil = $stmtP->fetch() ?: [];

    // Ambil nilai akhir + komponen
    $stmtN = $conn->prepare("
        SELECT nilai_akhir, nilai_laporan, nilai_seminar, catatan, komentar
        FROM Final_evaluation
        WHERE id_user = :id ORDER BY id_evaluation DESC LIMIT 1
    ");
    $stmtN->execute([':id' => $id_user]);
    $nilaiData = $stmtN->fetch();

    $nilaiAkhir   = $nilaiData ? (float) $nilaiData['nilai_akhir']   : null;
    $nilaiLaporan = $nilaiData ? $nilaiData['nilai_laporan']          : null;
    $nilaiSeminar = $nilaiData ? $nilaiData['nilai_seminar']          : null;
    $komentar     = $nilaiData ? ($nilaiData['catatan'] ?: $nilaiData['komentar']) : null;

    // Konversi nilai ke grade
    $grade = '-';
    $gradeColor = 'text-gray-400';
    if ($nilaiAkhir !== null) {
        if ($nilaiAkhir >= 90)      { $grade = 'A';  $gradeColor = 'text-green-600'; }
        elseif ($nilaiAkhir >= 85) { $grade = 'A-'; $gradeColor = 'text-green-500'; }
        elseif ($nilaiAkhir >= 80) { $grade = 'B+'; $gradeColor = 'text-blue-600'; }
        elseif ($nilaiAkhir >= 75) { $grade = 'B';  $gradeColor = 'text-blue-500'; }
        elseif ($nilaiAkhir >= 70) { $grade = 'B-'; $gradeColor = 'text-blue-400'; }
        elseif ($nilaiAkhir >= 65) { $grade = 'C+'; $gradeColor = 'text-yellow-600'; }
        elseif ($nilaiAkhir >= 60) { $grade = 'C';  $gradeColor = 'text-yellow-500'; }
        else                       { $grade = 'D';  $gradeColor = 'text-red-500'; }
    }

    // Konversi grade ke poin
    $gradePoints = ['A'=>4.0,'A-'=>3.7,'B+'=>3.3,'B'=>3.0,'B-'=>2.7,'C+'=>2.3,'C'=>2.0,'D'=>1.0,'-'=>0];
    $ips = $gradePoints[$grade] ?? 0;

    $get = fn($key, $default='-') => !empty($profil[$key]) ? $profil[$key] : $default;
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

        /* IPS Circle Progress */
        .ips-ring {
            width: 90px;
            height: 90px;
            position: relative;
        }
        .ips-ring svg {
            transform: rotate(-90deg);
        }
        .ips-ring .track {
            fill: none;
            stroke: #e0e7ff;
            stroke-width: 6;
        }
        .ips-ring .progress {
            fill: none;
            stroke: #3b82f6;
            stroke-width: 6;
            stroke-linecap: round;
            stroke-dasharray: 220;
            stroke-dashoffset: 33; /* ~85% filled */
            transition: stroke-dashoffset 1s ease;
        }
        .ips-label {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Header -->
        <?php include '../includes/header.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1100px] mx-auto space-y-6">

                <!-- Page Banner -->
                <div class="bg-[#3b66f5] rounded-2xl p-8 shadow-sm relative overflow-hidden">
                    <div class="text-white z-10 relative">
                        <h2 class="text-[26px] font-bold mb-1.5 tracking-tight">Transkrip Nilai Mahasiswa</h2>
                        <p class="text-blue-200 text-[14px] mb-5"><?= htmlspecialchars($get('prodi', 'Teknik Informatika')) ?></p>
                        <div class="flex flex-wrap items-center gap-6 text-[13px] text-blue-100">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-user text-blue-300"></i> <?= htmlspecialchars($get('nama')) ?>
                            </span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-id-card text-blue-300"></i> NIM: <?= htmlspecialchars($get('nim')) ?>
                            </span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-book-open text-blue-300"></i> Semester <?= htmlspecialchars($get('semester')) ?>
                            </span>
                        </div>
                    </div>
                    <div class="absolute right-0 top-0 w-80 h-full bg-gradient-to-l from-[#254bdb] to-transparent z-0 opacity-80"></div>
                </div>

                <!-- Nilai Akhir Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-[20px] font-bold text-gray-800 mb-1">Nilai Akhir</h3>
                        <p class="text-[14px] text-gray-400">Indeks Prestasi Semester (IPS)</p>
                        <?php if ($nilaiData && !empty($nilaiData['komentar'])): ?>
                            <p class="text-[13px] text-gray-500 mt-2 max-w-xs italic">"<?= htmlspecialchars($nilaiData['komentar']) ?>"</p>
                        <?php elseif ($nilaiAkhir === null): ?>
                            <p class="text-[13px] text-amber-500 mt-2">Nilai belum diinput oleh dosen.</p>
                        <?php endif; ?>
                    </div>
                    <!-- IPS Ring -->
                    <div class="ips-ring shrink-0">
                        <svg viewBox="0 0 80 80" width="90" height="90">
                            <circle class="track" cx="40" cy="40" r="35"/>
                            <circle class="progress" cx="40" cy="40" r="35"
                                style="stroke-dashoffset: <?= $nilaiAkhir ? round(220 - (220 * $nilaiAkhir / 100)) : 220 ?>"/>
                        </svg>
                        <div class="ips-label">
                            <span class="text-[20px] font-bold text-[#3b82f6] leading-none"><?= $grade ?></span>
                            <span class="text-[11px] text-gray-500 font-medium mt-0.5"><?= number_format($ips, 2) ?></span>
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

                                <!-- Row 1: Kehadiran -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 md:px-8 py-5 text-center text-gray-500">1</td>
                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-gray-800">Kehadiran</div>
                                        <div class="text-[12px] text-gray-400 mt-0.5">Presensi dan partisipasi aktif</div>
                                    </td>
                                    <td class="px-6 py-5 text-center text-gray-600">15%</td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 bg-green-100 text-green-700 rounded-lg text-[13px] font-bold">95</span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 bg-green-100 text-green-700 rounded-lg text-[13px] font-bold">A</span>
                                    </td>
                                </tr>

                                <!-- Row 2: Teknis -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 md:px-8 py-5 text-center text-gray-500">2</td>
                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-gray-800">Teknis</div>
                                        <div class="text-[12px] text-gray-400 mt-0.5">Kemampuan teknis dan coding</div>
                                    </td>
                                    <td class="px-6 py-5 text-center text-gray-600">40%</td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 bg-blue-100 text-blue-700 rounded-lg text-[13px] font-bold">88</span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 bg-blue-100 text-blue-700 rounded-lg text-[13px] font-bold">A-</span>
                                    </td>
                                </tr>

                                <!-- Row 3: Soft Skill -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 md:px-8 py-5 text-center text-gray-500">3</td>
                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-gray-800">Soft Skill</div>
                                        <div class="text-[12px] text-gray-400 mt-0.5">Komunikasi dan teamwork</div>
                                    </td>
                                    <td class="px-6 py-5 text-center text-gray-600">20%</td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 bg-green-100 text-green-700 rounded-lg text-[13px] font-bold">92</span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 bg-green-100 text-green-700 rounded-lg text-[13px] font-bold">A</span>
                                    </td>
                                </tr>

                                <!-- Row 4: UTS -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 md:px-8 py-5 text-center text-gray-500">4</td>
                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-gray-800">Ujian Tengah Semester</div>
                                        <div class="text-[12px] text-gray-400 mt-0.5">Evaluasi materi semester</div>
                                    </td>
                                    <td class="px-6 py-5 text-center text-gray-600">25%</td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 bg-yellow-100 text-yellow-700 rounded-lg text-[13px] font-bold">85</span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-7 bg-yellow-100 text-yellow-700 rounded-lg text-[13px] font-bold">B+</span>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- Download PDF -->
                    <div class="px-6 md:px-8 py-5 border-t border-gray-100 flex justify-end bg-gray-50/50 rounded-b-2xl">
                        <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-lg font-medium text-[14px] transition-colors shadow-sm flex items-center gap-2">
                            <i class="fas fa-download text-[13px]"></i> Download PDF
                        </button>
                    </div>
                </div>

            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

</body>
</html>
