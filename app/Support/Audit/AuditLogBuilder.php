<?php

namespace App\Support\Audit;

use Illuminate\Database\Eloquent\Builder;
use LogicException;

/**
 * Builder khusus audit_logs.
 *
 * Model event (updating/deleting) hanya menangkap operasi pada satu instance.
 * Operasi massal seperti `AuditLog::where(...)->delete()` tidak memicu event
 * tersebut, jadi harus ditutup di level builder agar audit log benar-benar
 * append-only untuk semua role, termasuk Super Admin.
 */
class AuditLogBuilder extends Builder
{
    public function update(array $values): never
    {
        throw new LogicException('Audit log bersifat append-only dan tidak dapat diubah.');
    }

    public function delete(): never
    {
        throw new LogicException('Audit log bersifat append-only dan tidak dapat dihapus.');
    }

    public function forceDelete(): never
    {
        throw new LogicException('Audit log bersifat append-only dan tidak dapat dihapus.');
    }

    public function truncate(): never
    {
        throw new LogicException('Audit log bersifat append-only dan tidak dapat dihapus.');
    }
}
