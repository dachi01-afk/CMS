<?php

namespace App\Traits;

trait FilterTanggalDataTable
{
    /**
     * Ubah keyword seperti "13 November 2026" menjadi "13 11 2026".
     * Return null kalau keyword bukan tanggal (misal nama pasien).
     */
    protected function normalisasiKeywordTanggal(string $keyword): ?array
    {
        $daftarBulan = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $tokens = preg_split('/[\s\/\-\.,]+/', strtolower(trim($keyword)), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($tokens)) {
            return null;
        }

        $hasil = [];
        $bulan = null;

        foreach ($tokens as $token) {
            if (ctype_digit($token)) {
                $hasil[] = strlen($token) === 1 ? '0' . $token : $token;
                continue;
            }

            if (strlen($token) >= 3) {
                foreach ($daftarBulan as $nama => $angka) {
                    if (str_starts_with($nama, $token)) {
                        $hasil[] = str_pad($angka, 2, '0', STR_PAD_LEFT);
                        $bulan = $angka;
                        continue 2;
                    }
                }
            }

            return null;
        }

        return [
            'teks'  => implode(' ', $hasil),
            'bulan' => count($tokens) === 1 ? $bulan : null,
        ];
    }

    /**
     * Terapkan filter tanggal ke query.
     * $kolom diisi oleh developer (bukan dari input user), contoh: 'order_layanan.tanggal_order'
     */
    protected function filterTanggal($query, string $keyword, string $kolom): void
    {
        $tanggal = $this->normalisasiKeywordTanggal($keyword);

        if (!$tanggal) {
            return;
        }

        if ($tanggal['bulan']) {
            $query->whereMonth($kolom, $tanggal['bulan']);
            return;
        }

        $query->where(function ($q) use ($tanggal, $kolom) {
            $q->whereRaw("DATE_FORMAT({$kolom}, '%d %m %Y') LIKE ?", ["%{$tanggal['teks']}%"])
              ->orWhereRaw("DATE_FORMAT({$kolom}, '%Y %m %d') LIKE ?", ["%{$tanggal['teks']}%"]);
        });
    }
}