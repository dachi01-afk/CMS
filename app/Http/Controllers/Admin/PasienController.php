<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    public function index()
    {
        $dataPasien = Pasien::all();

        return view('admin.pasien');
    }

    public function createPasien(Request $request)
    {

        $request->validate([
            'nama_pasien' => ['required'],
            'alamat' => ['required'],
            'tanggal_lahir' => ['required', 'date'],
        ]);

        Pasien::created([
            'nama_pasien' => $request->
        ]);

        return $request;
    }
}
