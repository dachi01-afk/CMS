<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function getDataSupplier(Request $request)
    {
        $search = $request->q; // TomSelect kirim "q" sebagai keyword search

        $query = Supplier::query()->where('is_active', 1);

        if ($search) {
            $query->where('nama_supplier', 'like', '%' . $search . '%');
        }

        // batasin biar ringan
        $data = $query->orderBy('nama_supplier')->limit(20)->get();

        return response()->json($data);
    }

    public function createDataSupplier(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier'  => ['required', 'string', 'max:255', 'unique:supplier,nama_supplier'],
            'kontak_person'  => ['nullable', 'string', 'max:255'],
            'no_hp'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:255'],
            'alamat'         => ['nullable', 'string'],
            'keterangan'     => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create([
            'nama_supplier' => $validated['nama_supplier'],
            'kontak_person' => $validated['kontak_person'] ?? null,
            'no_hp'         => $validated['no_hp'] ?? null,
            'email'         => $validated['email'] ?? null,
            'alamat'        => $validated['alamat'] ?? null,
            'keterangan'    => $validated['keterangan'] ?? null,
            'is_active'     => true,
        ]);

        return response()->json($supplier, 201);
    }

    public function deleteDataSupplier(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:supplier,id'],
        ]);

        $supplier = Supplier::findOrFail($request->id);

        // Kalau sudah dipakai di transaksi stok, jangan dihapus
        // Pastikan relasi ini ada di model Supplier:
        // public function stokTransaksi(){ return $this->hasMany(StokTransaksi::class,'supplier_id'); }
        if (method_exists($supplier, 'stokTransaksi') && $supplier->stokTransaksi()->exists()) {
            return response()->json([
                'message' => 'Supplier sudah dipakai di transaksi, tidak dapat dihapus.',
            ], 422);
        }

        // Alternatif lebih aman: soft delete via is_active
        $supplier->update(['is_active' => false]);

        return response()->json([
            'message' => 'Supplier berhasil dinonaktifkan.',
        ]);
    }

    public function showDataSupplier($id)
    {
        $supplier = Supplier::findOrFail($id);

        return response()->json($supplier);
    }

    public function updateDataSupplier(Request $request)
    {
        $validated = $request->validate([
            'id'            => ['required', 'integer', 'exists:supplier,id'],
            'kontak_person' => ['nullable', 'string', 'max:255'],
            'no_hp'         => ['nullable', 'string', 'max:30'],
            'email'         => ['nullable', 'email', 'max:255'],
            'alamat'        => ['nullable', 'string'],
            'keterangan'    => ['nullable', 'string'],
        ]);

        $supplier = Supplier::findOrFail($validated['id']);

        $supplier->update([
            'kontak_person' => $validated['kontak_person'] ?? null,
            'no_hp'         => $validated['no_hp'] ?? null,
            'email'         => $validated['email'] ?? null,
            'alamat'        => $validated['alamat'] ?? null,
            'keterangan'    => $validated['keterangan'] ?? null,
        ]);

        return response()->json($supplier);
    }
}
