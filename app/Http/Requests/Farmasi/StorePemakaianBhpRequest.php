<?php

namespace App\Http\Requests\Farmasi;

use Illuminate\Foundation\Http\FormRequest;

class StorePemakaianBhpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bahan_habis_pakai_id' => ['required', 'exists:bahan_habis_pakai,id'],
            'depot_id' => ['required', 'exists:depot,id'],
            'jumlah_pemakaian' => ['required', 'integer', 'min:1'],
            'tanggal_pemakaian' => ['required', 'date'],
            'keterangan' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            'bahan_habis_pakai_id.required' => 'Barang harus dipilih.',
            'depot_id.required' => 'Depot harus dipilih.',
            'jumlah_pemakaian.min' => 'Jumlah pemakaian minimal 1 unit.',
            'jumlah_pemakaian.required' => 'Jumlah pemakaian harus di isi.',
        ];
    }
}
