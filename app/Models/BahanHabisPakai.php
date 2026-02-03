<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BahanHabisPakai extends Model
{
    protected $table = 'bahan_habis_pakai';

    protected $guarded = [];

    protected $appends = ['sisa_hari'];

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

    public function batchBahanHabisPakai()
    {
        return $this->hasMany(BatchBahanHabisPakai::class);
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

    public static function getDataById($bhpId)
    {
        return self::where('id', $bhpId)->with(['jenisBHP', 'satuanBHP', 'brandFarmasi', 'depotBHP.tipeDepot'])->firstOrFail();
    }

    public static function simpanData($data)
    {
        return DB::transaction(function () use ($data) {
            $kode = $data['kode'] ?? self::buatKodeBHP();
            $totalStokInput = array_sum($data['stok_depot'] ?? []);

            // 1. Simpan ke tabel master
            $dataBhp = self::create([
                'kode'                   => $kode,
                'brand_farmasi_id'       => $data['brand_farmasi_id'],
                'jenis_id'               => $data['jenis_id'],
                'satuan_id'              => $data['satuan_id'],
                'nama_barang'            => $data['nama_barang'],
                'stok_barang'            => $totalStokInput,
                'dosis'                  => $data['dosis'],
                'harga_beli_satuan_bhp'  => $data['harga_beli_satuan_bhp'],
                'harga_jual_umum_bhp'    => $data['harga_jual_umum_bhp'],
                'harga_otc_bhp'          => $data['harga_otc_bhp'],
            ]);

            // 2. Simpan ke tabel Batch (per barang)
            $dataBatchBhp = BatchBahanHabisPakai::createData($dataBhp->id, $data);

            // 3. Siapkan data untuk Sync Pivot Depot
            $syncData = [];
            $depotIds = $data['depot_id'] ?? [];
            $tipeDepotIds = $data['tipe_depot'] ?? [];
            $stokDepot = $data['stok_depot'] ?? [];

            foreach ($depotIds as $index => $depId) {
                if (empty($depId)) continue;

                $stokBaris = (int) ($stokDepot[$index] ?? 0);
                $syncData[$depId] = ['stok_barang' => $stokBaris];

                // Update tipe depot (jika ada perubahan)
                if (isset($tipeDepotIds[$index])) {
                    Depot::where('id', $depId)->update(['tipe_depot_id' => $tipeDepotIds[$index]]);
                }
            }

            // 4. Sync ke tabel depot_bhp (Stok per Barang per Depot)
            $dataBhp->depotBHP()->sync($syncData);

            // 5. Update Total Akumulasi di Tabel Depot (Master Depot)
            foreach ($depotIds as $depId) {
                $totalSemuaDiDepot = DB::table('depot_bhp')->where('depot_id', $depId)->sum('stok_barang');
                // Jika ada tabel depot_obat, jumlahkan juga di sini agar balance
                Depot::where('id', $depId)->update(['jumlah_stok_depot' => $totalSemuaDiDepot]);
            }

            // 6. Simpan ke tabel Batch Depot (Stok per Batch per Depot)
            // Pastikan di dalam class ini kamu melakukan looping terhadap $data['depot_id']
            BatchBahanHabisPakaiDepot::createData($dataBatchBhp->id, $data);

            return $dataBhp; // Kembalikan objek BHP agar bisa di-load di controller
        });
    }

    public function updateBHP(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Karena ini bukan static, kita gunakan $this untuk merujuk ke record saat ini
            $this->update([
                'kode'                  => $data['kode'] ?? $this->kode,
                'brand_farmasi_id'      => $data['brand_farmasi_id'],
                'jenis_id'              => $data['jenis_id'],
                'satuan_id'             => $data['satuan_id'],
                'nama_barang'           => $data['nama_barang'],
                'dosis'                 => $data['dosis'],
                'harga_beli_satuan_bhp' => $data['harga_beli_satuan_bhp'],
                'harga_jual_umum_bhp'   => $data['harga_jual_umum_bhp'],
                'harga_otc_bhp'         => $data['harga_otc_bhp'],
                // Stok tetap terjaga karena tidak dimasukkan di sini
            ]);

            return $this; // Kembalikan objeknya sendiri
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

    public function scopeGetWarningKadaluarsa($query, $threshold = 90, $limit = 5)
    {
        $today = Carbon::today()->startOfDay();
        $nearDate = $today->copy()->addDays($threshold)->endOfDay();

        return $query->select('id', 'kode', 'nama_barang', 'stok_barang')
            ->addSelect([
                'tgl_exp_terdekat' => BatchBahanHabisPakai::select('tanggal_kadaluarsa_bahan_habis_pakai')
                    ->whereColumn('bahan_habis_pakai_id', 'bahan_habis_pakai.id')
                    ->whereNotNull('tanggal_kadaluarsa_bahan_habis_pakai')
                    ->orderBy('tanggal_kadaluarsa_bahan_habis_pakai', 'asc')
                    ->limit(1)
            ])
            ->whereHas('batchBahanHabisPakai', function ($q) use ($nearDate) {
                $q->whereNotNull('tanggal_kadaluarsa_bahan_habis_pakai')
                    ->whereDate('tanggal_kadaluarsa_bahan_habis_pakai', '<=', $nearDate);
            })
            ->with(['batchBahanHabisPakai' => function ($q) use ($nearDate) {
                $q->whereDate('tanggal_kadaluarsa_bahan_habis_pakai', '<=', $nearDate)
                    ->orderBy('tanggal_kadaluarsa_bahan_habis_pakai', 'asc')
                    ->with('batchBahanHabisPakaiDepot'); // Tambahkan ini untuk mengambil data stok per depot
            }])
            ->orderBy('tgl_exp_terdekat', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($bhp) use ($threshold) {
                $bhp->tanggal_kadaluarsa_terdekat = $bhp->tgl_exp_terdekat;
                $diff = $bhp->sisa_hari;

                if ($diff < 0) {
                    $bhp->status_key = 'expired';
                } elseif ($diff === 0) {
                    $bhp->status_key = 'today';
                } else {
                    $bhp->status_key = 'warning';
                }

                return $bhp;
            });
    }

    public function scopeGetDataKadaluarsa($query, $threshold = 90)
    {
        $nearDate = Carbon::today()->addDays($threshold)->endOfDay();

        return $query->select('id', 'kode', 'nama_barang', 'stok_barang', 'satuan_id')
            // Menambahkan kolom virtual 'tgl_exp_terdekat' untuk keperluan sorting
            ->addSelect([
                'tgl_exp_terdekat' => BatchBahanHabisPakai::select('tanggal_kadaluarsa_bahan_habis_pakai')
                    ->whereColumn('bahan_habis_pakai_id', 'bahan_habis_pakai.id')
                    ->whereNotNull('tanggal_kadaluarsa_bahan_habis_pakai')
                    ->orderBy('tanggal_kadaluarsa_bahan_habis_pakai', 'asc')
                    ->limit(1)
            ])
            ->with(['satuanBHP:id,nama_satuan_obat'])
            ->whereHas('batchBahanHabisPakai', function ($q) use ($nearDate) {
                $q->whereNotNull('tanggal_kadaluarsa_bahan_habis_pakai')
                    ->whereDate('tanggal_kadaluarsa_bahan_habis_pakai', '<=', $nearDate);
            })
            // Urutkan berdasarkan kolom virtual tadi
            ->orderBy('tgl_exp_terdekat', 'asc');
    }

    // Accessor untuk sisa hari (Bisa dipakai di mana saja: $obat->sisa_hari)
    public function getSisaHariAttribute()
    {
        // Menggunakan atribut virtual dari subquery di scope
        if (!$this->tgl_exp_terdekat) return null;

        $today = Carbon::today()->startOfDay();
        $exp   = Carbon::parse($this->tgl_exp_terdekat)->startOfDay();

        return $today->diffInDays($exp, false);
    }
}
