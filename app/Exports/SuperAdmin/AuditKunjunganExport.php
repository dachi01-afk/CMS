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

class AuditKunjunganExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithTitle
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
        return 'Laporan Audit Kunjungan';
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Kunjungan',
            'No. Antrian',
            'No. EMR',
            'Nama Pasien',
            'Poli',
            'Dokter',
            'Kunjungan Diinput Oleh',
            'Role Input',
            'Jam Input Kunjungan',
            'Perawat Pemeriksaan Awal',
            'Jam Pemeriksaan Awal',
            'Diagnosis',
            'Tindakan Dokter',
            'Total Item Tindakan',
            'Kode Pembayaran',
            'Total Pembayaran (Rp)',
            'Metode Pembayaran',
            'Status Pembayaran',
            'Tanggal Pembayaran',
            'Kasir',
            'Status Resep',
            'Farmasi Penyerah Obat',
            'Jam Serah Obat',
            'Obat Diserahkan',
            'Status Kunjungan',
        ];
    }

    public function map($row): array
    {
        $this->index++;

        return [
            $this->index,
            $row->tanggal_kunjungan
                ? Carbon::parse($row->tanggal_kunjungan)->format('d-m-Y')
                : '-',
            $row->no_antrian ?? '-',
            $row->no_emr ?? '-',
            $row->nama_pasien ?? '-',
            $row->nama_poli ?? '-',
            $row->nama_dokter ?? '-',
            $row->input_oleh ?? '-',
            $row->input_oleh_role ?? '-',
            $row->kunjungan_dibuat
                ? Carbon::parse($row->kunjungan_dibuat)->format('d-m-Y H:i')
                : '-',
            $row->nama_perawat ?? '-',
            $row->emr_dibuat
                ? Carbon::parse($row->emr_dibuat)->format('d-m-Y H:i')
                : '-',
            $row->diagnosis ?? '-',
            $row->tindakan ?: '-',
            (int) ($row->total_tindakan ?? 0),
            $row->kode_pembayaran ?? '-',
            (float) ($row->total_pembayaran ?? 0),
            $row->metode_pembayaran ?? '-',
            $row->status_pembayaran ?? '-',
            $row->tanggal_pembayaran
                ? Carbon::parse($row->tanggal_pembayaran)->format('d-m-Y H:i')
                : '-',
            $row->kasir ?? '-',
            $row->status_resep === 'done' ? 'Sudah Diambil' : ($row->status_resep === 'waiting' ? 'Menunggu' : '-'),
            $row->farmasi ?? '-',
            $row->farmasi_pada
                ? Carbon::parse($row->farmasi_pada)->format('d-m-Y H:i')
                : '-',
            $row->obat ?: '-',
            $row->status ?? '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('A1:Z1')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                if ($highestRow >= 2) {
                    $sheet->getStyle("A2:Z{$highestRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    $sheet->getStyle("Q2:Q{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle('D:D')->getNumberFormat()->setFormatCode('@');
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:Z{$highestRow}");
            },
        ];
    }
}
