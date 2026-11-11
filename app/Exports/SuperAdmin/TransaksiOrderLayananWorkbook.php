<?php

namespace App\Exports\SuperAdmin;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Workbook laporan order layanan: sheet "Ringkasan" (satu baris per
 * transaksi) dan "Rincian Layanan" (satu baris per item layanan). Kedua
 * sheet memakai data yang sudah difilter, sehingga isinya selalu konsisten.
 */
class TransaksiOrderLayananWorkbook implements WithMultipleSheets
{
    protected Collection $rows;

    public function __construct(Collection|array $rows)
    {
        $this->rows = $rows instanceof Collection ? $rows : collect($rows);
    }

    public function sheets(): array
    {
        return [
            new TransaksiOrderLayananExport($this->rows),
            new TransaksiOrderLayananDetailSheet($this->rows),
        ];
    }
}
