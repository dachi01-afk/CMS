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
            'nama_obat'       => ['bail', 'required', 'string', 'max:255'],
            'harga_jual_obat' => ['bail', 'required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_obat.required' => 'Nama obat wajib diisi.',
            'nama_obat.string'   => 'Nama obat harus berupa teks.',
            'nama_obat.max'      => 'Nama obat maksimal 255 karakter.',

            'harga_jual_obat.required' => 'Harga jual obat wajib diisi.',
            'harga_jual_obat.numeric'  => 'Harga jual obat harus berupa angka.',
            'harga_jual_obat.min'      => 'Harga jual obat tidak boleh kurang dari 0.',
        ];
    }
}
