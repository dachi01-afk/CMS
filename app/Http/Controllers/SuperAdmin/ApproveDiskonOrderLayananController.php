<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApproveDiskonOrderLayanan;
use App\Models\OrderLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApproveDiskonOrderLayananController extends Controller
{
    public function requestApproval(Request $request, OrderLayanan $orderLayanan)
    {
        $request->validate([
            'reason'       => ['required', 'string', 'min:3'],
            'diskon_items' => ['required'],
        ]);

        $diskonItemsRaw = $request->input('diskon_items');

        $decoded = is_string($diskonItemsRaw)
            ? json_decode($diskonItemsRaw, true)
            : $diskonItemsRaw;

        if (!is_array($decoded)) {
            return response()->json([
                'success' => false,
                'message' => 'Format diskon_items tidak valid.',
            ], 422);
        }

        $normalized = $this->normalizeDiskonItems($decoded);

        if (count($normalized) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada diskon. Tidak perlu approval.',
            ], 422);
        }

        $latest = ApproveDiskonOrderLayanan::where('order_layanan_id', $orderLayanan->id)
            ->latest('id')
            ->first();

        if ($latest && $latest->status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan diskon untuk order layanan ini masih menunggu approval manager.',
            ], 422);
        }

        $hash = $this->hashDiskonItems($normalized);

        ApproveDiskonOrderLayanan::create([
            'order_layanan_id' => $orderLayanan->id,
            'requested_by'     => Auth::id(),
            'approved_by'      => null,
            'status'           => 'pending',
            'reason'           => $request->reason,
            'diskon_items'     => json_encode($normalized),
            'diskon_hash'      => $hash,
            'rejection_note'   => null,
            'approved_at'      => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan approval diskon berhasil dikirim. Menunggu Manager (Super Admin).',
            'data'    => [
                'status'       => 'pending',
                'diskon_hash'  => $hash,
                'diskon_items' => $normalized,
            ],
        ]);
    }

    public function status(OrderLayanan $orderLayanan)
    {
        $latest = ApproveDiskonOrderLayanan::where('order_layanan_id', $orderLayanan->id)
            ->latest('id')
            ->first();

        if (!$latest) {
            return response()->json([
                'success' => true,
                'data' => [
                    'exists'         => false,
                    'status'         => null,
                    'diskon_hash'    => null,
                    'approved_at'    => null,
                    'reason'         => null,
                    'rejection_note' => null,
                    'diskon_items'   => [],
                ]
            ]);
        }

        $diskonItems = $latest->diskon_items;
        if (is_string($diskonItems)) {
            $decoded = json_decode($diskonItems, true);
            $diskonItems = is_array($decoded) ? $decoded : [];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'exists'         => true,
                'status'         => $latest->status,
                'diskon_hash'    => $latest->diskon_hash,
                'approved_at'    => optional($latest->approved_at)->toDateString(),
                'reason'         => $latest->reason,
                'rejection_note' => $latest->rejection_note,
                'diskon_items'   => array_values($diskonItems ?? []),
            ]
        ]);
    }

    private function normalizeDiskonItems(array $items): array
    {
        $out = [];

        foreach ($items as $it) {
            $id = (int) ($it['id'] ?? 0);
            $persen = (float) ($it['persen'] ?? 0);

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

    private function hashDiskonItems(array $normalized): string
    {
        return hash('sha256', json_encode($normalized));
    }
}
