<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Data Pasien</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>
</head>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <div class="max-w-6xl mx-auto px-4 py-6">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Detail Data Pasien</h1>
                <p class="text-sm text-gray-500">
                    Ringkasan identitas dan informasi medis dasar pasien.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="javascript:history.back()"
                    class="inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm font-medium rounded-lg
                      border border-gray-300 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    Kembali
                </a>

                <button type="button"
                    class="btn-edit-pasien inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm font-medium rounded-lg
                           bg-sky-600 text-white hover:bg-sky-700"
                    data-id="{{ $pasien->id }}">
                    <i class="fa-regular fa-pen-to-square text-[11px]"></i>
                    Edit Pasien
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1.1fr,2fr] gap-5">

            {{-- Kartu Profil --}}
            <div class="bg-white rounded-2xl shadow p-4 md:p-5 space-y-4">

                <div class="flex flex-col items-center gap-3">
                    {{-- Foto --}}
                    <div class="w-28 h-32 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center">
                        @if ($pasien->foto_pasien)
                            <img src="{{ asset('storage/' . $pasien->foto_pasien) }}" alt="Foto Pasien"
                                class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-user text-4xl text-gray-400"></i>
                        @endif
                    </div>

                </div>

                <div class="mt-3 space-y-2 text-sm">
                    <p class="font-semibold text-base text-gray-800">
                        {{ strtoupper($pasien->nama_pasien) }}
                    </p>

                    <div class="flex flex-wrap gap-2">
                        @if ($pasien->jenis_kelamin)
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                     bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fa-solid fa-venus-mars mr-1 text-[10px]"></i>
                                {{ $pasien->jenis_kelamin }}
                            </span>
                        @endif

                        @if ($umur)
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                     bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-regular fa-clock mr-1 text-[10px]"></i>
                                {{ $umur }}
                            </span>
                        @endif
                    </div>

                    <div class="border-t border-gray-100 pt-3 space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-500">No. EMR</span>
                            <span class="font-semibold text-gray-800">{{ $pasien->no_emr ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Barcode Value</span>
                            <span class="font-mono text-[11px]">{{ $pasien->barcode_pasien ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Terdaftar</span>
                            <span class="text-gray-700">
                                {{ optional($pasien->created_at)->format('d-m-Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Detail Lengkap --}}
            <div class="space-y-4">

                {{-- Data Akun --}}
                <div class="bg-white rounded-2xl shadow p-4 md:p-5 space-y-3">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-sm font-semibold text-gray-800">Data Akun</h2>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                                 bg-indigo-50 text-indigo-700 border border-indigo-200">
                            <i class="fa-solid fa-user-shield mr-1 text-[10px]"></i>
                            User Login
                        </span>
                    </div>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-6 text-xs">
                        <div>
                            <dt class="text-gray-500">Username</dt>
                            <dd class="font-semibold text-gray-800">
                                {{ $pasien->user->username ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd class="font-semibold text-gray-800">
                                {{ $pasien->user->email ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Role</dt>
                            <dd class="font-semibold text-gray-800">
                                {{ $pasien->user->role ?? '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Identitas Pasien --}}
                <div class="bg-white rounded-2xl shadow p-4 md:p-5 space-y-3">
                    <h2 class="text-sm font-semibold text-gray-800 mb-1">Identitas Pasien</h2>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-6 text-xs">
                        <div>
                            <dt class="text-gray-500">Nama Lengkap</dt>
                            <dd class="font-semibold text-gray-800">{{ $pasien->nama_pasien }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">NIK</dt>
                            <dd class="font-semibold text-gray-800">{{ $pasien->nik ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">No. BPJS</dt>
                            <dd class="font-semibold text-gray-800">{{ $pasien->no_bpjs ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">Tanggal Lahir</dt>
                            <dd class="font-semibold text-gray-800">
                                @if ($pasien->tanggal_lahir)
                                    {{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->format('d-m-Y') }}
                                @else
                                    -
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">Golongan Darah</dt>
                            <dd class="font-semibold text-gray-800">{{ $pasien->golongan_darah ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">Status Perkawinan</dt>
                            <dd class="font-semibold text-gray-800">{{ $pasien->status_perkawinan ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">Pekerjaan</dt>
                            <dd class="font-semibold text-gray-800">{{ $pasien->pekerjaan ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">No. HP Pasien</dt>
                            <dd class="font-semibold text-gray-800">{{ $pasien->no_hp_pasien ?? '-' }}</dd>
                        </div>

                        <div class="md:col-span-2">
                            <dt class="text-gray-500">Alamat</dt>
                            <dd class="font-semibold text-gray-800">
                                {{ $pasien->alamat ?? '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Penanggung Jawab & Medis --}}
                <div class="bg-white rounded-2xl shadow p-4 md:p-5 space-y-3">
                    <h2 class="text-sm font-semibold text-gray-800 mb-1">
                        Penanggung Jawab & Informasi Medis
                    </h2>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-6 text-xs mb-3">
                        <div>
                            <dt class="text-gray-500">Nama Penanggung Jawab</dt>
                            <dd class="font-semibold text-gray-800">
                                {{ $pasien->nama_penanggung_jawab ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">No. HP Penanggung Jawab</dt>
                            <dd class="font-semibold text-gray-800">
                                {{ $pasien->no_hp_penanggung_jawab ?? '-' }}
                            </dd>
                        </div>
                    </dl>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                        <div>
                            <h3 class="text-gray-500 mb-1">Alergi</h3>
                            <div
                                class="min-h-[60px] rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-gray-800">
                                {{ $pasien->alergi ?? 'Tidak ada alergi yang tercatat.' }}
                            </div>
                        </div>

                        <div>
                            <h3 class="text-gray-500 mb-1">Catatan Medis Umum</h3>
                            <div
                                class="min-h-[60px] rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-gray-800">
                                {{ $pasien->catatan_medis ?? 'Belum ada catatan medis khusus.' }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Optional: kalau mau pakai modal edit yang sama seperti di halaman list, script JS-nya akan tetap jalan karena class .btn-edit-pasien masih dipakai --}}
    @vite(['resources/js/admin/manajemenPengguna/data_pasien.js'])

</body>

</html>
