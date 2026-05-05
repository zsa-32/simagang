<?php // Form fields partial for group modal ?>
<div>
    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Kelompok <span class="text-red-500">*</span></label>
    <input type="text" name="name" required placeholder="Kelompok Magang A"
           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>
<div>
    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Perusahaan</label>
    <select name="company_id" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        <option value="">-- Pilih Perusahaan --</option>
        <?php foreach ($companies as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama_perusahaan']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div>
    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dosen Pembimbing</label>
    <select name="dosen_pembimbing_id" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        <option value="">-- Pilih Dosen --</option>
        <?php foreach ($dosens as $d): ?>
        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nama']) ?> (<?= $d['nip'] ?>)</option>
        <?php endforeach; ?>
    </select>
</div>
<div>
    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Pembimbing Lapang</label>
    <select name="pembimbing_lapang_id" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        <option value="">-- Pilih Pembimbing Lapang --</option>
        <?php foreach ($pembimbings as $p): ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?> - <?= htmlspecialchars($p['nama_perusahaan'] ?? '') ?></option>
        <?php endforeach; ?>
    </select>
</div>
