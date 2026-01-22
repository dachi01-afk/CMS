<?php

namespace App\Listeners;

use App\Events\HasilLabTersedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KirimNotifikasiHasilLab
{
    public function handle(HasilLabTersedia $event)
    {
        try {
            $hasilLab = $event->hasilLab;
            $pasien = $event->pasien;
            $orderLab = $event->orderLab;

            // Ambil user_id dari pasien
            $userId = $pasien->user_id;

            // Buat notifikasi
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'title' => 'Hasil Lab Tersedia',
                'body' => "Hasil pemeriksaan lab untuk order #{$orderLab->no_order_lab} sudah tersedia. Silakan cek hasil pemeriksaan Anda.",
                'data' => json_encode([
                    'type' => 'hasil_lab',
                    'order_lab_id' => $orderLab->id,
                    'hasil_lab_id' => $hasilLab->id,
                    'no_order_lab' => $orderLab->no_order_lab,
                ]),
                'sent_at' => now(),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("Notifikasi hasil lab berhasil dikirim ke pasien ID: {$userId}");

        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi hasil lab: " . $e->getMessage());
        }
    }
}