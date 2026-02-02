{{-- Header --}}
<div
  class="mb-4 flex flex-col gap-3 md:flex-row md:items-start md:justify-between"
>
  <div>
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
      Restock dan Return
    </h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">
      Kelola transaksi restock dan return obat & BHP
    </p>
  </div>

  <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto md:items-start">
    {{-- Search --}}
    <div class="w-full md:w-[360px]">
      <div class="relative">
        <span
          class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
        >
          <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
        </span>

        <input
          type="text"
          id="customSearch"
          class="block w-full pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-sky-500 focus:border-sky-500"
          placeholder="Cari kode / supplier / nama item..."
        />
      </div>

      <p
        class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block"
      >
        Contoh: <span class="italic">STK-0001, Kimia Farma, Paracetamol</span>.
      </p>
    </div>

    {{-- Button open modal --}}
    <button
      type="button"
      id="btn-open-modal-create"
      class="inline-flex items-center justify-center gap-2 px-4 py-2 h-[42px] bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 shadow-sm whitespace-nowrap"
    >
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Restock & Return Obat / Barang</span>
    </button>
  </div>
</div>

{{-- Card: Toolbar + Table --}}
<div
  class="bg-white dark:bg-slate-900 rounded-2xl shadow border border-slate-100 dark:border-slate-800 overflow-hidden"
>
  {{-- Toolbar --}}
  <div
    class="px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-800"
  >
    <div
      class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
    >
      <div class="flex items-center gap-2 text-sm">
        <span class="text-slate-600 dark:text-slate-300 hidden sm:inline"
          >Tampil</span
        >

        <select
          id="restock_pageLength"
          class="w-36 border border-slate-200 dark:border-slate-700 text-sm rounded-xl focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2"
        >
          <option value="10">10 baris</option>
          <option value="25">25 baris</option>
          <option value="50">50 baris</option>
          <option value="100">100 baris</option>
        </select>

        <span class="text-slate-600 dark:text-slate-300 hidden sm:inline"
          >/ halaman</span
        >
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="overflow-x-auto">
    <table id="table-restock-return" class="w-full text-sm">
      <thead
        class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200"
      >
        <tr>
          <th class="px-4 py-3">Kode</th>
          <th class="px-4 py-3">No Faktur</th>
          <th class="px-4 py-3">Jenis</th>
          <th class="px-4 py-3">Tgl Pengiriman</th>
          <th class="px-4 py-3">Tgl Pembuatan</th>
          <th class="px-4 py-3">Supplier</th>
          <th class="px-4 py-3">Nama Item</th>
          <th class="px-4 py-3">Jumlah</th>
          <th class="px-4 py-3">Diapprove</th>
          <th class="px-4 py-3">Total Harga</th>
          <th class="px-4 py-3">Tempo</th>
          <th class="px-4 py-3 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-slate-900 text-[11px] md:text-xs">
        {{-- server-side DataTables --}}
      </tbody>
    </table>
  </div>
  {{-- Footer --}}
  <div
    class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-3 sm:px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl"
  >
    <div
      id="custom_customInfo"
      class="text-xs md:text-sm text-slate-600 dark:text-slate-300"
    ></div>

    {{-- Pagination aman di HP --}}
    <div class="w-full md:w-auto overflow-x-auto">
      <ul
        id="custom_Pagination"
        class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden"
      ></ul>
    </div>
  </div>
</div>

{{-- MODAL CREATE RESTOCK/RETURN (STYLE DISAMAKAN DENGAN MODAL OBAT) --}}
<div
  id="modalCreateRestockReturn"
  aria-hidden="true"
  class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-black/40 px-4"
