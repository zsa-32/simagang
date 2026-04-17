<?php
session_start();
$role = 'admin';
$activePage = 'manajemen_user';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Magang TIF</title>
    <meta name="description" content="Kelola akun Mahasiswa dan Dosen Pembimbing pada sistem Magang TIF.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

                <!-- Page Heading -->
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Manajemen User</h2>
                        <p class="text-gray-500 text-sm mt-0.5">Kelola akun Mahasiswa dan Dosen Pembimbing</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button id="btnExport"
                            class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                            <i class="fas fa-download text-gray-500"></i> Export
                        </button>
                        <button id="btnBuatAkun" onclick="openModal()"
                            class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-[13px] font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                            <i class="fas fa-user-plus"></i> Buat Akun
                        </button>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <!-- Search & Filter -->
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-5 border-b border-gray-100">
                        <div class="relative flex-1 w-full">
                            <i
                                class="fas fa-search text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-[13px]"></i>
                            <input id="searchInput" type="text" placeholder="Cari nama atau email..."
                                oninput="filterTable()"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-[13px] text-gray-700 outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button id="filterAll" onclick="setFilter('semua')"
                                class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-blue-600 text-white transition-all">Semua</button>
                            <button id="filterMhs" onclick="setFilter('mahasiswa')"
                                class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all">Mahasiswa</button>
                            <button id="filterDosen" onclick="setFilter('dosen')"
                                class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all">Dosen
                                Pembimbing</button>
                            <button id="filterpembimbing" onclick="setFilter('pembimbing')"
                                class="filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all">
                                Pembimbing Lapang</button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full" id="userTable">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        Nama</th>
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        Role</th>
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        ID</th>
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        Perusahaan</th>
                                    <th
                                        class="text-left text-[12px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3.5">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody" class="divide-y divide-gray-50">
                                <?php
                                $users = [
                                    ['nama' => 'Balmond', 'email' => 'balmond@student.polije.ac.id', 'role' => 'Mahasiswa', 'id' => '21173431', 'perusahaan' => 'PT Telkom Indonesia', 'color' => 'bg-blue-600'],
                                    ['nama' => 'Lesley', 'email' => 'lesley@student.polije.ac.id', 'role' => 'Mahasiswa', 'id' => '20193432', 'perusahaan' => 'CV Digital Kreatif', 'color' => 'bg-indigo-500'],
                                    ['nama' => 'Harley', 'email' => 'harley@student.polije.ac.id', 'role' => 'Mahasiswa', 'id' => '22123532', 'perusahaan' => 'PT Bank BRI', 'color' => 'bg-green-600'],
                                    ['nama' => 'Budi Santoso', 'email' => 'budi.s@student.polije.ac.id', 'role' => 'Mahasiswa', 'id' => '21140024', 'perusahaan' => 'PT Astra International', 'color' => 'bg-blue-500'],
                                    ['nama' => 'Dr. Budi Santoso, M.Kom', 'email' => 'budi.santoso@polije.ac.id', 'role' => 'Dosen Pembimbing', 'id' => '198501012010011001', 'perusahaan' => '-', 'color' => 'bg-purple-600'],
                                    ['nama' => 'Dr. Siti Rahayu, M.T', 'email' => 'siti.rahayu@polije.ac.id', 'role' => 'Dosen Pembimbing', 'id' => '198703022012012002', 'perusahaan' => '-', 'color' => 'bg-purple-500'],
                                    ['nama' => 'Ir. Made Wirawan, M.Sc', 'email' => 'made.wirawan@polije.ac.id', 'role' => 'Dosen Pembimbing', 'id' => '198204152008011003', 'perusahaan' => '-', 'color' => 'bg-indigo-600'],
                                    ['nama' => 'Joko', 'email' => 'joko@student.polije.ac.id', 'role' => 'Mahasiswa', 'id' => '21130003', 'perusahaan' => 'PT Tokopedia', 'color' => 'bg-blue-700'],
                                ];
                                foreach ($users as $u):
                                    $initials = strtoupper(substr($u['nama'], 0, 1));
                                    $isDosenRole = ($u['role'] === 'Dosen Pembimbing');
                                    $roleBadge = $isDosenRole
                                        ? 'bg-purple-100 text-purple-700'
                                        : 'bg-blue-100 text-blue-700';
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors user-row"
                                        data-role="<?= strtolower($isDosenRole ? 'dosen' : 'mahasiswa') ?>"
                                        data-search="<?= strtolower($u['nama'] . ' ' . $u['email']) ?>">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-9 h-9 rounded-full <?= $u['color'] ?> text-white flex items-center justify-center text-[13px] font-bold shrink-0">
                                                    <?= $initials ?></div>
                                                <div>
                                                    <p class="font-semibold text-gray-800 text-[14px]">
                                                        <?= htmlspecialchars($u['nama']) ?></p>
                                                    <p class="text-[12px] text-gray-400">
                                                        <?= htmlspecialchars($u['email']) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-1 rounded-lg text-[12px] font-semibold <?= $roleBadge ?>"><?= $u['role'] ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-[13px] text-gray-600 font-medium"><?= $u['id'] ?></td>
                                        <td class="px-6 py-4 text-[13px] text-gray-600"><?= $u['perusahaan'] ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    class="w-8 h-8 rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 transition-colors flex items-center justify-center"
                                                    title="Edit"
                                                    onclick="openEditModal('<?= htmlspecialchars($u['nama']) ?>', '<?= htmlspecialchars($u['email']) ?>', '<?= htmlspecialchars($u['role']) ?>', '<?= htmlspecialchars($u['id']) ?>')">
                                                    <i class="fas fa-pen text-[12px]"></i>
                                                </button>
                                                <button
                                                    class="w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors flex items-center justify-center"
                                                    title="Hapus">
                                                    <i class="fas fa-trash text-[12px]"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
                        <p class="text-[13px] text-gray-500">Menampilkan 1-8 dari 11 user</p>
                        <div class="flex items-center gap-1">
                            <button
                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 flex items-center justify-center text-[12px]"><i
                                    class="fas fa-chevron-left"></i></button>
                            <button
                                class="w-8 h-8 rounded-lg bg-blue-600 text-white font-semibold text-[13px] flex items-center justify-center">1</button>
                            <button
                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 text-[13px] flex items-center justify-center">2</button>
                            <button
                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 flex items-center justify-center text-[12px]"><i
                                    class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

            </div>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- Modal Buat Akun -->
    <div id="modalBuatAkun" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Buat Akun Baru</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="proses_buat_akun.php" method="POST" class="space-y-4">
    
    <div>
        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
        <input type="text" name="nama" placeholder="Masukkan nama lengkap" required
            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
    </div>

    <div class="mb-4">
        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Email</label>
        <input type="email" name="email" placeholder="contoh@polije.ac.id" required
            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="mb-4">
            <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Role</label>
            <select name="role" required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-white">
                <option value="">Pilih role...</option>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="dosen">Dosen Pembimbing</option>
                <option value="pembimbing">Pembimbing Lapang</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-[13px] font-medium text-gray-700 mb-1.5">NIM/NIP</label>
            <input type="text" name="nomor_induk" placeholder="Masukan NIM/NIP" required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
        </div>
    </div>

    <div>
    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Password</label>
    <div class="relative">
        <input type="password" id="passwordInput" name="password" placeholder="Minimal 8 karakter" required
            class="w-full pl-4 pr-10 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
        
        <span id="togglePasswordAkun" class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-eye-slash" id="eyeIconAkun"></i>
        </span>
    </div>
