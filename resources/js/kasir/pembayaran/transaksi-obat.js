import axios from "axios";
import $ from "jquery";

$(function () {
    var table = $("#transaksiObatTable").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/kasir/get-data-transaksi-obat",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },

            {
                data: "nama_pasien",
                name: "nama_pasien",
            },

            {
                data: "nama_obat",
                name: "nama_obat",
            },

            {
                data: "dosis",
                name: "dosis",
                render: function (data, type, row) {
                    if (!data) return "-";
                    return data + " mg";
                },
            },

            {
                data: "jumlah",
                name: "jumlah",
                render: function (data, type, row) {
                    if (!data) return "-";
                    return data + " capsul";
                },
            },

            {
                data: "sub_total",
                name: "sub_total",
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
                data: "kode_transaksi",
                name: "kode_transaksi",
            },

            {
                data: "tanggal_transaksi",
                name: "tanggal_transaksi",
                render: function (data) {
                    if (!data) return "-";
                    const date = new Date(data);
                    const waktuIndonesia = date.toLocaleString("id-ID", {
                        timeZone: "Asia/Jakarta",
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                    });

                    return waktuIndonesia;
                },
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
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });
});

$(document).on("click", ".bayarSekarang", function () {
    const url = $(this).data("url");
    window.location.href = url;
});
