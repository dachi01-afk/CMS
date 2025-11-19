<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriLayanan;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class KategoriLayananController extends Controller
{
    public function index()
    {
        return view('admin.kategoriLayanan.kategori-layanan');
    }

    public function getDataKategoriLayanan()
    {
        $dataKategoriLayanan = KategoriLayanan::latest()->get();

        return DataTables::of($dataKategoriLayanan)
            ->addIndexColumn()
            ->addColumn('nama_kategori', fn($kategoriLayanan) => $kategoriLayanan->nama_kategori ?? '-')
            ->addColumn('deskripsi_kategori', fn($kategoriLayanan) => $kategoriLayanan->deskripsi_kategori ?? '-')
            ->addColumn('status_kategori', fn($kategoriLayanan) => $kategoriLayanan->status_kategori ?? '-')
            ->addColumn('action', function ($kategoriLayanan) {
                return '
                <button class="btn-edit-kategori-layanan text-blue-600 hover:text-blue-800 mr-2" 
                        data-id="' . $kategoriLayanan->id . '"  
                        title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-kategori-layanan text-red-600 hover:text-red-800" 
                        data-id="' . $kategoriLayanan->id . '" 
                        title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
                ';
            })
            ->make(true);
    }

    public function createDataKategoriLayanan(Request $request)
    {
        // Validasi input sesuai skema tabel
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'deskripsi_kategori' => 'nullable|string|max:1000', // Opsional, tapi bisa diisi
            'status_kategori' => 'required|in:Aktif,Tidak Aktif', // Enum sesuai skema
        ]);
        // Jika validasi gagal, return error 422 dengan pesan per field
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }
        // Simpan data ke database
        try {
            KategoriLayanan::create([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi_kategori' => $request->deskripsi_kategori,
                'status_kategori' => $request->status_kategori,
            ]);
            // Return response sukses
            return response()->json([
                'success' => true,
                'message' => 'Data Kategori Layanan Berhasil Ditambahkan.'
            ], 201); // Status 201 untuk created
        } catch (\Exception $e) {
            // Tangani error server (misalnya, database error)
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
            ], 500);
        }
    }

    public function getDataKategoriLayananById($id)
    {
        try {
            // Cari data berdasarkan ID
            $kategori = KategoriLayanan::findOrFail($id);
            // Return JSON sukses dengan data
            return response()->json([
                'success' => true,
                'data' => $kategori
            ], 200);
        } catch (Exception $e) {
            // Jika ID tidak ditemukan atau error
            return response()->json([
                'success' => false,
                'message' => 'Data kategori layanan tidak ditemukan.'
            ], 404);
        }
    }

    public function updateDataKategoriLayanan(Request $request)
    {
        $id = $request->id; // Ambil ID dari hidden input

        // Validasi
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'deskripsi_kategori' => 'nullable|string|max:1000',
            'status_kategori' => 'required|in:Aktif,Tidak Aktif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $kategori = KategoriLayanan::findOrFail($id);

            $kategori->update([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi_kategori' => $request->deskripsi_kategori,
                'status_kategori' => $request->status_kategori,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Kategori Layanan Berhasil Diupdate.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
            ], 500);
        }
    }

    public function deleteDataKategoriLayanan(Request $request)
    {
        $id = $request->id;

        try {
            $dataKategoriLayanan = KategoriLayanan::findOrFail($id);


            $dataKategoriLayanan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data Kategori Layanan Berhasil Dihapus.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            // ID tidak ditemukan
            return response()->json([
                'success' => false,
                'message' => 'Data kategori layanan tidak ditemukan.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
            ], 500);
        }
    }
}
