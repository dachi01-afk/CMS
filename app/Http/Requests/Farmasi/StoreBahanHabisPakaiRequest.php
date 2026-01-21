<?php

namespace App\Http\Requests\Farmasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreBahanHabisPakaiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function parseRupiah()
    {
        $parse = fn($v) => $v ? (float) str_replace(['.', ','], ['', '.'], $v) : 0;

        $this->merge([
            'harga_beli_satuan_bhp' => $parse($this->harga_beli_satuan_bhp),
            'harga_jual_satuan_bhp' => $parse($this->harga_jual_satuan_bhp),
            'harga_otc_bhp' => $parse($this->harga_otc_bhp),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kode'             => ['nullable', 'string', 'max:255', 'unique:bahan_habis_pakai,kode'],
            'nama_barang'      => ['required', 'string', 'max:255'],
            'brand_farmasi_id' => ['nullable', 'exists:brand_farmasi,id'],
            'jenis_id'         => ['nullable', 'exists:jenis_obat,id'],
            'satuan_id'        => ['required', 'exists:satuan_obat,id'],
            'dosis'            => ['required', 'numeric', 'min:0'],
            'tanggal_kadaluarsa_bhp' => ['required', 'date'],
            'no_batch'         => ['required', 'string', 'max:255'],
            'stok_barang'      => ['nullable', 'integer', 'min:0'],
            'harga_beli_satuan_bhp'  => ['nullable', 'numeric', 'min:0'],
            'harga_jual_umum_bhp'    => ['nullable', 'numeric', 'min:0'],
            'harga_otc_bhp'          => ['nullable', 'numeric', 'min:0'],
            'depot_id'         => ['required', 'array', 'min:1'],
            'depot_id.*'       => ['required', 'distinct', 'exists:depot,id'],
            'stok_depot'       => ['required', 'array', 'min:1'],
            'stok_depot.*'     => ['required', 'integer', 'min:0'],
            'tipe_depot'       => ['nullable', 'array'],
            'tipe_depot.*'     => ['nullable', 'exists:tipe_depot,id'],
        ];
    }

    public function messages()
    {
        return [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'satuan_id.required'   => 'Satuan wajib dipilih.',
            'depot_id.required'    => 'Minimal 1 depot harus dipilih.',
        ];
    }
}
