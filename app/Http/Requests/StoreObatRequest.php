<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreObatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Wajib diubah ke true agar request tidak ditolak
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
            'expired_date'     => ['required', 'date'],
            'nomor_batch'      => ['required', 'string', 'max:255'],
            'kandungan'        => ['nullable', 'string', 'max:255'],
            'stok_obat'        => ['required', 'integer', 'min:0'],
            'depot_id'         => ['required', 'array', 'min:1'],
            'depot_id.*'       => ['required', 'exists:depot,id', 'distinct'], // Tambahkan distinct agar tidak ada depot ganda
            'tipe_depot'       => ['nullable', 'array'],
            'stok_depot'       => ['required', 'array'],
        ];
    }

    // Custom message jika ingin bahasa Indonesia
    public function messages()
    {
        return [
            'depot_id.*.distinct' => 'Depot tidak boleh dipilih lebih dari satu kali.',
        ];
    }
}
