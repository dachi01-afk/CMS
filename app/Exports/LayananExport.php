<?php

namespace App\Exports;

use App\Models\Layanan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LayananExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    protected array $columns;

    protected ?string $tanggalDari;

    protected ?string $tanggalSampai;

    protected array $availableColumns = [
        'nama_layanan' => 'Nama Layanan',
        'poli' => 'Poli',
        'harga_sebelum_diskon' => 'Harga Layanan',
        'kategori_layanan' => 'Kategori Layanan',
        'is_global' => 'Status Global',
        'created_at' => 'Tanggal Dibuat',
    ];

    public function __construct(array $columns = [], ?string $tanggalDari = null, ?string $tanggalSampai = null)
    {
        $this->columns = ! empty($columns)
            ? array_values(array_intersect($columns, array_keys($this->availableColumns)))
            : ['nama_layanan', 'poli', 'harga_sebelum_diskon', 'kategori_layanan'];

        $this->tanggalDari = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
    }

    public function collection(): Collection
    {
        $query = Layanan::query()
            ->with(['kategoriLayanan']);

        if ($this->tanggalDari) {
            $query->whereDate('created_at', '>=', $this->tanggalDari);
        }

        if ($this->tanggalSampai) {
            $query->whereDate('created_at', '<=', $this->tanggalSampai);
        }

        return $query->latest()
            ->get()
            ->map(function ($layanan) {
                $row = [];

                foreach ($this->columns as $column) {
                    $row[] = match ($column) {
                        'nama_layanan' => $layanan->nama_layanan ?? '-',

                        'poli' => $this->getPoliLabel($layanan),

                        'harga_sebelum_diskon' => 'Rp '.number_format(
                            (float) ($layanan->harga_sebelum_diskon ?? 0),
                            0,
                            ',',
                            '.'
                        ),

                        'kategori_layanan' => $layanan->kategoriLayanan->nama_kategori ?? '-',

                        'is_global' => (int) ($layanan->is_global ?? 0) === 1
                            ? 'Global'
                            : 'Spesifik Poli',

                        'created_at' => $layanan->created_at
                            ? Carbon::parse($layanan->created_at)->timezone('Asia/Jakarta')->format('d-m-Y H:i')
                            : '-',

                        default => '-',
                    };
                }

                return $row;
            });
    }

    public function headings(): array
    {
        return array_map(
            fn ($column) => $this->availableColumns[$column] ?? $column,
            $this->columns
        );
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    private function getPoliLabel($layanan): string
    {
        if ((int) ($layanan->is_global ?? 0) === 1) {
            return 'Semua Poli';
        }

        // Kalau nanti relasi poli banyak sudah ada, ini aman.
        if (method_exists($layanan, 'polis') && $layanan->relationLoaded('polis')) {
            $namaPoli = $layanan->polis
                ->pluck('nama_poli')
                ->filter()
                ->implode(', ');

            return $namaPoli ?: '-';
        }

        // Fallback kalau data table kamu memang cuma pakai poli_label dari server.
        return $layanan->poli_label ?? '-';
    }
}
