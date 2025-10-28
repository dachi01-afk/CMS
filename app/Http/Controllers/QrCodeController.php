<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function generateAll()
    {
        $pasienList = Pasien::all();

        foreach ($pasienList as $pasien) {
            $url = route('qr.show', $pasien->id); // URL tujuan ketika QR discan
            $qrCodeSvg = QrCode::size(250)->generate($url);

            // Simpan link QR ke database (bisa juga simpan file PNG)
            $pasien->update([
                'qr_code_pasien' => $url,
            ]);
        }

        return view('qr-code-list', compact('pasienList'));
    }

    // Fungsi untuk membuat QR code per pasien
    public function generate($id)
    {
        $pasien = Pasien::findOrFail($id);
        $url = route('qr.show', $pasien->id);
        $qrCode = QrCode::size(250)->generate($url);

        $pasien->update([
            'qr_code_pasien' => $url,
        ]);

        return view('qr-code', compact('qrCode', 'pasien'));
    }

    // Fungsi untuk menampilkan data pasien dari QR
    public function showPasien($id)
    {
        $pasien = Pasien::findOrFail($id);

        return view('qr-view', compact('pasien'));
    }
}
