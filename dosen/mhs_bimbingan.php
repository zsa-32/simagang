<?php
    session_start();
    require_once '../config/db_connect.php';

    if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'dosen pembimbing') {
        header('Location: ../index.php'); exit();
    }

    $role = 'dosen';
    $activePage = 'bimbingan';
    $id_dosen = (int) $_SESSION['id_user'];

    // Ambil mahasiswa bimbingan beserta data lengkap
    $stmt = $conn->prepare("
        SELECT
            u.id_user, u.nama, u.email,
            p.nim, p.no_hp, p.prodi, p.dosen_pembimbing,
            c.nama_company AS instansi,
            c.alamat AS alamat,
            ip.tanggal_mulai, ip.tanggal_selesai,
            -- Hitung kehadiran
            (SELECT COUNT(*) FROM Attendances a WHERE a.id_user = u.id_user AND a.keterangan = 'Hadir') AS jml_hadir,
            (SELECT COUNT(*) FROM Attendances a WHERE a.id_user = u.id_user AND a.keterangan IN ('Izin','Sakit')) AS jml_izin,
            (SELECT COUNT(*) FROM Attendances a WHERE a.id_user = u.id_user AND a.keterangan = 'Alpha') AS jml_alpha,
            -- Catatan dari jurnal terakhir yang divalidasi
            (SELECT dj.catatan_dosen FROM Daily_journal dj
             WHERE dj.id_user = u.id_user AND dj.catatan_dosen IS NOT NULL
             ORDER BY dj.tanggal DESC LIMIT 1) AS catatan_terakhir,
            -- Status jurnal hari ini
            (SELECT dj.status FROM Daily_journal dj
             WHERE dj.id_user = u.id_user
             ORDER BY dj.tanggal DESC LIMIT 1) AS status_jurnal,
            -- Absen hari ini
            (SELECT a.keterangan FROM Attendances a
             WHERE a.id_user = u.id_user AND a.tanggal = CURDATE() LIMIT 1) AS absen_hari_ini
        FROM Users u
        JOIN Profile p ON u.id_user = p.id_user
        LEFT JOIN Internship_placement ip ON u.id_user = ip.id_user
        LEFT JOIN Company c ON ip.id_company = c.id_company
        WHERE p.id_dosen_pembimbing = :id_dosen
        ORDER BY u.nama ASC
    ");
    $stmt->execute([':id_dosen' => $id_dosen]);
    $students = $stmt->fetchAll();

    // Format untuk JS
    $studentsJS = [];
    foreach ($students as $s) {
        $tglMulai   = $s['tanggal_mulai']   ? date('d M Y', strtotime($s['tanggal_mulai']))   : '-';
        $tglSelesai = $s['tanggal_selesai'] ? date('d M Y', strtotime($s['tanggal_selesai'])) : '-';
        $studentsJS[] = [
            'nama'       => $s['nama'],
            'nim'        => $s['nim'] ?? '-',
            'instansi'   => $s['instansi'] ?? '-',
            'alamat'     => $s['alamat'] ?? '-',
            'pembimbing' => $s['dosen_pembimbing'] ?? '-',
            'periode'    => ($tglMulai !== '-') ? "$tglMulai – $tglSelesai" : '-',
            'status'     => 'Aktif',
            'telp'       => $s['no_hp'] ?? '-',
            'email'      => $s['email'],
            'hadir'      => (int)$s['jml_hadir'],
            'izin'       => (int)$s['jml_izin'],
            'tidakHadir' => (int)$s['jml_alpha'],
            'catatan'    => $s['catatan_terakhir'] ? '"' . $s['catatan_terakhir'] . '"' : '"Belum ada catatan bimbingan."',
            'badges'     => _buildBadges($s),
        ];
    }

    function _buildBadges(array $s): array {
        $badges = [];
        $absen = $s['absen_hari_ini'] ?? null;
        if ($absen === 'Hadir')   $badges[] = ['Hadir', 'green'];
        elseif ($absen === 'Izin') $badges[] = ['Izin', 'orange'];
        elseif ($absen === 'Alpha') $badges[] = ['Tidak Hadir', 'red'];
        else                        $badges[] = ['Belum Absen', 'gray'];

        $jurnal = $s['status_jurnal'] ?? null;
        if ($jurnal === 'Disetujui')      $badges[] = ['Sudah Approve', 'green'];
        elseif ($jurnal === 'Ditolak')    $badges[] = ['Revisi', 'red'];
        elseif ($jurnal === 'Menunggu')   $badges[] = ['Belum Review', 'orange'];
        else                              $badges[] = ['Belum Review', 'orange'];
        return $badges;
    }

    $totalMhs    = count($students);
    $totalAktif  = $totalMhs; // semua diasumsikan aktif
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

        .student-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .student-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px -8px rgba(0,0,0,0.13);
        }

        /* Modal Overlay */
        #studentModal {
            transition: opacity 0.25s ease;
        }
        #studentModal.hidden { opacity: 0; pointer-events: none; }
        #studentModal:not(.hidden) { opacity: 1; }

        /* Modal Box */
        #modalBox {
            transition: transform 0.3s cubic-bezier(.34,1.56,.64,1), opacity 0.25s ease;
        }
        #studentModal.hidden #modalBox { transform: scale(0.92) translateY(20px); opacity: 0; }
        #studentModal:not(.hidden) #modalBox { transform: scale(1) translateY(0); opacity: 1; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Top Page Header Bar -->
        <div class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 md:px-8 shrink-0">
            <h1 class="text-[18px] font-bold text-gray-900">Mahasiswa Bimbingan Dosen</h1>
            <div class="flex items-center gap-4">
                <button class="relative p-2 rounded-full text-gray-500 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell text-[17px]"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
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
                        <button class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition-colors text-[13px]">
                            <i class="fas fa-filter text-[12px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-user-friends text-blue-500 text-[20px]"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500 mb-0.5">Total Mahasiswa</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $totalMhs ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-[20px]"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500 mb-0.5">Magang Aktif</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $totalAktif ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-clock text-gray-400 text-[20px]"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-gray-500 mb-0.5">Selesai / Belum Mulai</p>
                            <p class="text-3xl font-bold text-gray-900">0 <span class="text-gray-300 text-2xl">/</span> 0</p>
                        </div>
                    </div>
                </div>

                <!-- Student Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="studentGrid">

                    <?php foreach ($studentsJS as $idx => $s): ?>
                    <div class="student-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-3 student-searchable"
                         data-name="<?= strtolower($s['nama']) ?> <?= strtolower($s['nim']) ?> <?= strtolower($s['instansi']) ?>"
                         onclick="openModal(<?= $idx ?>)">

                        <!-- Card Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-gray-800 flex items-center justify-center text-white font-bold text-[14px] shrink-0">
                                    <?= strtoupper(substr($s['nama'], 0, 2)) ?>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-[15px]"><?= htmlspecialchars($s['nama']) ?></p>
                                    <p class="text-[12px] text-gray-400"><?= $s['nim'] ?? '-' ?></p>
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold text-green-600 bg-green-50 px-2.5 py-1 rounded-full">Aktif</span>
                        </div>

                        <!-- Info -->
                        <div class="space-y-1.5 text-[13px] text-gray-500">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-building mt-0.5 w-4 text-center text-gray-400 text-[12px]"></i>
                                <span><?= htmlspecialchars($s['instansi'] ?? '-') ?></span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-map-marker-alt mt-0.5 w-4 text-center text-gray-400 text-[12px]"></i>
                                <span class="line-clamp-1"><?= htmlspecialchars($s['alamat'] ?? '-') ?></span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-calendar-alt mt-0.5 w-4 text-center text-gray-400 text-[12px]"></i>
                                <span><?= $s['periode'] ?></span>
                            </div>
                        </div>

                        <!-- Footer Badges + Arrow -->
                        <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                            <div class="flex flex-wrap gap-1.5">
                                <?php foreach ($s['badges'] as [$label, $color]): ?>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?= $badgeMap[$color] ?>">
                                    <?= $label ?>
                                </span>
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

    <!-- ═══════════════════════════════════════════════
         MODAL DETAIL MAHASISWA
    ════════════════════════════════════════════════ -->
    <div id="studentModal"
         class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         onclick="closeModalOnBackdrop(event)">

        <div id="modalBox"
             class="bg-white rounded-3xl w-full max-w-[520px] max-h-[90vh] overflow-y-auto shadow-2xl relative">

            <!-- ── Blue Banner Header ── -->
            <div class="bg-gradient-to-r from-[#1e40af] to-[#3b66f5] h-28 rounded-t-3xl relative">
                <!-- Close Button -->
                <button onclick="closeModal()"
                        class="absolute top-4 right-4 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-white text-[13px]"></i>
                </button>

                <!-- Avatar overlapping banner -->
                <div id="m-avatar"
                     class="absolute -bottom-7 left-6 w-16 h-16 rounded-full bg-gray-800 border-4 border-white flex items-center justify-center text-white font-bold text-[18px] shadow-lg">
                </div>
            </div>

            <!-- ── Content ── -->
            <div class="pt-10 pb-6 px-6 space-y-5">

                <!-- Name + Status -->
                <div class="flex items-start justify-between">
                    <div>
                        <h3 id="m-nama" class="text-[18px] font-bold text-gray-900"></h3>
                        <p id="m-nim"   class="text-[13px] text-gray-400 mt-0.5"></p>
                    </div>
                    <span id="m-status"
                          class="mt-1 text-[11px] font-semibold text-green-600 bg-green-50 px-3 py-1.5 rounded-full">
                    </span>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100"></div>

                <!-- INFORMASI MAGANG -->
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Informasi Magang</p>
                    <div class="space-y-3 text-[14px]">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-building text-blue-500 text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] text-gray-400">Perusahaan Magang</p>
                                <p id="m-instansi" class="font-semibold text-gray-800"></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-map-marker-alt text-red-400 text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] text-gray-400">Alamat Perusahaan</p>
                                <p id="m-alamat" class="font-semibold text-gray-800"></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-user-tie text-purple-500 text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] text-gray-400">Pembimbing Lapang</p>
                                <p id="m-pembimbing" class="font-semibold text-gray-800"></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-calendar-alt text-green-500 text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] text-gray-400">Periode Magang</p>
                                <p id="m-periode" class="font-semibold text-gray-800"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100"></div>

                <!-- KONTAK MAHASISWA -->
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Kontak Mahasiswa</p>
                    <div class="space-y-2.5 text-[14px]">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-phone-alt text-emerald-500 text-[12px]"></i>
                            </div>
                            <p id="m-telp" class="font-medium text-gray-700"></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-envelope text-indigo-500 text-[12px]"></i>
                            </div>
                            <p id="m-email" class="font-medium text-gray-700"></p>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100"></div>

                <!-- RINGKASAN KEHADIRAN -->
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Ringkasan Kehadiran</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-green-50 rounded-2xl p-4 text-center">
                            <p id="m-hadir" class="text-2xl font-bold text-green-600"></p>
                            <p class="text-[12px] text-green-500 mt-0.5">Hadir</p>
                        </div>
                        <div class="bg-orange-50 rounded-2xl p-4 text-center">
                            <p id="m-izin" class="text-2xl font-bold text-orange-500"></p>
                            <p class="text-[12px] text-orange-400 mt-0.5">Izin</p>
                        </div>
                        <div class="bg-red-50 rounded-2xl p-4 text-center">
                            <p id="m-tidakhadir" class="text-2xl font-bold text-red-500"></p>
                            <p class="text-[12px] text-red-400 mt-0.5">Tidak Hadir</p>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100"></div>

                <!-- CATATAN BIMBINGAN TERAKHIR -->
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Catatan Bimbingan Terakhir</p>
                    <div class="bg-blue-50 rounded-2xl px-4 py-3">
                        <p id="m-catatan" class="text-[14px] text-blue-700 italic leading-relaxed"></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Student data as JSON for JS -->
    <script>
        const students = <?= json_encode($studentsJS, JSON_UNESCAPED_UNICODE) ?>;

        function openModal(idx) {
            const s = students[idx];

            // Populate fields
            document.getElementById('m-avatar').textContent     = s.nama.substring(0,2).toUpperCase();
            document.getElementById('m-nama').textContent       = s.nama;
            document.getElementById('m-nim').textContent        = s.nim;
            document.getElementById('m-status').textContent     = s.status;
            document.getElementById('m-instansi').textContent   = s.instansi;
            document.getElementById('m-alamat').textContent     = s.alamat;
            document.getElementById('m-pembimbing').textContent = s.pembimbing;
            document.getElementById('m-periode').textContent    = s.periode;
            document.getElementById('m-telp').textContent       = s.telp;
            document.getElementById('m-email').textContent      = s.email;
            document.getElementById('m-hadir').textContent      = s.hadir;
            document.getElementById('m-izin').textContent       = s.izin;
            document.getElementById('m-tidakhadir').textContent = s.tidakHadir;
            document.getElementById('m-catatan').textContent    = s.catatan;

            // Show modal
            const modal = document.getElementById('studentModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('studentModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function closeModalOnBackdrop(e) {
            if (e.target === document.getElementById('studentModal')) {
                closeModal();
            }
        }

        // ESC key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

        // Live search
        document.getElementById('searchMhs').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.student-searchable').forEach(card => {
                card.style.display = card.dataset.name.includes(q) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
