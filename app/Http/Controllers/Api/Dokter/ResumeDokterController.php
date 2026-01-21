<?php

namespace App\Http\Controllers\Api\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Emr;
use App\Models\ResumeDokter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumeDokterController extends Controller
{
    /**
     * GET /api/dokter/emr/{emrId}/resume
     * Mengambil resume dokter berdasarkan EMR ID
     */
    public function show($emrId)
    {
        $emr = Emr::findOrFail($emrId);
        $resume = ResumeDokter::where('emr_id', $emr->id)->first();

        if (!$resume) {
            return response()->json([
                'success' => false,
                'message' => 'Resume belum dibuat untuk EMR ini',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Resume dokter ditemukan',
            'data' => $resume,
        ]);
    }

    /**
     * POST /api/dokter/emr/{emrId}/resume
     * Create/Update draft resume
     */
    public function store(Request $request, $emrId)
    {
        $request->validate([
            'ringkasan_kasus' => 'nullable|string',
            'diagnosis_utama' => 'nullable|string',
            'diagnosis_sekunder' => 'nullable|string',
            'tindakan' => 'nullable|string',
            'terapi_ringkas' => 'nullable|string',
            'hasil_penunjang_ringkas' => 'nullable|string',
            'kondisi_akhir' => 'nullable|string|max:255',
            'instruksi_pulang' => 'nullable|string',
            'rencana_tindak_lanjut' => 'nullable|string',
        ]);

        $emr = Emr::findOrFail($emrId);

        $dokter = Dokter::where('user_id', Auth::id())->first();
        if (!$dokter) {
            return response()->json([
                'success' => false,
                'message' => 'Data dokter tidak ditemukan.',
            ], 404);
        }

        $existing = ResumeDokter::where('emr_id', $emr->id)->first();

        if ($existing && $existing->status === 'final') {
            return response()->json([
                'success' => false,
                'message' => 'Resume sudah FINAL, tidak bisa diubah.',
                'data' => $existing,
            ], 422);
        }

        $resume = ResumeDokter::updateOrCreate(
            ['emr_id' => $emr->id],
            array_merge(
                $request->only([
                    'ringkasan_kasus',
                    'diagnosis_utama',
                    'diagnosis_sekunder',
                    'tindakan',
                    'terapi_ringkas',
                    'hasil_penunjang_ringkas',
                    'kondisi_akhir',
                    'instruksi_pulang',
                    'rencana_tindak_lanjut',
                ]),
                [
                    'dokter_id' => $dokter->id,
                    'status' => 'draft',
                    'finalized_at' => null,
                ]
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Resume dokter (draft) tersimpan',
            'data' => $resume,
        ]);
    }

    /**
     * POST /api/dokter/emr/{emrId}/resume/finalize
     * Finalize resume (ubah status jadi FINAL)
     */
    public function finalize($emrId)
    {
        $resume = ResumeDokter::where('emr_id', $emrId)->first();

        if (!$resume) {
            return response()->json([
                'success' => false,
                'message' => 'Resume belum dibuat. Simpan draft terlebih dahulu.',
            ], 404);
        }

        if ($resume->status === 'final') {
            return response()->json([
                'success' => true,
                'message' => 'Resume sudah FINAL',
                'data' => $resume,
            ]);
        }

        $resume->update([
            'status' => 'final',
            'finalized_at' => now(),
        ]);

        $resume->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Resume dokter berhasil difinalisasi',
            'data' => $resume,
        ]);
    }
}