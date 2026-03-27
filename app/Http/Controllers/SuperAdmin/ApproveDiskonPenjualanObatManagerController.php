<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApproveDiskonPenjualanObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ApproveDiskonPenjualanObatManagerController extends Controller
{
    private function ensureManager(): void
    {
        $user = Auth::user();

        $role = strtolower((string) ($user->role ?? $user->level ?? $user->jenis_role ?? ''));

        $isManager = in_array($role, ['super admin', 'super_admin', 'superadmin'], true)
            || (bool) ($user->is_super_admin ?? false);

        abort_unless($user && $isManager, 403, 'Hanya Super Admin (Manager) yang bisa akses approval diskon.');
    }

    public function index()
    {
        $this->ensureManager();

        return view('super-admin.approve-diskon-penjualan-obat.approve-diskon-penjualan-obat');
    }

    private function rupiah($n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
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
        foreach (['id', 'detail_id', 'penjualanObat_detail_id', 'penjualanObatDetailId'] as $key) {
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

    private function resolveJenis($detail): string
    {
        if (data_get($detail, 'order_radiologi_detail_id')) {
            return 'RADIOLOGI';
        }

        if (data_get($detail, 'order_lab_detail_id')) {
            return 'LABORATORIUM';
        }

        if (data_get($detail, 'resep_obat_id')) {
            return 'OBAT';
        }

        if (data_get($detail, 'layanan_id')) {
            return 'LAYANAN';
        }

        $namaItem = strtoupper((string) data_get($detail, 'nama_item', ''));

        if (str_contains($namaItem, 'RADIOLOGI')) {
            return 'RADIOLOGI';
        }

        if (str_contains($namaItem, 'LAB')) {
            return 'LABORATORIUM';
        }

        if (str_contains($namaItem, 'OBAT')) {
            return 'OBAT';
        }

        return 'LAINNYA';
    }

    private function resolveNamaItem($detail): string
    {
        return (string) (
            data_get($detail, 'nama_item')
            ?? data_get($detail, 'nama_layanan')
            ?? data_get($detail, 'nama')
            ?? '-'
        );
    }

    private function requesterName($row): string
    {
        $u = data_get($row, 'request');

        return (string) (
            data_get($u, 'kasir.nama_kasir')
            ?? data_get($u, 'name')
            ?? data_get($u, 'username')
            ?? data_get($u, 'email')
            ?? 'Kasir'
        );
    }

    private function approverName($row): string
    {
        $u = data_get($row, 'approve');

        return (string) (
            data_get($u, 'name')
            ?? data_get($u, 'username')
            ?? data_get($u, 'email')
            ?? 'Manager'
        );
    }

    private function statusBadgeHtml(?string $status): string
    {
        $status = (string) ($status ?? 'pending');

        $map = [
            'pending'  => ['Menunggu', 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:ring-amber-800'],
            'approved' => ['Approved', 'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800'],
            'rejected' => ['Rejected', 'bg-rose-50 text-rose-800 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-200 dark:ring-rose-800'],
        ];

        [$label, $cls] = $map[$status] ?? ['Unknown', 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:ring-slate-600'];

        return '<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ' . $cls . '">' . e($label) . '</span>';
    }

    private function applySearch($query): void
    {
        $search = request('search.value');

        if (!$search) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('reason', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('penjualanObat', function ($p) use ($search) {
                    $p->where('kode_transaksi', 'like', "%{$search}%");
                })
                ->orWhereHas('penjualanObat.pasien', function ($p) use ($search) {
                    $p->where('nama_pasien', 'like', "%{$search}%");
                })
                ->orWhereHas('request', function ($r) use ($search) {
                    $r->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('kasir', function ($k) use ($search) {
                            $k->where('nama_kasir', 'like', "%{$search}%");
                        });
                })
                ->orWhereHas('approve', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    private function buildDiskonData($approval): array
    {
        $diskonItems = $this->normalizeDiskonItems($approval->diskon_items);
        $details     = collect(data_get($approval, 'penjualanObat.penjualanObatDetail', []));
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

            $qty      = (int) data_get($detail, 'jumlah', 1);
            $harga    = (float) data_get($detail, 'harga_satuan', 0);
            $subtotal = (float) data_get($detail, 'sub_total', 0);

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
                data_get($detail, 'obat.nama_obat')
                ?? 'Obat #' . data_get($detail, 'obat_id', $detailId)
            );

            $items[] = [
                'id'             => (int) $detailId,
                'detail_id'      => (int) $detailId,
                'jenis'          => 'OBAT',
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

        $query = ApproveDiskonPenjualanObat::query()
            ->with([
                'penjualanObat.pasien',
                'penjualanObat.penjualanObatDetail',
                'request.kasir',
                'approve',
            ])
            ->where('status', 'pending')
            ->latest('id');

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->filter(function ($query) {
                $this->applySearch($query);
            })

            ->addColumn('nama_pasien', function ($row) {
                return data_get($row, 'penjualanObat.pasien.nama_pasien', '-');
            })

            ->addColumn('kode_transaksi', function ($row) {
                return data_get($row, 'penjualanObat.kode_transaksi', '-');
            })

            ->addColumn('requested_by', function ($row) {
                return e($this->requesterName($row));
            })

            ->addColumn('approved_by', function ($row) {
                if (!$row->approved_by) {
                    return '-';
                }

                return e($this->approverName($row));
            })

            ->addColumn('status_badge', function ($row) {
                return $this->statusBadgeHtml($row->status);
            })

            ->addColumn('reason', function ($row) {
                return $row->reason ? e($row->reason) : '-';
            })

            ->addColumn('approved_at', function ($row) {
                return $row->approved_at ? $row->approved_at->format('d M Y H:i') : '-';
            })

            ->addColumn('diskon_items_detail', function ($row) {
                $built = $this->buildDiskonData($row);
                $count = data_get($built, 'summary.count', 0);
                $totalDiskon = data_get($built, 'summary.potongan', 0);

                $detailUrl = route('super.admin.diskon.penjualan.obat.detail_items', $row->id);

                return '
                    <div class="flex min-w-[220px] flex-col gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:ring-slate-600">
                                ' . $count . ' item
                            </span>
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800">
                                Potongan: ' . $this->rupiah($totalDiskon) . '
                            </span>
                        </div>

                        <button
                            type="button"
                            class="btn-lihat-detail-item inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700"
                            data-approval-id="' . (int) $row->id . '"
                            data-detail-url="' . e($detailUrl) . '">
                            <i class="fa-solid fa-eye"></i>
                            Lihat Detail Item
                        </button>
                    </div>
                ';
            })

            ->addColumn('action', function ($row) {
                $approveUrl = route('super.admin.approve.diskon.penjualan.obat.approve', $row->id);
                $rejectUrl  = route('super.admin.reject.diskon.penjualan.obat', $row->id);

                return '
                    <div class="flex min-w-[190px] flex-col gap-2">
                        <button
                            type="button"
                            class="btn-approve inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                            data-approval-id="' . (int) $row->id . '"
                            data-approve-url="' . e($approveUrl) . '"
                            data-reject-url="' . e($rejectUrl) . '">
                            <i class="fa-solid fa-check"></i>
                            <span>Approve</span>
                        </button>

                        <button
                            type="button"
                            class="btn-reject inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700"
                            data-approval-id="' . (int) $row->id . '"
                            data-approve-url="' . e($approveUrl) . '"
                            data-reject-url="' . e($rejectUrl) . '">
                            <i class="fa-solid fa-xmark"></i>
                            <span>Reject</span>
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['status_badge', 'diskon_items_detail', 'action'])
            ->make(true);
    }

    public function getDetailItems(ApproveDiskonPenjualanObat $approval)
    {
        $this->ensureManager();

        $approval->load([
            'penjualanObat.pasien',
            'penjualanObat.penjualanObatDetail.obat',
            'request.kasir',
            'approve',
        ]);

        $built = $this->buildDiskonData($approval);
        $requesterName = $this->requesterName($approval);

        return response()->json([
            'success' => true,
            'data' => [
                'approval_id' => $approval->id,
                'nama_pasien' => data_get($approval, 'penjualanObat.pasien.nama_pasien', '-'),
                'kode_transaksi' => data_get($approval, 'penjualanObat.kode_transaksi', '-'),
                'requested_by' => $requesterName,
                'requester' => $requesterName,
                'reason' => $approval->reason,
                'diskon_hash' => $approval->diskon_hash,
                'summary' => $built['summary'],
                'totals' => $built['totals'],
                'items' => $built['items'],
            ],
        ]);
    }

    public function approve(Request $request, ApproveDiskonPenjualanObat $approval)
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

            $this->applyDiskonSnapshotTopenjualanObat((int) $approval->penjualanObat_id, $diskonItems);

            $approval->update([
                'status'         => 'approved',
                'approved_by'    => Auth::id(),
                'approved_at'    => now(),
                'rejection_note' => null,
            ]);

            DB::commit();

            $message = 'Diskon berhasil di-APPROVE.';

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

            $message = 'Terjadi kesalahan saat approve diskon.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return back()->with('error', $message);
        }
    }

    public function reject(Request $request, ApproveDiskonPenjualanObat $approval)
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

            $this->resetDiskonpenjualanObat((int) $approval->penjualanObat_id);

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

    private function applyDiskonSnapshotToPenjualanObat(int $penjualanObatId, array $diskonItems): void
    {
        $mapPersen = collect($diskonItems)->pluck('persen', 'id')->toArray();

        $details = DB::table('penjualan_obat_detail')
            ->where('penjualan_obat_id', $penjualanObatId)
            ->select('id', 'sub_total')
            ->get();

        $totalAwal = 0;
        $potonganTotal = 0;
        $totalSetelahDiskon = 0;

        foreach ($details as $detail) {
            $subtotal = (float) ($detail->sub_total ?? 0);
            $persen   = (float) ($mapPersen[(int) $detail->id] ?? 0);

            $persen = max(0, min(100, $persen));
            $potongan = min($subtotal, $subtotal * ($persen / 100));
            $after = $subtotal - $potongan;

            $totalAwal += $subtotal;
            $potonganTotal += $potongan;
            $totalSetelahDiskon += $after;

            DB::table('penjualan_obat_detail')
                ->where('id', $detail->id)
                ->update([
                    'diskon_tipe'          => $persen > 0 ? 'persen' : null,
                    'diskon_nilai'         => $persen,
                    'total_setelah_diskon' => $after,
                    'updated_at'           => now(),
                ]);
        }

        $diskonPersenGlobal = $totalAwal > 0 ? ($potonganTotal / $totalAwal) * 100 : 0;

        DB::table('penjualan_obat')
            ->where('id', $penjualanObatId)
            ->update([
                'diskon_tipe'          => $potonganTotal > 0 ? 'persen' : null,
                'diskon_nilai'         => $potonganTotal > 0 ? $diskonPersenGlobal : 0,
                'total_setelah_diskon' => $totalSetelahDiskon,
                'updated_at'           => now(),
            ]);
    }

    private function resetDiskonpenjualanObat(int $penjualanObatId): void
    {
        $details = DB::table('penjualanObat_detail')
            ->where('penjualanObat_id', $penjualanObatId)
            ->select('id', 'subtotal')
            ->get();

        $totalAwal = 0.0;

        foreach ($details as $detail) {
            $subtotal = (float) ($detail->subtotal ?? 0);
            $totalAwal += $subtotal;

            DB::table('penjualanObat_detail')
                ->where('id', $detail->id)
                ->update([
                    'total_tagihan'        => $subtotal,
                    'diskon_tipe'          => null,
                    'diskon_nilai'         => 0,
                    'total_setelah_diskon' => $subtotal,
                    'updated_at'           => now(),
                ]);
        }

        DB::table('penjualanObat')
            ->where('id', $penjualanObatId)
            ->update([
                'total_tagihan'        => $totalAwal,
                'diskon_tipe'          => null,
                'diskon_nilai'         => 0,
                'total_setelah_diskon' => $totalAwal,
                'updated_at'           => now(),
            ]);
    }

    public function getDataSudahApprove()
    {
        $this->ensureManager();

        $query = ApproveDiskonPenjualanObat::query()
            ->with([
                'penjualanObat.pasien',
                'penjualanObat.penjualanObatDetail',
                'request.kasir',
                'approve',
            ])
            ->where('status', 'approved')
            ->latest('approved_at')
            ->latest('id');

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->filter(function ($query) {
                $this->applySearch($query);
            })

            ->addColumn('nama_pasien', function ($row) {
                return data_get($row, 'penjualanObat.nama_pasien', '-');
            })

            ->addColumn('kode_transaksi', function ($row) {
                return data_get($row, 'penjualanObat.kode_transaksi', '-');
            })

            ->addColumn('requested_by', function ($row) {
                return e($this->requesterName($row));
            })

            ->addColumn('approved_by', function ($row) {
                if (!$row->approved_by) {
                    return '-';
                }

                return e($this->approverName($row));
            })

            ->addColumn('status_badge', function ($row) {
                return $this->statusBadgeHtml($row->status);
            })

            ->addColumn('reason', function ($row) {
                return $row->reason ? e($row->reason) : '-';
            })

            ->addColumn('approved_at', function ($row) {
                return $row->approved_at ? $row->approved_at->format('d M Y H:i') : '-';
            })

            ->addColumn('diskon_items_detail', function ($row) {
                $built = $this->buildDiskonData($row);
                $count = data_get($built, 'summary.count', 0);
                $totalDiskon = data_get($built, 'summary.potongan', 0);

                $detailUrl = route('super.admin.diskon.penjualan.obatdetail_items', $row->id);

                return '
                    <div class="flex min-w-[220px] flex-col gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:ring-slate-600">
                                ' . $count . ' item
                            </span>
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800">
                                Potongan: ' . $this->rupiah($totalDiskon) . '
                            </span>
                        </div>

                        <button
                            type="button"
                            class="btn-lihat-detail-item inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700"
                            data-approval-id="' . (int) $row->id . '"
                            data-detail-url="' . e($detailUrl) . '">
                            <i class="fa-solid fa-eye"></i>
                            Lihat Detail Item
                        </button>
                    </div>
                ';
            })

            ->addColumn('action', function ($row) {
                return '
                    <div class="flex min-w-[170px] items-center justify-center">
                        <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800">
                            <i class="fa-solid fa-circle-check"></i>
                            Sudah Diapprove
                        </span>
                    </div>
                ';
            })

            ->rawColumns(['status_badge', 'diskon_items_detail', 'action'])
            ->make(true);
    }

    public function getDetailSudahApprove(ApproveDiskonPenjualanObat $approval)
    {
        $this->ensureManager();

        $approval->load([
            'penjualanObat.pasien',
            'penjualanObat.penjualanObatDetail',
            'request.kasir',
            'approve',
        ]);

        $built = $this->buildDiskonData($approval);
        $requesterName = $this->requesterName($approval);

        return response()->json([
            'success' => true,
            'data' => [
                'approval_id' => $approval->id,
                'nama_pasien' => data_get($approval, 'penjualanObat.pasien.nama_pasien', '-'),
                'kode_transaksi' => data_get($approval, 'penjualanObat.kode_transaksi', '-'),
                'requester' => $requesterName,
                'requested_by' => $requesterName,
                'reason' => $approval->reason,
                'summary' => $built['summary'],
                'totals' => $built['totals'],
                'items' => $built['items'],
            ],
        ]);
    }
}
