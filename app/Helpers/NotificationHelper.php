<?php
// app/Helpers/NotificationHelper.php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Pasien;
use Carbon\Carbon;

class NotificationHelper
{
    /**
     * Kirim notifikasi hasil lab ke pasien
     */
    public static function kirimNotifikasiHasilLab($orderLab, $hasilLab = null)
    {
        try {
            // ✅ Ambil user_id dari pasien
            $pasien = Pasien::find($orderLab->pasien_id);
            
            if (!$pasien || !$pasien->user_id) {
                Log::warning("Pasien tidak memiliki user_id untuk order_lab: {$orderLab->id}");
                return false;
            }

            $userId = $pasien->user_id;

            // ✅ Insert notifikasi
            $notifId = DB::table('notifications')->insertGetId([
                'user_id' => $userId,
                'title' => 'Hasil Lab Tersedia',
                'body' => "Hasil pemeriksaan lab untuk order #{$orderLab->no_order_lab} sudah tersedia. Silakan cek hasil pemeriksaan Anda.",
                'data' => json_encode([
                    'type' => 'hasil_lab',
                    'order_lab_id' => $orderLab->id,
                    'no_order_lab' => $orderLab->no_order_lab,
                    'hasil_lab_id' => $hasilLab ? $hasilLab->id : null,
                    'tanggal_pemeriksaan' => $orderLab->tanggal_pemeriksaan,
                ]),
                'sent_at' => now(),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("✅ Notifikasi hasil lab berhasil dikirim", [
                'notif_id' => $notifId,
                'user_id' => $userId,
                'order_lab_id' => $orderLab->id,
                'pasien_id' => $pasien->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("❌ Gagal mengirim notifikasi hasil lab: " . $e->getMessage(), [
                'order_lab_id' => $orderLab->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Kirim notifikasi update hasil lab
     */
    public static function kirimNotifikasiUpdateHasilLab($orderLab, $hasilLab)
    {
        try {
            $pasien = Pasien::find($orderLab->pasien_id);
            
            if (!$pasien || !$pasien->user_id) {
                return false;
            }

            DB::table('notifications')->insert([
                'user_id' => $pasien->user_id,
                'title' => 'Hasil Lab Diperbarui',
                'body' => "Hasil pemeriksaan lab untuk order #{$orderLab->no_order_lab} telah diperbarui. Silakan cek kembali hasil pemeriksaan Anda.",
                'data' => json_encode([
                    'type' => 'hasil_lab_updated',
                    'order_lab_id' => $orderLab->id,
                    'no_order_lab' => $orderLab->no_order_lab,
                    'hasil_lab_id' => $hasilLab->id,
                    'updated_at' => now()->toISOString(),
                ]),
                'sent_at' => now(),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("✅ Notifikasi update hasil lab berhasil dikirim ke user ID: {$pasien->user_id}");
            return true;

        } catch (\Exception $e) {
            Log::error("❌ Gagal mengirim notifikasi update hasil lab: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ BARU: Kirim notifikasi EMR selesai dengan jadwal lab/radiologi
     */
    public static function kirimNotifikasiEMRSelesai($kunjungan, $emrData)
    {
        try {
            // Ambil user_id dari pasien
            $pasien = Pasien::find($kunjungan->pasien_id);
            
            if (!$pasien || !$pasien->user_id) {
                Log::warning("Pasien tidak memiliki user_id untuk kunjungan: {$kunjungan->id}");
                return false;
            }

            // Build notifikasi content
            $title = '✅ Pemeriksaan Selesai';
            $body = 'Pemeriksaan Anda telah selesai. ';
            
            $notifData = [
                'type' => 'emr_completed',
                'kunjungan_id' => $kunjungan->id,
                'emr_id' => $emrData['emr']->id ?? null,
            ];

            // ✅ Info Lab
            if (!empty($emrData['order_lab_id'])) {
                $orderLab = DB::table('order_lab')
                    ->where('id', $emrData['order_lab_id'])
                    ->first();
                
                if ($orderLab && $orderLab->tanggal_pemeriksaan) {
                    $tanggalLab = Carbon::parse($orderLab->tanggal_pemeriksaan)->format('d M Y');
                    $jamLab = substr($orderLab->jam_pemeriksaan ?? '00:00', 0, 5);
                    
                    $body .= "📋 Lab dijadwalkan: {$tanggalLab} jam {$jamLab}. ";
                    
                    $notifData['order_lab'] = [
                        'id' => $orderLab->id,
                        'no_order' => $orderLab->no_order_lab,
                        'tanggal' => $orderLab->tanggal_pemeriksaan,
                        'jam' => $orderLab->jam_pemeriksaan,
                    ];
                }
            }

            // ✅ Info Radiologi
            if (!empty($emrData['order_radiologi_id'])) {
                $orderRadiologi = DB::table('order_radiologi')
                    ->where('id', $emrData['order_radiologi_id'])
                    ->first();
                
                if ($orderRadiologi && $orderRadiologi->tanggal_pemeriksaan) {
                    $tanggalRad = Carbon::parse($orderRadiologi->tanggal_pemeriksaan)->format('d M Y');
                    $jamRad = substr($orderRadiologi->jam_pemeriksaan ?? '00:00', 0, 5);
                    
                    $body .= "🔬 Radiologi dijadwalkan: {$tanggalRad} jam {$jamRad}. ";
                    
                    $notifData['order_radiologi'] = [
                        'id' => $orderRadiologi->id,
                        'no_order' => $orderRadiologi->no_order_radiologi,
                        'tanggal' => $orderRadiologi->tanggal_pemeriksaan,
                        'jam' => $orderRadiologi->jam_pemeriksaan,
                    ];
                }
            }

            // ✅ Info Pembayaran
            if (!empty($emrData['pembayaran'])) {
                $body .= "💰 Silakan menuju kasir untuk pembayaran.";
                $notifData['pembayaran'] = [
                    'id' => $emrData['pembayaran']->id,
                    'total_tagihan' => $emrData['pembayaran']->total_tagihan ?? 0,
                ];
            }

            // ✅ Insert notifikasi
            $notifId = DB::table('notifications')->insertGetId([
                'user_id' => $pasien->user_id,
                'title' => $title,
                'body' => $body,
                'data' => json_encode($notifData),
                'sent_at' => now(),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("✅ Notifikasi EMR selesai berhasil dikirim", [
                'notif_id' => $notifId,
                'user_id' => $pasien->user_id,
                'kunjungan_id' => $kunjungan->id,
                'has_lab' => !empty($emrData['order_lab_id']),
                'has_radiologi' => !empty($emrData['order_radiologi_id']),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("❌ Gagal mengirim notifikasi EMR selesai: " . $e->getMessage(), [
                'kunjungan_id' => $kunjungan->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}