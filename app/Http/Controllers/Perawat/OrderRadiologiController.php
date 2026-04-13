<?php

namespace App\Http\Controllers\Perawat;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\EMR;
use App\Models\HasilRadiologi;
use App\Models\OrderRadiologi;
use App\Models\Perawat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class OrderRadiologiController extends Controller
{
    public function getDataOrderRadiologi(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $perawat = Perawat::where('user_id', $userId)->first();

        if (!$perawat) {
            return response()->json(['error' => 'User bukan perawat'], 403);
        }

        // ✅ status opsional: kalau tidak dikirim, tampilkan semua
        $status = $request->get('status'); // null kalau tidak ada
        $status = $status ? ucfirst(strtolower($status)) : null;

        $data = OrderRadiologi::getData()
            ->filterByPerawat($perawat->id)
            ->today()
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            });

        return DataTables::of($data)
            ->addIndexColumn()
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
                    default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'dot' => 'bg-gray-500'],
                };

                return '
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.$config['bg'].' '.$config['text'].'">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 '.$config['dot'].' rounded-full" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        '.$row->status.'
                    </span>
                ';
            })
            ->addColumn('item_pemeriksaan', function ($row) {
                if (!$row->orderRadiologiDetail || $row->orderRadiologiDetail->isEmpty()) {
                    return '-';
                }

                return $row->orderRadiologiDetail->map(function ($detail) {
                    return optional($detail->jenisPemeriksaanRadiologi)->nama_pemeriksaan ?? '-';
                })->implode(', ');
            })
            ->addColumn('action', function ($row) {
                $url = route('input.hasil.order.radiologi', $row->id);

                if ($row->status === 'Selesai') {
                    return '
                        <button class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-medium cursor-not-allowed">
                            <i class="fas fa-check-circle mr-1"></i> Terinput
                        </button>
                    ';
                }

                return '
                    <a href="'.$url.'" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Input Hasil
                    </a>
                ';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function inputHasilgetDataOrderRadiologi(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        // ✅ ini sebaiknya ambil model perawat, bukan query builder doang
        $perawat = Perawat::where('user_id', $userId)->first();

        if (!$perawat) {
            return response()->json(['error' => 'User bukan perawat'], 403);
        }

        // ✅ ambil status dari request, default: Pending
        // JS kirim "pending", DB kamu "Pending" → kita normalisasi
        $status = $request->get('status', 'Pending');
        $status = ucfirst(strtolower($status)); // pending -> Pending

        // 2. Query utama
        $data = OrderRadiologi::getData()
            ->filterByPerawat($perawat->id)
            ->today()
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            });

        return DataTables::of($data)
            ->addIndexColumn()
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
                    </span>
                ';
            })
            ->addColumn('item_pemeriksaan', function ($row) {
                if (!$row->orderRadiologiDetail || $row->orderRadiologiDetail->isEmpty()) {
                    return '-';
                }

                return $row->orderRadiologiDetail->map(function ($detail) {
                    return optional($detail->jenisPemeriksaanRadiologi)->nama_pemeriksaan ?? '-';
                })->implode(', ');
            })
            ->addColumn('action', function ($row) {
                $url = route('input.hasil.order.radiologi', $row->id);

                // Karena sekarang yang tampil Pending saja, ini sebenernya gak kepake.
                // Tapi biar aman kalau suatu saat filter berubah.
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
                    </a>
                ';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function inputHasil($id)
    {
        $order = OrderRadiologi::getDataById($id);

        return view('perawat.kunjungan.data-input-hasil-radiologi', compact('order'));
    }

    public function simpanHasil(Request $request)
    {
        $request->validate([
            'order_radiologi_id' => 'required|exists:order_radiologi,id',
            'keterangan' => 'array',
            'keterangan.*' => 'nullable|string',
            'foto_hasil_radiologi' => 'array',
            'foto_hasil_radiologi.*' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        try {
            $orderId = $request->order_radiologi_id;
            $hasilTerakhir = null;

            Log::info('=== SIMPAN HASIL RADIOLOGI START ===', [
                'order_radiologi_id' => $orderId,
                'perawat_user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            DB::transaction(function () use ($request, $orderId, &$hasilTerakhir) {

                $order = OrderRadiologi::with([
                    'orderRadiologiDetail.jenisPemeriksaanRadiologi',
                    'pasien.user'
                ])->findOrFail($orderId);

                $userId = Auth::user()->id;
                $perawat = Perawat::where('user_id', $userId)->firstOrFail();

                Log::info('Processing hasil radiologi for order', [
                    'order_id' => $orderId,
                    'perawat_id' => $perawat->id,
                    'pasien_id' => $order->pasien_id,
                    'total_detail' => $order->orderRadiologiDetail->count(),
                ]);

                $summary = [];

                foreach ($order->orderRadiologiDetail as $detail) {
                    $detailId = $detail->id;
                    $path = null;

                    if ($request->hasFile("foto_hasil_radiologi.$detailId")) {
                        $file = $request->file("foto_hasil_radiologi.$detailId");
                        $filename = now()->format('YmdHis').'_'.\Illuminate\Support\Str::random(8).'.'.$file->getClientOriginalExtension();
                        $path = $file->storeAs('radiologi', $filename, 'public');

                        Log::info('Foto radiologi uploaded', [
                            'detail_id' => $detailId,
                            'filename' => $filename,
                            'path' => $path,
                        ]);
                    }

                    $hasilTerakhir = HasilRadiologi::updateOrCreate(
                        ['order_radiologi_detail_id' => $detailId],
                        [
                            'perawat_id' => $perawat->id,
                            'foto_hasil_radiologi' => $path,
                            'keterangan' => $request->keterangan[$detailId] ?? '-',
                            'tanggal_pemeriksaan' => now()->format('Y-m-d'),
                            'jam_pemeriksaan' => now()->format('H:i:s'),
                        ]
                    );

                    Log::info('Hasil radiologi created/updated', [
                        'hasil_id' => $hasilTerakhir->id,
                        'detail_id' => $detailId,
                        'has_photo' => $path !== null,
                        'pemeriksaan' => $detail->jenisPemeriksaanRadiologi->nama_pemeriksaan ?? '-',
                    ]);

                    $summary[] = ($detail->jenisPemeriksaanRadiologi->nama_pemeriksaan ?? 'Pemeriksaan')
                        .': '.($path ? 'foto terupload' : 'tanpa foto');
                }

                // ✅ Update status order jadi selesai
                $order->update(['status' => 'Selesai']);

                $order->orderRadiologiDetail()->update([
                    'status_pemeriksaan' => 'Selesai',
                ]);

                Log::info('Order radiologi status updated to Selesai', [
                    'order_id' => $orderId,
                ]);

                EMR::updateOrCreate(
                    ['kunjungan_id' => $order->kunjungan_id],
                    [
                        'pasien_id' => $order->pasien_id,
                        'dokter_id' => $order->dokter_id,
                        'perawat_id' => $perawat->id,
                        'diagnosis' => 'Hasil Radiologi: '.implode(', ', $summary),
                    ]
                );

                Log::info('EMR updated/created for order radiologi', [
                    'kunjungan_id' => $order->kunjungan_id,
                    'order_radiologi_id' => $orderId,
                ]);

                // ✅✅✅ KIRIM NOTIF SETELAH COMMIT ✅✅✅
                DB::afterCommit(function () use ($orderId, $hasilTerakhir) {
                    try {
                        Log::info('🔔 Preparing to send notification for hasil radiologi', [
                            'order_radiologi_id' => $orderId,
                            'hasil_radiologi_id' => $hasilTerakhir ? $hasilTerakhir->id : null,
                        ]);

                        $orderFresh = OrderRadiologi::with(['pasien.user'])->find($orderId);
                        
                        if ($orderFresh) {
                            Log::info('Order radiologi loaded for notification', [
                                'order_id' => $orderFresh->id,
                                'pasien_id' => $orderFresh->pasien_id,
                                'pasien_user_id' => $orderFresh->pasien->user_id ?? null,
                            ]);

                            NotificationHelper::kirimNotifikasiHasilRadiologi($orderFresh, $hasilTerakhir);
                            
                            Log::info('✅ Notification helper called successfully');
                        } else {
                            Log::warning('⚠️ Order radiologi not found for notification', [
                                'order_radiologi_id' => $orderId,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('❌ Error sending notification for hasil radiologi', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'order_radiologi_id' => $orderId,
                        ]);
                    }
                });
            });

            Log::info('=== SIMPAN HASIL RADIOLOGI SUCCESS ===', [
                'order_radiologi_id' => $orderId,
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Hasil radiologi berhasil disimpan + notifikasi terkirim!'
            ]);

        } catch (\Exception $e) {
            Log::error('=== SIMPAN HASIL RADIOLOGI ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_radiologi_id' => $orderId ?? null,
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Gagal: '.$e->getMessage()
            ], 500);
        }
    }
    /**
 * 🧪 TEST ENDPOINT - Kirim notifikasi hasil radiologi via Postman
 * Route: POST /api/test/notif-hasil-radiologi
 */
public function testNotifikasiHasilRadiologi(Request $request)
{
    try {
        $request->validate([
            'order_radiologi_id' => 'required|exists:order_radiologi,id',
        ]);

        $orderId = $request->order_radiologi_id;

        Log::info('🧪 TEST NOTIFIKASI HASIL RADIOLOGI START', [
            'order_radiologi_id' => $orderId,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Load order radiologi dengan relasi
        $order = OrderRadiologi::with([
            'pasien.user',
            'orderRadiologiDetail.jenisPemeriksaanRadiologi',
            'orderRadiologiDetail.hasilRadiologi'
        ])->findOrFail($orderId);

        // Cek FCM token
        if (!$order->pasien || !$order->pasien->user || !$order->pasien->user->fcm_token) {
            Log::warning('⚠️ FCM Token tidak ditemukan', [
                'pasien_id' => $order->pasien_id,
                'user_id' => $order->pasien->user_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => '⚠️ FCM Token tidak ditemukan untuk pasien ini',
                'data' => [
                    'pasien_nama' => $order->pasien->nama_pasien ?? '-',
                    'pasien_user_id' => $order->pasien->user_id ?? null,
                    'fcm_token' => null,
                ],
            ], 400);
        }

        // Ambil hasil radiologi terakhir
        $hasilTerakhir = $order->orderRadiologiDetail()
            ->with('hasilRadiologi')
            ->get()
            ->flatMap(fn($detail) => $detail->hasilRadiologi)
            ->sortByDesc('created_at')
            ->first();

        Log::info('📊 Data loaded for test notification', [
            'order_id' => $order->id,
            'pasien_id' => $order->pasien_id,
            'pasien_nama' => $order->pasien->nama_pasien,
            'pasien_user_id' => $order->pasien->user_id,
            'fcm_token' => substr($order->pasien->user->fcm_token, 0, 20) . '...',
            'hasil_radiologi_id' => $hasilTerakhir ? $hasilTerakhir->id : null,
        ]);

        // 🔥 KIRIM NOTIFIKASI
        NotificationHelper::kirimNotifikasiHasilRadiologi($order, $hasilTerakhir);

        Log::info('✅ Test notification sent successfully');

        return response()->json([
            'success' => true,
            'message' => '✅ Notifikasi berhasil dikirim ke HP pasien!',
            'data' => [
                'order_radiologi_id' => $order->id,
                'pasien_nama' => $order->pasien->nama_pasien,
                'pasien_user_id' => $order->pasien->user_id,
                'fcm_token_preview' => substr($order->pasien->user->fcm_token, 0, 30) . '...',
                'hasil_radiologi_count' => $order->orderRadiologiDetail->count(),
                'status' => $order->status,
            ],
        ]);

    } catch (\Exception $e) {
        Log::error('❌ TEST NOTIFIKASI RADIOLOGI ERROR', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'order_radiologi_id' => $request->order_radiologi_id ?? null,
        ]);

        return response()->json([
            'success' => false,
            'message' => '❌ Error: ' . $e->getMessage(),
        ], 500);
    }
}
}
