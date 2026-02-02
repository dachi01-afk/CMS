<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateObatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode'          => ['nullable', 'string', 'max:255'],
            'nama_obat'        => ['required', 'string', 'max:255'],
            'brand_farmasi_id' => ['nullable', 'exists:brand_farmasi,id'],
            'kategori_obat'    => ['required', 'exists:kategori_obat,id'],
            'jenis'            => ['nullable', 'exists:jenis_obat,id'],
            'satuan'           => ['required', 'exists:satuan_obat,id'],
            'dosis'            => ['required', 'numeric', 'min:0'],
            'kandungan'        => ['nullable', 'string', 'max:255'],
            'harga_beli_satuan' => ['nullable'],
            'harga_jual_umum'   => ['nullable'],
            'harga_otc'         => ['nullable'],
        ];
    }

    /**
     * Memproses data setelah validasi berhasil.
     */
    protected function passedValidation()
    {
        // Helper untuk membersihkan format Rupiah
        $parse = function ($value) {
            if (empty($value)) return 0;
            return (float) str_replace(['.', ','], ['', '.'], $value);
        };

        // Ganti data input dengan versi yang sudah di-parse
        $this->merge([
            'harga_beli_satuan' => $parse($this->harga_beli_satuan),
            'harga_jual_umum'   => $parse($this->harga_jual_umum),
            'harga_otc'         => $parse($this->harga_otc),
            // Jika barcode kosong, gunakan null agar tetap aman
            'kode_obat'         => $this->barcode ?: null,
        ]);
    }
}
