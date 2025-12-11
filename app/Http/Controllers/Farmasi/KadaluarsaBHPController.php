<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KadaluarsaBHPController extends Controller
{
    public function index()
    {
        return view('farmasi.kadaluarsa-bhp.kadaluarsa-bhp');
    }
}
