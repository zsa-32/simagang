<?php
    session_start();
    $role = 'mahasiswa';
    $activePage = 'absen';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .dropzone { transition: background-color 0.2s, border-color 0.2s; }
        .dropzone.drag-over { background-color: #eff6ff; border-color: #3b82f6; }
        select:focus, input:focus, textarea:focus { outline: none; }
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
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-white">
            <div class="max-w-[860px] mx-auto space-y-6">

                <!-- Page Banner -->
                <div class="bg-[#2563eb] rounded-xl p-8 flex items-center gap-5 shadow-sm relative overflow-hidden">
                    <div class="text-white z-10 flex gap-4 items-start w-full">
                        <div class="mt-1 shrink-0">
                            <i class="fas fa-clipboard-list text-white text-[30px] drop-shadow-md"></i>
                        </div>
                        <div>
                            <h2 class="text-[20px] font-bold mb-1.5 tracking-tight leading-tight max-w-2xl">
                                Optimalkan Kehadiran dan Kedisiplinan Siswa: Inovasi Terkini dalam Manajemen Absensi
                            </h2>
                            <p class="text-blue-200 text-[13px] flex items-center gap-2">
                                Dashboard <span class="w-1 h-1 rounded-full bg-blue-200 inline-block"></span> Absensi
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8">
                    <div class="mb-7">
                        <h3 class="text-[20px] font-bold text-gray-800">Pengajuan Izin Ketidakhadiran</h3>
                        <p class="text-[14px] text-gray-400 mt-1">Silakan lengkapi form berikut untuk mengajukan izin (Sakit/Keperluan Kampus/Lainnya).</p>
                    </div>

                    <form action="#" method="POST" enctype="multipart/form-data" id="formIzin">

                        <!-- Kategori Izin -->
                        <div class="mb-5">
                            <label for="kategori" class="block text-[14px] font-semibold text-gray-700 mb-2">
                                Kategori Izin <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="kategori" name="kategori" required
                                        class="w-full appearance-none px-4 py-3 border border-gray-200 rounded-xl text-[14px] bg-gray-50 text-gray-500 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all cursor-pointer">
                                    <option value="" disabled selected>Pilih jenis izin..</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="keperluan_kampus">Keperluan Kampus</option>
                                    <option value="keperluan_keluarga">Keperluan Keluarga</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                                    <i class="fas fa-chevron-down text-[11px]"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Dari Tanggal & Sampai Tanggal -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                            <!-- Dari Tanggal -->
                            <div>
                                <label for="dari_tanggal" class="block text-[14px] font-semibold text-gray-700 mb-2">
                                    Dari Tanggal <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400 text-[13px]"></i>
                                    </div>
                                    <input type="date" id="dari_tanggal" name="dari_tanggal" required
                                           class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl text-[14px] text-gray-700 bg-gray-50 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                                </div>
                            </div>

                            <!-- Sampai Tanggal -->
                            <div>
                                <label for="sampai_tanggal" class="block text-[14px] font-semibold text-gray-700 mb-2">
                                    Sampai Tanggal <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400 text-[13px]"></i>
                                    </div>
                                    <input type="date" id="sampai_tanggal" name="sampai_tanggal" required
                                           class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl text-[14px] text-gray-700 bg-gray-50 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Alasan Detail -->
                        <div class="mb-5">
                            <label for="alasan" class="block text-[14px] font-semibold text-gray-700 mb-2">
                                Alasan Detail <span class="text-red-500">*</span>
                            </label>
                            <textarea id="alasan" name="alasan" required rows="4"
                                      placeholder="Jelaskan alasan izin Anda secara detail (misal: Bertemu Dosen Pembimbing Akademik / PA)"
                                      class="w-full px-4 py-3 border border-gray-200 rounded-xl text-[14px] text-gray-700 bg-gray-50 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all placeholder-gray-400 resize-none leading-relaxed"></textarea>
                        </div>

                        <!-- Bukti Pendukung -->
                        <div class="mb-8">
                            <label class="block text-[14px] font-semibold text-gray-700 mb-2">
                                Bukti Pendukung
                            </label>

                            <div id="dropzone"
                                 class="dropzone border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/60 p-10 flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/20 transition-all"
                                 onclick="document.getElementById('buktInput').click()"
                                 ondragover="onDragOver(event)"
                                 ondragleave="onDragLeave(event)"
                                 ondrop="onDrop(event)">

                                <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                    <i class="fas fa-file-alt text-gray-400 text-[22px]"></i>
                                </div>
                                <p class="text-[14px] text-gray-600 font-medium mb-0.5">Unggah Bukti (Surat Sakit / Bukti Chat Dosen)</p>
                                <p class="text-[12px] text-gray-400 mb-4">(Maks. 2MB)</p>
                                <button type="button"
                                        class="border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-[13px] font-medium px-5 py-2 rounded-lg transition-colors shadow-sm">
                                    Pilih File
                                </button>
                                <input type="file" id="buktInput" name="bukti" class="hidden"
                                       accept="image/jpeg,image/png,image/jpg,application/pdf"
                                       onchange="onFileSelect(event)">
                            </div>

                            <!-- File selected indicator -->
                            <div id="filePreview" class="hidden mt-3 p-3 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white rounded-lg border border-gray-200 flex items-center justify-center shrink-0">
                                        <i id="fileIcon" class="fas fa-file text-blue-400 text-[18px]"></i>
                                    </div>
                                    <div>
                                        <p id="fileName" class="text-[13px] font-medium text-gray-800 truncate max-w-[300px]">-</p>
                                        <p id="fileSize" class="text-[11px] text-gray-400 mt-0.5">-</p>
                                    </div>
                                </div>
                                <button type="button" onclick="clearFile()" class="text-gray-400 hover:text-red-500 transition-colors shrink-0">
                                    <i class="fas fa-times text-[15px]"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="absen.php"
                               class="px-6 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 rounded-xl font-medium text-[14px] transition-colors shadow-sm">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-6 py-2.5 bg-[#3b82f6] hover:bg-[#2563eb] text-white rounded-xl font-medium text-[14px] transition-colors shadow-sm flex items-center gap-2">
                                <i class="fas fa-paper-plane text-[13px]"></i> Ajukan Izin
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
        // ── Set default dates ────────────────────────────────────
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('dari_tanggal').value = today;
        document.getElementById('sampai_tanggal').value = today;

        // ── Validate: sampai_tanggal >= dari_tanggal ─────────────
        document.getElementById('dari_tanggal').addEventListener('change', function () {
            document.getElementById('sampai_tanggal').min = this.value;
            if (document.getElementById('sampai_tanggal').value < this.value) {
                document.getElementById('sampai_tanggal').value = this.value;
            }
        });

        // ── Drag & Drop ──────────────────────────────────────────
        function onDragOver(e) {
            e.preventDefault();
            document.getElementById('dropzone').classList.add('drag-over', 'border-blue-400', 'bg-blue-50');
        }
        function onDragLeave(e) {
            document.getElementById('dropzone').classList.remove('drag-over', 'border-blue-400', 'bg-blue-50');
        }
        function onDrop(e) {
            e.preventDefault();
            onDragLeave(e);
            const file = e.dataTransfer.files[0];
            if (file) showPreview(file);
        }
        function onFileSelect(e) {
            const file = e.target.files[0];
            if (file) showPreview(file);
        }

        // ── Preview File ─────────────────────────────────────────
        function showPreview(file) {
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Ukuran file melebihi 2MB. Silakan pilih file yang lebih kecil.');
                clearFile();
                return;
            }
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';

            // Set icon based on file type
            const icon = document.getElementById('fileIcon');
            if (file.type === 'application/pdf') {
                icon.className = 'fas fa-file-pdf text-red-400 text-[18px]';
            } else {
                icon.className = 'fas fa-file-image text-blue-400 text-[18px]';
            }
            document.getElementById('filePreview').classList.remove('hidden');
        }

        function clearFile() {
            document.getElementById('buktInput').value = '';
            document.getElementById('filePreview').classList.add('hidden');
        }
    </script>
</body>
</html>
