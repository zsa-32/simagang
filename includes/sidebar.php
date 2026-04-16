<?php
// Default role jika belum diset
$role = $role ?? 'mahasiswa';
// Default active page jika belum diset
$activePage = $activePage ?? '';

// Definisi menu untuk berbagari role
$menus = [
    'mahasiswa' => [
        ['title' => 'Dashboard',     'icon' => 'fa-chart-line',    'url' => 'dashboard.php', 'id' => 'dashboard'],
        ['title' => 'Jurnal',         'icon' => 'fa-book',          'url' => 'jurnal.php',    'id' => 'jurnal'],
        ['title' => 'Absensi',        'icon' => 'fa-calendar-check','url' => 'absen.php',     'id' => 'absen'],
        ['title' => 'Laporan Akhir',  'icon' => 'fa-file-alt',      'url' => 'laporan.php',   'id' => 'laporan'],
        ['title' => 'Lihat Nilai',    'icon' => 'fa-star',          'url' => 'nilai.php',     'id' => 'nilai'],
    ],
    'admin' => [
        ['title' => 'Dashboard',             'icon' => 'fa-th-large',      'url' => 'dashboard.php',            'id' => 'dashboard',            'group' => 'MENU UTAMA'],
        ['title' => 'Manajemen User',         'icon' => 'fa-users',         'url' => 'manajemen_user.php',       'id' => 'manajemen_user',       'group' => 'MENU UTAMA'],
        ['title' => 'Monitoring Mahasiswa',   'icon' => 'fa-user-graduate', 'url' => 'monitoring_mahasiswa.php', 'id' => 'monitoring_mahasiswa', 'group' => 'MONITORING'],
        ['title' => 'Monitoring Dosen',       'icon' => 'fa-chalkboard-teacher','url' => 'monitoring_dosen.php','id' => 'monitoring_dosen',     'group' => 'MONITORING'],
        ['title' => 'Pengaturan Sistem',      'icon' => 'fa-cog',           'url' => 'pengaturan.php',           'id' => 'pengaturan',           'group' => 'PENGATURAN'],
    ],
    'dosen' => [
        ['title' => 'Dashboard',           'icon' => 'fa-th-large',      'url' => 'dashboard.php',    'id' => 'dashboard'],
        ['title' => 'Mahasiswa Bimbingan', 'icon' => 'fa-user-friends',  'url' => 'mhs_bimbingan.php','id' => 'bimbingan'],
        ['title' => 'Lihat Jurnal',        'icon' => 'fa-file-alt',      'url' => 'jurnal.php',       'id' => 'lihat_jurnal'],
        ['title' => 'Presensi',            'icon' => 'fa-user-check',    'url' => 'presensi_mhs.php', 'id' => 'presensi'],
        ['title' => 'Penilaian',           'icon' => 'fa-clipboard-list','url' => 'penilaian.php',    'id' => 'penilaian'],
    ]
];

$currentMenu = $menus[$role] ?? [];
?>

<!-- Sidebar -->
<aside class="w-64 bg-white border-r border-gray-200 flex flex-col hidden md:flex shrink-0">
    <!-- Logo Area -->
    <div class="h-[72px] flex items-center px-6 border-b border-gray-200 shrink-0">
        <div class="flex items-center gap-3">
            <div class="bg-blue-600 text-white p-2 rounded-lg flex items-center justify-center">
                <i class="fas fa-graduation-cap text-xl"></i>
            </div>
            <div class="flex flex-col">
                <h1 class="font-bold text-[17px] leading-tight text-gray-900 tracking-tight">Magang TIF</h1>
                <p class="text-[10px] text-gray-500 leading-tight">Internship Management System</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 overflow-y-auto py-5 px-4 h-full">
        <?php if ($role === 'admin'): ?>
            <?php
                $groups = [];
                foreach ($currentMenu as $menu) {
                    $g = $menu['group'] ?? 'MENU UTAMA';
                    $groups[$g][] = $menu;
                }
            ?>
            <?php foreach ($groups as $groupName => $groupMenus): ?>
                <p class="text-[11px] font-semibold text-gray-400 mb-2 px-2 uppercase tracking-wider <?= $groupName !== array_key_first($groups) ? 'mt-5' : '' ?>"><?= $groupName ?></p>
                <nav class="space-y-1">
                    <?php foreach ($groupMenus as $menu): ?>
                        <?php $isActive = ($menu['id'] === $activePage); ?>
                        <a href="<?= $menu['url'] ?>" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-[10px] group transition-colors <?= $isActive ? 'bg-[#3b82f6] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' ?>">
                            <i class="fas <?= $menu['icon'] ?> w-5 text-center text-[15px] <?= $isActive ? '' : 'text-gray-500 group-hover:text-gray-700 transition-colors' ?>"></i>
                            <span class="font-medium text-[14px] <?= $isActive ? 'drop-shadow-sm' : '' ?>"><?= $menu['title'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-[11px] font-semibold text-gray-400 mb-3 px-2 uppercase tracking-wider">Menu Utama</p>
            <nav class="space-y-1">
                <?php foreach ($currentMenu as $menu): ?>
                    <?php $isActive = ($menu['id'] === $activePage); ?>
                    <a href="<?= $menu['url'] ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-[10px] group transition-colors <?= $isActive ? 'bg-[#3b82f6] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i class="fas <?= $menu['icon'] ?> w-5 text-center text-[15px] <?= $isActive ? '' : 'text-gray-500 group-hover:text-gray-700 transition-colors' ?>"></i>
                        <span class="font-medium text-[14px] <?= $isActive ? 'drop-shadow-sm' : '' ?>"><?= $menu['title'] ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </div>
</aside>
