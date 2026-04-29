<?php
require_once __DIR__ . '/../config/role_guard.php';
checkRole('mahasiswa');
require_once '../config/db_connect.php';
$role = 'mahasiswa';
$activePage = 'dashboard';

// ====== DYNAMIC DATA QUERIES ======
$userId = $_SESSION['id_user'];
$userName = $_SESSION['nama'] ?? 'Mahasiswa';

// Get mahasiswa record
$stmt = $conn->prepare("SELECT id FROM mahasiswa WHERE user_id = :uid");
$stmt->execute(['uid' => $userId]);
$mhs = $stmt->fetch();
$mhsId = $mhs ? $mhs['id'] : 0;

// Attendance stats
$stmt = $conn->prepare("SELECT status, COUNT(*) as total FROM attendances WHERE mahasiswa_id = :mid GROUP BY status");
$stmt->execute(['mid' => $mhsId]);
$attRows = $stmt->fetchAll();

$attStats = ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alpha' => 0, 'Terlambat' => 0];
$attTotal = 0;
foreach ($attRows as $r) {
    $attStats[$r['status']] = (int)$r['total'];
    $attTotal += (int)$r['total'];
}
// Calculate percentages
$attPct = [];
foreach ($attStats as $key => $val) {
    $attPct[$key] = $attTotal > 0 ? round($val / $attTotal * 100) : 0;
}

