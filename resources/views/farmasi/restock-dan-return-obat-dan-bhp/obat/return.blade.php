{{-- Nama Obat Return --}}
<div class="md:col-span-2">
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Nama Obat Return <span class="text-red-500">*</span>
    </label>
    <div class="mt-1">
        <select name="obat_id" id="return_obat_id" required placeholder="Cari obat..."></select>
    </div>
    <div class="text-red-600 text-[11px] mt-1" data-error="obat_id"></div>
</div>


{{-- Kategori Obat --}}
<div>
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Kategori Obat</label>
    <input id="return_kategori_obat_id"
        class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:bg-gray-800"
        disabled />
</div>

{{-- Satuan --}}
<div>
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Satuan <span
            class="text-red-500">*</span></label>
    <input id="return_satuan_obat_id"
        class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:bg-gray-800"
        disabled />
</div>

{{-- Baris Expired & Batch (Sekarang pakai Select) --}}
<div class="grid grid-cols-1 md:col-span-2 gap-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
            Pilih Batch & Expired Date <span class="text-red-500">*</span>
        </label>
        <div class="mt-1">
            <select id="return_batch_id" name="batch_obat_id" required placeholder="Pilih batch..."></select>
        </div>
        <div class="text-red-600 text-[11px] mt-1" data-error="batch_obat_id"></div>
    </div>
</div>

{{-- Hidden input untuk simpan nilai text jika diperlukan --}}
<input type="hidden" name="batch_obat" id="return_batch_obat">
<input type="hidden" name="expired_date_obat" id="return_expired_date_obat">  

{{-- Total Stok & Jumlah --}}
<div class="md:col-span-2 grid grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Total Stok Item</label>
        <input type="text" id="return_total_stok_item" readonly
            class="mt-1 block w-full text-sm bg-gray-100 border-gray-200 rounded-lg dark:bg-gray-800" value="0" />
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Jumlah Obat <span
                class="text-red-500">*</span></label>
        <input type="number" min="1" name="jumlah_obat" id="return_jumlah_obat" required
            class="mt-1 block w-full text-sm border-gray-200 rounded-lg dark:bg-gray-900" />
    </div>
</div>

{{-- Harga Beli Satuan Lama --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Beli Satuan Obat Lama
    </label>
    <input type="text" id="return_harga_beli_satuan_obat_lama" readonly
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
</div>

{{-- Harga Satuan Obat --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Satuan Obat <span class="text-red-500">*</span>
    </label>
    <input type="text" name="harga_satuan_obat" id="harga_satuan_obat" required
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
</div>

{{-- Harga Jual Satuan Lama --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Jual Satuan Obat Lama
    </label>
    <input type="text" id="return_harga_jual_lama_obat" readonly
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
</div>

{{-- Harga Jual OTC Lama --}}
<div>
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Jual OTC Lama
    </label>
    <input type="text" id="return_harga_jual_otc_lama_obat" readonly
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
</div>

{{-- Harga Total Awal --}}
<div class="md:col-span-2">
    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
        Harga Total Awal <span class="text-red-500">*</span>
    </label>
    <input type="text" name="harga_total_awal_obat" id="harga_total_awal_obat" readonly
        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        value="Rp. 0" />
</div>


{{-- Depot Tujuan --}}
<div class="md:col-span-2">
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Depot Tujuan
    </label>
    <select name="depot_id" id="depot_id"
        class="block w-full text-sm
        bg-gray-100 dark:bg-gray-800
        border border-gray-300 dark:border-gray-600
        rounded-md
        px-3 py-2
        focus:outline-none
        focus:ring-1 focus:ring-blue-500
        focus:border-blue-500
        text-gray-800 dark:text-white"></select>

    <div id="info-stok-depot" class="mt-2 text-[11px] text-blue-600 font-medium hidden">
        Stok di depot ini: <span id="nilai-stok">0</span>
    </div>

    <div class="text-red-600 text-[11px] mt-1" data-error="depot_id"></div>
</div>


{{-- Keterangan --}}
<div class="md:col-span-2">
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
        Keterangan
    </label>
    <textarea name="keterangan_return" id="keterangan_return" rows="3"
        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        placeholder="Masukkan alasan return obat..."></textarea>
</div>
