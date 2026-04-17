<?php
    session_start();
    require_once '../config/db_connect.php';

    if (!isset($_SESSION['id_user']) || strtolower($_SESSION['role_name']) !== 'mahasiswa') {
        header('Location: ../index.php'); exit();
    }

    $role = 'mahasiswa';
    $activePage = 'profil';
    $id_user = (int) $_SESSION['id_user'];

    // Ambil data lengkap user
    $stmt = $conn->prepare("
        SELECT
            u.nama, u.email,
            p.nim, p.no_hp, p.alamat, p.prodi, p.semester,
            p.dosen_pembimbing, p.foto,
            c.nama_company,
            ip.tanggal_mulai, ip.tanggal_selesai,
            ub.nama AS nama_pembimbing
        FROM Users u
        LEFT JOIN Profile       p  ON u.id_user   = p.id_user
        LEFT JOIN Internship_placement ip ON u.id_user = ip.id_user
        LEFT JOIN Company       c  ON ip.id_company = c.id_company
        LEFT JOIN Users         ub ON p.id_dosen_pembimbing = ub.id_user
        WHERE u.id_user = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id_user]);
    $data = $stmt->fetch() ?: [];

    // Fungsi helper agar tidak error jika kolom kosong
    $get = fn($key, $default = '-') => !empty($data[$key]) ? $data[$key] : $default;

    // Foto profil
    $fotoSrc = !empty($data['foto']) && file_exists('../' . $data['foto'])
        ? '../' . $data['foto']
        : 'https://ui-avatars.com/api/?name=' . urlencode($data['nama'] ?? 'U') . '&background=3b66f5&color=fff&size=128';

    // Format tanggal magang
    $tglMulai   = !empty($data['tanggal_mulai'])   ? date('d M Y', strtotime($data['tanggal_mulai']))   : '-';
    $tglSelesai = !empty($data['tanggal_selesai']) ? date('d M Y', strtotime($data['tanggal_selesai'])) : '-';
    $periodeStr = ($tglMulai !== '-') ? "$tglMulai – $tglSelesai" : '-';

    // Nama pembimbing lapang (dari profil atau relasi)
    $namaPembimbing = $get('nama_pembimbing') !== '-' ? $data['nama_pembimbing'] : $get('dosen_pembimbing');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .info-card { transition: background-color 0.15s ease; }
        .info-card:hover { background-color: #eef2ff; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../includes/header.php'; ?>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[860px] mx-auto space-y-5">

                <!-- Notifikasi -->
                <?php if (isset($_GET['success'])): ?>
                    <div id="notifBox" class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-[13px] font-medium">
                        <i class="fas fa-check-circle text-green-500"></i> Profil berhasil diperbarui.
                        <button onclick="document.getElementById('notifBox').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
                    </div>
                <?php elseif (isset($_GET['error'])): ?>
                    <?php
                    $errs = [
                        'field_kosong'   => 'Nama dan email tidak boleh kosong.',
                        'email_duplikat' => 'Email sudah digunakan akun lain.',
                        'file_besar'     => 'Ukuran foto melebihi 2MB.',
                        'file_format'    => 'Format foto tidak didukung (gunakan JPG/PNG).',
                        'upload_gagal'   => 'Gagal mengupload foto.',
                        'password_pendek'=> 'Password baru minimal 6 karakter.',
                        'db_error'       => 'Terjadi kesalahan database.',
                    ];
                    $errMsg = $errs[$_GET['error']] ?? 'Terjadi kesalahan.';
                    ?>
                    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-[13px] font-medium">
                        <i class="fas fa-exclamation-circle text-red-500"></i> <?= htmlspecialchars($errMsg) ?>
                    </div>
                <?php endif; ?>

                <!-- ══ Profile Header Card ══ -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#1e40af] to-[#3b66f5] h-28 relative"></div>
                    <div class="px-6 pb-6 relative">
                        <div class="absolute -top-12 left-6">
                            <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-gray-200 overflow-hidden flex items-center justify-center">
                                <img src="<?= htmlspecialchars($fotoSrc) ?>" alt="Foto Profil" class="w-full h-full object-cover" id="avatarImg">
                            </div>
                        </div>
                        <div class="pt-14 flex items-end justify-between">
                            <div>
                                <h2 class="text-[22px] font-bold text-gray-900"><?= htmlspecialchars($get('nama', 'Nama Pengguna')) ?></h2>
                                <p class="text-[14px] text-blue-600 font-medium mt-0.5">NIM: <?= htmlspecialchars($get('nim')) ?></p>
                            </div>
                            <button id="editBtn" onclick="toggleEdit()"
                                class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-xl text-[13px] font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-pen text-[12px]"></i> Edit Profil
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ══ Form Wrapper (View + Edit) ══ -->
                <form id="profilForm" action="../proses/profil_simpan.php" method="POST" enctype="multipart/form-data">

                    <!-- ══ Informasi Pribadi ══ -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center gap-2 mb-5">
                            <i class="fas fa-user text-[#3b66f5] text-[16px]"></i>
                            <h3 class="text-[16px] font-bold text-gray-800">Informasi Pribadi</h3>
                        </div>

                        <!-- View Mode -->
                        <div id="viewMode" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="info-card bg-gray-50 rounded-xl p-4">
                                <p class="text-[11px] font-semibold text-gray-400 mb-1">Nama Lengkap</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($get('nama')) ?></p>
                            </div>
                            <div class="info-card bg-gray-50 rounded-xl p-4">
                                <p class="text-[11px] font-semibold text-gray-400 mb-1">Program Studi &amp; Semester</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($get('prodi', 'Teknik Informatika')) ?></p>
                                <p class="text-[12px] text-gray-400 mt-0.5">Semester <?= htmlspecialchars($get('semester', '-')) ?></p>
                            </div>
                            <div class="info-card bg-gray-50 rounded-xl p-4">
                                <p class="text-[11px] font-semibold text-gray-400 mb-1">Email Aktif</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($get('email')) ?></p>
                            </div>
                            <div class="info-card bg-gray-50 rounded-xl p-4">
                                <p class="text-[11px] font-semibold text-gray-400 mb-1">Nomor Telepon / WhatsApp</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($get('no_hp')) ?></p>
                            </div>
                            <div class="info-card bg-gray-50 rounded-xl p-4 sm:col-span-2">
                                <p class="text-[11px] font-semibold text-gray-400 mb-1">Alamat</p>
                                <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($get('alamat')) ?></p>
                            </div>
                        </div>

                        <!-- Edit Mode -->
                        <div id="editMode" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Nama Lengkap</label>
                                <input type="text" name="nama" value="<?= htmlspecialchars($get('nama', '')) ?>" required
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Email Aktif</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($get('email', '')) ?>" required
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Program Studi</label>
                                <input type="text" name="prodi" value="<?= htmlspecialchars($get('prodi', '')) ?>"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Semester</label>
                                <input type="number" name="semester" value="<?= (int)($data['semester'] ?? 0) ?>" min="1" max="14"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Nomor Telepon / WhatsApp</label>
                                <input type="tel" name="no_hp" value="<?= htmlspecialchars($get('no_hp', '')) ?>"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Alamat</label>
                                <input type="text" name="alamat" value="<?= htmlspecialchars($get('alamat', '')) ?>"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Ganti Password <span class="text-gray-400 font-normal">(opsional)</span></label>
                                <input type="password" name="password_baru" placeholder="Kosongkan jika tidak ingin mengubah"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Foto Profil <span class="text-gray-400 font-normal">(maks 2MB)</span></label>
                                <label class="flex items-center gap-2 cursor-pointer border border-dashed border-gray-300 rounded-xl px-4 py-2.5 text-[13px] text-gray-500 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-upload text-gray-400"></i>
                                    <span id="fotoLabel">Pilih foto baru...</span>
                                    <input type="file" name="foto" accept="image/*" class="hidden" onchange="previewFoto(this)">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- ══ Informasi Penempatan Magang ══ -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-5">
                        <div class="flex items-center gap-2 mb-5">
                            <i class="fas fa-building text-[#3b66f5] text-[16px]"></i>
                            <h3 class="text-[16px] font-bold text-gray-800">Informasi Penempatan Magang</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                                <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-building text-white text-[13px]"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-blue-400 mb-1">Nama Perusahaan/Instansi</p>
                                    <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($get('nama_company', 'Belum ditetapkan')) ?></p>
                                </div>
                            </div>
                            <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                                <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-calendar-alt text-white text-[13px]"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-blue-400 mb-1">Periode Magang</p>
                                    <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($periodeStr) ?></p>
                                </div>
                            </div>
                            <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3 sm:col-span-2">
                                <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-user-tie text-white text-[13px]"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-blue-400 mb-1">Dosen / Pembimbing Lapang</p>
                                    <p class="text-[14px] font-semibold text-gray-800"><?= htmlspecialchars($namaPembimbing) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ Action Buttons ══ -->
                    <div class="flex justify-end gap-3 pb-2 mt-4">
                        <button type="button" id="cancelBtn" onclick="cancelEdit()"
                            class="hidden px-5 py-2.5 border border-gray-200 rounded-xl text-[13px] font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" id="saveBtn"
                            class="hidden items-center gap-2 px-5 py-2.5 bg-[#3b66f5] hover:bg-[#2d53d4] text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm shadow-blue-200">
                            <i class="fas fa-save text-[12px]"></i> Simpan Perubahan
                        </button>
                        <a href="dashboard.php" id="selesaiBtn"
                           class="px-6 py-2.5 bg-[#3b66f5] hover:bg-[#2d53d4] text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm shadow-blue-200">
                            Selesai
                        </a>
                    </div>
                </form>

            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
        function toggleEdit() {
            document.getElementById('viewMode').classList.add('hidden');
            document.getElementById('editMode').classList.remove('hidden');
            document.getElementById('editBtn').classList.add('hidden');
            document.getElementById('selesaiBtn').classList.add('hidden');
            document.getElementById('cancelBtn').classList.remove('hidden');
            document.getElementById('saveBtn').classList.remove('hidden');
            document.getElementById('saveBtn').classList.add('flex');
        }

        function cancelEdit() {
            document.getElementById('viewMode').classList.remove('hidden');
            document.getElementById('editMode').classList.add('hidden');
            document.getElementById('editBtn').classList.remove('hidden');
            document.getElementById('selesaiBtn').classList.remove('hidden');
            document.getElementById('cancelBtn').classList.add('hidden');
            document.getElementById('saveBtn').classList.add('hidden');
            document.getElementById('saveBtn').classList.remove('flex');
        }

        function previewFoto(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('fotoLabel').textContent = file.name;
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('avatarImg').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }hadow-blue-200');
            }, 1200);
        }
    </script>
</body>
</html>
