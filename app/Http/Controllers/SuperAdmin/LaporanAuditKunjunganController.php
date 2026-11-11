<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\SuperAdmin\AuditKunjunganExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EMR;
use App\Models\Kunjungan;
use App\Models\Layanan;
use App\Models\Pembayaran;
use App\Models\Poli;
use App\Models\Resep;
use App\Models\ResepObat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

/**
 * Laporan audit perjalanan pasien dari pendaftaran kunjungan sampai obat
 * diserahkan farmasi: siapa yang menginput kunjungan, poli & dokter, jam,
 * tindakan dokter, kasir yang melayani pembayaran, dan akun farmasi yang
 * menyerahkan obat.
 *
 * Nama pelaku diambil dari audit_logs (trigger mencatat user pada setiap
 * INSERT/UPDATE), karena tabel kunjungan/pembayaran/resep tidak menyimpan
 * kolom pembuat.
 */
class LaporanAuditKunjunganController extends Controller
{
    private const KUNJUNGAN_TYPES = ['kunjungan', Kunjungan::class];

    private const PEMBAYARAN_TYPES = ['pembayaran', Pembayaran::class];

    private const RESEP_TYPES = ['resep', Resep::class];

    private const RESEP_OBAT_TYPES = ['resep_obat', ResepObat::class];

    /** Batas jumlah opsi yang dikirim ke TomSelect per pencarian. */
    private const SEARCH_LIMIT = 20;

    private function ensureManager(): void
    {
        $user = Auth::user();

        $role = strtolower(trim((string) ($user->role ?? '')));

        $isManager = $role === 'super admin' || (bool) ($user->is_super_admin ?? false);

        abort_unless($user && $isManager, 403, 'Hanya Super Admin yang dapat mengakses laporan ini.');
    }

    public function index()
    {
        $this->ensureManager();

        // Opsi layanan & poli dimuat lewat AJAX (TomSelect), bukan di-render semua.
        return view('super-admin.laporan-audit-kunjungan.laporan-audit-kunjungan');
    }

    public function data(Request $request)
    {
        $this->ensureManager();

        $query = $this->buildQuery($request);

        $summary = [
            'total_kunjungan' => (clone $query)->count(),
            'total_pasien' => (clone $query)->distinct()->count('kunjungan.pasien_id'),
            'sudah_diperiksa' => (clone $query)->whereNotNull('emr.id')->count(),
            'total_pembayaran' => (clone $query)->sum(DB::raw($this->nilaiPembayaranSql())),
        ];

        // Pencarian teks hanya diterapkan sekali (di buildQuery), supaya
        // recordsFiltered tabel selalu sama dengan angka di kartu ringkasan.
        $this->neutralizeGlobalSearch($request);

        // Tanpa order default di sini agar pengurutan dari klik header (order[0])
        // benar-benar diterapkan; default dipasang di sisi DataTables.
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('tanggal_jam', fn ($row) => $this->selTanggalJam($row))
            ->addColumn('pasien', fn ($row) => $this->selPasien($row))
            ->editColumn('nama_poli', fn ($row) => $row->nama_poli ?: '-')
            ->editColumn('nama_dokter', fn ($row) => $row->nama_dokter ?: '-')
            ->addColumn('total_pembayaran_rp', fn ($row) => $this->selTotalPembayaran($row))
            ->addColumn('status_kunjungan', fn ($row) => $this->selStatus($row->status))
            ->addColumn('action', fn ($row) => $this->selAksi($row))
            ->rawColumns(['tanggal_jam', 'pasien', 'total_pembayaran_rp', 'status_kunjungan', 'action'])
            ->with('summary', $summary)
            ->make(true);
    }

    public function export(Request $request)
    {
        $this->ensureManager();

        $rows = $this->buildQuery($request)
            ->orderByDesc('kunjungan.tanggal_kunjungan')
            ->orderByDesc('kunjungan.id')
            ->get();

        if ($rows->isEmpty()) {
            return back()->with('error', 'Tidak ada data kunjungan untuk filter yang dipilih.');
        }

        $fileName = 'laporan-audit-kunjungan-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new AuditKunjunganExport($rows), $fileName);
    }

