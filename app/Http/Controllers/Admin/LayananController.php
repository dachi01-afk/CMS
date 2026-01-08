<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\Poli;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class LayananController extends Controller
{
    public function index()
    {
        $dataKategoriLayanan = KategoriLayanan::all();
        return view('admin.layanan.layanan', compact('dataKategoriLayanan'));
    }

    public function getDataLayanan()
    {
        $dataLayanan = Layanan::with('kategoriLayanan')->latest()->get();

        return DataTables::of($dataLayanan)
            ->addIndexColumn()
            ->addColumn('nama_layanan', fn($row) => $row->nama_layanan ?? '-')
            ->addColumn('harga_layanan', function ($row) {

                $hargaAwal  = number_format($row->harga_sebelum_diskon, 0, ',', '.');
                $hargaAkhir = number_format($row->harga_setelah_diskon, 0, ',', '.');

                if ($row->diskon > 0) {

                    // 🔑 LOGIC UTAMA: TENTUKAN FORMAT DISKON
                    if ($row->diskon >= 1 && $row->diskon <= 100) {
                        // Persen
                        $labelDiskon = rtrim(rtrim($row->diskon, '0'), '.') . '%';
                    } else {
                        // Nominal Rupiah
                        $labelDiskon = 'Rp' . number_format($row->diskon, 0, ',', '.');
                    }

                    return '
            <div class="flex flex-col">
                <span class="line-through text-xs text-gray-400">
                    Rp' . $hargaAwal . '
                </span>
                <span class="font-semibold text-green-600">
                    Rp' . $hargaAkhir . '
                </span>
                <span class="text-xs text-blue-500">
                    Diskon ' . $labelDiskon . '
                </span>
            </div>
        ';
                }

                return '<span class="font-medium">Rp' . $hargaAwal . '</span>';
            })
            ->addColumn('nama_kategori', fn($row) => $row->kategoriLayanan->nama_kategori ?? '-')
            ->addColumn('action', function ($l) {
                return '
                <button class="btn-edit-layanan text-blue-600 hover:text-blue-800 mr-2" 
                        data-id="' . $l->id . '"  
                        title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-layanan text-red-600 hover:text-red-800" 
                        data-id="' . $l->id . '" 
                        title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
                ';
            })
            ->rawColumns(['action', 'harga_layanan'])
            ->make(true);
    }

    public function createDataLayanan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori_layanan_id'      => 'required|exists:kategori_layanan,id',
            'nama_layanan'             => 'required|string|max:255',
            'harga_sebelum_diskon'     => 'required|numeric|min:0',
            'diskon'                   => 'nullable|numeric|min:0',
            'harga_setelah_diskon'     => 'required|numeric|min:0',
        ], [
            'kategori_layanan_id.required' => 'Kategori layanan wajib dipilih.',
            'kategori_layanan_id.exists'   => 'Kategori layanan tidak ditemukan.',

            'nama_layanan.required' => 'Nama layanan wajib diisi.',

            'harga_sebelum_diskon.required' => 'Harga sebelum diskon wajib diisi.',
            'harga_setelah_diskon.required' => 'Harga setelah diskon wajib diisi.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // dd($validator);

        try {
            Layanan::create([
                'kategori_layanan_id'      => $request->kategori_layanan_id,
                'nama_layanan'             => $request->nama_layanan,
                'harga_sebelum_diskon'     => $request->harga_sebelum_diskon,
                'diskon'                   => $request->diskon ?? 0,
                'harga_setelah_diskon'     => $request->harga_setelah_diskon,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data layanan.'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
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

    public function getDataLayananById($id)
    {
        $dataLayanan = Layanan::with('kategoriLayanan')->where('id', $id)->firstOrFail();

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
