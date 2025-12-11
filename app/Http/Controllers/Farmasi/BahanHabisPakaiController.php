<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BahanHabisPakaiController extends Controller
{
    public function index() {
        return view('farmasi.bahan-habis-pakai.bahan-habis-pakai');
    }

    
}