</div>

    <div class="flex items-center gap-3 pt-2">
        <button type="button" onclick="closeModal()"
            class="flex-1 py-2.5 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
        <button type="submit"
            class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-[13px] font-semibold hover:bg-blue-700 transition-colors">Simpan
            Akun</button>
    </div>
</form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div id="modalEditUser" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Edit User</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <!-- Form -->
            <form action="proses_edit_user.php" method="POST" class="px-6 py-5 space-y-4">
                <input type="hidden" name="id_user" id="editUserId">

                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="nama" id="editNama" placeholder="Nama lengkap" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" id="editEmail" placeholder="contoh@polije.ac.id" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Role</label>
                        <select name="role" id="editRole" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                            <option value="">Pilih role...</option>
                            <option value="Mahasiswa">Mahasiswa</option>
                            <option value="Dosen Pembimbing">Dosen Pembimbing</option>
                            <option value="Pembimbing Lapang">Pembimbing Lapang</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">NIM / NIP</label>
                        <input type="text" name="nomor_induk" id="editNomorInduk" placeholder="NIM / NIP"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Ganti Password</label>
                    <input type="password" name="password" id="editPassword"
                        placeholder="Kosongkan jika tidak ingin mengubah"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-[13px] outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all bg-gray-50">
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()"
                        class="px-5 py-2.5 border border-gray-200 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-[13px] font-semibold hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentFilter = 'semua';

        function openModal() {
            document.getElementById('modalBuatAkun').classList.remove('hidden');
            document.getElementById('modalBuatAkun').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('modalBuatAkun').classList.add('hidden');
            document.getElementById('modalBuatAkun').classList.remove('flex');
        }

        function setFilter(filter) {
            currentFilter = filter;
            const btns = { 'semua': 'filterAll', 'mahasiswa': 'filterMhs', 'dosen': 'filterDosen','pembimbing':'filterpembimbing' };
            Object.values(btns).forEach(id => {
                document.getElementById(id).className = 'filter-btn px-4 py-2.5 rounded-xl text-[13px] font-medium text-gray-600 hover:bg-gray-100 transition-all';
            });
            document.getElementById(btns[filter]).className = 'filter-btn px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-blue-600 text-white transition-all';
            filterTable();
        }

        function filterTable() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.user-row');
            rows.forEach(row => {
                const roleMatch = currentFilter === 'semua' || row.dataset.role === currentFilter;
                const searchMatch = row.dataset.search.includes(search);
                row.style.display = (roleMatch && searchMatch) ? '' : 'none';
            });
        }

        // ===== Edit User Modal =====
        function openEditModal(nama, email, role, nomorInduk) {
            document.getElementById('editNama').value = nama;
            document.getElementById('editEmail').value = email;
            document.getElementById('editNomorInduk').value = nomorInduk;
            document.getElementById('editPassword').value = '';

            // Set selected role
            const selectRole = document.getElementById('editRole');
            for (let i = 0; i < selectRole.options.length; i++) {
                if (selectRole.options[i].value === role) {
                    selectRole.selectedIndex = i;
                    break;
                }
            }

            const modal = document.getElementById('modalEditUser');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditModal() {
            const modal = document.getElementById('modalEditUser');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Toggle password visibility
        document.getElementById('togglePasswordAkun').addEventListener('click', function () {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIconAkun');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });

        // Close modal on backdrop click
        document.getElementById('modalBuatAkun').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        document.getElementById('modalEditUser').addEventListener('click', function (e) {
            if (e.target === this) closeEditModal();
        });
    </script>
</body>

</html>