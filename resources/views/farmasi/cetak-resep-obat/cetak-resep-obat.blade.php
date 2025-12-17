<x-mycomponents.layout>
    <div class="p-4">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h1 class="text-xl md:text-2xl font-extrabold text-blue-600">
                    Cetak Resep Obat
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Isi data resep, pilih pasien & dokter, lalu tambahkan obat.
                </p>
            </div>

            {{-- Tombol aksi kanan atas (opsional) --}}
            <div class="flex gap-2">
                <button type="button" id="btnResetHeader"
                    class="px-3 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-semibold">
                    Reset
                </button>

                <button type="button" id="btnPreviewHeader"
                    class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                    Preview / Cetak
                </button>
            </div>
        </div>

        {{-- Card Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="p-4 md:p-6">
                <form id="formCetakResep" method="GET" action="#">
                    @csrf

                    <input type="hidden" name="nama_pasien" id="nama_pasien">
                    <input type="hidden" name="nama_dokter" id="nama_dokter">
                    <input type="hidden" name="nama_poli" id="nama_poli">

                    {{-- GRID ATAS (kiri: tanggal/dokter/tipe, kanan: pasien + umur + bb + alamat) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        {{-- KIRI --}}
                        <div class="space-y-5">
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Tanggal Resep</label>
                                <input type="date" name="tanggal_resep"
                                    class="mt-1 w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                    value="{{ date('Y-m-d') }}">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-600">Pilih Dokter</label>
                                <select name="dokter_id" id="selectDokter"
                                    class="mt-1 w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Pilih Dokter --</option>
                                    {{-- isi dari backend --}}
                                </select>
                                <p class="text-[11px] text-gray-400 mt-1">* Bisa kamu upgrade ke TomSelect.</p>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-600">Tipe Resep</label>
                                <select name="tipe_resep" id="tipeResep"
                                    class="mt-1 w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="resep_dokter">Resep Dokter</option>
                                    <option value="resep_bebas">Resep Bebas</option>
                                </select>
                            </div>
                        </div>

                        {{-- KANAN --}}
                        <div class="space-y-5">
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Cari Pasien</label>

                                <select name="pasien_id" id="selectPasien"
                                    class="mt-1 w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Ketik nama / No RM / NIK...</option>
                                </select>

                                <p class="text-[11px] text-gray-400 mt-1">
                                    * Ketik minimal 2 huruf untuk mencari pasien.
                                </p>
                            </div>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-gray-600">Umur</label>
                                    <input type="text" name="umur" id="umur"
                                        class="mt-1 w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="contoh: 25 th">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600">Berat Badan</label>
                                    <div class="mt-1 flex">
                                        <input type="number" step="0.1" min="0" name="berat_badan"
                                            id="beratBadan"
                                            class="w-full rounded-l-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="0">
                                        <span
                                            class="px-3 inline-flex items-center rounded-r-lg border border-l-0 border-gray-200 bg-gray-50 text-gray-500 text-sm">
                                            Kg
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-600">Alamat</label>
                                <input type="text" name="alamat" id="alamat"
                                    class="mt-1 w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Alamat pasien...">
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="my-6 border-t border-gray-100"></div>

                    {{-- SECTION OBAT --}}
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div>
                            <h2 class="font-bold text-gray-800">Obat</h2>
                            <p class="text-xs text-gray-500">Tambahkan item obat sesuai resep.</p>
                        </div>

                        <button type="button" id="btnTambahObat"
                            class="px-3 py-2 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">
                            <i class="fa-solid fa-plus mr-1"></i> Tambah Obat
                        </button>
                    </div>

                    {{-- Header baris obat (desktop) --}}
                    <div
                        class="hidden md:grid grid-cols-12 gap-3 px-3 py-2 rounded-xl bg-gray-50 border border-gray-100 text-xs font-semibold text-gray-600">
                        <div class="col-span-4">Nama Obat</div>
                        <div class="col-span-1 text-center">Jumlah</div>
                        <div class="col-span-2">Signatura</div>
                        <div class="col-span-2">Detur</div>
                        <div class="col-span-2">Obat Iter</div>
                        <div class="col-span-1 text-right">Aksi</div>
                    </div>

                    {{-- Container baris --}}
                    <div id="obatContainer" class="mt-3 space-y-3">
                        {{-- default 1 baris --}}
                    </div>

                    {{-- Info bawah --}}
                    <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="text-xs text-gray-500">
                            * Tip: Gunakan TomSelect untuk dropdown obat & dokter biar pencarian cepat.
                        </div>

                        <div class="flex gap-2">
                            <button type="button" id="btnResetForm"
                                class="px-4 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-semibold">
                                Reset Form
                            </button>

                            <button type="button" id="btnSubmitPrint"
                                class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                Simpan / Cetak
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Template baris obat --}}
    <template id="tplObatRow">
        <div class="obat-row p-3 rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">

                {{-- Nama Obat --}}
                <div class="md:col-span-4">
                    <label class="md:hidden text-xs font-semibold text-gray-600">Nama Obat</label>

                    <select name="obat[obat_id][]" class="selectObat w-full"
                        data-url="{{ route('cetak.resep.obat.search.data.obat') }}">
                        <option value="">Ketik nama / kode obat...</option>
                    </select>

                    {{-- untuk keperluan print (nama obat yang dipilih) --}}
                    <input type="hidden" name="obat[nama][]" class="obat-nama">

                    {{-- opsional: simpan satuan --}}
                    <input type="hidden" name="obat[satuan][]" class="obat-satuan">
                </div>

                {{-- Jumlah --}}
                <div class="md:col-span-1">
                    <label class="md:hidden text-xs font-semibold text-gray-600">Jumlah</label>
                    <input type="number" min="0" name="obat[jumlah][]"
                        class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-center"
                        value="0">
                </div>

                {{-- Signatura --}}
                <div class="md:col-span-2">
                    <label class="md:hidden text-xs font-semibold text-gray-600">Signatura</label>
                    <input type="text" name="obat[signatura][]" placeholder="mis: 3x1"
                        class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- Detur --}}
                <div class="md:col-span-2">
                    <label class="md:hidden text-xs font-semibold text-gray-600">Detur</label>
                    <input type="text" name="obat[detur][]" placeholder="mis: caps"
                        class="w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- Obat Iter + Iter count --}}
                <div class="md:col-span-2">
                    <label class="md:hidden text-xs font-semibold text-gray-600">Obat Iter</label>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox"
                                class="chk-iter rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                name="obat[is_iter][]" value="1">
                            <span class="text-xs font-semibold">Obat Iter</span>
                        </label>

                        <div class="flex-1">
                            <input type="number" min="0" name="obat[iter_jumlah][]"
                                class="inp-iter w-full rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-center"
                                placeholder="Iter" value="0" disabled>
                        </div>
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="md:col-span-1 flex md:justify-end">
                    <button type="button"
                        class="btn-hapus-obat w-full md:w-10 h-10 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 flex items-center justify-center"
                        title="Hapus baris">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>

            </div>
        </div>
    </template>

</x-mycomponents.layout>

@vite(['resources/js/farmasi/cetak-resep-obat/data-cetak-resep-obat.js'])
