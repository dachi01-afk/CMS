<?php

namespace App\Helpers;

use App\Models\Pasien;
use App\Models\User;
use App\Services\FCMService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Helper untuk kirim notifikasi DB + FCM sekaligus
     */
    private static function sendNotification($userId, $title, $body, array $data = [])
    {
        try {
            Log::info('🔔 Sending notification to user', [
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);

            // 1️⃣ Simpan ke database
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

            Log::info('✅ Notification saved to database', [
                'user_id' => $userId,
            ]);

            // 2️⃣ Kirim FCM push notification
            $user = User::find($userId);

            if ($user && $user->fcm_token) {
                Log::info('📱 Sending FCM to token', [
                    'user_id' => $userId,
                    'fcm_token' => substr($user->fcm_token, 0, 20).'...',
                ]);

                $fcmService = new FCMService;
                $fcmService->sendToToken(
                    $user->fcm_token,
                    $title,
                    $body,
                    $data
                );

                Log::info('✅ Notification sent successfully', [
                    'user_id' => $userId,
                ]);
            } else {
                Log::warning("⚠️ User {$userId} tidak memiliki FCM token", [
                    'user_exists' => $user !== null,
                    'has_fcm_token' => $user ? ($user->fcm_token !== null) : false,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('❌ Gagal mengirim notifikasi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId ?? null,
            ]);

            return false;
        }
    }

    /**
     * Kirim notifikasi hasil lab ke pasien
     */
    public static function kirimNotifikasiHasilLab($orderLab, $hasilLab = null)
    {
        try {
            Log::info('📋 Preparing notification for hasil lab', [
                'order_lab_id' => $orderLab->id,
                'pasien_id' => $orderLab->pasien_id,
            ]);

            $pasien = Pasien::with('user')->find($orderLab->pasien_id);

            if (!$pasien || !$pasien->user_id) {
                Log::warning("⚠️ Pasien tidak memiliki user_id untuk order_lab: {$orderLab->id}");
                return false;
            }

            $namaPasien = $pasien->nama_pasien ?? 'Pasien';

            $title = '🔬 Hasil Lab Sudah Tersedia!';
            $body = "Halo {$namaPasien}! Hasil pemeriksaan laboratorium Anda sudah dapat dilihat. Silakan cek aplikasi untuk melihat hasilnya. Salam sehat! 🌟";

            $data = [
                'type' => 'hasil_lab',
                'order_lab_id' => (string) $orderLab->id,
                'no_order_lab' => $orderLab->no_order_lab,
                'hasil_lab_id' => $hasilLab ? (string) $hasilLab->id : '',
                'tanggal_pemeriksaan' => $orderLab->tanggal_pemeriksaan ?? '',
                'nama_pasien' => $namaPasien,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'route' => '/hasil-lab-detail',
            ];

            return self::sendNotification($pasien->user_id, $title, $body, $data);

        } catch (\Exception $e) {
            Log::error('❌ Gagal mengirim notifikasi hasil lab', [
                'error' => $e->getMessage(),
                'order_lab_id' => $orderLab->id ?? null,
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
            $pasien = Pasien::with('user')->find($orderLab->pasien_id);

            if (!$pasien || !$pasien->user_id) {
                return false;
            }

            $namaPasien = $pasien->nama_pasien ?? 'Pasien';

            $title = '🔄 Hasil Lab Diperbarui';
            $body = "Halo {$namaPasien}! Ada pembaruan hasil pemeriksaan laboratorium Anda. Silakan cek kembali aplikasi untuk informasi terbaru. Salam sehat! 🌟";

            $data = [
                'type' => 'hasil_lab_updated',
                'order_lab_id' => (string) $orderLab->id,
                'no_order_lab' => $orderLab->no_order_lab,
                'hasil_lab_id' => (string) $hasilLab->id,
                'nama_pasien' => $namaPasien,
                'updated_at' => now()->toISOString(),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'route' => '/hasil-lab-detail',
            ];

            return self::sendNotification($pasien->user_id, $title, $body, $data);

        } catch (\Exception $e) {
            Log::error('❌ Gagal mengirim notifikasi update hasil lab: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Kirim notifikasi hasil radiologi ke pasien
     */
    public static function kirimNotifikasiHasilRadiologi($orderRadiologi, $hasilRadiologi = null)
    {
        try {
            Log::info('📸 Preparing notification for hasil radiologi', [
                'order_radiologi_id' => $orderRadiologi->id,
                'pasien_id' => $orderRadiologi->pasien_id,
            ]);

            $pasien = Pasien::with('user')->find($orderRadiologi->pasien_id);

            if (!$pasien || !$pasien->user_id) {
                Log::warning("⚠️ Pasien tidak memiliki user_id untuk order_radiologi: {$orderRadiologi->id}");
                return false;
            }

            $namaPasien = $pasien->nama_pasien ?? 'Pasien';

            $title = '📸 Hasil Radiologi Sudah Tersedia!';
            $body = "Halo {$namaPasien}! Hasil pemeriksaan radiologi Anda sudah dapat dilihat. Silakan cek aplikasi untuk melihat hasilnya. Salam sehat! 🌟";

            $data = [
                'type' => 'hasil_radiologi',
                'order_radiologi_id' => (string) $orderRadiologi->id,
                'no_order_radiologi' => $orderRadiologi->no_order_radiologi,
                'hasil_radiologi_id' => $hasilRadiologi ? (string) $hasilRadiologi->id : '',
                'tanggal_pemeriksaan' => $orderRadiologi->tanggal_pemeriksaan ?? '',
                'nama_pasien' => $namaPasien,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'route' => '/hasil-radiologi-detail',
            ];

            return self::sendNotification($pasien->user_id, $title, $body, $data);

        } catch (\Exception $e) {
            Log::error('❌ Gagal mengirim notifikasi hasil radiologi', [
                'error' => $e->getMessage(),
                'order_radiologi_id' => $orderRadiologi->id ?? null,
            ]);

            return false;
        }
    }

    /**
     * Kirim notifikasi EMR selesai dengan jadwal lab/radiologi
     */
    public static function kirimNotifikasiEMRSelesai($kunjungan, $emrData)
    {
        try {
            Log::info('✅ Preparing notification for EMR selesai', [
                'kunjungan_id' => $kunjungan->id,
                'pasien_id' => $kunjungan->pasien_id,
            ]);

            $pasien = Pasien::with('user')->find($kunjungan->pasien_id);

            if (!$pasien || !$pasien->user_id) {
                Log::warning("⚠️ Pasien tidak memiliki user_id untuk kunjungan: {$kunjungan->id}");
                return false;
            }

            $namaPasien = $pasien->nama_pasien ?? 'Pasien';

            $title = '✅ Pemeriksaan Selesai!';
            $body = "Halo {$namaPasien}! Pemeriksaan Anda telah selesai. ";

            $notifData = [
                'type' => 'emr_completed',
                'kunjungan_id' => (string) $kunjungan->id,
                'emr_id' => isset($emrData['emr']) ? (string) $emrData['emr']->id : '',
                'nama_pasien' => $namaPasien,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'route' => '/riwayat-kunjungan-detail',
            ];

            // Info Lab
            if (!empty($emrData['order_lab_id'])) {
                $orderLab = DB::table('order_lab')->where('id', $emrData['order_lab_id'])->first();

                if ($orderLab && $orderLab->tanggal_pemeriksaan) {
                    $tanggalLab = Carbon::parse($orderLab->tanggal_pemeriksaan)->locale('id')->translatedFormat('d F Y');
                    $jamLab = substr($orderLab->jam_pemeriksaan ?? '00:00', 0, 5);
                    $body .= "Jadwal pemeriksaan lab Anda: {$tanggalLab} pukul {$jamLab} WIB. ";

                    $notifData['order_lab'] = [
                        'id' => (string) $orderLab->id,
                        'no_order' => $orderLab->no_order_lab,
                        'tanggal' => $orderLab->tanggal_pemeriksaan,
                        'jam' => $orderLab->jam_pemeriksaan,
                    ];
                }
            }

            // Info Radiologi
            if (!empty($emrData['order_radiologi_id'])) {
                $orderRadiologi = DB::table('order_radiologi')->where('id', $emrData['order_radiologi_id'])->first();

                if ($orderRadiologi && $orderRadiologi->tanggal_pemeriksaan) {
                    $tanggalRad = Carbon::parse($orderRadiologi->tanggal_pemeriksaan)->locale('id')->translatedFormat('d F Y');
                    $jamRad = substr($orderRadiologi->jam_pemeriksaan ?? '00:00', 0, 5);
                    $body .= "Jadwal pemeriksaan radiologi Anda: {$tanggalRad} pukul {$jamRad} WIB. ";

                    $notifData['order_radiologi'] = [
                        'id' => (string) $orderRadiologi->id,
                        'no_order' => $orderRadiologi->no_order_radiologi,
                        'tanggal' => $orderRadiologi->tanggal_pemeriksaan,
                        'jam' => $orderRadiologi->jam_pemeriksaan,
                    ];
                }
            }

            // Info Pembayaran
            if (!empty($emrData['pembayaran'])) {
                $totalTagihan = number_format($emrData['pembayaran']->total_tagihan ?? 0, 0, ',', '.');
                $body .= "Silakan menuju kasir untuk pembayaran sebesar Rp {$totalTagihan}. ";
                
                $notifData['pembayaran'] = [
                    'id' => (string) $emrData['pembayaran']->id,
                    'total_tagihan' => (string) ($emrData['pembayaran']->total_tagihan ?? 0),
                ];
            }

            $body .= "Terima kasih atas kepercayaan Anda. Salam sehat! 🌟";

            return self::sendNotification($pasien->user_id, $title, $body, $notifData);

        } catch (\Exception $e) {
            Log::error('❌ Gagal mengirim notifikasi EMR selesai', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjungan->id ?? null,
            ]);

            return false;
        }
    }

    /**
     * Kirim notifikasi saat status kunjungan berubah ke Engaged
     */
    public static function kirimNotifikasiStatusEngaged($kunjungan)
    {
        try {
            Log::info('🏥 Preparing notification for status engaged', [
                'kunjungan_id' => $kunjungan->id,
                'pasien_id' => $kunjungan->pasien_id,
            ]);

            $pasien = Pasien::with('user')->find($kunjungan->pasien_id);

            if (!$pasien || !$pasien->user_id) {
                Log::warning("⚠️ Pasien tidak memiliki user_id untuk kunjungan: {$kunjungan->id}");
                return false;
            }

            $namaPasien = $pasien->nama_pasien ?? 'Pasien';
            
            // Ambil data dokter untuk ditampilkan
            $dokter = $kunjungan->dokter;
            $namaDokter = $dokter ? $dokter->nama_dokter : 'Dokter';

            $title = '👨‍⚕️ Giliran Anda!';
            $body = "Halo {$namaPasien}! Dokter {$namaDokter} sudah siap memeriksa Anda. Nomor antrian Anda: {$kunjungan->no_antrian}. Silakan menuju ruang pemeriksaan. Salam sehat! 🌟";

            $data = [
                'type' => 'status_engaged',
                'kunjungan_id' => (string) $kunjungan->id,
                'no_antrian' => $kunjungan->no_antrian,
                'status' => 'Engaged',
                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'dokter_id' => (string) $kunjungan->dokter_id,
                'nama_dokter' => $namaDokter,
                'nama_pasien' => $namaPasien,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'route' => '/detail-kunjungan',
            ];

            return self::sendNotification($pasien->user_id, $title, $body, $data);

        } catch (\Exception $e) {
            Log::error('❌ Gagal mengirim notifikasi status engaged', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjungan->id ?? null,
            ]);

            return false;
        }
    }

    /**
     * Kirim notifikasi pembayaran selesai ke pasien
     */
    public static function kirimNotifikasiPembayaranSelesai($pembayaran)
    {
        try {
            Log::info('💰 Preparing notification for pembayaran selesai', [
                'pembayaran_id' => $pembayaran->id,
                'kode_transaksi' => $pembayaran->kode_transaksi,
            ]);

            // Ambil pasien dari relasi pembayaran -> emr -> kunjungan -> pasien
            $pasien = $pembayaran->emr->kunjungan->pasien ?? null;

            if (!$pasien || !$pasien->user_id) {
                Log::warning("⚠️ Pasien tidak memiliki user_id untuk pembayaran: {$pembayaran->id}");
                return false;
            }

            $namaPasien = $pasien->nama_pasien ?? 'Pasien';
            $metodePembayaran = $pembayaran->metodePembayaran->nama_metode ?? 'Tunai';
            $totalTagihan = number_format($pembayaran->total_setelah_diskon ?? $pembayaran->total_tagihan, 0, ',', '.');

            $title = '✅ Pembayaran Berhasil!';
            $body = "Halo {$namaPasien}! Pembayaran Anda sudah kami terima sebesar Rp {$totalTagihan}. ";

            $data = [
                'type' => 'pembayaran_selesai',
                'pembayaran_id' => (string) $pembayaran->id,
                'kode_transaksi' => $pembayaran->kode_transaksi,
                'total_tagihan' => (string) ($pembayaran->total_tagihan ?? 0),
                'total_setelah_diskon' => (string) ($pembayaran->total_setelah_diskon ?? 0),
                'metode_pembayaran' => $metodePembayaran,
                'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran ?? now()->toDateString(),
                'status' => 'Sudah Bayar',
                'nama_pasien' => $namaPasien,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'route' => '/riwayat-pembayaran-detail',
            ];

            // Tambahkan info kembalian jika ada (untuk cash)
            if ($pembayaran->kembalian && $pembayaran->kembalian > 0) {
                $kembalian = number_format($pembayaran->kembalian, 0, ',', '.');
                $body .= "Kembalian Anda: Rp {$kembalian}. ";
                $data['kembalian'] = (string) $pembayaran->kembalian;
            }

            $body .= "Terima kasih atas kepercayaan Anda. Semoga lekas sembuh! Salam sehat! 🌟";

            return self::sendNotification($pasien->user_id, $title, $body, $data);

        } catch (\Exception $e) {
            Log::error('❌ Gagal mengirim notifikasi pembayaran selesai', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pembayaran_id' => $pembayaran->id ?? null,
            ]);

            return false;
        }
    }
}