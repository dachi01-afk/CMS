<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\OrderLayanan;
use App\Models\OrderLayananDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderLayananDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataOrderLayanan = OrderLayanan::all();

        foreach ($dataOrderLayanan as $orderLayanan) {

            for ($i = 0; $i < 2; $i++) {

                $dataLayanan = Layanan::inRandomOrder()->first();

                $jumlah = rand(1, 5);

                OrderLayananDetail::updateOrCreate(
                    [
                        'order_layanan_id' => $orderLayanan->id,
                        'layanan_id' => $dataLayanan->id,
                    ],
                    [
                        'qty' => $jumlah,
                        'harga_satuan' => $dataLayanan->harga_setelah_diskon,
                        'total_harga_item' => $jumlah * $dataLayanan->harga_setelah_diskon,
                    ]
                );
            }

            // Hitung total seluruh detail milik OrderLayanan ini
            $totalBayar = OrderLayananDetail::where(
                'order_layanan_id',
                $orderLayanan->id
            )->sum('total_harga_item');

            // Simpan ke OrderLayanan
            $orderLayanan->update([
                'total_bayar' => $totalBayar,
            ]);
        }
    }
}
