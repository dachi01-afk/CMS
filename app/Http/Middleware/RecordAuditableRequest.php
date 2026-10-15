<?php

namespace App\Http\Middleware;

use App\Support\Audit\AuditLogger;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Mencatat aksi yang tidak terlihat oleh model observer:
 * membuka detail (LIHAT), export data, cetak dokumen, dan perubahan
 * pengaturan sistem. Pemetaan route → action/module ada di config/audit.php,
 * jadi tidak perlu menambah kode di dalam controller.
 */
class RecordAuditableRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->record($request, $response);
        } catch (Throwable $e) {
            // Jaring pengaman terakhir: audit tidak boleh mematahkan respons.
            Log::error('Gagal mencatat audit akses route', [
                'route' => $request->route()?->getName(),
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }

    protected function record(Request $request, Response $response): void
    {
        $route = $request->route();
        $name = $route?->getName();

        if (! $name) {
            return;
        }

        // Hanya catat akses yang benar-benar berhasil (bukan 403/404/500).
        if ($response->getStatusCode() >= 400) {
            return;
        }

        $meta = $this->match($name);

        if (! $meta) {
            return;
        }

        [$action, $module] = $meta;

        $auditable = $this->resolveEntity($name, $route);

        AuditLogger::record($action, $module, $this->describe($action, $module, $auditable), [
            'auditable' => $auditable,
        ]);
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    protected function match(string $name): ?array
    {
        foreach (config('audit.route_actions', []) as $pattern => $meta) {
            if (Str::is($pattern, $name)) {
                [$action, $module] = $meta;

                return [$action, $module];
            }
        }

        return null;
    }

    /** Ambil entitas yang diakses bila route-nya memang menunjuk ke satu entitas. */
    protected function resolveEntity(string $name, mixed $route): ?Model
    {
        foreach (config('audit.route_entities', []) as $pattern => $descriptor) {
            if (! Str::is($pattern, $name)) {
                continue;
            }

            [$class, $parameter, $column] = $descriptor;
            $value = $route->parameter($parameter);

            if ($value === null || ! class_exists($class)) {
                continue;
            }

            return $class::query()->where($column, $value)->first();
        }

        return null;
    }

    protected function describe(string $action, string $module, ?Model $auditable): string
    {
        $verb = match ($action) {
            'view' => 'Membuka detail',
            'export' => 'Mengekspor data',
            'print' => 'Mencetak dokumen',
            'create' => 'Menambah',
            'update' => 'Mengubah',
            'delete' => 'Menghapus',
            'cancel' => 'Membatalkan transaksi',
            'refund' => 'Melakukan refund',
            default => 'Mengakses',
        };

        $text = sprintf('%s %s', $verb, $module);

        if ($auditable && method_exists($auditable, 'auditLabel') && ($label = $auditable->auditLabel())) {
            $text .= ' ('.$label.')';
        }

        return $text.'.';
    }
}
