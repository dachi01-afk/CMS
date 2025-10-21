<?php

namespace App\Http\Controllers;

use App\Models\JenisSpesialis;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class JenisSpesialisController extends Controller
{
    public function index()
    {
        return view('admin.jenisSpesialisDokter.jenis-spesialis-dokter');
    }

    public function dataJenisSpesialisDokter()
    {
        $query = JenisSpesialis::all();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_spesialis', fn($row) => $row->nama_spesialis ?? '-')
            ->addColumn('action', function ($jenisSpesialis) {
                return '
            <button class="btn-edit-jenis-spesialis-dokter text-blue-600 hover:text-blue-800 mr-2" data-id="' . $jenisSpesialis->id . '" title="Edit">
                <i class="fa-regular fa-pen-to-square text-lg"></i>
            </button>
            <button class="btn-delete-jenis-spesialis-dokter text-red-600 hover:text-red-800" data-id="' . $jenisSpesialis->id . '" title="Hapus">
                <i class="fa-regular fa-trash-can text-lg"></i>
            </button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getDataJenisSPesialisById($id)
    {
        $dataJenisSpesialis = JenisSpesialis::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $dataJenisSpesialis
        ]);
    }

    public function createJenisSpesialisDokter(Request $request)
    {
        $request->validate([
            'nama_spesialis' => ['required']
        ]);

        $dataJenisSpesialisDokter = JenisSpesialis::create([
            'nama_spesialis' => $request->nama_spesialis,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'Data Jenis Spesialis' => $dataJenisSpesialisDokter,
            'message' => 'Berhasil Menambahkan Data Jenis Spesialis Dokter',
        ]);
    }


    public function updateJenisSpesialisDokter(Request $request)
    {
        $request->validate([
            'nama_spesialis' => ['required']
        ]);

        $dataJenisSpesialisDokter = JenisSpesialis::findOrFail($request->id);

        $dataJenisSpesialisDokter->update([
            'nama_spesialis' => $request->nama_spesialis,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'Data Jenis Spesialis' => $dataJenisSpesialisDokter,
            'message' => 'Berhasil Menambahkan Data Jenis Spesialis Dokter',
        ]);
    }

    public function deleteJenisSpesialisDokter($id)
    {
        $dataJenisSpesialisDokter = JenisSpesialis::findOrFail($id);

        $dataJenisSpesialisDokter->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'Data Jenis Spesialis Dokter' => $dataJenisSpesialisDokter,
            'message' => 'Berhasil Menghapus Data Jenis Spesialis Dokter',
        ]);
    }
}
