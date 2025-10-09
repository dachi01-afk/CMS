<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Testimoni;
use Illuminate\Http\Request;

class APIWebController extends Controller
{
    public function getDataDokter()
    {
        $dataDokter = Dokter::all();
        return response()->json([
            'success' => true,
            'status' => 200,
            'Data Dokter' => $dataDokter,
            'message' => 'Berhasil memunculkan data dokter',
        ]);
    }

    public function getDataTestimoni()
    {
        $dataTestimoni = Testimoni::all();

        return response()->json([
            'success' => true,
            'status' => 200,
            'Data Testimoni' => $dataTestimoni,
            'message' => 'Berhasil Memunculkan Data Testimoni'
        ]);
    }
}
