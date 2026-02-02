<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateBahanHabisPakaiRequest extends FormRequest
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
            // kode boleh divalidasi agar input aman, tapi TIDAK dipakai untuk update
            'kode'                   => ['nullable', 'string', 'max:255'],

            'nama_barang'            => ['required', 'string', 'max:255'],
            'brand_farmasi_id'       => ['nullable', 'exists:brand_farmasi,id'],
            'jenis_id'               => ['nullable', 'exists:jenis_obat,id'],
            'satuan_id'              => ['required', 'exists:satuan_obat,id'],

            'dosis'                  => ['required', 'numeric', 'min:0'],

            // stok_barang tidak lagi jadi sumber utama, tapi biarkan validasi kalau form kamu masih kirim
            'stok_barang'            => ['nullable', 'integer', 'min:0'],

            'harga_beli_satuan_bhp'  => ['nullable'],
            'harga_jual_umum_bhp'    => ['nullable'],
            'harga_otc_bhp'          => ['nullable'],

            // array depot
            'depot_id'               => ['nullable', 'array'],
            'depot_id.*'             => ['nullable', 'exists:depot,id'],

            'stok_depot'             => ['nullable', 'array'],
            'stok_depot.*'           => ['nullable', 'integer', 'min:0'],

            'tipe_depot'             => ['nullable', 'array'],
            'tipe_depot.*'           => ['nullable', 'exists:tipe_depot,id'],
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
