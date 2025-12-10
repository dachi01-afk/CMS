<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\DokterPoli;
use App\Models\Poli;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DokterPoliSeeder extends Seeder
{
    /**
     * Jika true, relasi lama per dokter akan DIHAPUS lalu diisi ulang (fresh).
     * Jika false, seeder hanya akan MENAMBAH relasi sampai minimal 2 poli per dokter.
     */
    protected bool $fresh = false;

    public function run(): void
    {
        $dokters = Dokter::select('id')->get();
        $poliIds = Poli::pluck('id');

        if ($dokters->isEmpty() || $poliIds->isEmpty()) {
            $this->command?->warn('DokterPoliSeeder: Dokter/Poli belum tersedia.');
            return;
        }

        // Kalau polinya cuma 1, otomatis GAK MUNGKIN minimal 2
        if ($poliIds->count() < 2) {
            $this->command?->warn('DokterPoliSeeder: Jumlah poli < 2, tidak bisa set minimal 2 poli per dokter.');
        }

        $totalCreated = 0;

        DB::transaction(function () use ($dokters, $poliIds, &$totalCreated) {
            foreach ($dokters as $d) {

                // Mode fresh: hapus semua relasi dokter → poli dulu
                if ($this->fresh) {
                    DokterPoli::where('dokter_id', $d->id)->delete();
                    $currentPoliIds = collect();            // kosongkan
                } else {
                    // Mode idempotent: ambil relasi yang sudah ada
                    $currentPoliIds = DokterPoli::where('dokter_id', $d->id)
                        ->pluck('poli_id');
                }

                $currentCount = $currentPoliIds->count();

                // Kalau sudah punya 2 atau lebih poli → lewat, tidak perlu tambah
                if ($currentCount >= 2) {
                    continue;
                }

                // Target minimal poli per dokter
                $minPoliPerDokter = 2;

                // Berapa yang masih dibutuhkan supaya genap minimal 2
                $needed = $minPoliPerDokter - $currentCount;

                // Jangan sampai minta lebih banyak dari sisa poli yang tersedia
                $availablePoliIds = $poliIds->diff($currentPoliIds);
                $needed = min($needed, $availablePoliIds->count());

                // Kalau tidak ada poli tersisa, skip dokter ini
                if ($needed <= 0) {
                    continue;
                }

                // Pilih poli secara acak sebanyak yang dibutuhkan
                $chosen = $availablePoliIds->shuffle()->take($needed);

                foreach ($chosen as $poliId) {
                    $created = DokterPoli::firstOrCreate([
                        'dokter_id' => $d->id,
                        'poli_id'   => $poliId,
                    ]);

                    if ($created->wasRecentlyCreated) {
                        $totalCreated++;
                    }
                }
            }
        });

        $mode = $this->fresh ? 'fresh (reset & isi ulang minimal 2 poli)' : 'idempotent (tambah sampai minimal 2 poli)';
        $this->command?->info("DokterPoliSeeder [$mode]: semua dokter dipastikan punya minimal 2 poli (jika poli mencukupi). Tambahan baru: {$totalCreated}");
    }
}
