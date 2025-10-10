<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js']);

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body>
    <div x-data="{
        openModalUbahStatusKunjungan: false,
        openModalTolakStatusKunjungan: false,
        idKunjungan: '',
    }" class="max-w-7xl mx-auto m-5 px-8 py-4 bg-gray-200 rounded-md gap-8">

        <div class="overflow-auto lg:my-2 rounded-lg shadow-slate-300 shadow-xl">
            <table class="w-full md:w-full md:text-sm text-center text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr class="">
                        <th class="px-4 py-3 lg:p-4 ">No</th>
                        <th class="px-4 py-3 lg:p-4 ">Nama Pasien</th>
                        <th class="px-4 py-3 lg:p-4 ">Nama Pasien</th>
                        <th class="px-4 py-3 lg:p-4 ">Resep</th>
                        <th class="px-4 py-3 lg:p-4 ">Obat</th>
                        <th class="px-4 py-3 lg:p-4 ">Status</th>
                        <th class="mx-4 py-3 lg:p-4 ">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataResepObat as $resepObat)
                        <tr class="xl:text-base">
                            <td class="px-4 py-3 lg:px-8">{{ $dataResepObat->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3 lg:py-4 text-center">
                                {{ $resepObat->kunjungan->pasien->nama_pasien }}
                            </td>
                            <td class="px-4 py-3 lg:p-4">
                                {{ $resepObat->kunjungan->dokter->nama_dokter }}
                            </td>
                            <td class="px-4 py-3 lg:p-4">
                                {{ $resepObat->tanggal_kunjungan }}
                            </td>
                            <td class="px-4 py-3 lg:p-4">
                                {{ $resepObat->no_antrian }}
                            </td>
                            <td class="px-4 py-3 lg:p-4">
                                {{ $resepObat->keluhan_awal }}
                            </td>
                            <td class="px-4 py-3 lg:p-4 text-center">
                                {{ $resepObat->status ?? 'Tidak Ada' }}
                            </td>

                            <td class="px-4 py-3 lg:py-4 flex items-center justify-center ">
                                <div class="grid gap-4 w-44">
                                    <button type="button"
                                        @click="openModalUbahStatusKunjungan = !openModalUbahStatusKunjungan; idKunjungan={{ $resepObat->id }};"
                                        class="flex items-center gap-2 justify-center px-5 py-2.5 bg-green-700 text-white rounded-lg hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium text-sm dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                            width="24px" fill="#FFFFFF">
                                            <path
                                                d="m381-240 424-424-57-56-368 367-169-170-57 57 227 226Zm0 113L42-466l169-170 170 170 366-367 172 168-538 538Z" />
                                        </svg>
                                        <span class="inline-flex">Setujui</span>
                                    </button>

                                    <button type="button"
                                        @click="openModalTolakStatusKunjungan = !openModalTolakStatusKunjungan; idKunjungan={{ $resepObat->id }};"
                                        class="py-3 px-6 bg-red-500 text-white rounded-lg flex items-center justify-center gap-4 hover:bg-red-600 focus:ring-4 focus:ring-red-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                            width="24px" fill="#FFFFFF">
                                            <path
                                                d="m336-280 144-144 144 144 56-56-144-144 144-144-56-56-144 144-144-144-56 56 144 144-144 144 56 56ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z" />
                                        </svg>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $dataResepObat->links() }}

        <!-- Modal Setujui Tugas -->
        <div x-show="openModalUbahStatusKunjungan" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center overflow-y-auto overflow-x-hidden w-full">
            <form action="{{ route('testing.ubah.status.kunjungan') }}" method="post">
                @csrf
                <div class="relative p-4 w-full max-w-md h-full md:h-auto">
                    <!-- Modal content -->
                    <input type="text" class="hidden" name="id" :value=idKunjungan></input>
                    <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                        <button type="button" @click="openModalUbahStatusKunjungan = false"
                            class="text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="deleteModal">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                width="24px" fill="#FFFFFF">
                                <path
                                    d="m381-240 424-424-57-56-368 367-169-170-57 57 227 226Zm0 113L42-466l169-170 170 170 366-367 172 168-538 538Z" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>

                        <svg class="text-gray-400 dark:text-gray-500 w-11 h-11 mb-3.5 mx-auto"
                            xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                            fill="#000000">
                            <path
                                d="m424-318 282-282-56-56-226 226-114-114-56 56 170 170ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h168q13-36 43.5-58t68.5-22q38 0 68.5 22t43.5 58h168q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm280-590q13 0 21.5-8.5T510-820q0-13-8.5-21.5T480-850q-13 0-21.5 8.5T450-820q0 13 8.5 21.5T480-790ZM200-200v-560 560Z" />
                        </svg>

                        <p class="mb-4 text-gray-500 dark:text-gray-300">Apakah anda yakin untuk menyetujui project
                            ini??
                        </p>
                        <div class="flex justify-center items-center space-x-4">
                            <button @click="openModalUbahStatusKunjungan = false" type="button"
                                class="py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                                Tidak
                            </button>
                            <button type="submit"
                                class="py-2 px-3 text-sm font-medium text-center text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-red-900">
                                Ya, saya yakin
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Setujui Tugas -->
        <div x-show="openModalTolakStatusKunjungan" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center overflow-y-auto overflow-x-hidden w-full">
            <form action="{{ route('testing.batalkan.status.kunjungan') }}" method="post">
                @csrf
                <div class="relative p-4 w-full max-w-md h-full md:h-auto">
                    <input type="text" class="hidden" name="id" :value=idKunjungan></input>
                    <!-- Modal content -->
                    <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                        <button type="button" @click="openModalTolakStatusKunjungan = false"
                            class="text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="deleteModal">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                width="24px" fill="#FFFFFF">
                                <path
                                    d="m381-240 424-424-57-56-368 367-169-170-57 57 227 226Zm0 113L42-466l169-170 170 170 366-367 172 168-538 538Z" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>

                        <svg class="text-gray-400 dark:text-gray-500 w-11 h-11 mb-3.5 mx-auto"
                            xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                            width="24px" fill="#000000">
                            <path
                                d="m388-212-56-56 92-92-92-92 56-56 92 92 92-92 56 56-92 92 92 92-56 56-92-92-92 92ZM200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Z" />
                        </svg>
                        <p class="mb-4 text-gray-500 dark:text-gray-300">Apakah anda yakin untuk menyetujui project
                            ini??
                        </p>
                        <div class="flex justify-center items-center space-x-4">
                            <button @click="openModalTolakStatusKunjungan = false" type="button"
                                class="py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                                Tidak
                            </button>
                            <button type="submit"
                                class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
                                Ya, saya yakin
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- <Form action="{{ route('testing.create.kunjungan') }}" method="post">
            @csrf
            <label class="text-xl font-bold">Form Create Kunjungan</label>
            <div class="mt-5 mb-10">
                <div class="grid mb-4 gap-1">
                    <label>Nama Pasien</label>
                    <select name="pasien_id">
                        @foreach ($dataPasien as $pasien)
                            <option value="{{ $pasien->id }}">{{ $pasien->nama_pasien }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid mb-4 gap-1">
                    <label>Nama Dokter</label>
                    <select name="dokter_id">
                        @foreach ($dataDokter as $dokter)
                            <option value="{{ $dokter->id }}">{{ $dokter->nama_dokter }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid mb-4 gap-1">
                    <label>Tanggal Kunjungan</label>
                    <input type="date" name="tanggal_kunjungan"></input>
                </div>
                <div class="grid mb-4 gap-1">
                    <label>Keluhan Awal</label>
                    <textarea name="keluhan_awal" rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Tuliskan Keluhan Anda"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end mx-6">
                <button class="bg-blue-500 rounded-md text-white text-lg hover:bg-blue-600 focus:bg-blue-700 px-4 py-2">
                    Simpan
                </button>
            </div>
        </Form> --}}
    </div>
</body>

</html>
