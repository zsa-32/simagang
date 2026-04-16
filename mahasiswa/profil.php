<?php
    session_start();
    $role = 'mahasiswa';
    $activePage = 'profil';
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

        .info-card {
            transition: background-color 0.15s ease;
        }
        .info-card:hover {
            background-color: #eef2ff;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <?php include '../includes/header.php'; ?>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-[#f8f9fa]">
            <div class="max-w-[860px] mx-auto space-y-5">

                <!-- ══ Profile Header Card ══ -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <!-- Blue Banner -->
                    <div class="bg-gradient-to-r from-[#1e40af] to-[#3b66f5] h-28 relative"></div>

                    <!-- Avatar + Name Row -->
                    <div class="px-6 pb-6 relative">
                        <!-- Avatar overlapping banner -->
                        <div class="absolute -top-12 left-6">
                            <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-gray-200 overflow-hidden flex items-center justify-center">
                                <!-- Dummy avatar — backend replace with actual photo -->
                                <img src="https://ui-avatars.com/api/?name=Ahmad+Rizki&background=3b66f5&color=fff&size=128"
                                     alt="Foto Profil"
                                     class="w-full h-full object-cover"
                                     id="avatarImg">
                            </div>
                        </div>

                        <!-- Name + Edit button -->
                        <div class="pt-14 flex items-end justify-between">
                            <div>
                                <h2 class="text-[22px] font-bold text-gray-900">
                                    <?= htmlspecialchars($_SESSION['nama'] ?? 'Ahmad Rizki') ?>
                                </h2>
                                <p class="text-[14px] text-blue-600 font-medium mt-0.5">
                                    NIM: <?= htmlspecialchars($_SESSION['nim'] ?? '2023001234') ?>
                                </p>
                            </div>
                            <button id="editBtn"
                                    onclick="toggleEdit()"
                                    class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-xl text-[13px] font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-pen text-[12px]"></i> Edit Profil
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ══ Informasi Pribadi ══ -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <i class="fas fa-user text-[#3b66f5] text-[16px]"></i>
                        <h3 class="text-[16px] font-bold text-gray-800">Informasi Pribadi</h3>
                    </div>

                    <!-- View Mode -->
                    <div id="viewMode" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Kampus</p>
                            <p class="text-[14px] font-semibold text-gray-800">Politeknik Negeri Jember</p>
                        </div>
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Program Studi & Semester</p>
                            <p class="text-[14px] font-semibold text-gray-800">Teknik Informatika</p>
                            <p class="text-[12px] text-gray-400 mt-0.5">Semester 6</p>
                        </div>
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Email Aktif</p>
                            <p class="text-[14px] font-semibold text-gray-800">AhmadRizki@students.polije.ac.id</p>
                        </div>
                        <div class="info-card bg-gray-50 rounded-xl p-4">
                            <p class="text-[11px] font-semibold text-gray-400 mb-1">Nomor Telepon / WhatsApp</p>
                            <p class="text-[14px] font-semibold text-gray-800">+62 812-3456-7890</p>
                        </div>
                    </div>

                    <!-- Edit Mode (hidden by default) -->
                    <div id="editMode" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Kampus</label>
                            <input type="text" value="Politeknik Negeri Jember"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Program Studi</label>
                            <input type="text" value="Teknik Informatika"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Semester</label>
                            <input type="number" value="6" min="1" max="14"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Email Aktif</label>
                            <input type="email" value="AhmadRizki@students.polije.ac.id"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Nomor Telepon / WhatsApp</label>
                            <input type="tel" value="+62 812-3456-7890"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[14px] text-gray-800 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-500 mb-1.5">Foto Profil</label>
                            <label class="flex items-center gap-2 cursor-pointer border border-dashed border-gray-300 rounded-xl px-4 py-2.5 text-[13px] text-gray-500 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-upload text-gray-400"></i>
                                <span>Pilih foto baru...</span>
                                <input type="file" accept="image/*" class="hidden">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ══ Informasi Penempatan Magang ══ -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
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
                                <p class="text-[14px] font-semibold text-gray-800">PT Maju Jaya Technology</p>
                            </div>
                        </div>

                        <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-laptop-code text-white text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-blue-400 mb-1">Posisi Magang</p>
                                <p class="text-[14px] font-semibold text-gray-800">Frontend Developer Intern</p>
                            </div>
                        </div>

                        <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-calendar-alt text-white text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-blue-400 mb-1">Tanggal Mulai – Selesai</p>
                                <p class="text-[14px] font-semibold text-gray-800">15 Januari 2026 – 15 Juni 2026</p>
                            </div>
                        </div>

                        <div class="info-card bg-blue-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#3b66f5] flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-user-tie text-white text-[13px]"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-blue-400 mb-1">Nama Pembimbing Lapang</p>
                                <p class="text-[14px] font-semibold text-gray-800">Budi Santoso, S.Kom</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ Action Buttons ══ -->
                <div class="flex justify-end gap-3 pb-2">
                    <button id="cancelBtn"
                            onclick="cancelEdit()"
                            class="hidden px-5 py-2.5 border border-gray-200 rounded-xl text-[13px] font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button id="saveBtn"
                            onclick="saveProfile()"
                            class="hidden flex items-center gap-2 px-5 py-2.5 bg-[#3b66f5] hover:bg-[#2d53d4] text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm shadow-blue-200">
                        <i class="fas fa-save text-[12px]"></i> Simpan Perubahan
                    </button>
                    <a href="dashboard.php"
                       id="selesaiBtn"
                       class="px-6 py-2.5 bg-[#3b66f5] hover:bg-[#2d53d4] text-white rounded-xl text-[13px] font-semibold transition-colors shadow-sm shadow-blue-200">
                        Selesai
                    </a>
                </div>

            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
        let isEditing = false;

        function toggleEdit() {
            isEditing = true;

            document.getElementById('viewMode').classList.add('hidden');
            document.getElementById('editMode').classList.remove('hidden');

            document.getElementById('editBtn').classList.add('hidden');
            document.getElementById('selesaiBtn').classList.add('hidden');
            document.getElementById('cancelBtn').classList.remove('hidden');
            document.getElementById('saveBtn').classList.remove('hidden');
        }

        function cancelEdit() {
            isEditing = false;

            document.getElementById('viewMode').classList.remove('hidden');
            document.getElementById('editMode').classList.add('hidden');

            document.getElementById('editBtn').classList.remove('hidden');
            document.getElementById('selesaiBtn').classList.remove('hidden');
            document.getElementById('cancelBtn').classList.add('hidden');
            document.getElementById('saveBtn').classList.add('hidden');
        }

        function saveProfile() {
            const btn = document.getElementById('saveBtn');
            btn.innerHTML = '<i class="fas fa-check text-[12px]"></i> Tersimpan!';
            btn.classList.replace('bg-[#3b66f5]', 'bg-green-500');
            btn.classList.replace('hover:bg-[#2d53d4]', 'hover:bg-green-600');
            btn.classList.replace('shadow-blue-200', 'shadow-green-200');

            setTimeout(() => {
                cancelEdit();
                btn.innerHTML = '<i class="fas fa-save text-[12px]"></i> Simpan Perubahan';
                btn.classList.replace('bg-green-500', 'bg-[#3b66f5]');
                btn.classList.replace('hover:bg-green-600', 'hover:bg-[#2d53d4]');
                btn.classList.replace('shadow-green-200', 'shadow-blue-200');
            }, 1200);
        }
    </script>
</body>
</html>
