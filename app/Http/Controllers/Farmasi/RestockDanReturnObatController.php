<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RestockDanReturnObatController extends Controller
{
    public function index()
    {
        return view('farmasi.restock-dan-return-obat.restock-dan-return-obat');
    }

    public function getData() {
        
    }
}
