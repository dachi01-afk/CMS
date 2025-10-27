<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="max-w-7xl mx-auto w-full">
        <div class="flex flex-col items-center justify-center gap-8 my-10">
            <div class="flex items-center justify-between gap-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                <dt class="text-lg font-bold text-gray-900 dark:text-white">Metode Pembayaran</dt>
                <select class="text-lg font-bold text-gray-900 dark:text-white rounded-md" id="pilih-metode-pembayaran">
                    @foreach ($dataMetodePembayaran as $metodePembayaran)
                        <option value="{{ $metodePembayaran->id }}"
                            {{ $metodePembayaran->nama_metode }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button class="text-white mr-2 bg-green-500 px-4 py-2 rounded-md" id="button-bayar-sekarang">
                    <i class="fa-solid fa-money-bill text-lg"></i>Bayar Sekarang
                </button>
            </div>
        </div>
    </div>


    <div id="pembayaranCash" class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-md p-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                <!-- Header -->
                <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Pembayaran Cash</h3>
                    <button type="button"
                        class="text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg text-sm p-1.5">✖</button>
                </div>
            </div>
        </div>
    </div>

    <div id="pembayaranTransfer" class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-md p-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                <!-- Header -->
                <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Pembayaran Transfer</h3>
                    <button type="button"
                        class="text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg text-sm p-1.5">✖</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const pilihMetode = document.getElementById("pilih-metode-pembayaran");
            const modalCash = document.getElementById("pembayaranCash");
            const modalTransfer = document.getElementById("pembayaranTransfer");
            const btnBayar = document.getElementById("button-bayar-sekarang");
            const hiddenInput = document.getElementById("metode_pembayaran_id"); // kalau sudah ada hidden input

            // helper untuk menampilkan/menyembunyikan modal
            function openModal(modal) {
                if (!modal) return;
                modal.classList.remove("hidden");
                modal.classList.add("flex");
                // disable scroll body saat modal terbuka
                document.documentElement.style.overflow = "hidden";
            }

            function closeModal(modal) {
                if (!modal) return;
                modal.classList.add("hidden");
                modal.classList.remove("flex");
                document.documentElement.style.overflow = "";
            }

            function closeAll() {
                closeModal(modalCash);
                closeModal(modalTransfer);
            }

            // klik tombol "Bayar Sekarang" -> baca pilihan dan buka modal sesuai
            if (btnBayar) {
                btnBayar.addEventListener("click", (e) => {
                    e.preventDefault(); // kalau tombol berada di form dan kamu mau mencegah submit otomatis
                    const selected = pilihMetode?.options[pilihMetode.selectedIndex];
                    if (!selected) return alert("Pilih metode pembayaran dulu.");

                    const metodeId = selected.value;
                    const metodeText = (selected.textContent || "").toLowerCase();

                    // isi hidden input jika tersedia
                    if (hiddenInput) hiddenInput.value = metodeId;

                    // tutup dulu semua modal lalu buka yg sesuai
                    closeAll();

                    if (metodeText.includes("cash")) {
                        openModal(modalCash);
                    } else if (metodeText.includes("transfer")) {
                        openModal(modalTransfer);
                    } else {
                        // fallback: jika nama metode tidak jelas
                        alert("Metode pembayaran belum dikenali: " + selected.textContent);
                    }
                });
            }

            // pasang event untuk tombol close (✖) di masing-masing modal
            document.querySelectorAll("#pembayaranCash button, #pembayaranTransfer button").forEach(
                btn => {
                    btn.addEventListener("click", () => {
                        // tombol close ada di header modal, kita tutup modal terdekat
                        const modal = btn.closest("#pembayaranCash") || btn.closest(
                            "#pembayaranTransfer");
                        closeModal(modal);
                    });
                });

            // klik di area overlay (di luar dialog) -> tutup modal
            [modalCash, modalTransfer].forEach(modal => {
                if (!modal) return;
                modal.addEventListener("click", (ev) => {
                    // jika target click adalah overlay (bukan isi modal), tutup
                    if (ev.target === modal) closeModal(modal);
                });
            });

            // tombol Escape untuk menutup modal
            document.addEventListener("keydown", (ev) => {
                if (ev.key === "Escape") closeAll();
            });
        });
    </script>

</body>

</html>
