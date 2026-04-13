<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApproveDiskonOrderLayanan;
use App\Models\OrderLayanan;
use App\Models\OrderLayananDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ApproveDiskonOrderLayananManagerController extends Controller
{
    private function ensureManager()
    {
        $user = Auth::user();

        $role = strtolower((string) ($user->role ?? '-'));

        $isManager = $role === "super admin" || (bool) ($user->is_super_admin ?? false);

        abort_unless($user && $isManager, 403, "Hanya super admin (Manager) yang dapat mengakses halaman ini.");
    }

    public function index()
    {
        $this->ensureManager();

        return view('super-admin.approve-diskon-order-layanan.approve-diskon-order-layanan');
    }

    protected function requestName($data)
    {
        return $data->request?->nama_role ?? '-';
    }

    protected function approveName($data)
    {
        return $data->approve?->nama_role ?? '-';
    }

    private function normalizeDiskonItems($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function extractDetailId(array $item): int
    {
        foreach (['id', 'detail_id', 'orderLayanan_detail_id', 'order_layanan_detail_id'] as $key) {
            if (isset($item[$key]) && is_numeric($item[$key])) {
                return (int) $item[$key];
            }
        }

        return 0;
    }

    private function clampPersen(float $persen): float
    {
        if ($persen < 0) {
            return 0;
        }

        if ($persen > 100) {
            return 100;
        }

        return $persen;
    }

    private function extractPersen(array $item, $detail = null): float
    {
        foreach (['persen', 'diskon_persen', 'percentage', 'discount_percent'] as $key) {
            if (isset($item[$key]) && is_numeric($item[$key])) {
                return $this->clampPersen((float) $item[$key]);
            }
        }

        $diskonTipe  = (string) data_get($detail, 'diskon_tipe', '');
        $diskonNilai = (float) data_get($detail, 'diskon_nilai', 0);
        $subtotal    = (float) data_get($detail, 'sub_total', 0);

        if ($diskonTipe === 'persen') {
            return $this->clampPersen($diskonNilai);
        }

        if ($diskonTipe === 'nomial' && $subtotal > 0) {
            return $this->clampPersen(($diskonNilai / $subtotal) * 100);
        }

        if ($diskonTipe === 'nominal' && $subtotal > 0) {
            return $this->clampPersen(($diskonNilai / $subtotal) * 100);
        }

        return 0;
    }

    private function rupiah($n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }

    private function buildDiskonData($approval): array
    {
        $diskonItems = $this->normalizeDiskonItems($approval->diskon_items);
        $details     = collect(data_get($approval, 'orderLayanan.orderLayananDetail', []));
        $detailMap   = $details->keyBy('id');

        $selectedMap = [];

        foreach ($diskonItems as $rawItem) {
            $rawItem = (array) $rawItem;

            $detailId = $this->extractDetailId($rawItem);
            if ($detailId <= 0) {
                continue;
            }

            $detail = $detailMap->get($detailId);
            $persen = $this->extractPersen($rawItem, $detail);

            if ($detail && $persen > 0) {
                $selectedMap[$detailId] = $persen;
            }
        }

        if (empty($selectedMap)) {
            foreach ($details as $detail) {
                $persen = $this->extractPersen([], $detail);
                if ($persen > 0) {
                    $selectedMap[(int) data_get($detail, 'id')] = $persen;
                }
            }
        }

        $items = [];
        $grandTotal = 0;
        $grandPotongan = 0;
        $grandAfter = 0;

        foreach ($selectedMap as $detailId => $persen) {
            $detail = $detailMap->get($detailId);
            if (!$detail) {
                continue;
            }

            $qty      = (int) data_get($detail, 'qty', 1);
            $harga    = (float) data_get($detail, 'harga_satuan', 0);
            $subtotal = (float) data_get($detail, 'total_harga_item', 0);

            if ($subtotal <= 0 && $qty > 0 && $harga > 0) {
                $subtotal = $qty * $harga;
            }

            $potongan = $subtotal * ($persen / 100);
            if ($potongan > $subtotal) {
                $potongan = $subtotal;
            }

            $total = $subtotal - $potongan;

            $grandTotal += $subtotal;
            $grandPotongan += $potongan;
            $grandAfter += $total;

            $namaItem = (string) (
                data_get($detail, 'layanan.nama_layanan')
                ?? 'Layanan #' . data_get($detail, 'layanan_id', $detailId)
            );

            $items[] = [
                'id'             => (int) $detailId,
                'detail_id'      => (int) $detailId,
                'jenis'          => 'LAYANAN',
                'item'           => $namaItem,
                'nama_item'      => $namaItem,
                'qty'            => $qty,
                'harga'          => $harga,
                'subtotal'       => $subtotal,
                'diskon_persen'  => round($persen, 2),
                'persen'         => round($persen, 2),
                'potongan'       => $potongan,
                'total'          => $total,
                'harga_rp'       => $this->rupiah($harga),
                'subtotal_rp'    => $this->rupiah($subtotal),
                'potongan_rp'    => $this->rupiah($potongan),
                'total_rp'       => $this->rupiah($total),
            ];
        }

        return [
            'items' => array_values($items),
            'summary' => [
                'count'             => count($items),
                'total'             => $grandTotal,
                'potongan'          => $grandPotongan,
                'setelah_diskon'    => $grandAfter,
                'total_rp'          => $this->rupiah($grandTotal),
                'potongan_rp'       => $this->rupiah($grandPotongan),
                'setelah_diskon_rp' => $this->rupiah($grandAfter),
            ],
            'totals' => [
                'item_count'      => count($items),
                'total_base'      => $grandTotal,
                'total_diskon'    => $grandPotongan,
                'total_after'     => $grandAfter,
                'total_base_rp'   => $this->rupiah($grandTotal),
                'total_diskon_rp' => $this->rupiah($grandPotongan),
                'total_after_rp'  => $this->rupiah($grandAfter),
            ],
        ];
    }

    public function getDataBelumApprove()
    {
        $this->ensureManager();

        $dataDiskon = ApproveDiskonOrderLayanan::with([
            'orderLayanan',
            'orderLayanan.pasien',
            'request.superAdmin',
            'request.kasir',
            'approve.superAdmin',
            'approve.kasir',
        ])->where('status', 'pending')->latest();

        return DataTables::of($dataDiskon)
            ->addIndexColumn()
            ->editColumn('nama_pasien', function ($data) {
                return $data->orderLayanan->pasien->nama_pasien ?? '-';
            })
            ->editColumn('kode_transaksi', function ($data) {
                return $data->orderLayanan->kode_transaksi ?? '-';
            })
            ->editColumn('request', function ($data) {
                return e($this->requestName($data));
            })
            ->editColumn('approve', function ($data) {
                return e($this->approveName($data));
            })
            ->editColumn('status', function ($data) {
                $badge = match ($data->status) {
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800',
                };

                return '<span class="px-2 py-1 rounded ' . $badge . '">' . ucfirst($data->status) . '</span>';
            })
            ->editColumn('approved_at', function ($data) {
                return $data->getFormatTanggalApprove();
            })
            ->addColumn('detail_diskon', function ($data) {
                $built = $this->buildDiskonData($data);
                $count = data_get($built, 'summary.count', 0);
                $totalDiskon = data_get($built, 'summary.potongan', 0);

                $detailUrl = route(
                    'super.admin.get.data.detail.diskon.order.layanan.belum.approve',
                    ['approval' => $data->id]
                );

                return '
                        <div class="flex min-w-[220px] flex-col gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                    ' . $count . ' item
                                </span>
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    Potongan: ' . $this->rupiah($totalDiskon) . '
                                </span>
                            </div>

                            <button
                                type="button"
                                class="btn-lihat-detail-item inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700"
                                data-approval-id="' . (int) $data->id . '"
                                data-detail-url="' . e($detailUrl) . '">
                                <i class="fa-solid fa-eye"></i>
                                Lihat Detail Item
                            </button>
                        </div>
                    ';
            })
            ->addColumn('action', function ($row) {
                $approveUrl = route('super.admin.approve.diskon.order.layanan', $row->id);
                $rejectUrl  = route('super.admin.reject.diskon.order.layanan', $row->id);

                return '
                    <div class="flex min-w-[190px] flex-col gap-2">
                        <button
                            type="button"
                            class="btn-approve-diskon-order-layanan inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                            data-approval-id="' . (int) $row->id . '"
                            data-approve-url="' . e($approveUrl) . '"
                            data-reject-url="' . e($rejectUrl) . '">
                            <i class="fa-solid fa-check"></i>
                            <span>Approve</span>
                        </button>

                        <button
                            type="button"
                            class="btn-reject-diskon-order-layanan inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700"
                            data-approval-id="' . (int) $row->id . '"
                            data-approve-url="' . e($approveUrl) . '"
                            data-reject-url="' . e($rejectUrl) . '">
                            <i class="fa-solid fa-xmark"></i>
                            <span>Reject</span>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['detail_diskon', 'status', 'action'])
            ->make(true);
    }

    public function getDetailDiskonOrderLayanan(ApproveDiskonOrderLayanan $approval)
    {
        $this->ensureManager();

        $approval->load([
            'orderLayanan.pasien',
            'orderLayanan.orderLayananDetail.layanan',
            'request.kasir',
            'request.superAdmin',
            'approve.superAdmin',
            'approve.kasir',
        ]);

        $built = $this->buildDiskonData($approval);
        $requesterName = $this->requestName($approval);
        $approverName = $this->approveName($approval);

        return response()->json([
            'success' => true,
            'data' => [
                'approval_id'    => $approval->id,
                'nama_pasien'    => data_get($approval, 'orderLayanan.pasien.nama_pasien', '-'),
                'kode_transaksi' => data_get($approval, 'orderLayanan.kode_transaksi', '-'),
                'requested_by'   => $requesterName,
                'requester'      => $requesterName,

                'approved_by_name' => $approverName,
                'approved_by'      => $approverName,
                'status'           => $approval->status,
                'approved_at'      => $approval->getFormatTanggalApprove(),
                'reason'           => $approval->reason,
                'rejection_note'   => $approval->rejection_note,

                'diskon_hash'    => $approval->diskon_hash,
                'summary'        => $built['summary'],
                'totals'         => $built['totals'],
                'items'          => $built['items'],
            ],
        ]);
    }

    public function approve(Request $request, ApproveDiskonOrderLayanan $approval)
    {
        $this->ensureManager();

        if ($approval->status !== 'pending') {
            $message = 'Request ini sudah diproses.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        try {
            DB::beginTransaction();

            $diskonItems = $this->decodeDiskonItems($approval->diskon_items);

            $this->applyDiskonSnapshotToOrderLayanan((int) $approval->order_layanan_id, $diskonItems);

            $approval->update([
                'status'         => 'approved',
                'approved_by'    => Auth::id(),
                'approved_at'    => now(),
                'rejection_note' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Diskon berhasil di-APPROVE.',
                'data' => [
                    'id'             => $approval->id,
                    'status'         => $approval->status,
                    'approved_by'    => $approval->approved_by,
                    'approved_at'    => optional($approval->approved_at)->format('Y-m-d H:i:s'),
                    'rejection_note' => $approval->rejection_note,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, ApproveDiskonOrderLayanan $approval)
    {
        $this->ensureManager();

        if ($approval->status !== 'pending') {
            $message = 'Request ini sudah diproses.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        $rejectionNote = trim((string) $request->input('rejection_note', ''));

        if ($rejectionNote === '') {
            $message = 'Alasan penolakan wajib diisi.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        try {
            DB::beginTransaction();

            $this->resetDiskonOrderLayanan((int) $approval->order_layanan_id);

            $approval->update([
                'status'         => 'rejected',
                'approved_by'    => Auth::id(),
                'approved_at'    => now(),
                'rejection_note' => $rejectionNote,
            ]);

            DB::commit();

            $message = 'Diskon berhasil di-REJECT.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'id'             => $approval->id,
                        'status'         => $approval->status,
                        'approved_by'    => $approval->approved_by,
                        'approved_at'    => optional($approval->approved_at)->format('Y-m-d H:i:s'),
                        'rejection_note' => $approval->rejection_note,
                    ],
                ]);
            }

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $message = 'Terjadi kesalahan saat reject diskon.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return back()->with('error', $message);
        }
    }

    private function decodeDiskonItems($raw): array
    {
        $items = $this->normalizeDiskonItems($raw);

        $out = [];
        foreach ($items as $it) {
            $it = (array) $it;

            $id = $this->extractDetailId($it);
            $persen = $this->extractPersen($it);

            if ($id > 0 && $persen > 0) {
                $out[] = [
                    'id' => $id,
                    'persen' => $persen,
                ];
            }
        }

        usort($out, fn($a, $b) => $a['id'] <=> $b['id']);

        return $out;
    }

    private function applyDiskonSnapshotToOrderLayanan(int $orderLayananId, array $diskonItems): void
    {
        $mapPersen = collect($diskonItems)->pluck('persen', 'id')->toArray();

        $details = DB::table('order_layanan_detail')
            ->where('order_layanan_id', $orderLayananId)
            ->select('id', 'qty', 'harga_satuan', 'total_harga_item')
            ->get();

        $totalAwal = 0;
        $potonganTotal = 0;
        $totalSetelahDiskon = 0;

        foreach ($details as $detail) {
            $subtotal = (float) ($detail->total_harga_item ?? 0);
            $persen   = (float) ($mapPersen[(int) $detail->id] ?? 0);

            $persen = max(0, min(100, $persen));
            $potongan = min($subtotal, $subtotal * ($persen / 100));
            $after = $subtotal - $potongan;

            $totalAwal += $subtotal;
            $potonganTotal += $potongan;
            $totalSetelahDiskon += $after;
        }

        DB::table('order_layanan')
            ->where('id', $orderLayananId)
            ->update([
                'subtotal'         => $totalAwal,
                'diskon_tipe'      => $potonganTotal > 0 ? 'persen' : null,
                'diskon_nilai'     => $potonganTotal > 0 && $totalAwal > 0 ? ($potonganTotal / $totalAwal) * 100 : 0,
                'potongan_pesanan' => $potonganTotal,
                'total_bayar'      => $totalSetelahDiskon,
                'updated_at'       => now(),
            ]);
    }

    private function resetDiskonOrderLayanan(int $orderLayananId): void
    {
        $details = OrderLayananDetail::where('order_layanan_id', $orderLayananId)
            ->select('id', 'total_harga_item')
            ->get();

        $totalAwal = 0.0;

        foreach ($details as $detail) {
            $subtotal = (float) ($detail->total_harga_item ?? 0);
            $totalAwal += $subtotal;
        }

        OrderLayanan::where('id', $orderLayananId)
            ->update([
                'subtotal'         => $totalAwal,
                'diskon_tipe'      => null,
                'diskon_nilai'     => 0,
                'potongan_pesanan' => 0,
                'total_bayar'      => $totalAwal,
                'updated_at'       => now(),
            ]);
    }

    public function getDataSudahApprove()
    {
        $this->ensureManager();

        $dataDiskon = ApproveDiskonOrderLayanan::with([
            'orderLayanan',
            'orderLayanan.pasien',
            'request.kasir',
            'approve'
        ])->whereIn('status', ['approved', 'rejected'])->latest();

        return DataTables::of($dataDiskon)
            ->addIndexColumn()
            ->editColumn('nama_pasien', function ($data) {
                return $data->orderLayanan->pasien->nama_pasien ?? '-';
            })
            ->filter(function ($data) {
                $search = trim(request('search.value'));

                if ($search !== '') {
                    $data->where(function ($q) use ($search) {
                        $q->whereRaw("DATE_FORMAT(approve_diskon_order_layanan.approved_at, '%d %b %Y') LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("DATE_FORMAT(approve_diskon_order_layanan.approved_at, '%d') LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("DATE_FORMAT(approve_diskon_order_layanan.approved_at, '%m') LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("DATE_FORMAT(approve_diskon_order_layanan.approved_at, '%Y') LIKE ?", ["%{$search}%"])
                            ->orWhereHas('orderLayanan.pasien', function ($qq) use ($search) {
                                $qq->where('nama_pasien', 'like', '%' . $search . '%');
                            });
                    });
                }
            })
            ->editColumn('kode_transaksi', function ($data) {
                return $data->orderLayanan->kode_transaksi ?? '-';
            })
            ->editColumn('request', function ($data) {
                return e($this->requestName($data));
            })
            ->editColumn('approve', function ($data) {
                return e($this->approveName($data));
            })
            ->editColumn('status', function ($data) {
                $badge = match ($data->status) {
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800',
                };

                return '<span class="px-2 py-1 rounded ' . $badge . '">' . ucfirst($data->status) . '</span>';
            })
            ->editColumn('approved_at', function ($data) {
                return $data->getFormatTanggalApprove();
            })
            ->addColumn('action', function ($data) {
                $detailUrl = route(
                    'super.admin.get.data.detail.diskon.order.layanan.belum.approve',
                    ['approval' => $data->id]
                );

                return '
                    <div class="flex min-w-[190px] flex-col gap-2">
                        <button
                                type="button"
                                class="btn-lihat-detail-order-layanan-sudah-approve inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700"
                                data-approval-id="' . (int) $data->id . '"
                                data-detail-url="' . e($detailUrl) . '">
                                <i class="fa-solid fa-eye"></i>
                                Lihat Detail Item
                            </button>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
