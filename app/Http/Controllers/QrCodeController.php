<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function generate()
    {
        $url = 'https://www.youtube.com/watch?v=76ptEG7WxSA&list=RD76ptEG7WxSA&start_radio=1';
        // Generate QR code for a specific URL or text
        $qrCode = QrCode::size(200)->generate($url);

        // You can also customize the QR code further
        // $qrCode = QrCode::format('png')->size(200)->color(255,0,0)->generate('Your Text Here');

        return view('qr-code', compact('qrCode'));
    }
}
