 {{-- BHP --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Bahan Habis Pakai Restock <span class="text-red-500">*</span>
     </label>
     <select name="bhp_id" id="bhp_id" required
         class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
         <option value="">Pilih BHP...</option>
     </select>
 </div>

 {{-- Kategori --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Kategori Bahan
     </label>
     <input id="kategori_bhp_id" disabled
         class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
 </div>

 {{-- Expired Date --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Expired Date
     </label>
     <input type="date" name="expired_date_bhp"
         class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
 </div>

 {{-- Batch --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Batch
     </label>
     <input name="batch_bhp"
         class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
 </div>

 {{-- Jumlah --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Jumlah Bahan <span class="text-red-500">*</span>
     </label>
     <input type="number" min="0" name="jumlah_bhp" value="0"
         class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
 </div>

 {{-- Satuan --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Satuan
     </label>
     <input id="satuan_bhp_id" disabled
         class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
 </div>

 {{-- Harga Beli Lama --}}
 <div>
     <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
         Harga Beli Satuan BHP Lama
     </label>
     <input type="text" id="harga_beli_satuan_bhp_lama" readonly
         class="input-rupiah mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
 </div>

 {{-- Harga Jual Lama --}}
 <div>
     <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
         Harga Jual Satuan BHP Lama
     </label>
     <input type="text" id="harga_jual_satuan_bhp_lama" readonly
         class="input-rupiah mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
 </div>

 {{-- Harga Beli Baru --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Harga Beli Satuan BHP Baru <span class="text-red-500">*</span>
     </label>
     <input type="text" name="harga_beli_satuan_bhp_baru"
         class="input-rupiah mt-1 block w-full text-sm bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
 </div>

 {{-- Harga Jual Baru --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Harga Jual Satuan BHP Baru <span class="text-red-500">*</span>
     </label>
     <input type="text" name="harga_jual_satuan_bhp_baru"
         class="input-rupiah mt-1 block w-full text-sm bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
 </div>

 {{-- Harga Rata-rata Lama --}}
 <div>
     <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
         Harga Beli Rata-rata Lama
     </label>
     <input type="text" id="harga_beli_rata_lama_bhp" readonly
         class="input-rupiah mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
 </div>

 {{-- Harga Rata-rata Baru --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Harga Beli Rata-rata Baru <span class="text-red-500">*</span>
     </label>
     <input type="text" name="harga_beli_rata_baru_bhp"
         class="input-rupiah mt-1 block w-full text-sm bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
 </div>

 {{-- Harga Total Awal --}}
 <div class="md:col-span-2">
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Harga Total Awal
     </label>
     <input type="text" name="harga_total_awal_bhp" readonly value="Rp. 0"
         class="input-rupiah mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
 </div>

 {{-- Diskon --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Diskon BHP (%)
     </label>
     <input type="number" min="0" max="100" value="0" name="diskon_bhp_persen"
         class="mt-1 block w-full text-sm bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
 </div>

 {{-- Depot Tujuan --}}
 <div>
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Depot Tujuan
     </label>
     <select name="depot_id"
         class="mt-1 block w-full text-sm bg-gray-100 border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
         <option value="">Pilih Depot...</option>
     </select>
 </div>

 {{-- Keterangan --}}
 <div class="md:col-span-2">
     <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
         Keterangan
     </label>
     <textarea name="keterangan_bhp" rows="3"
         class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
         placeholder="Opsional"></textarea>
 </div>
