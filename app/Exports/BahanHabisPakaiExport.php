<?php

namespace App\Exports;

use App\Models\BahanHabisPakai;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BahanHabisPakaiExport implements
    FromQuery,
    WithMapping,
    WithHeadings,
    WithCustomStartCell,
    WithEvents,
    ShouldAutoSize
{
    private int $rowNumber = 0;

    public function query()
    {
        // Export SEMUA DATA (lebih aman pakai query daripada get() untuk data besar)
        return BahanHabisPakai::query()
            ->with(['brandFarmasi', 'satuanBHP'])
            ->latest();
    }

    public function startCell(): string
    {
        // Baris 1-3 untuk judul + info
        // Heading tabel mulai baris 4
        return 'A4';
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode',
            'Nama Barang',
            'Brand Farmasi',
            'Stok',
            'Harga Umum',
            'Harga Beli',
            'Avg HPP',
            'Harga OTC',
            'Margin Profit',
        ];
    }

    public function map($bhp): array
    {
        $this->rowNumber++;

        // Pastikan 0 tetap tampil (jangan pakai empty())
        $stok = is_null($bhp->stok_barang) ? 0 : (int) $bhp->stok_barang;

        // Nama satuan (fallback biar gak null)
        $satuan = optional($bhp->satuanBHP)->nama_satuan_obat
            ?? optional($bhp->satuanBHP)->nama_satuan
            ?? 'pcs';

        $hargaJual = $bhp->harga_jual_umum_bhp ?? 0;
        $hpp       = $bhp->avg_hpp_bhp ?? 0;
        $margin    = $hargaJual - $hpp;

        return [
            $this->rowNumber,
            $bhp->kode ?? '-',
            $bhp->nama_barang ?? '-',
            optional($bhp->brandFarmasi)->nama_brand ?? '-',
            $stok . ' ' . $satuan,

            // Rupiah string (biar tampilan pasti sesuai Indonesia)
            'Rp' . number_format((float)($bhp->harga_jual_umum_bhp ?? 0), 2, ',', '.'),
            'Rp' . number_format((float)($bhp->harga_beli_satuan_bhp ?? 0), 2, ',', '.'),
            'Rp' . number_format((float)($bhp->avg_hpp_bhp ?? 0), 2, ',', '.'),
            'Rp' . number_format((float)($bhp->harga_otc_bhp ?? 0), 2, ',', '.'),
            'Rp ' . number_format((float)$margin, 0, ',', '.'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'J'; // Karena kolom A..J = 10 kolom

                // ===== Judul & Info di atas =====
                $tanggalExport = Carbon::now('Asia/Jakarta')->format('d/m/Y H:i');

                $sheet->setCellValue('A1', 'LAPORAN DATA STOK BAHAN HABIS PAKAI');
                $sheet->mergeCells("A1:{$lastColumn}1");

                // Karena export semua data, periode kita tulis "Semua Data"
                $sheet->setCellValue('A2', "Periode/Filter: Semua Data  |  Tanggal Export: {$tanggalExport}");
                $sheet->mergeCells("A2:{$lastColumn}2");

                $sheet->setCellValue('A3', 'Royal Klinik.id');
                $sheet->mergeCells("A3:{$lastColumn}3");

                // ===== Styling Judul =====
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle("A2:{$lastColumn}3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ===== Styling Header Tabel (baris 4) =====
                $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // Auto filter
                $sheet->setAutoFilter("A4:{$lastColumn}4");

                // Border untuk semua data (mulai baris 4 sampai last row)
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:{$lastColumn}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // Rata kanan untuk kolom harga (F..J)
                $sheet->getStyle("F5:J{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Rata tengah untuk No (A) dan Stok (E)
                $sheet->getStyle("A5:A{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("E5:E{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Lebar kolom manual kecil (opsional, tapi enak dibaca)
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(28);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(16);
                $sheet->getColumnDimension('G')->setWidth(16);
                $sheet->getColumnDimension('H')->setWidth(16);
                $sheet->getColumnDimension('I')->setWidth(16);
                $sheet->getColumnDimension('J')->setWidth(16);
            }
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => ['font' => ['bold' => true]], // baris heading
        ];
    }
}
