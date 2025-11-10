<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KunjunganController extends Controller
{
    public function index()
    {
        return view('perawat.kunjungan.kunjungan');
    }
}
