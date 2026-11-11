import $ from "jquery";
import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.css";
import "../../css/remote-filter-select.css";

/**
 * Filter dropdown dengan pencarian ke server (TomSelect remote search).
 *
 * Dipakai bersama oleh Laporan Audit Kunjungan dan Laporan Transaksi Order
 * Layanan supaya perilaku serta tampilannya identik dan perbaikan cukup
 * dilakukan di satu tempat.
 *
 * Elemen <select> wajib punya atribut:
 * - data-search-url : endpoint yang mengembalikan { data: [{ id, text }] }
 * - data-placeholder: teks saat belum ada pilihan ("Semua Layanan")
 *
 * @param {string} selector  selector CSS untuk <select>
 * @param {string} emptyText teks saat pencarian tidak menemukan hasil
 */
export function initRemoteFilterSelect(
    selector,
    emptyText = "Data tidak ditemukan",
) {
    const el = document.querySelector(selector);
    if (!el) return null;

    return new TomSelect(el, {
        create: false,
        valueField: "id",
        labelField: "text",
        searchField: ["text"],
        placeholder: el.dataset.placeholder || "",
        // Tampilkan beberapa data teratas saat kontrol dibuka tanpa mengetik
        preload: true,
        loadThrottle: 250,
        maxOptions: 20,
        plugins: ["clear_button"],
        render: {
            option: (item, escape) =>
                `<div class="py-0.5">${escape(item.text ?? "")}</div>`,
            item: (item, escape) => `<div>${escape(item.text ?? "")}</div>`,
            no_results: () =>
                `<div class="px-3 py-2 text-slate-500">${emptyText}</div>`,
        },
        load(query, callback) {
            const url = el.dataset.searchUrl;
            if (!url) return callback();

            $.ajax({
                url: url,
                method: "GET",
                dataType: "json",
                data: { q: query || "" },
                success: (res) => callback(Array.isArray(res?.data) ? res.data : []),
                error: () => callback(),
            });
        },
    });
}
