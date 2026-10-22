<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use App\Models\PengajuanPerubahanData;
use App\Services\PengajuanPerubahanService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PengajuanPerubahanController extends Controller
{
    public function __construct(private PengajuanPerubahanService $pengajuanService)
    {
    }

    public function index()
    {
        $totals = PengajuanPerubahanData::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('verifikator.pengajuan.index', [
            'totalMenunggu' => (int) ($totals[PengajuanPerubahanData::STATUS_MENUNGGU] ?? 0),
            'totalDisetujui' => (int) ($totals[PengajuanPerubahanData::STATUS_DISETUJUI] ?? 0),
            'totalDitolak' => (int) ($totals[PengajuanPerubahanData::STATUS_DITOLAK] ?? 0),
        ]);
    }

    public function data(Request $request)
    {
        $query = PengajuanPerubahanData::query()
            ->select([
                'id',
                'jenis_data',
                'aksi',
                'target_id',
                'target_label',
                'status',
                'diajukan_oleh',
                'created_at',
            ])
            ->with([
                'pengaju:id,username,role',
                'pengaju.admin:id,user_id,nama_admin',
                'pengaju.farmasi:id,user_id,nama_farmasi',
            ])
            ->latest();

        $status = $request->input('status');
        if (in_array($status, [
            PengajuanPerubahanData::STATUS_MENUNGGU,
            PengajuanPerubahanData::STATUS_DISETUJUI,
            PengajuanPerubahanData::STATUS_DITOLAK,
        ], true)) {
            $query->where('status', $status);
        }

        $jenis = $request->input('jenis_data');
        if (in_array($jenis, [
            PengajuanPerubahanData::JENIS_LAYANAN,
            PengajuanPerubahanData::JENIS_OBAT,
        ], true)) {
            $query->where('jenis_data', $jenis);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->makeHidden(['pengaju', 'diajukan_oleh'])
            ->addColumn('tanggal', function (PengajuanPerubahanData $row) {
                return $row->created_at?->translatedFormat('d M Y') ?? '-';
            })
            ->addColumn('waktu', function (PengajuanPerubahanData $row) {
                return $row->created_at?->format('H:i') ?? '-';
            })
            ->addColumn('pengaju_nama', function (PengajuanPerubahanData $row) {
                $nama = $row->pengaju?->nama_role;

                if (! $nama || $nama === '-') {
                    $nama = $row->pengaju?->username;
                }

                return $nama ?: '-';
            })
            ->addColumn('pengaju_role', function (PengajuanPerubahanData $row) {
                return $row->pengaju?->role ?? '-';
            })
            ->addColumn('pengaju_inisial', function (PengajuanPerubahanData $row) {
                $nama = trim((string) ($row->pengaju?->nama_role ?? ''));

                if ($nama === '' || $nama === '-') {
                    $nama = trim((string) ($row->pengaju?->username ?? ''));
                }

                if ($nama === '') {
                    return '?';
                }

                $parts = preg_split('/\s+/', $nama) ?: [];
                $inisial = '';

                foreach (array_slice($parts, 0, 2) as $part) {
                    $inisial .= mb_strtoupper(mb_substr($part, 0, 1));
                }

                return $inisial ?: '?';
            })
            ->toJson();
    }

    public function show(PengajuanPerubahanData $pengajuan)
    {
        $pengajuan->loadMissing(['pengaju', 'verifikator']);

        return response()->json([
            'data' => [
                'id' => $pengajuan->id,
                'jenis_data_label' => $pengajuan->jenis_data_label,
                'aksi_label' => $pengajuan->aksi_label,
                'status' => $pengajuan->status,
                'status_label' => $pengajuan->status_label,
                'target_label' => $pengajuan->target_label,
                'pengaju' => $pengajuan->pengaju?->nama_role ?? '-',
                'pengaju_role' => $pengajuan->pengaju?->role ?? '-',
                'diajukan_pada' => $pengajuan->created_at?->translatedFormat('d M Y H:i') ?? '-',
                'diverifikasi_oleh' => $pengajuan->verifikator?->nama_role,
                'diverifikasi_pada' => $pengajuan->diverifikasi_pada?->translatedFormat('d M Y H:i'),
                'catatan_verifikator' => $pengajuan->catatan_verifikator,
                'rows' => $pengajuan->detailRows(),
            ],
        ]);
    }

    public function setujui(Request $request, PengajuanPerubahanData $pengajuan)
    {
        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->pengajuanService->setujui(
                $pengajuan,
                $request->user(),
                $validated['catatan'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan disetujui. Perubahan data sudah diterapkan.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menerapkan perubahan data. Silakan coba lagi.',
            ], 500);
        }
    }

    public function tolak(Request $request, PengajuanPerubahanData $pengajuan)
    {
        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->pengajuanService->tolak(
                $pengajuan,
                $request->user(),
                $validated['catatan'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan ditolak. Data asli tidak diubah.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak pengajuan. Silakan coba lagi.',
            ], 500);
        }
    }
}
