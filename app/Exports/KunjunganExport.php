<?php

namespace App\Exports;

use App\Models\Kunjungan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KunjunganExport implements FromCollection, WithHeadings
{
    protected $periode;

    public function __construct($periode)
    {
        $this->periode = $periode;
    }

    /**
     * Ambil data kunjungan beserta relasinya (pasien, poli, dokter)
     */
    public function collection()
    {
        // Mulai query dasar (belum diambil datanya)
        $query = Kunjungan::with(['poli.dokter', 'pasien']);

        // Filter berdasarkan periode
        switch ($this->periode) {
            case 'minggu':
                $query->whereBetween('tanggal_kunjungan', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;

            case 'bulan':
                $query->whereMonth('tanggal_kunjungan', Carbon::now()->month)
                      ->whereYear('tanggal_kunjungan', Carbon::now()->year);
                break;

            case 'tahun':
                $query->whereYear('tanggal_kunjungan', Carbon::now()->year);
                break;

            default:
                // jika tidak pilih filter, ambil semua data
                break;
        }

        // Ambil hasil query setelah filter
        $dataKunjungan = $query->get();

        // Ubah format data jadi array sederhana untuk export Excel
        return $dataKunjungan->map(function ($item) {
            return [
                'No Antrian' => $item->no_antrian ?? '-',
                'Nama Dokter' => $item->poli->dokter->nama_dokter ?? '-',
                'Nama Pasien' => $item->pasien->nama_pasien ?? '-',
                'Poli Tujuan' => $item->poli->nama_poli ?? '-',
                'Keluhan Awal' => $item->keluhan_awal ?? '-',
                'Status' => ucfirst($item->status ?? '-'),
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
