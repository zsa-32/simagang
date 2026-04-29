<?php
require_once __DIR__ . "/../config/role_guard.php";
checkRole("admin");
require_once __DIR__ . '/../config/db_connect.php';
$role = 'admin';
$activePage = 'pengaturan';

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_bobot'])) {
    $komponenData = [
        ['nama' => 'Nilai Pembimbing Lapang (Industri)', 'bobot' => (int)$_POST['bobot_industri']],
        ['nama' => 'Nilai Dosen Pembimbing (Akademik)', 'bobot' => (int)$_POST['bobot_akademik']],
        ['nama' => 'Jurnal / Logbook Harian', 'bobot' => (int)$_POST['bobot_jurnal']],
        ['nama' => 'Laporan Akhir Magang', 'bobot' => (int)$_POST['bobot_laporan']],
        ['nama' => 'Presentasi / Seminar Hasil', 'bobot' => (int)$_POST['bobot_presentasi']],
    ];
    foreach ($komponenData as $k) {
        $chk = $conn->prepare("SELECT id FROM komponen_penilaian WHERE nama = :n");
        $chk->execute(['n' => $k['nama']]);
        $existing = $chk->fetch();
        if ($existing) {
            $conn->prepare("UPDATE komponen_penilaian SET bobot_persen = :b WHERE id = :id")->execute(['b' => $k['bobot'], 'id' => $existing['id']]);
        } else {
            $conn->prepare("INSERT INTO komponen_penilaian (nama, bobot_persen) VALUES (:n, :b)")->execute(['n' => $k['nama'], 'b' => $k['bobot']]);
        }
    }
    $saved = true;
}

// Load existing bobot
$defaults = [
    'Nilai Pembimbing Lapang (Industri)' => 30,
    'Nilai Dosen Pembimbing (Akademik)' => 25,
    'Jurnal / Logbook Harian' => 15,
    'Laporan Akhir Magang' => 20,
    'Presentasi / Seminar Hasil' => 10,
];
$stmt = $conn->query("SELECT nama, bobot_persen FROM komponen_penilaian");
$rows = $stmt->fetchAll();
foreach ($rows as $r) {
    if (isset($defaults[$r['nama']])) $defaults[$r['nama']] = (int)$r['bobot_persen'];
}
$komponen = [
    ['label' => 'Nilai Pembimbing Lapang (Industri)', 'id' => 'bobot_industri', 'value' => $defaults['Nilai Pembimbing Lapang (Industri)']],
    ['label' => 'Nilai Dosen Pembimbing (Akademik)', 'id' => 'bobot_akademik', 'value' => $defaults['Nilai Dosen Pembimbing (Akademik)']],
    ['label' => 'Jurnal / Logbook Harian', 'id' => 'bobot_jurnal', 'value' => $defaults['Jurnal / Logbook Harian']],
    ['label' => 'Laporan Akhir Magang', 'id' => 'bobot_laporan', 'value' => $defaults['Laporan Akhir Magang']],
    ['label' => 'Presentasi / Seminar Hasil', 'id' => 'bobot_presentasi', 'value' => $defaults['Presentasi / Seminar Hasil']],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - Magang TIF</title>
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
                <div><h2 class="text-2xl font-bold text-gray-900">Pengaturan Sistem</h2><p class="text-gray-500 text-sm mt-0.5">Konfigurasi bobot penilaian dan generate nilai akhir</p></div>

                <?php if ($saved): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-[14px] flex items-center gap-2"><i class="fas fa-check-circle"></i> Bobot penilaian berhasil disimpan ke database.</div>
                <?php endif; ?>

                <div class="flex gap-5 items-start">
                    <div class="w-56 shrink-0 space-y-1">
                        <button class="w-full flex items-center gap-2.5 px-4 py-3 rounded-xl text-[13px] font-semibold bg-blue-50 text-blue-700 text-left"><i class="fas fa-scale-balanced text-blue-500 text-[14px]"></i> Bobot Penilaian</button>
                    </div>
                    <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-[17px] font-bold text-gray-800 mb-1">Bobot Komponen Penilaian</h3>
                        <p class="text-[13px] text-gray-500 mb-6">Tentukan persentase bobot. Total harus 100%.</p>
                        <form method="POST">
                            <input type="hidden" name="save_bobot" value="1">
                            <div class="space-y-4" id="bobotForm">
                                <?php foreach ($komponen as $k): ?>
                                <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0">
                                    <label for="<?= $k['id'] ?>" class="text-[14px] text-gray-700"><?= $k['label'] ?></label>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <input type="number" id="<?= $k['id'] ?>" name="<?= $k['id'] ?>" value="<?= $k['value'] ?>" min="0" max="100" oninput="hitungTotal()" class="w-20 px-3 py-2 border border-gray-200 rounded-xl text-[14px] font-medium text-gray-800 text-center outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                                        <span class="text-[14px] text-gray-500 w-4">%</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="flex items-center justify-between mt-6 pt-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-[14px] text-gray-600">Total:</span>
                                    <span id="totalLabel" class="text-[15px] font-bold text-green-600">100%</span>
                                    <i id="totalIcon" class="fas fa-circle-check text-green-500 text-[14px]"></i>
                                    <span id="totalWarning" class="text-[13px] text-red-500 hidden">Total harus tepat 100%</span>
                                </div>
                                <button type="submit" id="btnSimpanBobot" class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-[13px] font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm"><i class="fas fa-floppy-disk"></i> Simpan Bobot</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
    <script>
        function hitungTotal() {
            const ids = ['bobot_industri','bobot_akademik','bobot_jurnal','bobot_laporan','bobot_presentasi'];
            const total = ids.reduce((s, id) => s + (parseInt(document.getElementById(id).value) || 0), 0);
            const label = document.getElementById('totalLabel'), icon = document.getElementById('totalIcon'), warning = document.getElementById('totalWarning'), btn = document.getElementById('btnSimpanBobot');
            label.textContent = total + '%';
            if (total === 100) { label.className = 'text-[15px] font-bold text-green-600'; icon.className = 'fas fa-circle-check text-green-500 text-[14px]'; icon.classList.remove('hidden'); warning.classList.add('hidden'); btn.disabled = false; btn.className = 'flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-[13px] font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm'; }
            else { label.className = 'text-[15px] font-bold text-red-500'; icon.classList.add('hidden'); warning.classList.remove('hidden'); btn.disabled = true; btn.className = 'flex items-center gap-2 px-5 py-2.5 bg-gray-300 text-gray-500 text-[13px] font-semibold rounded-xl cursor-not-allowed'; }
        }
        hitungTotal();
    </script>
</body>
</html>
