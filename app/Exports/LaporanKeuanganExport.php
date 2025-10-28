<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithDrawings;

class LaporanKeuanganExport implements FromView, WithDrawings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data, $filter, $bulan, $tahun;

    public function __construct($data, $filter, $bulan, $tahun)
    {
        $this->data = $data;
        $this->filter = $filter;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        return view('export.laporan-keuangan', [
            'data' => $this->data,
            'filter' => $this->filter,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
        ]);
    }

    public function drawings()
    {
        $drawings = [];
        $row = 2; // baris pertama setelah header
        foreach ($this->data as $index => $item) {
            if ($item->bukti_pembayaran && file_exists(public_path('storage/' . $item->bukti_pembayaran))) {
                $drawing = new Drawing();
                $drawing->setName('Bukti Pembayaran ' . ($index + 1));
                $drawing->setDescription('Bukti Pembayaran');
                $drawing->setPath(public_path('storage/' . $item->bukti_pembayaran)); // path gambar
                $drawing->setHeight(80); // tinggi gambar
                $drawing->setCoordinates('P' . $row); // kolom P (misalnya kolom gambar)
                $drawings[] = $drawing;
            }
            $row++;

            // dd($item);
        }
        return $drawings;
    }
}
