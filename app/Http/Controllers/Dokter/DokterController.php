<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DokterController extends Controller
{
    public function index()
    {
        return view('dokter.index');
    }

    public function logoutDokter()
    {
        Auth::logout();

        return redirect()->route('dokter.login');
    }
}
