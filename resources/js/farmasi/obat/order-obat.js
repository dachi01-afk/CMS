import axios from "axios";
import { Modal } from "flowbite";
import $ from "jquery";

const rupiah = (angka = 0) =>
    Number(angka || 0).toLocaleString("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    });

$(function () {
    const elementModal = document.getElementById("modalJualObat");
    const modal = elementModal ? new Modal(elementModal) : null;

    const $form = $("#form-penjualan-obat");
    const $table = $("#penjualanObatTable");
    const $paginate = $("#penjualan-obat-custom-paginate");
    const $info = $("#penjualan-obat-custom-info");
    const $perPage = $("#penjualan-obat-page-length");
    const $searchInput = $("#penjualan-obat-search-input");

    let editMode = false;
    let selectedItems = [];

    const pasienDataDiv = document.getElementById("pasien_data");
    const searchPasienInput = document.getElementById("search_pasien");
    const pasienResultsDiv = document.getElementById("search_results");

    const searchObatInput = document.getElementById("search_obat");
    const obatResultsDiv = document.getElementById("obat_results");
    const selectedObatList = document.getElementById("selected_obat_list");

    const resepIdInput = document.getElementById("resep_id");
    const pasienIdInput = document.getElementById("pasien_id");
    const penjualanObatIdInput = document.getElementById("penjualan_obat_id");

    const badgeMode = document.getElementById("badge-mode-penjualan-obat");
    const modalTitle = document.getElementById("modal-title-penjualan-obat");
    const modalSubtitle = document.getElementById(
        "modal-subtitle-penjualan-obat",
    );
    const statusInfo = document.getElementById("transaction-status-info");
    const btnResetPasien = document.getElementById("btn-reset-pasien");

    function getHargaObat(obat) {
        return Number(
            obat?.harga_jual_obat ??
                obat?.harga_otc_obat ??
                obat?.total_harga ??
                obat?.harga_satuan ??
                0,
        );
    }

    function setModeTambah() {
        editMode = false;
        modalTitle.textContent = "Tambah Order Obat";
        modalSubtitle.textContent =
            "Pilih pasien, tambahkan obat, lalu simpan transaksi.";
        $("#btn-submit-penjualan-obat").text("Simpan Order");
        badgeMode.classList.add("hidden");
        statusInfo.innerHTML = `
            Status transaksi otomatis dibuat sebagai
            <span class="font-semibold">Belum Bayar</span>.
        `;
    }

    function setModeEdit() {
        editMode = true;
        modalTitle.textContent = "Edit Order Obat";
        modalSubtitle.textContent =
            "Perbarui pasien atau daftar obat, lalu simpan perubahan transaksi.";
        $("#btn-submit-penjualan-obat").text("Update Order");
        badgeMode.classList.remove("hidden");
        statusInfo.innerHTML = `
            Anda sedang mengubah data order obat yang masih
            <span class="font-semibold">Belum Bayar</span>.
        `;
    }

    function clearPasien() {
        pasienIdInput.value = "";
        resepIdInput.value = "";
        $("#tanggal_kunjungan").val("");
        searchPasienInput.value = "";
        $("#nama_pasien").text("");
        $("#alamat_pasien").text("");
        $("#jk_pasien").text("");
        $("#no_emr_pasien").text("");
        pasienDataDiv.classList.add("hidden");
        pasienResultsDiv.classList.add("hidden");
    }

    function fillPasien(pasien) {
        pasienIdInput.value = pasien.id ?? "";
        $("#nama_pasien").text(pasien.nama_pasien ?? "-");
        $("#alamat_pasien").text(pasien.alamat ?? "-");
        $("#jk_pasien").text(pasien.jenis_kelamin ?? "-");
        $("#no_emr_pasien").text(pasien.no_emr ?? "-");
        searchPasienInput.value = pasien.nama_pasien ?? "";
        pasienDataDiv.classList.remove("hidden");
        pasienResultsDiv.classList.add("hidden");
    }

    const table = $table.DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/order-obat/get-data-penjualan-obat",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "kode_transaksi", name: "kode_transaksi" },
            { data: "nama_pasien", name: "pasien.nama_pasien" },
            {
                data: "jumlah_item",
                name: "jumlah_item",
                render: function (data) {
                    return `<span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">${data} item</span>`;
                },
            },
            {
                data: "total_tagihan",
                name: "total_tagihan",
                render: function (data) {
                    return `<span class="font-semibold text-slate-800">${rupiah(data)}</span>`;
                },
            },
            {
                data: "status",
                name: "status",
                render: function (data) {
                    const badge =
                        data === "Sudah Bayar"
                            ? "bg-emerald-100 text-emerald-700"
                            : "bg-amber-100 text-amber-700";

                    return `<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${badge}">${data}</span>`;
                },
            },
            {
                data: "tanggal_transaksi",
                name: "tanggal_transaksi",
                render: function (data) {
                    if (!data) return "-";
                    const date = new Date(data);
                    return date.toLocaleString("id-ID", {
                        timeZone: "Asia/Jakarta",
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                    });
                },
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center",
            },
        ],
        dom: "t",
        rowCallback: function (row) {
            $(row).addClass(
                "border-b border-slate-200 bg-white hover:bg-slate-50",
            );
            $("td", row).addClass("px-5 py-4 text-slate-700 align-top");
        },
    });

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.recordsDisplay ? info.start + 1 : 0}–${info.end} dari ${info.recordsDisplay} data`,
        );

        $paginate.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 pointer-events-none" : "";

        $paginate.append(`
            <li>
                <a href="#" id="btnPrev" class="flex h-9 items-center justify-center rounded-l-xl border border-slate-300 bg-white px-4 text-slate-600 hover:bg-slate-100 ${prevDisabled}">
                    Prev
                </a>
            </li>
        `);

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);

        if (end - start < maxVisible - 1) {
            start = Math.max(end - maxVisible + 1, 1);
        }

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage
                    ? "bg-sky-600 text-white border-sky-600"
                    : "bg-white text-slate-600 border-slate-300 hover:bg-slate-100";

            $paginate.append(`
                <li>
                    <a href="#" class="page-number flex h-9 items-center justify-center border px-4 ${active}" data-page="${i}">
                        ${i}
                    </a>
                </li>
            `);
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 pointer-events-none" : "";

        $paginate.append(`
            <li>
                <a href="#" id="btnNext" class="flex h-9 items-center justify-center rounded-r-xl border border-slate-300 bg-white px-4 text-slate-600 hover:bg-slate-100 ${nextDisabled}">
                    Next
                </a>
            </li>
        `);
    }

    $paginate.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);

        if ($link.attr("id") === "btnPrev") {
            table.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            table.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            table.page(parseInt($link.data("page")) - 1).draw("page");
        }
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val())).draw();
    });

    $searchInput.on("keyup", function () {
        table.search(this.value).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();

    function debounce(fn, ms = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), ms);
        };
    }

    function resetForm() {
        selectedItems = [];
        $form[0].reset();

        penjualanObatIdInput.value = "";
        clearPasien();

        selectedObatList.innerHTML = "";
        obatResultsDiv.classList.add("hidden");
        searchObatInput.value = "";

        btnResetPasien.classList.add("hidden");

        setModeTambah();
        renderSelectedItems();
        updateSummary();
    }

    function updateSummary() {
        const totalItem = selectedItems.reduce(
            (sum, item) => sum + Number(item.jumlah || 0),
            0,
        );

        const grandTotal = selectedItems.reduce((sum, item) => {
            const harga = Number(item.harga_jual_obat || 0);
            const jumlah = Number(item.jumlah || 0);
            return sum + harga * jumlah;
        }, 0);

        $("#summary-total-item").text(totalItem);
        $("#summary-grand-total").text(rupiah(grandTotal));
    }

    function renderSelectedItems() {
        selectedObatList.innerHTML = "";

        if (selectedItems.length === 0) {
            selectedObatList.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-4 text-center text-sm text-slate-500">
                        Belum ada obat dipilih
                    </td>
                </tr>
            `;
            updateSummary();
            return;
        }

        selectedItems.forEach((item, index) => {
            const subtotal =
                Number(item.jumlah || 0) * Number(item.harga_jual_obat || 0);

            const row = document.createElement("tr");
            row.className = "border-t border-slate-200";
            row.innerHTML = `
                <td class="px-4 py-3">
                    <div class="font-medium text-slate-800">${item.nama_obat}</div>
                </td>
                <td class="px-4 py-3">${rupiah(item.harga_jual_obat || 0)}</td>
                <td class="px-4 py-3">${item.stok}</td>
                <td class="px-4 py-3">
                    <input type="number"
                        min="1"
                        max="${item.stok}"
                        value="${item.jumlah}"
                        data-index="${index}"
                        class="qty-input w-24 rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                </td>
                <td class="px-4 py-3 font-semibold text-slate-800">${rupiah(subtotal)}</td>
                <td class="px-4 py-3 text-center">
                    <button type="button" data-index="${index}"
                        class="btn-remove-item rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">
                        Hapus
                    </button>
                </td>
            `;
            selectedObatList.appendChild(row);
        });

        updateSummary();
    }

    function addObatToList(obat) {
        const found = selectedItems.find(
            (item) => Number(item.obat_id) === Number(obat.id),
        );

        if (found) {
            Swal.fire({
                icon: "warning",
                title: "Obat sudah ada",
                text: "Silakan ubah qty pada daftar obat.",
            });
            return;
        }

        const stok = Number(obat.jumlah ?? 0);

        if (stok < 1) {
            Swal.fire({
                icon: "warning",
                title: "Stok habis",
                text: "Obat ini tidak memiliki stok yang cukup.",
            });
            return;
        }

        selectedItems.push({
            obat_id: obat.id,
            nama_obat: obat.nama_obat,
            harga_jual_obat: getHargaObat(obat),
            stok,
            jumlah: 1,
        });

        renderSelectedItems();
        obatResultsDiv.classList.add("hidden");
        searchObatInput.value = "";
    }

    async function fetchResepAktif(pasienId) {
        try {
            const res = await axios.get(`/farmasi/order-obat/resep-aktif`, {
                params: { pasien_id: pasienId },
            });

            if (res.data?.resep_id) {
                resepIdInput.value = res.data.resep_id;
                $("#tanggal_kunjungan").val(res.data.tanggal_kunjungan ?? "");
            } else {
                resepIdInput.value = "";
                $("#tanggal_kunjungan").val("");
            }
        } catch (error) {
            console.error("Gagal mengambil resep aktif", error);
        }
    }

    const onSearchPasien = debounce(async () => {
        const query = searchPasienInput.value.trim();

        if (query.length < 2) {
            pasienResultsDiv.classList.add("hidden");
            return;
        }

        try {
            const res = await axios.get(
                `/farmasi/order-obat/search-data-pasien`,
                {
                    params: { query },
                },
            );

            const data = res.data || [];
            pasienResultsDiv.innerHTML = "";
            pasienResultsDiv.classList.remove("hidden");

            if (!data.length) {
                pasienResultsDiv.innerHTML = `<div class="px-4 py-3 text-sm text-slate-500">Pasien tidak ditemukan</div>`;
                return;
            }

            data.forEach((pasien) => {
                const item = document.createElement("button");
                item.type = "button";
                item.className =
                    "block w-full border-b border-slate-100 px-4 py-3 text-left text-sm hover:bg-sky-50";
                item.innerHTML = `
                    <div class="font-medium text-slate-800">${pasien.nama_pasien}</div>
                    <div class="text-xs text-slate-500">${pasien.alamat ?? "-"}</div>
                `;

                item.onclick = async () => {
                    fillPasien(pasien);
                    btnResetPasien.classList.remove("hidden");
                    await fetchResepAktif(pasien.id);
                };

                pasienResultsDiv.appendChild(item);
            });
        } catch (error) {
            console.error(error);
        }
    }, 300);

    const onSearchObat = debounce(async () => {
        const query = searchObatInput.value.trim();

        if (query.length < 2) {
            obatResultsDiv.classList.add("hidden");
            return;
        }

        try {
            const res = await axios.get(
                `/farmasi/order-obat/search-data-obat`,
                {
                    params: { query },
                },
            );

            const data = res.data || [];
            obatResultsDiv.innerHTML = "";
            obatResultsDiv.classList.remove("hidden");

            if (!data.length) {
                obatResultsDiv.innerHTML = `<div class="px-4 py-3 text-sm text-slate-500">Obat tidak ditemukan</div>`;
                return;
            }

            data.forEach((obat) => {
                const item = document.createElement("button");
                item.type = "button";
                item.className =
                    "block w-full border-b border-slate-100 px-4 py-3 text-left text-sm hover:bg-sky-50";

                const hargaObat = getHargaObat(obat);

                item.innerHTML = `
                    <div class="font-medium text-slate-800">${obat.nama_obat}</div>
                    <div class="text-xs text-slate-500">
                        Stok: ${obat.jumlah} | Harga: ${rupiah(hargaObat)}
                    </div>
                `;

                item.onclick = () => addObatToList(obat);
                obatResultsDiv.appendChild(item);
            });
        } catch (error) {
            console.error(error);
        }
    }, 300);

    searchPasienInput.addEventListener("keyup", onSearchPasien);
    searchObatInput.addEventListener("keyup", onSearchObat);

    btnResetPasien.addEventListener("click", () => {
        clearPasien();
        btnResetPasien.classList.add("hidden");
    });

    $(document).on("input", ".qty-input", function () {
        const index = Number($(this).data("index"));
        let qty = Number($(this).val());

        if (Number.isNaN(qty) || qty < 1) qty = 1;
        if (qty > selectedItems[index].stok) qty = selectedItems[index].stok;

        selectedItems[index].jumlah = qty;
        renderSelectedItems();
    });

    $(document).on("click", ".btn-remove-item", function () {
        const index = Number($(this).data("index"));
        selectedItems.splice(index, 1);
        renderSelectedItems();
    });

    $("#btn-open-modal-penjualan-obat").on("click", function () {
        resetForm();
        if (modal) modal.show();
    });

    $("#btn-close-modal-penjualan-obat, #closeModalBtn").on(
        "click",
        function () {
            resetForm();
            if (modal) modal.hide();
        },
    );

    $(document).on("click", ".btn-edit-order", async function () {
        const id = $(this).data("id");

        try {
            resetForm();
            setModeEdit();

            const res = await axios.get(`/farmasi/order-obat/order/${id}`);
            const data = res.data.data;

            penjualanObatIdInput.value = data.id;
            pasienIdInput.value = data.pasien_id;
            resepIdInput.value = data.resep_id ?? "";

            fillPasien({
                id: data.pasien_id,
                nama_pasien: data.pasien?.nama_pasien ?? "-",
                alamat: data.pasien?.alamat ?? "-",
                jenis_kelamin: data.pasien?.jenis_kelamin ?? "-",
                no_emr: data.pasien?.no_emr ?? "-",
            });

            btnResetPasien.classList.remove("hidden");

            selectedItems = (
                data.penjualan_obat_detail ||
                data.details ||
                []
            ).map((item) => ({
                obat_id: item.obat_id,
                nama_obat: item.obat?.nama_obat ?? "-",
                harga_jual_obat: getHargaObat(item),
                stok: Number(item.obat?.jumlah ?? 0) + Number(item.jumlah || 0),
                jumlah: Number(item.jumlah || 0),
            }));

            renderSelectedItems();

            if (modal) modal.show();
        } catch (error) {
            console.error(error);
            Swal.fire("Gagal", "Data order tidak dapat dimuat", "error");
        }
    });

    $(document).on("click", ".btn-delete-order", async function () {
        const id = $(this).data("id");

        const confirm = await Swal.fire({
            title: "Hapus transaksi?",
            text: "Stok obat akan dikembalikan seperti semula.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus",
            cancelButtonText: "Batal",
        });

        if (!confirm.isConfirmed) return;

        try {
            await axios.delete(`/farmasi/order-obat/order/${id}`);
            Swal.fire("Berhasil", "Transaksi berhasil dihapus", "success");
            table.ajax.reload(null, false);
        } catch (error) {
            Swal.fire(
                "Gagal",
                error.response?.data?.message || "Gagal menghapus transaksi",
                "error",
            );
        }
    });

    $form.on("submit", async function (e) {
        e.preventDefault();

        if (!pasienIdInput.value) {
            Swal.fire("Validasi", "Pasien wajib dipilih", "warning");
            return;
        }

        if (!selectedItems.length) {
            Swal.fire("Validasi", "Minimal pilih 1 obat", "warning");
            return;
        }

        const invalidQty = selectedItems.find(
            (item) =>
                Number(item.jumlah) < 1 ||
                Number(item.jumlah) > Number(item.stok),
        );

        if (invalidQty) {
            Swal.fire(
                "Validasi",
                `Qty obat ${invalidQty.nama_obat} tidak valid`,
                "warning",
            );
            return;
        }

        const payload = {
            pasien_id: pasienIdInput.value,
            resep_id: resepIdInput.value || null,
            items: selectedItems.map((item) => ({
                obat_id: item.obat_id,
                jumlah: item.jumlah,
            })),
        };

        try {
            if (editMode) {
                await axios.put(
                    `/farmasi/order-obat/order/${penjualanObatIdInput.value}`,
                    payload,
                );
            } else {
                await axios.post(`/farmasi/order-obat/pesan-obat`, payload);
            }

            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: editMode
                    ? "Order obat berhasil diperbarui"
                    : "Order obat berhasil disimpan",
                timer: 1600,
                showConfirmButton: false,
            });

            resetForm();
            if (modal) modal.hide();
            table.ajax.reload(null, false);
        } catch (error) {
            console.error(error.response?.data || error);
            Swal.fire(
                "Gagal",
                error.response?.data?.message ||
                    "Terjadi kesalahan saat menyimpan data",
                "error",
            );
        }
    });
});
