<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Models\Pembayaran;
use App\Models\PenjualanObat;
use App\Models\Resep;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PengambilanObatController extends Controller
{
    public function index()
    {
        return view('farmasi.pengambilan-obat.pengambilan-obat');
    }

    public function getDataResepObat()
    {
        // ✅ Ambil resep yang statusnya belum selesai (antrian)
        // Sesuaikan status yang kamu pakai:
        // - 'waiting', 'preparing' untuk belum selesai
        // - 'done' untuk selesai
        $query = Resep::with([
            'obat' => fn($q) => $q->withPivot('jumlah', 'keterangan', 'dosis'),
            'kunjungan.pasien',
            'kunjungan.poli.dokter'
        ])
            ->where('status', 'waiting') // ✅ ganti filter pivot jadi filter resep.status
            ->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()

            // 🔹 Nama Dokter
            ->addColumn('nama_dokter', function ($row) {
                if (
                    $row->kunjungan &&
                    $row->kunjungan->poli &&
                    $row->kunjungan->poli->dokter &&
                    $row->kunjungan->poli->dokter->count() > 0
                ) {
                    return e($row->kunjungan->poli->dokter->first()->nama_dokter);
                }
                return '<span class="italic text-gray-400">-</span>';
            })

            // 🔹 Nama Pasien (kunjungan → fallback penjualan_obat)
            ->addColumn('nama_pasien', function ($row) {
                if ($row->kunjungan && $row->kunjungan->pasien) {
                    return e($row->kunjungan->pasien->nama_pasien);
                }

                $obatIds = $row->obat->pluck('id')->all();
                if (!empty($obatIds)) {
                    $pasienId = DB::table('penjualan_obat')
                        ->whereIn('obat_id', $obatIds)
                        ->whereDate('tanggal_transaksi', $row->created_at->toDateString())
                        ->orderByDesc('tanggal_transaksi')
                        ->value('pasien_id');

                    if ($pasienId) {
                        $nama = DB::table('pasien')->where('id', $pasienId)->value('nama_pasien');
                        if ($nama) return e($nama);
                    }
                }

                return '<span class="italic text-gray-400">-</span>';
            })

            // 🔹 Nomor Antrian
            ->addColumn('no_antrian', function ($row) {
                return e($row->kunjungan->no_antrian ?? '-');
            })

            // 🔹 Tanggal Kunjungan / Transaksi
            ->addColumn('tanggal_kunjungan', function ($row) {
                if ($row->kunjungan && $row->kunjungan->tanggal_kunjungan) {
                    return e($row->kunjungan->tanggal_kunjungan);
                }
                return e($row->created_at->format('Y-m-d'));
            })

            // 🔹 Nama Obat
            ->addColumn('nama_obat', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $html .= '<li>' . e($obat->nama_obat) . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // 🔹 Jumlah
            ->addColumn('jumlah', function ($row) {
                if ($row->obat->isEmpty()) return '-';
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $html .= '<li>' . e($obat->pivot->jumlah ?? '-') . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // 🔹 Keterangan
            ->addColumn('keterangan', function ($row) {
                if ($row->obat->isEmpty()) return '-';
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $html .= '<li>' . e($obat->pivot->keterangan ?? '-') . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // ✅ 🔹 Status (sekarang 1 status per resep, bukan per item obat)
            ->addColumn('status', function ($row) {
                $status = $row->status ?? 'waiting';

                $mapLabel = [
                    'waiting'    => 'Waiting',
                    'preparing'  => 'Preparing',
                    'done'       => 'Done',
                ];

                $mapColor = [
                    'waiting'    => 'text-slate-600',
                    'preparing'  => 'text-amber-600',
                    'done'       => 'text-emerald-700',
                ];

                $label = $mapLabel[$status] ?? $status;
                $color = $mapColor[$status] ?? 'text-slate-600';

                return "<span class='{$color} font-semibold'>" . e($label) . "</span>";
            })

            // 🔹 Action Button
            ->addColumn('action', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
                }

                $dataObat = $row->obat->map(fn($ob) => [
                    'id'     => $ob->id,
                    'jumlah' => $ob->pivot->jumlah,
                ]);

                $jsonObat = e(json_encode($dataObat));

                return '
                <div class="flex flex-col items-start gap-1">

                    <!-- Update Status -->
                    <button type="button"
                        class="btnUpdateStatus inline-flex items-center gap-1 text-xs font-semibold text-sky-700 hover:text-sky-900"
                        data-resep-id="' . $row->id . '"
                        data-obat=\'' . $jsonObat . '\'
                        title="Update Status Pengambilan">
                        <i class="fa-regular fa-pen-to-square text-[11px]"></i>
                        Update Status
                    </button>

                    <!-- Cetak Stiker Obat -->
                    <button type="button"
                        class="btnCetakStikerObat inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-900"
                        data-resep-id="' . $row->id . '"
                        title="Cetak Stiker Obat">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        Cetak Stiker
                    </button>

                </div>
            ';
            })

            ->rawColumns([
                'nama_dokter',
                'nama_pasien',
                'status',
                'action',
                'nama_obat',
                'jumlah',
                'keterangan',
            ])
            ->make(true);
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

    public function updateStatusResep(Request $request)
    {
        $request->validate([
            'resep_id'           => ['required', 'exists:resep,id'],
            'status'             => ['required', 'in:waiting,preparing,done'], // ✅ status sekarang di resep
            'obat_list'          => ['required', 'array', 'min:1'],
            'obat_list.*.id'     => ['required', 'exists:obat,id'],
            'obat_list.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                /** @var \App\Models\Resep $resep */
                $resep = Resep::with(['obat'])->lockForUpdate()->findOrFail($request->resep_id);

                $targetStatus = $request->status;

                // ==========================================
                // 0) Idempotent guard: kalau sudah done, stop
                // ==========================================
                if (($resep->status ?? 'waiting') === 'done') {
                    // sudah selesai → jangan kurangi stok lagi
                    return;
                }

                // ==========================================
                // 1) Validasi transisi status (opsional tapi bagus)
                // ==========================================
                $current = $resep->status ?? 'waiting';
                $allowed = [
                    'waiting'   => ['preparing', 'done'],
                    'preparing' => ['done'],
                    'done'      => [],
                ];

                if (!in_array($targetStatus, $allowed[$current] ?? [], true) && $targetStatus !== $current) {
                    throw new Exception("Transisi status tidak valid: {$current} → {$targetStatus}");
                }

                // ==================================================
                // 2) Kalau mau set done → wajib cek pembayaran dulu
                // ==================================================
                if ($targetStatus === 'done') {

                    // 🔍 A) Cek pembayaran dari jalur EMR (kunjungan)
                    $pembayaran = Pembayaran::whereHas('emr', function ($q) use ($resep) {
                        $q->where('resep_id', $resep->id);
                    })->first();

                    if ($pembayaran) {
                        if ($pembayaran->status !== 'Sudah Bayar') {
                            throw new Exception('Status pembayaran masih "Belum Bayar". Silakan lakukan pembayaran terlebih dahulu.');
                        }
                    } else {
                        // 🔍 B) Jalur beli obat langsung (OTC/penjualan obat)
                        $obatIds = collect($request->obat_list)->pluck('id')->all();

                        $penjualan = PenjualanObat::whereIn('obat_id', $obatIds)
                            ->where('status', 'Sudah Bayar')
                            ->orderByDesc('tanggal_transaksi')
                            ->first();

                        if (! $penjualan) {
                            throw new Exception('Transaksi obat belum dibayar. Silakan selesaikan pembayaran di menu Kasir.');
                        }
                    }

                    // ==========================================
                    // 3) Saat DONE: kurangi stok (sekali saja)
                    // ==========================================
                    foreach ($request->obat_list as $obatData) {
                        $obatId     = (int) $obatData['id'];
                        $jumlahAmbil = (int) $obatData['jumlah'];

                        // Pastikan obat memang ada di resep
                        $obatPivot = $resep->obat->firstWhere('id', $obatId);
                        if (! $obatPivot) {
                            throw new Exception("Obat dengan ID {$obatId} tidak ada di resep.");
                        }

                        $jumlahDiResep = (int) ($obatPivot->pivot->jumlah ?? 0);
                        if ($jumlahAmbil > $jumlahDiResep) {
                            throw new Exception("Jumlah pengambilan untuk obat '{$obatPivot->nama_obat}' melebihi jumlah resep.");
                        }

                        // ✅ Kurangi stok (sekali, karena guard done di atas)
                        $this->safeDecreaseObat($obatId, $jumlahAmbil);
                    }
                }

                // ==========================================
                // 4) Update status resep (bukan pivot lagi)
                // ==========================================
                $resep->update([
                    'status' => $targetStatus,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Status resep berhasil diperbarui.',
            ]);
        } catch (Exception $e) {
            Log::error('updateStatusResep error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function cetakStikerObat($resepId)
    {
        // 🔹 Ambil resep + relasi
        $resep = Resep::with([
            'obat' => fn($q) => $q->withPivot('jumlah', 'keterangan', 'status'),
            'kunjungan.pasien',
            'kunjungan.poli.dokter',
        ])->findOrFail($resepId);

        // 🔹 Pasien utama dari kunjungan
        $pasien = $resep->kunjungan->pasien ?? null;

        // 🔹 Fallback: pasien dari penjualan_obat (kalau beli obat langsung)
        if (!$pasien && $resep->obat->isNotEmpty()) {
            $obatIds = $resep->obat->pluck('id')->all();

            $pasienId = DB::table('penjualan_obat')
                ->whereIn('obat_id', $obatIds)
                ->whereDate('tanggal_transaksi', $resep->created_at->toDateString())
                ->orderByDesc('tanggal_transaksi')
                ->value('pasien_id');

            if ($pasienId) {
                $pasien = Pasien::find($pasienId);
            }
        }

        // 🔹 Umur pasien (jika ada)
        $umur = null;
        if ($pasien && $pasien->tanggal_lahir) {
            $tglLahir = Carbon::parse($pasien->tanggal_lahir);
            $diff     = $tglLahir->diff(Carbon::now());

            $umur = sprintf(
                '%d Tahun %d Bulan %d Hari',
                $diff->y,
                $diff->m,
                $diff->d
            );
        }

        // 🔹 Tanggal untuk label (pakai tanggal kunjungan kalau ada)
        $tanggalLabel = $resep->kunjungan->tanggal_kunjungan
            ?? $resep->created_at->toDateString();

        // 🔹 Nama fasilitas (silakan ganti sesuai setting)
        $namaFasilitas = config('app.nama_klinik', 'Royal Klinik.id');

        return view('farmasi.pengambilan-obat.cetak-stiker-obat', [
            'resep'         => $resep,
            'pasien'        => $pasien,
            'umur'          => $umur,
            'tanggalLabel'  => $tanggalLabel,
            'namaFasilitas' => $namaFasilitas,
        ]);
    }

    public function getDataResepWithStatusDone()
    {
        // ✅ Ambil resep yang statusnya belum selesai (antrian)
        // Sesuaikan status yang kamu pakai:
        // - 'waiting', 'preparing' untuk belum selesai
        // - 'done' untuk selesai
        $query = Resep::with([
            'obat' => fn($q) => $q->withPivot('jumlah', 'keterangan', 'dosis'),
            'kunjungan.pasien',
            'kunjungan.poli.dokter'
        ])
            ->where('status', 'done') // ✅ ganti filter pivot jadi filter resep.status
            ->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()

            // 🔹 Nama Dokter
            ->addColumn('nama_dokter', function ($row) {
                if (
                    $row->kunjungan &&
                    $row->kunjungan->poli &&
                    $row->kunjungan->poli->dokter &&
                    $row->kunjungan->poli->dokter->count() > 0
                ) {
                    return e($row->kunjungan->poli->dokter->first()->nama_dokter);
                }
                return '<span class="italic text-gray-400">-</span>';
            })

            // 🔹 Nama Pasien (kunjungan → fallback penjualan_obat)
            ->addColumn('nama_pasien', function ($row) {
                if ($row->kunjungan && $row->kunjungan->pasien) {
                    return e($row->kunjungan->pasien->nama_pasien);
                }

                $obatIds = $row->obat->pluck('id')->all();
                if (!empty($obatIds)) {
                    $pasienId = DB::table('penjualan_obat')
                        ->whereIn('obat_id', $obatIds)
                        ->whereDate('tanggal_transaksi', $row->created_at->toDateString())
                        ->orderByDesc('tanggal_transaksi')
                        ->value('pasien_id');

                    if ($pasienId) {
                        $nama = DB::table('pasien')->where('id', $pasienId)->value('nama_pasien');
                        if ($nama) return e($nama);
                    }
                }

                return '<span class="italic text-gray-400">-</span>';
            })

            // 🔹 Nomor Antrian
            ->addColumn('no_antrian', function ($row) {
                return e($row->kunjungan->no_antrian ?? '-');
            })

            // 🔹 Tanggal Kunjungan / Transaksi
            ->addColumn('tanggal_kunjungan', function ($row) {
                if ($row->kunjungan && $row->kunjungan->tanggal_kunjungan) {
                    return e($row->kunjungan->tanggal_kunjungan);
                }
                return e($row->created_at->format('Y-m-d'));
            })

            // 🔹 Nama Obat
            ->addColumn('nama_obat', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $html .= '<li>' . e($obat->nama_obat) . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // 🔹 Jumlah
            ->addColumn('jumlah', function ($row) {
                if ($row->obat->isEmpty()) return '-';
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $html .= '<li>' . e($obat->pivot->jumlah ?? '-') . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // 🔹 Keterangan
            ->addColumn('keterangan', function ($row) {
                if ($row->obat->isEmpty()) return '-';
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $html .= '<li>' . e($obat->pivot->keterangan ?? '-') . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // ✅ 🔹 Status (sekarang 1 status per resep, bukan per item obat)
            ->addColumn('status', function ($row) {
                $status = $row->status ?? 'waiting';

                $mapLabel = [
                    'waiting'    => 'Waiting',
                    'preparing'  => 'Preparing',
                    'done'       => 'Done',
                ];

                $mapColor = [
                    'waiting'    => 'text-slate-600',
                    'preparing'  => 'text-amber-600',
                    'done'       => 'text-emerald-700',
                ];

                $label = $mapLabel[$status] ?? $status;
                $color = $mapColor[$status] ?? 'text-slate-600';

                return "<span class='{$color} font-semibold'>" . e($label) . "</span>";
            })

            // 🔹 Action Button
            ->addColumn('action', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
                }

                $dataObat = $row->obat->map(fn($ob) => [
                    'id'     => $ob->id,
                    'jumlah' => $ob->pivot->jumlah,
                ]);

                $jsonObat = e(json_encode($dataObat));

                return '
                <div class="flex flex-col items-start gap-1">

                    <!-- Update Status -->
                    <button type="button"
                        class="btnUpdateStatus inline-flex items-center gap-1 text-xs font-semibold text-sky-700 hover:text-sky-900"
                        data-resep-id="' . $row->id . '"
                        data-obat=\'' . $jsonObat . '\'
                        title="Update Status Pengambilan">
                        <i class="fa-regular fa-pen-to-square text-[11px]"></i>
                        Update Status
                    </button>

                    <!-- Cetak Stiker Obat -->
                    <button type="button"
                        class="btnCetakStikerObat inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-900"
                        data-resep-id="' . $row->id . '"
                        title="Cetak Stiker Obat">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        Cetak Stiker
                    </button>

                </div>
            ';
            })

            ->rawColumns([
                'nama_dokter',
                'nama_pasien',
                'status',
                'action',
                'nama_obat',
                'jumlah',
                'keterangan',
            ])
            ->make(true);
    }
}
