<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">EMR</h2>
</div>

<div class="overflow-auto lg:my-2 rounded-lg shadow-slate-300 shadow-xl">
    <table class="w-full md:w-full md:text-sm text-center text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
            <tr class="">
                <th class="px-4 py-3 lg:p-4 ">No</th>
                <th class="px-4 py-3 lg:p-4 ">Nama Pasien</th>
                <th class="px-4 py-3 lg:p-4 ">Nama Dokter</th>
                <th class="px-4 py-3 lg:p-4 ">Nomor Antrian</th>
                <th class="px-4 py-3 lg:p-4 ">Tanggal Kunjungan</th>
                <th class="px-4 py-3 lg:p-4 ">Keluhan Awal</th>
                <th class="px-4 py-3 lg:p-4 ">Keluhan Utama</th>
                <th class="px-4 py-3 lg:p-4 ">Riwayat Penyakit Sekarang</th>
                <th class="px-4 py-3 lg:p-4 ">Riwayat Penyakit Dahulu</th>
                <th class="px-4 py-3 lg:p-4 ">Riwayat Penyakit Keluarga</th>
                <th class="px-4 py-3 lg:p-4 ">Tekanan Darah</th>
                <th class="px-4 py-3 lg:p-4 ">Suhu Tubuh</th>
                <th class="px-4 py-3 lg:p-4 ">Denyut Nadi</th>
                <th class="px-4 py-3 lg:p-4 ">Detak Pernapasan</th>
                <th class="px-4 py-3 lg:p-4 ">Saturasi Oksigen</th>
                <th class="px-4 py-3 lg:p-4 ">Diagnosis</th>
                <th class="px-4 py-3 lg:p-4 ">Nama Obat</th>
                <th class="px-4 py-3 lg:p-4 ">Jumlah Obat</th>
                <th class="px-4 py-3 lg:p-4 ">Keterangan</th>
                <th class="px-4 py-3 lg:p-4 ">Obat</th>
                <th class="px-4 py-3 lg:p-4 ">Status</th>
                <th class="mx-4 py-3 lg:p-4 ">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dataEMR as $emr)
                <tr class="xl:text-base">
                    <td class="px-4 py-3 lg:px-8">{{ $dataEMR->firstItem() + $loop->index }}
                    </td>
                    <td class="px-4 py-3 lg:py-4 text-center">
                        {{ $emr->kunjungan->pasien->nama_pasien }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->kunjungan->dokter->nama_dokter }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->kunjungan->no_antrian }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->kunjungan->tanggal_kunjungan }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->kunjungan->keluhan_awal }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->keluhan_utama }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->riwayat_penyakit_sekarang }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->riwayat_penyakit_dahulu }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->riwayat_penyakit_keluarga }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->tekanan_darah }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->suhu_tubuh }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->nadi }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->pernapasan }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->saturasi_oksigen }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        {{ $emr->diagnosis }}
                    </td>
                    <td class="px-4 py-3 lg:p-4">

                        {{-- {{ $emr->resep->obat->nama_obat }} --}}
                        @foreach ($emr->resep->obat as $obat)
                            <div>{{ $obat->nama_obat }}</div>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        @foreach ($emr->resep->obat as $obat)
                            <div>{{ $obat->pivot->jumlah }}</div>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 lg:p-4">
                        @foreach ($emr->resep->obat as $obat)
                            <div>{{ $obat->pivot->keterangan }}</div>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 lg:p-4 text-center">
                        @foreach ($emr->resep->obat as $obat)
                            <div>{{ $obat->pivot->status }}</div>
                        @endforeach
                    </td>

                    <td class="px-4 py-3 lg:py-4 flex items-center justify-center ">
                        <div class="grid gap-4 w-44">
                            <button type="button"
                                @click="openModalUbahStatusKunjungan = !openModalUbahStatusKunjungan; idKunjungan={{ $emr->id }};"
                                class="flex items-center gap-2 justify-center px-5 py-2.5 bg-green-700 text-white rounded-lg hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium text-sm dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                    width="24px" fill="#FFFFFF">
                                    <path
                                        d="m381-240 424-424-57-56-368 367-169-170-57 57 227 226Zm0 113L42-466l169-170 170 170 366-367 172 168-538 538Z" />
                                </svg>
                                <span class="inline-flex">Setujui</span>
                            </button>

                            <button type="button"
                                @click="openModalTolakStatusKunjungan = !openModalTolakStatusKunjungan; idKunjungan={{ $emr->id }};"
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
{{ $dataEMR->links() }}
