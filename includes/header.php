<?php
// Ambil nama user dari session (session_start() sudah dipanggil di masing-masing halaman)
$userName   = $userName   ?? ($_SESSION['nama']     ?? ($_SESSION['username'] ?? 'Pengguna'));
$userRole   = $userRole   ?? ($_SESSION['role']     ?? 'mahasiswa');
$userAvatar = $userAvatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=4f86f7&color=fff';
?>

<!-- Header -->
<header class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 md:px-8 shrink-0">
    <!-- Mobile: Hamburger Button -->
    <button class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors" onclick="toggleMobileSidebar()">
        <i class="fas fa-bars text-[18px]"></i>
    </button>

    <!-- Spacer for desktop (pushes user info to the right) -->
    <div class="hidden md:flex flex-1"></div>

    <!-- User Info -->
    <div class="relative" id="userDropdownWrapper">
        <div class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 py-2 px-3 rounded-lg transition-colors select-none"
             id="userDropdownTrigger"
             onclick="toggleUserDropdown()">
            <span class="text-[14px] font-medium text-gray-700"><?= htmlspecialchars($userName) ?></span>
            <img src="<?= htmlspecialchars($userAvatar) ?>"
                 alt="User Avatar"
                 class="w-9 h-9 rounded-full border-2 border-white shadow-sm">
            <i class="fas fa-chevron-down text-[11px] text-gray-500 ml-1 transition-transform duration-200" id="chevronIcon"></i>
        </div>

        <!-- Dropdown Menu -->
        <div id="userDropdown"
             class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-[13px] font-semibold text-gray-800"><?= htmlspecialchars($userName) ?></p>
                <p class="text-[12px] text-gray-400 mt-0.5">Mahasiswa Magang</p>
            </div>
            <a href="profil.php" class="flex items-center gap-3 px-4 py-2.5 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-800 transition-colors">
                <i class="fas fa-user w-4 text-center text-gray-400"></i> Profil Saya
            </a>

            <div class="border-t border-gray-100 mt-1 pt-1">
                <a href="../logout.php"
               class="flex items-center gap-3 px-4 py-2.5 text-[13px] text-red-500 hover:bg-red-50 transition-colors">
                <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
            </a>
            </div>
        </div>
    </div>
</header>

<script>
    // Toggle User Dropdown
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        const chevron  = document.getElementById('chevronIcon');
        dropdown.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const wrapper  = document.getElementById('userDropdownWrapper');
        const dropdown = document.getElementById('userDropdown');
        if (wrapper && !wrapper.contains(e.target)) {
            dropdown.classList.add('hidden');
            document.getElementById('chevronIcon').classList.remove('rotate-180');
        }
    });

    // Mobile sidebar toggle (optional)
    function toggleMobileSidebar() {
        const sidebar = document.querySelector('aside');
        if (sidebar) {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('flex');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('inset-y-0');
            sidebar.classList.toggle('left-0');
            sidebar.classList.toggle('z-50');
        }
    }
</script>
