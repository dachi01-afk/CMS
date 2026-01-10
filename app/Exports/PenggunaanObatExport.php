<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PenggunaanObatExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    /** @var EloquentBuilder|QueryBuilder */
    protected $query;

    protected string $title;

    public function __construct(EloquentBuilder|QueryBuilder $query, string $title = 'Laporan Penggunaan Obat')
    {
        $this->query = $query;
        $this->title = $title;
    }

    public function query()
    {
        return $this->query;
    }

    /**
     * Header mulai dari A2 (karena A1 dipakai untuk judul)
     */
    public function startCell(): string
    {
        return 'A2';
    }

    public function headings(): array
    {
        return [
            'Nama Obat',
            'Kandungan',
            'Satuan',
            'Penggunaan Umum',
            'Nominal Umum',
            'Penggunaan BPJS',
            'Nominal BPJS',
            'Sisa Obat',
        ];
    }

    /**
     * Pastikan value null/kosong jadi 0 (biar Excel tidak blank)
     */
    private function n($value): float|int
    {
        if ($value === null) return 0;
        if ($value === '') return 0;
        if (is_string($value) && trim($value) === '') return 0;

        // kalau string angka "0" / "0.00" aman
        return is_numeric($value) ? (0 + $value) : 0;
    }

    public function map($row): array
    {
        $satuan = $row->satuan ?? 'Unit';

        $penggunaanUmum = $this->n($row->penggunaan_umum ?? null);
        $nominalUmum    = $this->n($row->nominal_umum ?? null);
        $penggunaanBpjs = $this->n($row->penggunaan_bpjs ?? null);
        $nominalBpjs    = $this->n($row->nominal_bpjs ?? null);
        $sisa           = $this->n($row->sisa_obat ?? null);

        return [
            $row->nama_obat ?? '',
            $row->kandungan_obat ?? '',
            $satuan,

            // tampil seperti view
            $penggunaanUmum . ' ' . $satuan,
            'Rp ' . number_format($nominalUmum, 0, ',', '.'),

            $penggunaanBpjs . ' ' . $satuan,
            'Rp ' . number_format($nominalBpjs, 0, ',', '.'),

            $sisa . ' ' . $satuan, // opsional: kalau kamu mau sisa juga ikut ada satuan
        ];
    }


    public function columnFormats(): array
    {
        return [
            // Nominal columns
            'E' => '"Rp" #,##0',
            'G' => '"Rp" #,##0',

            // Qty columns
            'D' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul di A1, merge sampai H1
                $sheet->setCellValue('A1', $this->title);
                $sheet->mergeCells('A1:H1');

                // Styling judul
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Styling header (row 2)
                $sheet->getStyle('A2:H2')->getFont()->setBold(true);
                $sheet->freezePane('A3'); // biar judul+header tetap
            },
        ];
    }
}
