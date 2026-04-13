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
        $role = $user->role;

        $status = $request->get('status');
        $status = $status ? ucfirst(strtolower($status)) : null;

        $query = OrderRadiologi::getData();

        if ($role === 'Perawat') {
            $perawat = Perawat::where('user_id', $user->id)->first();

            if (!$perawat) {
                return response()->json(['error' => 'Data perawat tidak ditemukan'], 403);
            }

            $query = $query->filterByPerawat($perawat->id);
        } elseif ($role === 'Super Admin') {
            // Super Admin boleh melihat semua data
        } else {
            return response()->json(['error' => 'Role tidak diizinkan'], 403);
        }

        $query = $query->when($status, function ($q) use ($status) {
            $q->where('status', $status);
        });

        $orders = $query->get();

        $orders->loadMissing([
            'pasien',
            'dokter',
            'orderRadiologiDetail.jenisPemeriksaanRadiologi',
        ]);

        $detailIds = $orders->pluck('orderRadiologiDetail')
            ->flatten()
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $hasilRadiologi = $detailIds->isNotEmpty()
            ? HasilRadiologi::whereIn('order_radiologi_detail_id', $detailIds)
            ->get(['order_radiologi_detail_id', 'perawat_id'])
            ->keyBy('order_radiologi_detail_id')
            : collect();

        $perawatMap = $hasilRadiologi->pluck('perawat_id')
            ->filter()
            ->unique()
            ->pipe(function ($ids) {
                return $ids->isNotEmpty()
                    ? Perawat::whereIn('id', $ids)->pluck('nama_perawat', 'id')
                    : collect();
            });

        return DataTables::of($orders)
            ->addIndexColumn()
            ->addColumn('nama_pasien', function ($row) {
                return $row->pasien->nama_pasien ?? '-';
            })
            ->addColumn('nama_dokter', function ($row) {
                return $row->dokter->nama_dokter ?? '-';
            })
            ->addColumn('nama_perawat', function ($row) use ($role, $hasilRadiologi, $perawatMap) {
                if ($role !== 'Super Admin') {
                    return '-';
                }

                if (!$row->orderRadiologiDetail || $row->orderRadiologiDetail->isEmpty()) {
                    return '-';
                }

                $namaPerawat = $row->orderRadiologiDetail->map(function ($detail) use ($hasilRadiologi, $perawatMap) {
                    $hasil = $hasilRadiologi->get($detail->id);

                    if (!$hasil || !$hasil->perawat_id) {
                        return null;
                    }

                    return $perawatMap[$hasil->perawat_id] ?? ('Perawat ID: ' . $hasil->perawat_id);
                })->filter()->unique()->values();

                return $namaPerawat->isNotEmpty() ? $namaPerawat->implode(', ') : '-';
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
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $config['bg'] . ' ' . $config['text'] . '">
                <svg class="-ml-0.5 mr-1.5 h-2 w-2 ' . $config['dot'] . ' rounded-full" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                ' . $row->status . '
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
            ->addColumn('action', function ($row) use ($role) {
                if ($role === 'Super Admin') {
                    return '
                <button
                    type="button"
                    class="btn-detail-order-radiologi inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    data-id="' . $row->id . '"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Detail
                </button>
            ';
                }

                $url = route('input.hasil.order.radiologi', $row->id);

                if ($row->status === 'Selesai') {
                    return '
                <button class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-medium cursor-not-allowed">
                    <i class="fas fa-check-circle mr-1"></i> Terinput
                </button>
            ';
                }

                return '
            <a href="' . $url . '" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
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

    public function detailOrderRadiologi($id)
    {
        $user = Auth::user();
        $role = $user->role;

        $query = OrderRadiologi::with([
            'pasien',
            'dokter',
            'orderRadiologiDetail.jenisPemeriksaanRadiologi',
        ])->where('id', $id);

        if ($role === 'Perawat') {
            $perawat = Perawat::where('user_id', $user->id)->first();

            if (!$perawat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data perawat tidak ditemukan'
                ], 403);
            }

            $query->filterByPerawat($perawat->id);
        } elseif ($role === 'Super Admin') {
            // Super Admin boleh akses semua detail
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak diizinkan'
            ], 403);
        }

        $order = $query->firstOrFail();

        $detailIds = $order->orderRadiologiDetail->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $hasilRadiologi = $detailIds->isNotEmpty()
            ? HasilRadiologi::whereIn('order_radiologi_detail_id', $detailIds)
            ->get()
            ->keyBy('order_radiologi_detail_id')
            : collect();

        $perawatMap = $hasilRadiologi->pluck('perawat_id')
            ->filter()
            ->unique()
            ->pipe(function ($ids) {
                return $ids->isNotEmpty()
                    ? Perawat::whereIn('id', $ids)->pluck('nama_perawat', 'id')
                    : collect();
            });

        $detailPemeriksaan = $order->orderRadiologiDetail->map(function ($detail) use ($hasilRadiologi, $perawatMap) {
            $hasil = $hasilRadiologi->get($detail->id);

            $namaPerawatInput = '-';
            if ($hasil && $hasil->perawat_id) {
                $namaPerawatInput = $perawatMap[$hasil->perawat_id] ?? ('Perawat ID: ' . $hasil->perawat_id);
            }

            return [
                'detail_id' => $detail->id,
                'nama_pemeriksaan' => optional($detail->jenisPemeriksaanRadiologi)->nama_pemeriksaan ?? '-',
                'status_pemeriksaan' => $detail->status_pemeriksaan ?? '-',
                'keterangan_hasil' => $hasil->keterangan ?? '-',
                'tanggal_pemeriksaan' => $hasil && $hasil->tanggal_pemeriksaan
                    ? \Carbon\Carbon::parse($hasil->tanggal_pemeriksaan)->format('d-m-Y')
                    : '-',
                'jam_pemeriksaan' => $hasil->jam_pemeriksaan ?? '-',
                'perawat_input' => $namaPerawatInput,
                'foto_hasil_url' => $hasil && $hasil->foto_hasil_radiologi
                    ? asset('storage/' . $hasil->foto_hasil_radiologi)
                    : null,
                'foto_hasil_path' => $hasil->foto_hasil_radiologi ?? '-',
                'created_at' => optional($detail->created_at)->format('d-m-Y H:i:s'),
                'updated_at' => optional($detail->updated_at)->format('d-m-Y H:i:s'),
            ];
        });

        $listPerawat = $detailPemeriksaan->pluck('perawat_input')
            ->filter(fn($item) => $item && $item !== '-')
            ->unique()
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'kunjungan_id' => $order->kunjungan_id ?? '-',
                'status' => $order->status ?? '-',
                'tanggal_order' => optional($order->created_at)->format('d-m-Y H:i:s'),
                'updated_order' => optional($order->updated_at)->format('d-m-Y H:i:s'),

                'pasien' => [
                    'id' => $order->pasien->id ?? null,
                    'nama' => $order->pasien->nama_pasien ?? '-',
                    'jenis_kelamin' => $order->pasien->jenis_kelamin ?? '-',
                    'tanggal_lahir' => $order->pasien->tanggal_lahir ?? '-',
                    'alamat' => $order->pasien->alamat ?? '-',
                    'no_hp' => $order->pasien->no_hp ?? '-',
                ],

                'dokter' => [
                    'id' => $order->dokter->id ?? null,
                    'nama' => $order->dokter->nama_dokter ?? '-',
                    'spesialis' => $order->dokter->spesialis ?? '-',
                    'no_hp' => $order->dokter->no_hp ?? '-',
                ],

                'perawat' => $role === 'Super Admin' ? $listPerawat : [],
                'detail_pemeriksaan' => $detailPemeriksaan->values(),
            ]
        ]);
    }

    public function inputHasilgetDataOrderRadiologi(Request $request)
    {
        $user = Auth::user();

        $status = $request->get('status', 'Pending');
        $status = ucfirst(strtolower($status));

        $data = OrderRadiologi::getData()->today();

        if ($user->role === 'Perawat') {
            $perawat = Perawat::where('user_id', $user->id)->first();

            if (!$perawat) {
                return response()->json(['error' => 'Data perawat tidak ditemukan'], 403);
            }

            $data = $data->filterByPerawat($perawat->id);
        } elseif ($user->role !== 'Super Admin') {
            return response()->json(['error' => 'Role tidak diizinkan'], 403);
        }

        $data = $data->when($status, function ($q) use ($status) {
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
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $config['bg'] . ' ' . $config['text'] . '">
                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 ' . $config['dot'] . ' rounded-full" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    ' . $row->status . '
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
                    return '<button class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-medium cursor-not-allowed">
                    <i class="fas fa-check-circle mr-1"></i> Terinput
                </button>';
                }

                return '
                <a href="' . $url . '" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
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
                        $filename = now()->format('YmdHis') . '_' . \Illuminate\Support\Str::random(8) . '.' . $file->getClientOriginalExtension();
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
                        . ': ' . ($path ? 'foto terupload' : 'tanpa foto');
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
                        'diagnosis' => 'Hasil Radiologi: ' . implode(', ', $summary),
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
                'message' => 'Gagal: ' . $e->getMessage()
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
