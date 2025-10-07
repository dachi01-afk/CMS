<?php

namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function index()
    {
        $dataJadwalDokter = JadwalDokter::with('dokter')->get();

        return response()->json([
            'data Jadwal Dokter' => $dataJadwalDokter
        ]);
    }
}
