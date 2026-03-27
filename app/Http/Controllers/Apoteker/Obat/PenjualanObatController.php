<?php

namespace App\Http\Controllers\Apoteker\Obat;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\PenjualanObat;
use App\Models\PenjualanObatDetail;
use App\Models\Resep;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PenjualanObatController extends Controller
{
    public function getDataPenjualanObat()
    {
        // Ambil data dari tabel penjualan_obat beserta relasinya
        $dataPenjualan = PenjualanObat::with(['pasien', 'obat', 'metodePembayaran'])
            ->latest()
            ->get()
            ->groupBy('kode_transaksi'); // 🔥 Kelompokkan per transaksi

        // Ubah hasil menjadi bentuk siap tampil
        $penjualanData = $dataPenjualan->map(function ($group) {
            $first = $group->first();

            // Gabungkan nama obat dan jumlah
            $namaObat = $group->pluck('obat.nama_obat')->implode(', ');
            $jumlah   = $group->pluck('jumlah')->implode(', ');

            // Hitung total transaksi (subtotal semua obat)
            $totalTagihan = $group->sum('total_setelah_diskon');

            // Format uang diterima dan kembalian (jika ada)
            $uangDiterima = $first->uang_yang_diterima ?? 0;
            $kembalian    = $first->kembalian ?? 0;

            // Format tanggal
            $tanggalTransaksi = $first->tanggal_transaksi
                ? (is_string($first->tanggal_transaksi)
                    ? date('d-m-Y H:i', strtotime($first->tanggal_transaksi))
                    : $first->tanggal_transaksi->format('d-m-Y H:i'))
                : '-';

            return [
                'kode_transaksi'    => $first->kode_transaksi,
                'nama_pasien'       => $first->pasien->nama_pasien ?? '-',
                'nama_obat'         => $namaObat,
                'jumlah'            => $jumlah,
                'total_setelah_diskon'         => $totalTagihan,
                'total_tagihan'     => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                'uang_diterima'     => 'Rp ' . number_format($uangDiterima, 0, ',', '.'),
                'kembalian'         => 'Rp ' . number_format($kembalian, 0, ',', '.'),
                'metode_pembayaran' => $first->metodePembayaran->nama_metode ?? '-',
                'status'            => $first->status ?? '-',
                'tanggal_transaksi' => $tanggalTransaksi,
            ];
        })->values();

        // Return ke DataTables
        return DataTables::of($penjualanData)
            ->addIndexColumn()
            // ->addColumn('action', function ($row) {
            //     return '
            //     <button class="text-blue-600 hover:text-blue-800 mr-2" title="Edit">
            //         <i class="fa-regular fa-pen-to-square text-lg"></i>
            //     </button>
            //     <button class="text-red-600 hover:text-red-800" title="Hapus">
            //         <i class="fa-regular fa-trash-can text-lg"></i>
            //     </button>
            // ';
            // })
            ->make(true);
    }

    public function getDataRiwayatTransaksiObat()
    {
        $rows = PenjualanObat::with(['pasien', 'penjualanObatDetail.obat', 'metodePembayaran'])
            ->where('status', 'Sudah Bayar')
            ->latest()
            ->get()
            ->groupBy('kode_transaksi');

        $penjualanData = $rows->map(function ($group) {
            $first = $group->first();

            // ✅ Nama & Dosis & Jumlah
            $namaObat = $group->pluck('obat.nama_obat')->implode(', ');
            $dosis = $group->pluck('obat.dosis')->map(fn($item) => number_format((float) $item, 2) . ' mg')->implode(', ');
            $jumlah = $group->pluck('jumlah')->map(fn($item) => $item . ' capsul')->implode(', ');

            // ✅ Nominal & tanggal
            $totalTagihan = $group->sum('total_setelah_diskon');
            $uangDiterima = $first->uang_yang_diterima ?? 0;
            $kembalian    = $first->kembalian ?? 0;
            $tanggalISO   = $first->tanggal_transaksi
                ? \Carbon\Carbon::parse($first->tanggal_transaksi)->toIso8601String()
                : null;

            // ✅ Bukti Pembayaran (foto + teks)
            $buktiPembayaran = '-';
            if (!empty($first->bukti_pembayaran)) {
                $url = asset('storage/' . $first->bukti_pembayaran);
                $buktiPembayaran = '
                <div class="flex flex-col items-center text-center space-y-2">
                    <img src="' . $url . '" alt="Bukti Pembayaran" 
                        class="w-24 h-24 object-cover rounded-lg border border-gray-300 shadow-sm hover:scale-105 transition-transform duration-200 cursor-pointer"
                        onclick="window.open(\'' . $url . '\', \'_blank\')" />
                    <a href="' . $url . '" target="_blank" 
                        class="text-sky-600 underline text-sm font-medium">
                        Lihat Bukti Pembayaran
                    </a>
                </div>
            ';
            }

            return [
                'kode_transaksi'    => $first->kode_transaksi,
                'nama_pasien'       => $first->pasien->nama_pasien ?? '-',
                'nama_obat'         => $namaObat,
                'dosis'             => $dosis,
                'jumlah'            => $jumlah,
                'total_setelah_diskon'         => $totalTagihan,
                'metode_pembayaran' => $first->metodePembayaran->nama_metode ?? '-',
                'status'            => $first->status ?? '-',
                'tanggal_transaksi' => $tanggalISO,
                'bukti_pembayaran'  => $buktiPembayaran,
                'action' => '
    <div class="flex items-center justify-center gap-2">
        <button
            class="bayarSekarang inline-flex items-center px-3 py-1.5 text-xs font-semibold
                   text-white bg-sky-600 rounded-lg hover:bg-sky-700 focus:outline-none"
            data-url="' . route('kasir.transaksi.obat', $first->kode_transaksi) . '">
            <i class="fa-solid fa-eye text-[11px] mr-1"></i> Detail
        </button>

        <a href="' . route('kasir.transaksi.obat', $first->kode_transaksi) . '"
           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold
                  text-sky-700 bg-sky-50 border border-sky-200 rounded-lg
                  hover:bg-sky-100">
            <i class="fa-solid fa-print text-[11px] mr-1"></i> Kwitansi
        </a>
    </div>
',
            ];
        })->values();

        return DataTables::of($penjualanData)
            ->addIndexColumn()
            ->rawColumns(['bukti_pembayaran', 'action']) // penting agar HTML tampil
            ->make(true);
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $pasien = Pasien::where('nama_pasien', 'LIKE', "%{$query}%")->get(['id', 'nama_pasien', 'alamat', 'jenis_kelamin']);
        return response()->json($pasien);
    }

    public function searchObat(Request $request)
    {
        $query = $request->get('query');
        $obat = Obat::where('nama_obat', 'like', "%{$query}%")
            ->get(['id', 'nama_obat', 'dosis', 'total_harga', 'jumlah']);

        return response()->json($obat);
    }

    public function pesanObat(Request $request)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'obat_id'   => ['required', 'array', 'min:1'],
            'obat_id.*' => ['required', 'exists:obat,id'],
            'jumlah'    => ['required', 'array', 'min:1'],
            'jumlah.*'  => ['required', 'integer', 'min:1'],
        ]);

        if (count($request->obat_id) !== count($request->jumlah)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah item tidak sama dengan jumlah obat.'
            ], 422);
        }

        $kodeTransaksi = 'TRX-' . strtoupper(uniqid());
        $now = now();

        DB::beginTransaction();
        try {
            // 🔹 1. Buatkan resep dummy TANPA pasien_id (karena kolom itu tidak ada)
            $resepId = DB::table('resep')->insertGetId([
                'kunjungan_id' => null, // ini yang membedakan dari resep dokter
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            $grandTotal = 0;
            $items = [];

            // 🔹 2. Loop setiap obat yang dibeli
            foreach ($request->obat_id as $i => $obatId) {
                $qty = (int) $request->jumlah[$i];
                $obat = DB::table('obat')->where('id', $obatId)->first();

                if (!$obat) continue;

                $harga = $obat->harga ?? ($obat->total_harga ?? 0);
                $subTotal = $qty * $harga;
                $grandTotal += $subTotal;

                // Simpan ke penjualan_obat
                DB::table('penjualan_obat')->insert([
                    'pasien_id'         => $request->pasien_id,
                    'obat_id'           => $obatId,
                    'kode_transaksi'    => $kodeTransaksi,
                    'jumlah'            => $qty,
                    'total_setelah_diskon'         => $subTotal,
                    'total_tagihan'     => $subTotal,
                    'status'            => 'Belum Bayar',
                    'tanggal_transaksi' => $now,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                // Simpan ke resep_obat agar tampil di menu Pengambilan Obat
                DB::table('resep_obat')->insert([
                    'resep_id'   => $resepId,
                    'obat_id'    => $obatId,
                    'jumlah'     => $qty,
                    'status'     => 'Belum Diambil',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $items[] = [
                    'nama_obat' => $obat->nama_obat,
                    'jumlah'    => $qty,
                    'harga'     => $harga,
                    'subtotal'  => $subTotal,
                ];
            }

            DB::commit();

            return response()->json([
                'status'         => 'success',
                'message'        => 'Transaksi obat berhasil disimpan dan siap diambil.',
                'kode_transaksi' => $kodeTransaksi,
                'total'          => $grandTotal,
                'items'          => $items,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getOrCreateResepIdForPasien(int $pasienId): int
    {
        return DB::transaction(function () use ($pasienId) {
            // 1) Cari kunjungan "terbaru" hari ini; sesuaikan kriteria jika perlu
            $today = now()->toDateString();

            $kunjungan = Kunjungan::where('pasien_id', $pasienId)
                ->whereDate('tanggal_kunjungan', $today)
                ->latest('id')
                ->first();

            if (!$kunjungan) {
                // (opsional) cari poli khusus apotek/farmasi bila ada
                $poliId = DB::table('poli')
                    ->whereIn(DB::raw('LOWER(nama_poli)'), ['apotek', 'farmasi'])
                    ->value('id'); // bisa null kalau tidak ada & kolom kunjungan.poli_id nullable

                // nomor antrian per hari (3 digit)
                $lastNo = DB::table('kunjungan')
                    ->whereDate('tanggal_kunjungan', $today)
                    ->max('no_antrian');
                $nextNo = str_pad(((int)$lastNo) + 1, 3, '0', STR_PAD_LEFT);

                // **PASTIKAN** kolom berikut nullable jika tidak kamu isi (dokter_id, poli_id, dll.)
                $kunjunganId = DB::table('kunjungan')->insertGetId([
                    'pasien_id'         => $pasienId,
                    'tanggal_kunjungan' => now(),
                    'no_antrian'        => $nextNo,
                    'poli_id'           => $poliId,     // null jika tidak ada
                    // 'dokter_id'         => null,        // pastikan nullable
                    'status'            => 'Order Obat', // sesuaikan enum/status mu
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $kunjungan = Kunjungan::find($kunjunganId);
            }

            // 2) Ambil/buat resep untuk kunjungan ini
            $resep = Resep::firstOrCreate(
                ['kunjungan_id' => $kunjungan->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            return (int) $resep->id;
        });
    }

    public function ajaxResepAktif(Request $request)
    {
        $request->validate(['pasien_id' => 'required|exists:pasien,id']);
        $resepId = $this->getOrCreateResepIdForPasien((int)$request->pasien_id);

        $resep = Resep::with('kunjungan')->find($resepId);
        return response()->json([
            'resep_id'         => $resepId,
            'kunjungan_id'     => $resep->kunjungan_id,
            'tanggal_kunjungan' => optional($resep->kunjungan)->tanggal_kunjungan,
            'created'          => true,
        ]);
    }

    private function penjualanObatJoinDetail()
    {
        return DB::table('penjualan_obat as po')
            ->leftJoin('penjualan_obat_detail as pod', 'pod.penjualan_obat_id', '=', 'po.id');
    }

    protected function getModeLabel($periode)
    {
        if ($periode === 'harian') {
            return 'Harian';
        }

        if ($periode === 'mingguan') {
            return 'Mingguan';
        }

        if ($periode === 'bulanan') {
            return 'Bulanan';
        }

        return 'Tahunan';
    }

    protected function parseWeekValue(?string $weekValue, string $tz): Carbon
    {
        try {
            if ($weekValue && preg_match('/^(\d{4})-W(\d{2})$/', $weekValue, $matches)) {
                $year = (int) $matches[1];
                $week = (int) $matches[2];

                return Carbon::now($tz)->setISODate($year, $week, 1)->startOfDay();
            }
        } catch (\Throwable $th) {
        }

        return Carbon::now($tz)->startOfWeek(Carbon::MONDAY);
    }

    protected function parseMonthValue(?string $monthValue, string $tz): Carbon
    {
        try {
            if ($monthValue) {
                return Carbon::createFromFormat('Y-m', $monthValue, $tz)->startOfMonth();
            }
        } catch (\Throwable $th) {
        }

        return Carbon::now($tz)->startOfMonth();
    }

    protected function getChartFilterContext(Request $request, string $tz = 'Asia/Jakarta'): array
    {
        $now = Carbon::now($tz);
        $periode = $request->query('periode', $request->query('mode_periode', 'tahunan'));

        if (!in_array($periode, ['harian', 'mingguan', 'bulanan', 'tahunan'])) {
            $periode = 'tahunan';
        }

        if ($periode === 'harian') {
            try {
                $anchor = $request->query('tanggal')
                    ? Carbon::parse($request->query('tanggal'), $tz)->startOfDay()
                    : $now->copy()->startOfDay();
            } catch (\Throwable $th) {
                $anchor = $now->copy()->startOfDay();
            }

            return [
                'periode' => $periode,
                'anchor'  => $anchor,
                'raw'     => $anchor->toDateString(),
            ];
        }

        if ($periode === 'mingguan') {
            $anchor = $this->parseWeekValue($request->query('minggu'), $tz);

            return [
                'periode' => $periode,
                'anchor'  => $anchor,
                'raw'     => sprintf('%d-W%02d', $anchor->isoWeekYear, $anchor->isoWeek),
            ];
        }

        if ($periode === 'bulanan') {
            $anchor = $this->parseMonthValue($request->query('bulan'), $tz);

            return [
                'periode' => $periode,
                'anchor'  => $anchor,
                'raw'     => $anchor->format('Y-m'),
            ];
        }

        $tahun = (int) $request->query('tahun', $now->year);
        if ($tahun < 2020 || $tahun > 2100) {
            $tahun = (int) $now->year;
        }

        $anchor = Carbon::create($tahun, 1, 1, 0, 0, 0, $tz);

        return [
            'periode' => $periode,
            'anchor'  => $anchor,
            'raw'     => (string) $tahun,
        ];
    }

    protected function getDateRangeByFilter(array $filter): array
    {
        $periode = $filter['periode'];
        $anchor = $filter['anchor'];

        if ($periode === 'harian') {
            $start = $anchor->copy()->startOfDay();
            $end = $anchor->copy()->endOfDay();
            $filterLabel = $anchor->copy()->locale('id')->translatedFormat('d F Y');
        } elseif ($periode === 'mingguan') {
            $start = $anchor->copy()->startOfWeek(Carbon::MONDAY);
            $end = $anchor->copy()->endOfWeek(Carbon::SUNDAY);
            $filterLabel = $start->copy()->locale('id')->translatedFormat('d M Y') . ' - ' . $end->copy()->locale('id')->translatedFormat('d M Y');
        } elseif ($periode === 'bulanan') {
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth();
            $filterLabel = $anchor->copy()->locale('id')->translatedFormat('F Y');
        } else {
            $start = $anchor->copy()->startOfYear();
            $end = $anchor->copy()->endOfYear();
            $filterLabel = 'Tahun ' . $anchor->year;
        }

        return [
            'start'        => $start,
            'end'          => $end,
            'filter_label' => $filterLabel,
        ];
    }

    protected function getTodayOnlyFilterContext(string $tz = 'Asia/Jakarta'): array
    {
        $anchor = Carbon::now($tz)->startOfDay();

        return [
            'periode' => 'harian',
            'anchor'  => $anchor,
            'raw'     => $anchor->toDateString(),
        ];
    }

    private function resolvePenjualanObatFilter(Request $request, string $tz): array
    {
        $now = Carbon::now($tz);

        $periode = $request->query('periode', 'tahunan');
        $allowedPeriode = ['harian', 'mingguan', 'bulanan', 'tahunan'];

        if (!in_array($periode, $allowedPeriode, true)) {
            $periode = 'tahunan';
        }

        return [
            'periode' => $periode,
            'tanggal' => $request->query('tanggal', $now->format('Y-m-d')),
            'minggu'  => $request->query('minggu', $now->isoWeekYear . '-W' . str_pad($now->isoWeek, 2, '0', STR_PAD_LEFT)),
            'bulan'   => $request->query('bulan', $now->format('Y-m')),
            'tahun'   => (int) $request->query('tahun', $now->format('Y')),
        ];
    }

    private function getPenjualanObatModeLabel(string $periode): string
    {
        return match ($periode) {
            'harian' => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan' => 'Bulanan',
            default => 'Tahunan',
        };
    }

    private function resolvePenjualanObatDateRange(array $filter, string $tz): array
    {
        $now = Carbon::now($tz);
        $periode = $filter['periode'];

        if ($periode === 'harian') {
            try {
                $tanggal = Carbon::createFromFormat('Y-m-d', $filter['tanggal'], $tz);
            } catch (\Throwable $th) {
                $tanggal = $now->copy();
            }

            $start = $tanggal->copy()->startOfDay();
            $end = $tanggal->copy()->endOfDay();
            $filterLabel = $tanggal->locale('id')->translatedFormat('d F Y');

            return compact('start', 'end', 'filterLabel') + ['filter_label' => $filterLabel];
        }

        if ($periode === 'mingguan') {
            $minggu = $filter['minggu'];

            if (preg_match('/^(\d{4})-W(\d{2})$/', $minggu, $matches)) {
                $year = (int) $matches[1];
                $week = (int) $matches[2];
                $base = Carbon::now($tz)->setISODate($year, $week);
            } else {
                $base = $now->copy();
            }

            $start = $base->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $end = $start->copy()->addDays(6)->endOfDay();
            $filterLabel = $start->locale('id')->translatedFormat('d F Y') . ' - ' . $end->locale('id')->translatedFormat('d F Y');

            return compact('start', 'end', 'filterLabel') + ['filter_label' => $filterLabel];
        }

        if ($periode === 'bulanan') {
            try {
                $bulan = Carbon::createFromFormat('Y-m', $filter['bulan'], $tz);
            } catch (\Throwable $th) {
                $bulan = $now->copy();
            }

            $start = $bulan->copy()->startOfMonth();
            $end = $bulan->copy()->endOfMonth();
            $filterLabel = $bulan->locale('id')->translatedFormat('F Y');

            return compact('start', 'end', 'filterLabel') + ['filter_label' => $filterLabel];
        }

        $tahun = (int) ($filter['tahun'] ?: $now->format('Y'));

        $start = Carbon::create($tahun, 1, 1, 0, 0, 0, $tz)->startOfYear();
        $end = Carbon::create($tahun, 1, 1, 0, 0, 0, $tz)->endOfYear();
        $filterLabel = (string) $tahun;

        return compact('start', 'end', 'filterLabel') + ['filter_label' => $filterLabel];
    }

    public function penjualanObatPage(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $now = Carbon::now($tz);

        $defaultPeriode = 'tahunan';
        $defaultTanggal = $now->format('Y-m-d');
        $defaultMinggu = $now->isoWeekYear . '-W' . str_pad($now->isoWeek, 2, '0', STR_PAD_LEFT);
        $defaultBulan = $now->format('Y-m');
        $defaultTahun = $now->format('Y');
        $todayLabel = $now->locale('id')->translatedFormat('d F Y');

        return view('farmasi.penjualan-obat.index', compact(
            'todayLabel',
            'defaultPeriode',
            'defaultTanggal',
            'defaultMinggu',
            'defaultBulan',
            'defaultTahun'
        ));
    }

    public function penjualanObatHariIniPage(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $isTodayOnly = (int) $request->query('today_only', 0) === 1;
        $todayLabel = Carbon::now($tz)->locale('id')->translatedFormat('d F Y');

        return view('farmasi.penjualan-obat-hari-ini.index', compact('isTodayOnly', 'todayLabel'));
    }


    public function penjualanObatHariIni(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $start = Carbon::now($tz)->startOfDay();
        $end = Carbon::now($tz)->endOfDay();

        $data = DB::table('penjualan_obat as po')
            ->leftJoin('pasien as p', 'p.id', '=', 'po.pasien_id')
            ->leftJoin('penjualan_obat_detail as pod', 'pod.penjualan_obat_id', '=', 'po.id')
            ->select(
                'po.id',
                'po.kode_transaksi',
                'po.tanggal_transaksi',
                'po.status',
                DB::raw("COALESCE(p.nama_pasien, '-') as nama_pasien"),
                DB::raw('COALESCE(SUM(pod.total_setelah_diskon), 0) as total')
            )
            ->whereBetween('po.tanggal_transaksi', [
                $start->toDateTimeString(),
                $end->toDateTimeString(),
            ])
            ->groupBy('po.id', 'po.kode_transaksi', 'po.tanggal_transaksi', 'po.status', 'p.nama_pasien')
            ->orderByDesc('po.tanggal_transaksi')
            ->get();

        $summary = DB::table('penjualan_obat as po')
            ->leftJoin('penjualan_obat_detail as pod', 'pod.penjualan_obat_id', '=', 'po.id')
            ->whereBetween('po.tanggal_transaksi', [
                $start->toDateTimeString(),
                $end->toDateTimeString(),
            ])
            ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as total_transaksi')
            ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.total_setelah_diskon ELSE 0 END), 0) as total_pemasukan")
            ->first();

        return response()->json([
            'data' => $data,
            'meta' => [
                'is_today_only'   => true,
                'periode'         => 'harian',
                'mode_label'      => 'Hari Ini',
                'filter_label'    => Carbon::now($tz)->locale('id')->translatedFormat('d F Y'),
                'total_transaksi' => (int) ($summary->total_transaksi ?? 0),
                'total_pemasukan' => (float) ($summary->total_pemasukan ?? 0),
            ],
        ]);
    }

    public function penjualanObatData(Request $request)
    {
        $tz = 'Asia/Jakarta';

        $filter = $this->resolvePenjualanObatFilter($request, $tz);
        $range = $this->resolvePenjualanObatDateRange($filter, $tz);

        $start = $range['start'];
        $end = $range['end'];
        $filterLabel = $range['filter_label'];

        $rows = PenjualanObat::with([
            'pasien:id,nama_pasien',
            'penjualanObatDetail.obat:id,nama_obat',
            'latestApprovedDiskon',
        ])
            ->whereBetween('tanggal_transaksi', [
                $start->copy()->toDateTimeString(),
                $end->copy()->toDateTimeString(),
            ])
            ->orderByDesc('tanggal_transaksi')
            ->get();

        $data = $rows->map(fn($penjualan) => $this->transformPenjualanRow($penjualan))->values();

        $summary = [
            'total_transaksi' => $data->count(),
            'total_pemasukan' => $data->where('status', 'Sudah Bayar')->sum('total'),
        ];

        return response()->json([
            'data' => $data,
            'meta' => [
                'periode' => $filter['periode'],
                'mode_label' => $this->getPenjualanObatModeLabel($filter['periode']),
                'filter_label' => $filterLabel,
                'total_transaksi' => (int) $summary['total_transaksi'],
                'total_pemasukan' => (float) $summary['total_pemasukan'],
            ],
        ]);
    }

    public function chartPenjualanObat(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $filter = $this->getChartFilterContext($request, $tz);

        $periode = $filter['periode'];
        $anchor = $filter['anchor'];

        $labels = [];
        $seriesJumlahTransaksi = [];
        $seriesTotalPemasukan = [];
        $mapTransaksi = [];
        $mapPemasukan = [];

        $modeLabel = $this->getModeLabel($periode);
        $filterLabel = '-';
        $shortLabel = '-';
        $xTitle = '';

        if ($periode === 'harian') {
            $hari = $anchor->copy()->toDateString();

            $rows = $this->penjualanObatJoinDetail()
                ->selectRaw('HOUR(po.tanggal_transaksi) as idx')
                ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as jumlah_transaksi')
                ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.total_setelah_diskon ELSE 0 END), 0) as total_pemasukan")
                ->whereDate('po.tanggal_transaksi', $hari)
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $row) {
                $mapTransaksi[(int) $row->idx] = (int) $row->jumlah_transaksi;
                $mapPemasukan[(int) $row->idx] = (float) $row->total_pemasukan;
            }

            for ($hour = 0; $hour <= 23; $hour++) {
                $labels[] = sprintf('%02d:00', $hour);
                $seriesJumlahTransaksi[] = $mapTransaksi[$hour] ?? 0;
                $seriesTotalPemasukan[] = $mapPemasukan[$hour] ?? 0;
            }

            $filterLabel = $anchor->copy()->locale('id')->translatedFormat('d F Y');
            $shortLabel = $anchor->format('d/m/Y');
            $xTitle = 'Jam';
        } elseif ($periode === 'mingguan') {
            $start = $anchor->copy()->startOfWeek(Carbon::MONDAY);
            $end = $anchor->copy()->endOfWeek(Carbon::SUNDAY);

            $rows = $this->penjualanObatJoinDetail()
                ->selectRaw('WEEKDAY(po.tanggal_transaksi) as idx')
                ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as jumlah_transaksi')
                ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.total_setelah_diskon ELSE 0 END), 0) as total_pemasukan")
                ->whereBetween(DB::raw('DATE(po.tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $row) {
                $mapTransaksi[(int) $row->idx] = (int) $row->jumlah_transaksi;
                $mapPemasukan[(int) $row->idx] = (float) $row->total_pemasukan;
            }

            $hariIndo = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

            for ($day = 0; $day <= 6; $day++) {
                $labels[] = $hariIndo[$day];
                $seriesJumlahTransaksi[] = $mapTransaksi[$day] ?? 0;
                $seriesTotalPemasukan[] = $mapPemasukan[$day] ?? 0;
            }

            $filterLabel = $start->copy()->locale('id')->translatedFormat('d M Y') . ' - ' . $end->copy()->locale('id')->translatedFormat('d M Y');
            $shortLabel = 'Minggu ' . $start->isoWeek;
            $xTitle = 'Hari (Minggu Dipilih)';
        } elseif ($periode === 'bulanan') {
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth();
            $daysInMonth = $anchor->daysInMonth;

            $rows = $this->penjualanObatJoinDetail()
                ->selectRaw('DAY(po.tanggal_transaksi) as idx')
                ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as jumlah_transaksi')
                ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.total_setelah_diskon ELSE 0 END), 0) as total_pemasukan")
                ->whereBetween(DB::raw('DATE(po.tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $row) {
                $mapTransaksi[(int) $row->idx] = (int) $row->jumlah_transaksi;
                $mapPemasukan[(int) $row->idx] = (float) $row->total_pemasukan;
            }

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $labels[] = sprintf('%02d', $day);
                $seriesJumlahTransaksi[] = $mapTransaksi[$day] ?? 0;
                $seriesTotalPemasukan[] = $mapPemasukan[$day] ?? 0;
            }

            $filterLabel = $anchor->copy()->locale('id')->translatedFormat('F Y');
            $shortLabel = $anchor->format('m/Y');
            $xTitle = 'Tanggal (Bulan Dipilih)';
        } else {
            $start = $anchor->copy()->startOfYear();
            $end = $anchor->copy()->endOfYear();

            $rows = $this->penjualanObatJoinDetail()
                ->selectRaw('MONTH(po.tanggal_transaksi) as idx')
                ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as jumlah_transaksi')
                ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.total_setelah_diskon ELSE 0 END), 0) as total_pemasukan")
                ->whereBetween(DB::raw('DATE(po.tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $row) {
                $mapTransaksi[(int) $row->idx] = (int) $row->jumlah_transaksi;
                $mapPemasukan[(int) $row->idx] = (float) $row->total_pemasukan;
            }

            $bulanIndo = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            for ($month = 1; $month <= 12; $month++) {
                $labels[] = $bulanIndo[$month - 1];
                $seriesJumlahTransaksi[] = $mapTransaksi[$month] ?? 0;
                $seriesTotalPemasukan[] = $mapPemasukan[$month] ?? 0;
            }

            $filterLabel = 'Tahun ' . $anchor->year;
            $shortLabel = (string) $anchor->year;
            $xTitle = 'Bulan (Tahun Dipilih)';
        }

        $datasets = [
            [
                'label'   => 'Jumlah Transaksi',
                'data'    => $seriesJumlahTransaksi,
                'yAxisID' => 'y',
            ],
            [
                'label'   => 'Total Pemasukan (Rp)',
                'data'    => $seriesTotalPemasukan,
                'yAxisID' => 'y1',
            ],
        ];

        return response()->json([
            'periode'      => $periode,
            'mode_label'   => $modeLabel,
            'filter_label' => $filterLabel,
            'short_label'  => $shortLabel,
            'chart_title'  => 'Grafik Penjualan Obat ' . $modeLabel . ' — ' . $filterLabel,
            'labels'       => $labels,
            'values'       => $seriesJumlahTransaksi,
            'datasets'     => $datasets,
            'meta'         => [
                'x_title' => $xTitle,
            ],

            'label'   => $labels,
            'dataset' => $datasets,
        ]);
    }

    private function decodeDiskonItems($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (blank($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function getApprovedDiskonMap(PenjualanObat $penjualan): array
    {
        $approval = $penjualan->latestApprovedDiskon;

        if (!$approval) {
            return [];
        }

        $items = $this->decodeDiskonItems($approval->diskon_items);

        $map = [];
        foreach ($items as $item) {
            $detailId = (int) ($item['id'] ?? 0);
            $persen = (float) ($item['persen'] ?? 0);

            if ($detailId > 0) {
                $map[$detailId] = $persen;
            }
        }

        return $map;
    }

    private function transformDetailPenjualan(PenjualanObatDetail $detail, array $approvedDiskonMap = []): array
    {
        $qty = (int) ($detail->jumlah ?? 0);
        $hargaSatuan = (float) ($detail->harga_satuan ?? 0);
        $subTotal = (float) ($detail->sub_total ?? ($qty * $hargaSatuan));

        $persenDiskon = (float) ($approvedDiskonMap[$detail->id] ?? 0);

        $diskonTipe = $detail->diskon_tipe;
        $diskonNilai = (float) ($detail->diskon_nilai ?? 0);

        if ($persenDiskon > 0) {
            $diskonTipe = 'persen';
            $diskonNilai = $persenDiskon;
        }

        $totalSetelahDiskon = $detail->total_setelah_diskon;

        if ($totalSetelahDiskon === null) {
            if ($diskonTipe === 'persen') {
                $totalSetelahDiskon = $subTotal - ($subTotal * ($diskonNilai / 100));
            } elseif ($diskonTipe === 'nominal' || $diskonTipe === 'nomial') {
                $totalSetelahDiskon = $subTotal - $diskonNilai;
            } else {
                $totalSetelahDiskon = $subTotal;
            }
        }

        return [
            'id' => $detail->id,
            'obat_id' => $detail->obat_id,
            'nama_obat' => $detail->obat->nama_obat ?? '-',
            'jumlah' => $qty,
            'harga_satuan' => $hargaSatuan,
            'sub_total' => $subTotal,
            'diskon_tipe' => $diskonTipe,
            'diskon_nilai' => (float) $diskonNilai,
            'total_setelah_diskon' => max((float) $totalSetelahDiskon, 0),
        ];
    }

    private function transformPenjualanRow(PenjualanObat $penjualan): array
    {
        $approvedDiskonMap = $this->getApprovedDiskonMap($penjualan);

        $details = $penjualan->penjualanObatDetail
            ->map(fn($detail) => $this->transformDetailPenjualan($detail, $approvedDiskonMap));

        $totalTagihan = $details->sum('sub_total');
        $totalSetelahDiskon = $details->sum('total_setelah_diskon');

        return [
            'id' => $penjualan->id,
            'kode_transaksi' => $penjualan->kode_transaksi,
            'nama_pasien' => $penjualan->pasien->nama_pasien ?? '-',
            'tanggal_transaksi' => optional($penjualan->tanggal_transaksi)->toDateTimeString() ?? $penjualan->tanggal_transaksi,
            'status' => $penjualan->status ?? '-',
            'total_tagihan' => (float) $totalTagihan,
            'total' => (float) $totalSetelahDiskon,
        ];
    }

    public function showDetailPenjualanObat($id)
    {
        $penjualan = PenjualanObat::with([
            'pasien:id,nama_pasien',
            'metodePembayaran:id,nama_metode',
            'penjualanObatDetail.obat:id,nama_obat',
            'latestApprovedDiskon',
        ])
            ->findOrFail($id);

        $approvedDiskonMap = $this->getApprovedDiskonMap($penjualan);

        $details = $penjualan->penjualanObatDetail
            ->map(fn($detail) => $this->transformDetailPenjualan($detail, $approvedDiskonMap))
            ->values();

        $totalTagihan = $details->sum('sub_total');
        $totalSetelahDiskon = $details->sum('total_setelah_diskon');

        return response()->json([
            'data' => [
                'id' => $penjualan->id,
                'kode_transaksi' => $penjualan->kode_transaksi,
                'nama_pasien' => $penjualan->pasien->nama_pasien ?? '-',
                'tanggal_transaksi' => optional($penjualan->tanggal_transaksi)->toDateTimeString() ?? $penjualan->tanggal_transaksi,
                'status' => $penjualan->status ?? '-',
                'metode_pembayaran' => $penjualan->metodePembayaran->nama_metode ?? '-',
                'total_tagihan' => (float) $totalTagihan,
                'total_setelah_diskon' => (float) $totalSetelahDiskon,
                'details' => $details,
            ]
        ]);
    }
}
