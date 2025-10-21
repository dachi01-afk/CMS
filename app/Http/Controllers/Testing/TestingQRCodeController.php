<?php

namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestingQRCodeController extends Controller
{
    public function index() {
        return view('qr-code');
    }
}
