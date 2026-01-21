<?php

namespace App\Exports;

use App\Models\BahanHabisPakai;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PenggunaanBhpExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        // Memanggil scope yang sudah kita buat tadi
        return BahanHabisPakai::getDataPenggunaanBhp($this->filters);
    }

    // Menentukan Judul Kolom di Excel
    public function headings(): array
    {
        return [
            'Nama Barang',
            'Penggunaan Umum (Qty)',
            'Nominal Umum',
            'Sisa Stok',
        ];
    }

    // Memetakan data dari database ke kolom Excel
    public function map($row): array
    {
        $nominal = ($row->total_pakai_umum ?? 0) * ($row->harga_jual_umum_bhp ?? 0);

        return [
            $row->nama_barang,
            $row->total_pakai_umum ?? 0,
            $nominal,
            $row->stok_barang,
        ];
    }
}
