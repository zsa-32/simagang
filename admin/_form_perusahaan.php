<?php // Shared form fields for perusahaan (tambah & edit) ?>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Perusahaan <span class="text-red-500">*</span></label>
        <input type="text" name="nama_perusahaan" required placeholder="PT Contoh Indonesia"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email Bisnis <span class="text-red-500">*</span></label>
        <input type="email" name="email_business" required placeholder="hr@perusahaan.co.id"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Contact Person</label>
        <input type="text" name="contact_person" placeholder="Nama PIC"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">No. HP</label>
        <input type="text" name="no_hp" placeholder="08xxxxxxxxxx"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Bidang Usaha</label>
        <input type="text" name="bidang_usaha" placeholder="Teknologi Informasi"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status Permodalan</label>
        <select name="status_permodalan" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">-- Pilih --</option>
            <option value="BUMN">BUMN</option>
            <option value="Swasta Nasional">Swasta Nasional</option>
            <option value="Swasta Asing">Swasta Asing</option>
            <option value="Startup">Startup</option>
            <option value="UKM">UKM</option>
        </select>
    </div>
    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alamat</label>
        <textarea name="alamat_perusahaan" rows="2" placeholder="Alamat lengkap perusahaan"
                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Latitude</label>
        <input type="number" step="any" name="latitude" placeholder="-7.9752"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Longitude</label>
        <input type="number" step="any" name="longitude" placeholder="113.3669"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Radius Absensi (meter)</label>
        <input type="number" name="radius" value="200" min="50" max="1000"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</div>
