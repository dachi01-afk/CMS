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
                class="relative overflow-hidden rounded-[28px] bg-gradient-to-r from-slate-950 via-emerald-900 to-emerald-600 px-5 py-6 shadow-lg lg:px-6 lg:py-7">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -right-10 -top-10 h-36 w-36 rounded-full bg-white blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 h-28 w-28 rounded-full bg-white blur-2xl"></div>
                </div>

                <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.2em] text-white/90">
                            <i class="fa-solid fa-capsules"></i>
                            Penjualan Obat Hari Ini
                        </div>

                        <h1 class="text-3xl font-extrabold text-white lg:text-4xl">
                            Monitoring Penjualan Obat Apotek Hari Ini
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-7 text-white/90 lg:text-base">
                            Menampilkan seluruh transaksi penjualan obat yang terjadi hari ini secara realtime untuk
                            memantau jumlah transaksi dan total pemasukan farmasi.
                        </p>

                        <div
                            class="mt-5 inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white shadow-sm">
                            <i class="fa-regular fa-calendar-days"></i>
                            <span>{{ $todayLabel }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button id="btnRefreshPenjualan" type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-white/20">
                            <i class="fa-solid fa-rotate-right"></i>
                            Refresh Data
                        </button>

                        <a href="{{ route('farmasi.dashboard') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-white/20">
                            <i class="fa-solid fa-arrow-left"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                <div class="xl:col-span-4">
                    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Total Transaksi Hari Ini
                                </p>
                                <h3 id="penjualanTotalTransaksi" class="mt-2 text-4xl font-extrabold text-slate-900">
                                    0
                                </h3>
                                <p id="penjualanFilterLabel" class="mt-2 text-xs text-slate-400">
                                    {{ $todayLabel }}
                                </p>
                            </div>

                            <div
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                <i class="fa-solid fa-receipt text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4">
                    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Pemasukan Hari Ini
                                </p>
                                <h3 id="penjualanTotalPemasukan" class="mt-2 text-4xl font-extrabold text-emerald-600">
                                    Rp 0
                                </h3>
                                <p class="mt-2 text-xs text-slate-400">
                                    Akumulasi transaksi dengan status Sudah Bayar
                                </p>
                            </div>

                            <div
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                <i class="fa-solid fa-wallet text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4">
                    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Status Monitoring
                                </p>
                                <h3 class="mt-2 text-2xl font-extrabold text-slate-900">
                                    Hari Ini Saja
                                </h3>
                                <p class="mt-2 text-xs text-slate-400">
                                    Halaman ini hanya menampilkan transaksi penjualan obat di tanggal berjalan
                                </p>
                            </div>

                            <div
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                                <i class="fa-solid fa-clock text-lg"></i>
                            </div>
                        </div>

                        <div
                            class="mt-4 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700">
                            <i class="fa-solid fa-circle-check"></i>
                            Data Aktif Hari Ini
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">
                            Tabel Penjualan Obat Hari Ini
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Menampilkan daftar transaksi penjualan obat apotek yang tercatat pada hari ini.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700">
                            <i class="fa-solid fa-calendar-day"></i>
                            {{ $todayLabel }}
                        </span>

                        <span id="lastUpdatedBadge"
                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-bold text-slate-600">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            Belum diperbarui
                        </span>
                    </div>
                </div>

                <div id="penjualanEmptyState"
                    class="hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                        <i class="fa-solid fa-box-open text-xl"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-700">Belum Ada Penjualan Hari Ini</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Transaksi penjualan obat yang terjadi hari ini akan muncul pada tabel ini.
                    </p>
                </div>

                <div id="penjualanTableWrap" class="overflow-x-auto">
                    <table id="tablePenjualanObat" class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Kode Transaksi</th>
                                <th class="px-4 py-3">Pasien</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script>
        $(document).ready(function() {
            let penjualanTable = null;
            const formatter = new Intl.NumberFormat("id-ID");

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

            function formatTanggalJam(iso) {
                if (!iso) return "-";

                const date = new Date(iso);
                if (isNaN(date.getTime())) return iso;

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

            function stylingDataTable(tableId) {
                const wrapper = $(tableId + "_wrapper");

                wrapper.find("select").addClass(
                    "rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none"
                );

                wrapper.find('input[type="search"]').addClass(
                    "ml-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none"
                );

                wrapper.find(".dataTables_length, .dataTables_filter").addClass("text-sm text-slate-600");
                wrapper.find(".dataTables_info, .dataTables_paginate").addClass("text-sm text-slate-500 mt-3");
            }

            function setLoadingState(isLoading) {
                const btn = $("#btnRefreshPenjualan");

                if (isLoading) {
                    btn.prop("disabled", true)
                        .addClass("opacity-70 cursor-not-allowed")
                        .html('<i class="fa-solid fa-spinner fa-spin"></i> Memuat...');
                } else {
                    btn.prop("disabled", false)
                        .removeClass("opacity-70 cursor-not-allowed")
                        .html('<i class="fa-solid fa-rotate-right"></i> Refresh Data');
                }
            }

            function updateLastUpdated() {
                const now = new Date();
                const label = now.toLocaleString("id-ID", {
                    day: "2-digit",
                    month: "long",
                    year: "numeric",
                    hour: "2-digit",
                });

                $("#lastUpdatedBadge").html(`
                    <i class="fa-solid fa-arrows-rotate"></i>
                    Diperbarui ${escapeHtml(label)}
                `);
            }

            function toggleEmptyState(rows) {
                const isEmpty = !rows || rows.length === 0;

                if (isEmpty) {
                    $("#penjualanEmptyState").removeClass("hidden");
                    $("#penjualanTableWrap").addClass("hidden");
                } else {
                    $("#penjualanEmptyState").addClass("hidden");
                    $("#penjualanTableWrap").removeClass("hidden");
                }
            }

            function initTable(data) {
                if ($.fn.DataTable.isDataTable("#tablePenjualanObat")) {
                    penjualanTable.clear().destroy();
                    $("#tablePenjualanObat tbody").empty();
                }

                penjualanTable = $("#tablePenjualanObat").DataTable({
                    data: data,
                    autoWidth: false,
                    responsive: true,
                    pageLength: 10,
                    dom: "<'mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between'lf>t<'mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between'ip>",
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        zeroRecords: "Belum ada penjualan obat hari ini",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                        infoEmpty: "Tidak ada data",
                        paginate: {
                            previous: "Sebelumnya",
                            next: "Berikutnya",
                        },
                    },
                    columns: [{
                            data: null,
                            className: "px-4 py-3 text-slate-600",
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
                    ],
                });

                stylingDataTable("#tablePenjualanObat");
            }

            function loadPenjualanData() {
                setLoadingState(true);

                $.getJSON("{{ route('farmasi.penjualan.obat.hari.ini.data') }}", {
                        today_only: 1
                    })
                    .done(function(response) {
                        const rows = response.data || [];
                        const meta = response.meta || {};

                        toggleEmptyState(rows);

                        if (rows.length > 0) {
                            initTable(rows);
                        } else if ($.fn.DataTable.isDataTable("#tablePenjualanObat")) {
                            penjualanTable.clear().destroy();
                            $("#tablePenjualanObat tbody").empty();
                        }

                        $("#penjualanTotalTransaksi").text(formatNumber(meta.total_transaksi));
                        $("#penjualanTotalPemasukan").text(formatRupiah(meta.total_pemasukan));
                        $("#penjualanFilterLabel").text(meta.filter_label || "{{ $todayLabel }}");

                        updateLastUpdated();
                    })
                    .fail(function() {
                        alert("Gagal memuat data penjualan obat hari ini.");
                    })
                    .always(function() {
                        setLoadingState(false);
                    });
            }

            $("#btnRefreshPenjualan").on("click", function() {
                loadPenjualanData();
            });

            loadPenjualanData();
        });
    </script>
</body>

</html>
