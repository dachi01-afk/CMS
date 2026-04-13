<section class="space-y-5">

    {{-- ============== HEADER ============== --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3 md:flex-1">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shrink-0">
                <i class="fa-solid fa-rotate-left text-lg"></i>
            </div>

            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Monitoring Return Bahan Habis Pakai
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Pantau data return bahan habis pakai dari supplier secara teratur.
                    <span class="hidden sm:inline">
                        Gunakan pencarian untuk menemukan data return lebih cepat.
                    </span>
                </p>
            </div>
        </div>

        <div class="flex justify-center md:justify-end">
            <button id="button-open-modal-create-return-bhp" type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5
                text-sm font-semibold text-white rounded-xl shadow-md
                bg-gradient-to-r from-amber-500 to-orange-600
                hover:from-amber-600 hover:to-orange-700
                focus:outline-none focus:ring-2 focus:ring-amber-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Return BHP</span>
            </button>
        </div>
    </div>

    {{-- ============== CARD TABEL ============== --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        <div class="px-3 sm:px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">

                <div class="flex items-center gap-2 text-sm w-full md:w-auto">
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>

                    <select id="return-bhp-page-length"
                        class="w-full sm:w-40 md:w-32 border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                               focus:ring-amber-500 focus:border-amber-500
                               bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                               px-2 py-2">
                        <option value="10">10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>

                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">/ halaman</span>
                </div>

                <div class="w-full md:w-auto">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                        </span>

                        <input type="text" id="return-bhp-search-input"
                            class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                                   text-slate-800 dark:text-slate-100
                                   border border-slate-300 dark:border-slate-600 rounded-lg
                                   bg-slate-50 dark:bg-slate-700
                                   focus:ring-amber-500 focus:border-amber-500"
                            placeholder="Cari supplier, depot, kode return...">
                    </div>

                    <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block">
                        Contoh: <span class="italic">Nama supplier, nama depot, kode return</span>.
                    </p>
                </div>

            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-return-bhp"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100
                       border-t border-slate-100 dark:border-slate-700 whitespace-nowrap">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase
                           bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">No</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Supplier</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Depot</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Kode Return</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Return</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Status</th>
                        <th
                            class="px-3 sm:px-4 md:px-6 py-3 text-center whitespace-nowrap
                                   sticky right-0 z-10
                                   bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-3 sm:px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">

            <div id="return-bhp-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            <div class="w-full md:w-auto overflow-x-auto">
                <ul id="return-bhp-custom-pagination"
                    class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg
                           border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>

</section>

<div id="modal-create-return-bhp"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-3 sm:p-4 md:p-6">

    <div class="w-full max-w-6xl max-h-[94vh] overflow-hidden rounded-[28px] bg-white shadow-2xl">
        <div
            class="relative overflow-hidden bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 px-5 sm:px-6 md:px-8 py-5 md:py-6 text-white">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-14 left-10 h-32 w-32 rounded-full bg-white/10"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Create Return Bahan Habis Pakai</h2>
                    <p class="mt-1 text-sm text-emerald-50/90">
                        Buat transaksi return bahan habis pakai ke supplier dan otomatis siapkan data piutang supplier.
                    </p>
                </div>

                <button type="button" id="btnCloseModalCreateReturnBhp"
                    class="inline-flex h-10 w-10 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-white transition hover:bg-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <form id="formCreateReturnBhp" class="overflow-y-auto max-h-[calc(94vh-92px)]">
            @csrf

            <div class="space-y-6 md:space-y-7 px-4 sm:px-5 md:px-6 lg:px-8 py-5 md:py-6">

                <section class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4 sm:p-5 md:p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7.5A2.25 2.25 0 0 1 5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v9A2.25 2.25 0 0 1 18.75 18.75H5.25A2.25 2.25 0 0 1 3 16.5v-9Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-slate-800">Informasi Return</h3>
                            <p class="text-sm text-slate-500">Lengkapi informasi utama transaksi return bahan habis
                                pakai.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Kode Return</label>
                            <input type="text" name="kode_return" id="kode_return" value="{{ $kodeReturn ?? '' }}"
                                readonly
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm outline-none">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Tanggal Return <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_return" id="tanggal_return"
                                value="{{ now()->format('Y-m-d') }}"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Supplier <span class="text-rose-500">*</span>
                            </label>
                            <select name="supplier_id" id="supplier_id"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                                <option value="">Pilih Supplier</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Depot <span class="text-rose-500">*</span>
                            </label>
                            <select name="depot_id" id="depot_id"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                                <option value="">Pilih Depot</option>
                                @foreach ($dataDepot as $depot)
                                    <option value="{{ $depot->id }}">{{ $depot->nama_depot }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 md:mt-5">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="3"
                            placeholder="Contoh: return karena mendekati kadaluarsa / kemasan rusak / salah kirim"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"></textarea>
                    </div>
                </section>

                <section class="rounded-[24px] border border-slate-200 bg-white p-4 sm:p-5 md:p-6 shadow-sm">
                    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-600 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20.25 8.511c.884.284 1.5 1.11 1.5 2.039v7.5a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18.05v-7.5c0-.93.616-1.755 1.5-2.04" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-slate-800">Detail Item Return</h3>
                                <p class="text-sm text-slate-500">
                                    Pilih bahan habis pakai, lalu batch akan muncul sesuai item yang dipilih.
                                </p>
                            </div>
                        </div>

                        <button type="button" id="btnAddRowReturnBhp"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 sm:px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:scale-[1.01] hover:shadow-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Item
                        </button>
                    </div>

                    <div id="tableReturnBhpDetailBody" class="space-y-4"></div>

                    <div
                        class="mt-5 flex flex-col gap-3 rounded-3xl bg-slate-50 p-4 sm:p-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Piutang Return</p>
                            <h4 id="grandTotalReturnBhpText"
                                class="mt-1 text-xl sm:text-2xl font-bold tracking-tight text-slate-800">
                                Rp 0
                            </h4>
                        </div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            Nilai total akan otomatis digunakan untuk pembuatan data piutang bahan habis pakai.
                        </div>
                    </div>
                </section>
            </div>

            <div class="border-t border-slate-200 bg-white px-4 sm:px-5 md:px-8 py-4 sm:py-5">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end mb-4">
                    <button type="button" id="btnCancelModalCreateReturnBhp"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>

                    <button type="submit" id="btnSubmitCreateReturnBhp"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:scale-[1.01]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Simpan Return BHP
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@vite(['resources/js/farmasi/return-bahan-habis-pakai/data-return-bahan-habis-pakai.js'])
