<?php
    session_start();
    $role = 'dosen';
    $activePage = 'penilaian';

    // Grade helper
    function getGrade(int $nilai): string {
        if ($nilai >= 85) return 'A';
        if ($nilai >= 70) return 'B';
        if ($nilai >= 55) return 'C';
        if ($nilai >= 40) return 'D';
        return 'E';
    }
    function gradeColor(string $g): string {
        return match($g) {
            'A' => 'text-green-600',
            'B' => 'text-blue-600',
            'C' => 'text-yellow-600',
            default => 'text-red-500',
        };
    }

    // Student data
    $students = [
        [
            'id'       => 1,
            'nama'     => 'Balmond',
            'nim'      => '211134341',
            'instansi' => 'PT Telkom Indonesia',
            'file'     => 'Laporan_Akhir_Balmond.pdf',
            'fileDate' => '15 Januari 2026',
            'fileStatus'=> 'Pending',
            'nilaiLaporan' => 88,
            'nilaiSeminar' => null,
        ],
        [
            'id'       => 2,
            'nama'     => 'Lesley',
            'nim'      => '20133432',
            'instansi' => 'CV Digital Kreatif',
            'file'     => 'Laporan_Akhir_Lesley.pdf',
            'fileDate' => '18 Januari 2026',
            'fileStatus'=> 'Pending',
            'nilaiLaporan' => null,
            'nilaiSeminar' => null,
        ],
        [
            'id'       => 3,
            'nama'     => 'Harley',
            'nim'      => '22123232',
            'instansi' => 'PT Bank BRI',
            'file'     => 'Laporan_Akhir_Harley.pdf',
            'fileDate' => '20 Januari 2026',
            'fileStatus'=> 'Pending',
            'nilaiLaporan' => null,
            'nilaiSeminar' => null,
        ],
        [
            'id'       => 4,
            'nama'     => 'Budi Santoso',
            'nim'      => '21140004',
            'instansi' => 'PT Astra International',
            'file'     => 'Laporan_Akhir_Budi.pdf',
            'fileDate' => '10 Januari 2026',
            'fileStatus'=> 'Disetujui',
            'nilaiLaporan' => 92,
            'nilaiSeminar' => 88,
        ],
        [
            'id'       => 5,
            'nama'     => 'Joko',
            'nim'      => '22130003',
            'instansi' => 'PT Tokopedia',
            'file'     => 'Laporan_Akhir_Joko.pdf',
            'fileDate' => '22 Januari 2026',
            'fileStatus'=> 'Pending',
            'nilaiLaporan' => null,
            'nilaiSeminar' => null,
        ],
        [
            'id'       => 6,
            'nama'     => 'Meks Panda',
            'nim'      => '22130043',
            'instansi' => 'PT Gojek Indonesia',
            'file'     => 'Laporan_Akhir_Meks.pdf',
            'fileDate' => '12 Januari 2026',
            'fileStatus'=> 'Pending',
            'nilaiLaporan' => 85,
            'nilaiSeminar' => null,
        ],
        [
            'id'       => 7,
            'nama'     => 'Nana',
            'nim'      => '2213563',
            'instansi' => 'PT Bukalapak',
            'file'     => 'Laporan_Akhir_Nana.pdf',
            'fileDate' => '25 Januari 2026',
            'fileStatus'=> 'Pending',
            'nilaiLaporan' => null,
            'nilaiSeminar' => null,
        ],
    ];

    $sudahLengkap    = count(array_filter($students, fn($s) => $s['nilaiLaporan'] !== null && $s['nilaiSeminar'] !== null));
    $laporanKosong   = count(array_filter($students, fn($s) => $s['nilaiLaporan'] === null));
    $seminarKosong   = count(array_filter($students, fn($s) => $s['nilaiSeminar'] === null));
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

        /* Accordion panel */
        .accordion-panel {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease, opacity 0.25s ease;
            opacity: 0;
        }
        .accordion-panel.open {
            max-height: 600px;
            opacity: 1;
        }

        /* Chevron rotation */
        .chevron-icon {
            transition: transform 0.3s ease;
        }
        .accordion-item.open .chevron-icon {
            transform: rotate(180deg);
        }

        /* Row hover */
        .student-header {
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .student-header:hover {
            background-color: #f9fafb;
        }
        .accordion-item.open .student-header {
            background-color: #f9fafb;
        }

        /* Input focus */
        .grade-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Top Page Header Bar -->
        <div class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 md:px-8 shrink-0">
            <h1 class="text-[18px] font-bold text-gray-900">Penilaian Dosen</h1>
            <div class="flex items-center gap-4">
                <button class="relative p-2 rounded-full text-gray-500 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell text-[17px]"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <?php include '../includes/header.php'; ?>
            </div>
        </div>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[900px] mx-auto space-y-5">

                <!-- Section Header Card -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-[16px] font-bold text-gray-800">Penilaian Magang</h2>
                        <p class="text-[13px] text-gray-400 mt-0.5">Input nilai Laporan Akhir dan Seminar/Presentasi</p>
                    </div>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[13px]"></i>
                        <input type="text" id="searchPenilaian" placeholder="Cari mahasiswa..."
                               class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 w-52 transition-all">
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-400 mb-2">Sudah Dinilai Lengkap</p>
                        <p class="text-3xl font-bold text-gray-900"><?= $sudahLengkap ?></p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-400 mb-2">Nilai Laporan Kosong</p>
                        <p class="text-3xl font-bold text-orange-500"><?= $laporanKosong ?></p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <p class="text-[12px] text-gray-400 mb-2">Nilai Seminar Kosong</p>
                        <p class="text-3xl font-bold text-red-500"><?= $seminarKosong ?></p>
                    </div>
                </div>

                <!-- Accordion Student List -->
                <div class="space-y-3" id="studentList">

                    <?php foreach ($students as $s):
                        $hasLaporan  = $s['nilaiLaporan'] !== null;
                        $hasSeminar  = $s['nilaiSeminar'] !== null;
                        $isLengkap   = $hasLaporan && $hasSeminar;

                        $gradeL = $hasLaporan ? getGrade($s['nilaiLaporan']) : null;
                        $gradeS = $hasSeminar ? getGrade($s['nilaiSeminar']) : null;

                        $laporanDisplay = $hasLaporan
                            ? '<span class="font-bold ' . gradeColor($gradeL) . '">' . $s['nilaiLaporan'] . '<span class="text-[11px] ml-0.5">(' . $gradeL . ')</span></span>'
                            : '<span class="text-gray-400">-(−)</span>';
                        $seminarDisplay = $hasSeminar
                            ? '<span class="font-bold ' . gradeColor($gradeS) . '">' . $s['nilaiSeminar'] . '<span class="text-[11px] ml-0.5">(' . $gradeS . ')</span></span>'
                            : '<span class="text-gray-400">-(−)</span>';

                        $fileStatusClass = match($s['fileStatus']) {
                            'Disetujui' => 'bg-green-100 text-green-700',
                            default     => 'bg-orange-100 text-orange-600',
                        };
                    ?>
                    <div class="accordion-item bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden student-searchable"
                         data-name="<?= strtolower($s['nama']) ?> <?= $s['nim'] ?> <?= strtolower($s['instansi']) ?>">

                        <!-- Clickable Header Row -->
                        <div class="student-header px-6 py-4 flex items-center gap-4 select-none"
                             onclick="toggleAccordion(<?= $s['id'] ?>)">

                            <!-- Avatar -->
                            <div class="w-11 h-11 rounded-full bg-gray-800 flex items-center justify-center text-white font-bold text-[13px] shrink-0">
                                <?= strtoupper(substr($s['nama'], 0, 2)) ?>
                            </div>

                            <!-- Name + Info -->
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-[15px] truncate"><?= htmlspecialchars($s['nama']) ?></p>
                                <p class="text-[12px] text-gray-400 truncate"><?= $s['nim'] ?> · <?= htmlspecialchars($s['instansi']) ?></p>
                            </div>

                            <!-- Nilai Labels -->
                            <div class="hidden sm:flex items-center gap-6 shrink-0 text-[13px]">
                                <div class="text-right">
                                    <p class="text-[11px] text-gray-400 mb-0.5">Laporan</p>
                                    <?= $laporanDisplay ?>
                                </div>
                                <div class="text-right">
                                    <p class="text-[11px] text-gray-400 mb-0.5">Seminar</p>
                                    <?= $seminarDisplay ?>
                                </div>
                            </div>

                            <!-- Status Badge + Chevron -->
                            <div class="flex items-center gap-2 shrink-0">
                                <?php if ($isLengkap): ?>
                                <span class="flex items-center gap-1.5 bg-green-100 text-green-700 text-[12px] font-semibold px-3 py-1.5 rounded-full">
                                    <i class="fas fa-check-circle text-[11px]"></i> Lengkap
                                </span>
                                <?php else: ?>
                                <span class="bg-orange-100 text-orange-600 text-[12px] font-semibold px-3 py-1.5 rounded-full">
                                    Belum Lengkap
                                </span>
                                <?php endif; ?>
                                <i class="fas fa-chevron-down text-gray-400 text-[12px] chevron-icon"></i>
                            </div>
                        </div>

                        <!-- Accordion Panel (Dropdown Form) -->
                        <div class="accordion-panel" id="panel-<?= $s['id'] ?>">
                            <div class="px-6 pb-6 pt-1">
                                <!-- Divider -->
                                <div class="border-t border-gray-100 mb-5"></div>

                                <!-- Laporan Akhir Section -->
                                <div class="mb-5">
                                    <p class="text-[14px] font-semibold text-gray-800 mb-3">Laporan Akhir</p>
                                    <div class="bg-gray-50 rounded-xl px-4 py-3 flex items-center justify-between border border-gray-100">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center shrink-0">
                                                <i class="fas fa-file-pdf text-red-500 text-[14px]"></i>
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-semibold text-gray-700"><?= htmlspecialchars($s['file']) ?></p>
                                                <p class="text-[11px] text-gray-400"><?= $s['fileDate'] ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full <?= $fileStatusClass ?>">
                                                <?= $s['fileStatus'] ?>
                                            </span>
                                            <button class="flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-[12px] font-semibold border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                                                <i class="fas fa-eye text-[11px]"></i> Lihat
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Score Inputs -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <!-- Nilai Laporan -->
                                    <div>
                                        <label class="block text-[13px] font-semibold text-gray-700 mb-2">Nilai Laporan Akhir</label>
                                        <input type="number"
                                               id="laporan-<?= $s['id'] ?>"
                                               min="0" max="100"
                                               value="<?= $s['nilaiLaporan'] ?? '' ?>"
                                               placeholder="0 – 100"
                                               oninput="updateGrade(<?= $s['id'] ?>, 'laporan')"
                                               class="grade-input w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] text-gray-800 transition-all">
                                        <p class="text-[12px] text-gray-400 mt-1.5" id="grade-laporan-<?= $s['id'] ?>">
                                            <?php if ($hasLaporan): ?>
                                                Grade: <span class="font-bold <?= gradeColor($gradeL) ?>"><?= $gradeL ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>

                                    <!-- Nilai Seminar -->
                                    <div>
                                        <label class="block text-[13px] font-semibold text-gray-700 mb-2">Nilai Seminar / Presentasi</label>
                                        <input type="number"
                                               id="seminar-<?= $s['id'] ?>"
                                               min="0" max="100"
                                               value="<?= $s['nilaiSeminar'] ?? '' ?>"
                                               placeholder="0 – 100"
                                               oninput="updateGrade(<?= $s['id'] ?>, 'seminar')"
                                               class="grade-input w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] text-gray-800 transition-all">
                                        <p class="text-[12px] text-gray-400 mt-1.5" id="grade-seminar-<?= $s['id'] ?>">
                                            <?php if ($hasSeminar): ?>
                                                Grade: <span class="font-bold <?= gradeColor($gradeS) ?>"><?= $gradeS ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Catatan -->
                                <div class="mb-5">
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-2">Catatan Bimbingan / Umpan Balik</label>
                                    <textarea rows="3"
                                              placeholder="Berikan catatan atau umpan balik bimbingan..."
                                              class="grade-input w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] text-gray-700 resize-none transition-all"></textarea>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-end gap-3">
                                    <button onclick="toggleAccordion(<?= $s['id'] ?>)"
                                            class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-[13px] font-semibold hover:bg-gray-50 transition-colors">
                                        Batal
                                    </button>
                                    <button onclick="saveNilai(<?= $s['id'] ?>)"
                                            class="flex items-center gap-2 px-5 py-2.5 bg-[#3b66f5] hover:bg-[#2d53d4] text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm shadow-blue-200">
                                        <i class="fas fa-save text-[12px]"></i> Simpan Nilai
                                    </button>
                                </div>
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
        // ─── Grade Calculation ─────────────────────────────────────
        function calcGrade(val) {
            if (val >= 85) return { g: 'A', cls: 'text-green-600' };
            if (val >= 70) return { g: 'B', cls: 'text-blue-600' };
            if (val >= 55) return { g: 'C', cls: 'text-yellow-600' };
            if (val >= 40) return { g: 'D', cls: 'text-orange-500' };
            return { g: 'E', cls: 'text-red-500' };
        }

        function updateGrade(id, type) {
            const input = document.getElementById(`${type}-${id}`);
            const label = document.getElementById(`grade-${type}-${id}`);
            const val   = parseInt(input.value);

            if (!isNaN(val) && val >= 0 && val <= 100) {
                const { g, cls } = calcGrade(val);
                label.innerHTML = `Grade: <span class="font-bold ${cls}">${g}</span>`;
            } else {
                label.innerHTML = '';
            }
        }

        // ─── Accordion Toggle ──────────────────────────────────────
        let currentOpen = null;

        function toggleAccordion(id) {
            const item  = document.querySelector(`[onclick="toggleAccordion(${id})"]`).closest('.accordion-item');
            const panel = document.getElementById(`panel-${id}`);

            // Close currently open (if different)
            if (currentOpen !== null && currentOpen !== id) {
                const prevItem  = document.querySelector(`[onclick="toggleAccordion(${currentOpen})"]`).closest('.accordion-item');
                const prevPanel = document.getElementById(`panel-${currentOpen}`);
                prevItem.classList.remove('open');
                prevPanel.classList.remove('open');
            }

            // Toggle target
            const isOpen = item.classList.contains('open');
            item.classList.toggle('open', !isOpen);
            panel.classList.toggle('open', !isOpen);

            currentOpen = isOpen ? null : id;
        }

        // ─── Save Nilai (demo) ──────────────────────────────────────
        function saveNilai(id) {
            const laporan = document.getElementById(`laporan-${id}`).value;
            const seminar = document.getElementById(`seminar-${id}`).value;

            if (!laporan && !seminar) {
                alert('Silakan isi minimal satu nilai.');
                return;
            }

            // Visuall feedback
            const btn = event.currentTarget;
            btn.innerHTML = '<i class="fas fa-check text-[12px]"></i> Tersimpan!';
            btn.classList.replace('bg-[#3b66f5]', 'bg-green-500');
            btn.classList.replace('hover:bg-[#2d53d4]', 'hover:bg-green-600');
            btn.classList.replace('shadow-blue-200', 'shadow-green-200');

            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-save text-[12px]"></i> Simpan Nilai';
                btn.classList.replace('bg-green-500', 'bg-[#3b66f5]');
                btn.classList.replace('hover:bg-green-600', 'hover:bg-[#2d53d4]');
                btn.classList.replace('shadow-green-200', 'shadow-blue-200');
                toggleAccordion(id);
            }, 1200);
        }

        // ─── Live Search ────────────────────────────────────────────
        document.getElementById('searchPenilaian').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.student-searchable').forEach(el => {
                el.style.display = el.dataset.name.includes(q) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
