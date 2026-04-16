<?php
    session_start();
    $role = 'mahasiswa';
    $activePage = 'jurnal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jurnal - Magang TIF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .dropzone { transition: background-color 0.2s, border-color 0.2s; }
        .dropzone.drag-over { background-color: #eff6ff; border-color: #3b82f6; }
        input[type="date"]::-webkit-calendar-picker-indicator { opacity: 0.5; cursor: pointer; }
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

                <!-- Page Banner -->
                <div class="bg-[#3b66f5] rounded-2xl p-8 flex items-center gap-5 shadow-sm relative overflow-hidden">
                    <div class="w-[64px] h-[64px] bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/10 shrink-0 z-10">
                        <i class="fas fa-book text-white text-[24px]"></i>
                    </div>
                    <div class="text-white z-10">
                        <h2 class="text-[24px] font-bold mb-1 tracking-tight">Data Jurnal</h2>
                        <p class="text-blue-100 text-[14px]">Kelola dan pantau jurnal kegiatan magang siswa</p>
                    </div>
                    <div class="absolute right-0 top-0 w-80 h-full bg-gradient-to-l from-[#254bdb] to-transparent z-0 opacity-80"></div>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <div class="mb-7">
                        <h3 class="text-[20px] font-bold text-gray-800">Tambah Jurnal Harian</h3>
                        <p class="text-[14px] text-gray-400 mt-1">Isi detail kegiatan magang Anda hari ini.</p>
                    </div>

                    <form action="#" method="POST" enctype="multipart/form-data" id="formJurnal">

                        <!-- Tanggal Kegiatan -->
                        <div class="mb-6">
                            <label for="tanggal" class="block text-[14px] font-semibold text-gray-700 mb-2">
                                Tanggal Kegiatan <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400 text-[14px]"></i>
                                </div>
                                <input type="date" id="tanggal" name="tanggal" required
                                       class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl text-[14px] text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all placeholder-gray-400">
                            </div>
                        </div>

                        <!-- Judul Kegiatan -->
                        <div class="mb-6">
                            <label for="judul" class="block text-[14px] font-semibold text-gray-700 mb-2">
                                Judul Kegiatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="judul" name="judul" required
                                   placeholder="Misal: Training Dasar IT"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-[14px] text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all placeholder-gray-400">
                        </div>

                        <!-- Bukti Kegiatan -->
                        <div class="mb-6">
                            <label class="block text-[14px] font-semibold text-gray-700 mb-2">
                                Bukti Kegiatan
                            </label>

                            <!-- Dropzone -->
                            <div id="dropzone"
                                 class="dropzone border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/60 p-8 flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/30 transition-all"
                                 onclick="document.getElementById('buktFile').click()"
                                 ondragover="onDragOver(event)"
                                 ondragleave="onDragLeave(event)"
                                 ondrop="onDrop(event)">

                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-[20px]"></i>
                                </div>
                                <p class="text-[14px] text-gray-600 font-medium">Klik atau seret gambar ke sini</p>
                                <p class="text-[12px] text-gray-400 mt-1 mb-4">(Maks. 2MB)</p>
                                <button type="button"
                                        class="border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-[13px] font-medium px-5 py-2 rounded-lg transition-colors shadow-sm">
                                    Pilih File
                                </button>
                                <input type="file" id="buktFile" name="bukti" class="hidden"
                                       accept="image/jpeg,image/png,image/jpg"
                                       onchange="onFileSelect(event)">
                            </div>

                            <!-- Preview -->
                            <div id="filePreview" class="hidden mt-3 p-3 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden border border-gray-200 bg-white shrink-0 flex items-center justify-center">
                                        <img id="previewImg" src="#" alt="preview" class="w-full h-full object-cover hidden">
                                        <i class="fas fa-image text-gray-400 text-[18px]" id="previewIcon"></i>
                                    </div>
                                    <div>
                                        <p id="previewName" class="text-[13px] font-medium text-gray-800 truncate max-w-[280px]">-</p>
                                        <p id="previewSize" class="text-[11px] text-gray-400 mt-0.5">-</p>
                                    </div>
                                </div>
                                <button type="button" onclick="clearFile()" class="text-gray-400 hover:text-red-500 transition-colors shrink-0">
                                    <i class="fas fa-times text-[15px]"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Deskripsi Kegiatan -->
                        <div class="mb-8">
                            <label for="deskripsi" class="block text-[14px] font-semibold text-gray-700 mb-2">
                                Deskripsi Kegiatan <span class="text-red-500">*</span>
                            </label>
                            <textarea id="deskripsi" name="deskripsi" required rows="5"
                                      placeholder="Tuliskan rincian kegiatan atau observasi Anda..."
                                      class="w-full px-4 py-3 border border-gray-200 rounded-xl text-[14px] text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all placeholder-gray-400 resize-none leading-relaxed"></textarea>
                            <p class="text-[12px] text-gray-400 mt-1.5 text-right"><span id="charCount">0</span> karakter</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="jurnal.php"
                               class="px-6 py-2.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 rounded-xl font-medium text-[14px] transition-colors shadow-sm">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-6 py-2.5 bg-[#3b82f6] hover:bg-[#2563eb] text-white rounded-xl font-medium text-[14px] transition-colors shadow-sm flex items-center gap-2">
                                <i class="fas fa-save text-[13px]"></i> Simpan Jurnal
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

    <script>
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
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                alert('Ukuran file melebihi 2MB. Silakan pilih file yang lebih kecil.');
                clearFile();
                return;
            }
            document.getElementById('previewName').textContent = file.name;
            document.getElementById('previewSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
            document.getElementById('filePreview').classList.remove('hidden');

            // Show image preview if it's an image
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('previewImg');
                img.src = e.target.result;
                img.classList.remove('hidden');
                document.getElementById('previewIcon').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        function clearFile() {
            document.getElementById('buktFile').value = '';
            document.getElementById('filePreview').classList.add('hidden');
            document.getElementById('previewImg').src = '#';
            document.getElementById('previewImg').classList.add('hidden');
            document.getElementById('previewIcon').classList.remove('hidden');
        }

        // ── Character Counter ─────────────────────────────────────
        document.getElementById('deskripsi').addEventListener('input', function() {
            document.getElementById('charCount').textContent = this.value.length;
        });

        // ── Set tanggal default ke hari ini ──────────────────────
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tanggal').value = today;
    </script>
</body>
</html>
