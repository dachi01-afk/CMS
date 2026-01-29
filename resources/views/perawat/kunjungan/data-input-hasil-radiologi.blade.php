<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Document</title>
</head>

<body>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4">
                <h2 class="text-white text-xl font-bold">Input Hasil Radiologi</h2>
                <p class="text-indigo-100 text-sm">No. Order: {{ $order->no_order_radiologi }}</p>
            </div>

            <form id="formInputHasil">
                @csrf
                <input type="hidden" name="order_lab_id" value="{{ $order->id }}">

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 border-b border-gray-100 bg-gray-50">
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">Pasien</label>
                        <p class="text-gray-800 font-semibold">{{ $order->pasien->nama_pasien }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase font-bold">Dokter Pengirim</label>
                        <p class="text-gray-800 font-semibold">{{ $order->dokter->nama_dokter }}</p>
                    </div>
                </div>

                <div class="p-6">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-600 text-sm uppercase">
                                <th class="pb-3 border-b">Nama Pemeriksaan</th>
                                <th class="pb-3 border-b text-center">Hasil</th>
                                {{-- <th class="pb-3 border-b text-center">Satuan</th> --}}
                                {{-- <th class="pb-3 border-b text-center">Nilai Normal</th> --}}
                                <th class="pb-3 border-b text-center">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($order->orderRadiologiDetail as $detail)
                                <tr>
                                    <td class="py-4 font-medium text-gray-700">
                                        {{ $detail->jenisPemeriksaanRadiologi->nama_pemeriksaan }}
                                    </td>
                                    <td class="py-4">
                                        <input type="text" name="hasil[{{ $detail->id }}]"
                                            placeholder="Masukkan angka/hasil"
                                            class="w-full max-w-[200px] mx-auto block px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-center">
                                    </td>
                                    {{-- <td class="py-4 text-center text-gray-500">
                                        {{ optional($detail->jenisPemeriksaanLab->satuanLab)->nama_satuan ?? '-' }}
                                    </td> --}}
                                    {{-- <td class="py-4 text-center">
                                        <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                                            {{ $detail->jenisPemeriksaanLab->nilai_normal }}
                                        </span>
                                    </td> --}}
                                    <td class="py-4">
                                        <input type="text" name="keterangan[{{ $detail->id }}]"
                                            placeholder="Catatan..."
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-gray-50 flex justify-end gap-3">
                    <a href="{{ url()->previous() }}"
                        class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition">Batal</a>
                    <button type="submit" id="btnSimpan"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-md transition">Simpan
                        Hasil</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#formInputHasil').on('submit', function(e) {
                e.preventDefault();

                let btn = $('#btnSimpan');
                btn.prop('disabled', true).text('Menyimpan...');

                $.ajax({
                    url: "{{ route('simpan.hasil.order.lab') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire('Berhasil!', response.message, 'success').then(() => {
                            window.location.href =
                                "/perawat/kunjungan"; // Sesuaikan route kembali
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan saat menyimpan data.', 'error');
                        btn.prop('disabled', false).text('Simpan Hasil');
                    }
                });
            });
        });
    </script>
</body>

</html>
