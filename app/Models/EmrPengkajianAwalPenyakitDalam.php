<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmrPengkajianAwalPenyakitDalam extends Model
{
    use HasFactory;

    protected $table = 'emr_pengkajian_awal_penyakit_dalam';

    protected $fillable = [
        'emr_id',
        'dokter_id',
        'created_by',
        'updated_by',
        'tanggal_pengkajian',
        'jam_pengkajian',
        'no_rm_snapshot',
        'nik_snapshot',
        'alergi',
        'sumber_data',
        'sumber_data_lainnya',
        'nyeri_ada',
        'skala_nyeri',
        'karakteristik_nyeri',
        'lokasi_nyeri',
        'durasi_nyeri',
        'frekuensi_nyeri',
        'tren_nyeri',
        'keluhan_utama',
        'riwayat_penyakit_sekarang',
        'riwayat_keluarga_hipertensi',
        'riwayat_keluarga_kencing_manis',
        'riwayat_keluarga_jantung',
        'riwayat_keluarga_asthma',
        'riwayat_penyakit_keluarga_lain',
        'riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan',
        'keadaan_umum',
        'status_gizi',
        'gcs_e',
        'gcs_m',
        'gcs_v',
        'tindakan_resusitasi',
        'berat_badan',
        'tinggi_badan',
        'tensi_sistolik',
        'tensi_diastolik',
        'suhu_axila',
        'suhu_rectal',
        'nadi',
        'respirasi',
        'saturasi_o2',
        'saturasi_o2_dengan',
        'pemeriksaan_kulit',
        'pemeriksaan_kepala_dan_leher',
        'pemeriksaan_telinga_hidung_mulut',
        'pemeriksaan_leher',
        'paru_inspeksi',
        'paru_palpasi',
        'paru_perkusi',
        'paru_auskultasi',
        'jantung_inspeksi',
        'jantung_palpasi',
        'jantung_perkusi',
        'jantung_auskultasi',
        'pemeriksaan_ekstremitas',
        'pemeriksaan_alat_kelamin_dan_rektum',
        'pemeriksaan_neurologis',
        'diagnosa_kerja',
        'diagnosa_diferensial',
        'terapi_tindakan',
        'rencana_kerja',
        'boleh_pulang',
        'tanggal_pulang',
        'jam_keluar',
        'kontrol_poliklinik',
        'nama_poli_kontrol',
        'tanggal_kontrol',
        'dirawat_di_ruang',
        'kelas_rawat',
        'tanggal_ttd_dokter',
        'jam_ttd_dokter',
        'nama_dokter_ttd',
        'status_form',
    ];

    protected $casts = [
        'tanggal_pengkajian' => 'date',
        'tanggal_pulang' => 'date',
        'tanggal_kontrol' => 'date',
        'tanggal_ttd_dokter' => 'date',

        'nyeri_ada' => 'boolean',
        'riwayat_keluarga_hipertensi' => 'boolean',
        'riwayat_keluarga_kencing_manis' => 'boolean',
        'riwayat_keluarga_jantung' => 'boolean',
        'riwayat_keluarga_asthma' => 'boolean',
        'tindakan_resusitasi' => 'boolean',
        'boleh_pulang' => 'boolean',
        'kontrol_poliklinik' => 'boolean',

        'skala_nyeri' => 'integer',
        'gcs_e' => 'integer',
        'gcs_m' => 'integer',
        'gcs_v' => 'integer',
        'nadi' => 'integer',
        'respirasi' => 'integer',
        'saturasi_o2' => 'integer',

        'berat_badan' => 'decimal:2',
        'tinggi_badan' => 'decimal:2',
        'suhu_axila' => 'decimal:1',
        'suhu_rectal' => 'decimal:1',
    ];

    public function emr()
    {
        return $this->belongsTo(Emr::class, 'emr_id');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function riwayat()
    {
        return $this->hasMany(EmrPengkajianAwalPenyakitDalamRiwayat::class, 'pengkajian_id')
            ->orderBy('urutan');
    }

    public function penunjang()
    {
        return $this->hasMany(EmrPengkajianAwalPenyakitDalamPenunjang::class, 'pengkajian_id')
            ->orderBy('urutan');
    }
}