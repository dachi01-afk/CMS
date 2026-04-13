<?php

namespace App\Http\Controllers\Api\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Emr;
use App\Models\EmrKklp;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class KklpController extends Controller
{
    private function generateNoKasus(): string
    {
        do {
            $kode = 'KKLP-'.now()->format('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6));
        } while (EmrKklp::where('no_kasus', $kode)->exists());

        return $kode;
    }

    private function validationRules(bool $isUpdate = false): array
    {
        return [
            'kunjungan_id' => ($isUpdate ? 'sometimes' : 'required').'|exists:kunjungan,id',
            'emr_id' => ($isUpdate ? 'sometimes' : 'required').'|exists:emr,id',

            'nama_dokter_form' => 'nullable|string|max:255',
            'nim_dokter' => 'nullable|string|max:255',
            'kasus_ke' => 'nullable|string|max:255',
            'tanggal_kasus' => 'nullable|date',
            'no_kasus' => 'nullable|string|max:255',
            'telepon_pasien' => 'nullable|string|max:255',
            'agama_pasien' => 'nullable|string|max:255',
            'pendidikan_terakhir_pasien' => 'nullable|string|max:255',
            'suku_bangsa_pasien' => 'nullable|string|max:255',
            'tanggal_pemeriksaan' => 'nullable|date',
            'tanggal_homevisit' => 'nullable|date',

            'riwayat_penyakit_sekarang' => 'nullable|string',
            'riwayat_penyakit_dahulu_detail' => 'nullable|string',
            'riwayat_penyakit_keluarga_detail' => 'nullable|string',
            'riwayat_personal_sosial' => 'nullable|string',
            'review_sistem' => 'nullable|string',

            'illness_pikiran' => 'nullable|string',
            'illness_perasaan' => 'nullable|string',
            'illness_efek_fungsi' => 'nullable|string',
            'illness_harapan' => 'nullable|string',
            'illness_kesimpulan' => 'nullable|string',

            'genogram_keterangan' => 'nullable|string',
            'bentuk_keluarga' => 'nullable|string',
            'siklus_kehidupan_keluarga' => 'nullable|string',
            'family_map_keterangan' => 'nullable|string',
            'apgar_score_total' => 'nullable|integer',
            'apgar_kesimpulan' => 'nullable|string',
            'family_life_line_ringkasan' => 'nullable|string',

            'keadaan_umum' => 'nullable|string|max:255',
            'kesadaran' => 'nullable|string|max:255',
            'tekanan_darah' => 'nullable|string|max:255',
            'nadi' => 'nullable|string|max:255',
            'respirasi' => 'nullable|string|max:255',
            'suhu' => 'nullable|string|max:255',
            'tinggi_badan' => 'nullable|string|max:255',
            'berat_badan' => 'nullable|string|max:255',
            'imt' => 'nullable|string|max:255',
            'saturasi_oksigen' => 'nullable|string|max:255',

            'lingkar_pinggang' => 'nullable|string|max:255',
            'lingkar_panggul' => 'nullable|string|max:255',
            'lingkar_lengan_atas' => 'nullable|string|max:255',
            'status_gizi' => 'nullable|string|max:255',
            'waist_hip_ratio' => 'nullable|string|max:255',

            'pemeriksaan_kulit' => 'nullable|string',
            'pemeriksaan_kelenjar_limfe' => 'nullable|string',
            'pemeriksaan_otot' => 'nullable|string',
            'pemeriksaan_tulang' => 'nullable|string',
            'pemeriksaan_sendi' => 'nullable|string',
            'pemeriksaan_kepala' => 'nullable|string',
            'pemeriksaan_mata' => 'nullable|string',
            'pemeriksaan_hidung' => 'nullable|string',
            'pemeriksaan_telinga' => 'nullable|string',
            'pemeriksaan_mulut_gigi' => 'nullable|string',
            'pemeriksaan_tenggorokan' => 'nullable|string',
            'pemeriksaan_leher' => 'nullable|string',
            'thorax_paru_inspeksi' => 'nullable|string',
            'thorax_paru_palpasi' => 'nullable|string',
            'thorax_paru_perkusi' => 'nullable|string',
            'thorax_paru_auskultasi' => 'nullable|string',
            'thorax_jantung_inspeksi' => 'nullable|string',
            'thorax_jantung_palpasi' => 'nullable|string',
            'thorax_jantung_perkusi' => 'nullable|string',
            'thorax_jantung_auskultasi' => 'nullable|string',
            'abdomen_inspeksi' => 'nullable|string',
            'abdomen_palpasi' => 'nullable|string',
            'abdomen_perkusi' => 'nullable|string',
            'abdomen_auskultasi' => 'nullable|string',
            'anogenital' => 'nullable|string',
            'tambahan_pemeriksaan_khusus' => 'nullable|string',

            'ringkasan_laboratorium' => 'nullable|string',
            'ringkasan_radiologi' => 'nullable|string',
            'ringkasan_penunjang_lain' => 'nullable|string',
            'patogenesis_patofisiologi' => 'nullable|string',
            'diagnosis_klinis_banding' => 'nullable|string',
            'diagnosis_holistik' => 'nullable|string',
            'uraian_diagnosis_holistik' => 'nullable|string',

            'upaya_promotif' => 'nullable|string',
            'upaya_preventif' => 'nullable|string',
            'upaya_kuratif' => 'nullable|string',
            'upaya_rehabilitatif' => 'nullable|string',
            'upaya_paliatif' => 'nullable|string',
            'copc_plan_ringkasan' => 'nullable|string',
            'kesimpulan_phbs' => 'nullable|string',

            'kondisi_rumah' => 'nullable|string',
            'lingkungan_sekitar_rumah' => 'nullable|string',
            'catatan_tambahan_homevisit' => 'nullable|string',

            'nilai_humanisme' => 'nullable|integer',
            'nilai_komunikasi' => 'nullable|integer',
            'nilai_pemeriksaan_fisik' => 'nullable|integer',
            'nilai_penalaran_klinis' => 'nullable|integer',
            'nilai_diagnosis_holistik' => 'nullable|integer',
            'nilai_pengelolaan_komprehensif' => 'nullable|integer',
            'nilai_edukasi_konseling' => 'nullable|integer',
            'nilai_organisasi_efisiensi' => 'nullable|integer',
            'nilai_kompetensi_keseluruhan' => 'nullable|integer',
            'skor_total' => 'nullable|integer',
            'skor_akhir' => 'nullable|numeric',
            'komentar_pembimbing' => 'nullable|string',
            'komentar_dokter_residen' => 'nullable|string',
            'status_form' => 'nullable|in:draft,final',

            'orangtua' => 'nullable|array',
            'orangtua.*.hubungan' => 'required_with:orangtua|in:Ayah,Ibu',
            'orangtua.*.nama_lengkap' => 'nullable|string|max:255',
            'orangtua.*.tanggal_lahir' => 'nullable|date',
            'orangtua.*.umur' => 'nullable|string|max:255',
            'orangtua.*.alamat' => 'nullable|string',
            'orangtua.*.telepon' => 'nullable|string|max:255',
            'orangtua.*.pekerjaan' => 'nullable|string|max:255',
            'orangtua.*.agama' => 'nullable|string|max:255',
            'orangtua.*.pendidikan_terakhir' => 'nullable|string|max:255',
            'orangtua.*.suku_bangsa' => 'nullable|string|max:255',

            'heteroanamnesis' => 'nullable|array',
            'heteroanamnesis.nama_lengkap' => 'nullable|string|max:255',
            'heteroanamnesis.jenis_kelamin' => 'nullable|in:L,P',
            'heteroanamnesis.tanggal_lahir' => 'nullable|date',
            'heteroanamnesis.umur' => 'nullable|string|max:255',
            'heteroanamnesis.alamat' => 'nullable|string',
            'heteroanamnesis.telepon' => 'nullable|string|max:255',
            'heteroanamnesis.hubungan_dengan_pasien' => 'nullable|string|max:255',

            'family_apgar' => 'nullable|array',
            'family_apgar.adaptability' => 'nullable|integer|min:0|max:2',
            'family_apgar.partnership' => 'nullable|integer|min:0|max:2',
            'family_apgar.growth' => 'nullable|integer|min:0|max:2',
            'family_apgar.affection' => 'nullable|integer|min:0|max:2',
            'family_apgar.resolve' => 'nullable|integer|min:0|max:2',
            'family_apgar.total_skor' => 'nullable|integer',
            'family_apgar.interpretasi' => 'nullable|string|max:255',

            'family_screem' => 'nullable|array',

            'anggota_keluarga' => 'nullable|array',
            'anggota_keluarga.*.kategori' => 'required_with:anggota_keluarga|in:keluarga_asal,tinggal_serumah',
            'anggota_keluarga.*.nama' => 'nullable|string|max:255',
            'anggota_keluarga.*.jenis_kelamin' => 'nullable|in:L,P',
            'anggota_keluarga.*.tanggal_lahir' => 'nullable|date',
            'anggota_keluarga.*.umur' => 'nullable|string|max:255',
            'anggota_keluarga.*.pekerjaan' => 'nullable|string|max:255',
            'anggota_keluarga.*.no_hp' => 'nullable|string|max:255',
            'anggota_keluarga.*.status_kesehatan' => 'nullable|string|max:255',
            'anggota_keluarga.*.hubungan' => 'nullable|string|max:255',

            'homevisit' => 'nullable|array',
            'homevisit.*.nomor_kunjungan' => 'nullable|integer',
            'homevisit.*.tanggal' => 'nullable|date',
            'homevisit.*.catatan' => 'nullable|string',
            'homevisit.*.kesimpulan' => 'nullable|string',
            'homevisit.*.rencana_tindak_lanjut' => 'nullable|string',

            'family_plan' => 'nullable|array',
            'family_plan.*.nama' => 'nullable|string|max:255',
            'family_plan.*.usia' => 'nullable|string|max:255',
            'family_plan.*.status_kesehatan' => 'nullable|string|max:255',
            'family_plan.*.skrining' => 'nullable|string',
            'family_plan.*.edukasi_konseling' => 'nullable|string',
            'family_plan.*.imunisasi' => 'nullable|string',
            'family_plan.*.catatan' => 'nullable|string',

            'copc_plan' => 'nullable|array',
            'copc_plan.*.masalah_komunitas' => 'nullable|string',
            'copc_plan.*.rencana_eksplorasi' => 'nullable|string',
            'copc_plan.*.rencana_edukasi' => 'nullable|string',
            'copc_plan.*.target' => 'nullable|string',

            'ekstremitas' => 'nullable|array',
            'ekstremitas.*.anggota' => 'required_with:ekstremitas|in:kanan_atas,kiri_atas,kanan_bawah,kiri_bawah',
            'ekstremitas.*.akral' => 'nullable|string|max:255',
            'ekstremitas.*.gerakan' => 'nullable|string',
            'ekstremitas.*.tonus' => 'nullable|string',
            'ekstremitas.*.trofi' => 'nullable|string',
            'ekstremitas.*.refleks_fisiologis' => 'nullable|string',
            'ekstremitas.*.refleks_patologis' => 'nullable|string',
            'ekstremitas.*.sensibilitas' => 'nullable|string',
            'ekstremitas.*.meningeal_signs' => 'nullable|string',
        ];
    }

    private function mainPayloadFields(): array
    {
        return [
            'nama_dokter_form',
            'nim_dokter',
            'kasus_ke',
            'tanggal_kasus',
            'no_kasus',
            'telepon_pasien',
            'agama_pasien',
            'pendidikan_terakhir_pasien',
            'suku_bangsa_pasien',
            'tanggal_pemeriksaan',
            'tanggal_homevisit',
            'riwayat_penyakit_sekarang',
            'riwayat_penyakit_dahulu_detail',
            'riwayat_penyakit_keluarga_detail',
            'riwayat_personal_sosial',
            'review_sistem',
            'illness_pikiran',
            'illness_perasaan',
            'illness_efek_fungsi',
            'illness_harapan',
            'illness_kesimpulan',
            'genogram_keterangan',
            'bentuk_keluarga',
            'siklus_kehidupan_keluarga',
            'family_map_keterangan',
            'apgar_score_total',
            'apgar_kesimpulan',
            'family_life_line_ringkasan',
            'keadaan_umum',
            'kesadaran',
            'tekanan_darah',
            'nadi',
            'respirasi',
            'suhu',
            'tinggi_badan',
            'berat_badan',
            'imt',
            'saturasi_oksigen',
            'lingkar_pinggang',
            'lingkar_panggul',
            'lingkar_lengan_atas',
            'status_gizi',
            'waist_hip_ratio',
            'pemeriksaan_kulit',
            'pemeriksaan_kelenjar_limfe',
            'pemeriksaan_otot',
            'pemeriksaan_tulang',
            'pemeriksaan_sendi',
            'pemeriksaan_kepala',
            'pemeriksaan_mata',
            'pemeriksaan_hidung',
            'pemeriksaan_telinga',
            'pemeriksaan_mulut_gigi',
            'pemeriksaan_tenggorokan',
            'pemeriksaan_leher',
            'thorax_paru_inspeksi',
            'thorax_paru_palpasi',
            'thorax_paru_perkusi',
            'thorax_paru_auskultasi',
            'thorax_jantung_inspeksi',
            'thorax_jantung_palpasi',
            'thorax_jantung_perkusi',
            'thorax_jantung_auskultasi',
            'abdomen_inspeksi',
            'abdomen_palpasi',
            'abdomen_perkusi',
            'abdomen_auskultasi',
            'anogenital',
            'tambahan_pemeriksaan_khusus',
            'ringkasan_laboratorium',
            'ringkasan_radiologi',
            'ringkasan_penunjang_lain',
            'patogenesis_patofisiologi',
            'diagnosis_klinis_banding',
            'diagnosis_holistik',
            'uraian_diagnosis_holistik',
            'upaya_promotif',
            'upaya_preventif',
            'upaya_kuratif',
            'upaya_rehabilitatif',
            'upaya_paliatif',
            'copc_plan_ringkasan',
            'kesimpulan_phbs',
            'kondisi_rumah',
            'lingkungan_sekitar_rumah',
            'catatan_tambahan_homevisit',
            'nilai_humanisme',
            'nilai_komunikasi',
            'nilai_pemeriksaan_fisik',
            'nilai_penalaran_klinis',
            'nilai_diagnosis_holistik',
            'nilai_pengelolaan_komprehensif',
            'nilai_edukasi_konseling',
            'nilai_organisasi_efisiensi',
            'nilai_kompetensi_keseluruhan',
            'skor_total',
            'skor_akhir',
            'komentar_pembimbing',
            'komentar_dokter_residen',
            'status_form',
        ];
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                return \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function isMeaningfulRow(array $row): bool
    {
        foreach ($row as $value) {
            if (is_array($value)) {
                if ($this->isMeaningfulRow($value)) {
                    return true;
                }
                continue;
            }

            if ($value !== null && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function normalizeOrangtua(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                $row['tanggal_lahir'] = $this->normalizeDate($row['tanggal_lahir'] ?? null);
                return $row;
            })
            ->filter(fn ($row) => is_array($row) && $this->isMeaningfulRow($row))
            ->values()
            ->toArray();
    }

    private function normalizeHeteroanamnesis(?array $row): ?array
    {
        if (! is_array($row)) {
            return null;
        }

        $row['tanggal_lahir'] = $this->normalizeDate($row['tanggal_lahir'] ?? null);

        return $this->isMeaningfulRow($row) ? $row : null;
    }

    private function normalizeAnggotaKeluarga(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                $row['tanggal_lahir'] = $this->normalizeDate($row['tanggal_lahir'] ?? null);
                return $row;
            })
            ->filter(fn ($row) => is_array($row) && $this->isMeaningfulRow($row))
            ->values()
            ->toArray();
    }

    private function normalizeHomevisit(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                $row['tanggal'] = $this->normalizeDate($row['tanggal'] ?? null);
                return $row;
            })
            ->filter(fn ($row) => is_array($row) && $this->isMeaningfulRow($row))
            ->values()
            ->toArray();
    }

    private function normalizeGenericRows(array $rows): array
    {
        return collect($rows)
            ->filter(fn ($row) => is_array($row) && $this->isMeaningfulRow($row))
            ->values()
            ->toArray();
    }

    private function syncHasOne($parent, string $relation, ?array $payload): void
    {
        if (! method_exists($parent, $relation)) {
            return;
        }

        $rel = $parent->{$relation}();

        if (empty($payload)) {
            $rel->delete();
            return;
        }

        if ($rel->exists()) {
            $rel->update($payload);
        } else {
            $rel->create($payload);
        }
    }

    private function syncHasMany($parent, string $relation, array $rows): void
    {
        if (! method_exists($parent, $relation)) {
            return;
        }

        $rel = $parent->{$relation}();
        $rel->delete();

        if (! empty($rows)) {
            $rel->createMany($rows);
        }
    }

    private function persistNestedRelations(Request $request, EmrKklp $kklp): void
    {
        $orangtua = $this->normalizeOrangtua($request->input('orangtua', []));
        $heteroanamnesis = $this->normalizeHeteroanamnesis($request->input('heteroanamnesis'));
        $familyApgar = $request->input('family_apgar');
        $familyScreem = $request->input('family_screem');
        $anggotaKeluarga = $this->normalizeAnggotaKeluarga($request->input('anggota_keluarga', []));
        $homevisit = $this->normalizeHomevisit($request->input('homevisit', []));
        $familyPlan = $this->normalizeGenericRows($request->input('family_plan', []));
        $copcPlan = $this->normalizeGenericRows($request->input('copc_plan', []));
        $ekstremitas = $this->normalizeGenericRows($request->input('ekstremitas', []));

        $this->syncHasMany($kklp, 'orangtua', $orangtua);
        $this->syncHasOne($kklp, 'heteroanamnesis', $heteroanamnesis);
        $this->syncHasOne($kklp, 'familyApgar', is_array($familyApgar) && $this->isMeaningfulRow($familyApgar) ? $familyApgar : null);
        $this->syncHasOne($kklp, 'familyScreem', is_array($familyScreem) && $this->isMeaningfulRow($familyScreem) ? $familyScreem : null);
        $this->syncHasMany($kklp, 'anggotaKeluarga', $anggotaKeluarga);
        $this->syncHasMany($kklp, 'homevisit', $homevisit);
        $this->syncHasMany($kklp, 'familyPlan', $familyPlan);
        $this->syncHasMany($kklp, 'copcPlan', $copcPlan);
        $this->syncHasMany($kklp, 'ekstremitas', $ekstremitas);
    }

    private function loadFullKklpByEmr(int $emrId): ?EmrKklp
    {
        return EmrKklp::with([
            'emr',
            'kunjungan',
            'pasien',
            'dokter',
            'poli',
            'orangtua',
            'heteroanamnesis',
            'familyApgar',
            'familyScreem',
            'anggotaKeluarga',
            'homevisit',
            'familyPlan',
            'copcPlan',
            'ekstremitas',
        ])->where('emr_id', $emrId)->first();
    }

    public function showForm($kunjunganId)
    {
        try {
            $userId = Auth::id();
            $dokter = Dokter::where('user_id', $userId)->firstOrFail();

            $kunjungan = Kunjungan::with([
                'pasien',
                'poli',
                'emr' => function ($q) use ($dokter) {
                    $q->where('dokter_id', $dokter->id)->with('perawat');
                },
            ])->findOrFail($kunjunganId);

            if (! $kunjungan->emr) {
                return response()->json([
                    'success' => false,
                    'message' => 'EMR belum tersedia untuk kunjungan ini',
                ], 400);
            }

            $emr = $kunjungan->emr;

            $kklp = EmrKklp::with([
                'orangtua',
                'heteroanamnesis',
                'familyApgar',
                'familyScreem',
                'anggotaKeluarga',
                'homevisit',
                'familyPlan',
                'copcPlan',
                'ekstremitas',
            ])->where('emr_id', $emr->id)->first();

            $kasusKeOtomatis = EmrKklp::where('dokter_id', $dokter->id)->count() + 1;

            $headerForm = [
                'nama_dokter_form' => $dokter->nama_dokter,
                'nim_dokter' => Schema::hasColumn('dokter', 'nim_dokter')
                    ? ($dokter->nim_dokter ?? null)
                    : null,
                'kasus_ke' => (string) $kasusKeOtomatis,
                'tanggal_kasus' => now()->toDateString(),
                'no_kasus' => $kklp?->no_kasus ?: $this->generateNoKasus(),
            ];

            if ($kklp) {
                $headerForm['nama_dokter_form'] = $kklp->nama_dokter_form ?: $headerForm['nama_dokter_form'];
                $headerForm['nim_dokter'] = $kklp->nim_dokter ?: $headerForm['nim_dokter'];
                $headerForm['kasus_ke'] = $kklp->kasus_ke ?: $headerForm['kasus_ke'];
                $headerForm['tanggal_kasus'] = $kklp->tanggal_kasus ?: $headerForm['tanggal_kasus'];
                $headerForm['no_kasus'] = $kklp->no_kasus ?: $headerForm['no_kasus'];
            }

            return response()->json([
                'success' => true,
                'message' => 'Data form KKLP berhasil diambil',
                'data' => [
                    'form_type' => 'kklp',
                    'header_form' => $headerForm,
                    'kunjungan' => [
                        'id' => $kunjungan->id,
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'no_antrian' => $kunjungan->no_antrian,
                        'status' => $kunjungan->status,
                        'keluhan_awal' => $kunjungan->keluhan_awal,
                    ],
                    'pasien' => $kunjungan->pasien ? [
                        'id' => $kunjungan->pasien->id,
                        'nama_pasien' => $kunjungan->pasien->nama_pasien,
                        'alamat' => $kunjungan->pasien->alamat,
                        'tanggal_lahir' => $kunjungan->pasien->tanggal_lahir,
                        'jenis_kelamin' => $kunjungan->pasien->jenis_kelamin,
                        'no_emr' => $kunjungan->pasien->no_emr,
                        'nik' => $kunjungan->pasien->nik ?? null,
                        'no_bpjs' => $kunjungan->pasien->no_bpjs ?? null,
                        'no_hp_pasien' => $kunjungan->pasien->no_hp_pasien ?? null,
                        'pekerjaan' => $kunjungan->pasien->pekerjaan ?? null,
                        'agama' => $kunjungan->pasien->agama ?? null,
                        'pendidikan_terakhir' => $kunjungan->pasien->pendidikan_terakhir ?? null,
                        'suku_bangsa' => $kunjungan->pasien->suku_bangsa ?? null,
                    ] : null,
                    'poli' => $kunjungan->poli ? [
                        'id' => $kunjungan->poli->id,
                        'nama_poli' => $kunjungan->poli->nama_poli,
                    ] : null,
                    'emr' => [
                        'id' => $emr->id,
                        'keluhan_utama' => $emr->keluhan_utama,
                        'riwayat_penyakit_dahulu' => $emr->riwayat_penyakit_dahulu,
                        'riwayat_penyakit_keluarga' => $emr->riwayat_penyakit_keluarga,
                        'tekanan_darah' => $emr->tekanan_darah,
                        'suhu_tubuh' => $emr->suhu_tubuh,
                        'tinggi_badan' => $emr->tinggi_badan,
                        'berat_badan' => $emr->berat_badan,
                        'imt' => $emr->imt,
                        'nadi' => $emr->nadi,
                        'pernapasan' => $emr->pernapasan,
                        'saturasi_oksigen' => $emr->saturasi_oksigen,
                        'diagnosis' => $emr->diagnosis,
                    ],
                    'perawat' => $emr->perawat ? [
                        'id' => $emr->perawat->id,
                        'nama_perawat' => $emr->perawat->nama_perawat,
                    ] : null,
                    'form' => $kklp,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('KklpController@showForm error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data form KKLP: '.$e->getMessage(),
            ], 500);
        }
    }

    public function save(Request $request)
    {
        try {
            $request->validate($this->validationRules());

            $userId = Auth::id();
            $dokter = Dokter::where('user_id', $userId)->firstOrFail();

            $kunjungan = Kunjungan::with('emr')->findOrFail($request->kunjungan_id);
            $emr = Emr::findOrFail($request->emr_id);

            if ((int) $emr->kunjungan_id !== (int) $kunjungan->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'EMR tidak sesuai dengan kunjungan',
                ], 400);
            }

            if ((int) $emr->dokter_id !== (int) $dokter->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'EMR ini bukan milik dokter yang sedang login',
                ], 403);
            }

            $result = DB::transaction(function () use ($request, $kunjungan, $emr, $dokter) {
                $payload = $request->only($this->mainPayloadFields());

                $payload['tanggal_kasus'] = $this->normalizeDate($request->tanggal_kasus);
                $payload['tanggal_pemeriksaan'] = $this->normalizeDate($request->tanggal_pemeriksaan);
                $payload['tanggal_homevisit'] = $this->normalizeDate($request->tanggal_homevisit);

                $payload['emr_id'] = $emr->id;
                $payload['kunjungan_id'] = $kunjungan->id;
                $payload['pasien_id'] = $kunjungan->pasien_id;
                $payload['dokter_id'] = $dokter->id;
                $payload['poli_id'] = $kunjungan->poli_id;

                $existing = EmrKklp::where('emr_id', $emr->id)->first();
                $payload['no_kasus'] = $request->no_kasus ?: ($existing?->no_kasus ?: $this->generateNoKasus());

                $kklp = EmrKklp::updateOrCreate(
                    ['emr_id' => $emr->id],
                    $payload
                );

                $this->persistNestedRelations($request, $kklp);

                return $this->loadFullKklpByEmr($emr->id);
            });

            return response()->json([
                'success' => true,
                'message' => 'Form KKLP berhasil disimpan',
                'data' => $result,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('KklpController@save error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan form KKLP: '.$e->getMessage(),
            ], 500);
        }
    }

    public function riwayatPasienKklp(Request $request)
    {
        try {
            $userId = Auth::id();
            $dokter = Dokter::where('user_id', $userId)->first();

            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            $riwayatPasien = DB::table('emr_kklp as ek')
                ->join('emr as e', 'ek.emr_id', '=', 'e.id')
                ->join('kunjungan as k', 'ek.kunjungan_id', '=', 'k.id')
                ->join('pasien as p', 'k.pasien_id', '=', 'p.id')
                ->leftJoin('poli as po', 'k.poli_id', '=', 'po.id')
                ->leftJoin('perawat as pr', 'e.perawat_id', '=', 'pr.id')
                ->leftJoin('dokter as d', 'e.dokter_id', '=', 'd.id')
                ->leftJoin('jenis_spesialis as js', 'd.jenis_spesialis_id', '=', 'js.id')
                ->where('ek.dokter_id', $dokter->id)
                ->select(
                    'k.id',
                    'k.pasien_id',
                    'k.poli_id',
                    'k.tanggal_kunjungan',
                    'k.no_antrian',
                    'k.status as status_kunjungan',
                    'k.keluhan_awal',
                    'k.created_at',
                    'k.updated_at',

                    'p.nama_pasien',
                    'p.no_emr',

                    'po.nama_poli',

                    'e.id as emr_id',
                    'e.diagnosis',
                    'e.keluhan_utama',

                    'pr.id as perawat_id',
                    'pr.nama_perawat',
                    'pr.foto_perawat',
                    'pr.no_hp_perawat',

                    'd.id as dokter_pemeriksa_id',
                    'd.nama_dokter as dokter_pemeriksa_nama',
                    'd.foto_dokter as dokter_pemeriksa_foto',
                    'js.id as spesialis_id',
                    'js.nama_spesialis',

                    'ek.id as kklp_id',
                    'ek.no_kasus',
                    'ek.status_form',
                    'ek.created_at as kklp_created_at'
                )
                ->orderByDesc('ek.created_at')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'pasien_id' => $row->pasien_id,
                        'poli_id' => $row->poli_id,
                        'tanggal_kunjungan' => $row->tanggal_kunjungan,
                        'no_antrian' => $row->no_antrian ? (string) $row->no_antrian : null,
                        'status' => $row->status_form ?? 'draft',
                        'status_kunjungan' => $row->status_kunjungan,
                        'keluhan_awal' => $row->keluhan_awal,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,

                        'pasien' => [
                            'id' => $row->pasien_id,
                            'nama_pasien' => $row->nama_pasien,
                            'no_emr' => $row->no_emr,
                        ],

                        'poli' => [
                            'id' => $row->poli_id,
                            'nama_poli' => $row->nama_poli,
                        ],

                        'emr' => [
                            'id' => $row->emr_id,
                            'diagnosis' => $row->diagnosis,
                            'keluhan_utama' => $row->keluhan_utama,
                        ],

                        'perawat' => [
                            'id' => $row->perawat_id,
                            'nama_perawat' => $row->nama_perawat,
                            'foto_perawat' => $row->foto_perawat,
                            'no_hp_perawat' => $row->no_hp_perawat,
                        ],

                        'dokter_pemeriksa' => [
                            'id' => $row->dokter_pemeriksa_id,
                            'nama_dokter' => $row->dokter_pemeriksa_nama,
                            'foto_dokter' => $row->dokter_pemeriksa_foto,
                            'spesialis' => [
                                'id' => $row->spesialis_id,
                                'nama_spesialis' => $row->nama_spesialis ?? 'Umum',
                            ],
                        ],

                        'kklp' => [
                            'id' => $row->kklp_id,
                            'no_kasus' => $row->no_kasus,
                            'status_form' => $row->status_form ?? 'draft',
                            'created_at' => $row->kklp_created_at,
                        ],

                        'emr_id' => $row->emr_id,
                        'kklp_id' => $row->kklp_id,
                        'can_edit' => true,
                    ];
                });

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $riwayatPasien->values()->toArray(),
                'total_pasien' => $riwayatPasien->count(),
                'dokter_info' => [
                    'id' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                    'filtering_method' => 'KKLP by dokter login only',
                    'form_type' => 'kklp',
                ],
                'message' => 'Berhasil mengambil riwayat pasien KKLP',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('KklpController@riwayatPasienKklp error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pasien KKLP: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $kklpId)
    {
        try {
            $kklp = EmrKklp::findOrFail($kklpId);

            $userId = Auth::id();
            $dokter = Dokter::where('user_id', $userId)->firstOrFail();

            if ((int) $kklp->dokter_id !== (int) $dokter->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak punya akses untuk mengubah form ini',
                ], 403);
            }

            $request->validate($this->validationRules(true));

            $result = DB::transaction(function () use ($request, $kklp) {
                $payload = $request->only($this->mainPayloadFields());

                $payload['tanggal_kasus'] = $this->normalizeDate($request->tanggal_kasus);
                $payload['tanggal_pemeriksaan'] = $this->normalizeDate($request->tanggal_pemeriksaan);
                $payload['tanggal_homevisit'] = $this->normalizeDate($request->tanggal_homevisit);

                $kklp->update($payload);

                $this->persistNestedRelations($request, $kklp);

                return $this->loadFullKklpByEmr($kklp->emr_id);
            });

            return response()->json([
                'success' => true,
                'message' => 'Form KKLP berhasil diperbarui',
                'data' => $result,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('KklpController@update error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui form KKLP: '.$e->getMessage(),
            ], 500);
        }
    }

    public function detailByEmr($emrId)
    {
        try {
            $kklp = $this->loadFullKklpByEmr((int) $emrId);

            if (! $kklp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data KKLP tidak ditemukan untuk EMR ini',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail KKLP berhasil diambil',
                'data' => [
                    'form' => $kklp,
                    'pasien' => $kklp->pasien ? [
                        'id' => $kklp->pasien->id,
                        'nama_pasien' => $kklp->pasien->nama_pasien,
                        'alamat' => $kklp->pasien->alamat,
                        'tanggal_lahir' => $kklp->pasien->tanggal_lahir,
                        'jenis_kelamin' => $kklp->pasien->jenis_kelamin,
                        'no_emr' => $kklp->pasien->no_emr,
                        'nik' => $kklp->pasien->nik ?? null,
                        'no_bpjs' => $kklp->pasien->no_bpjs ?? null,
                        'no_hp_pasien' => $kklp->pasien->no_hp_pasien ?? null,
                        'pekerjaan' => $kklp->pasien->pekerjaan ?? null,
                        'agama' => $kklp->pasien->agama ?? null,
                        'pendidikan_terakhir' => $kklp->pasien->pendidikan_terakhir ?? null,
                        'suku_bangsa' => $kklp->pasien->suku_bangsa ?? null,
                        'foto_pasien' => $kklp->pasien->foto_pasien ?? null,
                    ] : null,
                    'kunjungan' => $kklp->kunjungan ? [
                        'id' => $kklp->kunjungan->id,
                        'tanggal_kunjungan' => $kklp->kunjungan->tanggal_kunjungan,
                        'no_antrian' => $kklp->kunjungan->no_antrian,
                        'status' => $kklp->kunjungan->status,
                        'keluhan_awal' => $kklp->kunjungan->keluhan_awal,
                    ] : null,
                    'poli' => $kklp->poli ? [
                        'id' => $kklp->poli->id,
                        'nama_poli' => $kklp->poli->nama_poli,
                    ] : null,
                    'dokter' => $kklp->dokter ? [
                        'id' => $kklp->dokter->id,
                        'nama_dokter' => $kklp->dokter->nama_dokter,
                        'foto_dokter' => $kklp->dokter->foto_dokter ?? null,
                    ] : null,
                    'emr' => $kklp->emr,
                    'orangtua' => $kklp->orangtua,
                    'heteroanamnesis' => $kklp->heteroanamnesis,
                    'family_apgars' => $kklp->familyApgar,
                    'family_screems' => $kklp->familyScreem,
                    'anggota_keluarga' => $kklp->anggotaKeluarga,
                    'homevisits' => $kklp->homevisit,
                    'family_plans' => $kklp->familyPlan,
                    'copc_plans' => $kklp->copcPlan,
                    'ekstremitas' => $kklp->ekstremitas,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('KklpController@detailByEmr error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail KKLP: '.$e->getMessage(),
            ], 500);
        }
    }
}