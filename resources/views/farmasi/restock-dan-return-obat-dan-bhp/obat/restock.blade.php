{{-- Nama Obat --}}
<div class="md:col-span-2">
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Nama Obat Restock <span class="text-red-500">*</span>
    </label>
    <select name="obat_id" id="obat_id" required class="mt-1">
        <option value="">Pilih obat...</option>
    </select>
    <div class="text-red-600 text-[11px] mt-1" data-error="obat_id"></div>
</div>

{{-- Kategori Obat --}}
<div>
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Kategori Obat
    </label>
    <input id="kategori_obat_id"
        class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        disabled />
</div>

{{-- Satuan --}}
<div>
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Satuan <span class="text-red-500">*</span>
    </label>
    <input id="satuan_obat_id"
        class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        disabled />
    <div class="text-red-600 text-[11px] mt-1" data-error="satuan_obat_id"></div>
</div>


{{-- Jumlah Obat --}}
<div>
    <label for="jumlah_obat" id="label_jumlah_obat" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Jumlah Obat <span class="text-red-500">*</span>
    </label>
    <input type="number" min="0" name="jumlah_obat" id="jumlah_obat" value="0"
        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        required />
    <div class="text-red-600 text-[11px] mt-1" data-error="jumlah_obat"></div>
</div>

{{-- Expired Date --}}
<div>
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Expired Date
    </label>
    <input type="date" name="expired_date_obat" id="expired_date_obat"
        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
</div>

{{-- Batch --}}
<div class="md:col-span-2 mb-8">
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Batch
    </label>
    <input name="batch_obat"
        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
</div>

{{-- Harga Beli Satuan Lama --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Beli Satuan Obat Lama
    </label>
    <input type="text" id="harga_beli_satuan_obat_lama" readonly
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
</div>

{{-- Harga Beli Satuan Baru --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Beli Satuan Obat Baru
        <span class="text-red-500">*</span>
    </label>
    <input type="text" name="harga_satuan_obat_baru" id="harga_satuan_obat_baru"
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        required />
    <div class="text-red-600 text-[11px] mt-1" data-error="harga_satuan_obat_baru"></div>
</div>

{{-- Harga Beli Rata-Rata Lama --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Beli Rata-Rata Lama
    </label>
    <input type="text" id="harga_beli_rata_lama_obat" readonly
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
</div>

{{-- Harga Beli Rata-Rata Baru --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Beli Rata-Rata Baru
        <span class="text-red-500">*</span>
    </label>
    <input type="text" name="harga_beli_rata_baru_obat" id="harga_beli_rata_baru_obat"
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        required />
    <div class="text-red-600 text-[11px] mt-1" data-error="harga_beli_rata_baru_obat"></div>
</div>

{{-- Harga Jual Satuan Lama --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Jual Satuan Obat Lama
    </label>
    <input type="text" id="harga_jual_lama_obat" readonly
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
</div>

{{-- Harga Jual Satuan Baru --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Jual Satuan Obat Baru
        <span class="text-red-500">*</span>
    </label>
    <input type="text" name="harga_jual_baru_obat" id="harga_jual_baru_obat"
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        required />
    <div class="text-red-600 text-[11px] mt-1" data-error="harga_jual_baru_obat"></div>
</div>

{{-- Harga Jual OTC Lama --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Jual OTC Lama
    </label>
    <input type="text" id="harga_jual_otc_lama_obat" readonly
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
</div>

{{-- Harga Jual OTC Baru --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Jual OTC Baru
    </label>
    <input type="text" name="harga_jual_otc_baru_obat" id="harga_jual_otc_baru_obat"
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
    <div class="text-red-600 text-[11px] mt-1" data-error="harga_jual_otc_baru_obat"></div>
</div>

{{-- Harga Total Awal --}}
<div class="md:col-span-2">
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Total Awal <span class="text-red-500">*</span>
    </label>
    <input type="text" name="harga_total_awal_obat" id="harga_total_awal_obat" readonly
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        value="Rp. 0" />
    <div class="text-red-600 text-[11px] mt-1" data-error="harga_total_awal_obat"></div>
</div>

{{-- Diskon Obat (%) --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Diskon Obat <span class="text-red-500">*</span>
    </label>
    <input type="number" min="0" max="100" step="1" name="diskon_obat_persen"
        id="diskon_obat_persen" value="0"
        class="mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
    <div class="text-red-600 text-[11px] mt-1" data-error="diskon_obat_persen"></div>
</div>

<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Total Diskon <span class="text-red-500">*</span>
    </label>
    <input type="text" name="harga_total_diskon_obat_restock" id="harga_total_diskon_obat_restock" readonly
        class="input-rupiah mt-1 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        value="Rp. 0" />
    <div class="text-red-600 text-[11px] mt-1" data-error="harga_total_diskon_obat_restock"></div>
</div>


{{-- Depot Tujuan --}}
<div class="md:col-span-2">
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Depot Tujuan
    </label>
    <select name="depot_id" id="depot_id" class="mt-1"></select>

    <div id="info-stok-depot" class="mt-2 text-[11px] text-blue-600 font-medium hidden">
        Stok di depot ini: <span id="nilai-stok">0</span>
    </div>

    <div class="text-red-600 text-[11px] mt-1" data-error="depot_id"></div>
</div>

{{-- Keterangan (RETURN ONLY) --}}
<div>
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Keterangan Return
    </label>
    <textarea name="keterangan_obat_return" id="keterangan_obat_return" rows="3"
        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        placeholder="Masukkan alasan atau keterangan return..."></textarea>

    <div class="text-red-600 text-[11px] mt-1" data-error="keterangan_obat_return"></div>
</div>
