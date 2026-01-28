<?php

namespace App\Http\Controllers\Perawat;

use App\Models\Perawat;
use App\Models\OrderLab;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EMR;
use App\Models\HasilLab;
use App\Models\OrderLabDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class OrderLabController extends Controller
{
    public function getDataHasilLab(Request $request)
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
        $data = OrderLab::getData() // Load relasi & select
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
                if (!$row->orderLabDetail || $row->orderLabDetail->isEmpty()) {
                    return '-';
                }

                return $row->orderLabDetail->map(function ($detail) {
                    // Gunakan optional untuk menghindari error jika jenis_pemeriksaan_lab null
                    return optional($detail->jenisPemeriksaanLab)->nama_pemeriksaan ?? '-';
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

    public function inputHasil($id)
    {
        $order = OrderLab::getDataById($id);

        return view('perawat.kunjungan.data-input-hasil-lab', compact('order'));
    }

    public function simpanHasil(Request $request)
    {
        $request->validate([
            'hasil.*' => 'required|numeric',
            'keterangan.*' => 'nullable|string',
            'order_lab_id' => 'required|exists:order_lab,id'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $order = OrderLab::findOrFail($request->order_lab_id);
                $userId = Auth::user()->id;
                $perawat = Perawat::where('user_id', $userId)->first();

                $summaryHasil = [];

                foreach ($request->hasil as $detailId => $nilaiHasil) {
                    $detail = OrderLabDetail::with('jenisPemeriksaanLab')->findOrFail($detailId);

                    // 1. Simpan ke tabel hasil_lab
                    HasilLab::create([
                        'order_lab_detail_id' => $detailId,
                        'perawat_id'          => $perawat->id,
                        'nilai_hasil'         => $nilaiHasil,
                        'nilai_rujukan'       => $detail->jenisPemeriksaanLab->nilai_normal, // Dari tabel jenis_pemeriksaan_lab
                        'keterangan'          => $request->keterangan[$detailId] ?? '-',
                        'tanggal_pemeriksaan' => now()->format('Y-m-d'),
                        'jam_pemeriksaan'     => now()->format('H:i:s'),
                    ]);

                    // Kumpulkan teks untuk EMR (Contoh: Gula Darah: 110 mg/dL)
                    $summaryHasil[] = $detail->jenisPemeriksaanLab->nama_pemeriksaan . ": " . $nilaiHasil;
                }

                // 2. Update status OrderLab menjadi Selesai
                $order->update(['status' => 'Selesai']);

                // 3. Masukkan ke tabel EMR (image_b9976c.png)
                // Kita cari EMR berdasarkan kunjungan_id yang ada di order_lab
                // Asumsi: Di order_lab kamu ada kolom kunjungan_id
                EMR::updateOrCreate(
                    ['kunjungan_id' => $order->kunjungan_id],
                    [
                        'pasien_id'  => $order->pasien_id,
                        'dokter_id'  => $order->dokter_id,
                        'perawat_id' => $perawat->id,
                        'diagnosis'  => "Hasil Lab: " . implode(', ', $summaryHasil)
                        // ^ Sesuaikan kolom diagnosis atau buat kolom baru jika perlu
                    ]
                );
            });

            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan dan diteruskan ke EMR!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
}
