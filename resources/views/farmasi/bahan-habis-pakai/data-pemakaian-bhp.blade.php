<div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Catat Pemakaian BHP</h2>
        <p class="text-sm text-slate-500 font-medium">Kurangi stok gudang berdasarkan penggunaan harian atau tindakan
            medis.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <form id="form-pemakaian-bhp" class="space-y-5" data-url="{{ route('store.data.pemakaian.bhp') }}">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Cari Nama Bahan / Alat</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                {{-- <i class="fa-solid fa-box-open"></i> --}}
                            </span>
                            <select id="select-bhp" name="bahan_habis_pakai_id"
                                class="block w-full p-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                <option value="">-- Pilih Barang --</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Jumlah Pakai</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" min="1" placeholder="0" name="jumlah_pemakaian"
                                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <span id="satuan-badge"
                                    class="px-3 py-2.5 bg-slate-100 text-slate-500 text-xs font-bold rounded-xl border border-slate-200 whitespace-nowrap">PCS</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Penggunaan</label>
                            <input type="date" value="{{ date('Y-m-d') }}" name="tanggal_pemakaian"
                                class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Keterangan / Keperluan</label>
                        <textarea rows="3" placeholder="Contoh: Digunakan untuk tindakan jahit luka pasien..." name="keterangan"
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                        <button type="button"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" id="btn-simpan"
                            class="px-8 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-200 transition-all">
                            Simpan Pemakaian
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 shadow-lg text-white">
                <p class="text-blue-100 text-xs font-semibold uppercase tracking-wider mb-1">Stok Tersedia</p>
                <h3 id="info-nama-barang" class="text-lg font-bold mb-4 leading-tight">Pilih barang terlebih dahulu</h3>

                <div class="flex items-baseline space-x-2">
                    <span id="info-stok" class="text-4xl font-extrabold tracking-tighter">0</span>
                    <span id="info-satuan" class="text-sm font-medium opacity-80">Satuan</span>
                </div>

                <div class="mt-6 pt-6 border-t border-white/20">
                    <div class="flex justify-between text-xs mb-2">
                        <span class="opacity-70">Harga Per Satuan:</span>
                        <span id="info-harga" class="font-bold">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="opacity-70">Kode Barang:</span>
                        <span id="info-kode" class="font-bold">-</span>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                <div class="flex space-x-3">
                    <i class="fa-solid fa-circle-info text-amber-500 mt-1"></i>
                    <div>
                        <h4 class="text-sm font-bold text-amber-800">Tips Pengisian</h4>
                        <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                            Pastikan jumlah yang diinput tidak melebihi stok tersedia untuk menghindari selisih data
                            pada laporan bulanan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@vite(['resources/js/farmasi/bahan-habis-pakai/data-pemakaian-bhp.js'])
