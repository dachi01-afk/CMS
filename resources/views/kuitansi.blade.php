<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kuitansi Pembayaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex items-center justify-center p-6">
  <div class="bg-white shadow-2xl rounded-2xl w-full max-w-2xl p-8 relative border border-gray-200">
    <!-- Header -->
    <div class="text-center border-b pb-4">
      <h1 class="text-3xl font-bold text-blue-700">Kuitansi Pembayaran</h1>
      <p class="text-gray-500 text-sm mt-1">No. Kuitansi: <span class="font-semibold">INV-2025-001</span></p>
    </div>

    <!-- Data Pembayaran -->
    <div class="py-6 space-y-3 text-gray-700">
      <div class="flex justify-between">
        <p>Tanggal:</p>
        <p class="font-semibold">19 Oktober 2025</p>
      </div>
      <div class="flex justify-between">
        <p>Nama Pembayar:</p>
        <p class="font-semibold">David Jonathan</p>
      </div>
      <div class="flex justify-between">
        <p>Deskripsi:</p>
        <p class="font-semibold">Pembayaran Layanan Kesehatan</p>
      </div>
    </div>

    <!-- Detail Item Pembayaran -->
    <div class="mt-6">
      <table class="w-full border border-gray-300 rounded-xl overflow-hidden">
        <thead class="bg-blue-100 text-blue-700">
          <tr>
            <th class="py-2 px-3 text-left">No</th>
            <th class="py-2 px-3 text-left">Nama Item</th>
            <th class="py-2 px-3 text-right">Harga (Rp)</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr class="hover:bg-gray-50">
            <td class="py-2 px-3">1</td>
            <td class="py-2 px-3">Konsultasi Dokter Umum</td>
            <td class="py-2 px-3 text-right">150.000</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="py-2 px-3">2</td>
            <td class="py-2 px-3">Obat Paracetamol 500mg</td>
            <td class="py-2 px-3 text-right">50.000</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="py-2 px-3">3</td>
            <td class="py-2 px-3">Pemeriksaan Tekanan Darah</td>
            <td class="py-2 px-3 text-right">30.000</td>
          </tr>
          <tr class="hover:bg-gray-50 bg-blue-50 font-semibold">
            <td colspan="2" class="py-2 px-3 text-right">Total</td>
            <td class="py-2 px-3 text-right text-blue-700">230.000</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <div class="border-t pt-4 mt-6 text-center text-gray-600">
      <p class="mb-2 italic">Terima kasih telah melakukan pembayaran.</p>
      <p class="font-semibold text-gray-800">PT. Royal Tech Solution</p>
    </div>

    <!-- Tombol Print -->
    <div class="absolute top-4 right-4">
      <button onclick="window.print()"   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded-lg shadow-md flex items-center gap-2 transition-transform transform hover:scale-105">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12M6 10h12" />
        </svg>
        Print Kuitansi
      </button>
    </div>
  </div>
</body>
</html>
