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
        $query = Resep::with([
            'obat' => fn($q) => $q->withPivot('jumlah', 'keterangan', 'status'),
            'kunjungan.pasien',
            'kunjungan.poli.dokter'
        ])
            ->whereHas('obat', function ($q) {
                // tampilkan yang belum diambil atau status-nya null (tanpa pakai orWherePivot)
                $q->where(function ($qq) {
                    $qq->where('resep_obat.status', 'Belum Diambil')
                        ->orWhereNull('resep_obat.status');
                });
            })
            ->latest()
            ->get();

        return DataTables::of($query)
            ->addIndexColumn()

            // 🔹 Nama Dokter (biarkan '-' jika tidak ada)
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

            // 🔹 Nama Pasien (dua logika: kunjungan → penjualan_obat)
            ->addColumn('nama_pasien', function ($row) {
                // 1) dari kunjungan (alur pemeriksaan)
                if ($row->kunjungan && $row->kunjungan->pasien) {
                    return e($row->kunjungan->pasien->nama_pasien);
                }

                // 2) fallback: cari dari penjualan_obat (alur beli obat langsung)
                //    ambil pasien terakhir di hari yang sama, dengan obat yang ada pada resep ini
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

            // 🔹 Nomor Antrian (tetap '-' jika tidak ada)
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

            // 🔹 Status
            ->addColumn('status', function ($row) {
                if ($row->obat->isEmpty()) return '-';
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $status = $obat->pivot->status ?? 'Belum Diambil';
                    $color = $status === 'Sudah Diambil' ? 'text-green-600' : 'text-red-600';
                    $html .= "<li class='{$color} font-semibold'>" . e($status) . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // 🔹 Action Button
            ->addColumn('action', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
                }

                $dataObat = $row->obat->map(fn($ob) => [
                    'id'     => $ob->id,
                    'jumlah' => $ob->pivot->jumlah
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
                'no_antrian',
                'tanggal_kunjungan',
                'nama_obat',
                'jumlah',
                'keterangan',
                'status',
                'action'
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

    public function updateStatusResepObat(Request $request)
    {
        $request->validate([
            'resep_id'           => ['required', 'exists:resep,id'],
            'obat_list'          => ['required', 'array', 'min:1'],
            'obat_list.*.id'     => ['required', 'exists:obat,id'],
            'obat_list.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $resep = Resep::findOrFail($request->resep_id);

                // ==============================
                // 🔍 1️⃣ CEK SUMBER PEMBAYARAN
                // ==============================

                // 🔹 Cek apakah resep ini berasal dari transaksi pemeriksaan dokter (EMR)
                $pembayaran = Pembayaran::whereHas('emr', function ($q) use ($resep) {
                    $q->where('resep_id', $resep->id);
                })->first();

                if ($pembayaran) {
                    // Jika ditemukan di tabel pembayaran EMR
                    if ($pembayaran->status !== 'Sudah Bayar') {
                        throw new \Exception(
                            'Status pembayaran masih "Belum Bayar". Silakan lakukan pembayaran terlebih dahulu.'
                        );
                    }
                } else {
                    // 🔹 Kalau tidak ada di pembayaran EMR, berarti ini resep dari penjualan obat langsung
                    $penjualan = PenjualanObat::whereIn(
                        'obat_id',
                        collect($request->obat_list)->pluck('id')
                    )
                        ->where('status', 'Sudah Bayar')
                        ->first();

                    if (! $penjualan) {
                        throw new \Exception(
                            'Transaksi obat belum dibayar. Silakan selesaikan pembayaran di menu Kasir.'
                        );
                    }
                }

                // ==============================
                // 🔹 2️⃣ KURANGI STOK + UPDATE STATUS
                // ==============================
                foreach ($request->obat_list as $obatData) {
                    $obatId     = $obatData['id'];
                    $jumlahObat = $obatData['jumlah'];

                    // Cek apakah obat ini memang ada di resep (pivot resep_obat)
                    $obatPivot = $resep->obat()->where('obat_id', $obatId)->firstOrFail();

                    // Opsional: validasi jumlah request tidak melebihi jumlah di resep
                    if ($jumlahObat > (int) ($obatPivot->pivot->jumlah ?? 0)) {
                        throw new \Exception(
                            "Jumlah pengambilan untuk obat '{$obatPivot->nama_obat}' melebihi jumlah resep."
                        );
                    }

                    // Kalau sudah pernah ditandai "Sudah Diambil", skip saja biar idempotent
                    if ($obatPivot->pivot->status === 'Sudah Diambil') {
                        continue;
                    }

                    // ✅ PENGURANGAN STOK DIPINDAH KE SINI
                    // Hanya dilakukan saat status diubah ke "Sudah Diambil"
                    $this->safeDecreaseObat($obatId, $jumlahObat);

                    // Update status di pivot resep_obat
                    $resep->obat()->updateExistingPivot($obatId, [
                        'status' => 'Sudah Diambil',
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Status resep obat berhasil diperbarui menjadi "Sudah Diambil" dan stok obat sudah dikurangi.',
            ]);
        } catch (\Exception $e) {
            Log::error('updateStatusResepObat error: ' . $e->getMessage(), [
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
}
