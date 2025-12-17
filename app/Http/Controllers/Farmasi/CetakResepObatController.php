<?php

namespace App\Http\Controllers\Farmasi;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Obat;

class CetakResepObatController extends Controller
{
    public function index()
    {
        return view('farmasi.cetak-resep-obat.cetak-resep-obat');
    }

    public function searchDataPasien(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        // Kalau query terlalu pendek, balikin kosong (biar gak berat)
        if (mb_strlen($q) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $limit = (int) $request->input('limit', 15);
        $limit = $limit > 0 ? min($limit, 30) : 15; // max 30 biar aman

        $data = Pasien::query()
            ->select([
                'id',
                'no_emr',
                'nik',
                'nama_pasien',
                'tanggal_lahir',
                'jenis_kelamin',
                'alamat',
                'no_hp_pasien',
            ])
            ->where(function ($w) use ($q) {
                $w->where('nama_pasien', 'like', "%{$q}%")
                    ->orWhere('no_emr', 'like', "%{$q}%")
                    ->orWhere('nik', 'like', "%{$q}%")
                    ->orWhere('no_hp_pasien', 'like', "%{$q}%");
            })
            ->orderBy('nama_pasien')
            ->limit($limit)
            ->get()
            ->map(function ($p) {
                // Hitung umur (tahun) kalau tanggal_lahir ada
                $umurTahun = null;
                if (!empty($p->tanggal_lahir)) {
                    $umurTahun = Carbon::parse($p->tanggal_lahir)->age;
                }

                $rm  = $p->no_emr ? $p->no_emr : '-';
                $nik = $p->nik ? $p->nik : '-';

                // text utama untuk TomSelect (yang tampil di item)
                $text = "{$rm} - {$p->nama_pasien}";

                // sub teks kecil (preview info)
                $subParts = [];
                if ($nik !== '-') $subParts[] = "NIK: {$nik}";
                if ($umurTahun !== null) $subParts[] = "Umur: {$umurTahun} th";
                if (!empty($p->jenis_kelamin)) $subParts[] = $p->jenis_kelamin;
                if (!empty($p->no_hp_pasien)) $subParts[] = "HP: {$p->no_hp_pasien}";

                $sub = implode(' • ', $subParts);

                return [
                    'id' => $p->id,
                    'text' => $text,
                    'sub' => $sub,

                    // optional: buat auto-fill form
                    'umur' => $umurTahun ? ($umurTahun . ' th') : null,
                    'alamat' => $p->alamat,
                    'no_emr' => $p->no_emr,
                    'nik' => $p->nik,
                    'jenis_kelamin' => $p->jenis_kelamin,
                    'no_hp_pasien' => $p->no_hp_pasien,
                    'tanggal_lahir' => $p->tanggal_lahir,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function searchDataDokter(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        // Minimal 2 karakter biar query gak berat
        if (mb_strlen($q) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $limit = (int) $request->input('limit', 15);
        $limit = $limit > 0 ? min($limit, 30) : 15;

        $dokter = Dokter::query()
            ->select([
                'dokter.id',
                'dokter.nama_dokter',
                'dokter.pengalaman',
                'dokter.no_hp',
                'dokter.jenis_spesialis_id',
            ])
            ->with([
                // Pastikan relasi ini ada di model Dokter:
                // public function poli(){ return $this->belongsToMany(Poli::class,'dokter_poli','dokter_id','poli_id'); }
                'poli:id,nama_poli'
            ])
            ->where(function ($w) use ($q) {
                $w->where('dokter.nama_dokter', 'like', "%{$q}%")
                    ->orWhere('dokter.pengalaman', 'like', "%{$q}%")
                    ->orWhere('dokter.no_hp', 'like', "%{$q}%")
                    ->orWhereHas('poli', function ($p) use ($q) {
                        $p->where('poli.nama_poli', 'like', "%{$q}%");
                    });
            })
            ->orderBy('dokter.nama_dokter')
            ->limit($limit)
            ->get()
            ->map(function ($d) {
                $poliNames = $d->poli?->pluck('nama_poli')->filter()->values()->all() ?? [];
                $poliText  = count($poliNames) ? implode(', ', $poliNames) : '-';

                $subParts = [];
                if ($poliText !== '-') $subParts[] = "Poli: {$poliText}";
                if (!empty($d->pengalaman)) $subParts[] = "Pengalaman: {$d->pengalaman}";
                if (!empty($d->no_hp)) $subParts[] = "HP: {$d->no_hp}";
                $sub = implode(' • ', $subParts);

                return [
                    'id' => $d->id,
                    'text' => $d->nama_dokter,
                    'sub' => $sub,

                    // optional: kalau mau dipakai autofill
                    'poli' => $poliNames, // array
                    'pengalaman' => $d->pengalaman,
                    'no_hp' => $d->no_hp,
                    'jenis_spesialis_id' => $d->jenis_spesialis_id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $dokter,
        ]);
    }

    public function searchDataObat(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        // minimal 2 char biar gak berat
        if (mb_strlen($q) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $limit = (int) $request->input('limit', 15);
        $limit = $limit > 0 ? min($limit, 30) : 15;

        $rows = Obat::query()
            ->select([
                'id',
                'kode_obat',
                'nama_obat',
                'jumlah',          // stok (sesuaikan jika nama kolom stok kamu beda)
                'satuan_obat_id',  // sesuaikan jika beda
            ])
            ->with(['satuanObat:id,nama_satuan_obat']) // pastikan relasi ada
            ->where(function ($w) use ($q) {
                $w->where('nama_obat', 'like', "%{$q}%")
                    ->orWhere('kode_obat', 'like', "%{$q}%");
            })
            ->orderBy('nama_obat')
            ->limit($limit)
            ->get()
            ->map(function ($o) {
                $kode   = $o->kode_obat ?: '-';
                $nama   = $o->nama_obat ?: '-';
                $stok   = (int) ($o->jumlah ?? 0);
                $satuan = optional($o->satuanObat)->nama_satuan_obat;

                // text utama yang tampil di input
                $text = ($kode !== '-' ? "{$kode} - {$nama}" : $nama);

                // sub kecil abu-abu
                $subParts = [];
                if ($satuan) $subParts[] = $satuan;
                $subParts[] = "Stok: {$stok}";
                $sub = implode(' • ', $subParts);

                return [
                    'id' => $o->id,
                    'text' => $text,
                    'sub' => $sub,

                    // optional untuk autofill per baris
                    'nama_obat' => $nama,
                    'kode_obat' => $o->kode_obat,
                    'stok' => $stok,
                    'satuan' => $satuan,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function printPreview(Request $request)
    {
        // =========================
        // Validasi ringan (opsional)
        // =========================
        $request->validate([
            'tipe_resep' => 'nullable|in:resep_dokter,resep_bebas',
            'pasien_id'  => 'nullable',
            'dokter_id'  => 'nullable',
            'obat'       => 'nullable|array',
        ]);

        // =========================
        // Normalisasi data obat
        // Biar aman kalau ada index yang beda2
        // =========================
        $obat = (array) $request->input('obat', []);

        $keys = ['obat_id', 'nama', 'jumlah', 'signatura', 'detur', 'is_iter', 'iter_jumlah'];

        // pastikan semua key ada dan berupa array
        foreach ($keys as $k) {
            if (!isset($obat[$k]) || !is_array($obat[$k])) {
                $obat[$k] = [];
            }
        }

        // samakan panjang berdasarkan obat_id
        $n = count($obat['obat_id']);

        foreach ($keys as $k) {
            $obat[$k] = array_slice(array_pad($obat[$k], $n, null), 0, $n);
        }

        // simpan kembali ke request supaya blade request('obat') dapat yang sudah rapi
        $request->merge(['obat' => $obat]);

        // =========================
        // View akan baca dari request()
        // =========================
        return view('farmasi.cetak-resep-obat.print-preview');
    }
}
