<?php

namespace App\DataTables;

use App\Models\MutasiStokObat;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class MutasiStokObatDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<MutasiStokObat> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($row) {
                return '
            <div class="flex items-center justify-center space-x-2">
                <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md text-xs transition duration-200">
                    <i class="fas fa-eye mr-1"></i> Detail
                </button>
                <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-md text-xs transition duration-200">
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
            </div>';
            })
            ->editColumn('jenis_transaksi', function ($row) {
                // Memberi warna badge berdasarkan jenis transaksi (RESTOCK atau RETURN)
                $class = $row->jenis_transaksi === 'RESTOCK'
                    ? 'bg-green-100 text-green-700 border-green-200'
                    : 'bg-red-100 text-red-700 border-red-200';

                return '<span class="px-2.5 py-0.5 rounded-full border text-[11px] font-semibold ' . $class . '">'
                    . $row->jenis_transaksi .
                    '</span>';
            })
            ->editColumn('tanggal_transaksi', function ($row) {
                return \Carbon\Carbon::parse($row->tanggal_transaksi)->format('d/m/Y');
            })
            ->rawColumns(['action', 'jenis_transaksi']) // Supaya HTML tidak dianggap teks biasa
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<MutasiStokObat>
     */
    public function query(MutasiStokObat $model): QueryBuilder
    {
        return $model->newQuery()->with(['supplier', 'farmasi'])->select('mutasi_stok_obat.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('mutasistokobat-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->addClass('text-center font-bold'),
            Column::make('nomor_transaksi')->title('No. Transaksi'),
            Column::make('nomor_faktur')->title('No. Faktur'),
            Column::make('jenis_transaksi')->title('Jenis')->addClass('text-center'),
            Column::make('tanggal_transaksi')->title('Tgl Transaksi'),
            Column::make('keterangan')->title('Keterangan'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(160)
                ->addClass('text-center')
                ->title('Aksi'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'MutasiStokObat_' . date('YmdHis');
    }
}
