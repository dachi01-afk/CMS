<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasienTransaksiContohSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // =========================
            // 1) BUAT / AMBIL USER PASIEN
            // =========================
            $email = 'pasien@gmail.com';

            $user = DB::table('user')->where('email', $email)->first();

            if (!$user) {
                $userId = DB::table('user')->insertGetId([
                    'username' => 'pasien',
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'), // ubah kalau mau
                    'role' => 'Pasien',
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $user = DB::table('user')->where('id', $userId)->first();
            }

            // =========================
            // 2) BUAT / AMBIL DATA PASIEN
            // =========================
            $pasien = DB::table('pasien')->where('user_id', $user->id)->first();

            if (!$pasien) {
                $payload = [
                    'user_id' => $user->id,
                    'nama_pasien' => 'Pasien Contoh',
                    'alamat' => 'Jl. Contoh No. 1',
                    'tanggal_lahir' => '1998-01-01',
                    'jenis_kelamin' => 'Laki-laki',
                    'foto_pasien' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // kolom tambahan (kalau ada di schema kamu)
                if (Schema::hasColumn('pasien', 'no_emr')) {
                    $payload['no_emr'] = 'EMR-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
                }
                if (Schema::hasColumn('pasien', 'no_hp_pasien')) {
                    $payload['no_hp_pasien'] = '081234567890';
                }
                if (Schema::hasColumn('pasien', 'qr_code_pasien')) {
                    $payload['qr_code_pasien'] = 'QR-' . Str::upper(Str::random(10));
                }
                if (Schema::hasColumn('pasien', 'nik')) {
                    $payload['nik'] = '1271' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
                }
                if (Schema::hasColumn('pasien', 'no_bpjs')) {
                    $payload['no_bpjs'] = '0000' . rand(10000000, 99999999);
                }

                $pasienId = DB::table('pasien')->insertGetId($payload);
                $pasien = DB::table('pasien')->where('id', $pasienId)->first();
            }

            // =========================
            // 3) DATA MASTER MINIMAL (POLI, SPESIALIS, DOKTER, JADWAL, KATEGORI, LAYANAN)
            // =========================
            // Poli
            $poli = DB::table('poli')->first();
            if (!$poli) {
                $poliId = DB::table('poli')->insertGetId([
                    'nama_poli' => 'Poli Umum',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $poli = DB::table('poli')->where('id', $poliId)->first();
            }

            // Jenis Spesialis
            $sp = DB::table('jenis_spesialis')->first();
            if (!$sp) {
                $spId = DB::table('jenis_spesialis')->insertGetId([
                    'nama_spesialis' => 'Umum',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sp = DB::table('jenis_spesialis')->where('id', $spId)->first();
            }

            // User Dokter (minimal)
            $dokterUser = DB::table('user')->where('role', 'Dokter')->first();
            if (!$dokterUser) {
                $dokterUserId = DB::table('user')->insertGetId([
                    'username' => 'dokter1',
                    'email' => 'dokter1@gmail.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'Dokter',
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $dokterUser = DB::table('user')->where('id', $dokterUserId)->first();
            }

            // Dokter
            $dokter = DB::table('dokter')->where('user_id', $dokterUser->id)->first();
            if (!$dokter) {
                $dokterPayload = [
                    'user_id' => $dokterUser->id,
                    'nama_dokter' => 'Dr. Contoh',
                    'foto_dokter' => null,
                    'deskripsi_dokter' => 'Dokter umum contoh untuk seed.',
                    'pengalaman' => '3 tahun',
                    'jenis_spesialis_id' => $sp->id,
                    'no_hp' => '081111111111',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // kalau kolom poli_id masih ada (sebagian schema lama)
                if (Schema::hasColumn('dokter', 'poli_id')) {
                    $dokterPayload['poli_id'] = $poli->id;
                }

                $dokterId = DB::table('dokter')->insertGetId($dokterPayload);
                $dokter = DB::table('dokter')->where('id', $dokterId)->first();
            }

            // dokter_poli pivot (kalau tabel ada)
            $dokterPoliId = null;
            if (Schema::hasTable('dokter_poli')) {
                $dp = DB::table('dokter_poli')
                    ->where('dokter_id', $dokter->id)
                    ->where('poli_id', $poli->id)
                    ->first();

                if (!$dp) {
                    $dokterPoliId = DB::table('dokter_poli')->insertGetId([
                        'dokter_id' => $dokter->id,
                        'poli_id' => $poli->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $dokterPoliId = $dp->id;
                }
            }

            // Jadwal dokter (minimal 1)
            $jadwal = DB::table('jadwal_dokter')->first();
            if (!$jadwal) {
                $jadwalPayload = [
                    'hari' => 'Senin',
                    'jam_awal' => '09:00:00',
                    'jam_selesai' => '12:00:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // schema lama / baru beda-beda, isi yang ada saja
                if (Schema::hasColumn('jadwal_dokter', 'dokter_id')) {
                    $jadwalPayload['dokter_id'] = $dokter->id;
                }
                if (Schema::hasColumn('jadwal_dokter', 'poli_id')) {
                    $jadwalPayload['poli_id'] = $poli->id;
                }
                if (Schema::hasColumn('jadwal_dokter', 'dokter_poli_id') && $dokterPoliId) {
                    $jadwalPayload['dokter_poli_id'] = $dokterPoliId;
                }

                $jadwalId = DB::table('jadwal_dokter')->insertGetId($jadwalPayload);
                $jadwal = DB::table('jadwal_dokter')->where('id', $jadwalId)->first();
            }

            // Kategori layanan
            if (Schema::hasTable('kategori_layanan')) {
                $kat = DB::table('kategori_layanan')->first();
                if (!$kat) {
                    $katId = DB::table('kategori_layanan')->insertGetId([
                        'nama_kategori' => 'Konsultasi',
                        'deskripsi_kategori' => 'Kategori contoh',
                        'status_kategori' => 'Aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $kat = DB::table('kategori_layanan')->where('id', $katId)->first();
                }
            } else {
                $kat = null;
            }

            // Layanan minimal 3 item (biar order detail bervariasi)
            $layananCount = DB::table('layanan')->count();
            if ($layananCount < 3) {
                for ($i = $layananCount + 1; $i <= 3; $i++) {
                    $hargaSebelum = 50000 * $i;
                    $diskon = 5000 * ($i - 1);
                    $hargaSetelah = max(0, $hargaSebelum - $diskon);

                    $layananPayload = [
                        'nama_layanan' => "Layanan Contoh {$i}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // field harga (schema kamu ada rename harga_sebelum_diskon/diskon/harga_setelah_diskon)
                    if (Schema::hasColumn('layanan', 'harga_sebelum_diskon')) {
                        $layananPayload['harga_sebelum_diskon'] = $hargaSebelum;
                    }
                    if (Schema::hasColumn('layanan', 'diskon')) {
                        $layananPayload['diskon'] = $diskon;
                    }
                    if (Schema::hasColumn('layanan', 'harga_setelah_diskon')) {
                        $layananPayload['harga_setelah_diskon'] = $hargaSetelah;
                    }
                    if (Schema::hasColumn('layanan', 'harga_layanan')) {
                        // schema lama
                        $layananPayload['harga_layanan'] = $hargaSetelah;
                    }

                    if ($kat && Schema::hasColumn('layanan', 'kategori_layanan_id')) {
                        $layananPayload['kategori_layanan_id'] = $kat->id;
                    }

                    // schema lama layanan punya poli_id (kalau masih ada)
                    if (Schema::hasColumn('layanan', 'poli_id')) {
                        $layananPayload['poli_id'] = $poli->id;
                    }

                    $layananId = DB::table('layanan')->insertGetId($layananPayload);

                    // pivot layanan_poli (kalau tabel ada)
                    if (Schema::hasTable('layanan_poli')) {
                        $exists = DB::table('layanan_poli')
                            ->where('layanan_id', $layananId)
                            ->where('poli_id', $poli->id)
                            ->first();

                        if (!$exists) {
                            DB::table('layanan_poli')->insert([
                                'layanan_id' => $layananId,
                                'poli_id' => $poli->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            $layanans = DB::table('layanan')->limit(10)->get();

            // helper ambil harga paling relevan dari schema kamu
            $getHarga = function ($layananRow) {
                if (isset($layananRow->harga_setelah_diskon) && $layananRow->harga_setelah_diskon !== null) return (float) $layananRow->harga_setelah_diskon;
                if (isset($layananRow->harga_sebelum_diskon) && $layananRow->harga_sebelum_diskon !== null) return (float) $layananRow->harga_sebelum_diskon;
                if (isset($layananRow->harga_layanan) && $layananRow->harga_layanan !== null) return (float) $layananRow->harga_layanan;
                return 0.0;
            };

            // =========================
            // 4) BUAT 10 ORDER_LAYANAN + DETAIL
            // =========================
            $statuses = ['Draf', 'Dipesan', 'Sudah Datang', 'Menunggu Pembayaran', 'Selesai', 'Dibatalkan'];

            for ($t = 1; $t <= 10; $t++) {
                $createdAt = Carbon::now()->subDays(10 - $t)->setTime(rand(8, 16), rand(0, 59), 0);

                $orderId = DB::table('order_layanan')->insertGetId([
                    'pasien_id' => $pasien->id,
                    'poli_id' => $poli->id,
                    'dokter_id' => $dokter->id,
                    'jadwal_dokter_id' => $jadwal->id,
                    'keluhan_utama' => "Keluhan contoh transaksi #{$t}: pusing & demam ringan.",
                    'subtotal' => 0,
                    'potongan_pesanan' => 0,
                    'total_bayar' => 0,
                    'status_order_layanan' => $statuses[array_rand($statuses)],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // random 1-3 item layanan
                $pickCount = rand(1, 3);
                $picked = $layanans->shuffle()->take($pickCount);

                $subtotal = 0;
                $potongan = 0;

                foreach ($picked as $ly) {
                    $qty = rand(1, 2);
                    $harga = $getHarga($ly);
                    $diskonItem = rand(0, 1) ? (float) (5000 * rand(0, 2)) : 0.0;

                    $totalItem = max(0, ($harga * $qty) - $diskonItem);

                    DB::table('order_layanan_detail')->insert([
                        'order_layanan_id' => $orderId,
                        'layanan_id' => $ly->id,
                        'qty' => $qty,
                        'harga_satuan' => $harga,
                        'diskon_item' => $diskonItem,
                        'total_harga_item' => $totalItem,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $subtotal += ($harga * $qty);
                    $potongan += $diskonItem;
                }

                $totalBayar = max(0, $subtotal - $potongan);

                DB::table('order_layanan')->where('id', $orderId)->update([
                    'subtotal' => $subtotal,
                    'potongan_pesanan' => $potongan,
                    'total_bayar' => $totalBayar,
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
