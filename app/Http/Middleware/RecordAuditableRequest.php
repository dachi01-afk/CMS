<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Audit\AuditContext;
use App\Support\Audit\AuditLogger;
use App\Support\Audit\PasienResolver;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Mencatat setiap AKSES pengguna yang tidak mengubah data, sehingga tidak
 * tertangkap trigger database:
 *
 *  - membuka halaman web,
 *  - membuka / mengambil satu data tertentu (detail pasien, rekam medis, ...),
 *  - setiap permintaan baca dari aplikasi mobile,
 *  - export, cetak, import (lihat 'route_actions' di config/audit.php),
 *  - login / logout / OTP di aplikasi mobile,
 *  - akses yang ditolak (HTTP 403).
 *
 * Perubahan data (tambah/ubah/hapus) dicatat oleh trigger MySQL, jadi tidak
 * dicatat ulang di sini supaya tidak ada log ganda.
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
        $status = $response->getStatusCode();

        if ($this->ignored($request, $name)) {
            return;
        }

        // ---- Login / logout / OTP aplikasi mobile ----
        if ($name && ($auth = config('audit.api_auth_routes', [])[$name] ?? null)) {
            $this->recordApiAuth($request, $response, $auth);

            return;
        }

        $user = $this->currentUser($request);

        // ---- Akses ditolak ----
        if ($status === 403) {
            if ($user) {
                AuditLogger::record('access_denied', $this->moduleFromPath($request),
                    sprintf('Akses ditolak: %s /%s.', $request->method(), ltrim($request->path(), '/')),
                    ['user' => $user, 'pasien_id' => PasienResolver::forRoute($route)]
                );
            }

            return;
        }

        // Permintaan yang gagal / tidak ditemukan tidak dianggap aktivitas.
        if ($status >= 400) {
            return;
        }

        // ---- Aksi yang dipetakan khusus (export, cetak, detail, dst.) ----
        if ($name && ($meta = $this->match($name))) {
            [$action, $module] = $meta;
            $auditable = $this->resolveEntity($name, $route);

            $description = $action === 'view' && ! $auditable && ! ($request->ajax() || $request->wantsJson())
                ? 'Membuka halaman '.$this->titleFromPath($request).'.'
                : $this->describe($action, $module, $auditable);

            AuditLogger::record($action, $module, $description, [
                'auditable' => $auditable,
                'user' => $user,
                'pasien_id' => $auditable ? PasienResolver::forModel($auditable) : PasienResolver::forRoute($route),
            ]);

            return;
        }

        // Aktivitas tamu (belum login) tidak dicatat, kecuali login di atas.
        if (! $user) {
            return;
        }

        // Tambah/ubah/hapus sudah dicatat trigger database.
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return;
        }

        $this->recordAccess($request, $route, $user);
    }

    /** Membuka halaman / data, dari web maupun aplikasi mobile. */
    protected function recordAccess(Request $request, ?Route $route, $user): void
    {
        $isApi = $request->is('api/*');
        $isAjax = $request->ajax() || $request->wantsJson();
        $parameters = $route ? $this->scalarParameters($route) : [];

        // Di web, permintaan AJAX tanpa parameter (isi tabel, grafik, dropdown)
        // adalah bagian dari halaman yang sedang dibuka — sudah tercatat.
        if (! $isApi && $isAjax && $parameters === []) {
            return;
        }

        $title = $this->titleFromPath($request);
        $module = $this->moduleFromPath($request);
        $auditable = $route ? $this->boundModel($route) : null;

        if ($isApi) {
            $description = 'Membuka '.$title.' di aplikasi mobile';
        } elseif ($isAjax) {
            $description = 'Melihat data '.$title;
        } else {
            $description = 'Membuka halaman '.$title;
        }

        if ($parameters !== []) {
            $description .= ' ('.collect($parameters)->map(fn ($v, $k) => $k.': '.$v)->implode(', ').')';
        }

        // URL yang jelas berupa cetak / export / import.
        $action = $this->actionFromPath($request);

        if ($action !== 'view') {
            $description = match ($action) {
                'print' => 'Mencetak',
                'export' => 'Mengekspor',
                default => 'Mengimpor',
            }.' '.$title.Str::after($description, $title);
        }

        AuditLogger::record($action, $module, $description.'.', [
            'user' => $user,
            'auditable' => $auditable,
            'pasien_id' => $auditable ? PasienResolver::forModel($auditable) : PasienResolver::forRoute($route),
        ]);
    }

    protected function recordApiAuth(Request $request, Response $response, array $config): void
    {
        $success = $response->getStatusCode() < 400 && $this->jsonFlag($response) !== false;
        $action = $success ? $config['success'] : $config['failed'];

        if (! $action) {
            return;
        }

        $identityField = $config['identity'] ?? null;
        $identity = $identityField ? trim((string) $request->input($identityField)) : null;

        $user = $this->currentUser($request);

        if (! $user && $identity) {
            $user = User::where($identityField === 'email' ? 'email' : 'username', $identity)->first();
        }

        $description = $config['label'].($success ? ' berhasil.' : ' gagal.');

        if (! $success && ($message = $this->jsonMessage($response))) {
            $description .= ' Pesan: '.Str::limit($message, 150);
        }

        AuditLogger::record($action, 'Autentikasi', $description, [
            'user' => $user,
            'user_name' => $user?->username ?? $identity,
            'user_role' => $user?->role,
            'source' => 'mobile',
        ]);
    }

    protected function currentUser(Request $request)
    {
        try {
            $user = $request->user() ?? ($request->is('api/*') ? Auth::guard('sanctum')->user() : null);
        } catch (Throwable) {
            $user = null;
        }

        if ($user) {
            return $user;
        }

        // User yang dikenali middleware konteks di awal request (mis. logout,
        // saat sesi sudah dihapus tetapi pelakunya tetap perlu dicatat).
        $context = AuditContext::user();

        return $context ? User::find($context['id']) : null;
    }

    protected function ignored(Request $request, ?string $name): bool
    {
        foreach (config('audit.ignored_routes', []) as $pattern) {
            if ($name && Str::is($pattern, $name)) {
                return true;
            }
        }

        $path = ltrim($request->path(), '/');

        foreach (config('audit.ignored_paths', []) as $pattern) {
            if (Str::is($pattern, $path)) {
                return true;
            }
        }

        return false;
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

            if ($value instanceof Model) {
                return $value;
            }

            if ($value === null || ! class_exists($class)) {
                continue;
            }

            return $class::query()->where($column, $value)->first();
        }

        return $route ? $this->boundModel($route) : null;
    }

    protected function boundModel(Route $route): ?Model
    {
        foreach ($route->parameters() as $value) {
            if ($value instanceof Model) {
                return $value;
            }
        }

        return null;
    }

    /** @return array<string, string> */
    protected function scalarParameters(Route $route): array
    {
        $result = [];

        foreach ($route->parameters() as $key => $value) {
            if ($value instanceof Model) {
                $result[$key] = (string) $value->getKey();
            } elseif (is_scalar($value) && $value !== '') {
                $result[$key] = Str::limit((string) $value, 60);
            }
        }

        return $result;
    }

    protected function actionFromPath(Request $request): string
    {
        $path = Str::lower($request->path());

        return match (true) {
            Str::contains($path, ['export', 'download', 'unduh']) => 'export',
            Str::contains($path, ['cetak', 'print', 'kwitansi', 'kuitansi', 'stiker', '/pdf']) => 'print',
            Str::contains($path, ['import']) => 'import',
            default => 'view',
        };
    }

    /** Nama modul dari URL, mis. /farmasi/restock-obat/... → "Restock Obat". */
    protected function moduleFromPath(Request $request): string
    {
        $segments = $this->meaningfulSegments($request);

        return $segments ? Str::headline($segments[0]) : 'Dashboard';
    }

    /** Judul halaman dari URL tanpa parameter, mis. "Farmasi / Restock Obat". */
    protected function titleFromPath(Request $request): string
    {
        $route = $request->route();
        $uri = $route ? $route->uri() : $request->path();

        $segments = array_values(array_filter(
            explode('/', $uri),
            fn ($segment) => $segment !== '' && ! str_starts_with($segment, '{') && $segment !== 'api'
        ));

        if ($segments === []) {
            return 'Beranda';
        }

        return collect($segments)
            ->take(-3)
            ->map(fn ($segment) => Str::headline(str_replace(['-', '_'], ' ', $segment)))
            ->implode(' / ');
    }

    /** @return array<int, string> */
    protected function meaningfulSegments(Request $request): array
    {
        $route = $request->route();
        $uri = $route ? $route->uri() : $request->path();
        $roles = config('audit.role_path_segments', []);

        return array_values(array_filter(
            explode('/', $uri),
            fn ($segment) => $segment !== '' && ! str_starts_with($segment, '{') && ! in_array($segment, $roles, true)
        ));
    }

    protected function jsonFlag(Response $response): ?bool
    {
        $data = json_decode((string) $response->getContent(), true);

        return is_array($data) && array_key_exists('success', $data) ? (bool) $data['success'] : null;
    }

    protected function jsonMessage(Response $response): ?string
    {
        $data = json_decode((string) $response->getContent(), true);

        return is_array($data) && is_string($data['message'] ?? null) ? $data['message'] : null;
    }

    protected function describe(string $action, string $module, ?Model $auditable): string
    {
        $verb = match ($action) {
            'view' => 'Membuka detail',
            'export' => 'Mengekspor data',
            'print' => 'Mencetak dokumen',
            'import' => 'Mengimpor data',
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
