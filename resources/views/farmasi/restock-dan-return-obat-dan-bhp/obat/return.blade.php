{{-- Nama Obat --}}
<div>
  <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
    Nama Obat Return <span class="text-red-500">*</span>
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
  <input
    id="kategori_obat_id"
    class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
    disabled
  />
</div>

{{-- Satuan --}}
<div class="md:col-span-2">
  <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
    Satuan <span class="text-red-500">*</span>
  </label>
  <input
    id="satuan_obat_id"
    class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
    disabled
  />
</div>

{{-- Expired Date --}}
<div>
  <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
    Expired Date <span class="text-red-500">*</span>
  </label>
  <input
    type="date"
    name="expired_date_obat"
    id="expired_date_obat"
    required
    class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
  />
</div>

{{-- Batch --}}
<div>
  <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
    Batch <span class="text-red-500">*</span>
  </label>
  <input
    name="batch_obat"
    id="batch_obat"
    required
    class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
  />
</div>

{{-- Total Stok Item --}}
<div class="md:col-span-2">
  <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
    Total Stok Item
  </label>
  <input
    type="text"
    id="total_stok_item"
    readonly
    class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
    value="0"
  />
</div>

{{-- Jumlah Obat --}}
<div class="md:col-span-2">
  <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
    Jumlah Obat <span class="text-red-500">*</span>
  </label>
  <input
    type="number"
    min="1"
    name="jumlah_obat"
    id="jumlah_obat"
    required
    class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
  />
</div>

{{-- Harga Beli Satuan Lama --}}
<div>
  <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
    Harga Beli Satuan Obat Lama
  </label>
  <input
    type="text"
    id="harga_beli_satuan_obat_lama"
    readonly
    class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
  />
</div>

{{-- Harga Satuan Obat --}}
<div>
  <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
    Harga Satuan Obat <span class="text-red-500">*</span>
  </label>
  <input
    type="text"
    name="harga_satuan_obat"
    id="harga_satuan_obat"
    required
    class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
  />
</div>

{{-- Harga Jual Satuan Lama --}}
<div>
  <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
    Harga Jual Satuan Obat Lama
  </label>
  <input
    type="text"
    id="harga_jual_lama_obat"
    readonly
    class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
  />
</div>

{{-- Harga Jual OTC Lama --}}
<div>
  <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
    Harga Jual OTC Lama
  </label>
  <input
    type="text"
    id="harga_jual_otc_lama_obat"
    readonly
    class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
  />
</div>

{{-- Harga Total Awal --}}
<div class="md:col-span-2">
  <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
    Harga Total Awal <span class="text-red-500">*</span>
  </label>
  <input
    type="text"
    name="harga_total_awal_obat"
    id="harga_total_awal_obat"
    readonly
    class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
    value="Rp. 0"
  />
</div>


{{-- Depot Tujuan --}}
<div class="md:col-span-2">
    <label
    class="block text-xs font-medium text-gray-600 dark:text-gray-300"
    >
    Depot Tujuan
    </label>
    <select
    name="depot_id"
    id="depot_id"
    class="block w-full text-sm
        bg-gray-100 dark:bg-gray-800
        border border-gray-300 dark:border-gray-600
        rounded-md
        px-3 py-2
        focus:outline-none
        focus:ring-1 focus:ring-blue-500
        focus:border-blue-500
        text-gray-800 dark:text-white"
    ></select>

    <div
    id="info-stok-depot"
    class="mt-2 text-[11px] text-blue-600 font-medium hidden"
    >
    Stok di depot ini: <span id="nilai-stok">0</span>
    </div>

    <div
    class="text-red-600 text-[11px] mt-1"
    data-error="depot_id"
    ></div>
</div>


{{-- Keterangan --}}
<div class="md:col-span-2">
  <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
    Keterangan
  </label>
  <textarea
    name="keterangan_return"
    id="keterangan_return"
    rows="3"
    class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
    placeholder="Masukkan alasan return obat..."
  ></textarea>
</div>
