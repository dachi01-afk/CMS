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
     * Jika true, relasi lama per dokter akan DIHAPUS lalu diisi ulang (fresh random).
     * Jika false, seeder hanya akan MENAMBAH relasi yang belum ada (idempotent).
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

        $poliCount = $poliIds->count();
        $totalCreated = 0;

        DB::transaction(function () use ($dokters, $poliIds, $poliCount, &$totalCreated) {
            foreach ($dokters as $d) {
                // Mode fresh: hapus relasi lama dokter ini, supaya hasil benar-benar random baru
                if ($this->fresh) {
                    DokterPoli::where('dokter_id', $d->id)->delete();
                }

                // Ambil 1–3 poli secara acak (maksimum sejumlah poli yang tersedia)
                $take = random_int(1, min(3, $poliCount));
                $chosen = $poliIds->shuffle()->take($take);

                foreach ($chosen as $poliId) {
                    // Pastikan unik (sesuai unique(['dokter_id','poli_id']))
                    $created = DokterPoli::firstOrCreate([
                        'dokter_id' => $d->id,
                        'poli_id'   => $poliId,
                    ]);

                    // Hitung hanya yang baru
                    if ($created->wasRecentlyCreated) {
                        $totalCreated++;
                    }
                }
            }
        });

        $mode = $this->fresh ? 'fresh (reset & isi ulang)' : 'idempotent (tambah yang belum ada)';
        $this->command?->info("DokterPoliSeeder [$mode]: relasi pivot dokter↔poli dipastikan ada. Tambahan baru: {$totalCreated}");
    }
}
