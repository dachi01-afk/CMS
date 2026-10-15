<?php

namespace App\Support\Audit;

use App\Observers\AuditableObserver;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Throwable;

/**
 * Membuat trigger MySQL yang mencatat SETIAP perubahan data ke audit_logs.
 *
 * Kenapa trigger: banyak bagian aplikasi menulis langsung lewat DB::table()
 * dan update/delete massal yang tidak terlihat oleh model observer. Trigger
 * berjalan di dalam database, jadi tidak ada jalur penulisan yang lolos —
 * termasuk API mobile, webhook, dan fitur yang ditambahkan di masa depan.
 *
 * Trigger dibangun dari struktur tabel yang sebenarnya (information_schema),
 * sehingga perlu dibuat ulang setelah struktur berubah. Ini dilakukan otomatis
 * setiap `php artisan migrate` selesai (lihat AuditDatabaseLifecycle).
 */
class AuditTriggerInstaller
{
    public const PREFIX = 'audit_';

    /** Batas kedalaman penelusuran relasi untuk menemukan pasien. */
    protected const MAX_PATH_DEPTH = 3;

    protected Connection $db;

    /** @var array<string, array<int, array{name: string, type: string}>> */
    protected array $columns = [];

    /** @var array<string, array<string, string>> tabel → [kolom → tabel tujuan] */
    protected array $references = [];

    /** @var array<string, string> tabel → kelas model */
    protected array $models = [];

    public function __construct(?Connection $connection = null)
    {
        $this->db = $connection ?? DB::connection();
    }

    public static function supported(?Connection $connection = null): bool
    {
        $connection ??= DB::connection();

        return $connection->getDriverName() === 'mysql'
            && config('audit.capture', 'trigger') === 'trigger';
    }

    /**
     * Hapus lalu buat ulang semua trigger audit.
     *
     * @return array{tables: array<int, string>, triggers: int}
     */
    public function install(): array
    {
        $this->loadSchema();
        $this->drop();

        // Tabel audit_logs belum ada (mis. migrasi di-rollback): jangan pasang
        // trigger, karena INSERT ke tabel yang tidak ada akan menggagalkan
        // semua penyimpanan data.
        if (! isset($this->columns['audit_logs'])) {
            return ['tables' => [], 'triggers' => 0];
        }

        $tables = $this->auditedTables();
        $count = 0;

        foreach ($tables as $table) {
            foreach ($this->triggersFor($table) as $sql) {
                $this->db->unprepared($sql);
                $count++;
            }
        }

        foreach ($this->protectionTriggers() as $sql) {
            $this->db->unprepared($sql);
            $count++;
        }

        return ['tables' => $tables, 'triggers' => $count];
    }

    /** Hapus semua trigger buatan audit (berawalan audit_). */
    public function drop(): int
    {
        $names = $this->db->select(
            'SELECT TRIGGER_NAME AS name FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME LIKE ?',
            [str_replace('_', '\\_', self::PREFIX).'%']
        );

        foreach ($names as $row) {
            $this->db->unprepared('DROP TRIGGER IF EXISTS '.$this->id($row->name));
        }

        return count($names);
    }

