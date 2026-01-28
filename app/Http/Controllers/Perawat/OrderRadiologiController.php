<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\OrderRadiologi;
use App\Models\Perawat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class OrderRadiologiController extends Controller
{
    public function getDataOrderRadiologi()
    {
        // 1. Ambil ID Perawat yang sedang login
        // Asumsi: User login punya relasi ke tabel perawat
        // Jika strukturmu User -> Perawat, pakai: Auth::user()->perawat->id
        // Jika hardcode dulu untuk test, isi angka ID perawat (misal: 1)

        $user = Auth::user();

        $userId = $user->id;

        $perawatId = Perawat::where('user_id', $userId)->first();

        // dd($perawatId->id);

        // Cek validasi perawat
        if (!$perawatId) {
            return response()->json(['error' => 'User bukan perawat'], 403);
        }

        // 2. Panggil Query menggunakan Scope yang kita buat tadi
        $data = OrderRadiologi::getData() // Load relasi & select
            ->filterByPerawat($perawatId->id) // Filter Logic Dokter-Perawat
            // ->today()
            ->latest('tanggal_order'); // Urutkan terbaru

        // 3. Return ke DataTables
        return DataTables::of($data)
            ->addIndexColumn() // Tambah nomor urut (DT_RowIndex)
            ->addColumn('nama_pasien', function ($row) {
                return $row->pasien->nama_pasien ?? '-';
            })
            ->addColumn('nama_dokter', function ($row) {
                return $row->dokter->nama_dokter ?? '-';
            })
            ->addColumn('status_badge', function ($row) {
                // Contoh custom column HTML untuk badge status
                $color = match ($row->status) {
                    'Selesai' => 'green',
                    'Pending' => 'yellow',
                    'Diproses' => 'blue',
                    default => 'gray'
                };
                return '<span class="badge bg-' . $color . '-100 text-' . $color . '-800">' . $row->status . '</span>';
            })
            ->addColumn('item_pemeriksaan', function ($row) {
                // Cek jika order_lab_detail null atau kosong
                if (!$row->orderRadiologiDetail || $row->orderRadiologiDetail->isEmpty()) {
                    return '-';
                }

                return $row->orderRadiologiDetail->map(function ($detail) {
                    // Gunakan optional untuk menghindari error jika jenis_pemeriksaan_lab null
                    return optional($detail->jenisPemeriksaanRadiologi)->nama_pemeriksaan ?? '-';
                })->implode(', ');
            })
            ->addColumn('action', function ($row) {
                // Arahkan ke route yang baru kita buat
                $url = route('input.hasil.order.lab', $row->id);

                return '<a href="' . $url . '" class="btn btn-sm btn-primary">Input Hasil</a>';
            })
            ->rawColumns(['status_badge', 'action']) // Izinkan render HTML
            ->make(true);
    }
}
