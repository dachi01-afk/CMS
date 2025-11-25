<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KunjunganExport implements FromCollection, WithHeadings
{
    protected $dataKunjungan;

    // 💡 terima collection yang sudah difilter
    public function __construct(Collection $dataKunjungan)
    {
        $this->dataKunjungan = $dataKunjungan;
    }

    /**
     * Data untuk Excel
     */
    public function collection()
    {
        return $this->dataKunjungan->map(function ($item) {
            return [
                'No Antrian'        => $item->no_antrian ?? '-',
                'Nama Dokter'       => $item->dokter->nama_dokter ?? '-',
                'Nama Pasien'       => $item->pasien->nama_pasien ?? '-',
                'Poli Tujuan'       => $item->poli->nama_poli ?? '-',
                'Keluhan Awal'      => $item->keluhan_awal ?? '-',
                'Status'            => ucfirst($item->status ?? '-'),
                'Tanggal Kunjungan' => $item->tanggal_kunjungan
                    ? Carbon::parse($item->tanggal_kunjungan)->format('d-m-Y H:i')
                    : '-',
            ];
        });
    }

    /**
     * Header kolom di Excel
     */
    public function headings(): array
    {
        return [
            'No Antrian',
            'Nama Dokter',
            'Nama Pasien',
            'Poli Tujuan',
            'Keluhan Awal',
            'Status',
            'Tanggal Kunjungan',
        ];
    }
}
