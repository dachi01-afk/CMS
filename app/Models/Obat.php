<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Obat extends Model
{
    protected $table = 'obat';

    protected $guarded = [];

    // Tambahkan ini agar sisa_hari selalu ikut di JSON
    protected $appends = ['sisa_hari'];

    public function resep()
    {
        return $this->belongsToMany(Resep::class, 'resep_obat', 'obat_id', 'resep_id')
            ->withPivot('jumlah', 'dosis', 'keterangan');
    }

    public function pasien()
    {
        return $this->belongsToMany(Pasien::class, 'penjualan_obat', 'obat_id', 'pasien_id')
            ->withPivot('kode_transaksi', 'jumlah', 'sub_total', 'tanggal_transaksi')
            ->withTimestamps();
    }

    public function brandFarmasi()
    {
        return $this->belongsTo(BrandFarmasi::class);
    }

    public function kategoriObat()
    {
        return $this->belongsTo(KategoriObat::class);
    }

    public function jenisObat()
    {
        return $this->belongsTo(JenisObat::class);
    }

    public function satuanObat()
    {
        return $this->belongsTo(SatuanObat::class);
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function depotObat()
    {
        return $this->belongsToMany(Depot::class, 'depot_obat', 'obat_id', 'depot_id')->withPivot('stok_obat')->withTimestamps();
    }

    public function stokTransaksiDetail()
    {
        return $this->hasMany(StokTransaksiDetail::class);
    }

    public function mutasiStokObatDetail()
    {
        return $this->hasMany(MutasiStokObatDetail::class);
    }

    public function batchObat()
    {
        return $this->hasMany(BatchObat::class);
    }

    public function scopeGetWarningKadaluarsa($query, $threshold = 90, $limit = 5)
    {
        $today = Carbon::today()->startOfDay();
        $nearDate = $today->copy()->addDays($threshold)->endOfDay();

        return $query->select('id', 'kode_obat', 'nama_obat', 'jumlah')
            ->addSelect([
                'tgl_exp_terdekat' => BatchObat::select('tanggal_kadaluarsa_obat')
                    ->whereColumn('obat_id', 'obat.id')
                    ->whereNotNull('tanggal_kadaluarsa_obat')
                    ->orderBy('tanggal_kadaluarsa_obat', 'asc')
                    ->limit(1)
            ])
            ->whereHas('batchObat', function ($q) use ($nearDate) {
                $q->whereNotNull('tanggal_kadaluarsa_obat')
                    ->whereDate('tanggal_kadaluarsa_obat', '<=', $nearDate);
            })
            ->with(['batchObat' => function ($q) use ($nearDate) {
                $q->whereDate('tanggal_kadaluarsa_obat', '<=', $nearDate)
                    ->orderBy('tanggal_kadaluarsa_obat', 'asc')
                    ->with('batchObatDepot'); // Tambahkan ini untuk mengambil data stok per depot
            }])
            ->orderBy('tgl_exp_terdekat', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($obat) use ($threshold) {
                $obat->tanggal_kadaluarsa_terdekat = $obat->tgl_exp_terdekat;
                $diff = $obat->sisa_hari;

                if ($diff < 0) {
                    $obat->status_key = 'expired';
                } elseif ($diff === 0) {
                    $obat->status_key = 'today';
                } else {
                    $obat->status_key = 'warning';
                }

                return $obat;
            });
    }

    public function scopeGetDataKadaluarsa($query, $threshold = 90)
    {
        $nearDate = Carbon::today()->addDays($threshold)->endOfDay();

        return $query->select('id', 'kode_obat', 'nama_obat', 'jumlah', 'satuan_obat_id')
            // Menambahkan kolom virtual 'tgl_exp_terdekat' untuk keperluan sorting
            ->addSelect([
                'tgl_exp_terdekat' => BatchObat::select('tanggal_kadaluarsa_obat')
                    ->whereColumn('obat_id', 'obat.id')
                    ->whereNotNull('tanggal_kadaluarsa_obat')
                    ->orderBy('tanggal_kadaluarsa_obat', 'asc')
                    ->limit(1)
            ])
            ->with(['satuanObat:id,nama_satuan_obat'])
            ->whereHas('batchObat', function ($q) use ($nearDate) {
                $q->whereNotNull('tanggal_kadaluarsa_obat')
                    ->whereDate('tanggal_kadaluarsa_obat', '<=', $nearDate);
            })
            // Urutkan berdasarkan kolom virtual tadi
            ->orderBy('tgl_exp_terdekat', 'asc');
    }

    // Accessor untuk sisa hari (Bisa dipakai di mana saja: $obat->sisa_hari)
    public function getSisaHariAttribute()
    {
        // Menggunakan atribut virtual dari subquery di scope
        if (!$this->tgl_exp_terdekat) return null;

        $today = \Carbon\Carbon::today()->startOfDay();
        $exp   = \Carbon\Carbon::parse($this->tgl_exp_terdekat)->startOfDay();

        return $today->diffInDays($exp, false);
    }

    // Tambahkan di App\Models\Obat.php

    public static function simpanData($data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Simpan ke tabel 'obat'
            $obat = self::create([
                'kode_obat'        => $data['kode_obat'],
                'brand_farmasi_id' => $data['brand_farmasi_id'],
                'kategori_obat_id' => $data['kategori_obat'],
                'jenis_obat_id'    => $data['jenis'],
                'satuan_obat_id'   => $data['satuan'],
                'nama_obat'        => $data['nama_obat'],
                'kandungan_obat'   => $data['kandungan'],
                'jumlah'           => $data['total_stok'],
                'dosis'            => $data['dosis'],
                'total_harga'      => $data['harga_beli'],
                'harga_jual_obat'  => $data['harga_jual'],
                'harga_otc_obat'   => $data['harga_otc'],
            ]);

            // 2. Simpan ke tabel 'batch_obat'
            $batch = $obat->batchObat()->create([
                'nama_batch'              => $data['nomor_batch'],
                'tanggal_kadaluarsa_obat' => $data['expired_date'],
            ]);

            // 3. Proses Depot dan Batch Depot
            foreach ($data['depot_id'] as $index => $depId) {
                $stokInput = (int) ($data['stok_depot'][$index] ?? 0);

                // Simpan stok per batch di depot tertentu (Tabel: batch_obat_depot)
                DB::table('batch_obat_depot')->insert([
                    'batch_obat_id' => $batch->id,
                    'depot_id'      => $depId,
                    'stok_obat'     => $stokInput,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // Update stok global di tabel pivot depot_obat (untuk relasi belongsToMany)
                $obat->depotObat()->syncWithoutDetaching([
                    $depId => ['stok_obat' => DB::raw("stok_obat + $stokInput")]
                ]);

                // Update akumulasi total di tabel 'depot'
                $totalStokDepot = DB::table('depot_obat')->where('depot_id', $depId)->sum('stok_obat');
                \App\Models\Depot::where('id', $depId)->update([
                    'jumlah_stok_depot' => $totalStokDepot
                ]);
            }

            return $obat;
        });
    }
}
