import axios from "axios";
import $ from "jquery";

$(function () {
    const table = $("#riwayatTransaksiObatTable").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/kasir/riwayat-transaksi-obat/get-data",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },

            { data: "kode_transaksi", name: "kode_transaksi" },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "tanggal_pembayaran", name: "tanggal_pembayaran" },
            {
                data: "total_tagihan",
                name: "total_tagihan",
                render: function (data) {
                    if (!data) return "-";
                    const n = Number(data) || 0;
                    return n.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                        minimumFractionDigits: 0,
                    });
                },
            },
            {
                data: "diskon_nilai",
                name: "diskon_nilai",
                render: function (data) {
                    if (!data) return "-";
                    const n = Number(data) || 0;
                    return n.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                        minimumFractionDigits: 0,
                    });
                },
            },
            {
                data: "total_setelah_diskon",
                name: "total_setelah_diskon",
                render: function (data) {
                    if (!data) return;
                    const formatRupiah = Number(data).toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                        minimumFractionDigits: 0,
                    });
                    return formatRupiah;
                },
            },

            {
                data: "metode_pembayaran",
                name: "metode_pembayaran",
            },

            {
                data: "status",
                name: "status",
            },

            {
                data: "bukti_pembayaran",
                name: "bukti_pembayaran",
                orderable: false,
                searchable: false,
            },

            {
                data: "action",
                name: "action",
                searchable: false,
                orderable: false,
                className: "text-center whitespace-nowrap",
            },
        ],

        dom: "t",
        rowCallback: function (row, data) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });
});

$(document).on("click", ".bayarSekarang", function () {
    const url = $(this).data("url");
    window.location.href = url;
});
