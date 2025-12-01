document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("waitingBody");
    const menuProses = document.getElementById("menuProsesKunjungan");
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    // --- Modal edit kunjungan ---
    const editModalEl = document.getElementById("editKunjunganModal");
    const editForm = document.getElementById("editKunjunganForm");
    const editErrorBox = document.getElementById("edit_error_box");

    // TomSelect instance
    let tsDokter = null;
    let tsPoli = null;

    const esc = (s) =>
        String(s ?? "-")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    // ---------- Helper Modal ----------
    function openEditModal() {
        if (!editModalEl) return;
        if (editErrorBox) {
            editErrorBox.classList.add("hidden");
            editErrorBox.textContent = "";
        }
        editModalEl.classList.remove("hidden");
    }

    function destroyTS(ts) {
        try {
            ts && ts.destroy();
        } catch (_) {}
    }

    function showPoliGroup() {
        const group = document.getElementById("group_poli_edit");
        if (group) group.classList.remove("hidden");
    }

    function hidePoliGroup() {
        const group = document.getElementById("group_poli_edit");
        if (group) group.classList.add("hidden");
        destroyTS(tsPoli);
        tsPoli = null;
        const poliSelect = document.getElementById("edit_poli_select");
        if (poliSelect) poliSelect.innerHTML = "";
    }

    function resetEditModalState() {
        if (!editForm) return;
        editForm.reset();
        if (editErrorBox) {
            editErrorBox.classList.add("hidden");
            editErrorBox.textContent = "";
        }

        destroyTS(tsDokter);
        destroyTS(tsPoli);
        tsDokter = null;
        tsPoli = null;

        const dokterSelect = document.getElementById("edit_dokter_select");
        if (dokterSelect) dokterSelect.innerHTML = "";

        hidePoliGroup();
    }

    function closeEditModal() {
        if (!editModalEl) return;
        resetEditModalState();
        editModalEl.classList.add("hidden");
    }

    // tombol close modal (X dan Batal)
    document.querySelectorAll(".close-edit-kunjungan").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            closeEditModal();
        });
    });

    // ⚠️ Tidak ada listener klik backdrop → klik background tidak menutup modal

    // ---------- TomSelect Dokter ----------
    function initTomSelectDokter(preId = null, preText = null) {
        destroyTS(tsDokter);

        const selectEl = document.getElementById("edit_dokter_select");
        if (!selectEl) return;

        if (preId && preText) {
            selectEl.innerHTML = `<option value="${preId}" selected>${preText}</option>`;
        } else {
            selectEl.innerHTML = "";
        }

        tsDokter = new TomSelect("#edit_dokter_select", {
            create: false,
            maxItems: 1,
            valueField: "id",
            labelField: "nama",
            searchField: "nama",
            placeholder: "Cari & pilih dokter…",
            preload: !!preId,
            render: {
                option: (item) => `<div class="py-1 px-2">${item.nama}</div>`,
                item: (item) => `<div>${item.nama}</div>`,
            },
            load: function (query, callback) {
                fetch(
                    `/jadwal_kunjungan/listDokter?q=${encodeURIComponent(
                        query || ""
                    )}`,
                    {
                        headers: { Accept: "application/json" },
                    }
                )
                    .then((res) => res.json())
                    .then((data) => {
                        const arr = Array.isArray(data?.data) ? data.data : [];
                        callback(
                            arr.map((d) => ({
                                id: d.id,
                                nama: d.nama_dokter || d.nama || `Dokter #${d.id}`,
                            }))
                        );
                    })
                    .catch(() => callback());
            },
            onChange: (val) => {
                if (val) {
                    initTomSelectPoli(val, null, null);
                } else {
                    hidePoliGroup();
                }
            },
        });

        if (preId) {
            tsDokter.setValue(String(preId), true);
        }
    }

    // ---------- TomSelect Poli (depend on Dokter) ----------
    function initTomSelectPoli(dokterId, preId = null, preText = null) {
        destroyTS(tsPoli);

        const selectEl = document.getElementById("edit_poli_select");
        if (!selectEl || !dokterId) {
            hidePoliGroup();
            return;
        }

        if (preId && preText) {
            selectEl.innerHTML = `<option value="${preId}" selected>${preText}</option>`;
        } else {
            selectEl.innerHTML = "";
        }

        tsPoli = new TomSelect("#edit_poli_select", {
            create: false,
            maxItems: 1,
            valueField: "id",
            labelField: "nama",
            searchField: "nama",
            placeholder: "Cari & pilih poli…",
            preload: "focus",
            shouldLoad: () => true,
            render: {
                option: (item) => `<div class="py-1 px-2">${item.nama}</div>`,
                item: (item) => `<div>${item.nama}</div>`,
            },
            load: function (query, callback) {
                fetch(
                    `/jadwal_kunjungan/listPoliByDokter/${dokterId}/poli?q=${encodeURIComponent(
                        query || ""
                    )}`,
                    { headers: { Accept: "application/json" } }
                )
                    .then((res) => res.json())
                    .then((data) => {
                        const arr = Array.isArray(data?.data) ? data.data : [];
                        callback(
                            arr.map((p) => ({
                                id: p.id,
                                nama: p.nama_poli || p.nama || `Poli #${p.id}`,
                            }))
                        );
                    })
                    .catch(() => callback());
            },
            onInitialize() {
                this.load("");
                setTimeout(() => this.open(), 60);
            },
            onFocus() {
                if (this.options_count === 0) this.load("");
            },
        });

        if (preId) {
            tsPoli.setValue(String(preId), true);
        }

        showPoliGroup();
    }

    // ---------- Load Waiting List ----------
    async function loadWaitingList() {
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-6 text-gray-500 italic">
                    Memuat data...
                </td>
            </tr>`;

        try {
            const res = await fetch("/jadwal_kunjungan/waiting", {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const json = await res.json();
            const payload = Array.isArray(json?.data)
                ? json.data
                : Array.isArray(json)
                ? json
                : [];

            if (!payload.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-6 text-gray-500 italic">
                            Tidak ada kunjungan pending hari ini.
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = payload
                .map((item) => {
                    const noAntrian = esc(item.no_antrian ?? "-");
                    const namaPasien = esc(item.pasien?.nama_pasien ?? "-");
                    const namaDokter = esc(
                        item.dokter?.nama_dokter ??
                            item.dokter_terpilih?.nama_dokter ??
                            item._nama_dokter ??
                            "-"
                    );
                    const namaPoli = esc(item.poli?.nama_poli ?? "-");
                    const keluhan = esc(item.keluhan_awal ?? "-");
                    const status = esc(item.status ?? "-");

                    const dokterId =
                        item.dokter_id ??
                        item.dokter?.id ??
                        item.dokter_terpilih?.id ??
                        "";
                    const poliId = item.poli_id ?? item.poli?.id ?? "";

                    return `
                        <tr class="hover:bg-indigo-50/60 transition-colors">
                            <td class="px-6 py-3 font-semibold text-gray-900">${noAntrian}</td>
                            <td class="px-6 py-3 text-gray-800 text-center">${namaPasien}</td>
                            <td class="px-6 py-3 text-gray-800 text-center">${namaDokter}</td>
                            <td class="px-6 py-3 text-gray-800 text-center">${namaPoli}</td>
                            <td class="px-6 py-3 text-gray-800 text-center">${keluhan}</td>
                            <td class="px-6 py-3 text-gray-800 text-center">${status}</td>
                           <td class="px-6 py-3 text-right align-top overflow-visible relative">

    <div class="aksi-dropdown-wrapper relative inline-block text-left">

        <!-- Tombol Toggle -->
        <button type="button"
                class="aksiDropdownToggle inline-flex items-center justify-center h-8 w-8 rounded-full
                       text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
            <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
        </button>

        <!-- MENU FLOAT / MELAYANG -->
        <div
            class="aksiDropdownMenu hidden absolute right-0 top-9 z-50 bg-white dark:bg-slate-800
                   border border-gray-100 dark:border-slate-700 w-44 rounded-xl shadow-xl
                   py-1 text-sm transition-all">

            <!-- Mulai Konsultasi -->
            <button data-id="${item.id}"
                    class="ubahStatusBtn w-full px-4 py-2 flex items-center gap-2 text-xs
                           text-indigo-700 hover:bg-indigo-50 dark:hover:bg-slate-700">
                <i class="fa-solid fa-play text-[11px]"></i>
                <span>Mulai Konsultasi</span>
            </button>

            <!-- Batalkan -->
            <button data-id="${item.id}"
                    class="batalkanKunjunganBtn w-full px-4 py-2 flex items-center gap-2 text-xs
                           text-red-600 hover:bg-red-50 dark:hover:bg-slate-700/40">
                <i class="fa-solid fa-xmark text-[11px]"></i>
                <span>Batalkan Kunjungan</span>
            </button>

            <div class="border-t border-gray-200 dark:border-slate-600 my-1"></div>

            <!-- Edit -->
            <button
                data-id="${item.id}"
                data-no_antrian="${noAntrian}"
                data-nama_pasien="${namaPasien}"
                data-nama_dokter="${namaDokter}"
                data-nama_poli="${namaPoli}"
                data-dokter_id="${dokterId}"
                data-poli_id="${poliId}"
                data-keluhan="${keluhan}"
                data-status-kunjungan="${status}"
                data-update-url="/jadwal_kunjungan/updateKunjungan/${item.id}"
                class="editKunjunganBtn w-full px-4 py-2 flex items-center gap-2 text-xs
                       text-gray-700 hover:bg-gray-50 dark:hover:bg-slate-700">
                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                <span>Edit Kunjungan</span>
            </button>

        </div>
    </div>

</td>

                        </tr>`;
                })
                .join("");
        } catch (err) {
            console.error("Gagal memuat waiting list:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-6 text-red-600">
                        ${esc(err.message ?? "Gagal memuat data. Coba lagi.")}
                    </td>
                </tr>`;
        }
    }

    if (menuProses) {
        menuProses.addEventListener("click", (e) => {
            e.preventDefault();
            loadWaitingList();
        });
    }

    // ---------- Event Delegation: dropdown + aksi ----------
    document.addEventListener("click", async (e) => {
        // Toggle dropdown
        const toggle = e.target.closest(".aksiDropdownToggle");
        if (toggle) {
            const wrapper = toggle.closest(".aksi-dropdown-wrapper");
            const menu = wrapper?.querySelector(".aksiDropdownMenu");

            document.querySelectorAll(".aksiDropdownMenu").forEach((m) => {
                if (m !== menu) m.classList.add("hidden");
            });

            if (menu) menu.classList.toggle("hidden");
            return;
        }

        // Klik di luar dropdown → tutup semua
        const insideDropdown =
            e.target.closest(".aksiDropdownMenu") ||
            e.target.closest(".aksiDropdownToggle");

        if (!insideDropdown) {
            document
                .querySelectorAll(".aksiDropdownMenu")
                .forEach((m) => m.classList.add("hidden"));
        }

        const startBtn = e.target.closest(".ubahStatusBtn");
        const cancelBtn = e.target.closest(".batalkanKunjunganBtn");
        const editBtn = e.target.closest(".editKunjunganBtn");

        // Mulai Konsultasi
        if (startBtn) {
            const id = startBtn.dataset.id;
            const konfirmasi = await Swal.fire({
                icon: "question",
                title: "Mulai konsultasi?",
                text: 'Status akan diubah menjadi "Waiting".',
                showCancelButton: true,
                confirmButtonText: "Ya, ubah",
                cancelButtonText: "Batal",
            });
            if (!konfirmasi.isConfirmed) return;

            startBtn.disabled = true;

            try {
                const res = await fetch(
                    `/jadwal_kunjungan/update-status/${id}`,
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            Accept: "application/json",
                        },
                        credentials: "same-origin",
                    }
                );
                const result = await res.json();

                if (result?.success) {
                    await Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: result.message ?? "Status diubah.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    loadWaitingList();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text: result?.message ?? "Gagal mengubah status.",
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Tidak dapat terhubung ke server.",
                });
            } finally {
                startBtn.disabled = false;
            }

            return;
        }

        // Batalkan Kunjungan
        if (cancelBtn) {
            const id = cancelBtn.dataset.id;
            const konfirmasi = await Swal.fire({
                icon: "question",
                title: "Batalkan Kunjungan?",
                text: "Apakah Anda yakin ingin membatalkan kunjungan?",
                showCancelButton: true,
                confirmButtonText: "Ya, batalkan",
                cancelButtonText: "Tidak",
            });
            if (!konfirmasi.isConfirmed) return;

            cancelBtn.disabled = true;

            try {
                const res = await fetch(
                    `/jadwal_kunjungan/batalkan-kunjungan/${id}`,
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            Accept: "application/json",
                        },
                        credentials: "same-origin",
                    }
                );
                const result = await res.json();

                if (result?.success) {
                    await Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: result.message ?? "Kunjungan dibatalkan.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    loadWaitingList();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text: result?.message ?? "Gagal membatalkan kunjungan.",
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Tidak dapat terhubung ke server.",
                });
            } finally {
                cancelBtn.disabled = false;
            }

            return;
        }

        // Edit Kunjungan → buka modal
        if (editBtn) {
            if (!editForm) return;

            resetEditModalState();

            const d = editBtn.dataset;

            const updateUrl =
                d.updateUrl || `jadwal_kunjungan/updateKunjungan/${d.id}`;
            editForm.setAttribute("action", updateUrl);

            document.getElementById("edit_no_antrian").value =
                d.no_antrian || "-";
            document.getElementById("edit_nama_pasien").value =
                d.nama_pasien || "-";
            document.getElementById("edit_keluhan_awal").value =
                d.keluhan || "";
            document.getElementById("edit_status").value =
                d.statusKunjungan || "";

            const dokterId = d.dokter_id || "";
            const dokterNama = d.nama_dokter || null;
            const poliId = d.poli_id || "";
            const poliNama = d.nama_poli || null;

            initTomSelectDokter(dokterId || null, dokterNama);
            if (dokterId) {
                initTomSelectPoli(dokterId, poliId || null, poliNama);
            } else {
                hidePoliGroup();
            }

            openEditModal();
            return;
        }
    });

    // ---------- Submit Edit ----------
    if (editForm) {
        editForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            if (!csrf) {
                console.error("CSRF token tidak ditemukan");
                return;
            }

            const action = editForm.getAttribute("action");
            const formData = new FormData(editForm);

            if (editErrorBox) {
                editErrorBox.classList.add("hidden");
                editErrorBox.textContent = "";
            }
            const errDokEl = document.getElementById("edit_dokter_id-error");
            const errPoliEl = document.getElementById("edit_poli_id-error");
            if (errDokEl) errDokEl.textContent = "";
            if (errPoliEl) errPoliEl.textContent = "";

            try {
                const res = await fetch(action, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                    },
                    credentials: "same-origin",
                    body: formData,
                });

                // VALIDATION ERROR
                if (res.status === 422) {
                    const json = await res.json();
                    const errors = json.errors || {};

                    await Swal.fire({
                        icon: "error",
                        title: "Gagal Disimpan",
                        html: "Periksa kembali input yang masih salah",
                        confirmButtonText: "OK",
                    });

                    let messages = Object.values(errors).flat().join(" ");
                    if (editErrorBox) {
                        editErrorBox.textContent = messages;
                        editErrorBox.classList.remove("hidden");
                    }

                    if (errors.dokter_id && errDokEl) {
                        errDokEl.textContent = errors.dokter_id[0];
                    }
                    if (errors.poli_id && errPoliEl) {
                        errPoliEl.textContent = errors.poli_id[0];
                    }
                    return;
                }

                // SERVER ERROR
                if (!res.ok) {
                    await Swal.fire({
                        icon: "error",
                        title: "Error Server",
                        text: "Terjadi kesalahan pada server.",
                    });
                    if (editErrorBox) {
                        editErrorBox.textContent = "Kesalahan server!";
                        editErrorBox.classList.remove("hidden");
                    }
                    return;
                }

                // SUCCESS
                const json = await res.json();

                await Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: json.message ?? "Kunjungan berhasil diperbarui.",
                    timer: 1500,
                    showConfirmButton: false,
                });

                closeEditModal();
                loadWaitingList();
            } catch (err) {
                console.error("Error:", err);

                await Swal.fire({
                    icon: "error",
                    title: "Kesalahan Jaringan",
                    text: "Tidak dapat terhubung ke server.",
                });

                if (editErrorBox) {
                    editErrorBox.textContent = "Gagal terhubung ke server.";
                    editErrorBox.classList.remove("hidden");
                }
            }
        });
    }

    // Load awal
    loadWaitingList();
});
