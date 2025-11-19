<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\Poli;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            ->addColumn('harga_layanan', fn($row) => $row->harga_layanan ?? '-')
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
            ->rawColumns(['action'])
            ->make(true);
    }

    public function createDataLayanan(Request $request)
    {
        // Validasi semua field sesuai skema tabel, dengan pesan custom
        $validator = Validator::make($request->all(), [
            'kategori_layanan_id' => 'required|exists:kategori_layanan,id',
            'nama_layanan' => 'required|string|max:255',
            'harga_layanan' => 'required|numeric|min:0|max:999999999.99',
        ], [
            // Pesan custom untuk kategori_layanan_id
            'kategori_layanan_id.required' => 'Kategori layanan wajib dipilih.', // Tambah pesan untuk required
            'kategori_layanan_id.exists' => 'Kategori layanan yang dipilih tidak ditemukan di sistem.',
            // Pesan custom untuk nama_layanan
            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'nama_layanan.string' => 'Nama layanan harus berupa teks.',
            'nama_layanan.max' => 'Nama layanan maksimal 255 karakter.',
            // Pesan custom untuk harga_layanan
            'harga_layanan.required' => 'Harga layanan wajib diisi.',
            'harga_layanan.numeric' => 'Harga layanan harus berupa angka.',
            'harga_layanan.min' => 'Harga layanan tidak boleh negatif.',
            'harga_layanan.max' => 'Harga layanan maksimal Rp 999.999.999,99.',
        ]);
        // Jika validasi gagal, return errors dengan pesan custom
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa input Anda.',
                'errors' => $validator->errors()
            ], 422);
        }
        // Konversi harga dari format rupiah (mis: "500.000" -> 500000.00)
        $hargaNumeric = floatval(str_replace(['.', ','], ['', '.'], $request->harga_layanan));
        // Simpan data ke database
        try {
            Layanan::create([
                'kategori_layanan_id' => $request->kategori_layanan_id,
                'nama_layanan' => $request->nama_layanan,
                'harga_layanan' => $hargaNumeric,
            ]);
            // Return sukses
            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data layanan.'
            ], 201);
        } catch (Exception $e) {
            // Handle error database
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

        // Validasi semua field sesuai skema tabel, dengan pesan custom
        $validator = Validator::make($request->all(), [
            'kategori_layanan_id' => 'required|exists:kategori_layanan,id',
            'nama_layanan' => 'required|string|max:255',
            'harga_layanan' => 'required|numeric|min:0|max:999999999.99',
        ], [
            // Pesan custom untuk kategori_layanan_id
            'kategori_layanan_id.required' => 'Kategori layanan wajib dipilih.', // Tambah pesan untuk required
            'kategori_layanan_id.exists' => 'Kategori layanan yang dipilih tidak ditemukan di sistem.',
            // Pesan custom untuk nama_layanan
            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'nama_layanan.string' => 'Nama layanan harus berupa teks.',
            'nama_layanan.max' => 'Nama layanan maksimal 255 karakter.',
            // Pesan custom untuk harga_layanan
            'harga_layanan.required' => 'Harga layanan wajib diisi.',
            'harga_layanan.numeric' => 'Harga layanan harus berupa angka.',
            'harga_layanan.min' => 'Harga layanan tidak boleh negatif.',
            'harga_layanan.max' => 'Harga layanan maksimal Rp 999.999.999,99.',
        ]);
        // Jika validasi gagal, return errors dengan pesan custom
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa input Anda.',
                'errors' => $validator->errors()
            ], 422);
        }
        // Konversi harga dari format rupiah (mis: "500.000" -> 500000.00)
        $hargaNumeric = floatval(str_replace(['.', ','], ['', '.'], $request->harga_layanan));
        // Simpan data ke database
        try {
            $id = $request->id;
            $dataLayanan = Layanan::findOrFail($id);
            $dataLayanan->update([
                'kategori_layanan_id' => $request->kategori_layanan_id,
                'nama_layanan' => $request->nama_layanan,
                'harga_layanan' => $hargaNumeric,
            ]);
            // Return sukses
            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data layanan.'
            ], 201);
        } catch (Exception $e) {
            // Handle error database
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
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
