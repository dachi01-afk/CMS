<?php

namespace Database\Seeders;

use App\Models\HasilRadiologi;
use App\Models\JenisPemeriksaanRadiologi;
use App\Models\OrderRadiologi;
use App\Models\OrderRadiologiDetail;
use App\Models\Perawat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrderRadiologiDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisPemeriksaanList = JenisPemeriksaanRadiologi::get();
        $perawatList = Perawat::get();
        $orderList = OrderRadiologi::get();

        if ($jenisPemeriksaanList->isEmpty()) {
            $this->command->error('Tabel jenis_pemeriksaan_radiologi masih kosong!');
            return;
        }

        if ($orderList->isEmpty()) {
            $this->command->error('Tabel order_radiologi masih kosong! Jalankan OrderRadiologiSeeder dulu.');
            return;
        }

        if ($perawatList->isEmpty()) {
            $this->command->error('Tabel perawat masih kosong! Dummy hasil radiologi butuh data perawat.');
            return;
        }

        foreach ($orderList as $order) {
            // Biar aman kalau seeder dijalankan ulang
            if ($order->orderRadiologiDetail()->exists()) {
                continue;
            }

            $jumlahDetail = rand(1, 4);

            $jenisTerpilih = $jenisPemeriksaanList->shuffle()->take($jumlahDetail);

            foreach ($jenisTerpilih as $index => $jenis) {
                $statusDetail = match ($order->status) {
                    'Selesai' => 'Selesai',
                    'Diproses' => $index === 0 ? 'Selesai' : 'Pending',
                    default => 'Pending',
                };

                $detail = OrderRadiologiDetail::create([
                    'order_radiologi_id' => $order->id,
                    'jenis_pemeriksaan_radiologi_id' => $jenis->id,
                    'status_pemeriksaan' => $statusDetail,
                    'created_at' => $order->created_at,
                    'updated_at' => $statusDetail === 'Selesai'
                        ? Carbon::parse($order->created_at)->addHours(rand(1, 4))
                        : $order->created_at,
                ]);

                // Buat hasil radiologi untuk detail yang selesai
                if ($statusDetail === 'Selesai') {
                    $perawat = $perawatList->random();

                    $tanggalPemeriksaan = $order->tanggal_pemeriksaan
                        ? Carbon::parse($order->tanggal_pemeriksaan)
                        : Carbon::parse($order->created_at)->addDay();

                    HasilRadiologi::updateOrCreate(
                        ['order_radiologi_detail_id' => $detail->id],
                        [
                            'perawat_id' => $perawat->id,
                            'foto_hasil_radiologi' => 'radiologi/dummy-radiologi-' . $detail->id . '.jpg',
                            'keterangan' => 'Hasil pemeriksaan radiologi untuk ' . ($jenis->nama_pemeriksaan ?? 'pemeriksaan') . ' - dummy seeder',
                            'tanggal_pemeriksaan' => $tanggalPemeriksaan->toDateString(),
                            'jam_pemeriksaan' => fake()->randomElement(['08:15:00', '09:30:00', '10:45:00', '13:20:00', '15:10:00']),
                            'created_at' => Carbon::parse($detail->created_at)->addMinutes(rand(15, 120)),
                            'updated_at' => Carbon::parse($detail->created_at)->addMinutes(rand(15, 120)),
                        ]
                    );
                }
            }
        }

        $this->command->info('Detail Order Radiologi + dummy Hasil Radiologi berhasil dibuat.');
    }
}
