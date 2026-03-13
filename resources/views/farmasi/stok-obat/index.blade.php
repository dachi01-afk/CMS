<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Stok Obat Farmasi</title>
    <link href="{{ asset('storage/assets/royal_klinik.svg') }}" rel="shortcut icon">

    @vite(['resources/css/app.css'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
</head>

<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-[1600px] space-y-5 px-4 py-5 sm:px-6 lg:px-8">

        {{-- HERO --}}
        <section
            class="relative overflow-hidden rounded-[28px] bg-gradient-to-r from-slate-950 via-indigo-900 to-indigo-600 px-5 py-6 shadow-lg lg:px-6 lg:py-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div
                        class="mb-4 inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-white/90">
                        Data Stok Obat
                    </div>

                    <h1 class="text-3xl font-extrabold leading-tight text-white lg:text-4xl">
                        Data Stok Obat Farmasi
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm leading-7 text-indigo-50/95 lg:text-base">
                        Monitoring stok utama dan kadaluarsa obat untuk operasional farmasi {{ $namaFarmasi }}.
                    </p>
                </div>

                <div class="flex items-start">
                    <a href="{{ route('farmasi.dashboard') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-white/15">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </section>

        {{-- SUMMARY --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Item Obat</p>
                        <h3 id="summaryTotalItemObat" class="mt-2 text-4xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Jumlah item obat sesuai filter aktif</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <i class="fa-solid fa-capsules text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Stok Ditampilkan</p>
                        <h3 id="summaryTotalStokObat" class="mt-2 text-4xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Mengikuti stok utama sesuai filter aktif</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <i class="fa-solid fa-boxes-stacked text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Stok Menipis</p>
                        <h3 id="summaryStokMenipis" class="mt-2 text-4xl font-extrabold text-amber-500">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Perlu restock segera</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Stok Habis</p>
                        <h3 id="summaryStokHabis" class="mt-2 text-4xl font-extrabold text-rose-500">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Harus segera ditindaklanjuti</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
                        <i class="fa-solid fa-circle-xmark text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER --}}
        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Filter Data Stok Obat</h2>
                        <p class="text-sm text-slate-500">
                            Filter data berdasarkan status stok, kategori obat, depot, dan kata kunci pencarian.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-4 py-2 text-xs font-bold text-indigo-700">
                        <i class="fa-solid fa-table"></i>
                        DataTables
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-bold text-slate-700">
                        <i class="fa-solid fa-warehouse"></i>
                        Inventory Based
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                <div class="xl:col-span-3">
                    <label for="filterStatusStokObat" class="mb-2 block text-sm font-semibold text-slate-700">
                        Status Stok
                    </label>
                    <select id="filterStatusStokObat"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        <option value="semua" selected>Semua</option>
                        <option value="aman">Aman</option>
                        <option value="menipis">Menipis</option>
                        <option value="habis">Habis</option>
                    </select>
                </div>

                <div class="xl:col-span-3">
                    <label for="filterKategoriObat" class="mb-2 block text-sm font-semibold text-slate-700">
                        Kategori Obat
                    </label>
                    <select id="filterKategoriObat"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriObat as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori_obat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="xl:col-span-3">
                    <label for="filterDepotObat" class="mb-2 block text-sm font-semibold text-slate-700">
                        Depot
                    </label>
                    <select id="filterDepotObat"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        <option value="">Semua Depot</option>
                        @foreach ($depotList as $depot)
                            <option value="{{ $depot->id }}">{{ $depot->nama_depot }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="xl:col-span-3">
                    <label for="filterKeywordStokObat" class="mb-2 block text-sm font-semibold text-slate-700">
                        Kata Kunci
                    </label>
                    <input type="text" id="filterKeywordStokObat" placeholder="Cari kode/nama/kategori/satuan..."
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                </div>

                <div class="xl:col-span-12 flex flex-wrap items-center gap-3">
                    <button id="btnApplyFilterStokObat" type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-indigo-700">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Terapkan Filter
                    </button>

                    <button id="btnResetFilterStokObat" type="button"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>

                    <div class="rounded-full bg-slate-50 px-4 py-2 text-xs font-bold text-slate-600">
                        Batas stok menipis: {{ $batasStokMenipis }}
                    </div>
                </div>
            </div>
        </section>

        {{-- TABLE --}}
        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-xl font-extrabold text-slate-900">Tabel Data Stok Obat</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Menampilkan data stok utama dan kadaluarsa terdekat.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table id="tableStokObat" class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Kode Obat</th>
                            <th class="px-4 py-3">Nama Obat</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Satuan</th>
                            <th class="px-4 py-3">Stok Utama</th>
                            <th class="px-4 py-3">Kadaluarsa Terdekat</th>
                            <th class="px-4 py-3">Status Stok</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- MODAL DETAIL --}}
    <div id="modalDetailObat" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 py-6">
        <div class="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-[28px] bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900">Detail Batch Obat</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Informasi batch untuk obat yang dipilih.
                    </p>
                </div>
                <button type="button" id="btnCloseModalDetailObat"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="max-h-[calc(90vh-88px)] overflow-y-auto px-6 py-5">
                <div id="detailObatLoading" class="hidden py-10 text-center text-sm text-slate-500">
                    Memuat detail obat...
                </div>

                <div id="detailObatContent" class="hidden space-y-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Kode Obat</p>
                            <p id="detailKodeObat" class="mt-2 text-sm font-bold text-slate-900">-</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Nama Obat</p>
                            <p id="detailNamaObat" class="mt-2 text-sm font-bold text-slate-900">-</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Kategori</p>
                            <p id="detailKategoriObat" class="mt-2 text-sm font-bold text-slate-900">-</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Satuan</p>
                            <p id="detailSatuanObat" class="mt-2 text-sm font-bold text-slate-900">-</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Stok Utama</p>
                            <p id="detailStokUtama" class="mt-2 text-sm font-bold text-slate-900">0</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Batch</p>
                            <p id="detailTotalBatch" class="mt-2 text-sm font-bold text-slate-900">0</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Filter Depot Aktif</p>
                            <p id="detailDepotAktif" class="mt-2 text-sm font-bold text-slate-900">Semua Depot</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200">
                        <div class="border-b border-slate-200 px-4 py-3">
                            <h4 class="text-sm font-extrabold text-slate-900">Daftar Batch Obat</h4>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                        <th class="px-4 py-3">No</th>
                                        <th class="px-4 py-3">Nama Batch</th>
                                        <th class="px-4 py-3">Kadaluarsa</th>
                                        <th class="px-4 py-3">Stok Batch</th>
                                        <th class="px-4 py-3">Depot</th>
                                        <th class="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="detailBatchTableBody">
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">
                                            Tidak ada data batch
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="detailObatEmpty" class="hidden py-10 text-center text-sm text-slate-500">
                    Detail obat tidak ditemukan.
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let stokTable = null;

            function formatNumber(value) {
                return Number(value || 0).toLocaleString("id-ID");
            }

            function formatTanggal(dateString) {
                if (!dateString) return "-";

                if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                    const parts = dateString.split("-");
                    const date = new Date(parts[0], parts[1] - 1, parts[2]);

                    return date.toLocaleDateString("id-ID", {
                        day: "2-digit",
                        month: "short",
                        year: "numeric",
                    });
                }

                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString;

                return date.toLocaleDateString("id-ID", {
                    day: "2-digit",
                    month: "short",
                    year: "numeric",
                });
            }

            function buildStatusStokBadge(status) {
                if (status === "Habis") {
                    return `<span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-600">Habis</span>`;
                }

                if (status === "Menipis") {
                    return `<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-600">Menipis</span>`;
                }

                return `<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-600">Aman</span>`;
            }

            function buildDetailBatchBadge(status) {
                if (status === "Kadaluarsa") {
                    return `<span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-600">Kadaluarsa</span>`;
                }

                if (status === "Segera Kadaluarsa") {
                    return `<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-600">Segera Kadaluarsa</span>`;
                }

                return `<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-600">Aman</span>`;
            }

            function styleDataTableControls() {
                const $wrapper = $("#tableStokObat_wrapper");

                $wrapper.find("select").addClass(
                    "rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none"
                );

                $wrapper.find(".paginate_button").addClass(
                    "!rounded-xl !border !border-slate-200 !bg-white !px-3 !py-2 !text-sm !text-slate-700 hover:!bg-slate-50"
                );

                $wrapper.find(".dataTables_info").addClass("text-sm text-slate-500");
                $wrapper.find(".dataTables_length").addClass("mb-3");
                $wrapper.find(".dataTables_paginate").addClass("mt-3");
            }

            function resetSummary() {
                $("#summaryTotalItemObat").text("0");
                $("#summaryTotalStokObat").text("0");
                $("#summaryStokMenipis").text("0");
                $("#summaryStokHabis").text("0");
            }

            function updateSummary(meta) {
                $("#summaryTotalItemObat").text(formatNumber(meta.total_item || 0));
                $("#summaryTotalStokObat").text(formatNumber(meta.total_stok_tampil || 0));
                $("#summaryStokMenipis").text(formatNumber(meta.stok_menipis || 0));
                $("#summaryStokHabis").text(formatNumber(meta.stok_habis || 0));
            }

            function getFilterParams() {
                return {
                    status: $("#filterStatusStokObat").val(),
                    kategori_obat_id: $("#filterKategoriObat").val(),
                    depot_id: $("#filterDepotObat").val(),
                    keyword: $("#filterKeywordStokObat").val().trim(),
                };
            }

            function openModalDetail() {
                $("#modalDetailObat").removeClass("hidden").addClass("flex");
                $("body").addClass("overflow-hidden");
            }

            function closeModalDetail() {
                $("#modalDetailObat").addClass("hidden").removeClass("flex");
                $("body").removeClass("overflow-hidden");
            }

            function showDetailLoading() {
                $("#detailObatLoading").removeClass("hidden");
                $("#detailObatContent").addClass("hidden");
                $("#detailObatEmpty").addClass("hidden");
            }

            function showDetailEmpty() {
                $("#detailObatLoading").addClass("hidden");
                $("#detailObatContent").addClass("hidden");
                $("#detailObatEmpty").removeClass("hidden");
            }

            function showDetailContent() {
                $("#detailObatLoading").addClass("hidden");
                $("#detailObatContent").removeClass("hidden");
                $("#detailObatEmpty").addClass("hidden");
            }

            function renderDetailBatchRows(items) {
                if (!items || !items.length) {
                    return `
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">
                                Tidak ada data batch
                            </td>
                        </tr>
                    `;
                }

                return items.map(function(item, index) {
                    return `
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 text-slate-600">${index + 1}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">${item.nama_batch || '-'}</td>
                            <td class="px-4 py-3 text-slate-700">${formatTanggal(item.tanggal_kadaluarsa_obat)}</td>
                            <td class="px-4 py-3 font-bold text-slate-900">${formatNumber(item.stok_batch || 0)}</td>
                            <td class="px-4 py-3 text-slate-700">${item.nama_depot || '-'}</td>
                            <td class="px-4 py-3">${buildDetailBatchBadge(item.status_batch)}</td>
                        </tr>
                    `;
                }).join("");
            }

            function loadDetailObat(obatId) {
                openModalDetail();
                showDetailLoading();

                const depotId = $("#filterDepotObat").val();

                $.ajax({
                    url: `/farmasi/stok-obat/${obatId}/detail`,
                    type: "GET",
                    data: {
                        depot_id: depotId
                    },
                    success: function(response) {
                        if (!response || !response.data) {
                            showDetailEmpty();
                            return;
                        }

                        const detail = response.data;

                        $("#detailKodeObat").text(detail.kode_obat || "-");
                        $("#detailNamaObat").text(detail.nama_obat || "-");
                        $("#detailKategoriObat").text(detail.kategori_obat || "-");
                        $("#detailSatuanObat").text(detail.satuan_obat || "-");
                        $("#detailStokUtama").text(formatNumber(detail.stok_master || 0));
                        $("#detailTotalBatch").text(formatNumber(detail.total_batch || 0));
                        $("#detailDepotAktif").text(detail.nama_depot_aktif || "Semua Depot");

                        $("#detailBatchTableBody").html(renderDetailBatchRows(detail.batch_list || []));

                        showDetailContent();
                    },
                    error: function(xhr) {
                        console.error("Gagal memuat detail obat:", xhr.responseText);
                        showDetailEmpty();
                    }
                });
            }

            function initTable() {
                stokTable = $("#tableStokObat").DataTable({
                    processing: true,
                    serverSide: false,
                    responsive: true,
                    searching: false,
                    autoWidth: false,
                    pageLength: 10,
                    destroy: true,
                    ajax: {
                        url: "/farmasi/stok-obat/data",
                        type: "GET",
                        data: function(d) {
                            const filters = getFilterParams();
                            d.status = filters.status;
                            d.kategori_obat_id = filters.kategori_obat_id;
                            d.depot_id = filters.depot_id;
                            d.keyword = filters.keyword;
                        },
                        dataSrc: function(json) {
                            updateSummary(json.meta || {});
                            return json.data || [];
                        },
                        error: function(xhr) {
                            resetSummary();
                            console.error("Gagal memuat data stok obat:", xhr.responseText);
                        }
                    },
                    columns: [{
                            data: null,
                            orderable: false,
                            searchable: false,
                            defaultContent: "",
                            className: "px-4 py-3 text-slate-600",
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                        },
                        {
                            data: "kode_obat",
                            defaultContent: "-",
                            className: "px-4 py-3",
                            render: function(data) {
                                return `<span class="font-semibold text-slate-700">${data || "-"}</span>`;
                            },
                        },
                        {
                            data: "nama_obat",
                            defaultContent: "-",
                            className: "px-4 py-3",
                            render: function(data) {
                                return `<span class="font-bold text-slate-900">${data || "-"}</span>`;
                            },
                        },
                        {
                            data: "kategori_obat",
                            defaultContent: "-",
                            className: "px-4 py-3 text-slate-700",
                            render: function(data) {
                                return data || "-";
                            },
                        },
                        {
                            data: "satuan_obat",
                            defaultContent: "-",
                            className: "px-4 py-3 text-slate-700",
                            render: function(data) {
                                return data || "-";
                            },
                        },
                        {
                            data: "stok_master",
                            defaultContent: 0,
                            className: "px-4 py-3",
                            render: function(data) {
                                return `<span class="font-bold text-slate-900">${formatNumber(data)}</span>`;
                            },
                        },
                        {
                            data: "kadaluarsa_terdekat",
                            defaultContent: "-",
                            className: "px-4 py-3 text-slate-700",
                            render: function(data) {
                                return formatTanggal(data);
                            },
                        },
                        {
                            data: "status_stok",
                            defaultContent: "Aman",
                            className: "px-4 py-3",
                            render: function(data) {
                                return buildStatusStokBadge(data);
                            },
                        },
                        {
                            data: "id",
                            orderable: false,
                            searchable: false,
                            className: "px-4 py-3",
                            render: function(data) {
                                return `
                                    <button type="button"
                                        class="btn-detail-obat inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700"
                                        data-id="${data}">
                                        <i class="fa-solid fa-eye"></i>
                                        Lihat Detail
                                    </button>
                                `;
                            },
                        },
                    ],
                    order: [
                        [2, "asc"]
                    ],
                    language: {
                        lengthMenu: "Tampilkan _MENU_ data",
                        zeroRecords: "Data stok obat tidak ditemukan",
                        emptyTable: "Data stok obat tidak tersedia",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                        infoEmpty: "Tidak ada data",
                        processing: "Memuat data...",
                        paginate: {
                            previous: "Sebelumnya",
                            next: "Berikutnya",
                        },
                    },
                    initComplete: function() {
                        styleDataTableControls();
                    },
                    drawCallback: function() {
                        styleDataTableControls();
                    },
                });
            }

            function reloadTable() {
                if (stokTable) {
                    stokTable.ajax.reload(null, true);
                }
            }

            function resetFilter() {
                $("#filterStatusStokObat").val("semua");
                $("#filterKategoriObat").val("");
                $("#filterDepotObat").val("");
                $("#filterKeywordStokObat").val("");
                reloadTable();
            }

            initTable();

            $("#btnApplyFilterStokObat").on("click", function() {
                reloadTable();
            });

            $("#btnResetFilterStokObat").on("click", function() {
                resetFilter();
            });

            $("#filterKeywordStokObat").on("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    reloadTable();
                }
            });

            $(document).on("click", ".btn-detail-obat", function() {
                const obatId = $(this).data("id");
                loadDetailObat(obatId);
            });

            $("#btnCloseModalDetailObat").on("click", function() {
                closeModalDetail();
            });

            $("#modalDetailObat").on("click", function(e) {
                if (e.target.id === "modalDetailObat") {
                    closeModalDetail();
                }
            });

            $(document).on("keydown", function(e) {
                if (e.key === "Escape") {
                    closeModalDetail();
                }
            });
        });
    </script>
</body>

</html>
