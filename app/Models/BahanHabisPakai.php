<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BahanHabisPakai extends Model
{
    protected $table = 'bahan_habis_pakai';

    protected $guarded = [];

    public function brandFarmasi()
    {
        return $this->belongsTo(BrandFarmasi::class);
    }
    public function jenisBHP()
    {
        return $this->belongsTo(JenisObat::class, 'jenis_id');
    }
    public function satuanBHP()
    {
        return $this->belongsTo(SatuanObat::class, 'satuan_id');
    }
    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function depotBHP()
    {
        return $this->belongsToMany(Depot::class, 'depot_bhp', 'bahan_habis_pakai_id', 'depot_id')
            ->withPivot('stok_barang')->withTimestamps();
    }

    public function stokTransaksiDetail()
    {
        return $this->hasMany(StokTransaksiDetail::class, 'bahan_habis_pakai_id');
    }

    public function scopeGetData($query)
    {
        return $query->select(
            'id',
            'nama_barang',
            'kode',
            'harga_jual_umum_bhp',
            'stok_barang',
        )->with(['satuanBHP' => function ($q) {
            $q->select('id', 'nama_satuan_obat');
        }])->where('stok_barang', '>', 0)->orderBy('nama_barang', 'asc');
    }

    public static function simpanData($data)
    {
        return DB::transaction(function () use ($data) {
            $kode = $data['kode'] ?? self::buatKodeBHP();
            $totalStokInput = array_sum($data['stok_depot'] ?? []);

            // 1. Simpan ke tabel bahan_habis_pakai
            $dataBhp = self::create([
                'kode'                   => $kode,
                'brand_farmasi_id'       => $data['brand_farmasi_id'],
                'jenis_id'               => $data['jenis_id'],
                'satuan_id'              => $data['satuan_id'],
                'nama_barang'            => $data['nama_barang'],
                'tanggal_kadaluarsa_bhp' => $data['tanggal_kadaluarsa_bhp'],
                'no_batch'               => $data['no_batch'],
                'stok_barang'            => $totalStokInput,
                'dosis'                  => $data['dosis'],
                'harga_beli_satuan_bhp'  => $data['harga_beli_satuan_bhp'],
                'harga_jual_umum_bhp'    => $data['harga_jual_umum_bhp'],
                'harga_otc_bhp'          => $data['harga_otc_bhp'],
            ]);

            $syncData = [];
            $depotIds = $data['depot_id'] ?? [];
            $tipeDepotIds = $data['tipe_depot'] ?? [];
            $stokDepot = $data['stok_depot'] ?? [];

            // 2. Loop Pertama: Sync Pivot dan Update Tipe Depot
            foreach ($depotIds as $index => $depId) {
                if (empty($depId)) continue;

                // Masukkan ke array sync untuk tabel pivot 'depot_bhp'
                $syncData[$depId] = [
                    'stok_barang' => (int) ($stokDepot[$index] ?? 0)
                ];

                // Update Tipe Depot di tabel 'depot'
                $tipeId = $tipeDepotIds[$index] ?? null;
                if ($tipeId) {
                    Depot::where('id', $depId)->update(['tipe_depot_id' => $tipeId]);
                }
            }

            if (!empty($syncData)) {
                // Jalankan sync ke tabel pivot
                $dataBhp->depotBHP()->sync($syncData);

                // 3. Loop Kedua: Hitung Akumulasi Stok Seluruh Barang di Depot tersebut
                // Ini agar kolom 'jumlah_stok_depot' adalah total dari SEMUA BHP + Obat yang ada di depot itu
                foreach ($depotIds as $depId) {
                    if (empty($depId)) continue;

                    // Hitung total stok dari semua BHP yang ada di depot ini
                    $totalBhpDiDepot = DB::table('depot_bhp')
                        ->where('depot_id', $depId)
                        ->sum('stok_barang');

                    // Jika tabel Obat juga menggunakan depot yang sama, 
                    // kamu bisa menjumlahkannya juga di sini (Opsional tergantung strukturmu)
                    // $totalObatDiDepot = DB::table('depot_obat')->where('depot_id', $depId)->sum('stok_obat');

                    $totalSemuaBaru = $totalBhpDiDepot; // + $totalObatDiDepot;

                    Depot::where('id', $depId)->update([
                        'jumlah_stok_depot' => $totalSemuaBaru
                    ]);
                }
            }

            return $dataBhp;
        });
    }

    private static function buatKodeBHP()
    {
        $prefix = "BHP -" . now()->format('Ymd') . '-';
        $last = self::where('kode', 'like', $prefix . '%')->orderBy('kode', 'desc')->first();

        $number = $last ? ((int) substr($last->kode, -4)) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function scopeGetDataPenggunaanBhp($query, $filters = [])
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate   = $filters['end_date'] ?? null;
        $namaBhp   = $filters['nama_barang'] ?? null;

        $query->select([
            'bahan_habis_pakai.id',
            'bahan_habis_pakai.nama_barang',
            'bahan_habis_pakai.stok_barang',
            'bahan_habis_pakai.harga_jual_umum_bhp',
        ]);

        // Filter Nama Barang
        if (!empty($namaBhp)) {
            $query->where('bahan_habis_pakai.nama_barang', 'like', '%' . $namaBhp . '%');
        }

        $query->addSelect([
            // PERBAIKAN DI SINI: Bandingkan riwayat_penggunaan dengan bahan_habis_pakai
            'total_pakai_umum' => DB::table('riwayat_penggunaan_bahan_habis_pakai')
                ->selectRaw('SUM(jumlah_pemakaian)')
                // Pastikan foreign key di tabel riwayat (misal: bhp_id) sesuai dengan ID di tabel utama
                ->whereColumn('riwayat_penggunaan_bahan_habis_pakai.bahan_habis_pakai_id', 'bahan_habis_pakai.id')
                ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate)),
        ]);

        return $query;
    }
}
