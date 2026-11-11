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

/**
 * Sheet "Rincian Layanan": satu baris per item order layanan.
 *
 * Harga satuan diambil dari kolom order_layanan_detail.harga_satuan, yaitu
 * harga yang tersimpan saat transaksi dibuat (bukan harga master layanan
 * yang berlaku sekarang).
 */
class TransaksiOrderLayananDetailSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithTitle
{
    protected Collection $rows;

    public function __construct(Collection|array $rows)
    {
        $this->rows = $rows instanceof Collection ? $rows : collect($rows);
    }

    public function title(): string
    {
        return 'Rincian Layanan';
    }

    public function collection(): Collection
    {
        $items = collect();

        foreach ($this->rows as $order) {
            foreach (($order->details ?? collect()) as $detail) {
                $items->push([
                    'order' => $order,
                    'detail' => $detail,
                ]);
            }
        }

        return $items;
    }

    public function headings(): array
    {
        return [
            'Kode Transaksi',
            'Tanggal Order',
            'Nama Pasien',
            'No. EMR',
            'Nama Layanan',
            'Harga Satuan (Rp)',
            'Qty',
            'Total Item (Rp)',
            'Status',
        ];
    }

    public function map($row): array
    {
        $order = $row['order'];
        $detail = $row['detail'];

        return [
            $order->kode_transaksi ?? '-',
            $order->tanggal_order
                ? Carbon::parse($order->tanggal_order)->format('d-m-Y H:i')
                : '-',
            $order->pasien->nama_pasien ?? '-',
            $order->pasien->no_emr ?? '-',
            $detail->layanan->nama_layanan ?? 'Layanan dihapus',
            (float) ($detail->harga_satuan ?? 0),
            (int) ($detail->qty ?? 0),
            (float) ($detail->total_harga_item ?? 0),
            $order->status_order_layanan ?? '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                if ($highestRow >= 2) {
                    $sheet->getStyle("A2:I{$highestRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    $sheet->getStyle("F2:H{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                $sheet->getStyle('D:D')->getNumberFormat()->setFormatCode('@');
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:I{$highestRow}");
            },
        ];
    }
}
