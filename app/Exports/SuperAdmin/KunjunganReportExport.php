<?php

namespace App\Exports\SuperAdmin;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class KunjunganReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithTitle
{
    protected Collection $rows;

    public function __construct(Collection|array $rows)
    {
        $this->rows = $rows instanceof Collection ? $rows : collect($rows);
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Laporan Kunjungan';
    }

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

    public function map($row): array
    {
        return [
            str_pad((string) ($row->no_antrian ?? ''), 3, '0', STR_PAD_LEFT),
            $row->nama_dokter ?? '-',
            $row->nama_pasien ?? '-',
            $row->nama_poli ?? '-',
            $row->keluhan_awal ?? '-',
            $row->status ?? '-',
            $row->tanggal_kunjungan
                ? Carbon::parse($row->tanggal_kunjungan)->format('d-m-Y')
                : '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                if ($highestRow >= 2) {
                    $sheet->getStyle("A2:G{$highestRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }

                $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode('@');
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:G{$highestRow}");
            },
        ];
    }
}
