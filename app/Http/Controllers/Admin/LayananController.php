<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\Poli;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class LayananController extends Controller
{
    public function index()
    {
        $dataKategoriLayanan = KategoriLayanan::all();
        return view('admin.layanan.layanan', compact('dataKategoriLayanan'));
    }

    public function getDataLayanan()
    {
        $query = Layanan::query()
            ->with([
                'kategoriLayanan',
                'layananPoli:id,nama_poli',
            ])
            ->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()

            // NAMA LAYANAN
            ->editColumn('nama_layanan', function ($row) {
                return $row->nama_layanan ?? '-';
            })

            // KATEGORI
            ->addColumn('nama_kategori', function ($row) {
                return optional($row->kategoriLayanan)->nama_kategori ?? '-';
            })

            // HARGA SEBELUM DISKON (angka mentah)
            ->addColumn('harga_sebelum_diskon', function ($row) {
                return is_null($row->harga_sebelum_diskon) ? 0 : (float) $row->harga_sebelum_diskon;
            })

            // DISKON (angka mentah)
            ->addColumn('diskon', function ($row) {
                return is_null($row->diskon) ? 0 : (float) $row->diskon;
            })

            // LABEL DISKON
            ->addColumn('label_diskon', function ($row) {
                $hargaAwal  = (float) ($row->harga_sebelum_diskon ?? 0);
                $hargaAkhir = (float) ($row->harga_setelah_diskon ?? 0);
                $diskon     = (float) ($row->diskon ?? 0);

                // tidak ada diskon
                if ($hargaAwal <= 0) return null;

                $selisih = $hargaAwal - $hargaAkhir;

                // kalau harga akhir >= harga awal, anggap tidak ada diskon
                if ($selisih <= 0) return null;

                /**
                 * LOGIKA:
                 * - Kalau diskon <= 100, kita anggap ini diskon persen (sesuai desain kamu sebelumnya),
                 *   tapi LABEL persen dihitung dari harga supaya tidak pernah salah tampil.
                 * - Kalau diskon > 100, anggap nominal.
                 */
                if ($diskon > 0 && $diskon <= 100) {
                    $pct = ($selisih / $hargaAwal) * 100;
                    $pct = max(0, $pct);

                    $pctStr = rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.');
                    return $pctStr . '%';
                }

                // nominal -> tampilkan dari selisih (lebih konsisten) atau dari diskon
                // saya pakai selisih supaya pasti match dengan tarif
                return 'Rp' . number_format($selisih, 0, ',', '.');
            })

            // HARGA SETELAH DISKON (angka mentah)
            ->addColumn('harga_setelah_diskon', function ($row) {
                return is_null($row->harga_setelah_diskon) ? 0 : (float) $row->harga_setelah_diskon;
            })

            // GLOBAL BOOLEAN
            ->addColumn('is_global', function ($row) {
                return (bool) ($row->is_global ?? false);
            })

            // ✅ KOLOM POLI (LOGIC GLOBAL VS PIVOT)
            ->addColumn('poli_label', function ($row) {
                $isGlobal = (int) ($row->is_global ?? 0);

                if ($isGlobal === 1) {
                    return 'Dapat Diakses Oleh Semua Poli';
                }

                // ambil nama poli dari relasi pivot
                $names = $row->layananPoli
                    ? $row->layananPoli->pluck('nama_poli')->filter()->values()->all()
                    : [];

                if (count($names) === 0) {
                    return 'Belum ditentukan';
                }

                return implode(', ', $names);
            })

            // AKSI
            ->addColumn('action', function ($row) {
                return '
                <div class="flex items-center justify-center gap-2">
                    <button 
                        class="btn-edit-layanan inline-flex items-center justify-center w-8 h-8 rounded-lg 
                               bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100"
                        data-id="' . $row->id . '" 
                        title="Edit">
                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                    </button>

                    <button 
                        class="btn-delete-layanan inline-flex items-center justify-center w-8 h-8 rounded-lg 
                               bg-red-50 text-red-600 hover:bg-red-100 border border-red-100"
                        data-id="' . $row->id . '" 
                        title="Hapus">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </div>
            ';
            })

            ->rawColumns(['action'])
            ->toJson();
    }



    public function createDataLayanan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori_layanan_id'      => 'required|exists:kategori_layanan,id',
            'nama_layanan'             => 'required|string|max:255',
            'harga_sebelum_diskon'     => 'required|numeric|min:0',
            'diskon'                   => 'nullable|numeric|min:0',
            'harga_setelah_diskon'     => 'required|numeric|min:0',

            'is_global'                => 'required|boolean',

            // ✅ PENTING:
            // kalau is_global = 1, poli_id diabaikan dari validasi (supaya min:1 tidak error walau poli_id=[])
            'poli_id'                  => 'exclude_if:is_global,1|required_if:is_global,0|array|min:1',
            'poli_id.*'                => 'integer|exists:poli,id',
        ], [
            'poli_id.required_if' => 'Jika layanan tidak global, minimal pilih 1 poli.',
            'poli_id.min'         => 'Minimal pilih 1 poli.',
        ]);

        // ❌ HAPUS dd() ini, karena bikin proses berhenti
        // dd($validator);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($request) {

                $layanan = Layanan::create([
                    'kategori_layanan_id'      => $request->kategori_layanan_id,
                    'nama_layanan'             => $request->nama_layanan,
                    'harga_sebelum_diskon'     => $request->harga_sebelum_diskon,
                    'diskon'                   => $request->diskon ?? 0,
                    'harga_setelah_diskon'     => $request->harga_setelah_diskon,
                    'is_global'                => (int) $request->is_global,
                ]);

                // ✅ kalau tidak global → simpan pivot
                if ((int) $request->is_global === 0) {
                    $poliId = $request->input('poli_id', []); // poli_id tetap ya
                    $layanan->layananPoli()->sync($poliId);
                } else {
                    // ✅ global → pastikan pivot bersih
                    $layanan->layananPoli()->detach();
                }

                return $layanan;
            });

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data layanan.',
                'data'    => $result
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDataKategoriLayanan()
    {
        try {
            $data = KategoriLayanan::all();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar kategori layanan.'
            ], 500);
        }
    }

    public function getDataPoli()
    {
        try {
            $data = Poli::all();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar poli.'
            ], 500);
        }
    }

    public function getDataLayananById($id)
    {
        $dataLayanan = Layanan::with(['kategoriLayanan', 'layananPoli:id,nama_poli'])
            ->where('id', $id)
            ->firstOrFail();

        // ✅ bikin field poli_id jadi array ID poli (sesuai request FE)
        $dataLayanan->poli_id = $dataLayanan->layananPoli->pluck('id')->values();

        return response()->json([
            'data' => $dataLayanan,
        ]);
    }

    public function updateDataLayanan(Request $request)
    {
        // helper: terima numeric murni atau string rupiah "150.000"
        $toNumber = function ($value) {
            if ($value === null || $value === '') return 0;
            if (is_numeric($value)) return (float) $value;

            // ambil digit saja (rupiah tanpa desimal)
            $digits = preg_replace('/\D+/', '', (string) $value);
            return $digits === '' ? 0 : (float) $digits;
        };

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:layanan,id'],

            'kategori_layanan_id' => ['required', 'integer', 'exists:kategori_layanan,id'],
            'nama_layanan' => ['required', 'string', 'max:255'],

            // kita validasi minimal ada, nanti dicek numeriknya manual supaya bisa terima "150.000"
            'harga_sebelum_diskon' => ['required'],
            'diskon_tipe' => ['nullable', Rule::in(['nominal', 'persen'])],
            'diskon' => ['nullable'],
            'harga_setelah_diskon' => ['nullable'], // boleh dikirim FE, tapi server tetap hitung ulang

            'is_global' => ['required', 'boolean'],
            'poli_id'   => ['exclude_if:is_global,1', 'required_if:is_global,0', 'array', 'min:1'],
            'poli_id.*' => ['integer', 'exists:poli,id'],
        ], [
            'id.required' => 'ID layanan wajib ada.',
            'id.exists' => 'Data layanan tidak ditemukan.',

            'kategori_layanan_id.required' => 'Kategori layanan wajib dipilih.',
            'kategori_layanan_id.exists' => 'Kategori layanan yang dipilih tidak ditemukan di sistem.',

            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'nama_layanan.max' => 'Nama layanan maksimal 255 karakter.',

            'harga_sebelum_diskon.required' => 'Harga layanan wajib diisi.',

            'diskon_tipe.in' => 'Jenis diskon tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa input Anda.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $toNumber) {

                $layanan = Layanan::lockForUpdate()->findOrFail((int) $request->id);

                $hargaAwal = $toNumber($request->harga_sebelum_diskon);
                $diskon = $toNumber($request->diskon);
                $tipe = $request->diskon_tipe ?: 'nominal';

                // VALIDASI SERVER-SIDE ANGKA
                if ($hargaAwal < 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal.',
                        'errors' => ['harga_sebelum_diskon' => ['Harga layanan tidak boleh negatif.']]
                    ], 422);
                }

                if ($diskon < 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal.',
                        'errors' => ['diskon' => ['Diskon tidak boleh negatif.']]
                    ], 422);
                }

                // HITUNG POTONGAN
                if ($tipe === 'persen') {
                    if ($diskon > 100) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Validasi gagal.',
                            'errors' => ['diskon' => ['Diskon persen harus 0 sampai 100.']]
                        ], 422);
                    }
                    $potongan = ($diskon / 100) * $hargaAwal;
                } else {
                    // nominal (opsional: larang diskon > harga)
                    if ($diskon > $hargaAwal) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Validasi gagal.',
                            'errors' => ['diskon' => ['Diskon nominal tidak boleh melebihi harga.']]
                        ], 422);
                    }
                    $potongan = $diskon;
                }

                $hargaAkhir = $hargaAwal - $potongan;
                if ($hargaAkhir < 0) $hargaAkhir = 0;

                // Payload update
                $payload = [
                    'kategori_layanan_id' => (int) $request->kategori_layanan_id,
                    'nama_layanan' => $request->nama_layanan,
                    'harga_sebelum_diskon' => $hargaAwal,
                    'diskon' => $diskon,
                    'harga_setelah_diskon' => round($hargaAkhir, 0),
                ];

                // Aman: hanya set diskon_tipe kalau kolomnya memang ada di DB
                if (Schema::hasColumn('layanan', 'diskon_tipe')) {
                    $payload['diskon_tipe'] = $tipe;
                }

                $layanan->update($payload);

                // ✅ simpan is_global kalau kolomnya ada
                if (Schema::hasColumn('layanan', 'is_global')) {
                    $layanan->is_global = (int) $request->is_global;
                    $layanan->save();
                }

                // ✅ pivot layanan_poli
                if ((int)$request->is_global === 0) {
                    $poliId = $request->input('poli_id', []);
                    $layanan->layananPoli()->sync($poliId);
                } else {
                    $layanan->layananPoli()->detach();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Data layanan berhasil diperbarui.',
                    'data' => [
                        'id' => $layanan->id,
                        'kategori_layanan_id' => $layanan->kategori_layanan_id,
                        'nama_layanan' => $layanan->nama_layanan,
                        'harga_sebelum_diskon' => $layanan->harga_sebelum_diskon,
                        'diskon' => $layanan->diskon,
                        'harga_setelah_diskon' => $layanan->harga_setelah_diskon,
                        'diskon_tipe' => Schema::hasColumn('layanan', 'diskon_tipe') ? $layanan->diskon_tipe : $tipe,
                    ]
                ], 200);
            });
        } catch (\Throwable $e) {

            Log::error('updateDataLayanan error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            // kalau APP_DEBUG=true, tampilkan detail biar cepat ketemu root cause
            $debug = config('app.debug') ? $e->getMessage() : null;

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'debug' => $debug,
            ], 500);
        }
    }


    public function deleteDataLayanan(Request $request)
    {
        $dataLayanan = Layanan::findOrFail($request->id);

        $dataLayanan->delete();

        return response()->json([
            'message' => "Berhasil Menghapus 1 Data Layanan",
        ]);
    }
}
