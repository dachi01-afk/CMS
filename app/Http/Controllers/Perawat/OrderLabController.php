<?php

namespace App\Http\Controllers\Perawat;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\EMR;
use App\Models\HasilLab;
use App\Models\OrderLab;
use App\Models\OrderLabDetail;
use App\Models\Perawat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class OrderLabController extends Controller
{
    public function getDataHasilLab(Request $request)
    {
        // 1. Ambil ID Perawat yang sedang login
        $user = Auth::user();
        $userId = $user->id;

        $perawatId = Perawat::where('user_id', $userId)->first();

        // Cek validasi perawat
        if (!$perawatId) {
            return response()->json(['error' => 'User bukan perawat'], 403);
        }

        // 2. Panggil Query menggunakan Scope yang kita buat tadi
        $data = OrderLab::getData() // Load relasi & select
            ->filterByPerawat($perawatId->id) // Filter Logic Dokter-Perawat
            ->today();

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
                $config = match ($row->status) {
                    'Selesai' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'dot' => 'bg-green-500'],
                    'Pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500'],
                    'Diproses' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'dot' => 'bg-blue-500'],
                    'Dibatalkan' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'dot' => 'bg-red-500'],
                    default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'dot' => 'bg-gray-500']
                };

                return '
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.$config['bg'].' '.$config['text'].'">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 '.$config['dot'].' rounded-full" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        '.$row->status.'
                    </span>';
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
                $url = route('input.hasil.order.lab', $row->id);

                // Jika status sudah selesai, kita bisa ganti tombol jadi 'Lihat' atau disable 'Input'
                if ($row->status === 'Selesai') {
                    return '<button class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-medium cursor-not-allowed">
                        <i class="fas fa-check-circle mr-1"></i> Terinput
                    </button>';
                }

                return '
                    <a href="'.$url.'" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Input Hasil
                    </a>';
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
            'order_lab_id' => 'required|exists:order_lab,id',
        ]);

        try {
            $orderId = $request->order_lab_id;
            $hasilLabTerakhir = null;

            Log::info('=== SIMPAN HASIL LAB START ===', [
                'order_lab_id' => $orderId,
                'perawat_user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            DB::transaction(function () use ($request, $orderId, &$hasilLabTerakhir) {
                $order = OrderLab::with(['pasien.user', 'orderLabDetail.jenisPemeriksaanLab'])
                    ->findOrFail($orderId);

                $userId = Auth::user()->id;
                $perawat = Perawat::where('user_id', $userId)->firstOrFail();

                Log::info('Processing hasil lab for order', [
                    'order_id' => $orderId,
                    'perawat_id' => $perawat->id,
                    'pasien_id' => $order->pasien_id,
                    'total_detail' => count($request->hasil),
                ]);

                // Loop semua hasil lab
                foreach ($request->hasil as $detailId => $nilaiHasil) {
                    $detail = OrderLabDetail::with('jenisPemeriksaanLab')->findOrFail($detailId);

                    // Simpan hasil lab
                    $hasilLabTerakhir = HasilLab::create([
                        'order_lab_detail_id' => $detailId,
                        'perawat_id' => $perawat->id,
                        'nilai_hasil' => $nilaiHasil,
                        'nilai_rujukan' => $detail->jenisPemeriksaanLab->nilai_normal,
                        'keterangan' => $request->keterangan[$detailId] ?? '-',
                        'tanggal_pemeriksaan' => now()->format('Y-m-d'),
                        'jam_pemeriksaan' => now()->format('H:i:s'),
                    ]);

                    Log::info('Hasil lab created', [
                        'hasil_lab_id' => $hasilLabTerakhir->id,
                        'detail_id' => $detailId,
                        'nilai_hasil' => $nilaiHasil,
                        'pemeriksaan' => $detail->jenisPemeriksaanLab->nama_pemeriksaan ?? '-',
                    ]);
                }

                // Update status order lab detail
                $order->orderLabDetail()->update([
                    'status_pemeriksaan' => 'Selesai',
                ]);

                // Update status order lab
                $order->update(['status' => 'Selesai']);

                Log::info('Order lab status updated to Selesai', [
                    'order_id' => $orderId,
                ]);

                // Update/insert EMR
                EMR::updateOrCreate(
                    ['kunjungan_id' => $order->kunjungan_id],
                    [
                        'pasien_id' => $order->pasien_id,
                        'dokter_id' => $order->dokter_id,
                        'perawat_id' => $perawat->id,
                        'order_lab_id' => $orderId,
                    ]
                );

                Log::info('EMR updated/created for order lab', [
                    'kunjungan_id' => $order->kunjungan_id,
                    'order_lab_id' => $orderId,
                ]);

                // ✅✅✅ KIRIM NOTIF SETELAH COMMIT (PALING AMAN) ✅✅✅
                DB::afterCommit(function () use ($orderId, $hasilLabTerakhir) {
                    try {
                        Log::info('🔔 Preparing to send notification for hasil lab', [
                            'order_lab_id' => $orderId,
                            'hasil_lab_id' => $hasilLabTerakhir ? $hasilLabTerakhir->id : null,
                        ]);

                        $orderFresh = OrderLab::with(['pasien.user'])->find($orderId);
                        
                        if ($orderFresh) {
                            Log::info('Order lab loaded for notification', [
                                'order_id' => $orderFresh->id,
                                'pasien_id' => $orderFresh->pasien_id,
                                'pasien_user_id' => $orderFresh->pasien->user_id ?? null,
                            ]);

                            NotificationHelper::kirimNotifikasiHasilLab($orderFresh, $hasilLabTerakhir);
                            
                            Log::info('✅ Notification helper called successfully');
                        } else {
                            Log::warning('⚠️ Order lab not found for notification', [
                                'order_lab_id' => $orderId,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('❌ Error sending notification for hasil lab', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'order_lab_id' => $orderId,
                        ]);
                    }
                });
            });

            Log::info('=== SIMPAN HASIL LAB SUCCESS ===', [
                'order_lab_id' => $orderId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan dan diteruskan ke EMR + notifikasi terkirim!',
            ]);

        } catch (\Exception $e) {
            Log::error('=== SIMPAN HASIL LAB ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_lab_id' => $orderId ?? null,
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Gagal: '.$e->getMessage()
            ], 500);
        }
    }
}