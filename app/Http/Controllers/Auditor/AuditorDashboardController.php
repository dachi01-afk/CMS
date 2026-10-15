<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Dashboard ringkasan aktivitas untuk role Auditor.
 * Seluruhnya hanya membaca tabel audit_logs.
 */
class AuditorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->input('days', 14);
        $days = in_array($days, [7, 14, 30, 90], true) ? $days : 14;

        $from = Carbon::now()->subDays($days - 1)->startOfDay();

        $perDay = AuditLog::query()
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->pluck('total', 'tanggal');

        // Lengkapi hari yang tidak punya aktivitas supaya grafik tidak bolong.
        $dailyLabels = [];
        $dailyTotals = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i);
            $dailyLabels[] = $date->format('d M');
            $dailyTotals[] = (int) ($perDay[$date->toDateString()] ?? 0);
        }

        $topUsers = AuditLog::query()
            ->where('created_at', '>=', $from)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, user_name, user_role, COUNT(*) as total')
            ->groupBy('user_id', 'user_name', 'user_role')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $actionTotals = AuditLog::query()
            ->where('created_at', '>=', $from)
            ->selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->orderByDesc('total')
            ->pluck('total', 'action');

        $moduleTotals = AuditLog::query()
            ->where('created_at', '>=', $from)
            ->selectRaw('module, COUNT(*) as total')
            ->groupBy('module')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'module');

        return view('auditor.dashboard', [
            'days' => $days,
            'from' => $from,
            'totalAktivitas' => AuditLog::where('created_at', '>=', $from)->count(),
            'totalHariIni' => AuditLog::whereDate('created_at', Carbon::today())->count(),
            'loginGagal' => AuditLog::where('action', 'login_failed')->where('created_at', '>=', $from)->count(),
            'loginGagalHariIni' => AuditLog::where('action', 'login_failed')->whereDate('created_at', Carbon::today())->count(),
            'luarJamOperasional' => $this->outsideOperationalHoursQuery($from)->count(),
            'luarJamOperasionalTerbaru' => $this->outsideOperationalHoursQuery($from)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'dailyLabels' => $dailyLabels,
            'dailyTotals' => $dailyTotals,
            'topUsers' => $topUsers,
            'actionTotals' => $actionTotals,
            'moduleTotals' => $moduleTotals,
            'jamOperasional' => config('audit.operational_hours'),
            'hariOperasional' => config('audit.operational_days'),
            'terbaru' => AuditLog::orderByDesc('created_at')->limit(8)->get(),
        ]);
    }

    /**
     * Query aktivitas di luar jam operasional: di luar rentang jam, atau
     * pada hari yang bukan hari operasional klinik.
     */
    protected function outsideOperationalHoursQuery(Carbon $from)
    {
        $start = config('audit.operational_hours.start', '07:00');
        $end = config('audit.operational_hours.end', '21:00');
        $days = config('audit.operational_days', [1, 2, 3, 4, 5, 6]);

        // DAYOFWEEK MySQL: 1 = Minggu ... 7 = Sabtu → konversi ke ISO 1 = Senin ... 7 = Minggu.
        $isoDayExpression = '((DAYOFWEEK(created_at) + 5) % 7) + 1';

        return AuditLog::query()
            ->where('created_at', '>=', $from)
            ->where(function ($query) use ($start, $end, $days, $isoDayExpression) {
                $query->whereRaw('TIME(created_at) < ?', [$start.':00'])
                    ->orWhereRaw('TIME(created_at) > ?', [$end.':00'])
                    ->orWhereRaw($isoDayExpression.' NOT IN ('.implode(',', array_map('intval', $days)).')');
            });
    }
}
