<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use Illuminate\Http\Request;

class APIWebController extends Controller
{
    public function getDataDokter () {
        $dataDokter = Dokter::all();
        return response()->json([
            'success' => true,
            'status' => 200,
            'Data Dokter' => $dataDokter,
            'message' => 'Berhasil memunculkan data dokter',
        ]);
    }
}
