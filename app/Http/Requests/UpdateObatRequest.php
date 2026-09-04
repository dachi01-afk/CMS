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
            'nama_obat' => ['bail', 'required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_obat.required' => 'Nama obat wajib diisi.',
            'nama_obat.string'   => 'Nama obat harus berupa teks.',
            'nama_obat.max'      => 'Nama obat maksimal 255 karakter.',
        ];
    }
}
