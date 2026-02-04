<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\EMR;
use App\Models\OrderLab;
use App\Models\OrderRadiologi;
use App\Models\Perawat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class RiwayatPemeriksaanController extends Controller
{
    public function index()
    {
        // nanti view berisi tab: radiologi / emr / lab
        return view('perawat.riwayat-pemeriksaan.index');
    }

    private function getPerawatIdOrFail(): int
    {
        $userId = Auth::id();

        $perawat = Perawat::where('user_id', $userId)->first();
        abort_if(!$perawat, 403, 'User bukan perawat');

        return (int) $perawat->id;
    }

    /**
     * DataTables - Riwayat Radiologi (hanya yang diinput perawat login)
     * 1 row = 1 order_radiologi yang punya hasil_radiologi oleh perawat tersebut.
     */
    public function dataRadiologi()
    {
        $perawatId = $this->getPerawatIdOrFail();

        $query = OrderRadiologi::query()
            ->with([
                'pasien:id,nama_pasien',
                'dokter:id,nama_dokter',
                'orderRadiologiDetail.jenisPemeriksaanRadiologi:id,nama_pemeriksaan',
                'orderRadiologiDetail.hasilRadiologi' => function ($q) use ($perawatId) {
                    $q->where('perawat_id', $perawatId);
                },
            ])
            // ✅ hanya order yang memiliki hasil radiologi oleh perawat login
            ->whereHas('orderRadiologiDetail.hasilRadiologi', function ($q) use ($perawatId) {
                $q->where('perawat_id', $perawatId);
            })
            ->orderByDesc('tanggal_pemeriksaan')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no_order', fn($row) => $row->no_order_radiologi ?? '-')
            ->addColumn('nama_pasien', fn($row) => $row->pasien->nama_pasien ?? '-')
            ->addColumn('nama_dokter', fn($row) => $row->dokter->nama_dokter ?? '-')
            ->addColumn('tanggal', function ($row) {
                // pakai tanggal_pemeriksaan kalau ada, fallback tanggal_order
                $tgl = $row->tanggal_pemeriksaan ?? $row->tanggal_order;
                return $tgl ? \Carbon\Carbon::parse($tgl)->format('d M Y') : '-';
            })
            ->addColumn('ringkasan', function ($row) use ($perawatId) {
                // hitung detail yang punya hasil oleh perawat login
                $details = $row->orderRadiologiDetail ?? collect();

                $doneCount = 0;
                $items = [];

                foreach ($details as $d) {
                    $hasMine = $d->hasilRadiologi?->where('perawat_id', $perawatId)->count() ?? 0;
                    if ($hasMine > 0) {
                        $doneCount += 1;
                        $items[] = optional($d->jenisPemeriksaanRadiologi)->nama_pemeriksaan ?? '-';
                    }
                }

                $itemsText = count($items) ? implode(', ', $items) : '-';

                return '
                    <div class="space-y-1">
                        <div class="text-slate-800 font-semibold">' . e($doneCount) . ' pemeriksaan</div>
                        <div class="text-xs text-slate-500 line-clamp-2">' . e($itemsText) . '</div>
                    </div>
                ';
            })
            ->addColumn('action', function ($row) {
                $url = route('riwayat-pemeriksaan.radiologi.show', $row->id);

                return '
                    <a href="' . $url . '"
                        class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition whitespace-nowrap">
                        <i class="fa-solid fa-eye mr-2"></i> Detail
                    </a>
                ';
            })
            ->rawColumns(['ringkasan', 'action'])
            ->make(true);
    }

    /**
     * Halaman detail: tampilkan semua hasil radiologi per pemeriksaan
     * yang diinput perawat login untuk order tersebut.
     */
    public function showRadiologi(OrderRadiologi $order)
    {
        $perawatId = $this->getPerawatIdOrFail(); // pakai helper yang sudah kamu bikin

        // Cek izin berdasarkan order -> detail -> hasil (jalur yang sama seperti DataTables)
        $allowed = $order->orderRadiologiDetail()
            ->whereHas('hasilRadiologi', function ($q) use ($perawatId) {
                $q->where('perawat_id', $perawatId);
            })
            ->exists();

        abort_unless($allowed, 403, 'DATA TIDAK DITEMUKAN UNTUK PERAWAT INI.');

        $order->load([
            'pasien',
            'dokter',
            'orderRadiologiDetail.jenisPemeriksaanRadiologi',
            'orderRadiologiDetail.hasilRadiologi' => function ($q) use ($perawatId) {
                $q->where('perawat_id', $perawatId);
            },
        ]);

        return view('perawat.riwayat-pemeriksaan.show-radiologi', [
            'order' => $order,
            'perawatId' => $perawatId,
        ]);
    }

    /**
     * ✅ DATATABLE: RIWAYAT LAB (yang sudah ada hasilnya) dan hanya milik perawat login
     */
    public function dataLab()
    {
        $user = Auth::user();
        $perawat = Perawat::where('user_id', $user->id)->first();

        if (!$perawat) {
            return response()->json(['error' => 'User bukan perawat'], 403);
        }

        // Ambil OrderLab yang:
        // - sesuai filter perawat (dokter yang dihandle perawat)
        // - punya hasil_lab minimal 1 (via order_lab_detail.hasilLab)
        // - biasanya yang sudah "Selesai" (optional)
        $query = OrderLab::query()
            ->with(['pasien', 'dokter', 'orderLabDetail.jenisPemeriksaanLab', 'orderLabDetail.hasilLab'])
            ->filterByPerawat($perawat->id)
            ->whereHas('orderLabDetail.hasilLab') // ✅ penting: hanya yang sudah ada hasil
            ->select('order_lab.*')
            ->orderByDesc('tanggal_order');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no_order', fn($row) => $row->no_order_lab ?? '-')
            ->addColumn('nama_pasien', fn($row) => $row->pasien->nama_pasien ?? '-')
            ->addColumn('nama_dokter', fn($row) => $row->dokter->nama_dokter ?? '-')
            ->addColumn('tanggal', function ($row) {
                // pakai tanggal_pemeriksaan kalau ada, fallback tanggal_order
                return $row->tanggal_pemeriksaan ?? $row->tanggal_order ?? '-';
            })
            ->addColumn('ringkasan', function ($row) {
                // Ringkasan = list jenis pemeriksaan yang ada hasilnya
                $items = $row->orderLabDetail
                    ->filter(fn($d) => $d->hasilLab) // yang sudah ada hasil
                    ->map(fn($d) => optional($d->jenisPemeriksaanLab)->nama_pemeriksaan ?? '-')
                    ->values();

                return $items->isEmpty() ? '-' : $items->implode(', ');
            })
            ->addColumn('action', function ($row) {
                $url = route('riwayat-pemeriksaan.lab.show', $row->id);

                return '<a href="' . $url . '"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        <i class="fa-solid fa-eye mr-1.5"></i> Detail
                    </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * ✅ HALAMAN DETAIL LAB (pastikan order ini memang "milik" perawat login)
     */
    public function showLab($order)
    {
        $user = Auth::user();
        $perawat = Perawat::where('user_id', $user->id)->first();

        if (!$perawat) {
            abort(403, 'User bukan perawat');
        }

        $data = OrderLab::query()
            ->with([
                'pasien',
                'dokter',
                'kunjungan',
                'orderLabDetail.jenisPemeriksaanLab.satuanLab',
                'orderLabDetail.hasilLab',
            ])
            ->filterByPerawat($perawat->id)
            ->whereHas('orderLabDetail.hasilLab') // biar detail cuma yg sudah ada hasil
            ->findOrFail($order);

        return view('perawat.riwayat-pemeriksaan.show-lab', compact('data'));
    }

    public function dataEmr()
    {
        $perawatId = $this->getPerawatIdOrFail();

        $query = EMR::query()
            ->with(['pasien:id,nama_pasien', 'dokter:id,nama_dokter'])
            ->where('perawat_id', $perawatId)
            ->select('emr.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no_emr', fn($row) => 'EMR-' . str_pad($row->id, 6, '0', STR_PAD_LEFT))
            ->addColumn('nama_pasien', fn($row) => $row->pasien->nama_pasien ?? '-')
            ->addColumn('dokter', fn($row) => $row->dokter->nama_dokter ?? '-')
            ->addColumn('tanggal', fn($row) => optional($row->created_at)->format('d M Y H:i'))
            ->addColumn('ringkasan', function ($row) {
                return
                    'Keluhan: ' . \Illuminate\Support\Str::limit($row->keluhan_utama ?? '-', 30) .
                    '<br>Dx: ' . \Illuminate\Support\Str::limit($row->diagnosis ?? '-', 30);
            })
            ->addColumn('action', function ($row) {
                // sementara kita buang dulu route show biar ga nambah error
                return '-';
            })
            ->rawColumns(['ringkasan'])
            ->make(true);
    }


    public function showEmr(EMR $emr)
    {
        $perawatId = auth()->user()->perawat->id ?? null;

        if ((int) $emr->perawat_id !== (int) $perawatId) {
            abort(403, 'DATA TIDAK DITEMUKAN UNTUK PERAWAT INI.');
        }

        $emr->load(['pasien', 'dokter', 'poli', 'kunjungan', 'perawat', 'resep']);

        return view('perawat.riwayat-pemeriksaan.emr-show', compact('emr'));
    }
}
