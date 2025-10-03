<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HistoryKunjunganController extends Controller
{
    public function index()
    {
        return view('admin.history_kunjungan');
    }
}
