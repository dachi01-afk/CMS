<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DiskonApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DiskonApprovalManagerController extends Controller
{
    private function ensureManager(): void
    {
        $user = auth()->user();

        $role = strtolower((string)($user->role ?? $user->level ?? $user->jenis_role ?? ''));

        $isManager = in_array($role, ['super admin', 'super_admin', 'superadmin'], true)
            || (bool)($user->is_super_admin ?? false);

        abort_unless($user && $isManager, 403, 'Hanya Super Admin (Manager) yang bisa akses approval diskon.');
    }

    public function index()
    {
        $this->ensureManager();

        return view('super-admin.diskon-approve.diskon-approve');
    }

    public function getDataBelumApprove()
    {
        $this->ensureManager();

        $query = DiskonApproval::query()
            ->with([
                'pembayaran.emr.kunjungan.pasien',
                'pembayaran.pembayaranDetail',
                'requester.kasir',
                'approver',
            ])
            ->where('status', 'pending')
            ->latest('id');

        $rupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->addColumn('nama_pasien', function ($row) {
                return data_get($row, 'pembayaran.emr.kunjungan.pasien.nama_pasien', '-');
            })

            ->addColumn('kode_transaksi', function ($row) {
                return data_get($row, 'pembayaran.kode_transaksi', '-');
            })

            ->addColumn('requested_by', function ($row) {
                $u = $row->requester;

                $name =
                    data_get($u, 'kasir.nama_kasir') ??
                    data_get($u, 'name') ??
                    data_get($u, 'username') ??
                    data_get($u, 'email') ??
                    'Kasir';

                return e($name);
            })

            ->addColumn('approved_by', function ($row) {
                if (!$row->approved_by) {
                    return '-';
                }

                $u = $row->approver;

                $name =
                    data_get($u, 'name') ??
                    data_get($u, 'username') ??
                    data_get($u, 'email') ??
                    'Manager';

                return e($name . ' (#' . (int) $row->approved_by . ')');
            })

            ->addColumn('status_badge', function ($row) {
                $status = (string) ($row->status ?? 'pending');

                $map = [
                    'pending'  => ['Menunggu', 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:ring-amber-800'],
                    'approved' => ['Approved', 'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800'],
                    'rejected' => ['Rejected', 'bg-rose-50 text-rose-800 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-200 dark:ring-rose-800'],
                ];

                [$label, $cls] = $map[$status] ?? ['Unknown', 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:ring-slate-600'];

                return '<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ' . $cls . '">' . e($label) . '</span>';
            })

            ->addColumn('reason', function ($row) {
                return $row->reason ? e($row->reason) : '-';
            })

            ->addColumn('approved_at', function ($row) {
                return $row->approved_at ? $row->approved_at->format('d M Y H:i') : '-';
            })

            ->addColumn('diskon_items_detail', function ($row) use ($rupiah) {
                $diskonItems = $row->diskon_items;

                if (is_string($diskonItems)) {
                    $decoded = json_decode($diskonItems, true);
                    $diskonItems = is_array($decoded) ? $decoded : [];
                }

                $diskonItems = collect($diskonItems ?? []);
                $detailMap = collect(data_get($row, 'pembayaran.pembayaranDetail', []))->keyBy('id');

                $totalDiskon = 0;
                $count = 0;

                foreach ($diskonItems as $it) {
                    $detailId = (int) ($it['id'] ?? 0);
                    $persen   = (float) ($it['persen'] ?? 0);

                    $d = $detailMap->get($detailId);
                    $subtotal = (float) data_get($d, 'subtotal', 0);

                    if ($detailId > 0 && $persen > 0) {
                        $count++;
                        $totalDiskon += ($subtotal * ($persen / 100));
                    }
                }

                return '
                <div class="flex min-w-[220px] flex-col gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:ring-slate-600">
                            ' . $count . ' item
                        </span>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800">
                            Potongan: ' . $rupiah($totalDiskon) . '
                        </span>
                    </div>

                    <button
                        type="button"
                        class="btn-lihat-detail-item inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700"
                        data-approval-id="' . (int) $row->id . '">
                        <i class="fa-solid fa-eye"></i>
                        Lihat Detail Item
                    </button>
                </div>
            ';
            })

            ->addColumn('action', function ($row) {
                $approveUrl = route('super.admin.diskon.approve', $row->id);
                $rejectUrl  = route('super.admin.diskon.reject', $row->id);

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

    public function getDetailItems(DiskonApproval $approval)
    {
        $this->ensureManager();

        $approval->load([
            'pembayaran.emr.kunjungan.pasien',
            'pembayaran.pembayaranDetail',
            'requester.kasir',
        ]);

        $rupiah = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');

        $diskonItems = $approval->diskon_items;
        if (is_string($diskonItems)) {
            $decoded = json_decode($diskonItems, true);
            $diskonItems = is_array($decoded) ? $decoded : [];
        }
        $diskonItems = collect($diskonItems ?? []);
        $detailMap = collect(data_get($approval, 'pembayaran.pembayaranDetail', []))->keyBy('id');

        $items = [];
        $totalBase = 0;
        $totalDiskon = 0;
        $totalAfter = 0;

        foreach ($diskonItems as $it) {
            $detailId = (int)($it['id'] ?? 0);
            $persen   = (float)($it['persen'] ?? 0);

            $d = $detailMap->get($detailId);

            $nama     = (string) data_get($d, 'nama_item', 'Item tidak ditemukan');
            $qty      = (int) data_get($d, 'qty', 1);
            $harga    = (float) data_get($d, 'harga', 0);
            $subtotal = (float) data_get($d, 'subtotal', 0);

            $diskonNominal = $subtotal * ($persen / 100);
            $after = max($subtotal - $diskonNominal, 0);

            $totalBase += $subtotal;
            $totalDiskon += $diskonNominal;
            $totalAfter += $after;

            // jenis dari prefix nama_item
            $jenis = 'LAINNYA';
            if (str_starts_with($nama, 'Obat:')) $jenis = 'OBAT';
            elseif (str_starts_with($nama, 'Layanan:')) $jenis = 'LAYANAN';
            elseif (str_starts_with($nama, 'Lab:')) $jenis = 'LAB';
            elseif (str_starts_with($nama, 'Radiologi:')) $jenis = 'RADIOLOGI';

            $items[] = [
                'detail_id' => $detailId,
                'jenis' => $jenis,
                'nama_item' => $nama,
                'qty' => $qty,
                'harga' => $harga,
                'subtotal' => $subtotal,
                'persen' => $persen,
                'potongan' => $diskonNominal,
                'total' => $after,
                'harga_rp' => $rupiah($harga),
                'subtotal_rp' => $rupiah($subtotal),
                'potongan_rp' => $rupiah($diskonNominal),
                'total_rp' => $rupiah($after),
            ];
        }

        $requesterName =
            data_get($approval, 'requester.kasir.nama_kasir')
            ?? data_get($approval, 'requester.name')
            ?? data_get($approval, 'requester.username')
            ?? 'Kasir';

        return response()->json([
            'success' => true,
            'data' => [
                'approval_id' => $approval->id,
                'kode_transaksi' => data_get($approval, 'pembayaran.kode_transaksi', '-'),
                'nama_pasien' => data_get($approval, 'pembayaran.emr.kunjungan.pasien.nama_pasien', '-'),
                'requested_by' => $requesterName,
                'reason' => $approval->reason,
                'diskon_hash' => $approval->diskon_hash,
                'totals' => [
                    'item_count' => count($items),
                    'total_base' => $totalBase,
                    'total_diskon' => $totalDiskon,
                    'total_after' => $totalAfter,
                    'total_base_rp' => $rupiah($totalBase),
                    'total_diskon_rp' => $rupiah($totalDiskon),
                    'total_after_rp' => $rupiah($totalAfter),
                ],
                'items' => $items,
            ],
        ]);
    }

    public function approve(Request $request, DiskonApproval $approval)
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

            $this->applyDiskonSnapshotToPembayaran((int) $approval->pembayaran_id, $diskonItems);

            $approval->update([
                'status'         => 'approved',
                'approved_by'    => auth()->id(),
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

    public function reject(Request $request, DiskonApproval $approval)
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

            $this->resetDiskonPembayaran((int) $approval->pembayaran_id);

            $approval->update([
                'status'         => 'rejected',
                'approved_by'    => auth()->id(),
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
        if (is_array($raw)) {
            $items = $raw;
        } elseif (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $items = is_array($decoded) ? $decoded : [];
        } else {
            $items = [];
        }

        $out = [];
        foreach ($items as $it) {
            $id = (int)($it['id'] ?? 0);
            $persen = (float)($it['persen'] ?? 0);
            $persen = max(0, min(100, $persen));

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

    private function applyDiskonSnapshotToPembayaran(int $pembayaranId, array $diskonItems): void
    {
        $mapPersen = collect($diskonItems)->pluck('persen', 'id')->toArray();

        $details = DB::table('pembayaran_detail')
            ->where('pembayaran_id', $pembayaranId)
            ->select('id', 'subtotal')
            ->get();

        $totalAwal = 0.0;
        $potonganTotal = 0.0;
        $totalSetelahDiskon = 0.0;

        foreach ($details as $detail) {
            $subtotal = (float) ($detail->subtotal ?? 0);
            $persen = (float) ($mapPersen[(int)$detail->id] ?? 0);

            if ($persen < 0) $persen = 0;
            if ($persen > 100) $persen = 100;

            $potongan = $subtotal * ($persen / 100);
            if ($potongan > $subtotal) $potongan = $subtotal;

            $after = $subtotal - $potongan;

            $totalAwal += $subtotal;
            $potonganTotal += $potongan;
            $totalSetelahDiskon += $after;

            DB::table('pembayaran_detail')
                ->where('id', $detail->id)
                ->update([
                    'total_tagihan'        => $subtotal,
                    'diskon_tipe'          => $persen > 0 ? 'persen' : null,
                    'diskon_nilai'         => $persen,
                    'total_setelah_diskon' => $after,
                    'updated_at'           => now(),
                ]);
        }

        $diskonPersenGlobal = $totalAwal > 0 ? ($potonganTotal / $totalAwal) * 100 : 0;

        DB::table('pembayaran')
            ->where('id', $pembayaranId)
            ->update([
                'total_tagihan'        => $totalAwal,
                'diskon_tipe'          => $potonganTotal > 0 ? 'persen' : null,
                'diskon_nilai'         => $potonganTotal > 0 ? $diskonPersenGlobal : 0,
                'total_setelah_diskon' => $totalSetelahDiskon,
                'updated_at'           => now(),
            ]);
    }

    private function resetDiskonPembayaran(int $pembayaranId): void
    {
        $details = DB::table('pembayaran_detail')
            ->where('pembayaran_id', $pembayaranId)
            ->select('id', 'subtotal')
            ->get();

        $totalAwal = 0.0;

        foreach ($details as $detail) {
            $subtotal = (float) ($detail->subtotal ?? 0);
            $totalAwal += $subtotal;

            DB::table('pembayaran_detail')
                ->where('id', $detail->id)
                ->update([
                    'total_tagihan'        => $subtotal,
                    'diskon_tipe'          => null,
                    'diskon_nilai'         => 0,
                    'total_setelah_diskon' => $subtotal,
                    'updated_at'           => now(),
                ]);
        }

        DB::table('pembayaran')
            ->where('id', $pembayaranId)
            ->update([
                'total_tagihan'        => $totalAwal,
                'diskon_tipe'          => null,
                'diskon_nilai'         => 0,
                'total_setelah_diskon' => $totalAwal,
                'updated_at'           => now(),
            ]);
    }
}
