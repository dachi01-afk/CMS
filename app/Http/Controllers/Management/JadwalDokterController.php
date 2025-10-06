<?php

namespace App\Http\Controllers\Management;

use App\Models\Dokter;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;


class JadwalDokterController extends Controller
{

    public function createJadwalDokter(Request $request)
    {

        $request->validate([
            'dokter_id'     => ['required', 'exists:dokter,id'],
            'hari'          => ['required', 'string', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
            'jam_awal'      => ['required', 'date_format:H:i:s'],
            'jam_selesai'   => ['required', 'date_format:H:i:s', 'after:jam_awal'],
        ]);

        $newDoctorId = $request->dokter_id;
        $newDay = $request->hari;
        $newStartTime = $request->jam_awal;
        $newEndTime = $request->jam_selesai;


        $isOverlapping = JadwalDokter::where('dokter_id', $newDoctorId)
            ->where('hari', $newDay)
            ->where(function ($query) use ($newStartTime, $newEndTime) {
                $query->where(function ($q) use ($newStartTime, $newEndTime) {
                    $q->where('jam_awal', '<', $newEndTime)
                        ->where('jam_selesai', '>', $newStartTime);
                });
            })
            ->exists();

        if ($isOverlapping) {
            return response()->json([
                'message' => 'Gagal menambahkan jadwal.',
                'errors' => [
                    'jam_awal' => ['Jadwal ini bertabrakan dengan sesi lain yang sudah ada untuk hari ' . $newDay . '.']
                ]
            ], 422);
        }

        $jadwal = JadwalDokter::create($request->all());

        return response()->json([
            'message' => 'Sesi jadwal berhasil ditambahkan.',
            'data' => $jadwal
        ], 201);
    }

    public function getJadwalDokterById($id)
    {
        $jadwal = JadwalDokter::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $jadwal
        ]);
    }

    public function updateJadwalDokter(Request $request, string $id)
    {
        $jadwal = JadwalDokter::findOrFail($id);

        $request->validate([
            'dokter_id'     => ['sometimes', 'required', 'exists:dokter,id'],
            'hari'          => ['sometimes', 'required', 'string', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
            'jam_awal'      => ['sometimes', 'required', 'date_format:H:i:s'],
            'jam_selesai'   => ['sometimes', 'required', 'date_format:H:i:s', 'after:jam_awal'],
        ]);

        $newDoctorId = $request->input('dokter_id', $jadwal->dokter_id);
        $newDay = $request->input('hari', $jadwal->hari);
        $newStartTime = $request->input('jam_awal', $jadwal->jam_awal);
        $newEndTime = $request->input('jam_selesai', $jadwal->jam_selesai);


        $isOverlapping = JadwalDokter::where('dokter_id', $newDoctorId)
            ->where('hari', $newDay)
            ->where('id', '!=', $jadwal->id)
            ->where(function ($query) use ($newStartTime, $newEndTime) {
                $query->where(function ($q) use ($newStartTime, $newEndTime) {
                    $q->where('jam_awal', '<', $newEndTime)
                        ->where('jam_selesai', '>', $newStartTime);
                });
            })
            ->exists();

        if ($isOverlapping) {
            return response()->json([
                'message' => 'Gagal memperbarui jadwal.',
                'errors' => [
                    'jam_awal' => ['Jadwal ini bertabrakan dengan sesi lain yang sudah ada untuk hari ' . $newDay . '.']
                ]
            ], 422);
        }

        $jadwal->update($request->all());

        return response()->json([
            'message' => 'Sesi jadwal berhasil diperbarui.',
            'data' => $jadwal
        ]);
    }

    public function deleteJadwalDokter($id)
    {
        $dataJadwalDokter = JadwalDokter::findOrFail($id);
        $dataJadwalDokter->delete();
        return response()->json([
            'success' => true,
            'data' => $dataJadwalDokter,
            'message' => 'Jadwal dokter berhasil dihapus.',
        ]);
    }
}
