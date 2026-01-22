<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Kirim notifikasi hasil lab ke pasien
     */
    public static function kirimNotifikasiHasilLab($orderLab, $pasienUserId, $hasilLab = null)
    {
        try {
            DB::table('notifications')->insert([
                'user_id' => $pasienUserId,
                'title' => 'Hasil Lab Tersedia',
                'body' => "Hasil pemeriksaan lab untuk order #{$orderLab->no_order_lab} sudah tersedia. Silakan cek hasil pemeriksaan Anda.",
                'data' => json_encode([
                    'type' => 'hasil_lab',
                    'order_lab_id' => $orderLab->id,
                    'no_order_lab' => $orderLab->no_order_lab,
                    'hasil_lab_id' => $hasilLab ? $hasilLab->id : null,
                ]),
                'sent_at' => now(),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("Notifikasi hasil lab berhasil dikirim ke user ID: {$pasienUserId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi hasil lab: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi umum
     */
    public static function kirimNotifikasi($userId, $title, $body, $data = [])
    {
        try {
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'data' => json_encode($data),
                'sent_at' => now(),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi: " . $e->getMessage());
            return false;
        }
    }
}