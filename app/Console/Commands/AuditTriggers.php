<?php

namespace App\Console\Commands;

use App\Support\Audit\AuditTriggerInstaller;
use Illuminate\Console\Command;
use Throwable;

/**
 * php artisan audit:triggers           → pasang / bangun ulang semua trigger audit
 * php artisan audit:triggers --status  → cek tabel mana yang sudah tercatat
 * php artisan audit:triggers --drop    → lepas semua trigger audit
 */
class AuditTriggers extends Command
{
    protected $signature = 'audit:triggers
        {--status : Tampilkan status trigger tanpa mengubah apa pun}
        {--drop : Lepas semua trigger audit}';

    protected $description = 'Pasang trigger MySQL yang mencatat semua perubahan data ke audit_logs';

    public function handle(): int
    {
        if (! AuditTriggerInstaller::supported()) {
            $this->error('Trigger audit hanya untuk MySQL/MariaDB dengan AUDIT_CAPTURE=trigger.');

            return self::FAILURE;
        }

        $installer = new AuditTriggerInstaller;

        try {
            if ($this->option('status')) {
                return $this->status($installer);
            }

            if ($this->option('drop')) {
                $count = $installer->drop();
                $this->warn("{$count} trigger audit dilepas. Perubahan data tidak tercatat sampai dipasang lagi.");

                return self::SUCCESS;
            }

            $result = $installer->install();
            $this->info(sprintf('%d trigger terpasang di %d tabel.', $result['triggers'], count($result['tables'])));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Gagal: '.$e->getMessage());
            $this->line('Pastikan user database memiliki hak TRIGGER (dan SUPER / log_bin_trust_function_creators=1 bila binary log aktif).');

            return self::FAILURE;
        }
    }

    protected function status(AuditTriggerInstaller $installer): int
    {
        $status = $installer->status();

        $this->table(['Tabel', 'Trigger'], array_map(
            fn ($table) => [$table, ($status['installed'][$table] ?? 0) >= 3 ? 'tercatat' : 'BELUM'],
            $status['audited']
        ));

        if ($status['missing']) {
            $this->warn(count($status['missing']).' tabel belum tercatat. Jalankan: php artisan audit:triggers');

            return self::FAILURE;
        }

        $this->info('Semua '.count($status['audited']).' tabel sudah tercatat.');

        return self::SUCCESS;
    }
}