    /**
     * Perjalanan pasien per tahap untuk modal detail.
     */
    public function detail(int $id)
    {
        $this->ensureManager();

        $row = $this->baseQuery()->where('kunjungan.id', $id)->first();

        if (! $row) {
            return response()->json([
                'success' => false,
                'message' => 'Data kunjungan tidak ditemukan.',
            ], 404);
        }

        $tindakan = DB::table('kunjungan_layanan')
            ->join('layanan', 'layanan.id', '=', 'kunjungan_layanan.layanan_id')
            ->where('kunjungan_layanan.kunjungan_id', $id)
            ->orderBy('layanan.nama_layanan')
            ->get(['layanan.nama_layanan', 'kunjungan_layanan.jumlah'])
            ->map(fn ($item) => [
                'nama' => $item->nama_layanan,
                'qty' => (int) $item->jumlah,
            ]);

        $obat = DB::table('resep_obat as ro')
            ->join('resep as r', 'r.id', '=', 'ro.resep_id')
            ->join('obat as o', 'o.id', '=', 'ro.obat_id')
            ->where('r.kunjungan_id', $id)
            ->orderBy('o.nama_obat')
            ->get(['o.nama_obat', 'ro.jumlah'])
            ->map(fn ($item) => [
                'nama' => $item->nama_obat,
                'qty' => (int) $item->jumlah,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $row->status,
                'pendaftaran' => [
                    'tanggal_kunjungan' => $row->tanggal_kunjungan
                        ? Carbon::parse($row->tanggal_kunjungan)->locale('id')->translatedFormat('d F Y')
                        : null,
                    'jam_kunjungan' => $row->tanggal_kunjungan
                        ? Carbon::parse($row->tanggal_kunjungan)->format('H:i')
                        : null,
                    'jam_input' => $row->kunjungan_dibuat
                        ? Carbon::parse($row->kunjungan_dibuat)->locale('id')->translatedFormat('d F Y H:i')
                        : null,
                    'no_antrian' => $row->no_antrian,
                    'nama_pasien' => $row->nama_pasien,
                    'no_emr' => $row->no_emr,
                    'nama_poli' => $row->nama_poli,
                    'nama_dokter' => $row->nama_dokter,
                    'input_oleh' => $row->input_oleh,
                    'input_oleh_role' => $row->input_oleh_role,
                ],
                'pemeriksaan' => [
                    'nama_perawat' => $row->nama_perawat,
                    'waktu' => $row->emr_dibuat
                        ? Carbon::parse($row->emr_dibuat)->locale('id')->translatedFormat('d F Y H:i')
                        : null,
                ],
                'dokter' => [
                    'nama_dokter' => $row->nama_dokter,
                    'diagnosis' => $row->diagnosis,
                    'tindakan' => $tindakan,
                    'total_tindakan' => (int) ($row->total_tindakan ?? 0),
                ],
                'kasir' => [
                    'kode_transaksi' => $row->kode_pembayaran,
                    'total' => $row->total_pembayaran !== null ? (float) $row->total_pembayaran : null,
                    'metode' => $row->metode_pembayaran,
                    'status' => $row->status_pembayaran,
                    'tanggal_bayar' => $row->tanggal_pembayaran
                        ? Carbon::parse($row->tanggal_pembayaran)->locale('id')->translatedFormat('d F Y H:i')
                        : null,
                    'kasir' => $row->kasir,
                ],
                'farmasi' => [
                    'status_resep' => $row->status_resep,
                    'akun' => $row->farmasi,
                    'waktu' => $row->farmasi_pada
                        ? Carbon::parse($row->farmasi_pada)->locale('id')->translatedFormat('d F Y H:i')
                        : null,
                    'obat' => $obat,
                ],
            ],
        ]);
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
     * Pencarian poli untuk filter TomSelect (remote search).
     */
    public function searchPoli(Request $request)
    {
        $this->ensureManager();

        $keyword = trim((string) $request->input('q', ''));

        $data = Poli::query()
            ->select(['id', 'nama_poli'])
            ->when($keyword !== '', fn (Builder $query) => $query->where('nama_poli', 'like', "%{$keyword}%"))
            ->orderBy('nama_poli')
            ->limit(self::SEARCH_LIMIT)
            ->get()
            ->map(fn (Poli $poli) => [
                'id' => (string) $poli->id,
                'text' => $poli->nama_poli,
            ]);

        return response()->json(['data' => $data->values()]);
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
        $query = $this->baseQuery();

        $this->applyFilters($query, $request);

        return $query;
    }

