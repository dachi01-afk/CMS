<?php

namespace App\Http\Controllers\Auditor;

use App\Exports\AuditLogExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EMR;
use App\Models\Pasien;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

/**
 * Halaman audit log untuk role Auditor (read-only).
 *
 * Controller ini tidak pernah menulis ke tabel selain audit_logs, dan
 * audit_logs sendiri hanya bisa ditambah.
 */
class AuditLogController extends Controller
{
    /** Menampilkan halaman tabel log. */
    public function index()
    {
        return view('auditor.logs.index', [
            'modules' => $this->moduleOptions(),
            'actions' => $this->actionOptions(),
            'roles' => $this->roleOptions(),
            'users' => $this->userOptions(),
            'sources' => $this->sourceOptions(),
        ]);
    }

    /** Sumber data DataTables (pagination & filter di sisi server). */
    public function data(Request $request)
    {
        $filters = $this->filters($request);

        $query = AuditLog::query()->filter($filters);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('created_at', fn (AuditLog $log) => $log->created_at?->format('d-m-Y H:i:s'))
            ->addColumn('source_label', fn (AuditLog $log) => $log->source_label)
            ->addColumn('action_label', fn (AuditLog $log) => view('auditor.partials.action-badge', ['action' => $log->action])->render())
            ->addColumn('aksi', fn (AuditLog $log) => '<a href="'.route('auditor.logs.show', $log->id).'" class="text-sky-600 hover:text-sky-800" title="Detail"><i class="fa-regular fa-eye"></i> Detail</a>')
            ->rawColumns(['action_label', 'aksi'])
            ->toJson();
    }

    /** Detail satu baris log, termasuk perbandingan nilai lama vs baru. */
    public function show($id)
    {
        $log = AuditLog::with(['user', 'pasien'])->findOrFail($id);

        return view('auditor.logs.show', [
            'log' => $log,
            'differences' => $this->differences($log),
        ]);
    }

    /**
     * Riwayat lengkap satu pasien: SEMUA aktivitas yang berkaitan dengan pasien
     * (data diri, kunjungan, rekam medis, resep, lab, radiologi, pembayaran,
     * dst.) beserta siapa saja yang pernah membuka datanya.
     */
    public function entity(Request $request)
    {
        $noEmr = $request->input('no_emr');
        $pasien = null;
        $logs = null;
        $accessors = collect();
        $moduleSummary = collect();
        $emrIds = collect();

        if ($noEmr) {
            $pasien = Pasien::where('no_emr', $noEmr)->first();

            if ($pasien) {
                $emrIds = EMR::where('pasien_id', $pasien->id)->pluck('id');

                $base = fn () => AuditLog::query()->where('pasien_id', $pasien->id);

                $logs = $base()
                    ->when($request->filled('module'), fn ($q) => $q->where('module', $request->input('module')))
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->paginate(25)
                    ->withQueryString();

                $accessors = $base()
                    ->whereIn('action', ['view', 'print', 'export'])
                    ->selectRaw('user_name, user_role, COUNT(*) as total, MAX(created_at) as terakhir')
                    ->groupBy('user_name', 'user_role')
                    ->orderByDesc('total')
                    ->get();

                $moduleSummary = $base()
                    ->selectRaw('module, COUNT(*) as total')
                    ->groupBy('module')
                    ->orderByDesc('total')
                    ->pluck('total', 'module');
            }
        }

        return view('auditor.logs.entity', [
            'noEmr' => $noEmr,
            'pasien' => $pasien,
            'logs' => $logs,
            'accessors' => $accessors,
            'moduleSummary' => $moduleSummary,
            'selectedModule' => $request->input('module'),
            'emrCount' => $emrIds->count(),
            'pasienOptions' => $this->pasienOptions(),
        ]);
    }

    /** Export hasil filter ke Excel atau CSV. */
    public function export(Request $request)
    {
        $format = $request->input('format', 'xlsx');
        $filters = $this->filters($request);

        $fileName = 'audit_log_'.now()->format('Ymd_His');

        if ($format === 'csv') {
            return Excel::download(new AuditLogExport($filters), $fileName.'.csv', ExcelWriter::CSV);
        }

        return Excel::download(new AuditLogExport($filters), $fileName.'.xlsx');
    }

    /**
     * Baca filter dari request. Tanggal tidak valid diabaikan (bukan error),
     * supaya query string yang keliru tidak mematahkan halaman.
     */
    protected function filters(Request $request): array
    {
        return [
            'from' => $this->parseDate($request->input('from'), false),
            'to' => $this->parseDate($request->input('to'), true),
            'user_id' => $request->filled('user_id') ? $request->input('user_id') : null,
            'role' => $request->filled('role') ? $request->input('role') : null,
            'module' => $request->filled('module') ? $request->input('module') : null,
            'action' => $request->filled('action') ? $request->input('action') : null,
            'keyword' => $request->filled('keyword') ? trim((string) $request->input('keyword')) : null,
            'source' => $request->filled('source') ? $request->input('source') : null,
        ];
    }

    protected function parseDate(?string $value, bool $endOfDay): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }

        return $endOfDay ? $date->endOfDay() : $date->startOfDay();
    }

    /**
     * Bandingkan nilai lama vs baru per field untuk halaman detail.
     */
    protected function differences(AuditLog $log): array
    {
        $old = $log->old_values ?? [];
        $new = $log->new_values ?? [];

        $keys = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
        sort($keys);

        return array_map(fn ($key) => [
            'field' => $key,
            'old' => $this->stringify($old[$key] ?? null),
            'new' => $this->stringify($new[$key] ?? null),
            'changed' => json_encode($old[$key] ?? null) !== json_encode($new[$key] ?? null),
        ], $keys);
    }

    protected function stringify(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    protected function moduleOptions(): array
    {
        $fromData = AuditLog::query()->distinct()->orderBy('module')->pluck('module')->all();

        return array_values(array_unique(array_merge(config('audit.modules', []), $fromData)));
    }

    protected function actionOptions(): array
    {
        return AuditLog::query()->distinct()->orderBy('action')->pluck('action')->all();
    }

    /** @return array<string, string> */
    protected function sourceOptions(): array
    {
        return ['web' => 'Web', 'mobile' => 'Aplikasi Mobile', 'sistem' => 'Sistem'];
    }

    protected function roleOptions(): array
    {
        return AuditLog::query()->whereNotNull('user_role')->distinct()->orderBy('user_role')->pluck('user_role')->all();
    }

    protected function userOptions()
    {
        $ids = AuditLog::query()->whereNotNull('user_id')->distinct()->pluck('user_id');

        return User::whereIn('id', $ids)->orderBy('username')->get(['id', 'username', 'role']);
    }

    protected function pasienOptions()
    {
        return Pasien::orderBy('nama_pasien')->limit(500)->get(['id', 'no_emr', 'nama_pasien']);
    }
}
