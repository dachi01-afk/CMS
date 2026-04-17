<?php

namespace App\Http\Controllers\Api\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\EMR;
use App\Models\EmrPengkajianAwalPenyakitDalam;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PengkajianAwalPenyakitDalamController extends Controller
{
    public function index(Request $request)
    {
        $query = EmrPengkajianAwalPenyakitDalam::with([
            'emr.pasien',
            'emr.dokter',
            'emr.poli',
            'dokter',
            'riwayat',
            'penunjang',
        ])->latest();

        if ($request->filled('emr_id')) {
            $query->where('emr_id', $request->emr_id);
        }

        if ($request->filled('dokter_id')) {
            $query->where('dokter_id', $request->dokter_id);
        }

        if ($request->filled('status_form')) {
            $query->where('status_form', $request->status_form);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pengkajian awal penyakit dalam berhasil diambil',
            'data' => $query->paginate($request->get('per_page', 10)),
        ]);
    }

    public function show($id)
    {
        $data = EmrPengkajianAwalPenyakitDalam::with([
            'emr.pasien',
            'emr.dokter',
            'emr.poli',
            'emr.perawat',
            'dokter',
            'riwayat',
            'penunjang',
        ])->find($id);

        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengkajian tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pengkajian awal penyakit dalam',
            'data' => $data,
        ]);
    }

    public function showByEmr($emrId)
    {
        $pengkajian = EmrPengkajianAwalPenyakitDalam::with([
            'emr.pasien',
            'emr.dokter',
            'emr.poli',
            'emr.perawat',
            'dokter',
            'riwayat',
            'penunjang',
        ])->where('emr_id', $emrId)->first();

        $emr = Emr::with([
            'pasien',
            'dokter',
            'poli',
            'perawat',
        ])->find($emrId);

        if (! $emr) {
            return response()->json([
                'success' => false,
                'message' => 'EMR tidak ditemukan',
            ], 404);
        }

        $now = now();

        $tekananDarah = trim((string) ($emr->tekanan_darah ?? ''));
        $tensiSistolikFromEmr = null;
        $tensiDiastolikFromEmr = null;

        if ($tekananDarah !== '') {
            if (preg_match('/^\s*(\d{2,3})\s*\/\s*(\d{2,3})\s*$/', $tekananDarah, $m)) {
                $tensiSistolikFromEmr = $m[1];
                $tensiDiastolikFromEmr = $m[2];
            } else {
                $parts = preg_split('/[\/\-]/', $tekananDarah);
                if (is_array($parts) && count($parts) >= 2) {
                    $tensiSistolikFromEmr = trim($parts[0]);
                    $tensiDiastolikFromEmr = trim($parts[1]);
                }
            }
        }

        $pick = function ($primary, $fallback = null) {
            if ($primary !== null && $primary !== '') {
                return $primary;
            }

            return $fallback;
        };

        $data = [
            'id' => $pengkajian->id ?? null,
            'emr_id' => $emr->id,
            'dokter_id' => $pick($pengkajian->dokter_id ?? null, $emr->dokter_id),

            'tanggal_pengkajian' => $pick($pengkajian->tanggal_pengkajian ?? null, $now->toDateString()),
            'jam_pengkajian' => $pick($pengkajian->jam_pengkajian ?? null, $now->format('H:i')),

            'no_rm_snapshot' => $pick($pengkajian->no_rm_snapshot ?? null, $emr->pasien->no_emr ?? null),
            'nik_snapshot' => $pick($pengkajian->nik_snapshot ?? null, $emr->pasien->nik ?? null),

            'alergi' => $pick($pengkajian->alergi ?? null, $emr->pasien->alergi ?? null),
            'sumber_data' => $pick($pengkajian->sumber_data ?? null, 'Pasien'),
            'sumber_data_lainnya' => $pengkajian->sumber_data_lainnya ?? null,

            'nyeri_ada' => $pengkajian->nyeri_ada ?? null,
            'skala_nyeri' => $pengkajian->skala_nyeri ?? null,
            'karakteristik_nyeri' => $pengkajian->karakteristik_nyeri ?? null,
            'lokasi_nyeri' => $pengkajian->lokasi_nyeri ?? null,
            'durasi_nyeri' => $pengkajian->durasi_nyeri ?? null,
            'frekuensi_nyeri' => $pengkajian->frekuensi_nyeri ?? null,
            'tren_nyeri' => $pengkajian->tren_nyeri ?? null,

            'keluhan_utama' => $pick($pengkajian->keluhan_utama ?? null, $emr->keluhan_utama),
            'riwayat_penyakit_sekarang' => $pick($pengkajian->riwayat_penyakit_sekarang ?? null, $emr->riwayat_penyakit_dahulu),

            'riwayat_keluarga_hipertensi' => $pengkajian->riwayat_keluarga_hipertensi ?? false,
            'riwayat_keluarga_kencing_manis' => $pengkajian->riwayat_keluarga_kencing_manis ?? false,
            'riwayat_keluarga_jantung' => $pengkajian->riwayat_keluarga_jantung ?? false,
            'riwayat_keluarga_asthma' => $pengkajian->riwayat_keluarga_asthma ?? false,
            'riwayat_penyakit_keluarga_lain' => $pick($pengkajian->riwayat_penyakit_keluarga_lain ?? null, $emr->riwayat_penyakit_keluarga),

            'riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan' => $pengkajian->riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan ?? null,

            'keadaan_umum' => $pengkajian->keadaan_umum ?? null,
            'status_gizi' => $pengkajian->status_gizi ?? null,

            'gcs_e' => $pengkajian->gcs_e ?? null,
            'gcs_m' => $pengkajian->gcs_m ?? null,
            'gcs_v' => $pengkajian->gcs_v ?? null,
            'tindakan_resusitasi' => $pengkajian->tindakan_resusitasi ?? null,

            'berat_badan' => $pick($pengkajian->berat_badan ?? null, $emr->berat_badan ?? null),
            'tinggi_badan' => $pick($pengkajian->tinggi_badan ?? null, $emr->tinggi_badan ?? null),

            'tensi_sistolik' => $pick($pengkajian->tensi_sistolik ?? null, $tensiSistolikFromEmr),
            'tensi_diastolik' => $pick($pengkajian->tensi_diastolik ?? null, $tensiDiastolikFromEmr),
            'suhu_axila' => $pick($pengkajian->suhu_axila ?? null, $emr->suhu_tubuh),
            'suhu_rectal' => $pengkajian->suhu_rectal ?? null,
            'nadi' => $pick($pengkajian->nadi ?? null, $emr->nadi),
            'respirasi' => $pick($pengkajian->respirasi ?? null, $emr->pernapasan),
            'saturasi_o2' => $pick($pengkajian->saturasi_o2 ?? null, $emr->saturasi_oksigen),
            'saturasi_o2_dengan' => $pengkajian->saturasi_o2_dengan ?? null,

            'pemeriksaan_kulit' => $pengkajian->pemeriksaan_kulit ?? null,
            'pemeriksaan_kepala_dan_leher' => $pengkajian->pemeriksaan_kepala_dan_leher ?? null,
            'pemeriksaan_telinga_hidung_mulut' => $pengkajian->pemeriksaan_telinga_hidung_mulut ?? null,
            'pemeriksaan_leher' => $pengkajian->pemeriksaan_leher ?? null,

            'paru_inspeksi' => $pengkajian->paru_inspeksi ?? null,
            'paru_palpasi' => $pengkajian->paru_palpasi ?? null,
            'paru_perkusi' => $pengkajian->paru_perkusi ?? null,
            'paru_auskultasi' => $pengkajian->paru_auskultasi ?? null,

            'jantung_inspeksi' => $pengkajian->jantung_inspeksi ?? null,
            'jantung_palpasi' => $pengkajian->jantung_palpasi ?? null,
            'jantung_perkusi' => $pengkajian->jantung_perkusi ?? null,
            'jantung_auskultasi' => $pengkajian->jantung_auskultasi ?? null,

            'pemeriksaan_ekstremitas' => $pengkajian->pemeriksaan_ekstremitas ?? null,
            'pemeriksaan_alat_kelamin_dan_rektum' => $pengkajian->pemeriksaan_alat_kelamin_dan_rektum ?? null,
            'pemeriksaan_neurologis' => $pengkajian->pemeriksaan_neurologis ?? null,

            'diagnosa_kerja' => $pengkajian->diagnosa_kerja ?? null,
            'diagnosa_diferensial' => $pengkajian->diagnosa_diferensial ?? null,
            'terapi_tindakan' => $pengkajian->terapi_tindakan ?? null,
            'rencana_kerja' => $pengkajian->rencana_kerja ?? null,

            'boleh_pulang' => $pengkajian->boleh_pulang ?? null,
            'tanggal_pulang' => $pengkajian->tanggal_pulang ?? null,
            'jam_keluar' => $pengkajian->jam_keluar ?? null,

            'kontrol_poliklinik' => $pengkajian->kontrol_poliklinik ?? null,
            'nama_poli_kontrol' => $pengkajian->nama_poli_kontrol ?? null,
            'tanggal_kontrol' => $pengkajian->tanggal_kontrol ?? null,

            'dirawat_di_ruang' => $pengkajian->dirawat_di_ruang ?? null,
            'kelas_rawat' => $pengkajian->kelas_rawat ?? null,

            'tanggal_ttd_dokter' => $pengkajian->tanggal_ttd_dokter ?? null,
            'jam_ttd_dokter' => $pengkajian->jam_ttd_dokter ?? null,
            'nama_dokter_ttd' => $pick($pengkajian->nama_dokter_ttd ?? null, $emr->dokter->nama_dokter ?? null),

            'status_form' => $pick($pengkajian->status_form ?? null, 'draft'),

            'riwayat' => $pengkajian ? $pengkajian->riwayat->values() : [],
            'penunjang' => $pengkajian ? $pengkajian->penunjang->values() : [],

            'emr' => [
                'id' => $emr->id,
                'keluhan_utama' => $emr->keluhan_utama,
                'riwayat_penyakit_dahulu' => $emr->riwayat_penyakit_dahulu,
                'riwayat_penyakit_keluarga' => $emr->riwayat_penyakit_keluarga,
                'tekanan_darah' => $emr->tekanan_darah,
                'suhu_tubuh' => $emr->suhu_tubuh,
                'nadi' => $emr->nadi,
                'pernapasan' => $emr->pernapasan,
                'saturasi_oksigen' => $emr->saturasi_oksigen,
                'berat_badan' => $emr->berat_badan ?? null,
                'tinggi_badan' => $emr->tinggi_badan ?? null,
                'perawat' => $emr->perawat ? [
                    'id' => $emr->perawat->id,
                    'nama_perawat' => $emr->perawat->nama_perawat,
                ] : null,
            ],

            'pasien' => $emr->pasien ? [
                'id' => $emr->pasien->id,
                'nama_pasien' => $emr->pasien->nama_pasien,
                'no_emr' => $emr->pasien->no_emr ?? null,
                'nik' => $emr->pasien->nik ?? null,
            ] : null,

            'dokter' => $emr->dokter ? [
                'id' => $emr->dokter->id,
                'nama_dokter' => $emr->dokter->nama_dokter,
            ] : null,

            'poli' => $emr->poli ? [
                'id' => $emr->poli->id,
                'nama_poli' => $emr->poli->nama_poli,
            ] : null,
        ];

        return response()->json([
            'success' => true,
            'message' => $pengkajian
                ? 'Detail pengkajian berdasarkan EMR'
                : 'Prefill pengkajian berdasarkan EMR perawat',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $emr = Emr::find($validated['emr_id']);
        if (! $emr) {
            return response()->json([
                'success' => false,
                'message' => 'EMR tidak ditemukan',
            ], 404);
        }

        $exists = EmrPengkajianAwalPenyakitDalam::where('emr_id', $validated['emr_id'])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Pengkajian awal penyakit dalam untuk EMR ini sudah ada',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $pengkajian = EmrPengkajianAwalPenyakitDalam::create([
                'emr_id' => $validated['emr_id'],
                'dokter_id' => $validated['dokter_id'] ?? $emr->dokter_id,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),

                'tanggal_pengkajian' => $validated['tanggal_pengkajian'] ?? null,
                'jam_pengkajian' => $validated['jam_pengkajian'] ?? null,
                'no_rm_snapshot' => $validated['no_rm_snapshot'] ?? ($emr->pasien->no_emr ?? null),
                'nik_snapshot' => $validated['nik_snapshot'] ?? ($emr->pasien->nik ?? null),

                'alergi' => $validated['alergi'] ?? null,
                'sumber_data' => $validated['sumber_data'] ?? null,
                'sumber_data_lainnya' => $validated['sumber_data_lainnya'] ?? null,

                'nyeri_ada' => $validated['nyeri_ada'] ?? null,
                'skala_nyeri' => $validated['skala_nyeri'] ?? null,
                'karakteristik_nyeri' => $validated['karakteristik_nyeri'] ?? null,
                'lokasi_nyeri' => $validated['lokasi_nyeri'] ?? null,
                'durasi_nyeri' => $validated['durasi_nyeri'] ?? null,
                'frekuensi_nyeri' => $validated['frekuensi_nyeri'] ?? null,
                'tren_nyeri' => $validated['tren_nyeri'] ?? null,

                'keluhan_utama' => $validated['keluhan_utama'] ?? null,
                'riwayat_penyakit_sekarang' => $validated['riwayat_penyakit_sekarang'] ?? null,

                'riwayat_keluarga_hipertensi' => $validated['riwayat_keluarga_hipertensi'] ?? false,
                'riwayat_keluarga_kencing_manis' => $validated['riwayat_keluarga_kencing_manis'] ?? false,
                'riwayat_keluarga_jantung' => $validated['riwayat_keluarga_jantung'] ?? false,
                'riwayat_keluarga_asthma' => $validated['riwayat_keluarga_asthma'] ?? false,
                'riwayat_penyakit_keluarga_lain' => $validated['riwayat_penyakit_keluarga_lain'] ?? null,

                'riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan' => $validated['riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan'] ?? null,

                'keadaan_umum' => $validated['keadaan_umum'] ?? null,
                'status_gizi' => $validated['status_gizi'] ?? null,

                'gcs_e' => $validated['gcs_e'] ?? null,
                'gcs_m' => $validated['gcs_m'] ?? null,
                'gcs_v' => $validated['gcs_v'] ?? null,
                'tindakan_resusitasi' => $validated['tindakan_resusitasi'] ?? null,

                'berat_badan' => $validated['berat_badan'] ?? null,
                'tinggi_badan' => $validated['tinggi_badan'] ?? null,

                'tensi_sistolik' => $validated['tensi_sistolik'] ?? null,
                'tensi_diastolik' => $validated['tensi_diastolik'] ?? null,
                'suhu_axila' => $validated['suhu_axila'] ?? null,
                'suhu_rectal' => $validated['suhu_rectal'] ?? null,
                'nadi' => $validated['nadi'] ?? null,
                'respirasi' => $validated['respirasi'] ?? null,
                'saturasi_o2' => $validated['saturasi_o2'] ?? null,
                'saturasi_o2_dengan' => $validated['saturasi_o2_dengan'] ?? null,

                'pemeriksaan_kulit' => $validated['pemeriksaan_kulit'] ?? null,
                'pemeriksaan_kepala_dan_leher' => $validated['pemeriksaan_kepala_dan_leher'] ?? null,
                'pemeriksaan_telinga_hidung_mulut' => $validated['pemeriksaan_telinga_hidung_mulut'] ?? null,
                'pemeriksaan_leher' => $validated['pemeriksaan_leher'] ?? null,

                'paru_inspeksi' => $validated['paru_inspeksi'] ?? null,
                'paru_palpasi' => $validated['paru_palpasi'] ?? null,
                'paru_perkusi' => $validated['paru_perkusi'] ?? null,
                'paru_auskultasi' => $validated['paru_auskultasi'] ?? null,

                'jantung_inspeksi' => $validated['jantung_inspeksi'] ?? null,
                'jantung_palpasi' => $validated['jantung_palpasi'] ?? null,
                'jantung_perkusi' => $validated['jantung_perkusi'] ?? null,
                'jantung_auskultasi' => $validated['jantung_auskultasi'] ?? null,

                'pemeriksaan_ekstremitas' => $validated['pemeriksaan_ekstremitas'] ?? null,
                'pemeriksaan_alat_kelamin_dan_rektum' => $validated['pemeriksaan_alat_kelamin_dan_rektum'] ?? null,
                'pemeriksaan_neurologis' => $validated['pemeriksaan_neurologis'] ?? null,

                'diagnosa_kerja' => $validated['diagnosa_kerja'] ?? null,
                'diagnosa_diferensial' => $validated['diagnosa_diferensial'] ?? null,
                'terapi_tindakan' => $validated['terapi_tindakan'] ?? null,
                'rencana_kerja' => $validated['rencana_kerja'] ?? null,

                'boleh_pulang' => $validated['boleh_pulang'] ?? null,
                'tanggal_pulang' => $validated['tanggal_pulang'] ?? null,
                'jam_keluar' => $validated['jam_keluar'] ?? null,

                'kontrol_poliklinik' => $validated['kontrol_poliklinik'] ?? null,
                'nama_poli_kontrol' => $validated['nama_poli_kontrol'] ?? null,
                'tanggal_kontrol' => $validated['tanggal_kontrol'] ?? null,

                'dirawat_di_ruang' => $validated['dirawat_di_ruang'] ?? null,
                'kelas_rawat' => $validated['kelas_rawat'] ?? null,

                'tanggal_ttd_dokter' => $validated['tanggal_ttd_dokter'] ?? null,
                'jam_ttd_dokter' => $validated['jam_ttd_dokter'] ?? null,
                'nama_dokter_ttd' => $validated['nama_dokter_ttd'] ?? null,

                'status_form' => $validated['status_form'] ?? 'draft',
            ]);

            foreach ($validated['riwayat'] ?? [] as $index => $row) {
                $pengkajian->riwayat()->create([
                    'riwayat_penyakit' => $row['riwayat_penyakit'] ?? null,
                    'tahun' => $row['tahun'] ?? null,
                    'riwayat_pengobatan' => $row['riwayat_pengobatan'] ?? null,
                    'urutan' => $index + 1,
                ]);
            }

            foreach ($validated['penunjang'] ?? [] as $index => $row) {
                $pengkajian->penunjang()->create([
                    'jenis_penunjang' => $row['jenis_penunjang'] ?? null,
                    'jenis_penunjang_lainnya' => $row['jenis_penunjang_lainnya'] ?? null,
                    'hasil_penunjang' => $row['hasil_penunjang'] ?? null,
                    'tanggal_penunjang' => $row['tanggal_penunjang'] ?? null,
                    'urutan' => $index + 1,
                ]);
            }

            $kunjungan = Kunjungan::findOrFail($emr->kunjungan_id);
            $dokter = $this->dokterLogin();

            $resepId = null;
            $orderLabId = null;
            $orderRadiologiId = null;
            $pembayaran = null;

            if (($validated['status_form'] ?? 'draft') === 'final') {
                $resepId = $this->syncResepPenyakitDalam($emr, $kunjungan, $validated);
                $this->syncLayananPenyakitDalam($kunjungan, $validated);
                $orderLabId = $this->syncOrderLabPenyakitDalam($dokter, $kunjungan, $validated);
                $orderRadiologiId = $this->syncOrderRadiologiPenyakitDalam($dokter, $kunjungan, $validated);

                $kunjungan->update([
                    'status' => 'Payment',
                ]);

                $pembayaran = $this->rebuildPembayaranFinal(
                    $emr,
                    $kunjungan,
                    $resepId,
                    $orderLabId,
                    $orderRadiologiId
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengkajian awal penyakit dalam berhasil disimpan',
                'data' => [
                    'pengkajian' => $pengkajian->load([
                        'emr.pasien',
                        'emr.dokter',
                        'emr.poli',
                        'dokter',
                        'riwayat',
                        'penunjang',
                    ]),
                    'order_lab_id' => $orderLabId,
                    'order_radiologi_id' => $orderRadiologiId,
                    'pembayaran' => $pembayaran,
                ],
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengkajian awal penyakit dalam',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $pengkajian = EmrPengkajianAwalPenyakitDalam::find($id);

        if (! $pengkajian) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengkajian tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate($this->rules($id));

        DB::beginTransaction();
        try {
            $pengkajian->update([
                'emr_id' => $validated['emr_id'],
                'dokter_id' => $validated['dokter_id'] ?? $pengkajian->dokter_id,
                'updated_by' => auth()->id(),

                'tanggal_pengkajian' => $validated['tanggal_pengkajian'] ?? null,
                'jam_pengkajian' => $validated['jam_pengkajian'] ?? null,
                'no_rm_snapshot' => $validated['no_rm_snapshot'] ?? null,
                'nik_snapshot' => $validated['nik_snapshot'] ?? null,

                'alergi' => $validated['alergi'] ?? null,
                'sumber_data' => $validated['sumber_data'] ?? null,
                'sumber_data_lainnya' => $validated['sumber_data_lainnya'] ?? null,

                'nyeri_ada' => $validated['nyeri_ada'] ?? null,
                'skala_nyeri' => $validated['skala_nyeri'] ?? null,
                'karakteristik_nyeri' => $validated['karakteristik_nyeri'] ?? null,
                'lokasi_nyeri' => $validated['lokasi_nyeri'] ?? null,
                'durasi_nyeri' => $validated['durasi_nyeri'] ?? null,
                'frekuensi_nyeri' => $validated['frekuensi_nyeri'] ?? null,
                'tren_nyeri' => $validated['tren_nyeri'] ?? null,

                'keluhan_utama' => $validated['keluhan_utama'] ?? null,
                'riwayat_penyakit_sekarang' => $validated['riwayat_penyakit_sekarang'] ?? null,

                'riwayat_keluarga_hipertensi' => $validated['riwayat_keluarga_hipertensi'] ?? false,
                'riwayat_keluarga_kencing_manis' => $validated['riwayat_keluarga_kencing_manis'] ?? false,
                'riwayat_keluarga_jantung' => $validated['riwayat_keluarga_jantung'] ?? false,
                'riwayat_keluarga_asthma' => $validated['riwayat_keluarga_asthma'] ?? false,
                'riwayat_penyakit_keluarga_lain' => $validated['riwayat_penyakit_keluarga_lain'] ?? null,

                'riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan' => $validated['riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan'] ?? null,

                'keadaan_umum' => $validated['keadaan_umum'] ?? null,
                'status_gizi' => $validated['status_gizi'] ?? null,

                'gcs_e' => $validated['gcs_e'] ?? null,
                'gcs_m' => $validated['gcs_m'] ?? null,
                'gcs_v' => $validated['gcs_v'] ?? null,
                'tindakan_resusitasi' => $validated['tindakan_resusitasi'] ?? null,

                'berat_badan' => $validated['berat_badan'] ?? null,
                'tinggi_badan' => $validated['tinggi_badan'] ?? null,

                'tensi_sistolik' => $validated['tensi_sistolik'] ?? null,
                'tensi_diastolik' => $validated['tensi_diastolik'] ?? null,
                'suhu_axila' => $validated['suhu_axila'] ?? null,
                'suhu_rectal' => $validated['suhu_rectal'] ?? null,
                'nadi' => $validated['nadi'] ?? null,
                'respirasi' => $validated['respirasi'] ?? null,
                'saturasi_o2' => $validated['saturasi_o2'] ?? null,
                'saturasi_o2_dengan' => $validated['saturasi_o2_dengan'] ?? null,

                'pemeriksaan_kulit' => $validated['pemeriksaan_kulit'] ?? null,
                'pemeriksaan_kepala_dan_leher' => $validated['pemeriksaan_kepala_dan_leher'] ?? null,
                'pemeriksaan_telinga_hidung_mulut' => $validated['pemeriksaan_telinga_hidung_mulut'] ?? null,
                'pemeriksaan_leher' => $validated['pemeriksaan_leher'] ?? null,

                'paru_inspeksi' => $validated['paru_inspeksi'] ?? null,
                'paru_palpasi' => $validated['paru_palpasi'] ?? null,
                'paru_perkusi' => $validated['paru_perkusi'] ?? null,
                'paru_auskultasi' => $validated['paru_auskultasi'] ?? null,

                'jantung_inspeksi' => $validated['jantung_inspeksi'] ?? null,
                'jantung_palpasi' => $validated['jantung_palpasi'] ?? null,
                'jantung_perkusi' => $validated['jantung_perkusi'] ?? null,
                'jantung_auskultasi' => $validated['jantung_auskultasi'] ?? null,

                'pemeriksaan_ekstremitas' => $validated['pemeriksaan_ekstremitas'] ?? null,
                'pemeriksaan_alat_kelamin_dan_rektum' => $validated['pemeriksaan_alat_kelamin_dan_rektum'] ?? null,
                'pemeriksaan_neurologis' => $validated['pemeriksaan_neurologis'] ?? null,

                'diagnosa_kerja' => $validated['diagnosa_kerja'] ?? null,
                'diagnosa_diferensial' => $validated['diagnosa_diferensial'] ?? null,
                'terapi_tindakan' => $validated['terapi_tindakan'] ?? null,
                'rencana_kerja' => $validated['rencana_kerja'] ?? null,

                'boleh_pulang' => $validated['boleh_pulang'] ?? null,
                'tanggal_pulang' => $validated['tanggal_pulang'] ?? null,
                'jam_keluar' => $validated['jam_keluar'] ?? null,

                'kontrol_poliklinik' => $validated['kontrol_poliklinik'] ?? null,
                'nama_poli_kontrol' => $validated['nama_poli_kontrol'] ?? null,
                'tanggal_kontrol' => $validated['tanggal_kontrol'] ?? null,

                'dirawat_di_ruang' => $validated['dirawat_di_ruang'] ?? null,
                'kelas_rawat' => $validated['kelas_rawat'] ?? null,

                'tanggal_ttd_dokter' => $validated['tanggal_ttd_dokter'] ?? null,
                'jam_ttd_dokter' => $validated['jam_ttd_dokter'] ?? null,
                'nama_dokter_ttd' => $validated['nama_dokter_ttd'] ?? null,

                'status_form' => $validated['status_form'] ?? 'draft',
            ]);

            $pengkajian->riwayat()->delete();
            foreach ($validated['riwayat'] ?? [] as $index => $row) {
                $pengkajian->riwayat()->create([
                    'riwayat_penyakit' => $row['riwayat_penyakit'] ?? null,
                    'tahun' => $row['tahun'] ?? null,
                    'riwayat_pengobatan' => $row['riwayat_pengobatan'] ?? null,
                    'urutan' => $index + 1,
                ]);
            }

            $pengkajian->penunjang()->delete();
            foreach ($validated['penunjang'] ?? [] as $index => $row) {
                $pengkajian->penunjang()->create([
                    'jenis_penunjang' => $row['jenis_penunjang'] ?? null,
                    'jenis_penunjang_lainnya' => $row['jenis_penunjang_lainnya'] ?? null,
                    'hasil_penunjang' => $row['hasil_penunjang'] ?? null,
                    'tanggal_penunjang' => $row['tanggal_penunjang'] ?? null,
                    'urutan' => $index + 1,
                ]);
            }

            $emr = Emr::findOrFail($pengkajian->emr_id);
            $kunjungan = Kunjungan::findOrFail($emr->kunjungan_id);
            $dokter = $this->dokterLogin();

            $resepId = null;
            $orderLabId = null;
            $orderRadiologiId = null;
            $pembayaran = null;

            if (($validated['status_form'] ?? 'draft') === 'final') {
                $resepId = $this->syncResepPenyakitDalam($emr, $kunjungan, $validated);
                $this->syncLayananPenyakitDalam($kunjungan, $validated);
                $orderLabId = $this->syncOrderLabPenyakitDalam($dokter, $kunjungan, $validated);
                $orderRadiologiId = $this->syncOrderRadiologiPenyakitDalam($dokter, $kunjungan, $validated);

                $kunjungan->update([
                    'status' => 'Payment',
                ]);

                $pembayaran = $this->rebuildPembayaranFinal(
                    $emr,
                    $kunjungan,
                    $resepId,
                    $orderLabId,
                    $orderRadiologiId
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengkajian awal penyakit dalam berhasil diperbarui',
                'data' => [
                    'pengkajian' => $pengkajian->load([
                        'emr.pasien',
                        'emr.dokter',
                        'emr.poli',
                        'dokter',
                        'riwayat',
                        'penunjang',
                    ]),
                    'order_lab_id' => $orderLabId,
                    'order_radiologi_id' => $orderRadiologiId,
                    'pembayaran' => $pembayaran,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pengkajian awal penyakit dalam',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $pengkajian = EmrPengkajianAwalPenyakitDalam::find($id);

        if (! $pengkajian) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengkajian tidak ditemukan',
            ], 404);
        }

        $pengkajian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pengkajian awal penyakit dalam berhasil dihapus',
        ]);
    }

    private function rules($id = null): array
    {
        return [
            'emr_id' => [
                'required',
                'exists:emr,id',
                Rule::unique('emr_pengkajian_awal_penyakit_dalam', 'emr_id')->ignore($id),
            ],
            'dokter_id' => 'nullable|exists:dokter,id',

            'tanggal_pengkajian' => 'nullable|date',
            'jam_pengkajian' => 'nullable',
            'no_rm_snapshot' => 'nullable|string|max:255',
            'nik_snapshot' => 'nullable|string|max:255',

            'alergi' => 'nullable|string',
            'sumber_data' => 'nullable|string|max:255',
            'sumber_data_lainnya' => 'nullable|string|max:255',

            'nyeri_ada' => 'nullable|boolean',
            'skala_nyeri' => 'nullable|integer|min:0|max:10',
            'karakteristik_nyeri' => 'nullable|string',
            'lokasi_nyeri' => 'nullable|string',
            'durasi_nyeri' => 'nullable|string|max:255',
            'frekuensi_nyeri' => 'nullable|string|max:255',
            'tren_nyeri' => 'nullable|string|max:255',

            'keluhan_utama' => 'nullable|string',
            'riwayat_penyakit_sekarang' => 'nullable|string',

            'riwayat_keluarga_hipertensi' => 'nullable|boolean',
            'riwayat_keluarga_kencing_manis' => 'nullable|boolean',
            'riwayat_keluarga_jantung' => 'nullable|boolean',
            'riwayat_keluarga_asthma' => 'nullable|boolean',
            'riwayat_penyakit_keluarga_lain' => 'nullable|string',
            'riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan' => 'nullable|string',

            'keadaan_umum' => 'nullable|string|max:255',
            'status_gizi' => 'nullable|string|max:255',

            'gcs_e' => 'nullable|integer',
            'gcs_m' => 'nullable|integer',
            'gcs_v' => 'nullable|integer',
            'tindakan_resusitasi' => 'nullable|boolean',

            'berat_badan' => 'nullable|numeric',
            'tinggi_badan' => 'nullable|numeric',
            'tensi_sistolik' => 'nullable|string|max:20',
            'tensi_diastolik' => 'nullable|string|max:20',
            'suhu_axila' => 'nullable|numeric',
            'suhu_rectal' => 'nullable|numeric',
            'nadi' => 'nullable|integer',
            'respirasi' => 'nullable|integer',
            'saturasi_o2' => 'nullable|integer',
            'saturasi_o2_dengan' => 'nullable|string|max:255',

            'pemeriksaan_kulit' => 'nullable|string',
            'pemeriksaan_kepala_dan_leher' => 'nullable|string',
            'pemeriksaan_telinga_hidung_mulut' => 'nullable|string',
            'pemeriksaan_leher' => 'nullable|string',

            'paru_inspeksi' => 'nullable|string',
            'paru_palpasi' => 'nullable|string',
            'paru_perkusi' => 'nullable|string',
            'paru_auskultasi' => 'nullable|string',

            'jantung_inspeksi' => 'nullable|string',
            'jantung_palpasi' => 'nullable|string',
            'jantung_perkusi' => 'nullable|string',
            'jantung_auskultasi' => 'nullable|string',

            'pemeriksaan_ekstremitas' => 'nullable|string',
            'pemeriksaan_alat_kelamin_dan_rektum' => 'nullable|string',
            'pemeriksaan_neurologis' => 'nullable|string',

            'diagnosa_kerja' => 'nullable|string',
            'diagnosa_diferensial' => 'nullable|string',
            'terapi_tindakan' => 'nullable|string',
            'rencana_kerja' => 'nullable|string',

            'boleh_pulang' => 'nullable|boolean',
            'tanggal_pulang' => 'nullable|date',
            'jam_keluar' => 'nullable',
            'kontrol_poliklinik' => 'nullable|boolean',
            'nama_poli_kontrol' => 'nullable|string|max:255',
            'tanggal_kontrol' => 'nullable|date',
            'dirawat_di_ruang' => 'nullable|string|max:255',
            'kelas_rawat' => 'nullable|string|max:255',

            'tanggal_ttd_dokter' => 'nullable|date',
            'jam_ttd_dokter' => 'nullable',
            'nama_dokter_ttd' => 'nullable|string|max:255',

            'status_form' => 'nullable|in:draft,final',

            'riwayat' => 'nullable|array',
            'riwayat.*.riwayat_penyakit' => 'nullable|string',
            'riwayat.*.tahun' => 'nullable|string|max:255',
            'riwayat.*.riwayat_pengobatan' => 'nullable|string',

            'penunjang' => 'nullable|array',
            'penunjang.*.jenis_penunjang' => 'nullable|string|max:255',
            'penunjang.*.jenis_penunjang_lainnya' => 'nullable|string|max:255',
            'penunjang.*.hasil_penunjang' => 'nullable|string',
            'penunjang.*.tanggal_penunjang' => 'nullable|date',

            'layanan' => 'nullable|array',
            'layanan.*.layanan_id' => 'required_with:layanan|exists:layanan,id',
            'layanan.*.jumlah' => 'required_with:layanan|integer|min:1',

            'resep' => 'nullable|array',
            'resep.*.obat_id' => 'required_with:resep|exists:obat,id',
            'resep.*.jumlah' => 'required_with:resep|integer|min:1',
            'resep.*.keterangan' => 'nullable|string',
            'resep.*.dosis' => 'nullable|numeric',

            'lab_tests' => 'nullable|array',
            'lab_tests.*.lab_test_id' => 'required_with:lab_tests|exists:jenis_pemeriksaan_lab,id',
            'lab_tests.*.tanggal_pemeriksaan' => 'nullable|date',
            'lab_tests.*.jam_pemeriksaan' => 'nullable|string',

            'radiologi_tests' => 'nullable|array',
            'radiologi_tests.*.jenis_radiologi_id' => 'required_with:radiologi_tests|exists:jenis_pemeriksaan_radiologi,id',
            'radiologi_tests.*.tanggal_pemeriksaan' => 'nullable|date',
            'radiologi_tests.*.jam_pemeriksaan' => 'nullable|string',
        ];
    }

    private function dokterLogin(): Dokter
    {
        $userId = Auth::id();

        return Dokter::where('user_id', $userId)->firstOrFail();
    }

    private function syncResepPenyakitDalam(Emr $emr, Kunjungan $kunjungan, array $validated): ?int
    {
        $resepId = $emr->resep_id;

        if (empty($validated['resep'])) {
            if (! empty($resepId)) {
                DB::table('resep_obat')->where('resep_id', $resepId)->delete();
            }

            return $resepId;
        }

        if (! $resepId) {
            $resepId = DB::table('resep')->insertGetId([
                'kunjungan_id' => $kunjungan->id,
                'status' => 'waiting',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $emr->update(['resep_id' => $resepId]);
        } else {
            DB::table('resep')->where('id', $resepId)->update([
                'kunjungan_id' => $kunjungan->id,
                'status' => 'waiting',
                'updated_at' => now(),
            ]);
        }

        DB::table('resep_obat')->where('resep_id', $resepId)->delete();

        foreach ($validated['resep'] as $item) {
            $obat = Obat::findOrFail($item['obat_id']);

            if (! is_null($obat->jumlah) && $obat->jumlah < (int) $item['jumlah']) {
                throw new \Exception("Stok obat {$obat->nama_obat} tidak mencukupi. Stok tersedia: {$obat->jumlah}");
            }

            DB::table('resep_obat')->insert([
                'resep_id' => $resepId,
                'obat_id' => $obat->id,
                'jumlah' => (int) $item['jumlah'],
                'dosis' => $item['dosis'] ?? $obat->dosis ?? null,
                'keterangan' => $item['keterangan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $resepId;
    }

    private function syncLayananPenyakitDalam(Kunjungan $kunjungan, array $validated): void
    {
        DB::table('kunjungan_layanan')->where('kunjungan_id', $kunjungan->id)->delete();

        if (! empty($validated['layanan'])) {
            foreach ($validated['layanan'] as $layananItem) {
                DB::table('kunjungan_layanan')->insert([
                    'kunjungan_id' => $kunjungan->id,
                    'layanan_id' => $layananItem['layanan_id'],
                    'jumlah' => (int) $layananItem['jumlah'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function syncOrderLabPenyakitDalam(Dokter $dokter, Kunjungan $kunjungan, array $validated): ?int
    {
        $orderLabId = null;

        if (empty($validated['lab_tests'])) {
            return null;
        }

        $existing = DB::table('order_lab')
            ->where('kunjungan_id', $kunjungan->id)
            ->whereIn('status', ['Pending', 'Diproses'])
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            $orderLabId = $existing->id;

            DB::table('order_lab_detail')->where('order_lab_id', $orderLabId)->delete();

            $firstLabTest = $validated['lab_tests'][0];
            $tanggalPemeriksaan = $firstLabTest['tanggal_pemeriksaan'] ?? null;
            $jamPemeriksaan = $firstLabTest['jam_pemeriksaan'] ?? null;

            DB::table('order_lab')->where('id', $orderLabId)->update([
                'dokter_id' => $dokter->id,
                'pasien_id' => $kunjungan->pasien_id,
                'tanggal_order' => now()->toDateString(),
                'tanggal_pemeriksaan' => $tanggalPemeriksaan,
                'jam_pemeriksaan' => $jamPemeriksaan,
                'status' => 'Pending',
                'updated_at' => now(),
            ]);
        } else {
            $noOrderLab = 'LAB-'.date('Ymd').'-'.strtoupper(Str::random(6));

            $firstLabTest = $validated['lab_tests'][0];
            $tanggalPemeriksaan = $firstLabTest['tanggal_pemeriksaan'] ?? null;
            $jamPemeriksaan = $firstLabTest['jam_pemeriksaan'] ?? null;

            $orderLabId = DB::table('order_lab')->insertGetId([
                'no_order_lab' => $noOrderLab,
                'kunjungan_id' => $kunjungan->id,
                'dokter_id' => $dokter->id,
                'pasien_id' => $kunjungan->pasien_id,
                'tanggal_order' => now()->toDateString(),
                'tanggal_pemeriksaan' => $tanggalPemeriksaan,
                'jam_pemeriksaan' => $jamPemeriksaan,
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($validated['lab_tests'] as $labTest) {
            DB::table('order_lab_detail')->insert([
                'order_lab_id' => $orderLabId,
                'jenis_pemeriksaan_lab_id' => $labTest['lab_test_id'],
                'status_pemeriksaan' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $orderLabId;
    }

    private function syncOrderRadiologiPenyakitDalam(Dokter $dokter, Kunjungan $kunjungan, array $validated): ?int
    {
        $orderRadiologiId = null;

        if (empty($validated['radiologi_tests'])) {
            return null;
        }

        $existingRad = DB::table('order_radiologi')
            ->where('kunjungan_id', $kunjungan->id)
            ->whereIn('status', ['Pending', 'Diproses'])
            ->orderByDesc('id')
            ->first();

        if ($existingRad) {
            $orderRadiologiId = $existingRad->id;

            DB::table('order_radiologi_detail')->where('order_radiologi_id', $orderRadiologiId)->delete();

            $firstRadiologiTest = $validated['radiologi_tests'][0];
            $tanggalPemeriksaan = $firstRadiologiTest['tanggal_pemeriksaan'] ?? null;
            $jamPemeriksaan = $firstRadiologiTest['jam_pemeriksaan'] ?? null;

            DB::table('order_radiologi')->where('id', $orderRadiologiId)->update([
                'dokter_id' => $dokter->id,
                'pasien_id' => $kunjungan->pasien_id,
                'tanggal_order' => now()->toDateString(),
                'tanggal_pemeriksaan' => $tanggalPemeriksaan,
                'jam_pemeriksaan' => $jamPemeriksaan,
                'status' => 'Pending',
                'updated_at' => now(),
            ]);
        } else {
            $noOrderRadiologi = 'RAD-'.date('Ymd').'-'.strtoupper(Str::random(6));

            $firstRadiologiTest = $validated['radiologi_tests'][0];
            $tanggalPemeriksaan = $firstRadiologiTest['tanggal_pemeriksaan'] ?? null;
            $jamPemeriksaan = $firstRadiologiTest['jam_pemeriksaan'] ?? null;

            $orderRadiologiId = DB::table('order_radiologi')->insertGetId([
                'no_order_radiologi' => $noOrderRadiologi,
                'kunjungan_id' => $kunjungan->id,
                'dokter_id' => $dokter->id,
                'pasien_id' => $kunjungan->pasien_id,
                'tanggal_order' => now()->toDateString(),
                'tanggal_pemeriksaan' => $tanggalPemeriksaan,
                'jam_pemeriksaan' => $jamPemeriksaan,
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($validated['radiologi_tests'] as $rad) {
            DB::table('order_radiologi_detail')->insert([
                'order_radiologi_id' => $orderRadiologiId,
                'jenis_pemeriksaan_radiologi_id' => $rad['jenis_radiologi_id'],
                'status_pemeriksaan' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $orderRadiologiId;
    }

    private function generateKodeTransaksiPenyakitDalam(): string
    {
        return strtoupper(uniqid('TRX_'));
    }

    private function rebuildPembayaranFinal(
        Emr $emr,
        Kunjungan $kunjungan,
        ?int $resepId,
        ?int $orderLabId,
        ?int $orderRadiologiId
    ): Pembayaran {
        $existingPembayaran = Pembayaran::where('emr_id', $emr->id)->first();

        $pembayaran = Pembayaran::updateOrCreate(
            ['emr_id' => $emr->id],
            [
                'kode_transaksi' => $existingPembayaran?->kode_transaksi ?? $this->generateKodeTransaksiPenyakitDalam(),
                'tanggal_pembayaran' => null,
                'status' => 'Belum Bayar',
                'metode_pembayaran_id' => null,
                'bukti_pembayaran' => null,
                'total_tagihan' => 0,
                'diskon_tipe' => $existingPembayaran?->diskon_tipe,
                'diskon_nilai' => $existingPembayaran?->diskon_nilai ?? 0,
                'total_setelah_diskon' => null,
                'uang_yang_diterima' => 0,
                'kembalian' => 0,
                'catatan' => 'Menunggu pembayaran di kasir - finalize pengkajian penyakit dalam',
            ]
        );

        DB::table('pembayaran_detail')->where('pembayaran_id', $pembayaran->id)->delete();

        $total = 0;

        $insertDetail = function (array $data) use ($pembayaran, &$total) {
            $row = [
                'pembayaran_id' => $pembayaran->id,
                'nama_item' => $data['nama_item'],
                'qty' => (int) $data['qty'],
                'harga' => (float) $data['harga'],
                'subtotal' => (float) $data['subtotal'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $row['layanan_id'] = $data['layanan_id'] ?? null;
            $row['resep_obat_id'] = $data['resep_obat_id'] ?? null;
            $row['order_lab_detail_id'] = $data['order_lab_detail_id'] ?? null;
            $row['order_radiologi_detail_id'] = $data['order_radiologi_detail_id'] ?? null;

            if (array_key_exists('hasil_lab_id', $data)) {
                $row['hasil_lab_id'] = $data['hasil_lab_id'] ?: null;
            }

            DB::table('pembayaran_detail')->insert($row);
            $total += (float) $row['subtotal'];
        };

        $layananRows = DB::table('kunjungan_layanan as kl')
            ->join('layanan as l', 'l.id', '=', 'kl.layanan_id')
            ->where('kl.kunjungan_id', $kunjungan->id)
            ->select(
                'kl.layanan_id',
                'kl.jumlah',
                'l.nama_layanan',
                'l.harga_sebelum_diskon',
                'l.harga_setelah_diskon'
            )
            ->get();

        foreach ($layananRows as $row) {
            $harga = (float) ($row->harga_setelah_diskon ?? $row->harga_sebelum_diskon ?? 0);
            $qty = (int) ($row->jumlah ?? 1);
            $subtotal = $harga * $qty;

            $insertDetail([
                'layanan_id' => $row->layanan_id,
                'nama_item' => 'Layanan: '.$row->nama_layanan,
                'qty' => $qty,
                'harga' => $harga,
                'subtotal' => $subtotal,
            ]);
        }

        if (! empty($resepId)) {
            $obatRows = DB::table('resep_obat as ro')
                ->join('obat as o', 'o.id', '=', 'ro.obat_id')
                ->where('ro.resep_id', $resepId)
                ->select('ro.id as resep_obat_id', 'ro.jumlah', 'o.nama_obat', 'o.harga_jual_obat', 'o.total_harga')
                ->get();

            foreach ($obatRows as $row) {
                $harga = (float) ($row->harga_jual_obat ?? $row->total_harga ?? 0);
                $qty = (int) ($row->jumlah ?? 1);
                $subtotal = $harga * $qty;

                $insertDetail([
                    'resep_obat_id' => $row->resep_obat_id,
                    'nama_item' => 'Obat: '.$row->nama_obat,
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ]);
            }
        }

        if (! empty($orderLabId)) {
            $labRows = DB::table('order_lab_detail as old')
                ->join('jenis_pemeriksaan_lab as jpl', 'jpl.id', '=', 'old.jenis_pemeriksaan_lab_id')
                ->where('old.order_lab_id', $orderLabId)
                ->select('old.id as order_lab_detail_id', 'jpl.nama_pemeriksaan', 'jpl.harga_pemeriksaan_lab')
                ->get();

            foreach ($labRows as $row) {
                $harga = (float) ($row->harga_pemeriksaan_lab ?? 0);
                $qty = 1;
                $subtotal = $harga;

                $insertDetail([
                    'order_lab_detail_id' => $row->order_lab_detail_id,
                    'nama_item' => 'Lab: '.$row->nama_pemeriksaan,
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ]);
            }
        }

        if (! empty($orderRadiologiId)) {
            $radRows = DB::table('order_radiologi_detail as ord')
                ->join('jenis_pemeriksaan_radiologi as jpr', 'jpr.id', '=', 'ord.jenis_pemeriksaan_radiologi_id')
                ->where('ord.order_radiologi_id', $orderRadiologiId)
                ->select(
                    'ord.id as order_radiologi_detail_id',
                    'jpr.nama_pemeriksaan',
                    'jpr.harga_pemeriksaan_radiologi'
                )
                ->get();

            foreach ($radRows as $row) {
                $harga = (float) ($row->harga_pemeriksaan_radiologi ?? 0);
                $qty = 1;
                $subtotal = $harga;

                $insertDetail([
                    'order_radiologi_detail_id' => $row->order_radiologi_detail_id,
                    'nama_item' => 'Radiologi: '.$row->nama_pemeriksaan,
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ]);
            }
        }

        $pembayaran->update([
            'total_tagihan' => $total,
        ]);

        return $pembayaran->fresh();
    }
}