    /**
     * Query dasar tanpa filter: satu baris per kunjungan beserta sub-data tahapannya.
     * Dipakai laporan (dengan filter) dan modal detail (tanpa filter).
     */
    private function baseQuery(): Builder
    {
        // Ambil EMR terakhir per kunjungan agar satu baris tetap satu kunjungan.
        $emrTerakhir = EMR::query()
            ->select(['id', 'kunjungan_id', 'perawat_id', 'diagnosis', 'created_at', 'updated_at'])
            ->whereIn('id', EMR::query()->selectRaw('MAX(id)')->groupBy('kunjungan_id'));

        $query = Kunjungan::query()
            ->leftJoin('pasien', 'pasien.id', '=', 'kunjungan.pasien_id')
            ->leftJoin('poli', 'poli.id', '=', 'kunjungan.poli_id')
            ->leftJoin('dokter', 'dokter.id', '=', 'kunjungan.dokter_id')
            ->leftJoinSub($emrTerakhir, 'emr', 'emr.kunjungan_id', '=', 'kunjungan.id')
            ->leftJoin('perawat', 'perawat.id', '=', 'emr.perawat_id')
            ->select([
                'kunjungan.id',
                'kunjungan.no_antrian',
                'kunjungan.status',
                'kunjungan.tanggal_kunjungan',
                'kunjungan.created_at as kunjungan_dibuat',
                'pasien.nama_pasien',
                'pasien.no_emr',
                'poli.nama_poli',
                'dokter.nama_dokter',
                'emr.id as emr_id',
                'emr.diagnosis',
                'emr.created_at as emr_dibuat',
                'perawat.nama_perawat',
            ])
            ->addSelect([
                'input_oleh' => AuditLog::query()
                    ->select('user_name')
                    ->whereIn('auditable_type', self::KUNJUNGAN_TYPES)
                    ->whereColumn('auditable_id', 'kunjungan.id')
                    ->where('action', 'create')
                    ->orderBy('id')
                    ->limit(1),
                'input_oleh_role' => AuditLog::query()
                    ->select('user_role')
                    ->whereIn('auditable_type', self::KUNJUNGAN_TYPES)
                    ->whereColumn('auditable_id', 'kunjungan.id')
                    ->where('action', 'create')
                    ->orderBy('id')
                    ->limit(1),
                'tindakan' => DB::table('kunjungan_layanan')
                    ->join('layanan', 'layanan.id', '=', 'kunjungan_layanan.layanan_id')
                    ->whereColumn('kunjungan_layanan.kunjungan_id', 'kunjungan.id')
                    ->select(DB::raw("GROUP_CONCAT(CONCAT(layanan.nama_layanan, ' (', kunjungan_layanan.jumlah, ')') SEPARATOR ', ')")),
                'total_tindakan' => DB::table('kunjungan_layanan')
                    ->whereColumn('kunjungan_id', 'kunjungan.id')
                    ->selectRaw('COALESCE(SUM(jumlah), 0)'),
                'kode_pembayaran' => Pembayaran::query()
                    ->select('kode_transaksi')
                    ->whereColumn('emr_id', 'emr.id')
                    ->orderByDesc('id')
                    ->limit(1),
                'total_pembayaran' => Pembayaran::query()
                    ->select(DB::raw('COALESCE(total_setelah_diskon, total_tagihan)'))
                    ->whereColumn('emr_id', 'emr.id')
                    ->orderByDesc('id')
                    ->limit(1),
                'status_pembayaran' => Pembayaran::query()
                    ->select('status')
                    ->whereColumn('emr_id', 'emr.id')
                    ->orderByDesc('id')
                    ->limit(1),
                'tanggal_pembayaran' => Pembayaran::query()
                    ->select('tanggal_pembayaran')
                    ->whereColumn('emr_id', 'emr.id')
                    ->orderByDesc('id')
                    ->limit(1),
                'metode_pembayaran' => DB::table('pembayaran as p')
                    ->join('metode_pembayaran as mp', 'mp.id', '=', 'p.metode_pembayaran_id')
                    ->whereColumn('p.emr_id', 'emr.id')
                    ->orderByDesc('p.id')
                    ->limit(1)
                    ->select('mp.nama_metode'),
                'kasir' => AuditLog::query()
                    ->select('user_name')
                    ->whereIn('auditable_type', self::PEMBAYARAN_TYPES)
                    ->where('action', 'create')
                    ->whereIn('auditable_id', Pembayaran::query()->select('id')->whereColumn('emr_id', 'emr.id'))
                    ->orderBy('id')
                    ->limit(1),
                'status_resep' => Resep::query()
                    ->select('status')
                    ->whereColumn('kunjungan_id', 'kunjungan.id')
                    ->orderByDesc('id')
                    ->limit(1),
                'farmasi' => AuditLog::query()
                    ->select('user_name')
                    ->where('action', 'update')
                    ->where(function (Builder $sub) {
                        $sub->where(function (Builder $resep) {
                            $resep->whereIn('auditable_type', self::RESEP_TYPES)
                                ->whereIn('auditable_id', Resep::query()->select('id')->whereColumn('kunjungan_id', 'kunjungan.id'));
                        })->orWhere(function (Builder $item) {
                            $item->whereIn('auditable_type', self::RESEP_OBAT_TYPES)
                                ->whereIn('auditable_id', ResepObat::query()->select('id')->whereIn(
                                    'resep_id',
                                    Resep::query()->select('id')->whereColumn('kunjungan_id', 'kunjungan.id')
                                ));
                        });
                    })
                    ->orderByDesc('id')
                    ->limit(1),
                'farmasi_pada' => AuditLog::query()
                    ->select('created_at')
                    ->where('action', 'update')
                    ->where(function (Builder $sub) {
                        $sub->where(function (Builder $resep) {
                            $resep->whereIn('auditable_type', self::RESEP_TYPES)
                                ->whereIn('auditable_id', Resep::query()->select('id')->whereColumn('kunjungan_id', 'kunjungan.id'));
                        })->orWhere(function (Builder $item) {
                            $item->whereIn('auditable_type', self::RESEP_OBAT_TYPES)
                                ->whereIn('auditable_id', ResepObat::query()->select('id')->whereIn(
                                    'resep_id',
                                    Resep::query()->select('id')->whereColumn('kunjungan_id', 'kunjungan.id')
                                ));
                        });
                    })
                    ->orderByDesc('id')
                    ->limit(1),
                'obat' => DB::table('resep_obat as ro')
                    ->join('resep as r', 'r.id', '=', 'ro.resep_id')
                    ->join('obat as o', 'o.id', '=', 'ro.obat_id')
                    ->whereColumn('r.kunjungan_id', 'kunjungan.id')
                    ->select(DB::raw("GROUP_CONCAT(CONCAT(o.nama_obat, ' (', ro.jumlah, ')') SEPARATOR ', ')")),
            ]);

        return $query;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $periode = strtolower(trim((string) $request->input('periode', 'bulanan')));
        $tahun = (int) ($request->input('tahun') ?: now()->year);
        $bulan = (int) ($request->input('bulan') ?: now()->format('m'));

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) now()->format('m');
        }

        // Periode tahunan selalu 1 Januari - 31 Desember tahun terpilih;
        // parameter bulan dari frontend sengaja diabaikan.
        if (in_array($periode, ['tahunan', 'tahun'], true)) {
            $query->whereYear('kunjungan.tanggal_kunjungan', $tahun);
        } else {
            $query->whereYear('kunjungan.tanggal_kunjungan', $tahun)
                ->whereMonth('kunjungan.tanggal_kunjungan', $bulan);
        }

        if ($poliId = $request->input('poli_id')) {
            $query->where('kunjungan.poli_id', $poliId);
        }

        if ($layananId = $request->input('layanan_id')) {
            $query->whereExists(function ($sub) use ($layananId) {
                $sub->select(DB::raw(1))
                    ->from('kunjungan_layanan')
                    ->whereColumn('kunjungan_layanan.kunjungan_id', 'kunjungan.id')
                    ->where('kunjungan_layanan.layanan_id', $layananId);
            });
        }

        $search = trim((string) $request->input('search.value'));

        if ($search !== '') {
            $query->where(function (Builder $sub) use ($search) {
                $sub->where('pasien.nama_pasien', 'like', "%{$search}%")
                    ->orWhere('pasien.no_emr', 'like', "%{$search}%")
                    ->orWhere('dokter.nama_dokter', 'like', "%{$search}%")
                    ->orWhere('poli.nama_poli', 'like', "%{$search}%")
                    ->orWhere('kunjungan.no_antrian', 'like', "%{$search}%");
            });
        }
    }

    private function nilaiPembayaranSql(): string
    {
        return '(SELECT COALESCE(p.total_setelah_diskon, p.total_tagihan) FROM pembayaran p WHERE p.emr_id = emr.id ORDER BY p.id DESC LIMIT 1)';
    }

    private function selTanggalJam($row): string
    {
        $tanggal = $row->tanggal_kunjungan
            ? Carbon::parse($row->tanggal_kunjungan)->locale('id')->translatedFormat('d M Y')
            : '-';

        $jam = $row->kunjungan_dibuat
            ? Carbon::parse($row->kunjungan_dibuat)->format('H:i')
            : '-';

        return '<div class="font-semibold text-slate-800 dark:text-slate-100">' . e($tanggal) . '</div>'
            . '<div class="text-xs text-slate-500 dark:text-slate-400">Input pukul ' . e($jam) . '</div>';
    }

    private function selPasien($row): string
    {
        $nama = e($row->nama_pasien ?? '-');
        $emr = e($row->no_emr ?? '-');

        return '<div class="font-semibold text-slate-800 dark:text-slate-100">' . $nama . '</div>'
            . '<div class="text-xs text-slate-500 dark:text-slate-400">' . $emr . '</div>';
    }

    private function selTotalPembayaran($row): string
    {
        if ($row->kode_pembayaran === null || $row->total_pembayaran === null) {
            return '<span class="text-xs italic text-slate-400">Belum ada tagihan</span>';
        }

        $badge = $row->status_pembayaran === 'Sudah Bayar'
            ? 'bg-emerald-100 text-emerald-700'
            : 'bg-amber-100 text-amber-700';

        return '<div class="font-semibold text-slate-800 dark:text-slate-100">' . e($this->rupiah($row->total_pembayaran)) . '</div>'
            . '<div class="mt-1"><span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold ' . $badge . '">'
            . e($row->status_pembayaran ?? '-') . '</span></div>';
    }

    private function selAksi($row): string
    {
        $url = route('super.admin.laporan.audit.kunjungan.detail', ['id' => $row->id]);

        return '<button type="button"'
            . ' class="btn-detail-audit-kunjungan inline-flex items-center gap-2 rounded-xl bg-sky-500 px-3 py-2 text-xs font-semibold text-white shadow-md transition duration-200 hover:bg-sky-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-300 active:scale-95"'
            . ' data-detail-url="' . e($url) . '">'
            . '<i class="fa-solid fa-route text-[11px]"></i><span>Detail</span></button>';
    }

    private function selStatus(?string $status): string
    {
        $badge = match ($status) {
            'Pending' => 'bg-slate-100 text-slate-700',
            'Waiting' => 'bg-amber-100 text-amber-700',
            'Engaged' => 'bg-blue-100 text-blue-700',
            'Payment' => 'bg-purple-100 text-purple-700',
            'Succeed' => 'bg-emerald-100 text-emerald-700',
            'Canceled' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };

        return '<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ' . $badge . '">'
            . e($status ?? '-') . '</span>';
    }

    private function rupiah($nilai): string
    {
        return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
    }
}
