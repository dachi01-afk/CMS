<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/css/app.js'])
    <title>Document</title>
</head>

<body>
    <div class="flex items-center justify-center max-w-7xl my-10">
        <button id="btnOpenModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            + Transaksi Stok
        </button>
    </div>

    <div id="modalTransaksi" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">

        <div class="bg-white w-full max-w-4xl rounded-xl shadow-lg p-6">

            <!-- HEADER -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Transaksi Stok</h2>
                <button id="btnCloseModal" class="text-gray-500 hover:text-red-500">
                    ✕
                </button>
            </div>

            <!-- JENIS TRANSAKSI -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">
                    Jenis Transaksi
                </label>
                <select id="jenis_transaksi" class="w-full rounded-lg border-gray-300">
                    <option value="">-- Pilih --</option>
                    <option value="restock">Restock</option>
                    <option value="return">Return</option>
                </select>
            </div>

            <!-- TABS -->
            <div class="border-b mb-4 flex gap-4">
                <button class="tab-btn border-b-2 border-blue-600 pb-2" data-tab="obat">Obat</button>

                <button class="tab-btn text-gray-500 pb-2" data-tab="bhp">Bahan Habis Pakai</button>
            </div>

            <!-- TAB CONTENT -->
            <div>

                <!-- ================= TAB OBAT ================= -->
                <div class="tab-content" id="tab-obat">

                    <!-- Nama -->
                    <div class="transaksi-field restock return mb-3">
                        <label class="text-sm">Nama Obat</label>
                        <input type="text" class="w-full border rounded-lg">
                    </div>

                    <!-- Kategori -->
                    <div class="transaksi-field restock return mb-3">
                        <label class="text-sm">Kategori</label>
                        <input type="text" class="w-full border rounded-lg">
                    </div>

                    <!-- Satuan -->
                    <div class="transaksi-field restock return mb-3">
                        <label class="text-sm">Satuan</label>
                        <input type="text" class="w-full border rounded-lg">
                    </div>

                    <!-- KHUSUS RESTOCK -->
                    <div class="transaksi-field restock mb-3">
                        <label class="text-sm">Tanggal Kadaluarsa</label>
                        <input type="date" class="w-full border rounded-lg">
                    </div>

                    <div class="transaksi-field restock mb-3">
                        <label class="text-sm">Batch</label>
                        <input type="text" class="w-full border rounded-lg">
                    </div>

                    <div class="transaksi-field restock grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="text-sm">Harga Beli Lama</label>
                            <input type="number" class="w-full border rounded-lg">
                        </div>
                        <div>
                            <label class="text-sm">Harga Beli Baru</label>
                            <input type="number" class="w-full border rounded-lg">
                        </div>
                    </div>

                    <!-- KHUSUS RETURN -->
                    <div class="transaksi-field return mb-3">
                        <label class="text-sm">Alasan Return</label>
                        <textarea class="w-full border rounded-lg"></textarea>
                    </div>

                </div>

                <!-- ================= TAB BHP ================= -->
                <div class="tab-content hidden" id="tab-bhp">

                    <div class="transaksi-field restock return mb-3">
                        <label class="text-sm">Nama BHP</label>
                        <input type="text" class="w-full border rounded-lg">
                    </div>

                    <div class="transaksi-field restock mb-3">
                        <label class="text-sm">Tanggal Kadaluarsa</label>
                        <input type="date" class="w-full border rounded-lg">
                    </div>

                    <div class="transaksi-field return mb-3">
                        <label class="text-sm">Jumlah Return</label>
                        <input type="number" class="w-full border rounded-lg">
                    </div>

                </div>
            </div>

            <!-- FOOTER -->
            <div class="mt-6 flex justify-end gap-2">
                <button class="px-4 py-2 rounded-lg border">Batal</button>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Simpan
                </button>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {

            // OPEN MODAL
            $('#btnOpenModal').click(function() {
                $('#modalTransaksi').removeClass('hidden').addClass('flex');
            });

            // CLOSE MODAL
            $('#btnCloseModal').click(function() {
                $('#modalTransaksi').addClass('hidden').removeClass('flex');
            });

            // TAB SWITCH
            $('.tab-btn').click(function() {
                let tab = $(this).data('tab');

                $('.tab-btn').removeClass('border-blue-600 text-black')
                    .addClass('text-gray-500');

                $(this).addClass('border-blue-600 text-black');

                $('.tab-content').addClass('hidden');
                $('#tab-' + tab).removeClass('hidden');
            });

            // AWAL SEMBUNYIKAN FIELD
            $('.transaksi-field').hide();

            // JENIS TRANSAKSI CHANGE
            $('#jenis_transaksi').change(function() {
                let jenis = $(this).val();

                $('.transaksi-field').hide();

                if (jenis) {
                    $('.' + jenis).show();
                }
            });

        });
    </script>


</body>

</html>
