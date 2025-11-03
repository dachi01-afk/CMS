<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\MetodePembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use Yajra\DataTables\DataTables;

class KasirController extends Controller
{
    public function index()
    {
        return view('admin.pembayaran.kasir');
    }

    public function getDataPembayaran()
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.resep.obat',
            'emr.kunjungan.layanan',
            'metodePembayaran',
        ])->where('status', 'Belum Bayar')->latest()->get();

        return DataTables::of($dataPembayaran)
            ->addIndexColumn()
            ->addColumn('nama_pasien', fn($p) => $p->emr->kunjungan->pasien->nama_pasien ?? '-')
            ->addColumn('tanggal_kunjungan', fn($p) => $p->emr->kunjungan->tanggal_kunjungan ?? '-')
            ->addColumn('no_antrian', fn($p) => $p->emr->kunjungan->no_antrian ?? '-')

            // daftar nama obat
            ->addColumn('nama_obat', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $output .= '<li>' . e($obat->nama_obat) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // dosis
            ->addColumn('dosis', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) return '-';
                $output = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->dosis ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // jumlah
            ->addColumn('jumlah', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) return '-';
                $output = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->jumlah ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('nama_layanan', function ($p) {
                $layanan = $p->emr->kunjungan->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($layanan as $l) {
                    $output .= '<li>' . e($l->nama_layanan ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('jumlah_layanan', function ($p) {
                $layanan = $p->emr->kunjungan->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($layanan as $l) {
                    $output .= '<li>' . e($l->pivot->jumlah ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('total_tagihan', fn($p) => 'Rp ' .  number_format($p->total_tagihan, 0, ',', '.')  ?? '-')
            ->addColumn('metode_pembayaran', fn($p) => $p->metodePembayaran->nama_metode ?? '-')
            ->addColumn('status', fn($p) => $p->status ?? '-')

            // kolom action
            ->addColumn('action', function ($p) {
                $url = route('kasir.transaksi', ['kode_transaksi' => $p->kode_transaksi]);

                return '
        <button class="bayarSekarang text-blue-600 hover:text-blue-800"
                data-url="' . $url . '"
                data-id="' . $p->id . '"
                data-emr-id="' . $p->emr->id . '"
                title="Bayar Sekarang">
            <i class="fa-regular fa-pen-to-square"></i> Bayar Sekarang
        </button>
    ';
            })
            ->rawColumns(['nama_obat', 'dosis', 'jumlah', 'nama_layanan', 'jumlah_layanan', 'action'])
            ->make(true);
    }

    public function getDataRiwayatPembayaran()
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.resep.obat',
            'emr.kunjungan.layanan',
            'metodePembayaran',
        ])->where('status', 'Sudah Bayar')
            ->latest()
            ->get();

        return DataTables::of($dataPembayaran)
            ->addIndexColumn()

            // Identitas dasar
            ->addColumn('nama_pasien', fn($p) => $p->emr->kunjungan->pasien->nama_pasien ?? '-')
            ->addColumn('tanggal_kunjungan', function ($p) {
                $tgl = $p->emr->kunjungan->tanggal_kunjungan ?? null;
                return $tgl ? \Carbon\Carbon::parse($tgl)->toIso8601String() : '-'; // ISO aman untuk JS
            })
            ->addColumn('no_antrian', fn($p) => $p->emr->kunjungan->no_antrian ?? '-')

            // Daftar nama obat
            ->addColumn('nama_obat', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $out = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $out .= '<li>' . e($obat->nama_obat) . '</li>';
                }
                $out .= '</ul>';
                return $out;
            })

            // Dosis: "100.00 mg"
            ->addColumn('dosis', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $out = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $val = $obat->pivot->dosis ?? null;
                    $val = is_numeric($val) ? number_format((float)$val, 2) . ' mg' : e($val ?? '-');
                    $out .= '<li>' . $val . '</li>';
                }
                $out .= '</ul>';
                return $out;
            })

            // Jumlah: "2 capsul"
            ->addColumn('jumlah', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $out = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $qty = $obat->pivot->jumlah ?? null;
                    $qtyTxt = ($qty !== null && $qty !== '') ? e($qty) . ' capsul' : '-';
                    $out .= '<li>' . $qtyTxt . '</li>';
                }
                $out .= '</ul>';
                return $out;
            })

            // Layanan & jumlah layanan
            ->addColumn('nama_layanan', function ($p) {
                $layanan = $p->emr->kunjungan->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $out = '<ul class="list-disc pl-4">';
                foreach ($layanan as $l) {
                    $out .= '<li>' . e($l->nama_layanan ?? '-') . '</li>';
                }
                $out .= '</ul>';
                return $out;
            })
            ->addColumn('jumlah_layanan', function ($p) {
                $layanan = $p->emr->kunjungan->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $out = '<ul class="list-disc pl-4">';
                foreach ($layanan as $l) {
                    $qty = $l->pivot->jumlah ?? null;
                    $out .= '<li>' . e($qty ?? '-') . '</li>';
                }
                $out .= '</ul>';
                return $out;
            })

            // Total & metode
            ->addColumn('total_tagihan', fn($p) => 'Rp ' . number_format((int)$p->total_tagihan, 0, ',', '.'))
            ->addColumn('metode_pembayaran', fn($p) => $p->metodePembayaran->nama_metode ?? '-')

            // Bukti pembayaran: thumbnail + link "Lihat Bukti Pembayaran"
            ->addColumn('bukti_pembayaran', function ($p) {
                if (!$p->bukti_pembayaran) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $url = asset('storage/' . $p->bukti_pembayaran);
                return '
                <div class="flex flex-col items-center text-center space-y-2">
                    <img src="' . e($url) . '" alt="Bukti Pembayaran"
                         class="w-24 h-24 object-cover rounded-lg border border-gray-300 shadow-sm
                                hover:scale-105 transition-transform duration-200 cursor-pointer"
                         onclick="window.open(\'' . e($url) . '\', \'_blank\')" />
                    <a href="' . e($url) . '" target="_blank"
                       class="text-sky-600 underline text-sm font-medium">
                       Lihat Bukti Pembayaran
                    </a>
                </div>
            ';
            })

            ->addColumn('status', fn($p) => $p->status ?? '-')

            // Action
            ->addColumn('action', function ($p) {
                $url = route('kasir.show.kwitansi', ['kodeTransaksi' => $p->kode_transaksi]);
                return '
                <button class="cetakKuitansi text-blue-600 hover:text-blue-800"
                        data-url="' . e($url) . '"
                        title="Cetak Kwitansi">
                    <i class="fa-solid fa-print"></i> Cetak Kwitansi
                </button>
            ';
            })

            ->rawColumns([
                'nama_obat',
                'dosis',
                'jumlah',
                'nama_layanan',
                'jumlah_layanan',
                'bukti_pembayaran',
                'action'
            ])
            ->make(true);
    }

    public function transaksi($kodeTransaksi)
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.kunjungan.poli',
            'emr.kunjungan.layanan', // ambil layanan langsung dari kunjungan
            'emr.resep.obat', // kalau kamu punya relasi ini
        ])->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();

        $dataMetodePembayaran = MetodePembayaran::all();
        // Debug (kalau masih mau cek hasil, bisa pakai info log biar nggak ganggu tampilan)
        Log::info($dataPembayaran);

        // dd($dataPembayaran);

        return view('admin.pembayaran.transaksi', compact('dataPembayaran', 'dataMetodePembayaran'));
    }

    public function transaksiCash(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:pembayaran,id'],
            'uang_yang_diterima' => ['required', 'numeric'],
            'kembalian' => ['required', 'numeric'],
            'metode_pembayaran_id' => ['required', 'exists:metode_pembayaran,id'],
        ]);

        $pembayaran = DB::transaction(function () use ($request) {
            $pemb = Pembayaran::with(['emr.resep.obat' /* pivot ikut otomatis */])
                ->lockForUpdate()
                ->findOrFail($request->id);

            // cegah double-decrement
            $shouldReduceStock = $pemb->status !== 'Sudah Bayar';
            if ($shouldReduceStock) {
                $this->reduceObatFromPembayaran($pemb);
            }

            $pemb->update([
                'uang_yang_diterima'   => $request->uang_yang_diterima,
                'kembalian'            => $request->kembalian,
                'tanggal_pembayaran'   => now(),
                'status'               => 'Sudah Bayar',
                'metode_pembayaran_id' => $request->metode_pembayaran_id,
            ]);

            return $pemb->fresh(['emr.resep.obat', 'emr.kunjungan.pasien', 'emr.kunjungan.layanan', 'metodePembayaran']);
        });

        return response()->json([
            'success' => true,
            'data'    => $pembayaran,
            'message' => 'Uang Kembalian Rp' . number_format($request->kembalian, 0, ',', '.') . '. Terimakasih 😊😊😊',
        ]);
    }

    /**
     * Kurangi stok obat berdasarkan detail resep pada sebuah pembayaran.
     * Aman terhadap duplikasi pemanggilan (karena dibungkus transaction + lock).
     */
    private function reduceObatFromPembayaran(Pembayaran $pembayaran): void
    {
        $resep = optional($pembayaran->emr)->resep;
        if (!$resep) return;

        // relasi belongsToMany -> koleksi model Obat, qty di pivot
        $obatItems = $resep->obat; // Collection<App\Models\Obat>
        foreach ($obatItems as $obat) {
            $obatId = (int) $obat->getKey();                 // id obat dari model Obat
            $qty    = (int) ($obat->pivot->jumlah ?? 0);     // qty dari pivot

            // (opsional) lewati item batal/retur jika Anda pakai status di pivot
            // if (in_array($obat->pivot->status, ['batal','retur'])) continue;

            $this->safeDecreaseObat($obatId, $qty);
        }
    }

    /**
     * Decrement stok obat secara aman (cek stok cukup + baris dikunci).
     */
    private function safeDecreaseObat(int $obatId, int $qty): void
    {
        if ($qty <= 0) return;

        // Satu query, atomic: hanya jalan kalau stok cukup
        $affected = DB::table('obat')
            ->where('id', $obatId)
            ->where('jumlah', '>=', $qty)
            ->decrement('jumlah', $qty);

        if ($affected === 0) {
            // rollback otomatis karena di dalam DB::transaction caller
            throw ValidationException::withMessages([
                'obat' => "Stok obat ID {$obatId} tidak mencukupi untuk dikurangi {$qty}."
            ]);
        }
    }


    public function transaksiTransfer(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:pembayaran,id'],
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,jfif', 'max:5120'],
            'metode_pembayaran_id' => ['required', 'exists:metode_pembayaran,id'],
        ]);

        $pembayaran = DB::transaction(function () use ($request) {
            // lock baris pembayaran
            $pembayaran = Pembayaran::with('emr.resep.obat')
                ->lockForUpdate()
                ->findOrFail($request->id);

            // ambil nominal dari salah satu kolom total
            $amount = $pembayaran->total_tagihan ?? $pembayaran->sub_total ?? $pembayaran->total ?? null;
            $amount = floatval($amount);
            if (!$amount || $amount <= 0) {
                throw ValidationException::withMessages([
                    'id' => 'Kolom subtotal/total tidak ditemukan atau tidak valid.',
                ]);
            }

            // upload bukti
            $fotoPath = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $ext  = strtolower($file->getClientOriginalExtension());
                if ($ext === 'jfif') $ext = 'jpg';

                $fileName = time() . '_' . uniqid() . '.' . $ext;
                $path = 'bukti-transaksi/' . $fileName;

                if ($ext === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($ext, quality: 80));
                }
                $fotoPath = $path;
            }

            $shouldReduceStock = $pembayaran->status !== 'Sudah Bayar';

            if ($shouldReduceStock) {
                $this->reduceObatFromPembayaran($pembayaran); // ⬅️ kurangi stok obat
            }

            $pembayaran->update([
                'bukti_pembayaran'     => $fotoPath,
                'uang_yang_diterima'   => $amount,
                'kembalian'            => 0,
                'tanggal_pembayaran'   => now(),
                'status'               => 'Sudah Bayar',
                'metode_pembayaran_id' => $request->metode_pembayaran_id,
            ]);

            return $pembayaran->fresh(['emr.resep.obat', 'emr.kunjungan.pasien', 'emr.kunjungan.layanan', 'metodePembayaran']);
        });

        return response()->json([
            'success' => true,
            'data' => $pembayaran,
            'message' => 'Bukti transfer diterima. Nominal terbayar: Rp' . number_format($pembayaran->uang_yang_diterima, 0, ',', '.') . '. Terimakasih 😊😊😊'
        ]);
    }

    public function showKwitansi($kodeTransaksi)
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.resep.obat',
            'emr.kunjungan.layanan',
            'metodePembayaran',
        ])->where('kode_transaksi', $kodeTransaksi)->firstOrFail();

        // Hitung total harga obat & layanan
        $totalObat = $dataPembayaran->emr->resep->obat->sum(function ($obat) {
            return $obat->pivot->jumlah * $obat->total_harga;
        });

        $totalLayanan = $dataPembayaran->emr->kunjungan->layanan->sum(function ($layanan) {
            return $layanan->pivot->jumlah * $layanan->harga_layanan;
        });

        $grandTotal = $totalObat + $totalLayanan;

        $namaPT = 'Royal Klinik';

        return view('admin.pembayaran.kwitansi', compact('dataPembayaran', 'totalObat', 'totalLayanan', 'grandTotal', 'namaPT'));
    }

    public function getDataMetodePembayaran()
    {
        $dataMetodePembayaran = MetodePembayaran::latest()->get();

        return DataTables::of($dataMetodePembayaran)
            ->addIndexColumn()
            ->addColumn('nama_metode', fn($mP) => $mP->nama_metode)
            ->addColumn('action', function ($mP) {
                return '
                <button class="btn-update-metode-pembayaran text-blue-600 hover:text-blue-800 mr-2" data-id="' . $mP->id . '" title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-metode-pembayaran text-red-600 hover:text-red-800" data-id="' . $mP->id . '" title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
