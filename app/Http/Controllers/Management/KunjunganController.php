<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KunjunganController extends Controller
{
    public function readDataKunjungan()
    {
        $dataKunjungan = Kunjungan::all();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataKunjungan,
            'message' => 'Data Berhasil Ditampilkan',
        ]);
    }

    public function createKunjungan(Request $request)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'dokter_id' => ['required', 'exists:dokter,id'],
            'tanggal_kunjungan' => ['required', 'date'],
            'keluhan_awal' => ['required'],
        ]);

        $tanggalKunjungan = $request->tanggal_kunjungan;

        // Gunakan transaksi supaya aman dari race condition (2 pasien booking bersamaan)
        $nomorAntrian = DB::transaction(function () use ($tanggalKunjungan, $request) {

            // Cari kunjungan terakhir di tanggal yang sama
            $lastKunjungan = Kunjungan::where('tanggal_kunjungan', $tanggalKunjungan)
                ->orderByDesc('id')
                ->lockForUpdate() // kunci baris agar tidak duplikat
                ->first();

            // Tentukan nomor antrian berikutnya
            if ($lastKunjungan) {
                $nextNumber = (int)$lastKunjungan->no_antrian + 1;
            } else {
                $nextNumber = 1;
            }

            // Format jadi 3 digit, misal: 001, 002, 010, 123
            $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Simpan data kunjungan baru
            $kunjungan = Kunjungan::create([
                'pasien_id' => $request->pasien_id,
                'dokter_id' => $request->dokter_id,
                'tanggal_kunjungan' => $tanggalKunjungan,
                'no_antrian' => $formattedNumber,
                'keluhan_awal' => $request->keluhan_awal,
                'status' => 'Pending', // contoh default status
                // tambahkan kolom lain sesuai struktur tabel kamu
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Kunjungan' => $kunjungan,
                'Data No Antrian' => $formattedNumber,
            ]);
        });

        return response()->json([
            'message' => 'Kunjungan berhasil dibuat',
            'no_antrian' => $nomorAntrian,
        ]);
    }

    public function updateDataKunjungan(Request $request)
    {
        $request->validate([
            'tanggal_kunjungan' => ['required', 'date'],
            'keluhan_awal' => ['required'],
        ]);

        $dataDokter = Dokter::findOrFail($request->dokter_id);
        $namaDokter = $dataDokter->nama_dokter;
        $dataPasien = Pasien::findOrFail($request->pasien_id);
        $namaPasien = $dataPasien->nama_pasien;

        $dataKunjungan = Kunjungan::findOrFail($request->id);

        if (empty($request->dokter_id)) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Data Dokter Dengan Nama ' . $namaDokter . 'Tidak Ada',
            ]);
        } elseif (empty($request->pasien_id)) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Data Pasien Dengan Nama ' . $namaPasien . 'Tidak Ada',
            ]);
        }

        $dataKunjungan->update([
            'dokter_id' => $request->dokter_id,
            'pasien_id' => $request->pasien_id,
            'tanggal_kunjungan' => $request->tanggal_kunjungan,
            'keluhan_awal' => $request->keluhan_awal,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataKunjungan,
            'message' => 'Data Kunjungan Berhasil Diupdate',
        ]);
    }

    public function deleteDataKunjungan(Request $request)
    {
        $dataKunjungan = Kunjungan::findOrFail($request->id);

        $dataKunjungan->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataKunjungan,
            'message' => 'Data Kunjungan Berhasil Dihapus',
        ]);
    }

    public function ubahStatusKunjungan(Request $request)
    {
        $dataKunjungan = Kunjungan::findOrFail($request->id);

        if ($dataKunjungan->status === 'Pending') {
            $dataKunjungan->update([
                'status' => 'Confirmed',
            ]);

            return response()->json([
                'success' => true,
                'Data Kunjungan' => $dataKunjungan,
                'message' => 'Berhasil mengubah status kunjungan menjadi Confirmed'
            ]);
        } elseif ($dataKunjungan->status === 'Confirmed') {
            $dataKunjungan->update([
                'status' => 'Waiting',
            ]);

            return response()->json([
                'success' => true,
                'Data Kunjungan' => $dataKunjungan,
                'message' => 'Berhasil mengubah status kunjungan menjadi Waiting'
            ]);
        } elseif ($dataKunjungan->status === 'Waiting') {
            $dataKunjungan->update([
                'status' => 'Engaged',
            ]);

            return response()->json([
                'success' => true,
                'Data Kunjungan' => $dataKunjungan,
                'message' => 'Berhasil mengubah status kunjungan menjadi Engaged'
            ]);
        }
    }
}
