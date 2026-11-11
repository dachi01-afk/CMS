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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TransaksiOrderLayananExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithTitle
{
    protected Collection $rows;

    protected int $index = 0;

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
        return 'Ringkasan';
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Transaksi',
            'Tanggal Order',
            'No. EMR',
            'Nama Pasien',
            'Metode Pembayaran',
            'Jumlah Item',
            'Rincian Layanan',
            'Subtotal (Rp)',
            'Potongan (Rp)',
            'Total Bayar (Rp)',
            'Status',
            'Tanggal Pembayaran',
        ];
    }

    public function map($row): array
    {
        $details = $row->details ?? collect();

        $rincian = $details
            ->map(function ($detail) {
                $namaLayanan = $detail->layanan->nama_layanan ?? 'Layanan dihapus';

                return $namaLayanan . ' x' . (int) ($detail->qty ?? 0);
            })
            ->filter()
            ->implode(', ');

        $subtotal = (float) ($row->subtotal ?? 0);

        if ($subtotal <= 0) {
            $subtotal = (float) ($row->total_item_value ?? $details->sum('total_harga_item'));
        }

        $this->index++;

        return [
            $this->index,
            $row->kode_transaksi ?? '-',
            $row->tanggal_order
                ? Carbon::parse($row->tanggal_order)->format('d-m-Y H:i')
                : '-',
            $row->pasien->no_emr ?? '-',
            $row->pasien->nama_pasien ?? '-',
            $row->metodePembayaran->nama_metode ?? '-',
            (int) ($row->total_qty ?? $details->sum('qty')),
            $rincian ?: '-',
            $subtotal,
            (float) ($row->potongan_pesanan ?? 0),
            (float) ($row->total_bayar ?? 0),
            $row->status_order_layanan ?? '-',
            $row->tanggal_pembayaran
                ? Carbon::parse($row->tanggal_pembayaran)->format('d-m-Y H:i')
                : '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('A1:M1')->applyFromArray([
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
                    $sheet->getStyle("A2:M{$highestRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // PhpSpreadsheet melewati nilai 0 saat menulis array (perbandingan
                    // longgar: 0 == null), jadi kolom Potongan ditulis ulang eksplisit
                    // sebagai angka 0 supaya tidak kosong seperti di tampilan web.
                    for ($row = 2; $row <= $highestRow; $row++) {
                        if ($sheet->getCell('J' . $row)->getValue() === null) {
                            $sheet->setCellValueExplicit('J' . $row, 0, DataType::TYPE_NUMERIC);
                        }
                    }

                    $sheet->getStyle("I2:K{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                $sheet->getStyle('D:D')->getNumberFormat()->setFormatCode('@');
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:M{$highestRow}");
            },
        ];
    }
}