    /** Daftar tabel yang akan/sudah dipasangi trigger. */
    public function auditedTables(): array
    {
        if ($this->columns === []) {
            $this->loadSchema();
        }

        $excluded = config('audit.excluded_tables', []);

        return array_values(array_filter(array_keys($this->columns), function (string $table) use ($excluded) {
            foreach ($excluded as $pattern) {
                if (Str::is($pattern, $table)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /** Status pemasangan trigger untuk perintah artisan. */
    public function status(): array
    {
        $rows = $this->db->select(
            'SELECT EVENT_OBJECT_TABLE AS tbl, COUNT(*) AS total FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME LIKE ? GROUP BY EVENT_OBJECT_TABLE',
            [str_replace('_', '\\_', self::PREFIX).'%']
        );

        $installed = [];

        foreach ($rows as $row) {
            $installed[$row->tbl] = (int) $row->total;
        }

        $tables = $this->auditedTables();

        return [
            'audited' => $tables,
            'installed' => $installed,
            'missing' => array_values(array_filter($tables, fn ($t) => ($installed[$t] ?? 0) < 3)),
        ];
    }

    // ------------------------------------------------------------------
    // Pembentukan SQL trigger
    // ------------------------------------------------------------------

    /** @return array<int, string> */
    public function triggersFor(string $table): array
    {
        return [
            $this->insertTrigger($table),
            $this->updateTrigger($table),
            $this->deleteTrigger($table),
        ];
    }

    protected function insertTrigger(string $table): string
    {
        $body = $this->insertLog($table, 'NEW', 'create', 'NULL', $this->jsonObject($table, 'NEW'),
            $this->describe($table, 'NEW', 'Menambah'));

        return $this->wrap($table, 'ai', 'AFTER INSERT', $body);
    }

    protected function deleteTrigger(string $table): string
    {
        $body = $this->insertLog($table, 'OLD', 'delete', $this->jsonObject($table, 'OLD'), 'NULL',
            $this->describe($table, 'OLD', 'Menghapus'));

        return $this->wrap($table, 'ad', 'AFTER DELETE', $body);
    }

    protected function updateTrigger(string $table): string
    {
        $checks = [];

        foreach ($this->trackedColumns($table) as $column) {
            $name = $column['name'];
            $col = $this->id($name);
            $field = $this->quote($name);

            if ($this->isSensitive($name)) {
                // Nilai rahasia tidak pernah disimpan, tetapi perubahannya tetap
                // terlihat di daftar "Field berubah".
                $checks[] = "IF NOT (OLD.{$col} <=> NEW.{$col}) THEN SET v_fields = CONCAT_WS(', ', v_fields, {$field}); END IF;";

                continue;
            }

            $path = $this->quote('$."'.str_replace('"', '\\"', $name).'"');
            $old = $this->value($column, 'OLD');
            $new = $this->value($column, 'NEW');

            $checks[] = "IF NOT (OLD.{$col} <=> NEW.{$col}) THEN "
                ."SET v_old = JSON_SET(v_old, {$path}, {$old}); "
                ."SET v_new = JSON_SET(v_new, {$path}, {$new}); "
                ."SET v_fields = CONCAT_WS(', ', v_fields, {$field}); END IF;";
        }

        $description = 'CONCAT('.$this->describe($table, 'OLD', 'Mengubah').", ' Field berubah: ', v_fields, '.')";

        $log = $this->insertLog(
            $table, 'NEW', 'update',
            'IF(JSON_LENGTH(v_old) = 0, NULL, v_old)',
            'IF(JSON_LENGTH(v_new) = 0, NULL, v_new)',
            $description
        );

        $body = "DECLARE v_old JSON DEFAULT JSON_OBJECT();\n"
            ."DECLARE v_new JSON DEFAULT JSON_OBJECT();\n"
            ."DECLARE v_fields TEXT DEFAULT NULL;\n"
            .'IF COALESCE(@audit_disabled, 0) = 0 THEN '."\n"
            .implode("\n", $checks)."\n"
            ."IF v_fields IS NOT NULL THEN\n{$log}\nEND IF;\n"
            .'END IF;';

        return $this->wrap($table, 'au', 'AFTER UPDATE', $body, false);
    }

    protected function wrap(string $table, string $suffix, string $timing, string $body, bool $guard = true): string
    {
        $name = $this->triggerName($table, $suffix);

        if ($guard) {
            $body = "IF COALESCE(@audit_disabled, 0) = 0 THEN\n{$body}\nEND IF;";
        }

        return "CREATE TRIGGER {$this->id($name)} {$timing} ON {$this->id($table)} FOR EACH ROW\nBEGIN\n{$body}\nEND";
    }

    /** Pernyataan INSERT ke audit_logs. */
    protected function insertLog(string $table, string $row, string $action, string $old, string $new, string $description): string
    {
        $type = $this->quote($this->models[$table] ?? $table);
        $module = $this->quote($this->moduleFor($table));
        $id = $this->hasColumn($table, 'id') && $this->isNumericColumn($table, 'id') ? "{$row}.`id`" : 'NULL';
        $pasien = $this->pasienExpression($table, $row);

        $values = [
            'user_id' => '@audit_user_id',
            'user_name' => "COALESCE(@audit_user_name, 'Sistem')",
            'user_role' => '@audit_user_role',
            'action' => $this->quote($action),
            'module' => $module,
            'auditable_type' => $type,
            'auditable_id' => $id,
            'pasien_id' => $pasien,
            'old_values' => $old,
            'new_values' => $new,
            'description' => $description,
            'ip_address' => '@audit_ip',
            'user_agent' => '@audit_user_agent',
            'url' => '@audit_url',
            'http_method' => '@audit_method',
            'source' => "COALESCE(@audit_source, 'sistem')",
            'created_at' => "IF(@audit_tz IS NULL, NOW(), CONVERT_TZ(UTC_TIMESTAMP(), '+00:00', @audit_tz))",
        ];

        // Hanya kolom yang benar-benar ada di audit_logs (aman bila migrasi
        // kolom tambahan belum / tidak dijalankan).
        $values = array_filter($values, fn ($column) => $this->hasColumn('audit_logs', $column), ARRAY_FILTER_USE_KEY);

        return 'INSERT INTO `audit_logs` ('.implode(', ', array_map(fn ($c) => $this->id($c), array_keys($values))).') VALUES ('
            .implode(', ', $values).');';
    }

    /** JSON_OBJECT seluruh kolom (kecuali kolom rahasia & yang diabaikan). */
    protected function jsonObject(string $table, string $row): string
    {
        $pairs = [];

        foreach ($this->trackedColumns($table) as $column) {
            // ID & waktu dibuat sudah tersimpan di kolom log sendiri.
            if ($this->isSensitive($column['name']) || in_array($column['name'], ['id', 'created_at'], true)) {
                continue;
            }

            $pairs[] = $this->quote($column['name']).', '.$this->value($column, $row);
        }

        return $pairs === [] ? 'NULL' : 'JSON_OBJECT('.implode(', ', $pairs).')';
    }

    /** Ekspresi nilai kolom yang aman dimasukkan ke JSON. */
    protected function value(array $column, string $row): string
    {
        $ref = "{$row}.{$this->id($column['name'])}";

        return match (true) {
            in_array($column['type'], ['datetime', 'timestamp', 'date', 'time', 'year'], true) => "CAST({$ref} AS CHAR)",
            in_array($column['type'], ['binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'geometry', 'point'], true) => "IF({$ref} IS NULL, NULL, CONCAT('[data biner ', LENGTH({$ref}), ' byte]'))",
            $column['type'] === 'bit' => "CAST({$ref} AS UNSIGNED)",
            default => $ref,
        };
    }

    /**
     * Deskripsi mudah dibaca, mis. "Menambah data Pasien (Budi Santoso)."
     */
    protected function describe(string $table, string $row, string $verb): string
    {
        $entity = $this->quote($verb.' data '.$this->entityName($table));
        $label = $this->labelExpression($table, $row);

        if ($label === null) {
            return "CONCAT({$entity}, '.')";
        }

        return "CONCAT({$entity}, IFNULL(CONCAT(' (', {$label}, ')'), ''), '.')";
    }

    protected function labelExpression(string $table, string $row): ?string
    {
        $parts = [];
        $label = $this->labelColumn($table);

        if ($label) {
            $parts[] = "NULLIF(CAST({$row}.{$this->id($label)} AS CHAR), '')";
        }

        if ($this->hasColumn($table, 'id')) {
            $parts[] = "CONCAT('#', {$row}.`id`)";
        }

        return $parts === [] ? null : 'COALESCE('.implode(', ', $parts).')';
    }

    protected function labelColumn(string $table): ?string
    {
        foreach (config('audit.label_columns', []) as $candidate) {
            if ($this->hasColumn($table, $candidate)) {
                return $candidate;
            }
        }

        foreach ($this->columns[$table] ?? [] as $column) {
            if (preg_match('/^(nama|kode|no)_/', $column['name']) && ! $this->isSensitive($column['name'])) {
                return $column['name'];
            }
        }

        return null;
    }

    /**
     * Ekspresi SQL untuk mencari pasien yang berkaitan dengan baris ini,
     * dengan menelusuri relasi (kolom *_id / foreign key) sampai bertemu
     * tabel yang punya kolom pasien_id. Contoh hasil_lab:
     * hasil_lab.order_lab_detail_id → order_lab_detail.order_lab_id → order_lab.pasien_id
     */
    public function pasienExpression(string $table, string $row): string
    {
        if ($this->columns === []) {
            $this->loadSchema();
        }

        $pasienTable = 'pasien';

        if ($table === $pasienTable) {
            return "{$row}.`id`";
        }

        $candidates = [];

        if ($this->hasColumn($table, 'pasien_id')) {
            $candidates[] = "{$row}.`pasien_id`";
        }

        // Akun user milik pasien.
        if (in_array($table, ['user', 'users'], true) && $this->hasColumn($pasienTable, 'user_id')) {
            $candidates[] = "(SELECT `id` FROM `{$pasienTable}` WHERE `user_id` = {$row}.`id` LIMIT 1)";
        }

        foreach ($this->pathsToPasien($table) as $path) {
            $candidates[] = $this->pathExpression($path, $row);

            if (count($candidates) >= 4) {
                break;
            }
        }

        if ($candidates === []) {
            return 'NULL';
        }

        return count($candidates) === 1 ? $candidates[0] : 'COALESCE('.implode(', ', $candidates).')';
    }

    /**
     * Cari jalur relasi terpendek ke tabel yang memiliki pasien_id.
     *
     * @return array<int, array<int, array{column: string, table: string}>>
     */
    protected function pathsToPasien(string $start): array
    {
        $paths = [];
        $queue = [[$start, []]];
        $visited = [$start => true];

        while ($queue) {
            [$table, $path] = array_shift($queue);

            if (count($path) >= self::MAX_PATH_DEPTH) {
                continue;
            }

            foreach ($this->references[$table] ?? [] as $column => $target) {
                if (isset($visited[$target]) || $column === 'pasien_id') {
                    continue;
                }

                $next = [...$path, ['column' => $column, 'table' => $target]];

                if ($target === 'pasien' || $this->hasColumn($target, 'pasien_id')) {
                    $paths[] = $next;

                    continue;
                }

                $visited[$target] = true;
                $queue[] = [$target, $next];
            }
        }

        return $paths;
    }

    protected function pathExpression(array $path, string $row): string
    {
        // Mulai dari nilai kolom di baris yang berubah.
        $expr = "{$row}.{$this->id($path[0]['column'])}";

        foreach ($path as $index => $step) {
            $target = $step['table'];
            $next = $path[$index + 1] ?? null;

            $select = $next
                ? $this->id($next['column'])
                : ($target === 'pasien' ? '`id`' : '`pasien_id`');

            $expr = "(SELECT {$select} FROM {$this->id($target)} WHERE `id` = {$expr} LIMIT 1)";
        }

        return $expr;
    }

    /** Trigger yang membuat audit_logs tidak bisa diubah/dihapus dari mana pun. */
    protected function protectionTriggers(): array
    {
        if (! isset($this->columns['audit_logs'])) {
            return [];
        }

        $message = 'Audit log bersifat append-only: data tidak dapat diubah atau dihapus.';

        return [
            "CREATE TRIGGER `audit_logs_tolak_ubah` BEFORE UPDATE ON `audit_logs` FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = {$this->quote($message)}; END",
            "CREATE TRIGGER `audit_logs_tolak_hapus` BEFORE DELETE ON `audit_logs` FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = {$this->quote($message)}; END",
        ];
    }

    // ------------------------------------------------------------------
    // Struktur database
    // ------------------------------------------------------------------

    protected function loadSchema(): void
    {
        $this->columns = [];
        $this->references = [];

        $rows = $this->db->select(
            "SELECT c.TABLE_NAME AS tbl, c.COLUMN_NAME AS col, c.DATA_TYPE AS type
             FROM information_schema.COLUMNS c
             JOIN information_schema.TABLES t ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME
             WHERE c.TABLE_SCHEMA = DATABASE() AND t.TABLE_TYPE = 'BASE TABLE'
             ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION"
        );

        foreach ($rows as $row) {
            $this->columns[$row->tbl][] = ['name' => $row->col, 'type' => strtolower($row->type)];
        }

        // Relasi dari foreign key yang benar-benar ada di database.
        $foreign = $this->db->select(
            'SELECT TABLE_NAME AS tbl, COLUMN_NAME AS col, REFERENCED_TABLE_NAME AS ref
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL AND REFERENCED_COLUMN_NAME = ?',
            ['id']
        );

        foreach ($foreign as $row) {
            $this->references[$row->tbl][$row->col] = $row->ref;
        }

        // Relasi dari konvensi nama kolom (xxx_id → tabel xxx) untuk kolom
        // yang tidak diberi foreign key.
        $actorColumns = ['user_id', 'created_by', 'updated_by', 'deleted_by', 'tokenable_id', 'auditable_id'];

        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $column) {
                $name = $column['name'];

                if (! str_ends_with($name, '_id') || in_array($name, $actorColumns, true) || isset($this->references[$table][$name])) {
                    continue;
                }

                $base = substr($name, 0, -3);

                foreach ([$base, Str::plural($base), $base.'s'] as $guess) {
                    if ($guess !== $table && $this->hasColumn($guess, 'id')) {
                        $this->references[$table][$name] = $guess;

                        break;
                    }
                }
            }
        }

        $this->models = $this->discoverModels();
    }

    /**
     * Peta tabel → kelas model, supaya auditable_type tetap berisi nama kelas
     * (mis. App\Models\Pasien) seperti log yang ditulis dari aplikasi.
     *
     * @return array<string, string>
     */
    protected function discoverModels(): array
    {
        $map = [];
        $preferred = array_keys(AuditableObserver::modules());

        foreach (File::glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\'.pathinfo($file, PATHINFO_FILENAME);

            try {
                if (! class_exists($class)) {
                    continue;
                }

                $reflection = new ReflectionClass($class);

                if ($reflection->isAbstract() || ! $reflection->isSubclassOf(Model::class)) {
                    continue;
                }

                $table = (new $class)->getTable();
            } catch (Throwable) {
                continue;
            }

            if (! isset($map[$table]) || in_array($class, $preferred, true)) {
                $map[$table] = $class;
            }
        }

        return $map;
    }

    /** @return array<int, array{name: string, type: string}> */
    protected function trackedColumns(string $table): array
    {
        $ignored = config('audit.ignored_columns', []);

        return array_values(array_filter(
            $this->columns[$table] ?? [],
            fn ($column) => ! in_array($column['name'], $ignored, true)
        ));
    }

    protected function hasColumn(string $table, string $column): bool
    {
        foreach ($this->columns[$table] ?? [] as $item) {
            if ($item['name'] === $column) {
                return true;
            }
        }

        return false;
    }

    protected function isNumericColumn(string $table, string $column): bool
    {
        foreach ($this->columns[$table] ?? [] as $item) {
            if ($item['name'] === $column) {
                return in_array($item['type'], ['bigint', 'int', 'integer', 'mediumint', 'smallint', 'tinyint'], true);
            }
        }

        return false;
    }

    protected function isSensitive(string $column): bool
    {
        return AuditLogger::isSensitiveField($column);
    }

    public function moduleFor(string $table): string
    {
        foreach (config('audit.table_modules', []) as $pattern => $module) {
            if (Str::is($pattern, $table)) {
                return $module;
            }
        }

        if (isset($this->models[$table])) {
            return AuditableObserver::moduleFor($this->models[$table]);
        }

        return Str::headline($table);
    }

    /** Nama entitas untuk deskripsi, mis. "Order Lab Detail". */
    protected function entityName(string $table): string
    {
        return config('audit.table_entities', [])[$table] ?? Str::headline($table);
    }

    protected function triggerName(string $table, string $suffix): string
    {
        $name = self::PREFIX.$table.'_'.$suffix;

        return strlen($name) <= 64 ? $name : self::PREFIX.substr(md5($table), 0, 12).'_'.$suffix;
    }

    protected function id(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }

    protected function quote(string $value): string
    {
        return $this->db->getPdo()->quote($value);
    }
}
