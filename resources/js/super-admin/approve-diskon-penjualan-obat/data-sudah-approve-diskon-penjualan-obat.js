import $, { ajax } from "jquery";

let table;

$(function () {
    const url =
        "/super-admin/approve-diskon-penjualan-obat/get-data-sudah-approve";
    table = $("#tabel-sudah-approve-diskon-order-obat").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        paging: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: { url: url, type: "GET" },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "kode_transaksi", name: "kode_transaksi" },
            { data: "request", name: "request" },
            { data: "approve", name: "approve" },
            {
                data: "status",
                name: "status",
                orderable: false,
                searchable: false,
            },
            { data: "reason", name: "reason" },
            {
                data: "approved_at",
                name: "approved_at",
                orderable: false,
                searchable: false,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
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
    // 🔎 Search input
    // =========================================================
    $("#sudahApprove-searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    // =========================================================
    // page length select + custom pagination
    // =========================================================
    const $info = $("#sudahApprove-customInfo");
    const $pagination = $("#sudahApprove-customPagination");
    const $perPage = $("#sudahApprove-pageLength");

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
});

$(function () {
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
    // ✅ MODAL DETAIL (HTML) — JS hanya isi data
    // =========================================================
    const modal = document.getElementById("modalDetailDiskonSudahApprove");
    const modalError = document.getElementById(
        "modalDetailDiskonErrorSudahApprove",
    );
    const modalLoading = document.getElementById(
        "modalDetailDiskonLoadingSudahApprove",
    );

    const elNamaPasien = document.getElementById("modalNamaPasienSudahApprove");
    const elKode = document.getElementById("modalKodeTransaksiSudahApprove");
    const elRequester = document.getElementById("modalRequesterSudahApprove");

    const reasonWrap = document.getElementById("modalReasonWrapSudahApprove");
    const elReason = document.getElementById("modalReasonSudahApprove");

    const elApprovedBy = document.getElementById("modalApprovedBySudahApprove");
    const elStatus = document.getElementById("modalStatusSudahApprove");

    const rejectReasonWrap = document.getElementById(
        "modalRejectReasonWrapSudahApprove",
    );
    const elRejectReason = document.getElementById(
        "modalRejectReasonSudahApprove",
    );

    const badgeCount = document.getElementById("modalBadgeCountSudahApprove");
    const badgeTotal = document.getElementById("modalBadgeTotalSudahApprove");
    const badgePotongan = document.getElementById(
        "modalBadgePotonganSudahApprove",
    );
    const badgeAfter = document.getElementById("modalBadgeAfterSudahApprove");

    const tbody = document.getElementById("modalItemsBodySudahApprove");

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

            tdItem.appendChild(divName);
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
        const status = (payload?.status || "").toLowerCase().trim();
        const reason = (payload?.reason || "").trim();
        const rejectionNote = (payload?.rejection_note || "").trim();

        if (elNamaPasien) {
            elNamaPasien.textContent = payload?.nama_pasien || "-";
        }

        if (elKode) {
            elKode.textContent = payload?.kode_transaksi || "-";
        }

        if (elRequester) {
            elRequester.textContent = payload?.requested_by || "-";
        }

        if (elApprovedBy) {
            elApprovedBy.textContent =
                payload?.approved_by_name || payload?.approved_by || "-";
        }

        if (elStatus) {
            const label =
                status === "approved"
                    ? "Approved"
                    : status === "rejected"
                      ? "Rejected"
                      : "-";

            elStatus.textContent = label;
            elStatus.className =
                "inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1";

            if (status === "approved") {
                elStatus.classList.add(
                    "bg-emerald-50",
                    "text-emerald-700",
                    "ring-emerald-200",
                );
            } else if (status === "rejected") {
                elStatus.classList.add(
                    "bg-rose-50",
                    "text-rose-700",
                    "ring-rose-200",
                );
            } else {
                elStatus.classList.add(
                    "bg-slate-100",
                    "text-slate-700",
                    "ring-slate-200",
                );
            }
        }

        if (reasonWrap && elReason) {
            if (reason) {
                reasonWrap.classList.remove("hidden");
                elReason.textContent = reason;

                const labelReason = reasonWrap.querySelector("div:first-child");
                if (labelReason) {
                    labelReason.textContent = "Alasan";
                    labelReason.className =
                        "mb-1 text-xs font-semibold text-slate-700 dark:text-slate-200";
                }
            } else {
                reasonWrap.classList.add("hidden");
                elReason.textContent = "";
            }
        }

        if (rejectReasonWrap && elRejectReason) {
            if (status === "rejected" && rejectionNote) {
                rejectReasonWrap.classList.remove("hidden");
                elRejectReason.textContent = rejectionNote;
            } else {
                rejectReasonWrap.classList.add("hidden");
                elRejectReason.textContent = "";
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
        .querySelectorAll('[data-close-modal="detail-diskon-sudah-approve"]')
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
    $("#tabel-sudah-approve-diskon-order-obat").on(
        "click",
        ".btn-lihat-detail-order-obat-sudah-approve",
        async function () {
            const detailUrl = $(this).data("detail-url");
            if (!detailUrl) return;

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
});
