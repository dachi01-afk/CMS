<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\Pembayaran;
use App\Models\PenjualanObat;
use App\Models\Resep;
use App\Models\ResepObat;
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
        $hariIni = Carbon::today();

        // 🔹 Ambil semua data dulu (COLLECTION)
        $data = Resep::with([
            'obat' => fn($q) => $q
                ->select('obat.id', 'obat.nama_obat', 'obat.satuan_obat_id') // opsional tapi bagus
                ->with(['satuanObat:id,nama_satuan_obat'])
                ->withPivot('jumlah', 'keterangan', 'dosis'),
            'kunjungan.pasien',
            'kunjungan.poli.dokter',
        ])
            ->where('status', 'waiting')
            ->where(function ($q) use ($hariIni) {
                $q->whereHas('kunjungan', function ($kunjungan) use ($hariIni) {
                    $kunjungan->whereDate('tanggal_kunjungan', $hariIni);
                })
                    ->orWhereDate('created_at', $hariIni);
            })
            ->latest()
            ->get(); // ✅ PENTING: GET

        return DataTables::of($data)
            ->addIndexColumn()

            // 🔹 Nama Dokter
            ->addColumn('nama_dokter', function ($row) {
                return optional(
                    optional($row->kunjungan)
                        ->poli
                        ->dokter
                        ->first()
                )->nama_dokter ?? '-';
            })

            // 🔹 Nama Pasien
            ->addColumn('nama_pasien', function ($row) {
                return $row->kunjungan->pasien->nama_pasien ?? '-';
            })

            // 🔹 Nama Poli
            ->addColumn('nama_poli', function ($row) {
                return $row->kunjungan->poli->nama_poli ?? '-';
            })

            // 🔹 Nomor Antrian
            ->addColumn('no_antrian', function ($row) {
                return $row->kunjungan->no_antrian ?? '-';
            })

            // 🔹 Tanggal
            ->addColumn('tanggal_kunjungan', function ($row) {
                return $row->kunjungan->tanggal_kunjungan
                    ?? $row->created_at->format('Y-m-d');
            })

            ->addColumn('nama_obat', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $html = '<div class="space-y-1.5">';
                foreach ($row->obat as $i => $obat) {
                    $no = $i + 1;

                    $html .= '
            <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg
                        border border-slate-200 bg-white
                        shadow-sm hover:bg-slate-50 transition">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full
                             bg-sky-100 text-sky-700 text-[11px] font-bold">
                    ' . $no . '
                </span>
                <span class="text-xs font-semibold text-slate-700 leading-tight">
                    ' . e($obat->nama_obat) . '
                </span>
            </div>
        ';
                }
                $html .= '</div>';

                return $html;
            })

            ->addColumn('jumlah', function ($row) {
                if ($row->obat->isEmpty()) return '<span class="text-gray-400 italic">-</span>';

                $html = '<div class="space-y-1.5">';
                foreach ($row->obat as $i => $obat) {
                    $no     = $i + 1;
                    $jumlah = $obat->pivot->jumlah ?? '-';
                    $satuan = optional($obat->satuanObat)->nama_satuan_obat ?? '';

                    $label = trim($jumlah . ' ' . $satuan);

                    $html .= '
            <div class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-lg
                        border border-slate-200 bg-white shadow-sm">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full
                             bg-slate-100 text-slate-600 text-[11px] font-bold">
                    ' . $no . '
                </span>

                <span class="ml-auto inline-flex items-center justify-center
                             px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700
                             text-xs font-bold border border-emerald-200">
                    ' . e($label) . '
                </span>
            </div>
        ';
                }
                $html .= '</div>';

                return $html;
            })



            ->addColumn('keterangan', function ($row) {
                if ($row->obat->isEmpty()) return '<span class="text-gray-400 italic">-</span>';

                $html = '<div class="space-y-1.5">';
                foreach ($row->obat as $i => $obat) {
                    $no  = $i + 1;
                    $val = $obat->pivot->keterangan ?? '-';

                    $html .= '
            <div class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex items-start gap-2">
                    <span class="mt-[1px] inline-flex items-center justify-center w-5 h-5 rounded-full
                                 bg-amber-100 text-amber-700 text-[11px] font-bold">
                        ' . $no . '
                    </span>
                    <span class="text-xs text-slate-700 leading-snug">
                        ' . e($val) . '
                    </span>
                </div>
            </div>
        ';
                }
                $html .= '</div>';

                return $html;
            })

            // 🔹 Status
            ->addColumn('status', function ($row) {
                return ucfirst($row->status);
            })

            ->addColumn('action', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
                }

                $dataObat = $row->obat->map(fn($ob) => [
                    'id'     => $ob->id,
                    'jumlah' => $ob->pivot->jumlah,
                ])->values();

                $jsonObat = e(json_encode($dataObat));
                $status   = e($row->status ?? 'waiting');

                return '
        <div class="flex flex-col items-start gap-2">

            <!-- Update Status -->
            <button type="button"
                class="btnUpdateStatus inline-flex items-center gap-1 text-xs font-semibold text-sky-700 hover:text-sky-900"
                data-resep-id="' . $row->id . '"
                data-status="' . $status . '"
                data-obat=\'' . $jsonObat . '\'
                title="Update Status Pengambilan">
                <i class="fa-regular fa-pen-to-square text-[11px]"></i>
                Update Status
            </button>

            <!-- Update Resep Obat -->
            <button type="button"
                class="btnUpdateResepObat inline-flex items-center gap-1 text-xs font-semibold text-sky-700 hover:text-sky-900"
                data-resep-id="' . $row->id . '"
                data-obat=\'' . $jsonObat . '\'
                title="Update Resep Obat">
                <i class="fa-regular fa-pen-to-square text-[11px]"></i>
                Update Resep Obat
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


            ->rawColumns(['nama_obat', 'jumlah', 'keterangan', 'action', 'status'])
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
            'resep_id'            => ['required', 'exists:resep,id'],

            // ✅ enum status cuma 2: waiting, done
            // FE kirim status yang sekarang dari DB (data-status)
            'status_now'          => ['required', 'in:waiting,done'],

            // obat_list tetap dipakai untuk cek resep & kurangi stok saat done
            'obat_list'           => ['required', 'array', 'min:1'],
            'obat_list.*.id'      => ['required', 'exists:obat,id'],
            'obat_list.*.jumlah'  => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                /** @var \App\Models\Resep $resep */
                $resep = Resep::with(['obat'])->lockForUpdate()->findOrFail($request->resep_id);

                // ✅ source of truth: status dari DB
                $dbStatus = $resep->status ?? 'waiting';

                // ✅ anti-stale: kalau FE kirim status berbeda dari DB, minta refresh
                if ($dbStatus !== $request->status_now) {
                    throw new Exception('Status resep sudah berubah. Silakan refresh tabel terlebih dahulu.');
                }

                // ==========================================
                // 0) Idempotent guard: kalau sudah done, stop
                // ==========================================
                if ($dbStatus === 'done') {
                    throw new Exception('Resep sudah selesai (Done). Tidak dapat diupdate lagi.');
                }

                // ==========================================
                // 1) Karena enum cuma 2 → otomatis naik ke DONE
                // ==========================================
                $targetStatus = 'done';

                // ==================================================
                // 2) Kalau mau set done → wajib cek pembayaran dulu
                // ==================================================
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
                    $obatId      = (int) $obatData['id'];
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

                    // ✅ Kurangi stok (sekali, karena kita pastikan status DB belum done)
                    $this->safeDecreaseObat($obatId, $jumlahAmbil);
                }

                // ==========================================
                // 4) Update status resep
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

            // ✅ error bisnis jangan 500, pakai 422
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    public function searchPasien(Request $request)
    {
        $keyword = $request->get('q');

        $pasien = Pasien::query()
            ->where('nama_pasien', 'like', "%{$keyword}%")
            ->orWhere('no_emr', 'like', "%{$keyword}%")
            ->limit(10)
            ->get(['id', 'nama_pasien', 'no_emr', 'jenis_kelamin']);

        return response()->json([
            'data' => $pasien
        ]);
    }

    public function searchDokter(Request $request)
    {
        $keyword = $request->get('q');

        $pasien = Dokter::query()
            ->where('nama_dokter', 'like', "%{$keyword}%")
            ->limit(10)
            ->get(['id', 'nama_dokter']);

        return response()->json([
            'data' => $pasien
        ]);
    }

    public function searchObat(Request $request)
    {
        $keyword = trim($request->get('q'));

        if (!$keyword || mb_strlen($keyword) < 2) {
            return response()->json(['data' => []]);
        }

        $obat = Obat::query()
            ->with([
                'satuanObat:id,nama_satuan_obat',
                'depotObat:id,nama_depot',
            ])
            ->select([
                'id',
                'kode_obat',
                'nama_obat',
                'jumlah',
                'harga_jual_obat',
                'harga_otc_obat',
                'satuan_obat_id',
                'kandungan_obat',
            ])
            ->where(function ($q) use ($keyword) {
                $q->where('nama_obat', 'like', "%{$keyword}%")
                    ->orWhere('kode_obat', 'like', "%{$keyword}%")
                    ->orWhere('kandungan_obat', 'like', "%{$keyword}%");
            })
            ->orderBy('nama_obat')
            ->limit(10)
            ->get()
            ->map(function ($item) {

                $depots = collect($item->depotObat ?? []);

                return [
                    'id'            => $item->id,
                    'kode_obat'     => $item->kode_obat,
                    'nama_obat'     => $item->nama_obat,
                    'stok_tersedia' => (int) ($item->jumlah ?? 0),
                    'satuan'        => optional($item->satuanObat)->nama_satuan_obat,
                    'harga_umum'    => (float) ($item->harga_jual_obat ?? 0),
                    'harga_otc'     => (float) ($item->harga_otc_obat ?? 0),

                    // ✅ ini yang benar
                    'depot'        => $depots->map(fn($d) => [
                        'id' => $d->id,
                        'nama_depot' => $d->nama_depot,
                    ])->values(),
                ];
            });

        return response()->json(['data' => $obat]);
    }

    public function getDataDepotByObat(Request $request)
    {
        $request->validate([
            'obat_id' => ['required', 'integer', 'exists:obat,id'],
        ]);

        $obatId = (int) $request->obat_id;

        // kalau relasi sudah dibuat di model Obat:
        // public function depots() { return $this->belongsToMany(Depot::class, 'depot_obat', 'obat_id', 'depot_id'); }

        $depots = Obat::findOrFail($obatId)
            ->depotObat()
            ->select('depot.id', 'depot.nama_depot')
            ->orderBy('depot.nama_depot')
            ->get();

        return response()->json([
            'data' => $depots
        ]);
    }

    public function createDataResepObat(Request $request)
    {
        $validated = $request->validate([
            // ✅ sesuai permintaan: kunjungan_id dikosongkan saja
            // (kalau request ngirim pun, kita ignore)
            'kunjungan_id' => ['nullable'],

            // info umum (UI)
            'tanggal_resep' => ['nullable', 'date'],
            'dokter_id'     => ['required', 'integer', 'exists:dokter,id'],
            'pasien_id'     => ['required', 'integer', 'exists:pasien,id'],
            'signature'     => ['nullable', 'string', 'max:255'],

            // list obat
            'obat' => ['required', 'array', 'min:1'],
            'obat.*.obat_id'  => ['required', 'integer', 'exists:obat,id'],
            'obat.*.depot_id' => ['required', 'integer', 'exists:depot,id'], // ✅ wajib di UI
            'obat.*.jumlah'   => ['required', 'integer', 'min:1'],

            // tabel resep_obat kamu: dosis decimal(8,2) => validasi numeric
            'obat.*.dosis'      => ['nullable', 'numeric'],
            'obat.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($validated) {

            // ✅ kunjungan_id diset NULL (sesuai permintaan)
            $resep = Resep::create([
                'kunjungan_id' => null,
                'status'       => 'waiting',
            ]);

            foreach ($validated['obat'] as $item) {

                // Kalau UI tidak pakai keterangan per-row, kita bisa pakai signature umum
                $keterangan = $item['keterangan']
                    ?? ($validated['signature'] ?? null);

                ResepObat::create([
                    'resep_id'   => $resep->id,
                    'obat_id'    => $item['obat_id'],
                    'jumlah'     => $item['jumlah'],
                    'dosis'      => $item['dosis'] ?? null, // decimal(8,2)
                    'keterangan' => $keterangan,
                    // depot_id tidak disimpan karena memang belum ada kolomnya di resep_obat
                ]);
            }

            return response()->json([
                'message' => 'Resep berhasil disimpan.',
                'data'    => [
                    'resep_id' => $resep->id,
                ],
            ], 201);
        });
    }



    public function cetakStikerObat($resepId)
    {
        // 🔹 Ambil resep + relasi
        $resep = Resep::with([
            'obat' => fn($q) => $q->withPivot('jumlah', 'keterangan'),
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


    public function getDataResepObatById($id)
    {
        $resep = Resep::with([
            'obat' => fn($q) => $q->withPivot('jumlah', 'dosis', 'keterangan'),
            'obat.satuanObat',
            'kunjungan.pasien',
            'kunjungan.poli.dokter',
        ])->findOrFail($id);

        // info header untuk ditampilkan di modal (readonly)
        $namaPasien = optional(optional($resep->kunjungan)->pasien)->nama_pasien;
        $namaPoli   = optional(optional($resep->kunjungan)->poli)->nama_poli;
        $namaDokter = optional(optional(optional($resep->kunjungan)->poli)->dokter)->first()->nama_dokter ?? null;

        $tanggal = $resep->created_at->format('Y-m-d');

        if ($resep->kunjungan && $resep->kunjungan->tanggal_kunjungan) {
            $tanggal = Carbon::parse($resep->kunjungan->tanggal_kunjungan)->format('Y-m-d');
        }

        return response()->json([
            'data' => [
                'id' => $resep->id,
                'status' => $resep->status,
                'kunjungan_id' => $resep->kunjungan_id,
                'tanggal_resep' => $tanggal,

                'nama_pasien' => $namaPasien,
                'nama_poli'   => $namaPoli,
                'nama_dokter' => $namaDokter,
                'no_antrian'  => optional($resep->kunjungan)->no_antrian,

                // items obat + pivot
                'items' => $resep->obat->map(function ($ob) {
                    return [
                        'obat_id'    => $ob->id,
                        'nama_obat'  => $ob->nama_obat,
                        'kode_obat'  => $ob->kode_obat,
                        'stok'       => (int) ($ob->jumlah ?? 0),
                        'satuan'     => optional($ob->satuanObat)->nama_satuan_obat ?? null, // kalau relasi ada
                        'harga_umum' => (float) ($ob->harga_jual_obat ?? 0),

                        'jumlah'     => (int) ($ob->pivot->jumlah ?? 1),
                        'dosis'      => $ob->pivot->dosis,
                        'keterangan' => $ob->pivot->keterangan,
                    ];
                })->values(),
            ]
        ]);
    }

    public function updateResepObat(Request $request, $id)
    {
        $validated = $request->validate([
            'obat' => ['required', 'array', 'min:1'],
            'obat.*.obat_id' => ['required', 'integer', 'exists:obat,id'],
            'obat.*.jumlah' => ['required', 'integer', 'min:1'],
            'obat.*.dosis' => ['required', 'string', 'max:255'],
            'obat.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $resep = Resep::with('obat')->findOrFail($id);

        // OPTIONAL: jangan izinkan update kalau sudah done
        if ($resep->status === 'done') {
            return response()->json([
                'message' => 'Resep sudah selesai (done), tidak dapat diubah.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // bentuk payload sync pivot
            $syncData = [];
            foreach ($validated['obat'] as $row) {
                $syncData[$row['obat_id']] = [
                    'jumlah'     => $row['jumlah'],
                    'dosis'      => $row['dosis'],
                    'keterangan' => $row['keterangan'] ?? null,
                ];
            }

            // ✅ sync pivot (hapus yang tidak ada, update yang ada, tambah yang baru)
            $resep->obat()->sync($syncData);

            DB::commit();

            return response()->json([
                'message' => 'Resep obat berhasil diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal update resep.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getDataResepObatYangSudahSelesai()
    {
        $hariIni = Carbon::today();

        // 🔹 Ambil semua data dulu (COLLECTION)
        $data = Resep::with([
            'obat' => fn($q) => $q->withPivot('jumlah', 'keterangan', 'dosis'),
            'kunjungan.pasien',
            'kunjungan.poli.dokter',
        ])
            ->where('status', 'done')
            ->where(function ($q) use ($hariIni) {
                $q->whereHas('kunjungan', function ($kunjungan) use ($hariIni) {
                    $kunjungan->whereDate('tanggal_kunjungan', $hariIni);
                })
                    ->orWhereDate('created_at', $hariIni);
            })
            ->latest()
            ->get(); // ✅ PENTING: GET

        return DataTables::of($data)
            ->addIndexColumn()

            // 🔹 Nama Dokter
            ->addColumn('nama_dokter', function ($row) {
                return optional(
                    optional($row->kunjungan)
                        ->poli
                        ->dokter
                        ->first()
                )->nama_dokter ?? '-';
            })

            // 🔹 Nama Pasien
            ->addColumn('nama_pasien', function ($row) {
                return $row->kunjungan->pasien->nama_pasien ?? '-';
            })

            // 🔹 Nama Poli
            ->addColumn('nama_poli', function ($row) {
                return $row->kunjungan->poli->nama_poli ?? '-';
            })

            // 🔹 Nomor Antrian
            ->addColumn('no_antrian', function ($row) {
                return $row->kunjungan->no_antrian ?? '-';
            })

            // 🔹 Tanggal
            ->addColumn('tanggal_kunjungan', function ($row) {
                return $row->kunjungan->tanggal_kunjungan
                    ?? $row->created_at->format('Y-m-d');
            })
            // 🔹 Nama Obat (jadi baris)
            ->addColumn('nama_obat', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $html = '<div class="flex flex-col gap-1">';
                foreach ($row->obat as $obat) {
                    $html .= '
            <div class="px-2 py-1 rounded-md border border-slate-200 bg-slate-50 text-xs text-slate-700">
                ' . e($obat->nama_obat) . '
            </div>
        ';
                }
                $html .= '</div>';

                return $html;
            })

            // 🔹 Jumlah Obat (jadi baris)
            ->addColumn('jumlah', function ($row) {
                if ($row->obat->isEmpty()) return '<span class="text-gray-400 italic">-</span>';

                $html = '<div class="flex flex-col gap-1">';
                foreach ($row->obat as $obat) {
                    $val = $obat->pivot->jumlah ?? '-';
                    $html .= '
            <div class="px-2 py-1 rounded-md border border-slate-200 bg-white text-xs text-slate-700 text-center">
                ' . e($val) . '
            </div>
        ';
                }
                $html .= '</div>';

                return $html;
            })

            // 🔹 Keterangan Obat (jadi baris)
            ->addColumn('keterangan', function ($row) {
                if ($row->obat->isEmpty()) return '<span class="text-gray-400 italic">-</span>';

                $html = '<div class="flex flex-col gap-1">';
                foreach ($row->obat as $obat) {
                    $val = $obat->pivot->keterangan ?? '-';
                    $html .= '
            <div class="px-2 py-1 rounded-md border border-slate-200 bg-white text-xs text-slate-700">
                ' . e($val) . '
            </div>
        ';
                }
                $html .= '</div>';

                return $html;
            })


            // 🔹 Status
            ->addColumn('status', function ($row) {
                return ucfirst($row->status);
            })

            // 🔹 Action
            ->addColumn('action', function ($row) {
                return '
                <button class="text-sky-600">Update</button>
            ';
            })

            ->rawColumns(['nama_obat', 'jumlah', 'keterangan', 'action', 'status'])
            ->make(true);
    }
}
