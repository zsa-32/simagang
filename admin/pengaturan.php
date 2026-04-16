<?php
session_start();
$role = 'admin';
$activePage = 'pengaturan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - Magang TIF</title>
    <meta name="description" content="Konfigurasi bobot penilaian dan generate nilai akhir mahasiswa magang TIF.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
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
            <div class="max-w-[1200px] mx-auto space-y-6">

                <!-- Heading -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Pengaturan Sistem</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Konfigurasi bobot penilaian dan generate nilai akhir</p>
                </div>

                <!-- Settings Layout -->
                <div class="flex gap-5 items-start">

                    <!-- Left Sidebar Tabs -->
                    <div class="w-56 shrink-0 space-y-1">
                        <button id="tabBobot" onclick="selectTab('bobot')"
                                class="w-full flex items-center gap-2.5 px-4 py-3 rounded-xl text-[13px] font-semibold bg-blue-50 text-blue-700 transition-all text-left">
                            <i class="fas fa-scale-balanced text-blue-500 text-[14px]"></i> Bobot Penilaian
                        </button>
                        <!-- Future tabs can be added here -->
                    </div>

                    <!-- Right Content Panel -->
                    <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

                        <!-- Bobot Penilaian Panel -->
                        <div id="panelBobot">
                            <h3 class="text-[17px] font-bold text-gray-800 mb-1">Bobot Komponen Penilaian</h3>
                            <p class="text-[13px] text-gray-500 mb-6">Tentukan persentase bobot untuk setiap komponen penilaian magang. Total harus 100%.</p>

                            <div class="space-y-4" id="bobotForm">
                                <?php
                                $komponen = [
                                    ['label' => 'Nilai Pembimbing Lapang (Industri)',  'id' => 'bobot_industri',   'value' => 30],
                                    ['label' => 'Nilai Dosen Pembimbing (Akademik)',   'id' => 'bobot_akademik',   'value' => 25],
                                    ['label' => 'Jurnal / Logbook Harian',             'id' => 'bobot_jurnal',     'value' => 15],
                                    ['label' => 'Laporan Akhir Magang',                'id' => 'bobot_laporan',    'value' => 20],
                                    ['label' => 'Presentasi / Seminar Hasil',          'id' => 'bobot_presentasi', 'value' => 10],
                                ];
                                foreach ($komponen as $k):
                                ?>
                                <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0">
                                    <label for="<?= $k['id'] ?>" class="text-[14px] text-gray-700"><?= $k['label'] ?></label>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <input type="number" id="<?= $k['id'] ?>" value="<?= $k['value'] ?>"
                                               min="0" max="100" oninput="hitungTotal()"
                                               class="w-20 px-3 py-2 border border-gray-200 rounded-xl text-[14px] font-medium text-gray-800 text-center outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                                        <span class="text-[14px] text-gray-500 w-4">%</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Total & Simpan -->
                            <div class="flex items-center justify-between mt-6 pt-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-[14px] text-gray-600">Total:</span>
                                    <span id="totalLabel" class="text-[15px] font-bold text-green-600">100%</span>
                                    <i id="totalIcon" class="fas fa-circle-check text-green-500 text-[14px]"></i>
                                    <span id="totalWarning" class="text-[13px] text-red-500 hidden">Total harus tepat 100%</span>
                                </div>
                                <button id="btnSimpanBobot" onclick="simpanBobot()"
                                        class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-[13px] font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                                    <i class="fas fa-floppy-disk"></i> Simpan Bobot
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden transition-all">
        <div class="flex items-center gap-3 bg-gray-900 text-white px-5 py-3.5 rounded-xl shadow-lg text-[13px] font-medium">
            <i class="fas fa-circle-check text-green-400"></i>
            Bobot penilaian berhasil disimpan!
        </div>
    </div>

    <script>
        function hitungTotal() {
            const ids = ['bobot_industri', 'bobot_akademik', 'bobot_jurnal', 'bobot_laporan', 'bobot_presentasi'];
            const total = ids.reduce((sum, id) => {
                const v = parseInt(document.getElementById(id).value) || 0;
                return sum + v;
            }, 0);

            const label   = document.getElementById('totalLabel');
            const icon    = document.getElementById('totalIcon');
            const warning = document.getElementById('totalWarning');
            const btn     = document.getElementById('btnSimpanBobot');

            label.textContent = total + '%';

            if (total === 100) {
                label.className = 'text-[15px] font-bold text-green-600';
                icon.className  = 'fas fa-circle-check text-green-500 text-[14px]';
                icon.classList.remove('hidden');
                warning.classList.add('hidden');
                btn.disabled = false;
                btn.className = 'flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-[13px] font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm';
            } else {
                label.className = 'text-[15px] font-bold text-red-500';
                icon.classList.add('hidden');
                warning.classList.remove('hidden');
                btn.disabled = true;
                btn.className = 'flex items-center gap-2 px-5 py-2.5 bg-gray-300 text-gray-500 text-[13px] font-semibold rounded-xl cursor-not-allowed';
            }
        }

        function simpanBobot() {
            const toast = document.getElementById('toast');
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function selectTab(tab) {
            // Currently only one tab, ready for extension
        }
    </script>
</body>
</html>
