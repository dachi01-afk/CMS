<?php

namespace App\Exports;

use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export audit log memakai FromQuery supaya tabel besar tidak dimuat
 * seluruhnya ke memori.
 */
class AuditLogExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected array $filters = []) {}

    public function query()
    {
        return AuditLog::query()->filter($this->filters)->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Waktu',
            'User',
            'Role',
            'Action',
            'Module',
            'Tipe Entitas',
            'ID Entitas',
            'Deskripsi',
            'Nilai Lama',
            'Nilai Baru',
            'IP Address',
            'HTTP Method',
            'URL',
        ];
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->created_at?->format('d-m-Y H:i:s'),
            $log->user_name,
            $log->user_role,
            $log->action,
            $log->module,
            $log->auditable_type,
            $log->auditable_id,
            $log->description,
            $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : null,
            $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : null,
            $log->ip_address,
            $log->http_method,
            $log->url,
        ];
    }
}