// Logbook stats per month (current year)
$currentYear = date('Y');
$stmt = $conn->prepare("
    SELECT MONTH(tanggal) as bulan, COUNT(*) as total
    FROM logbooks
    WHERE mahasiswa_id = :mid AND YEAR(tanggal) = :yr
    GROUP BY MONTH(tanggal)
    ORDER BY bulan
");
$stmt->execute(['mid' => $mhsId, 'yr' => $currentYear]);
$logbookMonthly = $stmt->fetchAll();

// Build 12-month logbook data
$logbookData = array_fill(0, 12, 0);
foreach ($logbookMonthly as $row) {
    $logbookData[(int)$row['bulan'] - 1] = (int)$row['total'];
}
$maxLogbook = max(max($logbookData), 5); // Minimum scale of 5

// Get available years for logbook
$stmt = $conn->prepare("SELECT DISTINCT YEAR(tanggal) as yr FROM logbooks WHERE mahasiswa_id = :mid ORDER BY yr DESC");
$stmt->execute(['mid' => $mhsId]);
$availableYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($availableYears)) $availableYears = [$currentYear];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
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

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[1200px] mx-auto space-y-6">

                <!-- Welcome Banner -->
                <div
                    class="bg-[#3b66f5] rounded-2xl p-8 flex items-center justify-between shadow-sm relative overflow-hidden">

                    <div class="text-white z-10 relative">
                        <h2 class="text-2xl font-bold mb-2 tracking-tight">Welcome back, <?= htmlspecialchars($userName) ?>!</h2>
                        <p class="text-blue-100 text-[15px]">Ready to continue your internship journey?</p>
                    </div>

                    <div class="relative z-10">
                        <img src="../assets/img/dashboard1.png" alt="Ilustrasi Dashboard" class="img-fluid">
                    </div>

                    <div
                        class="absolute right-0 top-0 w-80 h-full bg-gradient-to-l from-[#254bdb] to-transparent z-0 opacity-80 pointer-events-none">
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Bar Chart: Data Statistik Jurnal -->
                    <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-[17px] font-bold text-gray-800">Data Statistik Jurnal</h3>
                            <div class="relative">
                                <select id="yearSelect"
                                    class="appearance-none bg-white border border-gray-200 text-gray-700 py-1.5 pl-4 pr-10 rounded-lg text-[14px] font-medium outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500 cursor-pointer shadow-sm transition-all">
                                    <?php foreach ($availableYears as $yr): ?>
                                        <option value="<?= $yr ?>" <?= $yr == $currentYear ? 'selected' : '' ?>><?= $yr ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                        <div class="relative w-full h-[280px]">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>

                    <!-- Donut Chart: Data Absensi -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col">
                        <h3 class="text-[17px] font-bold text-gray-800 mb-8">Data Absensi</h3>

                        <div class="relative flex-1 flex justify-center items-center min-h-[160px] mb-8">
                            <canvas id="donutChart"></canvas>
                        </div>

                        <div class="space-y-3.5 mt-auto">
                            <!-- Legend Item: Hadir -->
                            <div class="flex items-center justify-between text-[14px]">
                                <span class="flex items-center gap-2.5 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span> Hadir
                                </span>
                                <span class="font-bold text-gray-800"><?= $attPct['Hadir'] ?>%</span>
                            </div>
                            <!-- Legend Item: Sakit -->
                            <div class="flex items-center justify-between text-[14px]">
                                <span class="flex items-center gap-2.5 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#fbbf24]"></span> Sakit
                                </span>
                                <span class="font-bold text-gray-800"><?= $attPct['Sakit'] ?>%</span>
                            </div>
                            <!-- Legend Item: Izin -->
                            <div class="flex items-center justify-between text-[14px]">
                                <span class="flex items-center gap-2.5 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></span> Izin
                                </span>
                                <span class="font-bold text-gray-800"><?= $attPct['Izin'] ?>%</span>
                            </div>
                            <!-- Legend Item: Alpha -->
                            <div class="flex items-center justify-between text-[14px]">
                                <span class="flex items-center gap-2.5 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#ef4444]"></span> Alpha
                                </span>
                                <span class="font-bold text-gray-800"><?= $attPct['Alpha'] ?>%</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- Chart Scripts -->
    <script>
        // Pass PHP data to JavaScript
        const logbookData = <?= json_encode(array_values($logbookData)) ?>;
        const maxLogbook = <?= $maxLogbook + 5 ?>;
        const attData = {
            hadir: <?= $attPct['Hadir'] ?>,
            sakit: <?= $attPct['Sakit'] ?>,
            izin: <?= $attPct['Izin'] ?>,
            alpha: <?= $attPct['Alpha'] ?>
        };

        document.addEventListener('DOMContentLoaded', function () {
            // Bar Chart Initialization
            const barCtx = document.getElementById('barChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Data Jurnal',
                        data: logbookData,
                        backgroundColor: '#3b82f6',
                        hoverBackgroundColor: '#2563eb',
                        borderRadius: 2,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 10,
                            titleFont: { family: "'Inter', sans-serif", size: 13 },
                            bodyFont: { family: "'Inter', sans-serif", size: 13, weight: 'bold' },
                            displayColors: false,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: maxLogbook,
                            ticks: {
                                stepSize: Math.ceil(maxLogbook / 5),
                                color: '#9ca3af',
                                font: { family: "'Inter', sans-serif", size: 11 },
                                padding: 10
                            },
                            grid: {
                                color: '#e5e7eb',
                                drawBorder: false,
                                borderDash: [5, 5]
                            },
                            border: { display: false }
                        },
                        x: {
                            ticks: {
                                color: '#9ca3af',
                                font: { family: "'Inter', sans-serif", size: 11 },
                                padding: 8
                            },
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                            border: { display: false }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                }
            });

            // Donut Chart Initialization
            const donutCtx = document.getElementById('donutChart').getContext('2d');
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Sakit', 'Izin', 'Alpha'],
                    datasets: [{
                        data: [attData.hadir, attData.sakit, attData.izin, attData.alpha],
                        backgroundColor: [
                            '#10b981',
                            '#fbbf24',
                            '#3b82f6',
                            '#ef4444'
                        ],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    layout: {
                        padding: 10
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 10,
                            titleFont: { family: "'Inter', sans-serif", size: 13 },
                            bodyFont: { family: "'Inter', sans-serif", size: 13, weight: 'bold' },
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    return ` ${context.label}: ${context.parsed}%`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>