<?php

namespace App\Exports;

use App\Models\Obat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class ObatExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function collection()
    {
        $data = collect();
        $no = 1;

        $obats = Obat::with([
            'brandFarmasi',
            'kategoriObat',
            'jenisObat',
            'satuanObat',
            'depotObat.tipeDepot'
        ])->get();

        foreach ($obats as $obat) {

            // Multi-line info depot
            $infoDepot = $obat->depotObat->map(function ($depot) {
                return
                    $depot->nama_depot . ' | ' .
                    ($depot->tipeDepot->nama_tipe_depot ?? '-') . ' | ' .
                    ($depot->jumlah_stok_depot ?? 0);
            })->implode("\n");

            // 🔹 FORMAT RUPIAH
            $totalHarga = 'Rp ' . number_format($obat->total_harga, 0, ',', '.');
            $hargaJual  = 'Rp ' . number_format($obat->harga_jual_obat, 0, ',', '.');
            $hargaOtc   = 'Rp ' . number_format($obat->harga_otc_obat, 0, ',', '.');

            $data->push([
                $no++,
                $obat->kode_obat,
                $obat->nama_obat,
                $obat->brandFarmasi->nama_brand ?? '-',
                $obat->kategoriObat->nama_kategori_obat ?? '-',
                $obat->jenisObat->nama_jenis_obat ?? '-',
                $obat->satuanObat->nama_satuan_obat ?? '-',
                $obat->kandungan_obat,
                $obat->tanggal_kadaluarsa_obat,
                $obat->nomor_batch_obat,
                $obat->jumlah,
                $obat->dosis,
                $totalHarga,
                $hargaJual,
                $hargaOtc,
                $infoDepot,
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "No",
            "Kode Obat",
            "Nama Obat",
            "Brand Farmasi",
            "Kategori Obat",
            "Jenis Obat",
            "Satuan Obat",
            "Kandungan Obat",
            "Tanggal Kadaluarsa Obat",
            "Nomor Batch Obat",
            "Stok Global Obat",
            "Dosis Obat",
            "Total Harga",
            "Harga Jual Obat",
            "Harga OTC Obat",
            "Informasi Depot (Nama | Tipe | Stok)"
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header bold + center
        $sheet->getStyle('A1:P1')->getFont()->setBold(true);
        $sheet->getStyle('A1:P1')->getAlignment()->setHorizontal('center')->setVertical('center');

        // Wrap text untuk semua cell
        $sheet->getStyle('A1:P' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 25,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 15,
            'H' => 25,
            'I' => 18,
            'J' => 18,
            'K' => 12,
            'L' => 12,
            'M' => 20,
            'N' => 20,
            'O' => 20,
            'P' => 40,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto filter
                $sheet->setAutoFilter('A1:P1');

                // Auto row height
                foreach (range(2, $sheet->getHighestRow()) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }

                // Format kolom harga (M, N, O) jadi rupiah di Excel
                $sheet->getStyle('M2:O' . $sheet->getHighestRow())
                    ->getNumberFormat()
                    ->setFormatCode('"Rp"#,##0;[Red]-"Rp"#,##0');
            },
        ];
    }
}
