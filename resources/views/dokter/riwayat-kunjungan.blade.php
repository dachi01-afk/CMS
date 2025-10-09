<x-layout-dokter>

    <x-slot:header>
        {{ $header }}
    </x-slot:header>

    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg relative md:mx-4 lg:mx-4 xl:my-8 xl:mx-4"
        x-data="{ openModalCreateKunjungan: }">
        {{-- <div class="flex flex-col items-center p-4 md:flex-row md:space-y-0 lg:justify-between">
            <div class="hidden lg:inline lg:w-auto">
                <button type="button" @click="openModalCreateTugas = !openModalCreateTugas;"
                    class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 flex items-center gap-2 justify-between">
                    <span>Tambahkan Tugas</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                        fill="#FFFFFF">
                        <path
                            d="M440-280h80v-160h160v-80H520v-160h-80v160H280v80h160v160Zm40 200q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z" />
                    </svg>
                </button>
            </div>

            <div class="w-full md:w-full lg:w-1/2">
                <form action="#" method="GET">
                    <div
                        class="items-center mx-auto space-y-4 max-w-screen-sm sm:flex sm:space-y-0 lg:mb-0    lg:mx-0 lg:max-w-screen-lg">
                        <div class="relative w-full">
                            <label for="search"
                                class="hidden mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 ">Search</label>
                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                <svg class="w-6 h-6 text-gray-800 dark:text-white " aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                    viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                        d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>

                            <input autocomplete="off" type="text"
                                class="block p-3 pl-10 w-full text-sm md:block md:w-full text-gray-900 
                                          bg-gray-50 rounded-lg border border-gray-300 sm:rounded-none 
                                          sm:rounded-l-lg  focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 
                                          dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Cara Data" type="search" id="search" name="search">
                        </div>
                        <div>
                            <button type="submit"
                                class="py-3 px-5 w-full text-sm font-medium text-center text-white rounded-lg border cursor-pointer bg-blue-700 border-blue-600 sm:rounded-none sm:rounded-r-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div> --}}

        <div class="overflow-auto lg:my-2 ">
            <table class="w-full table-fixed md:w-full md:text-sm text-center  text-gray-500 dark:text-gray-400 ">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr class="">
                        <th class="w-12 px-4 py-3 lg:p-4 ">No</th>
                        <th class="w-1/3 px-4 py-3 lg:p-4 ">Nama Pasien</th>
                        <th class="w-1/3 px-4 py-3 lg:p-4 ">Nama Dokter</th>
                        <th class="w-1/3 px-4 py-3 lg:p-4 ">Keluhan Awal</th>
                        <th class="w-1/3 px-4 py-3 lg:p-4 ">No Antrian</th>
                        <th class="w-1/3 px-4 py-3 lg:p-4 ">Tanggal Kunjungan</th>
                        <th class="w-1/3 px-4 py-3 lg:p-4 ">Status</th>
                        <th class="w-1/3 px-4 py-3 lg:p-4 ">Aksi</th>
                        {{-- <th class="w-56 mx-4 py-3 lg:p-4 ">Action</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataKunjungan as $kunjungan)
                        <tr class="xl:text-base">
                            <td class="px-4 py-3 lg:p-0">{{ $dataKunjungan->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 lg:p-0">
                                {{ $kunjungan->pasien->nama_pasien }}
                            </td>
                            <td class="px-4 py-3 lg:p-0">
                                {{ $kunjungan->dokter->nama_dokter }}
                            </td>
                            <td class="px-4 py-3 lg:p-0">
                                {{ $kunjungan->keluhan_awal }}
                            </td>
                            <td class="px-4 py-3 lg:p-0">
                                {{ $kunjungan->no_antrian }}
                            </td>
                            <td class="px-4 py-3 lg:p-0">
                                {{ $kunjungan->tanggal_kunjungan }}
                            </td>
                            <td class="px-4 py-3 lg:p-0">
                                {{ $kunjungan->status }}
                            </td>
                            <td class="px-4 py-3 lg:py-4 text-center">
                                <a href="#"
                                    class="inline-flex items-center justify-center w-44 py-2 px-3 bg-amber-500 text-white rounded-lg hover:bg-amber-600 focus:ring-4 focus:ring-amber-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                        width="24px" fill="#FFFFFF">
                                        <path
                                            d="M480-120q-75 0-140.5-28.5t-114-77q-48.5-48.5-77-114T120-480q0-75 28.5-140.5t77-114q48.5-48.5 114-77T480-840q82 0 155.5 35T760-706v-94h80v240H600v-80h110q-41-56-101-88t-129-32q-117 0-198.5 81.5T200-480q0 117 81.5 198.5T480-200q105 0 183.5-68T756-440h82q-15 137-117.5 228.5T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z" />
                                    </svg>
                                    <span>Lihat Detail Project</span>
                                </a>
                            </td>

                            {{-- <td class="px-4 py-3 lg:py-4 flex items-center justify-center ">
                                <div class="grid gap-4">
                                    <button type="button"
                                        class="py-3 px-6 bg-amber-500 text-white rounded-lg flex items-center justify-center gap-4 hover:bg-amber-600 focus:ring-4 focus:ring-amber-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                            width="24px" fill="#FFFFFF">
                                            <path
                                                d="M480-120q-75 0-140.5-28.5t-114-77q-48.5-48.5-77-114T120-480q0-75 28.5-140.5t77-114q48.5-48.5 114-77T480-840q82 0 155.5 35T760-706v-94h80v240H600v-80h110q-41-56-101-88t-129-32q-117 0-198.5 81.5T200-480q0 117 81.5 198.5T480-200q105 0 183.5-68T756-440h82q-15 137-117.5 228.5T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z" />
                                        </svg>
                                        <span>Ubah</span>
                                    </button>

                                    <button type="button"
                                        class="py-3 px-6 bg-red-500 text-white rounded-lg flex items-center justify-center gap-4 hover:bg-red-600 focus:ring-4 focus:ring-red-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                            width="24px" fill="#FFFFFF">
                                            <path
                                                d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $dataKunjungan->links() }}

        {{-- Modal Tambahkan Tugas --}}
        {{-- <div x-show="openModalCreateTugas" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg w-[700px]">
                <h2 class="text-xl font-bold mb-4">Upload Tugas Baru</h2>

                <form action="#" method="POST" class="grid gap-4">
                    @csrf
                    <div class="mb-4">
                        <label class="block mb-1 text-sm font-medium">Nama Tugas</label>
                        <input type="text" name="nama_tugas"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring">
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="openModalCreateTugas = false"
                            class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                            Batal
                        </button>
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div> --}}

        {{-- Modal Update Tugas --}}
        {{-- <div x-show="openModalUpdateTugas" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg w-[700px]">
                <h2 class="text-xl font-bold mb-4">Update Tugas </h2>

                <form action="#" method="POST" class="grid gap-4">
                    @csrf

                    <input type="hidden" name="tugas_id" :value=dataTugas.id></input>
                    <input type="hidden" name="admin_id" :value=dataTugas.admin.id></input>

                    <div class="mb-4">
                        <label class="block mb-1 text-sm font-medium">Nama Tugas</label>
                        <input type="text" name="nama_tugas" :value=dataTugas.nama_tugas
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring">
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="openModalUpdateTugas = false"
                            class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                            Batal
                        </button>
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div> --}}

        <!-- Modal Delete Tugas -->
        {{-- <div x-show="openModalDeleteTugas" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center overflow-y-auto overflow-x-hidden w-full">
            <form action="#" method="post">
                @csrf
                <input type="hidden" :value=idTugas name="id"></input>
                <div class="relative p-4 w-full max-w-md h-full md:h-auto">
                    <!-- Modal content -->
                    <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                        <button type="button" @click="openModalDeleteTugas = false"
                            class="text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="deleteModal">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                        <svg class="text-gray-400 dark:text-gray-500 w-11 h-11 mb-3.5 mx-auto" aria-hidden="true"
                            fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <p class="mb-4 text-gray-500 dark:text-gray-300">Apakah anda yakin untuk menghapus tugas ini??
                        </p>
                        <div class="flex justify-center items-center space-x-4">
                            <button data-modal-toggle="deleteModal" type="button"
                                @click="openModalDeleteTugas = false"
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
        </div> --}}
    </div>

</x-layout-dokter>
