<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk transaction (opsional tapi bagus)

use App\Models\Depot;
use App\Models\Obat;
use App\Models\DepotObat;

class DepotObatSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Ambil semua data obat (ID dan Jumlah Globalnya)
        $obats = Obat::select('id', 'jumlah')->get();

        // 2. Ambil semua ID Depot
        $depotIds = Depot::pluck('id');

        // Cek validasi data
        if ($depotIds->isEmpty() || $obats->isEmpty()) {
            $this->command->warn('⚠️ Depot atau Obat kosong. Seeder dibatalkan.');
            return;
        }

        $this->command->info('Memulai distribusi stok obat ke depot...');

        // Gunakan Transaction agar lebih aman saat insert massal
        DB::transaction(function () use ($obats, $depotIds, $now) {

            foreach ($obats as $obat) {
                $stokGlobal = $obat->jumlah;

                // Jika stok global 0, tidak perlu didistribusikan (atau set 0 ke semua)
                if ($stokGlobal <= 0) {
                    continue;
                }

                // Pilih secara acak berapa banyak depot yang akan menampung obat ini
                // Minimal 1 depot, Maksimal sebanyak jumlah depot yang ada
                $jumlahDepotTerpilih = rand(1, $depotIds->count());

                // Ambil depot secara acak
                $depotTarget = $depotIds->random($jumlahDepotTerpilih);

                // Variabel untuk melacak sisa stok yang belum dibagikan
                $sisaStok = $stokGlobal;

                foreach ($depotTarget as $index => $depotId) {

                    // Cek apakah ini depot terakhir dalam iterasi untuk obat ini?
                    if ($index === $jumlahDepotTerpilih - 1) {
                        // Jika depot terakhir, ambil SEMUA sisa stok agar totalnya pas
                        $alokasiStok = $sisaStok;
                    } else {
                        // Jika bukan terakhir, ambil angka random dari sisa stok
                        // Kita sisakan minimal 1 untuk depot berikutnya (jika sisaStok cukup)
                        $maxAlokasi = $sisaStok - ($jumlahDepotTerpilih - 1 - $index);

                        // Validasi agar tidak minus
                        if ($maxAlokasi < 0) $maxAlokasi = 0;

                        // Ambil random, tapi usahakan jangan 0 jika stok masih banyak
                        $alokasiStok = rand(0, $maxAlokasi);
                    }

                    // Update sisa stok untuk iterasi berikutnya
                    $sisaStok -= $alokasiStok;

                    // Simpan ke database
                    DepotObat::updateOrCreate(
                        [
                            'depot_id' => $depotId,
                            'obat_id'  => $obat->id,
                        ],
                        [
                            'stok_obat'  => $alokasiStok,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        });

        $this->command->info('✅ Sukses mendistribusikan stok obat sesuai jumlah global.');
    }
}
