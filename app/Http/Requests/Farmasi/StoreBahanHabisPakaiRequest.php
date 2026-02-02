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

    protected function prepareForValidation()
    {
        $parse = fn($v) => $v ? (float) str_replace(['.', ','], ['', '.'], $v) : 0;

        $this->merge([
            'harga_beli_satuan_bhp' => $parse($this->harga_beli_satuan_bhp),
            // Perhatikan nama field di rules adalah 'harga_jual_umum_bhp' 
            // tapi di parseRupiah kamu tulis 'harga_jual_satuan_bhp'. Samakan!
            'harga_jual_umum_bhp'   => $parse($this->harga_jual_umum_bhp),
            'harga_otc_bhp'         => $parse($this->harga_otc_bhp),
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
            'brand_farmasi_id' => ['required', 'exists:brand_farmasi,id'],
            'jenis_id'         => ['required', 'exists:jenis_obat,id'],
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
            'kode.unique'                       => 'Kode barang sudah ada.',
            'nama_barang.required'              => 'Nama barang wajib diisi.',
            'brand_farmasi_id.required'         => 'Nama Brand Farmasi wajib diisi.',
            'jenis_id.required'                 => 'Jenis barang wajib dipilih.',
            'satuan_id.required'                => 'Satuan barang wajib dipilih.',
            'dosis.required'                    => 'Dosis barang wajib diisi.',
            'tanggal_kadaluarsa_bhp.required'   => 'Tanggal kadaluarsa barang wajib diisi.',
            'no_batch.required'                 => 'Nomor Batch barang wajib diisi.',

            // Pesan untuk array depot_id
            'depot_id.required'                 => 'Minimal 1 depot harus dipilih.',
            'depot_id.min'                      => 'Minimal 1 depot harus dipilih.',
            'depot_id.*.distinct'               => 'Ada depot yang dipilih lebih dari satu kali (duplikat).',
            'depot_id.*.exists'                 => 'Data depot tidak valid.',

            'stok_depot.required'               => 'Stok depot wajib diisi.',
            'stok_depot.*.required'             => 'Setiap stok depot wajib diisi.',
            'stok_depot.*.integer'              => 'Stok harus berupa angka.',
        ];
    }
}
