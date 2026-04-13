<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DokterPoli;
use App\Models\Perawat;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Services\DataTable;

class ManagementPerawatController extends Controller
{
    public function getDataPerawat()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'Super Admin';

        $dataPerawat = Perawat::with(['user']);

        return DataTables::of($dataPerawat)
            ->addIndexColumn()
            ->editColumn('nama', function ($data) {
                return $data->nama_perawat ?? '-';
            })
            ->editColumn('username', function ($data) {
                return $data->user->username ?? '-';
            })
            ->editColumn('email', function ($data) {
                return $data->user->email ?? '-';
            })
            ->editColumn('role', function ($data) {
                return $data->user->role ?? '-';
            })
            ->editColumn('no_hp', function ($data) {
                return $data->no_hp_perawat ?? '-';
            })
            ->editColumn('foto', function (Perawat $row) {
                if (!empty($row->foto_perawat)) {
                    $url = asset('storage/' . $row->foto_perawat);
                    return '
                    <div class="flex items-center justify-center">
                        <img src="' . $url . '" alt="Foto Perawat"
                             class="w-10 h-10 md:w-12 md:h-12 rounded-xl object-cover shadow" />
                    </div>
                ';
                }

                return '<span class="text-gray-400 italic text-xs">Tidak ada</span>';
            })
            ->addColumn('action', function ($data) use ($isSuperAdmin) {
                $url = route('get.data.detail.perawat', ['slug' => Str::slug($data->nama_perawat)]);
                $urlUpdateDataPerawat = route('update.data.perawat', ['slug' => Str::slug($data->nama_perawat)]);
                $urlDeleteDataPerawat = route('delete.data.perawat', ['slug' => Str::slug($data->nama_perawat)]);

                $baseClass = 'inline-flex items-center justify-center w-10 h-10 rounded-full text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-1';

                $button = '
        <div class="flex items-center justify-center gap-2">
            <!-- Detail -->
            <button 
                class="btn-detail ' . $baseClass . ' bg-sky-500 hover:bg-sky-600 focus:ring-sky-300"
                data-url="' . $url . '" 
                title="Detail">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     fill="none" 
                     viewBox="0 0 24 24" 
                     stroke-width="1.8" 
                     stroke="currentColor" 
                     class="w-5 h-5">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.437 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>

            <!-- Edit -->
            <button 
                class="btn-edit ' . $baseClass . ' bg-amber-500 hover:bg-amber-600 focus:ring-amber-300"
                data-url="' . $url . '" 
                data-url-update-data-perawat="' . $urlUpdateDataPerawat . '" 
                title="Edit">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     fill="none" 
                     viewBox="0 0 24 24" 
                     stroke-width="1.8" 
                     stroke="currentColor" 
                     class="w-5 h-5">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          d="M16.862 4.487l1.687-1.688a2.25 2.25 0 113.182 3.182L10.582 17.13a4.5 4.5 0 01-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 011.13-1.897L16.862 4.487z" />
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          d="M19.5 7.125L16.875 4.5" />
                </svg>
            </button>
    ';

                if ($isSuperAdmin) {
                    $button .= '
            <!-- Delete -->
            <button 
                class="btn-delete ' . $baseClass . ' bg-rose-500 hover:bg-rose-600 focus:ring-rose-300"
                data-url-delete-data-perawat="' . $urlDeleteDataPerawat . '"
                title="Hapus">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     fill="none" 
                     viewBox="0 0 24 24" 
                     stroke-width="1.8" 
                     stroke="currentColor" 
                     class="w-5 h-5">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21H8.084a2.25 2.25 0 01-2.245-1.327L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0V4.875c0-1.121-.879-2.06-2-2.122a51.964 51.964 0 00-3 0C9.629 2.815 8.75 3.754 8.75 4.875V5.25m7.5 0h-7.5" />
                </svg>
            </button>
        ';
                }

                $button .= '</div>';

                return $button;
            })
            ->rawColumns(['foto', 'action'])
            ->make(true);
    }

    public function getDataDetailPerawat($slug)
    {
        $data = Perawat::with(['dokterPoli.dokter', 'dokterPoli.poli'])
            ->get()
            ->first(function ($item) use ($slug) {
                return Str::slug($item->nama_perawat) === $slug;
            });

        $dataAkunPerawat = $data->user;
        $dataPerawat = $data;
        $dataPenugasan = $data->dokterPoli;

        return response()->json([
            'dataPerawat' => $dataPerawat,
            'dataAkunPerawat' => $dataAkunPerawat,
            'dataPenugasan' => $dataPenugasan,
        ]);
    }

    public function getDataDokterPoli($slug)
    {
        $dataPoli = Poli::get()->first(function ($data) use ($slug) {
            return Str::slug($data->nama_poli) === $slug;
        });

        $dataDokterPoli = DokterPoli::with(['dokter', 'poli'])->where('poli_id', $dataPoli->id)->get();

        return response()->json([
            'dataDokterPoli' => $dataDokterPoli
        ]);
    }

    public function updateDataPerawat(Request $request, $slug)
    {
        $request->validate([
            'edit_username_perawat' => 'required|string|max:255',
            'edit_nama_perawat' => 'required|string|max:255',
            'edit_email_perawat' => 'required|email|max:255',
            'edit_no_hp_perawat' => 'required|string|max:20',
            'edit_foto_perawat' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'dokter_poli_id' => 'required|array|min:1',
            'dokter_poli_id.*' => 'exists:dokter_poli,id',
            'edit_password_perawat' => 'nullable|min:8|same:edit_password_perawat_confirmation',
        ]);

        $dataPerawat = Perawat::with(['user', 'dokterPoli'])->get()->first(function ($data) use ($slug) {
            return Str::slug($data->nama_perawat) === $slug;
        });

        if (!$dataPerawat) {
            return response()->json([
                'pesan' => 'Data perawat tidak ditemukan'
            ], 404);
        }

        $dataAkunPerawat = $dataPerawat->user;

        $updateUser = [
            'username' => $request->edit_username_perawat,
            'email' => $request->edit_email_perawat,
        ];

        if ($request->filled('edit_password_perawat')) {
            $updateUser['password'] = Hash::make($request->edit_password_perawat);
        }

        $dataAkunPerawat->update($updateUser);

        $updatePerawat = [
            'nama_perawat' => $request->edit_nama_perawat,
            'no_hp_perawat' => $request->edit_no_hp_perawat,
        ];

        if ($request->hasFile('edit_foto_perawat')) {
            if ($dataPerawat->foto_perawat && Storage::disk('public')->exists($dataPerawat->foto_perawat)) {
                Storage::disk('public')->delete($dataPerawat->foto_perawat);
            }

            $pathFoto = $request->file('edit_foto_perawat')->store('perawat', 'public');
            $updatePerawat['foto_perawat'] = $pathFoto;
        }

        $dataPerawat->update($updatePerawat);

        $dataPerawat->dokterPoli()->sync($request->dokter_poli_id);

        return response()->json([
            'pesan' => 'Data Perawat Berhasil Diupdate',
            'dataPerawat' => $dataPerawat->load(['user', 'dokterPoli']),
        ]);
    }

    public function deleteDataPerawat($slug)
    {
        $dataPerawat = Perawat::get()->first(function ($data) use ($slug) {
            return Str::slug($data->nama_perawat) === $slug;
        });

        $dataPerawat->delete();

        return response()->json([
            'pesan' => 'Berhasil Menghapus Data Perawat',
            'dataPerawat' => $dataPerawat,
        ]);
    }
}
