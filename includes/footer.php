<?php
// Default footer text
$appName    = $appName    ?? 'Magang TIF';
$appVersion = $appVersion ?? '1.0.0';
$currentYear = date('Y');
?>

<!-- Footer -->
<footer class="shrink-0 bg-white border-t border-gray-100 px-6 md:px-8 py-3">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-[12px] text-gray-400">
        <span>
            &copy; <?= $currentYear ?> <span class="font-semibold text-gray-500"><?= htmlspecialchars($appName) ?></span>. All rights reserved.
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
            Version <?= htmlspecialchars($appVersion) ?>
        </span>
    </div>
</footer>
