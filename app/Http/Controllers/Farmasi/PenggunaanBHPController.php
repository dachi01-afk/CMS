<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PenggunaanBHPController extends Controller
{
    public function index()
    {
        return view('farmasi.penggunaan-bhp.penggunaan-bhp');
    }
}
