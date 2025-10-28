<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\EMR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EMRController extends Controller
{
    public function createEMR(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'riwayat_penyakit' => ['required'],
            'alergi' => ['required'],
            'hasil_periksa' => ['required'],
        ]);

        $dataEMR = EMR::create([
            'kunjungan_id' => $request->kunjungan_id,
            'riwayat_penyakit' => $request->riwayat_penyakit,
            'alergi' => $request->alergi,
            'hasil_periksa' => $request->hasil_periksa,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataEMR,
            'message' => 'Data EMR Berhasil Ditambahkan',
        ]);
    }

    public function updateEMR(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'riwayat_penyakit' => ['required'],
            'alergi' => ['required'],
            'hasil_periksa' => ['required'],
        ]);

        $dataEMR = EMR::findOrFail($request->id);

        $dataEMR->update([
            'kunjungan_id' => $request->kunjungan_id,
            'riwayat_penyakit' => $request->riwayat_penyakit,
            'alergi' => $request->alergi,
            'hasil_periksa' => $request->hasil_periksa,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataEMR,
            'message' => 'Data EMR Berhasil Diupdate',
        ]);
    }

    public function deleteEMR(Request $request)
    {
        $dataEMR = EMR::findOrFail($request->id);

        $dataEMR->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataEMR,
            'message' => 'Data EMR Berhasil Dihapus',
        ]);
    }

    public function generateAll()
    {
        // Ambil semua data EMR beserta relasi kunjungan & pasien
        $emrList = Emr::with('kunjungan.pasien')->get();

        DB::beginTransaction();
        try {
            foreach ($emrList as $emr) {
                // Lewati jika sudah punya no_rm
                if (!empty($emr->no_rm)) {
                    continue;
                }

                // Ambil pasien_id dari tabel kunjungan
                $pasienId = $emr->kunjungan->pasien_id ?? null;

                if ($pasienId) {
                    // Buat no_rm unik berbasis pasien & EMR
                    // Format: RM-[PasienID 3 digit]-[EMR ID 4 digit]
                    $newNoRm = sprintf('RM-%03d-%04d', $pasienId, $emr->id);

                    // Pastikan tidak ada duplikat di tabel EMR
                    $exists = Emr::where('no_rm', $newNoRm)->exists();

                    if (!$exists) {
                        $emr->update(['no_rm' => $newNoRm]);
                    } else {
                        // Jika sudah ada (sangat jarang), buat varian baru
                        $emr->update(['no_rm' => $newNoRm . '-' . uniqid()]);
                    }
                }
            }

            DB::commit();

            $emrList = Emr::all();
            return view('testing-emr', compact('emrList'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal generate no_rm: ' . $e->getMessage());
        }
    }
}