>
  <div class="relative w-full max-w-7xl">
    {{-- Card --}}
    <div
      class="relative flex flex-col bg-white rounded-2xl shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-700 max-h-[90vh]"
    >
      {{-- Header (sticky) --}}
      <div
        class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-900 z-10 rounded-t-2xl"
      >
        <div>
          <h3
            class="text-base md:text-lg font-semibold text-gray-900 dark:text-white"
          >
            Buat Transaksi Restock / Return
          </h3>
          <p
            class="text-[11px] md:text-xs text-gray-500 dark:text-gray-400 mt-1"
          >
            Lengkapi header transaksi. Setelah tersimpan, lanjut input detail
            item.
          </p>
        </div>

        <button
          type="button"
          id="btn-close-modal-create"
          class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
        >
          <i class="fa-solid fa-xmark text-sm"></i>
        </button>
      </div>

      {{-- Body (scrollable) --}}
      <form
        id="formCreateRestockReturn"
        class="px-6 py-5 space-y-7 overflow-y-auto"
        data-url="{{ route('create.data.restock.dan.return') }}"
        method="POST"
      >
        @csrf {{-- Section: Header Transaksi --}}
        <div class="space-y-4">
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600"
              >
                <i class="fa-solid fa-clipboard-list text-xs"></i>
              </div>
              <div>
                <h4
                  class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200"
                >
                  Header Transaksi
                </h4>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                  Data utama transaksi restock/return.
                </p>
              </div>
            </div>

            <label class="inline-flex items-center cursor-pointer select-none">
              <div class="relative mr-3">
                <input
                  type="checkbox"
                  id="togglePurchaseOrder"
                  class="sr-only peer"
                />

                <div
                  class="w-10 h-5 bg-gray-300 rounded-full peer peer-checked:bg-blue-500 transition-colors"
                ></div>
                <div
                  class="absolute left-0.5 top-0.5 bg-white w-4 h-4 rounded-full shadow-sm transition-transform peer-checked:translate-x-5"
                ></div>
              </div>

              <span
                id="labelPurchaseOrder"
                class="text-sm font-medium text-gray-400 transition-colors duration-200 leading-tight text-left"
              >
                Create Purchase<br />Order
              </span>
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Tanggal transaksi --}}
            <div>
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                Tanggal Transaksi <span class="text-red-500">*</span>
              </label>
              <input
                type="date"
                name="tanggal_transaksi"
                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                required
              />
              <div
                class="text-red-600 text-[11px] mt-1"
                data-error="tanggal_transaksi"
              ></div>
            </div>

            {{-- Jenis transaksi --}}
            <div>
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                Jenis Transaksi <span class="text-red-500">*</span>
              </label>
              <select
                id="jenis_transaksi"
                name="jenis_transaksi"
                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                required
              >
                <option value="">-- Pilih --</option>
              </select>
              <div
                class="text-red-600 text-[11px] mt-1"
                data-error="jenis_transaksi"
              ></div>
            </div>

            {{-- SUPPLIER (TomSelect style seperti modal obat) --}}
            <div class="md:col-span-2">
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                Supplier
              </label>

              <div class="relative mt-1">
                <select
                  name="supplier_id"
                  id="supplier_id"
                  data-url-index="{{ route('get.data.supplier') }}"
                  data-url-store="{{ route('create.data.supplier') }}"
                  data-url-delete="{{ route('delete.data.supplier') }}"
                  data-url-update="{{ route('update.data.supplier') }}"
                  data-url-show="{{ route('get.data.supplier.by.id', ['id' => '__ID__']) }}"
                  class="block w-full pr-9 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                  <option value="">Ketik untuk cari / tambah supplier</option>
                </select>

                {{-- Tombol X (clear & delete supplier) --}}
                <button
                  type="button"
                  id="btn-clear-supplier"
                  class="hidden absolute inset-y-0 right-2 my-auto w-5 h-5 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/40"
                >
                  <i class="fa-solid fa-xmark text-[10px]"></i>
                </button>
              </div>

              <div
                class="text-red-600 text-[11px] mt-1"
                data-error="supplier_id"
              ></div>
            </div>

            {{-- Nomor faktur --}}
            <div>
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                Nomor Faktur
              </label>
              <input
                type="text"
                name="nomor_faktur"
                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                placeholder="Opsional"
                autocomplete="off"
              />
              <div
                class="text-red-600 text-[11px] mt-1"
                data-error="nomor_faktur"
              ></div>
            </div>

            {{-- Keterangan --}}
            <div class="md:col-span-2">
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                Keterangan
              </label>
              <textarea
                name="keterangan"
                rows="2"
                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                placeholder="Opsional"
              ></textarea>
              <div
                class="text-red-600 text-[11px] mt-1"
                data-error="keterangan"
              ></div>
            </div>

            {{-- Purchase Order Fields (muncul saat toggle ON) --}}
            <div id="purchaseOrderFields" class="md:col-span-2 hidden">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Tempo Pembayaran (DATE) --}}
                <div>
                  <label
                    class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                  >
                    Tempo Pembayaran
                  </label>
                  <input
                    type="date"
                    name="tempo_pembayaran"
                    id="tempo_pembayaran"
                    class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  />
                  <div
                    class="text-red-600 text-[11px] mt-1"
                    data-error="tempo_pembayaran"
                  ></div>
                </div>

                {{-- Tanggal Pengiriman --}}
                <div>
                  <label
                    class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                  >
                    Tanggal Pengiriman
                  </label>
                  <input
                    type="date"
                    name="tanggal_pengiriman"
                    id="tanggal_pengiriman"
                    class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 selection:focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  />
                  <div
                    class="text-red-600 text-[11px] mt-1"
                    data-error="tanggal_pengiriman"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Section: Detail Supplier (muncul saat create/pilih) --}}
        <div id="supplier-detail" class="space-y-4 hidden">
          <div class="flex items-center gap-2">
            <div
              class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"
            >
              <i class="fa-solid fa-truck-field text-xs"></i>
            </div>
            <div>
              <h4
                class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200"
              >
                Detail Supplier
              </h4>
              <p class="text-[11px] text-gray-500 dark:text-gray-400">
                Otomatis terisi jika memilih supplier yang sudah ada. Editable
                jika supplier baru dibuat.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                Kontak Person
              </label>
              <input
                type="text"
                id="supplier_kontak_person"
                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                autocomplete="off"
              />
            </div>

            <div>
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                No HP
              </label>
              <input
                type="text"
                id="supplier_no_hp"
                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                autocomplete="off"
              />
            </div>

            <div>
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                Email
              </label>
              <input
                type="email"
                id="supplier_email"
                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                autocomplete="off"
              />
            </div>

            <div class="md:col-span-2">
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                Alamat
              </label>
              <textarea
                id="supplier_alamat"
                rows="2"
                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
              ></textarea>
            </div>

            <div class="md:col-span-2">
              <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-300"
              >
                Keterangan Supplier
              </label>
              <textarea
                id="supplier_keterangan"
                rows="2"
                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
              ></textarea>
            </div>
          </div>
        </div>

        {{-- Section: Rincian Item + Sidebar Rincian (layout 2 kolom) --}}
        <div class="mt-5 border-t border-gray-200 dark:border-gray-700 pt-4">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            {{-- LEFT: Form Rincian Item --}}
            <div class="lg:col-span-8">
              {{-- Tabs + Action --}}
              <div class="flex items-center justify-between gap-4">
                <div
                  class="flex items-center gap-6 text-xs font-semibold uppercase tracking-wide"
                >
                  <button
                    type="button"
                    id="tab-obat"
                    data-tab="obat"
                    class="pb-2 border-b-2 border-pink-500 text-gray-900 dark:text-white"
                  >
                    Obat
                  </button>

                  <button
                    type="button"
                    id="tab-bhp"
                    data-tab="bhp"
                    class="pb-2 border-b-2 border-transparent text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                  >
                    Bahan Habis Pakai
                  </button>
                </div>

                <button
                  type="button"
                  id="btn-tambah-rincian"
                  class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold border border-blue-500 text-blue-600 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/30 whitespace-nowrap"
                >
                  Tambah Rincian
                  <i class="fa-solid fa-angle-right text-[10px]"></i>
                </button>
              </div>

              <div class="mt-4 space-y-6">
                {{-- PANEL OBAT --}}
                <div id="panel-obat" data-panel="obat">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nama Obat --}}
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Nama Obat <span class="text-red-500">*</span>
                      </label>
                      <select name="obat_id" id="obat_id" required class="mt-1">
                        <option value="">Pilih obat...</option>
                      </select>
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="obat_id"
                      ></div>
                    </div>

                    {{-- Kategori Obat --}}
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Kategori Obat
                      </label>
                      <input
                        id="kategori_obat_id"
                        class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        disabled
                      />
                    </div>

                    {{-- Transaksi --}}
                    <div class="md:col-span-2">
                      <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Transaksi <span class="text-red-500">*</span>
                      </label>
                      <select
                        id="transaksi_obat"
                        name="transaksi_obat"
                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                      >
                        <option value="">-- Pilih --</option>
                        <option value="Restock">Restock</option>
                        <option value="Return">Return</option>
                      </select>
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="transaksi_obat"
                      ></div>
                    </div>

                    {{-- Satuan --}}
                    <div class="md:col-span-2">
                      <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Satuan <span class="text-red-500">*</span>
                      </label>
                      <input
                        id="satuan_obat_id"
                        class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        disabled
                      />
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="satuan_obat_id"
                      ></div>
                    </div>

                    {{-- Expired Date --}}
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Expired Date <span class="text-red-500">*</span>
                      </label>
                      <input
                        type="date"
                        name="expired_date_obat"
                        id="expired_date_obat"
                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                      />
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="expired_date_obat"
                      ></div>
                    </div>

                    {{-- Expired Date Return --}}
                    <div id="expired_date_obat_return" class="hidden">
                      <label
                        class="text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Expired Date <span class="text-red-500">*</span>
                      </label>
                      <select
                        type="date"
                        id="select_expired_date_obat_restock"
                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                      ></select>
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="expired_date_obat"
                      ></div>
                    </div>

                    {{-- Batch (COMMON: Restock & Return) --}}
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Batch <span class="text-red-500">*</span>
                      </label>
                      <input
                        name="batch_obat"
                        id="batch_obat"
                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                      />
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="batch_obat"
                      ></div>
                    </div>

                    {{-- Total Stok Item (RETURN ONLY) --}}
                    <div id="wrapper_total_stok" class="hidden md:col-span-2">
                      <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Total Stok Item <span class="text-red-500">*</span>
                      </label>
                      <input
                        type="text"
                        id="total_stok_item"
                        readonly
                        class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        value="0"
                      />
                      <p class="text-[10px] text-gray-500 mt-1">
                        * Stok saat ini di depot tujuan
                      </p>
                    </div>

                    {{-- Jumlah Obat --}}
                    <div class="md:col-span-2">
                      <label
                        for="jumlah_obat"
                        id="label_jumlah_obat"
                        class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Jumlah Obat <span class="text-red-500">*</span>
                      </label>
                      <input
                        type="number"
                        min="0"
                        name="jumlah_obat"
                        id="jumlah_obat"
                        value="0"
                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        required
                      />
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="jumlah_obat"
                      ></div>
                    </div>

                    {{-- Harga Beli Satuan Lama --}}
                    <div>
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
                        Harga Beli Satuan Obat Lama
                      </label>
                      <input
                        type="text"
                        id="harga_beli_satuan_obat_lama"
                        readonly
                        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      />
                    </div>

                    {{-- Harga Beli Satuan Baru --}}
                    <div>
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
                        Harga Beli Satuan Obat Baru
                        <span class="text-red-500">*</span>
                      </label>
                      <input
                        type="text"
                        name="harga_satuan_obat_baru"
                        id="harga_satuan_obat_baru"
                        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        required
                      />
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="harga_satuan_obat_baru"
                      ></div>
                    </div>

                    {{-- Harga Beli Rata-Rata Lama --}}
                    <div>
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
                        Harga Beli Rata-Rata Lama
                      </label>
                      <input
                        type="text"
                        id="harga_beli_rata_lama_obat"
                        readonly
                        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      />
                    </div>

                    {{-- Harga Beli Rata-Rata Baru --}}
                    <div>
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
                        Harga Beli Rata-Rata Baru
                        <span class="text-red-500">*</span>
                      </label>
                      <input
                        type="text"
                        name="harga_beli_rata_baru_obat"
                        id="harga_beli_rata_baru_obat"
                        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        required
                      />
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="harga_beli_rata_baru_obat"
                      ></div>
                    </div>

                    {{-- Harga Jual Satuan Lama --}}
                    <div>
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
                        Harga Jual Satuan Obat Lama
                      </label>
                      <input
                        type="text"
                        id="harga_jual_lama_obat"
                        readonly
                        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      />
                    </div>

                    {{-- Harga Jual Satuan Baru --}}
                    <div>
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
                        Harga Jual Satuan Obat Baru
                        <span class="text-red-500">*</span>
                      </label>
                      <input
                        type="text"
                        name="harga_jual_baru_obat"
                        id="harga_jual_baru_obat"
                        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        required
                      />
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="harga_jual_baru_obat"
                      ></div>
                    </div>

                    {{-- Harga Jual OTC Lama --}}
                    <div>
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
                        Harga Jual OTC Lama
                      </label>
                      <input
                        type="text"
                        id="harga_jual_otc_lama_obat"
                        readonly
                        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      />
                    </div>

                    {{-- Harga Jual OTC Baru --}}
                    <div>
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
                        Harga Jual OTC Baru
                      </label>
                      <input
                        type="text"
                        name="harga_jual_otc_baru_obat"
                        id="harga_jual_otc_baru_obat"
                        class="input-rupiah mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      />
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="harga_jual_otc_baru_obat"
                      ></div>
                    </div>

                    {{-- Harga Total Awal --}}
                    <div class="md:col-span-2">
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
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
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="harga_total_awal_obat"
                      ></div>
                    </div>

                    {{-- Diskon Obat (%) --}}
                    <div>
                      <label
                        class="block text-[11px] font-medium text-gray-500 dark:text-gray-400"
                      >
                        Diskon Obat <span class="text-red-500">*</span>
                      </label>
                      <input
                        type="number"
                        min="0"
                        max="100"
                        step="1"
                        name="diskon_obat_persen"
                        id="diskon_obat_persen"
                        value="0"
                        class="mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      />
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="diskon_obat_persen"
                      ></div>
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
                        class="mt-1"
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
                  </div>

                  {{-- Button: Tambah Rincian --}}
                  <div class="mt-4 flex justify-end">
                    <button
                      type="button"
                      id="btn-tambah-rincian-obat"
                      class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold border border-blue-500 text-blue-600 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/30"
                    >
                      Tambah Rincian
                      <i class="fa-solid fa-angle-right text-[10px]"></i>
                    </button>
                  </div>
                </div>

                {{-- PANEL BHP --}}

                <div id="panel-bhp" data-panel="bhp" class="hidden">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
                    {{-- Bahan Habis Pakai --}}
                    <div class="md:col-span-2">
                      <label
                        class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                      >
                        Bahan Habis Pakai <span class="text-red-500">*</span>
                      </label>

                      <select
                        name="bhp_id"
                        id="bhp_id"
                        required
                        class="mt-1 w-full bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                      >
                        <option value="">Pilih BHP...</option>
                      </select>
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="bhp_id"
                      ></div>
                    </div>

                    {{-- Transaksi BHP (FULL WIDTH) --}}
                    <div class="md:col-span-2">
                      <label
                        class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                      >
                        Transaksi <span class="text-red-500">*</span>
                      </label>
                      <select
                        id="transaksi_bhp"
                        name="transaksi_bhp"
                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                      >
                        <option value="">-- Pilih --</option>
                        <option value="Restock">Restock</option>
                        <option value="Return">Return</option>
                      </select>
                      <div
                        class="text-red-600 text-[11px] mt-1"
                        data-error="transaksi_bhp"
                      ></div>
                    </div>

                    {{-- ========================= --}} {{-- BHP RESTOCK ONLY
                    (gambar 1) --}} {{-- ========================= --}}
                    <div
                      id="bhp_restock_only_fields"
                      class="hidden md:col-span-2"
                    >
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                          class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                          {{-- Kategori --}}
                          <div>
                            <label
                              class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                            >
                              Kategori Bahan <span class="text-red-500">*</span>
                            </label>
                            <input
                              id="kategori_bhp_id"
                              disabled
                              class="mt-1 w-full bg-transparent border border-gray-300 dark:border-gray-700 text-sm text-gray-900 dark:text-white px-0 py-2 focus:ring-0 focus:border-blue-500"
                            />
                          </div>

                          {{-- Satuan --}}
                          <div>
                            <label
                              class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                            >
                              Satuan <span class="text-red-500">*</span>
                            </label>
                            <input
                              id="satuan_bhp_id"
                              disabled
                              class="mt-1 w-full bg-transparent border border-gray-300 dark:border-gray-700 text-sm text-gray-900 dark:text-white px-0 py-2 focus:ring-0 focus:border-blue-500"
                            />
                          </div>
                        </div>

                        {{-- Expired (optional, restock boleh ada tapi tidak
                        wajib jika kamu mau) --}}
                        <div>
                          <label
                            class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                          >
                            Expired Date
                          </label>
                          <input
                            type="date"
                            name="expired_date_bhp"
                            id="expired_date_bhp"
                            class="mt-1 w-full bg-transparent border border-gray-300 dark:border-gray-700 text-sm text-gray-900 dark:text-white px-0 py-2 focus:ring-0 focus:border-blue-500"
                          />
                        </div>

                        {{-- Batch --}}
                        <div>
                          <label
                            class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                          >
                            Batch
                          </label>
                          <input
                            name="batch_bhp"
                            id="batch_bhp"
                            class="mt-1 w-full bg-transparent border border-gray-300 dark:border-gray-700 text-sm text-gray-900 dark:text-white px-0 py-2 focus:ring-0 focus:border-blue-500"
                          />
                        </div>

                        {{-- Jumlah --}}
                        <div>
                          <label
                            class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                          >
                            Jumlah Bahan <span class="text-red-500">*</span>
                          </label>
                          <input
                            type="number"
                            min="0"
                            name="jumlah_bhp"
                            id="jumlah_bhp"
                            value="0"
                            class="mt-1 w-full bg-transparent border border-gray-300 dark:border-gray-700 text-sm text-gray-900 dark:text-white px-0 py-2 focus:ring-0 focus:border-blue-500"
                          />
                        </div>

                        {{-- (tambahkan field restock lain sesuai kebutuhan
                        kamu) --}}
                      </div>
                    </div>

                    {{-- ========================= --}} {{-- BHP RETURN ONLY
                    (gambar 2) --}} {{-- ========================= --}}
                    <div
                      id="bhp_return_only_fields"
                      class="hidden md:col-span-2"
                    >
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Total stok --}}
                        <div>
                          <label
                            class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                          >
                            Total Stok Item <span class="text-red-500">*</span>
                          </label>
                          <input
                            type="text"
                            id="total_stok_bhp"
                            readonly
                            class="mt-1 w-full bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            value="0"
                          />
                        </div>

                        {{-- Expired wajib saat Return --}}
                        <div>
                          <label
                            class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                          >
                            Expired Date <span class="text-red-500">*</span>
                          </label>
                          <input
                            type="date"
                            name="expired_date_bhp"
                            id="expired_date_bhp"
                            class="mt-1 w-full bg-transparent border border-gray-300 dark:border-gray-700 text-sm text-gray-900 dark:text-white px-0 py-2 focus:ring-0 focus:border-blue-500"
                          />
                        </div>

                        {{-- Batch wajib saat Return --}}
                        <div>
                          <label
                            class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                          >
                            Batch <span class="text-red-500">*</span>
                          </label>
                          <input
                            name="batch_bhp"
                            id="batch_bhp"
                            class="mt-1 w-full bg-transparent border border-gray-300 dark:border-gray-700 text-sm text-gray-900 dark:text-white px-0 py-2 focus:ring-0 focus:border-blue-500"
                          />
                        </div>

                        {{-- Jumlah --}}
                        <div class="md:col-span-3">
                          <label
                            class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                          >
                            Jumlah Return <span class="text-red-500">*</span>
                          </label>
                          <input
                            type="number"
                            min="0"
                            name="jumlah_bhp"
                            id="jumlah_bhp"
                            value="0"
                            class="mt-1 w-full bg-transparent border border-gray-300 dark:border-gray-700 text-sm text-gray-900 dark:text-white px-0 py-2 focus:ring-0 focus:border-blue-500"
                          />
                        </div>
                      </div>
                    </div>

                    {{-- Keterangan --}}
                    <div class="md:col-span-2">
                      <label
                        class="block text-[11px] font-medium text-gray-600 dark:text-gray-300"
                      >
                        Keterangan
                      </label>
                      <textarea
                        name="keterangan_bhp"
                        id="keterangan_bhp"
                        rows="4"
                        class="mt-1 w-full bg-transparent border-0 border-b border-gray-300 dark:border-gray-700 text-sm text-gray-900 dark:text-white px-0 py-2 focus:ring-0 focus:border-blue-500"
                        placeholder="Opsional"
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- RIGHT: Sidebar Rincian --}}
            <div class="lg:col-span-4">
              <div class="lg:sticky lg:top-24 space-y-4">
                <div
                  class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4"
                >
                  <div id="container-rincian">
                    <h3 class="text-blue-500 font-bold text-lg mb-2">
                      Rincian
                    </h3>

                    {{--
                    <div class="flex items-start justify-between gap-4">
                      <div class="flex-1 space-y-1">
                        <h4 class="text-blue-400 font-medium text-sm">
                          Paracetamol 50
                        </h4>
                        <p class="text-gray-800 text-sm font-semibold">
                          Restock
                        </p>

                        <div class="pt-2">
                          <p class="text-[11px] text-gray-600 leading-tight">
                            Harga beli rata - rata
                          </p>
                          <p class="text-[11px] text-gray-600">
                            Exp. 31/01/2026
                          </p>
                        </div>
                      </div>

                      <div class="text-right min-w-[80px]">
                        <p class="text-gray-700 text-sm">1000 Tablet</p>
                      </div>

                      <div class="flex items-start gap-4">
                        <div class="text-right space-y-1">
                          <p class="text-gray-700 text-sm">@ Rp. 8,00</p>
                          <p class="text-gray-900 text-sm font-bold">
                            Rp. 7.200
                          </p>
                          <p class="text-gray-500 text-[11px] pt-1">@ Rp. 56</p>
                        </div>

                        <button
                          class="bg-pink-500 hover:bg-pink-600 text-white w-6 h-6 rounded-full flex items-center justify-center transition-colors shadow-md"
                        >
                          <i class="fa-solid fa-xmark text-[10px]"></i>
                        </button>
                      </div>
                    </div>
                    --}}
                  </div>

                  <div class="mt-6 text-sm font-semibold text-sky-600">
                    Biaya Lainnya
                  </div>

                  <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                      <span class="text-gray-600 dark:text-gray-300 font-medium"
                        >Subtotal</span
                      >
                      <span
                        id="sum-subtotal"
                        class="font-semibold text-gray-900 dark:text-white"
                        >Rp. 0</span
                      >
                    </div>

                    <div class="flex items-center justify-between gap-4">
                      <span class="text-gray-600 dark:text-gray-300 font-medium"
                        >Pajak</span
                      >
                      <div class="w-40">
                        <div class="relative">
                          <input
                            type="number"
                            min="0"
                            max="100"
                            step="1"
                            id="sum-pajak"
                            class="w-full text-right text-sm bg-transparent border-0 border-gray-300 dark:border-gray-700 focus:ring-0 focus:border-sky-500 pr-6 py-1"
                            value="0"
                          />
                          <span
                            class="absolute right-0 top-1 text-gray-500 text-sm"
                            >%</span
                          >
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-3 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                      <span class="text-gray-600 dark:text-gray-300 font-medium"
                        >Biaya Lainnya</span
                      >
                      <div class="w-40">
                        <input
                          type="text"
                          id="sum-biaya-lainnya"
                          class="input-rupiah w-full text-right text-sm bg-transparent border-0 border-gray-300 dark:border-gray-700 focus:ring-0 focus:border-sky-500 py-1"
                          value="Rp. 0"
                        />
                      </div>
                    </div>

                    <div
                      class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between"
                    >
                      <span
                        class="text-gray-700 dark:text-gray-200 font-semibold"
                        >Total Transaksi</span
                      >
                      <span
                        id="sum-total"
                        class="font-bold text-gray-900 dark:text-white"
                        >Rp. 0</span
                      >
                    </div>
                  </div>
                </div>

                {{-- Buttons (pakai ID yang lama biar JS gak perlu diubah) --}}
                <div class="flex items-center justify-end gap-2">
                  <button
                    type="button"
                    id="btn-cancel-modal-create"
                    class="px-4 py-2.5 text-xs font-medium text-red-600 bg-white rounded-lg hover:bg-red-50 border border-red-300 dark:bg-gray-900 dark:border-red-700/60 dark:hover:bg-red-900/20"
                  >
                    Batal
                  </button>

                  <button
                    type="submit"
                    id="btn-submit-create"
                    class="px-4 py-2.5 text-xs font-semibold text-white bg-rose-500 rounded-lg hover:bg-rose-600 shadow-sm"
                  >
                    Simpan Transaksi
                  </button>
                </div>

                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                  * Subtotal diambil dari “Harga Total Awal” item aktif
                  (Obat/BHP). Pajak & Biaya Lainnya auto dihitung.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Section: Depot -->
        <div class="space-y-4 pb-1 mt-6">
          <div class="text-center">
            <p
              class="text-[11px] font-semibold text-blue-600 uppercase tracking-[0.16em]"
            >
              Set Ketersediaan Obat Pada Depot
            </p>
            <p class="text-[11px] text-gray-500 mt-1 dark:text-gray-400">
              Atur distribusi stok ke Apotek / Gudang yang berbeda.
            </p>
          </div>

          <!-- WRAPPER DEPOT -->
          <div id="depot-container-restock" class="space-y-3">
            <!-- ROW DEPOT (TEMPLATE PERTAMA) -->
            <div
              class="depot-row grid grid-cols-12 gap-4 items-center bg-gray-50/60 dark:bg-gray-800/60 rounded-xl px-4 py-4 border border-dashed border-gray-200 dark:border-gray-700"
            >
              <!-- NAMA DEPOT -->
              <div class="col-span-12 md:col-span-4">
                <label
                  class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                >
                  Nama Depot <span class="text-red-500">*</span>
                </label>

                <div class="relative mt-1">
                  <select
                    name="depot_id[]"
                    class="select-nama-depot block w-full text-sm bg-transparent dark:bg-gray-900 border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                    data-url-index="{{ route('get.data.depot') }}"
                    data-url-store="{{ route('create.data.depot') }}"
                    data-url-delete="{{ route('delete.data.depot') }}"
                  >
                    <option value="">Pilih / ketik nama depot</option>
                  </select>

                  <button
                    type="button"
                    class="btn-clear-depot hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 text-base font-bold"
                  >
                    ×
                  </button>
                </div>

                <div class="depot_id-error text-red-600 text-[11px] mt-1"></div>
              </div>

              <!-- TIPE DEPOT -->
              <div class="col-span-12 md:col-span-3">
                <label
                  class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                >
                  Tipe Depot <span class="text-red-500">*</span>
                </label>

                <div class="relative mt-1">
                  <select
                    name="tipe_depot[]"
                    class="select-tipe-depot block w-full text-sm bg-transparent dark:bg-gray-900 border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                    data-url-index="{{ route('get.data.tipe.depot') }}"
                    data-url-store="{{ route('create.data.tipe.depot') }}"
                    data-url-delete="{{ route('delete.data.tipe.depot') }}"
                  >
                    <option value="">Pilih / ketik tipe depot</option>
                  </select>

                  <button
                    type="button"
                    class="btn-clear-tipe-depot hidden absolute right-2 top-1/2 -translate-y-1/2 flex items-center justify-center text-gray-400 hover:text-red-500"
                  >
                    <i class="fa-solid fa-xmark text-xs"></i>
                  </button>
                </div>

                <div class="tipe_depot-error text-red-600 text-[11px]"></div>
              </div>

              <!-- STOK DEPOT -->
              <div class="col-span-12 md:col-span-3">
                <label
                  class="block text-xs font-medium text-gray-600 dark:text-gray-300"
                >
                  Stok Depot
                </label>
                <input
                  type="number"
                  name="stok_depot[]"
                  class="input-stok-depot mt-1 block w-full text-sm bg-transparent dark:bg-gray-900 border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                  value="0"
                />
              </div>

              <!-- BUTTON HAPUS -->
              <div
                class="col-span-12 md:col-span-2 flex md:justify-center justify-end"
              >
                <button
                  type="button"
                  class="btn-remove-depot w-full md:w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-600 text-xs hover:bg-red-100 border border-red-100"
                >
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="flex justify-start">
            <button
              type="button"
              id="btn-add-depot-restock"
              class="inline-flex items-center text-[11px] font-medium text-blue-600 hover:text-blue-700 hover:underline"
            >
              <i class="fa-solid fa-plus-circle mr-1 text-xs"></i>
              Tambah Depot
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@vite(['resources/js/farmasi/restock-dan-return-obat-dan-bhp/data-restock-dan-return-obat-dan-bhp.js'])
