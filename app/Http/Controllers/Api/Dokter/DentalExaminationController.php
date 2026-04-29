<?php

namespace App\Http\Controllers\Api\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DentalExamination;
use App\Models\Dokter;
use App\Models\EMR;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\OrderLayanan;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DentalExaminationController extends Controller
{
    /**
     * Helper: Get logged in dokter
     */
    private function dokterLogin(): Dokter
    {
        $userId = Auth::id();

        return Dokter::where('user_id', $userId)->firstOrFail();
    }

    private function validationRules(bool $isUpdate = false): array
    {
        return [
            'pasien_id' => ($isUpdate ? 'sometimes' : 'required').'|exists:pasien,id',
            'kunjungan_id' => ($isUpdate ? 'sometimes' : 'required').'|exists:kunjungan,id',
            'order_layanan_id' => 'nullable|exists:order_layanan,id',

            'tanggal_kunjungan' => 'nullable|date',
            'dpjp_nama' => 'nullable|string|max:255',
            'ppjp_nama' => 'nullable|string|max:255',

            'gigi_dewasa_atas' => 'nullable|array',
            'gigi_dewasa_bawah' => 'nullable|array',
            'gigi_anak_atas' => 'nullable|array',
            'gigi_anak_bawah' => 'nullable|array',

            'occlusi' => 'nullable|in:normal_bite,cross_bite,steep_bite',
            'torus_palatinus' => 'nullable|in:tidak_ada,kecil,sedang,besar,multiple',
            'torus_mandibularis' => 'nullable|in:tidak_ada,sisi_kiri,sisi_kanan,kedua_sisi',
            'palatum' => 'nullable|in:dalam,sedang,rendah',

            'diastema_ada' => 'nullable|boolean',
            'diastema_keterangan' => 'nullable|string',

            'gigi_anomali_ada' => 'nullable|boolean',
            'gigi_anomali_keterangan' => 'nullable|string',

            'lain_lain' => 'nullable|string',

            // TAMBAHAN: masuk tabel dental_examinations
            'terapi' => 'nullable|string',

            'd_index' => 'nullable|integer|min:0',
            'm_index' => 'nullable|integer|min:0',
            'f_index' => 'nullable|integer|min:0',

            'jumlah_foto' => 'nullable|integer|min:0',
            'jenis_foto' => 'nullable|string|max:255',
            'jumlah_rontgen' => 'nullable|integer|min:0',
            'jenis_rontgen' => 'nullable|string|max:255',

            'diperiksa_oleh' => 'nullable|string|max:255',
            'tanggal_pemeriksaan' => 'nullable|date',

            // VITAL SIGN + DIAGNOSIS - masuk tabel emr
            'tekanan_darah' => 'nullable|string|max:255',
            'suhu_tubuh' => 'nullable|string|max:255',
            'nadi' => 'nullable|string|max:255',
            'pernapasan' => 'nullable|string|max:255',
            'saturasi_oksigen' => 'nullable|string|max:255',
            'tinggi_badan' => 'nullable|string|max:255',
            'berat_badan' => 'nullable|string|max:255',
            'imt' => 'nullable|string|max:255',
            'diagnosis' => 'nullable|string',

            'status' => 'nullable|in:draft,completed,cancelled',

            // RESEP
            'resep' => 'nullable|array',
            'resep.*.obat_id' => 'required_with:resep|exists:obat,id',
            'resep.*.jumlah' => 'required_with:resep|integer|min:1',
            'resep.*.keterangan' => 'nullable|string',
            'resep.*.dosis' => 'nullable|numeric',

            // LAYANAN
            'layanan' => 'nullable|array',
            'layanan.*.layanan_id' => 'required_with:layanan|exists:layanan,id',
            'layanan.*.jumlah' => 'required_with:layanan|integer|min:1',

            // LAB
            'lab_tests' => 'nullable|array',
            'lab_tests.*.lab_test_id' => 'required_with:lab_tests|exists:jenis_pemeriksaan_lab,id',
            'lab_tests.*.tanggal_pemeriksaan' => 'nullable|date',
            'lab_tests.*.jam_pemeriksaan' => 'nullable|string',

            // RADIOLOGI
            'radiologi_tests' => 'nullable|array',
            'radiologi_tests.*.jenis_radiologi_id' => 'required_with:radiologi_tests|exists:jenis_pemeriksaan_radiologi,id',
            'radiologi_tests.*.tanggal_pemeriksaan' => 'nullable|date',
            'radiologi_tests.*.jam_pemeriksaan' => 'nullable|string',
        ];
    }

    /**
     * Ambil kunjungan dari kunjungan_id (utama)
     */
    private function findKunjunganById(?int $kunjunganId): ?Kunjungan
    {
        if (empty($kunjunganId)) {
            return null;
        }

        return Kunjungan::with(['emr', 'pasien'])->find($kunjunganId);
    }

    /**
     * Fallback ambil kunjungan dari order_layanan_id
     */
    private function findKunjunganFromOrderLayanan(?int $orderLayananId): ?Kunjungan
    {
        if (empty($orderLayananId)) {
            return null;
        }

        $order = OrderLayanan::find($orderLayananId);

        if (! $order) {
            return null;
        }

        if (! empty($order->kunjungan_id)) {
            return Kunjungan::with(['emr', 'pasien'])->find($order->kunjungan_id);
        }

        $query = Kunjungan::with(['emr', 'pasien'])
            ->where('pasien_id', $order->pasien_id ?? null);

        if (! empty($order->dokter_id)) {
            $query->where('dokter_id', $order->dokter_id);
        }

        if (! empty($order->poli_id)) {
            $query->where('poli_id', $order->poli_id);
        }

        return $query->latest('id')->first();
    }

    /**
     * Cari kunjungan dari request:
     * prioritas kunjungan_id, fallback order_layanan_id
     */
    private function resolveKunjunganFromRequest(Request $request): ?Kunjungan
    {
        $kunjungan = $this->findKunjunganById((int) $request->kunjungan_id);

        if (! $kunjungan && $request->filled('order_layanan_id')) {
            $kunjungan = $this->findKunjunganFromOrderLayanan((int) $request->order_layanan_id);
        }

        return $kunjungan;
    }

    /**
     * Sync field milik tabel emr dari form dental.
     * Jangan masukkan field-field ini ke tabel dental_examinations.
     */
    private function syncEmrVitalSign(Request $request, ?EMR $emr): void
    {
        if (! $emr) {
            return;
        }

        $payload = [];

        if ($request->has('tekanan_darah')) {
            $payload['tekanan_darah'] = $request->tekanan_darah;
        }

        if ($request->has('suhu_tubuh')) {
            $payload['suhu_tubuh'] = $request->suhu_tubuh;
        }

        if ($request->has('nadi')) {
            $payload['nadi'] = $request->nadi;
        }

        if ($request->has('pernapasan')) {
            $payload['pernapasan'] = $request->pernapasan;
        }

        if ($request->has('saturasi_oksigen')) {
            $payload['saturasi_oksigen'] = $request->saturasi_oksigen;
        }

        if ($request->has('tinggi_badan')) {
            $payload['tinggi_badan'] = $request->tinggi_badan;
        }

        if ($request->has('berat_badan')) {
            $payload['berat_badan'] = $request->berat_badan;
        }

        if ($request->has('imt')) {
            $payload['imt'] = $request->imt;
        }

        if ($request->has('diagnosis')) {
            $payload['diagnosis'] = $request->diagnosis;
        }

        if (! empty($payload)) {
            $emr->update($payload);
        }
    }

    /**
     * Backward compatibility:
     * GET /dokter/dental/form/{orderLayananId}
     */
    public function showForm($orderLayananId)
    {
        $kunjungan = $this->findKunjunganFromOrderLayanan((int) $orderLayananId);

        if (! $kunjungan) {
            return response()->json([
                'success' => true,
                'message' => 'Kunjungan tidak ditemukan',
                'data' => ['form' => null],
            ]);
        }

        $kunjungan->loadMissing(['emr.perawat', 'pasien', 'poli']);

        $form = DentalExamination::where('kunjungan_id', $kunjungan->id)->first();

        return response()->json([
            'success' => true,
            'message' => $form ? 'Form berhasil diambil' : 'Form belum diisi',
            'data' => [
                'form' => $form,
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
                'emr' => $kunjungan->emr ? [
                    'id' => $kunjungan->emr->id,
                    'keluhan_utama' => $kunjungan->emr->keluhan_utama,
                    'riwayat_penyakit_dahulu' => $kunjungan->emr->riwayat_penyakit_dahulu,
                    'riwayat_penyakit_keluarga' => $kunjungan->emr->riwayat_penyakit_keluarga,
                    'tekanan_darah' => $kunjungan->emr->tekanan_darah,
                    'suhu_tubuh' => $kunjungan->emr->suhu_tubuh,
                    'tinggi_badan' => $kunjungan->emr->tinggi_badan,
                    'berat_badan' => $kunjungan->emr->berat_badan,
                    'imt' => $kunjungan->emr->imt,
                    'nadi' => $kunjungan->emr->nadi,
                    'pernapasan' => $kunjungan->emr->pernapasan,
                    'saturasi_oksigen' => $kunjungan->emr->saturasi_oksigen,
                    'diagnosis' => $kunjungan->emr->diagnosis,
                ] : null,
                'perawat' => $kunjungan->emr && $kunjungan->emr->perawat ? [
                    'id' => $kunjungan->emr->perawat->id,
                    'nama_perawat' => $kunjungan->emr->perawat->nama_perawat,
                ] : null,
            ],
        ]);
    }

    /**
     * New standard route:
     * GET /dokter/dental/form/kunjungan/{kunjunganId}
     */
    public function showFormByKunjungan($kunjunganId)
    {
        $kunjungan = $this->findKunjunganById((int) $kunjunganId);

        if (! $kunjungan) {
            return response()->json([
                'success' => true,
                'message' => 'Kunjungan tidak ditemukan',
                'data' => ['form' => null],
            ]);
        }

        $kunjungan->loadMissing(['emr.perawat', 'pasien', 'poli']);

        $emr = $kunjungan->emr;

        $form = DentalExamination::where('kunjungan_id', $kunjungan->id)
            ->orderByDesc('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | LAYANAN EXISTING
        |--------------------------------------------------------------------------
        */
        $layanan = DB::table('kunjungan_layanan as kl')
            ->leftJoin('layanan as l', 'l.id', '=', 'kl.layanan_id')
            ->where('kl.kunjungan_id', $kunjungan->id)
            ->select(
                'kl.id',
                'kl.layanan_id',
                'kl.jumlah',
                'l.nama_layanan',
                'l.harga_sebelum_diskon',
                'l.harga_setelah_diskon'
            )
            ->get()
            ->map(function ($row) {
                $harga = $row->harga_setelah_diskon ?? $row->harga_sebelum_diskon ?? 0;

                return [
                    'id' => $row->id,
                    'layanan_id' => $row->layanan_id,
                    'nama_layanan' => $row->nama_layanan,
                    'jumlah' => (int) ($row->jumlah ?? 1),
                    'harga_layanan' => $harga,
                    'harga_layanan_raw' => $harga,
                ];
            })
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | RESEP EXISTING
        |--------------------------------------------------------------------------
        */
        $resep = [];

        $resepId = $emr?->resep_id;

        if (! $resepId) {
            $resepId = DB::table('resep')
                ->where('kunjungan_id', $kunjungan->id)
                ->orderByDesc('id')
                ->value('id');
        }

        if ($resepId) {
            $resep = DB::table('resep_obat as ro')
                ->leftJoin('obat as o', 'o.id', '=', 'ro.obat_id')
                ->where('ro.resep_id', $resepId)
                ->select(
                    'ro.id',
                    'ro.resep_id',
                    'ro.obat_id',
                    'ro.jumlah',
                    'ro.dosis',
                    'ro.keterangan',
                    'o.nama_obat',
                    'o.harga_jual_obat'
                )
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'resep_id' => $row->resep_id,
                        'obat_id' => $row->obat_id,
                        'nama_obat' => $row->nama_obat,
                        'jumlah' => (int) ($row->jumlah ?? 1),
                        'dosis' => $row->dosis,
                        'keterangan' => $row->keterangan,
                        'harga_obat' => $row->harga_jual_obat ?? 0,
                    ];
                })
                ->values()
                ->toArray();
        }

        /*
        |--------------------------------------------------------------------------
        | LAB EXISTING
        |--------------------------------------------------------------------------
        */
        $labTests = [];

        $orderLab = DB::table('order_lab')
            ->where('kunjungan_id', $kunjungan->id)
            ->orderByDesc('id')
            ->first();

        if ($orderLab) {
            $labTests = DB::table('order_lab_detail as old')
                ->leftJoin('jenis_pemeriksaan_lab as jpl', 'jpl.id', '=', 'old.jenis_pemeriksaan_lab_id')
                ->where('old.order_lab_id', $orderLab->id)
                ->select(
                    'old.id',
                    'old.jenis_pemeriksaan_lab_id',
                    'old.status_pemeriksaan',
                    'jpl.nama_pemeriksaan',
                    'jpl.nilai_normal',
                    'jpl.harga_pemeriksaan_lab'
                )
                ->get()
                ->map(function ($row) use ($orderLab) {
                    return [
                        'id' => $row->id,
                        'lab_test_id' => $row->jenis_pemeriksaan_lab_id,
                        'jenis_pemeriksaan_lab_id' => $row->jenis_pemeriksaan_lab_id,
                        'nama_pemeriksaan' => $row->nama_pemeriksaan,
                        'lab_test_nama' => $row->nama_pemeriksaan,
                        'nilai_normal' => $row->nilai_normal,
                        'harga' => $row->harga_pemeriksaan_lab ?? 0,
                        'tanggal_pemeriksaan' => $orderLab->tanggal_pemeriksaan,
                        'jam_pemeriksaan' => $orderLab->jam_pemeriksaan,
                        'tanggal_kunjungan_terjadwal' => $orderLab->tanggal_pemeriksaan,
                        'jam_kunjungan_terjadwal' => $orderLab->jam_pemeriksaan,
                        'status' => $row->status_pemeriksaan,
                    ];
                })
                ->values()
                ->toArray();
        }

        /*
        |--------------------------------------------------------------------------
        | RADIOLOGI EXISTING
        |--------------------------------------------------------------------------
        */
        $radiologiTests = [];

        $orderRadiologi = DB::table('order_radiologi')
            ->where('kunjungan_id', $kunjungan->id)
            ->orderByDesc('id')
            ->first();

        if ($orderRadiologi) {
            $radiologiTests = DB::table('order_radiologi_detail as ord')
                ->leftJoin(
                    'jenis_pemeriksaan_radiologi as jpr',
                    'jpr.id',
                    '=',
                    'ord.jenis_pemeriksaan_radiologi_id'
                )
                ->where('ord.order_radiologi_id', $orderRadiologi->id)
                ->select(
                    'ord.id',
                    'ord.jenis_pemeriksaan_radiologi_id',
                    'ord.status_pemeriksaan',
                    'jpr.kode_pemeriksaan',
                    'jpr.nama_pemeriksaan',
                    'jpr.harga_pemeriksaan_radiologi'
                )
                ->get()
                ->map(function ($row) use ($orderRadiologi) {
                    return [
                        'id' => $row->id,
                        'jenis_radiologi_id' => $row->jenis_pemeriksaan_radiologi_id,
                        'jenis_pemeriksaan_radiologi_id' => $row->jenis_pemeriksaan_radiologi_id,
                        'kode_pemeriksaan' => $row->kode_pemeriksaan,
                        'nama_pemeriksaan' => $row->nama_pemeriksaan,
                        'jenis_radiologi_nama' => $row->nama_pemeriksaan,

                        // Tidak ada kolom deskripsi di tabel jenis_pemeriksaan_radiologi.
                        'deskripsi' => null,

                        'harga' => $row->harga_pemeriksaan_radiologi ?? 0,
                        'tanggal_pemeriksaan' => $orderRadiologi->tanggal_pemeriksaan,
                        'jam_pemeriksaan' => $orderRadiologi->jam_pemeriksaan,
                        'tanggal_kunjungan_terjadwal' => $orderRadiologi->tanggal_pemeriksaan,
                        'jam_kunjungan_terjadwal' => $orderRadiologi->jam_pemeriksaan,
                        'status' => $row->status_pemeriksaan,
                    ];
                })
                ->values()
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'message' => $form ? 'Form berhasil diambil' : 'Form belum diisi',
            'data' => [
                'form' => $form,

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

                'emr' => $emr ? [
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
                ] : null,

                'perawat' => $emr && $emr->perawat ? [
                    'id' => $emr->perawat->id,
                    'nama_perawat' => $emr->perawat->nama_perawat,
                ] : null,

                'layanan' => $layanan,
                'resep' => $resep,
                'resep_obat' => $resep,
                'lab_tests' => $labTests,
                'radiologi_tests' => $radiologiTests,
            ],
        ]);
    }

    private function rebuildPembayaranDental(
        ?EMR $emr,
        Kunjungan $kunjungan,
        ?int $resepId,
        ?int $orderLabId,
        ?int $orderRadiologiId
    ): ?Pembayaran {
        if (! $emr) {
            return null;
        }

        $existingPembayaran = Pembayaran::where('emr_id', $emr->id)->first();

        $pembayaran = Pembayaran::updateOrCreate(
            ['emr_id' => $emr->id],
            [
                'kode_transaksi' => $existingPembayaran?->kode_transaksi ?? strtoupper(uniqid('TRX_')),
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
                'catatan' => 'Menunggu pembayaran di kasir - Pemeriksaan gigi selesai',
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
                'layanan_id' => $data['layanan_id'] ?? null,
                'resep_obat_id' => $data['resep_obat_id'] ?? null,
                'order_lab_detail_id' => $data['order_lab_detail_id'] ?? null,
                'order_radiologi_detail_id' => $data['order_radiologi_detail_id'] ?? null,
            ];

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

            $insertDetail([
                'layanan_id' => $row->layanan_id,
                'nama_item' => 'Layanan: '.$row->nama_layanan,
                'qty' => $qty,
                'harga' => $harga,
                'subtotal' => $harga * $qty,
            ]);
        }

        if (! empty($resepId)) {
            $obatRows = DB::table('resep_obat as ro')
                ->join('obat as o', 'o.id', '=', 'ro.obat_id')
                ->where('ro.resep_id', $resepId)
                ->select('ro.id as resep_obat_id', 'ro.jumlah', 'o.nama_obat', 'o.harga_jual_obat')
                ->get();

            foreach ($obatRows as $row) {
                $harga = (float) ($row->harga_jual_obat ?? 0);
                $qty = (int) ($row->jumlah ?? 1);

                $insertDetail([
                    'resep_obat_id' => $row->resep_obat_id,
                    'nama_item' => 'Obat: '.$row->nama_obat,
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $harga * $qty,
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

                $insertDetail([
                    'order_lab_detail_id' => $row->order_lab_detail_id,
                    'nama_item' => 'Lab: '.$row->nama_pemeriksaan,
                    'qty' => 1,
                    'harga' => $harga,
                    'subtotal' => $harga,
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

                $insertDetail([
                    'order_radiologi_detail_id' => $row->order_radiologi_detail_id,
                    'nama_item' => 'Radiologi: '.$row->nama_pemeriksaan,
                    'qty' => 1,
                    'harga' => $harga,
                    'subtotal' => $harga,
                ]);
            }
        }

        $pembayaran->update([
            'total_tagihan' => $total,
        ]);

        return $pembayaran->fresh();
    }

    private function syncDentalSupportData(Request $request, Kunjungan $kunjungan, Dokter $dokter, ?EMR $emr = null): array
    {
        $resepId = $emr?->resep_id;
        $orderLabId = null;
        $orderRadiologiId = null;

        // 1) RESEP + RESEP_OBAT
        if (! empty($request->resep)) {
            if (! $resepId) {
                $resepId = DB::table('resep')->insertGetId([
                    'kunjungan_id' => $kunjungan->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('resep')->where('id', $resepId)->update([
                    'kunjungan_id' => $kunjungan->id,
                    'updated_at' => now(),
                ]);
            }

            DB::table('resep_obat')->where('resep_id', $resepId)->delete();

            foreach ($request->resep as $item) {
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
        }

        // 2) LAYANAN
        DB::table('kunjungan_layanan')->where('kunjungan_id', $kunjungan->id)->delete();

        if (! empty($request->layanan)) {
            foreach ($request->layanan as $layananItem) {
                DB::table('kunjungan_layanan')->insert([
                    'kunjungan_id' => $kunjungan->id,
                    'layanan_id' => $layananItem['layanan_id'],
                    'jumlah' => (int) $layananItem['jumlah'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3) LAB
        if (! empty($request->lab_tests)) {
            $existing = DB::table('order_lab')
                ->where('kunjungan_id', $kunjungan->id)
                ->whereIn('status', ['Pending', 'Diproses'])
                ->orderByDesc('id')
                ->first();

            if ($existing) {
                $orderLabId = $existing->id;

                DB::table('order_lab_detail')->where('order_lab_id', $orderLabId)->delete();

                $firstLabTest = $request->lab_tests[0];
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

                $firstLabTest = $request->lab_tests[0];
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

            foreach ($request->lab_tests as $labTest) {
                DB::table('order_lab_detail')->insert([
                    'order_lab_id' => $orderLabId,
                    'jenis_pemeriksaan_lab_id' => $labTest['lab_test_id'],
                    'status_pemeriksaan' => 'Pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4) RADIOLOGI
        if (! empty($request->radiologi_tests)) {
            $existingRad = DB::table('order_radiologi')
                ->where('kunjungan_id', $kunjungan->id)
                ->whereIn('status', ['Pending', 'Diproses'])
                ->orderByDesc('id')
                ->first();

            if ($existingRad) {
                $orderRadiologiId = $existingRad->id;

                DB::table('order_radiologi_detail')->where('order_radiologi_id', $orderRadiologiId)->delete();

                $firstRadiologiTest = $request->radiologi_tests[0];
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

                $firstRadiologiTest = $request->radiologi_tests[0];
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

            foreach ($request->radiologi_tests as $rad) {
                DB::table('order_radiologi_detail')->insert([
                    'order_radiologi_id' => $orderRadiologiId,
                    'jenis_pemeriksaan_radiologi_id' => $rad['jenis_radiologi_id'],
                    'status_pemeriksaan' => 'Pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($emr && $resepId) {
            $emr->update([
                'resep_id' => $resepId,
            ]);
        }

        return [
            'resep_id' => $resepId,
            'order_lab_id' => $orderLabId,
            'order_radiologi_id' => $orderRadiologiId,
        ];
    }

    public function save(Request $request)
    {
        try {
            $request->validate($this->validationRules(false));

            $dokter = $this->dokterLogin();

            $result = DB::transaction(function () use ($request, $dokter) {
                $kunjungan = $this->resolveKunjunganFromRequest($request);

                if (! $kunjungan) {
                    abort(response()->json([
                        'success' => false,
                        'message' => 'Kunjungan tidak ditemukan',
                    ], 404));
                }

                $emr = $kunjungan->emr;

                $dental = DentalExamination::updateOrCreate(
                    [
                        'kunjungan_id' => $kunjungan->id,
                        'pasien_id' => $kunjungan->pasien_id,
                    ],
                    [
                        'pasien_id' => $kunjungan->pasien_id,
                        'kunjungan_id' => $kunjungan->id,
                        'order_layanan_id' => $request->order_layanan_id,
                        'tanggal_kunjungan' => $request->tanggal_kunjungan ?? $kunjungan->tanggal_kunjungan,
                        'dpjp_nama' => $request->dpjp_nama,
                        'ppjp_nama' => $request->ppjp_nama,
                        'gigi_dewasa_atas' => $request->gigi_dewasa_atas,
                        'gigi_dewasa_bawah' => $request->gigi_dewasa_bawah,
                        'gigi_anak_atas' => $request->gigi_anak_atas,
                        'gigi_anak_bawah' => $request->gigi_anak_bawah,
                        'occlusi' => $request->occlusi,
                        'torus_palatinus' => $request->torus_palatinus,
                        'torus_mandibularis' => $request->torus_mandibularis,
                        'palatum' => $request->palatum,
                        'diastema_ada' => $request->diastema_ada,
                        'diastema_keterangan' => $request->diastema_keterangan,
                        'gigi_anomali_ada' => $request->gigi_anomali_ada,
                        'gigi_anomali_keterangan' => $request->gigi_anomali_keterangan,
                        'lain_lain' => $request->lain_lain,

                        // TAMBAHAN: masuk tabel dental_examinations
                        'terapi' => $request->terapi,

                        'd_index' => $request->d_index,
                        'm_index' => $request->m_index,
                        'f_index' => $request->f_index,
                        'jumlah_foto' => $request->jumlah_foto,
                        'jenis_foto' => $request->jenis_foto,
                        'jumlah_rontgen' => $request->jumlah_rontgen,
                        'jenis_rontgen' => $request->jenis_rontgen,
                        'diperiksa_oleh' => $request->diperiksa_oleh,
                        'tanggal_pemeriksaan' => $request->tanggal_pemeriksaan,
                        'status' => $request->status ?? 'draft',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]
                );

                // Field ini masuk tabel emr, bukan dental_examinations
                $this->syncEmrVitalSign($request, $emr);

                $sync = [
                    'resep_id' => null,
                    'order_lab_id' => null,
                    'order_radiologi_id' => null,
                ];

                $sync = $this->syncDentalSupportData($request, $kunjungan, $dokter, $emr);

                if (($request->status ?? 'draft') === 'completed') {
                    $kunjungan->update(['status' => 'Payment']);
                }

                $pembayaran = null;

                if (($request->status ?? 'draft') === 'completed') {
                    $pembayaran = $this->rebuildPembayaranDental(
                        $emr,
                        $kunjungan,
                        $sync['resep_id'],
                        $sync['order_lab_id'],
                        $sync['order_radiologi_id']
                    );
                }

                return [
                    'dental' => $dental->fresh(),
                    'kunjungan' => $kunjungan->fresh(),
                    'pembayaran' => $pembayaran,
                    'resep_id' => $sync['resep_id'],
                    'order_lab_id' => $sync['order_lab_id'],
                    'order_radiologi_id' => $sync['order_radiologi_id'],
                ];
            });

            return response()->json([
                'success' => true,
                'message' => $request->status === 'completed'
                    ? 'Pemeriksaan gigi telah difinalisasi dan billing berhasil dibuat'
                    : 'Draft pemeriksaan gigi berhasil disimpan',
                'data' => $result,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('DentalExaminationController@save error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pemeriksaan gigi: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $dentalExam = DentalExamination::findOrFail($id);
            $dokter = $this->dokterLogin();

            $request->validate($this->validationRules(true));

            $result = DB::transaction(function () use ($request, $dentalExam, $dokter) {
                // Jangan masukkan field emr/support ke tabel dental_examinations
                $payload = $request->except([
                    '_token',
                    '_method',

                    // Field tabel emr
                    'tekanan_darah',
                    'suhu_tubuh',
                    'nadi',
                    'pernapasan',
                    'saturasi_oksigen',
                    'tinggi_badan',
                    'berat_badan',
                    'imt',
                    'diagnosis',

                    // Data support, diproses terpisah
                    'resep',
                    'layanan',
                    'lab_tests',
                    'radiologi_tests',
                ]);

                $payload['updated_by'] = Auth::id();

                $kunjungan = null;

                if ($request->filled('kunjungan_id')) {
                    $kunjungan = $this->findKunjunganById((int) $request->kunjungan_id);
                }

                if (! $kunjungan && ! empty($dentalExam->kunjungan_id)) {
                    $kunjungan = $this->findKunjunganById((int) $dentalExam->kunjungan_id);
                }

                if (! $kunjungan && ($request->filled('order_layanan_id') || ! empty($dentalExam->order_layanan_id))) {
                    $kunjungan = $this->findKunjunganFromOrderLayanan(
                        (int) ($request->order_layanan_id ?? $dentalExam->order_layanan_id)
                    );
                }

                if ($kunjungan) {
                    $payload['kunjungan_id'] = $kunjungan->id;
                    $payload['pasien_id'] = $payload['pasien_id'] ?? $kunjungan->pasien_id;
                }

                // terapi akan ikut masuk dari $payload ke dental_examinations
                $dentalExam->update($payload);

                $emr = $kunjungan?->emr;

                // Field ini masuk tabel emr, bukan dental_examinations
                $this->syncEmrVitalSign($request, $emr);

                $sync = [
                    'resep_id' => null,
                    'order_lab_id' => null,
                    'order_radiologi_id' => null,
                ];

                if ($kunjungan) {
                    $sync = $this->syncDentalSupportData($request, $kunjungan, $dokter, $emr);

                    if (($request->status ?? $dentalExam->status) === 'completed') {
                        $kunjungan->update(['status' => 'Payment']);
                    }
                }

                $pembayaran = null;

                if ($kunjungan && (($request->status ?? $dentalExam->status) === 'completed')) {
                    $pembayaran = $this->rebuildPembayaranDental(
                        $emr,
                        $kunjungan,
                        $sync['resep_id'],
                        $sync['order_lab_id'],
                        $sync['order_radiologi_id']
                    );
                }

                return [
                    'dental' => $dentalExam->fresh(['pasien', 'kunjungan']),
                    'kunjungan' => $kunjungan?->fresh(),
                    'pembayaran' => $pembayaran,
                    'resep_id' => $sync['resep_id'],
                    'order_lab_id' => $sync['order_lab_id'],
                    'order_radiologi_id' => $sync['order_radiologi_id'],
                ];
            });

            return response()->json([
                'success' => true,
                'message' => (($request->status ?? $dentalExam->status) === 'completed')
                    ? 'Pemeriksaan gigi berhasil difinalisasi'
                    : 'Form pemeriksaan gigi berhasil diperbarui',
                'data' => $result,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('DentalExaminationController@update error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui form pemeriksaan gigi: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $dentalExam = DentalExamination::with([
                'pasien',
                'kunjungan',
            ])->find($id);

            if (! $dentalExam) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pemeriksaan gigi tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail pemeriksaan gigi berhasil diambil',
                'data' => $dentalExam,
            ]);
        } catch (\Throwable $e) {
            Log::error('DentalExaminationController@show error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pemeriksaan gigi: '.$e->getMessage(),
            ], 500);
        }
    }

    public function riwayatPasien(Request $request)
    {
        try {
            $dokter = $this->dokterLogin();

            $query = DentalExamination::with(['pasien', 'kunjungan'])
                ->whereHas('kunjungan', function ($q) use ($dokter) {
                    $q->where('dokter_id', $dokter->id);
                })
                ->latest();

            if ($request->filled('pasien_id')) {
                $query->where('pasien_id', $request->pasien_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $riwayat = $query->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil riwayat pemeriksaan gigi',
                'data' => $riwayat,
            ]);
        } catch (\Throwable $e) {
            Log::error('DentalExaminationController@riwayatPasien error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pemeriksaan gigi: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $dentalExam = DentalExamination::findOrFail($id);

            $dentalExam->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data pemeriksaan gigi berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            Log::error('DentalExaminationController@destroy error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pemeriksaan gigi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get dental examination by EMR ID (untuk riwayat pasien)
     */
    public function showByEmr($emrId)
    {
        try {
            $dentalExam = DentalExamination::with(['pasien', 'kunjungan', 'orderLayanan'])
                ->whereHas('kunjungan', function ($q) use ($emrId) {
                    $q->whereHas('emr', function ($q2) use ($emrId) {
                        $q2->where('id', $emrId);
                    });
                })
                ->first();

            if (! $dentalExam) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pemeriksaan gigi tidak ditemukan untuk EMR ini',
                    'data' => null,
                ], 404);
            }

            $emr = EMR::with('perawat')->find($emrId);

            $data = [
                'id' => $dentalExam->id,
                'pasien_id' => $dentalExam->pasien_id,
                'kunjungan_id' => $dentalExam->kunjungan_id,
                'order_layanan_id' => $dentalExam->order_layanan_id,
                'tanggal_kunjungan' => $dentalExam->tanggal_kunjungan,
                'dpjp_nama' => $dentalExam->dpjp_nama,
                'ppjp_nama' => $dentalExam->ppjp_nama,
                'status' => $dentalExam->status,
                'diperiksa_oleh' => $dentalExam->diperiksa_oleh,
                'tanggal_pemeriksaan' => $dentalExam->tanggal_pemeriksaan,

                'gigi_dewasa_atas' => $this->normalizeOdontogram($dentalExam->gigi_dewasa_atas),
                'gigi_dewasa_bawah' => $this->normalizeOdontogram($dentalExam->gigi_dewasa_bawah),
                'gigi_anak_atas' => $this->normalizeOdontogram($dentalExam->gigi_anak_atas),
                'gigi_anak_bawah' => $this->normalizeOdontogram($dentalExam->gigi_anak_bawah),

                'occlusi' => $dentalExam->occlusi,
                'torus_palatinus' => $dentalExam->torus_palatinus,
                'torus_mandibularis' => $dentalExam->torus_mandibularis,
                'palatum' => $dentalExam->palatum,
                'diastema_ada' => $dentalExam->diastema_ada,
                'diastema_keterangan' => $dentalExam->diastema_keterangan,
                'gigi_anomali_ada' => $dentalExam->gigi_anomali_ada,
                'gigi_anomali_keterangan' => $dentalExam->gigi_anomali_keterangan,
                'lain_lain' => $dentalExam->lain_lain,

                // TAMBAHAN: terapi dari tabel dental_examinations
                'terapi' => $dentalExam->terapi,

                'd_index' => $dentalExam->d_index,
                'm_index' => $dentalExam->m_index,
                'f_index' => $dentalExam->f_index,

                'jumlah_foto' => $dentalExam->jumlah_foto,
                'jenis_foto' => $dentalExam->jenis_foto,
                'jumlah_rontgen' => $dentalExam->jumlah_rontgen,
                'jenis_rontgen' => $dentalExam->jenis_rontgen,

                'emr' => $emr ? [
                    'id' => $emr->id,
                    'tekanan_darah' => $emr->tekanan_darah,
                    'suhu_tubuh' => $emr->suhu_tubuh,
                    'tinggi_badan' => $emr->tinggi_badan,
                    'berat_badan' => $emr->berat_badan,
                    'imt' => $emr->imt,
                    'nadi' => $emr->nadi,
                    'pernapasan' => $emr->pernapasan,
                    'saturasi_oksigen' => $emr->saturasi_oksigen,
                    'diagnosis' => $emr->diagnosis,
                ] : null,

                'perawat' => $emr && $emr->perawat ? [
                    'id' => $emr->perawat->id,
                    'nama_perawat' => $emr->perawat->nama_perawat,
                ] : null,

                'pasien' => $dentalExam->pasien,
                'kunjungan' => $dentalExam->kunjungan,
                'order_layanan' => $dentalExam->orderLayanan,

                'created_at' => $dentalExam->created_at,
                'updated_at' => $dentalExam->updated_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail pemeriksaan gigi berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('DentalExaminationController@showByEmr error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pemeriksaan gigi: '.$e->getMessage(),
            ], 500);
        }
    }

    private function normalizeOdontogram($raw): array
    {
        if (empty($raw)) {
            return [];
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if ($raw instanceof \Illuminate\Support\Collection) {
            $raw = $raw->toArray();
        }

        if (is_object($raw)) {
            $raw = (array) $raw;
        }

        if (! is_array($raw)) {
            return [];
        }

        $result = [];

        foreach ($raw as $kodeGigi => $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }

            if (is_array($item)) {
                $result[$kodeGigi] = [
                    'status' => $item['status']
                        ?? $item['label']
                        ?? $item['kondisi']
                        ?? $item['value']
                        ?? '-',
                    'keterangan' => $item['keterangan']
                        ?? $item['catatan']
                        ?? $item['note']
                        ?? '-',
                ];
            } else {
                $result[$kodeGigi] = [
                    'status' => is_scalar($item) ? (string) $item : '-',
                    'keterangan' => '-',
                ];
            }
        }

        return $result;
    }
}
