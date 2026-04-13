import $ from "jquery";

$(function () {
    const table = $("#tabel-belum-approve-diskon").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: {
            url: "/super-admin/approve-diskon-penjualan-obat/get-data-belum-approve",
            type: "GET",
        },

        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "kode_transaksi", name: "kode_transaksi" },
            { data: "requested_by", name: "requested_by" },
            { data: "approved_by", name: "approved_by" },
            {
                data: "status_badge",
                name: "status",
                orderable: false,
                searchable: true,
            },
            { data: "reason", name: "reason" },
            {
                data: "approved_at",
                name: "approved_at",
                orderable: false,
                searchable: false,
            },
            {
                data: "diskon_items_detail",
                name: "diskon_items",
                orderable: false,
                searchable: false,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center whitespace-nowrap",
            },
        ],
        dom: "t",
        rowCallback: function (row) {
            $(row).addClass(
                "bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/40",
            );
            $("td", row).addClass("px-6 py-4 align-top");
        },
    });

    // =========================================================
    // Helper
    // =========================================================
    function getCsrfToken() {
        return (
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") || ""
        );
    }

    function setButtonLoading($btn, isLoading, loadingText = "Memproses...") {
        if (!$btn || !$btn.length) return;

        if (isLoading) {
            $btn.data("original-html", $btn.html());
            $btn.prop("disabled", true);
            $btn.addClass("opacity-70 cursor-not-allowed");
            $btn.html(`
                <i class="fa-solid fa-spinner fa-spin"></i>
                ${loadingText}
            `);
        } else {
            const originalHtml = $btn.data("original-html");
            $btn.prop("disabled", false);
            $btn.removeClass("opacity-70 cursor-not-allowed");
            if (originalHtml) {
                $btn.html(originalHtml);
            }
        }
    }

    async function sendRequest(url, method = "POST", body = null) {
        const options = {
            method,
            credentials: "same-origin",
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": getCsrfToken(),
            },
        };

        if (body !== null) {
            options.headers["Content-Type"] = "application/json";
            options.body = JSON.stringify(body);
        }

        const response = await fetch(url, options);

        let json = null;
        try {
            json = await response.json();
        } catch (err) {
            json = null;
        }

        if (!response.ok) {
            throw new Error(
                json?.message ||
                    `HTTP ${response.status} ${response.statusText}`,
            );
        }

        return json;
    }

    // =========================================================
    // 🔎 Search input
    // =========================================================
    $("#layanan-searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    // =========================================================
    // page length select + custom pagination
    // =========================================================
    const $info = $("#layanan-customInfo");
    const $pagination = $("#layanan-customPagination");
    const $perPage = $("#layanan-pageLength");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
        );

        $pagination.empty();

        const prevDisabled =
            currentPage === 1
                ? "opacity-50 cursor-not-allowed pointer-events-none"
                : "";

        $pagination.append(`
            <li>
                <a href="#" id="btnPrev"
                   class="flex items-center justify-center px-3 h-9 text-slate-600 dark:text-slate-200 bg-white dark:bg-slate-700 border-r border-slate-200 dark:border-slate-600 ${prevDisabled}">
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
                    ? "bg-sky-50 text-sky-700 dark:bg-slate-600 dark:text-white font-bold"
                    : "bg-white text-slate-600 dark:bg-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600";

            $pagination.append(`
                <li>
                    <a href="#" class="page-number flex items-center justify-center px-3 h-9 border-r border-slate-200 dark:border-slate-600 ${active}"
                       data-page="${i}">
                       ${i}
                    </a>
                </li>
            `);
        }

        const nextDisabled =
            currentPage === totalPages
                ? "opacity-50 cursor-not-allowed pointer-events-none"
                : "";

        $pagination.append(`
            <li>
                <a href="#" id="btnNext"
                   class="flex items-center justify-center px-3 h-9 text-slate-600 dark:text-slate-200 bg-white dark:bg-slate-700 ${nextDisabled}">
                   Next
                </a>
            </li>
        `);
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);

        if ($link.hasClass("pointer-events-none")) return;

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

    table.on("draw", updatePagination);
    updatePagination();

    // =========================================================
    // ✅ MODAL DETAIL (HTML) — JS hanya isi data
    // =========================================================
    const modal = document.getElementById("modalDetailDiskon");
    const modalError = document.getElementById("modalDetailDiskonError");
    const modalLoading = document.getElementById("modalDetailDiskonLoading");

    const elNamaPasien = document.getElementById("modalNamaPasien");
    const elKode = document.getElementById("modalKodeTransaksi");
    const elRequester = document.getElementById("modalRequester");
    const elHash = document.getElementById("modalDiskonHash");

    const reasonWrap = document.getElementById("modalReasonWrap");
    const elReason = document.getElementById("modalReason");

    const badgeCount = document.getElementById("modalBadgeCount");
    const badgeTotal = document.getElementById("modalBadgeTotal");
    const badgePotongan = document.getElementById("modalBadgePotongan");
    const badgeAfter = document.getElementById("modalBadgeAfter");

    const tbody = document.getElementById("modalItemsBody");

    function openModal() {
        if (!modal) return;
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.documentElement.style.overflow = "hidden";
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        document.documentElement.style.overflow = "";
    }

    function setLoading(isLoading) {
        if (modalLoading) modalLoading.classList.toggle("hidden", !isLoading);
    }

    function setError(msg) {
        if (!modalError) return;

        if (!msg) {
            modalError.classList.add("hidden");
            modalError.textContent = "";
            return;
        }

        modalError.textContent = msg;
        modalError.classList.remove("hidden");
    }

    function clearItems() {
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    Belum ada data.
                </td>
            </tr>
        `;
    }

    function jenisBadge(jenis) {
        const span = document.createElement("span");
        span.className =
            "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1";

        const map = {
            OBAT: "bg-indigo-50 text-indigo-700 ring-indigo-200",
            LAYANAN: "bg-amber-50 text-amber-800 ring-amber-200",
            LAB: "bg-cyan-50 text-cyan-700 ring-cyan-200",
            RADIOLOGI: "bg-rose-50 text-rose-700 ring-rose-200",
            LAINNYA: "bg-slate-100 text-slate-700 ring-slate-200",
        };

        span.className += " " + (map[jenis] || map.LAINNYA);
        span.textContent = jenis || "LAINNYA";

        return span;
    }

    function renderItems(items) {
        if (!tbody) return;

        tbody.innerHTML = "";

        if (!items || items.length === 0) {
            clearItems();
            return;
        }

        items.forEach((it) => {
            const tr = document.createElement("tr");
            tr.className = "border-b border-slate-200 dark:border-slate-700";

            const tdJenis = document.createElement("td");
            tdJenis.className = "px-4 py-3";
            tdJenis.appendChild(jenisBadge(it.jenis));
            tr.appendChild(tdJenis);

            const tdItem = document.createElement("td");
            tdItem.className = "px-4 py-3";

            const divName = document.createElement("div");
            divName.className =
                "font-semibold text-slate-800 dark:text-slate-100 break-words";
            divName.textContent = it.nama_item || "-";

            const divMeta = document.createElement("div");
            divMeta.className =
                "text-[11px] text-slate-500 dark:text-slate-400";
            divMeta.textContent = `detail_id: #${it.detail_id}`;

            tdItem.appendChild(divName);
            tdItem.appendChild(divMeta);
            tr.appendChild(tdItem);

            const tdQty = document.createElement("td");
            tdQty.className = "px-4 py-3 text-right";
            tdQty.textContent = `x${it.qty}`;
            tr.appendChild(tdQty);

            const tdHarga = document.createElement("td");
            tdHarga.className = "px-4 py-3 text-right";
            tdHarga.textContent = it.harga_rp || "Rp 0";
            tr.appendChild(tdHarga);

            const tdSub = document.createElement("td");
            tdSub.className = "px-4 py-3 text-right";
            tdSub.textContent = it.subtotal_rp || "Rp 0";
            tr.appendChild(tdSub);

            const tdPersen = document.createElement("td");
            tdPersen.className = "px-4 py-3 text-right font-semibold";
            tdPersen.textContent = `${it.persen}%`;
            tr.appendChild(tdPersen);

            const tdPot = document.createElement("td");
            tdPot.className =
                "px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-200";
            tdPot.textContent = `- ${it.potongan_rp || "Rp 0"}`;
            tr.appendChild(tdPot);

            const tdTotal = document.createElement("td");
            tdTotal.className =
                "px-4 py-3 text-right font-bold text-slate-900 dark:text-white";
            tdTotal.textContent = it.total_rp || "Rp 0";
            tr.appendChild(tdTotal);

            tbody.appendChild(tr);
        });
    }

    function fillModal(payload) {
        const totals = payload?.totals || {};
        const items = payload?.items || [];

        if (elNamaPasien) {
            elNamaPasien.textContent = payload?.nama_pasien || "-";
        }

        if (elKode) {
            elKode.textContent = payload?.kode_transaksi || "-";
        }

        if (elRequester) {
            elRequester.textContent = payload?.requested_by || "-";
        }

        if (elHash) {
            elHash.textContent = payload?.diskon_hash || "-";
        }

        const reason = (payload?.reason || "").trim();

        if (reasonWrap && elReason) {
            if (reason) {
                reasonWrap.classList.remove("hidden");
                elReason.textContent = reason;
            } else {
                reasonWrap.classList.add("hidden");
                elReason.textContent = "";
            }
        }

        if (badgeCount) {
            badgeCount.textContent = `${totals.item_count || 0} item`;
        }

        if (badgeTotal) {
            badgeTotal.textContent = `Total: ${totals.total_base_rp || "Rp 0"}`;
        }

        if (badgePotongan) {
            badgePotongan.textContent = `Potongan: ${totals.total_diskon_rp || "Rp 0"}`;
        }

        if (badgeAfter) {
            badgeAfter.textContent = `Setelah: ${totals.total_after_rp || "Rp 0"}`;
        }

        renderItems(items);
    }

    document
        .querySelectorAll('[data-close-modal="detail-diskon"]')
        .forEach((btn) => {
            btn.addEventListener("click", closeModal);
        });

    if (modal) {
        modal.addEventListener("click", (ev) => {
            if (ev.target === modal) closeModal();
        });
    }

    document.addEventListener("keydown", (ev) => {
        if (ev.key === "Escape") closeModal();
    });

    // =========================================================
    // ✅ CLICK LIHAT DETAIL ITEM
    // =========================================================
    $("#tabel-belum-approve-diskon").on(
        "click",
        ".btn-lihat-detail-item",
        async function () {
            const approvalId = $(this).data("approval-id");
            const detailUrl = $(this).data("detail-url");
            if (!approvalId || !detailUrl) return;

            setError(null);
            setLoading(true);
            clearItems();
            openModal();

            try {
                const res = await fetch(detailUrl, {
                    method: "GET",
                    credentials: "same-origin",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                const json = await res.json();

                if (!json?.success) {
                    setError(json?.message || "Gagal mengambil detail item.");
                    setLoading(false);
                    return;
                }

                fillModal(json.data || {});
                setLoading(false);
            } catch (err) {
                console.error(err);
                setError("Tidak dapat terhubung ke server.");
                setLoading(false);
            }
        },
    );

    // =========================================================
    // ✅ CLICK APPROVE
    // =========================================================
    $("#tabel-belum-approve-diskon").on(
        "click",
        ".btn-approve",
        async function () {
            const $btn = $(this);
            const approvalId = $btn.data("approval-id");
            const approveUrl = $btn.data("approve-url");

            if (!approvalId || !approveUrl) {
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Data approve tidak valid.",
                    confirmButtonText: "Tutup",
                });
                return;
            }

            const result = await Swal.fire({
                icon: "question",
                title: "Approve Pembayaran?",
                text: "Apakah Anda benar ingin approve pengajuan diskon ini?",
                showCancelButton: true,
                confirmButtonText: "Ya, Approve",
                cancelButtonText: "Batal",
                reverseButtons: true,
                focusCancel: true,
                allowOutsideClick: false,
                buttonsStyling: false,
                customClass: {
                    popup: "rounded-2xl",
                    actions: "flex items-center justify-center gap-3 mt-4",
                    confirmButton:
                        "inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white font-semibold hover:bg-emerald-700",
                    cancelButton:
                        "inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-slate-700 font-semibold hover:bg-slate-300",
                },
            });

            if (!result.isConfirmed) return;

            try {
                setButtonLoading($btn, true, "Approving...");

                Swal.fire({
                    title: "Memproses...",
                    text: "Sedang mengapprove pengajuan diskon.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                const json = await sendRequest(approveUrl, "POST");

                Swal.close();

                if (!json?.success) {
                    await Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: json?.message || "Gagal approve data.",
                        confirmButtonText: "Tutup",
                    });
                    return;
                }

                await Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: json?.message || "Data berhasil di-approve.",
                    confirmButtonText: "OK",
                });

                table.ajax.reload(null, false);
            } catch (err) {
                console.error(err);
                Swal.close();

                await Swal.fire({
                    icon: "error",
                    title: "Terjadi Kesalahan",
                    text: err.message || "Tidak dapat terhubung ke server.",
                    confirmButtonText: "Tutup",
                });
            } finally {
                setButtonLoading($btn, false);
            }
        },
    );

    // =========================================================
    // ✅ CLICK REJECT
    // =========================================================
    $("#tabel-belum-approve-diskon").on(
        "click",
        ".btn-reject",
        async function () {
            const $btn = $(this);
            const approvalId = $btn.data("approval-id");
            const rejectUrl = $btn.data("reject-url");

            if (!approvalId || !rejectUrl) {
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Data reject tidak valid.",
                    confirmButtonText: "Tutup",
                });
                return;
            }

            const result = await Swal.fire({
                icon: "warning",
                title: "Reject Pengajuan?",
                text: `Masukkan alasan penolakan untuk pengajuan #${approvalId}.`,
                input: "textarea",
                inputLabel: "Alasan Penolakan",
                inputPlaceholder: "Tulis alasan penolakan di sini...",
                inputAttributes: {
                    "aria-label": "Alasan penolakan",
                    rows: 4,
                },
                showCancelButton: true,
                confirmButtonText: "Ya, Reject",
                cancelButtonText: "Batal",
                reverseButtons: true,
                focusCancel: true,
                allowOutsideClick: false,
                buttonsStyling: false,
                customClass: {
                    popup: "rounded-2xl",
                    actions: "flex items-center justify-center gap-3 mt-4",
                    confirmButton:
                        "inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-white font-semibold hover:bg-red-700",
                    cancelButton:
                        "inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-slate-700 font-semibold hover:bg-slate-300",
                },
                preConfirm: (value) => {
                    const trimmed = (value || "").trim();

                    if (!trimmed) {
                        Swal.showValidationMessage(
                            "Alasan penolakan wajib diisi.",
                        );
                        return false;
                    }

                    return trimmed;
                },
            });

            if (!result.isConfirmed) return;

            const rejectionNote = result.value;

            try {
                setButtonLoading($btn, true, "Rejecting...");

                Swal.fire({
                    title: "Memproses...",
                    text: "Sedang menolak pengajuan diskon.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                const json = await sendRequest(rejectUrl, "POST", {
                    rejection_note: rejectionNote,
                });

                Swal.close();

                if (!json?.success) {
                    await Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: json?.message || "Gagal reject data.",
                        confirmButtonText: "Tutup",
                    });
                    return;
                }

                await Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: json?.message || "Data berhasil di-reject.",
                    confirmButtonText: "OK",
                });

                table.ajax.reload(null, false);
            } catch (err) {
                console.error(err);
                Swal.close();

                await Swal.fire({
                    icon: "error",
                    title: "Terjadi Kesalahan",
                    text: err.message || "Tidak dapat terhubung ke server.",
                    confirmButtonText: "Tutup",
                });
            } finally {
                setButtonLoading($btn, false);
            }
        },
    );
});
