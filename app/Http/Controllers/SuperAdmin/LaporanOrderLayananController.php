<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\SuperAdmin\TransaksiOrderLayananWorkbook;
use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\OrderLayanan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LaporanOrderLayananController extends Controller
{
    private const STATUS_OPTIONS = ['Sudah Bayar', 'Belum Bayar'];

    /** Batas jumlah opsi yang dikirim ke TomSelect per pencarian. */
    private const SEARCH_LIMIT = 20;

    private function ensureManager(): void
    {
        $user = Auth::user();

        $role = strtolower(trim((string) ($user->role ?? '')));

        $isManager = in_array($role, ['super admin', 'admin'], true)
            || (bool) ($user->is_super_admin ?? false);

        abort_unless($user && $isManager, 403, 'Hanya Super Admin dan Admin yang dapat mengakses laporan ini.');
    }

    public function index()
    {
        $this->ensureManager();

        return view('super-admin.laporan-order-layanan.laporan-order-layanan');
    }

    public function data(Request $request)
    {
        $this->ensureManager();

        $query = $this->buildQuery($request);

        $summary = [
            'total_transaksi' => (clone $query)->count(),
            'total_pasien' => (clone $query)->distinct()->count('pasien_id'),
            'total_sudah_bayar' => (clone $query)
                ->where('status_order_layanan', 'Sudah Bayar')
                ->sum('total_bayar'),
            'total_belum_bayar' => (clone $query)
                ->where('status_order_layanan', 'Belum Bayar')
                ->sum('total_bayar'),
        ];

        // Pencarian teks hanya diterapkan sekali (di buildQuery), supaya
        // recordsFiltered tabel selalu sama dengan angka di kartu ringkasan.
        $this->neutralizeGlobalSearch($request);

        // Tanpa order default di sini agar pengurutan dari klik header (order[0])
        // benar-benar diterapkan; default dipasang di sisi DataTables.
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('kode_transaksi', fn ($row) => $row->kode_transaksi ?? '-')
            ->editColumn('tanggal_order', function ($row) {
                return $row->tanggal_order
                    ? Carbon::parse($row->tanggal_order)->locale('id')->translatedFormat('d F Y H:i')
                    : '-';
            })
            ->addColumn('nama_pasien', fn ($row) => $row->pasien->nama_pasien ?? '-')
            ->addColumn('no_emr', fn ($row) => $row->pasien->no_emr ?? '-')
            ->addColumn('metode_pembayaran', fn ($row) => $row->metodePembayaran->nama_metode ?? '-')
            ->addColumn('total_qty', function ($row) {
                return (int) ($row->total_qty ?? $row->details->sum('qty'));
            })
            ->addColumn('subtotal_rp', fn ($row) => $this->rupiah($this->subtotalOrder($row)))
            ->addColumn('potongan_rp', fn ($row) => $this->rupiah($row->potongan_pesanan))
            ->addColumn('total_bayar_rp', fn ($row) => $this->rupiah($row->total_bayar))
            ->editColumn('status_order_layanan', function ($row) {
                $badge = $row->status_order_layanan === 'Sudah Bayar'
                    ? 'bg-emerald-100 text-emerald-700'
                    : 'bg-amber-100 text-amber-700';

                return '<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ' . $badge . '">'
                    . e($row->status_order_layanan ?? '-') . '</span>';
            })
            ->editColumn('tanggal_pembayaran', function ($row) {
                return $row->tanggal_pembayaran
                    ? Carbon::parse($row->tanggal_pembayaran)->locale('id')->translatedFormat('d F Y H:i')
                    : '-';
            })
            ->addColumn('action', function ($row) {
                $detailUrl = route('laporan.order.layanan.detail', ['id' => $row->id]);

                return '
                    <button
                        type="button"
                        class="btn-detail-order-layanan inline-flex items-center gap-2 rounded-xl bg-sky-500 px-4 py-2 text-sm font-semibold text-white shadow-md transition duration-200 hover:bg-sky-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-300 active:scale-95"
                        data-order-id="' . (int) $row->id . '"
                        data-detail-url="' . e($detailUrl) . '"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5z" />
                        </svg>
                        Detail
                    </button>
                ';
            })
            ->rawColumns(['status_order_layanan', 'action'])
            ->with('summary', $summary)
            ->make(true);
    }

    public function export(Request $request)
    {
        $this->ensureManager();

        $dataOrderLayanan = $this->buildQuery($request)
            ->orderByDesc('tanggal_order')
            ->orderByDesc('id')
            ->get();

        if ($dataOrderLayanan->isEmpty()) {
            return back()->with('error', 'Tidak ada data transaksi order layanan untuk filter yang dipilih.');
        }

        $fileName = 'laporan-transaksi-order-layanan-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new TransaksiOrderLayananWorkbook($dataOrderLayanan), $fileName);
    }

    /**
     * Pencarian layanan untuk filter TomSelect (remote search).
     */
    public function searchLayanan(Request $request)
    {
        $this->ensureManager();

        $keyword = trim((string) $request->input('q', ''));

        $data = Layanan::query()
            ->select(['id', 'nama_layanan'])
            ->when($keyword !== '', fn (Builder $query) => $query->where('nama_layanan', 'like', "%{$keyword}%"))
            ->orderBy('nama_layanan')
            ->limit(self::SEARCH_LIMIT)
            ->get()
            ->map(fn (Layanan $layanan) => [
                'id' => (string) $layanan->id,
                'text' => $layanan->nama_layanan,
            ]);

        return response()->json(['data' => $data->values()]);
    }

    /**
     * Detail satu transaksi untuk modal: info transaksi + rincian item layanan.
     * Harga satuan diambil dari tabel order_layanan_detail (harga saat transaksi).
     */
    public function detail(int $id)
    {
        $this->ensureManager();

        $order = OrderLayanan::with([
            'pasien:id,no_emr,nama_pasien',
            'metodePembayaran:id,nama_metode',
            'details:id,order_layanan_id,layanan_id,qty,harga_satuan,total_harga_item',
            'details.layanan:id,nama_layanan',
        ])->find($id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi order layanan tidak ditemukan.',
            ], 404);
        }

        $items = $order->details
            ->values()
            ->map(function ($detail, $index) {
                return [
                    'no' => $index + 1,
                    'nama_layanan' => $detail->layanan->nama_layanan ?? 'Layanan dihapus',
                    'harga_satuan' => (float) $detail->harga_satuan,
                    'qty' => (int) $detail->qty,
                    'total_item' => (float) $detail->total_harga_item,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'kode_transaksi' => $order->kode_transaksi ?? '-',
                'tanggal_order' => $order->tanggal_order
                    ? Carbon::parse($order->tanggal_order)->locale('id')->translatedFormat('d F Y H:i')
                    : '-',
                'nama_pasien' => $order->pasien->nama_pasien ?? '-',
                'no_emr' => $order->pasien->no_emr ?? '-',
                'metode_pembayaran' => $order->metodePembayaran->nama_metode ?? '-',
                'status' => $order->status_order_layanan ?? '-',
                'tanggal_pembayaran' => $order->tanggal_pembayaran
                    ? Carbon::parse($order->tanggal_pembayaran)->locale('id')->translatedFormat('d F Y H:i')
                    : '-',
                'subtotal' => $this->subtotalOrder($order),
                'potongan' => (float) ($order->potongan_pesanan ?? 0),
                'total_bayar' => (float) ($order->total_bayar ?? 0),
                'items' => $items,
            ],
        ]);
    }

    /**
     * Yajra menerapkan global search-nya sendiri ke kolom yang dianggap
     * searchable. Karena keyword sudah diterapkan di buildQuery(), global search
     * dikosongkan agar tabel tidak disaring ulang memakai kolom/nama yang beda.
     */
    private function neutralizeGlobalSearch(Request $request): void
    {
        $search = (array) $request->input('search', []);
        $search['value'] = '';
        $search['regex'] = 'false';

        $request->merge(['search' => $search]);

        $bound = request();

        if ($bound instanceof Request && $bound !== $request) {
            $bound->merge(['search' => $search]);
        }
    }

    private function buildQuery(Request $request): Builder
    {
        $query = OrderLayanan::query()
            ->with([
                'pasien:id,no_emr,nama_pasien,jenis_kelamin',
                'metodePembayaran:id,nama_metode',
                'details:id,order_layanan_id,layanan_id,qty,harga_satuan,total_harga_item',
                'details.layanan:id,nama_layanan',
            ])
            ->withSum('details as total_qty', 'qty')
            ->withSum('details as total_item_value', 'total_harga_item');

        $this->applyFilters($query, $request);

        return $query;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $periode = strtolower(trim((string) $request->input('periode', '')));
        $bulan = (int) ($request->input('bulan') ?: now()->format('m'));
        $tahun = (int) ($request->input('tahun') ?: now()->year);
        $status = (string) $request->input('status', '');

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) now()->format('m');
        }

        // Parameter yang tidak relevan diabaikan:
        // - Semua & Minggu Ini: bulan dan tahun tidak dipakai
        // - Per Tahun: bulan tidak dipakai (1 Januari - 31 Desember)
        if ($periode === 'minggu') {
            $query->whereBetween('tanggal_order', [
                now()->startOfWeek(Carbon::MONDAY),
                now()->endOfWeek(Carbon::SUNDAY),
            ]);
        } elseif ($periode === 'bulan') {
            $query->whereYear('tanggal_order', $tahun)
                ->whereMonth('tanggal_order', $bulan);
        } elseif ($periode === 'tahun') {
            $query->whereYear('tanggal_order', $tahun);
        }

        // Layanan: transaksi yang punya minimal satu item layanan tersebut.
        if ($layananId = $request->input('layanan_id')) {
            $query->whereHas('details', fn (Builder $detail) => $detail->where('layanan_id', $layananId));
        }

        if (in_array($status, self::STATUS_OPTIONS, true)) {
            $query->where('status_order_layanan', $status);
        }

        $search = trim((string) $request->input('search.value'));

        if ($search !== '') {
            $query->where(function (Builder $sub) use ($search) {
                $sub->where('order_layanan.kode_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('pasien', function (Builder $pasien) use ($search) {
                        $pasien->where('nama_pasien', 'like', "%{$search}%")
                            ->orWhere('no_emr', 'like', "%{$search}%");
                    })
                    ->orWhereHas('metodePembayaran', function (Builder $metode) use ($search) {
                        $metode->where('nama_metode', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Sebagian transaksi lama menyimpan subtotal = 0, jadi pakai total item sebagai cadangan.
     */
    private function subtotalOrder(OrderLayanan $order): float
    {
        $subtotal = (float) ($order->subtotal ?? 0);

        if ($subtotal > 0) {
            return $subtotal;
        }

        return (float) ($order->total_item_value ?? $order->details->sum('total_harga_item'));
    }

    private function rupiah($nilai): string
    {
        return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
    }
}
