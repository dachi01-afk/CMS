<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EMRContohSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // =========================
            // 0) Guard tabel wajib
            // =========================
            foreach (['user','pasien','kunjungan','resep','emr'] as $tbl) {
                if (!Schema::hasTable($tbl)) {
                    throw new \RuntimeException("Tabel {$tbl} belum ada. Jalankan migrate dulu.");
                }
            }

            // =========================
            // 1) Buat/ambil user pasien
            // =========================
            $email = 'pasien@gmail.com';

            $user = DB::table('user')->where('email', $email)->first();
            if (!$user) {
                $userId = DB::table('user')->insertGetId([
                    'username' => 'pasien',
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'Pasien',
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $user = DB::table('user')->where('id', $userId)->first();
            }

            // =========================
            // 2) Buat/ambil pasien
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

                if (Schema::hasColumn('pasien', 'no_emr')) {
                    $payload['no_emr'] = 'EMR-' . str_pad((string)$user->id, 6, '0', STR_PAD_LEFT);
                }
                if (Schema::hasColumn('pasien', 'no_hp_pasien')) {
                    $payload['no_hp_pasien'] = '081234567890';
                }

                $pasienId = DB::table('pasien')->insertGetId($payload);
                $pasien = DB::table('pasien')->where('id', $pasienId)->first();
            }

            // =========================
            // 3) Ambil master minimal: poli, dokter, jadwal_dokter
            //    (asumsi seeder kamu sebelumnya sudah bikin)
            // =========================
            $poli = Schema::hasTable('poli') ? DB::table('poli')->first() : null;
            $dokter = Schema::hasTable('dokter') ? DB::table('dokter')->first() : null;
            $jadwal = Schema::hasTable('jadwal_dokter') ? DB::table('jadwal_dokter')->first() : null;

            // Kalau tidak ada, buat minimal agar EMR bisa jadi
            if (!$poli && Schema::hasTable('poli')) {
                $poliId = DB::table('poli')->insertGetId([
                    'nama_poli' => 'Poli Umum',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $poli = DB::table('poli')->where('id', $poliId)->first();
            }

            if (!$dokter && Schema::hasTable('dokter')) {
                // butuh user dokter minimal
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

                // jenis_spesialis minimal
                $sp = Schema::hasTable('jenis_spesialis') ? DB::table('jenis_spesialis')->first() : null;
                if (!$sp && Schema::hasTable('jenis_spesialis')) {
                    $spId = DB::table('jenis_spesialis')->insertGetId([
                        'nama_spesialis' => 'Umum',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $sp = DB::table('jenis_spesialis')->where('id', $spId)->first();
                }

                $dokterPayload = [
                    'user_id' => $dokterUser->id,
                    'nama_dokter' => 'Dr. Contoh',
                    'foto_dokter' => null,
                    'deskripsi_dokter' => 'Dokter contoh untuk seed EMR.',
                    'pengalaman' => '3 tahun',
                    'no_hp' => '081111111111',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if ($sp && Schema::hasColumn('dokter', 'jenis_spesialis_id')) {
                    $dokterPayload['jenis_spesialis_id'] = $sp->id;
                }
                if ($poli && Schema::hasColumn('dokter', 'poli_id')) {
                    $dokterPayload['poli_id'] = $poli->id;
                }

                $dokterId = DB::table('dokter')->insertGetId($dokterPayload);
                $dokter = DB::table('dokter')->where('id', $dokterId)->first();
            }

            if (!$jadwal && Schema::hasTable('jadwal_dokter')) {
                $jadwalPayload = [
                    'hari' => 'Senin',
                    'jam_awal' => '09:00:00',
                    'jam_selesai' => '12:00:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if ($dokter && Schema::hasColumn('jadwal_dokter', 'dokter_id')) {
                    $jadwalPayload['dokter_id'] = $dokter->id;
                }
                if ($poli && Schema::hasColumn('jadwal_dokter', 'poli_id')) {
                    $jadwalPayload['poli_id'] = $poli->id;
                }

                $jadwalId = DB::table('jadwal_dokter')->insertGetId($jadwalPayload);
                $jadwal = DB::table('jadwal_dokter')->where('id', $jadwalId)->first();
            }

            // =========================
            // 4) Buat 10 Kunjungan + Resep + EMR
            // =========================
            $keluhanUtamaList = [
                'Demam dan batuk 3 hari',
                'Pusing dan mual',
                'Nyeri ulu hati',
                'Sakit kepala sebelah',
                'Batuk pilek',
                'Nyeri sendi',
                'Gatal kulit',
                'Sesak ringan',
                'Diare',
                'Kontrol rutin',
            ];

            for ($i = 1; $i <= 10; $i++) {
                $tgl = Carbon::now()->subDays(10 - $i)->toDateString();
                $createdAt = Carbon::now()->subDays(10 - $i)->setTime(rand(8, 16), rand(0, 59), 0);

                // ---- KUNJUNGAN
                $kunjunganPayload = [
                    'tanggal_kunjungan' => $tgl,
                    'no_antrian' => str_pad((string)$i, 3, '0', STR_PAD_LEFT),
                    'keluhan_awal' => $keluhanUtamaList[$i - 1],
                    'status' => 'Succeed',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if (Schema::hasColumn('kunjungan', 'pasien_id')) {
                    $kunjunganPayload['pasien_id'] = $pasien->id;
                }
                if ($poli && Schema::hasColumn('kunjungan', 'poli_id')) {
                    $kunjunganPayload['poli_id'] = $poli->id;
                }
                if ($dokter && Schema::hasColumn('kunjungan', 'dokter_id')) {
                    $kunjunganPayload['dokter_id'] = $dokter->id;
                }
                if ($jadwal && Schema::hasColumn('kunjungan', 'jadwal_dokter_id')) {
                    $kunjunganPayload['jadwal_dokter_id'] = $jadwal->id;
                }

                $kunjunganId = DB::table('kunjungan')->insertGetId($kunjunganPayload);

                // ---- RESEP
                $resepPayload = [
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if (Schema::hasColumn('resep', 'kunjungan_id')) {
                    $resepPayload['kunjungan_id'] = $kunjunganId;
                }
                if (Schema::hasColumn('resep', 'status')) {
                    // migration kamu bikin enum waiting/done
                    $resepPayload['status'] = (rand(0, 1) ? 'waiting' : 'done');
                }

                $resepId = DB::table('resep')->insertGetId($resepPayload);

                // ---- EMR
                $emrPayload = [
                    'kunjungan_id' => $kunjunganId,
                    'resep_id' => $resepId,
                    'keluhan_utama' => $keluhanUtamaList[$i - 1],
                    'riwayat_penyakit_dahulu' => 'Tidak ada riwayat berat.',
                    'riwayat_penyakit_keluarga' => 'Tidak diketahui.',
                    'tekanan_darah' => rand(110, 130) . '/' . rand(70, 90),
                    'suhu_tubuh' => rand(360, 390) / 10, // 36.0 - 39.0
                    'nadi' => rand(70, 100),
                    'pernapasan' => rand(16, 22),
                    'saturasi_oksigen' => rand(95, 99),
                    'diagnosis' => 'Observasi / Diagnosis contoh',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                // kolom tambahan emr (kalau ada)
                if (Schema::hasColumn('emr', 'pasien_id')) $emrPayload['pasien_id'] = $pasien->id;
                if ($dokter && Schema::hasColumn('emr', 'dokter_id')) $emrPayload['dokter_id'] = $dokter->id;
                if ($poli && Schema::hasColumn('emr', 'poli_id')) $emrPayload['poli_id'] = $poli->id;
                if (Schema::hasColumn('emr', 'perawat_id')) $emrPayload['perawat_id'] = null;

                if (Schema::hasColumn('emr', 'tinggi_badan')) $emrPayload['tinggi_badan'] = rand(1550, 1800) / 10; // 155.0 - 180.0
                if (Schema::hasColumn('emr', 'berat_badan')) $emrPayload['berat_badan'] = rand(450, 900) / 10; // 45.0 - 90.0
                if (Schema::hasColumn('emr', 'imt')) $emrPayload['imt'] = null;

                $emrId = DB::table('emr')->insertGetId($emrPayload);

                // hitung IMT jika ada kolomnya
                if (Schema::hasColumn('emr', 'imt') && Schema::hasColumn('emr', 'tinggi_badan') && Schema::hasColumn('emr', 'berat_badan')) {
                    $row = DB::table('emr')->where('id', $emrId)->first();
                    if ($row && $row->tinggi_badan && $row->berat_badan) {
                        $m = ((float)$row->tinggi_badan) / 100.0;
                        $imt = $m > 0 ? ((float)$row->berat_badan) / ($m * $m) : null;
                        DB::table('emr')->where('id', $emrId)->update([
                            'imt' => $imt ? round($imt, 1) : null,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
}
