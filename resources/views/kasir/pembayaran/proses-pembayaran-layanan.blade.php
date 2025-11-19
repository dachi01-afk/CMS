<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pembayaran Layanan</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<body class="bg-slate-50">

    <div class="min-h-screen flex flex-col">

        {{-- Header --}}
        <header class="bg-white border-b border-slate-200 shadow-sm">
            <div class="max-w-5xl mx-auto px-4 py-3 flex justify-between items-center">
                <div>
                    <p class="text-[11px] uppercase text-slate-400 mb-1">
                        Kasir • Pembayaran Layanan
                    </p>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Proses Pembayaran
                    </h1>
                </div>

                <button onclick="window.history.back()"
                    class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    Kembali
                </button>
            </div>
        </header>


        {{-- MAIN CONTENT --}}
        <main class="flex-1">
            <div class="max-w-5xl mx-auto px-4 py-6 space-y-6">

                {{-- SUMMARY --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-bold text-slate-800">Ringkasan Transaksi</h2>
                        <span class="text-sm font-mono text-slate-500">
                            {{ $transaksi->kode_transaksi ?? '-' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <p class="text-[10px] uppercase text-slate-500 mb-1">Pasien</p>
                            <p class="font-semibold text-slate-800">
                                {{ $transaksi->pasien->nama_pasien ?? '-' }}
                            </p>
                            <p class="text-xs text-slate-500">No EMR: {{ $transaksi->pasien->no_emr ?? '-' }}</p>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <p class="text-[10px] uppercase text-slate-500 mb-1">Layanan</p>
                            <p class="font-semibold text-slate-800">
                                {{ $transaksi->layanan->nama_layanan ?? '-' }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $transaksi->layanan->kategoriLayanan->nama_kategori ?? '-' }}
                            </p>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <p class="text-[10px] uppercase text-slate-500 mb-1">Total Tagihan</p>
                            <p class="text-xl font-bold text-sky-600">
                                Rp {{ number_format($transaksi->total_tagihan, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- FORM PEMBAYARAN --}}
                <form action="{{ route('kasir.submit.pembayaran.layanan') }}" method="POST"
                    enctype="multipart/form-data"
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-6">
                    @csrf

                    <input type="hidden" name="id" value="{{ $transaksi->id }}">

                    {{-- METODE PEMBAYARAN --}}
                    <div>
                        <label class="text-sm font-semibold text-slate-700 mb-2 block">
                            Pilih Metode Pembayaran
                        </label>

                        <select name="metode_pembayaran"
                            class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:ring-sky-500 focus:border-sky-500 bg-slate-50">
                            <option value="">-- Pilih --</option>
                            @foreach ($metodePembayaran as $m)
                                <option value="{{ $m->nama_metode }}">
                                    {{ $m->nama_metode }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BUKTI PEMBAYARAN --}}
                    <div>
                        <label class="text-sm font-semibold text-slate-700 mb-2 block">
                            Upload Bukti Pembayaran (Opsional)
                        </label>

                        <input type="file" name="bukti_pembayaran"
                            class="w-full border border-slate-300 rounded-lg p-3 text-sm bg-slate-50">
                        <p class="text-xs text-slate-500 mt-1">
                            Format: JPG, JPEG, PNG, PDF • Maks 2MB
                        </p>
                    </div>

                    {{-- BUTTON --}}
                    <div class="flex justify-end pt-4 border-t border-slate-200">
                        <button type="submit"
                            class="px-6 py-3 rounded-lg font-semibold text-white text-sm
                               bg-gradient-to-r from-sky-500 to-indigo-600
                               hover:from-sky-600 hover:to-indigo-700 shadow">
                            <i class="fa-solid fa-check text-xs mr-1"></i>
                            Konfirmasi Pembayaran
                        </button>
                    </div>
                </form>

            </div>
        </main>

    </div>

</body>

</html>
