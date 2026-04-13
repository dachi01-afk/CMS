<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <title>CMS-Royal-Klinik</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>
</head>

<body class="bg-slate-50">
    <main class="min-h-screen px-4 py-5 lg:px-6">
        <div class="space-y-5">

            <section
                class="relative overflow-hidden rounded-[24px] bg-gradient-to-r from-slate-950 via-blue-900 to-blue-600 px-5 py-6 shadow-lg lg:px-6 lg:py-7">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-white/90">
                            <i class="fa-solid fa-capsules"></i>
                            Data Penjualan Obat
                        </div>

                        <h1 class="text-3xl font-extrabold text-white lg:text-4xl">
                            Data Penjualan Obat Farmasi
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-7 text-white/90 lg:text-base">
                            Monitoring seluruh transaksi penjualan obat dengan filter harian, mingguan, bulanan,
                            dan tahunan untuk kebutuhan operasional farmasi.
                        </p>

                        <div
                            class="mt-5 inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white shadow-sm">
                            <i class="fa-regular fa-calendar-days"></i>
                            <span>{{ $todayLabel }}</span>
                        </div>
                    </div>

                    <a href="{{ route('farmasi.dashboard') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-white/20">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Filter Data Penjualan Obat</h2>
                        <p class="text-sm text-slate-500">
                            Gunakan filter untuk melihat transaksi penjualan obat berdasarkan periode.
                        </p>
                    </div>

                    <span id="penjualanFilterBadge"
                        class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-bold text-blue-700">
                        <i class="fa-solid fa-calendar-days"></i>
                        Filter aktif
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                    <div class="xl:col-span-3">
                        <label for="filterPeriodePenjualan" class="mb-2 block text-sm font-semibold text-slate-700">
                            Mode Periode
                        </label>
                        <select id="filterPeriodePenjualan"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                            <option value="harian" {{ $defaultPeriode === 'harian' ? 'selected' : '' }}>Harian</option>
                            <option value="mingguan" {{ $defaultPeriode === 'mingguan' ? 'selected' : '' }}>Mingguan
                            </option>
                            <option value="bulanan" {{ $defaultPeriode === 'bulanan' ? 'selected' : '' }}>Bulanan
                            </option>
                            <option value="tahunan" {{ $defaultPeriode === 'tahunan' ? 'selected' : '' }}>Tahunan
                            </option>
                        </select>
                    </div>

                    <div id="filterHarianPenjualanWrap" class="hidden xl:col-span-3">
                        <label for="filterTanggalPenjualan" class="mb-2 block text-sm font-semibold text-slate-700">
                            Pilih Tanggal
                        </label>
                        <input type="date" id="filterTanggalPenjualan" value="{{ $defaultTanggal }}"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div id="filterMingguanPenjualanWrap" class="hidden xl:col-span-3">
                        <label for="filterMingguPenjualan" class="mb-2 block text-sm font-semibold text-slate-700">
                            Pilih Minggu
                        </label>
                        <input type="week" id="filterMingguPenjualan" value="{{ $defaultMinggu }}"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div id="filterBulananPenjualanWrap" class="hidden xl:col-span-3">
                        <label for="filterBulanPenjualan" class="mb-2 block text-sm font-semibold text-slate-700">
                            Pilih Bulan
                        </label>
                        <input type="month" id="filterBulanPenjualan" value="{{ $defaultBulan }}"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div id="filterTahunanPenjualanWrap" class="hidden xl:col-span-3">
                        <label for="filterTahunPenjualan" class="mb-2 block text-sm font-semibold text-slate-700">
                            Pilih Tahun
                        </label>
                        <input type="number" id="filterTahunPenjualan" min="2020" max="2100"
                            value="{{ $defaultTahun }}"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div class="xl:col-span-3 flex items-end gap-3">
                        <button id="btnApplyPenjualanFilter" type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-blue-700">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Terapkan Filter
                        </button>

                        <button id="btnResetPenjualanFilter" type="button"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                <div class="xl:col-span-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total Transaksi</p>
                        <h3 id="penjualanTotalTransaksi" class="mt-2 text-4xl font-extrabold text-slate-900">0</h3>
                        <p id="penjualanFilterLabel" class="mt-2 text-xs text-slate-400">-</p>
                    </div>
                </div>

                <div class="xl:col-span-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total Pemasukan</p>
                        <h3 id="penjualanTotalPemasukan" class="mt-2 text-4xl font-extrabold text-emerald-600">Rp 0
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Akumulasi transaksi status Sudah Bayar</p>
                    </div>
                </div>

                <div class="xl:col-span-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Periode Aktif</p>
                        <h3 id="penjualanModeLabel" class="mt-2 text-2xl font-extrabold text-slate-900">-</h3>
                        <p id="penjualanLastUpdated" class="mt-2 text-xs text-slate-400">Belum diperbarui</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">Tabel Penjualan Obat</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Menampilkan data transaksi penjualan obat sesuai filter yang aktif.
                        </p>
                    </div>

                    <button id="btnRefreshPenjualan" type="button"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-rotate-right"></i>
                        Refresh Data
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="tablePenjualanObat" class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Kode Transaksi</th>
                                <th class="px-4 py-3">Pasien</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>
        </div>

        <div id="modalDetailPenjualan"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 px-4">
            <div class="w-full max-w-5xl rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Detail Transaksi Obat</h3>
                        <p class="text-sm text-slate-500">Informasi lengkap transaksi penjualan obat</p>
                    </div>
                    <button type="button" id="btnCloseModalDetailPenjualan"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="max-h-[80vh] overflow-y-auto px-6 py-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-semibold text-slate-500">Kode Transaksi</p>
                            <p id="detailKodeTransaksi" class="mt-2 text-sm font-bold text-slate-900">-</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-semibold text-slate-500">Pasien</p>
                            <p id="detailNamaPasien" class="mt-2 text-sm font-bold text-slate-900">-</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-semibold text-slate-500">Tanggal</p>
                            <p id="detailTanggalTransaksi" class="mt-2 text-sm font-bold text-slate-900">-</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-semibold text-slate-500">Status</p>
                            <p id="detailStatusTransaksi" class="mt-2 text-sm font-bold text-slate-900">-</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-semibold text-slate-500">Metode Pembayaran</p>
                            <p id="detailMetodePembayaran" class="mt-2 text-sm font-bold text-slate-900">-</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-semibold text-slate-500">Total Setelah Diskon</p>
                            <p id="detailGrandTotal" class="mt-2 text-lg font-extrabold text-emerald-600">Rp 0</p>
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Obat</th>
                                    <th class="px-4 py-3 text-center">Qty</th>
                                    <th class="px-4 py-3 text-right">Harga</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                    <th class="px-4 py-3 text-center">Diskon</th>
                                    <th class="px-4 py-3 text-right">Setelah Diskon</th>
                                </tr>
                            </thead>
                            <tbody id="detailPenjualanItems"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        $(function() {
            let penjualanTable = null;

            const defaultState = {
                periode: @json($defaultPeriode),
                tanggal: @json($defaultTanggal),
                minggu: @json($defaultMinggu),
                bulan: @json($defaultBulan),
                tahun: @json($defaultTahun),
            };

            const dataUrl = @json(route('farmasi.penjualan-obat.data'));
            const detailRouteTemplate = @json(route('farmasi.penjualan.obat.detail', ['id' => '__ID__']));
            const formatter = new Intl.NumberFormat("id-ID");

            const $table = $("#tablePenjualanObat");
            const $modal = $("#modalDetailPenjualan");
            const $detailItems = $("#detailPenjualanItems");

            function formatNumber(value) {
                return formatter.format(Number(value || 0));
            }

            function formatRupiah(value) {
                return "Rp " + formatter.format(Number(value || 0));
            }

            function escapeHtml(text) {
                return String(text ?? "")
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function formatTanggalJam(value) {
                if (!value) return "-";

                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return value;

                return date.toLocaleString("id-ID", {
                    day: "2-digit",
                    month: "long",
                    year: "numeric",
                });
            }

            function buildStatusBadge(status) {
                const safeStatus = escapeHtml(status || "-");

                if (status === "Sudah Bayar") {
                    return `<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-600">${safeStatus}</span>`;
                }

                if (status === "Belum Bayar") {
                    return `<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-600">${safeStatus}</span>`;
                }

                if (status === "Batal") {
                    return `<span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-600">${safeStatus}</span>`;
                }

                return `<span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">${safeStatus}</span>`;
            }

            function toggleFilterInputs() {
                const periode = $("#filterPeriodePenjualan").val();

                $("#filterHarianPenjualanWrap, #filterMingguanPenjualanWrap, #filterBulananPenjualanWrap, #filterTahunanPenjualanWrap")
                    .addClass("hidden");

                if (periode === "harian") {
                    $("#filterHarianPenjualanWrap").removeClass("hidden");
                    return;
                }

                if (periode === "mingguan") {
                    $("#filterMingguanPenjualanWrap").removeClass("hidden");
                    return;
                }

                if (periode === "bulanan") {
                    $("#filterBulananPenjualanWrap").removeClass("hidden");
                    return;
                }

                $("#filterTahunanPenjualanWrap").removeClass("hidden");
            }

            function getFilterParams() {
                const periode = $("#filterPeriodePenjualan").val();

                const params = {
                    periode
                };

                if (periode === "harian") {
                    params.tanggal = $("#filterTanggalPenjualan").val();
                } else if (periode === "mingguan") {
                    params.minggu = $("#filterMingguPenjualan").val();
                } else if (periode === "bulanan") {
                    params.bulan = $("#filterBulanPenjualan").val();
                } else {
                    params.tahun = $("#filterTahunPenjualan").val();
                }

                return params;
            }

            function setLoadingState(isLoading) {
                const $applyBtn = $("#btnApplyPenjualanFilter");
                const $refreshBtn = $("#btnRefreshPenjualan");

                if (isLoading) {
                    $applyBtn
                        .prop("disabled", true)
                        .addClass("opacity-70 cursor-not-allowed")
                        .html('<i class="fa-solid fa-spinner fa-spin"></i> Memuat...');

                    $refreshBtn
                        .prop("disabled", true)
                        .addClass("opacity-70 cursor-not-allowed");

                    return;
                }

                $applyBtn
                    .prop("disabled", false)
                    .removeClass("opacity-70 cursor-not-allowed")
                    .html('<i class="fa-solid fa-magnifying-glass"></i> Terapkan Filter');

                $refreshBtn
                    .prop("disabled", false)
                    .removeClass("opacity-70 cursor-not-allowed");
            }

            function updateLastUpdated() {
                const now = new Date();
                const label = now.toLocaleString("id-ID", {
                    day: "2-digit",
                    month: "long",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit",
                });

                $("#penjualanLastUpdated").text("Diperbarui: " + label);
            }

            function stylingDataTable(tableId) {
                const wrapper = $(tableId + "_wrapper");

                wrapper.find("select").addClass(
                    "rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none"
                );

                wrapper.find('input[type="search"]').addClass(
                    "ml-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none"
                );

                wrapper.find(".dataTables_length, .dataTables_filter").addClass("text-sm text-slate-600");
                wrapper.find(".dataTables_info, .dataTables_paginate").addClass("text-sm text-slate-500");
            }

            function openDetailModal() {
                $modal.removeClass("hidden").addClass("flex");
                $("body").addClass("overflow-hidden");
            }

            function closeDetailModal() {
                $modal.removeClass("flex").addClass("hidden");
                $("body").removeClass("overflow-hidden");
            }

            function resetDetailModal() {
                $("#detailKodeTransaksi").text("-");
                $("#detailNamaPasien").text("-");
                $("#detailTanggalTransaksi").text("-");
                $("#detailStatusTransaksi").text("-");
                $("#detailMetodePembayaran").text("-");
                $("#detailGrandTotal").text("Rp 0");

                $detailItems.html(`
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                        Memuat detail transaksi...
                    </td>
                </tr>
            `);
            }

            function formatDiskonLabel(item) {
                if (!item.diskon_tipe || Number(item.diskon_nilai || 0) <= 0) {
                    return "-";
                }

                if (item.diskon_tipe === "persen") {
                    return `${item.diskon_nilai}%`;
                }

                return formatRupiah(item.diskon_nilai);
            }

            function renderDetailItems(items) {
                if (!items || !items.length) {
                    $detailItems.html(`
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                            Tidak ada detail transaksi.
                        </td>
                    </tr>
                `);
                    return;
                }

                const rows = items.map((item, index) => `
                <tr class="border-b border-slate-100">
                    <td class="px-4 py-3">${index + 1}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">${escapeHtml(item.nama_obat || "-")}</td>
                    <td class="px-4 py-3 text-center">${formatNumber(item.jumlah)}</td>
                    <td class="px-4 py-3 text-right">${formatRupiah(item.harga_satuan)}</td>
                    <td class="px-4 py-3 text-right">${formatRupiah(item.sub_total)}</td>
                    <td class="px-4 py-3 text-center">${escapeHtml(formatDiskonLabel(item))}</td>
                    <td class="px-4 py-3 text-right font-extrabold text-emerald-600">${formatRupiah(item.total_setelah_diskon)}</td>
                </tr>
            `).join("");

                $detailItems.html(rows);
            }

            function loadDetailPenjualan(url) {
                resetDetailModal();
                openDetailModal();

                $.getJSON(url)
                    .done(function(response) {
                        const data = response.data || {};

                        $("#detailKodeTransaksi").text(data.kode_transaksi || "-");
                        $("#detailNamaPasien").text(data.nama_pasien || "-");
                        $("#detailTanggalTransaksi").text(formatTanggalJam(data.tanggal_transaksi));
                        $("#detailStatusTransaksi").text(data.status || "-");
                        $("#detailMetodePembayaran").text(data.metode_pembayaran || "-");
                        $("#detailGrandTotal").text(formatRupiah(data.total_setelah_diskon || 0));

                        renderDetailItems(data.details || []);
                    })
                    .fail(function() {
                        $detailItems.html(`
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-rose-500">
                                Gagal memuat detail transaksi obat.
                            </td>
                        </tr>
                    `);
                    });
            }

            function initTable(rows) {
                if ($.fn.DataTable.isDataTable("#tablePenjualanObat")) {
                    penjualanTable.destroy();
                    $table.find("tbody").empty();
                }

                penjualanTable = $table.DataTable({
                    data: rows,
                    autoWidth: false,
                    responsive: true,
                    pageLength: 10,
                    destroy: true,
                    dom: "<'mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between'lf>t<'mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between'ip>",
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        zeroRecords: "Data penjualan obat tidak ditemukan",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                        infoEmpty: "Tidak ada data",
                        paginate: {
                            previous: "Sebelumnya",
                            next: "Berikutnya",
                        },
                    },
                    columns: [{
                            data: null,
                            className: "px-4 py-3 text-center",
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            },
                        },
                        {
                            data: "kode_transaksi",
                            className: "px-4 py-3",
                            render: function(data) {
                                return `<span class="font-bold text-slate-800">${escapeHtml(data || "-")}</span>`;
                            },
                        },
                        {
                            data: "nama_pasien",
                            className: "px-4 py-3 text-slate-700",
                            render: function(data) {
                                return escapeHtml(data || "-");
                            },
                        },
                        {
                            data: "tanggal_transaksi",
                            className: "px-4 py-3 text-slate-700",
                            render: function(data) {
                                return formatTanggalJam(data);
                            },
                        },
                        {
                            data: "status",
                            className: "px-4 py-3",
                            render: function(data) {
                                return buildStatusBadge(data);
                            },
                        },
                        {
                            data: "total",
                            className: "px-4 py-3 text-right",
                            render: function(data) {
                                return `<span class="font-extrabold text-slate-800">${formatRupiah(data)}</span>`;
                            },
                        },
                        {
                            data: "id",
                            className: "px-4 py-3 text-center",
                            orderable: false,
                            searchable: false,
                            render: function(data) {
                                const detailUrl = detailRouteTemplate.replace("__ID__", data);

                                return `
                                <button
                                    type="button"
                                    class="btn-detail-penjualan inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-blue-700"
                                    data-url="${detailUrl}">
                                    <i class="fa-solid fa-eye"></i>
                                    Detail
                                </button>
                            `;
                            },
                        },
                    ],
                });

                stylingDataTable("#tablePenjualanObat");
            }

            function updateSummary(meta = {}) {
                $("#penjualanTotalTransaksi").text(formatNumber(meta.total_transaksi));
                $("#penjualanTotalPemasukan").text(formatRupiah(meta.total_pemasukan));
                $("#penjualanFilterLabel").text(meta.filter_label || "-");
                $("#penjualanModeLabel").text(meta.mode_label || "-");

                $("#penjualanFilterBadge").html(`
                <i class="fa-solid fa-calendar-days"></i>
                ${escapeHtml(meta.mode_label || "-")} - ${escapeHtml(meta.filter_label || "-")}
            `);

                updateLastUpdated();
            }

            function loadPenjualanData() {
                setLoadingState(true);

                $.getJSON(dataUrl, getFilterParams())
                    .done(function(response) {
                        initTable(response.data || []);
                        updateSummary(response.meta || {});
                    })
                    .fail(function() {
                        alert("Gagal memuat data penjualan obat.");
                    })
                    .always(function() {
                        setLoadingState(false);
                    });
            }

            function resetFilter() {
                $("#filterPeriodePenjualan").val(defaultState.periode);
                $("#filterTanggalPenjualan").val(defaultState.tanggal);
                $("#filterMingguPenjualan").val(defaultState.minggu);
                $("#filterBulanPenjualan").val(defaultState.bulan);
                $("#filterTahunPenjualan").val(defaultState.tahun);

                toggleFilterInputs();
                loadPenjualanData();
            }

            $("#filterPeriodePenjualan").on("change", toggleFilterInputs);
            $("#btnApplyPenjualanFilter").on("click", loadPenjualanData);
            $("#btnResetPenjualanFilter").on("click", resetFilter);
            $("#btnRefreshPenjualan").on("click", loadPenjualanData);

            $(document).on("click", ".btn-detail-penjualan", function() {
                const url = $(this).data("url");
                loadDetailPenjualan(url);
            });

            $("#btnCloseModalDetailPenjualan").on("click", closeDetailModal);

            $modal.on("click", function(e) {
                if (e.target.id === "modalDetailPenjualan") {
                    closeDetailModal();
                }
            });

            $(document).on("keydown", function(e) {
                if (e.key === "Escape" && $modal.hasClass("flex")) {
                    closeDetailModal();
                }
            });

            toggleFilterInputs();
            loadPenjualanData();
        });
    </script>
</body>

</html>
