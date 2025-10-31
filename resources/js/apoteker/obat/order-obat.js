import axios from "axios";
import { Modal } from "flowbite";
import $ from "jquery";

$(function () {
    var table = $("#penjualanObatTable").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/obat/get-data-penjualan-obat",
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
                data: "kode_transaksi",
                name: "kode_transaksi",
            },

            {
                data: "jumlah",
                name: "jumlah",
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

    $("#penjualan-obat-search-input").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#penjualan-obat-custom-info");
    const $paginate = $("#penjualan-obat-custom-paginate");
    const $perPage = $("#penjualan-obat-page-length");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );
        $paginate.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`
        );

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);
        if (end - start < maxVisible - 1)
            start = Math.max(end - maxVisible + 1, 1);

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage
                    ? "text-blue-600 bg-blue-50 border-blue-300 hover:bg-blue-100"
                    : "text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700";
            $paginate.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`
        );
    }

    $paginate.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;
        if ($link.attr("id") === "btnPrev") table.page("previous").draw("page");
        else if ($link.attr("id") === "btnNext")
            table.page("next").draw("page");
        else if ($link.hasClass("page-number"))
            table.page(parseInt($link.data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val())).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();
});

$(function () {
    const elementModal = document.getElementById("modalJualObat");
    const modal = elementModal ? new Modal(elementModal) : null;
    const $form = $("#form-penjualan-obat");

    const pasienDataDiv = document.getElementById("pasien_data");
    const searchInput = document.getElementById("search_pasien");
    const resultsDiv = document.getElementById("search_results");

    const searchObatInput = document.getElementById("search_obat");
    const obatResultsDiv = document.getElementById("obat_results");
    const selectedObatList = document.getElementById("selected_obat_list");

    function resetForm() {
        $form[0].reset();
        $form.find(".is-invalid").removeClass("is-invalid");
        $form.find(".text-danger").empty();
    }

    $("#btn-open-modal-penjualan-obat").on("click", function () {
        resetForm();
        if (modal) modal.show();

        searchInput.addEventListener("keyup", async () => {
            const query = searchInput.value.trim();
            if (query.length < 2) {
                resultsDiv.classList.add("hidden");
                return;
            }

            const response = await fetch(
                `/obat/search-data-pasien?query=${query}`
            );
            const data = await response.json();

            resultsDiv.innerHTML = "";
            if (data.length > 0) {
                resultsDiv.classList.remove("hidden");
                data.forEach((pasien) => {
                    const item = document.createElement("div");
                    item.className =
                        "px-4 py-2 hover:bg-indigo-100 cursor-pointer text-sm";
                    item.textContent = pasien.nama_pasien;
                    item.onclick = () => {
                        document.getElementById("pasien_id").value = pasien.id;
                        document.getElementById("nama_pasien").textContent =
                            pasien.nama_pasien;
                        document.getElementById("alamat_pasien").textContent =
                            pasien.alamat;
                        document.getElementById("jk_pasien").textContent =
                            pasien.jenis_kelamin;
                        pasienDataDiv.classList.remove("hidden");
                        resultsDiv.classList.add("hidden");
                        searchInput.value = pasien.nama_pasien;
                    };
                    resultsDiv.appendChild(item);
                });
            } else {
                resultsDiv.classList.remove("hidden");
                resultsDiv.innerHTML = `<div class="px-4 py-2 text-gray-500 text-sm">Tidak ditemukan</div>`;
            }
        });

        searchObatInput.addEventListener("keyup", async () => {
            const query = searchObatInput.value.trim();
            if (query.length < 2) {
                obatResultsDiv.classList.add("hidden");
                return;
            }

            const response = await fetch(
                `/obat/search-data-obat?query=${query}`
            );
            const data = await response.json();

            obatResultsDiv.innerHTML = "";
            if (data.length > 0) {
                obatResultsDiv.classList.remove("hidden");
                data.forEach((obat) => {
                    const item = document.createElement("div");
                    item.className =
                        "px-4 py-2 hover:bg-indigo-100 cursor-pointer text-sm";
                    item.textContent = `${obat.nama_obat} (Stok: ${obat.jumlah})`;
                    item.onclick = () => addObatToList(obat);
                    obatResultsDiv.appendChild(item);
                });
            } else {
                obatResultsDiv.classList.remove("hidden");
                obatResultsDiv.innerHTML = `<div class="px-4 py-2 text-gray-500 text-sm">Obat tidak ditemukan</div>`;
            }
        });

        function addObatToList(obat) {
            // Cek kalau obat sudah ada di daftar
            const existingRow = document.querySelector(
                `tr[data-obat-id='${obat.id}']`
            );
            if (existingRow) {
                alert("Obat ini sudah ditambahkan.");
                return;
            }

            const row = document.createElement("tr");
            row.setAttribute("data-obat-id", obat.id);
            row.innerHTML = `
        <td class="px-3 py-2">${obat.nama_obat}
            <input type="hidden" name="obat_id[]" value="${obat.id}">
        </td>
        <td class="px-3 py-2">${obat.jumlah}</td>
        <td class="px-3 py-2">
            <input type="number" name="jumlah[]" min="1" max="${obat.jumlah}" value="1"
                class="border border-gray-300 rounded p-1 w-16">
        </td>
        <td class="px-3 py-2 text-center">
            <button type="button" class="btn-remove text-red-500 hover:text-red-700">Hapus</button>
        </td>
    `;
            selectedObatList.appendChild(row);
            obatResultsDiv.classList.add("hidden");
            searchObatInput.value = "";
        }

        $(document).on("click", ".btn-remove", function () {
            $(this).closest("tr").remove();
        });
    });

    $("#btn-close-modal-penjualan-obat").on("click", function () {
        resetForm();
        if (modal) modal.hide();
    });

    $("#closeModalBtn").on("click", function () {
        resetForm();
        if (modal) modal.hide();
    });

    $form.on("submit", async function (e) {
        e.preventDefault();

        const obatIds = $("input[name='obat_id[]']")
            .map(function () {
                return $(this).val();
            })
            .get();

        const jumlahs = $("input[name='jumlah[]']")
            .map(function () {
                return $(this).val();
            })
            .get();

        const formData = {
            pasien_id: $("#pasien_id").val(),
            obat_id: obatIds,
            jumlah: jumlahs,
        };

        axios
            .post("/obat/pesan-obat", formData)
            .then((res) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: "Transaksi berhasil disimpan.",
                    timer: 2000,
                    showConfirmButton: false,
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch((err) => {
                console.error(err.response?.data || err);
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text:
                        err.response?.data?.message ||
                        "Terjadi kesalahan saat menyimpan transaksi.",
                });
            });
    });
});
